@extends('layouts.admin')

@section('page_title', 'Theo Dõi Tiến Độ Khóa Luận Tốt Nghiệp')

@push('styles')
<style>
    :root {
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
        --huit-bg-light: #f8fafc;
        --border-color: #e2e8f0;
    }

    body {
        background-color: var(--huit-bg-light);
        color: #1e293b;
    }

    /* Card Layout chung */
    .card-theodoi {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
    }

    /* KPI Cards */
    .stat-kpi-card {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.25rem 1.35rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .stat-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }
    .stat-kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }
    .stat-kpi-card.kpi-total::before { background-color: var(--huit-primary); }
    .stat-kpi-card.kpi-active::before { background-color: #10b981; }
    .stat-kpi-card.kpi-pending::before { background-color: #f59e0b; }
    .stat-kpi-card.kpi-overdue::before { background-color: #ef4444; }
    .stat-kpi-card.kpi-completed::before { background-color: #0284c7; }

    /* Custom Modern Tabs */
    .nav-tabs-theodoi {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.75rem;
    }
    .nav-tabs-theodoi .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 0.85rem 1.25rem;
        font-size: 0.95rem;
        border-radius: 0.5rem 0.5rem 0 0;
        background: transparent;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .nav-tabs-theodoi .nav-link:hover {
        color: var(--huit-primary);
        background: rgba(0, 48, 90, 0.03);
    }
    .nav-tabs-theodoi .nav-link.active {
        color: var(--huit-primary);
        border-bottom-color: var(--huit-accent);
        background: #ffffff;
        font-weight: 700;
        box-shadow: 0 -2px 6px rgba(0,0,0,0.02);
    }

    /* 6 Stage Cards - Thiết kế Nổi Bật */
    .stage-card {
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        background: #ffffff;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.35rem;
    }
    .stage-card:hover {
        border-color: #0284c7;
        box-shadow: 0 10px 25px rgba(2, 132, 199, 0.1);
        transform: translateY(-4px);
    }
    .stage-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    /* Hộp làm nổi bật mô tả */
    .stage-description-box {
        background-color: #f8fafc;
        border-left: 3px solid var(--huit-accent);
        border-radius: 0 0.5rem 0.5rem 0;
        padding: 0.65rem 0.85rem;
        font-size: 0.83rem;
        color: #475569;
        line-height: 1.5;
        min-height: 52px;
        display: flex;
        align-items: center;
    }

    /* Table styles */
    .table-theodoi th {
        font-weight: 600;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.85rem 1rem;
        white-space: nowrap;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-theodoi td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(2, 132, 199, 0.02);
    }

    /* Pulse Dot */
    .pulse-dot-sm {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseSm 2s infinite;
    }
    @keyframes pulseSm {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- ── 1. TIÊU ĐỀ & KẾ HOẠCH HIỆN TẠI ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-chart-line text-primary me-2"></i>
                Theo Dõi Tiến Độ Khóa Luận Tốt Nghiệp
            </h4>
            <p class="text-muted small mb-0">
                Quản lý và theo dõi sát sao tình hình thực hiện các giai đoạn nghiệp vụ của sinh viên.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="card-theodoi px-3 py-2 bg-white d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-bold" style="font-size: 12px;">
                    <i class="fa-solid fa-calendar-check me-2"></i>{{ $activePlan->TenKeHoach ?? 'Kế hoạch Khóa luận HK1 2026-2027' }}
                </span>
                <span class="border-start ps-2 d-none d-sm-inline-flex align-items-center gap-2 small text-muted">
                    <span class="pulse-dot-sm"></span>
                    <span class="fw-semibold text-dark">{{ $currentPhase->TenMoc ?? 'Đang triển khai' }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- ── 2. KHU VỰC BỘ LỌC ĐA NĂNG & TÌM KIẾM ── --}}
    <div class="card-theodoi p-3 p-md-4 mb-4">
        <form method="GET" action="{{ route('admin.theodoi.index') }}" id="filterForm">
            <input type="hidden" name="tab" id="activeTabInput" value="{{ request('tab', 'tong-quan') }}">
            @if(request('stage'))
                <input type="hidden" name="stage" value="{{ request('stage') }}">
            @endif
            @if(request('status_code'))
                <input type="hidden" name="status_code" value="{{ request('status_code') }}">
            @endif

            <div class="row g-3 align-items-center">
                {{-- Ô tìm kiếm từ khóa --}}
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none" 
                               placeholder="Tìm đề tài, nhóm, MSSV..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Lọc Học kỳ --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <select name="MaHocKy" class="form-select form-select-sm shadow-none" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc Khoa --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <select name="MaKhoa" class="form-select form-select-sm shadow-none" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Tất cả khoa --</option>
                        @foreach($khoas as $k)
                            <option value="{{ $k->MaKhoa }}" {{ request('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                                {{ $k->TenKhoa }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc Bộ môn --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <select name="MaBoMon" class="form-select form-select-sm shadow-none" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Tất cả bộ môn --</option>
                        @foreach($boMons as $bm)
                            <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                {{ $bm->TenBoMon }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc GVHD --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <select name="MaGV" class="form-select form-select-sm shadow-none" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Tất cả GVHD --</option>
                        @foreach($giangViens as $gv)
                            <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                {{ $gv->HoTen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nút bấm Lọc & Xóa lọc --}}
                <div class="col-lg-1 col-md-12 col-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold w-100 shadow-sm d-flex align-items-center justify-content-center" title="Áp dụng bộ lọc">
                        <i class="fa-solid fa-filter me-2"></i>Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'MaHocKy', 'MaKhoa', 'MaBoMon', 'MaGV', 'stage', 'status_code']))
                        <a href="{{ route('admin.theodoi.index', ['tab' => request('tab', 'tong-quan')]) }}" 
                           class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── 3. HỆ THỐNG TAB ── --}}
    <ul class="nav nav-tabs nav-tabs-theodoi mb-4" id="theodoiTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'tong-quan') === 'tong-quan' ? 'active' : '' }}" 
                    id="tab-tongquan-btn" data-bs-toggle="tab" data-bs-target="#tab-tong-quan" type="button" role="tab"
                    onclick="switchTab('tong-quan')">
                <i class="fa-solid fa-chart-pie me-2"></i>
                <span>1. Tổng Quan</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'danh-sach' ? 'active' : '' }}" 
                    id="tab-danhsach-btn" data-bs-toggle="tab" data-bs-target="#tab-danh-sach" type="button" role="tab"
                    onclick="switchTab('danh-sach')">
                <i class="fa-solid fa-list-check me-2"></i>
                <span>2. Danh Sách Theo Dõi</span>
                <span class="badge bg-primary text-white rounded-pill px-2 py-1 ms-1" style="font-size: 11px;">
                    {{ $filteredNhoms->count() }}
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'nhac-nho' ? 'active' : '' }}" 
                    id="tab-nhacnho-btn" data-bs-toggle="tab" data-bs-target="#tab-nhac-nho" type="button" role="tab"
                    onclick="switchTab('nhac-nho')">
                <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                <span>3. Cần Theo Dõi &amp; Nhắc Nhở</span>
                @if($warningNhoms->count() > 0)
                    <span class="badge bg-danger text-white rounded-pill px-2 py-1 ms-1" style="font-size: 11px;">
                        {{ $warningNhoms->count() }}
                    </span>
                @endif
            </button>
        </li>
    </ul>

    {{-- ── 4. NỘI DUNG CÁC TAB ── --}}
    <div class="tab-content" id="theodoiTabsContent">

        {{-- TAB 1: TỔNG QUAN --}}
        <div class="tab-pane fade {{ request('tab', 'tong-quan') === 'tong-quan' ? 'show active' : '' }}" 
             id="tab-tong-quan" role="tabpanel">
            
            {{-- 5 Thẻ KPI --}}
            <div class="row g-3 mb-4">
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="stat-kpi-card kpi-total h-100">
                        <span class="text-muted small fw-semibold d-block mb-1">Tổng Số Đề Tài</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] }}</h3>
                            <i class="fa-solid fa-layer-group text-primary fs-3 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="stat-kpi-card kpi-active h-100">
                        <span class="text-muted small fw-semibold d-block mb-1">Đang Thực Hiện</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold text-success mb-0">{{ $stats['dang_thuc_hien'] }}</h3>
                            <i class="fa-solid fa-circle-play text-success fs-3 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="stat-kpi-card kpi-pending h-100">
                        <span class="text-muted small fw-semibold d-block mb-1">Chờ Xử Lý</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold text-warning mb-0">{{ $stats['cho_xu_ly'] }}</h3>
                            <i class="fa-solid fa-hourglass-half text-warning fs-3 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="stat-kpi-card kpi-overdue h-100">
                        <span class="text-muted small fw-semibold d-block mb-1">Quá Hạn</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold text-danger mb-0">{{ $stats['qua_han'] }}</h3>
                            <i class="fa-solid fa-circle-exclamation text-danger fs-3 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="stat-kpi-card kpi-completed h-100">
                        <span class="text-muted small fw-semibold d-block mb-1">Đã Hoàn Thành</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold text-info mb-0">{{ $stats['hoan_thanh'] }}</h3>
                            <i class="fa-solid fa-circle-check text-info fs-3 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TIẾN TRÌNH 6 GIAI ĐOẠN - CÓ MÔ TẢ NỔI BẬT --}}
            <div class="card-theodoi p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 fs-6">
                            <i class="fa-solid fa-diagram-project text-primary me-2"></i>
                            Tiến Trình 6 Giai Đoạn Nghiệp Vụ
                        </h6>
                        <span class="text-muted small">Chi tiết nội dung nhiệm vụ và tỷ lệ tiến độ toàn Khoa</span>
                    </div>
                    <span class="badge bg-light text-secondary border font-monospace px-3 py-2">
                        6 Giai đoạn quy chuẩn
                    </span>
                </div>

                <div class="row g-3">
                    @foreach($stageDefinitions as $stNum => $stMeta)
                        @php
                            $stData = $stageStats[$stNum] ?? [];
                            $totalInStage = $stData['total_in_stage'] ?? 0;
                            $rate = $stData['completion_rate'] ?? 0;
                        @endphp
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="stage-card">
                                <div>
                                    {{-- Header Giai đoạn --}}
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="stage-icon-circle" style="background-color: {{ $stMeta['color'] }}15; color: {{ $stMeta['color'] }};">
                                                <i class="fa-solid {{ $stMeta['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <span class="badge rounded-pill fw-bold px-3 py-1" style="background-color: {{ $stMeta['color'] }}20; color: {{ $stMeta['color'] }}; font-size: 11px;">
                                                    Giai đoạn {{ $stNum }}
                                                </span>
                                                <h6 class="fw-bold text-dark mb-0 mt-1" style="font-size: 0.95rem;">
                                                    {{ $stMeta['ten'] }}
                                                </h6>
                                            </div>
                                        </div>

                                        @if(($stData['qua_han'] ?? 0) > 0)
                                            <span class="badge bg-danger text-white rounded-pill px-3 py-1 small shadow-sm" title="Có nhóm trễ hạn">
                                                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $stData['qua_han'] }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Khung làm nổi bật mô tả nhiệm vụ --}}
                                    <div class="stage-description-box mb-3" style="border-left-color: {{ $stMeta['color'] }};">
                                        <span><i class="fa-solid fa-circle-info me-2 opacity-50" style="color: {{ $stMeta['color'] }};"></i>{{ $stMeta['mo_ta'] ?? 'Triển khai công việc theo mốc thời gian quy định của khoa.' }}</span>
                                    </div>
                                </div>

                                <div>
                                    {{-- Con số nhóm --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 text-muted small">
                                        <span>Số nhóm đang ở GĐ này:</span>
                                        <strong class="text-dark font-monospace fs-6">{{ $totalInStage }}</strong>
                                    </div>

                                    {{-- Thanh Tiến độ --}}
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center text-muted small mb-1">
                                            <span>Hoàn thành điều kiện:</span>
                                            <span class="fw-bold text-dark font-monospace">{{ $rate }}%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                            <div class="progress-bar rounded-pill" role="progressbar" 
                                                 style="width: {{ $rate }}%; background-color: {{ $stMeta['color'] }};"></div>
                                        </div>
                                    </div>

                                    {{-- Action Link --}}
                                    <a href="{{ route('admin.theodoi.index', ['tab' => 'danh-sach', 'stage' => $stNum]) }}" 
                                       class="btn btn-sm btn-light border w-100 rounded-pill fw-semibold text-primary shadow-none py-2" style="font-size: 12px;">
                                        <span>Xem danh sách GĐ {{ $stNum }}</span>
                                        <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- TAB 2: DANH SÁCH THEO DÕI --}}
        <div class="tab-pane fade {{ request('tab') === 'danh-sach' ? 'show active' : '' }}" 
             id="tab-danh-sach" role="tabpanel">

            <div class="card-theodoi mb-4">
                <div class="p-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-table-list text-primary me-2"></i>
                            Danh Sách Nhóm Khóa Luận
                        </h6>
                        <span class="text-muted small">
                            Hiển thị <strong>{{ $paginatedNhoms->firstItem() ?? 0 }}</strong> - <strong>{{ $paginatedNhoms->lastItem() ?? 0 }}</strong> 
                            trên tổng số <strong>{{ $paginatedNhoms->total() }}</strong> nhóm
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.theodoi.index', array_merge(request()->except(['page', 'stage']), ['tab' => 'danh-sach'])) }}" 
                           class="badge text-decoration-none {{ !request('stage') ? 'bg-primary text-white' : 'bg-light text-secondary border' }} rounded-pill px-3 py-2">
                            Tất cả GĐ
                        </a>
                        @foreach($stageDefinitions as $sNum => $sInfo)
                            <a href="{{ route('admin.theodoi.index', array_merge(request()->except(['page']), ['tab' => 'danh-sach', 'stage' => $sNum])) }}" 
                               class="badge text-decoration-none {{ request('stage') == $sNum ? 'text-white' : 'bg-light text-secondary border' }} rounded-pill px-3 py-2"
                               style="{{ request('stage') == $sNum ? 'background-color: ' . $sInfo['color'] . ';' : '' }}">
                                GĐ {{ $sNum }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-theodoi mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">STT</th>
                                <th style="width: 100px;">Mã Nhóm</th>
                                <th style="min-width: 220px;">Tên Đề Tài</th>
                                <th style="min-width: 170px;">Nhóm Sinh Viên</th>
                                <th style="width: 130px;">Khoa / Bộ Môn</th>
                                <th style="width: 150px;">GV Hướng Dẫn</th>
                                <th class="text-center" style="width: 130px;">Giai Đoạn</th>
                                <th class="text-center" style="width: 110px;">Trạng Thái</th>
                                <th style="width: 110px;">Hạn Xử Lý</th>
                                <th class="text-center" style="width: 120px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paginatedNhoms as $index => $nhom)
                                @php
                                    $tienDo = $nhom->tien_do_nghiep_vu;
                                    $stNum = $tienDo->GiaiDoanHienTai;
                                    $stMeta = $stageDefinitions[$stNum] ?? [];
                                    $deTai = $nhom->deTai;
                                    $stt = ($paginatedNhoms->currentPage() - 1) * $paginatedNhoms->perPage() + $index + 1;
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace text-muted">{{ $stt }}</td>
                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace fw-bold px-3 py-1 d-block text-center">
                                            {{ $nhom->MaNhom }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 250px;" title="{{ $deTai->TenDeTai ?? 'Chưa đăng ký đề tài' }}">
                                            {{ $deTai->TenDeTai ?? 'Chưa đăng ký đề tài' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $nhom->TenNhom ?: $nhom->MaNhom }}</div>
                                        <div class="text-muted small" style="font-size: 11.5px;">
                                            <i class="fa-solid fa-crown text-warning me-2"></i>{{ $nhom->truongNhom?->HoTen ?? 'Chưa có TN' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">
                                            {{ $deTai?->giangVien?->boMon?->TenBoMon ?? '---' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($deTai && $deTai->giangVien)
                                            <div class="fw-semibold text-dark text-truncate" style="max-width: 140px;" title="{{ $deTai->giangVien->HoTen }}">
                                                {{ $deTai->giangVien->HoTen }}
                                            </div>
                                        @else
                                            <span class="badge bg-light text-secondary border">Chưa phân công</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill text-white fw-bold px-3 py-1 d-inline-block"
                                              style="background-color: {{ $stMeta['color'] ?? '#0284c7' }}; font-size: 11px;">
                                            GĐ {{ $stNum }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($tienDo->TrangThaiGiaiDoan === 'HOAN_THANH')
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1">Hoàn thành</span>
                                        @elseif($tienDo->TrangThaiGiaiDoan === 'CHO_XU_LY')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Chờ xử lý</span>
                                        @elseif($tienDo->TrangThaiGiaiDoan === 'QUA_HAN')
                                            <span class="badge bg-danger text-white rounded-pill px-3 py-1">Quá hạn</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                Đang làm
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small font-monospace">
                                            {{ $tienDo->NgayHoanThanh ? \Carbon\Carbon::parse($tienDo->NgayHoanThanh)->format('d/m/Y') : 'Theo kế hoạch' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <a href="{{ route('admin.theodoi.show', $nhom->MaNhom) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold shadow-none" title="Chi tiết">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}" class="d-inline"
                                                  onsubmit="return confirm('Gửi nhắc nhở tiến độ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-circle p-1 shadow-none" 
                                                        style="width: 28px; height: 28px;" title="Gửi nhắc nhở">
                                                    <i class="fa-solid fa-bell"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fa-2x mb-2 opacity-50 d-block me-2"></i>
                                        Không tìm thấy nhóm khóa luận phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($paginatedNhoms->hasPages())
                    <div class="p-3 px-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted small">
                            Trang <strong>{{ $paginatedNhoms->currentPage() }}</strong> / <strong>{{ $paginatedNhoms->lastPage() }}</strong>
                        </span>
                        <div>
                            {{ $paginatedNhoms->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 3: CẦN THEO DÕI VÀ NHẮC NHỞ --}}
        <div class="tab-pane fade {{ request('tab') === 'nhac-nho' ? 'show active' : '' }}" 
             id="tab-nhac-nho" role="tabpanel">

            <div class="card-theodoi p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                            Các Trường Hợp Cần Nhắc Nhở Tiến Độ
                        </h6>
                        <span class="text-muted small">Tập trung các nhóm quá hạn hoặc chậm trễ hồ sơ</span>
                    </div>
                    <span class="badge bg-danger text-white rounded-pill px-3 py-2 fw-bold">
                        {{ $warningNhoms->count() }} trường hợp
                    </span>
                </div>

                @if($warningNhoms->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-shield-heart fa-3x text-success mb-3 opacity-75"></i>
                        <h6 class="fw-bold text-dark">Tuyệt vời! Không có trường hợp nào cần nhắc nhở</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-theodoi mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">STT</th>
                                    <th style="width: 100px;">Mã Nhóm</th>
                                    <th>Tên Đề Tài &amp; GVHD</th>
                                    <th style="width: 140px;">Giai Đoạn</th>
                                    <th style="min-width: 220px;">Lý Do Cảnh Báo</th>
                                    <th style="width: 110px;" class="text-center">Tình Trạng</th>
                                    <th style="width: 130px;" class="text-center">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warningNhoms as $wIdx => $wn)
                                    <tr>
                                        <td class="text-center font-monospace text-muted">{{ $wIdx + 1 }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace px-3 py-1">
                                                {{ $wn->MaNhom }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 260px;" title="{{ $wn->deTai->TenDeTai ?? $wn->TenNhom }}">
                                                {{ $wn->deTai->TenDeTai ?? $wn->TenNhom }}
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                                                GVHD: <strong>{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-primary border rounded-pill px-3 py-1 fw-bold">
                                                {{ $wn->current_stage_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <ul class="mb-0 ps-3 text-danger small" style="line-height: 1.4;">
                                                @foreach($wn->warning_reasons as $r)
                                                    <li>{{ $r }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger text-white rounded-pill px-3 py-1">Quá hạn</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-2 align-items-center">
                                                <a href="{{ route('admin.theodoi.show', $wn->MaNhom) }}" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-none" style="font-size: 11.5px;">
                                                    Chi tiết
                                                </a>
                                                <form method="POST" action="{{ route('admin.theodoi.remind', $wn->MaNhom) }}" class="d-inline"
                                                      onsubmit="return confirm('Xác nhận gửi thông báo nhắc tiến độ?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1 fw-bold shadow-none d-inline-flex align-items-center" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-bell me-2"></i>Nhắc
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabName) {
        document.getElementById('activeTabInput').value = tabName;
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const currentTab = urlParams.get('tab') || 'tong-quan';

        if (currentTab === 'danh-sach') {
            const btn = document.getElementById('tab-danhsach-btn');
            if (btn) new bootstrap.Tab(btn).show();
        } else if (currentTab === 'nhac-nho') {
            const btn = document.getElementById('tab-nhacnho-btn');
            if (btn) new bootstrap.Tab(btn).show();
        } else {
            const btn = document.getElementById('tab-tongquan-btn');
            if (btn) new bootstrap.Tab(btn).show();
        }
    });
</script>
@endpush