"""Single HTTP attempt per model. Recovery belongs to CallSession, not nested loops."""
import datetime
import email.utils
import json
import math
import time
import hashlib
import os
import urllib.error
import urllib.request

from ai_processing import ProcessingError, current_session


def count_prompt_tokens(prompt, api_key, model, requests_module, timeout):
    """Model-specific preflight, cached privately; no generated text or credentials persisted."""
    session = current_session()
    identity = hashlib.sha256((model + '|' + hashlib.sha256(api_key.encode()).hexdigest() + '|' + prompt).encode()).hexdigest()
    target = session.cache_dir / ('tokens-v6-' + identity + '.json') if session.cache_dir else None
    if target:
        try:
            if time.time() - target.stat().st_mtime < 3 * 86400:
                count = json.loads(target.read_text())['totalTokens']
                if type(count) is int and count >= 0: return count
        except (OSError, ValueError, KeyError): pass
    url = 'https://generativelanguage.googleapis.com/v1beta/models/' + model + ':countTokens'
    payload = {'contents':[{'parts':[{'text':prompt}]}]}
    headers = {'Content-Type':'application/json', 'x-goog-api-key':api_key}
    session.before_http(model, 0, time.monotonic() + timeout)
    try:
        if requests_module:
            response = requests_module.post(url, json=payload, headers=headers, timeout=max(1, timeout))
            status, body, response_headers = response.status_code, response.json(), getattr(response, 'headers', {})
        else:
            request = urllib.request.Request(url, data=json.dumps(payload).encode(), headers=headers)
            try:
                with urllib.request.urlopen(request, timeout=max(1, timeout)) as response:
                    status, body, response_headers = response.status, json.loads(response.read()), response.headers
            except urllib.error.HTTPError as error:
                status, body, response_headers = error.code, json.loads(error.read()), error.headers
    except Exception as error:
        raise ProcessingError('timeout' if 'timeout' in type(error).__name__.lower() else 'connection_error', model=model) from None
    if status == 429:
        detail = quota_detail(body, response_headers, model)
        session.defer(model, detail)
        raise ProcessingError('rate_limit', **detail)
    if status in (401, 403): raise ProcessingError('authentication', model=model)
    if status != 200: raise ProcessingError('model_unavailable' if status == 404 else 'service_unavailable', model=model, http_status=status)
    count = body.get('totalTokens')
    if type(count) is not int or count < 0: raise ProcessingError('invalid_response', model=model)
    if target:
        try:
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_text(json.dumps({'totalTokens':count}), encoding='utf-8')
        except OSError: session.checkpoint_failed = True
    return count


def quota_detail(body, headers, model):
    details = body.get('error',{}).get('details',[]) if isinstance(body,dict) else []
    ids, delays = [], []
    for detail in details:
        for violation in detail.get('violations',[]):
            ids.append(str(violation.get('quotaId',''))+' '+str(violation.get('quotaMetric','')))
        retry = detail.get('retryDelay')
        if isinstance(retry,str):
            try: delays.append(float(retry.rstrip('s')))
            except ValueError: pass
    retry = (headers or {}).get('Retry-After')
    if retry:
        try: delays.append(float(retry))
        except ValueError:
            try: delays.append(email.utils.parsedate_to_datetime(retry).timestamp()-time.time())
            except (ValueError,TypeError): pass
    joined = ' '.join(ids).lower().replace('_','')
    quota = 'day' if 'perday' in joined else ('minute' if 'perminute' in joined else 'unknown')
    if quota == 'day':
        # Use the provider's Pacific reset when timezone data is installed.
        try:
            from zoneinfo import ZoneInfo
            now = datetime.datetime.now(ZoneInfo('America/Los_Angeles'))
            reset = datetime.datetime.combine(now.date()+datetime.timedelta(days=1),datetime.time(),tzinfo=now.tzinfo)
            delays.append(reset.timestamp()-now.timestamp()+5)
        except Exception: delays.append(86400)
    valid_delays = [delay for delay in delays if math.isfinite(delay) and delay >= 0]
    return dict(quota=quota,retry_after=max(1,math.ceil(max(valid_delays,default=60))),model=model,http_status=429)


def generate_groq(prompt, key, model, deadline, requests_module, clean_json):
    session = current_session()
    provider_model = 'groq:' + model
    output_tokens = 2048
    # Conservative preflight for the default 8k-token/minute Groq budget.
    tokens = math.ceil(len(prompt.encode('utf-8')) / 3)
    session.before_http(provider_model, tokens + output_tokens, deadline)
    remaining = min(deadline - time.monotonic(), session.remaining(45))
    if remaining < 1:
        raise ProcessingError('timeout', model=provider_model)
    payload = {'model': model, 'messages': [{'role': 'user', 'content': prompt}],
               'response_format': {'type': 'json_object'}, 'max_completion_tokens': output_tokens}
    if model.startswith('openai/gpt-oss-'):
        payload['reasoning_effort'] = 'low'
    url = 'https://api.groq.com/openai/v1/chat/completions'
    headers = {'Authorization': 'Bearer ' + key, 'Content-Type': 'application/json'}
    try:
        if requests_module:
            connect = min(5, remaining / 4)
            response = requests_module.post(url, json=payload, headers=headers,
                                            timeout=(connect, max(.1, remaining - connect)))
            status, text, response_headers = response.status_code, response.text, response.headers
        else:
            request = urllib.request.Request(url, data=json.dumps(payload).encode(), headers=headers)
            try:
                with urllib.request.urlopen(request, timeout=remaining) as response:
                    status, text, response_headers = response.status, response.read().decode(), response.headers
            except urllib.error.HTTPError as error:
                status, text, response_headers = error.code, error.read().decode(), error.headers
    except Exception as error:
        code = 'timeout' if 'timeout' in type(error).__name__.lower() else 'connection_error'
        raise ProcessingError(code, model=provider_model) from None
    if status == 429:
        detail = quota_detail({}, response_headers, provider_model)
        session.defer(provider_model, detail)
        raise ProcessingError('rate_limit', **detail)
    if status != 200:
        code = {400: 'invalid_request', 401: 'authentication', 403: 'authentication',
                404: 'model_unavailable', 413: 'input_too_large'}.get(status, 'service_unavailable')
        detail = {}
        if status == 503 and response_headers.get('Retry-After'):
            detail['retry_after'] = quota_detail({}, response_headers, provider_model)['retry_after']
            session.defer(provider_model, {**detail, 'model': provider_model, 'http_status': status})
        raise ProcessingError(code, model=provider_model, http_status=status, **detail)
    try:
        body = json.loads(text)
        choice = body['choices'][0]
        if choice.get('finish_reason') != 'stop':
            raise ProcessingError('invalid_response', model=provider_model)
        value = json.loads(clean_json(choice['message']['content']))
    except (ValueError, KeyError, TypeError, IndexError):
        raise ProcessingError('invalid_json', model=provider_model) from None
    usage = body.get('usage') or {}
    session.record_usage({'promptTokenCount': usage.get('prompt_tokens'),
                          'candidatesTokenCount': usage.get('completion_tokens'),
                          'totalTokenCount': usage.get('total_tokens')})
    return value, provider_model


def generate(prompt, api_key, model, timeout, requests_module, clean_json, allow_groq=True):
    groq_key = os.environ.get('GROQ_API_KEY', '').strip()
    groq_model = os.environ.get('GROQ_MODEL', '').strip()
    deadline = time.monotonic() + timeout
    if allow_groq and groq_key and groq_model and model == 'groq:' + groq_model:
        return generate_groq(prompt, groq_key, groq_model, deadline, requests_module, clean_json)
    if not api_key: raise ProcessingError('authentication')
    fallback = os.environ.get('GEMINI_FALLBACK_MODEL', '').strip()
    candidates = list(dict.fromkeys(candidate for candidate in (model, fallback) if candidate))
    session = current_session()
    last = None
    for chosen in candidates:
        tokens = math.ceil(len(prompt.encode('utf-8')) / 3)
        if os.environ.get('AI_EXACT_TOKEN_COUNT') == '1':
            try:
                tokens = count_prompt_tokens(prompt, api_key, chosen, requests_module,
                    min(10, max(1, deadline - time.monotonic()), session.remaining(timeout)))
            except ProcessingError as error:
                if error.code == 'model_unavailable' or error.details.get('http_status') == 503:
                    last = error
                    continue
                raise
        if tokens > int(os.environ.get('AI_MAX_REQUEST_TOKENS', '32000')):
            raise ProcessingError('input_too_large', model=chosen)
        session.before_http(chosen,tokens + 8192,deadline)
        remaining = min(deadline-time.monotonic(),session.remaining(timeout))
        if remaining < 1: raise ProcessingError('timeout', model=chosen)
        generation = {'maxOutputTokens':8192,'responseMimeType':'application/json'}
        if chosen.startswith(('gemini-3.8','gemini-3.6')): generation['thinkingConfig']={'thinkingLevel':'low'}
        payload = {'contents':[{'parts':[{'text':prompt}]}],'generationConfig':generation}
        url = 'https://generativelanguage.googleapis.com/v1beta/models/'+chosen+':generateContent'
        headers = {'Content-Type':'application/json; charset=utf-8','x-goog-api-key':api_key}
        allowance = max(1,min(45,remaining))
        try:
            if requests_module:
                connect = min(5,allowance/4)
                response = requests_module.post(url,json=payload,headers=headers,timeout=(connect,max(.1,allowance-connect)))
                status, text, response_headers = response.status_code,response.text,getattr(response,'headers',{})
            else:
                req = urllib.request.Request(url,data=json.dumps(payload).encode(),headers=headers)
                try:
                    with urllib.request.urlopen(req,timeout=allowance) as response:
                        status,text,response_headers = response.status,response.read().decode(),response.headers
                except urllib.error.HTTPError as error:
                    status,text,response_headers = error.code,error.read().decode(),error.headers
        except Exception as error:
            name = type(error).__name__.lower()
            raise ProcessingError('timeout' if 'timeout' in name else 'connection_error',model=chosen) from None
        try: body=json.loads(text)
        except ValueError: body={}
        if status == 200:
            session.record_usage(body.get('usageMetadata'))
            candidate=(body.get('candidates') or [{}])[0]
            parts=candidate.get('content',{}).get('parts',[])
            raw=''.join(part.get('text','') for part in parts if not part.get('thought'))
            if not raw: raise ProcessingError('invalid_response',model=chosen,http_status=status)
            try: return json.loads(clean_json(raw)),chosen
            except ValueError: raise ProcessingError('invalid_json',model=chosen,http_status=status) from None
        if status == 429:
            detail=quota_detail(body,response_headers,chosen)
            session.defer(chosen,detail)
            # A quota error is a wait, never a reason to rotate models or credentials.
            raise ProcessingError('rate_limit',**detail)
        if status in (401,403): raise ProcessingError('authentication',http_status=status,model=chosen)
        if status == 400: raise ProcessingError('invalid_request',http_status=status,model=chosen)
        last=ProcessingError('model_unavailable' if status==404 else 'service_unavailable',http_status=status,model=chosen)
        if status not in (404,503): raise last
    if allow_groq and groq_key and groq_model and last and last.details.get('http_status') == 503:
        try:
            return generate_groq(prompt, groq_key, groq_model, deadline, requests_module, clean_json)
        except ProcessingError as error:
            if error.code != 'input_too_large':
                raise
    raise last or ProcessingError('service_unavailable')
