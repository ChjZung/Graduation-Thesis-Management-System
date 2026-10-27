import concurrent.futures
import json
import os
from pathlib import Path
import sys
import tempfile
import unittest
from unittest.mock import patch

sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services'))
from ai_rate_limit import ProjectRateLimiter
from ai_gemini_transport import quota_detail
from ai_processing import CallSession, ProcessingError, configure_session, error_detail
from ai_summarizer import call_gemini_api


class QueueLimitTests(unittest.TestCase):
    def tearDown(self): configure_session()

    def test_model_token_preflight_is_cached_and_provider_usage_is_recorded(self):
        class Response:
            def __init__(self, body):
                self.status_code=200; self.headers={}; self.text=json.dumps(body); self.body=body
            def json(self): return self.body
        counted=Response({'totalTokens':30})
        generated=Response({'candidates':[{'content':{'parts':[{'text':'{"ok":true}'}]}}],
                            'usageMetadata':{'promptTokenCount':30,'candidatesTokenCount':4,'totalTokenCount':34}})
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            session=configure_session(folder)
            with patch.dict(os.environ,{'AI_EXACT_TOKEN_COUNT':'1','AI_MAX_REQUEST_TOKENS':'12000'}), \
                 patch('ai_summarizer.requests.post',side_effect=[counted,generated,generated]) as post:
                self.assertEqual({'ok':True},call_gemini_api('same prompt','SECRET')[0])
                self.assertEqual({'ok':True},call_gemini_api('same prompt','SECRET')[0])
            self.assertEqual(3,post.call_count)
            self.assertTrue(post.call_args_list[0].args[0].endswith(':countTokens'))
            self.assertEqual(60,session.token_usage['prompt_tokens'])

    def test_token_preflight_rejects_large_input_before_generation(self):
        response=type('Response',(),{'status_code':200,'headers':{},'json':lambda _: {'totalTokens':12001}})()
        with patch.dict(os.environ,{'AI_EXACT_TOKEN_COUNT':'1','AI_MAX_REQUEST_TOKENS':'12000'}), \
             patch('ai_summarizer.requests.post',return_value=response) as post:
            with self.assertRaises(ProcessingError) as caught: call_gemini_api('prompt','SECRET')
        self.assertEqual('input_too_large',caught.exception.code)
        self.assertEqual(1,post.call_count)

    def test_workers_share_atomic_project_model_reservations(self):
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            a=ProjectRateLimiter(Path(folder)/'limits.sqlite','project',rpm=4)
            b=ProjectRateLimiter(Path(folder)/'limits.sqlite','project',rpm=4)
            with concurrent.futures.ThreadPoolExecutor(2) as pool:
                values=list(pool.map(lambda limiter:limiter.reserve('flash',100,1000),[a,b]))
            self.assertEqual(1,sum(delay==0 for delay,_ in values))
            self.assertAlmostEqual(15,max(delay for delay,_ in values))
            self.assertEqual((0,None),a.reserve('flash',100,1061))

    def test_tpm_is_shared_and_models_have_independent_limits(self):
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            limiter=ProjectRateLimiter(Path(folder)/'limits.sqlite','project',rpm=60,tpm=100)
            self.assertEqual((0,None),limiter.reserve('flash',70,1000))
            self.assertEqual(58,limiter.reserve('flash',40,1002)[0])
            self.assertEqual((0,None),limiter.reserve('lite',40,1002))
            self.assertEqual('input_too_large',limiter.reserve('flash',101,1002)[1]['code'])

    def test_cooldown_survives_new_worker_and_is_not_key_scoped(self):
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            path=Path(folder)/'limits.sqlite'
            a=ProjectRateLimiter(path,'project')
            a.defer('flash',{'quota':'day','retry_after':500,'model':'flash'},1000)
            b=ProjectRateLimiter(path,'project')
            wait,detail=b.reserve('flash',1,1100)
            self.assertEqual(400,wait)
            self.assertEqual('day',detail['quota'])
            self.assertEqual((0,None),ProjectRateLimiter(path,'other-project').reserve('flash',1,1100))

    def test_lite_does_not_escalate_back_to_flash_when_service_is_busy(self):
        response=type('Response',(),{'status_code':503,'text':'{"error":{"status":"UNAVAILABLE"}}','headers':{}})()
        with patch('ai_summarizer.requests.post',return_value=response) as post:
            with self.assertRaises(ProcessingError): call_gemini_api('prompt','SECRET')
        self.assertEqual(1,post.call_count)
        self.assertIn('/gemini-3.5-flash-lite:',post.call_args.args[0])

    def test_quota_parser_uses_metadata_not_resource_exhausted_guess(self):
        daily={'error':{'details':[{'violations':[{'quotaId':'GenerateRequestsPerDayPerProjectPerModel-FreeTier'}]},{'retryDelay':'60s'}]}}
        minute={'error':{'details':[{'violations':[{'quotaId':'GenerateRequestsPerMinutePerProjectPerModel-FreeTier'}]},{'retryDelay':'22.4s'}]}}
        self.assertEqual('day',quota_detail(daily,{},'flash')['quota'])
        result=quota_detail(minute,{'Retry-After':'30'},'flash')
        self.assertEqual('minute',result['quota'])
        self.assertEqual(30,result['retry_after'])
        self.assertEqual('unknown',quota_detail({'error':{'status':'RESOURCE_EXHAUSTED'}},{},'flash')['quota'])

    def test_429_is_one_request_without_fallback_or_nested_retry(self):
        body={'error':{'details':[{'retryDelay':'20s'},{'violations':[{'quotaId':'RequestsPerMinute'}]}]}}
        response=type('Response',(),{'status_code':429,'text':json.dumps(body),'headers':{}})()
        with patch('ai_summarizer.requests.post',return_value=response) as post:
            with self.assertRaises(ProcessingError) as caught:
                CallSession().call(call_gemini_api,'prompt','SECRET',model='gemini-3.5-flash',stage='citation_audit')
        self.assertEqual(1,post.call_count)
        self.assertEqual('minute',caught.exception.details['quota'])
        self.assertEqual(20,caught.exception.details['retry_after'])
        self.assertNotIn('SECRET',json.dumps(error_detail(caught.exception)))

    def test_authentication_does_not_retry_and_error_hides_body(self):
        response=type('Response',(),{'status_code':403,'text':'{"error":{"message":"SECRET"}}','headers':{}})()
        with patch('ai_summarizer.requests.post',return_value=response) as post:
            with self.assertRaises(ProcessingError) as caught:
                CallSession().call(call_gemini_api,'prompt','SECRET',model='gemini-3.5-flash')
        self.assertEqual(1,post.call_count)
        self.assertEqual('authentication',caught.exception.code)
        self.assertNotIn('SECRET',str(caught.exception))

    def test_wait_exceeding_call_budget_returns_retry_instead_of_sending(self):
        session=CallSession()
        session.limiter=type('Limiter',(),{'reserve':lambda *_:(100,{'quota':'minute','retry_after':100,'model':'flash'})})()
        with patch('ai_processing.time.sleep') as sleep:
            with self.assertRaises(ProcessingError) as caught: session.before_http('flash',1)
        sleep.assert_not_called()
        self.assertEqual(100,caught.exception.details['retry_after'])

    def test_checkpoints_are_bound_to_document_identity(self):
        calls=[]
        def api(*args,**kwargs): calls.append(1); return {'ok':True},'flash'
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            CallSession(folder,namespace='file-a-v3').call(api,'same text','key')
            resumed=CallSession(folder,namespace='file-a-v3'); resumed.call(api,'same text','key')
            CallSession(folder,namespace='file-b-v3').call(api,'same text','key')
            self.assertEqual(2,len(calls))
            self.assertEqual(1,resumed.cache_hits)


if __name__=='__main__': unittest.main()
