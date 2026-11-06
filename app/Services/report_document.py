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
from ai_source_navigation import CAPTION

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'


def docx_styles(archive):
    try:
        info = archive.getinfo('word/styles.xml')
    except KeyError:
        return {}
    if info.file_size > 2 * 1024 * 1024:
        return {}
    xml = archive.read(info)
    if re.search(br'<!\s*(?:DOCTYPE|ENTITY)', xml, re.I):
        return {}
    try:
        root = ET.fromstring(xml)
    except ET.ParseError:
        return {}
    return {style.get(W + 'styleId'): style for style in root.findall(W + 'style')
            if style.get(W + 'styleId')}


def word_value(element, *tags):
    if element is None:
        return ''
    node = element.find('/'.join(W + tag for tag in tags))
    return node.get(W + 'val', '') if node is not None else ''


def paragraph_metadata(paragraph, text, styles, in_contents):
    from document_sections import heading

    properties = paragraph.find(W + 'pPr')
    style_id = word_value(properties, 'pStyle')
    outline = word_value(properties, 'outlineLvl')
    level = int(outline) + 1 if re.fullmatch(r'[0-8]', outline) else None
    style = style_id
    seen = set()
    is_caption = bool(CAPTION.match(text))
    while style and style not in seen:
        seen.add(style)
        definition = styles.get(style)
        name = word_value(definition, 'name')
        for label in (style, name):
            key = ''.join(c for c in unicodedata.normalize('NFD', label.casefold()) if not unicodedata.combining(c))
            key = re.sub(r'[^a-z0-9]', '', key)
            in_contents |= bool(re.fullmatch(r'toc\d*|tableofcontents|mucluc\d*|tableoffigures', key))
            is_caption |= key in ('caption', 'chuthich')
            match = re.fullmatch(r'(?:heading|tieude)([1-9])', key)
            if match and level is None and not outline:
                level = int(match.group(1))
        inherited = word_value(definition, 'pPr', 'outlineLvl')
        if level is None and not outline and re.fullmatch(r'[0-8]', inherited):
            level = int(inherited) + 1
        style = word_value(definition, 'basedOn')
    in_contents |= ' '.join(text.casefold().split()).rstrip(':') in (
        'mục lục', 'danh mục hình', 'danh mục bảng', 'table of contents', 'list of figures', 'list of tables')
    in_contents |= bool(re.search(r'\.{3,}|…{2,}|\t', text) and re.search(r'\s\d+\s*$', text))
    if in_contents:
        return {'is_index': True}
    if is_caption:
        return {'caption': text}
    candidate = heading(' '.join(text.split()))
    if level is not None and text:
        number, title, _, kind = candidate or (None, ' '.join(text.split()), level, 'section')
        return {'docx_heading': (number, title, level, kind if level == 1 else 'section')}
    if candidate and (candidate[1] or candidate[3] == 'unnumbered'):
        return {'docx_heading': candidate}
    text = ' '.join(text.split())
    if not candidate and 3 <= len(text) <= 90 and not re.search(r'[.!?;:]$', text):
        runs = [run for run in paragraph.iter(W + 'r') if any(t.text for t in run.findall(W + 't'))]
        emphasis = []
        for run in runs:
            props = run.find(W + 'rPr')
            bold = props.find(W + 'b') if props is not None else None
            size = word_value(props, 'sz')
            emphasis.append(bold is not None and bold.get(W + 'val', '1') not in ('0', 'false', 'off')
                            and size.isdecimal() and int(size) >= 30)
        if emphasis and all(emphasis) and word_value(properties, 'jc') == 'center':
            return {'docx_heading': (None, text, 1, 'unnumbered')}
    return {}


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
        styles = docx_styles(archive)
    if re.search(br'<!\s*(?:DOCTYPE|ENTITY)', xml, re.I):
        raise ValueError('Không hỗ trợ khai báo XML trong DOCX.')
    root = ET.fromstring(xml)
    body = root.find(W + 'body')
    if body is None:
        raise ValueError('DOCX không có nội dung đọc được.')

    def text_of(element):
        return ''.join(node.text or '' if node.tag == W + 't' else '\t' if node.tag == W + 'tab' else '\n'
                       for node in element.iter() if node.tag in (W + 't', W + 'tab', W + 'br')).strip()

    def blocks(element, in_contents=False):
        for child in element:
            if child.tag in (W + 'p', W + 'tbl'):
                yield child, in_contents
            elif child.tag in (W + 'sdt', W + 'sdtContent', W + 'customXml'):
                gallery = word_value(child, 'sdtPr', 'docPartObj', 'docPartGallery')
                yield from blocks(child, in_contents or gallery.casefold() in ('table of contents', 'table of figures'))

    units, paragraph, table = [], 0, 0
    for block, in_contents in blocks(body):
        metadata = {'is_index': True} if in_contents else {}
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
            metadata = paragraph_metadata(block, value, styles, in_contents)
        for part, (text, label) in enumerate(labels, 1):
            is_table = block.tag == W + 'tbl'
            location = {'kind': 'docx', 'unit': len(units) + 1,
                        **({'table': table, 'part': part} if is_table else {'paragraph': paragraph})}
            units.append({'page': len(units) + 1, 'text': text, 'source_label': label,
                          'source_kind': 'docx', 'visual_unread': visual, 'headings': [],
                          'source_location': location, **metadata,
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
            'text': text, 'source_location': source['source_location'], 'matched': start >= 0, 'highlight_start': max(start, 0),
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
