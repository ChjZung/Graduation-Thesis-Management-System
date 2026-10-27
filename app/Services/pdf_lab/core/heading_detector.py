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
    return title in SPECIAL or is_abstract_heading(text) or bool(re.match(r'^phu luc\s+[a-z0-9]', title))


def toc_entry(text):
    """Dot leaders alone also occur on blank assessment forms; require an actual entry."""
    return bool(re.search(r'\.{3,}|…{2,}', text)
                and re.search(r'\s(?:\d{1,4}|[ivxlcdmIVXLCDM]{1,8})\s*$', text)
                and re.search(r'[^\W\d_]', re.split(r'\.{3,}|…{2,}', text)[0], re.UNICODE))


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
    candidates, accepted, consumed = [], [], set()
    in_introduction = False
    for i, row in enumerate(lines):
        if row['line_id'] in consumed:
            continue
        text = row['text'].strip()
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
        if numbered and any(h['section_number'] == number.split('.')[0] for h in accepted):
            score += .45; signals.append('known_chapter')
        if len(text) > 200:
            rejected.append('Dòng quá dài, có khả năng là nội dung đoạn văn.')
        if chapter and re.search(r'\b(?:da\s*trinh\s*bay|trinh\s*bay|bao\s*gom|dong\s*vai\s*tro|se\s*trinh\s*bay|tong\s*ket\s*nhung)\b', folded(text)):
            rejected.append('Câu giới thiệu/kết chương, không phải tiêu đề chương.')
        if chapter and len(text) > 65 and not chapter.group(2).isupper() and re.search(r'\b(?:khao\s*sat\s*va|phan\s*tich\s*yeu\s*cau|thiet\s*ke\s*kien\s*truc)\b', folded(chapter.group(2))):
            rejected.append('Mô tả chương trong danh sách cấu trúc đề tài.')
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
            if chapter or special:
                in_introduction = special and folded(text) in {'mo dau', 'loi mo dau', 'introduction'}
    return {'accepted': accepted, 'candidates': candidates, 'body_font_size': body_size,
            'toc_pages': sorted(toc_pages), 'heading_threshold': settings.heading_threshold,
            'score_kind': 'heuristic_not_probability'}
