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
from ai_gemini_transport import generate_groq
from ai_report_progress import SCHEMA as PROGRESS_SCHEMA, extract as extract_progress
from ai_processing import ProcessingError, analysis_cancelled, configure_session

try:
    import requests
except ImportError:
    requests = None
from ai_runtime.content_review import safe_review_content
from ai_runtime.summarizer import fingerprint
from ai_runtime.model_transfer import run_summary_with_provenance
from ai_runtime.requirement_links import run_requirement_links, build_candidates, link_identity, VERSION as LINK_VERSION
from ai_runtime.instructor import run_reader,requirement_document,reader_fingerprint,VERSION as READER_VERSION,OVERVIEW_VERSION
from core.caption_index import extract_captions, section_nodes, section_text_sources, VERSION as CAPTION_VERSION
from ai_source_navigation import from_pdf_lab, publish
from ai_runtime.requirement_relevance import VERSION as MATCHER_VERSION

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
    elif code in ('reader_input_limit','input_limit','groq_input_limit','tpm_limit','requirement_link_input_limit'):mapped='input_too_large'
    elif code in ('network_error','worker_network'):mapped='connection_error'
    elif code=='provider_timeout':mapped='timeout'
    elif isinstance(error,SegmentationError):mapped='extraction_failed'
    elif code in ('http_error',):mapped='model_unavailable' if status==404 else 'service_unavailable'
    elif code=='review_retry_exhausted':mapped='output_validation'
    elif code.startswith('reader_') or code in ('cached_reader_error','invalid_reader_output','invalid_reader_reference'):mapped='output_validation'
    elif isinstance(error,ApiError):mapped='invalid_request'
    else:mapped='service_unavailable'
    detail={'stage':stage,'code':mapped,'engine_code':code,'message':str(error) if isinstance(error,(ApiError,SegmentationError)) else 'Chưa hoàn tất xử lý AI. Các bước đã lưu được giữ.'}
    if mapped=='budget_exhausted':detail['message']='Đã lưu tiến độ; hệ thống sẽ tự tiếp tục các bước còn lại.'
    if status is not None:detail['http_status']=status
    detail.update(getattr(error, 'metadata', {}))
    retry_after = getattr(error, 'retry_after', None)
    if mapped == 'rate_limit':
        detail.setdefault('quota', 'unknown')
        detail['retry_after'] = 60 if retry_after is None else retry_after
    elif retry_after is not None:
        detail['retry_after'] = retry_after
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
        if analysis_cancelled():
            raise ApiError('Lượt phân tích đã được dừng.', 'analysis_cancelled')
        if time.monotonic()+65>self.deadline:raise ApiError('Đã hết thời gian lượt xử lý nền; sẽ tiếp tục từ checkpoint.','worker_budget')
        if not self.limiter:return
        while True:
            if analysis_cancelled(): raise ApiError('Lượt phân tích đã được dừng.', 'analysis_cancelled')
            wait,detail=self.limiter.reserve(self.model,input_tokens)
            if detail and detail.get('code')=='input_too_large':raise ApiError('Request vượt TPM đã cấu hình.','tpm_limit')
            if wait<=0:self.progress(waiting_seconds=0);return
            self.progress(waiting_seconds=int(wait)+1,stage='waiting')
            if wait+65>=self.deadline-time.monotonic():
                raise ApiError('Chờ cửa sổ quota; giữ checkpoint để tiếp tục.', 'pacing_wait',
                               status=detail.get('http_status'), retry_after=detail.get('retry_after'),
                               metadata=detail)
            time.sleep(min(wait,1))


class WorkerClient(GeminiClient):
    def __init__(self, key, model, pacer):
        super().__init__(key, model, timeout=60, pacer=pacer)
        self.groq_key = os.environ.get('GROQ_API_KEY', '').strip()
        self.groq_model = os.environ.get('GROQ_MODEL', '').strip()
        self.gemini_retry_at = 0
        self.gemini_fallback_error = None
        if self.groq_key and self.groq_model:
            configure_session(budget=max(1, pacer.deadline - time.monotonic()))

    def allow_fallback_after(self, error):
        if error.status != 503 or not (self.groq_key and self.groq_model):
            return False
        self.gemini_fallback_error = error
        self.gemini_retry_at = time.monotonic() + max(30, error.retry_after or 0)
        return True

    def model_info(self):
        if time.monotonic() < self.gemini_retry_at:
            raise self.gemini_fallback_error
        return super().model_info()

    def count_tokens(self, prompt, schema, max_output_tokens):
        if time.monotonic() >= self.gemini_retry_at:
            try:
                return super().count_tokens(prompt, schema, max_output_tokens)
            except ApiError as error:
                if not self.allow_fallback_after(error):
                    raise
        # Groq has no Gemini countTokens endpoint. A local estimate is used only
        # for planning. The Groq transport separately checks its token budget.
        return max(1, (len(prompt.encode('utf-8')) + 2) // 3)

    def generate(self, prompt, schema, max_output_tokens, input_tokens):
        if time.monotonic() >= self.gemini_retry_at:
            try:
                return super().generate(prompt, schema, max_output_tokens, input_tokens)
            except ApiError as error:
                if not self.allow_fallback_after(error):
                    raise
        instruction = '\nTrả về JSON theo cấu trúc sau:\n' + json.dumps(schema, ensure_ascii=False, separators=(',', ':'))
        try:
            data, used = generate_groq(prompt + instruction, self.groq_key, self.groq_model,
                                       self.pacer.deadline, requests, lambda text: text)
        except ProcessingError as error:
            if error.code in ('input_too_large', 'invalid_response'):
                raise ApiError('Gói PDF vượt ngân sách Groq hoặc đầu ra bị cắt.', 'groq_input_limit') from None
            if error.code == 'budget_exhausted':
                raise ApiError('Đã hết thời gian lượt xử lý nền.', 'worker_budget') from None
            message = {'rate_limit': 'Groq đang giới hạn lượt gọi. Đã giữ checkpoint.',
                       'authentication': f"Groq từ chối API key hoặc quyền truy cập (HTTP {error.details.get('http_status', '?')}).",
                       'invalid_request': 'Groq từ chối cấu hình yêu cầu.',
                       'model_unavailable': 'Groq không tìm thấy model đã cấu hình.',
                       'invalid_json': 'Groq trả JSON không hợp lệ.',
                       'invalid_response': 'Groq chưa trả kết quả đầy đủ.'}.get(error.code, 'Groq chưa hoàn tất yêu cầu.')
            code = {'connection_error':'network_error','timeout':'provider_timeout',
                    'service_unavailable':'http_error','model_unavailable':'http_error'}.get(error.code,error.code)
            metadata = dict(error.details)
            if self.gemini_fallback_error is not None:
                metadata.update(primary_http_status=self.gemini_fallback_error.status, primary_model=self.model)
            raise ApiError(message, code, error.details.get('http_status'),
                           error.details.get('retry_after'), metadata=metadata) from None
        if not isinstance(data, dict):
            raise ApiError('Groq không trả JSON object.', 'invalid_response')
        return {'data': data, 'usage': {'provider_model': used}, 'finish_reason': 'STOP'}

    def _request(self,*args,**kwargs):
        try:return super()._request(*args,**kwargs)
        except ApiError as exc:
            exc.metadata.setdefault('model', self.model)
            if exc.status in (401,403):exc.code='authentication'
            elif exc.status==400:exc.code='invalid_request'
            elif exc.status==404:exc.code='invalid_model'
            elif getattr(self.pacer, 'limiter', None) and (
                    exc.status == 429 or (exc.status == 503 and exc.retry_after is not None)):
                until = self.pacer.limiter.defer(self.model, {
                    'code': 'rate_limit' if exc.status == 429 else 'service_unavailable',
                    'quota': 'unknown', 'retry_after': 60 if exc.retry_after is None else exc.retry_after,
                    'http_status': exc.status, 'model': self.model,
                })
                exc.metadata.update(model=self.model, cooldown_until=until)
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
    try:
        caption_index = extract_captions(result)
    except Exception:
        caption_index = {'version':CAPTION_VERSION,'status':'failed','assets':[]}
    overview=[item(p['text'],p['evidence_ids']) for p in (reader or {}).get('report_overview',[])]
    summaries={c['root_id']:c for c in (reader or {}).get('chapter_summaries',[])}
    roots=list(dict.fromkeys(b['root_id'] for b in plan['batches']));chapters=[]
    for order,rid in enumerate(roots):
        batches=[b for b in plan['batches'] if b['root_id']==rid];c=summaries.get(rid,{})
        chapters.append({'id':rid,'number':'','title':batches[0]['chapter_title'],'level':1,'kind':'section','parent_id':None,'order_index':order,
            'start_page':min(b['start_page'] for b in batches),'end_page':max(b['end_page'] for b in batches),
            'summary':' '.join(p['text'] for p in c.get('paragraphs',[])),
            'items':[item(p['text'],p['evidence_ids']) for p in c.get('paragraphs',[])],
            'status':'completed' if c.get('paragraphs') and c.get('coverage')=='selected_text_complete' else 'partial','sections':[]})
    for chapter in chapters:
        prose = [entry['content'] for entry in chapter['items']]
        chapter['chapter_reading'] = {
            'paragraphs': prose,
            'error': summaries.get(chapter['id'], {}).get('reading_error'),
            'status': ('complete' if prose and chapter['status'] == 'completed'
                       and not summaries.get(chapter['id'], {}).get('visual_content_unread') else 'partial'),
        }
        if not prose:
            chapter['items'] = [item(source['summary'], [iid], source['kind'])
                                for iid, source in evidence.items()
                                if source.get('section_id') == chapter['id'] and source.get('summary')]
        chapter['sections'] = section_nodes(result, evidence, item, chapter['id'], caption_index)
        chapter['related_assets'] = [a for a in caption_index['assets'] if a['section_id'] == chapter['id']]
    section_prose = (reader or {}).get('section_summaries', {})
    raw_sections = {}
    mapped_sections = {entry.get('section_id') for entry in evidence.values()}
    for section in result['sections']:
        sid = section['section_id']
        if section.get('parent_section_id') and section.get('include_in_map') and sid not in mapped_sections:
            raw_sections[sid] = section_text_sources(result, section)
    for sources in raw_sections.values():
        evidence.update(sources)
    def attach_section_reading(nodes):
        for node in nodes:
            paragraphs = section_prose.get(node['id'])
            if paragraphs:
                node['chapter_reading'] = {'paragraphs': [p['text'] for p in paragraphs], 'status': 'complete'}
                node['items'] = [item(p['text'], p['evidence_ids']) for p in paragraphs]
            elif node['status'] == 'insufficient_text':
                sources = raw_sections.get(node['id'], {})
                if sources:
                    node['raw_source_references'] = item('', list(sources))['source_references']
                elif node.get('related_assets'):
                    node['visual_only'] = True
            attach_section_reading(node.get('sections', []))
    for chapter in chapters:
        attach_section_reading(chapter['sections'])
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
    if direct is not None:
        from ai_runtime.requirement_output_guard import guard_direct_report
        from verified_requirement_review import render_teacher_items
        direct=guard_direct_report(direct)
        teacher=render_teacher_items(direct)
    cross_matched = False
    try:
        from ai_runtime.requirement_cross_matcher import cross_match_teacher_requirements
        matched_teacher = cross_match_teacher_requirements(requirements, chapters, completed, caption_index, context)
        if matched_teacher and direct is None and (links or {}).get('status') == 'complete':
            for req in matched_teacher:
                req['document_fingerprint'] = requirements.get('fingerprint')
            teacher = matched_teacher
            cross_matched = True
    except Exception:
        pass
    full=(base or {}).get('status')=='complete' and (reader or {}).get('status')=='complete' and (reader or {}).get('coverage',{}).get('all_planned_text_complete',False)
    reading_errors = (reader or {}).get('reading_errors', [])
    visual=[p['page'] for p in result['document']['pages'] if p.get('image_count') or p.get('visual_regions')]
    source_coverage={'selected_text_coverage':plan['metrics'].get('selected_text_coverage'), 'excluded_sections':plan.get('excluded_sections',[])}
    counts={'total_chunks':len(plan['batches']),'analyzed_chunks':len((base or {}).get('map_results',{}))}
    analysis={'version':6,'engine':'pdf_lab','engine_version':ENGINE_VERSION,'reader_version':READER_VERSION,
        'overview_version':(reader or {}).get('overview_version'),
        'source':'gemini_map_reduce','method':'structure_aware_map_reduce','model':model,'status':'completed' if full and not reading_errors else 'partial',
        'total_pages':result['document']['page_count'],'total_units':result['document']['page_count'],**counts,
        'text_complete':full,'reduce_completed':(base or {}).get('status')=='complete','completion_audit_completed':False,
        'quality_checks_completed':full and not reading_errors,'sections_detected':len(result['sections']),'semantic_audit_completed':False,'semantic_support':'NOT_VERIFIED',
        'visual_unread_pages':visual,'source_coverage':source_coverage,'processing_time_seconds':round(elapsed,3),
        'token_settings':{'engine':ENGINE_VERSION,'plan':plan['fingerprint'],'settings':(base or {}).get('settings',{})},
        'errors':errors,'completed_calls':(base or {}).get('generation_attempts',0)+(reader or {}).get('generation_attempts',0)+(links or {}).get('generation_attempts',0),'requirements_link_version':LINK_VERSION,
        'note': ('Đã đọc xong văn bản; một số bản tóm tắt chưa qua kiểm tra. Đối chiếu yêu cầu vẫn dùng nguồn đã lưu.' if reading_errors else '') or 'Đã xử lý đủ các gói text của phương án. Câu trích đã khớp nguồn; chưa kiểm chứng ngữ nghĩa toàn diện, chưa đọc hình/sơ đồ hoặc các mục loại khỏi Map.' if full else 'Bản đọc đang xử lý; chưa dùng phần chưa đọc để kết luận báo cáo thiếu nội dung.',
        'generated_at':datetime.now(timezone.utc).isoformat(),'source_labels':{str(p['page']):f"Trang {p['page']}" for p in result['document']['pages']}}
    content_review=safe_review_content(result,base)
    data={'caption_index':caption_index,'content_review':content_review,'schema_version':6,'document_type':'pdf','document_id':identity.get('document_id'),'document_version_id':identity.get('document_version_id'),
        'source_identity':identity,'_source_hash':identity['source_hash'],'analysis_status':analysis['status'],'analysis':analysis,
        'overview_summary':'\n\n'.join(p['content'] for p in overview),'overall_summary':'\n\n'.join(p['content'] for p in overview),
        'document_content':overview,'chapters':chapters,'completed_work':completed,'processing_errors':errors,
        'citations':[{'page':i['start_page'],'snippet':i['evidence_quote'],'verified':True,'semantic_support':'NOT_VERIFIED'} for i in evidence.values()],
        'document_outline':[{'id':s['section_id'],'title':s['section_title'],'start_page':s['start_page']} for s in result['sections']],
        'teacher_requirements':teacher,'requirements_links_status':(links or {}).get('status','pending'),'requirements_document':requirements,
        'teacher_requirements_status':(reader or {}).get('requirements_status','pending'),
        'requirements_review':{'status':'pending' if requirements.get('sources') else 'not_configured','mode':'source_links_only','items':[],'note':'Liên kết đoạn liên quan để giảng viên đối chiếu, không tự chấm đạt/không đạt.'},
        'summary_scope':{'context_hash':hashlib.sha256(json.dumps(context or {},ensure_ascii=False,sort_keys=True,separators=(',',':')).encode()).hexdigest()},
        'validation':{'semantic_audit':False,'quote_matching':True,'quality_rules':'targeted_heuristics'},
        'analysis_metadata':{'pages_total':result['document']['page_count'],'chunks_total':counts['total_chunks'],'chunks_succeeded':counts['analyzed_chunks'],'chunks_failed':counts['total_chunks']-counts['analyzed_chunks']}}
    if cross_matched:
        data['teacher_requirements'] = teacher
        data['teacher_requirements_status'] = 'provided'
        data['requirements_links_status'] = 'completed'
        data['requirements_review'] = {'status':'completed','mode':'verified_v2','items':teacher,
            'note':'Dẫn chứng đối chiếu trực tiếp từ các chương và công việc đã tóm tắt của báo cáo.'}
        data['analysis']['requirements_link_version'] = 'verified_v2'
    elif direct is not None:
        data['requirements_review']={'status':'completed' if (links or {}).get('status')=='complete' else 'partial','mode':'verified_v2',
            'items':direct.get('results',[]),'processing':direct.get('processing',{}),'policy_version':direct.get('verification_policy_version'),
            'note':'Dẫn chứng hỗ trợ đối chiếu; chưa tìm được nguồn không có nghĩa sinh viên chưa thực hiện.'}
        data['teacher_requirements_status']='provided'
        data['analysis']['requirements_link_version']='verified_v2'
    elif (links or {}).get('not_configured'):
        data['requirements_review']={'status':'not_configured','mode':'verified_v2','items':[]}
        data['teacher_requirements']=[]
    from ai_runtime.requirement_output_guard import guard_teacher_items
    data['teacher_requirements'], scope_audit = guard_teacher_items(data['teacher_requirements'], caption_index)
    data['requirements_review']['presentation_guard'] = scope_audit
    data['requirements_provenance'] = {
        'method': 'verified_pdf_review' if direct is not None else ('heuristic_summary' if cross_matched else 'source_links'),
        'algorithm_version': direct.get('verification_policy_version') if direct is not None else MATCHER_VERSION,
        'context_hash': data['summary_scope']['context_hash'],
        'source_hash': data.get('_source_hash'),
        'created_at': time.time(),
        'scope_guard_version': MATCHER_VERSION,
    }
    return {'success':True,'source':'pdf_lab_map_reduce','data':data,'total_pages':analysis['total_pages'],'total_units':analysis['total_pages'],
            'processing_time':round(elapsed,3),'metrics':{'total_pages':analysis['total_pages'],'chunk_count':counts['total_chunks']}}


def run_pipeline(path,key,model,cache_dir,context=None,identity=None,options=None,provider_factory=None,import_checkpoint=None,fresh=False):
    started=time.monotonic();options=options or {};context=context or {};identity=identity or {}
    direct_review=options.get('review_engine')=='verified_v2'
    settings=RunSettings(int(options.get('input_budget',8500)),int(options.get('max_output_tokens',16384)),int(options.get('max_generation_calls',5)))
    # Requirement answers have a separate output budget; chapter/Map caches keep theirs.
    link_settings=RunSettings(settings.input_budget,min(settings.max_output_tokens,8192),settings.max_generation_calls)
    source=segment_pdf(path,Settings());plan=prepare_ai_inputs(source,PackagingSettings())
    navigation = from_pdf_lab(source, extract_captions(source)['assets'])
    publish({'source_navigation': navigation}, identity, source['document']['sha256'])

    progress_result = {}

    def with_navigation(result):
        result['data']['source_navigation'] = navigation
        result['data'].update(progress_result)
        return result

    requirements=context_document(context)
    reader_requirements=context_document({}) if direct_review else requirements
    binding=fingerprint([ENGINE_VERSION,READER_VERSION,identity,source['document']['sha256'],model,settings.input_budget,settings.max_output_tokens,plan['fingerprint']])
    folder=Path(cache_dir)/'pdf_lab'/binding;folder.mkdir(parents=True,exist_ok=True)
    if fresh or options.get('fresh'):
        for p in folder.glob('*.json'):
            try: p.unlink()
            except OSError: pass
    summary_path=folder/'summary.json';reader_path=folder/'reader_chapter_v2.json'
    summary_state=load_state(summary_path);reader_state=load_state(reader_path)
    if direct_review:
        from ai_runtime.requirement_review.prompts import PROMPT_VERSION
        from ai_runtime.requirement_review.validation import GUARD_VERSION
        from ai_runtime.requirement_review.retrieval import VERSION as RETRIEVAL_VERSION
        from ai_runtime.requirement_review.coverage import VERSION as COVERAGE_VERSION
        review_key=fingerprint([requirements['fingerprint'],PROMPT_VERSION,GUARD_VERSION,RETRIEVAL_VERSION,COVERAGE_VERSION,'criteria-1'])
        links_path=folder/('review_'+review_key[:16]+'.json')
    else:links_path=folder/'requirement_links.json'
    links_state=load_state(links_path)
    run_id=os.environ.get('AI_PROGRESS_FILE','manual')
    # A human-requested new generation may retry invalid output; an automatic pass may not.
    if reader_state.get('_worker_run') != run_id and (reader_state.get('report') or {}).get('reading_errors'):
        reader_state.pop('report', None)
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
    if reader and (reader.get('section_text_fallback_version') != 1 or
                   (not reader.get('report_overview') and reader.get('overview_version') != OVERVIEW_VERSION)):
        reader = None
        reader_state.pop('report', None)
    if base:
        from ai_runtime.summarizer import validate_map, map_prompt, MAP_SCHEMA
        batches_by_id = {b['batch_id']: b for b in plan['batches']}
        invalid_maps = []
        for bid, mapped in base.get('map_results', {}).items():
            try:
                rebuilt = validate_map({'processed_source_ids':mapped['processed_source_ids'],
                    'items':[{k:i[k] for k in ('source_id','summary','evidence_quote','kind')} for i in mapped['items']]}, batches_by_id[bid], source['document'])
                mapped.update(rebuilt)
            except (ApiError,KeyError,TypeError):
                invalid_maps.append(bid)
        if invalid_maps:
            base = None
            summary_state.pop('report', None)
            summary_state['tasks'] = {k:v for k,v in summary_state.get('tasks',{}).items()
                if v.get('label','').startswith('Map ') and v.get('label')[4:] not in invalid_maps}
            reader_state = {}; reader = None; links_state = {}
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
    published_digest=None
    def publish_partial():
        nonlocal published_digest
        destination=os.environ.get('AI_PARTIAL_RESULT_FILE')
        current_base=summary_state.get('report') or base
        if not destination or not current_base:return
        current_reader=reader_state.get('report') or reader or {}
        partial=reader_state.get('partial_chapters',[])
        if len(partial)>len(current_reader.get('chapter_summaries',[])):
            current_reader=dict(current_reader,status='running',chapter_summaries=partial)
        if reader_state.get('partial_sections'):
            current_reader=dict(current_reader,section_summaries=reader_state['partial_sections'])
        current_links=links_state.get('report') or links
        try:
            value=convert_result(source,plan,current_base,current_reader,requirements,identity,model,time.monotonic()-started,[],context,current_links)
            data=value['data']
            data.update(progress_result)
            visible=[data.get(k) for k in ('document_content','chapters','completed_work','teacher_requirements','content_review','progress_v1','progress_v1_status')]
            digest=fingerprint(visible)
            if digest==published_digest:return
            atomic_json(destination,value);published_digest=digest
            print('AI_PARTIAL_READY',flush=True)
        except (OSError,ValueError,KeyError,TypeError,ApiError):pass # Optional publication cannot fail analysis.
    def phase(label):
        checkpoint();report_progress(stage=label);publish_partial()
    def update(values):
        checkpoint();report_progress(**{k:v for k,v in values.items() if k in ('stage','map_done','map_total','chapter_done','chapter_total')})
    error_list=[]
    try:
        deadline=started+int(options.get('pass_seconds',260))
        cached_analysis = (base and base.get('status') == 'complete' and reader
                           and reader.get('status') == 'complete' and links and links.get('status') == 'complete')
        provider = None
        progress_calls = 0
        if cached_analysis or settings.max_generation_calls > 1:
            def generate_progress(prompt, validator):
                nonlocal provider, progress_calls
                path = folder / ('progress_v1_' + fingerprint(prompt)[:24] + '.json')
                cached = load_state(path)
                if cached:
                    try:
                        return validator(cached)
                    except ValueError:
                        pass
                if provider is None:
                    provider = provider_factory() if provider_factory else WorkerClient(
                        key, model, pacer=SharedPacer(model, deadline, report_progress))
                # This changed progress contract has not been benchmarked on Groq.
                count = super(WorkerClient, provider).count_tokens if isinstance(provider, WorkerClient) else provider.count_tokens
                generate = super(WorkerClient, provider).generate if isinstance(provider, WorkerClient) else provider.generate
                tokens = count(prompt, PROGRESS_SCHEMA, 2048)
                if tokens > settings.input_budget:
                    raise ValueError('progress_input_budget')
                progress_calls += 1
                value = validator(generate(prompt, PROGRESS_SCHEMA, 2048, tokens)['data'])
                try:
                    atomic_json(path, value)
                except OSError:
                    pass
                return value

            fragments = [part for batch in plan['batches'] for part in batch['fragments']]
            try:
                progress_result = extract_progress(
                    (part['text'] for part in fragments), generate_progress,
                    source_complete=not any(part.get('section_visual_content_unread') for part in fragments)
                    and not any(page.get('reading_order_requires_review')
                                or page['state'] in ('scan_suspected', 'vector_without_text')
                                for page in source['document']['pages']),
                )
            except ApiError as exc:
                progress_result = {'progress_v1_status': 'unavailable'}
                if exc.status in (429, 503) or exc.code in ('pacing_wait', 'provider_timeout', 'network_error'):
                    # Progress itself stays Gemini-only. Let compatible Map/reader
                    # tasks use their existing fallback policy in the same pass.
                    fallback = not cached_analysis and isinstance(provider, WorkerClient) and provider.allow_fallback_after(exc)
                    if not cached_analysis and not fallback:
                        raise
                    error_list.append(processing_error(exc, 'progress_v1'))
            except ValueError:
                progress_result = {}
            if not progress_result:
                progress_result = {'progress_v1_status': 'unavailable'}
        if cached_analysis:
            report_progress(stage='complete',map_done=len(plan['batches']),map_total=len(plan['batches']),reused_calls=1)
            return with_navigation(convert_result(source,plan,base,reader,requirements,identity,model,time.monotonic()-started,error_list,context,links))
        if provider is None:
            provider=provider_factory() if provider_factory else WorkerClient(key,model,pacer=SharedPacer(model,deadline,report_progress))
        if progress_calls:
            settings = RunSettings(settings.input_budget, settings.max_output_tokens,
                                   settings.max_generation_calls - progress_calls)
        if not base or base.get('status')!='complete':
            before=summary_state.get('generation_attempts',0)
            base=run_summary_with_provenance(source,plan,provider,summary_state,settings,on_progress=phase,on_update=update)
            used=summary_state.get('generation_attempts',0)-before
        else:used=0
        checkpoint();publish_partial()
        if base.get('status')=='complete' and (not reader or reader.get('status')!='complete'):
            if settings.max_generation_calls<=used:raise ApiError('Tiếp tục bước tạo bản đọc ở lượt nền tiếp theo.','worker_budget')
            before=reader_state.get('generation_attempts',0)
            reader=run_reader(source,plan,base,reader_requirements,provider,reader_state,
                RunSettings(settings.input_budget,settings.max_output_tokens,settings.max_generation_calls-used),on_progress=phase)
            used+=reader_state.get('generation_attempts',0)-before
        publish_partial()
        if reader and reader.get('status')=='complete':
            if settings.max_generation_calls<=used and reader.get('teacher_requirements'):raise ApiError('Tiếp tục nối yêu cầu ở lượt nền sau.','worker_budget')
            if direct_review:
                if used>=settings.max_generation_calls:raise ApiError('Tiếp tục đối chiếu ở lượt nền sau.','worker_budget')
                from verified_requirement_review import run_direct_review
                def review_snapshot(value):
                    links_state['report']=value;publish_partial()
                links,review_errors=run_direct_review(path,context,requirements,identity,model,
                    RunSettings(link_settings.input_budget,link_settings.max_output_tokens,settings.max_generation_calls-used),
                    cache_dir,provider,atomic_json,load_state,phase,run_id,on_snapshot=review_snapshot)
                links_state['report']=links
                for error in review_errors:
                    error_list.append(processing_error(ApiError.from_details(error, 'review_incomplete'),'requirements_review'))
            else:
                links=run_requirement_links(source,base,reader,provider,links_state,
                    RunSettings(link_settings.input_budget,link_settings.max_output_tokens,max(1,settings.max_generation_calls-used)),on_progress=phase)
        for stage, obj in (('summary', base), ('reader', reader), ('requirements_review', links)):
            if obj and obj.get('error'):
                error=obj['error'];error_list.append(processing_error(ApiError.from_details(error), stage))
        if not reader and not error_list:error_list.append(processing_error(ApiError('Chưa hoàn tất bản đọc.','worker_budget')))
    except (ApiError,SegmentationError) as exc:error_list.append(processing_error(exc))
    finally:checkpoint()
    if hashlib.sha256(Path(path).read_bytes()).hexdigest()!=source['document']['sha256']:
        raise ApiError('Tệp nguồn đã thay đổi trong lúc xử lý.','source_changed')
    report_progress(stage='complete' if links and links.get('status')=='complete' else 'partial',map_done=len((base or {}).get('map_results',{})),map_total=len(plan['batches']),
        completed_calls=summary_state.get('generation_attempts',0)+reader_state.get('generation_attempts',0)+links_state.get('generation_attempts',0),waiting_seconds=0)
    return with_navigation(convert_result(source,plan,base,reader,requirements,identity,model,time.monotonic()-started,error_list,context,links))


def main():
    parser=argparse.ArgumentParser()
    parser.add_argument('--pdf',required=True);parser.add_argument('--model',default='gemini-3.5-flash-lite')
    parser.add_argument('--cache-dir',required=True);parser.add_argument('--review-context');parser.add_argument('--document-identity')
    parser.add_argument('--lab-settings');parser.add_argument('--import-lab-checkpoint');parser.add_argument('--output')
    parser.add_argument('--review-only',action='store_true')
    parser.add_argument('--fresh',action='store_true',help='Force fresh run without reading disk cache')
    for name in ('stage','chunk-size','chunk-overlap','method','pipeline-version','target-tokens','overlap-tokens','prompt-reserve-tokens','max-input-tokens'):parser.add_argument('--'+name)
    args=parser.parse_args()
    try:
        result=run_pipeline(args.pdf,os.environ.get('GEMINI_API_KEY',''),args.model,args.cache_dir,
            json.loads(args.review_context) if args.review_context else {},json.loads(args.document_identity) if args.document_identity else {},
            json.loads(args.lab_settings) if args.lab_settings else {},import_checkpoint=args.import_lab_checkpoint,fresh=args.fresh)
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
