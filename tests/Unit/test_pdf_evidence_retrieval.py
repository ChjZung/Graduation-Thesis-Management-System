import json
import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app/Services'))
from ai_requirement_review import criteria_from_context, response_items, review_requirements
from pdf_evidence_retrieval import retrieve_candidates, page_passages, is_index_text


TABLE_BRIEF = 'Nộp báo cáo PDF trình bày thiết kế cơ sở dữ liệu. Các yêu cầu cần đối chiếu:\n\n' + '\n\n'.join(
    f'{i}. Mô tả bảng {name}, gồm tên các cột, kiểu dữ liệu và ý nghĩa của các cột.'
    for i, name in enumerate(['người dùng (user/users)', 'hội đồng (council)', 'khóa luận (thesis)', 'điểm (score)', 'tiêu chí (criteria)', 'điểm chi tiết (score_detail)'], 1)
)


class EvidenceRetrievalTest(unittest.TestCase):
    def pages(self):
        return [{'page': 7, 'text': '3.4.1. Bảng score\nTên cột\nKiểu dữ liệu\nMô tả ý nghĩa\nid\nINT\nMã điểm\nBảng 3.4.1. Bảng score\n3.4.2. Bảng score_detail\nTên cột\nKiểu dữ liệu\nMô tả ý nghĩa\nscore_id\nINT\nMã điểm\nmark\nFLOAT\nĐiểm chi tiết'}]

    def test_numbered_table_rubric_has_six_items_without_intro(self):
        criteria = criteria_from_context({'submission_requirements': TABLE_BRIEF})
        self.assertEqual(6, len(criteria))
        self.assertTrue(all(c['requirement'].startswith('Mô tả bảng') for c in criteria))

    def test_inline_numbered_table_rubric_is_not_one_large_item(self):
        self.assertEqual(6, len(criteria_from_context({'submission_requirements': TABLE_BRIEF.replace('\n', ' ')})))

    def test_standalone_requirement_before_numbered_list_is_kept(self):
        criteria = criteria_from_context({'submission_requirements': 'Báo cáo phải có xác nhận giảng viên.\n1. Có thiết kế dữ liệu.\n2. Có kết quả kiểm thử.'})
        self.assertEqual(3, len(criteria))

    def test_table_body_includes_types_meanings_and_correct_entity(self):
        criteria = [{'id': 1, 'requirement': 'Mô tả bảng điểm (score), gồm tên cột, kiểu dữ liệu và ý nghĩa.'}]
        evidence = retrieve_candidates(criteria, self.pages())[1]
        self.assertEqual(1, len(evidence))
        self.assertIn('INT\nMã điểm', evidence[0]['quote'])
        self.assertNotIn('score_detail', evidence[0]['quote'])
        self.assertNotIn('mark', evidence[0]['quote'])

    def test_new_table_names_are_retrieved_without_known_project_entities(self):
        pages = [{'page': 9, 'text': '2.1. Bảng inventory_locations\nTên cột\nKiểu dữ liệu\nÝ nghĩa\nid\nINTEGER\nMã vị trí lưu trữ'}]
        evidence = retrieve_candidates([{'id': 1, 'requirement': 'Mô tả bảng vị trí (inventory_locations).'}], pages)[1]
        self.assertEqual(9, evidence[0]['page'])
        self.assertIn('Mã vị trí lưu trữ', evidence[0]['quote'])

    def test_dense_pages_keep_the_end_of_source(self):
        text = '\n'.join(f'ROW_{i} datatype description' for i in range(300)) + '\nEND_OF_SOURCE'
        passages = page_passages([{'page': 1, 'text': text}])
        self.assertIn('END_OF_SOURCE', passages[-1]['quote'])
        self.assertTrue(all(p['quote'] in text for p in passages))

    def test_list_of_tables_is_a_pointer_not_table_body_evidence(self):
        index = {'page': 1, 'text': 'DANH MỤC BẢNG\nBảng 3.4.1. Bảng score.............19\nBảng 3.4.2. Bảng score_detail........20'}
        self.assertTrue(is_index_text(index['text']))
        evidence = retrieve_candidates([{'id': 1, 'requirement': 'Mô tả bảng điểm (score).'}], [index] + self.pages())[1]
        self.assertTrue(all(e['page'] == 7 for e in evidence))


class DecisionProtocolTest(unittest.TestCase):
    quote = 'The database design contains five normalized tables.'

    def pages(self):
        return [{'page': 1, 'text': self.quote}]

    def test_top_level_json_array_is_valid_not_silently_discarded(self):
        payload = [{'id': 1, 'status': 'met', 'evidence_ids': ['C1_E1']}]
        self.assertEqual(payload, response_items(payload))
        def api(prompt, key, **kwargs):
            if 'KIỂM CHỨNG TRÍCH DẪN' in prompt:
                return [{'id': 1, 'status': 'met', 'explanation': 'Có năm bảng dữ liệu.', 'evidence': [{'source_id': 'C1_E1', 'quote': self.quote, 'relation': 'supports'}]}], 'test'
            if '--- DỮ LIỆU PDF ---' in prompt:
                return [{'id': 1, 'evidence': [{'page': 1, 'quote': self.quote}]}], 'test'
            return payload, 'test'
        result = review_requirements({'submission_requirements': 'Thiết kế cơ sở dữ liệu'}, self.pages(), 'key', 'test', api)
        self.assertEqual('met', result['items'][0]['status'])
        self.assertEqual(self.quote, result['items'][0]['evidence'][0]['quote'])

    def test_invalid_shape_is_rejected(self):
        for response in [None, 'not JSON', {'unexpected': []}, {'items': ['invalid item']}]:
            with self.assertRaises(ValueError):
                response_items(response)

    def test_failed_decision_keeps_found_evidence_and_explains_service_failure(self):
        def api(prompt, key, **kwargs):
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': [{'id': 1, 'evidence': [{'page': 1, 'quote': self.quote}]}]}, 'test'
            return {'unexpected': []}, 'test'
        result = review_requirements({'submission_requirements': 'Thiết kế cơ sở dữ liệu'}, self.pages(), 'key', 'test', api)
        self.assertEqual('service_error', result['status'])
        self.assertEqual('failed', result['decision_status'])
        self.assertTrue(result['items'][0]['evidence'])
        self.assertIn('chưa trả kết luận hợp lệ', result['items'][0]['explanation'])

    def test_decision_cannot_select_other_criterion_evidence(self):
        def api(prompt, key, **kwargs):
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': [{'id': 1, 'evidence': [{'page': 1, 'quote': self.quote}]}]}, 'test'
            return {'items': [{'id': 2, 'status': 'met', 'evidence_ids': ['C1_E1']}]}, 'test'
        result = review_requirements({'submission_requirements': 'Thiết kế cơ sở dữ liệu; Kiểm thử hệ thống'}, self.pages(), 'key', 'test', api)
        self.assertEqual('insufficient_evidence', result['items'][1]['status'])

    def test_unrelated_short_page_does_not_invalidate_existing_positive_evidence(self):
        def api(prompt, key, **kwargs):
            if 'KIỂM CHỨNG TRÍCH DẪN' in prompt:
                return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Có năm bảng dữ liệu.', 'evidence': [{'source_id': 'C1_E1', 'quote': self.quote, 'relation': 'supports'}]}]}, 'test'
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': [{'id': 1, 'evidence': [{'page': 1, 'quote': self.quote}]}]}, 'test'
            return {'items': [{'id': 1, 'status': 'met', 'evidence_ids': ['C1_E1']}]}, 'test'
        result = review_requirements({'submission_requirements': 'Thiết kế cơ sở dữ liệu'}, self.pages() + [{'page': 2, 'text': 'Figure only'}], 'key', 'test', api)
        self.assertEqual('completed', result['status'])
        self.assertEqual('met', result['items'][0]['status'])

    def test_local_table_evidence_is_present_when_model_is_unavailable(self):
        pages = EvidenceRetrievalTest().pages()
        result = review_requirements({'submission_requirements': 'Mô tả bảng điểm (score), gồm tên cột và kiểu dữ liệu.'}, pages)
        self.assertEqual('not_evaluated', result['status'])
        self.assertEqual('insufficient_evidence', result['items'][0]['status'])
        self.assertIn('INT', result['items'][0]['evidence'][0]['quote'])

    def test_model_partial_verdict_is_not_overridden_by_table_presence(self):
        pages = [{'page': 2, 'text': '3.4.1. Bảng orders\nTên cột\nid\nÝ nghĩa\nMã đơn hàng'}]
        def api(prompt, key, **kwargs):
            if 'KIỂM CHỨNG TRÍCH DẪN' in prompt:
                return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Có bảng đơn hàng.', 'evidence': [{'source_id': 'C1_E1', 'quote': pages[0]['text'], 'relation': 'supports'}]}]}, 'test'
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': []}, 'test'
            return {'items': [{'id': 1, 'status': 'partial', 'explanation': 'Chưa có kiểu dữ liệu của cột.', 'evidence_ids': ['C1_E1']}]}, 'test'
        result = review_requirements({'submission_requirements': 'Mô tả bảng đơn hàng (orders), gồm tên cột, kiểu dữ liệu và ý nghĩa.'}, pages, 'key', 'test', api)
        self.assertEqual('partial', result['items'][0]['status'])
        self.assertIn('Chưa có kiểu dữ liệu', result['items'][0]['explanation'])

    def test_map_index_quote_does_not_become_design_evidence(self):
        pages = [{'page': 1, 'text': 'DANH MỤC BẢNG\nBảng score là tên trong danh mục các bảng của báo cáo.'}]
        def api(prompt, key, **kwargs):
            if '--- DỮ LIỆU PDF ---' in prompt:
                return {'items': [{'id': 1, 'evidence': [{'page': 1, 'quote': 'Bảng score là tên trong danh mục các bảng của báo cáo.'}]}]}, 'test'
            return {'items': [{'id': 1, 'status': 'met', 'evidence_ids': ['C1_E1']}]}, 'test'
        result = review_requirements({'submission_requirements': 'Mô tả bảng điểm (score).'}, pages, 'key', 'test', api)
        self.assertEqual('insufficient_evidence', result['items'][0]['status'])
        self.assertFalse(result['items'][0]['evidence'])


if __name__ == '__main__':
    unittest.main()
