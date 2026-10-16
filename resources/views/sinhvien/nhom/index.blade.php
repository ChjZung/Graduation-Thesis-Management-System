@extends('layouts.sinhvien')

@section('page_title', 'Quản Trị Nhóm Khóa Luận')

@section('content')
<style>
    /* Bảng màu chuẩn: Midnight Navy (var(--admin-primary-dark)), Ocean Cyan (var(--admin-primary)), Persian Green (var(--admin-success)), Mint (var(--admin-info-soft)) */
    .nhom-page-root {
        font-size: 0.875rem;
        color: var(--admin-body-text);
        padding: 8px 4px 24px 4px; /* Bổ sung padding tổng thể cho toàn trang */
    }
    .nhom-page-root .small, 
    .nhom-page-root small, 
    .nhom-page-root .form-text,
    .nhom-page-root code {
        font-size: 0.8rem !important;
    }

    /* Thẻ bao quát và hiệu ứng bóng đổ tự nhiên */
    .nhom-panel-card {
        border-radius: 14px;
        background-color: var(--admin-surface);
        border: 1px solid var(--admin-border);
        box-shadow: 0 4px 12px var(--shadow-sm);
    }
    .group-item-card {
        background-color: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        box-shadow: 0 2px 6px var(--shadow-sm);
    }
    .group-item-card:hover {
        transform: translateY(-2px);
        border-color: var(--admin-primary);
        box-shadow: 0 8px 20px -4px var(--shadow-md);
    }

    /* Bảng dữ liệu chuẩn chỉnh với padding thoáng */
    .table-custom th {
        background-color: var(--admin-table-header) !important;
        font-weight: 700;
        font-size: 0.775rem !important;
        padding: 14px 16px !important;
        color: var(--admin-surface) !important;
        border-bottom: 1px solid var(--admin-border) !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .table-custom td {
        font-size: 0.85rem !important;
        padding: 14px 16px !important;
        vertical-align: middle;
        line-height: 1.6;
        color: var(--admin-body-text);
        border-bottom: 1px solid var(--admin-border-soft);
    }

    /* Bộ lọc toàn cục có padding cân đối */
    .filter-item-box {
        display: flex;
        align-items: center;
        background-color: var(--admin-secondary-button-bg);
        border: 1px solid var(--admin-border);
        border-radius: 10px;
        height: 44px;
        padding: 0 14px;
        transition: all 0.15s ease;
    }
    .filter-item-box:hover, .filter-item-box:focus-within {
        border-color: var(--admin-primary);
        background-color: var(--admin-surface);
        box-shadow: 0 2px 6px var(--shadow-sm);
    }
    .filter-item-label {
        font-size: 0.825rem;
        font-weight: 600;
        color: var(--admin-text-secondary);
        white-space: nowrap;
        margin-right: 10px;
        display: flex;
        align-items: center;
    }
    .filter-item-select {
        border: none !important;
        background-color: transparent !important;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--admin-primary-dark);
        padding: 0 4px;
        box-shadow: none !important;
        cursor: pointer;
        width: 100%;
    }
    .filter-item-select:focus {
        outline: none;
    }
    .filter-btn-invite {
        height: 44px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Thanh điều hướng Tab có padding rộng rãi */
    .workspace-nav-pills {
        background-color: var(--admin-border-soft);
        border: 1px solid var(--admin-border);
        gap: 8px;
        padding: 8px;
        border-radius: 50rem !important;
    }
    .workspace-nav-pills .nav-link {
        color: var(--admin-text-secondary);
        background-color: transparent;
        border: 1px solid transparent;
        transition: all 0.18s ease;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 50rem !important;
    }
    .workspace-nav-pills .nav-link:hover {
        color: var(--admin-primary-dark);
        background-color: var(--admin-border);
    }
    .workspace-nav-pills .nav-link .nav-icon {
        color: var(--admin-primary);
    }
    .workspace-nav-pills .nav-link.active {
        color: var(--admin-surface) !important;
        background-color: var(--admin-primary) !important;
        box-shadow: 0 4px 10px var(--shadow-md);
        border-color: var(--admin-primary-dark) !important;
    }
    .workspace-nav-pills .nav-link.active .nav-icon {
        color: var(--admin-surface) !important;
    }
    .workspace-nav-pills .badge.bg-secondary {
        background-color: var(--admin-neutral-soft) !important;
        color: var(--admin-neutral-text) !important;
        border: 1px solid var(--admin-neutral-border) !important;
    }

    /* Tiến độ Timeline / Milestones tăng padding */
    .milestone-step-card {
        background-color: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        padding: 16px 12px;
        position: relative;
        transition: all 0.16s ease;
    }
    .milestone-step-card:hover {
        border-color: var(--admin-primary);
        background-color: var(--admin-secondary-button-bg);
    }
    .milestone-icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: 10px;
    }

    /* Khung cuộn lời mời & danh sách nhóm */
    .loi-moi-scroll-container, .nhom-open-scroll-container {
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 8px;
        scrollbar-width: thin;
        scrollbar-color: var(--admin-primary) var(--admin-border-soft);
    }
    .loi-moi-scroll-container { max-height: 240px; }
    .nhom-open-scroll-container { max-height: 450px; }

    .loi-moi-scroll-container::-webkit-scrollbar,
    .nhom-open-scroll-container::-webkit-scrollbar {
        width: 5px;
    }
    .loi-moi-scroll-container::-webkit-scrollbar-track,
    .nhom-open-scroll-container::-webkit-scrollbar-track {
        background: var(--admin-border-soft);
        border-radius: 4px;
    }
    .loi-moi-scroll-container::-webkit-scrollbar-thumb,
    .nhom-open-scroll-container::-webkit-scrollbar-thumb {
        background: var(--admin-neutral-border);
        border-radius: 4px;
    }

    .badge-neutral {
        background-color: var(--admin-border-soft);
        color: var(--admin-text-secondary);
        border: 1px solid var(--admin-border);
    }

    /* Button Custom Cyan */
    .btn-cyan {
        background-color: var(--admin-primary);
        border-color: var(--admin-primary);
        color: var(--admin-surface);
    }
    .btn-cyan:hover, .btn-cyan:focus {
        background-color: var(--admin-primary-hover);
        border-color: var(--admin-primary-hover);
        color: var(--admin-surface);
    }
    .btn-outline-cyan {
        border-color: var(--admin-primary);
        color: var(--admin-primary);
    }
    .btn-outline-cyan:hover {
        background-color: var(--admin-primary);
        color: var(--admin-surface);
    }
</style>

<div class="nhom-page-root">

    <!-- ========================================================================================== -->
    <!-- [1] BẢNG ĐIỀU HƯỚNG BỘ LỌC TẬP TRUNG (GLOBAL HEADER FILTER) -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 p-3 bg-white">
        <div class="row g-3 align-items-center">
            <!-- 1. HỌC KỲ -->
            <div class="col-12 col-md-3">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-regular fa-calendar-days text-secondary me-1"></i>Học kỳ:
                    </span>
                    <select class="form-select form-select-sm filter-item-select" id="filter_hoc_ky" onchange="onHocKyFilterChange()">
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ $maHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '•' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. BỘ MÔN CHUYÊN NGÀNH -->
            <div class="col-12 col-md-3">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-solid fa-sitemap text-secondary me-1"></i>Bộ môn:
                    </span>
                    <select class="form-select form-select-sm filter-item-select" id="filter_bo_mon" onchange="onBoMonChange()">
                        <option value="ALL" {{ ($selectedBoMon === 'ALL' || !$selectedBoMon) ? 'selected' : '' }}>-- Tất cả bộ môn --</option>
                        @foreach($boMons as $bm)
                            <option value="{{ $bm->MaBoMon }}" {{ $selectedBoMon === $bm->MaBoMon ? 'selected' : '' }}>
                                {{ $bm->TenBoMon }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. HỌC PHẦN CHUYÊN NGÀNH -->
            <div class="col-12 col-md-4">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-solid fa-graduation-cap text-secondary me-1"></i>Học phần:
                    </span>
                    <select class="form-select form-select-sm filter-item-select" id="filter_hoc_phan" onchange="onHocPhanFilterChange()">
                        <option value="ALL" {{ ($selectedHocPhan == 'ALL' || !$selectedHocPhan) ? 'selected' : '' }}>
                            -- Tất cả học phần --
                        </option>
                        @foreach($hocPhans as $hp)
                            <option value="{{ $hp->MaHocPhan }}" {{ $selectedHocPhan == $hp->MaHocPhan ? 'selected' : '' }}>
                                {{ $hp->TenHocPhan }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 4. LỜI MỜI NHÓM -->
            <div class="col-12 col-md-2">
                @php
                    $loiMoiCount = isset($loiMois) ? $loiMois->count() : 0;
                @endphp
                <button type="button" 
                        class="btn w-100 filter-btn-invite border {{ $loiMoiCount > 0 ? 'btn-warning text-dark border-warning' : 'btn-light border-slate-200 text-slate-700' }}"
                        onclick="switchWorkspaceTab('tab-loimoi')">
                    <i class="fa-regular fa-bell {{ $loiMoiCount > 0 ? 'fa-shake text-danger' : 'text-secondary' }} me-1"></i>
                    <span>Lời mời</span>
                    <span class="badge {{ $loiMoiCount > 0 ? 'bg-danger text-white' : 'badge-neutral' }} rounded-pill ms-1" style="font-size: 11px;">
                        {{ $loiMoiCount }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================================== -->
    <!-- [2] THANH TRẠNG THÁI & CHUYỂN ĐỔI NHÓM NHANH (WORKSPACE SWITCHER) -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 p-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                <span class="fw-bold text-dark d-flex align-items-center" style="color: var(--admin-primary-dark) !important;">
                    <i class="fa-solid fa-layer-group me-2" style="color: var(--admin-primary);"></i>Không Gian Nhóm:
                </span>
                
                @if(isset($sinhVienAllGroups) && $sinhVienAllGroups->count() > 0)
                    @foreach($sinhVienAllGroups as $g)
                        @if($g->nhom)
                            @php
                                $isActiveGroup = ($nhomCurrent && $nhomCurrent->MaNhom === $g->nhom->MaNhom);
                            @endphp
                            <a href="{{ route('sinhvien.nhom.index', ['nhom_id' => $g->nhom->MaNhom, 'hoc_ky' => $g->nhom->MaHocKy, 'hoc_phan' => $g->nhom->MaHocPhan]) }}"
                               class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 {{ $isActiveGroup ? 'btn-cyan shadow-xs' : 'btn-outline-secondary bg-white text-dark' }}">
                                <span class="d-inline-block rounded-circle {{ $isActiveGroup ? 'bg-white' : 'bg-secondary' }}" style="width: 8px; height: 8px;"></span>
                                <span>{{ $g->nhom->TenNhom }} ({{ $g->nhom->hocPhan->TenHocPhan ?? $g->nhom->MaHocPhan }})</span>
                                @if($isActiveGroup)
                                    <span class="badge bg-white text-dark rounded-pill px-2 py-0.5 ms-1" style="font-size: 10px;">Hiện tại</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                @else
                    <span class="badge badge-neutral rounded-pill px-3 py-2">
                        <i class="fa-solid fa-circle-info me-1 text-secondary"></i>Chưa tham gia nhóm nào
                    </span>
                @endif
            </div>

            <div>
                @if($isTaoNhomOpen ?? true)
                    <button type="button" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                        <i class="fa-solid fa-plus-circle me-1"></i>+ Tạo Nhóm Mới
                    </button>
                @else
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2 fw-bold" disabled title="Tạm thời không trong thời gian tạo nhóm">
                        <i class="fa-solid fa-lock me-1"></i>Hết Hạn Tạo Nhóm
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(!($isTaoNhomOpen ?? true))
        <div class="alert alert-warning border-warning-subtle shadow-xs rounded-4 p-4 mb-4 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning bg-opacity-25 p-3 text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-dark mb-1">Tạm thời không trong thời gian tạo nhóm</div>
                <div class="text-muted" style="font-size: 0.85rem;">
                    {{ $groupPhaseState['message'] ?? 'Cổng tạo và ghép nhóm thành viên hiện đang đóng theo Kế hoạch khóa luận.' }}
                </div>
            </div>
            <div>
                <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold">Đã Khóa</span>
            </div>
        </div>
    @endif

    <!-- ========================================================================================== -->
    <!-- [3] KHU VỰC NỘI DUNG CHÍNH (MAIN WORKSPACE - TABBED SYSTEM) -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs nhom-panel-card mb-4 overflow-hidden">
        <!-- HEADER TABS CHÍNH -->
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3.5 px-1 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: var(--admin-primary-dark);">
                        <i class="fa-solid fa-users-gear" style="color: var(--admin-primary);"></i>
                        <span>Quản Trị Nhóm Của Bạn</span>
                    </h5>
                    <div class="text-muted mt-1" style="font-size: 0.85rem;">Không gian điều phối thông tin nhóm, đề tài và thành viên khóa luận</div>
                </div>
                @if($nhomCurrent)
                    <span class="badge {{ $isNhomLocked ? 'bg-secondary' : ($nhomCurrent->thanhViens->count() === 3 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }} rounded-pill px-3.5 py-2 fw-semibold">
                        <i class="fa-solid {{ $isNhomLocked ? 'fa-lock' : 'fa-users' }} me-1"></i>
                        {{ $isNhomLocked ? 'Đã Khóa Đề Tài' : $nhomCurrent->thanhViens->count() . '/3 Thành Viên' }}
                    </span>
                @endif
            </div>

            <ul class="nav nav-pills workspace-nav-pills p-1.5" id="workspaceTabs" role="tablist">
                <!-- TAB 1: TỔNG QUAN & ĐỀ TÀI -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="tab-tongquan-tab" data-bs-toggle="pill" data-bs-target="#tab-tongquan" type="button" role="tab">
                        <i class="fa-solid fa-layer-group nav-icon me-2"></i>Tổng Quan &amp; Đề Tài
                    </button>
                </li>
                <!-- TAB 2: THÀNH VIÊN -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="tab-thanhvien-tab" data-bs-toggle="pill" data-bs-target="#tab-thanhvien" type="button" role="tab">
                        <i class="fa-solid fa-users nav-icon me-2"></i>Thành Viên 
                        <span class="badge {{ $nhomCurrent && $nhomCurrent->thanhViens->count() === 3 ? 'bg-success text-white' : 'bg-secondary text-white' }} rounded-pill ms-1.5" style="font-size: 0.725rem;">
                            {{ $nhomCurrent ? $nhomCurrent->thanhViens->count() . '/3' : '0/3' }}
                        </span>
                    </button>
                </li>
                <!-- TAB 3: LỜI MỜI & YÊU CẦU -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="tab-loimoi-tab" data-bs-toggle="pill" data-bs-target="#tab-loimoi" type="button" role="tab">
                        <i class="fa-regular fa-envelope nav-icon me-2"></i>Lời Mời &amp; Yêu Cầu
                        @php
                            $totalInvitesAndReqs = (isset($loiMois) ? $loiMois->count() : 0) + (isset($yeuCauXinVao) ? $yeuCauXinVao->count() : 0);
                        @endphp
                        @if($totalInvitesAndReqs > 0)
                            <span class="badge bg-danger text-white rounded-pill ms-1.5" style="font-size: 0.725rem;">{{ $totalInvitesAndReqs }}</span>
                        @endif
                    </button>
                </li>
                <!-- TAB 4: TÌM NHÓM MỞ -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="tab-timnhom-tab" data-bs-toggle="pill" data-bs-target="#tab-timnhom" type="button" role="tab">
                        <i class="fa-solid fa-magnifying-glass nav-icon me-2"></i>Tìm Nhóm Mở
                        <span class="badge badge-neutral rounded-pill ms-1.5" style="font-size: 0.725rem;">{{ count($nhomsOpen ?? []) }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="workspaceTabsContent">

                <!-- ################################################################## -->
                <!-- TAB 1: TỔNG QUAN & ĐỀ TÀI -->
                <!-- ################################################################## -->
                <div class="tab-pane fade show active" id="tab-tongquan" role="tabpanel">
                    @if($nhomCurrent)
                        @php
                            $currentDeTai = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
                            $memberCountCurrent = $nhomCurrent->thanhViens->count();
                            $isFullGroup = ($memberCountCurrent >= 3);
                            $nhomNganh = $nhomCurrent->truongNhom?->nganh?->TenNganh ?? $nhomCurrent->truongNhom?->lop?->nganh?->TenNganh;
                        @endphp

                        <!-- 📊 TIẾN ĐỘ & BẢNG SỰ KIỆN QUA CÁC GIAI ĐOẠN (TIMELINE / MILESTONES) -->
                        <div class="card border border-slate-200 shadow-xs rounded-4 overflow-hidden mb-4">
                            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0" style="color: var(--admin-primary-dark); font-size: 0.9rem;">
                                    <i class="fa-solid fa-chart-line me-2" style="color: var(--admin-primary);"></i>TIẾN ĐỘ &amp; CÁC GIAI ĐOẠN KHÓA LUẬN (TIMELINE)
                                </h6>
                                <span class="badge badge-neutral px-3 py-1.5" style="font-size: 0.75rem;">Quy trình chuẩn</span>
                            </div>
                            <div class="card-body p-3.5 px-4">
                                <div class="row g-3 align-items-center text-center">
                                    <!-- Giai đoạn 1: Mở nhóm -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle bg-success text-white">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 0.85rem;">1. Mở Nhóm</div>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                <i class="fa-solid fa-check me-1"></i>Hoàn thành
                                            </span>
                                            <div class="text-muted mt-1.5" style="font-size: 0.775rem;">Đã khởi tạo nhóm</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 2: Đủ 3 SV -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ $isFullGroup ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                                                <i class="fa-solid {{ $isFullGroup ? 'fa-user-check' : 'fa-user-clock' }}"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 0.85rem;">2. Đủ 3 SV</div>
                                            @if($isFullGroup)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-check me-1"></i>Đủ 3/3 SV
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-hourglass-half me-1"></i>{{ $memberCountCurrent }}/3 SV
                                                </span>
                                            @endif
                                            <div class="text-muted mt-1.5" style="font-size: 0.775rem;">{{ $isFullGroup ? 'Sẵn sàng đăng ký' : 'Cần thêm ' . (3 - $memberCountCurrent) . ' bạn' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 3: Đăng ký đề tài -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ $currentDeTai ? 'bg-success text-white' : ($isFullGroup ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border') }}">
                                                <i class="fa-solid fa-book-bookmark"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 0.85rem;">3. Đăng Ký Đề Tài</div>
                                            @if($currentDeTai)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-check me-1"></i>Đã duyệt
                                                </span>
                                            @elseif($isFullGroup)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-arrow-right me-1"></i>Đang đăng ký
                                                </span>
                                            @else
                                                <span class="badge badge-neutral rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-lock me-1"></i>Chưa mở
                                                </span>
                                            @endif
                                            <div class="text-muted mt-1.5" style="font-size: 0.775rem;">{{ $currentDeTai ? 'Đã gán đề tài' : 'Chờ đủ thành viên' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 4: Nộp đề cương / Báo cáo -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ ($currentDeTai && $currentDeTai->FileDeCuong) ? 'bg-success text-white' : ($currentDeTai ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-light text-muted border') }}">
                                                <i class="fa-solid fa-file-signature"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 0.85rem;">4. Nộp Báo Cáo</div>
                                            @if($currentDeTai && $currentDeTai->FileDeCuong)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-check me-1"></i>Có đề cương
                                                </span>
                                            @elseif($currentDeTai)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-clock me-1"></i>Chờ nộp
                                                </span>
                                            @else
                                                <span class="badge badge-neutral rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-lock me-1"></i>Chưa mở
                                                </span>
                                            @endif
                                            <div class="text-muted mt-1.5" style="font-size: 0.775rem;">Đề cương &amp; Báo cáo</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 5: Bảo vệ khóa luận -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle bg-light text-secondary border">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 0.85rem;">5. Bảo Vệ</div>
                                            <span class="badge badge-neutral rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">
                                                <i class="fa-solid fa-lock me-1"></i>Giai đoạn cuối
                                            </span>
                                            <div class="text-muted mt-1.5" style="font-size: 0.775rem;">Hội đồng bảo vệ</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BỐ CỤC 2 HỘP: THÔNG TIN NHÓM & THÔNG TIN ĐỀ TÀI -->
                        <div class="row g-4">
                            <!-- 🏢 THÔNG TIN NHÓM & GVHD -->
                            <div class="col-12 col-lg-6">
                                <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0" style="color: var(--admin-primary-dark); font-size: 0.9rem;">
                                            <i class="fa-solid fa-id-card me-2" style="color: var(--admin-primary);"></i>THÔNG TIN NHÓM &amp; GVHD
                                        </h6>
                                        <span class="badge {{ $isNhomLocked ? 'bg-secondary' : ($isFullGroup ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }} rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                            <i class="fa-solid {{ $isNhomLocked ? 'fa-lock' : 'fa-users' }} me-1"></i>
                                            {{ $isNhomLocked ? 'Đã Khóa Đề Tài' : $memberCountCurrent . '/3 Thành Viên' }}
                                        </span>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                            <div>
                                                <h5 class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 1rem;">
                                                    <i class="fa-solid fa-users me-1.5" style="color: var(--admin-primary);"></i>{{ $nhomCurrent->TenNhom }}
                                                </h5>
                                                <div class="text-muted" style="font-size: 0.8rem;">Mã nhóm: <code>{{ $nhomCurrent->MaNhom }}</code></div>
                                            </div>
                                            <div class="d-flex gap-1.5 flex-wrap">
                                                @if($nhomCurrent->hocKy)
                                                    <span class="badge badge-neutral px-2.5 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-calendar me-1" style="color: var(--admin-primary);"></i>{{ $nhomCurrent->hocKy->TenHocKy }}
                                                    </span>
                                                @endif
                                                @if($nhomCurrent->hocPhan)
                                                    <span class="badge badge-neutral px-2.5 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-book me-1" style="color: var(--admin-primary);"></i>{{ $nhomCurrent->hocPhan->TenHocPhan }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="p-3 bg-light rounded-3 border border-slate-200 mb-3">
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <span class="text-muted d-block mb-1" style="font-size: 0.775rem;"><i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm:</span>
                                                    <strong style="color: var(--admin-primary-dark); font-size: 0.875rem;">{{ $nhomCurrent->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                                    <div class="text-muted mt-1" style="font-size: 0.775rem;">MSSV: <code>{{ $nhomCurrent->truongNhom->taiKhoan->TenDangNhap ?? $nhomCurrent->MaTruongNhom }}</code></div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="text-muted d-block mb-1" style="font-size: 0.775rem;"><i class="fa-solid fa-chalkboard me-1" style="color: var(--admin-primary);"></i>Lớp &amp; Ngành:</span>
                                                    <div class="fw-semibold text-dark" style="font-size: 0.875rem;">{{ $nhomCurrent->truongNhom->lop->TenLop ?? 'Chưa rõ' }}</div>
                                                    <div class="text-muted mt-1" style="font-size: 0.775rem;">{{ $nhomNganh ?? 'Công nghệ thông tin' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-3 bg-white rounded-3 border border-slate-200">
                                            <span class="text-muted d-block mb-1" style="font-size: 0.775rem;"><i class="fa-solid fa-chalkboard-user me-1" style="color: var(--admin-primary);"></i>Giảng viên hướng dẫn (GVHD):</span>
                                            @if($currentDeTai && $currentDeTai->giangVien)
                                                <div class="fw-bold" style="color: var(--admin-primary-dark); font-size: 0.925rem;">{{ $currentDeTai->giangVien->HoTen }}</div>
                                                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                                    <span>{{ $currentDeTai->giangVien->HocVi ?? 'Giảng viên' }}</span>
                                                    <span class="mx-1">&bull;</span>
                                                    <span>{{ $currentDeTai->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                                                </div>
                                                @if($currentDeTai->giangVien->Email)
                                                    <div class="text-muted mt-1" style="font-size: 0.775rem;"><i class="fa-regular fa-envelope me-1"></i><code>{{ $currentDeTai->giangVien->Email }}</code></div>
                                                @endif
                                            @else
                                                <div class="text-muted fst-italic" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-circle-info me-1" style="color: var(--admin-primary);"></i>Chưa gán GVHD. Giảng viên sẽ được phân công sau khi hoàn tất đăng ký đề tài.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light px-4 py-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="text-muted" style="font-size: 0.8rem;"><i class="fa-solid fa-user-group me-1"></i>Quy mô: <strong>{{ $memberCountCurrent }}/3 SV</strong></span>
                                        <button type="button" class="btn btn-cyan btn-sm rounded-pill px-3.5 py-1.5 shadow-xs" onclick="switchWorkspaceTab('tab-thanhvien')">
                                            <i class="fa-solid fa-users me-1"></i>Xem Thành Viên
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 📄 THÔNG TIN ĐỀ TÀI KHÓA LUẬN -->
                            <div class="col-12 col-lg-6">
                                <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0" style="color: var(--admin-primary-dark); font-size: 0.9rem;">
                                            <i class="fa-solid fa-book-bookmark me-2" style="color: var(--admin-primary);"></i>THÔNG TIN ĐỀ TÀI KHÓA LUẬN
                                        </h6>
                                        @if($currentDeTai)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                <i class="fa-solid fa-circle-check me-1"></i>{{ $currentDeTai->TrangThai ?? 'Đã duyệt' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        @if($currentDeTai)
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                                    <span class="badge badge-neutral px-2.5 py-1" style="font-size: 0.75rem;">Mã: <strong>{{ $currentDeTai->MaDeTai }}</strong></span>
                                                    @if($currentDeTai->LinhVuc)
                                                        <span class="badge badge-neutral px-2.5 py-1" style="font-size: 0.75rem;">{{ $currentDeTai->LinhVuc }}</span>
                                                    @endif
                                                </div>
                                                <h5 class="fw-bold mb-2 text-dark" style="line-height: 1.45; font-size: 1rem;">
                                                    <a href="javascript:void(0)" class="text-decoration-none" style="color: var(--admin-primary-dark);" data-bs-toggle="modal" data-bs-target="#modalTopicDetail" title="Xem chi tiết đề tài">
                                                        {{ $currentDeTai->TenDeTai }} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted" style="font-size: 0.75rem;"></i>
                                                    </a>
                                                </h5>
                                                @if($currentDeTai->TenDeTaiTiengAnh)
                                                    <div class="text-muted fst-italic mb-3" style="font-size: 0.85rem;">{{ $currentDeTai->TenDeTaiTiengAnh }}</div>
                                                @endif

                                                <!-- Đề cương chi tiết -->
                                                <div class="p-3 rounded-3 border border-slate-200 mb-3 bg-light">
                                                    <div class="fw-bold mb-1.5" style="color: var(--admin-primary-dark); font-size: 0.85rem;">
                                                        <i class="fa-solid {{ $currentDeTai->FileDeCuong ? 'fa-file-circle-check text-success' : 'fa-file-circle-question text-secondary' }} me-1"></i>
                                                        Đề Cương Chi Tiết:
                                                    </div>
                                                    @if($currentDeTai->FileDeCuong)
                                                        @php
                                                            $fileDcUrl = Str::startsWith($currentDeTai->FileDeCuong, ['http', 'storage/']) ? asset($currentDeTai->FileDeCuong) : asset('storage/' . $currentDeTai->FileDeCuong);
                                                            $fileName = basename($currentDeTai->FileDeCuong);
                                                        @endphp
                                                        <div class="text-muted mb-2" style="font-size: 0.8rem;">
                                                            Tệp: <code>{{ $fileName }}</code>
                                                        </div>
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold bg-white"
                                                                    onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($currentDeTai->TenDeTai ?? '') }}')" style="font-size: 0.8rem;">
                                                                <i class="fa-solid fa-eye me-1" style="color: var(--admin-primary);"></i>Xem nhanh
                                                            </button>
                                                            <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold bg-white" style="font-size: 0.8rem;">
                                                                <i class="fa-solid fa-download me-1"></i>Tải về
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="text-muted" style="font-size: 0.8rem;">
                                                            Chưa có tệp đề cương. Nhóm chờ GVHD tải lên hoặc duyệt.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 fw-semibold bg-white" data-bs-toggle="modal" data-bs-target="#modalTopicDetail" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-circle-info me-1" style="color: var(--admin-primary);"></i>Chi tiết đề tài
                                                </button>
                                            </div>
                                        @else
                                            <!-- Khi nhóm chưa đăng ký đề tài -->
                                            <div>
                                                @if(!$isFullGroup)
                                                    <div class="alert alert-warning border-warning p-3 rounded-3 mb-3" style="font-size: 0.85rem;">
                                                        <div class="fw-bold mb-1">
                                                            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Chưa đủ 3 thành viên để đăng ký đề tài
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.8rem;">
                                                            Hiện nhóm có <strong>{{ $memberCountCurrent }}/3 thành viên</strong>. Cần thêm <strong>{{ 3 - $memberCountCurrent }} sinh viên</strong> để mở quyền đăng ký.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-success border-success p-3 rounded-3 mb-3" style="font-size: 0.85rem;">
                                                        <div class="fw-bold text-success mb-1">
                                                            <i class="fa-solid fa-circle-check text-success me-1"></i>Nhóm đã đủ 3 thành viên!
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.8rem;">
                                                            Đã đủ điều kiện số lượng. Hãy truy cập cổng đăng ký để chọn đề tài khóa luận.
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                @if($isFullGroup)
                                                    <a href="{{ route('sinhvien.dangky.index', ['HocPhan' => $nhomCurrent->MaHocPhan ?? 'HP_KLCN', 'HocKy' => $nhomCurrent->MaHocKy]) }}" 
                                                       class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs">
                                                        <i class="fa-solid fa-clipboard-list me-1"></i>Đăng Ký Đề Tài Ngay
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3.5 py-2 fw-bold" disabled style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-lock me-1"></i>Đăng Ký (Cần 3 SV)
                                                    </button>
                                                    <button type="button" class="btn btn-cyan btn-sm rounded-pill px-3.5 py-2 shadow-xs" onclick="switchWorkspaceTab('tab-thanhvien')" style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-user-plus me-1"></i>Mời Bạn Vào Nhóm
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- TRƯỜNG HỢP CHƯA THAM GIA NHÓM -->
                        <div class="text-center py-5 px-3">
                            <div class="avatar-circle bg-light text-secondary border fs-1 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%;">
                                <i class="fa-solid fa-users-slash"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--admin-primary-dark);">Bạn Chưa Có Nhóm Cho Học Phần Này</h5>
                            <p class="text-muted mb-4 px-3" style="max-width: 520px; margin: 0 auto; font-size: 0.875rem;">
                                Bạn hiện chưa thuộc nhóm nào cho học phần này trong học kỳ đang chọn. Hãy tạo nhóm mới hoặc tìm kiếm nhóm mở để tham gia.
                            </p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                @if($isTaoNhomOpen ?? true)
                                    <button type="button" class="btn btn-cyan rounded-pill px-4.5 py-2.5 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                        <i class="fa-solid fa-plus-circle me-1"></i>Tạo Nhóm Mới Ngay
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary rounded-pill px-4.5 py-2.5 fw-bold" disabled>
                                        <i class="fa-solid fa-lock me-1"></i>Hết Hạn Tạo Nhóm
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-cyan rounded-pill px-4.5 py-2.5 fw-bold" onclick="switchWorkspaceTab('tab-timnhom')">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i>Tìm Nhóm Đang Mở
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- ################################################################## -->
                <!-- TAB 2: THÀNH VIÊN -->
                <!-- ################################################################## -->
                <div class="tab-pane fade" id="tab-thanhvien" role="tabpanel">
                    @if($nhomCurrent)
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 1rem;">
                                    <i class="fa-solid fa-user-group me-2" style="color: var(--admin-primary);"></i>Quản Trị Thành Viên Nhóm ({{ $nhomCurrent->thanhViens->count() }}/3)
                                </h5>
                                <div class="text-muted" style="font-size: 0.85rem;">Danh sách thành viên chính thức và chức năng mời thành viên cùng ngành</div>
                            </div>
                            @if($isNhomLocked)
                                <span class="badge bg-secondary rounded-pill px-3.5 py-2"><i class="fa-solid fa-lock me-1"></i>Nhóm đã khóa thành viên</span>
                            @else
                                <span class="badge {{ $nhomCurrent->thanhViens->count() === 3 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill px-3.5 py-2 fw-semibold">
                                    <i class="fa-solid fa-users me-1"></i>{{ $nhomCurrent->thanhViens->count() === 3 ? 'Đã đủ 3/3 thành viên' : 'Còn thiếu ' . (3 - $nhomCurrent->thanhViens->count()) . ' thành viên' }}
                                </span>
                            @endif
                        </div>

                        <!-- BẢNG DANH SÁCH THÀNH VIÊN -->
                        <div class="table-responsive mb-4 rounded-4 border border-slate-200 overflow-hidden shadow-xs">
                            <table class="table table-custom table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>MSSV</th>
                                        <th>Họ Và Tên</th>
                                        <th>Lớp</th>
                                        <th>Chuyên Ngành</th>
                                        <th class="text-center">Vai Trò</th>
                                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked)
                                        <th class="text-center">Thao Tác</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nhomCurrent->thanhViens as $tv)
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0)" class="btn-view-profile text-decoration-none fw-bold" 
                                               data-mssv="{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}" title="Xem hồ sơ">
                                                <span class="badge badge-neutral px-2.5 py-1"><code>{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}</code></span>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $tv->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted" style="font-size: 0.8rem;">{{ $tv->sinhVien->Email ?? '' }}</div>
                                        </td>
                                        <td>{{ $tv->sinhVien->lop->TenLop ?? 'Chưa rõ' }}</td>
                                        <td>{{ $tv->sinhVien->lop->nganh->TenNganh ?? $tv->sinhVien->nganh->TenNganh ?? 'Công nghệ thông tin' }}</td>
                                        <td class="text-center">
                                            @if($tv->VaiTro === 'Trưởng nhóm')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5">Trưởng nhóm</span>
                                            @else
                                                <span class="badge badge-neutral rounded-pill px-3 py-1.5">Thành viên</span>
                                            @endif
                                        </td>
                                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked)
                                        <td class="text-center">
                                            @if($tv->MaSV !== $sinhVien->MaSV)
                                                <form action="{{ route('sinhvien.nhom.khaiTru', ['id' => $nhomCurrent->MaNhom, 'maSV' => $tv->MaSV]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn KHAI TRỪ sinh viên {{ $tv->sinhVien->HoTen }} khỏi nhóm?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5" title="Khai trừ thành viên" style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-user-minus me-1"></i>Khai Trừ
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted fst-italic" style="font-size: 0.8rem;">Trưởng nhóm</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- KHU VỰC TRƯỞNG NHÓM MỜI THÀNH VIÊN -->
                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked && $nhomCurrent->thanhViens->count() < 3)
                            <div class="card border border-slate-200 shadow-xs rounded-4 p-4 mt-4 bg-white">
                                <h6 class="fw-bold mb-3" style="color: var(--admin-primary-dark); font-size: 0.95rem;">
                                    <i class="fa-solid fa-user-plus me-2" style="color: var(--admin-primary);"></i>Mời Sinh Viên Cùng Ngành Vào Nhóm Bằng MSSV
                                </h6>

                                @if(!($isTaoNhomOpen ?? false))
                                    <div class="alert alert-warning p-3 rounded-3 mb-0" style="font-size: 0.85rem;">
                                        <i class="fa-solid fa-clock me-1"></i>{{ $groupPhaseState['message'] ?? 'Cổng quản lý và ghép nhóm hiện đang đóng.' }}
                                    </div>
                                @else
                                    @if(session('invite_success'))
                                    <div class="alert alert-success alert-dismissible fade show p-3 mb-3 border-0 rounded-3" role="alert" style="font-size: 0.85rem;">
                                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('invite_success') }}
                                        <button type="button" class="btn-close p-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    @endif

                                    <div id="stepSearchArea">
                                        <div class="input-group">
                                            <input type="text" id="inputMSSVSearch" class="form-control py-2.5 px-3" placeholder="Nhập chính xác MSSV cùng ngành (ví dụ: 2001210060)..." style="font-size: 0.875rem;">
                                            <button class="btn btn-cyan px-4 fw-bold" type="button" id="btnDoSearch">
                                                <i class="fa-solid fa-magnifying-glass me-1"></i>Tra Cứu
                                            </button>
                                        </div>
                                        <div class="form-text text-muted mt-2" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-shield-halved me-1" style="color: var(--admin-primary);"></i>Hệ thống tự động kiểm tra điều kiện cùng ngành và điều kiện làm khóa luận trước khi mời.
                                        </div>
                                        <div id="searchErrorMessage" class="alert alert-danger p-3 mt-3 d-none" style="font-size: 0.85rem;"></div>
                                    </div>

                                    <!-- Thẻ xác nhận thông tin sinh viên -->
                                    <div id="stepVerificationCard" class="d-none mt-4">
                                        <div class="card border border-slate-300 bg-light p-4 rounded-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2.5 border-bottom">
                                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;"><i class="fa-solid fa-id-card me-2" style="color: var(--admin-primary);"></i>Xác Nhận Sinh Viên</h6>
                                                <span id="cardBadgeStatus" class="badge rounded-pill px-3 py-1" style="font-size: 0.75rem;"></span>
                                            </div>
                                            <div class="mb-3">
                                                <div class="fw-bold text-dark" id="cardHoTen" style="font-size: 0.95rem;"></div>
                                                <div class="text-muted mt-1" style="font-size: 0.8rem;">MSSV: <strong id="cardMSSV" class="text-dark"></strong></div>
                                            </div>
                                            <div class="row g-3 mb-3.5" style="font-size: 0.8rem;">
                                                <div class="col-6"><span class="text-muted">Lớp:</span> <strong id="cardLop" class="text-dark"></strong></div>
                                                <div class="col-6"><span class="text-muted">Ngành:</span> <strong id="cardNganh" class="text-dark"></strong></div>
                                            </div>
                                            <div class="p-3 rounded-3 bg-white border mb-3.5" id="cardStatusDetailBox" style="font-size: 0.8rem;">
                                                <div class="text-muted mb-1">Trạng thái:</div>
                                                <div class="fw-bold text-dark" id="cardStatusText"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2" id="btnBackSearch" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                                                </button>
                                                <form action="{{ route('sinhvien.nhom.moiThanhVien') }}" method="POST" id="formConfirmInvite">
                                                    @csrf
                                                    <input type="hidden" name="MaNhom" value="{{ $nhomCurrent->MaNhom }}">
                                                    <input type="hidden" name="MaSV" id="hiddenMaSV" value="">
                                                    <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs" id="btnSubmitInvite" style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận & Gửi Lời Mời
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users fs-1 opacity-50 mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">Bạn chưa tham gia nhóm nào</h6>
                            <p class="mb-4" style="font-size: 0.85rem;">Vui lòng tạo nhóm mới hoặc xin gia nhập một nhóm đang mở.</p>
                            @if($isTaoNhomOpen ?? true)
                                <button type="button" class="btn btn-cyan btn-sm rounded-pill px-4.5 py-2.5 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                    <i class="fa-solid fa-plus-circle me-1"></i>Tạo Nhóm Mới
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4.5 py-2.5 fw-bold" disabled>
                                    <i class="fa-solid fa-lock me-1"></i>Hết Hạn Tạo Nhóm
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- ################################################################## -->
                <!-- TAB 3: LỜI MỜI & YÊU CẦU -->
                <!-- ################################################################## -->
                <div class="tab-pane fade" id="tab-loimoi" role="tabpanel">
                    <div class="mb-4">
                        <h5 class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 1rem;"><i class="fa-solid fa-envelope-open-text me-2" style="color: var(--admin-primary);"></i>Quản Trị Lời Mời &amp; Yêu Cầu Ghép Nhóm</h5>
                        <div class="text-muted" style="font-size: 0.85rem;">Theo dõi và phản hồi các lời mời gia nhập nhóm hoặc yêu cầu xin vào nhóm</div>
                    </div>

                    <!-- 1. LỜI MỜI GIA NHẬP NHÓM GỬI TỚI BẠN -->
                    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">
                                <i class="fa-solid fa-bell text-warning me-2"></i>Lời Mời Tham Gia Nhóm Gửi Tới Bạn
                            </h6>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                {{ isset($loiMois) ? $loiMois->count() : 0 }} lời mời
                            </span>
                        </div>
                        <div class="card-body p-4">
                            @if(isset($loiMois) && $loiMois->count() > 0)
                                <div class="loi-moi-scroll-container">
                                    @foreach($loiMois as $lm)
                                    <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3.5 rounded-3 mb-3 border border-slate-200 shadow-xs">
                                        <div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                                                {{ $lm->nhom->TenNhom ?? 'Nhóm Khóa Luận' }}
                                                @if($lm->nhom && $lm->nhom->hocPhan)
                                                    <span class="badge badge-neutral ms-1.5 px-2.5 py-1" style="font-size: 0.725rem;">{{ $lm->nhom->hocPhan->TenHocPhan }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                Từ Trưởng nhóm: <strong>{{ $lm->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (<code>{{ $lm->nhom->truongNhom->taiKhoan->TenDangNhap ?? $lm->nhom->MaTruongNhom }}</code>)
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-2 mt-sm-0">
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-view-group-detail bg-white" data-manhom="{{ $lm->MaNhom }}" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-eye me-1" style="color: var(--admin-primary);"></i>Xem
                                            </button>
                                            <form action="{{ route('sinhvien.nhom.xacNhanLoiMoi', $lm->MaNhom) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-check me-1"></i>Chấp nhận
                                                </button>
                                            </form>
                                            <form action="{{ route('sinhvien.nhom.tuChoiLoiMoi', $lm->MaNhom) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2.5" style="font-size: 0.8rem;">Từ chối</button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-inbox fs-3 opacity-50 mb-2 d-block"></i>
                                    Hiện tại bạn không có lời mời tham gia nhóm nào.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. NẾU LÀ TRƯỞNG NHÓM -->
                    @if(isset($nhomCurrent) && $nhomCurrent->MaTruongNhom === $sinhVien->MaSV)
                    <div class="row g-4 mb-4">
                        <!-- Yêu Cầu Xin Vào Nhóm -->
                        <div class="col-12 col-md-6">
                            <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-light py-3 px-4 fw-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.9rem;">
                                    <span><i class="fa-solid fa-hand-holding-hand me-2 text-secondary"></i>Yêu Cầu Xin Vào Nhóm ({{ $yeuCauXinVao->count() }})</span>
                                </div>
                                <div class="card-body p-3.5">
                                    @forelse($yeuCauXinVao as $yc)
                                    <div class="d-flex justify-content-between align-items-center p-3 mb-2.5 border rounded-3 bg-white" style="font-size: 0.825rem;">
                                        <div>
                                            <div class="fw-bold text-dark mb-0.5">{{ $yc->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">MSSV: <code>{{ $yc->sinhVien->taiKhoan->TenDangNhap ?? $yc->MaSV }}</code></div>
                                        </div>
                                        <div class="d-flex gap-1.5">
                                            <form action="{{ route('sinhvien.nhom.duyetYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-2.5 py-1.5" title="Duyệt">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('sinhvien.nhom.tuChoiYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5" title="Từ chối">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4" style="font-size: 0.825rem;">Chưa có yêu cầu xin gia nhập.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Lời Mời Đã Gửi Đi -->
                        <div class="col-12 col-md-6">
                            <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-light py-3 px-4 fw-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.9rem;">
                                    <span><i class="fa-solid fa-paper-plane me-2 text-secondary"></i>Lời Mời Bạn Đã Gửi ({{ $loiMoiDaGui->count() }})</span>
                                </div>
                                <div class="card-body p-3.5">
                                    @forelse($loiMoiDaGui as $lm)
                                    <div class="d-flex justify-content-between align-items-center p-3 mb-2.5 border rounded-3 bg-white" style="font-size: 0.825rem;">
                                        <div>
                                            <div class="fw-semibold text-dark mb-0.5">{{ $lm->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">MSSV: <code>{{ $lm->sinhVien->taiKhoan->TenDangNhap ?? $lm->MaSV }}</code></div>
                                        </div>
                                        <form action="{{ route('sinhvien.nhom.huyLoiMoiDaGui', ['id' => $nhomCurrent->MaNhom, 'maSV' => $lm->MaSV]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5" title="Thu hồi">
                                                <i class="fa-solid fa-rotate-left"></i> Thu Hồi
                                            </button>
                                        </form>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4" style="font-size: 0.825rem;">Không có lời mời nào đang chờ.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 3. YÊU CẦU GIA NHẬP BẠN ĐÃ GỬI -->
                    @if(isset($yeuCauDaGui) && $yeuCauDaGui->count() > 0)
                    <div class="card border border-slate-200 shadow-xs rounded-4 overflow-hidden">
                        <div class="card-header bg-light py-3 px-4 fw-bold text-dark" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-hourglass-start me-2 text-warning"></i>Yêu Cầu Gia Nhập Bạn Đã Gửi ({{ $yeuCauDaGui->count() }})
                        </div>
                        <div class="card-body p-3.5">
                            @foreach($yeuCauDaGui as $maNhom => $yc)
                            <div class="d-flex justify-content-between align-items-center p-3.5 mb-2.5 border rounded-3 bg-white" style="font-size: 0.85rem;">
                                <div>
                                    <div class="fw-bold text-dark mb-1">{{ $yc->nhom->TenNhom ?? 'Nhóm ' . $maNhom }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">Trưởng nhóm: <strong>{{ $yc->nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong></div>
                                </div>
                                <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $maNhom) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3.5 py-1.5 text-dark fw-bold" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-xmark me-1"></i>Hủy Yêu Cầu
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- ################################################################## -->
                <!-- TAB 4: TÌM NHÓM MỞ -->
                <!-- ################################################################## -->
                <div class="tab-pane fade" id="tab-timnhom" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: var(--admin-primary-dark); font-size: 1rem;">
                                <i class="fa-solid fa-users-viewfinder me-2" style="color: var(--admin-primary);"></i>Danh Sách Nhóm Khóa Luận Đang Mở
                            </h5>
                            <div class="text-muted" style="font-size: 0.85rem;">Danh sách các nhóm khóa luận cùng ngành đang mở và còn trống chỗ</div>
                        </div>
                        @if($isTaoNhomOpen ?? true)
                            <button type="button" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                <i class="fa-solid fa-plus-circle me-1"></i>+ Tạo Nhóm Mới
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2 fw-bold" disabled>
                                <i class="fa-solid fa-lock me-1"></i>Đã Khóa Tạo Nhóm
                            </button>
                        @endif
                    </div>

                    <!-- THANH TÌM KIẾM NHÓM MỞ -->
                    <form method="GET" action="{{ route('sinhvien.nhom.index') }}" class="mb-4">
                        <input type="hidden" name="hoc_phan" value="{{ $selectedHocPhan }}">
                        <input type="hidden" name="hoc_ky" value="{{ $maHocKy }}">
                        <input type="hidden" name="bo_mon" value="{{ $selectedBoMon }}">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-secondary ps-3.5">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0 py-2.5 fw-semibold input-search-sync" 
                                   placeholder="🔍 Nhập MSSV hoặc tên nhóm..." value="{{ request('q') }}" style="font-size: 0.875rem;">
                            <button type="submit" class="btn btn-cyan px-4.5 fw-bold shadow-xs">
                                <i class="fa-solid fa-search me-1"></i>Tìm
                            </button>
                            @if(request('q'))
                            <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $selectedHocPhan, 'hoc_ky' => $maHocKy, 'bo_mon' => $selectedBoMon]) }}" class="btn btn-outline-secondary px-3.5" title="Xóa tìm kiếm">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                            @endif
                        </div>
                    </form>

                    <!-- SỐ LƯỢNG NHÓM -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge badge-neutral px-3.5 py-2" id="badge-group-count" style="font-size: 0.8rem;">
                            Tìm thấy {{ count($nhomsOpen ?? []) }} nhóm
                        </span>
                    </div>

                    @php
                        $userGroups = isset($sinhVienAllGroups) ? $sinhVienAllGroups->pluck('nhom')->filter() : collect();
                        $invitedGroupIds = isset($loiMois) ? $loiMois->pluck('MaNhom')->toArray() : [];
                    @endphp

                    <div class="nhom-open-scroll-container d-flex flex-column gap-3">
                        @forelse($nhomsOpen ?? [] as $no)
                        @php
                            if (in_array($no->MaNhom, $invitedGroupIds)) {
                                continue;
                            }
                            $memberCount = $no->thanhViens->count();
                            $isFull = ($memberCount >= 3);
                            $hasPdk = ($no->phieuDangKys && $no->phieuDangKys->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])->isNotEmpty()) 
                                      || (!empty($no->MaDeTai));
                            $hasRequestedThis = isset($yeuCauDaGui[$no->MaNhom]);
                            $isMyGroup = ($nhomCurrent && $nhomCurrent->MaNhom === $no->MaNhom) || $userGroups->contains('MaNhom', $no->MaNhom);
                            
                            $alreadyInThisCourseGroup = $userGroups->first(function($g) use ($no) {
                                return $g && $g->MaHocKy === $no->MaHocKy && $g->MaHocPhan === $no->MaHocPhan;
                            });
                        @endphp
                        <div class="card rounded-4 p-3.5 shadow-xs border group-item-card {{ $isMyGroup ? 'bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2.5">
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                                        <h6 class="fw-bold mb-0 text-dark" style="color: var(--admin-primary-dark) !important; font-size: 0.95rem;">{{ $no->TenNhom }}</h6>
                                        @if($isMyGroup)
                                            <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 0.725rem;">Nhóm của bạn</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-1.5 align-items-center">
                                        @if($no->hocPhan)
                                            <span class="badge badge-neutral px-2.5 py-1" style="font-size: 0.725rem;"><i class="fa-solid fa-graduation-cap me-1 text-secondary"></i>{{ $no->hocPhan->TenHocPhan }}</span>
                                        @endif
                                        @if($no->hocKy)
                                            <span class="badge badge-neutral px-2.5 py-1" style="font-size: 0.725rem;"><i class="fa-solid fa-calendar me-1 text-secondary"></i>{{ $no->hocKy->TenHocKy }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($hasPdk)
                                        <span class="badge bg-secondary rounded-pill px-3 py-1.5" style="font-size: 0.725rem;"><i class="fa-solid fa-book-bookmark me-1"></i>Đã Chọn Đề Tài</span>
                                    @elseif($isFull)
                                        <span class="badge bg-secondary rounded-pill px-3 py-1.5" style="font-size: 0.725rem;"><i class="fa-solid fa-lock me-1"></i>Đã Đủ 3/3</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5" style="font-size: 0.725rem;"><i class="fa-solid fa-user-plus me-1"></i>Còn {{ 3 - $memberCount }} Chỗ</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3" style="font-size: 0.85rem;">
                                <div class="text-dark">
                                    <i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm: <strong>{{ $no->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                    <code class="ms-1">({{ $no->truongNhom->taiKhoan->TenDangNhap ?? $no->MaTruongNhom }})</code>
                                </div>
                                <div class="text-muted mt-1">
                                    Lớp: {{ $no->truongNhom->lop->TenLop ?? 'Chưa rõ' }} &bull; Thành viên: <strong>{{ $memberCount }} / 3</strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2.5 border-top flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 btn-view-group-detail bg-white" data-manhom="{{ $no->MaNhom }}" style="font-size: 0.8rem;">
                                    <i class="fa-solid fa-eye me-1" style="color: var(--admin-primary);"></i>Chi tiết
                                </button>

                                @if($isMyGroup)
                                    <span class="badge badge-neutral px-3.5 py-2" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-check me-1 text-success"></i>Bạn đang ở trong nhóm này
                                    </span>
                                @elseif($hasRequestedThis)
                                    <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $no->MaNhom) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3.5 py-1.5 text-dark fw-bold" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-clock me-1"></i>Đang Chờ Duyệt (Hủy)
                                        </button>
                                    </form>
                                @elseif($hasPdk)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3.5 py-1.5" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-lock me-1"></i>Khóa Đề Tài
                                    </button>
                                @elseif($isFull)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3.5 py-1.5" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-ban me-1"></i>Đã Đủ Thành Viên
                                    </button>
                                @elseif(!($isTaoNhomOpen ?? true))
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3.5 py-1.5" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-lock me-1"></i>Hết Hạn
                                    </button>
                                @elseif($alreadyInThisCourseGroup)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3.5 py-1.5" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-ban me-1"></i>Đã Có Nhóm Môn Này
                                    </button>
                                @else
                                    <form action="{{ route('sinhvien.nhom.xinGiaNhap', $no->MaNhom) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-xs" onclick="return confirm('Bạn có chắc muốn gửi yêu cầu xin gia nhập nhóm {{ $no->TenNhom }}?');" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-hand me-1"></i>Xin Gia Nhập
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted" style="font-size: 0.875rem;">
                            <i class="fa-solid fa-users-slash fs-1 opacity-50 mb-3 d-block"></i>
                            <p class="mb-2">Không tìm thấy nhóm nào phù hợp với bộ lọc hoặc từ khóa tìm kiếm.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL XEM CHI TIẾT NHÓM -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGroupDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="font-size: 0.875rem;">
            <div class="modal-header text-white py-3.5 px-4.5" style="background-color: var(--admin-primary-dark) !important;">
                <div>
                    <h5 class="modal-title fw-bold" id="groupDetailName"><i class="fa-solid fa-users me-2"></i>Nhóm Khóa Luận</h5>
                    <div class="opacity-75" id="groupDetailSub" style="font-size: 0.8rem;">Chi tiết thành viên trong nhóm</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4.5">
                <div class="p-3.5 bg-light rounded-3 mb-4 border">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem;"><i class="fa-solid fa-calendar-alt me-1 text-secondary"></i>Học kỳ:</span>
                            <span class="badge badge-neutral text-wrap text-start px-2.5 py-1" id="groupDetailHocKy">--</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem;"><i class="fa-solid fa-book-open me-1 text-secondary"></i>Môn học / Học phần:</span>
                            <span class="badge badge-neutral text-wrap text-start px-2.5 py-1" id="groupDetailHocPhan">--</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3.5 pb-2.5 border-bottom">
                    <div>
                        <span class="text-muted" style="font-size: 0.8rem;">Quy mô:</span>
                        <strong class="text-dark ms-1" id="groupDetailCount">--</strong>
                    </div>
                    <div id="groupDetailStatusBadge"></div>
                </div>

                <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;"><i class="fa-solid fa-user-group text-secondary me-2"></i>DANH SÁCH THÀNH VIÊN</h6>
                <div id="groupDetailMembersList" class="d-flex flex-column gap-2.5 mb-3.5"></div>
                <div class="p-3 rounded-3 bg-light text-center text-muted" id="groupDetailSlotMessage" style="font-size: 0.825rem;"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between px-4.5 py-3.5 bg-light border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2" data-bs-dismiss="modal">Đóng</button>
                <div id="groupDetailActionArea"></div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL XEM HỒ SƠ SINH VIÊN -->
<!-- ======================================================== -->
<div class="modal fade" id="modalStudentProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="font-size: 0.875rem;">
            <div class="modal-header text-white py-3.5 px-4.5" style="background-color: var(--admin-primary-dark) !important;">
                <h6 class="modal-title fw-bold" id="modalProfileTitle"><i class="fa-solid fa-user-graduate me-2"></i>Hồ Sơ Sinh Viên</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4.5">
                <div class="text-center mb-4">
                    <div class="avatar-circle bg-light text-secondary border fs-2 fw-bold mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-radius: 50%;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" id="profileModalHoTen" style="font-size: 1.1rem;">--</h5>
                    <span class="badge badge-neutral px-3 py-1" id="profileModalMSSV">--</span>
                </div>
                <div class="list-group list-group-flush border-top border-bottom my-3.5" style="font-size: 0.85rem;">
                    <div class="list-group-item d-flex justify-content-between px-0 py-2.5">
                        <span class="text-muted"><i class="fa-solid fa-chalkboard me-2"></i>Lớp học:</span>
                        <strong id="profileModalLop" class="text-dark">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2.5">
                        <span class="text-muted"><i class="fa-solid fa-graduation-cap me-2"></i>Chuyên ngành:</span>
                        <strong id="profileModalNganh" class="text-dark">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2.5">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-2"></i>Email:</span>
                        <span id="profileModalEmail" class="text-dark">--</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2.5">
                        <span class="text-muted"><i class="fa-solid fa-users me-2"></i>Tình trạng nhóm:</span>
                        <span id="profileModalGroupStatus">--</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4.5 py-3.5 bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL CHI TIẾT ĐỀ TÀI -->
<!-- ======================================================== -->
@if(isset($nhomCurrent) && ($nhomCurrent->deTai || $nhomCurrent->dangKyDeTai?->deTai))
@php
    $dtDetail = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
@endphp
<div class="modal fade" id="modalTopicDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="font-size: 0.875rem;">
            <div class="modal-header text-white py-3.5 px-4.5" style="background-color: var(--admin-primary-dark) !important;">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-book-bookmark me-2"></i>Chi Tiết Đề Tài Khóa Luận
                    </h5>
                    <div class="opacity-75" style="font-size: 0.8rem;">Thông tin đề tài đã được gán chính thức cho nhóm của bạn</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4.5">
                <div class="mb-4 pb-3.5 border-bottom">
                    <div class="d-flex align-items-center gap-2 mb-2.5 flex-wrap">
                        <span class="badge badge-neutral px-2.5 py-1">Mã: <strong>{{ $dtDetail->MaDeTai }}</strong></span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1"><i class="fa-solid fa-circle-check me-1"></i>{{ $dtDetail->TrangThai }}</span>
                        @if($dtDetail->LinhVuc)
                            <span class="badge badge-neutral px-2.5 py-1">{{ $dtDetail->LinhVuc }}</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark mb-1.5" style="color: var(--admin-primary-dark) !important; font-size: 1.05rem;">{{ $dtDetail->TenDeTai }}</h5>
                    @if($dtDetail->TenDeTaiTiengAnh)
                        <div class="text-secondary fst-italic" style="font-size: 0.85rem;">{{ $dtDetail->TenDeTaiTiengAnh }}</div>
                    @endif
                </div>

                <div class="row g-3.5 mb-4" style="font-size: 0.85rem;">
                    <div class="col-md-6">
                        <div class="p-3.5 bg-light rounded-3 border h-100">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;"><i class="fa-solid fa-graduation-cap text-secondary me-2"></i>Thông tin môn học</h6>
                            <div class="mb-1.5"><span class="text-muted">Học kỳ:</span> <strong class="text-dark">{{ $dtDetail->hocKy->TenHocKy ?? $nhomCurrent->hocKy->TenHocKy ?? 'Chưa xác định' }}</strong></div>
                            <div><span class="text-muted">Quy mô:</span> <strong class="text-dark">Tối đa {{ $dtDetail->SoLuongSinhVienToiDa ?? 3 }} sinh viên</strong></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3.5 bg-light rounded-3 border h-100">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;"><i class="fa-solid fa-chalkboard-user text-secondary me-2"></i>GVHD</h6>
                            <div class="mb-1.5"><span class="text-muted">Họ tên:</span> <strong class="text-dark">{{ $dtDetail->giangVien->HoTen ?? 'Chưa gán' }}</strong></div>
                            @if($dtDetail->giangVien && $dtDetail->giangVien->Email)
                            <div><span class="text-muted">Email:</span> <code>{{ $dtDetail->giangVien->Email }}</code></div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($dtDetail->MoTa)
                <div class="mb-3.5" style="font-size: 0.85rem;">
                    <label class="fw-bold text-muted text-uppercase mb-1.5" style="font-size: 0.775rem;">Mô tả tóm tắt:</label>
                    <div class="p-3.5 bg-light rounded-3 border text-dark" style="line-height: 1.6;">
                        {!! \App\Helpers\HtmlHelper::clean($dtDetail->MoTa) !!}
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer px-4.5 py-3.5 bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ======================================================== -->
<!-- MODAL TẠO NHÓM MỚI -->
<!-- ======================================================== -->
<div class="modal fade" id="modalCreateGroup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="font-size: 0.875rem;">
            <form action="{{ route('sinhvien.nhom.store') }}" method="POST" id="formCreateGroup">
                @csrf
                <div class="modal-header border-bottom px-4.5 py-3.5 text-white" style="background-color: var(--admin-primary-dark) !important;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fa-solid fa-users-gear me-2"></i>Tạo Nhóm Khóa Luận / Đồ Án Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4.5">
                    <!-- BƯỚC 1: CHỌN HỌC KỲ -->
                    <div class="mb-4 p-3.5 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2" style="font-size: 0.875rem;">
                            <span><span class="badge badge-neutral px-2.5 py-1 me-1.5">Bước 1</span> Chọn Học Kỳ Áp Dụng <span class="text-danger">*</span></span>
                        </label>
                        <select name="MaHocKy" id="create_group_hocky" class="form-select border shadow-xs py-2.5" required style="font-size: 0.875rem;">
                            <option value="">-- Chọn Học Kỳ --</option>
                            @if(isset($hocKies))
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ (isset($maHocKy) && $maHocKy === $hk->MaHocKy) ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} — [{{ $hk->TrangThai }}]
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- BƯỚC 2: CHỌN HỌC PHẦN -->
                    <div class="mb-4 p-3.5 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2" style="font-size: 0.875rem;">
                            <span><span class="badge badge-neutral px-2.5 py-1 me-1.5">Bước 2</span> Chọn Học Phần Khóa Luận <span class="text-danger">*</span></span>
                            <span class="text-muted fw-normal" id="step2_badge_status" style="font-size: 0.8rem;"><i class="fa-solid fa-lock me-1"></i>Chọn Bước 1 trước</span>
                        </label>
                        <select name="MaHocPhan" id="create_group_hocphan" class="form-select border shadow-xs py-2.5" required disabled style="font-size: 0.875rem;">
                            <option value="">-- Vui lòng chọn học kỳ ở bước 1 trước --</option>
                        </select>
                        <div class="form-text text-muted mt-2" id="create_group_hocphan_help" style="font-size: 0.8rem;">
                            Học phần khóa luận chuyên ngành mở trong kỳ.
                        </div>
                    </div>

                    <!-- THÔNG TIN NHÓM TỰ SINH -->
                    <div class="card border border-slate-200 bg-white rounded-3 p-3.5 shadow-xs" style="font-size: 0.85rem;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="text-muted d-block mb-1" style="font-size: 0.8rem;">Tên nhóm tự sinh:</span>
                                <div class="fw-bold text-dark fs-6" style="color: var(--admin-primary-dark) !important;">
                                    <i class="fa-solid fa-users-rectangle me-1.5" style="color: var(--admin-primary);"></i>Nhóm {{ $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted d-block mb-1" style="font-size: 0.8rem;">Trưởng nhóm sáng lập:</span>
                                <div class="fw-bold text-dark">
                                    {{ $sinhVien->HoTen }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4.5 py-3.5 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">Hủy Bỏ</button>
                    @if($isTaoNhomOpen ?? true)
                        <button type="submit" id="btn_submit_create_group" class="btn btn-cyan rounded-pill px-4.5 py-2 fw-bold shadow-xs">
                            <i class="fa-solid fa-check-circle me-1.5"></i>Xác Nhận Tạo Nhóm
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary rounded-pill px-4.5 py-2 fw-bold" disabled>Tạm Khóa</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchWorkspaceTab(tabId) {
    const triggerEl = document.getElementById(tabId + '-tab');
    if (triggerEl) {
        const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
        tab.show();
        triggerEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

const allHocPhansList = @json($hocPhans ?? []);
const userJoinedGroupKeys = @json(isset($sinhVienAllGroups) ? $sinhVienAllGroups->filter(fn($g) => $g->nhom)->map(fn($g) => ($g->nhom->MaHocKy ?? '') . '_' . ($g->nhom->MaHocPhan ?? ''))->values() : []);

function populateFilterHocPhans(selectedBm, currentHpVal) {
    const filterHocPhan = document.getElementById('filter_hoc_phan');
    if (!filterHocPhan) return;
    filterHocPhan.innerHTML = '';

    const optAll = document.createElement('option');
    optAll.value = 'ALL';
    optAll.textContent = '-- Tất cả học phần --';
    if (!currentHpVal || currentHpVal === 'ALL') optAll.selected = true;
    filterHocPhan.appendChild(optAll);

    let filtered = allHocPhansList;
    if (selectedBm && selectedBm !== 'ALL') {
        filtered = allHocPhansList.filter(hp => hp.MaBoMon === selectedBm);
    }

    filtered.forEach(hp => {
        const opt = document.createElement('option');
        opt.value = hp.MaHocPhan;
        opt.textContent = hp.TenHocPhan;
        if (hp.MaHocPhan === currentHpVal) opt.selected = true;
        filterHocPhan.appendChild(opt);
    });
}

function onHocKyFilterChange() { applyCascadeFilter(); }
function onHocPhanFilterChange() { applyCascadeFilter(); }
function onBoMonChange() {
    const filterBoMon = document.getElementById('filter_bo_mon');
    populateFilterHocPhans(filterBoMon ? filterBoMon.value : 'ALL', null);
    applyCascadeFilter();
}

function applyCascadeFilter() {
    const hk = document.getElementById('filter_hoc_ky')?.value || '';
    const bm = document.getElementById('filter_bo_mon')?.value || '';
    const hp = document.getElementById('filter_hoc_phan')?.value || '';

    const url = new URL("{{ route('sinhvien.nhom.index') }}", window.location.origin);
    if (hk) url.searchParams.set('hoc_ky', hk);
    if (bm) url.searchParams.set('bo_mon', bm);
    if (hp) url.searchParams.set('hoc_phan', hp);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetTab = urlParams.get('tab') || window.location.hash.replace('#', '');
    if (targetTab) switchWorkspaceTab(targetTab);

    populateFilterHocPhans("{{ $selectedBoMon }}", "{{ $selectedHocPhan }}");

    // Live Search
    document.querySelectorAll('.input-search-sync').forEach(inputEl => {
        inputEl.addEventListener('input', function() {
            const lowerKey = this.value.trim().toLowerCase();
            let visibleCount = 0;
            document.querySelectorAll('.group-item-card').forEach(card => {
                if (!lowerKey || card.textContent.toLowerCase().includes(lowerKey)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            const badgeCount = document.getElementById('badge-group-count');
            if (badgeCount) badgeCount.textContent = `Tìm thấy ${visibleCount} nhóm`;
        });
    });

    // Tạo nhóm logic
    const hocPhansData = @json($hocPhans ?? []);
    const selectHocKy = document.getElementById('create_group_hocky');
    const selectHocPhan = document.getElementById('create_group_hocphan');
    const studentBoMon = '{{ str_replace("BM_", "", $sinhVien->getMaBoMon() ?? "") }}';

    function updateHocPhanByHocKy() {
        if (!selectHocKy || !selectHocPhan) return;
        const currentHk = selectHocKy.value;
        selectHocPhan.innerHTML = '';
        if (!currentHk) {
            selectHocPhan.disabled = true;
            selectHocPhan.innerHTML = '<option value="">-- Vui lòng chọn học kỳ trước --</option>';
            return;
        }

        const filtered = hocPhansData.filter(hp => {
            if (hp.hoc_phan_hoc_kies && Array.isArray(hp.hoc_phan_hoc_kies)) {
                return hp.hoc_phan_hoc_kies.some(hphk => hphk.MaHocKy === currentHk && hphk.TrangThai === 'Đang mở');
            }
            return true;
        });

        if (filtered.length === 0) {
            selectHocPhan.disabled = true;
            selectHocPhan.innerHTML = '<option value="">-- Học kỳ này chưa mở học phần --</option>';
            return;
        }

        selectHocPhan.disabled = false;
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = '-- Chọn học phần (' + filtered.length + ' môn mở) --';
        selectHocPhan.appendChild(defaultOpt);

        filtered.forEach(hp => {
            const opt = document.createElement('option');
            opt.value = hp.MaHocPhan;
            opt.textContent = hp.TenHocPhan;
            selectHocPhan.appendChild(opt);
        });
    }

    if (selectHocKy) {
        selectHocKy.addEventListener('change', updateHocPhanByHocKy);
        if (selectHocKy.value) updateHocPhanByHocKy();
    }

    // Tra cứu thành viên
    const btnDoSearch = document.getElementById('btnDoSearch');
    const inputMSSVSearch = document.getElementById('inputMSSVSearch');
    const stepVerificationCard = document.getElementById('stepVerificationCard');
    const searchErrorMessage = document.getElementById('searchErrorMessage');

    if (btnDoSearch && inputMSSVSearch) {
        btnDoSearch.addEventListener('click', function() {
            const mssv = inputMSSVSearch.value.trim();
            if (!mssv) return;
            fetch(`{{ route('sinhvien.nhom.traCuuSinhVien') }}?mssv=${encodeURIComponent(mssv)}&ma_nhom={{ $nhomCurrent->MaNhom ?? '' }}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        searchErrorMessage.textContent = data.message || 'Không tìm thấy sinh viên.';
                        searchErrorMessage.classList.remove('d-none');
                        stepVerificationCard.classList.add('d-none');
                        return;
                    }
                    searchErrorMessage.classList.add('d-none');
                    const sv = data.student;
                    document.getElementById('cardHoTen').textContent = sv.HoTen;
                    document.getElementById('cardMSSV').textContent = sv.MSSV;
                    document.getElementById('cardLop').textContent = sv.TenLop;
                    document.getElementById('cardNganh').textContent = sv.TenNganh;
                    document.getElementById('cardStatusText').textContent = data.status_text;
                    document.getElementById('hiddenMaSV').value = sv.MaSV;
                    stepVerificationCard.classList.remove('d-none');
                });
        });
    }

    // Modal Chi tiết nhóm
    const groupDetailModalEl = document.getElementById('modalGroupDetail');
    const groupDetailModal = groupDetailModalEl ? new bootstrap.Modal(groupDetailModalEl) : null;

    document.querySelectorAll('.btn-view-group-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const maNhom = this.dataset.manhom;
            if (!maNhom || !groupDetailModal) return;
            fetch(`{{ url('sinhvien/nhom') }}/${maNhom}/chi-tiet`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;
                    const nhom = data.nhom;
                    document.getElementById('groupDetailName').innerHTML = `<i class="fa-solid fa-users me-2"></i>${nhom.TenNhom}`;
                    document.getElementById('groupDetailHocKy').textContent = nhom.TenHocKy || '--';
                    document.getElementById('groupDetailHocPhan').textContent = nhom.TenHocPhan || '--';
                    document.getElementById('groupDetailCount').textContent = `${nhom.SoLuong} / 3 thành viên`;

                    const membersList = document.getElementById('groupDetailMembersList');
                    membersList.innerHTML = '';
                    data.thanhViens.forEach(tv => {
                        const item = document.createElement('div');
                        item.className = 'p-3 rounded-3 border bg-white';
                        item.style.fontSize = '0.825rem';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark">${tv.HoTen}</strong>
                                <span class="badge ${tv.IsLeader ? 'bg-warning text-dark' : 'badge-neutral'} px-2.5 py-1" style="font-size: 0.725rem;">${tv.VaiTro}</span>
                            </div>
                            <div class="text-muted">MSSV: <code>${tv.MSSV}</code> | Lớp: ${tv.TenLop}</div>
                        `;
                        membersList.appendChild(item);
                    });
                    groupDetailModal.show();
                });
        });
    });

    // Modal Profile
    const profileModalEl = document.getElementById('modalStudentProfile');
    const profileModal = profileModalEl ? new bootstrap.Modal(profileModalEl) : null;
    document.querySelectorAll('.btn-view-profile').forEach(btn => {
        btn.addEventListener('click', function() {
            const mssv = this.dataset.mssv;
            if (!mssv || !profileModal) return;
            fetch(`{{ route('sinhvien.nhom.traCuuSinhVien') }}?mssv=${encodeURIComponent(mssv)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.student) {
                        const sv = data.student;
                        document.getElementById('profileModalHoTen').textContent = sv.HoTen;
                        document.getElementById('profileModalMSSV').textContent = 'MSSV: ' + sv.MSSV;
                        document.getElementById('profileModalLop').textContent = sv.TenLop;
                        document.getElementById('profileModalNganh').textContent = sv.TenNganh;
                        document.getElementById('profileModalEmail').textContent = sv.Email;
                        document.getElementById('profileModalGroupStatus').innerHTML = `<span class="badge ${data.badge_class} px-2.5 py-1">${data.status_text}</span>`;
                        profileModal.show();
                    }
                });
        });
    });
});
</script>
@endpush

@include('partials.modal_preview_decuong')
@endsection