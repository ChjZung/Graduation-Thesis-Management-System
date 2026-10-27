import json
import sys
from pathlib import Path
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services'))
import lab_pdf_summary as bridge
from verified_requirement_review import requirement_set
from test_lab_pdf_bridge import Provider,PDF

class VerifiedProvider(Provider):
    def __init__(self,partial=False):super().__init__();self.partial=partial
    def generate(self,prompt,schema,output,tokens):
        if 'PLANNING_DATA:' in prompt:
            self.calls+=1;p=json.loads(prompt.split('PLANNING_DATA:\n')[1]);data={'items':{i['item_id']:{'checks':[{'description':i['original_text'],'requirement_excerpt':i['original_text'],'kind':'content'}]} for i in p['items']}}
        elif 'MATCHING_DATA:' in prompt:
            self.calls+=1;p=json.loads(prompt.split('MATCHING_DATA:\n')[1]);data={'checks':{}}
            for check in p['checks']:
                sid=check['candidate_ids'][0];text=p['sources'][sid]['raw_text']
                data['checks'][check['check_id']]={'status':'CANDIDATE','finding':'Nguồn mô tả nội dung được yêu cầu.','evidence':[{'source_id':sid,'quote':text[:500],'occurrence':1}]}
        elif 'VERIFICATION_DATA:' in prompt:
            self.calls+=1;p=json.loads(prompt.split('VERIFICATION_DATA:\n')[1]);data={'checks':{}}
            for check in p['checks']:
                data['checks'][check['check_id']]={'verdict':'PARTIAL' if self.partial else 'SUPPORTS','explanation':'Kiểm tra giao thức bằng nguồn giả lập.',
                    'supported_elements':[check['description']],'missing_elements':[],'overclaim_detected':False,'safe_rewrite':'Nguồn chỉ hỗ trợ một phần.' if self.partial else '',
                    'supported_evidence_ids':[e['evidence_id'] for e in check['evidence']]}
        else:return super().generate(prompt,schema,output,tokens)
        return {'data':data,'usage':{'totalTokenCount':100}}

def test_numbering_resets_between_stage_and_round_are_preserved():
    context={'stage_requirements':'1. Giới thiệu bài toán.','submission_requirements':'1. Trình bày mục tiêu.\n2. Nêu phạm vi.\nChưa yêu cầu lập trình.'}
    result=requirement_set(context,bridge.context_document(context))
    assert len(result['items'])==3 and len({i['item_id'] for i in result['items']})==3
    assert result['items'][-1]['original_text']=='Nêu phạm vi.' and result['general_conditions']

def test_new_review_runs_after_summary_and_resumes_without_api(tmp_path):
    p=VerifiedProvider();context={'submission_requirements':'1. Describe students and teachers.'};options={'review_engine':'verified_v2','max_generation_calls':100}
    out=bridge.run_pipeline(PDF,'fake-key',p.model,tmp_path,context=context,options=options,provider_factory=lambda:p)
    data=out['data'];assert data['analysis']['status']=='completed'
    assert data['requirements_review']['mode']=='verified_v2' and data['requirements_review']['status']=='completed'
    assert data['chapters'] and data['teacher_requirements']
    refs=[r for i in data['teacher_requirements'] for q in i['report_points'] for r in q['source_references']]
    assert refs and all(r['semantic_support']=='SUPPORTS' and r['document_sha256']==data['_source_hash'] for r in refs)
    before=p.calls
    again=bridge.run_pipeline(PDF,'',p.model,tmp_path,context=context,options=options,provider_factory=lambda:(_ for _ in ()).throw(AssertionError('Unnecessary API')))
    assert again['data']['requirements_review']['status']=='completed' and p.calls==before
    assert 'fake-key' not in json.dumps(data)

def test_verified_partial_support_keeps_sources_and_safe_rewrite(tmp_path):
    p=VerifiedProvider(partial=True)
    out=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,context={'submission_requirements':'1. Describe students and teachers.'},options={'review_engine':'verified_v2','max_generation_calls':100},provider_factory=lambda:p)
    assert out['data']['requirements_review']['status']=='completed'
    points=[q for i in out['data']['teacher_requirements'] for q in i['report_points']]
    assert any(q['check_status']=='PARTIAL' and q['explanation']=='Nguồn chỉ hỗ trợ một phần.' for q in points)
    refs=[r for q in points for r in q['source_references']]
    assert refs and all(r['semantic_support']=='PARTIAL' and r['quote_verified'] for r in refs)

def test_verified_review_finishes_across_small_background_passes(tmp_path):
    provider=VerifiedProvider();options={'review_engine':'verified_v2','max_generation_calls':1}
    context={'submission_requirements':'1. Describe students and teachers.'}
    for attempt in range(40):
        result=bridge.run_pipeline(PDF,'fake',provider.model,tmp_path,context=context,options=options,provider_factory=lambda:provider)
        if result['data']['analysis']['status']=='completed' and result['data']['requirements_review']['status']=='completed':break
    assert result['data']['analysis']['status']=='completed'
    assert result['data']['requirements_review']['status']=='completed'
    assert attempt>0


def test_broad_vietnamese_usecase_requirement_covers_each_document_section():
    from ai_runtime.requirement_review.coverage import apply_coverage
    names=['Lập kế hoạch','Sinh viên đủ điều kiện','Quản lý đề tài','Đăng ký đề tài','Theo dõi tiến độ','Hồ sơ bảo vệ','Tổ chức bảo vệ','Lưu trữ kết quả']
    index={'canonical':{'text':'x'*900,'lines':[]},'sections':[{'title':f'3.3.2.{n+1} Đặc tả Use Case {name}','level':4,'char_start':n*100,'path':[]} for n,name in enumerate(names)]}
    for wording in ['Sơ đồ Use-Case tổng quát và đặc tả các ca sử dụng chính.','Trình bày đặc tả các Use Case chính.']:
        item={'item_id':'R2','original_text':wording,'checks':[{'kind':'diagram','description':'Sơ đồ tổng quát'}]}
        expanded=apply_coverage([item],index)[0]
        specs=[c for c in expanded['checks'] if c.get('coverage_family')=='usecase']
        assert len(specs)==8
        assert {c['coverage_target']['name'] for c in specs}==set(names)
        assert all(c['requirement_text']==wording for c in specs)
        assert any(c['kind']=='diagram' for c in expanded['checks'])


def test_named_or_implementation_request_does_not_expand_to_all_usecases():
    from ai_runtime.requirement_review.coverage import apply_coverage
    index={'canonical':{'text':'x'*200,'lines':[]},'sections':[{'title':'3.3.2.1 Đặc tả Use Case đăng ký đề tài','level':4,'char_start':0,'path':[]}]}
    for wording in ['Đặc tả Use Case đăng ký đề tài.','Đã lập trình đặc tả các ca sử dụng chính.']:
        item={'item_id':'R','original_text':wording,'checks':[]}
        assert apply_coverage([item],index)[0]['checks']==[]


def test_mixed_diagram_requirement_does_not_reject_textual_usecase_evidence():
    from ai_runtime.requirement_review.validation import source_guard
    check={'kind':'content','coverage_family':'usecase','coverage_target':{'name':'Đăng ký đề tài'},'requirement_text':'Sơ đồ Use-Case tổng quát và đặc tả các ca sử dụng chính.','requirement_excerpt':'đặc tả các ca sử dụng chính','description':'Đặc tả và quy tắc nghiệp vụ: Đăng ký đề tài'}
    evidence={'source_type':'table_cell','quote':'Sinh viên chọn đề tài, hệ thống kiểm tra điều kiện và lưu phiếu đăng ký.','row_label':'Luồng chính','section_title':'Đặc tả Use Case đăng ký đề tài','section_path':['Phân tích hệ thống','Đặc tả Use Case đăng ký đề tài']}
    assert source_guard(check,{'evidence':[evidence]}) is None
    check['kind']='diagram'
    assert source_guard(check,{'evidence':[evidence]})[0]=='NEEDS_VISUAL_REVIEW'


def test_manual_caption_location_is_exact_but_never_proof_of_diagram_content():
    from ai_runtime.requirement_review.engine import diagram_locations
    text='Hình 3.10 Sơ đồ Use-Case hệ thống'
    doc={'document_id':'D','version_id':'V','sha256':'hash','total_pages':1}
    source={'source_id':'S','source_type':'figure_caption','raw_text':text,'document_id':'D','document_version_id':'V','document_sha256':'hash','page_number':1,'page_index':0,'segments':[{'raw_start':0,'raw_end':len(text),'char_start':0,'char_end':len(text),'page_number':1,'line_id':'L'}],'retrieval_context':'Sơ đồ hệ thống','expanded_context':text}
    index={'document':doc,'canonical':{'text':text,'pages':[{'start':0,'end':len(text)}]},'sections':[],'sources':{'S':source}}
    check={'check_id':'C','kind':'diagram','description':'Sơ đồ Use-Case tổng quát','requirement_excerpt':'Sơ đồ Use-Case tổng quát'}
    refs=diagram_locations(index,check,{})
    assert len(refs)==1 and refs[0]['page_number']==1 and refs[0]['quote']==text
    assert refs[0]['semantic_support']=='NEEDS_VISUAL_REVIEW'
    check['description']='Sơ đồ tuần tự';check['requirement_excerpt']='Sơ đồ tuần tự'
    assert diagram_locations(index,check,{})==[]


def test_incremental_publication_exposes_valid_chapters_before_review_finishes(tmp_path,monkeypatch):
    import copy
    saved=[];write=bridge.atomic_json;path=tmp_path/'partial.json'
    def capture(destination,value):
        write(destination,value)
        if str(destination)==str(path):saved.append(copy.deepcopy(value))
    monkeypatch.setattr(bridge,'atomic_json',capture)
    monkeypatch.setenv('AI_PARTIAL_RESULT_FILE',str(path))
    provider=VerifiedProvider();context={'submission_requirements':'1. Describe students and teachers.'}
    options={'review_engine':'verified_v2','max_generation_calls':100}
    final=bridge.run_pipeline(PDF,'fake',provider.model,tmp_path/'streamed',context=context,options=options,provider_factory=lambda:provider)
    ready=[d['data'] for d in saved if any(c['items'] for c in d['data']['chapters']) and d['data']['requirements_review']['status']!='completed']
    assert ready, 'A cited chapter must be published before review completes'
    assert all(r['quote_verified'] for d in ready for c in d['chapters'] for i in c['items'] for r in i['source_references'])
    assert any(d['analysis']['status']=='completed' and d['requirements_review']['status']=='pending' for d in ready)
    calls=provider.calls
    monkeypatch.delenv('AI_PARTIAL_RESULT_FILE')
    other=VerifiedProvider()
    baseline=bridge.run_pipeline(PDF,'fake',other.model,tmp_path/'baseline',context=context,options=options,provider_factory=lambda:other)
    assert baseline['data']['overview_summary']==final['data']['overview_summary'] and other.calls==calls
