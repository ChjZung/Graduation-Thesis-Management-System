@extends($layout)

@section('page_title', 'Lịch Quy Trình Khóa Luận — Kế Hoạch vs Thực Tế')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-primary-custom mb-1"><i class="fa-solid fa-calendar-week me-2"></i>Lịch Quy Trình Khóa Luận — Kế Hoạch vs Thực Tế</h4>
        <p class="text-muted small mb-0">Ma trận đối chiếu giữa mốc thời gian Kế Hoạch và Tiến độ Thực Tế của từng nhóm thực hiện.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.calendar') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="fa-regular fa-calendar-days me-1"></i> Xem Calendar
        </a>
    </div>
</div>

<!-- Plan & Advisor Filters -->
<div class="card card-premium mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('calendar.matrix') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-bold mb-1">Chọn Kế Hoạch Khóa Luận:</label>
                <select name="MaKeHoach" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($keHoachs as $kh)
                        <option value="{{ $kh->MaKeHoach }}" {{ ($activePlan && $activePlan->MaKeHoach == $kh->MaKeHoach) ? 'selected' : '' }}>
                            {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }} - {{ $kh->TrangThai }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold mb-1">Lọc Theo Giảng Viên Hướng Dẫn:</label>
                <select name="MaGV" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả GVHD --</option>
                    @foreach($giangViens as $gv)
                        <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>{{ $gv->HoTen }} ({{ $gv->MaGV }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('calendar.matrix') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 w-100 mt-4"><i class="fa-solid fa-rotate me-1"></i>Đặt lại</a>
            </div>
        </form>
    </div>
</div>

@if($activePlan)
<!-- Visual Matrix Table -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-table-cells text-primary me-2"></i> Kế Hoạch: <strong>{{ $activePlan->TenKeHoach }}</strong></span>
        <div class="d-flex gap-2">
            <span class="badge bg-success rounded-pill px-2 py-1">🟢 Hoàn thành</span>
            <span class="badge bg-warning text-dark rounded-pill px-2 py-1">🟡 Sắp đến hạn</span>
            <span class="badge bg-danger rounded-pill px-2 py-1">🔴 Quá hạn</span>
            <span class="badge bg-secondary rounded-pill px-2 py-1">⚪ Chưa mở</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-center">
                    <tr>
                        <th width="4%">#</th>
                        <th width="22%">Mốc Quy Trình (KẾ HOẠCH)</th>
                        <th width="14%">Thời Gian Kế Hoạch</th>
                        @foreach($nhoms as $nhom)
                            <th width="{{ floor(60 / max(count($nhoms), 1)) }}%">
                                <div class="fw-bold text-primary">{{ $nhom->MaNhom }}</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">{{ Str::limit($nhom->truongNhom->HoTen ?? 'Nhóm', 15) }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrix as $idx => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item['moc']->TenMoc }}</div>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ $item['moc']->LoaiGiaiDoan }}</span>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-primary" style="font-size: 0.8rem;"><i class="fa-regular fa-calendar-days me-1"></i>{{ date('d/m/Y', strtotime($item['moc']->NgayBatDau)) }}</div>
                            <div class="small text-muted" style="font-size: 0.75rem;"><i class="fa-solid fa-arrow-right me-1"></i>{{ date('d/m/Y', strtotime($item['moc']->NgayKetThuc)) }}</div>
                        </td>
                        @foreach($nhoms as $nhom)
                            @php
                                $teamData = $item['teams'][$nhom->MaNhom] ?? null;
                                $status = $teamData['status'] ?? 'CHUA_BAT_DAU';
                            @endphp
                            <td class="text-center">
                                @if($status === 'HOAN_THANH')
                                    <span class="badge bg-success rounded-pill px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Hoàn thành</span>
                                    @if($teamData['actual_date'])
                                        <div class="small text-success fw-semibold mt-1" style="font-size: 0.72rem;">{{ $teamData['actual_date'] }}</div>
                                    @endif
                                @elseif($status === 'SAP_DEN_HAN')
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Sắp đến hạn</span>
                                    @if($teamData['warning'])
                                        <div class="small text-warning fw-bold mt-1" style="font-size: 0.72rem;">{{ $teamData['warning'] }}</div>
                                    @endif
                                @elseif($status === 'QUA_HAN')
                                    <span class="badge bg-danger rounded-pill px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Quá hạn</span>
                                    @if($teamData['warning'])
                                        <div class="small text-danger fw-bold mt-1" style="font-size: 0.72rem;">{{ $teamData['warning'] }}</div>
                                    @endif
                                @elseif($status === 'DANG_DIEN_RA')
                                    <span class="badge bg-info text-white rounded-pill px-2 py-1">Đang diễn ra</span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1">Chưa nộp</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
