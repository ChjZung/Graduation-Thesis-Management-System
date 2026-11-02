"""Caption pointers only. Never changes canonical source or verifies visual content."""
import hashlib
import re
import unicodedata

VERSION = 'caption-text-1'

def folded(text):
    return ''.join(c for c in unicodedata.normalize('NFD', text.casefold()) if unicodedata.category(c) != 'Mn')

PATTERN = re.compile(r'^(Hình|Bảng|Figure|Table)\s+(\d+(?:[.\-]\d+)*)(?:\s*[.:–—-]\s*|\s+)(\S.*)$', re.I)

def extract_captions(result):
    doc = result['document']; sections = result['sections']; lines = doc['lines']; assets = []; seen = set()
    for i, line in enumerate(lines):
        text = line['text'].strip(); match = PATTERN.match(text)
        if not match or line.get('running_margin'):
            continue
        containing = [s for s in sections if s['start'] <= line['start'] < s['end']]
        section = containing[-1] if containing else None
        context = folded((section or {}).get('section_title', ''))
        if any(t in context for t in ('danh muc', 'muc luc', 'list of figures', 'list of tables', 'tai lieu tham khao')):
            continue
        if re.search(r'\.{2,}\s*\d+\s*$', text) or re.search(r'\b(?:mo ta|trinh bay|the hien|duoc|cho thay|illustrates|shows)\b', folded(text.split(match[2], 1)[-1])) and text.endswith('.'):
            continue
        rows = [line]
        for nxt in lines[i+1:i+4]:
            prev = rows[-1]
            if (nxt['page'] != line['page'] or nxt.get('running_margin') or PATTERN.match(nxt['text'].strip())
                or nxt['block_id'] != line['block_id'] or nxt['bbox'][1] - prev['bbox'][3] > max(8, line['font_size'])
                or abs(nxt['font_size'] - line['font_size']) > 1 or nxt['text'].strip().endswith(':')
                or any(s['start'] == nxt['start'] for s in sections)):
                break
            rows.append(nxt)
        key = (line['page'], tuple(round(v, 1) for v in line['bbox']), ' '.join(text.split()))
        if key in seen: continue
        seen.add(key)
        raw = doc['text'][rows[0]['start']:rows[-1]['end']].rstrip('\n')
        assets.append({'asset_id': 'caption-' + hashlib.sha256(f"{doc['sha256']}:{line['start']}".encode()).hexdigest()[:20],
            'type': 'FIGURE_CAPTION' if folded(match[1]) in ('hinh', 'figure') else 'TABLE_CAPTION',
            'label': match[1] + ' ' + match[2], 'display_text': ' '.join(r['text'].strip() for r in rows), 'raw_text': raw,
            'section_id': (section or {}).get('section_id'), 'section_number': (section or {}).get('section_number'),
            'section_title': (section or {}).get('section_title'), 'page': line['page'], 'pdf_page_index': line['page']-1,
            'source_offsets': [[r['start'], r['end']] for r in rows], 'source_rects': [r['bbox'] for r in rows],
            'status': 'CAPTION_LOCATED' if section else 'CAPTION_AMBIGUOUS', 'content_analyzed': False,
            'source_type': 'CAPTION_REFERENCE', 'document_sha256': doc['sha256']})
    return {'version': VERSION, 'status': 'completed', 'assets': assets}

def section_nodes(result, evidence, make_item, root_id, caption_index):
    """Aggregate grounded Map observations; parent summaries include descendant text."""
    sections = result['sections']; by_id = {s['section_id']: s for s in sections}
    def descendants(s):
        return {s['section_id']} | set().union(*(descendants(by_id[c]) for c in s['children'])) if s['children'] else {s['section_id']}
    def node(s):
        ids = descendants(s); observations = []; seen = set()
        for iid, e in evidence.items():
            if e.get('section_id') not in ids: continue
            if any(a['raw_text'].strip() == e.get('evidence_quote', '').strip() for a in caption_index['assets']): continue
            a, b = e.get('evidence_start'), e.get('evidence_end')
            owner = by_id.get(e.get('section_id'))
            if not owner or not isinstance(a, int) or not isinstance(b, int) or not owner['start'] <= a < b <= owner['end']: continue
            key = (a, b, e['summary'])
            if key in seen: continue
            seen.add(key); observations.append(make_item(e['summary'], [iid]))
        number = s.get('section_number') or ''
        title = re.sub(r'^' + re.escape(number) + r'[.\s]+', '', s['section_title']) if number else s['section_title']
        return {'id': s['section_id'], 'number': number, 'title': title,
            'level': s['section_level'], 'kind': s['kind'], 'start_page': s['start_page'],
            'end_page': s.get('subtree_end_page', s['end_page']), 'items': observations,
            'summary': ' '.join(o['content'] for o in observations), 'status': 'completed' if observations else 'insufficient_text',
            'related_assets': [a for a in caption_index['assets'] if a['section_id'] == s['section_id']],
            'sections': [node(by_id[c]) for c in s['children']]}
    return [node(s) for s in sections if s.get('parent_section_id') == root_id]
