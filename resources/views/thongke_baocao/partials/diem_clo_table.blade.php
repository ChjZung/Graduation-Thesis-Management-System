@php
    $score = static fn ($value) => $value === null ? '—' : number_format((float) $value, 2);
@endphp
<div class="table-responsive">
<table class="data-table table table-sm table-bordered align-middle" style="width:100%; table-layout:fixed; font-size:8pt;">
    <thead>
        <tr>
            <th style="width:4%">STT</th>
            <th style="width:16%">Sinh viên</th>
            <th style="width:22%">Đề tài và GVHD</th>
            <th style="width:15%">Ngành, học phần và hội đồng</th>
            <th style="width:12%">Điểm thang 10</th>
            <th style="width:19%">Điểm trung bình từng CLO</th>
            <th style="width:12%">Kết quả</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $row['hoTen'] }}</strong><br>{{ $row['maSV'] }}<br>{{ $row['tenLop'] ?: '—' }}<br>GPA tích lũy: {{ $score($row['gpaTichLuy']) }}</td>
                <td><strong>{{ $row['maDeTai'] }}</strong><br>{{ $row['tenDeTai'] }}<br>GVHD: {{ $row['tenGVHD'] }}</td>
                <td>{{ $row['tenNganh'] }}<br>{{ $row['maHocPhan'] }}<br>{{ $row['tenHoiDong'] }}</td>
                <td>TB hội đồng: {{ $score($row['diemHD']) }}<br>Tổng kết: {{ $score($row['diemTongKet']) }}</td>
                <td>
                    @forelse($row['cloScores'] as $code => $value)
                        <span style="display:inline-block; margin-right:4px; white-space:nowrap;">{{ $code }} {{ $score($value) }}</span>
                    @empty
                        Chưa đủ dữ liệu CLO
                    @endforelse
                </td>
                <td><strong>{{ $row['ketQua'] }}</strong>
                    @if($row['failedClos'])<br>CLO chưa đạt: {{ implode(', ', $row['failedClos']) }}@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Chưa có hồ sơ chấm điểm phù hợp với bộ lọc.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
<p style="font-size:8pt;">Kết luận đạt khi điểm tổng kết và tất cả CLO đạt ngưỡng {{ number_format(\App\Services\CloCalculationService::PASSING_THRESHOLD, 2) }}/10. Dấu — chỉ trường chưa có dữ liệu.</p>
