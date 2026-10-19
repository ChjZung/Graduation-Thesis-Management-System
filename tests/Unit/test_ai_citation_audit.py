import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app/Services'))
from ai_citation_audit import audit_citations, keeps_statement_context
from ai_requirement_review import criteria_from_context, response_items
from pdf_evidence_retrieval import retrieve_candidates


BRIEF = '''Đối chiếu báo cáo PDF với từng yêu cầu sau:
1. Nêu giới hạn tối đa hai giảng viên trong một khóa luận.
2. Nêu thành phần bắt buộc của hội đồng: một Chủ tịch, một Thư ký, một Giảng viên phản biện; có thể thêm hai thành viên nếu cần thiết.
3. Nêu quy định không được chấm điểm khi sinh viên chưa nộp bài.
4. Nêu quy định không được chỉnh sửa điểm khi hội đồng đã đóng.
5. Nêu cách xử lý khi một sinh viên trong nhóm đã nộp bài: thành viên còn lại được xem hoặc cập nhật bài thay vì nộp mới.
6. Nêu quy định không được cập nhật bài nộp khi hội đồng đang chấm điểm.'''


class CitationAuditTest(unittest.TestCase):
    quote = 'Không được chấm điểm khi sinh viên chưa nộp bài.'
    requirement = 'Nêu quy định không được chấm điểm khi sinh viên chưa nộp bài.'

    def run_audit(self, decision=None, quote=None, status='met', fail=False):
        actual = quote or self.quote
        source = {'page': 29, 'quote': actual, 'verified': True}
        items = [{'id': 1, 'requirement': self.requirement, 'status': status,
                  'explanation': 'Có quy định chấm điểm.', 'evidence': [source]}]
        if decision is None:
            decision = {'id': 1, 'status': 'met', 'explanation': 'Chưa nộp bài thì không được chấm điểm.',
                        'evidence': [{'source_id': 'C1_E1', 'quote': self.quote, 'relation': 'supports'}]}
        prompts = []
        def api(prompt, key, **kwargs):
            prompts.append(prompt)
            if fail:
                raise RuntimeError('Unavailable')
            return {'items': [decision]}, 'test'
        result = audit_citations(items, {1: {'C1_E1': source}}, {29: actual}, {}, api, 'test', 'test', response_items)
        return items[0], result, prompts

    def test_verified_sentence_replaces_full_page_and_keeps_true_page(self):
        page = 'Giới thiệu chung về hệ thống. ' + self.quote + ' Sinh viên có thể xem báo cáo.'
        item, result, _ = self.run_audit(quote=page)
        self.assertEqual('met', item['status'])
        self.assertEqual(29, item['evidence'][0]['page'])
        self.assertEqual(self.quote, item['evidence'][0]['quote'])
        self.assertTrue(item['evidence'][0]['relevance_verified'])
        self.assertEqual('completed', result['status'])

    def test_irrelevant_real_pdf_text_cannot_survive_audit_rejection(self):
        decision = {'id': 1, 'status': 'insufficient_evidence', 'explanation': 'Đoạn này nói về Java, không chứng minh quy định chấm điểm.', 'evidence': []}
        item, _, _ = self.run_audit(decision, quote='Java là ngôn ngữ lập trình dùng xây dựng website.')
        self.assertEqual('insufficient_evidence', item['status'])
        self.assertEqual([], item['evidence'])
        self.assertTrue(item['related_evidence'])

    def test_invented_quote_does_not_receive_green_verdict(self):
        decision = {'id': 1, 'status': 'met', 'explanation': 'Có quy định.', 'evidence': [{'source_id': 'C1_E1', 'quote': 'Sinh viên đã hoàn thành mọi yêu cầu kiểm thử.', 'relation': 'supports'}]}
        item, result, _ = self.run_audit(decision)
        self.assertEqual('insufficient_evidence', item['status'])
        self.assertEqual([], item['evidence'])
        self.assertEqual(1, result['rejected_quotes'])

    def test_other_criterion_source_cannot_be_used(self):
        decision = {'id': 1, 'status': 'met', 'explanation': 'Có quy định.', 'evidence': [{'source_id': 'C2_E1', 'quote': self.quote, 'relation': 'supports'}]}
        item, _, _ = self.run_audit(decision)
        self.assertEqual('insufficient_evidence', item['status'])

    def test_malformed_source_id_is_rejected_without_crashing(self):
        decision = {'id': 1, 'status': 'met', 'explanation': 'Có quy định.', 'evidence': [{'source_id': ['C1_E1'], 'quote': self.quote, 'relation': 'supports'}]}
        item, _, _ = self.run_audit(decision)
        self.assertEqual('insufficient_evidence', item['status'])

    def test_contradicting_evidence_cannot_support_positive_verdict(self):
        decision = {'id': 1, 'status': 'met', 'explanation': 'Có quy định.', 'evidence': [{'source_id': 'C1_E1', 'quote': self.quote, 'relation': 'contradicts'}]}
        item, _, _ = self.run_audit(decision)
        self.assertEqual('insufficient_evidence', item['status'])

    def test_unavailable_audit_keeps_sources_but_does_not_publish_positive_proof(self):
        item, result, _ = self.run_audit(fail=True)
        self.assertEqual('failed', result['status'])
        self.assertEqual('insufficient_evidence', item['status'])
        self.assertEqual([], item['evidence'])
        self.assertTrue(item['related_evidence'])

    def test_audit_cannot_promote_partial_assessment(self):
        item, _, _ = self.run_audit(status='partial')
        self.assertEqual('partial', item['status'])
        self.assertEqual('Có quy định chấm điểm.', item['explanation'])

    def test_pdf_field_boundary_does_not_import_previous_rule_condition(self):
        page = 'Có thể sửa điểm nếu chưa đóng hội đồng\n- Luồng thay thế: không có\n- Luồng ngoại lệ: ' + self.quote
        self.assertTrue(keeps_statement_context(self.quote, page))

    def test_negation_and_condition_cannot_be_cut_out_of_quote(self):
        self.assertFalse(keeps_statement_context('được chấm điểm khi sinh viên chưa nộp bài.', self.quote))
        self.assertFalse(keeps_statement_context('Không được chấm điểm', self.quote))
        text = 'Khi hội đồng đang chấm điểm, sinh viên không được cập nhật bài.'
        self.assertFalse(keeps_statement_context('sinh viên không được cập nhật bài.', text))
        self.assertTrue(keeps_statement_context(text, text))

    def test_numeric_bound_cannot_be_dropped(self):
        text = 'Một khóa luận có tối đa hai giảng viên hướng dẫn.'
        self.assertFalse(keeps_statement_context('hai giảng viên hướng dẫn.', text))
        self.assertTrue(keeps_statement_context(text, text))

    def test_six_numbered_requirements_exclude_each_requirement_intro(self):
        criteria = criteria_from_context({'submission_requirements': BRIEF})
        self.assertEqual(6, len(criteria))
        self.assertTrue(all(c['requirement'].startswith('Nêu ') for c in criteria))
        self.assertEqual(list(range(1, 7)), [c['id'] for c in criteria])

    def test_short_negative_rule_is_retrieved_before_generic_technology_page(self):
        pages = [{'page': 18, 'text': 'Java là ngôn ngữ lập trình. Sinh viên sử dụng Spring Boot xây dựng hệ thống quản lý khóa luận và chấm điểm.'},
                 {'page': 29, 'text': self.quote}]
        evidence = retrieve_candidates([{'id': 1, 'requirement': self.requirement}], pages)[1]
        self.assertEqual(29, evidence[0]['page'])
        self.assertEqual(self.quote, evidence[0]['quote'])


if __name__ == '__main__':
    unittest.main()
