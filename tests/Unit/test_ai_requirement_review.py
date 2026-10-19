import base64
import hashlib
import os
import sys
import tempfile
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app' / 'Services'))
from ai_requirement_review import criteria_from_context, review_requirements, verified_evidence, page_batches
from pdf_citation_preview import fitz, render_citation


class RequirementsReviewTest(unittest.TestCase):
    def setUp(self):
        self.context = {'stage_title': 'Thiết kế hệ thống', 'round_title': 'Đợt thiết kế CSDL', 'round_description': 'Kiểm tra thiết kế dữ liệu.', 'submission_requirements': 'Thiết kế cơ sở dữ liệu; Báo cáo kết quả kiểm thử', 'stage_requirements': '', 'require_source_code': False}
        self.quote = 'The database design contains five normalized tables.'
        self.pages = [{'page': 1, 'text': self.quote}, {'page': 2, 'text': 'The implementation plan describes the next milestones.'}]
        self.prompts = []

    def api(self, prompt, api_key, **kwargs):
        self.prompts.append(prompt)
        evidence = [{'page': 1, 'quote': self.quote}]
        if 'KIỂM CHỨNG TRÍCH DẪN' in prompt:
            return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Có thiết kế năm bảng dữ liệu.', 'evidence': [{'source_id': 'C1_E1', 'quote': self.quote, 'relation': 'supports'}]}]}, 'test-model'
        if '--- DỮ LIỆU PDF ---' in prompt:
            return {'items': [{'id': 1, 'evidence': evidence}]}, 'test-model'
        return {'items': [{'id': 1, 'status': 'met', 'explanation': 'Có thiết kế bảng dữ liệu.', 'evidence': evidence}, {'id': 2, 'status': 'not_found', 'explanation': 'Chưa tìm thấy kết quả kiểm thử.', 'evidence': []}]}, 'test-model'

    def test_full_context_and_separate_positive_and_missing_results(self):
        review = review_requirements(self.context, self.pages, 'fake', 'test', self.api)
        self.assertEqual(['met', 'not_found'], [i['status'] for i in review['items']])
        for key in ['stage_title', 'round_title', 'round_description', 'submission_requirements']:
            self.assertIn(self.context[key], self.prompts[0])
        self.assertIn('require_source_code=false', self.prompts[0])

    def test_all_explicit_criteria_are_preserved(self):
        self.assertEqual(2, len(criteria_from_context(self.context)))

    def test_wrong_page_is_rejected(self):
        self.assertEqual([], verified_evidence([{'page': 2, 'quote': self.quote}], {1: self.quote, 2: 'Unrelated'}))

    def test_invented_quote_is_rejected(self):
        self.assertEqual([], verified_evidence([{'page': 1, 'quote': 'All tests passed successfully.'}], {1: self.quote}))

    def test_fake_positive_without_evidence_cannot_be_met(self):
        def bad_api(*args, **kwargs):
            return {'items': [{'id': 1, 'status': 'met', 'evidence': [{'page': 1, 'quote': 'This sentence does not occur in the PDF.'}]}]}, 'test'
        review = review_requirements(self.context, self.pages, 'fake', 'test', bad_api)
        self.assertEqual('insufficient_evidence', review['items'][0]['status'])

    def test_no_api_does_not_mean_student_failed(self):
        review = review_requirements(self.context, self.pages)
        self.assertEqual('not_evaluated', review['status'])
        self.assertTrue(all(i['status'] == 'insufficient_evidence' for i in review['items']))

    def test_api_error_does_not_make_a_negative_assessment(self):
        def failed(*args, **kwargs):
            raise RuntimeError('Unavailable')
        review = review_requirements(self.context, self.pages, 'fake', 'test', failed)
        self.assertEqual('not_evaluated', review['status'])

    def test_scanned_page_prevents_claiming_absence(self):
        review = review_requirements(self.context, self.pages + [{'page': 3, 'text': ''}], 'fake', 'test', self.api)
        self.assertEqual('insufficient_evidence', review['items'][1]['status'])
        self.assertEqual('partial', review['status'])

    def test_long_pdf_is_not_truncated(self):
        batches = page_batches([{'page': 1, 'text': 'Z' * 50000 + 'END_OF_PDF'}])
        self.assertIn('END_OF_PDF', batches[-1])
        self.assertEqual(50000, sum(b.count('Z') for b in batches))

    def test_empty_pdf_has_no_verdict(self):
        review = review_requirements(self.context, [{'page': 1, 'text': ''}], 'fake', 'test', self.api)
        self.assertEqual('not_evaluated', review['status'])

    def test_no_requirements_are_invented(self):
        review = review_requirements({'stage_title': 'Giai đoạn 1'}, self.pages, 'fake', 'test', self.api)
        self.assertEqual('not_configured', review['status'])


class CitationPreviewTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.path = os.path.join(self.temp.name, 'citation.pdf')
        with fitz.open() as doc:
            page = doc.new_page()
            page.insert_text((50, 80), 'Chapter One - Progress Report')
            page.insert_text((50, 650), 'The database design contains five normalized tables.')
            doc.new_page().insert_text((50, 100), 'Next steps and unresolved questions.')
            doc.save(self.path)
        self.before = hashlib.sha256(Path(self.path).read_bytes()).hexdigest()

    def tearDown(self):
        self.temp.cleanup()

    def test_highlight_focus_and_source_pdf_unchanged(self):
        result = render_citation(self.path, 1, 'The database design contains five normalized tables.')
        self.assertTrue(result['matched'])
        self.assertGreater(result['focus_y'], .6)
        self.assertEqual(2, result['total_pages'])
        self.assertTrue(base64.b64decode(result['image'].split(',', 1)[1]).startswith(b'\x89PNG'))
        self.assertEqual(self.before, hashlib.sha256(Path(self.path).read_bytes()).hexdigest())

    def test_wrong_page_has_no_fake_highlight(self):
        self.assertFalse(render_citation(self.path, 2, 'The database design contains five normalized tables.')['matched'])

    def test_invalid_page_is_rejected(self):
        with self.assertRaises(ValueError):
            render_citation(self.path, 99)

    def test_rotated_page_has_valid_focus(self):
        rotated = os.path.join(self.temp.name, 'rotated.pdf')
        with fitz.open(self.path) as doc:
            doc[0].set_rotation(90)
            doc.save(rotated)
        result = render_citation(rotated, 1, 'The database design contains five normalized tables.')
        self.assertTrue(result['matched'])
        self.assertTrue(0 <= result['focus_y'] <= 1)

    def test_vietnamese_quote_can_be_highlighted(self):
        font = next((p for p in ['C:/Windows/Fonts/arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'] if os.path.isfile(p)), None)
        if not font:
            self.skipTest('No Unicode font installed for the Vietnamese PDF fixture.')
        path = os.path.join(self.temp.name, 'vietnamese.pdf')
        quote = 'Nhóm đã hoàn thiện thiết kế cơ sở dữ liệu.'
        with fitz.open() as doc:
            page = doc.new_page()
            page.insert_text((50, 180), quote, fontname='vietnamese', fontfile=font, fontsize=12)
            doc.save(path)
        self.assertTrue(render_citation(path, 1, quote)['matched'])


if __name__ == '__main__':
    unittest.main()
