import json
import sys
import tempfile
import unittest
import zipfile
from pathlib import Path
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app/Services'))
from report_document import extract_docx_units, preview_docx
from ai_summarizer import extract_pdf_pages, run_map_reduce_pipeline


def docx_fixture(path, body):
    with zipfile.ZipFile(path, 'w', zipfile.ZIP_DEFLATED) as archive:
        archive.writestr('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>')
        archive.writestr('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>')
        archive.writestr('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' + body + '</w:body></w:document>')


class ReportDocumentsTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.path = Path(self.temp.name) / 'progress.docx'
        self.work = 'Nhóm đã hoàn thành thiết kế cơ sở dữ liệu.'
        self.problem = 'Khó khăn: nhóm chưa kết nối được máy chủ kiểm thử.'
        self.plan = 'Kế hoạch tiếp theo: kiểm thử chức năng đăng ký đề tài.'
        docx_fixture(self.path, ''.join('<w:p><w:r><w:t>' + sentence + '</w:t></w:r></w:p>' for sentence in [self.work, self.problem, self.plan]))

    def tearDown(self):
        self.temp.cleanup()

    def test_docx_preserves_paragraph_sources_without_word_page_claims(self):
        units, total, meta = extract_pdf_pages(str(self.path))
        self.assertEqual(3, total)
        self.assertEqual(['Đoạn 1', 'Đoạn 2', 'Đoạn 3'], [u['source_label'] for u in units])
        self.assertTrue(all(u['source_kind'] == 'docx' for u in units))
        self.assertGreater(meta['total_chars'], 50)

    def test_table_cells_and_wrapped_paragraphs_keep_stable_source(self):
        docx_fixture(self.path, '<w:p><w:r><w:t>Mở đầu</w:t></w:r></w:p><w:tbl><w:tr><w:tc><w:p><w:r><w:t>Công việc</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đã thiết kế bảng users</w:t></w:r></w:p></w:tc></w:tr></w:tbl>')
        units, _, _ = extract_docx_units(self.path)
        self.assertEqual('Bảng 1', units[1]['source_label'])
        self.assertIn('Công việc | Đã thiết kế bảng users', units[1]['text'])

    def test_preview_matches_correct_unit_and_returns_plain_text_not_html(self):
        preview = preview_docx(self.path, 2, self.problem)
        self.assertTrue(preview['matched'])
        self.assertEqual('Đoạn 2', preview['label'])
        self.assertEqual(self.problem, preview['text'][preview['highlight_start']:preview['highlight_end']])
        self.assertFalse(preview_docx(self.path, 1, self.problem)['matched'])

    def test_non_bmp_characters_before_quote_preserve_codepoint_offsets(self):
        docx_fixture(self.path, '<w:p><w:r><w:t>📘 ' + self.work + '</w:t></w:r></w:p>')
        preview = preview_docx(self.path, 1, self.work)
        self.assertEqual(2, preview['highlight_start'])
        self.assertEqual(self.work, preview['text'][preview['highlight_start']:preview['highlight_end']])

    def test_source_file_does_not_change_when_previewing(self):
        before = self.path.read_bytes()
        preview_docx(self.path, 1, self.work)
        self.assertEqual(before, self.path.read_bytes())

    def test_out_of_bounds_unit_is_rejected(self):
        with self.assertRaises(ValueError): preview_docx(self.path, 99, self.work)

    def test_xml_entity_declarations_are_rejected(self):
        with zipfile.ZipFile(self.path, 'w') as archive:
            archive.writestr('word/document.xml', '<!DOCTYPE test [<!ENTITY x "boom">]><test/>')
        with self.assertRaises(ValueError): extract_docx_units(self.path)

    def test_three_groups_and_source_locations_survive_pipeline(self):
        payload = {'completed_work':[{'content':'Đã thiết kế CSDL','evidence':self.work,'source_pages':[1]}],
                   'difficulties':[{'content':'Chưa kết nối máy chủ','evidence':self.problem,'source_pages':[2]}],
                   'next_steps':[{'content':'Kiểm thử đăng ký','evidence':self.plan,'source_pages':[3]}],
                   'tom_tat':'Nhóm đã thiết kế CSDL, còn khó khăn kết nối máy chủ. Kế hoạch là kiểm thử đăng ký.'}
        with patch('ai_summarizer.call_gemini_api', return_value=(payload,'test')):
            result = run_map_reduce_pipeline(str(self.path),api_key='test')
        data = result['data']
        self.assertEqual('docx', data['document_type'])
        self.assertEqual(0, data['analysis']['total_pages'])
        self.assertEqual(3, data['analysis']['total_units'])
        self.assertEqual({'id':3,'label':'Đoạn 3','kind':'docx'}, data['next_steps'][0]['source_locations'][0])
        self.assertEqual(3, data['schema_version'])
        self.assertNotIn('deliverables',data)

    def test_empty_groups_are_preserved_as_arrays(self):
        payload = {'completed_work':[{'content':'Đã thiết kế','evidence':self.work,'source_pages':[1]}], 'difficulties':[], 'next_steps':[], 'tom_tat':'Nhóm đã thiết kế CSDL.'}
        docx_fixture(self.path,'<w:p><w:r><w:t>'+self.work+'</w:t></w:r></w:p>')
        with patch('ai_summarizer.call_gemini_api', return_value=(payload,'test')):
            data=run_map_reduce_pipeline(str(self.path),api_key='test')['data']
        self.assertEqual([],data['difficulties'])
        self.assertEqual([],data['next_steps'])


if __name__ == '__main__': unittest.main()
