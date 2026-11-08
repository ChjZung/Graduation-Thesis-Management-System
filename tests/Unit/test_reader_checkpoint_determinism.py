"""Task keys must survive Python hash randomization between Queue passes."""
import os
import subprocess
import sys
from pathlib import Path

SCRIPT=r'''
import sys,json,hashlib
from pathlib import Path
sys.path.insert(0,sys.argv[1])
sys.path.insert(0,str(Path(sys.argv[1]).parent))
from ai_runtime.instructor import run_reader,requirement_document
from ai_runtime.summarizer import RunSettings
quote='Đã xây dựng chức năng chính như đăng nhập, quản lý đề tài và theo dõi tiến độ.'
items=[dict(item_id='E'+str(i),root_id='C',summary=quote,evidence_quote=quote,kind='reported_work',quote_status='MATCH') for i in range(10)]
base=dict(status='complete',identity='base',document_sha256='doc',plan_fingerprint='plan',selected_batch_ids=['B'],map_results={'B':{'processed_source_ids':['S'],'items':items}})
plan=dict(fingerprint='plan',source_fingerprint='source',local_integrity_pass=True,batches=[dict(batch_id='B',source_ids=['S'],root_id='C',chapter_title='Chapter')])
class P:
    model='fixture';http_calls=[]
    def __init__(self):self.prompts=[]
    def model_info(self):return dict(inputTokenLimit=100000,outputTokenLimit=20000,supportedGenerationMethods=['generateContent'])
    def count_tokens(self,*args):return 100
    def generate(self,prompt,schema,*args):
        self.prompts.append(prompt)
        ids=schema['properties']['paragraphs']['items']['properties']['evidence_ids']['items']['enum']
        text=('Nội dung '*200+quote) if len(self.prompts)==1 else (
            'Báo cáo ghi nhận nhóm đã triển khai ba chức năng gồm đăng nhập, quản lý đề tài và theo dõi tiến độ.')
        return dict(data={'paragraphs':[dict(text=text,evidence_ids=ids)]},usage={})
p=P();out=run_reader({'document':{'sha256':'doc'},'fingerprint':'source'},plan,base,requirement_document(''),p,{},RunSettings(8500,8192,5))
assert out['status']=='complete',out.get('error')
assert not out['reading_errors'],out['reading_errors']
assert len(p.prompts)==2
print(hashlib.sha256(p.prompts[1].encode()).hexdigest())
'''


def test_compaction_prompt_cache_key_is_stable_across_worker_processes():
    lib=Path(__file__).resolve().parents[2]/'app/Services/pdf_lab'
    keys=[]
    for seed in ('1','2','3'):
        env=dict(os.environ,PYTHONHASHSEED=seed,PYTHONIOENCODING='utf-8')
        keys.append(subprocess.check_output([sys.executable,'-c',SCRIPT,str(lib)],env=env,text=True).strip())
    assert len(set(keys))==1,'Identical compaction input creates different checkpoint keys in new workers'
