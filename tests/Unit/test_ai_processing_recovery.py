import json
import sys
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[2]/'app/Services'))
from ai_processing import CallSession, ProcessingError, configure_session, error_detail
from ai_summarizer import run_map_reduce_pipeline, verify_document_content, call_gemini_api
from ai_requirement_review import review_requirements


class RecoveryTests(unittest.TestCase):
    def tearDown(self):
        configure_session()

    def test_valid_success_is_reused_but_failed_parts_are_retried(self):
        calls=[]
        def api(prompt, key, **kwargs):
            calls.append(prompt)
            if prompt=='failed':
                raise TimeoutError('timed out')
            return {'items':[]}, 'test'
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            session=CallSession(folder)
            session.call(api,'good','secret')
            with self.assertRaises(ProcessingError): session.call(api,'failed','secret')
            session=CallSession(folder)
            session.call(api,'good','secret')
            with self.assertRaises(ProcessingError): session.call(api,'failed','secret')
            self.assertEqual(['good','failed','failed','failed','failed'],calls)
            self.assertEqual(1,session.cache_hits)
            self.assertEqual(1,len(list(Path(folder).glob('*.json'))))

    def test_http_connect_and_read_share_the_time_allowance(self):
        response=type('Response',(),{'status_code':200,'text':json.dumps({'candidates':[{'content':{'parts':[{'text':'{"ok":true}'}]}}]})})()
        with patch('ai_summarizer.requests.post',return_value=response) as post:
            self.assertEqual({'ok':True},call_gemini_api('prompt','SECRET',model='test',timeout=60)[0])
        connect,read=post.call_args.kwargs['timeout']
        self.assertLessEqual(connect+read,60)
        self.assertGreater(read,12)
        self.assertNotIn('SECRET',post.call_args.args[0])

    def test_gemini_38_uses_json_and_supported_thinking_without_sampling_parameters(self):
        response=type('Response',(),{'status_code':200,'text':json.dumps({'candidates':[{'content':{'parts':[{'text':'hidden','thought':True},{'text':'{"ok":true}'}]}}]})})()
        with patch('ai_summarizer.requests.post',return_value=response) as post:
            self.assertEqual({'ok':True},call_gemini_api('prompt','SECRET',model='gemini-3.8-flash')[0])
        self.assertIn('/gemini-3.8-flash:generateContent',post.call_args.args[0])
        config=post.call_args.kwargs['json']['generationConfig']
        self.assertEqual('application/json',config['responseMimeType'])
        self.assertEqual({'thinkingLevel':'low'},config['thinkingConfig'])
        self.assertFalse({'temperature','topP','topK','candidateCount'} & set(config))

    def test_busy_35_falls_back_to_35_lite_without_upgrading(self):
        busy=type('Response',(),{'status_code':503,'text':'{"error":{"status":"UNAVAILABLE"}}'})()
        good=type('Response',(),{'status_code':200,'text':json.dumps({'candidates':[{'content':{'parts':[{'text':'{"ok":true}'}]}}]})})()
        with patch('ai_summarizer.requests.post',side_effect=[busy,good]) as post:
            result,used=call_gemini_api('prompt','SECRET',model='gemini-3.5-flash')
        self.assertTrue(result['ok'])
        self.assertEqual('gemini-3.5-flash-lite',used)
        self.assertEqual(2,post.call_count)
        self.assertIn('/gemini-3.5-flash:',post.call_args_list[0].args[0])
        self.assertIn('/gemini-3.5-flash-lite:',post.call_args_list[1].args[0])

    def test_default_35_lite_does_not_receive_parameters_added_for_38(self):
        response=type('Response',(),{'status_code':200,'text':json.dumps({'candidates':[{'content':{'parts':[{'text':'{"ok":true}'}]}}]})})()
        with patch('ai_summarizer.requests.post',return_value=response) as post:
            _,used=call_gemini_api('prompt','SECRET')
        self.assertEqual('gemini-3.5-flash-lite',used)
        config=post.call_args.kwargs['json']['generationConfig']
        self.assertEqual('application/json',config['responseMimeType'])
        self.assertNotIn('thinkingConfig',config)

    def test_cache_changes_with_source_context_model_and_account(self):
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            session=CallSession(folder)
            with patch('builtins.print'):
                for prompt,key,model in [('document v1','key1','m1'),('document v2','key1','m1'),('document v1','key2','m1'),('document v1','key1','m2')]:
                    session.call(lambda *a,**kw: ({'items':[]},kw['model']),prompt,key,model=model)
            self.assertEqual(4,len(list(Path(folder).glob('*.json'))))
            self.assertFalse(any('key1' in p.read_text() or 'key2' in p.read_text() for p in Path(folder).glob('*.json')))

    def test_transient_failure_recovers_and_malformed_success_is_not_cached(self):
        responses=iter([TimeoutError('timed out'),{'items':[{'id':1}]}])
        def api(*args,**kwargs):
            item=next(responses)
            if isinstance(item,Exception): raise item
            return item,'test'
        self.assertEqual({'items':[{'id':1}]},CallSession().call(api,'prompt','key')[0])
        def validate(value):
            if not value.get('items'): raise ValueError('invalid_response')
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            with self.assertRaises(ProcessingError):
                CallSession(folder).call(lambda *a,**k: ({'items':[]},'test'),'bad','key',validator=validate)
            self.assertEqual([],list(Path(folder).glob('*')))

    def test_quota_is_not_hammered_and_diagnostics_hide_credentials(self):
        with patch('ai_processing.time.monotonic',return_value=10):
            session=CallSession(budget=1)
        with self.assertRaises(ProcessingError):
            session.call(lambda *a,**k: ({},'test'),'expired','secret')
        with patch('builtins.print'):
            def quota(*args,**kwargs): raise RuntimeError('HTTP 429 https://bad/?key=SECRET')
            session=CallSession()
            with self.assertRaises(ProcessingError): session.call(quota,'prompt','SECRET')
            self.assertEqual(1,len(session.errors))
            self.assertNotIn('SECRET',json.dumps(session.errors))
            self.assertNotIn('SECRET',json.dumps(error_detail(ValueError('key=SECRET'))))

    def pipeline(self, api):
        texts=['The survey found fragmented spreadsheets and email communication in the current thesis management workflow.',
               'The actors are students, lecturers and academic staff.']
        pages=[{'page':i+9,'text':text,'headings':[],'visual_unread':False} for i,text in enumerate(texts)]
        meta={'valid_pages':2,'total_chars':sum(map(len,texts)),'total_words':20,'extraction_time':0}
        chunks=[{'start_page':i+9,'end_page':i+9,'text':text} for i,text in enumerate(texts)]
        with patch('ai_summarizer.extract_pdf_pages',return_value=(pages,2,meta)), patch('ai_summarizer.build_structured_chunks',return_value=(chunks,0)), patch('ai_summarizer.call_gemini_api',side_effect=api):
            return run_map_reduce_pipeline('report.pdf',api_key='test')

    def payload(self):
        return {'document_content':[{'content':'Báo cáo trình bày hiện trạng quản lý phân tán.',
                  'evidence':'The survey found fragmented spreadsheets and email communication in the current thesis management workflow.','source_pages':[9]}],
                'completed_work':[],'difficulties':[],'next_steps':[]}

    def test_partial_document_cannot_claim_full_coverage_or_absent_content(self):
        def api(prompt,*args,**kwargs):
            if 'Nguồn từ 10 đến 10:' in prompt: raise TimeoutError('timed out')
            return self.payload(),'test'
        data=self.pipeline(api)['data']
        self.assertEqual(1,data['analysis']['analyzed_chunks'])
        self.assertFalse(data['analysis']['text_complete'])
        self.assertIn('1/2',data['analysis']['note'])
        self.assertTrue(data['document_content'][0]['verified'])
        self.assertEqual('Báo cáo trình bày hiện trạng quản lý phân tán.',data['overall_summary'])
        self.assertEqual([],data['completed_work'])

    def test_cover_and_heading_only_evidence_cannot_describe_detailed_content(self):
        title='3.3. MÔ HÌNH HÓA CHỨC NĂNG VÀ ĐẶC TẢ CÁC QUY TRÌNH NGHIỆP VỤ CỦA HỆ THỐNG'
        item={'content':'Đã có các đặc tả đầy đủ.','evidence':title,'source_pages':[58]}
        self.assertEqual([],verify_document_content([item],{58:title}))
        cover={'content':'Mô tả chi tiết hệ thống.','evidence':'The survey found fragmented spreadsheets and email communication in the current thesis management workflow.','source_pages':[1]}
        self.assertEqual([],verify_document_content([cover],{1:cover['evidence']}))
        self.assertTrue(verify_document_content([cover],{1:cover['evidence']},'docx'))

    def test_merge_failure_preserves_content_from_successful_parts(self):
        def api(prompt,*args,**kwargs):
            if 'Đã xử lý' in prompt: raise TimeoutError('timed out')
            return self.payload(),'test'
        data=self.pipeline(api)['data']
        self.assertEqual(2,data['analysis']['analyzed_chunks'])
        self.assertFalse(data['analysis']['reduce_completed'])
        self.assertFalse(data['analysis']['text_complete'])
        self.assertTrue(data['document_content'])

    def test_review_failures_keep_candidates_with_safe_error_reason(self):
        def api(*args,**kwargs): raise TimeoutError('timed out with key=SECRET')
        result=review_requirements({'submission_requirements':'1. Describe the survey.'},
            [{'page':1,'text':'The survey found fragmented spreadsheets and email communication in the current thesis management workflow.'}], 'key','model',api)
        self.assertEqual('service_error',result['status'])
        self.assertTrue(result['errors'])
        self.assertEqual('timeout',result['errors'][0]['code'])
        self.assertNotIn('SECRET',json.dumps(result))
        self.assertNotEqual('not_found',result['items'][0]['status'])


if __name__=='__main__': unittest.main()
