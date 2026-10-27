"""Offline report-content observations; never grade a student's actual progress."""
import re
from bisect import bisect_left
from .claim_guards import review_pending_claim
from core.section_tree import page_for_offset

VERSION = 'report-content-review-1.0'
UNFINISHED = re.compile(r'\bchưa\s+(?:được\s+)?(?:hoàn\s*(?:thiện|thành)|triển\s*khai|cài\s*đặt|thực\s*hiện|xây\s*dựng|kiểm\s*thử|hỗ\s*trợ)\b|\b(?:not\s+(?:yet\s+)?(?:implemented|completed)|unimplemented)\b', re.I)
PLACEHOLDER = re.compile(r'^\s*(?:[-*•]\s*)?(?:TODO|TBD)\b\s*(?::|[-–]|$)', re.I)
CAPTION = re.compile(r'^\s*(?:Hình|Bảng|Figure|Table)\s+\d', re.I)


def review_content(result, base=None):
    doc = result['document']; text = doc['text']; sections = result.get('sections', [])
    headings = {h['heading_id']:h for h in result.get('heading_detection', {}).get('accepted', [])}
    lines = doc.get('lines', []); starts = [r['start'] for r in lines]
    output = {'version':VERSION, 'source_hash':doc['sha256'], 'status':'completed',
              'missing_content':[], 'reported_unfinished':[], 'manual_review':[],
              'coverage':{'checked_leaf_sections':0, 'total_pages':doc['page_count']},
              'note':'Quan sát nội dung báo cáo, không kết luận tiến độ thực tế. Không đọc nội dung ảnh/sơ đồ.'}
    seen = set()
    def add(group, section, status, message, quote, start, end, basis):
        if not quote or text[start:end] != quote:return
        key = (status, start, end)
        if key in seen:return
        seen.add(key)
        page = page_for_offset(doc, start)
        output[group].append({'section_id':section['section_id'], 'section_title':section['section_title'],
            'status':status, 'content':message, 'basis':basis,
            'source_references':[{'source_id':'content:'+section['section_id'], 'page':page,
                'end_page':page_for_offset(doc,end,True), 'quote':quote, 'start':start, 'end':end,
                'quote_verified':True, 'semantic_support':'CONTENT_OBSERVATION', 'document_sha256':doc['sha256']}]})
    eligible = {}
    for section in sections:
        if section.get('section_type') != 'main_content' or section.get('structure_fallback'):continue
        eligible[section['section_id']] = section
        # Containers with child sections are not empty merely because they have no introduction.
        if section.get('children'):continue
        output['coverage']['checked_leaf_sections'] += 1
        h = headings.get(section.get('heading_id'))
        rows = lines[bisect_left(starts,section['start']):bisect_left(starts,section['end'])]
        heading_ids = set((h or {}).get('line_ids', []))
        body = [r for r in rows if r.get('line_id') not in heading_ids and not r.get('running_margin')
                and not (r.get('bbox') and (r['bbox'][1] < r.get('page_height',1)*.08 or r['bbox'][3] > r.get('page_height',1)*.92)
                         and re.fullmatch(r'(?:\d{1,4}|[ivxlcdmIVXLCDM]{1,8})',r['text'].strip()))]
        meaningful = [r for r in body if re.search(r'\w',r['text']) and not CAPTION.match(r['text'])]
        placeholders = [r for i,r in enumerate(meaningful) if PLACEHOLDER.match(r['text'])
                        and not re.search(r'ví dụ|minh họa|nhãn|từ khóa',section['section_title']+' '+(' '.join(x['text'] for x in meaningful[max(0,i-2):i])),re.I)]
        for row in placeholders:
            a=row['start'];q=row['text'];add('missing_content',section,'placeholder',
                'Mục còn dấu TODO/TBD trong báo cáo; chưa phải nội dung hoàn chỉnh.',q,a,a+len(q),'literal_placeholder')
        if meaningful:continue  # Even short prose or numerical table cells are actual content.
        if not h or not heading_ids:continue  # Cannot distinguish heading from body reliably.
        a=section['start']; heading_rows=[r for r in rows if r.get('line_id') in heading_ids]
        if not heading_rows:continue
        b=max(r['end'] for r in heading_rows);q=text[a:b].rstrip();b=a+len(q)
        next_heading_page = next((s['start_page'] for s in sections if s['start']>=section['end'] and s['start']>section['start']),doc['page_count']+1)
        pages = [p for p in doc['pages'] if section['start_page'] <= p['page'] <= max(section['end_page'],next_heading_page-1)]
        uncertain = (section.get('reading_order_review') or section.get('classification_status')=='uncertain'
                     or any(p.get('state') in ('scan_suspected','vector_without_text') for p in pages))
        visual = section.get('visual_content_detected') or section.get('has_images') or any(CAPTION.match(r['text']) for r in body)
        # Physical pages with no text offsets can otherwise disappear from section spans.
        visual = visual or any(p.get('image_count') or p.get('visual_regions') for p in pages if not p.get('text','').strip())
        if visual:
            add('manual_review',section,'needs_visual_review','Ít văn bản, có hình/bảng/đồ họa; cần xem trực tiếp, không coi là mục trống.',q,a,b,'visual_or_caption_present')
        elif uncertain or section.get('requires_manual_review'):
            add('manual_review',section,'undetermined','Chưa xác định nội dung do cấu trúc hoặc trích xuất cần kiểm tra.',q,a,b,'extraction_uncertain')
        else:
            add('missing_content',section,'heading_only','Mục mới có tiêu đề, chưa thấy nội dung trong phần văn bản trích xuất.',q,a,b,'heading_without_body')
    # Reuse already matched Map quotes. Future plans alone never imply unfinished work.
    for mapped in (base or {}).get('map_results', {}).values():
        for item in mapped.get('items', []):
            section=eligible.get(item.get('section_id'));quote=item.get('evidence_quote','')
            if not section or item.get('quote_status')!='MATCH' or not UNFINISHED.search(quote):continue
            context=' '.join(item.get('path',[]))+section['section_title']
            if re.search(r'use[- ]?case|đặc tả|cơ sở lý thuyết|yêu cầu',context,re.I):continue
            if re.search(r'\b(?:nếu|khi|trường hợp|ví dụ)\b',quote,re.I):continue
            a=item.get('evidence_start');b=item.get('evidence_end')
            if not isinstance(a,int) or not isinstance(b,int):continue
            add('reported_unfinished',section,'reported_unfinished',
                'Báo cáo nêu nội dung chưa hoàn thiện; xem câu trích để đối chiếu.',quote,a,b,'matched_map_quote')
    output['coverage']['map_available']=bool((base or {}).get('map_results'))
    output['coverage']['map_scope_complete']=(base or {}).get('status')=='complete'
    if not output['coverage']['checked_leaf_sections']:output['status']='undetermined'
    return output


def safe_review_content(result, base=None):
    """An optional local audit must never invalidate an otherwise valid summary."""
    try:
        return review_content(result,base)
    except (KeyError,TypeError,ValueError,IndexError):
        return {'version':VERSION,'source_hash':result.get('document',{}).get('sha256'),
                'status':'unavailable','missing_content':[],'reported_unfinished':[],
                'manual_review':[],'coverage':{},'note':'Chưa hoàn tất kiểm tra cấu trúc; không kết luận nội dung bị thiếu.'}
