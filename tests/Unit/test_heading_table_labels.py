import sys
from pathlib import Path
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services/pdf_lab'))
import pymupdf as fitz
from core import segment_pdf

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
