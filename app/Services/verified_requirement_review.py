"""Laravel queue adapter for direct-PDF requirement review; no browser/UI or keys on disk."""
import copy
import hashlib
import json
from pathlib import Path
from ai_runtime.requirement_review.engine import analysis_identity,make_report,run_review
from ai_runtime.requirement_review.parsing import parse_requirements,sha
from ai_runtime.requirement_review.source_index import build_source_index,VERSION as INDEX_VERSION
from ai_runtime.summarizer import RunSettings,fingerprint

VERSION='verified-requirement-adapter-1.0'
MISSING='Hệ thống chưa tìm được dẫn chứng phù hợp; giảng viên vui lòng đối chiếu báo cáo gốc.'


def requirement_set(context,document):
    raw=document.get('text','');items=[];conditions=[];seen=set()
    for field in ('stage_requirements','round_description','submission_requirements'):
        value=str(context.get(field) or '').strip()
        if not value or value in seen:continue
        seen.add(value);part=parse_requirements(value);offset=max(0,raw.find(value))
        for item in part['items']:
            item=dict(item,item_id=f'req_{len(items)+1:03}',char_start=item['char_start']+offset,char_end=item['char_end']+offset)
            items.append(item)
        conditions.extend(dict(c,char_start=c['char_start']+offset,char_end=c['char_end']+offset) for c in part['general_conditions'])
    if not items and raw.strip():return parse_requirements(raw)
    return {'id':'teacher-context','version':sha(raw)[:16],'sha256':sha(raw),'raw_text':raw,'intro':'',
        'items':items,'general_conditions':conditions,'warnings':[]}


def render_teacher_items(report):
    accepted={e['evidence_id']:e for e in report['evidence']};items=[]
    for item in report['results']:
        points=[]
        for check in item['checks']:
            refs=[]
            if check['status']=='SUPPORTED':
                for eid in check['evidence_ids']:
                    e=accepted.get(eid)
                    if not e:continue
                    for page in sorted({r['page_number'] for r in e['char_ranges']}):
                        refs.append({'source_id':e['source_id'],'page':page,'end_page':page,'quote':e['quote'],
                            'start':e['char_start'],'end':e['char_end'],'char_ranges':[r for r in e['char_ranges'] if r['page_number']==page],
                            'quote_verified':True,'semantic_support':'SUPPORTS','document_sha256':e['document_sha256']})
            relation='related_text' if refs else ('pending' if check['status'] in ('NOT_PROCESSED','PENDING_VERIFICATION','PROCESSING_ERROR') else 'not_found')
            points.append({'title':check['description'],'relation':relation,'explanation':check.get('finding') if refs else MISSING,
                'source_references':refs,'check_status':check['status']})
        items.append({'requirement_id':item['item_id'],'text':item['original_label'],'evidence_quote':item['original_label'],
            'report_relation':'related_text' if any(p['source_references'] for p in points) else ('pending' if any(p['relation']=='pending' for p in points) else 'not_found'),
            'report_explanation':item['short_answer'],'report_points':points,'report_matches':[]})
    return items


def run_direct_review(path,context,document,identity,model,settings,cache_dir,provider,atomic_json,load_state,on_progress,run_id):
    requirements=requirement_set(context,document)
    if not requirements['items']:
        return {'status':'complete','teacher_requirements':[],'not_configured':True,'generation_attempts':0},[]
    digest=hashlib.sha256(Path(path).read_bytes()).hexdigest()
    folder=Path(cache_dir)/'verified_pdf_review';folder.mkdir(parents=True,exist_ok=True)
    index_key=fingerprint([INDEX_VERSION,digest,identity.get('document_id'),identity.get('document_version_id')])
    index_path=folder/('index_'+index_key+'.json');index=load_state(index_path)
    if not index or index.get('index_version')!=INDEX_VERSION or index.get('document',{}).get('sha256')!=digest:
        index=build_source_index(path,document_id=identity.get('document_id'),version_id=identity.get('document_version_id'),on_progress=on_progress)
        atomic_json(index_path,index)
    key=analysis_identity(index,requirements,model,settings);state_path=folder/(key+'.json');state=load_state(state_path)
    retry_failed=bool(state and state.get('_worker_run')!=run_id)
    state['_worker_run']=run_id if state.get('identity') else None
    # Automatic queue passes retry only explicit transient failures, at most
    # three attempts per task; valid results and semantic rejections stay intact.
    if not retry_failed:
        for tid,error in list(state.get('errors',{}).items()):
            if error.get('code') not in ('http_error','rate_limit'):continue
            counts=state.setdefault('_transient_retries',{});attempts=counts.get(tid,0)
            if attempts<2:
                counts[tid]=attempts+1;state['errors'].pop(tid,None);state.get('tasks',{}).pop(tid,None);state.pop('last_error',None)
    def checkpoint(label):
        state['_worker_run']=run_id;atomic_json(state_path,state);on_progress(label)
    run_review(index,requirements,provider,state,settings,on_progress=checkpoint,retry_failed=retry_failed,busy_retry_delays=())
    atomic_json(state_path,state);report=make_report(state,index,requirements)
    if hashlib.sha256(Path(path).read_bytes()).hexdigest()!=digest:raise ValueError('Source changed during review')
    errors=list(report['processing']['errors'])
    if report['processing']['status']!='COMPLETED' and not errors:
        errors=[state.get('last_error') or {'code':'review_incomplete','message':'Chưa hoàn tất đối chiếu.'}]
    return {'status':'complete' if report['processing']['status']=='COMPLETED' else 'partial','_verified_report':report,
        'teacher_requirements':render_teacher_items(report),'generation_attempts':state.get('generation_calls',0)},errors
