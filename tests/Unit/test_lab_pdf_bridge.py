"""Integration protocol tests; no external API or application database writes."""
import json
import sys
from pathlib import Path
import pytest
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services'))
import lab_pdf_summary as bridge

PDF=Path(__file__).resolve().parents[1]/'fixtures/pdf_lab/sample_report.pdf'

class Provider:
    model='fixture-model'
    def __init__(self):self.http_calls=[];self.calls=0
    def model_info(self):return {'inputTokenLimit':1000000,'outputTokenLimit':65536,'supportedGenerationMethods':['generateContent']}
    def count_tokens(self,*args):return 200
    def generate(self,prompt,schema,output,tokens):
        self.calls+=1
        if 'SOURCE_DATA:\n' in prompt:
            sources=json.loads(prompt.split('SOURCE_DATA:\n')[1])
            data={'processed_source_ids':[s['source_id'] for s in sources],'items':[{'source_id':s['source_id'],'summary':'Nội dung cơ sở trong nguồn thử nghiệm.','evidence_quote':s['text'].strip(),'kind':'theory'} for s in sources if len(s['text'].strip())>=8]}
        elif 'SOURCE:\n' in prompt:
            source=json.loads(prompt.split('SOURCE:\n')[1])
            data={'processed_source_ids':[source['source_id']],'items':[{'text':'Yêu cầu được tóm tắt từ mô tả đợt.','source_id':source['source_id'],'evidence_quote':source['text']}]}
        elif 'checks' in schema['properties']:
            data={'checks':{k:True for k in schema['properties']['checks']['properties']},'missing':[]}
        elif 'links' in schema['properties']:
            data=json.loads(prompt.split('LINK_DATA:\n')[1])
            pool=data['evidence'];links=[]
            for r in data['requirements']:
                ids=r['candidate_ids'][:1];relation='related_text' if ids else 'not_found'
                point=dict(title='Nội dung yêu cầu',requirement_excerpt=r['text'],relation=relation,explanation='Đoạn nguồn liên quan để giảng viên xem.',evidence_ids=ids,support_quotes=[pool[i]['quote'] for i in ids])
                links.append(dict(requirement_id=r['requirement_id'],relation=relation,explanation=point['explanation'],evidence_ids=ids,points=[point]))
            data={'links':links}
        elif 'paragraphs' in schema['properties']:
            ids=schema['properties']['paragraphs']['items']['properties']['evidence_ids']['items']['enum']
            data={'paragraphs':[{'text':'Đoạn tóm tắt thử nghiệm có dẫn chứng từ tài liệu.','evidence_ids':ids}]}
        else:
            entries=json.loads(prompt.split('ENTRIES:\n')[1]);data={'points':[{'text':'Nội dung thử nghiệm.','item_ids':[i for e in entries for i in e['item_ids']]}]}
        return {'data':data,'usage':{'promptTokenCount':200,'totalTokenCount':250}}


def test_full_adapter_output_has_chapters_individual_citations_and_no_fake_semantic_audit(tmp_path):
    p=Provider();out=bridge.run_pipeline(PDF,'not-a-real-key',p.model,tmp_path,identity={'document_id':'D','document_version_id':'V','group_id':'G'},options={'max_generation_calls':100},provider_factory=lambda:p)
    data=out['data']
    assert data['analysis']['status']=='completed' and data['analysis']['engine']=='pdf_lab'
    assert data['requirements_review']['status']=='not_configured'
    assert data['source_identity']['document_version_id']=='V'
    assert not data['analysis']['semantic_audit_completed']
    assert not data['analysis']['completion_audit_completed']
    assert data['analysis']['quality_checks_completed']
    assert data['chapters'] and data['document_content']
    for chapter in data['chapters']:
        for item in chapter['items']:
            assert item['source_references'] and not item['relevance_verified']
            assert all(r['quote'] and r['page']>=1 for r in item['source_references'])
    assert 'not-a-real-key' not in json.dumps(out)
    assert all('not-a-real-key' not in f.read_text(encoding='utf-8') for f in tmp_path.rglob('*.json'))


def test_resume_from_disk_and_completed_read_never_calls_provider(tmp_path):
    calls=[]
    def provider():p=Provider();calls.append(p);return p
    for _ in range(30):
        out=bridge.run_pipeline(PDF,'fake',Provider.model,tmp_path,options={'max_generation_calls':1},provider_factory=provider)
        if out['data']['analysis']['status']=='completed':break
    assert out['data']['analysis']['status']=='completed'
    before=len(calls)
    out=bridge.run_pipeline(PDF,'',Provider.model,tmp_path,options={'max_generation_calls':1},provider_factory=provider)
    assert len(calls)==before and out['data']['analysis']['status']=='completed'


def test_changed_teacher_brief_reuses_maps_and_chapters_but_updates_source(tmp_path):
    p=Provider();opts={'max_generation_calls':100}
    a=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,context={'submission_requirements':'Hoàn thiện sơ đồ hoạt động trong tuần này.'},options=opts,provider_factory=lambda:p)
    n=p.calls
    b=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,context={'submission_requirements':'Bổ sung mô tả cơ sở dữ liệu trước ngày báo cáo.'},options=opts,provider_factory=lambda:p)
    assert a['data']['analysis']['status']=='completed' and b['data']['analysis']['status']=='completed'
    assert p.calls==n+3
    assert 'cơ sở dữ liệu' in b['data']['requirements_document']['text']
    assert b['data']['teacher_requirements'][0]['document_fingerprint']==b['data']['requirements_document']['fingerprint']
    assert a['data']['summary_scope']!=b['data']['summary_scope']


def test_missing_key_returns_partial_and_authentication_instead_of_fabricating(tmp_path):
    out=bridge.run_pipeline(PDF,'',Provider.model,tmp_path)
    assert out['success'] and out['data']['analysis']['status']=='partial'
    assert out['data']['processing_errors'][0]['code']=='authentication'
    assert not out['data']['completed_work'] and not out['data']['document_content']


def test_submission_binding_prevents_other_versions_reusing_full_result(tmp_path):
    p=Provider();opts={'max_generation_calls':100}
    for version in ('V1','V2'):
        out=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,identity={'document_version_id':version},options=opts,provider_factory=lambda:p)
        assert out['data']['document_version_id']==version
    assert len(list((tmp_path/'pdf_lab').iterdir()))==2


def test_provider_error_classification_and_context_sources_are_separate():
    assert bridge.processing_error(bridge.ApiError('bad','authentication'))['code']=='authentication'
    assert bridge.processing_error(bridge.ApiError('stop','reader_missing_key_outputs'))['code']=='invalid_request'
    assert bridge.processing_error(bridge.ApiError('pause','reader_call_budget'))['code']=='budget_exhausted'
    doc=bridge.context_document({'submission_requirements':'Nộp sơ đồ hoạt động.','require_source_code':True})
    assert 'Yêu cầu nộp mã nguồn' in doc['text']

def test_progress_file_write_failure_does_not_abort_the_summary(tmp_path,monkeypatch):
    progress=tmp_path/'progress.json';monkeypatch.setenv('AI_PROGRESS_FILE',str(progress))
    original=bridge.atomic_json
    def write(path,value):
        if Path(path)==progress:raise PermissionError('Reader temporarily holds the progress file')
        return original(path,value)
    monkeypatch.setattr(bridge,'atomic_json',write)
    p=Provider()
    out=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,options={'max_generation_calls':100},provider_factory=lambda:p)
    assert out['data']['analysis']['status']=='completed'
    assert list((tmp_path/'pdf_lab').rglob('summary.json'))


def test_atomic_checkpoint_retries_temporary_windows_sharing_error(tmp_path,monkeypatch):
    original=bridge.os.replace;attempts=[]
    def replace(a,b):
        attempts.append(1)
        if len(attempts)<3:raise PermissionError('Windows sharing violation')
        return original(a,b)
    monkeypatch.setattr(bridge.os,'replace',replace)
    target=tmp_path/'saved.json';bridge.atomic_json(target,{'ok':True})
    assert len(attempts)==3 and json.loads(target.read_text())=={'ok':True}

@pytest.mark.parametrize('quote', [
    '- Ti?n ?i?u ki?n: TODO (Ch?a hoàn thành, s? b? sung ? ??t n?p sau)',
    '  2. TODO: ?ang xây d?ng giao di?n ??ng nh?p.',
    '  3. TBD: Ch?a k?t n?i c? s? d? li?u xác th?c.',
    '- Ti?n ?i?u ki?n: TBD (Ch?a xác ??nh)',
    '  2. TODO: Ch?c n?ng n?p file ?ang b? l?i upload.',
    '- H??ng gi?i quy?t: TODO (S? xin ý ki?n GVHD vào tu?n sau).',
])
def test_vendored_runtime_rejects_observed_placeholder_claims(quote):
    from ai_runtime.summarizer import classify_claim
    assert classify_claim('reported_work','Đã hoàn thành chức năng.',quote)==('design',['placeholder_not_completed_work'])


def test_placeholder_guard_keeps_chapter_content_but_excludes_completed_work(tmp_path):
    class PlaceholderProvider(Provider):
        def generate(self,prompt,schema,output,tokens):
            response=super().generate(prompt,schema,output,tokens)
            if 'SOURCE_DATA:\n' in prompt:
                for item in response['data']['items']:
                    item.update(kind='reported_work',summary='TODO: cần hoàn thiện chức năng.')
            return response
    p=PlaceholderProvider()
    out=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,options={'max_generation_calls':100},provider_factory=lambda:p)
    assert out['data']['analysis']['status']=='completed'
    assert not out['data']['completed_work']
    assert out['data']['chapters'] and out['data']['document_content']


def test_engine_upgrade_separates_disk_cache_from_previous_guard(tmp_path,monkeypatch):
    p=Provider();options={'max_generation_calls':100}
    current=bridge.ENGINE_VERSION
    assert current=='pdf-lab-v1.3'
    monkeypatch.setattr(bridge,'ENGINE_VERSION','pdf-lab-v1.2')
    before=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,options=options,provider_factory=lambda:p)
    calls=p.calls
    monkeypatch.setattr(bridge,'ENGINE_VERSION',current)
    after=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,options=options,provider_factory=lambda:p)
    assert p.calls>calls and len(list((tmp_path/'pdf_lab').iterdir()))==2
    assert before['data']['analysis']['engine_version']=='pdf-lab-v1.2'
    assert after['data']['analysis']['engine_version']==current



def test_project_budget_pause_message_explains_automatic_resume():
    for code in ('call_budget','reader_call_budget','worker_budget'):
        detail=bridge.processing_error(bridge.ApiError('Bấm tiếp tục để dùng kết quả đã có.',code))
        assert detail['code']=='budget_exhausted'
        assert 'tự tiếp tục' in detail['message'] and 'Bấm tiếp tục' not in detail['message']


def test_resumed_progress_never_resets_saved_map_count_to_zero(tmp_path,monkeypatch):
    p=Provider();options={'max_generation_calls':1}
    first=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,options=options,provider_factory=lambda:p)
    saved=first['data']['analysis']['analyzed_chunks']
    assert saved>0
    progress=tmp_path/'visible-progress.json';monkeypatch.setenv('AI_PROGRESS_FILE',str(progress))
    original=bridge.atomic_json;seen=[]
    def capture(path,value):
        if Path(path)==progress:seen.append(value['map_done'])
        return original(path,value)
    monkeypatch.setattr(bridge,'atomic_json',capture)
    bridge.run_pipeline(PDF,'fake',p.model,tmp_path,options=options,provider_factory=lambda:p)
    assert seen and min(seen)>=saved


def test_old_atomic_cache_still_runs_new_support_audit(tmp_path):
    p=Provider();opts={'max_generation_calls':100}
    first=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,context={'submission_requirements':'Mô tả quy trình của hệ thống.'},options=opts,provider_factory=lambda:p)
    assert first['data']['analysis']['status']=='completed'
    path=next((tmp_path/'pdf_lab').rglob('requirement_links.json'))
    state=json.loads(path.read_text(encoding='utf-8'));state['report'].pop('support_review')
    # Simulate a completed older cache that has no support-audit checkpoint.
    state['tasks']={}
    path.write_text(json.dumps(state),encoding='utf-8');before=p.calls
    result=bridge.run_pipeline(PDF,'fake',p.model,tmp_path,context={'submission_requirements':'Mô tả quy trình của hệ thống.'},options=opts,provider_factory=lambda:p)
    assert result['data']['analysis']['status']=='completed' and p.calls>before
    assert json.loads(path.read_text(encoding='utf-8'))['report']['support_review']=='complete'
