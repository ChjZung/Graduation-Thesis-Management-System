"""Content-role and positional visual diagnostics; never change canonical source text."""
from bisect import bisect_left, bisect_right
from .heading_detector import folded, is_abstract_heading

FRONT = {'loi cam on', 'loi cam doan', 'muc luc', 'contents', 'table of contents',
         'nhan xet cua giang vien huong dan', 'nhan xet cua giao vien huong dan', 'nhan xet cua giang vien phan bien'}
MAIN = {'mo dau', 'loi mo dau', 'introduction', 'ket luan', 'conclusion'}


def annotate_sections(document, sections, detection):
    by_id = {s['section_id']: s for s in sections}
    context = ('unknown', 'unknown', True, 'Chưa đủ tín hiệu phân loại.')
    # Source offsets are ordered by extraction. Build indexes once, not once per section.
    lines = document['lines']
    starts = [r['start'] for r in lines]
    by_start = {r['start']: r for r in lines}
    candidates = sorted(detection['candidates'], key=lambda h: h['start'])
    candidate_starts = [h['start'] for h in candidates]
    threshold = detection.get('heading_threshold', 3.4)
    for section in sections:
        title = folded(section['section_title']).rstrip(' :')
        parent = by_id.get(section['parent_section_id'])
        if is_abstract_heading(title):
            role, subtype, include, reason = 'front_matter', 'abstract', True, 'Tóm tắt do tác giả cung cấp; giữ cho Map, không coi là minh chứng độc lập.'
        elif title in {'loi cam on', 'loi cam doan'} or title.startswith('nhan xet '):
            subtype = ('acknowledgments' if title == 'loi cam on'
                       else 'commitment' if title == 'loi cam doan'
                       else 'assessment')
            role, include, reason = 'front_matter', False, 'Phần mở đầu hành chính / cảm ơn / nhận xét của hội đồng.'
        elif title in {'muc luc', 'contents', 'table of contents'} or title.startswith('danh muc '):
            subtype = ('table_of_contents' if title in {'muc luc', 'contents', 'table of contents'}
                       else 'list_of_figures' if 'hinh' in title
                       else 'list_of_tables' if 'bang' in title
                       else 'list_of_abbreviations' if any(w in title for w in ('viet tat', 'ky hieu'))
                       else 'front_heading')
            role, include, reason = 'front_matter', False, 'Mục lục / danh mục tài liệu.'
        elif title in MAIN or section['kind'] == 'chapter':
            subtype = ('introduction' if title in {'mo dau', 'loi mo dau', 'introduction'}
                       else 'conclusion' if title in {'ket luan', 'conclusion'}
                       else 'chapter' if section['kind'] == 'chapter'
                       else 'academic_content')
            role, include, reason = 'main_content', True, 'Mở đầu/kết luận/chương học thuật có tiêu đề nguồn.'
        elif title in {'tai lieu tham khao', 'references'}:
            role, subtype, include, reason = 'back_matter', 'references', False, 'Danh mục tham khảo; có thể bật khi yêu cầu cần đối chiếu tài liệu.'
        elif title.startswith(('phu luc', 'appendix')):
            role, subtype, include, reason = 'back_matter', 'appendix', True, 'Giữ phụ lục mặc định vì có thể chứa minh chứng quan trọng.'
        elif section['structure_fallback']:
            text = folded(section['content'])
            school = any(s in text for s in ('truong dai hoc', 'university', 'bo cong thuong', 'hoc vien'))
            thesis = any(s in text for s in ('de tai', 'thesis', 'sinh vien thuc hien', 'khoa luan', 'do an'))
            cover = school and thesis
            role, subtype, include, reason = ('front_matter', 'cover_candidate', False, 'Vùng chưa có heading có đồng thời thông tin trường và đề tài/sinh viên.') if cover else (
                'unknown', 'unclassified', True, 'Giữ nội dung chưa phân loại để tránh bỏ mất phần học thuật.')
        elif parent:
            role, subtype, include, reason = parent['section_type'], parent['section_subtype'], parent['include_in_llm'], 'Kế thừa vai trò từ mục cha theo cây nguồn.'
        else:
            role, subtype, include, reason = context

        status = ('detected' if not section['structure_fallback'] and role != 'unknown'
                  else 'fallback' if section['structure_fallback']
                  else 'unclassified')
        section.update(section_type=role, section_subtype=subtype, include_in_llm=include,
                       include_in_map=include, classification_status=status,
                       classification_reason=reason, classification_method='heading_context_heuristic')
        if not parent:
            context = role, subtype, include, 'Ngữ cảnh phần tài liệu trước đó; cần đối chiếu.'
        first = by_start.get(section['start'])
        next_line = by_start.get(section['end'])
        visuals = []
        for page in document['pages'][section['start_page'] - 1:section['end_page']]:
            lo = first['bbox'][1] if first and page['page'] == first['page'] and section['start'] else 0
            hi = next_line['bbox'][1] if next_line and page['page'] == next_line['page'] else page['height']
            for region in page.get('visual_regions', []):
                center = (region['bbox'][1] + region['bbox'][3]) / 2
                if lo <= center < hi:
                    visuals.append(dict(region, page=page['page']))
        section['visual_regions'] = visuals
        section['image_count'] = sum(v['kind'] == 'image' for v in visuals)
        section['vector_region_count'] = sum(v['kind'] == 'vector' for v in visuals)
        section['has_images'] = section['image_count'] > 0
        section['visual_assignment_method'] = 'region_center_within_direct_section_vertical_band (heuristic)'
        section['text_token_count'] = section.get('token_count', 0)
        section['visual_content_detected'] = bool(visuals)
        section['visual_content_unread'] = bool(visuals)
        section['extraction_status'] = 'review_required' if visuals else 'text_extracted'
        section['review_reasons'] = []
        if visuals:
            section['review_reasons'].append(
                'Ít văn bản và có ảnh/đồ họa trong vùng mục; cần xem PDF, chưa đọc ý nghĩa hình.'
                if section['text_token_count'] < 120 else
                'Có ảnh/đồ họa chưa được đọc; số token text cao không chứng minh đã bao phủ nội dung hình.')
        if role == 'front_matter' and section['end_page'] - section['start_page'] >= 3 and title not in {'muc luc', 'contents', 'table of contents'} and not title.startswith('danh muc '):
            section['review_reasons'].append('Phần đầu kéo dài nhiều trang; cần kiểm tra ranh giới, không tự cắt theo số trang.')
        for h in candidates[bisect_right(candidate_starts, section['start']):bisect_left(candidate_starts, section['end'])]:
            if not h['accepted'] and h['bold'] and h['score'] >= threshold:
                if not any(w in h['reason'] for w in ('mục lục', 'Chú thích', 'Header/footer', 'Số trang')):
                    section['review_reasons'].append('Có ứng viên heading bị loại bên trong vùng: ' + h['title'])
        direct_lines = lines[bisect_left(starts, section['start']):bisect_left(starts, section['end'])]
        section['reading_order_review'] = any(
            a['page'] == b['page'] and b['bbox'][1] < a['bbox'][1] - max(a['font_size'], b['font_size']) * 2
            for a, b in zip(direct_lines, direct_lines[1:]))
        section['requires_manual_review'] = (section['extraction_status'] == 'review_required'
                                             or bool(section['review_reasons'])
                                             or section['structure_fallback'])
