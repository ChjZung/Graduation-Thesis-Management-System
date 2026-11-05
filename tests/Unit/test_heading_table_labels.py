import sys
from pathlib import Path
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services/pdf_lab'))
import pymupdf as fitz
from core import segment_pdf

def test_report_structure_preview_is_not_a_second_set_of_chapters():
    titles = ['DATABASE DESIGN', 'PROCESSING', 'ADMINISTRATION', 'APPLICATION']
    with fitz.open() as doc:
        page = doc.new_page()
        page.insert_text((60,60),'Report structure',fontsize=14,fontname='hebo')
        for index,title in enumerate(titles):
            y=110+index*110
            page.insert_text((60,y),f'Chapter {index+1}: {title.title()}',fontsize=12,fontname='hebo')
            page.insert_text((60,y+28),'This chapter describes the corresponding work and the expected report contents.',fontsize=12)
        for index,title in enumerate(titles):
            page=doc.new_page()
            page.insert_text((60,60),f'Chapter {index+1}: {title}',fontsize=18,fontname='hebo')
            page.insert_text((60,110),f'{index+1}.1. Introduction',fontsize=14,fontname='hebo')
            page.insert_text((60,150),'The actual chapter contains the analysis, explanations and implementation details.',fontsize=12)
        result=segment_pdf(doc.tobytes())
    chapters=[s for s in result['sections'] if s['kind']=='chapter']
    assert [s['start_page'] for s in chapters]==[2,3,4,5]
    assert [s['section_number'] for s in chapters]==['1','2','3','4']
    assert result['validation']['coverage_percent']==100
    assert ''.join(s['content'] for s in result['sections'])==result['document']['text']
    assert result['document']['text'].count('Chapter 2:')==2

def test_structure_label_does_not_remove_real_chapters_in_ordinary_fonts():
    with fitz.open() as doc:
        page=doc.new_page()
        page.insert_text((60,60),'Report structure',fontsize=14,fontname='hebo')
        page.insert_text((60,100),'The report contains several chapters presenting the analysis and design work.',fontsize=12)
        for number in [1,2]:
            page=doc.new_page()
            page.insert_text((60,60),f'Chapter {number}: ACTUAL WORK',fontsize=12,fontname='hebo')
            page.insert_text((60,110),'The chapter explains actual work in detail rather than listing the report structure.',fontsize=12)
        result=segment_pdf(doc.tobytes())
    assert [s['start_page'] for s in result['sections'] if s['kind']=='chapter']==[2,3]

def test_table_conclusion_does_not_end_chapter_or_steal_numbered_sections():
    with fitz.open() as doc:
        p=doc.new_page()
        p.insert_text((60,60),'CHAPTER 6: EVALUATION',fontsize=18,fontname='hebo')
        p.insert_text((60,100),'6.6. Evaluation results',fontsize=14,fontname='hebo')
        p.insert_text((60,140),'This paragraph explains the evaluation and comparison results in the report.',fontsize=12)
        p.insert_text((60,200),'CONCLUSION',fontsize=12)
        p.insert_text((220,200),'Source comparison table content',fontsize=12)
        p=doc.new_page()
        p.insert_text((60,60),'6.7. Deployment results',fontsize=14,fontname='hebo')
        p.insert_text((60,100),'The deployment results are still part of chapter six.',fontsize=12)
        p=doc.new_page()
        p.insert_text((200,60),'CONCLUSION',fontsize=18,fontname='hebo')
        p.insert_text((60,100),'This is the actual final conclusion of the whole report.',fontsize=12)
        r=segment_pdf(doc.tobytes())
    conclusions=[s for s in r['sections'] if s['section_title']=='CONCLUSION']
    assert len(conclusions)==1 and conclusions[0]['start_page']==3
    by_id={s['section_id']:s for s in r['sections']}
    section=next(s for s in r['sections'] if s['section_number']=='6.7')
    assert by_id[section['parent_section_id']]['section_number']=='6'
    assert ''.join(s['content'] for s in r['sections'])==r['document']['text']
    assert r['document']['text'].count('CONCLUSION')==2
