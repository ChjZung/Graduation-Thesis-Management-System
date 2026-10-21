import copy
import json
import sys
import tempfile
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app/Services'))
from ai_flexible_summary import ground_facts, resolve_sections, reduce_facts, run_pipeline
from ai_processing import configure_session
from ai_requirement_review import context_hash
from pdf_document_analysis import exact_quote


class FlexibleSummaryTests(unittest.TestCase):
    def tearDown(self):
        configure_session()

    def fixture(self):
        texts = [
            'Nhóm đã khảo sát quy trình quản lý hiện tại qua phỏng vấn ba giảng viên.',
            'Nhóm đề xuất sử dụng mô hình Transformer cho bài toán phân loại văn bản.',
            'Dự kiến thực nghiệm với bộ dữ liệu tiếng Việt và so sánh với mô hình cơ sở.',
            'Kết quả thử nghiệm ghi nhận macro F1 là 0.84 trên tập kiểm thử độc lập.',
        ]
        topics = ['Khảo sát hiện trạng', 'Mô hình đề xuất', 'Thiết kế thực nghiệm', 'Kết quả thực nghiệm']
        kinds = ['reported_work', 'proposal', 'future_work', 'reported_result']
        pages = [{'page': i, 'text': text} for i, text in enumerate(texts, 1)]
        chunks = [{'start_page': i, 'end_page': i, 'text': text, 'pages': [i]} for i, text in enumerate(texts, 1)]
        payloads = [{'items': [{'topic': topic, 'kind': kind, 'content': text, 'evidence': text, 'source_pages': [i]}]}
                    for i, (text, topic, kind) in enumerate(zip(texts, topics, kinds), 1)]
        return pages, chunks, payloads

    def pipeline(self, api, context):
        pages, chunks, _ = self.fixture()
        return run_pipeline('fixture.pdf', 'private-key', 'test-model', context,
            lambda path: (pages, len(pages), {'valid_pages': len(pages)}),
            lambda *args: (chunks, 0), api)['data']

    def selection(self, inputs, title='Nội dung trọng tâm', mode='focused', focus=None):
        ids = [f['source_ids'][0] for f in inputs]
        chosen = ids if focus is None else [key for key in ids if key in focus]
        extra = [key for key in ids if key not in chosen]
        return {'overview_source_ids': ids[:3], 'scope': {'mode': mode, 'note': 'Phạm vi theo yêu cầu hiện có.'},
                'summary_sections': [{'title': title, 'source_ids': chosen}] if chosen else [],
                'additional_sections': [{'title': 'Nội dung khác của tài liệu', 'source_ids': extra}] if extra else []}

    def api(self, prompt, *args, **kwargs):
        if 'MAP LINH HOẠT' in prompt:
            for index, chunk in enumerate(self.fixture()[1]):
                if f"Nguồn từ {chunk['start_page']} đến {chunk['end_page']}:" in prompt:
                    return self.fixture()[2][index], 'test-model'
            raise AssertionError('Unknown map chunk')
        inputs = json.loads(prompt.split('DỮ LIỆU: ', 1)[1])
        if 'Chỉ khảo sát hiện trạng' in prompt:
            return self.selection(inputs, 'Khảo sát theo yêu cầu', focus={'F1_1'}), 'test-model'
        return self.selection(inputs, 'Phương pháp và thực nghiệm', focus={'F2_1', 'F3_1', 'F4_1'}), 'test-model'

    def test_same_document_new_brief_reuses_map_but_refocuses_summary(self):
        calls = []
        def api(prompt, *args, **kwargs):
            calls.append(prompt)
            return self.api(prompt, *args, **kwargs)
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            configure_session(cache_dir=folder, namespace='v5-test')
            survey = self.pipeline(api, {'stage_name': 'Giai đoạn 99', 'submission_requirements': 'Chỉ khảo sát hiện trạng'})
            self.assertEqual(4, sum('MAP LINH HOẠT' in p for p in calls))
            calls.clear()
            configure_session(cache_dir=folder, namespace='v5-test')
            research = self.pipeline(api, {'stage_name': 'Giai đoạn 1', 'submission_requirements': 'Trình bày mô hình đề xuất và thực nghiệm'})
        self.assertFalse(any('MAP LINH HOẠT' in p for p in calls))
        self.assertEqual(1, len(calls))
        self.assertEqual('Khảo sát theo yêu cầu', survey['summary_sections'][0]['title'])
        self.assertEqual('Phương pháp và thực nghiệm', research['summary_sections'][0]['title'])
        self.assertNotEqual(survey['summary_scope']['context_hash'], research['summary_scope']['context_hash'])
        self.assertEqual(3, len(survey['additional_sections'][0]['items']))
        self.assertEqual(4, research['analysis']['reused_calls'])
        self.assertTrue(research['analysis']['text_complete'])
        self.assertIsNone(research['completion_percentage'])

    def test_reduce_preserves_proposal_future_work_and_result_as_distinct_facts(self):
        data = self.pipeline(self.api, {'submission_requirements': 'Mô hình và thực nghiệm'})
        items = data['summary_sections'][0]['items']
        self.assertEqual(['proposal', 'future_work', 'reported_result'], [i['kind'] for i in items])
        self.assertEqual(self.fixture()[0][2]['text'], items[1]['content'])
        self.assertEqual([3], items[1]['source_pages'])
        self.assertEqual('flexible_map_reduce', data['analysis']['method'])
        self.assertFalse({'Khó khăn gặp phải', 'Kế hoạch tiếp theo'} & {s['title'] for s in data['summary_sections']})

    def test_empty_brief_forces_document_fallback_even_if_model_says_focused(self):
        data = self.pipeline(self.api, {'stage_name': 'Giai đoạn 1'})
        self.assertEqual('document_fallback', data['summary_scope']['mode'])
        self.assertIn('Chưa có yêu cầu', data['summary_scope']['note'])
        self.assertEqual(context_hash({'stage_name': 'Giai đoạn 1'}), data['summary_scope']['context_hash'])

    def test_vague_or_conflicting_brief_can_return_explicit_fallback(self):
        for brief in ['Nộp tiến độ', 'Chỉ khảo sát; đồng thời không đưa nội dung khảo sát vào báo cáo']:
            def api(prompt, *args, **kwargs):
                if 'MAP LINH HOẠT' in prompt: return self.api(prompt, *args, **kwargs)
                inputs = json.loads(prompt.split('DỮ LIỆU: ', 1)[1])
                self.assertIn(brief, prompt)
                result = self.selection(inputs, 'Nội dung tài liệu', mode='document_fallback')
                result['scope']['note'] = 'Yêu cầu chưa đủ rõ hoặc mâu thuẫn; chưa suy ra tiêu chí đánh giá.'
                return result, 'test-model'
            data = self.pipeline(api, {'submission_requirements': brief})
            self.assertEqual('document_fallback', data['summary_scope']['mode'])
            self.assertIn('chưa', data['summary_scope']['note'])

    def test_sources_outside_chunk_or_wrong_pages_are_rejected(self):
        pages, chunks, payloads = self.fixture()
        by_page = {p['page']: p['text'] for p in pages}
        bad = copy.deepcopy(payloads[0])
        bad['items'][0]['source_pages'] = [2, True]
        with self.assertRaises(ValueError): ground_facts(bad, chunks[0], by_page, 1)
        with self.assertRaises(ValueError): ground_facts(payloads[1], chunks[0], by_page, 1)
        bad['items'][0]['source_pages'] = [1]
        bad['items'][0]['evidence'] += ' Kết quả này không có trong nguồn.'
        with self.assertRaises(ValueError): ground_facts(bad, chunks[0], by_page, 1)

    def test_reduce_cannot_invent_sources_or_rewrite_canonical_content(self):
        pages, chunks, payloads = self.fixture()
        facts = ground_facts(payloads[1], chunks[1], {p['page']: p['text'] for p in pages}, 2)
        value = self.selection(facts)
        value['summary_sections'][0]['content'] = 'Mô hình đã triển khai thành công'
        value['summary_sections'][0]['kind'] = 'reported_result'
        resolved = resolve_sections(value, facts, {'submission_requirements': 'Đề xuất mô hình'})
        self.assertEqual('proposal', resolved['summary_sections'][0]['items'][0]['kind'])
        self.assertEqual(facts[0]['content'], resolved['summary_sections'][0]['items'][0]['content'])
        value['summary_sections'][0]['source_ids'] = ['NONEXISTENT']
        with self.assertRaises(ValueError): resolve_sections(value, facts, {})

    def test_duplicate_sections_cannot_repeat_one_fact(self):
        pages, chunks, payloads = self.fixture()
        facts = ground_facts(payloads[0], chunks[0], {p['page']: p['text'] for p in pages}, 1)
        value = self.selection(facts)
        value['additional_sections'] = [{'title': 'Lặp lại', 'source_ids': ['F1_1']}]
        with self.assertRaises(ValueError): resolve_sections(value, facts, {})

    def test_no_relevant_content_may_keep_only_additional_sections(self):
        def api(prompt, *args, **kwargs):
            if 'MAP LINH HOẠT' in prompt: return self.api(prompt, *args, **kwargs)
            inputs = json.loads(prompt.split('DỮ LIỆU: ', 1)[1])
            return self.selection(inputs, focus=set()), 'test-model'
        data = self.pipeline(api, {'submission_requirements': 'Trình bày thiết kế phần cứng'})
        self.assertEqual([], data['summary_sections'])
        self.assertTrue(data['additional_sections'])
        self.assertTrue(data['analysis']['text_complete'])
        self.assertNotIn('requirements_review', data)  # Grouping must not decide met/not_found.

    def test_bad_reduce_keeps_verified_facts_and_partial_state(self):
        def api(prompt, *args, **kwargs):
            if 'MAP LINH HOẠT' in prompt: return self.api(prompt, *args, **kwargs)
            return {'summary_sections': [{'title': 'Bịa nguồn', 'source_ids': ['MISSING']}]}, 'test-model'
        data = self.pipeline(api, {'submission_requirements': 'Đánh giá thực nghiệm'})
        self.assertFalse(data['analysis']['reduce_completed'])
        self.assertFalse(data['analysis']['text_complete'])
        self.assertEqual('document_fallback', data['summary_scope']['mode'])
        self.assertEqual(4, sum(len(s['items']) for s in data['summary_sections']))
        self.assertTrue(all(i['verified'] for s in data['summary_sections'] for i in s['items']))

    def test_long_reduce_is_bounded_and_carries_brief_through_every_level(self):
        facts = [{'topic': f'Topic {i}', 'kind': 'method', 'content': 'x' * 400,
                  'evidence': 'e' * 400, 'source_pages': [i], 'source_ids': [f'F{i}_1'],
                  'source_chunk_ids': [i], 'verified': True} for i in range(1, 31)]
        calls = []
        def api(prompt, *args, **kwargs):
            self.assertIn('Chỉ khảo sát hiện trạng', prompt)
            inputs = json.loads(prompt.split('DỮ LIỆU: ', 1)[1])
            calls.append(len(inputs))
            return self.selection(inputs[:1]), 'test-model'
        result, done, metrics, errors, _ = reduce_facts(facts,
            {'submission_requirements': 'Chỉ khảo sát hiện trạng'}, api, 'test', 'test-model', token_budget=1700)
        self.assertTrue(done)
        self.assertGreater(metrics['reduce_levels'], 1)
        self.assertEqual([], errors)
        self.assertLess(len(calls), 30)
        self.assertTrue(result['summary_sections'])


if __name__ == '__main__':
    unittest.main()
