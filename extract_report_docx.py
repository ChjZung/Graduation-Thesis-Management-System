import zipfile
import xml.etree.ElementTree as ET
import glob
import sys
import os

sys.stdout.reconfigure(encoding='utf-8')

possible_paths = [
    r"\\Mac\Home\Documents\1_monhoc\KhoaLuan\BaoCaoKhoaLuan_No1.docx",
    r"C:\Users\hochidung\Documents\1_monhoc\KhoaLuan\BaoCaoKhoaLuan_No1.docx",
    r"\\Mac\Home\Documents\1_monhoc\Khoá luận\BaoCaoKhoaLuan_No1.docx",
    r"\\Mac\Home\Documents\1_monhoc\KhoaLuan\*BaoCao*.docx",
    r"\\Mac\Home\Documents\1_monhoc\Khoá luận\*BaoCao*.docx",
    r"C:\Users\hochidung\Documents\*BaoCao*.docx"
]

target_file = None
for p in possible_paths:
    matches = glob.glob(p)
    if matches:
        target_file = matches[0]
        break

if not target_file:
    for root_dir in [r"\\Mac\Home\Documents", r"C:\Users\hochidung\Documents"]:
        for root, dirs, files in os.walk(root_dir):
            for f in files:
                if f.endswith('.docx') and ('baocaokhoaluan' in f.lower() or 'baocao' in f.lower()):
                    target_file = os.path.join(root, f)
                    break
            if target_file:
                break

if not target_file:
    print("FILE_NOT_FOUND")
    sys.exit(1)

print(f"FOUND: {target_file}")

with zipfile.ZipFile(target_file) as z:
    xml_content = z.read('word/document.xml')

root = ET.fromstring(xml_content)

texts = []
for p in root.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}p'):
    p_text = ''.join([node.text for node in p.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t') if node.text])
    if p_text.strip():
        texts.append(p_text.strip())

table_data = []
for table in root.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tbl'):
    for row in table.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tr'):
        row_text = []
        for cell in row.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tc'):
            cell_text = ''.join([node.text for node in cell.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t') if node.text])
            row_text.append(cell_text.strip())
        if any(row_text):
            table_data.append(" | ".join(row_text))

out_path = r'C:\laragon\www\KhoaLuanCuNhan\report_docx_extracted.txt'
with open(out_path, 'w', encoding='utf-8') as f:
    f.write(f"=== TARGET FILE: {target_file} ===\n\n")
    f.write("=== PARAGRAPHS ===\n")
    f.write("\n".join(texts))
    f.write("\n\n=== TABLES ===\n")
    f.write("\n".join(table_data))

print(f"Extracted successfully to {out_path}!")
