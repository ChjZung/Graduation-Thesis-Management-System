
<section data-weighted-report aria-label="Báo cáo đánh giá chuẩn đầu ra CLO">
    @php
        $students = collect($report['students']);
        $complete = $students->filter(fn ($student) => $student['audit']['complete']);
        $passed = $complete->filter(fn ($student) => $student['audit']['passed'])->count();
        $threshold = \App\Services\CloCalculationService::PASSING_THRESHOLD;
        $num = fn ($value, $digits = 2) => $value === null ? '—' : number_format($value, $digits, ',', '.');
        $percent = fn ($part, $total) => $total ? $num(100 * $part / $total, 1).'%' : '—';
        $codes = ['CLO1', 'CLO2', 'CLO3', 'CLO4', 'CLO5', 'CLO6'];
        $criteria = $students->first()['audit']['criteria'];
        $groups = $students->first()['audit']['clo'];
    @endphp
    <h5>Báo cáo đánh giá chuẩn đầu ra CLO</h5>
    <p class="small text-muted">{{ $report['scope']['TenKhoa'] }} · {{ $report['scope']['TenNganh'] }} · {{ $report['scope']['label'] }}</p>
    <dl class="row small">
        <dt class="col-8">Sinh viên / Hội đồng</dt><dd class="col-4">{{ $students->count() }} / {{ count($report['councils']) }}</dd>
        <dt class="col-8">Đủ điểm / Chưa đủ dữ liệu</dt><dd class="col-4">{{ $complete->count() }} / {{ $students->count() - $complete->count() }}</dd>
        <dt class="col-8">Điểm trung bình khóa luận</dt><dd class="col-4">{{ $num($complete->avg('audit.final')) }}</dd>
        <dt class="col-8">Đạt / Không đạt / Tỷ lệ đạt trong số đã đủ điểm</dt>
        <dd class="col-4">{{ $passed }} / {{ $complete->count() - $passed }} / {{ $percent($passed, $complete->count()) }}</dd>
    </dl>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead><tr><th>CLO</th><th>Điểm trung bình</th><th>Dưới ngưỡng</th><th>Tỷ lệ đạt</th></tr></thead>
            <tbody>
            @foreach($codes as $code)
                @php
                    $scores = $complete->pluck('audit.clo.'.$code.'.score');
                    $under = $scores->filter(fn ($score) => $score < $threshold)->count();
                @endphp
                <tr><th>{{ $code }}</th><td>{{ $num($scores->avg()) }}</td><td>{{ $under }}</td><td>{{ $percent($scores->count() - $under, $scores->count()) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @foreach(['lop' => 'Lớp', 'tenHoiDong' => 'Hội đồng'] as $key => $label)
        <details class="mb-2">
            <summary>So sánh theo {{ mb_strtolower($label) }}</summary>
            @foreach($students->groupBy($key) as $name => $group)
                @php
                    $graded = $group->filter(fn ($student) => $student['audit']['complete']);
                    $groupPassed = $graded->filter(fn ($student) => $student['audit']['passed'])->count();
                @endphp
                <div class="small py-1">
                    {{ $name }} · {{ $groupPassed }}/{{ $graded->count() }} đạt · {{ $group->count() - $graded->count() }} chưa đủ dữ liệu
                    @if($graded->count())
                        <meter min="0" max="{{ $graded->count() }}" value="{{ $groupPassed }}" aria-label="Tỷ lệ đạt của {{ $name }}"></meter>
                    @endif
                </div>
            @endforeach
        </details>
    @endforeach
    <details class="mb-3">
        <summary>Trọng số tiêu chí</summary>
        <div class="table-responsive">
            <table class="table table-sm small">
                <thead><tr><th>Tiêu chí</th><th>CLO</th><th>Điểm tối đa</th><th>Trọng số</th><th>Tổng trọng số CLO</th></tr></thead>
                <tbody>
                @foreach($criteria as $item)
                    <tr><td>{{ $item['label'] }}</td><td>CLO{{ $item['code'] }}</td><td>{{ $num($item['max']) }}</td>
                        <td>{{ $num($item['max']) }} / 10 = {{ $percent($item['weight'], 1) }}</td>
                        <td>{{ $percent($groups['CLO'.(int) $item['code']]['weight'], 1) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </details>
    <h6>Kết quả sinh viên</h6>
    <div class="table-responsive">
        <table class="table table-sm small align-middle">
            <thead><tr><th>Sinh viên</th><th>Lớp / Hội đồng</th><th>GK1 / GK2 / GK3</th><th>Điểm tổng</th>
                @foreach($codes as $code)<th>{{ $code }}</th>@endforeach<th>Kết quả</th><th>Đối chiếu</th></tr></thead>
            <tbody>
            @foreach($students as $student)
                @php($audit = $student['audit'])
                <tr>
                    <th>{{ $student['maSV'] }} · {{ $student['hoTen'] }}</th>
                    <td>{{ $student['lop'] }} / {{ $student['tenHoiDong'] }}</td>
                    <td>{{ implode(' / ', array_map($num, $audit['judgeTotals'])) }}</td><td>{{ $num($audit['final']) }}</td>
                    @foreach($codes as $code)<td>{{ $num($audit['clo'][$code]['score']) }}</td>@endforeach
                    <td>{{ $audit['complete'] ? ($audit['passed'] ? 'Đạt' : 'Không đạt') : 'Chưa đủ dữ liệu' }}</td>
                    <td><a href="#clo-audit-{{ $loop->iteration }}">Công thức</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @foreach($students as $student)
        @php($audit = $student['audit'])
        <details id="clo-audit-{{ $loop->iteration }}" class="border rounded px-2 py-1 mb-2">
            <summary>{{ $student['maSV'] }} · {{ $student['hoTen'] }} · Đối chiếu từng tiêu chí</summary>
            <div class="table-responsive">
                <table class="table table-sm small">
                    <thead><tr><th>Tiêu chí</th><th>Điểm tối đa</th><th>GK1 / GK2 / GK3</th><th>Trung bình</th><th>Trọng số</th><th>Quy đổi</th><th>Điểm có trọng số</th></tr></thead>
                    <tbody>
                    @foreach($audit['criteria'] as $item)
                        <tr>
                            <th>CLO{{ $item['code'] }} · {{ $item['label'] }}</th><td>{{ $num($item['max']) }}</td>
                            <td>{{ implode(' / ', array_map($num, $item['scores'])) }}</td><td>{{ $num($item['average'], 4) }}</td>
                            <td>{{ $num($item['max']) }} / 10 = {{ $percent($item['weight'], 1) }}</td>
                            <td>@if($item['average'] !== null){{ $num($item['average'], 4) }} × 10 / {{ $num($item['max']) }} = {{ $num($item['normalized'], 4) }}@else — @endif</td>
                            <td>@if($item['weighted'] !== null){{ $num($item['normalized'], 4) }} × {{ $num($item['weight'], 4) }} = {{ $num($item['weighted'], 4) }}@else — @endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <ul class="small">
                @foreach($audit['clo'] as $code => $group)
                    <li>{{ $code }} = @if($group['score'] !== null){{ $num($group['weighted'], 4) }} / {{ $num($group['weight'], 4) }} = {{ $num($group['score']) }}@else Chưa đủ dữ liệu @endif</li>
                @endforeach
            </ul>
        </details>
    @endforeach
</section>

