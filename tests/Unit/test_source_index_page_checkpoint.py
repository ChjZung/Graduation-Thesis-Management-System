"""Per-page extraction resumes without changing source identity, offsets or coordinates."""
import json
import sys
import time
from pathlib import Path
import pytest
import pymupdf as fitz
sys.path.insert(0,str(Path(__file__).resolve().parents[2]/'app/Services/pdf_lab'))
from ai_runtime.requirement_review.source_index import build_source_index
from ai_runtime.provider import ApiError


def pdf_bytes():
    with fitz.open() as doc:
        for n in range(3):
            page=doc.new_page();page.insert_text((72,72),f'Chapter {n+1}. Report text with exact source positions and evidence.')
        return doc.tobytes()


def test_interrupted_index_resumes_without_reprocessing_completed_page(tmp_path,monkeypatch):
    data=pdf_bytes();baseline=build_source_index(data,document_id='D',version_id='V')
    calls=[];original=fitz.Page.find_tables
    def counted(page,*args,**kwargs):
        calls.append(page.number);return original(page,*args,**kwargs)
    monkeypatch.setattr(fitz.Page,'find_tables',counted)
    def interrupt(label):
        if '2/3' in label:raise ApiError('Stop after page 1','worker_budget')
    with pytest.raises(ApiError):build_source_index(data,'D','V',interrupt,page_checkpoint_dir=tmp_path)
    assert calls==[0]
    assert len(list(tmp_path.glob('page_*.json')))==1
    result=build_source_index(data,'D','V',page_checkpoint_dir=tmp_path)
    assert calls==[0,1,2]
    assert result['statistics']['pages_reused']==1
    for field in ('document','canonical','sources','tables','warnings','sections'):
        assert result[field]==baseline[field]
    calls.clear()
    cached=build_source_index(data,'D','V',page_checkpoint_dir=tmp_path)
    assert calls==[] and cached['statistics']['pages_reused']==3


def test_page_cache_cannot_cross_document_version(tmp_path):
    data=pdf_bytes();build_source_index(data,'D','V1',page_checkpoint_dir=tmp_path)
    other=build_source_index(data,'D','V2',page_checkpoint_dir=tmp_path)
    assert other['statistics']['pages_reused']==0
    assert all(s['document_version_id']=='V2' for s in other['sources'].values())


def test_corrupt_cache_rebuilds_only_affected_page(tmp_path):
    data=pdf_bytes();build_source_index(data,'D','V',page_checkpoint_dir=tmp_path)
    (tmp_path/'page_00002.json').write_text('[]',encoding='utf-8')
    result=build_source_index(data,'D','V',page_checkpoint_dir=tmp_path)
    assert result['statistics']['pages_reused']==2


def test_deadline_stops_before_expensive_page_and_keeps_prior_checkpoint(tmp_path):
    data=pdf_bytes()
    def interrupt(label):
        if '2/3' in label:raise ApiError('stop','worker_budget')
    with pytest.raises(ApiError):build_source_index(data,'D','V',interrupt,page_checkpoint_dir=tmp_path)
    with pytest.raises(ApiError) as error:
        build_source_index(data,'D','V',page_checkpoint_dir=tmp_path,deadline=time.monotonic()-1)
    assert error.value.code=='worker_budget'
    assert len(list(tmp_path.glob('page_*.json')))==1
    assert build_source_index(data,'D','V',page_checkpoint_dir=tmp_path)['statistics']['pages_reused']==1
