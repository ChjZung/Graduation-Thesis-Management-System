import hashlib
import json
import time
from .schemas import Settings
from .pdf_extractor import extract_pdf
from .heading_detector import detect_headings
from .section_tree import build_sections
from .token_chunker import chunk_sections, LexicalCounter
from .validators import validate
from .section_analysis import annotate_sections

VERSION = '1.3.0'


def segment_pdf(source, settings=None, on_progress=None, extracted=None):
    """Accept path or PDF bytes. No network, DB, UI imports or automatic file writes.

    `extracted` may be the extraction dict from an earlier run of the SAME source.
    UI uses it only after checking the file SHA256. Other callers should omit it.
    """
    settings = settings if isinstance(settings, Settings) else Settings(**(settings or {}))
    start = time.perf_counter()
    def progress(message, value):
        if on_progress:
            on_progress(message, value)
    progress('Trích xuất văn bản, font và vị trí nguồn', .1)
    document = extracted if extracted is not None else extract_pdf(source, settings)
    if extracted is not None:
        from .pdf_extractor import source_bytes
        data = source_bytes(source, settings)
        if hashlib.sha256(data).hexdigest() != document['sha256'] or document['page_count'] > settings.max_pages:
            from .schemas import SegmentationError
            raise SegmentationError('Cache trích xuất không khớp tài liệu/cấu hình.', 'cache_mismatch')
        if document.get('extraction_schema_version', 0) < 2:
            document = extract_pdf(source, settings)
    progress('Nhận diện tiêu đề và phân biệt mục lục', .35)
    detection = detect_headings(document, settings)
    progress('Dựng cây và gán độc quyền từng đoạn nguồn', .55)
    sections = build_sections(document, detection)
    progress('Chia chunk theo cấu trúc và token ước tính', .7)
    chunks = chunk_sections(document, sections, settings)
    annotate_sections(document, sections, detection)
    by_section = {s['section_id']: s for s in sections}
    for chunk in chunks:
        s = by_section[chunk['section_id']]
        chunk.update(section_type=s['section_type'], section_subtype=s.get('section_subtype'),
                     include_in_llm=s['include_in_llm'], include_in_map=s.get('include_in_map', s['include_in_llm']),
                     section_extraction_status=s['extraction_status'],
                     visual_content_detected=s.get('visual_content_detected', False),
                     requires_manual_review=s.get('requires_manual_review', False))
    progress('Kiểm tra độ bao phủ, thứ tự và overlap', .9)
    validation = validate(document, sections, chunks, settings, detection)
    counter = LexicalCounter()
    counts = [c['token_count'] for c in chunks]
    metrics = {'pages': document['page_count'], 'chapters': sum(s['kind'] == 'chapter' for s in sections),
               'sections': sum(s['section_level'] == 2 for s in sections),
               'subsections': sum(s['section_level'] >= 3 for s in sections), 'chunks': len(chunks),
               'estimated_tokens': counter.count(document['text']),
               'mean_chunk_tokens': round(sum(counts) / len(counts), 1) if counts else 0,
               'max_chunk_tokens': max(counts, default=0),
               'short_chunks': sum(n < settings.target_tokens * .5 for n in counts),
               'oversize_chunks': sum(n > settings.max_tokens for n in counts),
               'heading_warnings': sum(h['warning'] for h in detection['accepted']),
               'fallback_sections': sum(s['structure_fallback'] for s in sections),
               'coverage_percent': validation['coverage_percent'],
               'elapsed_seconds': round(time.perf_counter() - start, 3)}
    config = settings.to_dict()
    fingerprint = hashlib.sha256((document['sha256'] + json.dumps(config, sort_keys=True) + VERSION).encode()).hexdigest()
    result = {'schema_version': VERSION, 'fingerprint': fingerprint,
              'document': document, 'extraction': document,
              'heading_detection': detection, 'sections': sections,
              'root_section_ids': [s['section_id'] for s in sections if not s['parent_section_id']],
              'chapters': [s['section_id'] for s in sections if s['kind'] == 'chapter'],
              'chunks': chunks, 'validation': validation,
              'metadata': {'settings': config, 'tokenizer': counter.name, 'token_count_is_estimate': True,
                           'offset_convention': 'Unicode codepoints, half-open [start,end), canonical document.text',
                           'metrics': metrics},
              'analysis_metadata': {'settings': config, 'tokenizer': counter.name,
                                    'token_count_is_estimate': True, 'metrics': metrics}}
    progress('Hoàn tất', 1.)
    return result
