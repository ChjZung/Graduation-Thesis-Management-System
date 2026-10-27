"""Coverage, exact quote/source validation and deterministic conservative guards."""
import hashlib
import re
from .source_index import normalized,section_at
from .schemas import KINDS,VERDICTS
from ..provider import ApiError
from ..claim_guards import review_pending_claim

GUARD_VERSION="review-guards-2.6"


def bad(message,code='invalid_review_output'):raise ApiError(message,code)


def short_text(value,limit=700,words=90,empty=False):
    if not isinstance(value,str) or len(value)>limit or len(value.split())>words or (not empty and not value.strip()):bad('Nội dung trả lời rỗng hoặc vượt giới hạn ngắn gọn.')
    if re.search(r'^(?:đạt yêu cầu|không đạt|hoàn thành đầy đủ|pass|fail)\b|\b(?:báo cáo|bài nộp|nhóm|sinh viên)\s+(?:đã\s+)?(?:đạt yêu cầu|chưa làm|chưa thực hiện|đáp ứng đầy đủ|hoàn thành toàn bộ)',value,re.I):bad('Không chấp nhận AI tự đánh giá sinh viên.','review_grading')
    return value.strip()


def keyed(data,key,expected):
    value=data.get(key) if isinstance(data,dict) else None
    if not isinstance(value,dict) or set(value)!=set(expected) or any(not isinstance(v,dict) for v in value.values()):bad('Đầu ra không giữ đủ và đúng các mã yêu cầu/tiêu chí.','review_coverage')
    return value


def validate_plan(data,items):
    rows=keyed(data,'items',[i['item_id'] for i in items]);output=[]
    for item in items:
        checks=rows[item['item_id']].get('checks')
        if not isinstance(checks,list) or not 1<=len(checks)<=20:bad('Mỗi yêu cầu cần 1–20 tiêu chí.','review_coverage')
        clean=[];seen=set()
        for n,c in enumerate(checks,1):
            if not isinstance(c,dict):bad('Tiêu chí không hợp lệ.')
            desc=short_text(c.get('description'),220,40);quote=c.get('requirement_excerpt');kind=c.get('kind')
            if kind not in KINDS or not isinstance(quote,str) or not quote.strip() or ' '.join(quote.split()) not in ' '.join(item['original_text'].split()) or desc.casefold() in seen:bad('Tiêu chí không thuộc đúng yêu cầu gốc.','review_coverage')
            seen.add(desc.casefold());clean.append({'check_id':item['item_id']+f'_c{n:02}','item_id':item['item_id'],
                'requirement_text':item['original_text'],'description':desc,'requirement_excerpt':quote.strip(),'kind':kind})
        # Named Use Case fields are explicit requirements, not optional guesses.
        aliases={'actors':['tác nhân','actor'],'precondition':['tiền điều kiện','precondition'],
            'main_flow':['luồng chính','main flow'],'alternative_flow':['luồng phụ','luồng ngoại lệ','alternative flow'],'postcondition':['hậu điều kiện','postcondition']}
        original=item['original_text'].casefold()
        for kind,labels in aliases.items():
            label=next((q for q in labels if q in original),None)
            if label and not any(c['kind']==kind or label in c['description'].casefold() for c in clean):
                at=original.index(label)
                clean.append({'check_id':item['item_id']+f'_c{len(clean)+1:02}','item_id':item['item_id'],
                    'requirement_text':item['original_text'],'description':item['original_text'][at:at+len(label)].capitalize(),'requirement_excerpt':item['original_text'][at:at+len(label)],'kind':kind})
        if re.search(r'cai dat|lap trinh|da trien khai|da xay dung',normalized(item['original_text'])):
            for check in clean:check['kind']='implementation'
        output.append(dict(item,checks=clean))
    return output


def validate_quote(raw,index,allowed,check_id,max_chars=600):
    if not isinstance(raw,dict):bad('Dẫn chứng không phải object.','invalid_citation')
    sid=raw.get('source_id');quote=raw.get('quote');occurrence=raw.get('occurrence')
    if sid not in allowed or sid not in index['sources']:bad('Source ID không thuộc nguồn ứng viên của tiêu chí.','invalid_citation')
    source=index['sources'][sid];doc=index['document']
    if source.get('document_sha256')!=doc['sha256'] or source.get('document_version_id')!=doc['version_id'] or source.get('document_id')!=doc['document_id']:bad('Nguồn không thuộc đúng phiên bản PDF.','source_version_mismatch')
    if not isinstance(quote,str) or not quote.strip() or len(quote)>max_chars or type(occurrence) is not int or occurrence<1:bad('Câu trích phải ngắn và có đúng lần xuất hiện.','invalid_citation')
    matches=list(re.finditer(r'\s+'.join(re.escape(t) for t in quote.strip().split()),source['raw_text']))
    if occurrence>len(matches):bad('Câu trích không khớp nguyên văn nguồn.','quote_mismatch')
    match=matches[occurrence-1];a,b=match.span();exact=source['raw_text'][a:b];ranges=[]
    for seg in source['segments']:
        left=max(a,seg['raw_start']);right=min(b,seg['raw_end'])
        if left>=right:continue
        start=seg['char_start']+left-seg['raw_start'];end=seg['char_start']+right-seg['raw_start']
        page=seg['page_number']
        if not 1<=page<=doc['total_pages'] or index['canonical']['text'][start:end]!=source['raw_text'][left:right]:bad('Không định vị được câu trích trong PDF.','citation_location')
        page_info=index['canonical']['pages'][page-1]
        if not page_info['start']<=start<end<=page_info['end']:bad('Vị trí câu trích sai trang.','citation_location')
        ranges.append({'char_start':start,'char_end':end,'page_number':page,'page_index':page-1,'line_id':seg['line_id']})
    if not ranges:bad('Câu trích không có vị trí gốc.','citation_location')
    if source['source_type']!='table_cell':
        cells={s['cell_id'] for s in index['sources'].values() if s.get('cell_id') and s['page_number']==source['page_number'] and any(
            r['char_start']<seg['char_end'] and r['char_end']>seg['char_start'] for r in ranges for seg in s['segments'])}
        if len(cells)>1:bad('Câu trích đi qua nhiều ô bảng. Cần trích từng ô riêng với ngữ cảnh trường.','cross_cell_quote')
    quote_section=section_at(index,ranges[0]['char_start'])
    eid='ev_'+hashlib.sha256(repr([check_id,sid,a,b]).encode()).hexdigest()[:20]
    return {'evidence_id':eid,'source_id':sid,'source_type':source['source_type'],'quote':exact,
        'section_path':quote_section.get('path',[]),'section_title':quote_section.get('title',''),
        'retrieval_context':('Mục chứa câu trích: '+' > '.join(quote_section.get('path',[]))+'\n'+source['retrieval_context']),'expanded_context':source['expanded_context'],
        'table_id':source.get('table_id'),'cell_id':source.get('cell_id'),'row':source.get('row'),'column':source.get('column'),
        'row_label':source.get('row_label'),'table_title':source.get('table_title'),
        'document_id':doc['document_id'],'document_version_id':doc['version_id'],'document_sha256':doc['sha256'],
        'page_index':source['page_index'],'page_number':source['page_number'],'char_start':ranges[0]['char_start'],
        'char_end':ranges[-1]['char_end'],'char_ranges':ranges,'source_quote_start':a,'source_quote_end':b,
        'occurrence':occurrence,'text_match':'VERIFIED','semantic_support':'NOT_VERIFIED','quads':[]}


def validate_matches(data,checks,index):
    values=keyed(data,'checks',[c['check_id'] for c in checks]);output={}
    for check in checks:
        cid=check['check_id'];row=values[cid];status=row.get('status');raw=row.get('evidence');finding=row.get('finding')
        if status not in ('CANDIDATE','NOT_FOUND','NEEDS_VISUAL_REVIEW') or not isinstance(raw,list) or len(raw)>3:bad('Định dạng đối chiếu không hợp lệ.')
        if status!='CANDIDATE':
            # A provider explanation for NOT_FOUND/manual review is not evidence.
            # Discard it rather than promote it or fail unrelated criteria.
            if not isinstance(finding,str):bad('Nhận định không đúng kiểu dữ liệu.')
            raw=[];finding=''
        else:finding=short_text(finding,700,80,True)
        evidence=[];errors=[]
        for ref in raw:
            try:
                e=validate_quote(ref,index,set(check['candidate_ids']),cid,check.get('max_quote_chars',600))
                if check.get('coverage_target') and not all(any(span['start']<=r['char_start']<r['char_end']<=span['end'] for span in check['coverage_target']['ranges']) for r in e['char_ranges']):bad('Câu trích thuộc phần khác với mục đang kiểm tra.','citation_scope')
                evidence.append(e)
            except ApiError as exc:errors.append({'code':exc.code,'message':str(exc)})
        if status=='CANDIDATE' and (not raw or not finding):bad('Nhận định đề xuất phải có câu trích.')
        output[cid]={'check_id':cid,'status':status,'finding':finding,'evidence':evidence,'citation_errors':errors}
    return output


def source_guard(check,matching):
    evidence=matching['evidence'];q=' '.join(e['quote'] for e in evidence);n=normalized(q)
    if not evidence:return None
    types={e['source_type'] for e in evidence}
    if check.get('coverage_family') in ('business','usecase'):
        target=normalized(check.get('coverage_target',{}).get('name',''))
        names_only=target and all(normalized(e['quote'])==target for e in evidence)
        labels_only=all(e.get('row_label') and any(x in normalized(e['row_label']).replace(' ','') for x in ['tenchucnang','tenusecase','tacnhan','actor']) for e in evidence)
        if names_only or labels_only:
            return 'DOES_NOT_SUPPORT','Nguồn chỉ nêu tên chức năng/tác nhân, chưa mô tả luồng xử lý hoặc quy tắc nghiệp vụ của chức năng.'
    demand=normalized(check.get('requirement_text','')+' '+check.get('description',''))
    context=' '.join(normalized(' '.join(e.get('section_path',[]))+' '+e.get('section_title','')) for e in evidence)
    if check.get('kind')=='actors' and 'nhom nguoi dung' in demand and any(v in context for v in ['use case','usecase']) and not re.search(r'use.?case',demand):
        return 'DOES_NOT_SUPPORT','Tác nhân của một Use Case không đại diện đầy đủ các nhóm người dùng của hệ thống.'
    implementation=check.get('kind')=='implementation'
    if implementation:
        if any(any(v in normalized(' '.join(e.get('section_path',[]))+' '+e.get('section_title','')) for v in ['use case','usecase','dac ta use','khao sat','phan tich he thong','thiet ke he thong']) for e in evidence):
            return 'DOES_NOT_SUPPORT','Nguồn mô tả khảo sát/phân tích/thiết kế, không chứng minh chức năng đã cài đặt.'
        if all(e.get('row_label') and any(v in normalized(e['row_label']) for v in ['tac nhan','ten chuc nang','ten use']) for e in evidence):
            return 'DOES_NOT_SUPPORT','Tên chức năng hoặc tác nhân không chứng minh việc cài đặt chức năng.'
    if all(re.search(r'(?:chuong|bao cao|phan nay).{0,45}(?:da |se )?(?:gioi thieu|trinh bay|tong quan|de cap)',normalized(e['quote'])) for e in evidence) and not any(v in demand for v in ['bo cuc','cau truc bao cao','ket chuong','tong ket']):
        return 'DOES_NOT_SUPPORT','Đoạn này chỉ nói chương/báo cáo đã trình bày chủ đề, chưa cung cấp nội dung thực chất của yêu cầu.'

    asks_structure=any(v in demand.replace(' ','') for v in ['bocuc','cautrucbaocao','motacautruc'])
    if not asks_structure and any(any(v in normalized(e.get('section_title','')).replace(' ','') for v in ['motacautruc','bocucbaocao','cautrucbaocao']) for e in evidence):
        return 'DOES_NOT_SUPPORT','Đoạn này chỉ giới thiệu bố cục báo cáo, không trình bày nội dung cụ thể được yêu cầu.'
    if any(x in demand for x in ['dac ta','nghiep vu','phan tich chuc nang']) and all(any(x in normalized(e.get('section_title','')) for x in ['ket chuong','gioi thieu','co so ly thuyet']) for e in evidence):
        return 'DOES_NOT_SUPPORT','Nguồn chỉ giới thiệu/tổng kết, không thay thế nội dung đặc tả hoặc quy tắc nghiệp vụ cụ thể.'
    topic=re.search(r'use\s*case\s+([^:\n.,;]+)',check.get('requirement_text','') or check.get('description',''),re.I)
    if topic:
        wanted=normalized(topic.group(1)).replace(' ','')
        if any(wanted.startswith(x) for x in ['va','cac','hethong','nhung']):wanted=''
        for e in evidence:
            actual=re.search(r'use\s*case\s+(.+)',e.get('section_title',''),re.I)
            if actual and wanted and wanted not in normalized(actual.group(1)).replace(' ',''):
                return 'DOES_NOT_SUPPORT','Câu trích nằm trong đặc tả Use Case khác với yêu cầu, không dùng trường cùng tên của chức năng khác.'
    if any(re.search(r'(?:với mục đích|bao gồm|như sau|giúp|để|và|bằng|của|là)\s*[:;,]?$',e['quote'].strip(),re.I) for e in evidence):
        return 'DOES_NOT_SUPPORT','Câu trích bị cắt trước phần nội dung giải thích; chưa đủ ý để hỗ trợ nhận định.'
    requested=normalized(check.get('requirement_excerpt','')+' '+check.get('description',''))
    diagram=check['kind'] in ('diagram','class_diagram','sequence_diagram') or 'so do' in requested or 'erd' in requested
    if diagram and check['kind']!='existence':
        return 'NEEDS_VISUAL_REVIEW','Nội dung sơ đồ chưa được đọc; nhắc tên, chú thích hoặc đặc tả bằng chữ không xác minh được sơ đồ theo yêu cầu.'
    if check['kind']=='data_model' and re.search(r'cac bang co khoa chinh|lien ket voi nhau bang khoa|phan tach thanh cac bang|^lien ket thong qua khoa chinh va khoa ngoai$|^moi quan he giua cac bang$',n):
        return 'DOES_NOT_SUPPORT','Câu trích chỉ nói chung về bảng và khóa, chưa chỉ ra bảng/trường hoặc quan hệ cụ thể của thiết kế.'
    explanatory='giai thich' in requested or any(v in requested for v in ['luong xu ly','luong chinh','cac buoc'])
    if explanatory and re.search(r'cau truc du lieu luong xu ly|muc tieu|lam co so|chuyen .* thanh|huong den',n):
        actions=sum(bool(re.search(r'\b'+verb+r'\b',n)) for verb in ['chon','kiem tra','luu','nop','gui','phe duyet','nhap','tu choi','cap nhat','nhan xet'])
        if actions<2:return 'DOES_NOT_SUPPORT','Nguồn chỉ giới thiệu mục tiêu/phạm vi thiết kế, chưa giải thích thành phần hoặc các bước xử lý cụ thể.'
    if types<=set(['figure_caption','heading','contents_listing']) and check['kind']!='existence':
        return ('NEEDS_VISUAL_REVIEW' if check['kind'] in ('diagram','class_diagram','sequence_diagram') else 'DOES_NOT_SUPPORT','Chỉ có tiêu đề/chú thích, chưa có nội dung đủ hỗ trợ.')
    if check['kind'] in ('diagram','class_diagram','sequence_diagram') and len(n.split())<35 and re.search(r'so do|hinh \d|figure',n):
        return 'NEEDS_VISUAL_REVIEW','Chưa đọc nội dung hình/sơ đồ; cần kiểm tra PDF trực tiếp.'
    if check['kind'] in ('workflow','main_flow','alternative_flow') and re.search(r'muc tieu|lam co so|chuyen .* thanh|huong den|duoc trinh bay',n):
        actions=sum(bool(re.search(r'\b'+verb+r'\b',n)) for verb in ['chon','kiem tra','luu','nop','gui','phe duyet','nhap','tu choi','cap nhat','nhan xet'])
        if actions<2 and len(re.findall(r'(?:buoc\s*\d|\d[.)])',q,re.I))<2:return 'DOES_NOT_SUPPORT','Câu trích chỉ nêu mục tiêu/giới thiệu, chưa giải thích các bước của luồng xử lý.'
    if any(review_pending_claim('',e['quote']) for e in evidence):return 'DOES_NOT_SUPPORT','Nguồn có TODO/TBD; chưa có nội dung hoàn chỉnh cho tiêu chí.'
    if check['kind']=='implementation' and re.search(r'du kien|se trien khai|se xay dung|chua trien khai|chua cai dat',n):return 'DOES_NOT_SUPPORT','Nguồn là dự kiến/phủ định, không chứng minh đã triển khai.'
    return None


def validate_verifications(data,checks):
    rows=keyed(data,'checks',[c['check_id'] for c in checks]);clean={}
    for c in checks:
        row=rows[c['check_id']];verdict=row.get('verdict');ids=row.get('supported_evidence_ids');allowed={e['evidence_id'] for e in c['evidence']}
        if verdict not in VERDICTS or type(row.get('overclaim_detected')) is not bool or not isinstance(ids,list) or len(ids)!=len(set(ids)) or any(i not in allowed for i in ids):bad('Kiểm chứng có mã nguồn hoặc trạng thái không hợp lệ.','invalid_verification')
        if verdict in ('SUPPORTS','PARTIAL') and not ids:bad('Không thể xác nhận hỗ trợ mà không có nguồn.','invalid_verification')
        if verdict not in ('SUPPORTS','PARTIAL') and ids:bad('Nguồn bị bác bỏ không được giữ làm dẫn chứng hỗ trợ.','invalid_verification')
        for field in ('supported_elements','missing_elements'):
            if not isinstance(row.get(field),list) or len(row[field])>20 or any(not isinstance(x,str) or len(x)>250 for x in row[field]):bad('Danh sách ý kiểm chứng không hợp lệ.')
        clean[c['check_id']]={**row,'explanation':short_text(row.get('explanation'),800,120),
            'safe_rewrite':short_text(row.get('safe_rewrite'),700,80,True)}
    return clean
