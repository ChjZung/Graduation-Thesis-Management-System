"""Laravel adapter for the independently tested PDF Lab engine (no UI dependency)."""
import argparse
import hashlib
import json
import os
import sys
import tempfile
import time
from datetime import datetime,timezone
from pathlib import Path

LIB=Path(__file__).resolve().parent/'pdf_lab'
sys.path.insert(0,str(LIB))
from core import segment_pdf,prepare_ai_inputs,Settings,PackagingSettings,SegmentationError
from ai_runtime import ApiError,GeminiClient,RunSettings
from ai_runtime.summarizer import fingerprint
from ai_runtime.model_transfer import run_summary_with_provenance
from ai_runtime.requirement_links import run_requirement_links, build_candidates, link_identity, VERSION as LINK_VERSION
from ai_runtime.instructor import run_reader,requirement_document,reader_fingerprint,VERSION as READER_VERSION

ENGINE_VERSION='pdf-lab-v1.3'


def atomic_json(path,value):
    path=Path(path);path.parent.mkdir(parents=True,exist_ok=True)
    temporary=None
    try:
        with tempfile.NamedTemporaryFile('w',encoding='utf-8',dir=path.parent,delete=False) as f:
            temporary=f.name;json.dump(value,f,ensure_ascii=False)
        for attempt in range(4):
            try:
                os.replace(temporary,path);break
            except PermissionError:
                if attempt==3:raise
                time.sleep(.05*(attempt+1))
    finally:
        if temporary and os.path.exists(temporary):os.unlink(temporary)


def load_state(path):
    try:
        value=json.loads(Path(path).read_text(encoding='utf-8'))
        return value if isinstance(value,dict) else {}
    except (OSError,ValueError):return {}


def context_document(context):
    context=context or {};parts=[]
    for name,label in [('stage_requirements','Yêu cầu giai đoạn'),('round_description','Mô tả đợt'),('submission_requirements','Yêu cầu nộp báo cáo')]:
        value=str(context.get(name) or '').strip()
        if value and value not in [p[1] for p in parts]:parts.append((label,value))
    if context.get('require_source_code'):parts.append(('Tệp nộp','Yêu cầu nộp mã nguồn.'))
    text='\n\n'.join(label+':\n'+value for label,value in parts)
    return requirement_document(text,str(context.get('round_title') or ''),str(context.get('stage_title') or ''))


def processing_error(error,stage='summary'):
    code=getattr(error,'code','service_unavailable');status=getattr(error,'status',None)
    if status in (401,403) or code in ('missing_key','authentication'):mapped='authentication'
    elif status==400 or code in ('invalid_request','invalid_model','reader_model_limit','model_limit','invalid_settings'):mapped='invalid_request'
    elif code in ('call_budget','reader_call_budget','worker_budget','requirement_link_call_budget','review_call_budget'):mapped='budget_exhausted'
    elif code in ('rate_limit','pacing_wait'):mapped='rate_limit'
    elif code in ('reader_input_limit','input_limit','tpm_limit','requirement_link_input_limit'):mapped='input_too_large'
    elif code in ('network_error','worker_network'):mapped='connection_error'
    elif isinstance(error,SegmentationError):mapped='extraction_failed'
    elif code in ('http_error',):mapped='model_unavailable' if status==404 else 'service_unavailable'
    elif isinstance(error,ApiError):mapped='invalid_request'
    else:mapped='service_unavailable'
    detail={'stage':stage,'code':mapped,'engine_code':code,'message':str(error) if isinstance(error,(ApiError,SegmentationError)) else 'Chưa hoàn tất xử lý AI. Các bước đã lưu được giữ.'}
    if mapped=='budget_exhausted':detail['message']='Đã lưu tiến độ; hệ thống sẽ tự tiếp tục các bước còn lại.'
    if mapped=='rate_limit':detail.update(quota='unknown',retry_after=60)
    return detail


class SharedPacer:
    """Reuse the project's SQLite project-wide limiter, not a per-process quota."""
    def __init__(self,model,deadline,progress):
        self.model=model;self.deadline=deadline;self.progress=progress;self.limiter=None
        if os.environ.get('AI_RATE_DB'):
            from ai_rate_limit import ProjectRateLimiter
            self.limiter=ProjectRateLimiter(os.environ['AI_RATE_DB'],os.environ.get('AI_RATE_SCOPE','default-gemini-project'),
                int(os.environ.get('AI_SAFE_RPM',4)),int(os.environ.get('AI_LITE_SAFE_RPM',12)),int(os.environ.get('AI_SAFE_TPM',200000)))
    def before_request(self,input_tokens=0):
        if time.monotonic()+65>self.deadline:raise ApiError('Đã hết thời gian lượt xử lý nền; sẽ tiếp tục từ checkpoint.','worker_budget')
        if not self.limiter:return
        while True:
            wait,detail=self.limiter.reserve(self.model,input_tokens)
            if detail and detail.get('code')=='input_too_large':raise ApiError('Request vượt TPM đã cấu hình.','tpm_limit')
            if wait<=0:self.progress(waiting_seconds=0);return
            self.progress(waiting_seconds=int(wait)+1,stage='waiting')
            if wait+65>=self.deadline-time.monotonic():raise ApiError('Chờ cửa sổ quota; giữ checkpoint để tiếp tục.','pacing_wait')
            time.sleep(min(wait,1))


class WorkerClient(GeminiClient):
    def _request(self,*args,**kwargs):
        try:return super()._request(*args,**kwargs)
        except ApiError as exc:
            if exc.status in (401,403):exc.code='authentication'
            elif exc.status==400:exc.code='invalid_request'
            elif exc.status==404:exc.code='invalid_model'
            elif exc.status==429 and getattr(self.pacer,'limiter',None):
                self.pacer.limiter.defer(self.model,{'code':'rate_limit','quota':'unknown','retry_after':60,'model':self.model})
            raise


def convert_result(result,plan,base,reader,requirements,identity,model,elapsed,errors,context=None,links=None):
    """Keep the Laravel v6 display contract, with Lab source references intact."""
    identity=dict(identity or {},source_hash=result['document']['sha256'])
    evidence={i['item_id']:i for m in (base or {}).get('map_results',{}).values() for i in m['items'] if i['quote_status']=='MATCH'}
    if base and reader:
        _,requirement_evidence=build_candidates(result,base,reader)
        evidence.update(requirement_evidence)
    def item(text,ids,kind='other'):
        refs=[{'source_id':iid,'page':evidence[iid]['start_page'],'end_page':evidence[iid]['end_page'],
               'quote':evidence[iid]['evidence_quote'],'start':evidence[iid]['evidence_start'],'end':evidence[iid]['evidence_end'],
               'quote_verified':True,'semantic_support':'NOT_VERIFIED'} for iid in dict.fromkeys(ids) if iid in evidence]
        if not refs:raise ApiError('Không có dẫn chứng hợp lệ cho đầu ra chuyển sang project.','adapter_reference')
        return {'content':text,'kind':kind,'source_ids':[r['source_id'] for r in refs],
                'source_pages':sorted({r['page'] for r in refs}), 'evidence':refs[0]['quote'],
                'source_references':refs,'verified':True,'relevance_verified':False,'semantic_support':'NOT_VERIFIED'}
    overview=[item(p['text'],p['evidence_ids']) for p in (reader or {}).get('report_overview',[])]
    summaries={c['root_id']:c for c in (reader or {}).get('chapter_summaries',[])}
    roots=list(dict.fromkeys(b['root_id'] for b in plan['batches']));chapters=[]
    for order,rid in enumerate(roots):
        batches=[b for b in plan['batches'] if b['root_id']==rid];c=summaries.get(rid,{})
        chapters.append({'id':rid,'number':'','title':batches[0]['chapter_title'],'level':1,'kind':'section','parent_id':None,'order_index':order,
            'start_page':min(b['start_page'] for b in batches),'end_page':max(b['end_page'] for b in batches),
            'summary':' '.join(p['text'] for p in c.get('paragraphs',[])),
            'items':[item(p['text'],p['evidence_ids']) for p in c.get('paragraphs',[])],
            'status':'completed' if rid in summaries and (reader or {}).get('status')=='complete' else 'partial','sections':[]})
    completed=[item(p['text'],p['item_ids'],'reported_work') for p in ((base or {}).get('final_result') or {}).get('completed_work',[])]
    teacher=[]
    matches={i['requirement_id']:i for i in (links or {}).get('links',[])}
    for index,req in enumerate((reader or {}).get('teacher_requirements',[])):
        match=matches.get(f'R{index+1:04}',{})
        report_items=[item(evidence[iid]['summary'],[iid],evidence[iid]['kind']) for iid in match.get('evidence_ids',[]) if iid in evidence]
        points=[]
        for point in match.get('points',[]):
            ids=point.get('evidence_ids',[])
            grounded=item(point['explanation'],ids) if ids else {'source_references':[]}
            points.append(dict(point,source_references=grounded['source_references']))
        teacher.append(dict(req,report_points=points,requirement_id=f'R{index+1:04}',report_relation=match.get('relation','pending'),
            report_explanation=match.get('explanation',''),report_matches=report_items))
    direct=(links or {}).get('_verified_report')
    if direct is not None:teacher=(links or {}).get('teacher_requirements',[])
    full=(links or {}).get('status')=='complete' and (base or {}).get('status')=='complete' and (reader or {}).get('status')=='complete' and (reader or {}).get('coverage',{}).get('all_planned_text_complete',False)
    visual=[p['page'] for p in result['document']['pages'] if p.get('image_count') or p.get('visual_regions')]
    source_coverage={'selected_text_coverage':plan['metrics'].get('selected_text_coverage'), 'excluded_sections':plan.get('excluded_sections',[])}
    counts={'total_chunks':len(plan['batches']),'analyzed_chunks':len((base or {}).get('map_results',{}))}
    analysis={'version':6,'engine':'pdf_lab','engine_version':ENGINE_VERSION,'reader_version':READER_VERSION,
        'source':'gemini_map_reduce','method':'structure_aware_map_reduce','model':model,'status':'completed' if full else 'partial',
        'total_pages':result['document']['page_count'],'total_units':result['document']['page_count'],**counts,
        'text_complete':full,'reduce_completed':(base or {}).get('status')=='complete','completion_audit_completed':False,
        'quality_checks_completed':full,'sections_detected':len(result['sections']),'semantic_audit_completed':False,'semantic_support':'NOT_VERIFIED',
        'visual_unread_pages':visual,'source_coverage':source_coverage,'processing_time_seconds':round(elapsed,3),
        'token_settings':{'engine':ENGINE_VERSION,'plan':plan['fingerprint'],'settings':(base or {}).get('settings',{})},
        'errors':errors,'completed_calls':(base or {}).get('generation_attempts',0)+(reader or {}).get('generation_attempts',0)+(links or {}).get('generation_attempts',0),'requirements_link_version':LINK_VERSION,
        'note':'Đã xử lý đủ các gói text của phương án. Câu trích đã khớp nguồn; chưa kiểm chứng ngữ nghĩa toàn diện, chưa đọc hình/sơ đồ hoặc các mục loại khỏi Map.' if full else 'Bản đọc đang xử lý; chưa dùng phần chưa đọc để kết luận báo cáo thiếu nội dung.',
        'generated_at':datetime.now(timezone.utc).isoformat(),'source_labels':{str(p['page']):f"Trang {p['page']}" for p in result['document']['pages']}}
    data={'schema_version':6,'document_type':'pdf','document_id':identity.get('document_id'),'document_version_id':identity.get('document_version_id'),
        'source_identity':identity,'_source_hash':identity['source_hash'],'analysis_status':analysis['status'],'analysis':analysis,
        'overview_summary':'\n\n'.join(p['content'] for p in overview),'overall_summary':'\n\n'.join(p['content'] for p in overview),
        'document_content':overview,'chapters':chapters,'completed_work':completed,'processing_errors':errors,
        'citations':[{'page':i['start_page'],'snippet':i['evidence_quote'],'verified':True,'semantic_support':'NOT_VERIFIED'} for i in evidence.values()],
        'document_outline':[{'id':s['section_id'],'title':s['section_title'],'start_page':s['start_page']} for s in result['sections']],
        'teacher_requirements':teacher,'requirements_links_status':(links or {}).get('status','pending'),'requirements_document':requirements,
        'teacher_requirements_status':(reader or {}).get('requirements_status','pending'),
        'requirements_review':{'status':'not_configured','mode':'source_links_only','items':[],'note':'Liên kết đoạn liên quan để giảng viên đối chiếu, không tự chấm đạt/không đạt.'},
        'summary_scope':{'context_hash':hashlib.sha256(json.dumps(context or {},ensure_ascii=False,sort_keys=True,separators=(',',':')).encode()).hexdigest()},
        'validation':{'semantic_audit':False,'quote_matching':True,'quality_rules':'targeted_heuristics'},
        'analysis_metadata':{'pages_total':result['document']['page_count'],'chunks_total':counts['total_chunks'],'chunks_succeeded':counts['analyzed_chunks'],'chunks_failed':counts['total_chunks']-counts['analyzed_chunks']}}
    if direct is not None:
        data['requirements_review']={'status':'completed' if (links or {}).get('status')=='complete' else 'partial','mode':'verified_v2',
            'items':direct.get('results',[]),'processing':direct.get('processing',{}),'policy_version':direct.get('verification_policy_version'),
            'note':'Dẫn chứng hỗ trợ đối chiếu; chưa tìm được nguồn không có nghĩa sinh viên chưa thực hiện.'}
        data['teacher_requirements_status']='provided'
        data['analysis']['requirements_link_version']='verified_v2'
    elif (links or {}).get('not_configured'):
        data['requirements_review']={'status':'not_configured','mode':'verified_v2','items':[]}
        data['teacher_requirements']=[]
    return {'success':True,'source':'pdf_lab_map_reduce','data':data,'total_pages':analysis['total_pages'],'total_units':analysis['total_pages'],
            'processing_time':round(elapsed,3),'metrics':{'total_pages':analysis['total_pages'],'chunk_count':counts['total_chunks']}}


def run_pipeline(path,key,model,cache_dir,context=None,identity=None,options=None,provider_factory=None,import_checkpoint=None):
    started=time.monotonic();options=options or {};context=context or {};identity=identity or {}
    direct_review=options.get('review_engine')=='verified_v2'
    settings=RunSettings(int(options.get('input_budget',8500)),int(options.get('max_output_tokens',16384)),int(options.get('max_generation_calls',5)))
    # Requirement answers have a separate output budget; chapter/Map caches keep theirs.
    link_settings=RunSettings(settings.input_budget,min(settings.max_output_tokens,8192),settings.max_generation_calls)
    source=segment_pdf(path,Settings());plan=prepare_ai_inputs(source,PackagingSettings())
    requirements=context_document(context)
    reader_requirements=context_document({}) if direct_review else requirements
    binding=fingerprint([ENGINE_VERSION,READER_VERSION,identity,source['document']['sha256'],model,settings.input_budget,settings.max_output_tokens,plan['fingerprint']])
    folder=Path(cache_dir)/'pdf_lab'/binding;folder.mkdir(parents=True,exist_ok=True)
    summary_path=folder/'summary.json';reader_path=folder/'reader.json'
    summary_state=load_state(summary_path);reader_state=load_state(reader_path)
    if direct_review:
        from ai_runtime.requirement_review.prompts import PROMPT_VERSION
        from ai_runtime.requirement_review.validation import GUARD_VERSION
        from ai_runtime.requirement_review.retrieval import VERSION as RETRIEVAL_VERSION
        from ai_runtime.requirement_review.coverage import VERSION as COVERAGE_VERSION
        review_key=fingerprint([requirements['fingerprint'],PROMPT_VERSION,GUARD_VERSION,RETRIEVAL_VERSION,COVERAGE_VERSION])
        links_path=folder/('review_'+review_key[:16]+'.json')
    else:links_path=folder/'requirement_links.json'
    links_state=load_state(links_path)
    run_id=os.environ.get('AI_PROGRESS_FILE','manual')
    # A human-requested new generation may retry invalid output; an automatic pass may not.
    for state in (summary_state,reader_state,links_state):
        if state.get('_worker_run')!=run_id:
            state['tasks']={k:v for k,v in state.get('tasks',{}).items() if not v.get('error')}
            state['_worker_run']=run_id
    # Core state dictionaries must be empty until the core assigns identity/config.
    if not summary_state.get('identity'):summary_state={}
    if not reader_state.get('config'):reader_state={}
    if import_checkpoint:
        from ai_runtime.checkpoints import restore_completed_checkpoint
        summary_state=restore_completed_checkpoint(json.loads(Path(import_checkpoint).read_text(encoding='utf-8-sig')),source,plan,model,settings)
    base=summary_state.get('report');reader=reader_state.get('report')
    if reader and (not base or (not direct_review and reader.get('identity')!=reader_fingerprint(base,reader_requirements,model,settings))):
        reader=None
    links=links_state.get('report')
    if not direct_review and (not base or not reader or links_state.get('identity')!=link_identity(base,reader,model,link_settings) or (links or {}).get('support_review')!='complete'):links=None
    progress={'engine':'pdf_lab','stage':'extract','map_done':len((base or {}).get('map_results',{})),'map_total':len(plan['batches']),'started_at':time.time()}
    def checkpoint():
        if summary_state:summary_state['_worker_run']=run_id;atomic_json(summary_path,summary_state)
        if reader_state:reader_state['_worker_run']=run_id;atomic_json(reader_path,reader_state)
        if links_state:links_state['_worker_run']=run_id;atomic_json(links_path,links_state)
    def report_progress(**values):
        progress.update(values,updated_at=time.time())
        if os.environ.get('AI_PROGRESS_FILE'):
            try:atomic_json(os.environ['AI_PROGRESS_FILE'],progress)
            except OSError:pass  # A browser reading the progress file must not stop the analysis.
    def phase(label):
        checkpoint();report_progress(stage=label)
    def update(values):
        checkpoint();report_progress(**{k:v for k,v in values.items() if k in ('stage','map_done','map_total','chapter_done','chapter_total')})
    error_list=[]
    try:
        deadline=started+int(options.get('pass_seconds',260))
        if base and base.get('status')=='complete' and reader and reader.get('status')=='complete' and links and links.get('status')=='complete':
            report_progress(stage='complete',map_done=len(plan['batches']),map_total=len(plan['batches']),reused_calls=1)
            return convert_result(source,plan,base,reader,requirements,identity,model,time.monotonic()-started,[],context,links)
        provider=provider_factory() if provider_factory else WorkerClient(key,model,pacer=SharedPacer(model,deadline,report_progress))
        if not base or base.get('status')!='complete':
            before=summary_state.get('generation_attempts',0)
            base=run_summary_with_provenance(source,plan,provider,summary_state,settings,on_progress=phase,on_update=update)
            used=summary_state.get('generation_attempts',0)-before
        else:used=0
        checkpoint()
        if base.get('status')=='complete' and (not reader or reader.get('status')!='complete'):
            if settings.max_generation_calls<=used:raise ApiError('Tiếp tục bước tạo bản đọc ở lượt nền tiếp theo.','worker_budget')
            before=reader_state.get('generation_attempts',0)
            reader=run_reader(source,plan,base,reader_requirements,provider,reader_state,
                RunSettings(settings.input_budget,settings.max_output_tokens,settings.max_generation_calls-used),on_progress=phase)
            used+=reader_state.get('generation_attempts',0)-before
        if reader and reader.get('status')=='complete':
            if settings.max_generation_calls<=used and reader.get('teacher_requirements'):raise ApiError('Tiếp tục nối yêu cầu ở lượt nền sau.','worker_budget')
            if direct_review:
                if used>=settings.max_generation_calls:raise ApiError('Tiếp tục đối chiếu ở lượt nền sau.','worker_budget')
                from verified_requirement_review import run_direct_review
                links,review_errors=run_direct_review(path,context,requirements,identity,model,
                    RunSettings(link_settings.input_budget,link_settings.max_output_tokens,settings.max_generation_calls-used),
                    cache_dir,provider,atomic_json,load_state,phase,run_id)
                links_state['report']=links
                for error in review_errors:
                    error_list.append(processing_error(ApiError(error.get('message','Chưa hoàn tất đối chiếu.'),error.get('code','review_incomplete')),'requirements_review'))
            else:
                links=run_requirement_links(source,base,reader,provider,links_state,
                    RunSettings(link_settings.input_budget,link_settings.max_output_tokens,max(1,settings.max_generation_calls-used)),on_progress=phase)
        for obj in (base,reader,links):
            if obj and obj.get('error'):
                error=obj['error'];error_list.append(processing_error(ApiError(error['message'],error['code'])))
        if not reader and not error_list:error_list.append(processing_error(ApiError('Chưa hoàn tất bản đọc.','worker_budget')))
    except (ApiError,SegmentationError) as exc:error_list.append(processing_error(exc))
    finally:checkpoint()
    if hashlib.sha256(Path(path).read_bytes()).hexdigest()!=source['document']['sha256']:
        raise ApiError('Tệp nguồn đã thay đổi trong lúc xử lý.','source_changed')
    report_progress(stage='complete' if links and links.get('status')=='complete' else 'partial',map_done=len((base or {}).get('map_results',{})),map_total=len(plan['batches']),
        completed_calls=summary_state.get('generation_attempts',0)+reader_state.get('generation_attempts',0)+links_state.get('generation_attempts',0),waiting_seconds=0)
    return convert_result(source,plan,base,reader,requirements,identity,model,time.monotonic()-started,error_list,context,links)


def main():
    parser=argparse.ArgumentParser()
    parser.add_argument('--pdf',required=True);parser.add_argument('--model',default='gemini-3.5-flash-lite')
    parser.add_argument('--cache-dir',required=True);parser.add_argument('--review-context');parser.add_argument('--document-identity')
    parser.add_argument('--lab-settings');parser.add_argument('--import-lab-checkpoint');parser.add_argument('--output')
    parser.add_argument('--review-only',action='store_true')
    for name in ('stage','chunk-size','chunk-overlap','method','pipeline-version','target-tokens','overlap-tokens','prompt-reserve-tokens','max-input-tokens'):parser.add_argument('--'+name)
    args=parser.parse_args()
    try:
        result=run_pipeline(args.pdf,os.environ.get('GEMINI_API_KEY',''),args.model,args.cache_dir,
            json.loads(args.review_context) if args.review_context else {},json.loads(args.document_identity) if args.document_identity else {},
            json.loads(args.lab_settings) if args.lab_settings else {},import_checkpoint=args.import_lab_checkpoint)
    except Exception as exc:
        import traceback
        try:
            atomic_json(Path(args.cache_dir)/'pdf_lab_last_error.json',{'type':type(exc).__name__,
                'frames':[{'file':Path(f.filename).name,'line':f.lineno,'function':f.name} for f in traceback.extract_tb(exc.__traceback__)]})
        except OSError:pass
        detail=processing_error(exc)
        result={'success':False,'error':detail['message'],'processing_error':detail,'metrics':{'processing_time':0}}
    value=json.dumps(result,ensure_ascii=False)
    if args.output:atomic_json(args.output,result)
    else:print(value)

if __name__=='__main__':
    if hasattr(sys.stdout,'reconfigure'):sys.stdout.reconfigure(encoding='utf-8')
    main()
