"""Gemini REST adapter. Never logs credentials, prompts, or raw provider errors."""
import json
import re
import socket
import time
import urllib.error
import urllib.request
from collections import deque

BASE = 'https://generativelanguage.googleapis.com/v1beta/'


class ApiError(ValueError):
    def __init__(self, message, code='api_error', status=None):
        super().__init__(message)
        self.code, self.status = code, status


class RequestPacer:
    """Conservative local RPM/input-TPM pacing, shared across clicks in one session."""
    def __init__(self, rpm=12, tpm=0, clock=time.monotonic, sleep=time.sleep):
        self.rpm, self.tpm = rpm, tpm
        self.clock, self.sleep = clock, sleep
        self.events = deque()

    def before_request(self, input_tokens=0):
        if input_tokens < 0 or not 1 <= self.rpm <= 1000 or self.tpm < 0:
            raise ApiError('RPM/TPM không hợp lệ.', 'invalid_settings')
        if self.tpm and input_tokens > self.tpm:
            raise ApiError('Một request đã vượt TPM bạn nhập. Giảm target đóng gói hoặc kiểm tra quota.', 'tpm_limit')
        now = self.clock()
        while self.events and now - self.events[0][0] >= 60:
            self.events.popleft()
        wait = 0.
        if self.events:
            wait = max(wait, self.events[-1][0] + 60 / self.rpm - now)
        if len(self.events) >= self.rpm:
            wait = max(wait, self.events[-self.rpm][0] + 60 - now)
        if self.tpm:
            total = sum(n for _, n in self.events)
            for at, n in self.events:
                if total + input_tokens <= self.tpm:
                    break
                wait = max(wait, at + 60 - now)
                total -= n
        if wait > 20:
            raise ApiError('Cần chờ cửa sổ RPM/TPM. Kết quả đã có được giữ; chờ khoảng một phút rồi bấm tiếp tục.', 'pacing_wait')
        if wait > 0:
            self.sleep(wait)
        self.events.append((self.clock(), input_tokens))


class _NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        raise ApiError('API trả chuyển hướng ngoài dự kiến; đã dừng gửi.', 'redirect')


class GeminiClient:
    def __init__(self, api_key, model='gemini-2.5-flash', timeout=60, pacer=None):
        if not isinstance(api_key, str) or not api_key.strip():
            raise ApiError('Nhập Gemini API key trong ô ẩn hoặc biến môi trường GEMINI_API_KEY.', 'missing_key')
        model = model.removeprefix('models/')
        if not re.fullmatch(r'[A-Za-z0-9][A-Za-z0-9._-]{0,120}', model):
            raise ApiError('Model ID không hợp lệ.', 'invalid_model')
        self._api_key = api_key.strip()
        self.model, self.timeout, self.pacer = model, timeout, pacer
        self.http_calls = []
        self._opener = urllib.request.build_opener(_NoRedirect())

    def __repr__(self):
        return f'GeminiClient(model={self.model!r}, api_key=<hidden>)'

    def _request(self, path, payload=None, input_tokens=0):
        method = 'GET' if payload is None else 'POST'
        if self.pacer:
            self.pacer.before_request(input_tokens)
        request = urllib.request.Request(BASE + path,
            data=None if payload is None else json.dumps(payload, ensure_ascii=False).encode('utf-8'),
            headers={'Content-Type': 'application/json', 'x-goog-api-key': self._api_key}, method=method)
        started = time.monotonic()
        event = {'operation': path.split(':')[-1] if ':' in path else 'model_metadata', 'status': 'attempted'}
        self.http_calls.append(event)
        try:
            with self._opener.open(request, timeout=self.timeout) as response:
                raw = response.read(16 * 1024 * 1024 + 1)
            if len(raw) > 16 * 1024 * 1024:
                raise ApiError('Phản hồi API quá lớn.', 'response_size')
            result = json.loads(raw)
            if not isinstance(result, dict):
                raise ApiError('API trả phản hồi không hợp lệ.', 'invalid_response')
            event['status'] = 'ok'
            return result
        except urllib.error.HTTPError as exc:
            messages = {400: 'Request/model không hỗ trợ cấu hình JSON hiện tại. Kiểm tra model và ngân sách.',
                401: 'API key không hợp lệ.', 403: 'API key chưa có quyền gọi model hoặc bị giới hạn truy cập.',
                404: 'Không tìm thấy model. Nhập đúng Model ID được cấp trong Google AI Studio.',
                429: 'Hết hoặc vượt quota/tốc độ. Đã dừng; không tự thử lại để tránh hao lượt.',
                500: 'Google đang lỗi máy chủ. Kết quả trước đó vẫn được giữ.',
                503: 'Model đang bận. Chờ rồi bấm tiếp tục.'}
            event['status'] = exc.code
            exc.close()
            raise ApiError(messages.get(exc.code, f'Gemini trả HTTP {exc.code}; đã dừng.'),
                           'rate_limit' if exc.code == 429 else 'http_error', exc.code) from None
        except (urllib.error.URLError, TimeoutError, socket.timeout, OSError):
            event['status'] = 'network_error'
            raise ApiError('Không kết nối được Gemini hoặc request hết thời gian. Không tự gọi lại; kiểm tra mạng rồi tiếp tục.', 'network_error') from None
        except (json.JSONDecodeError, UnicodeDecodeError):
            event['status'] = 'invalid_json'
            raise ApiError('API trả dữ liệu không phải JSON hợp lệ.', 'invalid_response') from None
        finally:
            event['seconds'] = round(time.monotonic() - started, 3)

    def model_info(self):
        info = self._request('models/' + self.model)
        return {key: info.get(key) for key in ('name', 'displayName', 'inputTokenLimit', 'outputTokenLimit', 'supportedGenerationMethods')}

    @staticmethod
    def request_body(prompt, schema, max_output_tokens):
        return {'contents': [{'role': 'user', 'parts': [{'text': prompt}]}],
                'generationConfig': {'responseMimeType': 'application/json', 'responseJsonSchema': schema,
                                     'maxOutputTokens': max_output_tokens}}

    def count_tokens(self, prompt, schema, max_output_tokens):
        body = self.request_body(prompt, schema, max_output_tokens)
        count = self._request('models/' + self.model + ':countTokens',
            {'generateContentRequest': {'model': 'models/' + self.model, **body}}).get('totalTokens')
        if type(count) is not int or count <= 0:
            raise ApiError('countTokens không trả số token hợp lệ.', 'invalid_token_count')
        return count

    def generate(self, prompt, schema, max_output_tokens, input_tokens):
        response = self._request('models/' + self.model + ':generateContent',
                                self.request_body(prompt, schema, max_output_tokens), input_tokens)
        usage = response.get('usageMetadata', {})
        candidates = response.get('candidates') or []
        if not candidates:
            raise ApiError('Không có câu trả lời: model có thể đã chặn nội dung. Không tạo tóm tắt giả.', 'blocked_response')
        candidate = candidates[0]
        if candidate.get('finishReason') != 'STOP':
            raise ApiError('Model chưa kết thúc đầy đủ (có thể hết output token). Tăng output limit hoặc giảm target gói; không dùng JSON bị cắt.', 'incomplete_response')
        text = ''.join(p.get('text', '') for p in candidate.get('content', {}).get('parts', []) if not p.get('thought'))
        try:
            result = json.loads(text)
        except (json.JSONDecodeError, TypeError):
            raise ApiError('Model trả JSON không hợp lệ. Kết quả này chưa được chấp nhận.', 'invalid_output') from None
        if not isinstance(result, dict):
            raise ApiError('Kết quả AI phải là JSON object.', 'invalid_output')
        return {'data': result, 'usage': usage, 'finish_reason': 'STOP'}
