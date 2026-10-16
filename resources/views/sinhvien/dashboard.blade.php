@extends('layouts.sinhvien')

@section('page_title', 'Tổng Quan Khóa Luận')

@section('content')
<style>
    /* Dashboard accents use the same palette as the admin layout. */
    .dashboard-container {
        font-size: 0.85rem;
        color: var(--admin-text-primary);
    }

    /* KPI Metric Cards */
    .kpi-metric-box {
        background-color: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        padding: 12px 16px;
        height: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: none;
    }
    .kpi-metric-box:hover {
        transform: translateY(-2px);
        border-color: var(--admin-neutral-border);
        box-shadow: 0 6px 16px var(--shadow-sm);
    }
    .kpi-icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background-color: var(--admin-neutral-soft);
        color: var(--admin-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .metric-title {
        font-size: 0.725rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--admin-text-secondary);
    }
    .metric-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--admin-text-primary);
        line-height: 1.25;
    }

    /* Card Panels */
    .dashboard-card-panel {
        background-color: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        box-shadow: none;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .panel-header-compact {
        padding: 12px 18px;
        background-color: var(--admin-surface);
        border-bottom: 1px solid var(--admin-border-soft);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-body-compact {
        padding: 16px 18px;
    }

    /* Progress bar custom */
    .progress-bar-cyan {
        background: linear-gradient(90deg, var(--admin-primary) 0%, var(--admin-primary-dark) 100%);
    }

    /* Smooth Scroll Bar */
    .sv-smooth-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .sv-smooth-scroll::-webkit-scrollbar-track {
        background: var(--admin-border-soft);
        border-radius: 4px;
    }
    .sv-smooth-scroll::-webkit-scrollbar-thumb {
        background: var(--admin-scrollbar);
        border-radius: 4px;
    }
    .sv-smooth-scroll::-webkit-scrollbar-thumb:hover {
        background: var(--admin-primary-dark);
    }

    .badge-neutral {
        background-color: var(--admin-border-soft);
        color: var(--admin-text-secondary);
        border: 1px solid var(--admin-border);
    }
    .border-end-sm {
        border-right: 1px solid var(--admin-border);
    }
    @media (max-width: 575.98px) {
        .border-end-sm {
            border-right: none;
            border-bottom: 1px solid var(--admin-border);
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
        }
    }
</style>

<div class="dashboard-container">

    {{-- ── 1. TIÊU ĐỀ VÀ THÔNG TIN SINH VIÊN ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: var(--admin-text-primary);">
                <i class="fa-solid fa-chart-pie" style="color: var(--admin-primary);"></i>
                <span>Tổng Quan Khóa Luận</span>
                <span class="badge badge-neutral fw-semibold ms-1" style="font-size: 0.75rem;">
                    <i class="fa-regular fa-calendar-check me-1" style="color: var(--admin-primary);"></i>{{ $hocKyHienTai->TenHocKy ?? 'Học kỳ hiện tại' }}
                </span>
            </h5>
            <div class="text-secondary" style="font-size: 0.825rem;">
                Xin chào, <strong style="color: var(--admin-text-primary);">{{ $sinhVien->HoTen }}</strong> &bull; MSSV: <code>{{ $sinhVien->MaSV }}</code> &bull; Lớp: <strong>{{ $sinhVien->lop->TenLop ?? 'Chưa phân lớp' }}</strong>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-calendar-day me-1"></i>Lịch báo cáo
            </a>
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                <i class="fa-solid fa-upload me-1"></i>Nộp báo cáo
            </a>
        </div>
    </div>

    {{-- ── 2. THÔNG BÁO LỜI MỜI NHÓM (NẾU CÓ) ── --}}
    @if(isset($loiMois) && $loiMois->count() > 0)
    <div class="alert alert-warning border-warning shadow-sm py-2 px-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-envelope-open-text fs-5 text-warning"></i>
            <div>
                <strong class="text-dark">Bạn có {{ $loiMois->count() }} lời mời tham gia nhóm khóa luận:</strong>
                <span class="text-muted ms-1" style="font-size: 0.825rem;">
                    Từ Trưởng nhóm <strong>{{ $loiMois->first()->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (Nhóm: {{ $loiMois->first()->nhom->TenNhom ?? '' }}).
                </span>
            </div>
        </div>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold text-dark">
            <i class="fa-solid fa-users me-1"></i>Xem &amp; Phản Hồi
        </a>
    </div>
    @endif

    {{-- ── 3. KHU VỰC 4 THẺ CHỈ SỐ NHANH (KPI METRICS) ── --}}
    <div class="row g-3 mb-3">
        <!-- 1. Điều Kiện Khóa Luận -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Điều Kiện Khóa Luận</div>
                    <div class="metric-value mt-1">
                        {{ $sinhVien->SoTinChiTichLuy ?? 0 }} <span class="fs-6 text-muted fw-normal">TC</span>
                    </div>
                    <div class="mt-1.5">
                        @if($isDuDieuKien)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-circle-check me-1"></i>Đủ ĐK (GPA: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }})
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-circle-xmark me-1"></i>Thiếu ({{ $sinhVien->SoTinChiTichLuy ?? 0 }}/115 TC)
                            </span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>

        <!-- 2. Đề Tài Khóa Luận -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Đề Tài Khóa Luận</div>
                    <div class="metric-value mt-1 text-truncate" style="max-width: 140px;" title="{{ $deTai ? $deTai->MaDeTai : '' }}">
                        {{ $deTai ? $deTai->MaDeTai : ($nhom ? 'Chờ đăng ký' : 'Chưa có') }}
                    </div>
                    <div class="mt-1.5">
                        @if($deTai)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-check me-1"></i>Đã duyệt
                            </span>
                        @elseif($nhom)
                            <a href="{{ route('sinhvien.dangky.index') }}" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5 text-decoration-none">
                                <i class="fa-solid fa-arrow-right me-1"></i>Đăng ký
                            </a>
                        @else
                            <a href="{{ route('sinhvien.nhom.index') }}" class="badge badge-neutral rounded-pill py-1 px-2.5 text-decoration-none">
                                <i class="fa-solid fa-user-plus me-1"></i>Vào nhóm
                            </a>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>
        </div>

        <!-- 3. Nhóm Khóa Luận -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Nhóm Khóa Luận</div>
                    <div class="metric-value mt-1 text-truncate" style="max-width: 140px;" title="{{ $nhom ? $nhom->TenNhom : '' }}">
                        {{ $nhom ? $nhom->TenNhom : 'Chưa có nhóm' }}
                    </div>
                    <div class="mt-1.5">
                        @if($nhom)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-users me-1"></i>{{ $thanhViens->count() }}/3 Thành viên
                            </span>
                        @else
                            <a href="{{ route('sinhvien.nhom.index') }}" class="badge badge-neutral rounded-pill py-1 px-2.5 text-decoration-none">
                                <i class="fa-solid fa-plus me-1"></i>Tạo nhóm
                            </a>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <!-- 4. Tiến Độ Khóa Luận -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Tiến Độ Khóa Luận</div>
                    <div class="metric-value mt-1" style="color: var(--admin-primary);">
                        {{ $nhom ? $tienDoPhanTram . '%' : '--' }}
                    </div>
                    <div class="mt-1.5">
                        @if(!$nhom)
                            <span class="badge badge-neutral rounded-pill py-1 px-2.5">Chưa có nhóm</span>
                        @elseif($ketQua && $ketQua->DiemTongKet > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5">
                                Điểm: {{ $ketQua->DiemTongKet }} ({{ $ketQua->XepLoai }})
                            </span>
                        @elseif($hoSoBaoVe)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5">
                                {{ $hoSoBaoVe->TrangThai }}
                            </span>
                        @else
                            <span class="badge badge-neutral rounded-pill py-1 px-2.5">
                                {{ $soBaoCaoDat }}/5 đợt đạt
                            </span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-bars-progress"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. BỐ CỤC CHÍNH 2 CỘT CÂN BẰNG ── --}}
    <div class="row g-3 align-items-stretch">

        {{-- ── CỘT TRÁI (7 COLS): THÔNG TIN ĐỀ TÀI & HÀNH ĐỘNG ── --}}
        <div class="col-12 col-lg-7 d-flex flex-column gap-3">

            <!-- CARD 1: THÔNG TIN ĐỀ TÀI & HƯỚNG DẪN -->
            <div class="dashboard-card-panel flex-grow-1">
                <div class="panel-header-compact">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($deTai)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-circle-check me-1"></i>Đã Phê Duyệt
                            </span>
                        @elseif($nhom)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-users me-1"></i>Chờ Đăng Ký Đề Tài
                            </span>
                        @else
                            <span class="badge badge-neutral rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-circle-info me-1"></i>Chưa Có Nhóm
                            </span>
                        @endif

                        @if($nhom && $nhom->hocPhan)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5">
                                <i class="fa-solid fa-book me-1"></i>{{ $nhom->hocPhan->TenHocPhan }}
                            </span>
                        @endif

                        <span class="badge badge-neutral rounded-pill py-1 px-2.5">
                            <i class="fa-solid fa-building-columns me-1" style="color: var(--admin-primary);"></i>{{ $sinhVien->lop->nganh->TenNganh ?? 'Công nghệ thông tin' }}
                        </span>
                    </div>

                    <div class="fw-bold" style="color: var(--admin-text-primary); font-size: 0.85rem;">
                        Tiến độ: <span style="color: var(--admin-primary);">{{ $tienDoPhanTram }}%</span>
                    </div>
                </div>

                <div class="panel-body-compact d-flex flex-column justify-content-between flex-grow-1">
                    <!-- Tên Đề Tài -->
                    <div class="mb-3">
                        <div class="text-muted fw-semibold mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em;">Tên đề tài đăng ký</div>
                        <h6 class="fw-bold mb-0 text-dark" style="line-height: 1.5; font-size: 0.95rem;">
                            {{ $deTai->TenDeTai ?? ($nhom ? 'Nhóm chưa đăng ký đề tài khóa luận. Vui lòng chọn đề tài trong danh sách mở.' : 'Sinh viên chưa tham gia nhóm. Hãy tạo nhóm mới hoặc tìm kiếm nhóm mở.') }}
                        </h6>
                    </div>

                    <!-- 2 Cột nhỏ: GVHD & Thành Viên -->
                    <div class="row g-3 pt-3 border-top">
                        <!-- Giảng viên hướng dẫn -->
                        <div class="col-12 col-sm-5 border-end-sm">
                            <div class="text-muted fw-semibold mb-1" style="font-size: 0.75rem; text-transform: uppercase;">
                                <i class="fa-solid fa-chalkboard-user me-1" style="color: var(--admin-primary);"></i>GV Hướng dẫn:
                            </div>
                            <div class="fw-bold" style="color: var(--admin-text-primary); font-size: 0.875rem;">
                                @if($gvhd)
                                    {{ ($gvhd->HocHam ? $gvhd->HocHam . '.' : '') . ($gvhd->HocVi ? $gvhd->HocVi . '.' : '') . ' ' . $gvhd->HoTen }}
                                @else
                                    <span class="text-muted fw-normal fst-italic">Chưa phân công</span>
                                @endif
                            </div>
                            <div class="text-muted mt-1 text-truncate" style="font-size: 0.775rem;" title="{{ $gvhd->Email ?? '' }}">
                                <i class="fa-regular fa-envelope me-1"></i>{{ $gvhd->Email ?? 'Chưa có email' }}
                            </div>
                        </div>

                        <!-- Thành viên nhóm -->
                        <div class="col-12 col-sm-7">
                            <div class="text-muted fw-semibold mb-1" style="font-size: 0.75rem; text-transform: uppercase;">
                                <i class="fa-solid fa-user-group me-1" style="color: var(--admin-primary);"></i>Thành viên nhóm:
                            </div>
                            <div class="d-flex flex-wrap gap-1.5">
                                @if($truongNhom)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5" style="font-size: 0.75rem;">
                                        👑 {{ $truongNhom->HoTen }} (Trưởng nhóm)
                                    </span>
                                @endif

                                @forelse($thanhViens as $tv)
                                    @if(!$truongNhom || $tv->MaSV !== $truongNhom->MaSV)
                                        <span class="badge badge-neutral rounded-pill py-1 px-2.5" style="font-size: 0.75rem;">
                                            {{ $tv->sinhVien->HoTen ?? $tv->MaSV }}
                                        </span>
                                    @endif
                                @empty
                                    @if(!$truongNhom)
                                        <span class="text-muted fst-italic" style="font-size: 0.8rem;">Chưa có thành viên</span>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Thanh Progress Bar Mảnh -->
                    @if($nhom)
                    <div class="mt-3 pt-2">
                        <div class="progress" style="height: 6px; background-color: var(--admin-border-soft); border-radius: 10px;">
                            <div class="progress-bar progress-bar-cyan" role="progressbar" 
                                 style="width: {{ $tienDoPhanTram }}%; border-radius: 10px;"
                                 aria-valuenow="{{ $tienDoPhanTram }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- CARD 2: KẾ HOẠCH & CÔNG VIỆC TIẾP THEO -->
            <div class="dashboard-card-panel">
                <div class="panel-header-compact">
                    <div class="fw-bold d-flex align-items-center gap-2" style="color: var(--admin-text-primary); font-size: 0.875rem;">
                        <i class="fa-solid fa-list-check" style="color: var(--admin-primary);"></i>
                        <span>Kế Hoạch &amp; Công Việc Tiếp Theo</span>
                    </div>

                    @if($nhom && $nextMoc)
                        <span class="badge {{ $daysRemaining !== null && $daysRemaining < 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} rounded-pill py-1 px-2.5" style="font-size: 0.75rem;">
                            <i class="fa-regular fa-clock me-1"></i>
                            @if($daysRemaining !== null)
                                {{ $daysRemaining >= 0 ? "Còn {$daysRemaining} ngày" : "Quá hạn " . abs($daysRemaining) . " ngày" }}
                            @else
                                Hạn: {{ \Carbon\Carbon::parse($nextMoc->NgayKetThuc)->format('d/m/Y') }}
                            @endif
                        </span>
                    @endif
                </div>

                <div class="panel-body-compact">
                    @if($nhom && $nextMoc)
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold mb-1" style="color: var(--admin-text-primary); font-size: 0.9rem;">
                                    {{ $nextMoc->TenMoc ?? ('Báo Cáo Tiến Độ Đợt ' . $mocTiepTheo) }}
                                </div>
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-calendar-xmark me-1 text-danger"></i>Hạn chót: <strong>{{ \Carbon\Carbon::parse($nextMoc->NgayKetThuc)->format('d/m/Y') }}</strong>
                                    &bull; Giai đoạn: <span class="badge badge-neutral py-0.5 px-2" style="font-size: 0.725rem;">{{ $nextMoc->LoaiGiaiDoan ?? 'Thực hiện' }}</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                @if(($nextMoc->LoaiGiaiDoan ?? '') === 'TAO_NHOM')
                                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                                        <i class="fa-solid fa-users me-1"></i>Quản lý nhóm
                                    </a>
                                @elseif(($nextMoc->LoaiGiaiDoan ?? '') === 'DANG_KY_DE_TAI')
                                    <a href="{{ route('sinhvien.dangky.index') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                                        <i class="fa-solid fa-book-bookmark me-1"></i>Đăng ký đề tài
                                    </a>
                                @elseif(in_array($nextMoc->LoaiGiaiDoan ?? '', ['THUC_HIEN', 'NOP_BAO_CAO']))
                                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                                        <i class="fa-solid fa-upload me-1"></i>Nộp báo cáo ngay
                                    </a>
                                @else
                                    <a href="{{ route('sinhvien.calendar') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                                        <i class="fa-solid fa-calendar-check me-1"></i>Xem kế hoạch
                                    </a>
                                @endif
                                <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-calendar-week me-1"></i>Lịch
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold mb-1" style="color: var(--admin-text-primary);">Chưa có nhóm khóa luận</div>
                                <div class="text-muted" style="font-size: 0.8rem;">Thời hạn nộp báo cáo sẽ kích hoạt sau khi bạn tạo hoặc gia nhập nhóm.</div>
                            </div>
                            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                                <i class="fa-solid fa-users me-1"></i>Gia nhập nhóm
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── CỘT PHẢI (5 COLS): NHẬN XÉT GVHD & LỊCH HẸN BẢO VỆ ── --}}
        <div class="col-12 col-lg-5">
            <div class="dashboard-card-panel h-100 justify-content-between">
                <div>
                    <div class="panel-header-compact">
                        <div class="fw-bold d-flex align-items-center gap-2" style="color: var(--admin-text-primary); font-size: 0.875rem;">
                            <i class="fa-solid fa-comments" style="color: var(--admin-primary);"></i>
                            <span>Hoạt Động &amp; Nhận Xét GVHD</span>
                        </div>
                        <a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none fw-semibold" style="font-size: 0.8rem; color: var(--admin-primary);">
                            Xem tất cả &rarr;
                        </a>
                    </div>

                    <div class="panel-body-compact">
                        <!-- PHẦN 1: NHẬN XÉT MỚI NHẤT -->
                        <div class="p-3 rounded-3 border mb-3" style="background-color: var(--admin-neutral-soft); border-color: var(--admin-border) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5" style="font-size: 0.725rem;">
                                    {{ $latestFeedback ? 'Đợt ' . $latestFeedback->LanBaoCao . ': ' . Str::limit($latestFeedback->TieuDe, 24) : 'Phản hồi mới nhất' }}
                                </span>
                                @if($latestFeedback && $latestFeedback->TrangThai === 'Đạt')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5" style="font-size: 0.725rem;">
                                        <i class="fa-solid fa-check me-1"></i>Đạt
                                    </span>
                                @elseif($latestFeedback)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5" style="font-size: 0.725rem;">
                                        {{ $latestFeedback->TrangThai }}
                                    </span>
                                @endif
                            </div>

                            @if($latestFeedback && $latestFeedback->NhanXet)
                                <div class="text-dark fst-italic" style="font-size: 0.825rem; line-height: 1.5;">
                                    "{!! Str::limit($latestFeedback->NhanXet, 120) !!}"
                                </div>
                                <div class="text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                                    <span><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($latestFeedback->updated_at)->format('H:i - d/m/Y') }}</span>
                                    @if($latestFeedback->DiemSo)
                                        <span class="fw-bold" style="color: var(--admin-primary);">Điểm: {{ $latestFeedback->DiemSo }}/10</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted text-center py-2" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-comment-dots text-secondary me-1"></i>Chưa có nhận xét mới từ Giảng viên hướng dẫn.
                                </div>
                            @endif
                        </div>

                        <!-- PHẦN 2: LỊCH HẸN GẶP & LỊCH HỘI ĐỒNG BẢO VỆ -->
                        <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between" style="font-size: 0.8rem; color: var(--admin-text-primary);">
                            <span><i class="fa-regular fa-calendar-check me-1" style="color: var(--admin-primary);"></i>Lịch hẹn gặp &amp; Bảo vệ Hội đồng:</span>
                            <a href="{{ route('sinhvien.calendar') }}" class="text-muted text-decoration-none" style="font-size: 0.75rem;">Lịch biểu &rarr;</a>
                        </div>

                        <div class="sv-smooth-scroll" style="max-height: 150px; overflow-y: auto; padding-right: 4px;">
                            <!-- Lịch Hội Đồng Bảo Vệ (Nếu có) -->
                            @if($hoSoBaoVe && $hoSoBaoVe->hoiDong)
                                <div class="p-2.5 rounded-3 border mb-2" style="background-color: var(--admin-primary-soft); border-color: var(--admin-info-border) !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong style="color: var(--admin-text-primary); font-size: 0.825rem;">
                                            <i class="fa-solid fa-building-columns me-1"></i>{{ $hoSoBaoVe->hoiDong->TenHoiDong }}
                                        </strong>
                                        <span class="badge bg-success rounded-pill py-0.5 px-2" style="font-size: 0.7rem;">Bảo Vệ</span>
                                    </div>
                                    <div class="text-dark" style="font-size: 0.775rem;">
                                        <span><i class="fa-regular fa-clock me-1 text-primary"></i>{{ $hoSoBaoVe->hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoSoBaoVe->hoiDong->ThoiGianBatDau)->format('H:i - d/m/Y') : 'Chưa xếp lịch' }}</span>
                                        &bull; <span>Phòng: <strong>{{ $hoSoBaoVe->hoiDong->DiaDiem ?? $hoSoBaoVe->PhongBaoVe ?? 'Chưa công bố' }}</strong></span>
                                    </div>
                                </div>
                            @endif

                            <!-- Lịch Hướng Dẫn Sắp Tới -->
                            @forelse($lichGaps as $lg)
                                <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="font-size: 0.8rem;">
                                    <span class="badge badge-neutral py-1.5 px-2 rounded text-center" style="min-width: 46px; font-size: 0.725rem;">
                                        {{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->format('d/m') }}
                                    </span>
                                    <div class="flex-grow-1 text-truncate">
                                        <div class="fw-semibold text-dark text-truncate">{{ $lg->NoiDung ?? 'Trao đổi tiến độ đề tài' }}</div>
                                        <div class="text-muted text-truncate" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $lg->DiaDiem ?? 'Bộ môn' }} &bull; {{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                @if(!$hoSoBaoVe || !$hoSoBaoVe->hoiDong)
                                    <div class="text-muted text-center py-2" style="font-size: 0.775rem;">
                                        <i class="fa-solid fa-info-circle me-1" style="color: var(--admin-primary);"></i>Chưa có lịch hẹn gặp mới với GVHD.
                                    </div>
                                @endif
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- FOOTER CARD PHẢI -->
                <div class="panel-header-compact border-top bg-light" style="border-bottom: none;">
                    <div class="d-flex gap-2 w-100">
                        <a href="{{ route('sinhvien.hoso.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-50 justify-content-center d-flex align-items-center">
                            <i class="fa-solid fa-file-circle-check me-1"></i>Hồ sơ bảo vệ
                        </a>
                        <a href="{{ route('sinhvien.calendar') }}" class="btn btn-primary btn-sm rounded-pill w-50 justify-content-center d-flex align-items-center" style="background-color: var(--admin-primary); border-color: var(--admin-primary);">
                            <i class="fa-solid fa-calendar-days me-1"></i>Xem toàn bộ lịch
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
