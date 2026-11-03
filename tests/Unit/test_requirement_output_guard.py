import sys
from pathlib import Path
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services/pdf_lab'))
from ai_runtime.requirement_output_guard import guard_teacher_items


def test_verified_cache_cannot_bypass_type_and_module_guard_and_keeps_correct_captions():
    items=[]
    cases=[('Xây dựng sơ đồ lớp ở mức thiết kế.','Hình 3.11 Sơ đồ lớp mức phân tích',79),
           ('Thiết kế sơ đồ tuần tự theo dõi tiến độ.','Hình 4.3 Sơ đồ tuần tự lập kế hoạch học kỳ',82)]
    for req,quote,page in cases:
        items.append({'text':req,'report_points':[{'relation':'related_text','check_status':'SUPPORTED',
            'explanation':'Old verified finding','source_references':[{'page':page,'quote':quote}]}]})
    captions={'assets':[{'asset_id':'CLASS','page':81,'display_text':'Hình 4.2 Sơ đồ lớp mức thiết kế'},
                        {'asset_id':'PROGRESS','page':85,'display_text':'Hình 4.7 Sơ đồ tuần tự theo dõi tiến độ'}]}
    out,audit=guard_teacher_items(items,captions)
    assert audit['rejected_references']==2
    for item,page in zip(out,[81,85]):
        refs=[r for p in item['report_points'] for r in p['source_references']]
        assert [r['page'] for r in refs]==[page]
        assert refs[0]['semantic_support']=='NEEDS_VISUAL_REVIEW'
        assert not any(p['check_status']=='SUPPORTED' for p in item['report_points'])
    assert items[0]['report_points'][0]['source_references'][0]['page']==79


def test_missing_caption_does_not_fabricate_a_replacement():
    item={'text':'Sơ đồ lớp mức thiết kế','report_points':[{'source_references':[{'page':1,'quote':'Sơ đồ lớp mức phân tích'}]}]}
    out,audit=guard_teacher_items([item],{'assets':[]})
    assert out[0]['report_relation']=='not_found'
    assert out[0]['report_points'][0]['source_references']==[]


def test_manual_caption_stays_visible_but_never_becomes_content_proof():
    cap={'page':80,'quote':'Hình 4.1 Sơ đồ ERD'}
    item={'text':'Thiết kế CSDL và ERD','report_points':[{'relation':'not_found','source_references':[], 'manual_references':[cap]}]}
    out,_=guard_teacher_items([item],{'assets':[]})
    p=out[0]['report_points'][0]
    assert p['relation']=='related_text' and p['check_status']=='NEEDS_VISUAL_REVIEW'
    assert p['source_references'][0]['semantic_support']=='NEEDS_VISUAL_REVIEW'
    assert 'Sơ đồ ERD' in out[0]['report_explanation']


def test_raw_review_cannot_keep_a_rejected_analysis_diagram_as_supported():
    from ai_runtime.requirement_output_guard import guard_direct_report
    report={'evidence':[{'evidence_id':'wrong','quote':'Sơ đồ lớp mức phân tích'}],
        'results':[{'original_label':'Sơ đồ lớp mức thiết kế','status':'HAS_EVIDENCE','checks':[
            {'status':'SUPPORTED','evidence_ids':['wrong'],'manual_evidence':[]}]}]}
    out=guard_direct_report(report)
    assert out['results'][0]['checks'][0]['status']=='NOT_FOUND'
    assert out['evidence']==[]
    assert report['results'][0]['checks'][0]['status']=='SUPPORTED'
