"""Exclusive direct spans own all text exactly once. Subtree spans are separate."""


def page_for_offset(document, offset, end=False):
    if not document['text']:
        return 1
    pos = max(0, offset - 1) if end else offset
    for page in document['pages']:
        if page['start'] <= pos < page['end']:
            return page['page']
    return document['page_count']


def build_sections(document, detection):
    headings, text = detection['accepted'], document['text']
    sections = []
    entries = list(headings)
    if text and (not entries or entries[0]['start'] > 0):
        entries.insert(0, {'title': 'Văn bản chưa gán tiêu đề', 'section_number': None, 'level': 1,
                           'start': 0, 'heading_id': None, 'kind': 'fallback', 'page': 1})
    stack = []
    for index, h in enumerate(entries):
        start = h['start']; end = entries[index + 1]['start'] if index + 1 < len(entries) else len(text)
        while stack and stack[-1]['section_level'] >= h['level']:
            stack.pop()
        parent = stack[-1] if stack and h['kind'] != 'fallback' else None
        # A numbered parent must be a prefix; avoid silently nesting chapter 2 under chapter 1.
        if parent and h.get('section_number') and parent.get('section_number'):
            if not h['section_number'].startswith(parent['section_number'] + '.'):
                parent = None; stack.clear()
        section = {'section_id': f's{index + 1:05}', 'heading_id': h['heading_id'],
                   'section_number': h['section_number'], 'section_title': h['title'],
                   'section_level': h['level'], 'kind': h['kind'],
                   'numbering_scope': h.get('numbering_scope', 'document'),
                   'parent_section_id': parent['section_id'] if parent else None,
                   'start_page': page_for_offset(document, start), 'end_page': page_for_offset(document, end, True),
                   'start': start, 'end': end, 'subtree_end': end, 'order_index': index,
                   'content': text[start:end], 'direct_content': text[start:end], 'children': [], 'structure_fallback': h['kind'] == 'fallback'}
        if parent:
            parent['children'].append(section['section_id'])
        sections.append(section); stack.append(section)
    by_id = {s['section_id']: s for s in sections}
    for section in reversed(sections):
        section['subtree_end_page'] = page_for_offset(document, section['subtree_end'], True)
        if section['parent_section_id']:
            p = by_id[section['parent_section_id']]
            p['subtree_end'] = max(p['subtree_end'], section['subtree_end'])
    return sections
