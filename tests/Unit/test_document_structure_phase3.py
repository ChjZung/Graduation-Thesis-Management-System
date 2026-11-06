"""Offline DOCX/PDF structure and locator regression fixtures."""
import ast
from pathlib import Path
import sys
import tempfile
import unittest
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[2] / 'app' / 'Services'
sys.path[:0] = [str(ROOT), str(ROOT / 'pdf_lab')]
import pymupdf
from ai_source_navigation import from_pdf_lab, from_units
from core import segment_pdf
from document_sections import build_section_tree, coverage_report, section_chunks
from report_document import extract_docx_units, preview_docx


class DocumentStructureTest(unittest.TestCase):
    def test_docx_styles_numbering_table_caption_and_unchanged_unit_ids(self):
        word_ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
        paragraphs = '''
        <w:p><w:pPr><w:pStyle w:val="TOC1"/></w:pPr><w:r><w:t>Chương 1: Thiết kế</w:t></w:r></w:p>
        <w:p><w:pPr><w:pStyle w:val="ProjectChapter"/></w:pPr><w:r><w:t>Chương 1: Thiết kế</w:t></w:r></w:p>
        <w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>Thiết kế hệ thống</w:t></w:r></w:p>
        <w:p><w:pPr><w:outlineLvl w:val="2"/></w:pPr><w:r><w:t>Mô hình dữ liệu</w:t></w:r></w:p>
        <w:tbl><w:tr><w:tc><w:p><w:r><w:t>1.1.2 Tên trường dữ liệu</w:t></w:r></w:p></w:tc></w:tr></w:tbl>
        <w:p><w:pPr><w:pStyle w:val="Caption"/></w:pPr><w:r><w:t>Sơ đồ thực thể liên kết</w:t></w:r></w:p>
        <w:p><w:r><w:t>1.2 Mô tả chức năng</w:t></w:r></w:p>
        <w:p><w:r><w:t>Nội dung mô tả chức năng đăng nhập.</w:t></w:r></w:p>
        <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="34"/></w:rPr><w:t>Khảo sát bổ sung</w:t></w:r></w:p>'''
        styles = f'<w:styles xmlns:w="{word_ns}"><w:style w:styleId="ProjectChapter"><w:basedOn w:val="Heading1"/></w:style></w:styles>'
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / 'report.docx'
            with ZipFile(path, 'w') as archive:
                archive.writestr('word/document.xml', f'<w:document xmlns:w="{word_ns}"><w:body>{paragraphs}</w:body></w:document>')
                archive.writestr('word/styles.xml', styles)
            units, total, _ = extract_docx_units(path)
            self.assertEqual(total, 9)
            nodes, metadata = build_section_tree(units)
            self.assertEqual([(n['title'], n['level']) for n in nodes], [
                ('Thiết kế', 1), ('Thiết kế hệ thống', 2), ('Mô hình dữ liệu', 3),
                ('Mô tả chức năng', 2), ('Khảo sát bổ sung', 1)])
            self.assertEqual(nodes[2]['parent_id'], nodes[1]['id'])
            self.assertTrue(coverage_report(units, nodes, section_chunks(nodes), metadata)['source_offsets_verified'])
            self.assertEqual(metadata['index_pages'], [1])
            preview = preview_docx(path, 5, 'Tên trường dữ liệu')
            self.assertTrue(preview['matched'])
            self.assertEqual(preview['source_location'], {'kind': 'docx', 'unit': 5, 'table': 1, 'part': 1})
            indexed = from_units(units)
            self.assertTrue(any(a['source_location']['unit'] == 6 for a in indexed['assets']))
            self.assertFalse(any(row['page'] == 1 for row in indexed['rows']))
            self.assertEqual(next(row for row in indexed['rows'] if row['page'] == 8)['source_location']['paragraph'], 7)

    def test_pdf_toc_duplicate_header_table_columns_and_scan(self):
        with pymupdf.open() as pdf:
            toc = pdf.new_page()
            toc.insert_text((60, 80), 'TABLE OF CONTENTS', fontname='hebo')
            for i, line in enumerate(('Chapter 1: Design .... 10', '1.1 Models .... 11', '1.2 Data .... 12')):
                toc.insert_text((60, 130 + i * 20), line)
            page = pdf.new_page()
            page.insert_text((50, 105), 'CHAPTER 1: DESIGN', fontname='hebo', fontsize=16)
            page.insert_text((55, 155), 'Left column alpha')
            page.insert_text((55, 175), 'Left column beta')
            page.insert_text((360, 158), 'Right column alpha')
            page.insert_text((360, 178), 'Right column beta')
            page.insert_text((55, 230), 'Table field and meaning')
            page.insert_text((355, 230), 'Table value and constraint')
            page.insert_text((55, 250), 'Another table field')
            page.insert_text((355, 250), 'Another table value')
            page.insert_text((290, 810), '10')
            scan = pdf.new_page()
            pix = pymupdf.Pixmap(pymupdf.csRGB, (0, 0, 5, 5), False)
            pix.clear_with(255)
            scan.insert_image((40, 40, 520, 700), pixmap=pix)
            for number in range(2):
                pdf[number].insert_text((50, 24), 'Repeated report header')
            pdf.set_page_labels([{'startpage': 0, 'prefix': 'front'}, {'startpage': 1, 'style': 'D', 'firstpagenum': 10}])
            source = segment_pdf(pdf.tobytes())
        pages = source['document']['pages']
        self.assertEqual([p['page'] for p in pages], [1, 2, 3])
        self.assertEqual(pages[2]['state'], 'scan_suspected')
        self.assertTrue(pages[1]['reading_order_requires_review'])
        self.assertEqual(pages[1]['printed_number_candidates'], ['10'])
        headings = [h for h in source['heading_detection']['accepted'] if h['kind'] == 'chapter']
        self.assertEqual([h['page'] for h in headings], [2])
        index = from_pdf_lab(source, [])
        row = next(row for row in index['rows'] if row['quote'] == 'Right column alpha')
        self.assertEqual(row['source_location']['physical_page'], 2)
        self.assertEqual(row['source_location']['pdf_page_label'], '10')
        for line in source['document']['lines']:
            self.assertEqual(source['document']['text'][line['start']:line['end']], line['text'] + '\n')


if __name__ == '__main__':
    unittest.main()
