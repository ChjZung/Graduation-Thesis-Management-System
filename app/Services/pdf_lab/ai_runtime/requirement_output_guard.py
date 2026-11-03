"""Conservative output guard over saved evidence; never retrieves or calls a model."""
from copy import deepcopy
from .requirement_relevance import relevant, families, VERSION
from .requirement_cross_matcher import find_matching_captions, tokenize

OUTPUT_GUARD_VERSION='review-output-guard-1.3'


def guard_direct_report(report):
    """Downgrade cached verifier results too, so raw review and UI cannot disagree."""
    report=deepcopy(report);evidence={e['evidence_id']:e for e in report.get('evidence',[])}
    for item in report.get('results',[]):
        request=item.get('original_label','')
        if not families(request):continue
        changed=False
        for check in item.get('checks',[]):
            before=check.get('evidence_ids',[])
            ids=[eid for eid in before if eid in evidence and relevant(request,evidence[eid].get('quote',''))]
            manual=check.get('manual_evidence',[])
            kept=[e for e in manual if relevant(request,e.get('quote',''))]
            if ids!=before or len(kept)!=len(manual):
                changed=True;check['evidence_ids']=ids;check['manual_evidence']=kept
                check['status']='PARTIAL' if ids else ('NEEDS_VISUAL_REVIEW' if kept else 'NOT_FOUND')
                check['finding']='Nguồn sai loại nội dung/chức năng đã bị loại; cần kiểm tra thêm.'
                check['reason']='Conservative output scope guard'
        if changed:
            item['status']='PARTIAL' if any(c.get('evidence_ids') for c in item.get('checks',[])) else 'NOT_FOUND'
            item['short_answer']='Chỉ sử dụng những nguồn còn phù hợp; các phần khác cần giảng viên kiểm tra trong PDF gốc.'
    used={eid for item in report.get('results',[]) for c in item.get('checks',[]) for eid in c.get('evidence_ids',[])}
    report['evidence']=[e for e in report.get('evidence',[]) if e['evidence_id'] in used]
    report['presentation_guard_version']=OUTPUT_GUARD_VERSION
    return report


def guard_teacher_items(items, caption_index):
    output=deepcopy(items);rejected=0
    for item in output:
        request=item.get('text','');points=item.get('report_points',[])
        # A type/subject gate is meaningful for known evidence domains only.
        # Unknown natural-language requests remain governed by the direct verifier.
        scoped=bool(families(request));seen=set()
        for point in points:
            removed=False
            for field in ('source_references','manual_references'):
                refs=[]
                for ref in point.get(field,[]):
                    if scoped and not relevant(request,ref.get('quote','')):
                        rejected+=1;removed=True;continue
                    refs.append(ref);seen.add((ref.get('page'),ref.get('quote','').strip()))
                point[field]=refs
            if removed:
                point['explanation']='Một số nguồn không đúng loại nội dung hoặc chức năng đã được loại. Phần còn lại cần giảng viên kiểm tra.'
                point['check_status']='PARTIAL' if point['source_references'] else 'INSUFFICIENT_EVIDENCE'
                point['relation']='related_text' if point['source_references'] else 'not_found'
            # Caption pointers can never inherit a textual SUPPORTS verdict.
            for ref in point['source_references']:
                if ref.get('source_type') in ('CAPTION_REFERENCE','figure_caption','table_caption'):
                    ref['source_type']='CAPTION_REFERENCE';ref['semantic_support']='NEEDS_VISUAL_REVIEW'
                    point['check_status']='NEEDS_VISUAL_REVIEW'
            # A located manual caption remains visible as a pointer, never proof.
            existing={(r.get('page'),r.get('quote','').strip()) for r in point['source_references']}
            for ref in point['manual_references']:
                ref['source_type']='CAPTION_REFERENCE';ref['semantic_support']='NEEDS_VISUAL_REVIEW'
                key=(ref.get('page'),ref.get('quote','').strip())
                if key not in existing:
                    point['source_references'].append(deepcopy(ref));existing.add(key)
            if point['source_references'] and all(r.get('source_type')=='CAPTION_REFERENCE' for r in point['source_references']):
                point['relation']='related_text';point['check_status']='NEEDS_VISUAL_REVIEW'
                point['explanation']='Đã tìm thấy tên hình/bảng liên quan. Giảng viên cần kiểm chứng nội dung trong PDF gốc.'
        for cap in find_matching_captions(tokenize(request),request,caption_index) if scoped else []:
            key=(cap.get('page'),cap.get('quote','').strip())
            if key in seen:continue
            seen.add(key)
            points.append({'title':request,'relation':'related_text',
                'explanation':'Đã tìm thấy tên hình/bảng liên quan. Chỉ đối chiếu tên; giảng viên cần mở PDF để kiểm tra nội dung.',
                'source_references':[cap],'manual_references':[cap], 'check_status':'NEEDS_VISUAL_REVIEW'})
        item['report_points']=points
        item['report_matches']=[] if scoped else item.get('report_matches',[])
        if scoped:
            item['report_relation']='related_text' if any(p.get('source_references') or p.get('manual_references') for p in points) else 'not_found'
            pointers=[r for p in points for r in p.get('source_references',[]) if r.get('source_type')=='CAPTION_REFERENCE']
            text_findings=[p.get('explanation','') for p in points if any(r.get('source_type')!='CAPTION_REFERENCE' for r in p.get('source_references',[]))]
            if text_findings:
                item['report_explanation']=' '.join(dict.fromkeys(text_findings))[:600]
            elif pointers:
                names=list(dict.fromkeys(r['quote'] for r in pointers))
                item['report_explanation']='Tìm thấy tên hình/bảng liên quan: '+'; '.join(names[:2])+'. Chỉ đối chiếu tên; giảng viên cần kiểm chứng nội dung trong PDF gốc.'
            else:
                item['report_explanation']='Chưa tìm thấy dẫn chứng phù hợp. Giảng viên vui lòng kiểm tra PDF gốc; không kết luận sinh viên chưa thực hiện.'
    return output,{'version':OUTPUT_GUARD_VERSION,'matcher_version':VERSION,'rejected_references':rejected}
