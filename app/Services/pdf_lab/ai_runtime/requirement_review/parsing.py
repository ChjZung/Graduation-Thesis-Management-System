"""Preserve original item boundaries, numbering, introductory text and global conditions."""
import hashlib
import re
from ..provider import ApiError

CONDITION=re.compile(r'^(?:#{1,6}\s*)?(?:\*\*)?(?:mỗi nội dung|chỉ xác nhận|không dùng|không tự suy|lưu ý|điều kiện chung|yêu cầu chung|phạm vi đối chiếu|nội dung cần|báo cáo cần|các sơ đồ phải|chưa yêu cầu|không yêu cầu|chỉ yêu cầu|chỉ có|đợt này|không bắt buộc)',re.I)
NUMBER=re.compile(r'(?m)^(?P<indent>[ \t]*)(?P<number>\d+)(?P<marker>[.)])[ \t]+')


def sha(text):return hashlib.sha256(text.encode('utf-8')).hexdigest()


def parse_requirements(text,set_id='manual',version=None):
    if not isinstance(text,str) or not text.strip() or len(text)>250000:
        raise ApiError('Mô tả yêu cầu phải có nội dung và tối đa 250.000 ký tự.','invalid_requirements')
    matches=list(NUMBER.finditer(text));selected=[];warnings=[]
    if matches:
        indent=min(len(m['indent'].expandtabs(4)) for m in matches)
        for m in matches:
            if len(m['indent'].expandtabs(4))!=indent:continue
            if selected and int(m['number'])<=int(selected[-1]['number']):
                warnings.append('Số thứ tự lặp/giảm được giữ trong nội dung mục trước; hãy thụt lề rõ các ý con.')
                continue
            selected.append(m)
    else:
        bullets=list(re.finditer(r'(?m)^(?P<indent>[ \t]*)[-•][ \t]+',text))
        if bullets:
            indent=min(len(m['indent'].expandtabs(4)) for m in bullets)
            selected=[m for m in bullets if len(m['indent'].expandtabs(4))==indent]
    items=[];conditions=[]
    def trim_span(start,end):
        while start<end and text[start].isspace():start+=1
        while end>start and text[end-1].isspace():end-=1
        return start,end
    if selected:
        intro=text[:selected[0].start()].strip()
        spans=[(m,selected[n+1].start() if n+1<len(selected) else len(text)) for n,m in enumerate(selected)]
        for index,(m,end) in enumerate(spans):
            body_start=m.end();body_end=end
            # Conditions can start immediately after a numbered item, not only after a blank line.
            for line in re.finditer(r'(?m)^[^\r\n]*',text[body_start:end]):
                raw=line.group();stripped=raw.strip()
                if CONDITION.match(stripped) and len(raw)-len(raw.lstrip())<=len(m['indent']):
                    body_end=body_start+line.start();cstart,cend=trim_span(body_end,end)
                    if cend>cstart:conditions.append({'text':text[cstart:cend],'char_start':cstart,'char_end':cend})
                    break
            start,stop=trim_span(body_start,body_end)
            if stop<=start:raise ApiError('Có mục yêu cầu rỗng. Hãy bổ sung nội dung.','invalid_requirements')
            whole_start,whole_end=trim_span(m.start(),body_end)
            items.append({'item_id':f'req_{index+1:03}','number':m.groupdict().get('number'),
                'marker':m.groupdict().get('marker','-'),'original_text':text[start:stop],
                'original_label':text[whole_start:whole_end], 'char_start':start,'char_end':stop,'checks':[]})
    else:
        intro='';blocks=list(re.finditer(r'[^\r\n]+(?:(?:\r?\n)(?![ \t]*\r?\n)[^\r\n]+)*',text))
        for block in blocks:
            start,end=trim_span(block.start(),block.end());value=text[start:end]
            if not value:continue
            if CONDITION.match(value):conditions.append({'text':value,'char_start':start,'char_end':end});continue
            if not items and re.search(r'(?:gồm|bao gồm|như sau)\s*:\s*$',value,re.I):intro=value;continue
            items.append({'item_id':f'req_{len(items)+1:03}','number':None,'marker':None,'original_text':value,
                'original_label':value,'char_start':start,'char_end':end,'checks':[]})
    if not items or len(items)>100:raise ApiError('Cần từ 1 đến 100 mục yêu cầu.','invalid_requirements')
    return {'id':set_id,'version':version or sha(text)[:16],'sha256':sha(text),'raw_text':text,'intro':intro,
        'general_conditions':conditions,'items':items,'warnings':list(dict.fromkeys(warnings))}
