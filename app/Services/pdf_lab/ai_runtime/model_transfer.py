"""Explicit transfer of checked Map outputs to a new execution model.

Model transfer never calls an API. It preserves declared producer models, clears
Reduce/model metadata, and reuses only revalidated Map outputs for the same plan.
"""
import re
from .provider import ApiError
from .checkpoints import restore_map_checkpoint
from .summarizer import VERSION, MAP_SCHEMA, RunSettings, fingerprint, map_prompt, run_summary as _run_summary


def transfer_map_checkpoint(exported, result, plan, target_model, settings=None):
    settings = settings or RunSettings()
    if not isinstance(target_model, str) or not re.fullmatch(r'[A-Za-z0-9][A-Za-z0-9._-]{0,120}', target_model):
        raise ApiError('Model đích không hợp lệ.', 'invalid_model')
    source_model = exported.get('model') if isinstance(exported, dict) else None
    # Verify the original identity with its original model BEFORE changing cache keys.
    state = restore_map_checkpoint(exported, result, plan, source_model, settings)
    new_identity = fingerprint([VERSION, plan['fingerprint'], target_model, settings.input_budget, settings.max_output_tokens])
    by_id = {b['batch_id']: b for b in plan['batches']}
    producers, tasks = {}, {}
    for bid, mapped in state['report']['map_results'].items():
        previous = (exported.get('map_producer_models') or {}).get(bid, source_model)
        if not isinstance(previous, str) or not re.fullmatch(r'[A-Za-z0-9][A-Za-z0-9._-]{0,120}', previous):
            raise ApiError('Thông tin model của Map không hợp lệ.', 'invalid_checkpoint')
        producers[bid] = previous
        key = fingerprint([new_identity, map_prompt(by_id[bid]), MAP_SCHEMA])
        tasks[key] = {'label': 'Map ' + bid, 'validated': mapped, 'restored_from_export': True, 'producer_model': previous}
    state.update(identity=new_identity, tasks=tasks, map_producer_models=producers,
                 model_transfers=[{'from_model': source_model, 'to_model': target_model, 'reused_map_batches': list(producers)}])
    state['report'].update(identity=new_identity, model=target_model, map_producer_models=dict(producers),
                           model_transfers=list(state['model_transfers']), mixed_model_run=any(m != target_model for m in producers.values()))
    state['report']['error'] = {'code': 'model_transferred', 'message': f'Đã giữ {len(producers)} gói Map; phần còn lại và Reduce sẽ dùng {target_model}. Chưa gọi API khi đổi model.'}
    return state


def annotate_model_provenance(state, report, plan, settings, model):
    known = state.setdefault('map_producer_models', {})
    by_id = {b['batch_id']: b for b in plan['batches']}
    for bid in report['completed_batch_ids']:
        key = fingerprint([state['identity'], map_prompt(by_id[bid]), MAP_SCHEMA])
        entry = state.get('tasks', {}).get(key, {})
        known[bid] = entry.get('producer_model', known.get(bid, model))
        entry['producer_model'] = known[bid]
    report['map_producer_models'] = {b: known[b] for b in report['completed_batch_ids']}
    report['mixed_model_run'] = any(m != model for m in report['map_producer_models'].values())
    report['model_transfers'] = state.get('model_transfers', [])
    report['generation_provenance'] = [{'task': t['label'], 'model': t.get('producer_model', model),
                                      'origin': 'restored' if t.get('restored_from_export') else 'generated'}
                                     for t in state.get('tasks', {}).values() if 'validated' in t]
    return report


def run_summary_with_provenance(result, plan, provider, checkpoint=None, settings=None, **kwargs):
    state = checkpoint if checkpoint is not None else {}
    settings = settings or RunSettings()
    report = _run_summary(result, plan, provider, state, settings, **kwargs)
    return annotate_model_provenance(state, report, plan, settings, provider.model)
