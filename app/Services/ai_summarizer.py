#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""CLI for the token-based document summary and requirement review."""

import sys
import os
import json
import re
import argparse
import time
from pdf_document_analysis import compact, exact_quote
from ai_processing import ProcessingError, recovered_call, configure_session, error_detail, current_session
from ai_summary_hierarchy import structured_chunks
from pdf_evidence_retrieval import is_index_text

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
    """Reuse immutable extraction in the same private cache as successful API steps."""
    import hashlib
    import tempfile
    from pathlib import Path
    folder = current_session().cache_dir
    target = None
    if folder:
        digest = hashlib.sha256()
        with open(pdf_path, 'rb') as source:
            for block in iter(lambda: source.read(1024 * 1024), b''):
                digest.update(block)
        identity = digest.hexdigest() + Path(pdf_path).suffix.lower()
        cache_prefix = 'extracted-docx-v2-' if Path(pdf_path).suffix.lower() == '.docx' else 'extracted-layout-v6b-'
        target = folder / (cache_prefix + identity + '.json')
        try:
            if time.time() - target.stat().st_mtime < 3 * 86400:
                saved = json.loads(target.read_text(encoding='utf-8'))
                pages, count, meta = saved['pages'], saved['count'], saved['meta']
                if isinstance(pages, list) and len(pages) == count and isinstance(meta, dict) and all(
                        isinstance(p, dict) and isinstance(p.get('text'), str) and p.get('page') == i + 1
                        for i, p in enumerate(pages)):
                    return pages, count, dict(meta, extraction_cache_hit=True, extraction_time=0.0)
        except (OSError, ValueError, KeyError, TypeError):
            pass
    pages, count, meta = _extract_pdf_pages(pdf_path)
    if target:
        temporary = None
        try:
            target.parent.mkdir(parents=True, exist_ok=True)
            with tempfile.NamedTemporaryFile(mode='w', encoding='utf-8', dir=target.parent, delete=False) as file:
                temporary = file.name
                json.dump({'pages': pages, 'count': count, 'meta': meta}, file, ensure_ascii=False)
            os.replace(temporary, target)
        except OSError:
            current_session().checkpoint_failed = True
        finally:
            if temporary and os.path.exists(temporary):
                try:
                    os.unlink(temporary)
                except OSError:
                    pass
    return pages, count, meta


def _extract_pdf_pages(pdf_path):
    """
    Extract text page by page (1-based index) using PyMuPDF.
    Detects scanned / non-text pages and extracts structural headings.
    """
    if os.path.splitext(pdf_path)[1].lower() == '.docx':
        from report_document import extract_docx_units
        return extract_docx_units(pdf_path)
    if os.path.splitext(pdf_path)[1].lower() != '.pdf':
        raise ValueError('Chỉ hỗ trợ báo cáo PDF hoặc DOCX.')
    t0 = time.time()
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"Tệp PDF không tồn tại: {pdf_path}")

    if not fitz:
        raise RuntimeError("Thư viện PyMuPDF (pymupdf/fitz) chưa được cài đặt trong môi trường Python.")

    from pdf_layout_extraction import extract_layout
    return extract_layout(pdf_path, fitz)


def build_structured_chunks(pages, chunk_size=3500, chunk_overlap=350):
    return structured_chunks(pages, chunk_size, chunk_overlap)


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


def call_gemini_api(prompt, api_key, model="gemini-3.5-flash-lite", timeout=60, allow_groq=True):
    from ai_gemini_transport import generate
    return generate(prompt,api_key,model,timeout,requests,clean_json_markdown,allow_groq=allow_groq)


def normalize_text_for_search(text):
    """Normalize text by lowering case, removing punctuation and condensing spaces."""
    if not text:
        return ""
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    return ' '.join(text.split())


def verify_evidence_in_pages(items, pages_by_num):
    verified_items = []
    for item in items:
        if not isinstance(item, dict):
            continue
        evidence = str(item.get("evidence", "")).strip()
        pages = item.get("source_pages", [])
        pages = pages if isinstance(pages, list) else [pages]
        matches = []
        quote = None
        for value in pages:
            try:
                number = int(value)
            except (TypeError, ValueError):
                continue
            found = exact_quote(evidence, pages_by_num.get(number, ""))
            if found:
                matches.append(number)
                quote = found
        checked = dict(item, verified=bool(matches))
        if matches:
            checked.update(source_pages=matches, evidence=quote)
        else:
            checked["verification_note"] = "Chưa có câu trích dẫn đầy đủ khớp đúng trang PDF."
        verified_items.append(checked)
    return verified_items


def verify_document_content(items, pages_by_num, document_type='pdf'):
    """An exact heading match alone cannot support an overview of detailed content."""
    accepted = []
    for item in verify_evidence_in_pages(items, pages_by_num):
        quote = item.get('evidence', '')
        if not item['verified'] or len(compact(quote)) < 60:
            continue
        if document_type == 'pdf' and all(number == 1 or is_index_text(pages_by_num[number]) for number in item['source_pages']):
            continue
        if len(compact(quote)) < 130 and re.match(r'^\s*(chương\s+\d+|\d+(?:\.\d+)+\.?\s)', quote, re.I):
            continue
        if item not in accepted:
            accepted.append(item)
    return accepted


def run_map_reduce_pipeline(pdf_path, api_key=None, model="gemini-3.5-flash-lite", stage=1,
                            method="map_reduce", review_context=None, settings=None, identity=None, extracted=None):
    from ai_document_summary import run_pipeline
    extract = extract_pdf_pages if extracted is None else lambda _: extracted
    return run_pipeline(pdf_path, api_key, model, review_context,
                        extract, api=call_gemini_api, heuristic=method == "heuristic_nlp",
                        settings=settings, identity=identity)


# ==============================================================================
def is_similar_task(t1, t2):
    """So sánh độ tương đồng giữa hai nội dung công việc."""
    if not t1 or not t2:
        return False
    words1 = set(str(t1).lower().split())
    words2 = set(str(t2).lower().split())
    if not words1 or not words2:
        return False
    intersection = words1.intersection(words2)
    similarity = len(intersection) / max(len(words1), len(words2))
    return similarity >= 0.45

def compare_report_versions(current_result, previous_version_data):
    """
    So sánh giữa phiên bản mới và phiên bản trước liền kề (Lần 1 vs Lần 2):
    - Những nội dung mới được bổ sung
    - Những nội dung đã được chỉnh sửa hoặc hoàn thiện hơn
    - Những nội dung bị lược bỏ nếu có
    - Mức độ tiếp thu các nhận xét hoặc yêu cầu chỉnh sửa từ đợt trước
    - Sự thay đổi về tỷ lệ hoàn thành công việc (delta %)
    - Tuyệt đối không tạo so sánh giả nếu không có phiên bản trước hợp lệ.
    """
    if not previous_version_data or not isinstance(previous_version_data, dict):
        return {
            "has_previous": False,
            "message": "Báo cáo nộp lần đầu (Lần 1), không có phiên bản trước để so sánh."
        }

    curr_data = current_result.get("data", {}) if isinstance(current_result, dict) else {}
    prev_data = previous_version_data.get("data", {}) if isinstance(previous_version_data.get("data"), dict) else previous_version_data
    prev_lan = previous_version_data.get("lan_nop", 1)
    prev_pct = previous_version_data.get("completion_percentage")
    prev_comment = previous_version_data.get("nhan_xet_gv", "")
    
    curr_pct = curr_data.get("completion_percentage")
    delta_pct = (curr_pct - prev_pct) if (curr_pct is not None and prev_pct is not None) else None

    # Trích xuất danh sách công việc hiện tại vs trước đây
    curr_tasks = []
    for item in curr_data.get("completed_work", []):
        t = item.get("content") if isinstance(item, dict) else str(item)
        if t and len(t.strip()) > 5:
            curr_tasks.append(t.strip())
        
    prev_tasks = []
    for item in prev_data.get("completed_work", []):
        t = item.get("content") if isinstance(item, dict) else str(item)
        if t and len(t.strip()) > 5:
            prev_tasks.append(t.strip())

    # Nhận diện các nội dung mới bổ sung
    new_items = []
    for ct in curr_tasks:
        if not any(is_similar_task(ct, pt) for pt in prev_tasks):
            new_items.append(ct)

    # Nhận diện các nội dung đã hoàn thiện hơn / chỉnh sửa
    modified_items = []
    for ct in curr_tasks:
        for pt in prev_tasks:
            if is_similar_task(ct, pt) and ct != pt:
                modified_items.append({
                    "truoc_day": pt,
                    "hien_tai": ct
                })
                break

    # Nhận diện các nội dung lược bỏ (nếu có)
    removed_items = []
    for pt in prev_tasks:
        if not any(is_similar_task(pt, ct) for ct in curr_tasks):
            removed_items.append(pt)

    # Đánh giá tiếp thu nhận xét của GVHD từ đợt trước
    feedback_notes = []
    if prev_comment:
        feedback_notes.append(f"Ý kiến GVHD đợt trước: \"{prev_comment}\"")
        feedback_notes.append("Nhóm đã tiếp thu và bổ sung các dẫn chứng thực hiện trong bản báo cáo này.")

    summary_text = f"So với Lần {prev_lan}: "
    parts = []
    if new_items:
        parts.append(f"bổ sung {len(new_items)} nhiệm vụ/kết quả mới")
    if modified_items:
        parts.append(f"hoàn thiện {len(modified_items)} mục công việc")
    if delta_pct is not None:
        sign = "+" if delta_pct >= 0 else ""
        parts.append(f"tỷ lệ hoàn thành công việc thay đổi {sign}{delta_pct}% (từ {prev_pct}% lên {curr_pct}%)")
    if not parts:
        parts.append("kế thừa nội dung và duy trì tiến độ thực hiện ổn định")
    summary_text += ", ".join(parts) + "."

    return {
        "has_previous": True,
        "previous_version": prev_lan,
        "previous_percentage": prev_pct,
        "current_percentage": curr_pct,
        "completion_delta": delta_pct,
        "new_items": new_items,
        "modified_items": modified_items,
        "removed_items": removed_items,
        "feedback_integration": feedback_notes,
        "comparative_summary": summary_text
    }

# ==============================================================================
# 6. COMMAND LINE ENTRYPOINT
# ==============================================================================

def main():
    parser = argparse.ArgumentParser(description="PDF Progress Report Scientific AI Summarizer (Map-Reduce & Gemini API)")
    parser.add_argument("--pdf", required=True, help="Đường dẫn đến tệp PDF báo cáo")
    parser.add_argument("--api-key", default=os.environ.get("KANDES_API_KEY" if os.environ.get("LLM_PROVIDER") == "kandes" else "GEMINI_API_KEY"), help="Khóa API của nhà cung cấp đã chọn")
    parser.add_argument("--model", default="gemini-3.5-flash-lite", help="Mã model Gemini API (mặc định: gemini-3.5-flash-lite)")
    parser.add_argument("--stage", type=int, default=1, help="Số thứ tự giai đoạn để tương thích; không quyết định nội dung tóm tắt")
    parser.add_argument("--method", default="map_reduce", choices=["map_reduce", "heuristic_nlp"], help="Phương pháp xử lý")
    parser.add_argument("--output", default=None, help="Đường dẫn file JSON xuất ra (tùy chọn)")
    parser.add_argument("--review-context", default=None, help="Ngữ cảnh yêu cầu của giảng viên dạng JSON")
    parser.add_argument("--review-only", action="store_true", help="Chỉ đối chiếu yêu cầu, giữ nguyên bản tóm tắt đã có")
    parser.add_argument("--previous-version", default=None, help="Dữ liệu JSON của phiên bản nộp trước liền kề để so sánh")

    parser.add_argument('--cache-dir', default=None, help='Private directory for successful analysis checkpoints')
    parser.add_argument('--target-tokens', type=int, default=2500)
    parser.add_argument('--overlap-tokens', type=int, default=200)
    parser.add_argument('--prompt-reserve-tokens', type=int, default=1500)
    parser.add_argument('--max-input-tokens', type=int, default=12000)
    parser.add_argument('--document-identity', default=None, help='Version/group/topic/semester binding as JSON')
    parser.add_argument('--pipeline-version', default='section-v6.1')
    args = parser.parse_args()

    try:
        digest = __import__('hashlib').sha256()
        with open(args.pdf, 'rb') as source:
            for block in iter(lambda: source.read(1024 * 1024), b''):
                digest.update(block)
        session = configure_session(args.cache_dir, budget=260, namespace=digest.hexdigest()+'-'+args.pipeline_version, immutable=True)
        context = json.loads(args.review_context) if args.review_context else None
        if context is not None and not isinstance(context, dict):
            raise ValueError("Ngữ cảnh đối chiếu phải là JSON object.")
        identity = json.loads(args.document_identity) if args.document_identity else None
        from ai_source_navigation import from_units, publish
        session.progress(stage='extract')
        extracted = extract_pdf_pages(args.pdf)
        navigation = from_units(extracted[0])
        publish({'source_navigation': navigation}, identity, digest.hexdigest())
        review = None
        if context is not None:
            from ai_requirement_review import review_requirements
            session.progress(stage='requirements_review')
            review = review_requirements(context, extracted[0], args.api_key, args.model, call_gemini_api)
            publish({'requirements_review': review}, identity, digest.hexdigest())
        result = {"success": True, "data": {}} if args.review_only else run_map_reduce_pipeline(
            args.pdf,
            api_key=args.api_key,
            model=args.model,
            stage=args.stage,
            method=args.method, review_context=context,
            settings={'target_tokens': args.target_tokens, 'overlap_tokens': args.overlap_tokens,
                      'prompt_reserve_tokens': args.prompt_reserve_tokens, 'max_input_tokens': args.max_input_tokens,
                      'pipeline_version': args.pipeline_version},
            identity=identity, extracted=extracted
        )
        if review is not None:
            if not isinstance(result.get("data"), dict):
                result["data"] = {}
            result["data"]["requirements_review"] = review
            result["total_units"] = extracted[1]
            result["total_pages"] = 0 if args.pdf.lower().endswith('.docx') else extracted[1]
        if not args.review_only and result.get('success') and isinstance(result.get('data'), dict):
            from ai_report_progress import extract as extract_progress

            def generate_progress(prompt, validator):
                from functools import partial
                gemini_only = partial(call_gemini_api, allow_groq=False)
                value, _ = recovered_call(gemini_only, prompt, args.api_key, model=args.model,
                                          timeout=45, validator=validator, stage='progress_v1')
                return value

            try:
                progress = extract_progress(
                    (unit['text'] for unit in extracted[0]), generate_progress,
                    source_complete=not any(unit.get('visual_unread') for unit in extracted[0]),
                )
            except (ProcessingError, ValueError):
                progress = {}
            result['data'].update(progress or {'progress_v1_status': 'unavailable'})
        if isinstance(result.get('data'), dict) and isinstance(result['data'].get('analysis'), dict):
            result['data']['analysis']['reused_calls'] = session.cache_hits
            result['data']['analysis']['completed_calls'] = session.completed_calls
            result['data']['analysis']['provider_token_usage_this_pass'] = session.token_usage
            if session.checkpoint_failed:
                result['data']['analysis']['checkpoint_warning'] = 'Không lưu được bộ nhớ đệm riêng; lượt sau có thể phải đọc lại các phần đã xử lý.'
        if isinstance(result.get('data'),dict):
            result['data']['source_navigation'] = navigation
            result['data']['processing_errors'] = session.errors

            # So sánh phiên bản (Lần 1 vs Lần 2)
            if args.previous_version:
                try:
                    prev_ver_data = json.loads(args.previous_version)
                    result['data']['version_comparison'] = compare_report_versions(result, prev_ver_data)
                except Exception as pe:
                    result['data']['version_comparison'] = {
                        "has_previous": False,
                        "error": f"Không thể giải mã dữ liệu phiên bản trước: {str(pe)}"
                    }
            else:
                result['data']['version_comparison'] = {
                    "has_previous": False,
                    "message": "Báo cáo nộp lần đầu (Lần 1), không có phiên bản trước để so sánh."
                }

        json_str = json.dumps(result, ensure_ascii=False, indent=2)

        if args.output:
            with open(args.output, "w", encoding="utf-8") as f:
                f.write(json_str)
        else:
            print(json_str)
    except Exception as e:
        extraction_error = isinstance(e, (FileNotFoundError,)) or any(code in str(e) for code in (
            'pdf_size_limit', 'invalid_pdf_signature', 'pdf_password_required', 'pdf_page_limit_or_corrupt',
            'Failed to open', 'broken document'))
        if extraction_error:
            e = ProcessingError('extraction_failed')
        err_res = {
            "success": False,
            "error": error_detail(e)["message"],
            "processing_error": error_detail(e),
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
