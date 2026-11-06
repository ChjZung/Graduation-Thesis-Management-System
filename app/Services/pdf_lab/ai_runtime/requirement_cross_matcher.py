"""Cross-match teacher requirements directly with AI-generated chapters, sections, completed work, and diagram captions."""
import re
import unicodedata
from .requirement_relevance import relevant, select_candidate, families
from .requirement_review.parsing import criterion_spans

STOP_WORDS = set(
    'va la cua cac nhung mot duoc cho voi trong theo ve co da de khi nay bao cao '
    'he thong noi dung yeu cau sinh vien giang vien thuc hien de tai phan muc va/hoac '
    'hoac bang cac can trinh bay danh cho gom thiet ke xay dung'.split()
)

CONCEPT_CLUSTERS = {
    'database': {'csdl', 'database', 'co_so_du_lieu', 'mysql', 'sql', 'bang', 'table', 'erd', 'thuc_the', 'quan_he', 'khoa'},
    'ui': {'giao_dien', 'ui', 'ux', 'man_hinh', 'form', 'nguoi_dung', 'view', 'frontend', 'layout'},
    'sequence': {'tuan_tu', 'sequence', 'so_do_tuan_tu', 'luong', 'tuong_tac', 'so_do'},
    'usecase': {'use_case', 'usecase', 'chuc_nang', 'dac_ta', 'tac_nhan', 'actor', 'nghiep_vu'},
    'class_diagram': {'lop', 'class', 'class_diagram', 'so_do_lop', 'thiet_ke_lop'},
    'survey': {'khao_sat', 'hien_trang', 'quy_trinh', 'nghiep_vu', 'thuc_trang', 'tong_quan', 'gioi_thieu', 'ly_do'},
    'testing': {'kiem_thu', 'test', 'danh_gia', 'ket_qua', 'thu_nghiem', 'trien_khai', 'ap_dung', 'thuc_te'},
    'topic_mgmt': {'de_tai', 'de_xuat', 'dang_ky', 'quan_ly_de_tai', 'danh_sach_de_tai'},
    'progress_defense': {'tien_do', 'theo_doi', 'ho_so', 'bao_ve', 'hoi_dong', 'cham_diem', 'khoa_luan'},
    'architecture': {'kien_truc', 'cong_nghe', 'mo_hinh', 'mvc', 'client_server', 'laravel', 'framework', 'api'},
}


def remove_diacritics(text):
    text = unicodedata.normalize('NFD', str(text).casefold().replace('đ', 'd').replace('Đ', 'd'))
    return ''.join(c for c in text if not unicodedata.combining(c))


def tokenize(text):
    clean = remove_diacritics(text)
    words = re.findall(r'[a-z0-9_]+', clean)
    return set(w for w in words if len(w) > 1 and w not in STOP_WORDS)


def parse_requirements_text(raw_text):
    if not raw_text or not isinstance(raw_text, str) or not raw_text.strip():
        return []
    lines = [l.strip() for l in raw_text.splitlines() if l.strip()]
    numbered = []
    for line in lines:
        m = re.match(r'^(?:Yêu cầu\s*)?(\d+)[.)]\s*(.+)$', line, re.IGNORECASE)
        if m:
            numbered.append((m.group(1), m.group(2).strip(), line))
        elif line.startswith(('-', '•', '*', '+')):
            clean_line = line.lstrip('-•*+ ').strip()
            if len(clean_line) > 5:
                numbered.append((str(len(numbered) + 1), clean_line, line))

    items = []
    if numbered:
        for idx, (num, body, full) in enumerate(numbered):
            items.append({
                'id': f'req_{idx+1:03d}',
                'num': num,
                'text': body,
                'quote': full
            })
    else:
        for idx, line in enumerate(lines):
            if len(line) > 10 and not line.lower().startswith(('giai đoạn', 'đợt nộp', 'mô tả đợt')):
                items.append({
                    'id': f'req_{idx+1:03d}',
                    'num': str(idx + 1),
                    'text': line,
                    'quote': line
                })
    criteria = []
    for item in items:
        body = item['text']
        prefix = item['quote'][:len(item['quote'])-len(body)]
        for start, end in criterion_spans(body):
            criterion = body[start:end]
            criteria.append(dict(item, id=f'req_{len(criteria)+1:03d}', text=criterion,
                                 quote=(prefix if start == 0 else '') + criterion))
    return criteria


def flatten_chapters(chapters, parent_titles=None, parent_refs=None):
    flat = []
    parent_titles = parent_titles or []
    parent_refs = parent_refs or []

    for ch in chapters:
        title = ch.get('title', '')
        current_titles = parent_titles + [title] if title else parent_titles
        full_title = ' > '.join(current_titles)

        summary_text = ch.get('summary', '')
        if not summary_text and ch.get('items'):
            summary_text = ' '.join(it.get('content', '') for it in ch['items'] if isinstance(it, dict))

        refs = []
        for it in ch.get('items', []):
            if isinstance(it, dict) and it.get('source_references'):
                refs.extend(it['source_references'])

        # If this node has no references of its own, inherit parent references
        inherited_refs = []

        flat.append({
            'id': ch.get('id', ''),
            'title': title,
            'full_title': full_title,
            'start_page': ch.get('start_page'),
            'end_page': ch.get('end_page'),
            'summary': summary_text,
            'observations': ch.get('items', []),
            'source_references': refs,
            'inherited_references': inherited_refs,
            'level': ch.get('level', 1),
        })

        children = ch.get('sections', []) or ch.get('children', [])
        if children:
            flat.extend(flatten_chapters(children, current_titles, inherited_refs))
    return flat


def find_matching_captions(req_tokens, req_text, caption_index):
    """Find figure/table captions matching requirement keywords for manual diagram review."""
    if not caption_index or not isinstance(caption_index, dict):
        return []
    assets = caption_index.get('assets', [])
    if not isinstance(assets, list):
        return []

    req_clean = remove_diacritics(req_text)
    matched = []

    for ast in assets:
        raw_text = ast.get('display_text') or ast.get('raw_text') or ''
        sec_title = ast.get('section_title') or ''
        if not relevant(req_text, raw_text):
            continue
        comb_clean = remove_diacritics(raw_text)

        cap_tokens = tokenize(raw_text)
        overlap = req_tokens & cap_tokens

        has_phrase = bool(families(req_text) & families(raw_text))
        if 'erd' in req_clean and ('erd' in comb_clean or 'quan he co so du lieu' in comb_clean):
            has_phrase = True
        elif 'so do lop' in req_clean and ('so do lop' in comb_clean or 'class' in comb_clean):
            has_phrase = True
        elif 'tuan tu' in req_clean and 'tuan tu' in comb_clean:
            # Check matching function for sequence diagram
            for term in ['ke hoach', 'sinh vien', 'de tai', 'tien do', 'ho so', 'hoi dong', 'cham diem', 'dang ky', 'de xuat']:
                if term in req_clean and term in comb_clean:
                    has_phrase = True
                    break
            if len(overlap) >= 2:
                has_phrase = True

        if has_phrase or len(overlap) >= 3:
            matched.append({
                'source_id': ast.get('asset_id') or f"p{ast.get('page')}_caption",
                'page': ast.get('page') or 1,
                'end_page': ast.get('page') or 1,
                'quote': raw_text.strip(),
                'start': (ast.get('source_offsets') or [[0, 0]])[0][0] if ast.get('source_offsets') else 0,
                'end': (ast.get('source_offsets') or [[0, 0]])[0][1] if ast.get('source_offsets') else 0,
                'quote_verified': True,
                'semantic_support': 'NEEDS_VISUAL_REVIEW',
                'source_type': 'CAPTION_REFERENCE',
                'document_sha256': ast.get('document_sha256', '')
            })

    seen = set()
    unique = []
    for m in matched:
        k = (m['page'], m['quote'])
        if k not in seen:
            seen.add(k)
            unique.append(m)
    return unique


def calculate_match_score(req_tokens, req_text, candidate, matched_captions=None):
    title = candidate.get('title', '')
    full_title = candidate.get('full_title', '') or title
    summary = candidate.get('summary', '')

    title_tokens = tokenize(f"{title} {full_title}")
    summary_tokens = tokenize(summary)

    # 1. Title keyword overlap (highest weight)
    title_overlap = req_tokens & title_tokens
    score = len(title_overlap) * 7.0

    # 2. Summary keyword overlap
    summary_overlap = req_tokens & summary_tokens
    score += len(summary_overlap) * 1.5

    # 3. Concept cluster bonus
    for cluster_name, cluster_words in CONCEPT_CLUSTERS.items():
        req_has = bool(req_tokens & cluster_words)
        cand_has = bool((title_tokens | summary_tokens) & cluster_words)
        if req_has and cand_has:
            score += 8.0

    # 4. Phrase match bonus
    req_clean = remove_diacritics(req_text)
    full_title_clean = remove_diacritics(full_title)
    key_phrases = [
        'co so du lieu', 'so do lop', 'tuan tu', 'use case', 'khao sat',
        'giao dien', 'de xuat', 'dang ky de tai', 'hoi dong', 'bao ve',
        'erd', 'quan he', 'bang du lieu', 'cham diem', 'ho so', 'tien do'
    ]
    for ph in key_phrases:
        if ph in req_clean and ph in full_title_clean:
            score += 12.0

    # 5. Caption proximity bonus: if candidate page range covers a matching caption
    if matched_captions and candidate.get('start_page') and candidate.get('end_page'):
        for cap in matched_captions:
            if candidate['start_page'] <= cap['page'] <= candidate['end_page']:
                score += 15.0
                break

    return score


def cross_match_teacher_requirements(requirements_doc, chapters, completed_work=None, caption_index=None, context=None):
    """Link each teacher requirement directly to the AI summary results and diagram captions."""
    req_text = ''
    if context:
        for field in ('submission_requirements', 'stage_requirements', 'round_description'):
            val = str(context.get(field) or '').strip()
            if val:
                req_text += val + '\n'
    if not req_text and requirements_doc:
        req_text = str(requirements_doc.get('text') or '').strip()

    req_items = parse_requirements_text(req_text)
    if not req_items:
        return []

    flat_sections = flatten_chapters(chapters or [])
    completed_items = []  # Match chapter observations, not unrelated document-wide work.

    all_candidates = list(flat_sections)
    for c_idx, cw in enumerate(completed_items):
        if isinstance(cw, dict) and cw.get('content'):
            all_candidates.append({
                'id': f'CW_{c_idx+1}',
                'title': cw['content'][:70],
                'full_title': cw['content'][:70],
                'start_page': cw.get('source_references', [{}])[0].get('page') if cw.get('source_references') else None,
                'end_page': cw.get('source_references', [{}])[-1].get('end_page') if cw.get('source_references') else None,
                'summary': cw['content'],
                'source_references': cw.get('source_references', []),
                'inherited_references': cw.get('source_references', []),
                'level': 99,
            })

    result_teacher = []

    for req in req_items:
        req_tokens = tokenize(req['text'])
        matched_captions = find_matching_captions(req_tokens, req['text'], caption_index)

        # Rank candidates
        scored = []
        for cand in all_candidates:
            cand = select_candidate(req['text'], cand)
            local_caps = [c for c in matched_captions if cand.get('start_page') and cand.get('end_page') and cand['start_page'] <= c['page'] <= cand['end_page'] and relevant(req['text'], cand.get('title',''))]
            if not cand['source_references'] and not local_caps:
                continue
            if not relevant(req['text'], cand.get('title','')+' '+cand.get('summary','')):
                continue
            sc = calculate_match_score(req_tokens, req['text'], cand, matched_captions)
            # Require minimum relevance
            if sc >= 10.0:
                scored.append((sc, cand))

        scored.sort(key=lambda x: x[0], reverse=True)

        if scored:
            cand = scored[0][1]
            # Captions passed the requirement/type gate independently. A matching
            # ERD must not disappear just because the best prose is on another page.
            title_to_show = cand.get('full_title') or cand.get('title')

            # Clean & format page numbers
            pages_str = ''
            if cand.get('start_page'):
                if cand.get('end_page') and cand['end_page'] != cand['start_page']:
                    pages_str = f" (Trang {cand['start_page']}–{cand['end_page']})"
                else:
                    pages_str = f" (Trang {cand['start_page']})"

            # Collect source references: candidate's own, or inherited, or caption references
            seen_refs = set()
            clean_refs = []
            cand_refs = cand.get('source_references') or cand.get('inherited_references') or []
            for r in cand_refs:
                key = (r.get('page'), r.get('quote', '')[:50])
                if key not in seen_refs:
                    seen_refs.add(key)
                    clean_refs.append(r)

            # If still no references, convert matched captions to references
            if not clean_refs and matched_captions:
                for mc in matched_captions:
                    clean_refs.append({
                        'source_id': mc['source_id'],
                        'page': mc['page'],
                        'end_page': mc['end_page'],
                        'quote': mc['quote'],
                        'quote_verified': True,
                        'semantic_support': 'NEEDS_VISUAL_REVIEW', 'source_type': 'CAPTION_REFERENCE'
                    })

            summary_sentence = cand.get('summary', '').strip()
            if not summary_sentence:
                if matched_captions:
                    cap_titles = '; '.join(c['quote'] for c in matched_captions[:2])
                    summary_sentence = f"Nhận diện tên hình/bảng liên quan: {cap_titles}. Chỉ đối chiếu tên; giảng viên cần kiểm chứng nội dung trong PDF gốc."
                else:
                    summary_sentence = f"Nội dung được trình bày tại mục {title_to_show}."

            if len(summary_sentence) > 300:
                summary_sentence = summary_sentence[:297].rstrip() + '...'

            explanation = f"Báo cáo có nội dung liên quan tại {title_to_show}{pages_str}: {summary_sentence}"

            point = {
                'title': f"{title_to_show}{pages_str}",
                'relation': 'related_text',
                'explanation': explanation,
                'source_references': clean_refs[:12],
                'check_status': 'NEEDS_VISUAL_REVIEW' if clean_refs and all(r.get('source_type') == 'CAPTION_REFERENCE' for r in clean_refs) else 'PARTIAL',
                'manual_references': matched_captions
            }

            report_match = {
                'content': summary_sentence,
                'source_references': clean_refs[:12]
            }

            result_teacher.append({
                'requirement_id': req['id'],
                'text': req['quote'],
                'evidence_quote': req['quote'],
                'report_relation': 'related_text',
                'report_explanation': explanation,
                'report_points': [point],
                'report_matches': [report_match]
            })
        elif matched_captions:
            cap_pages = ", ".join(f"Trang {c['page']}" for c in matched_captions)
            cap_names = "; ".join(c['quote'] for c in matched_captions[:2])
            explanation = f"Nhận diện tên hình/bảng liên quan tại {cap_pages}: {cap_names}. Chưa xác minh nội dung bên trong; giảng viên cần kiểm chứng PDF gốc."
            point = {
                'title': req['text'],
                'relation': 'related_text',
                'explanation': explanation,
                'source_references': [
                    {
                        'source_id': matched_captions[0]['source_id'],
                        'page': matched_captions[0]['page'],
                        'end_page': matched_captions[0]['end_page'],
                        'quote': matched_captions[0]['quote'],
                        'quote_verified': True,
                        'semantic_support': 'NEEDS_VISUAL_REVIEW', 'source_type': 'CAPTION_REFERENCE'
                    }
                ],
                'check_status': 'NEEDS_VISUAL_REVIEW',
                'manual_references': matched_captions
            }
            result_teacher.append({
                'requirement_id': req['id'],
                'text': req['quote'],
                'evidence_quote': req['quote'],
                'report_relation': 'related_text',
                'report_explanation': explanation,
                'report_points': [point],
                'report_matches': []
            })
        else:
            point = {
                'title': req['text'],
                'relation': 'not_found',
                'explanation': 'Hệ thống chưa tìm thấy mục tương ứng trong các chương báo cáo đã tóm tắt. Giảng viên vui lòng đối chiếu trực tiếp file PDF.',
                'source_references': [],
                'check_status': 'NOT_FOUND',
                'manual_references': []
            }
            result_teacher.append({
                'requirement_id': req['id'],
                'text': req['quote'],
                'evidence_quote': req['quote'],
                'report_relation': 'not_found',
                'report_explanation': 'Hệ thống chưa tìm thấy mục tương ứng trong các chương báo cáo đã tóm tắt. Giảng viên vui lòng đối chiếu trực tiếp file PDF.',
                'report_points': [point],
                'report_matches': []
            })

    return result_teacher
