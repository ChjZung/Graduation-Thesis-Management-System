import pathlib
import sys
import unittest

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parents[2] / 'app' / 'Services'))
from document_sections import build_section_tree, section_chunks, coverage_report, estimate_tokens, heading


def page(number, text):
    return {'page': number, 'text': text}


class SectionTreeTest(unittest.TestCase):
    def test_toc_roadmap_captions_and_parent_content(self):
        pages = [page(1, 'MỤC LỤC\nChương 1: Tổng quan .... 2\n1.1. Khảo sát .... 3'),
                 page(2, 'Chương 1:\nTỔNG QUAN\nGiới thiệu trực tiếp.\n1.1. Khảo sát\nKhảo sát hiện trạng.\n1.1.1. Phỏng vấn\nĐã phỏng vấn.\n1.2. Phạm vi\nPhạm vi nghiên cứu.\nBảng 1.2 Danh sách\nChương 2 sẽ trình bày các phương pháp.'),
                 page(3, 'Chương 2: PHƯƠNG PHÁP\n2.1. Phương pháp\nNội dung phương pháp.\nKẾT LUẬN\nTổng kết.')]
        nodes, meta = build_section_tree(pages)
        self.assertEqual([n['number'] for n in nodes], [None, '1', '1.1', '1.1.1', '1.2', '2', '2.1', None])
        self.assertEqual(nodes[3]['parent_id'], nodes[2]['id'])
        self.assertIn('Giới thiệu trực tiếp.', nodes[1]['content'])
        self.assertNotIn('Đã phỏng vấn.', nodes[2]['content'])
        self.assertEqual(nodes[-1]['number'], None)
        chunks = section_chunks(nodes, 200, 20)
        self.assertTrue(coverage_report(pages, nodes, chunks, meta)['source_offsets_verified'])

    def test_long_line_bounded_overlap_order_no_gaps(self):
        pages = [page(1, '1.1. Kết quả\n' + ('Nội dung dài có dấu. ' * 3000)), page(2, 'Nội dung tiếp tục không mất trang.')]
        nodes, meta = build_section_tree(pages)
        chunks = section_chunks(nodes, 2500, 200, 'D', 'V')
        self.assertGreater(len(chunks), 3)
        self.assertTrue(all(c['estimated_tokens'] <= 2500 for c in chunks))
        self.assertEqual(chunks[0]['document_version_id'], 'V')
        self.assertTrue(coverage_report(pages, nodes, chunks, meta)['source_offsets_verified'])
        for previous, current in zip(chunks, chunks[1:]):
            self.assertLess(current['section_start'], previous['section_end'])
            self.assertGreater(current['section_start'], previous['section_start'])
            self.assertLessEqual(estimate_tokens(nodes[0]['content'][current['section_start']:previous['section_end']]), 200)

    def test_fallback_and_invalid_budget(self):
        pages = [page(1, 'Nội dung tài liệu không có tiêu đề. ' * 100)]
        nodes, meta = build_section_tree(pages)
        self.assertTrue(meta['structure_fallback'])
        with self.assertRaises(ValueError):
            section_chunks(nodes, 100, 100)

    def test_non_headings(self):
        for text in ['Hình 2.1: Sơ đồ', 'Bảng 3.2 Dữ liệu', '1.2. Phạm vi ....... 12',
                     'Chương 1 đã trình bày tổng quan.', '3.2. 12', '1.2. x = 5 + 2']:
            self.assertIsNone(heading(text), text)

    def test_split_number_and_wrapped_heading_use_layout_and_keep_source_coverage(self):
        text='Chương 1: TỔNG QUAN\n1.1.\nKHẢO SÁT HIỆN TRẠNG\nVÀ NHU CẦU NGƯỜI DÙNG\nNội dung khảo sát.'
        raw=text.splitlines()
        lines=[{'text':s,'bold':True,'size':14,'block_index':0 if i==0 else 1 if i<=3 else 2,
                'bbox':[50, i*20 if i!=2 else 20, 450, i*20+15]} for i,s in enumerate(raw)]
        lines[1]['bbox']=[50,20,70,35]
        lines[2]['bbox']=[90,20,450,35]
        lines[3]['bbox']=[90,40,450,55]
        lines[4].update(bold=False,size=12)
        pages=[dict(page(1,text),lines=lines)]
        nodes,meta=build_section_tree(pages)
        self.assertEqual('KHẢO SÁT HIỆN TRẠNG VÀ NHU CẦU NGƯỜI DÙNG',nodes[-1]['title'])
        self.assertTrue(coverage_report(pages,nodes,section_chunks(nodes),meta)['source_offsets_verified'])


if __name__ == '__main__':
    unittest.main()
