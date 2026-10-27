"""Original text plus spatially bounded table cells. No summary data enters this index."""
import hashlib
import re
import time
import unicodedata
import pymupdf as fitz
from core.pdf_extractor import extract_pdf,source_bytes
from ..provider import ApiError

VERSION='pdf-source-index-2.3'


def normalized(text):
    text=unicodedata.normalize('NFD',text.casefold().replace('đ','d'))
    return ' '.join(re.findall(r'\w+',''.join(c for c in text if not unicodedata.combining(c))))


def build_source_index(pdf,document_id=None,version_id=None,on_progress=None):
    started=time.monotonic();data=source_bytes(pdf);doc=extract_pdf(data);digest=doc['sha256']
    identity={'document_id':document_id or 'pdf_'+digest[:16],'version_id':version_id or digest,
        'sha256':digest,'total_pages':doc['page_count']}
    sources={};tables=[];warnings=[]
    by_page={p['page']:[l for l in doc['lines'] if l['page']==p['page']] for p in doc['pages']}
    def add(kind,lines,page,extra=None):
        if not lines:return None
        parts=[];segments=[];pos=0
        for line in lines:
            value=line['text'];left=len(value)-len(value.lstrip());right=len(value.rstrip())
            if right<=left:continue
            raw=value[left:right]
            if parts:parts.append('\n');pos+=1
            parts.append(raw);segments.append({'raw_start':pos,'raw_end':pos+len(raw),
                'char_start':line['start']+left,'char_end':line['start']+right,'line_id':line['line_id'],
                'block_id':line['block_id'],'bbox':line['bbox'],'page_index':page['page_index'],'page_number':page['page']})
            pos+=len(raw)
        raw=''.join(parts)
        if not raw:return None
        tag=(extra or {}).get('cell_id') or (lines[0]['line_id']+'-'+lines[-1]['line_id'])
        sid=f'{digest[:12]}:p{page["page"]}:{kind}:{tag}'
        record={'source_id':sid,'document_id':identity['document_id'],'document_version_id':identity['version_id'],
            'document_sha256':digest,'source_type':kind,'page_index':page['page_index'],'page_number':page['page'],
            'raw_text':raw,'normalized_text':normalized(raw),'segments':segments,
            'char_start':min(s['char_start'] for s in segments),'char_end':max(s['char_end'] for s in segments),
            'block_ids':list(dict.fromkeys(s['block_id'] for s in segments)),
            'line_ids':[s['line_id'] for s in segments],'rotation':page.get('rotation',0),
            'retrieval_context':raw,'expanded_context':raw}
        if extra:record.update(extra)
        sources[sid]=record;return sid
    with fitz.open(stream=data,filetype='pdf') as pdfdoc:
        previous=None
        for page_info in doc['pages']:
            page=pdfdoc[page_info['page_index']];page_info['rotation']=page.rotation;page_info['cropbox']=list(page.cropbox)
            lines=by_page[page_info['page']];inside=set()
            if on_progress:on_progress(f'Lập chỉ mục trang {page_info["page"]}/{doc["page_count"]}')
            try:found=page.find_tables().tables
            except Exception:
                found=[];warnings.append({'page':page_info['page'],'code':'table_extraction_failed'})
            for ti,table in enumerate(found):
                table_id=f'p{page_info["page"]}_t{ti+1}';matrix=table.extract()
                before=[l['text'].strip() for l in lines if l['bbox'][3]<=table.bbox[1]+3 and l['text'].strip()]
                caption=next((v for v in reversed(before[-8:]) if re.search(r'(?:bảng|table)\s*[\d.]',v,re.I)),'')
                title=caption or 'Bảng văn bản'
                continuation=None
                if not caption and previous and previous['page']==page_info['page']-1 and previous['columns']==table.col_count and table.bbox[1]<page.rect.height*.25 and previous['bottom']>previous['height']*.75:
                    continuation=previous['table_id'];title=previous['title']
                meta={'table_id':table_id,'page_number':page_info['page'],'title':title,'rows':table.row_count,
                    'columns':table.col_count,'bbox':list(table.bbox),'possible_continuation_of':continuation,'cells':[]}
                carry_label='';carry_label_id=None
                for ri,row in enumerate(table.rows):
                    nonempty=[ci for ci,val in enumerate(matrix[ri]) if val and val.strip()]
                    label=(matrix[ri][nonempty[0]].strip() if len(nonempty)>1 else carry_label) if nonempty else carry_label
                    label_id=carry_label_id;row_records=[]
                    for ci,rect in enumerate(row.cells):
                        if rect is None:continue
                        selected=[l for l in lines if rect[0]-.5<=(l['bbox'][0]+l['bbox'][2])/2<=rect[2]+.5 and rect[1]-.5<=(l['bbox'][1]+l['bbox'][3])/2<=rect[3]+.5]
                        cell_id=f'{table_id}_r{ri+1}_c{ci+1}'
                        sid=add('table_cell',selected,page_info,{'table_id':table_id,'cell_id':cell_id,'row':ri+1,'column':ci+1,
                            'cell_bbox':list(rect),'table_title':title,'row_label':label,'row_label_source_id':label_id,
                            'possible_continuation_of':continuation})
                        if sid:
                            inside.update(l['line_id'] for l in selected);row_records.append(sid);meta['cells'].append(sid)
                            if ci==(nonempty[0] if nonempty else -1) and len(nonempty)>1:label_id=sid
                    if len(nonempty)>1:carry_label=label;carry_label_id=label_id
                    for sid in row_records:
                        sources[sid]['row_label_source_id']=label_id
                        sources[sid]['retrieval_context']=f'Bảng: {title}\nTrường: {label}\nNội dung ô: '+sources[sid]['raw_text']
                        sources[sid]['expanded_context']=sources[sid]['retrieval_context']
                tables.append(meta);previous={'page':page_info['page'],'table_id':table_id,'columns':table.col_count,'bottom':table.bbox[3],'height':page.rect.height,'title':title}
            # Keep genuine paragraph blocks, with line-based offsets. A source never
            # concatenates text from different cells to fabricate an original sentence.
            blocks={}
            for line in lines:
                if line['line_id'] in inside or line.get('running_margin'):continue
                blocks.setdefault(line['block_id'],[]).append(line)
            for block_lines in blocks.values():
                group=[];size=0
                for line in block_lines:
                    if size>1500 and group:add('text_block',group,page_info);group=[];size=0
                    group.append(line);size+=len(line['text'])
                if group:
                    raw=' '.join(l['text'] for l in group).strip()
                    kind='text_block'
                    if re.match(r'^(?:hình|figure)\s*[\d.]',raw,re.I):kind='figure_caption'
                    elif len(raw)<150 and (group[0].get('bold') or re.match(r'^\d+(?:\.\d+)+\.?\s',raw)):kind='heading'
                    if len(re.findall(r'\.{4,}',raw))>=2:kind='contents_listing'
                    add(kind,group,page_info)
            # Page windows provide context across neighbouring blocks. They are real
            # contiguous original text and remain distinguishable from table cells.
            for at in range(page_info['start'],page_info['end'],1600):
                stop=min(at+2000,page_info['end']);selected=[l for l in lines if l['start']>=at and l['start']<stop and not l.get('running_margin')]
                if selected and sum(len(l['text']) for l in selected)>100:
                    add('page_context',selected,page_info)
    result={'index_version':VERSION,'document':identity,'canonical':doc,'sources':sources,'tables':tables,
        'warnings':warnings,'statistics':{'sources':len(sources),'tables':len(tables),'table_cells':sum(s['source_type']=='table_cell' for s in sources.values()),'seconds':round(time.monotonic()-started,3)}}
    return add_section_context(result)


def add_section_context(index):
    sections=[];stack=[]
    for line in index['canonical']['lines']:
        value=line['text'].strip()
        if re.search(r'\.{3,}',value):continue
        match=re.match(r'^(\d+(?:\.\d+)+)\.?\s*(\S.*)$',value)
        chapter=re.match(r'^(?:CHƯƠNG|Chương|CHAPTER)\s*(\d+)',value,re.I)
        if match:
            level=match.group(1).count('.')+1
        elif chapter:
            tail=value[chapter.end():].strip(' :.–-')
            letters=[c for c in tail if c.isalpha()]
            if not letters or sum(c.isupper() for c in letters)/len(letters)<.75:continue
            level=0
        elif normalized(value).replace(' ','') in ('modau','ketluan','tailieuthamkhao'):level=0
        else:continue
        if len(value)>220:continue
        while stack and stack[-1]['level']>=level:stack.pop()
        entry={'char_start':line['start'],'title':value,'level':level}
        stack.append(entry);entry['path']=[h['title'] for h in stack];sections.append(entry)
    index['sections']=sections;index['index_version']=VERSION
    index['sources']={sid:source for sid,source in index['sources'].items() if source.get('source_type') not in ('text_passage','usecase_field')}
    if index.get('document') and index['canonical'].get('pages'):add_reading_passages(index)
    for source in index['sources'].values():
        section=section_at(index,source['char_start'])
        source['section_path']=section.get('path',[])
        source['section_title']=section.get('title','')
        if source.get('field_label'):source['retrieval_context']='Trường: '+source['field_label']+'\n'+source.get('retrieval_context','')
        base=source.get('table_title','')+' '+source.get('row_label','')+' '+source.get('field_label','')+'\n'+source['raw_text']
        source['retrieval_context']='Mục: '+' > '.join(source['section_path'])+'\n'+base if section else base
        source['expanded_context']=source['retrieval_context']
    return index



def add_reading_passages(index):
    # Rejoin spatially adjacent original lines, without inserting paraphrases.
    doc=index['document'];lines=index['canonical']['lines'];headings={s['char_start'] for s in index['sections']}
    table_lines={seg['line_id'] for s in index['sources'].values() if s['source_type']=='table_cell' for seg in s['segments']}
    def add(kind,group,extra=None):
        if len(group)<2:return
        parts=[];segments=[];at=0
        for line in group:
            value=line['text'];left=len(value)-len(value.lstrip());right=len(value.rstrip())
            if right<=left:continue
            if parts:parts.append('\n');at+=1
            text=value[left:right];parts.append(text)
            segments.append({'raw_start':at,'raw_end':at+len(text),'char_start':line['start']+left,'char_end':line['start']+right,
                'line_id':line['line_id'],'block_id':line['block_id'],'bbox':line['bbox'],'page_number':line['page'],'page_index':line['page']-1})
            at+=len(text)
        if not segments:return
        raw=''.join(parts);sid=f"{doc['sha256'][:12]}:p{group[0]['page']}:{kind}:{segments[0]['line_id']}-{segments[-1]['line_id']}"
        index['sources'][sid]={'source_id':sid,'document_id':doc['document_id'],'document_version_id':doc['version_id'],
            'document_sha256':doc['sha256'],'source_type':kind,'page_number':group[0]['page'],'page_index':group[0]['page']-1,
            'raw_text':raw,'normalized_text':normalized(raw),'char_start':segments[0]['char_start'],'char_end':segments[-1]['char_end'],
            'segments':segments,'block_ids':list(dict.fromkeys(x['block_id'] for x in segments)),
            'line_ids':[x['line_id'] for x in segments],'retrieval_context':raw,'expanded_context':raw,**(extra or {})}
    def field_label(text):
        match=re.match(r'^\s*[-•]?\s*([^:]{2,65}):',text)
        if not match:return None
        label=match.group(1).strip();n=normalized(label).replace(' ','')
        if n.startswith(('tacnhan','actor','tiendieukien','haudieukien','luong','motav','precondition','postcondition','mainflow','alternativeflow')):return label
        return None
    paragraph=[];field=[];label=None
    def flush_paragraph():
        if paragraph:add('text_passage',paragraph)
        paragraph.clear()
    for line in lines:
        text=line['text'].strip()
        if line['line_id'] in table_lines or line.get('running_margin'):
            flush_paragraph()
            if field:add('usecase_field',field,{'field_label':label})
            field=[];label=None;continue
        new_label=field_label(text)
        boundary=line['start'] in headings
        if field and (new_label or boundary or line['page']!=field[-1]['page'] or sum(len(x['text']) for x in field)>1800):
            add('usecase_field',field,{'field_label':label});field=[];label=None
        if new_label:field=[line];label=new_label
        elif field:field.append(line)
        starts_item=bool(re.match(r'^(?:[-+•]|\d+[.)]\s|Hình\s|Figure\s)',text,re.I))
        if paragraph:
            prev=paragraph[-1];pb=prev['bbox'];cb=line['bbox'];height=max(pb[3]-pb[1],cb[3]-cb[1],1)
            adjacent=line['page']==prev['page'] and -.3*height<=cb[1]-pb[3]<=height*.9 and abs(cb[0]-pb[0])<85
            if not adjacent or boundary or starts_item or sum(len(x['text']) for x in paragraph)>1400:flush_paragraph()
        paragraph.append(line)
    flush_paragraph()
    if field:add('usecase_field',field,{'field_label':label})
    index['statistics']['sources']=len(index['sources'])

def section_at(index,offset):
    from bisect import bisect_right
    sections=index.get('sections',[])
    at=bisect_right([s['char_start'] for s in sections],offset)-1
    return sections[at] if at>=0 else {}


def geometry_for_ranges(pdf,index,ranges):
    data=source_bytes(pdf)
    if hashlib.sha256(data).hexdigest()!=index['document']['sha256']:raise ApiError('PDF đã đổi; không mở dẫn chứng của phiên bản khác.','source_version_mismatch')
    canonical=index['canonical'];by_id={l['line_id']:l for l in canonical['lines']};quads=[]
    with fitz.open(stream=data,filetype='pdf') as doc:
        for page_number in sorted({r['page_number'] for r in ranges}):
            page=doc[page_number-1];raw=page.get_text('rawdict',sort=True,flags=fitz.TEXTFLAGS_DICT & ~fitz.TEXT_PRESERVE_IMAGES)
            for bi,block in enumerate(raw['blocks']):
                if block.get('type')!=0:continue
                for li,line in enumerate(block.get('lines',[])):
                    known=by_id.get(f'p{page_number}-b{bi}-l{li}')
                    if not known:continue
                    characters=[(span,char) for span in line['spans'] for char in span['chars']]
                    if ''.join(c['c'] for _,c in characters)!=known['text']:continue
                    groups=[];selected=[]
                    for offset,(span,char) in enumerate(characters):
                        if any(r['page_number']==page_number and r['char_start']<=known['start']+offset<r['char_end'] for r in ranges):selected.append((span,char))
                        elif selected:groups.append(selected);selected=[]
                    if selected:groups.append(selected)
                    for group in groups:
                        allq=[fitz.recover_char_quad(line['dir'],span,char) for span,char in group]
                        if line['dir'][0]>.99:
                            q=fitz.Quad(allq[0].ul,allq[-1].ur,allq[0].ll,allq[-1].lr)
                            quads.append({'page_number':page_number,'quad':list(q.ul)+list(q.ur)+list(q.ll)+list(q.lr)})
                        else:
                            for q in allq:quads.append({'page_number':page_number,'quad':list(q.ul)+list(q.ur)+list(q.ll)+list(q.lr)})
    return quads



def enrich_report_geometry(report,pdf,index):
    """Attach native positions to accepted quotes; never invent a missing highlight."""
    for evidence in report.get('evidence',[]):
        try:
            evidence['quads']=geometry_for_ranges(pdf,index,evidence['char_ranges'])
            evidence['geometry_status']='VERIFIED' if evidence['quads'] else 'UNAVAILABLE'
        except ApiError:
            evidence['quads']=[];evidence['geometry_status']='UNAVAILABLE'
    return report
