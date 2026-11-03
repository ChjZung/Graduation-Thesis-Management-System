import unittest
import sys
import json
from pathlib import Path

# Add app/Services/pdf_lab to sys.path
sys.path.insert(0, str(Path(__file__).resolve().parent.parent.parent / "app" / "Services" / "pdf_lab"))

from ai_runtime.requirement_cross_matcher import (
    parse_requirements_text,
    flatten_chapters,
    cross_match_teacher_requirements,
)


class TestRequirementCrossMatcher(unittest.TestCase):
    def test_seven_relevant_captions_are_not_silently_cut_to_six(self):
        from ai_runtime.requirement_cross_matcher import find_matching_captions,tokenize
        req='Trình bày các sơ đồ hoạt động của hệ thống'
        caps={'assets':[{'page':i,'display_text':f'Hình 3.{i} Sơ đồ hoạt động module {i}'} for i in range(1,8)]}
        self.assertEqual(len(find_matching_captions(tokenize(req),req,caps)),7)
    def test_joined_pdf_words_match_without_modifying_source_or_design_scope(self):
        from ai_runtime.requirement_relevance import relevant
        raw='Hình 3.3. Sơ đồlớp'
        self.assertTrue(relevant('Trình bày sơ đồ lớp',raw))
        self.assertFalse(relevant('Sơ đồ lớp mức thiết kế','Sơ đồlớp mức phântích'))
        self.assertFalse(relevant('Sơ đồ lớp mức phân tích','Sơ đồlớp mức thiếtkế'))
        self.assertFalse(relevant('Tuần tự theo dõi tiến độ','Sơ đồtuầntự đăngkýđềtài'))
        self.assertFalse(relevant('Giao diện đăng nhập','Giao diện danh sách người dùng'))
        self.assertFalse(relevant('Thiết kế CSDL và ERD, các bảng dữ liệu','Bảng 6.2 Nhóm dữ liệu thử nghiệm'))
        caps={'assets':[{'page':11,'display_text':raw}]}
        result=cross_match_teacher_requirements({},[],[],caps,{'submission_requirements':'1. Trình bày sơ đồ lớp.'})
        self.assertEqual(result[0]['report_points'][0]['source_references'][0]['quote'],raw)
    def test_named_usecase_does_not_match_a_different_module(self):
        from ai_runtime.requirement_relevance import relevant
        self.assertFalse(relevant('Đặc tả Use Case quản lý đề tài','Use Case quản lý danh sách sinh viên đủ điều kiện'))
        self.assertTrue(relevant('Đặc tả Use Case quản lý đề tài','Use Case quản lý đề tài'))
    def test_erd_is_kept_when_database_prose_is_on_another_page(self):
        data=json.loads((Path(__file__).parents[1]/'fixtures/requirement_scope.json').read_text(encoding='utf-8'))
        data['chapters'][0]['sections'].append({'id':'TABLES','title':'Cơ sở dữ liệu và quan hệ giữa các bảng dữ liệu chính',
            'start_page':92,'end_page':92,'items':[{'content':'Cơ sở dữ liệu gồm các bảng dữ liệu chính và quan hệ giữa các bảng.',
            'source_references':[{'page':92,'quote':'Cơ sở dữ liệu gồm các bảng dữ liệu chính và quan hệ giữa các bảng.'}]}]})
        out=cross_match_teacher_requirements({},data['chapters'],[],data['caption_index'],data['context'])
        point=out[0]['report_points'][0]
        self.assertEqual([r['page'] for r in point['source_references']],[92])
        self.assertEqual([r['page'] for r in point['manual_references']],[80])
        self.assertNotIn(62,[r['page'] for r in point['manual_references']])
    def test_scope_rejects_other_diagrams_parent_sources_and_wrong_design_level(self):
        data=json.loads((Path(__file__).parents[1]/'fixtures/requirement_scope.json').read_text(encoding='utf-8'))
        out=cross_match_teacher_requirements({},data['chapters'],[],data['caption_index'],data['context'])
        for index,page in [(0,80),(1,81)]:
            point=out[index]['report_points'][0]
            self.assertEqual([r['page'] for r in point['source_references']],[page])
            self.assertEqual([r['page'] for r in point['manual_references']],[page])
            self.assertEqual(point['check_status'],'NEEDS_VISUAL_REVIEW')
            self.assertNotIn('hoạt động',point['explanation'])
        self.assertEqual(out[2]['report_relation'],'not_found')
        self.assertFalse(out[2]['report_points'][0]['source_references'])
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
