"""Page-preserving retrieval. Presence of documentation is not proof of running code."""
import re
import unicodedata


def compact(text):
    return re.sub(r"\s+", "", unicodedata.normalize("NFKC", str(text)).casefold()).replace("\u00ad", "")


def exact_quote(quote, text):
    """Accept PDF spacing/line-wrap differences, never a matching prefix alone."""
    normalized = unicodedata.normalize("NFKC", str(text))
    needle = compact(quote)
    if len(needle) < 12:
        return None
    chars, offsets = [], []
    for index, char in enumerate(normalized):
        if char.isspace() or char == "\u00ad":
            continue
        for folded in char.casefold():
            chars.append(folded)
            offsets.append(index)
    start = "".join(chars).find(needle)
    if start < 0:
        return None
    return normalized[offsets[start]:offsets[start + len(needle) - 1] + 1]


PART_LABELS = {"actor": "tác nhân", "precondition": "tiền điều kiện", "main_flow": "luồng hoạt động chính"}
FIELD = re.compile(r"^\s*[-•]?\s*(Actor\s+chính|Actor\s+phụ|Tiền\s*điều\s*kiện|Hậu\s*điều\s*kiện|Luồng\s*hoạt\s*động\s*chính|Luồng\s*thay\s*thế|Luồng\s*ngoại\s*lệ)\s*:", re.I)
USECASE = re.compile(r"^\s*(?:\d+(?:\.\d+)+\.?\s*)?Use\s*case\s+(.+?)\s*$", re.I)
SECTION = re.compile(r"^\s*\d+(?:\.\d+)+\.?\s+\D")


def usecase_criteria(text):
    """Attach shared constraints to EACH enumerated Use Case, not an orphan final item."""
    if "usecase" not in compact(text):
        return None
    common = re.search(r"(?:Đối\s+với|Với)\s+(?:từng|mỗi)\s+Use\s*Case\b.*", text, re.I | re.S)
    if not common:
        return None
    shared = common.group().strip()
    body = text[:common.start()]
    entries = re.split(r"(?<!\w)\d+[.)]\s+", body)[1:]
    if not entries:
        return None
    required = []
    shared_compact = compact(shared)
    for key, terms in {"actor": ["tác nhân", "actor"], "precondition": ["tiền điều kiện"], "main_flow": ["luồng hoạt động chính", "luồng chính"]}.items():
        if any(compact(term) in shared_compact for term in terms):
            required.append(key)
    # This deterministic check is deliberately limited to the documented three-field rubric.
    residue = shared_compact
    allowed = ['đối với từng Use Case', 'đối với mỗi Use Case', 'với từng Use Case', 'với mỗi Use Case',
               'tác nhân thực hiện', 'tác nhân chính', 'tác nhân', 'actor', 'tiền điều kiện',
               'luồng hoạt động chính', 'luồng chính', 'báo cáo', 'trình bày', 'bao gồm', 'cần', 'phải', 'nêu', 'có', 'và']
    for phrase in sorted(allowed, key=lambda s: len(compact(s)), reverse=True):
        residue = residue.replace(compact(phrase), '')
    structural_only = set(required) == set(PART_LABELS) and not re.sub(r'[\W_]+', '', residue)
    criteria = []
    for entry in entries:
        name = entry.strip().strip(".;: ")
        if not name or len(name) > 150 or "\n\n" in name:
            return None
        criteria.append({"id": len(criteria) + 1, "requirement": f"Đặc tả Use Case {name}. {shared}",
                         "usecase_title": name, "required_parts": required, "structural_only": structural_only})
    return criteria


def usecase_sections(pages):
    records = [(int(p["page"]), line) for p in pages for line in p.get("text", "").splitlines()]
    sections = []
    for index, (page, line) in enumerate(records):
        match = USECASE.match(line)
        if not match or ":" in match.group(1) or re.search(r"\.{3,}", line):
            continue
        end = index + 1
        while end < len(records) and not (USECASE.match(records[end][1]) or SECTION.match(records[end][1])):
            end += 1
        sections.append({"title": match.group(1).strip(), "page": page, "records": records[index:end]})
    return sections


def retrieve_usecase(criterion, sections):
    title = compact(criterion["usecase_title"])
    matches = [s for s in sections if compact(s["title"]) == title]
    if len(matches) != 1:
        return None, []
    section = matches[0]
    grouped = {}
    active = None
    meaningful = set()
    def has_value(value):
        value = re.sub(r'^\s*\d+[.)]\s*', '', value).strip(' .;-+•')
        return len(compact(value)) >= 2 and not any(compact(marker) in compact(value) for marker in ['TODO', 'TBD', 'chưa xác định', 'sẽ bổ sung', 'đang bổ sung'])
    for page, line in section["records"]:
        field = FIELD.match(line)
        if field:
            label = compact(field.group(1))
            active = {"actorchính": "actor", "tiềnđiềukiện": "precondition", "luồnghoạtđộngchính": "main_flow"}.get(label)
            remainder = line[field.end():].strip()
            if active and has_value(remainder):
                meaningful.add(active)
        elif active and has_value(line) and not line.strip().isdigit():
            meaningful.add(active)
        if active and line.strip() and not line.strip().isdigit():
            grouped.setdefault((active, page), []).append(line)
    evidence = []
    for (part, page), lines in grouped.items():
        quote = "\n".join(lines).strip()
        if part in meaningful and len(compact(quote)) >= 12:
            evidence.append({"page": page, "quote": quote, "part": part, "verified": True})
    return section, evidence


def structural_usecase_review(criteria, pages):
    """Only answer presence of actor/precondition/main flow; do not grade their quality."""
    sections = usecase_sections(pages)
    items, retrieved_pages = [], set()
    for criterion in criteria:
        section, evidence = retrieve_usecase(criterion, sections)
        evidence = [e for e in evidence if exact_quote(e["quote"], next(p["text"] for p in pages if p["page"] == e["page"]))]
        present = {e["part"] for e in evidence}
        missing = set(criterion["required_parts"]) - present
        retrieved_pages.update(e["page"] for e in evidence)
        if section and not missing:
            status = "met"
            explanation = f"Tìm thấy phần đặc tả Use Case {criterion['usecase_title']} và đủ tác nhân, tiền điều kiện, luồng hoạt động chính. Đây là kiểm tra sự hiện diện của tài liệu, không xác nhận phần mềm chạy đúng."
        elif section and evidence:
            status = "partial"
            explanation = "Đã tìm đúng phần đặc tả; chưa xác minh được: " + ", ".join(PART_LABELS[k] for k in criterion["required_parts"] if k in missing) + "."
        else:
            status = "insufficient_evidence"
            explanation = "Chưa định vị được một phần đặc tả rõ ràng có đủ bằng chứng. Không kết luận sinh viên chưa làm; cần kiểm tra PDF hoặc hình ảnh."
        items.append({"id": criterion["id"], "requirement": criterion["requirement"], "status": status, "explanation": explanation, "evidence": evidence})
    return items, sorted(retrieved_pages)


def document_structure(pages):
    """Extract document contents and author-reported conclusions, never infer execution."""
    outline, deliverables, completed, difficulties, next_steps = [], [], [], [], []
    category = None
    for page in pages:
        text = page.get("text", "")
        # Contents pages are pointers, not proof that a described section exists.
        if compact("MỤC LỤC") in compact(text) or text.count("....") > 3:
            continue
        lines = text.splitlines()
        blocks, buffer = [], []
        def flush():
            if buffer:
                blocks.append((category, "\n".join(buffer).strip()))
                buffer.clear()
        for line in lines:
            stripped = line.strip()
            if not stripped or stripped.isdigit():
                continue
            folded = compact(stripped)
            if folded in ("tàiliệuthamkhảo", "phụlục", "mụclục"):
                flush()
                category = None
            if re.match(r"^(chương|chapter)\s*\d+[.:]?", stripped, re.I):
                flush()
                category = None
                outline.append({"content": stripped, "evidence": stripped, "source_pages": [page["page"]], "verified": True})
            if SECTION.match(stripped):
                flush()
                if "kếtquảđạtđược" in folded:
                    category = "completed"
                elif "hạnchế" in folded or "khókhăn" in folded:
                    category = "difficulties"
                elif "hướngpháttriển" in folded or "kếhoạchtiếptheo" in folded:
                    category = "next_steps"
                else:
                    category = None
                if any(term in folded for term in ["đặctảusecase", "thiếtkếcơsởdữliệu", "cácbảngtrongcơsởdữliệu", "cấuhìnhcàiđặt", "sơđồtuầntự", "sơđồlớp"]):
                    deliverables.append({"content": "Tài liệu có phần: " + stripped, "evidence": stripped, "source_pages": [page["page"]], "verified": True})
                continue
            if stripped.startswith(("-", "+", "•")):
                flush()
            if category:
                buffer.append(stripped)
        flush()
        for kind, quote in blocks:
            item = {"content": " ".join(quote.split()), "evidence": quote, "source_pages": [page["page"]], "verified": True}
            if kind == "completed" and any(w in compact(quote) for w in ["emđã", "nhómđã", "chúngtôiđã", "đãcơbảnhoànthành"]):
                completed.append(item)
            elif kind == "difficulties":
                difficulties.append(item)
            elif kind == "next_steps":
                next_steps.append(item)
    return {"document_outline": outline, "deliverables": deliverables, "completed_work": completed,
            "difficulties": difficulties, "next_steps": next_steps}
