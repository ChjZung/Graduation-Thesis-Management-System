"""BM25 + phrase/structure retrieval over original PDF sources, per criterion."""
import math
import re
from collections import Counter
from .source_index import normalized

VERSION='requirement-retrieval-2.5'

STOP=set('va la cua cac nhung mot cho voi trong theo co de khi nay bao cao he thong noi dung yeu cau trinh bay mo ta'.split())
FACETS=[('bai toan',['bai toan','boi canh','hien trang','dat van de','ly do chon','su can thiet','mo dau']),('muc tieu',['muc tieu de tai','muc tieu nghien cuu']),('pham vi',['pham vi','gioi han de tai']),('nhu cau',['nhu cau','yeu cau nguoi dung','doi voi']),('kho khan',['kho khan','han che','bat cap','danh gia hien trang']),('giai quyet',['giai phap','giai quyet','de xuat'])]

LABELS={'actors':['tac nhan','actor'],'precondition':['tien dieu kien','precondition'],
    'main_flow':['luong chinh','luong hoat dong chinh','main flow'],'alternative_flow':['luong phu','luong ngoai le','ngoai le','alternative'],
    'postcondition':['hau dieu kien','postcondition']}



def source_label(source):
    value=source.get('row_label') or source.get('field_label')
    if value:return normalized(value)
    first=source['raw_text'].splitlines()[0]
    label=first.split(':',1)[0].lstrip('-• ').strip()
    folded=normalized(label)
    if ':' in first and any(alias.replace(' ','') in folded.replace(' ','') for aliases in LABELS.values() for alias in aliases):return folded
    return ''


def named_usecase(text):
    match=re.search(r'use\s*case\s+([^:\n.,;]+)',text,re.I)
    if not match:return ''
    value=normalized(match.group(1)).replace(' ','')
    return '' if any(value.startswith(x) for x in ['va','cac','hethong','nhung']) else value


def intents(check):
    description=normalized(check['description']).replace(' ','')
    excerpt=normalized(check.get('requirement_excerpt','')).replace(' ','')
    def detect(value):
        found=[]
        if any(x in value for x in ['huongphattrien','kehoach','congviectieptheo','congviecdukien','dukienthuchien','thuchientiep']):found.append('future')
        if any(x in value for x in ['hanche','chuahoanthien','loicontontai','tontai']):found.append('limitations')
        return found
    return detect(description) or detect(excerpt)

def words(text):return [w for w in normalized(text).split() if len(w)>1 and w not in STOP]


def grams(text):
    ts=normalized(text).split()
    return {tuple(ts[n:n+k]) for k in (2,3,4) for n in range(len(ts)-k+1)}


class SourceSearch:
    def __init__(self,index):
        self.index=index;self.sources=index['sources'];self.documents={};self.lengths={};self.grams={};self.df=Counter()
        for sid,source in self.sources.items():
            text=' '.join(source.get('section_path',[]))+' '+source.get('table_title','')+' '+source.get('row_label','')+' '+source_label(source)+' '+source['raw_text']
            ts=words(text);self.documents[sid]=Counter(ts);self.lengths[sid]=len(ts);self.df.update(set(ts));self.grams[sid]=grams(text)
        self.n=max(1,len(self.documents));self.average=max(1,sum(self.lengths.values())/self.n)

    def retrieve(self,item,check,limit=6):
        topic=item['original_text'].split(':',1)[0]
        purposes=intents(check);wanted=named_usecase(item['original_text'])
        criterion=normalized(check['description'])
        aliases=next((terms for term,terms in FACETS if term in criterion),[])
        project_facet=bool(aliases and 'de tai' in normalized(item['original_text']) and any(v in criterion for v in ['bai toan','muc tieu','pham vi']))
        extra=' '.join(aliases)+' '+' '.join(LABELS.get(check.get('kind'),[]))
        if 'future' in purposes:extra+=' hướng phát triển kế hoạch dự kiến công việc tiếp theo'
        if 'limitations' in purposes:extra+=' hạn chế tồn tại chưa hoàn thiện'
        primary=set(words(check['description']+' '+check.get('requirement_excerpt','')+' '+extra))
        query=words(topic+' '+check['description']+' '+extra);qgrams=grams(check['description']+' '+check.get('requirement_excerpt',''));scored=[]
        for sid,freq in self.documents.items():
            score=0.;length=self.lengths[sid];source=self.sources[sid]
            if check.get('coverage_target'):
                from .coverage import source_in_target
                if not source_in_target(source,check['coverage_target']):continue
            for t in set(query):
                if not freq[t]:continue
                idf=math.log(1+(self.n-self.df[t]+.5)/(self.df[t]+.5))
                score+=(2. if t in primary else .4)*idf*freq[t]*2.2/(freq[t]+1.2*(.25+.75*length/self.average))
            if not score:continue
            common=qgrams & self.grams[sid];score+=sum(min(3,len(g)-1) for g in common)*.7
            labels=LABELS.get(check.get('kind'),[]);label=source_label(source)
            if check.get('coverage_family') in ('business','usecase') and source['source_type']=='table_cell':
                compact=label.replace(' ','')
                if any(v in compact for v in ['luongchinh','luonghoatdongchinh','luongphu','luongngoaile','tiendieukien','haudieukien']):score*=3
                elif any(v in compact for v in ['tacnhan','tenchucnang']):score*=.25
            if labels and any(v.replace(' ','') in label.replace(' ','') for v in labels):score*=2.6
            if source['source_type'] in ('table_cell','usecase_field'):
                score*=1.2
                if normalized(source['raw_text'])==label:score*=.2
            elif source['source_type'] in ('heading','figure_caption','contents_listing'):score*=.1
            elif source['source_type']=='page_context':score*=.65
            section=normalized(source.get('section_title','')).replace(' ','')
            demand=normalized(item['original_text']).replace(' ','')
            if any(v in section for v in ['motacautruc','bocucbaocao','cautrucbaocao']) and not any(v in demand for v in ['bocuc','cautrucbaocao','motacautruc']):score*=.02
            if check.get('kind')=='implementation':
                context=normalized(' '.join(source.get('section_path',[]))+' '+source.get('section_title',''))
                if any(v in context for v in ['use case','usecase','khao sat','phan tich he thong','thiet ke he thong']):score*=.015
                if any(v in context for v in ['cai dat','trien khai','thuc nghiem']):score*=2.5
            source_context=normalized(' '.join(source.get('section_path',[])))
            if project_facet and 'mo dau' in source_context:score*=4
            if aliases and any(v in source_context for v in ['co so ly thuyet','tong quan ly thuyet']):score*=.01
            if any(v in section for v in ['ketchuong','ketluan','tongket']) and not any(v in demand for v in ['ketchuong','ketluan','tongket','ketqua']):score*=.03
            # Prefer the section answering this criterion, rather than a sibling
            # matching the broad parent requirement.
            title_words=set(words(source.get('section_title','')))
            criterion_words=set(words(check['description']))
            score*=1+min(2,len(title_words & criterion_words)*.35)
            actual=named_usecase(source.get('section_title',''))
            if wanted and actual and wanted not in actual:score*=.08
            scored.append((score,sid))
        ranked=sorted(scored,key=lambda x:(-x[0],x[1]));picked=[];seen=set()
        for score,sid in ranked:
            source=self.sources[sid]
            # Exact duplicate text/geometry never consumes another candidate slot.
            signature=(source['page_number'],source['char_start'],source['char_end'],source['raw_text'])
            if signature in seen:continue
            seen.add(signature);picked.append({'source_id':sid,'retrieval_score':round(score,4)})
            if len(picked)>=limit:break
        if check.get('kind') in ('diagram','class_diagram','sequence_diagram'):
            # Keep a directly relevant caption for manual review even when its text
            # score is lower than long prose; its presence never proves content.
            captions=[(score,sid) for score,sid in ranked if self.sources[sid]['source_type']=='figure_caption']
            if captions and all(p['source_id']!=captions[0][1] for p in picked):
                hit={'source_id':captions[0][1],'retrieval_score':round(captions[0][0],4)}
                picked=(picked[:limit-1]+[hit])
        # Keep the original candidate set and add bounded original-source anchors.
        # These improve coverage; they never count as verified evidence.
        anchors=[]
        if aliases:
            anchor_ranked=sorted(ranked,key=lambda h:(0 if 'mo dau' in normalized(' '.join(self.sources[h[1]].get('section_path',[]))) else 1,-h[0])) if project_facet else ranked
            for score,sid in anchor_ranked:
                source=self.sources[sid];title=normalized(source.get('section_title',''))
                if source['source_type'] in ('heading','figure_caption','contents_listing','page_context'):continue
                if any(v in title for v in ['ket chuong','ket luan','tong ket']):continue
                context=normalized(' '.join(source.get('section_path',[])))
                if any(v in context for v in ['co so ly thuyet','tong quan ly thuyet']):continue
                if any(term in title for term in aliases):
                    anchors.append({'source_id':sid,'retrieval_score':round(score,4)})
                    if len(anchors)>=2:break
        for purpose in (['field'] if LABELS.get(check.get('kind')) else [])+purposes:
            count=0
            for score,sid in ranked:
                source=self.sources[sid]
                if source['source_type'] in ('heading','figure_caption','contents_listing','page_context'):continue
                actual=named_usecase(source.get('section_title',''))
                if wanted and actual and wanted not in actual:continue
                if purpose=='field':
                    label=source_label(source)
                    valid=label and any(v.replace(' ','') in label.replace(' ','') for v in LABELS[check['kind']]) and normalized(source['raw_text'])!=label
                else:
                    title=normalized(source.get('section_title','')).replace(' ','')
                    valid=any(v in title for v in (['huongphattrien','kehoach','futurework'] if purpose=='future' else ['hanche','limitations']))
                if not valid:continue
                anchors.append({'source_id':sid,'retrieval_score':round(score,4)});count+=1
                if count==2:break
        result=[];ids=set()
        for hit in anchors+picked:
            if hit['source_id'] not in ids:ids.add(hit['source_id']);result.append(hit)
        return result
