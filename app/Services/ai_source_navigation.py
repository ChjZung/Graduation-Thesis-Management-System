"""Bounded, local navigation for extracted sources. Matches are unverified."""
import json
import os
import re
import tempfile
import unicodedata
from bisect import bisect_right

VERSION = 'source-navigation-v2'
CAPTION = re.compile(r'^(Hình|Bảng|Figure|Table)\s+\d+(?:[.-]\d+)*[. :–—-]+\S', re.I)
MAX_ENTRIES = 1100


def folded(value):
    return ''.join(c for c in unicodedata.normalize('NFD', value.casefold())
                   if not unicodedata.combining(c)).replace('đ', 'd')


def category(text):
    text = folded(text)
    for name, keywords in (
        ('Use Case', ('use case', 'usecase', 'ca su dung')),
        ('Activity', ('activity', 'hoat dong')),
        ('Sequence', ('sequence', 'tuan tu')),
        ('ERD', ('erd', 'thuc the lien ket')),
        ('Class Diagram', ('class diagram', 'so do lop')),
        ('Database Diagram', ('database diagram', 'so do co so du lieu')),
        ('Kiến trúc', ('kien truc', 'architecture')),
        ('Luồng dữ liệu', ('luong du lieu', 'data flow', 'dfd')),
        ('Mô hình dữ liệu', ('mo hinh du lieu', 'data model')),
        ('Bảng cấu trúc dữ liệu', ('cau truc bang', 'cau truc du lieu', 'dinh nghia bang')),
        ('Bảng kiểm thử', ('kiem thu', 'test case')),
        ('Bảng dữ liệu', ('du lieu', 'database')),
    ):
        if any(word in text for word in keywords):
            return name
    return 'Khác'


def evidence_type(section):
    section = folded(section)
    for label, keywords in (
        ('Kiểm thử', ('kiem thu', 'testing', 'test case')),
        ('Triển khai', ('trien khai', 'cai dat', 'implementation')),
        ('Thiết kế', ('thiet ke', 'mo hinh', 'design')),
        ('Đặc tả', ('dac ta', 'use case')),
        ('Yêu cầu', ('yeu cau', 'requirement')),
        ('Nghiệp vụ', ('nghiep vu', 'business process', 'quy trinh')),
    ):
        if any(word in section for word in keywords):
            return label
    return ''


def make_index(entries, captions=None, locations=None, unit_captions=None):
    locations = locations or {}
    unit_captions = unit_captions or {}
    rows, assets = [], []
    seen = set()
    for page, label, section, text in entries:
        text = text.strip()
        if not text:
            continue
        is_caption = page in unit_captions or bool(CAPTION.match(text))
        if not is_caption and len(text) < 12:
            continue
        context_type = next((kind for part in reversed(section.split(' › ')) if (kind := evidence_type(part))), '')
        # Long blocks remain searchable in consecutive source snippets.
        for offset in range(0, len(text), 440):
            quote = text[offset:offset + 500].strip()
            key = (page, section, quote)
            if len(quote) < 12 or key in seen:
                continue
            seen.add(key)
            if len(rows) < MAX_ENTRIES:
                rows.append(dict(page=page, label=label, section=section, quote=quote, type=context_type))
        if captions is None and (is_caption or label.startswith('Bảng ')) and len(assets) < 200:
            kind = category(text if is_caption else section)
            assets.append(dict(page=page, label=label, section=section, quote=text[:500],
                               type=kind if is_caption or kind.startswith('Bảng') else 'Bảng dữ liệu',
                               basis='caption' if is_caption else 'table'))
    if captions is not None:
        assets = [dict(page=a['page'], label=f"Trang {a['page']}", section=a.get('section_title') or '',
                       quote=a['raw_text'][:500], type=category(a['display_text']),
                       caption_label=a.get('label'), basis='caption')
                  for a in captions[:200]]
    for item in rows + assets:
        if item['page'] in locations:
            item['source_location'] = locations[item['page']]
    return dict(version=VERSION, rows=rows, assets=assets,
                truncated=len(seen) > MAX_ENTRIES or len(assets) >= 200)


def section_paths(nodes, id_key, parent_key, title_key):
    by_id = {node[id_key]: node for node in nodes}
    paths = {}
    for key, node in by_id.items():
        parts = []
        seen = set()
        while node and node[id_key] not in seen:
            seen.add(node[id_key])
            if node[title_key]:
                parts.append(node[title_key])
            node = by_id.get(node.get(parent_key))
        paths[key] = ' › '.join(reversed(parts))
    return paths


def from_units(units):
    from document_sections import build_section_tree
    nodes, structure = build_section_tree(units)
    paths = section_paths(nodes, 'id', 'parent_id', 'title')
    owners = {span['page']: paths[node['id']] for node in nodes for span in node['source_spans']}
    labels = {p['page']: p.get('source_label', f"Trang {p['page']}") for p in units}
    ignored = set(structure.get('index_pages', []))
    entries = ((p['page'], labels[p['page']], owners.get(p['page'], ''), p.get('text', ''))
               for p in units if p['page'] not in ignored)
    locations = {p['page']: p['source_location'] for p in units if p.get('source_location')}
    captions = {p['page']: p['caption'] for p in units if p.get('caption')}
    return make_index(entries, locations=locations, unit_captions=captions)


def from_pdf_lab(source, captions):
    sections = sorted(source['sections'], key=lambda s: s['start'])
    starts = [s['start'] for s in sections]
    paths = section_paths(sections, 'section_id', 'parent_section_id', 'section_title')

    def entries():
        for line in source['document']['lines']:
            if line.get('running_margin'):
                continue
            idx = bisect_right(starts, line['start']) - 1
            title = paths[sections[idx]['section_id']] if idx >= 0 else ''
            if any(term in folded(title) for term in ('muc luc', 'danh muc', 'list of figures', 'list of tables')):
                continue
            yield line['page'], f"Trang {line['page']}", title, line['text']
    locations = {p['page']: {'kind': 'pdf', 'physical_page': p['page'],
                             'pdf_page_label': p.get('pdf_page_label') or None,
                             'printed_number_candidates': p.get('printed_number_candidates', []),
                             'extraction_status': p.get('state'),
                             'reading_order_requires_review': p.get('reading_order_requires_review', False)}
                 for p in source['document'].get('pages', [])}
    return make_index(entries(), captions, locations=locations)


def publish(data, identity, source_hash):
    destination = os.environ.get('AI_PARTIAL_RESULT_FILE')
    version = (identity or {}).get('document_version_id')
    if not destination or not version:
        return
    data = dict(data, schema_version=6, document_version_id=version, _source_hash=source_hash)
    temporary = None
    try:
        with tempfile.NamedTemporaryFile(mode='w', encoding='utf-8', delete=False,
                                         dir=os.path.dirname(os.path.abspath(destination))) as stream:
            temporary = stream.name
            json.dump({'success': True, 'review_only': True, 'data': data}, stream, ensure_ascii=False)
        os.replace(temporary, destination)
        temporary = None
        print('AI_PARTIAL_READY', flush=True)
    except OSError:
        pass
    finally:
        if temporary:
            try:
                os.unlink(temporary)
            except OSError:
                pass
