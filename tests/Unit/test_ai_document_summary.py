import copy
import json
import pathlib
import sys
import tempfile
import unittest
import os
import time

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parents[2] / 'app' / 'Services'))
from ai_document_summary import run_pipeline, select_sources, audit_map, completed_facts
from ai_processing import configure_session


class DocumentSummaryTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(dir=pathlib.Path(__file__).parent)
        self.path = pathlib.Path(self.temp.name) / 'source.bin'
        self.path.write_bytes(b'Synthetic extraction fixture, not a real student PDF')
        self.work = 'Nhóm đã khảo sát quy trình hiện tại qua phỏng vấn ba giảng viên.'
        self.proposal = 'Nhóm đề xuất sử dụng Transformer cho phân loại văn bản tiếng Việt.'
        self.result = 'Kết quả thử nghiệm ghi nhận macro F1 bằng 0.84 trên tập kiểm thử.'
        self.pages = [{'page':1,'text':'Chương 1: TỔNG QUAN\nNội dung mở đầu chương có nguồn trực tiếp.\n1.1. Khảo sát\n'+self.work},
                      {'page':2,'text':'Chương 2: PHƯƠNG PHÁP\n2.1. Mô hình đề xuất\n'+self.proposal+'\n2.2. Kết quả\n'+self.result}]
        self.calls = []

    def tearDown(self):
        configure_session()
        self.temp.cleanup()

    def api(self, prompt, *args, **kwargs):
        self.calls.append(prompt)
        if prompt.startswith('MAP CẤU TRÚC'):
            items = []
            for text, kind in [(self.work,'reported_work'), (self.proposal,'proposal'), (self.result,'reported_result')]:
                if text in prompt:
                    page = 1 if text == self.work else 2
                    items.append({'topic':'Chủ đề', 'kind':kind,'content':text,'evidence':text,'source_pages':[page]})
            return {'items':items}, 'test'
        if prompt.startswith('KIỂM CHỨNG Ý'):
            facts = json.loads(prompt.split('Ý CẦN KIỂM: ')[1].split('\nNGỮ CẢNH GỐC:')[0])
            return {'decisions':[{'id':f['source_ids'][0], 'supported':True,
                'completed':f['kind'] in ('reported_work','reported_result'), 'reason':'Đủ căn cứ nguyên văn.'} for f in facts]}, 'test'
        if prompt.startswith('REDUCE CẤU TRÚC'):
            facts = json.loads(prompt.split('DỮ LIỆU: ')[1])
            return {'source_ids':[f['source_ids'][0] for f in facts[:6]]}, 'test'
        if prompt.startswith('KIỂM TRA MÂU THUẪN'):
            facts = json.loads(prompt.split('ỨNG VIÊN: ')[1].split('\nPHẦN Ý')[0])
            return {'decisions':[{'id':f['id'],'consistent':True,'reason':'Không có mâu thuẫn.'} for f in facts]}, 'test'
        raise AssertionError(prompt[:60])

    def run_fixture(self, api=None, **kwargs):
        return run_pipeline(str(self.path), 'test-key', 'test', {},
            lambda path: (self.pages, len(self.pages), {'valid_pages':len(self.pages)}),
            api=api or self.api, identity={'document_id':'D','document_version_id':'V','group_id':'G'}, **kwargs)['data']

    def test_hierarchy_preserves_every_section_and_provenance(self):
        data = self.run_fixture()
        self.assertEqual(6, data['schema_version'])
        self.assertEqual(['1','2'], [n['number'] for n in data['chapters']])
        self.assertEqual(['2.1','2.2'], [n['number'] for n in data['chapters'][1]['sections']])
        self.assertEqual(self.proposal, data['chapters'][1]['sections'][0]['items'][0]['content'])
        self.assertEqual(2, len(data['completed_work']))
        self.assertTrue(all(f['relevance_verified'] and f['completion_verified'] for f in data['completed_work']))
        self.assertNotIn(self.proposal, [f['content'] for f in data['completed_work']])
        for field in ('difficulties','next_steps','completion_percentage','pending_items'):
            self.assertNotIn(field, data)
        self.assertTrue(data['analysis']['text_complete'])
        self.assertTrue(data['analysis']['source_coverage']['source_offsets_verified'])
        self.assertEqual('V', data['document_version_id'])

    def test_resume_reuses_validated_map_audit_and_reduce(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='V6')
        self.run_fixture()
        self.assertGreater(len(self.calls), 0)
        self.calls.clear()
        configure_session(cache, namespace='V6')
        self.run_fixture()
        self.assertEqual([], self.calls)

    def test_canonical_summary_remains_identical_after_expiry_and_new_submission_binding(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='V6', immutable=True)
        first = self.run_fixture()
        for path in cache.glob('*.json'):
            os.utime(path, (time.time()-10*86400, time.time()-10*86400))
        self.calls.clear()
        configure_session(cache, namespace='V6', immutable=True)
        second = run_pipeline(str(self.path), 'another-valid-account-key', 'test', {},
            lambda path: (_ for _ in ()).throw(AssertionError('Canonical source should not be re-extracted')),
            api=self.api, identity={'document_id':'NEW_D','document_version_id':'NEW_V','group_id':'NEW_G'})['data']
        self.assertEqual([], self.calls)
        for field in ('overview_summary','chapters','completed_work','citations'):
            self.assertEqual(first[field], second[field])
        self.assertEqual('NEW_V', second['document_version_id'])
        self.assertEqual('NEW_G', second['source_identity']['group_id'])
        self.assertTrue(second['analysis']['canonical_reused'])

    def test_changed_chunk_configuration_creates_a_new_canonical_snapshot(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='V6', immutable=True)
        self.run_fixture()
        self.run_fixture(settings={'target_tokens':2200})
        self.assertEqual(2, len(list(cache.glob('canonical-summary-*.json'))))

    def test_changed_pdf_bytes_cannot_receive_previous_source_snapshot(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='source-a', immutable=True)
        first = self.run_fixture()
        self.path.write_bytes(b'Changed synthetic source version')
        self.calls.clear()
        configure_session(cache, namespace='source-b', immutable=True)
        second = self.run_fixture()
        self.assertNotEqual(first['source_identity']['source_hash'], second['source_identity']['source_hash'])
        self.assertGreater(len(self.calls),0)
        self.assertFalse(second['analysis']['canonical_reused'])

    def test_source_changed_during_analysis_cannot_poison_canonical_snapshot(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='source-race', immutable=True)
        def api(*args, **kwargs):
            self.path.write_bytes(b'Changed while API was processing')
            return self.api(*args, **kwargs)
        with self.assertRaisesRegex(ValueError, 'source_changed_during_analysis'):
            self.run_fixture(api)
        self.assertEqual([], list(cache.glob('canonical-summary-*.json')))

    def test_empty_map_on_real_prose_is_partial_and_not_frozen(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='empty-map', immutable=True)
        data = self.run_fixture(lambda *args, **kwargs: ({'items':[]},'test'))
        self.assertFalse(data['analysis']['text_complete'])
        self.assertTrue(data['analysis']['errors'])
        self.assertEqual([], list(cache.glob('canonical-summary-*.json')))

    def test_failed_partial_analysis_does_not_freeze_an_empty_result(self):
        cache = pathlib.Path(self.temp.name) / 'cache'
        configure_session(cache, namespace='V6', immutable=True)
        def failed(*args, **kwargs): raise TimeoutError('timed out')
        partial = self.run_fixture(failed)
        self.assertFalse(partial['analysis']['text_complete'])
        self.assertEqual([], list(cache.glob('canonical-summary-*.json')))
        final = self.run_fixture()
        self.assertTrue(final['analysis']['text_complete'])
        self.assertEqual(1, len(list(cache.glob('canonical-summary-*.json'))))

    def test_map_without_independent_audit_never_publishes_claim(self):
        def api(prompt, *args, **kwargs):
            if prompt.startswith('KIỂM CHỨNG Ý'): raise TimeoutError('timed out')
            return self.api(prompt,*args,**kwargs)
        data = self.run_fixture(api)
        self.assertEqual([], data['completed_work'])
        self.assertEqual([], data['document_content'])
        self.assertFalse(data['analysis']['text_complete'])
        self.assertTrue(data['analysis']['errors'])

    def test_unsupported_map_claim_is_excluded_even_when_quote_matches(self):
        def api(prompt, *args, **kwargs):
            response, model = self.api(prompt, *args, **kwargs)
            if prompt.startswith('KIỂM CHỨNG Ý'):
                for d in response['decisions']:
                    d.update(supported=False, completed=False, reason='Câu nguồn chỉ là mục tiêu.')
            return response, model
        data = self.run_fixture(api)
        self.assertEqual([], data['completed_work'])
        self.assertEqual([], data['document_content'])
        self.assertTrue(data['validation']['excluded_claims'])

    def test_cross_section_contradictions_exclude_completed_work_only(self):
        def api(prompt, *args, **kwargs):
            response, model = self.api(prompt, *args, **kwargs)
            if prompt.startswith('KIỂM TRA MÂU THUẪN'):
                for d in response['decisions']:
                    d.update(consistent=False, reason='Mục khác mô tả kết quả trái ngược chưa giải thích.')
            return response, model
        data = self.run_fixture(api)
        self.assertEqual([], data['completed_work'])
        self.assertTrue(data['document_content'])
        self.assertTrue(data['validation']['excluded_claims'])
        self.assertNotIn('unfinished_work', data)

    def test_reduce_failure_keeps_verified_section_facts_and_partial_status(self):
        def api(prompt, *args, **kwargs):
            if prompt.startswith('REDUCE CẤU TRÚC'): raise TimeoutError('timed out')
            return self.api(prompt,*args,**kwargs)
        data = self.run_fixture(api)
        self.assertTrue(data['document_content'])
        self.assertFalse(data['analysis']['reduce_completed'])
        self.assertEqual('partial', data['analysis_status'])
        self.assertEqual(2, len(data['chapters'][1]['sections']))

    def test_unknown_ids_and_incomplete_audits_are_rejected(self):
        fact = {'source_ids':['F1_1'],'kind':'method','content':'X','evidence':'Original','source_pages':[1]}
        with self.assertRaises(ValueError): select_sources({'source_ids':['UNKNOWN']},[fact])
        with self.assertRaises(ValueError): audit_map({'decisions':[]},[fact])

    def test_all_failed_or_no_text_never_claim_full_coverage(self):
        self.pages = [{'page':1,'text':'','visual_unread':True,'scan_unread':True}]
        data = self.run_fixture()
        self.assertEqual('partial', data['analysis']['status'])
        self.assertEqual(0, data['analysis']['analyzed_chunks'])
        self.assertEqual([], data['completed_work'])
        self.assertEqual([1], data['analysis']['visual_unread_pages'])

    def test_literal_fallback_is_labelled_and_not_completed_work(self):
        data = self.run_fixture(heuristic=True)
        self.assertFalse(data['analysis']['text_complete'])
        self.assertEqual([], data['completed_work'])
        self.assertTrue(data['document_content'])
        self.assertFalse(data['document_content'][0]['relevance_verified'])


if __name__ == '__main__':
    unittest.main()
