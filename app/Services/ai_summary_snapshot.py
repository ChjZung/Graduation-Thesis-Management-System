"""Permanent first-verified summary per immutable source/configuration, without job ownership."""
import copy
import hashlib
import json
import os
import tempfile
from ai_processing import install_json_first

REVISION = 'section-v6.1-layout-v6b'


def snapshot_path(session, source_hash, model, settings):
    if not session.cache_dir:
        return None
    key = hashlib.sha256(json.dumps([REVISION, source_hash, model, settings], sort_keys=True).encode()).hexdigest()
    return session.cache_dir / ('canonical-summary-' + key + '.json')


def read_snapshot(path, source_hash):
    if not path:
        return None
    try:
        result = json.loads(path.read_text(encoding='utf-8'))
        data = result['data']
        if (result.get('success') is True and data.get('schema_version') == 6
                and data['source_identity']['source_hash'] == source_hash
                and data['analysis'].get('text_complete') is True
                and data['analysis'].get('completion_audit_completed') is True
                and isinstance(data.get('chapters'), list) and isinstance(data.get('completed_work'), list)):
            return result
    except (OSError, ValueError, TypeError, KeyError):
        pass
    try: path.unlink(missing_ok=True)  # Invalid private cache only; submission history is separate.
    except OSError: pass
    return None


def bind_snapshot(result, identity, source_hash, reused=True):
    result = copy.deepcopy(result)
    data = result['data']
    data['document_id'] = identity.get('document_id') or source_hash
    data['document_version_id'] = identity.get('document_version_id') or source_hash
    data['source_identity'] = dict(identity, source_hash=source_hash)
    data['analysis']['canonical_reused'] = reused
    result.setdefault('metrics', {})['canonical_cache_hit'] = reused
    return result


def save_snapshot(path, result, session, source_hash):
    """No last-writer-wins overwrite: every concurrent caller adopts the same winner."""
    if not path:
        return result
    temporary = None
    try:
        path.parent.mkdir(parents=True, exist_ok=True)
        saved = copy.deepcopy(result)
        # A shared source snapshot contains no group, topic, semester or submission IDs.
        saved['data']['source_identity'] = {'source_hash':source_hash}
        saved['data']['document_id'] = source_hash
        saved['data']['document_version_id'] = source_hash
        winner = install_json_first(path, saved)
        return result if winner is saved else winner
    except OSError:
        session.checkpoint_failed = True
        return result
    finally:
        if temporary and os.path.exists(temporary):
            try: os.unlink(temporary)
            except OSError: pass
