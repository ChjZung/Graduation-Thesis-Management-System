<!doctype html><html lang="vi"><head><meta charset="utf-8"><style>
@page{margin:12mm}body{font-family:DejaVu Serif,serif;font-size:9pt;color:#111}h2{text-align:center;font-size:14pt}table{border-collapse:collapse;width:100%;margin:12px 0}td,th{border:1px solid #555;padding:5px}th{background:#bdd7ee}thead{display:table-header-group}tr{page-break-inside:avoid}.stats td{background:#fbe5d6}h3{page-break-after:avoid;font-size:11pt}.number{text-align:center}
</style></head><body>
<strong>BỘ CÔNG THƯƠNG<br>TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH</strong>
<h2>ĐÁNH GIÁ CHUẨN ĐẦU RA (CLO) HỌC PHẦN CỐT LÕI</h2>
<p>Ngành: {{ $report['scope']['TenNganh'] }} · {{ $report['councils'][0]['hoiDong']->hocKy?->TenDayDu }} · {{ $report['scope']['label'] ?? $report['scope']['DotBaoVe'] }} · {{ $report['scope']['loai'] }}</p>
<table><thead><tr><th>STT</th><th>Mã SV</th><th>Họ tên</th><th>Lớp</th><th>Đề tài</th>@foreach($report['cloGroups'] as $code => $group)<th>{{ $group['plo'] }}<br>{{ $code }}</th>@endforeach<th>Kết quả CLO</th><th>Điểm tổng kết</th></tr></thead><tbody>
@foreach($report['students'] as $student)<tr><td>{{ $student['stt'] }}</td><td>{{ $student['maSV'] }}</td><td>{{ $student['hoTen'] }}</td><td>{{ $student['lop'] }}</td><td>{{ $student['tenDeTai'] }}</td>@foreach($report['cloGroups'] as $code => $group)<td class="number">{{ number_format($student['cloScores'][$code]['score'] ?? 0, 2) }}</td>@endforeach<td>{{ $student['all_clo_passed'] ? 'Đạt' : 'Chưa đạt' }}</td><td>{{ number_format($student['finalScore'], 2) }}</td></tr>@endforeach
</tbody></table>
<h3>THỐNG KÊ CLO ĐÁNH GIÁ CLĐT</h3>
<table class="stats"><thead><tr><th>Nội dung</th>@foreach($report['cloGroups'] as $code => $group)<th>{{ $code }}</th>@endforeach</tr></thead><tbody>
@php
$labels = ['Điểm tối đa', 'Chọn ngưỡng Đạt cho CLO', 'Điểm Đạt tương ứng', 'Sinh viên đạt ngưỡng CLO', 'Tỷ lệ SV đạt ngưỡng CLO', 'Kết quả thống kê'];
@endphp
@foreach($labels as $i => $label)<tr><td>{{ $label }}</td>@foreach($report['cloGroups'] as $code => $group)
@php
$passed = count(array_filter($report['students'], fn($s) => ($s['cloScores'][$code]['score'] ?? 0) >= 4));
$rate = $passed / count($report['students']);
$value = match($i) {0 => '10,0', 1 => '40%', 2 => '4,0', 3 => $passed, 4 => number_format($rate * 100, 1).'%', 5 => $rate >= .4 ? 'ĐẠT' : 'KHÔNG ĐẠT'};
@endphp
<td class="number">{{ $value }}</td>@endforeach</tr>@endforeach
</tbody></table>
<p>Điểm CLO và điểm tổng kết dùng kết quả chấm đã hoàn thành. Bảng thống kê áp dụng ngưỡng theo mẫu 5B_CĐR.</p>
</body></html>
