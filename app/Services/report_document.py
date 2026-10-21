"""Read DOCX paragraphs/tables as stable source units, never pretend they are pages."""
import argparse
import json
import re
import time
import unicodedata
import zipfile
from pathlib import Path
from xml.etree import ElementTree as ET

from pdf_document_analysis import exact_quote

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'


def extract_docx_units(path):
    started = time.monotonic()
    with zipfile.ZipFile(path) as archive:
        entries = archive.infolist()
        if len(entries) > 5000 or sum(e.file_size for e in entries) > 100 * 1024 * 1024:
            raise ValueError('Tệp DOCX quá lớn để xử lý.')
        info = archive.getinfo('word/document.xml')
        if info.file_size > 8 * 1024 * 1024:
            raise ValueError('Nội dung DOCX vượt giới hạn xử lý.')
        xml = archive.read(info)
    if re.search(br'<!\s*(?:DOCTYPE|ENTITY)', xml, re.I):
        raise ValueError('Không hỗ trợ khai báo XML trong DOCX.')
    root = ET.fromstring(xml)
    body = root.find(W + 'body')
    if body is None:
        raise ValueError('DOCX không có nội dung đọc được.')

    def text_of(element):
        return ''.join(node.text or '' if node.tag == W + 't' else '\t' if node.tag == W + 'tab' else '\n'
                       for node in element.iter() if node.tag in (W + 't', W + 'tab', W + 'br')).strip()

    def blocks(element):
        for child in element:
            if child.tag in (W + 'p', W + 'tbl'):
                yield child
            elif child.tag in (W + 'sdt', W + 'sdtContent', W + 'customXml'):
                yield from blocks(child)

    units, paragraph, table = [], 0, 0
    for block in blocks(body):
        visual = any(node.tag.endswith(('}drawing', '}pict')) for node in block.iter())
        if block.tag == W + 'tbl':
            table += 1
            rows = [' | '.join('\n'.join(text_of(p) for p in cell.findall('.//' + W + 'p'))
                               for cell in row.findall(W + 'tc')) for row in block.findall(W + 'tr')]
            pieces, current = [], ''
            for row in rows:
                if current and len(current) + len(row) > 12000:
                    pieces.append(current.strip()); current = ''
                current += row + '\n'
            if current.strip():
                pieces.append(current.strip())
            labels = [(piece, f'Bảng {table}' + (f' · Phần {i + 1}' if len(pieces) > 1 else '')) for i, piece in enumerate(pieces)]
        else:
            value = text_of(block)
            if not value and not visual:
                continue
            paragraph += 1
            labels = [(value, f'Đoạn {paragraph}')]
        for text, label in labels:
            units.append({'page': len(units) + 1, 'text': text, 'source_label': label,
                          'source_kind': 'docx', 'visual_unread': visual, 'headings': [],
                          'char_count': len(text), 'word_count': len(text.split())})
    return units, len(units), {'total_chars': sum(len(u['text']) for u in units),
                              'total_words': sum(len(u['text'].split()) for u in units),
                              'valid_pages': sum(bool(u['text'].strip()) for u in units),
                              'extraction_time': round(time.monotonic() - started, 3)}


def preview_docx(path, unit, quote=''):
    units, total, _ = extract_docx_units(path)
    if not 1 <= unit <= total:
        raise ValueError('Không tìm thấy đoạn trong DOCX.')
    source = units[unit - 1]
    text = unicodedata.normalize('NFKC', source['text'])
    matched = exact_quote(quote, text) if quote else None
    start = text.find(matched) if matched else -1
    return {'kind': 'docx', 'page': unit, 'total_pages': total, 'label': source['source_label'],
            'text': text, 'matched': start >= 0, 'highlight_start': max(start, 0),
            'highlight_end': start + len(matched) if start >= 0 else 0}


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--file', required=True)
    parser.add_argument('--unit', type=int, required=True)
    parser.add_argument('--quote', default='')
    args = parser.parse_args()
    try:
        if Path(args.file).suffix.lower() != '.docx':
            raise ValueError('Định dạng tài liệu chưa được hỗ trợ.')
        print(json.dumps(preview_docx(args.file, args.unit, args.quote), ensure_ascii=False))
    except Exception:
        print(json.dumps({'error': 'Chưa mở được nội dung DOCX.'}, ensure_ascii=False))
        raise SystemExit(1)
