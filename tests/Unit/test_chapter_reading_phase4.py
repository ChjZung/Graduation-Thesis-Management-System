"""Offline tests for chapter prose and PDF reader integration."""
from pathlib import Path
import sys
import unittest

ROOT = Path(__file__).resolve().parents[2] / 'app' / 'Services'
sys.path[:0] = [str(ROOT), str(ROOT / 'pdf_lab')]
from ai_chapter_reading import validate_chapter
from ai_runtime.instructor import requirement_document, run_reader

PROSE = (
    'Chương này giới thiệu mục đích của hệ thống. Các nhóm người dùng được mô tả theo vai trò. '
    'Phần yêu cầu xác định phạm vi xử lý. Thiết kế đề xuất cách tổ chức dữ liệu. '
    'Tác giả phân biệt giữa hai luồng thao tác. Phần cuối mô tả điều kiện sử dụng chức năng.'
)
QUOTES = [
    'Tài liệu xác định mục tiêu cho hệ thống quản lý.',
    'Đối tượng sử dụng được chia theo nhiệm vụ.',
    'Hệ thống cần giải quyết các yêu cầu nghiệp vụ.',
    'Mô hình dữ liệu nằm trong phần phân tích thiết kế.',
    'Hai cách thao tác được thể hiện trong sơ đồ.',
    'Điều kiện nghiệp vụ áp dụng cho chức năng này.',
]


class ChapterReadingTest(unittest.TestCase):
    def test_six_sentences_and_short_sources(self):
        self.assertEqual(validate_chapter([{'text': PROSE}], QUOTES), 6)
        short = [{'text': 'Báo cáo giới thiệu phương án quản lý.'}]
        self.assertEqual(validate_chapter(short, QUOTES[:1]), 1)
        self.assertEqual(validate_chapter(short, QUOTES), 1)

    def test_rejects_quotes_and_repeated_sentences(self):
        with self.assertRaisesRegex(ValueError, 'chapter_copies_source'):
            validate_chapter([{'text': QUOTES[0]}], QUOTES)
        with self.assertRaisesRegex(ValueError, 'chapter_repeats_sentence'):
            validate_chapter([{'text': ' '.join([PROSE, PROSE.split('. ')[0] + '.'])}], QUOTES)

    def test_pdf_reader_with_fake_provider(self):
        ids = [str(i) for i in range(len(QUOTES))]
        fragments = [{'item_id': i, 'quote_status': 'MATCH', 'kind': 'design', 'root_id': 'c',
                      'summary': q, 'evidence_quote': q} for i, q in zip(ids, QUOTES)]
        plan = {'fingerprint': 'plan', 'source_fingerprint': 'source', 'local_integrity_pass': True,
                'batches': [{'batch_id': 'b', 'root_id': 'c', 'chapter_title': 'Thiết kế', 'source_ids': ids}]}
        base = {'status': 'complete', 'document_sha256': 'hash', 'plan_fingerprint': 'plan',
                'selected_batch_ids': ['b'], 'map_results': {'b': {'items': fragments, 'processed_source_ids': ids}},
                'identity': 'base'}

        class Provider:
            model = 'mock'
            http_calls = []

            def model_info(self):
                return {'supportedGenerationMethods': ['generateContent'], 'inputTokenLimit': 99999, 'outputTokenLimit': 20000}

            def count_tokens(self, *args):
                return 300

            def generate(self, prompt, *args):
                if 'MỘT MỤC CON' in prompt:
                    return {'data': {'paragraphs': [{'text': 'Các vai trò được trình bày theo nhiệm vụ.', 'evidence_ids': [ids[0]]}]}}
                assert '6–8 câu' in prompt
                return {'data': {'paragraphs': [{'text': PROSE, 'evidence_ids': ids}]}}

        output = run_reader({'document': {'sha256': 'hash'}, 'fingerprint': 'source'}, plan, base,
                            requirement_document(''), Provider())
        self.assertEqual(output['status'], 'complete')
        self.assertEqual(output['chapter_summaries'][0]['paragraphs'][0]['text'], PROSE)

        fragments[0]['section_id'] = 'section-1'
        section_result = {'document': {'sha256': 'hash'}, 'fingerprint': 'source', 'sections': [
            {'section_id':'section-1','parent_section_id':'c','section_title':'Vai trò'},
            {'section_id':'section-2','parent_section_id':'c','section_title':'Chưa có nguồn'},
        ]}
        section_reading = run_reader(section_result, plan, base, requirement_document(''), Provider())
        self.assertEqual(list(section_reading['section_summaries']), ['section-1'])
        self.assertEqual(section_reading['section_summaries']['section-1'][0]['evidence_ids'], [ids[0]])

        class ShortProvider(Provider):
            def generate(self, prompt, *args):
                return {'data': {'paragraphs': [{'text': PROSE.split('. ')[0] + '.', 'evidence_ids': ids}]}}

        short = run_reader({'document': {'sha256': 'hash'}, 'fingerprint': 'source'}, plan, base,
                           requirement_document(''), ShortProvider())
        self.assertFalse(short['reading_errors'])
        self.assertEqual(short['chapter_summaries'][0]['paragraphs'][0]['text'], PROSE.split('. ')[0] + '.')


    def test_bad_chapter_does_not_block_next_chapter(self):
        from unittest.mock import patch
        from ai_runtime import instructor

        first = 'Chương lỗi'
        second = 'Chương kế tiếp'
        ids = ['1', '2']
        batches = [
            {'batch_id': 'b'+i, 'root_id': 'c'+i, 'chapter_title': title, 'source_ids': [i]}
            for i, title in zip(ids, [first, second])
        ]
        maps = {
            'b'+i: {'processed_source_ids': [i], 'items': [{
                'item_id': i, 'quote_status': 'MATCH', 'kind': 'design', 'root_id': 'c'+i,
                'summary': QUOTES[int(i)-1], 'evidence_quote': QUOTES[int(i)-1],
            }]}
            for i in ids
        }
        base = {'status': 'complete', 'document_sha256': 'hash', 'plan_fingerprint': 'plan',
                'selected_batch_ids': ['b1','b2'], 'map_results': maps, 'identity': 'base'}
        plan = {'fingerprint': 'plan', 'source_fingerprint': 'source',
                'local_integrity_pass': True, 'batches': batches}
        broken = 'Bản thử này thiếu các chức năng quan trọng.'
        valid = 'Phần này mô tả vai trò của từng nhóm sử dụng.'

        class Provider:
            model = 'mock'
            http_calls = []

            def model_info(self):
                return {'supportedGenerationMethods': ['generateContent'],
                        'inputTokenLimit': 99999, 'outputTokenLimit': 20000}

            def count_tokens(self, *args):
                return 300

            def generate(self, prompt, schema, *args):
                allowed = schema['properties']['paragraphs']['items']['properties']['evidence_ids']['items']['enum']
                return {'data': {'paragraphs': [{'text': broken if allowed == ['1'] else valid,
                                                'evidence_ids': allowed}]}}

        def check(data, *args):
            if data['paragraphs'][0]['text'] == broken:
                raise instructor.ApiError('thiếu đầu ra', 'reader_missing_key_outputs')
            return data

        with patch.object(instructor, 'validate_quality', side_effect=check):
            output = run_reader({'document': {'sha256': 'hash'}, 'fingerprint': 'source'}, plan, base,
                                requirement_document(''), Provider())
        self.assertEqual(output['status'], 'complete')
        self.assertEqual(output['reading_errors'], [{'root_id': 'c1', 'code': 'reader_missing_key_outputs'}])
        self.assertEqual(output['chapter_summaries'][0]['paragraphs'], [])
        self.assertEqual(output['chapter_summaries'][1]['paragraphs'][0]['text'], valid)


if __name__ == '__main__':
    unittest.main()
