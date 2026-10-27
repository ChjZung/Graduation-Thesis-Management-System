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

def test_partial_support_not_exposed_as_accepted_reference(tmp_path):
    p=VerifiedProvider(partial=True)
    out=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,context={'submission_requirements':'1. Describe students and teachers.'},options={'review_engine':'verified_v2','max_generation_calls':100},provider_factory=lambda:p)
    assert out['data']['requirements_review']['status']=='completed'
    assert all(not q['source_references'] for i in out['data']['teacher_requirements'] for q in i['report_points'])

def test_verified_review_finishes_across_small_background_passes(tmp_path):
    provider=VerifiedProvider();options={'review_engine':'verified_v2','max_generation_calls':1}
    context={'submission_requirements':'1. Describe students and teachers.'}
    for attempt in range(40):
        result=bridge.run_pipeline(PDF,'fake',provider.model,tmp_path,context=context,options=options,provider_factory=lambda:provider)
        if result['data']['analysis']['status']=='completed':break
    assert result['data']['analysis']['status']=='completed'
    assert result['data']['requirements_review']['status']=='completed'
    assert attempt>0
