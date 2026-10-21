"""Render one actual PDF page with an exact quote highlighted; never modify the source PDF."""
import argparse
import base64
import json
import re
import unicodedata
try:
    import pymupdf as fitz
except ImportError:
    import fitz


def normalized(text):
    return " ".join(unicodedata.normalize("NFKC", str(text)).replace("\u00ad", "").casefold().split())


def quote_rects(page, quote):
    if len(normalized(quote)) < 12:
        return []
    # Try the PDF text engine first (handles quotes spanning lines).
    rects = page.search_for(quote)
    if rects:
        return rects
    # Whitespace / punctuation can differ after PDF extraction. Locate complete word sequences.
    words = page.get_text("words", sort=True)
    tokens = [normalized(w[4]) for w in words]
    target = normalized(quote)
    joined = " ".join(tokens)
    start = joined.find(target)
    if start < 0:
        return []
    end = start + len(target)
    offset, hits = 0, []
    for word, token in zip(words, tokens):
        if offset < end and offset + len(token) > start:
            hits.append(fitz.Rect(word[:4]))
        offset += len(token) + 1
    return hits


def render_citation(pdf_path, page_number, quote=""):
    with fitz.open(pdf_path) as doc:
        if page_number < 1 or page_number > len(doc):
            raise ValueError("Số trang nằm ngoài phạm vi tài liệu PDF.")
        page = doc[page_number - 1]
        rects = quote_rects(page, quote) if quote else []
        display_rects = [r * page.rotation_matrix for r in rects]
        if rects:
            annotation = page.add_highlight_annot(rects)
            annotation.set_colors(stroke=(1, .82, .12))
            annotation.set_opacity(.5)
            annotation.update()
            page = doc.reload_page(page)
        scale = min(1.7, 1600 / max(page.rect.width, page.rect.height, 1))
        pixmap = page.get_pixmap(matrix=fitz.Matrix(scale, scale), alpha=False, annots=True)
        return {
            "page": page_number, "total_pages": len(doc), "matched": bool(rects),
            "focus_y": min((r.y0 for r in display_rects), default=0) / max(page.rect.height, 1),
            "image": "data:image/png;base64," + base64.b64encode(pixmap.tobytes("png")).decode("ascii"),
            "width": pixmap.width, "height": pixmap.height,
        }


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--pdf", required=True)
    parser.add_argument("--page", type=int, required=True)
    parser.add_argument("--quote", default="")
    args = parser.parse_args()
    try:
        print(json.dumps(render_citation(args.pdf, args.page, args.quote), ensure_ascii=False))
    except Exception:
        print(json.dumps({"error": "Không thể dựng trang PDF hoặc xác định đoạn trích dẫn."}, ensure_ascii=False))
        raise SystemExit(1)
