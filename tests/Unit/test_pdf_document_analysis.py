import sys
import unittest
from pathlib import Path
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app' / 'Services'))
from pdf_document_analysis import exact_quote, usecase_criteria, structural_usecase_review, document_structure
from ai_requirement_review import review_requirements
from ai_summarizer import extract_pdf_pages, run_map_reduce_pipeline, verify_evidence_in_pages


BRIEF = ('Nộp báo cáo PDF có đặc tả các Use Case:\n1. Đăng nhập.\n2. Tạo khóa luận.\n'
         '3. Thành lập hội đồng.\n4. Chấm điểm.\n5. Nộp bài.\n'
         'Đối với từng Use Case, báo cáo cần nêu tác nhân thực hiện, tiền điều kiện và luồng hoạt động chính.')


class DocumentRetrievalTest(unittest.TestCase):
    def fixture(self):
        return [{'page': 1, 'text': '3.2.2. Use case đăng nhập\n- Actor chính: Sinh viên'},
                {'page': 2, 'text': '- Tiền điều kiện: Sinh viên có tài khoản.\n- Luồng hoạt động chính:\n1. Nhập tên.\n2. Nhập mật khẩu.\n- Luồng ngoại lệ: Sai thông tin.\n3.2.3. Use case Nộp bài\n- Actor chính: Sinh viên\n- Luồng hoạt động chính:\n1. Nộp PDF.'}]

    def test_shared_rubric_is_attached_to_each_named_usecase(self):
        criteria = usecase_criteria(BRIEF)
        self.assertEqual(5, len(criteria))
        self.assertTrue(all(set(c['required_parts']) == {'actor', 'precondition', 'main_flow'} for c in criteria))
        self.assertIn('tiền điều kiện', criteria[-1]['requirement'])

    def test_flattened_html_numbered_list_still_yields_five_criteria(self):
        self.assertEqual(5, len(usecase_criteria(BRIEF.replace('\n', ' '))))

    def test_quality_constraints_are_not_approved_by_presence_checker(self):
        criteria = usecase_criteria(BRIEF + ' Nội dung phải đúng nghiệp vụ.')
        self.assertTrue(all(not c['structural_only'] for c in criteria))

    def test_unspecified_additional_constraints_cannot_be_silently_ignored(self):
        self.assertTrue(all(not c['structural_only'] for c in usecase_criteria(BRIEF + ' Kèm ví dụ minh họa.')))

    def test_placeholder_field_values_are_not_evidence_of_completed_specification(self):
        pages = [{'page': 1, 'text': '3.2.2. Use case đăng nhập\n- Actor chính: TBD\n- Tiền điều kiện: sẽ bổ sung\n- Luồng hoạt động chính:\n1. TODO'}]
        items, _ = structural_usecase_review(usecase_criteria(BRIEF)[:1], pages)
        self.assertNotEqual('met', items[0]['status'])

    def test_cross_page_section_retains_actor_and_precondition_on_correct_pages(self):
        items, pages = structural_usecase_review(usecase_criteria(BRIEF)[:1], self.fixture())
        self.assertEqual('met', items[0]['status'])
        self.assertEqual([1, 2], pages)
        self.assertEqual(1, next(e['page'] for e in items[0]['evidence'] if e['part'] == 'actor'))
        self.assertEqual(2, next(e['page'] for e in items[0]['evidence'] if e['part'] == 'precondition'))
        self.assertNotIn('Nộp PDF', str(items[0]['evidence']))

    def test_missing_precondition_cannot_be_borrowed_from_another_usecase(self):
        items, _ = structural_usecase_review([usecase_criteria(BRIEF)[4]], self.fixture())
        self.assertEqual('partial', items[0]['status'])
        self.assertIn('tiền điều kiện', items[0]['explanation'])

    def test_arbitrary_new_usecase_name_is_retrieved_without_hardcoded_topic_names(self):
        brief = BRIEF.replace('Đăng nhập', 'Quản lý hợp đồng')
        pages = self.fixture()
        pages[0]['text'] = pages[0]['text'].replace('đăng nhập', 'Quản lý hợp đồng')
        items, _ = structural_usecase_review(usecase_criteria(brief)[:1], pages)
        self.assertEqual('met', items[0]['status'])

    def test_heading_without_field_values_is_insufficient(self):
        pages = [{'page': 1, 'text': '3.2.2. Use case đăng nhập\n- Actor chính:\n- Tiền điều kiện:\n- Luồng hoạt động chính:'}]
        items, _ = structural_usecase_review(usecase_criteria(BRIEF)[:1], pages)
        self.assertNotEqual('met', items[0]['status'])

    def test_unknown_usecase_is_not_automatically_failed(self):
        items, _ = structural_usecase_review([usecase_criteria(BRIEF)[2]], self.fixture())
        self.assertEqual('insufficient_evidence', items[0]['status'])

    def test_full_quote_required_even_if_first_six_words_match(self):
        original = 'The database contains five tables and has no performance tests.'
        fabricated = 'The database contains five tables and all performance tests passed.'
        checked = verify_evidence_in_pages([{'content': original, 'evidence': fabricated, 'source_pages': [1]}], {1: original})
        self.assertFalse(checked[0]['verified'])

    def test_pdf_spacing_differences_match_but_quote_is_restored_to_pdf_text(self):
        actual = 'Tiền điều kiện: giáo vụđăng nhập\nthành công vào hệthống.'
        quote = 'Tiền điều kiện: giáo vụ đăng nhập thành công vào hệ thống.'
        self.assertEqual(actual, exact_quote(quote, actual))
        self.assertIsNone(exact_quote(quote, 'Nội dung khác hoàn toàn.'))

    def test_manual_structural_review_does_not_call_api_or_claim_software_quality(self):
        def forbidden(*args, **kwargs):
            raise AssertionError('A structural presence check must not need a model.')
        review = review_requirements({'submission_requirements': BRIEF}, self.fixture(), 'test', 'test', forbidden)
        self.assertEqual('document_structure', review['method'])
        self.assertIn('không xác nhận phần mềm chạy đúng', review['items'][0]['explanation'])

    def test_future_work_stops_at_references(self):
        structure = document_structure([{'page': 1, 'text': 'Chương 5. TỔNG KẾT\n5.3. Hướng phát triển\n- Thêm chức năng chat trong hệ thống.\nTÀI LIỆU THAM KHẢO\nMột tài liệu không phải kế hoạch.'}])
        self.assertEqual(1, len(structure['next_steps']))
        self.assertNotIn('không phải kế hoạch', str(structure['next_steps']))


class RealThesisRegressionTest(unittest.TestCase):
    path = Path(r'C:\Mac\Home\Documents\1_monhoc\KhoaLuan\[Vn Document]xay-dung-website-quan-ly-khoa-luan-tot-nghiep-dwa1752077489.pdf')

    def setUp(self):
        if not self.path.exists():
            self.skipTest('Optional user-provided PDF fixture is not available on this machine.')

    def test_five_required_usecases_have_evidence_for_all_three_fields(self):
        pages, total, _ = extract_pdf_pages(str(self.path))
        result = review_requirements({'submission_requirements': BRIEF}, pages)
        self.assertEqual(72, total)
        self.assertEqual(['met'] * 5, [i['status'] for i in result['items']])
        for item in result['items']:
            self.assertEqual({'actor', 'precondition', 'main_flow'}, {e['part'] for e in item['evidence']})
            for e in item['evidence']:
                self.assertTrue(23 <= e['page'] <= 29)
                self.assertIsNotNone(exact_quote(e['quote'], pages[e['page'] - 1]['text']))

    def test_unavailable_api_preserves_outline_and_exact_extracts_without_inferred_progress(self):
        with patch('ai_summarizer.call_gemini_api', side_effect=RuntimeError('API unavailable')):
            result = run_map_reduce_pipeline(str(self.path), api_key='test')
        data = result['data']
        self.assertEqual(6, data['schema_version'])
        self.assertNotIn('completion_percentage', data)
        self.assertEqual(72, data['analysis']['total_pages'])
        self.assertFalse(data['analysis']['text_complete'])
        self.assertEqual([], data['completed_work'])
        self.assertEqual([], data['document_content'])
        self.assertNotIn('next_steps', data)
        self.assertTrue(data['chapters'])
        self.assertTrue(data['analysis']['source_coverage']['source_offsets_verified'])
        self.assertTrue(data['analysis']['visual_unread_pages'])


if __name__ == '__main__':
    unittest.main()
