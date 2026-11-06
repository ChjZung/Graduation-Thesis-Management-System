"""Conservative subject/type gating, not a semantic accuracy probability."""
import re
import unicodedata

VERSION = 'requirement-scope-5'
PATTERNS = {
 'database':r'co so du lieu|\bcsdl\b|\berd\b|database|mo hinh quan he|quan he du lieu|nhom du lieu|bang du lieu|khoa chinh|khoa ngoai|primary key|foreign key',
 'class':r'so do lop|bieu do lop|class diagram',
 'sequence':r'tuan tu|sequence',
 'activity':r'hoat dong|activity',
 'usecase':r'use\s*case|usecase|ca su dung',
 'ui':r'giao dien|man hinh|frontend',
 'testing':r'kiem thu|thu nghiem|test case',
}
SUBJECTS = ['dang ky de tai','quan ly de tai','de xuat de tai','tien do','ho so bao ve','hoi dong','cham diem','sinh vien','dang nhap','nguoi dung']
UI_SUBJECTS = [s for s in SUBJECTS if s not in ('sinh vien', 'nguoi dung')]
UI_SUBJECTS += ['quan ly cau hoi', 'de thi', 'lich thi', 'ket qua thi']
def norm(text):
    text=unicodedata.normalize('NFD',str(text).casefold().replace('đ','d'))
    return ' '.join(''.join(c for c in text if not unicodedata.combining(c)).split())
def families(text):
    text=norm(text);return {k for k,p in PATTERNS.items() if re.search(p.replace(' ',r'\s*'),text)}
def relevant(req,text):
    r,t=norm(req),norm(text);wanted=families(r);got=families(t)
    if wanted and not wanted.intersection(got):return False
    rc,tc=r.replace(' ',''),t.replace(' ','')
    if 'thietke' in rc and 'testing' not in wanted and 'testing' in got and 'thietke' not in tc:return False
    if 'class' in wanted and ('mucthietke' in rc or 'thietkesodolop' in rc):
        if 'phantich' in tc and not ('mucthietke' in tc or 'thietkesodolop' in tc):return False
    if 'class' in wanted and 'mucphantich' in rc and 'mucthietke' not in rc:
        if 'mucthietke' in tc and 'mucphantich' not in tc:return False
    if wanted.intersection({'sequence','usecase','activity'}):
        subjects=[s.replace(' ','') for s in SUBJECTS if s.replace(' ','') in rc]
        if subjects and not any(s in tc for s in subjects):return False
    if 'ui' in wanted:
        modules=[s.replace(' ','') for s in UI_SUBJECTS if s.replace(' ','') in rc]
        if modules and not any(s in tc for s in modules):return False
    if wanted:return True
    stop=set('thiet ke so do quan ly he thong bao cao trinh bay cac va cua noi dung xay dung'.split())
    tokens=lambda s:set(re.findall(r'[a-z0-9]+',s))-stop
    return len(tokens(r)&tokens(t))>=2

def select_candidate(req,cand):
    refs=[];summaries=[];seen=set()
    for observation in cand.get('observations',[]):
        content=observation.get('content','')
        if not relevant(req,content):continue
        accepted=[]
        for ref in observation.get('source_references',[]):
            if not relevant(req,ref.get('quote','')):continue
            page=ref.get('page');start=cand.get('start_page');end=cand.get('end_page')
            if page and start and end and not start<=page<=end:continue
            key=(page,ref.get('quote',''))
            if key not in seen:seen.add(key);refs.append(ref)
            accepted.append(ref)
        if accepted and content not in summaries:summaries.append(content)
    return dict(cand,source_references=refs,inherited_references=[],summary=' '.join(summaries))
