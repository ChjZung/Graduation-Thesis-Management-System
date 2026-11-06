@php
    $byDepartment = $groupMode === 'department';
    $groups = collect($rows)->groupBy($byDepartment ? 'maBoMon' : 'maHocPhan');
    $score = static fn ($value) => $value === null ? '—' : number_format((float) $value, 2);
@endphp
<section class="mt-3">
    <h4 style="font-size:10pt; color:#003366; margin:0 0 8px;">PHỤ LỤC · HỘI ĐỒNG VÀ SINH VIÊN THEO BỘ LỌC</h4>
    @forelse($groups as $code => $group)
        <h5 style="font-size:9pt; margin:8px 0 5px;">{{ $code }} · {{ $group->first()[$byDepartment ? 'tenBoMon' : 'tenHocPhan'] }}</h5>
        @foreach($group->groupBy('maHoiDong') as $councilId => $students)
            @php
                $completed = $students->where('isDuPhieu', true);
                $passed = $completed->where('isDat', true)->count();
                $grades = $completed->pluck('diemTongKet')->filter(fn ($value) => $value !== null);
            @endphp
            <p style="font-size:8pt; margin:5px 0;">
                <strong>{{ $councilId }} · {{ $students->first()['tenHoiDong'] }}</strong><br>
                {{ $students->count() }} sinh viên · {{ $students->pluck('maDeTai')->unique()->count() }} đề tài ·
                Đạt {{ $passed }} · Không đạt {{ $completed->count() - $passed }} · Chưa có kết quả {{ $students->count() - $completed->count() }} ·
                Điểm TB tổng kết {{ $score($grades->avg()) }}
            </p>
            <table class="data-table table table-sm table-bordered" style="width:100%; font-size:8pt;">
                <thead><tr><th>MSSV</th><th>Họ và tên · Lớp</th><th>Đề tài · GVHD</th><th>Điểm TB HĐ</th><th>Tổng kết</th><th>CLO chưa đạt</th><th>Kết quả</th></tr></thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $student['maSV'] }}</td>
                            <td>{{ $student['hoTen'] }}<br>{{ $student['tenLop'] }}</td>
                            <td>{{ $student['maDeTai'] }} · {{ $student['tenDeTai'] }}<br>GVHD: {{ $student['tenGVHD'] }}</td>
                            <td>{{ $score($student['diemHD']) }}</td>
                            <td>{{ $score($student['diemTongKet']) }}</td>
                            <td>{{ $student['failedClos'] ? implode(', ', $student['failedClos']) : ($student['isDuPhieu'] ? 'Không có' : '—') }}</td>
                            <td>{{ $student['ketQua'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @empty
        <p>Không có hội đồng hoặc sinh viên phù hợp với bộ lọc.</p>
    @endforelse
</section>
