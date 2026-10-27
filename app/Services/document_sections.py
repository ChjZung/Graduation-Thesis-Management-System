"""Physical-page section tree and bounded, source-preserving semantic chunks.

No API dependency. Offsets always address the extracted page text, never a prompt
with artificial page labels. Parent content is owned once, not copied to children.
"""
import collections
import math
import re
import statistics
import time
import unicodedata
import bisect

NONSPACE = re.compile(r'\S')


def fold(text):
    return re.sub(r'\s+', ' ', unicodedata.normalize('NFKC', text)).strip().casefold()


def estimate_tokens(text):
    # Deliberately conservative for Vietnamese. NOT Gemini's countTokens tokenizer.
    return math.ceil(len(text.encode('utf-8')) / 3)


def heading(text):
    text = text.strip()
    if len(text) > 180 or re.search(r'\.{3,}|…|\s\d+\s*$', text):
        return None
    if re.match(r'^(bảng|hình|biểu đồ|figure|table|công thức)\s*\d', text, re.I):
        return None
    match = re.match(r'^(?:chương|chapter|phần)\s*(\d+|[IVXLC]+)\s*[:.\-–]?\s*(.*)$', text, re.I)
    if match:
        number, title = match.groups()
        # Narrative roadmap/recap is prose, including the frequent "Chương 1: ... sẽ ...".
        if re.search(r'\b(trình bày|đề cập|đã trình|sẽ trình|bao gồm|giới thiệu về|presents|will discuss)\b', title, re.I):
            return None
        return number, title.strip(), 1, 'chapter'
    match = re.match(r'^(\d+(?:\.\d+){1,5})[.)]?\s*([^\d\W].*)$', text, re.U)
    if match:
        number, title = match.groups()
        if any(len(part) > 3 for part in number.split('.')) or len(title) < 2 or re.search(r'[=<>]|\s\+\s', title):
            return None
        return number, title.strip(), number.count('.') + 1, 'section'
    if fold(text).strip(':') in ('mở đầu', 'lời mở đầu', 'kết luận', 'tài liệu tham khảo',
                                  'phụ lục', 'introduction', 'conclusion', 'references', 'appendix'):
        return None, text, 1, 'unnumbered'
    return None


def page_lines(page):
    """Attach available layout signals to stable text offsets."""
    layout = page.get('lines', [])
    cursor = 0
    result = []
    for index, raw in enumerate(page['text'].splitlines(keepends=True)):
        text = raw.rstrip('\r\n')
        hints = layout[index] if index < len(layout) and layout[index].get('text') == text else {}
        result.append(dict(hints, text=text, start=cursor, end=cursor + len(raw), page=page['page']))
        cursor += len(raw)
    return result


def build_section_tree(pages):
    lines = [line for page in pages for line in page_lines(page)]
    sizes = [l['size'] for l in lines if len(l['text']) > 50 and l.get('size')]
    body_size = statistics.median(sizes) if sizes else 0
    # Repeated marginal lines are excluded only with positional evidence.
    repeated = collections.Counter(fold(l['text']) for l in lines if l.get('marginal'))
    by_page = {p['page']: p for p in pages}
    index_pages = set()
    for page in pages:
        text = page['text']
        leaders = len(re.findall(r'\.{3,}|…{2,}', text))
        contents_label = re.sub(r'\s+', '', fold(text[:180]))
        trailing_pages = sum(bool(re.search(r'\s\d+\s*$', line)) for line in text.splitlines())
        if ('mụclục' in contents_label or 'tableofcontents' in contents_label or trailing_pages >= 7
                or leaders >= 3):
            index_pages.add(page['page'])
    nodes = []
    stack = []
    consumed = set()
    current = None
    ignored = []
    for i, line in enumerate(lines):
        if i in consumed:
            continue
        text, page = line['text'], by_page[line['page']]
        marginal = line.get('marginal') and repeated[fold(text)] >= max(2, len(pages) // 4)
        if marginal or re.fullmatch(r'\s*(?:Trang\s*)?\d+\s*', text, re.I):
            ignored.append({'page': line['page'], 'start': line['start'], 'end': line['end'], 'reason': 'page_furniture'})
            continue
        if (re.fullmatch(r'\s*\d+(?:\.\d+){1,5}[.)]?\s*', text) and i + 1 < len(lines)
                and line['page'] not in index_pages):
            nxt = lines[i + 1]
            same_row = (not line.get('bbox') or not nxt.get('bbox') or abs(line['bbox'][1] - nxt['bbox'][1]) < 3)
            if nxt['page'] == line['page'] and same_row and (nxt.get('bold') or not page.get('lines')):
                text = text.rstrip('.) ') + '. ' + nxt['text'].strip()
                line = dict(line, end=nxt['end'])
                consumed.add(i + 1)
        candidate = heading(text) if line['page'] not in index_pages else None
        is_outline = any(fold(text) == fold(h.get('title', '')) for h in page.get('outline', []))
        style = bool(line.get('bold') or body_size and line.get('size', 0) >= body_size + .5 or is_outline)
        # Layout-less sources can use conservative short numbered headings.
        if candidate and page.get('lines') and not style:
            candidate = None
        if candidate and candidate[3] == 'chapter' and not candidate[1] and i + 1 < len(lines):
            nxt = lines[i + 1]
            next_heading = heading(nxt['text'])
            if (nxt['page'] == line['page'] and 2 < len(nxt['text'].strip()) < 140
                    and (not next_heading or next_heading[3] == 'unnumbered')):
                if nxt.get('bold') or nxt['text'].isupper() or not page.get('lines'):
                    candidate = (candidate[0], nxt['text'].strip(), 1, 'chapter')
                    line = dict(line, end=nxt['end'])
                    consumed.add(i + 1)
        elif candidate and i + 1 < len(lines) and line.get('block_index') is not None:
            next_index = i + 2 if i + 1 in consumed else i + 1
            nxt = lines[next_index] if next_index < len(lines) else {}
            if (nxt.get('page') == line['page'] and nxt.get('block_index') == line.get('block_index')
                    and nxt.get('bold') and not heading(nxt['text'])
                    and abs(nxt.get('size', 0) - line.get('size', 0)) < .5
                    and len(candidate[1] + nxt['text']) <= 160):
                candidate = (candidate[0], candidate[1] + ' ' + nxt['text'].strip(), candidate[2], candidate[3])
                line = dict(line, end=nxt['end'])
                consumed.add(next_index)
        if candidate:
            number, title, level, kind = candidate
            # A numbered section must belong to the current chapter, not a table row.
            if kind == 'section' and nodes:
                chapter = next((n for n in reversed(nodes) if n['level'] == 1 and n['number']), None)
                if chapter and str(chapter['number']).isdigit() and not number.startswith(str(chapter['number']) + '.'):
                    candidate = None
            if candidate:
                while stack and (stack[-1]['level'] >= level or kind == 'section'
                                 and stack[-1]['number'] and not number.startswith(str(stack[-1]['number']) + '.')):
                    stack.pop()
                parent = stack[-1] if stack else None
                current = {'id': f'S{len(nodes) + 1}', 'number': number, 'title': title or text.strip(),
                           'level': level, 'kind': kind, 'parent_id': parent['id'] if parent else None,
                           'start_page': line['page'], 'end_page': line['page'], 'order_index': len(nodes),
                           'heading': {'page': line['page'], 'start': line['start'], 'end': line['end']},
                           'source_spans': [], 'content': ''}
                nodes.append(current)
                stack.append(current)
                continue
        if current is None:
            current = {'id': 'S1', 'number': None, 'title': 'Phần đầu tài liệu', 'level': 1,
                       'kind': 'front_matter', 'parent_id': None, 'start_page': line['page'],
                       'end_page': line['page'], 'order_index': 0, 'source_spans': [], 'content': ''}
            nodes.append(current)
            stack = [current]
        current['source_spans'].append({'page': line['page'], 'start': line['start'], 'end': line['end'],
                                        'text': page['text'][line['start']:line['end']]})
        current['end_page'] = line['page']
    # An ancestor's page range includes descendants, but its own content does not.
    lookup = {n['id']: n for n in nodes}
    for node in reversed(nodes):
        node['content'] = ''.join(s['text'] for s in node['source_spans'])
        if node['parent_id']:
            parent = lookup[node['parent_id']]
            parent['end_page'] = max(parent['end_page'], node['end_page'])
    detected = sum(n['kind'] in ('chapter', 'section', 'unnumbered') for n in nodes)
    if not detected:
        # Page/paragraph fallback is explicit; these are source locations, not invented chapters.
        spans = [span for node in nodes for span in node['source_spans']]
        nodes = []
        for page in pages:
            own = [span for span in spans if span['page'] == page['page']]
            if not own:
                continue
            nodes.append({'id':f"P{page['page']}", 'number':None,
                          'title':page.get('source_label', f"Trang {page['page']}"), 'level':1,
                          'kind':'fallback', 'parent_id':None, 'start_page':page['page'], 'end_page':page['page'],
                          'order_index':len(nodes), 'source_spans':own, 'content':''.join(s['text'] for s in own)})
    return nodes, {'sections_detected': detected, 'structure_fallback': not bool(detected),
                   'index_pages': sorted(index_pages), 'ignored_spans': ignored}


def section_chunks(nodes, target_tokens=2500, overlap_tokens=200, document_id=None, version_id=None,
                   prompt_reserve_tokens=1500, max_input_tokens=12000):
    """Sentence/paragraph boundaries preferred; even a single long line is bounded."""
    if target_tokens < 64 or overlap_tokens < 0 or overlap_tokens >= target_tokens:
        raise ValueError('invalid_chunk_token_budget')
    budget = min(target_tokens, max_input_tokens - prompt_reserve_tokens)
    if budget <= overlap_tokens:
        raise ValueError('insufficient_prompt_budget')
    lookup = {n['id']: n for n in nodes}
    chunks = []
    for node in nodes:
        text = node['content']
        positions, cursor = [], 0
        for span in node['source_spans']:
            positions.append((cursor, cursor + len(span['text']), span))
            cursor += len(span['text'])
        span_ends = [stop for _, stop, _ in positions]
        offset, local = 0, []
        while offset < len(text):
            nonspace = NONSPACE.search(text, offset)
            if not nonspace:
                break
            low, high = offset + 1, min(len(text), offset + budget * 3)
            while low < high:
                mid = (low + high + 1) // 2
                if estimate_tokens(text[offset:mid]) <= budget:
                    low = mid
                else:
                    high = mid - 1
            end = low
            if end < len(text):
                bounds = [m.end() + offset for m in re.finditer(r'\n\s*\n|(?<=[.!?])\s+|\n', text[offset:end])]
                candidates = [b for b in bounds if b >= offset + (end - offset) * .55]
                if candidates:
                    end = candidates[-1]
            source_spans = []
            first_span = bisect.bisect_right(span_ends, offset)
            for span_index in range(first_span, len(positions)):
                start, stop, span = positions[span_index]
                if start >= end:
                    break
                a, b = max(offset, start), min(end, stop)
                if b > a:
                    source_spans.append({'page': span['page'], 'start': span['start'] + a - start,
                                         'end': span['start'] + b - start, 'text': text[a:b]})
            root = node
            while root['parent_id']:
                root = lookup[root['parent_id']]
            page_numbers = sorted({s['page'] for s in source_spans})
            prompt_parts, last_page = [], None
            for span in source_spans:
                if span['page'] != last_page:
                    prompt_parts.append(f"\n[Trang {span['page']}]\n")
                    last_page = span['page']
                prompt_parts.append(span['text'])
            local.append({'document_id': document_id, 'document_version_id': version_id,
                          'chapter_id': root['id'], 'section_id': node['id'], 'section': node['title'],
                          'chapter_number': root['number'], 'section_title': node['title'],
                          'section_number': node['number'], 'source_spans': source_spans,
                          'pages': page_numbers, 'start_page': min(page_numbers), 'end_page': max(page_numbers),
                          'text': ''.join(prompt_parts),
                          'content': text[offset:end], 'estimated_tokens': estimate_tokens(text[offset:end]),
                          'token_count_method': 'conservative_utf8_estimate', 'prompt_reserve_tokens': prompt_reserve_tokens,
                          'section_start': offset, 'section_end': end})
            if end == len(text):
                break
            start_overlap = end
            while start_overlap > offset and estimate_tokens(text[start_overlap - 1:end]) <= overlap_tokens:
                start_overlap -= 1
            offset = max(offset + 1, start_overlap)
        for index, chunk in enumerate(local, 1):
            chunk.update(chunk_index=index, section_chunks_total=len(local), chunk_id=f"{node['id']}_C{index}")
        chunks.extend(local)
    for chunk in chunks:
        chunk['chunks_total'] = len(chunks)
    return chunks


def coverage_report(pages, nodes, chunks, metadata):
    """Audit exact source offsets, disjoint section ownership and chunk coverage."""
    page_text = {p['page']: p['text'] for p in pages}
    ownership = {p: bytearray(len(t)) for p, t in page_text.items()}
    chunked = {p: bytearray(len(t)) for p, t in page_text.items()}
    exclusions = {p: bytearray(len(t)) for p, t in page_text.items()}
    for span in metadata.get('ignored_spans', []) + [n['heading'] for n in nodes if n.get('heading')]:
        exclusions[span['page']][span['start']:span['end']] = b'\1' * (span['end'] - span['start'])
    for node in nodes:
        for span in node['source_spans']:
            if page_text[span['page']][span['start']:span['end']] != span['text']:
                raise ValueError('section_source_offset_mismatch')
            for i in range(span['start'], span['end']):
                ownership[span['page']][i] += 1
    for chunk in chunks:
        for span in chunk['source_spans']:
            if page_text[span['page']][span['start']:span['end']] != span['text']:
                raise ValueError('chunk_source_offset_mismatch')
            chunked[span['page']][span['start']:span['end']] = b'\1' * (span['end'] - span['start'])
    missing, duplicate = 0, 0
    for page, text in page_text.items():
        for i, char in enumerate(text):
            if not char.isspace() and not exclusions[page][i]:
                missing += not bool(ownership[page][i] and chunked[page][i])
                duplicate += ownership[page][i] > 1
    return {'uncovered_characters': missing, 'duplicate_owned_characters': duplicate,
            'source_offsets_verified': missing == 0 and duplicate == 0}
