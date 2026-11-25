"""OpenAI-compatible transport for Kandes. Diagnostics contain metadata only."""
import json
import os
import re
import sys
import time
import urllib.error
import urllib.request
from contextlib import contextmanager
from pathlib import Path
from urllib.parse import urlsplit

from .provider import ApiError, _NoRedirect, retry_after_seconds

DEFAULT_URL = 'https://apistudio.kandes.shop/v1'
DEFAULT_MODEL = 'gemini-3-8-flash-high'
TRANSIENT = (429, 502, 503)
BILLING_CODES = {'insufficient_quota', 'insufficient_balance', 'insufficient_credits',
                 'billing_hard_limit_reached', 'billing_not_active', 'payment_required', 'out_of_credits'}
AUTH_CODES = {'invalid_api_key', 'invalid_token', 'authentication_error', 'unauthorized'}


@contextmanager
def request_slot(path):
    if not path:
        yield
        return
    path = Path(path)
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open('a+b') as lock:
        lock.seek(0)
        if os.name == 'nt':
            import msvcrt
            acquire = lambda: msvcrt.locking(lock.fileno(), msvcrt.LK_NBLCK, 1)
        else:
            import fcntl
            acquire = lambda: fcntl.flock(lock.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
        try:
            acquire()
        except OSError:
            raise ApiError('Kandes đang xử lý một yêu cầu khác.', 'pacing_wait', retry_after=5) from None
        yield  # Closing the descriptor releases the lock, including on failure.


class KandesClient:
    def __init__(self, api_key, model=DEFAULT_MODEL, timeout=60, pacer=None, deadline=None):
        self._api_key = (api_key or '').strip()
        if not self._api_key:
            raise ApiError('Chưa cấu hình KANDES_API_KEY.', 'missing_key')
        base = os.environ.get('KANDES_BASE_URL', DEFAULT_URL).strip().rstrip('/')
        try:
            parsed = urlsplit(base)
            valid = parsed.scheme == 'https' and parsed.hostname and not (
                parsed.username or parsed.password or parsed.query or parsed.fragment)
        except ValueError:
            valid = False
        if not valid:
            raise ApiError('KANDES_BASE_URL phải là URL HTTPS hợp lệ.', 'invalid_request')
        if not model or len(model) > 120 or any(not (c.isascii() and (c.isalnum() or c in '._-/')) for c in model):
            raise ApiError('KANDES_MODEL không hợp lệ.', 'invalid_model')
        self.url = base + '/chat/completions'
        self.model, self.timeout, self.pacer = model, timeout, pacer
        self.deadline = deadline if deadline is not None else getattr(pacer, 'deadline', None)
        self.http_calls = []
        self._opener = urllib.request.build_opener(_NoRedirect())

    def model_info(self):
        # Local working limits for this adapter. Chat Completions has no token-count contract.
        return {'name': self.model, 'inputTokenLimit': 32768, 'outputTokenLimit': 16384,
                'supportedGenerationMethods': ['generateContent']}

    @staticmethod
    def messages(prompt, schema=None):
        messages = [{'role': 'user', 'content': prompt}]
        if schema is not None:
            messages.insert(0, {'role': 'system', 'content': 'Return a JSON object matching this JSON Schema: '
                                + json.dumps(schema, ensure_ascii=False, separators=(',', ':'))})
        return messages

    def count_tokens(self, prompt, schema, max_output_tokens):
        encoded = json.dumps(self.messages(prompt, schema), ensure_ascii=False).encode('utf-8')
        return (len(encoded) + 2) // 3 + 32

    @staticmethod
    def _terminal_error(error, status=None):
        if not isinstance(error, dict):
            error = {'message': error}
        tags = {str(error.get(field, '')).lower() for field in ('code', 'type')}
        message = str(error.get('message', '')).lower()
        if status == 402 or tags & BILLING_CODES or any(marker in message for marker in (
            'insufficient balance', 'insufficient credits', 'insufficient quota', 'out of credits',
            'payment required', 'billing hard limit', 'không đủ số dư', 'hết số dư',
        )):
            return ApiError('Kandes báo hết số dư hoặc có lỗi thanh toán.', 'billing', status)
        if status in (401, 403) or tags & AUTH_CODES or 'invalid api key' in message:
            return ApiError('Kandes từ chối API key hoặc quyền truy cập.', 'authentication', status)
        return None

    @classmethod
    def _http_error(cls, exc):
        status = exc.code
        retry_after = retry_after_seconds(exc.headers.get('Retry-After')) if exc.headers else None
        try:
            envelope = json.loads(exc.read(16384))
            error = envelope.get('error', envelope)
        except (ValueError, AttributeError, OSError):
            error = {}
        finally:
            exc.close()
        terminal = cls._terminal_error(error, status)
        if terminal:
            return terminal
        code = {400: 'invalid_request', 404: 'invalid_model', 429: 'rate_limit'}.get(status, 'http_error')
        return ApiError(f'Kandes trả HTTP {status}.', code, status, retry_after)

    def _send(self, request, input_tokens):
        if self.pacer:
            self.pacer.before_request(input_tokens)
        timeout = min(self.timeout, self.deadline - time.monotonic()) if self.deadline else self.timeout
        if timeout < 1:
            raise ApiError('Đã hết thời gian lượt xử lý nền.', 'worker_budget')
        event = {'provider': 'kandes', 'model': self.model, 'operation': 'chat_completions'}
        started = time.monotonic()
        try:
            with request_slot(os.environ.get('KANDES_LOCK_FILE')):
                self.http_calls.append(event)
                with self._opener.open(request, timeout=timeout) as response:
                    raw = response.read(16 * 1024 * 1024 + 1)
            if len(raw) > 16 * 1024 * 1024:
                raise ApiError('Phản hồi Kandes quá lớn.', 'invalid_response')
            result = json.loads(raw)
            if not isinstance(result, dict):
                raise ApiError('Phản hồi Kandes phải là JSON object.', 'invalid_response')
            if result.get('error'):
                raise self._terminal_error(result['error']) or ApiError(
                    'Kandes trả lỗi trong phản hồi.', 'invalid_response')
            usage = result.get('usage') or {}
            if isinstance(usage, dict):
                event.update({field: usage[key] for field, key in
                             [('input_tokens', 'prompt_tokens'), ('output_tokens', 'completion_tokens')]
                             if type(usage.get(key)) is int and usage[key] >= 0})
            event['status'] = 200
            return result
        except urllib.error.HTTPError as exc:
            event['status'] = exc.code
            raise self._http_error(exc) from None
        except (urllib.error.URLError, TimeoutError, OSError) as exc:
            reason = exc.reason if isinstance(exc, urllib.error.URLError) else exc
            code = 'provider_timeout' if isinstance(reason, TimeoutError) else 'network_error'
            event['status'] = code
            raise ApiError('Kandes chưa phản hồi hoặc kết nối bị gián đoạn.', code) from None
        except (ValueError, UnicodeError) as exc:
            event['status'] = exc.code if isinstance(exc, ApiError) else 'invalid_response'
            if isinstance(exc, ApiError):
                raise
            raise ApiError('Kandes trả phản hồi JSON không hợp lệ.', 'invalid_response') from None
        finally:
            event['seconds'] = round(time.monotonic() - started, 3)
            print('AI_PROVIDER_METRIC ' + json.dumps(event), file=sys.stderr, flush=True)

    def complete(self, prompt, max_output_tokens, schema=None, json_mode=True, attempts=2, input_tokens=0):
        body = {'model': self.model, 'messages': self.messages(prompt, schema),
                'max_tokens': max_output_tokens, 'stream': False}
        if json_mode:
            body['response_format'] = {'type': 'json_object'}
        request = urllib.request.Request(self.url, json.dumps(body, ensure_ascii=False).encode('utf-8'),
            headers={'Content-Type': 'application/json', 'Authorization': 'Bearer ' + self._api_key}, method='POST')
        for attempt in range(attempts):
            try:
                response = self._send(request, input_tokens)
                break
            except ApiError as exc:
                exc.metadata.setdefault('model', self.model)
                transient = exc.code not in ('billing', 'authentication') and (
                    exc.status in TRANSIENT or exc.code == 'provider_timeout')
                delay = max(2 ** (attempt + 1), exc.retry_after or 0)
                if not transient or attempt + 1 == attempts or delay > 8:
                    limiter = getattr(self.pacer, 'limiter', None)
                    if transient and limiter and (exc.status == 429 or exc.retry_after is not None):
                        exc.metadata['cooldown_until'] = limiter.defer(self.model, {
                            'code': 'rate_limit' if exc.status == 429 else 'service_unavailable',
                            'http_status': exc.status, 'quota': 'unknown',
                            'retry_after': exc.retry_after if exc.retry_after is not None else 60,
                        })
                    raise
                # Wait before the next request, which rechecks cancellation and the worker deadline.
                time.sleep(delay)
        choices = response.get('choices')
        choice = choices[0] if isinstance(choices, list) and choices else None
        message = choice.get('message') if isinstance(choice, dict) else None
        content = message.get('content') if isinstance(message, dict) else None
        if not isinstance(content, str) or not content.strip() or choice.get('finish_reason') != 'stop':
            raise ApiError('Kandes chưa trả nội dung hoàn chỉnh.', 'incomplete_response')
        if not json_mode:
            return content, response.get('usage')
        # Some compatible gateways still wrap a JSON object in a single Markdown fence.
        fenced = re.fullmatch(r'```(?:json)?[ \t]*\r?\n(.*)\r?\n[ \t]*```', content.strip(),
                              flags=re.DOTALL | re.IGNORECASE)
        try:
            data = json.loads(fenced.group(1) if fenced else content)
        except ValueError:
            raise ApiError('Kandes trả nội dung JSON không hợp lệ.', 'invalid_output') from None
        if not isinstance(data, dict):
            raise ApiError('Nội dung Kandes phải là JSON object.', 'invalid_output')
        return data, response.get('usage')

    def generate(self, prompt, schema, max_output_tokens, input_tokens):
        data, usage = self.complete(prompt, max_output_tokens, schema=schema, input_tokens=input_tokens)
        usage = usage if isinstance(usage, dict) else {}
        aliases = {'prompt_tokens': 'promptTokenCount', 'completion_tokens': 'candidatesTokenCount',
                   'total_tokens': 'totalTokenCount'}
        normalized = {output: usage[key] for key, output in aliases.items() if type(usage.get(key)) is int}
        return {'data': data, 'usage': {**normalized, 'provider_model': self.model}, 'finish_reason': 'STOP'}


def probe():
    """Explicit connectivity check with two short prompts and one attempt each."""
    client = KandesClient(os.environ.get('KANDES_API_KEY'), os.environ.get('KANDES_MODEL', DEFAULT_MODEL), timeout=30)
    text, _ = client.complete('Reply with the single word OK.', 256, json_mode=False, attempts=1)
    if text.strip().rstrip('.').upper() != 'OK':
        raise ApiError('Kandes phản hồi nhưng không qua kiểm tra văn bản.', 'invalid_output')
    schema = {'type': 'object', 'properties': {'check': {'type': 'string'}},
              'required': ['check'], 'additionalProperties': False}
    data, _ = client.complete('Set check to the string ok.', 256, schema=schema, attempts=1)
    if data != {'check': 'ok'}:
        raise ApiError('Kandes phản hồi nhưng không qua kiểm tra cấu trúc JSON.', 'invalid_output')
    return {'ok': True, 'provider': 'kandes', 'model': client.model, 'checks': ['text', 'json_object']}


if __name__ == '__main__':
    if sys.argv[1:] != ['--probe']:
        sys.exit(2)
    try:
        print(json.dumps(probe()))
    except ApiError as exc:
        print(json.dumps({'ok': False, **exc.details()}))
        sys.exit(1)

