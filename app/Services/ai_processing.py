"""Bounded retries and private checkpoints for document analysis calls.

Only validated successful responses are cached. Credentials and provider response
bodies never enter diagnostic output. No cache is used without an explicit path.
"""
import hashlib
import json
import math
import os
from pathlib import Path
import tempfile
import threading
import time


class ProcessingError(RuntimeError):
    def __init__(self, code, **details):
        self.code = code
        self.details = {k:v for k,v in details.items() if k in ('quota','retry_after','model','http_status')}
        super().__init__(code)


def error_detail(error, stage='ai'):
    value = str(error).lower()
    code = getattr(error, 'code', None)
    if not isinstance(code,str):
        if '429' in value or 'quota' in value: code='rate_limit'
        elif '401' in value or '403' in value: code='authentication'
        elif 'timeout' in value or 'timed out' in value or 'hết thời gian' in value: code='timeout'
        elif isinstance(error,json.JSONDecodeError): code='invalid_json'
        elif isinstance(error,ValueError): code='invalid_response'
        else: code='service_unavailable'
    messages = {
        'rate_limit':'Dịch vụ AI đang giới hạn lượt gọi. Đã giữ kết quả để tiếp tục sau thời gian chờ.',
        'authentication':'Dịch vụ AI từ chối thông tin xác thực hoặc quyền truy cập.',
        'timeout':'Dịch vụ AI chưa trả kết quả trong thời gian cho phép.',
        'connection_error':'Chưa kết nối được dịch vụ AI.',
        'budget_exhausted':'Đã hết thời gian của lượt xử lý nền; các bước thành công được giữ để tiếp tục.',
        'invalid_json':'Dịch vụ AI trả dữ liệu JSON không hợp lệ.',
        'invalid_response':'Dịch vụ AI trả kết quả thiếu cấu trúc cần thiết.',
        'invalid_request':'Dịch vụ AI từ chối định dạng yêu cầu; cần kiểm tra cấu hình.',
        'input_too_large':'Phần văn bản vượt ngân sách token đã cấu hình; cần chia nhỏ trước khi gửi.',
        'extraction_failed':'Không đọc được tệp báo cáo; hãy kiểm tra định dạng và nội dung tệp.',
        'model_unavailable':'Model đã cấu hình hiện không khả dụng.',
        'service_unavailable':'Dịch vụ AI tạm thời chưa xử lý được yêu cầu.',
    }
    if code not in messages: code='service_unavailable'
    detail={'stage':stage,'code':code,'message':messages[code]}
    detail.update(getattr(error,'details',{}))
    if detail.get('quota') == 'day': detail['message']='API bị giới hạn hạn mức ngày. Đã tạm dừng và giữ kết quả để tiếp tục sau thời gian chờ.'
    elif code == 'rate_limit' and detail.get('quota') == 'unknown': detail['message']='API giới hạn lượt gọi; chưa xác định được hạn mức phút hay ngày. Hệ thống sẽ chờ rồi thử tiếp.'
    return detail


class CallSession:
    def __init__(self, cache_dir=None, budget=None, namespace=""):
        self.cache_dir = Path(cache_dir) if cache_dir else None
        self.deadline = time.monotonic() + budget if budget else None
        self.slots = threading.BoundedSemaphore(2)
        self.lock = threading.Lock()
        self.cache_hits = 0
        self.checkpoint_failed = False
        self.errors = []
        self.namespace = namespace
        self.completed_calls = 0
        self.local = threading.local()
        self.progress_path = os.environ.get('AI_PROGRESS_FILE')
        self.limiter = None
        if os.environ.get('AI_RATE_DB'):
            from ai_rate_limit import ProjectRateLimiter
            self.limiter = ProjectRateLimiter(os.environ['AI_RATE_DB'],os.environ.get('AI_RATE_SCOPE','default-gemini-project'),
                int(os.environ.get('AI_SAFE_RPM',4)),int(os.environ.get('AI_LITE_SAFE_RPM',12)),int(os.environ.get('AI_SAFE_TPM',200000)))

    def progress(self, **values):
        if not self.progress_path: return
        temporary = None
        try:
            target=Path(self.progress_path); target.parent.mkdir(parents=True,exist_ok=True)
            values.update(stage=getattr(self.local,'stage','ai'),completed_calls=self.completed_calls,reused_calls=self.cache_hits)
            with tempfile.NamedTemporaryFile(mode='w',encoding='utf-8',dir=target.parent,delete=False) as f:
                temporary=f.name; json.dump(values,f,ensure_ascii=False)
            os.replace(temporary,target)
        except OSError: pass
        finally:
            if temporary and os.path.exists(temporary): os.unlink(temporary)

    def before_http(self, model, tokens, deadline=None):
        if not self.limiter: return
        while True:
            wait, detail = self.limiter.reserve(model,tokens)
            if detail and detail.get('code') == 'input_too_large':
                raise ProcessingError('input_too_large',model=model)
            if wait <= 0:
                self.progress(model=model,waiting_seconds=0); return
            self.progress(model=model,waiting_seconds=math.ceil(wait))
            allowance = min(self.remaining(60), deadline-time.monotonic() if deadline else 60)
            if wait >= allowance or detail.get('quota') == 'day':
                raise ProcessingError('rate_limit',**{k:v for k,v in detail.items() if k!='code'})
            time.sleep(min(wait,1))

    def defer(self, model, detail):
        if self.limiter: self.limiter.defer(model,detail)


    def remaining(self, timeout):
        left = min(timeout, self.deadline-time.monotonic()) if self.deadline else timeout
        if left < 1:
            raise ProcessingError('budget_exhausted')
        return left

    def call(self, call_api, prompt, api_key, model=None, timeout=60, validator=None, stage='ai'):
        self.local.stage = stage
        self.progress(waiting_seconds=0)
        identity = json.dumps(['document-ai-v1', self.namespace, model, prompt,
                               hashlib.sha256(str(api_key).encode()).hexdigest()], ensure_ascii=False)
        key = hashlib.sha256(identity.encode()).hexdigest()
        target = self.cache_dir / (key+'.json') if self.cache_dir else None
        if target:
            try:
                if time.time()-target.stat().st_mtime < 3*86400:
                    saved = json.loads(target.read_text(encoding='utf-8'))
                    value, used = saved['response'], saved['model']
                    if validator:
                        validator(value)
                    with self.lock:
                        self.cache_hits += 1
                    self.progress(waiting_seconds=0)
                    return value, used
            except (OSError, ValueError, KeyError, TypeError):
                pass
        for attempt in range(2):
            try:
                if not self.slots.acquire(timeout=self.remaining(timeout)):
                    raise ProcessingError('budget_exhausted')
                try:
                    value, used = call_api(prompt, api_key, model=model, timeout=self.remaining(timeout))
                    if validator:
                        validator(value)
                finally:
                    self.slots.release()
                if target:
                    temporary = None
                    try:
                        target.parent.mkdir(parents=True, exist_ok=True)
                        with tempfile.NamedTemporaryFile(mode='w', encoding='utf-8', dir=target.parent, delete=False) as file:
                            temporary = file.name
                            json.dump({'response': value, 'model': used}, file, ensure_ascii=False)
                        os.replace(temporary, target)
                    except OSError:
                        # Checkpoint failure must not discard an otherwise valid result.
                        self.checkpoint_failed = True
                    finally:
                        if temporary and os.path.exists(temporary):
                            try:
                                os.unlink(temporary)
                            except OSError:
                                pass
                self.completed_calls += 1
                self.progress(waiting_seconds=0)
                return value, used
            except Exception as error:
                detail = error_detail(error, stage)
                with self.lock:
                    self.errors.append(detail)
                if detail['code'] in ('authentication','rate_limit','budget_exhausted','invalid_request','input_too_large') or attempt == 1:
                    raise ProcessingError(detail['code'],**{k:v for k,v in detail.items() if k in ('quota','retry_after','model','http_status')}) from None
        raise ProcessingError('service_unavailable')


_session = CallSession()


def current_session():
    return _session


def configure_session(cache_dir=None, budget=None, namespace=""):
    global _session
    _session = CallSession(cache_dir, budget, namespace)
    return _session


def recovered_call(call_api, prompt, api_key, model=None, timeout=60, validator=None, stage='ai'):
    return _session.call(call_api, prompt, api_key, model, timeout, validator, stage)
