"""Explainable heuristics, not calibrated probabilities or semantic understanding."""
import re
import statistics
import unicodedata
from collections import defaultdict


def folded(text):
    normalized = unicodedata.normalize('NFKC', text).casefold()
    plain = ''.join(c for c in unicodedata.normalize('NFD', normalized) if unicodedata.category(c) != 'Mn').replace('đ', 'd')
    return re.sub(r'\s+', ' ', plain).strip()


CHAPTER = re.compile(r'^(?:chương|chapter)\s+(\d+|[IVXLCDM]+)\b[.\s:–—-]*(.*)$', re.I)
NUMBERED = re.compile(r'^(\d+(?:\.\d+){1,7})\.?[\s:–—-]+(.+)$')
SINGLE = re.compile(r'^(\d{1,3})[.)]\s+([^\W\d_].*)$', re.UNICODE)
SPECIAL = {'mo dau', 'ket luan', 'tai lieu tham khao', 'phu luc', 'loi cam on', 'muc luc', 'danh muc hinh', 'danh muc bang',
           'introduction', 'conclusion', 'references', 'appendix', 'contents', 'table of contents',
           'loi mo dau', 'loi cam doan', 'nhan xet cua giang vien huong dan', 'nhan xet cua giao vien huong dan',
           'nhan xet cua giang vien phan bien', 'danh muc cac ky hieu va chu viet tat', 'danh muc chu viet tat',
           'danh muc tu viet tat', 'danh muc hinh anh', 'danh muc bang bieu'}


ABSTRACT_HEADINGS = {'tom tat', 'tom tat khoa luan', 'tom tat luan van', 'tom tat do an',
                     'tom tat bao cao', 'abstract', 'thesis abstract'}
SPECIAL.update(ABSTRACT_HEADINGS)


def is_abstract_heading(text):
    title = folded(text).rstrip(' :')
    # A parenthesized language subtitle may be merged with the source heading.
    base = re.sub(r'\s+\([^()]+\)$', '', title)
    return base in ABSTRACT_HEADINGS


def is_special_heading(text):
    title = folded(text).rstrip(' :')
    return title.replace(' ', '') == 'modau' or title in SPECIAL or is_abstract_heading(text) or bool(re.match(r'^phu luc\s+[a-z0-9]', title))


def toc_entry(text):
    """Dot leaders alone also occur on blank assessment forms; require an actual entry."""
    return bool(re.search(r'\.{3,}|…{2,}', text)
                and re.search(r'\s(?:\d{1,4}|[ivxlcdmIVXLCDM]{1,8})\s*$', text)
                and re.search(r'[^\W\d_]', re.split(r'\.{3,}|…{2,}', text)[0], re.UNICODE))


def chapter_preview_starts(lines, toc_pages):
    """Recognize a compact chapter list repeated as headings across the body."""
    chapters = []
    for row in lines:
        text = row['text'].strip()
        match = CHAPTER.match(text)
        if not match or row['running_margin'] or row['page'] in toc_pages or toc_entry(text):
            continue
        title = re.sub(r'[^a-z0-9]+', '', folded(match.group(2)))
        if title:
            chapters.append((row, (match.group(1).casefold(), title)))

    previews = set()
    for i, (first, key) in enumerate(chapters):
        if first['start'] in previews:
            continue
        for j in range(i + 2, len(chapters) - 1):
            body, body_key = chapters[j]
            if body_key != key:
                continue
            length = 0
            while (i + length < j and j + length < len(chapters)
                   and chapters[i + length][1] == chapters[j + length][1]
                   and chapters[i + length][0]['page'] - first['page'] <= 2):
                length += 1
            if length < 2:
                continue
            early_end = chapters[i + length - 1][0]
            body_end = chapters[j + length - 1][0]
            if body['page'] >= early_end['page'] and body_end['page'] - body['page'] > 2:
                previews.update(row['start'] for row, _ in chapters[i:i + length])
                break
    return previews


def detect_headings(document, settings):
    lines = document['lines']
    sizes = [r['font_size'] for r in lines if len(r['text']) > 65]
    body_size = statistics.median(sizes or [r['font_size'] for r in lines] or [12])
    outline = {(folded(title.strip()), page): level for level, title, page in document['outline']}
    lines_by_page = defaultdict(list)
    for row in lines:
        lines_by_page[row['page']].append(row)
    toc_pages = set()
    for page in document['pages']:
        rows = [r['text'].strip() for r in lines_by_page[page['page']]]
        leaders = sum(toc_entry(t) for t in rows)
        numeric_tails = sum(bool(re.search(r'\s\d{1,4}$', t)) for t in rows)
        titles = any(folded(t) in {'muc luc', 'contents', 'table of contents', 'danh muc hinh', 'danh muc bang'} for t in rows)
        if leaders >= 3 or (titles and numeric_tails >= 3):
            toc_pages.add(page['page'])
    preview_starts = chapter_preview_starts(lines, toc_pages)
    candidates, accepted, consumed = [], [], set()
    in_introduction = False
    current_chapter_num = None
    outline_start = None
    chapter_occurrences = defaultdict(list)
    for row in lines:
        label = row['text'].strip()
        chapter_match = CHAPTER.match(label)
        if chapter_match and chapter_match.group(2).isupper():
            chapter_occurrences[re.sub(r'[^a-z0-9]+', '', folded(label))].append(row['start'])
    for i, row in enumerate(lines):
        if row['line_id'] in consumed:
            continue
        text = row['text'].strip()
        compact_title = re.sub(r'[^a-z0-9]+', '', folded(text))
        if compact_title == 'modau':
            text = 'MỞ ĐẦU'
        if compact_title in {'bocucbaocao', 'cautrucbaocao', 'bocuckhoaluan', 'cautruckhoaluan'}:
            outline_start = row['page']
        chapter, numbered = CHAPTER.match(text), NUMBERED.match(text)
        special = is_special_heading(text)
        bookmark = outline.get((folded(text), row['page'])) if settings.use_outline else None
        single = SINGLE.match(text) if (in_introduction or bookmark) else None
        strong_font = settings.use_fonts and row['font_size'] > body_size * 1.17
        caption = bool(re.match(r'^(?:hình|bảng|figure|table)\s+\d', text, re.I))
        page_number = bool(re.fullmatch(r'\d{1,4}|[ivxlcdmIVXLCDM]{1,8}', text))
        if not (chapter or numbered or single or special or bookmark or strong_font or caption or page_number or row['running_margin']):
            continue
        number = chapter.group(1) if chapter else numbered.group(1) if numbered else single.group(1) if single else None
        level = 1 if chapter or special else len(number.split('.')) + (1 if in_introduction else 0) if numbered else 2 if single and in_introduction else bookmark or 2
        kind = 'chapter' if chapter else 'section' if numbered or single else 'special' if special else 'outline' if bookmark else 'typographic'
        score, signals, rejected = 0., [], []
        if chapter:
            score += 4; signals.append('chapter_number')
        elif numbered:
            score += 2.7; signals.append('hierarchical_number')
        elif single:
            score += 2.7; signals.append('single_number_in_intro_or_outline')
        elif special:
            score += 3.7; signals.append('named_front_or_back_matter')
        if bookmark:
            score += 2; signals.append('pdf_outline')
        if settings.use_fonts and row['font_size'] > body_size * 1.12:
            score += 1.1; signals.append('larger_font')
        if settings.use_fonts and row['bold']:
            score += .8; signals.append('bold')
        prev = lines[i - 1] if i else None
        if not prev or prev['page'] != row['page'] or row['bbox'][1] - prev['bbox'][3] > body_size * .45:
            score += .45; signals.append('vertical_gap_or_page_start')
        if row['bbox'][0] < row['page_width'] * .3:
            score += .25; signals.append('left_alignment')
        if numbered and current_chapter_num and number.split('.')[0] == current_chapter_num:
            score += .45; signals.append('known_chapter')
        if len(text) > 200:
            rejected.append('Dòng quá dài, có khả năng là nội dung đoạn văn.')
        if chapter and re.search(r'\b(?:da\s*trinh\s*bay|trinh\s*bay|bao\s*gom|dong\s*vai\s*tro|se\s*trinh\s*bay|tong\s*ket\s*nhung)\b', folded(text)):
            rejected.append('Câu giới thiệu/kết chương, không phải tiêu đề chương.')
        if chapter and len(text) > 65 and not chapter.group(2).isupper() and re.search(r'\b(?:khao\s*sat\s*va|phan\s*tich\s*yeu\s*cau|thiet\s*ke\s*kien\s*truc)\b', folded(chapter.group(2))):
            rejected.append('Mô tả chương trong danh sách cấu trúc đề tài.')
        if chapter and chapter.group(2):
            g2_lower = chapter.group(2).casefold().strip()
            if re.match(r'^(?:và|va|được|duoc|đã|da|trong|ở|có|là|la|này|nay|sẽ|se|nhằm|nham|với|voi|cho|do|bởi|boi|theo|tại|tai|như|nhu)\b', g2_lower):
                rejected.append('Sau từ Chương là từ nối/giới từ/vị ngữ, là câu văn trong đoạn chứ không phải tiêu đề chương.')
        if chapter and chapter.group(2) and re.search(r'\.\s+[a-zà-ỹA-ZÀ-Ỹ]', chapter.group(2)):
            rejected.append('Tiêu đề chứa dấu chấm ngắt câu văn, là nội dung đoạn văn chứ không phải tiêu đề.')
        if chapter and not bookmark:
            if not row['bold'] and not text.isupper() and row['font_size'] <= body_size * 1.08:
                rejected.append('Định dạng chữ thường/không đậm giống nội dung văn bản, không phải tiêu đề chương.')
            num_str = chapter.group(1)
            if num_str.isdigit():
                ch_val = int(num_str)
                accepted_ch_vals = [int(h['section_number']) for h in accepted if h['kind'] == 'chapter' and h['section_number'] and h['section_number'].isdigit()]
                if accepted_ch_vals and ch_val < max(accepted_ch_vals):
                    if not (row['bold'] and (row['font_size'] > body_size * 1.15 or text.isupper())):
                        rejected.append(f'Số chương ({ch_val}) nhỏ hơn chương đã xuất hiện trước đó ({max(accepted_ch_vals)}), có khả năng là câu tham chiếu ngược.')
        # CHECKS FOR NUMBERED SECTIONS
        if numbered and numbered.group(2):
            title_part = numbered.group(2).strip()
            if title_part and title_part[0].islower() and not bookmark:
                rejected.append('Tiêu đề mục bắt đầu bằng chữ thường, có khả năng là câu văn trong đoạn.')
            g2_lower = title_part.casefold()
            is_subordinating = bool(re.match(r'^(?:thay|va|và|duoc|được|da|đã|trong|o|ở|co|có|la|là|nay|này|se|sẽ|nham|nhằm|voi|với|cho|do|boi|bởi|tai|tại|nhu|như|khi|de|để|neu|nếu|vi|vì)\b', g2_lower))
            if is_subordinating or (re.match(r'^theo\b', g2_lower) and not re.match(r'^theo\s+(?:dõi|doi|sát|sat)\b', g2_lower)):
                rejected.append('Sau số mục là từ nối/giới từ/vị ngữ, là câu văn trong đoạn chứ không phải tiêu đề mục.')
        if numbered and not bookmark:
            if not row['bold'] and not text.isupper() and row['font_size'] <= body_size * 1.08:
                rejected.append('Định dạng chữ thường/không đậm giống nội dung văn bản, không phải tiêu đề mục.')
            if current_chapter_num:
                major_num = number.split('.')[0]
                if major_num != current_chapter_num and not (row['bold'] and row['font_size'] > body_size * 1.15):
                    rejected.append(f'Số mục {number} không thuộc phạm vi chương hiện tại ({current_chapter_num}).')

        # CHECKS FOR SPECIAL / FRONT-MATTER HEADINGS
        if special and not bookmark:
            # Uppercase text in a table cell is not a document-level heading.
            # Keep isolated headings and distinctive large headings unchanged.
            if row['font_size'] <= body_size * 1.08:
                x0,y0,x1,y1 = row['bbox']
                column_peers = [other for other in lines_by_page[row['page']]
                    if other['line_id'] != row['line_id'] and len(other['text'].strip()) > 6
                    and abs((other['bbox'][1]+other['bbox'][3])/2-(y0+y1)/2) <= min(y1-y0,other['bbox'][3]-other['bbox'][1])*.45
                    and (other['bbox'][0] > x1+body_size*.5 or other['bbox'][2] < x0-body_size*.5)]
                if column_peers:
                    rejected.append('Tên mục nằm cùng hàng với nội dung cột khác, có dấu hiệu là ô bảng, không phải tiêu đề độc lập.')
            if is_abstract_heading(text) or folded(text) in {'loi mo dau', 'loi cam on', 'loi cam doan', 'nhan xet cua giang vien huong dan', 'nhan xet cua giao vien huong dan'}:
                if current_chapter_num is not None or row['page'] > 20:
                    rejected.append('Mục front-matter (tóm tắt/lời cảm ơn) chỉ xuất hiện ở đầu tài liệu, không xuất hiện trong thân bài/bảng.')
            if not row['bold'] and not text.isupper() and row['font_size'] <= body_size * 1.08:
                rejected.append('Định dạng chữ thường/không đậm giống nội dung văn bản, không phải tiêu đề đặc biệt.')

        if row['start'] in preview_starts:
            rejected.append('Danh sách giới thiệu chương; các tiêu đề tương ứng xuất hiện trong thân bài.')
        if chapter and outline_start is not None and row['page'] - outline_start <= 4 and not chapter.group(2).isupper():
            if any(start > row['start'] for start in chapter_occurrences.get(compact_title, [])):
                rejected.append('Dòng giới thiệu bố cục; chương có tiêu đề chính ở phần nội dung.')
        if caption:
            rejected.append('Chú thích hình/bảng, không phải heading.')
        if page_number:
            rejected.append('Số trang đứng riêng.')
        if row['running_margin']:
            rejected.append('Header/footer lặp lại ở lề trang.')
        if single and not (bookmark or (row['bold'] and single.group(2).isupper())):
            rejected.append('Số đơn thiếu tín hiệu tiêu đề; có thể là danh sách thường.')
        index_label = folded(text) in {'muc luc', 'contents', 'table of contents'} or folded(text).startswith('danh muc ')
        if row['page'] in toc_pages and not (special and index_label):
            rejected.append('Trang mục lục/danh mục: giữ văn bản, không dựng heading từ dòng này.')
        if re.search(r'\.{3,}|…{2,}', text):
            rejected.append('Dòng có dấu dẫn mục lục.')
        # A sentence, version/IP-like string or formula must not become a heading based on regex alone.
        if numbered and (number.count('.') > 4 or text.endswith((';', '=', '+'))):
            rejected.append('Dạng công thức/danh sách không đủ tín hiệu.')
        if kind == 'typographic' and not (row['bold'] and strong_font):
            rejected.append('Kiểu chữ riêng lẻ chưa đủ xác định tiêu đề.')
        accepted_flag = not rejected and score >= settings.heading_threshold
        merged_ids = [row['line_id']]
        title = text
        # Only merge a close, equally styled uppercase continuation; never swallow a numbered next heading.
        if accepted_flag:
            k = i + 1
            while k < len(lines):
                nxt = lines[k]; nt = nxt['text'].strip()
                if nxt['page'] != row['page']:
                    break
                prev_line = lines[k - 1]
                close = 0 <= nxt['bbox'][1] - prev_line['bbox'][3] < body_size * .85
                same_row = (abs(nxt['bbox'][1] - prev_line['bbox'][1]) < 1.5
                            and nxt['bbox'][0] >= prev_line['bbox'][2])
                same_style = abs(nxt['font_size'] - row['font_size']) < .5 and nxt['bold'] == row['bold']
                if not (close or same_row) or not same_style:
                    break
                if (CHAPTER.match(nt) or NUMBERED.match(nt) or is_special_heading(nt)
                    or re.match(r'^(?:hình|bảng|figure|table)\s+\d', nt, re.I)
                    or re.match(r'^[-–—•*]\s+', nt)
                    or len(nt) >= 120):
                    break
                valid_continuation = (nt.isupper()
                                      or same_row
                                      or (row['bold'] and nxt['bold'] and not nt.endswith(('.', ':', ';'))))
                if not valid_continuation:
                    break
                title += ' ' + nt
                merged_ids.append(nxt['line_id'])
                consumed.add(nxt['line_id'])
                signals.append('same_row_fragment' if same_row else 'wrapped_heading')
                k += 1
        candidate = {'heading_id': f'h{len(candidates) + 1:05}', 'title': title, 'section_number': number,
                     'level': level, 'kind': kind, 'page': row['page'], 'page_index': row['page_index'],
                     'start': row['start'], 'line_ids': merged_ids, 'bbox': row['bbox'],
                     'font_size': row['font_size'], 'bold': row['bold'], 'regex_match': bool(chapter or numbered or single),
                     'numbering_scope': 'introduction' if in_introduction and not chapter and not special else 'document',
                     'score': round(score, 2), 'accepted': accepted_flag, 'signals': signals,
                     'reason': '; '.join(rejected) if rejected else 'Đủ ngưỡng heuristic.' if accepted_flag else 'Điểm thấp hơn ngưỡng.',
                     'method': 'regex + layout + outline', 'warning': accepted_flag and score < settings.heading_threshold + .5}
        candidates.append(candidate)
        if accepted_flag:
            accepted.append(candidate)
            if chapter:
                outline_start = None
                current_chapter_num = number
            elif special:
                if folded(text) in {'ket luan', 'tai lieu tham khao', 'phu luc', 'conclusion', 'references', 'appendix'} or bool(re.match(r'^phu luc\s+[a-z0-9]', folded(text))):
                    current_chapter_num = None
                in_introduction = folded(text) in {'mo dau', 'loi mo dau', 'introduction'}
    return {'accepted': accepted, 'candidates': candidates, 'body_font_size': body_size,
            'toc_pages': sorted(toc_pages), 'heading_threshold': settings.heading_threshold,
            'score_kind': 'heuristic_not_probability'}
