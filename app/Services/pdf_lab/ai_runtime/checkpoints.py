"""Restore Map results from a user-selected export without trusting its source offsets.

Only maps are reusable: Reduce output is recomputed for the current output contract.
No credentials, arbitrary task records, model limits or prompts are imported.
"""
from .provider import ApiError
from .summarizer import (VERSION, OUTPUT_CONTRACT, MAP_SCHEMA, RunSettings, fingerprint,
                         map_prompt, validate_map, progress_snapshot)


def restore_map_checkpoint(exported, result, plan, model, settings=None):
    settings = settings or RunSettings()
    if not isinstance(exported, dict):
        raise ApiError('File kết quả phải là JSON object.', 'invalid_checkpoint')
    identity = fingerprint([VERSION, plan['fingerprint'], model, settings.input_budget, settings.max_output_tokens])
    source_version = exported.get('version')
    source_identity = fingerprint([source_version, plan['fingerprint'], model, settings.input_budget, settings.max_output_tokens])
    if (not plan.get('local_integrity_pass') or plan.get('source_fingerprint') != result['fingerprint']
            or source_version not in (VERSION, 'gemini-summary-1.2', 'gemini-summary-1.1')
            or exported.get('document_sha256') != result['document']['sha256']
            or exported.get('plan_fingerprint') != plan['fingerprint']
            or exported.get('model') != model or exported.get('identity') != source_identity):
        raise ApiError('JSON không khớp PDF/phương án/model/input/output hiện tại. Chọn đúng tài liệu và cấu hình đã chạy.', 'checkpoint_mismatch')
    batches = {b['batch_id']: b for b in plan['batches']}
    selected, saved = exported.get('selected_batch_ids'), exported.get('map_results')
    if (not isinstance(selected, list) or not selected or any(not isinstance(b, str) or b not in batches for b in selected)
            or len(selected) != len(set(selected)) or not isinstance(saved, dict)
            or any(b not in selected for b in saved)):
        raise ApiError('Phạm vi hoặc danh sách Map trong JSON không hợp lệ.', 'invalid_checkpoint')
    tasks, maps = {}, {}
    for bid in selected:
        if bid not in saved:
            continue
        value = saved[bid]
        if not isinstance(value, dict) or not isinstance(value.get('items'), list):
            raise ApiError('Dữ liệu Map trong JSON không hợp lệ.', 'invalid_checkpoint')
        raw = []
        for item in value['items']:
            if not isinstance(item, dict):
                raise ApiError('Ý nguồn trong JSON không hợp lệ.', 'invalid_checkpoint')
            raw_item = {k: item.get(k) for k in ('source_id', 'summary', 'evidence_quote', 'kind')}
            raw_item['kind'] = item.get('model_kind', item.get('kind'))
            raw.append(raw_item)
        # Recheck coverage, classification, exact quote spans and PDF page numbers.
        validated = validate_map({'processed_source_ids': value.get('processed_source_ids'), 'items': raw}, batches[bid], result['document'])
        maps[bid] = validated
        cache_key = fingerprint([identity, map_prompt(batches[bid]), MAP_SCHEMA])
        tasks[cache_key] = {'label': 'Map ' + bid, 'validated': validated, 'restored_from_export': True, 'producer_model': (exported.get('map_producer_models') or {}).get(bid, model)}
    if not maps:
        raise ApiError('JSON chưa có gói Map hoàn tất để khôi phục.', 'invalid_checkpoint')
    usage = exported.get('usage_confirmed') or {}
    if not isinstance(usage, dict):
        usage = {}
    usage = {k: v for k, v in usage.items() if k in ('promptTokenCount', 'candidatesTokenCount', 'thoughtsTokenCount', 'totalTokenCount') and type(v) is int and v >= 0}
    attempts = exported.get('generation_attempts', 0)
    attempts = attempts if type(attempts) is int and 0 <= attempts <= 1000000 else 0
    items = [i for m in maps.values() for i in m['items']]
    grouped = {}
    for item in items:
        grouped.setdefault(item['section_id'], {'section_id': item['section_id'], 'title': item['section_title'], 'path': item['path'], 'items': []})['items'].append(item)
    report = {'version': VERSION, 'output_contract': OUTPUT_CONTRACT, 'document_sha256': result['document']['sha256'],
        'plan_fingerprint': plan['fingerprint'], 'identity': identity, 'model': model, 'model_info': None,
        'settings': {'input_budget': settings.input_budget, 'max_output_tokens': settings.max_output_tokens},
        'map_producer_models': {bid: (exported.get('map_producer_models') or {}).get(bid, model) for bid in maps},
        'revalidated_from_version': source_version,
        'status': 'paused', 'error': {'code': 'restored_maps', 'message': 'Đã khôi phục Map và kiểm tra lại nguồn. Bấm tiếp tục để xử lý phần còn lại và tổng hợp kết quả cuối.'},
        'selected_batch_ids': selected, 'completed_batch_ids': list(maps), 'pending_batch_ids': [b for b in selected if b not in maps],
        'scope_complete': False, 'whole_document_scope': set(selected) == set(batches),
        'document_summary': None, 'final_result': None, 'chapter_summaries': [], 'section_summaries': list(grouped.values()),
        'map_results': maps, 'reported_work': [i for i in items if i['quote_status'] == 'MATCH' and i['kind'] in ('reported_work', 'reported_result')],
        'matched_quotes': sum(i['quote_status'] == 'MATCH' for i in items), 'unmatched_quotes': sum(i['quote_status'] != 'MATCH' for i in items),
        'classification_reviews': sum(bool(i.get('classification_flags')) for i in items),
        'generation_attempts': attempts, 'http_ledger': [], 'usage_confirmed': usage,
        'imported_map_batches': list(maps), 'progress': progress_snapshot(plan, selected, maps, []),
        'limitations': ['Đầu ra nhập lại chưa được xác thực nguồn phát sinh từ provider; câu trích và phạm vi đã kiểm tra lại bằng code.',
                        'Usage cũ lấy từ JSON nhập lại; nhật ký request chỉ ghi từ phiên mới.',
                        'Câu trích MATCH không chứng minh ngữ nghĩa; ảnh/sơ đồ chưa được đọc.']}
    return {'identity': identity, 'tasks': tasks, 'generation_attempts': attempts, 'http_ledger': [],
            'imported_usage': usage, 'imported_map_batches': list(maps), 'map_producer_models': {bid: (exported.get('map_producer_models') or {}).get(bid, model) for bid in maps}, 'report': report}

def restore_completed_checkpoint(exported, result, plan, model, settings=None):
    """Restore final output only after revalidating every Map and work reference."""
    from .summarizer import validate_completed_reduce, COMPLETED_KINDS
    state = restore_map_checkpoint(exported,result,plan,model,settings)
    maps = state['report']['map_results']
    selected = state['report']['selected_batch_ids']
    if (exported.get('status')!='complete' or exported.get('output_contract')!=OUTPUT_CONTRACT
            or set(maps)!=set(selected) or not isinstance(exported.get('final_result'),dict)):
        raise ApiError('Bản xuất chưa hoàn tất hoặc khác hợp đồng đầu ra. Chỉ khôi phục Map.', 'incomplete_final_checkpoint')
    items = {i['item_id']:i for m in maps.values() for i in m['items']}
    allowed = {iid for iid,i in items.items() if i['quote_status']=='MATCH' and i['kind'] in COMPLETED_KINDS}
    def checked_points(points, ids):
        # A theory/design-only chapter legitimately has no completed work.
        if points == [] and not ids:
            return {'points': []}
        return validate_completed_reduce({'points':points},ids,items)
    final = checked_points(exported['final_result'].get('completed_work'),allowed)
    by_batch = {b['batch_id']:b for b in plan['batches']}
    roots = list(dict.fromkeys(by_batch[b]['root_id'] for b in selected))
    saved = exported.get('chapter_summaries',[])
    if not isinstance(saved,list) or [c.get('root_id') for c in saved]!=roots:
        raise ApiError('Danh sách chương trong bản xuất không khớp phạm vi.', 'invalid_final_checkpoint')
    chapters=[]
    for c in saved:
        rid=c['root_id'];ids={iid for iid in allowed if items[iid]['root_id']==rid}
        points=checked_points(c.get('points'),ids)
        title=next(b['chapter_title'] for b in plan['batches'] if b['root_id']==rid)
        chapters.append({'root_id':rid,'title':title,**points})
    report=state['report']
    report.update(status='complete',error=None,scope_complete=True,pending_batch_ids=[],
        final_result={'completed_work':final['points']},document_summary=final,chapter_summaries=chapters,
        progress=progress_snapshot(plan,selected,maps,chapters,'complete'),restored_completed_output=True)
    return state
