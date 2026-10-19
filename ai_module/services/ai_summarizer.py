#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Scientific NLP & Map-Reduce Pipeline for Graduation Thesis Progress Reports.
Integrated with Google Gemini API & High-Precision Heuristic NLP Fallback.

Core Principles:
- ZERO HALLUCINATION: Never invent deliverables, difficulties, technologies, or citations.
  If information is missing, return empty arrays or clear indicators.
- EVIDENCE VERIFICATION: Cross-check every extracted quote and page citation against
  actual PDF text at that exact page. Mark unverified citations transparently.
- STRUCTURE-AWARE CHUNKING: Preserve chapters, sections, and subsections with page provenance.
- ROBUST GEMINI CALLS: Header-based authentication (x-goog-api-key), exponential backoff,
  handling of HTTP 400/401/403/404/429/500/503 and automatic model fallbacks.
- 11 STANDARDIZED SECTIONS: Perfect compatibility with Laravel UI & thesis evaluation workflows.
"""

import sys
import os
import json
import re
import argparse
import time
import urllib.request
import urllib.error
import concurrent.futures

# Ensure UTF-8 output on Windows
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8')

try:
    import pymupdf as fitz
except ImportError:
    try:
        import fitz
    except ImportError:
        fitz = None

try:
    import requests
except ImportError:
    requests = None


# ==============================================================================
# 1. PDF EXTRACTION & STRUCTURAL PREPROCESSING
# ==============================================================================

def extract_pdf_pages(pdf_path):
    """
    Extract text page by page (1-based index) using PyMuPDF.
    Detects scanned / non-text pages and extracts structural headings.
    """
    t0 = time.time()
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"Tệp PDF không tồn tại: {pdf_path}")

    if not fitz:
        raise RuntimeError("Thư viện PyMuPDF (pymupdf/fitz) chưa được cài đặt trong môi trường Python.")

    doc = fitz.open(pdf_path)
    total_pages = len(doc)
    pages = []
    total_chars = 0
    total_words = 0
    valid_pages = 0

    heading_regex = re.compile(
        r'^(chương\s+\d+|chương\s+[ivxlcdm]+|phần\s+\d+|mục\s+\d+|'
        r'\d+\.\d+(\.\d+)*\s+[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]|'
        r'[ivxlcdm]+\.\s+[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ])',
        re.IGNORECASE
    )

    for page_num in range(total_pages):
        page = doc[page_num]
        raw_text = page.get_text("text").strip()
        # Clean control characters except newlines/tabs
        cleaned_text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', raw_text)
        char_cnt = len(cleaned_text)
        words = len(cleaned_text.split()) if char_cnt > 0 else 0

        headings = []
        for line in cleaned_text.split('\n'):
            line_str = line.strip()
            if heading_regex.match(line_str) and len(line_str) <= 120:
                headings.append(line_str)

        if char_cnt > 30:
            valid_pages += 1
            total_chars += char_cnt
            total_words += words

        pages.append({
            "page": page_num + 1,
            "text": cleaned_text,
            "char_count": char_cnt,
            "word_count": words,
            "headings": headings
        })

    doc.close()
    extraction_time = round(time.time() - t0, 3)

    return pages, total_pages, {
        "total_chars": total_chars,
        "total_words": total_words,
        "valid_pages": valid_pages,
        "extraction_time": extraction_time
    }


def build_structured_chunks(pages, chunk_size=3500, chunk_overlap=350):
    """
    Build structure-aware text chunks preserving chapter/section titles and page provenance.
    If a section exceeds chunk_size, splits with sliding-window overlap on paragraph boundaries.
    """
    t0 = time.time()
    chunks = []
    current_blocks = []
    current_length = 0
    current_section = "Nội dung chung"

    all_blocks = []
    for p in pages:
        if not p["text"]:
            continue
        paragraphs = [para.strip() for para in p["text"].split("\n\n") if para.strip()]
        if not paragraphs:
            paragraphs = [line.strip() for line in p["text"].split("\n") if line.strip()]

        for para in paragraphs:
            for h in p.get("headings", []):
                if h in para:
                    current_section = h
                    break
            all_blocks.append({
                "page": p["page"],
                "section": current_section,
                "text": para
            })

    if not all_blocks:
        return [], 0.0

    start_page = all_blocks[0]["page"]

    for b in all_blocks:
        b_len = len(b["text"])
        if current_length + b_len > chunk_size and current_blocks:
            end_page = current_blocks[-1]["page"]
            sec_title = current_blocks[0]["section"]
            chunk_text = f"=== PHẦN: {sec_title} (Từ Trang {start_page} đến Trang {end_page}) ===\n\n"
            chunk_text += "\n\n".join([f"[Trang {item['page']}] {item['text']}" for item in current_blocks])

            chunks.append({
                "chunk_id": len(chunks) + 1,
                "section": sec_title,
                "start_page": start_page,
                "end_page": end_page,
                "text": chunk_text,
                "pages": sorted(list(set(item["page"] for item in current_blocks))),
                "char_count": len(chunk_text)
            })

            # Overlap: keep trailing blocks within chunk_overlap characters
            overlap_blocks = []
            overlap_len = 0
            for prev_b in reversed(current_blocks):
                if overlap_len + len(prev_b["text"]) <= chunk_overlap:
                    overlap_blocks.insert(0, prev_b)
                    overlap_len += len(prev_b["text"])
                else:
                    break

            current_blocks = list(overlap_blocks)
            current_length = overlap_len
            start_page = current_blocks[0]["page"] if current_blocks else b["page"]

        current_blocks.append(b)
        current_length += b_len

    if current_blocks:
        end_page = current_blocks[-1]["page"]
        sec_title = current_blocks[0]["section"]
        chunk_text = f"=== PHẦN: {sec_title} (Từ Trang {start_page} đến Trang {end_page}) ===\n\n"
        chunk_text += "\n\n".join([f"[Trang {item['page']}] {item['text']}" for item in current_blocks])

        chunks.append({
            "chunk_id": len(chunks) + 1,
            "section": sec_title,
            "start_page": start_page,
            "end_page": end_page,
            "text": chunk_text,
            "pages": sorted(list(set(item["page"] for item in current_blocks))),
            "char_count": len(chunk_text)
        })

    chunking_time = round(time.time() - t0, 3)
    return chunks, chunking_time


# ==============================================================================
# 2. ROBUST GOOGLE GEMINI API CALLER
# ==============================================================================

def clean_json_markdown(text):
    """Strip markdown code fences and whitespace from LLM json response."""
    text = text.strip()
    if text.startswith("```"):
        lines = text.split("\n")
        if lines[0].startswith("```"):
            lines = lines[1:]
        if lines and lines[-1].strip() == "```":
            lines = lines[:-1]
        text = "\n".join(lines).strip()
    return text


def call_gemini_api(prompt, api_key, model="gemini-3.5-flash", timeout=60):
    """
    Call Google Gemini REST API using header authentication (x-goog-api-key).
    Implements intelligent failover: if primary model times out or returns 503/404/429 quota exhaustion,
    quickly tries reliable fallback models (gemini-3.5-flash, gemini-3.5-flash-lite, gemini-flash-lite-latest).
    """
    if not api_key:
        raise ValueError("GEMINI_API_KEY không được cung cấp.")

    candidate_models = [model]
    for fb in ["gemini-3.5-flash", "gemini-3.5-flash-lite", "gemini-flash-lite-latest", "gemini-3.6-flash"]:
        if fb not in candidate_models:
            candidate_models.append(fb)

    payload = {
        "contents": [{
            "parts": [{"text": prompt}]
        }],
        "generationConfig": {
            "temperature": 0.1,  # Low temperature for factual precision without hallucination
            "maxOutputTokens": 4096,
            "responseMimeType": "application/json"
        }
    }
    payload_bytes = json.dumps(payload, ensure_ascii=False).encode("utf-8")
    headers = {
        "Content-Type": "application/json; charset=utf-8",
        "x-goog-api-key": api_key
    }

    last_error = None

    for current_model in candidate_models:
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{current_model}:generateContent"

        max_attempts = 2
        for attempt in range(max_attempts):
            try:
                if requests:
                    resp = requests.post(url, json=payload, headers=headers, timeout=timeout)
                    status_code = resp.status_code
                    resp_text = resp.text
                else:
                    req = urllib.request.Request(url, data=payload_bytes, headers=headers, method="POST")
                    try:
                        with urllib.request.urlopen(req, timeout=timeout) as response:
                            status_code = response.status
                            resp_text = response.read().decode("utf-8")
                    except urllib.error.HTTPError as he:
                        status_code = he.code
                        resp_text = he.read().decode("utf-8")

                if status_code == 200:
                    data = json.loads(resp_text)
                    raw_text = data["candidates"][0]["content"]["parts"][0]["text"]
                    cleaned = clean_json_markdown(raw_text)
                    return json.loads(cleaned), current_model

                # Fatal client errors: do not retry
                if status_code in (400, 401, 403):
                    try:
                        err_json = json.loads(resp_text)
                        msg = err_json.get("error", {}).get("message", resp_text)
                    except Exception:
                        msg = resp_text
                    raise RuntimeError(f"Gemini API Client Error (HTTP {status_code}): {msg}")

                # Model not found (404) or 503 high demand: switch to next candidate model immediately!
                if status_code in (404, 503):
                    last_error = f"Model {current_model} tạm thời bận hoặc dừng (HTTP {status_code})."
                    break

                # Rate limits and quota: 429
                if status_code == 429:
                    last_error = f"Gemini {current_model} chạm hạn mức (HTTP 429)."
                    # If daily quota is exhausted (RESOURCE_EXHAUSTED or Quota exceeded), switch to next model immediately!
                    if "RESOURCE_EXHAUSTED" in resp_text or "Quota exceeded" in resp_text:
                        break
                    time.sleep(1.5 * (attempt + 1))
                    continue

                if status_code in (500, 502, 504):
                    last_error = f"Gemini {current_model} lỗi máy chủ (HTTP {status_code})."
                    time.sleep(1.5 * (attempt + 1))
                    continue

                last_error = f"Gemini HTTP {status_code}: {resp_text[:200]}"
                time.sleep(1.0)

            except (requests.exceptions.RequestException if requests else Exception, urllib.error.URLError) as net_err:
                last_error = f"Lỗi kết nối / timeout Gemini {current_model}: {str(net_err)}"
                # If timed out on a busy model, break to fallback model
                if len(candidate_models) > 1 and current_model != candidate_models[-1]:
                    break
                time.sleep(1.5)
            except json.JSONDecodeError as json_err:
                last_error = f"Lỗi phân tích JSON từ Gemini: {str(json_err)}"
                time.sleep(1)

    raise RuntimeError(f"Không thể kết nối Gemini API thành công sau các lần thử: {last_error}")


# ==============================================================================
# 3. EVIDENCE VERIFICATION ENGINE (CHỐNG TỰ BỊA NỘI DUNG)
# ==============================================================================

def normalize_text_for_search(text):
    """Normalize text by lowering case, removing punctuation and condensing spaces."""
    if not text:
        return ""
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    return ' '.join(text.split())


def verify_evidence_in_pages(items, pages_by_num):
    """
    Verify each extracted item by checking if its evidence string actually appears
    in the text of the claimed source_pages in the PDF.
    Marks verified=True/False and flags unverified items for inspection.
    """
    verified_items = []
    for item in items:
        if not isinstance(item, dict):
            continue
        content = item.get("content", "").strip()
        evidence = item.get("evidence", "").strip()
        claimed_pages = item.get("source_pages", [])
        if not isinstance(claimed_pages, list):
            claimed_pages = [claimed_pages] if claimed_pages else [1]

        is_verified = False
        norm_evidence = normalize_text_for_search(evidence)
        norm_content = normalize_text_for_search(content)

        # Check in the claimed pages first
        checked_pages = []
        for p_num in claimed_pages:
            checked_pages.append(p_num)
            page_text = pages_by_num.get(p_num, "")
            norm_page = normalize_text_for_search(page_text)

            # Check if evidence snippet (or key phrases) appears in page
            if norm_evidence and len(norm_evidence) >= 8:
                # Substring match or partial phrase match (at least 5 consecutive words)
                evidence_words = norm_evidence.split()
                if norm_evidence in norm_page:
                    is_verified = True
                    break
                elif len(evidence_words) >= 4:
                    phrase = " ".join(evidence_words[:min(6, len(evidence_words))])
                    if phrase in norm_page:
                        is_verified = True
                        break

            # Fallback check on content
            if norm_content and len(norm_content) >= 10:
                content_words = norm_content.split()
                if len(content_words) >= 4:
                    phrase = " ".join(content_words[:min(5, len(content_words))])
                    if phrase in norm_page:
                        is_verified = True
                        break

        item_copy = dict(item)
        item_copy["verified"] = is_verified
        if not is_verified:
            item_copy["verification_note"] = f"Cần đối chiếu thêm với trang {', '.join(map(str, claimed_pages))}"
        verified_items.append(item_copy)

    return verified_items


# ==============================================================================
# 4. ZERO-HALLUCINATION HEURISTIC NLP EXTRACTOR
# ==============================================================================

def nlp_extract_from_text(pages, stage=1):
    """
    Factual NLP extractor for fallback situations.
    STRICT ZERO-HALLUCINATION POLICY:
    - If difficulties/deliverables/links are not found, leave them EMPTY.
    - NEVER invent fake Laravel, MySQL, Bootstrap tech stacks.
    - NEVER fabricate % progress without explicit student metric in text.
    """
    completed = []
    deliverables = []
    difficulties = []
    pending_items = []
    next_steps = []
    missing_info = []
    technical_links = []
    citations = []
    time_milestones = []

    kw_completed = re.compile(
        r'(đã hoàn thành|kết quả đạt được|chức năng đã|đã thực hiện|xây dựng xong|'
        r'triển khai thành công|đã hoàn tất|thiết kế xong|đã cấu hình|đã kiểm thử|'
        r'nghiên cứu xong|khảo sát xong)',
        re.IGNORECASE
    )
    kw_deliverables = re.compile(
        r'(sản phẩm đạt được|kết quả đầu ra|bản vẽ sơ đồ|bảng csdl|mã nguồn|'
        r'giao diện đã thiết kế|tài liệu đặc tả|module hoàn thiện)',
        re.IGNORECASE
    )
    kw_difficulties = re.compile(
        r'(khó khăn gặp phải|vướng mắc kỹ thuật|hạn chế hiện tại|thách thức|'
        r'lỗi phát sinh chưa sửa|xung đột phiên bản|chưa tối ưu được)',
        re.IGNORECASE
    )
    kw_pending = re.compile(
        r'(công việc tồn đọng|chưa hoàn thành|đang hoàn thiện dở dang|'
        r'còn thiếu sót|chưa kiểm thử xong|chưa tích hợp được)',
        re.IGNORECASE
    )
    kw_next_steps = re.compile(
        r'(kế hoạch tiếp theo|hướng phát triển tuần tới|dự kiến giai đoạn sau|'
        r'công việc cần làm tiếp|mục tiêu tuần tới|nhiệm vụ bước tiếp)',
        re.IGNORECASE
    )
    kw_time = re.compile(
        r'(ngày\s+\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}|tuần\s+\d{1,2}|tháng\s+\d{1,2}|'
        r'giai đoạn\s+\d|hạn chót|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})',
        re.IGNORECASE
    )
    kw_links = re.compile(
        r'(https?://[^\s\)\"\']+|github\.com/[^\s\)\"\']+|gitlab\.com/[^\s\)\"\']+|bitbucket\.org/[^\s\)\"\']+)',
        re.IGNORECASE
    )

    # Known technologies to search for factual mention
    tech_candidates = [
        'Laravel', 'PHP', 'Python', 'MySQL', 'PostgreSQL', 'MongoDB', 'SQL Server',
        'React', 'Vue', 'Angular', 'Node.js', 'Express', 'Bootstrap', 'Tailwind',
        'Docker', 'Kubernetes', 'FastAPI', 'Flask', 'Flutter', 'React Native',
        'Git', 'GitHub', 'GitLab', 'Figma', 'Postman', 'Spring Boot', 'Java',
        'C#', '.NET', 'Redis', 'TensorFlow', 'PyTorch', 'AWS', 'Firebase'
    ]
    detected_tech = set()
    explicit_progress_percent = None

    # Search for explicit percentage in text (e.g. "Tiến độ: 80%", "hoàn thành 75%")
    pct_regex = re.compile(r'(?:tiến độ|hoàn thành|đạt được)[:\s]+(\d{1,3})\s*%', re.IGNORECASE)

    for p in pages:
        p_num = p["page"]
        text = p["text"]
        if not text:
            continue
        lines = [line.strip() for line in text.split("\n") if line.strip()]

        # Find URLs
        for m in kw_links.finditer(text):
            url = m.group(1).rstrip('.,;:)">]')
            if not any(tl['url'] == url for tl in technical_links):
                is_git = any(g in url.lower() for g in ['github.com', 'gitlab.com', 'bitbucket.org'])
                technical_links.append({
                    "name": "Mã nguồn dự án (Repository)" if is_git else "Liên kết tham chiếu tài liệu",
                    "url": url,
                    "type": "repository" if is_git else "external_link",
                    "source_pages": [p_num]
                })

        # Detect technologies explicitly written
        for tk in tech_candidates:
            if re.search(r'\b' + re.escape(tk) + r'\b', text, re.IGNORECASE):
                detected_tech.add(tk)

        # Detect explicit progress percentage
        if explicit_progress_percent is None:
            pct_match = pct_regex.search(text)
            if pct_match:
                val = int(pct_match.group(1))
                if 0 <= val <= 100:
                    explicit_progress_percent = val

        # Detect milestones
        for m in kw_time.finditer(text):
            m_text = m.group(1).strip()
            if m_text not in time_milestones and len(time_milestones) < 6:
                time_milestones.append(f"{m_text} (Trang {p_num})")

        # Sentence-by-sentence classification
        for line in lines:
            clean_line = re.sub(r'^[•\-\*\+\d+\.\)]\s*', '', line).strip()
            if len(clean_line) < 10 or len(clean_line) > 280:
                continue

            if kw_completed.search(line):
                if not any(item["content"] == clean_line for item in completed):
                    completed.append({
                        "content": clean_line,
                        "evidence": clean_line[:140],
                        "source_pages": [p_num]
                    })
                    if len(citations) < 8 and not any(c['page'] == p_num for c in citations):
                        citations.append({
                            "page": p_num,
                            "topic": "Hạng mục hoàn thành",
                            "snippet": clean_line[:120]
                        })

            if kw_deliverables.search(line) and any(w in line.lower() for w in ['đã', 'kết quả', 'đạt được', 'xây dựng', 'xong', 'bản mẫu']):
                if not any(item["content"] == clean_line for item in deliverables):
                    deliverables.append({
                        "content": clean_line,
                        "evidence": clean_line[:140],
                        "source_pages": [p_num]
                    })

            if kw_difficulties.search(line):
                if not any(item["content"] == clean_line for item in difficulties):
                    difficulties.append({
                        "content": clean_line,
                        "evidence": clean_line[:140],
                        "source_pages": [p_num]
                    })

            if kw_pending.search(line):
                if not any(item["content"] == clean_line for item in pending_items):
                    pending_items.append({
                        "content": clean_line,
                        "evidence": clean_line[:140],
                        "source_pages": [p_num]
                    })

            if kw_next_steps.search(line):
                if not any(item["content"] == clean_line for item in next_steps):
                    next_steps.append({
                        "content": clean_line,
                        "evidence": clean_line[:140],
                        "source_pages": [p_num]
                    })

    # Missing info & warnings
    if len(pages) < 2:
        missing_info.append("Báo cáo có dung lượng ngắn (< 2 trang), đề nghị bổ sung chi tiết nội dung và hình ảnh minh chứng.")
    if not technical_links and stage >= 2:
        missing_info.append("Chưa phát hiện liên kết mã nguồn (GitHub/GitLab) hoặc tài nguyên triển khai trong tài liệu.")
    if not completed:
        missing_info.append("Chưa tìm thấy đoạn văn bản nêu rõ công việc đã hoàn tất cụ thể trong tài liệu nộp.")

    # Objective recommendations based on factual findings
    recs = []
    if not technical_links:
        recs.append("Cần cập nhật link repository Git hoặc tài liệu minh chứng để GVHD có căn cứ đánh giá mã nguồn.")
    if not difficulties:
        recs.append("Nếu có vướng mắc kỹ thuật trong quá trình thực hiện, nhóm cần ghi rõ để GVHD kịp thời hướng dẫn.")
    recs.append(f"Chuẩn bị các tài liệu nghiệm thu cho Giai đoạn {stage} theo đúng tiến độ kế hoạch.")

    # ZERO HALLUCINATION SUMMARY:
    if completed:
        summary_text = f"Báo cáo tiến độ ({len(pages)} trang) trích xuất được {len(completed[:8])} công việc đã thực hiện."
        if deliverables:
            summary_text += f" Ghi nhận {len(deliverables[:5])} sản phẩm đầu ra."
        if difficulties:
            summary_text += f" Nhóm báo cáo {len(difficulties[:4])} khó khăn."
        else:
            summary_text += " Tài liệu không nêu khó khăn cụ thể."
    else:
        summary_text = f"Đã đọc tài liệu ({len(pages)} trang), tuy nhiên văn bản không có các đề mục thể hiện rõ công việc đã hoàn thành."

    ref_pages = sorted(list(set([c["page"] for c in citations] + [1]))) if citations else [1]

    standard_report = {
        "tom_tat": summary_text,
        "cong_viec_hoan_thanh": [item["content"] for item in completed[:8]],
        "ket_qua_dat_duoc": [item["content"] for item in deliverables[:6]],
        "kho_khan": [item["content"] for item in difficulties[:5]],
        "ke_hoach_tiep_theo": [item["content"] for item in next_steps[:5]],
        "moc_thoi_gian": time_milestones if time_milestones else [f"Giai đoạn {stage}"],
        "noi_dung_chua_ro": missing_info,
        "canh_bao": recs[:2] if missing_info else [],
        "nguon_tham_chieu": [f"Trang {p}" for p in ref_pages]
    }

    presentation_data = {
        "overall_summary": summary_text,
        "completion_percentage": explicit_progress_percent,
        "completed_work": completed[:8],
        "deliverables": deliverables[:6],
        "technical_links": technical_links,
        "tech_stack": sorted(list(detected_tech)),
        "difficulties": difficulties[:5],
        "pending_items": pending_items[:5],
        "next_steps": next_steps[:5],
        "missing_information": missing_info,
        "citations": citations[:8],
        "ai_recommendations": recs
    }

    return standard_report, presentation_data


# ==============================================================================
# 5. FULL MAP-REDUCE PIPELINE INTEGRATION
# ==============================================================================

def run_map_reduce_pipeline(
    pdf_path,
    api_key=None,
    model="gemini-3.5-flash",
    stage=1,
    chunk_size=3500,
    chunk_overlap=350,
    method="map_reduce"
):
    """
    Execute full structure-aware Map-Reduce summarization using Google Gemini API.
    Guarantees verified evidence, clean factual extraction, and graceful fallback.
    """
    t_start = time.time()
    pages, total_pages, meta = extract_pdf_pages(pdf_path)

    # Scanned / empty PDF check
    if total_pages == 0 or meta["valid_pages"] == 0 or meta["total_chars"] < 40:
        return {
            "success": False,
            "error": "Tài liệu PDF không chứa nội dung văn bản (hoặc là file scan ảnh chưa qua nhận dạng OCR).",
            "total_pages": total_pages,
            "metrics": {
                "total_pages": total_pages,
                "valid_pages": 0,
                "total_chars": meta.get("total_chars", 0),
                "total_words": meta.get("total_words", 0),
                "processing_time": round(time.time() - t_start, 3)
            },
            "standard_report": None,
            "data": None
        }

    pages_by_num = {p["page"]: p["text"] for p in pages}
    chunks, chunking_time = build_structured_chunks(pages, chunk_size=chunk_size, chunk_overlap=chunk_overlap)
    standard_nlp, pres_nlp = nlp_extract_from_text(pages, stage=stage)

    # If no API key or method is explicitly set to heuristic NLP
    if not api_key or method == "heuristic_nlp":
        # Verify evidence citations in NLP results
        pres_nlp["completed_work"] = verify_evidence_in_pages(pres_nlp.get("completed_work", []), pages_by_num)
        pres_nlp["deliverables"] = verify_evidence_in_pages(pres_nlp.get("deliverables", []), pages_by_num)
        pres_nlp["difficulties"] = verify_evidence_in_pages(pres_nlp.get("difficulties", []), pages_by_num)
        pres_nlp["pending_items"] = verify_evidence_in_pages(pres_nlp.get("pending_items", []), pages_by_num)
        pres_nlp["next_steps"] = verify_evidence_in_pages(pres_nlp.get("next_steps", []), pages_by_num)

        t_total = round(time.time() - t_start, 3)
        return {
            "success": True,
            "source": "heuristic_nlp",
            "total_pages": total_pages,
            "metrics": {
                "total_pages": total_pages,
                "valid_pages": meta["valid_pages"],
                "char_count": meta["total_chars"],
                "word_count": meta["total_words"],
                "chunk_count": len(chunks),
                "chunk_size": chunk_size,
                "chunk_overlap": chunk_overlap,
                "method": "heuristic_nlp",
                "model_used": "none",
                "extraction_time": meta["extraction_time"],
                "chunking_time": chunking_time,
                "map_time": 0.0,
                "reduce_time": 0.0,
                "processing_time": t_total,
                "citation_count": len(pres_nlp.get("citations", []))
            },
            "standard_report": standard_nlp,
            "data": pres_nlp
        }

    # ==================== MAP PHASE (CONCURRENT) ====================
    t_map_start = time.time()
    map_slots = [None] * len(chunks)
    actual_model_used = model
    gemini_error_msg = None
    failed_chunks = 0

    def process_chunk(idx_and_chunk):
        idx, chunk = idx_and_chunk
        map_prompt = f"""Bạn là chuyên gia thẩm định tiến độ khóa luận tốt nghiệp ngành CNTT.
Nhiệm vụ: Đọc đoạn văn bản báo cáo dưới đây và trích xuất thông tin ĐÚNG THỰC TẾ với format JSON.

QUY TẮC BẮT BUỘC (CHỐNG TỰ BỊA NỘI DUNG):
1. CHỈ trích xuất thông tin CÓ THẬT và XUẤT HIỆN RÕ RÀNG trong đoạn văn.
2. Phân biệt rõ:
   - "completed_work": Việc ĐÃ LÀM XONG trong giai đoạn này.
   - "deliverables": Sản phẩm cụ thể ĐÃ CÓ (file, link git, bảng CSDL, giao diện demo...).
   - "difficulties": Khó khăn, lỗi kỹ thuật THỰC TẾ được tác giả nêu trong văn bản.
   - "pending_items": Việc đang làm dở dang hoặc chưa hoàn thành.
   - "next_steps": Kế hoạch cho tuần tới hoặc giai đoạn sau.
3. NẾU đoạn văn bản KHÔNG đề cập đến mục nào, BẮT BUỘC để mảng rỗng [] cho mục đó.
   TUYỆT ĐỐI KHÔNG tự đoán, không tự bịa khó khăn hay sản phẩm nếu tài liệu không nhắc đến.
4. "evidence" phải là câu trích dẫn ngắn NGUYÊN VĂN từ văn bản. "source_pages" là mảng số trang chứa câu trích dẫn đó.
5. "tech_stack": Chỉ liệt kê các công nghệ/ngôn ngữ/framework thực sự được nhắc đến trong đoạn này. Nếu không có, để [].
6. "explicit_percentage": Nếu tài liệu ghi rõ % tiến độ (ví dụ "hoàn thành 70%"), trả về số nguyên (ví dụ: 70). Nếu không ghi, trả về null.

ĐOẠN VĂN BẢN (Từ Trang {chunk['start_page']} đến Trang {chunk['end_page']}):
{chunk['text']}

Trả về đúng JSON theo cấu trúc sau (không kèm markdown ngoài JSON):
{{
  "completed_work": [
    {{ "content": "Mô tả công việc đã làm", "evidence": "Trích dẫn nguyên văn", "source_pages": [{chunk['start_page']}] }}
  ],
  "deliverables": [
    {{ "content": "Sản phẩm đạt được", "evidence": "Trích dẫn nguyên văn", "source_pages": [{chunk['start_page']}] }}
  ],
  "difficulties": [
    {{ "content": "Khó khăn thực tế", "evidence": "Trích dẫn nguyên văn", "source_pages": [{chunk['start_page']}] }}
  ],
  "pending_items": [
    {{ "content": "Việc dở dang", "evidence": "Trích dẫn nguyên văn", "source_pages": [{chunk['start_page']}] }}
  ],
  "next_steps": [
    {{ "content": "Kế hoạch giai đoạn tới", "evidence": "Trích dẫn nguyên văn", "source_pages": [{chunk['end_page']}] }}
  ],
  "tech_stack": [],
  "explicit_percentage": null
}}
"""
        try:
            chunk_res, used_model = call_gemini_api(map_prompt, api_key, model=model)
            return idx, chunk_res, used_model, None
        except Exception as e:
            return idx, None, None, str(e)

    # Chạy song song tối đa 3-4 workers để tối ưu thời gian gọi API và tránh rate limit
    max_workers = min(len(chunks), 4) if len(chunks) > 0 else 1
    with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
        future_to_idx = {executor.submit(process_chunk, (i, ch)): i for i, ch in enumerate(chunks)}
        for future in concurrent.futures.as_completed(future_to_idx):
            idx, chunk_res, used_model, err = future.result()
            if chunk_res and isinstance(chunk_res, dict):
                map_slots[idx] = chunk_res
                if used_model:
                    actual_model_used = used_model
            else:
                failed_chunks += 1
                if err:
                    gemini_error_msg = err

    map_results = [res for res in map_slots if res is not None]
    t_map = round(time.time() - t_map_start, 3)

    # ==================== REDUCE PHASE ====================
    t_reduce_start = time.time()
    reduced = None

    if map_results:
        # If document is very long (> 5 chunks), perform two-tier hierarchical reduce
        chunks_to_reduce = map_results
        if len(map_results) > 5:
            grouped_chunks = []
            group_size = 4
            for i in range(0, len(map_results), group_size):
                sub_group = map_results[i:i + group_size]
                sub_prompt = f"""Bạn là trợ lý tổng hợp tiến độ khóa luận. Hãy gộp dữ liệu từ {len(sub_group)} phần dưới đây, loại bỏ trùng lặp và giữ nguyên số trang trích dẫn:
DỮ LIỆU CÁC PHẦN:
{json.dumps(sub_group, ensure_ascii=False)}

Trả về JSON có cấu trúc tương tự. Không thêm markdown ngoài JSON."""
                try:
                    sub_reduced, _ = call_gemini_api(sub_prompt, api_key, model=actual_model_used)
                    if sub_reduced and isinstance(sub_reduced, dict):
                        grouped_chunks.append(sub_reduced)
                    else:
                        grouped_chunks.extend(sub_group)
                except Exception:
                    grouped_chunks.extend(sub_group)
            chunks_to_reduce = grouped_chunks

        reduce_prompt = f"""Bạn là trợ lý thẩm định báo cáo tiến độ khóa luận tốt nghiệp Khoa CNTT - HUIT.
Dưới đây là dữ liệu đã trích xuất từ các trang của báo cáo ({total_pages} trang).
Nhiệm vụ: Tổng hợp toàn diện, lọc trùng lặp, bảo toàn số trang trích dẫn chính xác và KHÔNG TỰ BỊA NỘI DUNG.

YÊU CẦU:
1. "tom_tat": Bản tóm tắt súc tích tình hình thực hiện trong 2-3 câu dựa đúng sự thật.
2. "cong_viec_hoan_thanh": Mảng các chuỗi công việc cốt lõi đã hoàn thành.
3. "ket_qua_dat_duoc": Mảng các sản phẩm/kết quả cụ thể đã bàn giao.
4. "kho_khan": Mảng các khó khăn thực tế. Nếu không có khó khăn nào được nhắc đến, để [].
5. "ke_hoach_tiep_theo": Mảng kế hoạch tuần tới hoặc giai đoạn sau.
6. "completion_percentage": Lấy giá trị phần trăm nếu tài liệu có ghi rõ; nếu không ghi, tính toán ước lượng hợp lý (0 đến 100) dựa trên khối lượng công việc thực tế, hoặc để null nếu không thể ước tính.
7. "completed_work", "deliverables", "difficulties", "pending_items", "next_steps":
   Mảng các đối tượng chứa {{"content": "...", "evidence": "...", "source_pages": [...]}}.
8. "tech_stack": Danh sách các công nghệ/ngôn ngữ được tài liệu nhắc đến.

DỮ LIỆU ĐÃ TRÍCH XUẤT:
{json.dumps(chunks_to_reduce, ensure_ascii=False)}

Trả về đúng JSON theo định dạng sau:
{{
  "tom_tat": "Tóm tắt tiến độ súc tích",
  "completion_percentage": null,
  "cong_viec_hoan_thanh": [],
  "ket_qua_dat_duoc": [],
  "kho_khan": [],
  "ke_hoach_tiep_theo": [],
  "completed_work": [],
  "deliverables": [],
  "difficulties": [],
  "pending_items": [],
  "next_steps": [],
  "tech_stack": []
}}
"""
        try:
            reduced, used_model = call_gemini_api(reduce_prompt, api_key, model=actual_model_used)
            actual_model_used = used_model
        except Exception as e:
            gemini_error_msg = str(e)
            reduced = None

    t_reduce = round(time.time() - t_reduce_start, 3)
    t_total = round(time.time() - t_start, 3)

    # If Gemini failed entirely or yielded empty results, fallback to NLP with clear note
    if not reduced or not isinstance(reduced, dict):
        pres_nlp["completed_work"] = verify_evidence_in_pages(pres_nlp.get("completed_work", []), pages_by_num)
        pres_nlp["deliverables"] = verify_evidence_in_pages(pres_nlp.get("deliverables", []), pages_by_num)
        pres_nlp["difficulties"] = verify_evidence_in_pages(pres_nlp.get("difficulties", []), pages_by_num)
        pres_nlp["pending_items"] = verify_evidence_in_pages(pres_nlp.get("pending_items", []), pages_by_num)
        pres_nlp["next_steps"] = verify_evidence_in_pages(pres_nlp.get("next_steps", []), pages_by_num)

        return {
            "success": True,
            "source": "fallback_nlp",
            "gemini_error": gemini_error_msg,
            "total_pages": total_pages,
            "metrics": {
                "total_pages": total_pages,
                "valid_pages": meta["valid_pages"],
                "char_count": meta["total_chars"],
                "word_count": meta["total_words"],
                "chunk_count": len(chunks),
                "chunk_size": chunk_size,
                "chunk_overlap": chunk_overlap,
                "method": "map_reduce_fallback",
                "model_used": "none",
                "extraction_time": meta["extraction_time"],
                "chunking_time": chunking_time,
                "map_time": t_map,
                "reduce_time": t_reduce,
                "processing_time": t_total,
                "citation_count": len(pres_nlp.get("citations", []))
            },
            "standard_report": standard_nlp,
            "data": pres_nlp
        }

    # ==================== VERIFY EVIDENCE CITATIONS ====================
    verified_completed = verify_evidence_in_pages(reduced.get("completed_work") or pres_nlp["completed_work"], pages_by_num)
    verified_deliverables = verify_evidence_in_pages(reduced.get("deliverables") or pres_nlp["deliverables"], pages_by_num)
    verified_difficulties = verify_evidence_in_pages(reduced.get("difficulties") or pres_nlp["difficulties"], pages_by_num)
    verified_pending = verify_evidence_in_pages(reduced.get("pending_items") or pres_nlp["pending_items"], pages_by_num)
    verified_next_steps = verify_evidence_in_pages(reduced.get("next_steps") or pres_nlp["next_steps"], pages_by_num)

    # Combine tech stack: union of Gemini detection and Regex verification
    combined_tech = set(reduced.get("tech_stack", [])) | set(pres_nlp.get("tech_stack", []))

    # Calculate verified citation rate
    all_citations = []
    for item in verified_completed + verified_deliverables + verified_difficulties:
        for p in item.get("source_pages", []):
            all_citations.append({
                "page": p,
                "topic": item.get("content", "")[:60],
                "snippet": item.get("evidence", "")[:120],
                "verified": item.get("verified", False)
            })

    # Summary
    summary_text = reduced.get("tom_tat") or pres_nlp["overall_summary"]
    pct = reduced.get("completion_percentage")
    if pct is None:
        pct = pres_nlp.get("completion_percentage")

    # Formulate standard report (Matching Section 3 research schema)
    std_report = {
        "tom_tat": summary_text,
        "cong_viec_hoan_thanh": reduced.get("cong_viec_hoan_thanh") or [i["content"] for i in verified_completed],
        "ket_qua_dat_duoc": reduced.get("ket_qua_dat_duoc") or [i["content"] for i in verified_deliverables],
        "kho_khan": reduced.get("kho_khan") or [i["content"] for i in verified_difficulties],
        "ke_hoach_tiep_theo": reduced.get("ke_hoach_tiep_theo") or [i["content"] for i in verified_next_steps],
        "moc_thoi_gian": standard_nlp["moc_thoi_gian"],
        "noi_dung_chua_ro": standard_nlp["noi_dung_chua_ro"],
        "canh_bao": standard_nlp["canh_bao"],
        "nguon_tham_chieu": standard_nlp["nguon_tham_chieu"]
    }

    # Formulate 11 UI sections
    presentation_data = {
        "overall_summary": summary_text,
        "completion_percentage": pct,
        "completed_work": verified_completed,
        "deliverables": verified_deliverables,
        "technical_links": pres_nlp["technical_links"],
        "tech_stack": sorted(list(combined_tech)),
        "difficulties": verified_difficulties,
        "pending_items": verified_pending,
        "next_steps": verified_next_steps,
        "missing_information": standard_nlp["noi_dung_chua_ro"],
        "citations": all_citations[:10] if all_citations else pres_nlp["citations"],
        "ai_recommendations": pres_nlp["ai_recommendations"]
    }

    return {
        "success": True,
        "source": "gemini_map_reduce",
        "total_pages": total_pages,
        "metrics": {
            "total_pages": total_pages,
            "valid_pages": meta["valid_pages"],
            "char_count": meta["total_chars"],
            "word_count": meta["total_words"],
            "chunk_count": len(chunks),
            "chunk_size": chunk_size,
            "chunk_overlap": chunk_overlap,
            "method": "gemini_map_reduce",
            "model_used": actual_model_used,
            "extraction_time": meta["extraction_time"],
            "chunking_time": chunking_time,
            "map_time": t_map,
            "reduce_time": t_reduce,
            "processing_time": t_total,
            "citation_count": len(presentation_data["citations"])
        },
        "standard_report": std_report,
        "data": presentation_data
    }


# ==============================================================================
# 6. COMMAND LINE ENTRYPOINT
# ==============================================================================

def main():
    parser = argparse.ArgumentParser(description="PDF Progress Report Scientific AI Summarizer (Map-Reduce & Gemini API)")
    parser.add_argument("--pdf", required=True, help="Đường dẫn đến tệp PDF báo cáo")
    parser.add_argument("--api-key", default=None, help="Google Gemini API Key")
    parser.add_argument("--model", default="gemini-3.5-flash", help="Mã model Gemini API (ví dụ: gemini-3.5-flash, gemini-3.5-flash-lite)")
    parser.add_argument("--stage", type=int, default=1, help="Giai đoạn tiến độ hiện tại (1 đến 6)")
    parser.add_argument("--chunk-size", type=int, default=3500, help="Kích thước chunk theo số ký tự")
    parser.add_argument("--chunk-overlap", type=int, default=350, help="Độ chồng lặp giữa các chunk")
    parser.add_argument("--method", default="map_reduce", choices=["map_reduce", "heuristic_nlp"], help="Phương pháp xử lý")
    parser.add_argument("--output", default=None, help="Đường dẫn file JSON xuất ra (tùy chọn)")

    args = parser.parse_args()

    try:
        result = run_map_reduce_pipeline(
            args.pdf,
            api_key=args.api_key,
            model=args.model,
            stage=args.stage,
            chunk_size=args.chunk_size,
            chunk_overlap=args.chunk_overlap,
            method=args.method
        )
        json_str = json.dumps(result, ensure_ascii=False, indent=2)

        if args.output:
            with open(args.output, "w", encoding="utf-8") as f:
                f.write(json_str)
        else:
            print(json_str)
    except Exception as e:
        err_res = {
            "success": False,
            "error": str(e),
            "metrics": {
                "processing_time": 0
            },
            "standard_report": None,
            "data": None
        }
        print(json.dumps(err_res, ensure_ascii=False, indent=2))
        sys.exit(1)


if __name__ == "__main__":
    main()
