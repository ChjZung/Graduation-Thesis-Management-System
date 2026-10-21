import json
import sys
import unittest
import tempfile
from pathlib import Path
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app/Services'))
from ai_summary_hierarchy import (GROUPS, estimated_tokens, structured_chunks,
                                  ground_map, grounded_reduce, hierarchical_reduce)
from ai_processing import configure_session
from ai_requirement_review import review_requirements
from pdf_evidence_retrieval import retrieve_candidates


class HierarchyTests(unittest.TestCase):
    def tearDown(self):
        configure_session()

    def test_dense_paragraph_has_bounded_chunks_overlap_and_complete_source_spans(self):
        text = 'Sinh viên khảo sát hệ thống và mô tả nghiệp vụ. ' * 1800 + 'END_OF_SOURCE'
        pages = [{'page': 7, 'text': text, 'headings': []}]
        chunks, _ = structured_chunks(pages, 1800, 160, max_tokens=600)
        self.assertGreater(len(chunks), 10)
        covered = set()
        for chunk in chunks:
            self.assertLessEqual(chunk['char_count'], 1800)
            self.assertLessEqual(estimated_tokens(chunk['text']), 600)
            for span in chunk['source_spans']:
                self.assertEqual(span['text'], text[span['start']:span['end']])
                covered.update(range(span['start'], span['end']))
        self.assertEqual(set(range(len(text))), covered)
        self.assertIn('END_OF_SOURCE', chunks[-1]['text'])
        self.assertLess(chunks[1]['source_spans'][0]['start'], chunks[0]['source_spans'][-1]['end'])

    def test_chapter_boundaries_do_not_mix_sections(self):
        text = 'CHƯƠNG 1 TỔNG QUAN\nNội dung khảo sát.\nCHƯƠNG 2 THIẾT KẾ\nNội dung thiết kế.'
        chunks, _ = structured_chunks([{'page': 9, 'text': text,
            'headings': ['CHƯƠNG 1 TỔNG QUAN', 'CHƯƠNG 2 THIẾT KẾ']}])
        self.assertEqual(2, len(chunks))
        self.assertNotIn('Nội dung thiết kế', chunks[0]['text'])
        self.assertEqual('CHƯƠNG 2 THIẾT KẾ', chunks[1]['section'])

    def map_value(self, quote='Nhóm đã khảo sát hiện trạng quản lý khóa luận.'):
        return {'completed_work': [{'content': 'Đã khảo sát', 'evidence': quote, 'source_pages': [9]}],
                'document_content': [], 'difficulties': [], 'next_steps': []}

    def test_map_cannot_cite_a_different_chunk_even_when_quote_exists_in_pdf(self):
        quote = self.map_value()['completed_work'][0]['evidence']
        with self.assertRaises(ValueError):
            ground_map(self.map_value(), {'pages': [10], 'start_page': 10, 'end_page': 10,
                'text': 'Nhóm sẽ kiểm thử sau.'}, {9: quote}, 2)

    def test_reduce_cannot_change_group_page_or_invent_source(self):
        quote = self.map_value()['completed_work'][0]['evidence']
        mapped = ground_map(self.map_value(), {'pages': [9], 'start_page': 9, 'end_page': 9,
            'text': quote}, {9: quote}, 1)
        self.assertEqual([1], mapped['completed_work'][0]['source_chunk_ids'])
        bad = self.map_value()
        bad['completed_work'][0]['source_ids'] = ['INVENTED_SOURCE']
        with self.assertRaises(ValueError):
            grounded_reduce(bad, [mapped])
        bad = {key: [] for key in GROUPS}
        bad['next_steps'] = mapped['completed_work']
        with self.assertRaises(ValueError):
            grounded_reduce(bad, [mapped])

    def test_reduce_uses_more_than_two_levels_and_keeps_original_sources(self):
        parts = []
        for number in range(150):
            value = {key: [] for key in GROUPS}
            value['completed_work'] = [{'content': 'Công việc số ' + str(number),
                'evidence': 'Nhóm đã hoàn thành công việc độc lập số ' + str(number) + ' với kết quả đã kiểm tra.',
                'source_pages': [number + 1], 'source_ids': ['S' + str(number)], 'source_chunk_ids': [number + 1]}]
            parts.append(value)
        prompts = []
        def api(prompt, key, **kwargs):
            prompts.append(prompt)
            data = json.loads(prompt.split('DỮ LIỆU: ', 1)[1])
            return {group: data[group][:2] for group in GROUPS}, 'test'
        result, complete, metrics, errors, _ = hierarchical_reduce(parts, api, 'test', 'test', 150, token_budget=1000)
        self.assertTrue(complete)
        self.assertGreater(metrics['reduce_levels'], 2)
        self.assertFalse(errors)
        self.assertTrue(all(estimated_tokens(prompt) < 1800 for prompt in prompts))
        for item in result['completed_work']:
            source = parts[int(item['source_ids'][0][1:])]['completed_work'][0]
            self.assertEqual(source, item)
        self.assertEqual([], result['difficulties'])
        self.assertEqual([], result['next_steps'])

    def test_failed_merge_preserves_sources_and_marks_partial(self):
        item = self.map_value()
        def api(*args, **kwargs):
            raise TimeoutError('timed out')
        result, complete, _, errors, _ = hierarchical_reduce([item], api, 'test', 'test', 1)
        self.assertFalse(complete)
        self.assertTrue(errors)
        self.assertEqual(item['completed_work'], result['completed_work'])

    def test_reason_section_outranks_registration_keyword_match(self):
        pages = [
            {'page': 9, 'text': '1.1. Lý do lựa chọn đề tài\nViệc quản lý bằng bảng tính phân tán gây khó khăn khi theo dõi nên nhóm đề xuất hệ thống tập trung.'},
            {'page': 30, 'text': '3.2. Quy trình đăng ký đề tài\nNhóm lựa chọn đề tài phù hợp để đăng ký khóa luận.'}]
        candidates = retrieve_candidates([{'id': 1, 'requirement': 'Lý do lựa chọn đề tài.'}], pages)
        self.assertEqual(9, candidates[1][0]['page'])

    def test_only_invalid_criterion_is_repaired_and_keeps_good_decision(self):
        quote = 'Nhóm đã khảo sát hiện trạng bằng phiếu hỏi và phỏng vấn sinh viên.'
        repairs = []
        def api(prompt, key, **kwargs):
            if 'KIỂM CHỨNG TRÍCH DẪN' in prompt:
                return {'items': [{'id': n, 'status': 'met', 'explanation': 'Có dẫn chứng trực tiếp.',
                    'evidence': [{'source_id': f'C{n}_E1', 'quote': quote, 'relation': 'supports'}]} for n in (1, 2)]}, 'test'
            if 'SỬA RIÊNG MỘT TIÊU CHÍ' in prompt:
                repairs.append(prompt)
                return {'items': [{'id': 2, 'status': 'met', 'explanation': 'Đã phỏng vấn.',
                    'evidence_ids': ['C2_E1']}]}, 'test'
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': [{'id': n, 'evidence': [{'page': 9, 'quote': quote}]} for n in (1, 2)]}, 'test'
            return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Đã khảo sát.', 'evidence_ids': ['C1_E1']},
                {'id': 2, 'status': 'met', 'evidence_ids': ['C1_E1']}]}, 'test'
        result = review_requirements({'submission_requirements': '1. Khảo sát hiện trạng.\n2. Phỏng vấn sinh viên.'},
            [{'page': 9, 'text': quote}], 'test', 'test', api)
        self.assertEqual(1, len(repairs))
        self.assertIn('id=2', repairs[0])
        self.assertTrue(all(item['status'] == 'met' for item in result['items']))

    def test_extraction_cache_is_reused_and_invalidated_by_file_content(self):
        from ai_summarizer import extract_pdf_pages
        with tempfile.TemporaryDirectory(dir=Path(__file__).parent) as folder:
            path = Path(folder) / 'fixture.pdf'
            path.write_bytes(b'PDF_VERSION_ONE')
            cache = Path(folder) / 'cache'
            configure_session(cache)
            data = ([{'page': 1, 'text': 'Nội dung đã trích xuất.'}], 1, {'valid_pages': 1})
            with patch('ai_summarizer._extract_pdf_pages', return_value=data) as extract:
                extract_pdf_pages(str(path))
                self.assertTrue(extract_pdf_pages(str(path))[2]['extraction_cache_hit'])
                self.assertEqual(1, extract.call_count)
                path.write_bytes(b'PDF_VERSION_TWO')
                extract_pdf_pages(str(path))
                self.assertEqual(2, extract.call_count)

    def test_failed_audit_is_repaired_only_for_that_criterion(self):
        quote = 'Nhóm đã khảo sát hiện trạng bằng phiếu hỏi và phỏng vấn sinh viên.'
        audit_calls = []
        def api(prompt, key, **kwargs):
            if 'KIỂM CHỨNG TRÍCH DẪN' in prompt:
                repair = 'citation_repair' in prompt
                audit_calls.append(repair)
                return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Đã khảo sát bằng phiếu hỏi.',
                    'evidence': [{'source_id': 'C1_E1' if repair else 'INVALID', 'quote': quote, 'relation': 'supports'}]}]}, 'test'
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': [{'id': 1, 'evidence': [{'page': 9, 'quote': quote}]}]}, 'test'
            return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Có khảo sát.', 'evidence_ids': ['C1_E1']}]}, 'test'
        result = review_requirements({'submission_requirements': 'Khảo sát hiện trạng bằng phiếu hỏi.'},
            [{'page': 9, 'text': quote}], 'test', 'test', api)
        self.assertEqual([False, True], audit_calls)
        self.assertEqual(1, result['citation_audit']['repairs'])
        self.assertEqual('met', result['items'][0]['status'])
        self.assertTrue(result['items'][0]['evidence'][0]['relevance_verified'])
        self.assertNotIn('related_evidence', result['items'][0])


if __name__ == '__main__':
    unittest.main()
