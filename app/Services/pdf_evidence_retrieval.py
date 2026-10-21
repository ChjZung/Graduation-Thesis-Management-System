"""Retrieve original PDF text with stable evidence IDs; never decide whether a rubric is met."""
import re
import math
from pdf_document_analysis import compact, exact_quote


TABLE_HEADING = re.compile(r'^\s*(?:\d+(?:\.\d+)+\.?\s*)?(?:Bảng|Table)\s+[`"\[]?([A-Za-z_][A-Za-z0-9_]*)', re.I)
TABLE_CAPTION = re.compile(r'^\s*(?:Bảng|Table)\s+\d+(?:\.\d+)+', re.I)
SECTION = re.compile(r'^\s*\d+(?:\.\d+)+\.?\s+\D')


def is_index_text(text):
    first_lines = [compact(line) for line in text.splitlines() if line.strip() and not line.strip().isdigit()][:4]
    return text.count('....') > 3 or any(line in ('mụclục', 'danhmụcbảng', 'danhmụchìnhvẽ', 'danhmụctừviếttắt') for line in first_lines)


def numbered_requirements(text):
    """Ignore a list's introductory instruction, while preserving standalone constraints."""
    markers = list(re.finditer(r'(?<![\w.])\d+[.)]\s+(?=\S)', text))
    if not markers:
        return None
    intro = text[:markers[0].start()].strip()
    is_intro = intro.endswith((':', '：')) and any(term in compact(intro) for term in [
        'cácyêucầu', 'cáctiêuchí', 'từngyêucầu', 'mỗiyêucầu', 'nhữngyêucầu',
        'từngtiêuchí', 'mỗitiêuchí', 'yêucầusau', 'tiêuchísau', 'baogồm', 'trìnhbày'])
    values = [] if not intro or is_intro else [intro]
    for index, marker in enumerate(markers):
        end = markers[index + 1].start() if index + 1 < len(markers) else len(text)
        item = text[marker.end():end].strip()
        if item and item not in values:
            values.append(item)
    return values


def table_passages(pages):
    records = [(int(p['page']), line) for p in pages if not is_index_text(p.get('text', '')) for line in p.get('text', '').splitlines()]
    passages = []
    for index, (number, line) in enumerate(records):
        match = TABLE_HEADING.match(line)
        if not match or re.search(r'\.{3,}', line):
            continue
        groups = {}
        end = index
        while end < len(records):
            page, current = records[end]
            if end > index and (TABLE_HEADING.match(current) or TABLE_CAPTION.match(current) or SECTION.match(current)):
                break
            if current.strip() and not current.strip().isdigit():
                groups.setdefault(page, []).append(current)
            end += 1
        for page, lines in groups.items():
            quote = '\n'.join(lines).strip()
            if len(quote) <= 2600:
                passages.append({'page': page, 'quote': quote, 'table_name': match.group(1).casefold()})
            else:
                # No data is discarded: dense tables keep the same page and table identity.
                passages.extend(dict(p, table_name=match.group(1).casefold()) for p in page_passages([{'page': page, 'text': quote}]))
    return passages


def page_passages(pages, limit=2400):
    passages = []
    for page in pages:
        text = page.get('text', '')
        if is_index_text(text):
            continue
        start = 0
        while start < len(text):
            end = min(start + limit, len(text))
            if end < len(text):
                newline = text.rfind('\n', start + limit // 2, end)
                if newline >= 0:
                    end = newline
            quote = text[start:end].strip()
            if len(compact(quote)) >= 12 and not (quote.count('....') > 3):
                passages.append({'page': int(page['page']), 'quote': quote})
            if end == len(text):
                break
            start = max(start + 1, end - 160)
    return passages


def sentence_passages(pages):
    """Keep exact sentence spans, including wrapped lines, rather than whole pages."""
    passages = []
    for page in pages:
        text = page.get('text', '')
        if is_index_text(text):
            continue
        for match in re.finditer(r'.+?(?:[.!?;](?=\s|$)|\Z)', text, re.S):
            quote = match.group().strip()
            # Long/table fragments remain available through page_passages/table_passages.
            if 12 <= len(compact(quote)) and len(quote) <= 1000:
                passages.append({'page': int(page['page']), 'quote': quote})
    return passages


def section_passages(pages):
    """Keep heading and local body together; headings alone are only pointers."""
    records = []
    heading = ''
    for page in pages:
        text = page.get('text', '')
        if is_index_text(text):
            continue
        known = set(page.get('headings', []))
        lines = text.splitlines(keepends=True)
        start = 0
        offset = 0
        for line in lines:
            value = line.strip()
            is_heading = value in known or bool(SECTION.match(value)) or bool(re.match(r'^chương\s+(?:\d+|[ivxlcdm]+)\b', value, re.I))
            if is_heading:
                body = text[start:offset].strip()
                if body:
                    records.extend(dict(p, section=heading) for p in page_passages([{'page': page['page'], 'text': body}]))
                heading = value
                start = offset
            offset += len(line)
        body = text[start:].strip()
        if body:
            records.extend(dict(p, section=heading) for p in page_passages([{'page': page['page'], 'text': body}]))
    return records


def retrieve_candidates(criteria, pages):
    tables = table_passages(pages)
    passages = page_passages(pages)
    sentences = sentence_passages(pages)
    sections = section_passages(pages)
    by_page = {int(p['page']): p.get('text', '') for p in pages}
    result = {}
    for criterion in criteria:
        requirement = criterion['requirement']
        aliases = []
        for group in re.findall(r'\(([^()]*)\)', requirement):
            aliases.extend(a.casefold().strip(' `"') for a in re.split(r'[/,|]', group) if re.fullmatch(r'[A-Za-z_][A-Za-z0-9_]*', a.strip(' `"')))
        exact_tables = [p for p in tables if p['table_name'] in aliases] if ('bảng' in requirement.casefold() or 'table' in requirement.casefold()) else []
        if exact_tables:
            selected = exact_tables
        else:
            stopwords = {'nêu', 'mô', 'tả', 'trình', 'bày', 'gồm', 'trong', 'báo', 'cáo', 'pdf', 'của', 'các', 'một', 'với', 'và', 'cho', 'được', 'quy', 'định', 'cần', 'phải', 'report', 'with', 'that', 'have', 'the'}
            words = re.findall(r'[\w]+', requirement.casefold())
            terms = set(t for t in words if len(t) >= 2 and t not in stopwords)
            phrases = [compact(' '.join(words[i:i + 3])) for i in range(len(words) - 2)
                       if sum(w not in stopwords for w in words[i:i + 3]) >= 2]
            search = sections + sentences if sections else (sentences or passages)
            frequencies = {term: sum(compact(term) in compact(p.get('text', '')) for p in pages) for term in terms}
            ranked = []
            for passage in search:
                text = compact(passage['quote'])
                score = sum(math.log(1 + len(pages) / (1 + frequencies[term])) for term in terms if compact(term) in text)
                score += sum(4 for phrase in phrases if phrase in text)
                title = compact(passage.get('section', ''))
                title_hits = sum(math.log(1 + len(pages) / (1 + frequencies[t])) for t in terms if compact(t) in title)
                score += 6 * title_hits
                score += sum(12 for phrase in phrases if phrase in title)
                # Introduction/conclusion headings are weak evidence for detailed criteria.
                if any(term in title for term in ('giớithiệuchương', 'kếtluậnchương', 'tómtắtchương')):
                    score *= .2
                # A chapter roadmap describes the report's layout, not its findings.
                if re.search(r'chương\s+\d+\..{0,160}:\s*(?:trình bày|giới thiệu|tổng hợp)', passage['quote'], re.I):
                    score *= .05
                score += sum(100 for alias in aliases if re.search(r'(?<!\w)' + re.escape(alias) + r'(?!\w)', passage['quote'], re.I))
                if score:
                    ranked.append((score, passage))
            selected = []
            for _, passage in sorted(ranked, key=lambda item: (-item[0], len(item[1]['quote']))):
                if not any(p['page'] == passage['page'] and compact(passage['quote']) in compact(p['quote']) for p in selected):
                    selected.append(passage)
                if len(selected) == 4:
                    break
        evidence = []
        for passage in selected:
            quote = exact_quote(passage['quote'], by_page.get(passage['page'], ''))
            item = {'page': passage['page'], 'quote': quote, 'verified': True}
            if quote and item not in evidence:
                evidence.append(item)
        result[criterion['id']] = evidence
    return result
