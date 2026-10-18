@extends('layouts.sinhvien')

@section('page_title', 'Tổng Quan Học Tập & Khóa Luận')

@section('content')
<style>
    :root {
        --huit-navy: #00305a;
        --huit-navy-dark: #002240;
        --huit-blue: #0284c7;
        --huit-blue-light: #e0f2fe;
        --huit-green: #10b981;
        --huit-bg: #f8fafc;
        --huit-border: #e2e8f0;
    }
    /* 1. Loại bỏ gạch chân cho các nút bấm trong Banner */
.btn-banner-action, 
.btn-banner-primary {
    text-decoration: none !important;
}

/* 2. Loại bỏ gạch chân cho các nút bấm thông thường (Chi tiết đề tài, Chi tiết nhóm, Nộp bài, v.v.) */
.sv-dashboard .btn,
.sv-dashboard a.btn {
    text-decoration: none !important;
}

    .sv-dashboard {
        font-size: 0.85rem;
        color: #334155;
    }

    /* ── BANNER CHÀO MỪNG ── */
    .welcome-banner {
        background: linear-gradient(135deg, #00305a 0%, #004d8c 60%, #0284c7 100%);
        border-radius: 1.15rem;
        color: #ffffff;
        padding: 1.75rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 48, 90, 0.12);
        margin-bottom: 1.75rem;
    }
    .welcome-banner::after {
        content: "";
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }
    .welcome-title {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        margin-bottom: 0.5rem;
    }
    .welcome-desc {
        color: #e2e8f0;
        font-size: 0.875rem;
        max-width: 780px;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    .btn-banner-action {
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.35);
        backdrop-filter: blur(4px);
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.55rem 1.15rem;
        border-radius: 50rem;
        transition: all 0.25s ease;
    }
    .btn-banner-action:hover {
        background-color: #ffffff;
        color: var(--huit-navy);
        border-color: #ffffff;
        transform: translateY(-2px);
    }
    .btn-banner-primary {
        background-color: #ffffff;
        color: var(--huit-navy);
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.55rem 1.25rem;
        border-radius: 50rem;
        border: 1px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.25s ease;
    }
    .btn-banner-primary:hover {
        background-color: #f1f5f9;
        color: #002240;
        transform: translateY(-2px);
    }

    /* ── 5 THẺ CHỈ SỐ KPI ── */
    .kpi-card-box {
        background: #ffffff;
        border: 1px solid var(--huit-border);
        border-radius: 1rem;
        padding: 1.15rem 1.35rem;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 1px 3px rgba(0, 48, 90, 0.02);
    }
    .kpi-card-box:hover {
        transform: translateY(-3px);
        border-color: var(--huit-blue);
        box-shadow: 0 10px 24px -4px rgba(2, 132, 199, 0.15);
        color: inherit;
    }
    .kpi-card-box .kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        background: #f1f5f9;
        color: var(--huit-navy);
        transition: all 0.25s ease;
    }
    .kpi-card-box:hover .kpi-icon {
        background: var(--huit-navy);
        color: #ffffff;
    }
    .kpi-label {
        font-size: 0.725rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .kpi-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--huit-navy);
        line-height: 1.3;
        margin-bottom: 0.35rem;
    }

    /* ── CARD PANELS ── */
    .sv-panel {
        background: #ffffff;
        border: 1px solid var(--huit-border);
        border-radius: 1.15rem;
        box-shadow: 0 1px 3px rgba(0, 48, 90, 0.02);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    .sv-panel:hover {
        box-shadow: 0 8px 24px rgba(0, 48, 90, 0.05);
        border-color: #cbd5e1;
    }
    .sv-panel-header {
        padding: 1.25rem 1.5rem;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sv-panel-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--huit-navy);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .sv-panel-body {
        padding: 1.5rem;
    }

    /* ── ACTION ITEMS LIST ── */
    .action-item-card {
        border-radius: 0.85rem;
        padding: 1.15rem 1.35rem;
        margin-bottom: 1rem;
        background: #ffffff;
        border: 1px solid var(--huit-border);
        border-left-width: 4px;
        transition: all 0.25s ease;
    }
    .action-item-card:last-child { margin-bottom: 0; }
    .action-item-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
        border-color: #cbd5e1;
    }
    .action-item-card.border-level-danger { border-left-color: #ef4444 !important; background-color: #fffafb; }
    .action-item-card.border-level-warning { border-left-color: #f59e0b !important; background-color: #fffdfa; }
    .action-item-card.border-level-info { border-left-color: #0284c7 !important; background-color: #f9fcff; }
    .action-item-card.border-level-primary { border-left-color: #00305a !important; }

    /* ── STEPPER 6 GIAI ĐOẠN ── */
    .business-stepper-wrap { padding: 0.75rem 0 1rem 0; }
    .business-step-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        position: relative;
    }
    .business-step-col {
        position: relative;
        text-align: center;
        padding: 0.85rem 0.5rem;
        border-radius: 0.85rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .business-step-col.active-stage {
        background-color: #f0f9ff;
        border-color: #bae6fd;
        box-shadow: 0 3px 10px rgba(2, 132, 199, 0.08);
    }
    .step-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        margin: 0 auto 8px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        background: #f1f5f9;
        border: 2px solid #cbd5e1;
        color: #64748b;
        transition: all 0.3s ease;
    }
    .step-circle.completed { background: #10b981; border-color: #10b981; color: #ffffff; }
    .step-circle.current { background: #0284c7; border-color: #0284c7; color: #ffffff; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.2); }
    .step-name-text {
        font-size: 0.775rem;
        font-weight: 700;
        line-height: 1.3;
        color: #1e293b;
        margin-bottom: 0.35rem;
    }
    .step-status-tag {
        font-size: 0.7rem;
        padding: 2.5px 8px;
        border-radius: 20px;
        display: inline-block;
    }

    /* ── TIMELINE ── */
    .activity-timeline { position: relative; padding-left: 24px; }
    .activity-timeline::before {
        content: "";
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 9px;
        width: 2px;
        background-color: #e2e8f0;
    }
    .activity-node { position: relative; margin-bottom: 16px; }
    .activity-node:last-child { margin-bottom: 0; }
    .activity-dot {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        background: #ffffff;
        border: 2px solid var(--huit-blue);
        z-index: 2;
    }

    .sv-scroll::-webkit-scrollbar { width: 5px; }
    .sv-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .sv-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    @media (max-width: 991.98px) {
        .business-step-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; }
    }
    @media (max-width: 575.98px) {
        .business-step-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="sv-dashboard">

    {{-- 1. BANNER CHÀO MỪNG --}}
    <div class="welcome-banner">
        <div class="row align-items-center g-4">
            <div class="col-12 col-xl-8">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge bg-white text-dark fw-bold rounded-pill px-3 py-1 text-uppercase" style="font-size: 0.725rem; letter-spacing: 0.04em;">
                        <i class="fa-solid fa-user-graduate me-2 text-primary"></i>Cổng Sinh Viên
                    </span>
                    <span class="badge rounded-pill px-3 py-1 text-white border border-white-50" style="background-color: rgba(255,255,255,0.18); font-size: 0.725rem;">
                        <i class="fa-regular fa-calendar-check me-2"></i>{{ $hocKyHienTai->TenHocKy ?? 'Học kỳ hiện tại' }}
                    </span>
                    @if($deTai)
                        <span class="badge bg-success rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                            <i class="fa-solid fa-circle-check me-2"></i>Đang thực hiện đề tài
                        </span>
                    @elseif($nhom)
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                            <i class="fa-solid fa-users me-2"></i>Đã có nhóm &bull; Chờ đăng ký đề tài
                        </span>
                    @else
                        <span class="badge bg-secondary rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                            <i class="fa-solid fa-circle-info me-2"></i>Chưa tham gia nhóm
                        </span>
                    @endif
                </div>

                <h1 class="welcome-title">
                    Tổng Quan Học Tập &amp; Khóa Luận
                </h1>

                <div class="welcome-desc">
                    Chào mừng bạn quay trở lại, <strong>{{ $sinhVien->HoTen }}</strong>! Theo dõi thông tin khóa luận, cập nhật tiến độ thực hiện, nộp báo cáo và quản lý các công việc cần hoàn thành trong học kỳ.
                </div>

                <div class="d-flex align-items-center gap-3 text-white-50 flex-wrap" style="font-size: 0.8rem;">
                    <span><i class="fa-solid fa-id-card me-2 text-white"></i>MSSV: <strong class="text-white">{{ $sinhVien->MaSV }}</strong></span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-chalkboard-user me-2 text-white"></i>Lớp: <strong class="text-white">{{ $sinhVien->lop->TenLop ?? 'Chưa phân lớp' }}</strong></span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-building-columns me-2 text-white"></i>Khoa: <strong class="text-white">{{ $sinhVien->khoa->TenKhoa ?? 'Công nghệ Thông tin' }}</strong></span>
                </div>
            </div>

           <div class="col-12 col-xl-4 text-xl-end d-flex flex-wrap gap-2 justify-content-xl-end">
    @if($deTai)
        <a href="{{ route('sinhvien.baocao.index') }}" class="btn-banner-primary d-inline-flex align-items-center">
            <i class="fa-solid fa-upload me-2 text-primary"></i>Nộp Báo Cáo
        </a>
        <a href="{{ route('sinhvien.dangky.index') }}" class="btn-banner-action d-inline-flex align-items-center">
            <i class="fa-solid fa-book-open me-2"></i>Xem Đề Tài
        </a>
    @elseif($nhom)
        <a href="{{ route('sinhvien.dangky.index') }}" class="btn-banner-primary d-inline-flex align-items-center">
            <i class="fa-solid fa-book-bookmark me-2 text-primary"></i>Đăng Ký Đề Tài
        </a>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn-banner-action d-inline-flex align-items-center">
            <i class="fa-solid fa-users me-2"></i>Nhóm Của Tôi
        </a>
    @else
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn-banner-primary d-inline-flex align-items-center">
            <i class="fa-solid fa-user-plus me-2 text-primary"></i>Tạo / Gia Nhập Nhóm
        </a>
        <a href="{{ route('sinhvien.dangky.index') }}" class="btn-banner-action d-inline-flex align-items-center">
            <i class="fa-solid fa-list-ul me-2"></i>Xem Danh Mục
        </a>
    @endif
    <a href="{{ route('sinhvien.calendar') }}" class="btn-banner-action d-inline-flex align-items-center">
        <i class="fa-regular fa-calendar-days me-2"></i>Lịch Trình
    </a>
</div>
        </div>
    </div>

    {{-- ── BỘ CHUYỂN ĐỔI NHÓM / HỌC PHẦN (CHO SINH VIÊN THAM GIA NHIỀU ĐỀ TÀI) ── --}}
    @if(isset($allStudentMemberships) && $allStudentMemberships->count() > 1)
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white" style="width: 44px; height: 44px; font-size: 1.2rem; flex-shrink: 0;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                Bạn đang tham gia {{ $allStudentMemberships->count() }} đề tài / học phần trong học kỳ
                            </h6>
                            <span class="text-secondary small">
                                Chọn học phần &amp; đề tài bạn muốn theo dõi tiến độ hoặc nộp báo cáo. Tiến độ và lịch sử được quản lý độc lập.
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @foreach($allStudentMemberships as $mb)
                            @php
                                $mNhom = $mb->nhom;
                                $mHp = $mNhom->hocPhan;
                                $mDt = $mNhom->dangKyDeTai?->deTai;
                                $isActive = ($nhom && $nhom->MaNhom === $mNhom->MaNhom);
                            @endphp
                            <a href="{{ route('sinhvien.dashboard', ['nhom_id' => $mNhom->MaNhom]) }}" 
                               class="btn btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 transition-all {{ $isActive ? 'btn-primary shadow-sm' : 'btn-outline-primary bg-white' }}">
                                @if($isActive)
                                    <i class="fa-solid fa-circle-check"></i>
                                @else
                                    <i class="fa-regular fa-circle"></i>
                                @endif
                                <span>{{ $mHp->TenHocPhan ?? $mNhom->TenNhom }}</span>
                                @if($mDt)
                                    <span class="badge {{ $isActive ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} rounded-pill font-monospace" style="font-size: 10px;">
                                        {{ $mDt->MaDeTai }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 2. THÔNG BÁO LỜI MỜI NHÓM --}}
    @if(isset($loiMois) && $loiMois->count() > 0)
    <div class="alert alert-warning border-warning shadow-sm py-2 px-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-3" style="font-size: 0.85rem;">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-envelope-open-text fs-4 text-warning"></i>
            <div>
                <strong class="text-dark">Bạn có {{ $loiMois->count() }} lời mời tham gia nhóm khóa luận:</strong>
                <span class="text-muted ms-1">
                    Từ Trưởng nhóm <strong>{{ $loiMois->first()->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (Nhóm: {{ $loiMois->first()->nhom->TenNhom ?? '' }}).
                </span>
            </div>
        </div>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-bold text-dark shadow-xs d-inline-flex align-items-center" style="font-size: 0.8rem;">
            <i class="fa-solid fa-users me-2"></i>Xem &amp; Phản Hồi Ngay
        </a>
    </div>
    @endif

    {{-- 3. 5 THẺ CHỈ SỐ KPI --}}
    <div class="row g-3 mb-4">
        <!-- 1. Đề tài của tôi -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <a href="{{ $kpiDeTai['link'] }}" class="kpi-card-box">
                <div class="overflow-hidden">
                    <div class="kpi-label">{{ $kpiDeTai['title'] }}</div>
                    <div class="kpi-value text-truncate" title="{{ $deTai?->TenDeTai ?? $kpiDeTai['value'] }}">
                        {{ $kpiDeTai['value'] }}
                    </div>
                    <div>
                        <span class="badge {{ $kpiDeTai['badge_class'] }} rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                            {{ $kpiDeTai['badge'] }}
                        </span>
                    </div>
                </div>
                <div class="kpi-icon"><i class="{{ $kpiDeTai['icon'] }}"></i></div>
            </a>
        </div>

        <!-- 2. Tiến độ khóa luận -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <a href="{{ $kpiTienDo['link'] }}" class="kpi-card-box">
                <div class="overflow-hidden">
                    <div class="kpi-label">{{ $kpiTienDo['title'] }}</div>
                    <div class="kpi-value">{{ $kpiTienDo['value'] }}</div>
                    <div>
                        <span class="badge {{ $kpiTienDo['badge_class'] }} rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                            {{ $kpiTienDo['badge'] }}
                        </span>
                    </div>
                </div>
                <div class="kpi-icon"><i class="{{ $kpiTienDo['icon'] }}"></i></div>
            </a>
        </div>

        <!-- 3. Báo cáo tiến độ -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <a href="{{ $kpiBaoCao['link'] }}" class="kpi-card-box">
                <div class="overflow-hidden">
                    <div class="kpi-label">{{ $kpiBaoCao['title'] }}</div>
                    <div class="kpi-value">{{ $kpiBaoCao['value'] }}</div>
                    <div>
                        <span class="badge {{ $kpiBaoCao['badge_class'] }} rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                            {{ $kpiBaoCao['badge'] }}
                        </span>
                    </div>
                </div>
                <div class="kpi-icon"><i class="{{ $kpiBaoCao['icon'] }}"></i></div>
            </a>
        </div>

        <!-- 4. Công việc cần thực hiện -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <a href="{{ $kpiCongViec['link'] }}" class="kpi-card-box">
                <div class="overflow-hidden">
                    <div class="kpi-label">{{ $kpiCongViec['title'] }}</div>
                    <div class="kpi-value">{{ $kpiCongViec['value'] }}</div>
                    <div>
                        <span class="badge {{ $kpiCongViec['badge_class'] }} rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                            {{ $kpiCongViec['badge'] }}
                        </span>
                    </div>
                </div>
                <div class="kpi-icon"><i class="{{ $kpiCongViec['icon'] }}"></i></div>
            </a>
        </div>

        <!-- 5. Thông báo mới -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <a href="{{ $kpiThongBao['link'] }}" class="kpi-card-box">
                <div class="overflow-hidden">
                    <div class="kpi-label">{{ $kpiThongBao['title'] }}</div>
                    <div class="kpi-value">{{ $kpiThongBao['value'] }}</div>
                    <div>
                        <span class="badge {{ $kpiThongBao['badge_class'] }} rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                            {{ $kpiThongBao['badge'] }}
                        </span>
                    </div>
                </div>
                <div class="kpi-icon"><i class="{{ $kpiThongBao['icon'] }}"></i></div>
            </a>
        </div>
    </div>

    {{-- 4. BỐ CỤC CHÍNH 2 CỘT --}}
    <div class="row g-4">

        {{-- CỘT TRÁI --}}
        <div class="col-12 col-lg-7 col-xl-8 d-flex flex-column gap-4">

            <!-- VIỆC CẦN LÀM -->
            <div class="sv-panel mb-0" id="action-items-section">
                <div class="sv-panel-header">
                    <div class="sv-panel-title">
                        <i class="fa-solid fa-list-check text-primary me-2"></i>
                        <span>Việc Cần Làm &amp; Nhiệm Vụ Ưu Tiên</span>
                    </div>
                    <div>
                        @if($actionItems->isNotEmpty())
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill py-2 px-3" style="font-size: 0.725rem;">
                                <i class="fa-solid fa-bell me-2"></i>{{ $actionItems->count() }} việc cần xử lý
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-2 px-3" style="font-size: 0.725rem;">
                                <i class="fa-solid fa-circle-check me-2"></i>Đã hoàn thành
                            </span>
                        @endif
                    </div>
                </div>

                <div class="sv-panel-body">
                    @forelse($actionItems as $item)
                        <div class="action-item-card border-level-{{ $item['level'] }}">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        @if($item['level'] === 'danger')
                                            <span class="badge bg-danger rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $item['badge'] }}
                                            </span>
                                        @elseif($item['level'] === 'warning')
                                            <span class="badge bg-warning text-dark rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-clock me-2"></i>{{ $item['badge'] }}
                                            </span>
                                        @elseif($item['level'] === 'info')
                                            <span class="badge bg-info text-dark rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-spinner me-2"></i>{{ $item['badge'] }}
                                            </span>
                                        @else
                                            <span class="badge bg-primary rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif

                                        <strong class="text-dark" style="font-size: 0.9rem;">{{ $item['title'] }}</strong>
                                    </div>

                                    <div class="text-muted mb-2" style="font-size: 0.8rem; line-height: 1.5;">
                                        {{ $item['desc'] }}
                                    </div>

                                    @if(!empty($item['deadline']))
                                        <div class="text-danger fw-semibold" style="font-size: 0.775rem;">
                                            <i class="fa-regular fa-clock me-2"></i>Hạn chót: {{ $item['deadline'] }}
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <a href="{{ $item['action_url'] }}" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold {{ $item['action_class'] }}" style="font-size: 0.775rem;">
                                        {{ $item['action_label'] }} &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 px-3">
                            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h6 class="fw-bold text-success mb-1" style="font-size: 0.95rem;">Bạn đã hoàn thành các công việc hiện tại!</h6>
                            <p class="text-muted small mb-0">Không có công việc hoặc báo cáo nào đang bị quá hạn.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TIẾN ĐỘ KHÓA LUẬN -->
            <div class="sv-panel mb-0" id="progress-section">
                <div class="sv-panel-header">
                    <div class="sv-panel-title">
                        <i class="fa-solid fa-bars-progress text-primary me-2"></i>
                        <span>Tiến Độ Khóa Luận Của Tôi</span>
                    </div>
                    <div class="fw-bold" style="color: var(--huit-navy); font-size: 0.9rem;">
                        Tổng thể: <span style="color: var(--huit-blue);">{{ $tienDoPhanTram }}%</span>
                    </div>
                </div>

                <div class="sv-panel-body">

                    <div class="alert alert-info py-2 px-3 mb-4 d-flex align-items-center gap-2 rounded-3 border-info" style="font-size: 0.8rem; background-color: #f0f9ff;">
                        <i class="fa-solid fa-circle-info text-info fs-5"></i>
                        <div>
                            <strong>Quy chuẩn tiến độ:</strong> Gồm (1) <strong>6 Giai đoạn nghiệp vụ học phần</strong> (cấp Khoa/Bộ môn) và (2) <strong>Các đợt báo cáo chuyên môn</strong> trực tiếp với GVHD.
                        </div>
                    </div>

                    <!-- 6 GIAI ĐOẠN NGHIỆP VỤ -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="fw-bold text-dark text-uppercase" style="font-size: 0.775rem; letter-spacing: 0.04em; color: var(--huit-navy);">
                                <i class="fa-solid fa-building-columns me-2" style="color: var(--huit-blue);"></i>1. Tiến độ nghiệp vụ học phần (Cấp Khoa/Bộ môn)
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-3" style="font-size: 0.725rem;">
                                Đang ở GĐ {{ $giaiDoanHienTaiNghiepVu }}/6
                            </span>
                        </div>

                        <div class="business-stepper-wrap">
                            <div class="business-step-grid">
                                @php
                                    $dsGiaiDoan = \App\Models\TienDoNghiepVuKhoaLuan::DANH_SACH_GIAI_DOAN;
                                @endphp

                                @foreach($dsGiaiDoan as $stgNum => $stgInfo)
                                    @php
                                        $isCompleted = isset($dieuKienNghiepVu[$stgNum]) && $dieuKienNghiepVu[$stgNum]['dat'];
                                        $isCurrent = ($giaiDoanHienTaiNghiepVu == $stgNum && !$isCompleted);
                                        $condMsg = $dieuKienNghiepVu[$stgNum]['mo_ta'] ?? ($isCompleted ? 'Đã hoàn thành' : 'Chưa đến lượt');
                                    @endphp
                                    <div class="business-step-col {{ $isCurrent ? 'active-stage' : '' }}" title="{{ $stgInfo['mo_ta'] }}">
                                        <div class="step-circle {{ $isCompleted ? 'completed' : ($isCurrent ? 'current' : '') }}">
                                            @if($isCompleted) <i class="fa-solid fa-check me-2"></i> @else {{ $stgNum }} @endif
                                        </div>

                                        <div class="step-name-text">{{ $stgInfo['short'] }}</div>

                                        <div>
                                            @if($isCompleted)
                                                <span class="step-status-tag bg-success-subtle text-success border border-success-subtle fw-semibold">Đạt</span>
                                            @elseif($isCurrent)
                                                <span class="step-status-tag bg-primary text-white fw-bold">Đang làm</span>
                                            @else
                                                <span class="step-status-tag bg-light text-muted border">Chờ</span>
                                            @endif
                                        </div>

                                        <div class="text-muted mt-2 text-truncate" style="font-size: 0.65rem;" title="{{ $condMsg }}">
                                            {{ Str::limit($condMsg, 20) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Chi Tiết Phần Trăm & Thanh Tiến Độ Từng Giai Đoạn --}}
                    @if(!empty($stageBreakdown))
                        <div class="mb-4">
                            @include('partials.stage_progress_bars', ['stages' => $stageBreakdown, 'showOverall' => false])
                        </div>
                    @endif

                    <!-- BÁO CÁO CHUYÊN MÔN VỚI GVHD -->
                    <div class="pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div>
                                <div class="fw-bold text-dark text-uppercase" style="font-size: 0.775rem; letter-spacing: 0.04em; color: var(--huit-navy);">
                                    <i class="fa-solid fa-laptop-code me-2" style="color: var(--huit-blue);"></i>2. Tiến độ báo cáo chuyên môn với GVHD
                                </div>
                                <div class="text-muted" style="font-size: 0.775rem;">
                                    Các mốc nộp sản phẩm &amp; tài liệu trực tiếp cho Giảng viên hướng dẫn
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-2 px-3" style="font-size: 0.725rem;">
                                <i class="fa-solid fa-check-double me-2"></i>Đạt {{ $soBaoCaoDat }}/{{ $tongDotNop ?? ($allDotNops->count() > 0 ? $allDotNops->count() : 5) }} đợt
                            </span>
                        </div>

                        @if($allDotNops->isNotEmpty())
                            <div class="d-flex flex-column gap-2">
                                @foreach($allDotNops as $dot)
                                    @php
                                        $latest = $dot->phienBanMoiNhat;
                                        $infoStatus = $dot->getTrangThaiHienThi();
                                    @endphp
                                    <div class="p-3 rounded-3 border bg-light d-flex justify-content-between align-items-center flex-wrap gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" 
                                                 style="width: 30px; height: 30px; font-size: 0.75rem; background-color: {{ $infoStatus['code'] === 'DAT' ? '#10b981' : ($infoStatus['code'] === 'NOP_LAI' ? '#f59e0b' : ($infoStatus['code'] === 'CHO_DUYET' ? '#0284c7' : '#94a3b8')) }};">
                                                @if($infoStatus['code'] === 'DAT') <i class="fa-solid fa-check me-2"></i>
                                                @elseif($infoStatus['code'] === 'NOP_LAI') <i class="fa-solid fa-exclamation me-2"></i>
                                                @elseif($infoStatus['code'] === 'CHO_DUYET') <i class="fa-solid fa-hourglass-half me-2"></i>
                                                @else {{ $loop->iteration }} @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $dot->TenDotNop }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    @if($dot->HanNop)
                                                        <i class="fa-regular fa-clock me-2"></i>Hạn: <strong>{{ $dot->HanNop->format('d/m/Y') }}</strong> &bull;
                                                    @endif
                                                    <span class="badge {{ $infoStatus['badge'] }} py-1 px-2" style="font-size: 0.675rem;">
                                                        {{ $infoStatus['label'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            @if($dot->isMoNop() && (!$latest || in_array($latest->TrangThai, ['Yêu cầu nộp lại', 'Cần bổ sung'])))
                                                <a href="{{ route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop) }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2" style="font-size: 0.775rem; background-color: var(--huit-blue); border-color: var(--huit-blue);">
                                                    <i class="fa-solid fa-upload me-2"></i>Nộp bài
                                                </a>
                                            @else
                                                <a href="{{ route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2" style="font-size: 0.775rem;">
                                                    <i class="fa-solid fa-eye me-2"></i>Chi tiết
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2">
                                @foreach(range(1, 5) as $lan)
                                    @php $bc = $baoCaos->firstWhere('LanBaoCao', $lan); @endphp
                                    <div class="p-3 rounded-3 border bg-light d-flex justify-content-between align-items-center flex-wrap gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                 style="width: 30px; height: 30px; font-size: 0.75rem; background-color: {{ ($bc && $bc->TrangThai === 'Đạt') ? '#10b981' : ($bc ? '#0284c7' : '#94a3b8') }};">
                                                @if($bc && $bc->TrangThai === 'Đạt') <i class="fa-solid fa-check me-2"></i> @else {{ $lan }} @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                    Báo cáo đợt {{ $lan }}: {{ $bc->TieuDe ?? ('Mốc báo cáo ' . $lan) }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    @if($bc) <span>Trạng thái: <strong>{{ $bc->TrangThai }}</strong></span>
                                                    @else <span class="badge bg-secondary py-1 px-2" style="font-size: 0.675rem;">Chưa nộp</span> @endif
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2" style="font-size: 0.775rem;">
                                            {{ $bc ? 'Xem bài' : 'Nộp' }} &rarr;
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3 pt-2">
                            <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.775rem;">
                                <span>Tiến độ tổng thể học phần</span>
                                <span class="fw-bold text-primary">{{ $tienDoPhanTram }}%</span>
                            </div>
                            <div class="progress" style="height: 7px; background-color: #f1f5f9; border-radius: 10px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $tienDoPhanTram }}%; border-radius: 10px; background: linear-gradient(90deg, #0284c7 0%, #00305a 100%);"
                                     aria-valuenow="{{ $tienDoPhanTram }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- CỘT PHẢI --}}
        <div class="col-12 col-lg-5 col-xl-4 d-flex flex-column gap-4">

            <!-- THÔNG TIN ĐỀ TÀI & NHÓM -->
            <div class="sv-panel mb-0">
                <div class="sv-panel-header">
                    <div class="sv-panel-title">
                        <i class="fa-solid fa-book-bookmark text-primary me-2"></i>
                        <span>Thông Tin Đề Tài &amp; Nhóm</span>
                    </div>
                    @if($deTai)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                            {{ $deTai->TrangThai ?? 'Đã duyệt' }}
                        </span>
                    @endif
                </div>

                <div class="sv-panel-body">
                    @if($deTai)
                        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold" style="font-size: 0.725rem;">
                                {{ $deTai->MaDeTai }}
                            </span>
                            @if($deTai->giangVien?->boMon)
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                    {{ $deTai->giangVien->boMon->TenBoMon }}
                                </span>
                            @endif
                        </div>

                        <h6 class="fw-bold mb-3 text-dark" style="font-size: 0.95rem; line-height: 1.45;">
                            {{ $deTai->TenDeTai }}
                        </h6>

                        <!-- GVHD -->
                        <div class="p-3 rounded-3 border bg-light mb-3">
                            <div class="text-muted text-uppercase fw-semibold mb-2" style="font-size: 0.7rem;">
                                <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Giảng viên hướng dẫn:
                            </div>
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                @if($gvhd)
                                    {{ ($gvhd->HocHam ? $gvhd->HocHam . '.' : '') . ($gvhd->HocVi ? $gvhd->HocVi . '.' : '') . ' ' . $gvhd->HoTen }}
                                @else
                                    <span class="text-muted fst-italic">Chưa phân công</span>
                                @endif
                            </div>
                            @if($gvhd && $gvhd->Email)
                                <div class="text-muted mt-1 text-truncate" style="font-size: 0.75rem;">
                                    <i class="fa-regular fa-envelope me-2"></i>{{ $gvhd->Email }}
                                </div>
                            @endif
                        </div>

                        <!-- THÀNH VIÊN NHÓM XẾP DỌC XUỐNG ĐỀU (3 THÀNH VIÊN) -->
                        <div class="mb-3">
                            <div class="text-muted text-uppercase fw-semibold mb-2" style="font-size: 0.7rem;">
                                <i class="fa-solid fa-user-group text-primary me-2"></i>Thành viên nhóm ({{ $thanhViens->count() }}/3):
                            </div>
                            <div class="d-flex flex-column gap-2">
                                @if($truongNhom)
                                    <div class="p-2 rounded-2 border bg-primary-subtle bg-opacity-25 border-primary d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="fw-bold text-dark small">👑 {{ $truongNhom->HoTen }}</span>
                                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ $truongNhom->MaSV ?? '' }}</div>
                                        </div>
                                        <span class="badge bg-warning text-dark border rounded-pill px-2 py-1" style="font-size: 10px;">Trưởng nhóm</span>
                                    </div>
                                @endif
                                @foreach($thanhViens as $tv)
                                    @if(!$truongNhom || $tv->MaSV !== $truongNhom->MaSV)
                                        <div class="p-2 rounded-2 border bg-light d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="fw-bold text-dark small">{{ $tv->sinhVien->HoTen ?? $tv->MaSV }}</span>
                                                <div class="text-muted font-monospace" style="font-size: 11px;">{{ $tv->MaSV }}</div>
                                            </div>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 10px;">Thành viên</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- MÔ TẢ ĐỀ TÀI (Lọc bớt các mô tả rườm rà, giữ dạng hộp cuộn gọn) -->
                        @if($deTai->MoTa)
                            <div class="mb-3">
                                <div class="text-muted text-uppercase fw-semibold mb-2" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-align-left text-primary me-2"></i>Tóm tắt mô tả đề tài:
                                </div>
                                <div class="topic-desc-content sv-scroll p-3 rounded-3 border bg-light">
                                    {!! \App\Services\HtmlSanitizerService::cleanDescription($deTai->MoTa) !!}
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <a href="{{ route('sinhvien.dangky.index') }}" class="btn btn-outline-primary btn-sm rounded-pill w-50 justify-content-center d-flex align-items-center py-2" style="font-size: 0.775rem;">
                                <i class="fa-solid fa-file-lines me-2"></i>Chi tiết đề tài
                            </a>
                            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-50 justify-content-center d-flex align-items-center py-2" style="font-size: 0.775rem;">
                                <i class="fa-solid fa-users me-2"></i>Chi tiết nhóm
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; font-size: 1.25rem; color: #64748b;">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <h6 class="fw-bold mb-2" style="color: var(--huit-navy); font-size: 0.9rem;">Chưa có đề tài khóa luận</h6>
                            <p class="text-muted small mb-3">
                                Bạn chưa đăng ký hoặc chưa được duyệt đề tài trong học kỳ. Hãy xem danh mục đề tài mở và nộp phiếu đăng ký.
                            </p>
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('sinhvien.dangky.index') }}" class="btn btn-primary btn-sm rounded-pill py-2 fw-semibold d-inline-flex align-items-center" style="background-color: var(--huit-blue); border-color: var(--huit-blue); font-size: 0.8rem;">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i>Xem Danh Mục Đề Tài
                                </a>
                                <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill py-2 d-inline-flex align-items-center" style="font-size: 0.8rem;">
                                    <i class="fa-solid fa-users me-2"></i>Quản Lý Nhóm Khóa Luận
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- THÔNG BÁO MỚI NHẤT -->
            <div class="sv-panel mb-0">
                <div class="sv-panel-header">
                    <div class="sv-panel-title">
                        <i class="fa-solid fa-bullhorn text-primary me-2"></i>
                        <span>Thông Báo Mới Nhất</span>
                    </div>
                    <a href="{{ route('sinhvien.thongbao.index') }}" class="text-decoration-none fw-semibold" style="font-size: 0.775rem; color: var(--huit-blue);">
                        Xem tất cả &rarr;
                    </a>
                </div>

                <div class="sv-panel-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($latestAnnouncements as $tb)
                            <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao) }}" class="list-group-item list-group-item-action p-3 d-flex align-items-start gap-3 border-0 border-bottom">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" 
                                     style="width: 34px; height: 34px; background-color: {{ !$tb->DaDoc ? '#fee2e2' : '#f1f5f9' }}; color: {{ !$tb->DaDoc ? '#ef4444' : '#64748b' }}; font-size: 0.85rem;">
                                    <i class="fa-solid {{ $tb->icon }}"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                                        <span class="badge bg-light text-secondary border rounded-pill py-1 px-2" style="font-size: 0.675rem;">
                                            {{ $tb->Loai }}
                                        </span>
                                        @if(!$tb->DaDoc)
                                            <span class="badge bg-danger rounded-pill py-1 px-2" style="font-size: 0.65rem;">Mới</span>
                                        @endif
                                    </div>
                                    <div class="fw-semibold text-dark text-truncate" style="font-size: 0.825rem;" title="{{ $tb->TieuDe }}">
                                        {{ $tb->TieuDe }}
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.725rem;">
                                        <i class="fa-regular fa-calendar me-2"></i>{{ \Carbon\Carbon::parse($tb->created_at)->format('d/m/Y') }}
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted" style="font-size: 0.8rem;">
                                <i class="fa-regular fa-bell-slash fs-5 d-block mb-1 text-secondary me-2"></i>
                                Không có thông báo mới nào.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- HOẠT ĐỘNG GẦN ĐÂY -->
            <div class="sv-panel mb-0">
                <div class="sv-panel-header">
                    <div class="sv-panel-title">
                        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                        <span>Hoạt Động Gần Đây</span>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill py-1 px-3" style="font-size: 0.7rem;">
                        Nhật ký
                    </span>
                </div>

                <div class="sv-panel-body">
                    @if($recentActivities->isNotEmpty())
                        <div class="activity-timeline">
                            @foreach($recentActivities as $act)
                                <div class="activity-node">
                                    <div class="activity-dot">
                                        <i class="fa-solid fa-circle" style="font-size: 6px; color: var(--huit-blue);"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                            <strong class="text-dark" style="font-size: 0.825rem;">{{ $act['title'] }}</strong>
                                            <span class="text-muted" style="font-size: 0.7rem;">
                                                {{ \Carbon\Carbon::parse($act['time'])->format('d/m H:i') }}
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.775rem; line-height: 1.45;">
                                            {{ $act['description'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted" style="font-size: 0.8rem;">
                            <i class="fa-regular fa-calendar-xmark fs-5 d-block mb-1 text-secondary me-2"></i>
                            Chưa ghi nhận hoạt động nào gần đây.
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection