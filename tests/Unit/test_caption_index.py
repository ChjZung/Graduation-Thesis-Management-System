import sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[2]/'app/Services/pdf_lab'))
from core.caption_index import extract_captions, section_nodes

def fixture():
    texts=['DANH MỤC HÌNH','Hình 3.1. Sơ đồ tổng quát .... 4','3.2 Thiết kế','3.2.1 Luồng chính','Hình 3.1. Sơ đồ tổng quát','đăng ký đề tài','Quy trình được mô tả tại Hình 3.1.','Bảng 3.1. Các dữ liệu','Nhóm đã xây dựng chức năng đăng ký.']
    lines=[]; offset=0
    for i,t in enumerate(texts):
        lines.append(dict(text=t,start=offset,end=offset+len(t)+1,page=1,bbox=[10,i*20,300,i*20+12],font_size=12,block_id='caption' if i in (4,5) else str(i)))
        offset+=len(t)+1
    sections=[dict(section_id='toc',section_title=texts[0],section_number=None,start=0,end=lines[2]['start'],children=[],parent_section_id=None,section_level=1,kind='front_matter',start_page=1,end_page=1),
      dict(section_id='parent',section_title=texts[2],section_number='3.2',start=lines[2]['start'],end=lines[3]['start'],children=['child'],parent_section_id='root',section_level=2,kind='section',start_page=1,end_page=1),
      dict(section_id='child',section_title=texts[3],section_number='3.2.1',start=lines[3]['start'],end=offset,children=[],parent_section_id='parent',section_level=3,kind='section',start_page=1,end_page=1)]
    return dict(document=dict(text=''.join(t+'\n' for t in texts),sha256='test',lines=lines),sections=sections)


def test_table_caption_does_not_swallow_adjacent_column_headers():
    r=fixture();r['sections']=[];r['document']['lines']=[]
    rows=[('Table 5.3 Database relationships',[100,10,350,24]),
          ('STT',[20,28,45,42]),('Table name',[100,28,180,42]),('Primary key',[220,28,300,42])]
    offset=0;text=''
    for value,box in rows:
        r['document']['lines'].append(dict(text=value,start=offset,end=offset+len(value)+1,page=1,bbox=box,font_size=12,block_id='same'))
        text+=value+'\n';offset+=len(value)+1
    r['document']['text']=text
    out=extract_captions(r)
    assert len(out['assets'])==1
    assert out['assets'][0]['raw_text']=='Table 5.3 Database relationships'
    assert r['document']['text']==text

def test_caption_index_excludes_index_and_mentions_and_keeps_offsets():
    r=fixture(); original=r['document']['text']; index=extract_captions(r)
    assert len(index['assets'])==2
    for a in index['assets']:
        assert a['section_number']=='3.2.1' and a['pdf_page_index']==0
        assert not a['content_analyzed']
        start=a['source_offsets'][0][0];end=a['source_offsets'][-1][1]
        assert original[start:end].rstrip('\n')==a['raw_text']
    assert len(index['assets'][0]['source_rects'])==2
    assert original==r['document']['text'] and index==extract_captions(r)

def test_parent_without_direct_body_aggregates_child_and_rejects_bad_offsets():
    r=fixture();text=r['document']['lines'][-1]
    e={'ok':dict(section_id='child',evidence_start=text['start'],evidence_end=text['end'],summary='Đã xây dựng chức năng.'),
       'bad':dict(section_id='child',evidence_start=-1,evidence_end=2,summary='Sai nguồn.')}
    nodes=section_nodes(r,e,lambda t,ids:dict(content=t,ids=ids),'root',extract_captions(r))
    assert len(nodes)==1 and len(nodes[0]['items'])==1
    assert nodes[0]['items']==nodes[0]['sections'][0]['items']
    assert nodes[0]['status']=='completed'

def test_missing_section_retains_caption_without_fabricating_number():
    r=fixture();r['sections']=[]
    assert all(a['section_id'] is None and a['status']=='CAPTION_AMBIGUOUS' for a in extract_captions(r)['assets'])

def test_rotated_pdf_caption_keeps_page_offset_and_searchable_region():
    import pymupdf as fitz
    from core.pdf_extractor import extract_pdf
    for rotation in (0,90,180,270):
        with fitz.open() as doc:
            page=doc.new_page();page.insert_text((72,90),'Figure 3.1. System architecture')
            page.set_rotation(rotation)
            source=extract_pdf(doc.tobytes())
            index=extract_captions(dict(document=source,sections=[]))
            assert len(index['assets'])==1
            a=index['assets'][0]
            assert a['page']==1 and a['pdf_page_index']==0
            assert page.search_for(a['raw_text'])
            assert a['source_rects'][0]==source['lines'][0]['bbox']
