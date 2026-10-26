import sys
sys.path.insert(0, 'app/Services')
from ai_summarizer import compare_report_versions

# Test 1: Lần 1 (Không có previous) -> has_previous False
res1 = compare_report_versions({'data': {'completed_work': ['A']}}, None)
assert res1['has_previous'] == False
print('Test 1 OK: has_previous is False')

# Test 2: Lần 2 (Có previous) -> has_previous True, tính delta
prev = {
    'lan_nop': 1,
    'completion_percentage': 50,
    'nhan_xet_gv': 'Cần bổ sung Module B và hoàn thiện tài liệu',
    'data': {
        'completed_work': ['Xây dựng Module A cơ bản']
    }
}
curr = {
    'data': {
        'completion_percentage': 75,
        'completed_work': [
            'Xây dựng Module A cơ bản và tối ưu hiệu năng',
            'Xây dựng Module B tích hợp thanh toán trực tuyến'
        ]
    }
}
res2 = compare_report_versions(curr, prev)
assert res2['has_previous'] == True
assert res2['completion_delta'] == 25
assert len(res2['new_items']) >= 1
print('Test 2 OK: has_previous is True, delta =', res2['completion_delta'])
print('Comparative summary:', res2['comparative_summary'])
print('New items:', res2['new_items'])
print('ALL PYTHON TESTS PASSED!')
