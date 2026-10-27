from .token_chunker import LexicalCounter
from .section_tree import page_for_offset


def validate(document, sections, chunks, settings, detection=None):
    checks = []
    def add(code, status, message, count=0):
        checks.append({'code': code, 'status': status, 'message': message, 'count': count})
    source = document['text']; by_id = {s['section_id']: s for s in sections}
    section_exact = ''.join(s['content'] for s in sections) == source
    cursor, partition_ok = 0, True
    for s in sections:
        partition_ok &= s['start'] == cursor and s['end'] >= s['start'] and s['content'] == source[s['start']:s['end']]
        cursor = s['end']
    partition_ok &= cursor == len(source)
    add('section_ownership', 'PASS' if section_exact and partition_ok else 'FAIL', 'Nội dung trực tiếp của các mục phải phân hoạch nguồn đúng thứ tự, không thiếu/lặp.')
    counter = LexicalCounter(); core_parts, cursor, invalid_chunks = [], 0, []
    for chunk in chunks:
        s = by_id.get(chunk.get('section_id'))
        required = ['chunk_id', 'start_page', 'end_page', 'token_count', 'content', 'start', 'end', 'novel_start']
        if not s or any(k not in chunk for k in required):
            invalid_chunks.append(chunk.get('chunk_id', '?')); continue
        lo, hi, novel = chunk['start'], chunk['end'], chunk['novel_start']
        valid = (s['start'] <= lo <= novel < hi <= s['end'] and novel == cursor and
                 chunk['content'] == source[lo:hi] and bool(chunk['content'].strip()) and
                 chunk['token_count'] == counter.count(chunk['content']) and
                 chunk['token_count'] <= settings.max_tokens and
                 chunk['overlap_tokens'] == counter.count(source[lo:novel]) and
                 chunk['overlap_tokens'] <= settings.overlap_tokens and
                 chunk['start_page'] == page_for_offset(document, lo) and
                 chunk['end_page'] == page_for_offset(document, hi, True))
        if not valid:
            invalid_chunks.append(chunk['chunk_id'])
        core_parts.append(source[novel:hi]); cursor = hi
    chunk_exact = ''.join(core_parts) == source and cursor == len(source) and not invalid_chunks
    add('chunk_integrity', 'PASS' if chunk_exact else 'FAIL', 'Kiểm tra nguồn, thứ tự, overlap, giới hạn token và metadata từng chunk.', len(invalid_chunks))
    empty_chunks = sum(not c.get('content', '').strip() for c in chunks)
    oversize = sum(c.get('token_count', 0) > settings.max_tokens for c in chunks)
    metadata_missing = sum(any(k not in c for k in ['chunk_id', 'section_id', 'start_page', 'end_page', 'start', 'end', 'novel_start', 'token_count']) for c in chunks)
    page_errors = sum(not (1 <= c.get('start_page', 0) <= c.get('end_page', 0) <= document['page_count']) for c in chunks)
    for code, count, message in [('empty_chunks', empty_chunks, 'Chunk rỗng.'),
                                 ('oversize_chunks', oversize, 'Chunk vượt giới hạn token cứng theo bộ đếm local.'),
                                 ('missing_metadata', metadata_missing, 'Chunk thiếu metadata bắt buộc.'),
                                 ('page_order', page_errors, 'Phạm vi trang chunk sai hoặc nằm ngoài file PDF.')]:
        add(code, 'FAIL' if count else 'PASS', message, count)
    assigned_lines = sum(any(c.get('novel_start', -1) <= r['start'] < c.get('end', -1) for c in chunks) for r in document['lines'])
    exact = partition_ok and section_exact and chunk_exact
    add('source_integrity', 'PASS' if exact and source else 'WARNING' if not source else 'FAIL',
        'Bảo toàn văn bản nguồn: ghép nối các phần không overlap của section và chunk tái tạo 100% văn bản canonical.', 0 if exact and source else 1)
    cross_section = [c['chunk_id'] for c in chunks if by_id.get(c.get('section_id')) and (c['start'] < by_id[c['section_id']]['start'] or c['end'] > by_id[c['section_id']]['end'])]
    add('chapter_boundary', 'FAIL' if cross_section else 'PASS',
        'Ranh giới chương/mục: chunk phải thuộc trọn vẹn trong một section, không cắt ngang hay trộn mục.', len(cross_section))
    link_issues = [c['chunk_id'] for c in chunks if (
        not (1 <= c.get('start_page', 0) <= c.get('end_page', 0) <= document['page_count']) or
        c.get('start_page') != page_for_offset(document, c.get('start', 0)) or
        c.get('end_page') != page_for_offset(document, c.get('end', 0), True)
    )]
    add('source_links', 'FAIL' if link_issues else 'PASS',
        'Truy vết nguồn: metadata trang bắt đầu và kết thúc của chunk khớp chính xác với offset văn bản nguồn.', len(link_issues))
    add('text_coverage', 'PASS' if exact and source else 'WARNING' if not source else 'FAIL',
        'Độ bao phủ trên VĂN BẢN ĐÃ TRÍCH XUẤT; không đo độ chính xác heading hoặc nội dung ảnh.', assigned_lines)
    invalid_tree = []
    for s in sections:
        p = by_id.get(s['parent_section_id']) if s['parent_section_id'] else None
        if (s['parent_section_id'] and (not p or p['order_index'] >= s['order_index'] or p['section_level'] >= s['section_level'])):
            invalid_tree.append(s['section_id'])
        if any(child not in by_id or by_id[child]['parent_section_id'] != s['section_id'] for child in s['children']):
            invalid_tree.append(s['section_id'])
    add('tree_integrity', 'FAIL' if invalid_tree else 'PASS', 'Quan hệ cha/con phải hợp lệ và đi theo thứ tự nguồn.', len(invalid_tree))
    low_text = [p['page'] for p in document['pages'] if p['state'] not in ('text', 'empty')]
    add('unread_pages', 'WARNING' if low_text else 'PASS', f'Trang không có text: {low_text}. Scan/vector cần OCR hoặc đọc ảnh; trang rỗng được ghi riêng.', len(low_text))
    blank = [p['page'] for p in document['pages'] if p['state'] == 'empty']
    add('empty_pages', 'PASS', f'Trang được ghi nhận rỗng (không text/ảnh/drawing): {blank}. Không tự kết luận bị mất dữ liệu.', len(blank))
    image_pages = [p['page'] for p in document['pages'] if p['image_count']]
    add('images_unread', 'WARNING' if image_pages else 'PASS', 'Ảnh/sơ đồ chưa được phân tích nội dung; chữ trong ảnh có thể không được trích xuất.', len(image_pages))
    fallback = [s['section_id'] for s in sections if s['structure_fallback']]
    add('structure_fallback', 'WARNING' if fallback else 'PASS', 'Văn bản trước heading hoặc tài liệu không có heading được giữ ở vùng fallback.', len(fallback))
    empty = [s['section_id'] for s in sections if not s['content'].strip() or (s['heading_id'] and len(s['content'].strip().splitlines()) <= 1)]
    add('empty_sections', 'WARNING' if empty else 'PASS', 'Mục chỉ có tiêu đề/chưa có nội dung trực tiếp; có thể có các mục con.', len(empty))
    seen, duplicates = set(), []
    for s in sections:
        key = (s['parent_section_id'], s['section_number'] or s['section_title'].casefold())
        if key in seen:
            duplicates.append(s['section_id'])
        seen.add(key)
    add('duplicate_headings', 'WARNING' if duplicates else 'PASS', 'Tiêu đề/số mục trùng dưới cùng mục cha cần đối chiếu.', len(duplicates))
    orphan = [s for s in sections if s['section_level'] > 1 and (not s['parent_section_id'] or
              s['section_level'] > by_id[s['parent_section_id']]['section_level'] + 1)]
    add('skipped_heading_level', 'WARNING' if orphan else 'PASS', 'Mục thiếu cha hoặc nhảy cấp; không tự tạo heading giả để bù.', len(orphan))
    warnings = sum(h['warning'] for h in (detection or {}).get('accepted', []))
    add('heuristic_headings', 'WARNING' if warnings else 'PASS', 'Điểm heading là heuristic, cần đối chiếu thủ công; không phải xác suất.', warnings)
    boundary = [s['section_id'] for s in sections if any(r.startswith(('Phần đầu', 'Có ứng viên')) for r in s.get('review_reasons', []))]
    add('heading_boundary_review', 'WARNING' if boundary else 'PASS', 'Cảnh báo ranh giới theo dấu hiệu heading bị loại/phần đầu dài; không tự cắt trang.', len(boundary))
    uncertain = [s['section_id'] for s in sections if s.get('section_type') == 'unknown' or s.get('section_subtype') == 'cover_candidate']
    add('front_matter_review', 'WARNING' if uncertain else 'PASS', 'Phân loại chưa rõ hoặc vùng bìa suy ra từ thông tin trường/đề tài cần đối chiếu.', len(uncertain))
    unassigned = [s['section_id'] for s in sections if s.get('structure_fallback') and s.get('section_type') == 'unknown']
    add('unassigned_text_review', 'WARNING' if unassigned else 'PASS', 'Text vẫn được giữ nhưng có vùng chưa xác định được vai trò/heading.', len(unassigned))
    visual = [s['section_id'] for s in sections if s.get('extraction_status') == 'review_required']
    add('image_content_review', 'WARNING' if visual else 'PASS', 'Mục có ảnh/đồ họa chưa được đọc, kể cả khi có nhiều text; cần đối chiếu PDF.', len(visual))
    locs = [{'section_id': s['section_id'], 'title': s['section_title'],
             'start_page': s['start_page'], 'end_page': s['end_page']}
            for s in sections if s['section_id'] in set(visual)]
    checks[-1]['locations'] = locs
    add('visual_content_review', 'WARNING' if visual else 'PASS', 'Cảnh báo nội dung hình/sơ đồ chưa được phân tích trong các mục.', len(visual))
    checks[-1]['locations'] = locs
    pages_unaccounted = [p['page'] for p in document['pages'] if p['state'] not in ('text', 'empty', 'scan_suspected', 'vector_without_text')]
    add('page_coverage', 'FAIL' if pages_unaccounted else 'PASS', 'Kiểm tra trạng thái từng trang: toàn bộ các trang PDF vật lý đều có trạng thái định danh rõ ràng.', len(pages_unaccounted))
    reading = [s['section_id'] for s in sections if s.get('reading_order_review')]
    add('reading_order_review', 'WARNING' if reading else 'PASS', 'Có bước lùi dọc trang trong thứ tự text: có thể do nhiều cột/bảng, cần xem nguồn.', len(reading))
    add('section_hierarchy_review', 'WARNING' if orphan else 'PASS', 'Quan hệ có cấp bị bỏ qua/mục chưa có cha cần đối chiếu. PASS không chứng nhận ngữ nghĩa.', len(orphan))
    status = 'FAIL' if any(c['status'] == 'FAIL' for c in checks) else 'WARNING' if any(c['status'] == 'WARNING' for c in checks) else 'PASS'
    return {'status': status, 'checks': checks, 'coverage_percent': 100. if exact and source else 0.,
            'coverage_scope': 'extracted_text_only', 'source_chars': len(source), 'line_count': len(document['lines']),
            'assigned_lines': assigned_lines, 'paragraph_count': len(document['blocks']),
            'invalid_chunk_ids': invalid_chunks, 'invalid_tree_ids': invalid_tree,
            'note': 'PASS kỹ thuật không chứng minh hiểu đúng cấu trúc/ngữ nghĩa. Cần nhãn heading đối chiếu độc lập.'}
