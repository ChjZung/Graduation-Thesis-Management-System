"""Targeted quality checks, not a general semantic verifier."""
import re
import unicodedata
from .provider import ApiError


def normalized(text):
    return ' '.join(unicodedata.normalize('NFC',text).casefold().split())

EXPECTED = re.compile(r'\bsẽ\b|kỳ vọng|dự kiến|mong đợi|mục tiêu|nhằm|đề xuất')
OBSERVED = re.compile(r'được ghi nhận|đã (?:góp phần|giúp|cải thiện|nâng cao|giảm|tăng)|kết quả (?:cho thấy|chứng minh)|đã mang lại')
BENEFIT = re.compile(r'hiệu quả|hiệu năng|thao tác|chính xác|minh bạch|thời gian|chi phí|tốc độ')


def output_features(item):
    if item.get('kind') not in ('reported_work','reported_result') or item.get('classification_flags'):
        return []
    quote=normalized(item.get('evidence_quote',''))
    if EXPECTED.search(quote): return []
    found=re.search(r'chức năng(?: chính)?\s+(?:như|bao gồm)\s+([^.;\n]+)',quote)
    if not found: return []
    phrases=[]
    for part in re.split(r',|\s+và\s+',found.group(1)):
        part=part.strip(' :–-')
        if 2<=len(part.split())<=10 and part not in phrases: phrases.append(part)
    return phrases if len(phrases)>=3 else []


def priority_score(item):
    features=output_features(item)
    if features: return 100+len(features)
    if item.get('kind') not in ('reported_work','reported_result'): return 0
    text=normalized(item.get('summary',''))
    if re.search(r'cài đặt|composer|npm|git|thư viện|môi trường|cấu hình',text): return 1
    return 10


def required_outputs(allowed,evidence):
    # One explicit function list is enough to identify the principal deliverable.
    candidates=[(iid,output_features(evidence[iid])) for iid in allowed if iid in evidence]
    candidates=[(iid,parts) for iid,parts in candidates if parts]
    if not candidates:return None
    iid,parts=max(candidates,key=lambda pair:(len(pair[1]),pair[0]))
    return {'evidence_id':iid,'features':parts,'minimum_features':min(3,len(parts))}


def feature_matches(text,features):
    text=normalized(text)
    def simplify(value):
        return normalized(re.sub(r'\b(?:khóa luận|hệ thống|công tác)\b','',value))
    return [f for f in features if simplify(f) in simplify(text)]


def validate_quality(data,allowed,evidence,require_outputs=True):
    for paragraph in data['paragraphs']:
        text=normalized(paragraph['text'])
        quotes=[normalized(evidence[i]['evidence_quote']) for i in paragraph['evidence_ids'] if i in evidence]
        if (BENEFIT.search(text) and OBSERVED.search(text)
                and any(EXPECTED.search(q) and BENEFIT.search(q) for q in quotes)
                and not any(OBSERVED.search(q) and BENEFIT.search(q) for q in quotes)):
            raise ApiError('Bản đọc biến lợi ích kỳ vọng thành kết quả đã ghi nhận. Cần giữ đúng mức khẳng định của nguồn.', 'reader_assertion_strength')
    requirement=required_outputs(allowed,evidence) if require_outputs else None
    if requirement:
        text=' '.join(p['text'] for p in data['paragraphs'])
        cited={i for p in data['paragraphs'] for i in p['evidence_ids']}
        if (requirement['evidence_id'] not in cited or
                len(feature_matches(text,requirement['features']))<requirement['minimum_features']):
            raise ApiError('Bản đọc bỏ sót các chức năng chính tác giả báo cáo đã xây dựng; không ưu tiên thao tác cài đặt thay cho đầu ra.', 'reader_missing_key_outputs')
    return data
