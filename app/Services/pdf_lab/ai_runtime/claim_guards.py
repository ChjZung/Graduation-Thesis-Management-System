"""Conservative evidence guards for observed false completed-work claims.

These rules do not establish semantic entailment or independent implementation.
They review the evidence text, rather than trusting verbs invented in a summary.
"""
import re
import unicodedata


def normalized(text):
    return ' '.join(unicodedata.normalize('NFKC', text).casefold().split())


def completion_qualifier(quote):
    return 'partial' if re.search(r'cơ\s*bản\s*hoàn\s*thành|bước\s*đầu|hoàn\s*thành\s*một\s*phần|partially|mostly\s+completed', normalized(quote)) else None


def review_completed_evidence(kind, quote, section_title='', path=()):
    if kind not in ('reported_work', 'reported_result'):
        return kind, []
    context = normalized(' '.join([section_title, *(path or [])]))
    evidence = normalized(quote)
    if re.search(r'hạn\s*chế|khó\s*khăn|limitations|difficulties', context):
        return 'other', ['limitation_not_completed_work']
    if re.search(r'giao\s*diện\s*chưa\s*(?:chỉnh|cân|hài|hoàn)|chưa\s*đạt\s*yêu\s*cầu', evidence):
        return 'other', ['limitation_not_completed_work']
    specification = re.search(r'\b(?:cpu|ram|hardware)\b|\d\s*(?:gb|mb|ghz)\b|bộ\s*nhớ|dung\s*lượng', evidence)
    # A source statement that actually reports installation/configuration must
    # survive even when its section contains hardware requirements.
    performed = re.search(r'đã\s*(?:cài\s*đặt|triển\s*khai|cấu\s*hình|chọn|đo|kiểm\s*thử|thử\s*nghiệm|tối\s*ưu|xây\s*dựng)|được\s*(?:cài\s*đặt|triển\s*khai|cấu\s*hình)|(?:nhóm|tác\s*giả|chúng\s*tôi|em)\s*(?:đã\s*)?(?:cài\s*đặt|triển\s*khai|cấu\s*hình)|(?:was|were|has\s+been|have\s+been)\s+(?:installed|configured|deployed|tested)|(?:we|the\s+author)\s+(?:installed|configured|deployed|tested)', evidence)
    if specification and not performed:
        return 'other', ['reference_specification_not_work']
    quality = re.search(r'giao\s*diện\s*đơn\s*giản|dễ\s*(?:dàng\s*)?(?:thao\s*tác|sử\s*dụng)|nhiều\s*tính\s*năng\s*tiện|simple\s+interface|easy\s+to\s+use|many\s+convenient\s+features', evidence)
    measured = re.search(r'\d+(?:[.,]\d+)?\s*(?:%|ms\b|giây|seconds)|kết\s*quả\s*(?:đo|kiểm)|đã\s*(?:kiểm\s*thử|đánh\s*giá)|\b(?:tested|measured|evaluated)\b', evidence)
    if quality and not measured:
        return 'other', ['unmeasured_quality_description']
    return kind, []


def review_pending_claim(summary, quote):
    """Review only this claim/evidence, never unrelated chapter/fragment text.

    ASCII TODO/TBD labels still work when Vietnamese glyphs in a PDF are broken.
    Do not reconstruct damaged Vietnamese or treat every mention of a Todo app
    as an unfinished task. A label has field/list/start syntax or :/( suffix.
    """
    for value in (quote, summary):
        for line in unicodedata.normalize('NFKC', value).casefold().splitlines():
            label = re.search(r'(?:^\s*(?:[-*•]\s*)?|[:;(]\s*)(?:todo|tbd)\b|\b(?:todo|tbd)\s*[:(]', line)
            if label:
                return 'design', ['placeholder_not_completed_work']
    return None
