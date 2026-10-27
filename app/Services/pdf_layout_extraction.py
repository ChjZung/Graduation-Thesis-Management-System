"""Validated PDF extraction with physical-page layout signals and optional local OCR."""
import os
import re
import shutil
import time


def extract_layout(path, fitz, max_bytes=50 * 1024 * 1024, max_pages=1000):
    if os.path.getsize(path) > max_bytes:
        raise ValueError('pdf_size_limit')
    with open(path, 'rb') as stream:
        if b'%PDF-' not in stream.read(1024):
            raise ValueError('invalid_pdf_signature')
    started = time.monotonic()
    pages, ocr_pages, ocr_failed = [], [], []
    ocr_available = bool(shutil.which('tesseract') and os.environ.get('TESSDATA_PREFIX'))
    with fitz.open(path) as doc:
        if doc.needs_pass:
            raise ValueError('pdf_password_required')
        if not doc.is_pdf or not 0 < len(doc) <= max_pages:
            raise ValueError('pdf_page_limit_or_corrupt')
        outline = doc.get_toc()
        for index, page in enumerate(doc):
            if time.monotonic() - started > 90:
                raise TimeoutError('pdf_extraction_timeout')
            data = page.get_text('dict', sort=True)
            is_scan = len(page.get_text().strip()) < 40
            if is_scan and ocr_available and len(ocr_pages) + len(ocr_failed) < 10:
                try:
                    textpage = page.get_textpage_ocr(language='vie+eng', dpi=150, full=True)
                    data = page.get_text('dict', textpage=textpage, sort=True)
                    ocr_pages.append(index + 1)
                except Exception:
                    ocr_failed.append(index + 1)
            lines = []
            for block_index, block in enumerate(data.get('blocks', [])):
                for line_index, line in enumerate(block.get('lines', [])):
                    spans = line.get('spans', [])
                    text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', ''.join(s['text'] for s in spans))
                    box = list(line['bbox'])
                    lines.append({'text': text, 'bbox': box, 'block_index': block_index,
                                  'line_index': line_index, 'size': max((s['size'] for s in spans), default=0),
                                  'bold': any(s.get('flags', 0) & 16 or 'bold' in s.get('font', '').lower() for s in spans),
                                  'fonts': sorted({s.get('font', '') for s in spans}),
                                  'marginal': box[3] < page.rect.height * .08 or box[1] > page.rect.height * .92})
            text = '\n'.join(l['text'] for l in lines)
            image_area = sum((b['bbox'][2] - b['bbox'][0]) * (b['bbox'][3] - b['bbox'][1])
                             for b in data.get('blocks', []) if b.get('type') == 1)
            drawings = page.get_drawings()
            complex_vector = len([d for d in drawings if len(d.get('items', [])) > 2]) >= 8
            unread_scan = len(text.strip()) < 40
            pages.append({'page': index + 1, 'source_kind': 'pdf', 'source_label': f'Trang {index + 1}',
                          'text': text, 'lines': lines, 'page_size': [page.rect.width, page.rect.height],
                          'char_count': len(text), 'word_count': len(text.split()),
                          'outline': [{'level': level, 'title': title} for level, title, n in outline if n == index + 1],
                          'headings': [l['text'] for l in lines if l['bold']],
                          'ocr': index + 1 in ocr_pages, 'scan_unread': unread_scan,
                          'visual_unread': unread_scan or complex_vector or image_area > page.rect.width * page.rect.height * .08,
                          'complex_vector_graphics': complex_vector})
    return pages, len(pages), {'total_chars': sum(p['char_count'] for p in pages),
                               'total_words': sum(p['word_count'] for p in pages),
                               'valid_pages': sum(len(p['text'].strip()) >= 40 for p in pages),
                               'extraction_time': round(time.monotonic() - started, 3),
                               'ocr_available': ocr_available, 'ocr_pages': ocr_pages,
                               'ocr_failed_pages': ocr_failed,
                               'scan_unread_pages': [p['page'] for p in pages if p['scan_unread']]}
