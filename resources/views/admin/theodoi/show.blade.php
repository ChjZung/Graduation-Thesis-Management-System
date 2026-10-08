@extends('layouts.admin')

@section('page_title', 'Chi Tiết Tiến Độ Nhóm Đồ Án')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.theodoi.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách theo dõi
    </a>
    
    <form action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}" method="POST" class="d-inline" onsubmit="return confirm('Gửi thông báo nhắc nhở tiến độ tới nhóm {{ $nhom->MaNhom }} và GVHD?');">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm text-dark rounded-pill px-4 shadow-sm fw-bold">
            <i class="fa-solid fa-bell me-1"></i> Gửi Thông Báo Nhắc Tiến Độ
        </button>
    </form>
</div>

<!-- General Info Header Banner -->
<div class="card card-premium mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge-code fs-6">{{ $nhom->MaNhom }}</span>
                    <span class="badge bg-light text-dark border fs-6 px-3 py-1 rounded-pill">{{ $nhom->TenNhom ?? 'Nhóm Khóa Luận' }}</span>
                </div>
                <h3 class="fw-bold text-primary mb-2">{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}</h3>
                <p class="text-muted mb-0"><i class="fa-solid fa-user-tie me-1 text-secondary"></i> <strong>Trưởng nhóm:</strong> {{ $nhom->truongNhom->HoTen ?? 'Chưa rõ' }} ({{ $nhom->MaTruongNhom }}) | <strong>GVHD:</strong> {{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen ?? 'Chưa phân công' }}</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="small text-muted mb-1">Báo cáo tiến độ đã nộp:</div>
                    <div class="fw-bold fs-4 text-success">{{ $nhom->baoCaos->count() }} <span class="fs-6 text-muted">/ 3 lần</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Member List & Progress Timeline Breakdown -->
<div class="row g-4 mb-4">
    <!-- Left Column: Member Details -->
    <div class="col-md-5">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <span><i class="fa-solid fa-users me-2 text-primary"></i> Thành Viên Nhóm ({{ $nhom->thanhViens->count() + 1 }} SV)</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <!-- Trưởng nhóm -->
                    <li class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge-code">{{ $nhom->truongNhom->MaSoSinhVien ?? $nhom->MaTruongNhom }}</span>
                            <span class="badge bg-primary rounded-pill px-3">Trưởng nhóm</span>
                        </div>
                        <div class="fw-bold text-primary fs-6">{{ $nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</div>
                        <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $nhom->truongNhom->Email ?? 'Chưa có email' }}</div>
                    </li>
                    <!-- Thành viên khác -->
                    @foreach($nhom->thanhViens as $tv)
                    <li class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge-code">{{ $tv->sinhVien->MaSoSinhVien ?? $tv->MaSV }}</span>
                            <span class="badge bg-light text-dark border rounded-pill px-3">Thành viên</span>
                        </div>
                        <div class="fw-bold text-dark fs-6">{{ $tv->sinhVien->HoTen ?? 'Chưa rõ' }}</div>
                        <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $tv->sinhVien->Email ?? 'Chưa có email' }}</div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column: Timeline Breakdown -->
    <div class="col-md-7">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <span><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Timeline Quy Trình Theo Kế Hoạch</span>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush">
                    @php
                        $checkpoints = [
                            1 => [
                                'title' => 'Giai đoạn 1: Nhận đề cương chi tiết + Thiết kế CSDL', 
                                'done' => $nhom->baoCaos->count() >= 1, 
                                'desc' => $nhom->baoCaos->count() >= 1 ? 'Đã nộp đề cương và thiết kế CSDL (Mốc 14/09).' : 'Chưa nộp đề cương & CSDL (Hạn 14/09 - 18/09/2026).'
                            ],
                            2 => [
                                'title' => 'Giai đoạn 2: Thiết kế hệ thống & Xây dựng chức năng', 
                                'done' => $nhom->baoCaos->count() >= 2, 
                                'desc' => $nhom->baoCaos->count() >= 2 ? 'Đã nộp báo cáo thiết kế & mã nguồn chức năng (Mốc 12/10).' : 'Chưa nộp báo cáo chức năng (Hạn 12/10 - 16/10/2026).'
                            ],
                            3 => [
                                'title' => 'Giai đoạn 3: Kiểm thử & Hoàn thành dự thảo báo cáo', 
                                'done' => $nhom->baoCaos->count() >= 3, 
                                'desc' => $nhom->baoCaos->count() >= 3 ? 'Đã nộp kết quả kiểm thử & bản thảo toàn văn (Mốc 02/11).' : 'Chưa nộp dự thảo báo cáo (Hạn 02/11 - 06/11/2026).'
                            ],
                            4 => [
                                'title' => 'Giai đoạn 4: Hoàn thiện hồ sơ bảo vệ (Turnitin < 20% & GVHD ký duyệt)', 
                                'done' => (bool)($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null && $nhom->hoSoBaoVe->XacNhanGVHD), 
                                'desc' => ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null) 
                                    ? ('Tỷ lệ trùng lặp: ' . $nhom->hoSoBaoVe->TyLeTrungLap . '%, ' . ($nhom->hoSoBaoVe->XacNhanGVHD ? 'GVHD đã ký duyệt bảo vệ.' : 'Chờ GVHD ký duyệt.')) 
                                    : 'Chưa quét Turnitin / Chưa nộp cuốn chính thức (Hạn 11/11 - 16/11/2026).'
                            ],
                            5 => [
                                'title' => 'Giai đoạn 5: Tiến hành bảo vệ trước Hội đồng chấm', 
                                'done' => (bool)($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->MaHoiDong), 
                                'desc' => ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->MaHoiDong) ? 'Đã xếp lịch và tiến hành bảo vệ trước Hội đồng.' : 'Chờ phân công Hội đồng chấm bảo vệ.'
                            ],
                            6 => [
                                'title' => 'Giai đoạn 6: Nhận kết quả & Nộp lại kết quả hậu khóa luận', 
                                'done' => (bool)($nhom->TrangThai === 'Hoàn thành' || ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TrangThai === 'Đã hoàn thành')), 
                                'desc' => ($nhom->TrangThai === 'Hoàn thành') ? 'Đã hoàn tất chỉnh sửa theo góp ý Hội đồng và nộp lưu trữ.' : 'Chờ nhận kết quả và hoàn thiện nộp lưu trữ hậu khóa luận.'
                            ],
                        ];
                    @endphp

                    @foreach($checkpoints as $step => $cp)
                    <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center">
                            @if($cp['done'])
                                <span class="badge bg-success rounded-circle p-2 me-3"><i class="fa-solid fa-check fs-6"></i></span>
                            @else
                                <span class="badge bg-secondary rounded-circle p-2 me-3"><i class="fa-solid fa-ellipsis fs-6"></i></span>
                            @endif
                            <div>
                                <h6 class="mb-0 fw-bold {{ $cp['done'] ? 'text-dark' : 'text-muted' }}">{{ $step }}. {{ $cp['title'] }}</h6>
                                <small class="text-muted">{{ $cp['desc'] }}</small>
                            </div>
                        </div>
                        <div>
                            @if($cp['done'])
                                <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3">✓ Hoàn thành</span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill px-3">○ Chưa hoàn thành</span>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
