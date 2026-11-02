import unittest
import sys
from pathlib import Path

# Add app/Services/pdf_lab to sys.path
sys.path.insert(0, str(Path(__file__).resolve().parent.parent.parent / "app" / "Services" / "pdf_lab"))

from ai_runtime.requirement_cross_matcher import (
    parse_requirements_text,
    flatten_chapters,
    cross_match_teacher_requirements,
)


class TestRequirementCrossMatcher(unittest.TestCase):
    def test_parse_numbered_requirements(self):
        text = """1. Thiết kế cơ sở dữ liệu và ERD.\n2. Xây dựng sơ đồ lớp.\n3. Sơ đồ tuần tự chức năng đề tài."""
        items = parse_requirements_text(text)
        self.assertEqual(len(items), 3)
        self.assertEqual(items[0]['num'], '1')
        self.assertIn('cơ sở dữ liệu', items[0]['text'])

    def test_cross_match_finds_related_sections(self):
        chapters = [
            {
                'id': 'CH1',
                'title': 'CHƯƠNG 4: THIẾT KẾ HỆ THỐNG',
                'start_page': 80,
                'end_page': 88,
                'items': [{'content': 'Tổng quan thiết kế hệ thống.', 'source_references': [{'page': 80, 'quote': 'Thiết kế hệ thống'}]}],
                'sections': [
                    {
                        'id': 'SEC1',
                        'title': 'THIẾT KẾ CƠ SỞ DỮ LIỆU',
                        'start_page': 80,
                        'end_page': 80,
                        'items': [{'content': 'Mô hình cơ sở dữ liệu quan hệ MySQL.', 'source_references': [{'page': 80, 'quote': 'Cơ sở dữ liệu MySQL'}]}],
                        'sections': []
                    },
                    {
                        'id': 'SEC2',
                        'title': 'SƠ ĐỒ LỚP Ở MỨC THIẾT KẾ',
                        'start_page': 81,
                        'end_page': 81,
                        'items': [],
                        'sections': []
                    }
                ]
            }
        ]

        context = {
            'submission_requirements': "1. Thiết kế cơ sở dữ liệu và trình bày sơ đồ ERD.\n2. Xây dựng sơ đồ lớp ở mức thiết kế."
        }

        caption_index = {
            'assets': [
                {
                    'asset_id': 'cap1',
                    'page': 80,
                    'display_text': 'Hình 4.1 Sơ đồ ERD',
                    'section_title': 'THIẾT KẾ CƠ SỞ DỮ LIỆU'
                },
                {
                    'asset_id': 'cap2',
                    'page': 81,
                    'display_text': 'Hình 4.2 Sơ đồ lớp mức thiết kế',
                    'section_title': 'SƠ ĐỒ LỚP Ở MỨC THIẾT KẾ'
                }
            ]
        }

        matched = cross_match_teacher_requirements({}, chapters, [], caption_index, context)
        self.assertEqual(len(matched), 2)

        # Requirement 1: Database
        req1 = matched[0]
        self.assertEqual(req1['report_relation'], 'related_text')
        self.assertTrue(len(req1['report_points']) > 0)
        self.assertEqual(req1['report_points'][0]['relation'], 'related_text')
        self.assertTrue(len(req1['report_points'][0]['source_references']) > 0)

        # Requirement 2: Class diagram
        req2 = matched[1]
        self.assertEqual(req2['report_relation'], 'related_text')
        self.assertTrue(len(req2['report_points']) > 0)
        self.assertEqual(req2['report_points'][0]['relation'], 'related_text')
        self.assertTrue(len(req2['report_points'][0]['source_references']) > 0)


if __name__ == '__main__':
    unittest.main()
