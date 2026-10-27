"""Resumable requirement-only pipeline: planning, per-check retrieval, matching, verification."""
import copy
import hashlib
import json
import threading
import time
from . import VERSION
from .source_index import VERSION as INDEX_VERSION
from .prompts import PROMPT_VERSION,PLAN_PROMPT,MATCH_PROMPT,VERIFY_PROMPT
from .schemas import plan_schema,match_schema,verify_schema
from .validation import validate_plan,validate_matches,validate_verifications,source_guard,GUARD_VERSION
from .retrieval import SourceSearch,VERSION as RETRIEVAL_VERSION
from .coverage import apply_coverage,VERSION as COVERAGE_VERSION
from ..provider import ApiError
from ..summarizer import fingerprint,RunSettings

LOCKS={};LOCK_GUARD=threading.Lock()
MISSING='Chưa đủ dẫn chứng để kết luận; giảng viên vui lòng đối chiếu báo cáo gốc.'
SCHEDULER_VERSION='incremental-verified-1.0'
BUSY_RETRY_DELAYS=(5,10)


def analysis_identity(index,requirements,model,settings):
    return fingerprint([VERSION,INDEX_VERSION,PROMPT_VERSION,GUARD_VERSION,index['document'],requirements['id'],requirements['version'],requirements['sha256'],model,settings.input_budget,settings.max_output_tokens])


def make_report(state,index,requirements):
    planned=state.get('planned_items') or requirements['items'];matches=state.get('matches',{});verifications=state.get('verifications',{})
    results=[];accepted={};diagnostics=[];processed=0;total=0
    failed_checks={cid for error in state.get('errors',{}).values() for cid in error.get('check_ids',[])}
    for item in planned:
        checks=[]
        for check in item.get('checks',[]):
            cid=check['check_id'];total+=1;match=matches.get(cid);verification=verifications.get(cid)
            # Apply current deterministic policy even to cached LLM decisions.
            guard=source_guard(check,match) if match and match.get('evidence') else None
            if guard and verification:
                verification={'verdict':guard[0],'explanation':guard[1],'supported_elements':[],
                    'missing_elements':[check['description']],'overclaim_detected':True,'safe_rewrite':'',
                    'supported_evidence_ids':[],'method':'deterministic_guard','policy_version':GUARD_VERSION}
            row=dict(check,status='NOT_PROCESSED',finding=None,evidence_ids=[],missing_elements=[],reason='Chưa đối chiếu tiêu chí này.')
            if cid in failed_checks:row.update(status='PROCESSING_ERROR',reason='Bước đối chiếu gặp lỗi; chưa thể kết luận về dẫn chứng.')
            elif match:
                if match['citation_errors'] and not match['evidence']:
                    row.update(status='PROCESSING_ERROR',reason='Câu trích đề xuất không vượt qua kiểm tra nguyên văn/vị trí; chưa thể dùng làm dẫn chứng.')
                elif match['status'] in ('NOT_FOUND','NEEDS_VISUAL_REVIEW'):
                    row.update(status='NEEDS_VISUAL_REVIEW' if match['status']=='NEEDS_VISUAL_REVIEW' else 'NO_VERIFIED_EVIDENCE',reason='Cần mở PDF kiểm tra nội dung hình/sơ đồ.' if match['status']=='NEEDS_VISUAL_REVIEW' else MISSING);processed+=1
                elif verification:
                    verdict=verification['verdict'];reason=verification['explanation'];selected=[e for e in match['evidence'] if e['evidence_id'] in verification['supported_evidence_ids']]
                    if verdict=='SUPPORTS' and not verification['overclaim_detected'] and selected:
                        row.update(status='SUPPORTED',finding=match['finding'],reason=reason)
                    elif verdict=='PARTIAL' and selected and verification['safe_rewrite']:
                        row.update(status='NO_VERIFIED_EVIDENCE',reason='Nguồn chỉ hỗ trợ một phần, chưa đủ chắc chắn để hiển thị kết luận. '+reason)
                    else:row.update(status='NEEDS_VISUAL_REVIEW' if verdict=='NEEDS_VISUAL_REVIEW' else 'NO_VERIFIED_EVIDENCE',reason=reason)
                    row['missing_elements']=verification['missing_elements'];row['semantic_verification']=verification
                    for e in match['evidence']:
                        if e in selected and row['status'] in ('SUPPORTED','PARTIAL'):
                            out=dict(e,semantic_support=verdict);accepted[e['evidence_id']]=out;row['evidence_ids'].append(e['evidence_id'])
                        else:diagnostics.append(dict(e,semantic_support=verdict,check_id=cid,rejection_reason=reason))
                    processed+=1
                elif match['status']=='CANDIDATE' and match['evidence']:
                    row.update(status='PENDING_VERIFICATION',reason='Đã tìm được nguồn ứng viên; chưa hoàn tất kiểm chứng, chưa có kết luận đối chiếu.')
            checks.append(row)
        positive=[c for c in checks if c['status'] in ('SUPPORTED','PARTIAL')]
        missing=[c for c in checks if c['status'] not in ('SUPPORTED',)]
        if checks and all(c['status']=='SUPPORTED' for c in checks):status='HAS_EVIDENCE'
        elif positive:status='PARTIAL_EVIDENCE'
        elif any(c['status']=='PROCESSING_ERROR' for c in checks) or (not checks and (state.get('errors') or state.get('processing_status')=='FAILED')):status='PROCESSING_ERROR'
        elif not checks or any(c['status'] in ('NOT_PROCESSED','PENDING_VERIFICATION') for c in checks):status='NOT_PROCESSED'
        elif any(c['status']=='NEEDS_VISUAL_REVIEW' for c in checks):status='NEEDS_VISUAL_REVIEW'
        else:status='NOT_FOUND'
        if positive:
            answer=' '.join(c['finding'] for c in positive[:2] if c['finding'])
            if len(positive)>2:answer+=f' Có thêm {len(positive)-2} tiêu chí có nguồn ở phần chi tiết.'
            if missing:answer+=' Cần kiểm tra thêm: '+', '.join(c['description'] for c in missing[:3])+('.' if len(missing)<=3 else '…')
        else:answer=MISSING if status=='NOT_FOUND' else ('Chưa thực hiện đối chiếu yêu cầu này.' if status=='NOT_PROCESSED' else 'Xử lý gặp lỗi; chưa có kết luận đối chiếu cho yêu cầu này.')
        if not positive and any(c['status']=='PENDING_VERIFICATION' for c in checks):answer='Đã tìm nguồn ứng viên nhưng chưa hoàn tất kiểm chứng; chưa có kết luận đối chiếu.'
        if status=='NEEDS_VISUAL_REVIEW':
            visual=sum(c['status']=='NEEDS_VISUAL_REVIEW' for c in checks)
            answer=f'Cần mở PDF kiểm tra hình/sơ đồ cho {visual} tiêu chí; văn bản trích xuất chưa đủ để xác minh.'
        coverage=None
        if item.get('coverage'):
            coverage=dict(item['coverage'],processed=sum(c['status'] not in ('NOT_PROCESSED','PENDING_VERIFICATION','PROCESSING_ERROR') for c in checks),
                with_support=sum(c['status'] in ('SUPPORTED','PARTIAL') for c in checks),
                fully_supported=sum(c['status']=='SUPPORTED' for c in checks),
                manual_review=sum(c['status']=='NEEDS_VISUAL_REVIEW' for c in checks),
                incomplete_targets=[c.get('coverage_target',{}).get('name',c['description']) for c in checks if c['status']!='SUPPORTED'])
        results.append({'item_id':item['item_id'],'number':item.get('number'),'original_text':item['original_text'],'original_label':item['original_label'],
            'status':status,'short_answer':answer,'checks':checks,'coverage':coverage,'needs_manual_review':[c['description']+': '+c['reason'] for c in missing]})
    errors=list(state.get('errors',{}).values())
    if state.get('processing_status')=='FAILED' and not errors and state.get('last_error'):errors=[dict(state['last_error'],stage='Khởi tạo đối chiếu',check_ids=[])]
    citation_error=any(c['status']=='PROCESSING_ERROR' for r in results for c in r['checks'])
    complete=bool(state.get('planned_items')) and processed==total and not errors and not citation_error
    status='COMPLETED' if complete else state.get('processing_status','NOT_PROCESSED')
    if status=='COMPLETED' and not complete:status='PARTIAL'
    return {'schema_version':'2.0','scheduler_version':SCHEDULER_VERSION,'pipeline_version':VERSION,'prompt_version':PROMPT_VERSION,'verification_policy_version':GUARD_VERSION,'retrieval_version':state.get('retrieval_version','legacy' if state.get('matches') else RETRIEVAL_VERSION),'analysis_id':state.get('identity'),
        'coverage_version':state.get('coverage_version'),'document':index['document'],'requirement_set':dict(requirements,items=planned),'results':results,
        'evidence':list(accepted.values()),'rejected_evidence':diagnostics,
        'processing':{'status':status,'completed_checks':processed,'total_checks':total,'errors':errors,
            'generation_calls':state.get('generation_calls',0),'http_request_count':len(state.get('http_ledger',[])),
            'elapsed_seconds':round(state.get('elapsed_seconds',0),3),'transient_retries':len(state.get('retry_events',[]))},'usage':state.get('usage',{}),
        'limitations':['Chỉ xử lý văn bản và bảng có chữ; chưa đọc nội dung hình/sơ đồ.','Kiểm chứng ngữ nghĩa bằng LLM không phải nhãn chuẩn hoặc điểm đánh giá sinh viên.']}


def run_review(index,requirements,provider,state=None,settings=None,on_progress=None,retry_failed=False,busy_retry_delays=BUSY_RETRY_DELAYS):
    state=state if state is not None else {};settings=settings or RunSettings();identity=analysis_identity(index,requirements,provider.model,settings)
    if state.get('identity')!=identity:state.clear();state.update(identity=identity,tasks={},matches={},verifications={},errors={},generation_calls=0,http_ledger=[],usage={},elapsed_seconds=0)
    with LOCK_GUARD:lock=LOCKS.setdefault(identity,threading.Lock())
    if not lock.acquire(blocking=False):return make_report(state,index,requirements)
    started=time.monotonic();http_start=len(getattr(provider,'http_calls',[]));first_calls=state['generation_calls']
    def notify(label):
        state['stage']=label
        if on_progress:on_progress(label)
    if retry_failed:
        invalid={cid for cid,m in state['matches'].items() if m.get('citation_errors')}
        if invalid:
            for cid in invalid:state['matches'].pop(cid,None);state['verifications'].pop(cid,None)
            for key in [k for k,t in state['tasks'].items() if t.get('stage')=='Tìm câu trích cho từng tiêu chí' and isinstance(t.get('validated'),dict) and invalid.intersection(t['validated'])]:state['tasks'].pop(key)
        state.pop('last_error',None)
        for key in [key for key,task in state['tasks'].items() if task.get('error')]:state['tasks'].pop(key)
        state['errors'].clear()
    def request_with_busy_retry(stage,operation,generation=False):
        for attempt in range(len(busy_retry_delays)+1):
            before_requests=len(getattr(provider,'http_calls',[]))
            if generation:
                if state['generation_calls']-first_calls>=settings.max_generation_calls:
                    raise ApiError('Đã lưu phần hoàn tất và chạm ngân sách lượt gọi; bấm tiếp tục để xử lý phần còn lại.','review_call_budget')
                state['generation_calls']+=1;notify(stage)
            try:
                return operation()
            except ApiError as exc:
                if generation and len(getattr(provider,'http_calls',[]))==before_requests and exc.code in ('pacing_wait','job_cancelled','job_lease_lost','job_deadline','job_budget','tpm_request_limit'):state['generation_calls']-=1
                # Only an explicit 503 is retried. Never blindly resend timeouts,
                # authentication failures, quota errors or malformed responses.
                if exc.status!=503 or attempt>=len(busy_retry_delays):raise
                if generation and state['generation_calls']-first_calls>=settings.max_generation_calls:
                    raise ApiError('Gemini trả 503 và đã chạm ngân sách lượt gọi; kết quả được giữ, bấm tiếp tục để thử lại.','review_call_budget') from None
                delay=busy_retry_delays[attempt]
                state.setdefault('retry_events',[]).append({'stage':stage,'status':503,'retry':attempt+1,'wait_seconds':delay})
                notify(f'{stage} — Gemini trả 503, chờ {delay} giây rồi thử lại {attempt+1}/{len(busy_retry_delays)}')
                time.sleep(delay)

    def call(stage,group,prompt,schema,validator):
        task_id=fingerprint([stage,prompt,schema]);state['active_task_id']=task_id;task=state['tasks'].setdefault(task_id,{'stage':stage})
        if task.get('error'):raise ApiError(task['error']['message'],'cached_review_error')
        if 'validated' in task:return task['validated']
        try:
            notify(stage)
            if state['generation_calls']-first_calls>=settings.max_generation_calls:raise ApiError('Đã lưu phần hoàn tất; bấm tiếp tục để xử lý phần còn lại.','review_call_budget')
            if 'tokens' not in task:task['tokens']=request_with_busy_retry(stage,lambda:provider.count_tokens(prompt,schema,settings.max_output_tokens))
            if task['tokens']>settings.input_budget:raise ApiError('Cần chia nhỏ nhóm tiêu chí.','review_input_budget')
            limits=state['model_info']
            if settings.max_output_tokens>limits['outputTokenLimit'] or task['tokens']+settings.max_output_tokens>limits['inputTokenLimit']:raise ApiError('Vượt giới hạn input/output đã xác minh của model.','model_limit')
            response=request_with_busy_retry(stage,lambda:provider.generate(prompt,schema,settings.max_output_tokens,task['tokens']),generation=True)
            for key,value in response.get('usage',{}).items():
                if type(value) is int and value>=0:state['usage'][key]=state['usage'].get(key,0)+value
            task['validated']=validator(response['data']);task['usage']=response.get('usage',{});state['errors'].pop(task_id,None);notify(stage+' — đã lưu')
            return task['validated']
        except ApiError as exc:
            if exc.code not in ('review_call_budget','review_input_budget','pacing_wait','job_cancelled','job_lease_lost','job_deadline','job_budget','tpm_request_limit'):
                error={'stage':stage,'code':exc.code,'message':str(exc),'check_ids':[c['check_id'] for c in group if 'check_id' in c]}
                task['error']=error;state['errors'][task_id]=error
            raise
    def context():return {'intro':requirements['intro'],'general_conditions':[c['text'] for c in requirements['general_conditions']], 'document':index['document']}
    def planning(items):
        payload={**context(),'items':items};prompt=PLAN_PROMPT+json.dumps(payload,ensure_ascii=False,separators=(',',':'))
        try:return call('Phân tích tiêu chí yêu cầu',items,prompt,plan_schema(items),lambda data:validate_plan(data,items))
        except ApiError as exc:
            if exc.code=='review_input_budget' and len(items)>1:
                mid=len(items)//2;return planning(items[:mid])+planning(items[mid:])
            raise
    item_lookup={}
    def matching(checks):
        if not checks:return {}
        payload={**context(),'checks':checks,'items':{c['item_id']:item_lookup[c['item_id']]['original_text'] for c in checks},
            'sources':{sid:{k:s[k] for k in ('source_id','source_type','raw_text','retrieval_context','page_number')} for c in checks for sid in c['candidate_ids'] for s in [index['sources'][sid]]}}
        prompt=MATCH_PROMPT+json.dumps(payload,ensure_ascii=False,separators=(',',':'))
        try:return call('Tìm câu trích cho từng tiêu chí',checks,prompt,match_schema(checks),lambda data:validate_matches(data,checks,index))
        except ApiError as exc:
            if exc.code=='review_input_budget':
                if len(checks)>1:
                    mid=len(checks)//2;left=matching(checks[:mid]);state['matches'].update(left);verify_ready(checks[:mid]);return dict(left,**matching(checks[mid:]))
                check=checks[0]
                if len(check['candidate_ids'])>2:
                    reduced=dict(check,candidate_ids=check['candidate_ids'][:-1]);state['retrieval'][check['check_id']]['used_candidate_ids']=reduced['candidate_ids']
                    return matching([reduced])
            raise
    def verifying(checks):
        if not checks:return {}
        payload={**context(),'checks':checks,'items':{c['item_id']:item_lookup[c['item_id']]['original_text'] for c in checks}}
        prompt=VERIFY_PROMPT+json.dumps(payload,ensure_ascii=False,separators=(',',':'))
        try:return call('Kiểm chứng mức hỗ trợ của dẫn chứng',checks,prompt,verify_schema(checks),lambda data:validate_verifications(data,checks))
        except ApiError as exc:
            if exc.code=='review_input_budget' and len(checks)>1:
                mid=len(checks)//2;left=verifying(checks[:mid]);state['verifications'].update(left);return dict(left,**verifying(checks[mid:]))
            raise
    def verify_ready(available_checks):
        pending_verification=[]
        for c in available_checks:
            cid=c['check_id'];match=state['matches'].get(cid)
            if cid in state['verifications'] or not match or match['status']!='CANDIDATE' or not match['evidence']:continue
            guard=source_guard(c,match)
            if guard:
                state['verifications'][cid]={'verdict':guard[0],'explanation':guard[1],'supported_elements':[],
                    'missing_elements':[c['description']],'overclaim_detected':True,'safe_rewrite':'','supported_evidence_ids':[],'method':'deterministic_guard'}
                notify('Đã lưu kết quả kiểm chứng nguồn')
            else:pending_verification.append(dict(c,finding=match['finding'],evidence=match['evidence']))
        for at in range(0,len(pending_verification),4):
            state['verifications'].update(verifying(pending_verification[at:at+4]));notify('Đã lưu kết quả kiểm chứng nhóm')

    try:
        if state.get('retrieval_version')!=RETRIEVAL_VERSION:
            # Only an explicit run upgrades old search results; passive page views
            # keep the saved report and never trigger provider work.
            state['matches'].clear();state['verifications'].clear();state['errors'].clear()
            state.pop('retrieval',None);state.pop('last_error',None)
            state['tasks']={key:task for key,task in state['tasks'].items() if task.get('stage')=='Phân tích tiêu chí yêu cầu'}
            state['retrieval_version']=RETRIEVAL_VERSION;state['processing_status']='NOT_PROCESSED'
        previous=make_report(state,index,requirements)
        if previous['processing']['status']=='COMPLETED':return previous
        state['processing_status']='PROCESSING'
        if 'model_info' not in state:
            info=request_with_busy_retry('Đọc thông tin model',provider.model_info)
            if 'generateContent' not in (info.get('supportedGenerationMethods') or []) or any(type(info.get(k)) is not int or info[k]<=0 for k in ('inputTokenLimit','outputTokenLimit')):raise ApiError('Không xác minh được giới hạn model.','model_limit')
            state['model_info']=info
        if not state.get('planned_items'):state['planned_items']=planning(requirements['items'])
        if state.get('coverage_version')!=COVERAGE_VERSION:
            state['planned_items']=apply_coverage(state['planned_items'],index);state['coverage_version']=COVERAGE_VERSION
        item_lookup.update({i['item_id']:i for i in state['planned_items']})
        checks=[c for item in state['planned_items'] for c in item['checks']]
        if 'retrieval' not in state:
            notify('Tìm nguồn gốc PDF theo từng tiêu chí');search=SourceSearch(index);state['retrieval']={}
            for c in checks:
                found=search.retrieve(item_lookup[c['item_id']],c)
                state['retrieval'][c['check_id']]={'candidates':found,'used_candidate_ids':[s['source_id'] for s in found]}
        pending=[]
        for c in checks:
            cid=c['check_id'];ids=state['retrieval'][cid]['used_candidate_ids']
            if cid in state['matches']:continue
            if c.get('coverage_family')=='diagram':
                state['matches'][cid]={'check_id':cid,'status':'NEEDS_VISUAL_REVIEW','finding':'','evidence':[],'citation_errors':[]};continue
            if not ids:state['matches'][cid]={'check_id':cid,'status':'NOT_FOUND','finding':'','evidence':[],'citation_errors':[]}
            else:pending.append(dict(c,candidate_ids=ids))
        # Recover useful work already saved before a previous interruption first.
        verify_ready(checks)
        for at in range(0,len(pending),4):
            batch=pending[at:at+4]
            state['matches'].update(matching(batch));notify('Đã lưu kết quả tìm câu trích nhóm')
            verify_ready(batch)
        state['processing_status']='COMPLETED'
    except ApiError as exc:
        state['processing_status']='PAUSED' if exc.code in ('review_call_budget','pacing_wait') else ('PARTIAL' if state['matches'] else 'FAILED')
        state['last_error']={'code':exc.code,'message':str(exc)}
    finally:
        state['elapsed_seconds']+=time.monotonic()-started;state['http_ledger'].extend(getattr(provider,'http_calls',[])[http_start:]);lock.release()
        if on_progress:on_progress(state.get('stage','Đã lưu trạng thái'))
    report=make_report(state,index,requirements);state['report']=report
    return report
