"""Canonical source: all extracted lines retained, with half-open offsets [start,end)."""
import hashlib
import re
from collections import Counter, defaultdict
from pathlib import Path
import pymupdf as fitz
from .schemas import Settings, SegmentationError


def source_bytes(source, settings=None):
    settings = settings or Settings()
    if isinstance(source, (str, Path)):
        path = Path(source)
        if not path.is_file():
            raise SegmentationError('Không tìm thấy file PDF.', 'missing_file')
        if path.stat().st_size > settings.max_upload_mb * 1024**2:
            raise SegmentationError('File vượt giới hạn dung lượng.', 'file_too_large')
        data = path.read_bytes()
    elif isinstance(source, (bytes, bytearray)):
        data = bytes(source)
    else:
        raise SegmentationError('Đầu vào phải là đường dẫn hoặc bytes PDF.', 'invalid_input')
    if len(data) > settings.max_upload_mb * 1024**2:
        raise SegmentationError('File vượt giới hạn dung lượng.', 'file_too_large')
    if b'%PDF-' not in data[:1024]:
        raise SegmentationError('File không có định dạng PDF hợp lệ.', 'invalid_pdf')
    return data


def extract_pdf(source, settings=None):
    settings = settings or Settings()
    data = source_bytes(source, settings)
    try:
        doc = fitz.open(stream=data, filetype='pdf')
    except Exception as exc:
        raise SegmentationError('PDF lỗi hoặc không mở được.', 'invalid_pdf') from exc
    with doc:
        if doc.needs_pass:
            raise SegmentationError('PDF có mật khẩu. Hãy mở khóa bản dùng để test.', 'encrypted_pdf')
        if not doc.page_count or doc.page_count > settings.max_pages:
            raise SegmentationError('Số trang rỗng hoặc vượt giới hạn cấu hình.', 'page_limit')
        pages, lines, blocks, parts = [], [], [], []
        offset = 0
        for index, page in enumerate(doc):
            page_start = offset
            raw = page.get_text('dict', sort=True, flags=fitz.TEXTFLAGS_DICT & ~fitz.TEXT_PRESERVE_IMAGES)
            page_lines = []
            for bi, block in enumerate(raw['blocks']):
                if block.get('type') != 0:
                    continue
                block_ids = []
                for li, line in enumerate(block.get('lines', [])):
                    spans = line.get('spans', [])
                    text = ''.join(s.get('text', '') for s in spans)
                    if not text.strip():
                        continue
                    line_id = f'p{index + 1}-b{bi}-l{li}'
                    bold_flag = any((s.get('flags', 0) & 16) or 'bold' in s.get('font', '').lower() for s in spans)
                    row = {
                        'line_id': line_id, 'source_id': line_id,
                        'block_id': f'p{index + 1}-b{bi}',
                        'page_index': index, 'page': index + 1, 'page_number': index + 1,
                        'text': text,
                        'bbox': list(line['bbox']), 'font_size': max((s['size'] for s in spans), default=0),
                        'bold': bold_flag, 'is_bold': bold_flag,
                        'fonts': sorted(set(s.get('font', '') for s in spans)),
                        'spans': [{k: s.get(k) for k in ('text', 'font', 'size', 'flags', 'bbox')} for s in spans],
                        'start': offset, 'end': offset + len(text) + 1,
                        'page_width': page.rect.width, 'page_height': page.rect.height,
                    }
                    parts.append(text + '\n'); offset = row['end']
                    lines.append(row); page_lines.append(row); block_ids.append(line_id)
                if block_ids:
                    blocks.append({'block_id': f'p{index + 1}-b{bi}', 'page': index + 1,
                                   'bbox': list(block['bbox']), 'line_ids': block_ids})
            # An empty page contributes no fictional text; its physical index still exists.
            images = [{'kind': 'image', 'bbox': list(im['bbox'])} for im in page.get_image_info()]
            drawings = page.get_drawings()
            vectors = [{'kind': 'vector', 'bbox': list(item['rect'])} for item in drawings
                       if item['rect'].width > 20 and item['rect'].height > 15 and item['rect'].get_area() > 400]
            image_count = len(images)
            has_drawings = bool(drawings)
            state = 'text' if page_lines else ('scan_suspected' if image_count else 'vector_without_text' if has_drawings else 'empty')
            printed = [r['text'].strip() for r in page_lines
                       if (r['bbox'][1] < page.rect.height * .08 or r['bbox'][3] > page.rect.height * .92)
                       and re.fullmatch(r'(?:\d{1,4}|[ivxlcdmIVXLCDM]{1,8})', r['text'].strip())]
            pages.append({'page_index': index, 'page': index + 1, 'pdf_page_label': page.get_label(),
                          'printed_number_candidates': printed, 'width': page.rect.width, 'height': page.rect.height,
                          'start': page_start, 'end': offset, 'state': state, 'image_count': image_count,
                          'visual_regions': images + vectors,
                          'line_ids': [r['line_id'] for r in page_lines]})
        lines_by_page = defaultdict(list)
        for row in lines:
            lines_by_page[row['page']].append(row)
        margin_keys = Counter()
        for page in pages:
            keys = {re.sub(r'\d+', '#', r['text'].strip().casefold()) for r in lines_by_page[page['page']]
                    if (r['bbox'][1] < r['page_height'] * .07
                                                     or r['bbox'][3] > r['page_height'] * .93)}
            margin_keys.update(keys)
        for row in lines:
            margin = row['bbox'][1] < row['page_height'] * .07 or row['bbox'][3] > row['page_height'] * .93
            key = re.sub(r'\d+', '#', row['text'].strip().casefold())
            row['running_margin'] = bool(margin and len(pages) >= 3 and margin_keys[key] >= max(3, len(pages) * .5))
        text = ''.join(parts)
        for page in pages:
            page['text'] = text[page['start']:page['end']]
        return {'extraction_schema_version': 2, 'sha256': hashlib.sha256(data).hexdigest(), 'byte_size': len(data),
                'page_count': len(pages), 'pdf_metadata': doc.metadata, 'outline': doc.get_toc(),
                'pages': pages, 'lines': lines, 'blocks': blocks, 'text': text,
                'reading_order': 'PyMuPDF sort=True: approximate geometric order; inspect multi-column pages.'}
