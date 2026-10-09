@extends('layouts.sinhvien')

@section('page_title', 'Bảng Tổng Quan Khóa Luận')

@section('content')
{{-- ── TIÊU ĐỀ TRANG CHUẨN ADMIN ── --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-chart-pie me-2 text-primary"></i> Tổng Quan Khóa Luận Tốt Nghiệp
        </h4>
        <div class="text-muted small">
            Sinh viên: <strong>{{ $sinhVien->HoTen }}</strong> &bull; MSSV: <strong>{{ $sinhVien->MaSV }}</strong> &bull; Lớp: <strong>{{ $sinhVien->lop->TenLop ?? 'Chưa phân lớp' }}</strong>
            <span class="badge bg-light text-primary border ms-2"><i class="fa-regular fa-calendar-check me-1"></i>{{ $hocKyHienTai->TenHocKy ?? 'Học kỳ hiện tại' }}</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-xs">
            <i class="fa-solid fa-calendar-day me-1"></i> Lịch báo cáo
        </a>
        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-xs">
            <i class="fa-solid fa-upload me-1"></i> Nộp báo cáo tiến độ
        </a>
    </div>
</div>

{{-- ── HÀNG 1: 4 CARD KPI ĐỒNG BỘ CHUẨN HỌC THUẬT HUIT ── --}}
<div class="row g-3 mb-4">
    <!-- 1. Điều Kiện Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Điều Kiện Khóa Luận</div>
                    <div class="kpi-value text-dark" style="font-size: 1.45rem !important;">
                        {{ $sinhVien->SoTinChiTichLuy ?? 0 }} <span class="fs-6 text-muted fw-normal">TC</span>
                    </div>
                    <div class="kpi-subtext mt-1">
                        @if($isDuDieuKien)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-circle-check me-1"></i>Đủ điều kiện (ĐTB: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }})
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-circle-xmark me-1"></i>Chưa đủ điều kiện ({{ $sinhVien->SoTinChiTichLuy ?? 0 }}/115 TC)
                            </span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Đề Tài Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Đề Tài Khóa Luận</div>
                    <div class="kpi-value text-dark" style="font-size: 1.45rem !important;">
                        {{ $deTai ? $deTai->MaDeTai : ($nhom ? 'Chờ đăng ký' : 'Chưa có') }}
                    </div>
                    <div class="kpi-subtext mt-1">
                        @if($deTai)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-check me-1"></i>Đã duyệt đề tài
                            </span>
                        @elseif($nhom)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-users me-1"></i>Đã có nhóm &bull; Chọn đề tài
                            </span>
                        @else
                            <a href="{{ route('sinhvien.nhom.index') }}" class="text-primary text-decoration-none fw-semibold small">
                                <i class="fa-solid fa-user-plus me-1"></i>Tham gia nhóm &rarr;
                            </a>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Nhóm Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Nhóm Khóa Luận</div>
                    <div class="kpi-value text-dark" style="font-size: 1.45rem !important;">
                        {{ $nhom ? $nhom->MaNhom : 'Chưa có nhóm' }}
                    </div>
                    <div class="kpi-subtext mt-1 text-muted small">
                        @if($nhom)
                            <span class="fw-semibold text-dark">{{ $nhom->TenNhom }}</span> &bull; {{ $thanhViens->count() }}/3 SV
                        @else
                            <span class="text-secondary">Chưa tham gia nhóm nào</span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tiến Độ & Bảo Vệ -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Tiến Độ Hoàn Thành</div>
                    <div class="kpi-value text-primary" style="font-size: 1.45rem !important;">
                        {{ $tienDoPhanTram }}%
                    </div>
                    <div class="kpi-subtext mt-1">
                        @if($ketQua && $ketQua->DiemTongKet > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                Điểm TK: {{ $ketQua->DiemTongKet }} ({{ $ketQua->XepLoai }})
                            </span>
                        @elseif($hoSoBaoVe)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                {{ $hoSoBaoVe->TrangThai }}
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                {{ $soBaoCaoDat }}/5 đợt báo cáo đạt
                            </span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                    <i class="fa-solid fa-bars-progress"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 2: CARD HỒ SƠ ĐỀ TÀI & HƯỚNG DẪN (GIAO DIỆN SÁNG CHUẨN HUIT) ── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important; border-top: 4px solid #003366 !important;">
    <div class="card-body p-4 p-md-4">
        <!-- Badge Header -->
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @if($isDuDieuKien)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-check me-1"></i> ĐỦ ĐIỀU KIỆN LÀM KHÓA LUẬN ({{ $sinhVien->SoTinChiTichLuy ?? 0 }} TC &bull; GPA: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }})
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-xmark me-1"></i> CHƯA ĐỦ ĐIỀU KIỆN ({{ $sinhVien->SoTinChiTichLuy ?? 0 }}/115 TC &bull; GPA: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }}/2.0)
                    </span>
                @endif

                @if($deTai)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-check me-1"></i> ĐÃ DUYỆT ĐỀ TÀI CHÍNH THỨC
                    </span>
                @elseif($nhom)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-users me-1"></i> ĐÃ CÓ NHÓM &bull; CHỜ ĐĂNG KÝ ĐỀ TÀI
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user me-1"></i> CHƯA CÓ NHÓM KHÓA LUẬN
                    </span>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 small fw-medium">
                    <i class="fa-regular fa-calendar me-1 text-primary"></i>{{ $hocKyHienTai->TenHocKy ?? 'Học kỳ hiện tại' }}
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 small fw-medium">
                    <i class="fa-solid fa-graduation-cap me-1 text-primary"></i>{{ $sinhVien->lop->TenLop ?? 'Lớp' }}
                </span>
            </div>
        </div>

        <!-- Tên Đề Tài Lớn -->
        <h4 class="fw-bold text-dark mb-3" style="line-height: 1.35;">
            {{ $deTai->TenDeTai ?? ($nhom ? 'Nhóm chưa đăng ký đề tài khóa luận chính thức' : 'Sinh viên chưa tham gia nhóm khóa luận') }}
        </h4>

        <!-- Metadata tags -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            @if($deTai)
                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
                    <i class="fa-solid fa-hashtag me-1 text-primary"></i>Mã đề tài: <strong class="text-dark">{{ $deTai->MaDeTai }}</strong>
                </span>
                @if($deTai->LinhVuc)
                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
                    <i class="fa-solid fa-layer-group me-1 text-primary"></i>Lĩnh vực: <strong class="text-dark">{{ $deTai->LinhVuc }}</strong>
                </span>
                @endif
            @endif

            @if($nhom)
                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
                    <i class="fa-solid fa-users me-1 text-primary"></i>Mã nhóm: <strong class="text-dark">{{ $nhom->MaNhom }} ({{ $nhom->TenNhom }})</strong>
                </span>
                @if($nhom->hocPhan)
                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
                    <i class="fa-solid fa-book me-1 text-primary"></i>Học phần: <strong class="text-dark">{{ $nhom->hocPhan->TenHocPhan }}</strong>
                </span>
                @endif
            @endif

            <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small">
                <i class="fa-solid fa-building-columns me-1 text-primary"></i>Ngành: <strong class="text-dark">{{ $sinhVien->lop->nganh->TenNganh ?? 'Công nghệ thông tin' }}</strong>
            </span>
        </div>

        <!-- 2 Khối Thông Tin Chi Tiết: GVHD & Thành Viên -->
        <div class="row g-3 pt-3 border-top">
            <!-- Giảng viên hướng dẫn -->
            <div class="col-12 col-md-5 border-end-md">
                <div class="text-muted small fw-semibold mb-1">
                    <i class="fa-solid fa-chalkboard-user me-1 text-primary"></i>Giảng viên hướng dẫn:
                </div>
                <div class="fw-bold text-dark fs-6">
                    @if($gvhd)
                        {{ ($gvhd->HocHam ? $gvhd->HocHam . '.' : '') . ($gvhd->HocVi ? $gvhd->HocVi . '.' : '') . ' ' . $gvhd->HoTen }}
                    @else
                        <span class="text-muted fw-normal fst-italic">Chưa phân công giảng viên hướng dẫn</span>
                    @endif
                </div>
                <div class="small text-muted mt-1">
                    <i class="fa-regular fa-envelope me-1"></i>{{ $gvhd->Email ?? 'Chưa cập nhật email' }}
                    @if($gvhd && $gvhd->boMon)
                        &bull; <span class="fw-medium text-secondary">{{ $gvhd->boMon->TenBoMon }}</span>
                    @endif
                </div>
            </div>

            <!-- Thành viên nhóm -->
            <div class="col-12 col-md-7">
                <div class="text-muted small fw-semibold mb-1">
                    <i class="fa-solid fa-user-group me-1 text-primary"></i>Thành viên nhóm (Tối đa 3 SV):
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if($truongNhom)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill small fw-semibold">
                            👑 {{ $truongNhom->HoTen }} (Trưởng nhóm) - {{ $truongNhom->MaSV }}
                        </span>
                    @endif

                    @forelse($thanhViens as $tv)
                        @if(!$truongNhom || $tv->MaSV !== $truongNhom->MaSV)
                            <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill small fw-medium">
                                {{ $tv->sinhVien->HoTen ?? $tv->MaSV }} (Thành viên) - {{ $tv->MaSV }}
                            </span>
                        @endif
                    @empty
                        @if(!$truongNhom)
                            <span class="text-muted small fst-italic">Chưa có thành viên nào trong nhóm</span>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tiến độ hoàn thành khóa luận -->
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-secondary fw-semibold">
                    <i class="fa-solid fa-bars-progress me-1 text-primary"></i>Tiến độ thực hiện khóa luận:
                </span>
                <span class="fs-6 fw-bold text-primary">{{ $tienDoPhanTram }}%</span>
            </div>
            <div class="progress rounded-pill bg-light border" style="height: 10px;">
                <div class="progress-bar rounded-pill" role="progressbar" 
                     style="width: {{ $tienDoPhanTram }}%; background: linear-gradient(90deg, #0072ce 0%, #003366 100%);"
                     aria-valuenow="{{ $tienDoPhanTram }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 3: 2 CỘT CHỨC NĂNG (KẾ HOẠCH BÁO CÁO & PHẢN HỒI GVHD) ── --}}
<div class="row g-4">
    <!-- Cột Trái (7 phần): Việc Cần Làm Tiếp Theo & Lịch Báo Cáo Sắp Tới -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary fa-lg"></i>
                <h6 class="fw-bold mb-0 text-dark">Kế Hoạch &amp; Việc Cần Làm Tiếp Theo</h6>
            </div>
            <div class="card-body p-4">
                <!-- Action Box: Mốc Báo Cáo Kế Hoạch Hiện Tại -->
                <div class="p-3 rounded-3 bg-light border mb-4" style="border-left: 4px solid #0072ce !important;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-regular fa-clock me-1"></i>Hạn chót: {{ $nextMoc ? \Carbon\Carbon::parse($nextMoc->NgayKetThuc)->format('d/m/Y') : 'Theo kế hoạch' }}
                            @if($daysRemaining !== null)
                                &bull; {{ $daysRemaining >= 0 ? "Còn lại {$daysRemaining} ngày" : "Đã quá hạn " . abs($daysRemaining) . " ngày" }}
                            @endif
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                            Đợt {{ $mocTiepTheo }}
                        </span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2">
                        {{ $nextMoc->TenMoc ?? ('Báo Cáo Tiến Độ Đợt ' . $mocTiepTheo . ': Triển khai & Kiểm thử hệ thống') }}
                    </h6>
                    <p class="small text-secondary mb-3" style="line-height: 1.5;">
                        {{ $nextMoc->MoTa ?? 'Sinh viên cần nộp file báo cáo chi tiết kèm minh chứng công việc để Giảng viên hướng dẫn xem xét, chấm điểm và góp ý hoàn thiện đề tài.' }}
                    </p>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-xs">
                            <i class="fa-solid fa-upload me-1"></i> Nộp bài báo cáo ngay
                        </a>
                        <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                            <i class="fa-solid fa-calendar-week me-1"></i> Xem lịch báo cáo
                        </a>
                    </div>
                </div>

                <!-- Lịch hẹn hướng dẫn với GVHD sắp tới -->
                <div class="p-3 rounded-3 bg-white border">
                    <div class="fw-bold text-dark small mb-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-regular fa-calendar-check text-success"></i>
                            <span>Lịch hẹn gặp hướng dẫn sắp tới</span>
                        </div>
                        <a href="{{ route('sinhvien.calendar') }}" class="small text-primary text-decoration-none fw-semibold">
                            Xem lịch &rarr;
                        </a>
                    </div>

                    @forelse($lichGaps as $lg)
                        <div class="d-flex align-items-start gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="p-2 rounded bg-success bg-opacity-10 text-success text-center" style="min-width: 52px;">
                                <div class="fw-bold fs-6">{{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->format('d') }}</div>
                                <div class="small fw-semibold text-uppercase" style="font-size: 0.68rem;">TH {{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->format('m') }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark small">
                                    {{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->isoFormat('dddd, DD/MM/YYYY (HH:mm)') }}
                                </div>
                                <div class="small text-muted mb-0.5">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $lg->DiaDiem ?? 'Văn phòng Bộ môn chuyên ngành' }}
                                </div>
                                <div class="small text-secondary">
                                    Nội dung: {{ $lg->NoiDung ?? 'Trao đổi tiến độ đề tài khóa luận' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small py-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-info-circle text-primary"></i>
                            <span>Chưa có lịch hẹn gặp trực tiếp mới với GVHD. Sinh viên có thể chủ động liên hệ GVHD qua email khi cần trao đổi.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Cột Phải (5 phần): Phản Hồi Từ GVHD & Lịch Bảo Vệ Hội Đồng -->
    <div class="col-12 col-lg-5">
        <!-- Card 1: Nhận Xét & Đánh Giá Mới Nhất Từ GVHD -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-comment-dots text-primary fa-lg"></i>
                    <h6 class="fw-bold mb-0 text-dark">Nhận Xét &amp; Đánh Giá Mới Nhất</h6>
                </div>
                <a href="{{ route('sinhvien.baocao.index') }}" class="small text-primary text-decoration-none fw-semibold">
                    Lịch sử &rarr;
                </a>
            </div>
            <div class="card-body p-4">
                @if($latestFeedback && $latestFeedback->NhanXet)
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                                Đợt {{ $latestFeedback->LanBaoCao }}: {{ $latestFeedback->TieuDe }}
                            </span>
                            @if($latestFeedback->TrangThai === 'Đạt')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small fw-bold">
                                    <i class="fa-solid fa-check me-1"></i>Đạt
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 small fw-bold">
                                    {{ $latestFeedback->TrangThai }}
                                </span>
                            @endif
                        </div>
                        <div class="small text-dark fst-italic" style="line-height: 1.5;">
                            "{{ $latestFeedback->NhanXet }}"
                        </div>
                        <div class="text-muted small mt-2 d-flex justify-content-between align-items-center">
                            <span><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($latestFeedback->updated_at)->format('H:i - d/m/Y') }}</span>
                            @if($latestFeedback->DiemSo)
                                <span class="fw-bold text-primary">Điểm: {{ $latestFeedback->DiemSo }}/10</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-comments text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="small mb-1 fw-medium text-secondary">Chưa có nhận xét mới từ Giảng viên hướng dẫn</p>
                        <p class="small text-muted mb-0">Sau khi nộp báo cáo tiến độ, giảng viên sẽ xem xét và phản hồi góp ý tại đây.</p>
                    </div>
                @endif

                <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                    <i class="fa-solid fa-clipboard-list me-1"></i> Xem chi tiết các đợt báo cáo tiến độ
                </a>
            </div>
        </div>

        <!-- Card 2: Lịch Báo Cáo Hội Đồng & Hồ Sơ Bảo Vệ -->
        <div class="card border-0 shadow-sm rounded-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-building-columns text-primary fa-lg"></i>
                    <h6 class="fw-bold mb-0 text-dark">Lịch Báo Cáo Hội Đồng</h6>
                </div>
                <a href="{{ route('sinhvien.hoso.index') }}" class="small text-primary text-decoration-none fw-semibold">
                    Hồ sơ &rarr;
                </a>
            </div>
            <div class="card-body p-4">
                @if($hoSoBaoVe && $hoSoBaoVe->hoiDong)
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-purple-subtle text-purple border rounded-pill px-2.5 py-1 small fw-bold" style="background: #f3e8ff; color: #7e22ce; border-color: #d8b4fe !important;">
                                <i class="fa-solid fa-users-rectangle me-1"></i>{{ $hoSoBaoVe->hoiDong->TenHoiDong }}
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5 small fw-semibold">
                                {{ $hoSoBaoVe->TrangThai }}
                            </span>
                        </div>
                        <div class="small text-dark mb-1">
                            <i class="fa-regular fa-calendar-check text-primary me-1"></i>Thời gian: <strong>{{ $hoSoBaoVe->hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoSoBaoVe->hoiDong->ThoiGianBatDau)->format('H:i - d/m/Y') : 'Chưa xếp lịch' }}</strong>
                        </div>
                        <div class="small text-dark mb-2">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i>Địa điểm: <strong>{{ $hoSoBaoVe->hoiDong->DiaDiem ?? $hoSoBaoVe->PhongBaoVe ?? 'Chưa công bố phòng' }}</strong>
                        </div>
                        @if($hoSoBaoVe->hoiDong->thanhViens && $hoSoBaoVe->hoiDong->thanhViens->isNotEmpty())
                            <div class="small text-muted border-top pt-2">
                                Thành viên HĐ: 
                                @foreach($hoSoBaoVe->hoiDong->thanhViens as $tvHd)
                                    <span class="badge bg-white text-secondary border rounded-pill px-2 py-0.5">{{ $tvHd->giangVien->HoTen ?? '' }} ({{ $tvHd->VaiTro }})</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-3 rounded-3 bg-light border mb-3 text-center">
                        <i class="fa-solid fa-file-shield text-muted mb-2" style="font-size: 1.8rem;"></i>
                        <div class="small fw-semibold text-dark mb-1">
                            {{ $hoSoBaoVe ? $hoSoBaoVe->TrangThai : 'Chưa nộp hồ sơ bảo vệ' }}
                        </div>
                        <div class="small text-muted">
                            Lịch báo cáo Hội đồng sẽ được tự động cập nhật sau khi hoàn thành các đợt báo cáo tiến độ và được GVHD duyệt hồ sơ bảo vệ.
                        </div>
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 fw-semibold">
                        <i class="fa-solid fa-calendar-days me-1"></i> Xem lịch báo cáo
                    </a>
                    <a href="{{ route('sinhvien.hoso.index') }}" class="btn btn-primary btn-sm rounded-pill flex-grow-1 fw-semibold">
                        <i class="fa-solid fa-file-circle-check me-1"></i> Chi tiết hồ sơ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
