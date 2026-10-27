"""Document-derived coverage targets, distinct from proof that content is correct."""
import re
from difflib import SequenceMatcher
from .source_index import normalized,section_at

VERSION='document-coverage-1.3'


def fold(text):return normalized(text).replace(' ','')


def title_name(text):
    text=re.sub(r'^\d+(?:\.\d+)*\.?\s*','',text).strip()
    return re.sub(r'^(?:Đặc tả\s+)?Use[- ]?Case\s*|^Quy trình\s*','',text,flags=re.I).strip()


def ranges_for(index,section):
    start=section['char_start']
    end=next((s['char_start'] for s in index['sections'] if s['char_start']>start and s['level']<=section['level']),len(index['canonical']['text']))
    return {'start':start,'end':end}


def inventory(index):
    out={'usecases':[],'workflows':[],'role_overviews':[],'design_figures':[]}
    for section in index.get('sections',[]):
        title=fold(section['title']);name=title_name(section['title']);entry={'title':section['title'],'name':name,'ranges':[ranges_for(index,section)]}
        if re.search(r'(?:dacta)?usecase',title) and not any(x in title for x in ['sodousecase','luocdousecase']):
            # A named leaf, not a container such as "Đặc tả Use-Case hệ thống".
            body=title_name(section['title']);children=[s for s in index['sections'] if entry['ranges'][0]['start']<s['char_start']<entry['ranges'][0]['end']]
            if body and fold(body) not in ('hethong','dacta','dactausecasehethong') and not children:out['usecases'].append(entry)
        if fold(re.sub(r'^\d+(?:\.\d+)*\.?\s*','',section['title'])).startswith('quytrinh') and not any('thunghiem' in fold(t) for t in section.get('path',[])):
            if any('nghiepvu' in fold(t) for t in section.get('path',[])):
                out['workflows'].append(entry)
        if any(x in title for x in ['doituong','phamvi','nhomnguoidung']) and not any(x in title for x in ['dacta','danhgia','thunghiem']):
            span=entry['ranges'][0];body=index['canonical']['text'][span['start']:span['end']]
            if re.search(r'nhóm người dùng|đối tượng khảo sát|đối với|vai trò người dùng',body,re.I):out['role_overviews'].append(entry)
    families={}
    for flow in out['workflows']:
        number=re.match(r'^(\d+(?:\.\d+)+)',flow['title'])
        key=number.group(1).rsplit('.',1)[0] if number else flow['title']
        families.setdefault(key,[]).append(flow)
    if families:
        def priority(group):
            paths=' '.join(' '.join(section_at(index,f['ranges'][0]['start']).get('path',[])) for f in group)
            return (int('mohinhhoa' in fold(paths)),len(group),group[0]['ranges'][0]['start'])
        out['workflows']=max(families.values(),key=priority)
    for flow in out['workflows']:
        choices=[(SequenceMatcher(None,fold(flow['name']),fold(uc['name'])).ratio(),uc) for uc in out['usecases']]
        if choices:
            score,uc=max(choices,key=lambda x:x[0])
            if score>=.72:flow['ranges'].extend(uc['ranges']);flow['related_spec']=uc['name']
    seen=set()
    for line in index['canonical'].get('lines',[]):
        text=line['text'].strip()
        if not re.match(r'^(?:Hình|Figure)\s+\d',text,re.I) or re.search(r'\.{3,}',text):continue
        section=section_at(index,line['start']);context=' '.join(section.get('path',[]))
        if 'thietke' not in fold(context) or not any(x in fold(text) for x in ['sodo','erd','diagram']):continue
        if (line['page'],text) in seen:continue
        seen.add((line['page'],text));out['design_figures'].append({'title':text,'name':text,'page_number':line['page'],
            'ranges':[{'start':line['start'],'end':line['start']+len(line['text'])}]})
    return out


def is_named_request(text):
    match=re.search(r'use[- ]?case\s+([^:.,;\n]+)',text,re.I)
    if not match:return False
    rest=fold(match.group(1))
    return bool(rest) and not any(rest.startswith(x) for x in ['va','cac','cuahethong','hethong','nhung','tongquat','tongthe'])


def apply_coverage(items,index):
    catalog=inventory(index);output=[]
    for item in items:
        text=fold(item['original_text']);checks=[]
        implementation=bool(re.search(r'cai dat|lap trinh|da trien khai|da xay dung',normalized(item['original_text'])))
        wants_roles=any(x in text for x in ['cacnhomnguoidung','nhomnguoidung','doituongsudung','cacvaitro'])
        wants_business=any(x in text for x in ['nghiepvuchinh','cacquytrinh','cacluongchinh'])
        wants_specs=(any(x in text for x in ['dactacaccasudung','dactacasudung','cacdactausecase','dactacacusecase']) or (any(x in text for x in ['phantichchucnang','dactausecase','dactacacchucnang']) and not is_named_request(item['original_text']))) and not implementation
        wants_design=any(x in text for x in ['cacsodothietke','sodothietkehethong','sodohethong'])
        def add(description,kind,target,family):
            checks.append({'item_id':item['item_id'],'requirement_text':item['original_text'],'requirement_excerpt':item['original_text'],
                'description':description[:220],'kind':kind,'max_quote_chars':1800,'coverage_family':family,'coverage_target':target})
        if wants_roles and not implementation and catalog['role_overviews']:
            target={'name':'Các nhóm người dùng','ranges':[r for e in catalog['role_overviews'] for r in e['ranges']]}
            add('Các nhóm người dùng trong phần đối tượng/phạm vi hệ thống','actors',target,'roles')
        if wants_business:
            for entry in catalog['workflows'] or catalog['usecases']:add('Nghiệp vụ: '+entry['name'],'content',entry,'business')
        if wants_specs:
            for entry in catalog['usecases']:add('Đặc tả và quy tắc nghiệp vụ: '+entry['name'],'content',entry,'usecase')
            excerpt=re.search(r'đặc\s+tả[^.;\n]*',item['original_text'],re.I)
            if excerpt:
                for c in checks:
                    if c.get('coverage_family')=='usecase':c['requirement_excerpt']=excerpt.group(0)
        if wants_design:
            for entry in catalog['design_figures']:add(entry['title'],'diagram',entry,'diagram')
        # Use structured expansion only when all triggered broad categories have
        # a document inventory; otherwise keep the model plan and report limits.
        ready=(not wants_roles or bool(catalog['role_overviews'])) and (not wants_business or bool(catalog['workflows'] or catalog['usecases'])) and (not wants_specs or bool(catalog['usecases'])) and (not wants_design or bool(catalog['design_figures']))
        if checks and ready:
            # Original model criteria remain authoritative; inventory adds coverage,
            # never replaces needs, problems, solutions or proof-of-implementation.
            original=[dict(c) for c in item.get('checks',[]) if not c.get('coverage_family')]
            if wants_specs:
                original=[c for c in original if not (c.get('kind')=='content' and any(v in fold(c.get('description','')+' '+c.get('requirement_excerpt','')) for v in ['dactacaccasudung','dactausecase','dactacacusecase']))]
            role_check=next((c for c in checks if c.get('coverage_family')=='roles'),None)
            broad_actors=[c for c in original if c.get('kind')=='actors'] if not implementation else []
            if role_check and broad_actors:
                for c in broad_actors:c.update(coverage_family='roles',coverage_target=role_check['coverage_target'])
                checks=[c for c in checks if c is not role_check]
            checks=original+checks
            for n,check in enumerate(checks,1):check['check_id']=item['item_id']+f'_c{n:02}'
            output.append(dict(item,checks=checks,coverage={'version':VERSION,'target_count':len(checks),'basis':'Original PDF section/figure inventory; not proof of completion.'}))
        else:output.append(item)
    return output


def source_in_target(source,target):
    return any(span['start']<=source['char_start']<source['char_end']<=span['end'] for span in target['ranges'])
