"""Offline tests for report progress extraction (no provider credentials needed)."""
import json
from pathlib import Path
import sys
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app' / 'Services'))
from ai_report_progress import FIELDS, PROMPT, extract, validate


EMPTY = dict.fromkeys(FIELDS)
WORK = 'Nhóm đã hoàn thành chức năng đăng nhập.'
LABELED_WORK = 'Đã hoàn thành: ' + WORK
ISSUE = 'Nhóm gặp lỗi đồng bộ dữ liệu.'
PLAN = 'Tuần tới nhóm sẽ bổ sung kiểm thử.'


class ReportProgressTest(unittest.TestCase):
    def mocked(self, texts, payload, complete=True):
        def provider(prompt, validator):
            supplied = json.loads(prompt.removeprefix(PROMPT))
            self.assertEqual(set(supplied), set(texts))
            return validator(payload)
        return extract(texts, provider, source_complete=complete)

    def test_three_fields_and_missing_fields(self):
        for texts, payload in [
            ([WORK, ISSUE, PLAN], dict(zip(FIELDS, (LABELED_WORK, ISSUE, PLAN)))),
            ([WORK, PLAN], dict(zip(FIELDS, (LABELED_WORK, None, PLAN)))),
            ([WORK, ISSUE], dict(zip(FIELDS, (LABELED_WORK, ISSUE, None)))),
        ]:
            with self.subTest(texts=texts):
                self.assertEqual(self.mocked(texts, payload)['progress_v1'], payload)

    def test_mock_classification_for_design_pending_negation_and_conflict(self):
        for sentence in [
            'Sơ đồ mô tả thiết kế chức năng đăng nhập.',
            'Nhóm chưa hoàn thành chức năng đăng nhập.',
            'Nhóm không gặp lỗi đồng bộ dữ liệu.',
        ]:
            with self.subTest(sentence=sentence):
                self.assertEqual(self.mocked([sentence], EMPTY)['progress_v1'], EMPTY)
        conflict = [WORK, 'Nhóm chưa hoàn thành chức năng đăng nhập.']
        self.assertEqual(self.mocked(conflict, EMPTY)['progress_v1'], EMPTY)

    def test_rejects_missing_extra_wrong_type_invalid_json_and_unsupported_text(self):
        for payload in [
            {'cong_viec': WORK},
            dict(EMPTY, extra=None),
            dict(EMPTY, cong_viec=[WORK]),
            dict(EMPTY, cong_viec=''),
            dict(EMPTY, cong_viec='Nhóm đã triển khai lên máy chủ.'),
            dict(EMPTY, cong_viec='hoàn thành chức năng đăng nhập.'),
        ]:
            with self.subTest(payload=payload), self.assertRaises(ValueError):
                validate(payload, {WORK})
        with self.assertRaises(json.JSONDecodeError):
            extract([WORK], lambda *_: json.loads('{"cong_viec":'))

    def test_work_groups_keep_the_three_key_schema_and_ground_each_line(self):
        pending = 'Nhóm đang thực hiện phần kiểm thử.'
        progress = dict(EMPTY, cong_viec=LABELED_WORK + '\nChưa hoàn thành: ' + pending)
        self.assertEqual(self.mocked([WORK, pending], progress)['progress_v1'], progress)
        for bad in [pending, 'Đã hoàn thành: ' + pending]:
            with self.assertRaises(ValueError):
                self.mocked([pending], dict(EMPTY, cong_viec=bad))

    def test_partial_coverage_and_empty_source(self):
        self.assertEqual(self.mocked([WORK], dict(EMPTY, cong_viec=LABELED_WORK), False)['progress_v1_status'], 'partial')
        self.assertEqual(extract([WORK, 'a' * 1201], lambda *_: EMPTY)['progress_v1_status'], 'partial')
        self.assertEqual(extract([], lambda *_: self.fail('called on empty input')), {})


if __name__ == '__main__':
    unittest.main()
