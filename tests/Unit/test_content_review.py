"""Content observations do not change segmentation or infer implementation progress."""
import copy
import sys
from pathlib import Path
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services'))
import lab_pdf_summary
from ai_runtime.content_review import review_content


def fixture(rows):
    text='';lines=[];pages=[];sections=[];headings=[]
    for n,(title,body,extra) in enumerate(rows,1):
        start=len(text);hid=f'h{n}';lid=f'hline{n}'
        text+=title+'\n';lines.append({'start':start,'end':len(text),'text':title,'line_id':lid,'page':n})
        headings.append({'heading_id':hid,'line_ids':[lid]})
        if body:
            for j,value in enumerate(body.splitlines()):
                a=len(text);text+=value+'\n';lines.append({'start':a,'end':len(text),'text':value,'line_id':f'b{n}-{j}','page':n})
        pages.append({'page':n,'start':start,'end':len(text),'state':'text','text':text[start:],'image_count':0,'visual_regions':[]})
        sections.append({'section_id':f's{n}','section_title':title,'heading_id':hid,'section_type':'main_content','structure_fallback':False,'children':[],'start':start,'end':len(text),'start_page':n,'end_page':n,**extra})
    return {'document':{'text':text,'sha256':'test-hash','page_count':len(pages),'lines':lines,'pages':pages},'sections':sections,'heading_detection':{'accepted':headings}}


def test_heading_only_is_observation_not_unfinished_work_and_keeps_exact_location():
    data=fixture([('4.2 Thiết kế cơ sở dữ liệu','',{})]);before=copy.deepcopy(data)
    out=review_content(data)
    assert data==before
    assert len(out['missing_content'])==1 and out['reported_unfinished']==[]
    assert out['missing_content'][0]['status']=='heading_only'
    ref=out['missing_content'][0]['source_references'][0]
    assert data['document']['text'][ref['start']:ref['end']]==ref['quote']
    assert ref['page']==1 and ref['document_sha256']=='test-hash'


def test_container_and_front_matter_are_not_empty_chapters():
    data=fixture([('Chương 4','',{'children':['s2']}),('4.1 Thiết kế','Có nội dung thiết kế.',{}),('Mục lục','',{'section_type':'front_matter'})])
    assert review_content(data)['missing_content']==[]


def test_images_tables_and_extraction_uncertainty_are_not_marked_missing():
    data=fixture([('Sơ đồ hệ thống','',{'visual_content_detected':True}),('Sơ đồ lớp','Hình 4.1 Sơ đồ lớp',{}),('Bảng kết quả','100 200 300',{}),('Mục khó đọc','',{'reading_order_review':True})])
    out=review_content(data)
    assert out['missing_content']==[] and len(out['manual_review'])==3
    assert {r['status'] for r in out['manual_review']}=={'needs_visual_review','undetermined'}


def test_placeholder_is_explicit_but_todo_application_and_examples_are_not():
    out=review_content(fixture([('Thiết kế','TODO: bổ sung mô tả.',{}),('Ứng dụng Todo','Ứng dụng Todo quản lý các việc cần làm.',{}),('Ví dụ','Ví dụ TODO: là một nhãn trong công cụ.',{})]))
    assert len(out['missing_content'])==1 and out['missing_content'][0]['status']=='placeholder'


def test_matched_map_negative_statement_is_not_future_plan_or_usecase_condition():
    data=fixture([('Hạn chế','Nhóm chưa triển khai chức năng thông báo.',{}),('Hướng phát triển','Nhóm dự kiến bổ sung tìm kiếm.',{}),('Đặc tả Use Case','Khi sinh viên chưa hoàn thành đăng ký, hệ thống nhắc nhở.',{})])
    items=[]
    for sec in data['sections']:
        a=data['document']['text'].index('\n',sec['start'])+1;b=sec['end']-1
        items.append({'section_id':sec['section_id'],'quote_status':'MATCH','evidence_quote':data['document']['text'][a:b],'evidence_start':a,'evidence_end':b,'path':[]})
    out=review_content(data,{'map_results':{'B':{'items':items}}})
    assert len(out['reported_unfinished'])==1
    assert out['reported_unfinished'][0]['section_title']=='Hạn chế'
    items[0]['quote_status']='NOT_FOUND'
    assert review_content(data,{'map_results':{'B':{'items':items}}})['reported_unfinished']==[]


def test_empty_physical_scan_between_headings_is_manual_review():
    data=fixture([('Thiết kế','',{}),('Chương sau','Nội dung.',{})])
    data['document']['pages'][1]['page']=3;data['sections'][1]['start_page']=3;data['sections'][1]['end_page']=3
    data['document']['pages'].insert(1,{'page':2,'start':data['sections'][0]['end'],'end':data['sections'][0]['end'],'state':'scan_suspected','text':'','image_count':1,'visual_regions':[{}]})
    data['document']['page_count']=3
    out=review_content(data)
    assert not out['missing_content'] and out['manual_review'][0]['status']=='needs_visual_review'


def test_todo_in_multiline_documented_example_is_not_missing_content():
    data=fixture([('Hướng dẫn sử dụng','Ví dụ mã minh họa:\nTODO: kiểm tra dữ liệu.',{})])
    assert review_content(data)['missing_content']==[]


def test_unknown_structure_does_not_claim_no_unfinished_work():
    data=fixture([('Văn bản chưa gán tiêu đề','',{'structure_fallback':True})])
    assert review_content(data)['status']=='undetermined'


def test_optional_audit_failure_does_not_abort_the_summary():
    from ai_runtime.content_review import safe_review_content
    out=safe_review_content({'document':{'sha256':'hash'}})
    assert out['status']=='unavailable' and not out['missing_content']
