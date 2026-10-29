@extends('layouts.sinhvien')

@section('page_title', 'Quản Trị Nhóm Khóa Luận')

@section('content')
<style>
    /* Bảng màu chuẩn: Midnight Navy (#003366), Ocean Cyan (#0072CE), Persian Green (#10B981), Mint (#E5F0FA) */
    .nhom-page-root {
        font-size: 0.875rem;
        color: #334155;
        padding: 8px 4px 16px 4px;
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
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 51, 102, 0.05);
    }
    .group-item-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        box-shadow: 0 2px 6px rgba(0, 51, 102, 0.03);
    }
    .group-item-card:hover {
        transform: translateY(-2px);
        border-color: #0072CE;
        box-shadow: 0 8px 20px -4px rgba(0, 114, 206, 0.12);
    }

    /* Bảng dữ liệu chuẩn chỉnh với padding thoáng */
    .table-custom th {
        background-color: #003366 !important;
        font-weight: 700;
        font-size: 0.775rem !important;
        padding: 14px 16px !important;
        color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .table-custom td {
        font-size: 0.85rem !important;
        padding: 14px 16px !important;
        vertical-align: middle;
        line-height: 1.6;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Thanh điều hướng Tab có padding rộng rãi */
    .workspace-nav-pills {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        gap: 8px;
        padding: 8px;
        border-radius: 50rem !important;
    }
    .workspace-nav-pills .nav-link {
        color: #475569;
        background-color: transparent;
        border: 1px solid transparent;
        transition: all 0.18s ease;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 50rem !important;
    }
    .workspace-nav-pills .nav-link:hover {
        color: #003366;
        background-color: #e2e8f0;
    }
    .workspace-nav-pills .nav-link.active {
        color: #ffffff !important;
        background-color: #003366 !important;
        box-shadow: 0 4px 10px rgba(0, 51, 102, 0.25);
        border-color: #003366 !important;
    }
    .workspace-nav-pills .nav-link.active .nav-icon {
        color: #ffffff !important;
    }

    /* Tiến độ Timeline / Milestones tăng padding */
    .milestone-step-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 12px;
        position: relative;
        transition: all 0.16s ease;
    }
    .milestone-step-card:hover {
        border-color: #0072CE;
        background-color: #f8fafc;
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
        scrollbar-color: #0072CE #f1f5f9;
    }
    .loi-moi-scroll-container { max-height: 240px; }
    .nhom-open-scroll-container { max-height: 450px; }

    .loi-moi-scroll-container::-webkit-scrollbar,
    .nhom-open-scroll-container::-webkit-scrollbar {
        width: 5px;
    }
    .loi-moi-scroll-container::-webkit-scrollbar-track,
    .nhom-open-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .loi-moi-scroll-container::-webkit-scrollbar-thumb,
    .nhom-open-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .badge-neutral {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    /* Button Custom Cyan */
    .btn-cyan {
        background-color: #0072CE;
        border-color: #0072CE;
        color: #ffffff;
    }
    .btn-cyan:hover, .btn-cyan:focus {
        background-color: #006b73;
        border-color: #006b73;
        color: #ffffff;
    }
    .btn-outline-cyan {
        border-color: #0072CE;
        color: #0072CE;
    }
    .btn-outline-cyan:hover {
        background-color: #0072CE;
        color: #ffffff;
    }
</style>

<div class="nhom-page-root">

    <!-- ========================================================================================== -->
    <!-- [1] BỘ LỌC TẬP TRUNG (GLOBAL HEADER FILTER) - ĐỒNG BỘ GIAO DIỆN VỚI TRANG ĐĂNG KÝ -->
    <!-- ========================================================================================== -->
    <div class="card mb-4 bg-white border border-slate-200 shadow-xs rounded-4 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 px-1">
            <span class="fw-bold text-dark d-flex align-items-center" style="color: #003366 !important; font-size: 0.95rem;">
                <i class="fa-solid fa-filter me-2" style="color: #0072CE;"></i>Bộ Lọc Nhóm Khóa Luận:
            </span>
            @php
                $loiMoiCount = isset($loiMois) ? $loiMois->count() : 0;
            @endphp
            @if($loiMoiCount > 0)
                <button type="button" 
                        class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center"
                        onclick="switchWorkspaceTab('tab-loimoi')">
                    <i class="fa-regular fa-envelope-open me-2" style="color: #0072CE;"></i>Lời mời tham gia:
                    <span class="badge bg-danger text-white rounded-pill ms-2">{{ $loiMoiCount }}</span>
                </button>
            @endif
        </div>

        <!-- HÀNG 1: 3 CỘT CÂN ĐỐI (HỌC KỲ, BỘ MÔN, HỌC PHẦN) -->
        <div class="row g-3 align-items-center">
            <!-- 1. HỌC KỲ -->
            <div class="col-12 col-md-4">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-regular fa-calendar-days text-secondary me-2"></i>Học kỳ:
                    </span>
                    <select class="form-select form-select-sm filter-item-select" id="filter_hoc_ky" onchange="onHocKyFilterChange()">
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ $maHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '(Hiện tại)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. BỘ MÔN CHUYÊN NGÀNH (HIỂN THỊ DẠNG FILTER-ITEM-STATIC) -->
            <div class="col-12 col-md-4">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-solid fa-sitemap text-secondary me-2"></i>Bộ môn:
                    </span>
                    @php
                        $boMonDisplayName = $boMons->firstWhere('MaBoMon', $selectedBoMon)->TenBoMon ?? ($sinhVien->lop->nganh->TenNganh ?? 'Bộ môn chuyên ngành');
                    @endphp
                    <div class="filter-item-static" title="{{ $boMonDisplayName }}">
                        {{ $boMonDisplayName }}
                    </div>
                    <input type="hidden" id="filter_bo_mon" value="{{ $selectedBoMon }}">
                </div>
            </div>

            <!-- 3. HỌC PHẦN CHUYÊN NGÀNH -->
            <div class="col-12 col-md-4">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-solid fa-graduation-cap text-secondary me-2"></i>Học phần:
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
        </div>

        <!-- HÀNG 2: BỘ LỌC TRẠNG THÁI ĐỀ TÀI, CHỖ TRỐNG & TÌM KIẾM MSSV / TÊN NHÓM -->
        <form method="GET" action="{{ route('sinhvien.nhom.index') }}" id="groupFilterForm" class="mt-3 pt-3 border-top border-slate-200">
            <input type="hidden" name="hoc_ky" value="{{ $maHocKy }}">
            <input type="hidden" name="hoc_phan" value="{{ $selectedHocPhan }}">
            <input type="hidden" name="bo_mon" value="{{ $selectedBoMon }}">
            <input type="hidden" name="tab" value="tab-timnhom">

            <div class="row g-3 align-items-center">
                <!-- 1. Trạng Thái Đề Tài -->
                <div class="col-12 col-md-3">
                    <div class="filter-item-box">
                        <span class="filter-item-label">
                            <i class="fa-solid fa-book-bookmark text-secondary me-2"></i>Đề tài:
                        </span>
                        <select name="TrangThaiDeTai" class="filter-item-select" onchange="document.getElementById('groupFilterForm').submit()">
                            <option value="ALL" {{ ($trangThaiDeTai ?? 'ALL') === 'ALL' ? 'selected' : '' }}>Tất cả</option>
                            <option value="da_chon" {{ ($trangThaiDeTai ?? '') === 'da_chon' ? 'selected' : '' }}>Đã chọn đề tài</option>
                            <option value="chua_chon" {{ ($trangThaiDeTai ?? '') === 'chua_chon' ? 'selected' : '' }}>Chưa chọn đề tài (còn trống)</option>
                        </select>
                    </div>
                </div>

                <!-- 2. Số Chỗ Trống -->
                <div class="col-12 col-md-3">
                    <div class="filter-item-box">
                        <span class="filter-item-label">
                            <i class="fa-solid fa-users text-secondary me-2"></i>Chỗ trống:
                        </span>
                        <select name="ChoTrong" class="filter-item-select" onchange="document.getElementById('groupFilterForm').submit()">
                            <option value="ALL" {{ ($choTrong ?? 'ALL') === 'ALL' ? 'selected' : '' }}>Tất cả</option>
                            <option value="con_cho" {{ ($choTrong ?? '') === 'con_cho' ? 'selected' : '' }}>Còn chỗ trống (&lt;3 SV)</option>
                            <option value="da_du" {{ ($choTrong ?? '') === 'da_du' ? 'selected' : '' }}>Đã đủ thành viên (3 SV)</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Ô Tìm MSSV / Tên Nhóm + Nút Tìm (Không icon thừa, không emoji, bo góc đầy đủ, padding-left 14px) -->
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <div class="filter-item-box flex-grow-1" style="padding-left: 14px; padding-right: 14px;">
                            <input type="text" 
                                   name="q" 
                                   class="filter-item-select w-100 input-search-sync" 
                                   placeholder="Nhập MSSV hoặc tên nhóm..." 
                                   value="{{ request('q') }}" 
                                   style="font-size: 0.875rem; border-radius: 12px; padding-left: 0; outline: none; box-shadow: none; font-weight: normal;">
                        </div>
                        <button type="submit" class="btn btn-cyan px-4 fw-bold shadow-xs d-inline-flex align-items-center flex-shrink-0" style="height: 48px; border-radius: 12px;">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>Tìm
                        </button>
                        @if(request('q') || (isset($trangThaiDeTai) && $trangThaiDeTai !== 'ALL') || (isset($choTrong) && $choTrong !== 'ALL'))
                            <a href="{{ route('sinhvien.nhom.index', ['hoc_ky' => $maHocKy, 'hoc_phan' => $selectedHocPhan, 'bo_mon' => $selectedBoMon, 'tab' => 'tab-timnhom']) }}" 
                               class="btn btn-outline-secondary px-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" 
                               style="height: 48px; border-radius: 12px;" 
                               title="Đặt lại bộ lọc">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================================================================== -->
    <!-- [2] THANH TRẠNG THÁI & CHUYỂN ĐỔI NHÓM NHANH (WORKSPACE SWITCHER) -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 p-3 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold text-dark d-flex align-items-center" style="color: #003366 !important;">
                    <i class="fa-solid fa-layer-group me-2" style="color: #0072CE;"></i>Không Gian Nhóm:
                </span>
                
                @if(isset($sinhVienAllGroups) && $sinhVienAllGroups->count() > 0)
                    @foreach($sinhVienAllGroups as $g)
                        @if($g->nhom)
                            @php
                                $isActiveGroup = ($nhomCurrent && $nhomCurrent->MaNhom === $g->nhom->MaNhom);
                            @endphp
                            <a href="{{ route('sinhvien.nhom.index', ['nhom_id' => $g->nhom->MaNhom, 'hoc_ky' => $g->nhom->MaHocKy, 'hoc_phan' => $g->nhom->MaHocPhan]) }}"
                               class="btn btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 {{ $isActiveGroup ? 'btn-cyan shadow-xs' : 'btn-outline-secondary bg-white text-dark' }}">
                                <span class="d-inline-block rounded-circle {{ $isActiveGroup ? 'bg-white' : 'bg-secondary' }}" style="width: 8px; height: 8px;"></span>
                                <span>{{ $g->nhom->TenNhom }} ({{ $g->nhom->hocPhan->TenHocPhan ?? $g->nhom->MaHocPhan }})</span>
                                @if($isActiveGroup)
                                    <span class="badge bg-white text-dark rounded-pill px-2 py-1 ms-1" style="font-size: 10px;">Hiện tại</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                @else
                    <span class="badge badge-neutral rounded-pill px-3 py-2">
                        <i class="fa-solid fa-circle-info me-2 text-secondary"></i>Chưa tham gia nhóm nào
                    </span>
                @endif
            </div>

            <div>
                @if($isTaoNhomOpen ?? true)
                    <button type="button" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                        <i class="fa-solid fa-plus-circle me-2"></i>+ Tạo Nhóm Mới
                    </button>
                @else
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center" disabled title="Tạm thời không trong thời gian tạo nhóm">
                        <i class="fa-solid fa-lock me-2"></i>Hết Hạn Tạo Nhóm
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
            <div class="d-flex justify-content-between align-items-center mb-3 px-1 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: #003366;">
                        <i class="fa-solid fa-users-gear me-2" style="color: #0072CE;"></i>
                        <span>Quản Trị Nhóm Của Bạn</span>
                    </h5>
                    <div class="text-muted mt-1" style="font-size: 0.85rem;">Không gian điều phối thông tin nhóm, đề tài và thành viên khóa luận</div>
                </div>
                @if($nhomCurrent)
                    <span class="badge {{ $isNhomLocked ? 'bg-secondary' : ($nhomCurrent->thanhViens->count() === 3 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }} rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid {{ $isNhomLocked ? 'fa-lock' : 'fa-users' }} me-2"></i>
                        {{ $isNhomLocked ? 'Đã Khóa Đề Tài' : $nhomCurrent->thanhViens->count() . '/3 Thành Viên' }}
                    </span>
                @endif
            </div>

            <ul class="nav nav-pills workspace-nav-pills p-2" id="workspaceTabs" role="tablist">
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
                        <span class="badge {{ $nhomCurrent && $nhomCurrent->thanhViens->count() === 3 ? 'bg-primary text-white' : 'bg-secondary text-white' }} rounded-pill ms-2" style="font-size: 0.725rem;">
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
                            <span class="badge bg-danger text-white rounded-pill ms-2" style="font-size: 0.725rem;">{{ $totalInvitesAndReqs }}</span>
                        @endif
                    </button>
                </li>
                <!-- TAB 4: TÌM NHÓM MỞ -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="tab-timnhom-tab" data-bs-toggle="pill" data-bs-target="#tab-timnhom" type="button" role="tab">
                        <i class="fa-solid fa-magnifying-glass nav-icon me-2"></i>Tìm Nhóm Mở
                        <span class="badge badge-neutral rounded-pill ms-2" style="font-size: 0.725rem;">{{ count($nhomsOpen ?? []) }}</span>
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

                        <!-- BỐ CỤC 2 HỘP: THÔNG TIN NHÓM & THÔNG TIN ĐỀ TÀI (ĐÃ ĐƯỢC ĐƯA LÊN ĐẦU TIÊN) -->
                        <div class="row g-4 mb-4">
                            <!-- THÔNG TIN NHÓM & GVHD -->
                            <div class="col-12 col-lg-6">
                                <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.9rem;">
                                            <i class="fa-solid fa-id-card me-2" style="color: #0072CE;"></i>THÔNG TIN NHÓM &amp; GVHD
                                        </h6>
                                        <span class="badge {{ $isNhomLocked ? 'bg-secondary' : ($isFullGroup ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }} rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                            <i class="fa-solid {{ $isNhomLocked ? 'fa-lock' : 'fa-users' }} me-2"></i>
                                            {{ $isNhomLocked ? 'Đã Khóa Đề Tài' : $memberCountCurrent . '/3 Thành Viên' }}
                                        </span>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                            <div>
                                                <h5 class="fw-bold mb-1" style="color: #003366; font-size: 1rem;">
                                                    <i class="fa-solid fa-users me-2" style="color: #0072CE;"></i>{{ $nhomCurrent->TenNhom }}
                                                </h5>
                                                <div class="text-muted" style="font-size: 0.8rem;">Mã nhóm: <code>{{ $nhomCurrent->MaNhom }}</code></div>
                                            </div>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($nhomCurrent->hocKy)
                                                    <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-calendar me-2" style="color: #0072CE;"></i>{{ $nhomCurrent->hocKy->TenHocKy }}
                                                    </span>
                                                @endif
                                                @if($nhomCurrent->hocPhan)
                                                    <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-book me-2" style="color: #0072CE;"></i>{{ $nhomCurrent->hocPhan->TenHocPhan }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="p-3 bg-light rounded-3 border border-slate-200 mb-3">
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <span class="text-muted d-block mb-1" style="font-size: 0.775rem;"><i class="fa-solid fa-crown text-warning me-2"></i>Trưởng nhóm:</span>
                                                    <strong style="color: #003366; font-size: 0.875rem;">{{ $nhomCurrent->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                                    <div class="text-muted mt-1" style="font-size: 0.775rem;">MSSV: <code>{{ $nhomCurrent->truongNhom->taiKhoan->TenDangNhap ?? $nhomCurrent->MaTruongNhom }}</code></div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="text-muted d-block mb-1" style="font-size: 0.775rem;"><i class="fa-solid fa-chalkboard me-2" style="color: #0072CE;"></i>Lớp &amp; Ngành:</span>
                                                    <div class="fw-semibold text-dark" style="font-size: 0.875rem;">{{ $nhomCurrent->truongNhom->lop->TenLop ?? 'Chưa rõ' }}</div>
                                                    <div class="text-muted mt-1" style="font-size: 0.775rem;">{{ $nhomNganh ?? 'Công nghệ thông tin' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-3 bg-white rounded-3 border border-slate-200">
                                            <span class="text-muted d-block mb-1" style="font-size: 0.775rem;"><i class="fa-solid fa-chalkboard-user me-2" style="color: #0072CE;"></i>Giảng viên hướng dẫn (GVHD):</span>
                                            @if($currentDeTai && $currentDeTai->giangVien)
                                                <div class="fw-bold" style="color: #003366; font-size: 0.925rem;">{{ $currentDeTai->giangVien->HoTen }}</div>
                                                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                                    <span>{{ $currentDeTai->giangVien->HocVi ?? 'Giảng viên' }}</span>
                                                    <span class="mx-1">&bull;</span>
                                                    <span>{{ $currentDeTai->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                                                </div>
                                                @if($currentDeTai->giangVien->Email)
                                                    <div class="text-muted mt-1" style="font-size: 0.775rem;"><i class="fa-regular fa-envelope me-2"></i><code>{{ $currentDeTai->giangVien->Email }}</code></div>
                                                @endif
                                            @else
                                                <div class="text-muted fst-italic" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-circle-info me-2" style="color: #0072CE;"></i>Chưa gán GVHD. Giảng viên sẽ được phân công sau khi hoàn tất đăng ký đề tài.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light px-4 py-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="text-muted" style="font-size: 0.8rem;"><i class="fa-solid fa-user-group me-2"></i>Quy mô: <strong>{{ $memberCountCurrent }}/3 SV</strong></span>
                                        <button type="button" class="btn btn-cyan btn-sm rounded-pill px-3 py-2 shadow-xs d-inline-flex align-items-center" onclick="switchWorkspaceTab('tab-thanhvien')">
                                            <i class="fa-solid fa-users me-2"></i>Xem Thành Viên
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- THÔNG TIN ĐỀ TÀI KHÓA LUẬN -->
                            <div class="col-12 col-lg-6">
                                <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.9rem;">
                                            <i class="fa-solid fa-book-bookmark me-2" style="color: #0072CE;"></i>THÔNG TIN ĐỀ TÀI KHÓA LUẬN
                                        </h6>
                                        @if($currentDeTai)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                <i class="fa-solid fa-circle-check me-2"></i>{{ $currentDeTai->TrangThai ?? 'Đã duyệt' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        @if($currentDeTai)
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                                    <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem;">Mã: <strong>{{ $currentDeTai->MaDeTai }}</strong></span>
                                                    @if($currentDeTai->LinhVuc)
                                                        <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem;">{{ $currentDeTai->LinhVuc }}</span>
                                                    @endif
                                                </div>
                                                <h5 class="fw-bold mb-2 text-dark" style="line-height: 1.45; font-size: 1rem;">
                                                    <a href="javascript:void(0)" class="text-decoration-none" style="color: #003366;" data-bs-toggle="modal" data-bs-target="#modalTopicDetail" title="Xem chi tiết đề tài">
                                                        {{ $currentDeTai->TenDeTai }} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted" style="font-size: 0.75rem;"></i>
                                                    </a>
                                                </h5>
                                                @if($currentDeTai->TenDeTaiTiengAnh)
                                                    <div class="text-muted fst-italic mb-3" style="font-size: 0.85rem;">{{ $currentDeTai->TenDeTaiTiengAnh }}</div>
                                                @endif

                                                <!-- Đề cương chi tiết -->
                                                <div class="p-3 rounded-3 border border-slate-200 mb-3 bg-light">
                                                    <div class="fw-bold mb-2" style="color: #003366; font-size: 0.85rem;">
                                                        <i class="fa-solid {{ $currentDeTai->coTepDeCuong() ? 'fa-file-circle-check text-primary' : 'fa-file-circle-question text-secondary' }} me-1"></i>
                                                        Đề Cương Chi Tiết:
                                                    </div>
                                                    @if($currentDeTai->coTepDeCuong())
                                                        @php
                                                            $fileDcUrl = Str::startsWith($currentDeTai->FileDeCuong, ['http', 'storage/']) ? asset($currentDeTai->FileDeCuong) : asset('storage/' . $currentDeTai->FileDeCuong);
                                                            $fileName = basename($currentDeTai->FileDeCuong);
                                                        @endphp
                                                        <div class="text-muted mb-2" style="font-size: 0.8rem;">
                                                            Tệp: <code>{{ $fileName }}</code>
                                                        </div>
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold bg-white d-inline-flex align-items-center"
                                                                    onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($currentDeTai->TenDeTai ?? '') }}')" style="font-size: 0.8rem;">
                                                                <i class="fa-solid fa-eye me-2" style="color: #0072CE;"></i>Xem nhanh
                                                            </button>
                                                            <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold bg-white d-inline-flex align-items-center" style="font-size: 0.8rem;">
                                                                <i class="fa-solid fa-download me-2"></i>Tải về
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
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold bg-white d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTopicDetail" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-circle-info me-2" style="color: #0072CE;"></i>Chi tiết đề tài
                                                </button>
                                            </div>
                                        @else
                                            <!-- Khi nhóm chưa đăng ký đề tài -->
                                            <div>
                                                @if(!$isFullGroup)
                                                    <div class="alert alert-warning border-warning p-3 rounded-3 mb-3" style="font-size: 0.85rem;">
                                                        <div class="fw-bold mb-1">
                                                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Chưa đủ 3 thành viên để đăng ký đề tài
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.8rem;">
                                                            Hiện nhóm có <strong>{{ $memberCountCurrent }}/3 thành viên</strong>. Cần thêm <strong>{{ 3 - $memberCountCurrent }} sinh viên</strong> để mở quyền đăng ký.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-success border-primary p-3 rounded-3 mb-3" style="font-size: 0.85rem;">
                                                        <div class="fw-bold text-primary mb-1">
                                                            <i class="fa-solid fa-circle-check text-primary me-2"></i>Nhóm đã đủ 3 thành viên!
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
                                                        <i class="fa-solid fa-clipboard-list me-2"></i>Đăng Ký Đề Tài Ngay
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center" disabled style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-lock me-2"></i>Đăng Ký (Cần 3 SV)
                                                    </button>
                                                    <button type="button" class="btn btn-cyan btn-sm rounded-pill px-3 py-2 shadow-xs d-inline-flex align-items-center" onclick="switchWorkspaceTab('tab-thanhvien')" style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-user-plus me-2"></i>Mời Bạn Vào Nhóm
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TIẾN ĐỘ & BẢNG SỰ KIỆN QUA CÁC GIAI ĐOẠN (TIMELINE / MILESTONES) -->
                        <div class="card border border-slate-200 shadow-xs rounded-4 overflow-hidden mb-4">
                            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.9rem;">
                                    <i class="fa-solid fa-chart-line me-2" style="color: #0072CE;"></i>TIẾN ĐỘ &amp; CÁC GIAI ĐOẠN KHÓA LUẬN (TIMELINE)
                                </h6>
                                <span class="badge badge-neutral px-3 py-2" style="font-size: 0.75rem;">Quy trình chuẩn</span>
                            </div>
                            <div class="card-body p-3 px-4">
                                 <div class="row g-3 align-items-center text-center">
                                    <!-- Giai đoạn 1: Mở nhóm -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle bg-primary text-white">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: #003366; font-size: 0.85rem;">1. Mở Nhóm</div>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                <i class="fa-solid fa-check me-2"></i>Hoàn thành
                                            </span>
                                            <div class="text-muted mt-2" style="font-size: 0.775rem;">Đã khởi tạo nhóm</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 2: Đủ 3 SV -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ $isFullGroup ? 'bg-primary text-white' : 'bg-warning text-dark' }}">
                                                <i class="fa-solid {{ $isFullGroup ? 'fa-user-check' : 'fa-user-clock' }}"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: #003366; font-size: 0.85rem;">2. Đủ 3 SV</div>
                                            @if($isFullGroup)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-check me-2"></i>Đủ 3/3 SV
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-hourglass-half me-2"></i>{{ $memberCountCurrent }}/3 SV
                                                </span>
                                            @endif
                                            <div class="text-muted mt-2" style="font-size: 0.775rem;">{{ $isFullGroup ? 'Sẵn sàng đăng ký' : 'Cần thêm ' . (3 - $memberCountCurrent) . ' bạn' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 3: Đăng ký đề tài -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ $currentDeTai ? 'bg-primary text-white' : ($isFullGroup ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border') }}">
                                                <i class="fa-solid fa-book-bookmark"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: #003366; font-size: 0.85rem;">3. Đăng Ký Đề Tài</div>
                                            @if($currentDeTai)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-check me-2"></i>Đã duyệt
                                                </span>
                                            @elseif($isFullGroup)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-arrow-right me-2"></i>Đang đăng ký
                                                </span>
                                            @else
                                                <span class="badge badge-neutral rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-lock me-2"></i>Chưa mở
                                                </span>
                                            @endif
                                            <div class="text-muted mt-2" style="font-size: 0.775rem;">{{ $currentDeTai ? 'Đã gán đề tài' : 'Chờ đủ thành viên' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 4: Nộp đề cương / Báo cáo -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ ($currentDeTai && $currentDeTai->coTepDeCuong()) ? 'bg-primary text-white' : ($currentDeTai ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-light text-muted border') }}">
                                                <i class="fa-solid fa-file-signature"></i>
                                            </div>
                                            <div class="fw-bold mb-1" style="color: #003366; font-size: 0.85rem;">4. Nộp Báo Cáo</div>
                                            @if($currentDeTai && $currentDeTai->coTepDeCuong())
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-check me-2"></i>Có đề cương
                                                </span>
                                            @elseif($currentDeTai)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-clock me-2"></i>Chờ nộp
                                                </span>
                                            @else
                                                <span class="badge badge-neutral rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                    <i class="fa-solid fa-lock me-2"></i>Chưa mở
                                                </span>
                                            @endif
                                            <div class="text-muted mt-2" style="font-size: 0.775rem;">Đề cương &amp; Báo cáo</div>
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
                                            <div class="fw-bold mb-1" style="color: #003366; font-size: 0.85rem;">5. Bảo Vệ</div>
                                            <span class="badge badge-neutral rounded-pill px-3 py-1" style="font-size: 0.725rem;">
                                                <i class="fa-solid fa-lock me-2"></i>Giai đoạn cuối
                                            </span>
                                            <div class="text-muted mt-2" style="font-size: 0.775rem;">Hội đồng bảo vệ</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KẾT QUẢ BẢO VỆ & HOÀN THIỆN BÁO CÁO SAU HỘI ĐỒNG (YÊU CẦU 5) -->
                        @php
                            $currentHoSo = $nhomCurrent->hoSoBaoVe;
                            $hasDefenseInfo = $currentHoSo && ($currentHoSo->hoiDong || !empty($currentHoSo->YeuCauChinhSuaSauBaoVe) || in_array($currentHoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaNopChinhSua', 'DaHoanThanh', 'ChuaDat']));
                            $latestBaoCaoSauBaoVe = $currentHoSo?->baoCaoSauBaoVes?->sortByDesc('LanChinhSua')?->first();
                            $lichSuBaoCaoSauBaoVe = $currentHoSo?->baoCaoSauBaoVes?->sortByDesc('LanChinhSua') ?? collect();
                            $hanNopSauBaoVe = $currentHoSo?->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($currentHoSo->HanNopBaoCaoSauBaoVe) : null;
                            $isQuaHanNopSauBaoVe = $hanNopSauBaoVe && $hanNopSauBaoVe->isPast();
                            $isLeaderOfCurrentGroup = ($nhomCurrent->MaTruongNhom === $sinhVien->MaSV) || ($nhomCurrent->truongNhom?->MaSV === $sinhVien->MaSV);
                            $trangThaiSuaBV = $currentHoSo?->TrangThaiChinhSuaSauBaoVe ?? 'ChoSinhVienNop';
                        @endphp

                        @if($hasDefenseInfo)
                        <div class="card border border-slate-200 shadow-xs rounded-4 overflow-hidden mb-4 bg-white" style="border-left: 5px solid {{ $trangThaiSuaBV === 'DaHoanThanh' ? '#10B981' : ($isQuaHanNopSauBaoVe ? '#EF4444' : '#0072CE') }} !important;">
                            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.95rem;">
                                    <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>KẾT QUẢ BẢO VỆ &amp; BÁO CÁO HOÀN THIỆN SAU HỘI ĐỒNG
                                </h6>
                                <div>
                                    @if($trangThaiSuaBV === 'DaHoanThanh')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-circle-check me-1"></i> Báo Cáo Đã Đạt Chuẩn Lưu Trữ
                                        </span>
                                    @elseif($trangThaiSuaBV === 'DaNop')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-hourglass-half me-1"></i> Đã Nộp — Chờ Giảng Viên Kiểm Tra
                                        </span>
                                    @elseif($trangThaiSuaBV === 'YeuCauSuaTiep')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-rotate-right me-1"></i> Yêu Cầu Chỉnh Sửa Tiếp
                                        </span>
                                    @elseif(!empty($currentHoSo->YeuCauChinhSuaSauBaoVe))
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Chờ Nhóm Nộp Bản Chỉnh Sửa
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-check me-1"></i> Đã Có Kết Quả Bảo Vệ
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <!-- 1. Thông tin kết quả bảo vệ & Hội đồng -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-light border border-slate-200 h-100">
                                            <div class="text-muted small mb-1"><i class="fa-solid fa-landmark me-1 text-primary"></i>Hội Đồng Bảo Vệ:</div>
                                            <div class="fw-bold text-dark">{{ $currentHoSo->hoiDong->TenHoiDong ?? 'Hội đồng Khoa CNTT' }}</div>
                                            <div class="small text-muted mt-1">
                                                <span>Phòng: <strong>{{ $currentHoSo->PhongBaoVe ?? ($currentHoSo->hoiDong->DiaDiem ?? 'Phòng bảo vệ Khoa') }}</strong></span>
                                                @if($currentHoSo->ThoiGianBaoVe)
                                                    <span class="mx-1">&bull;</span>
                                                    <span>Ngày: <strong>{{ \Carbon\Carbon::parse($currentHoSo->ThoiGianBaoVe)->format('d/m/Y H:i') }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 bg-light border border-slate-200 h-100">
                                            <div class="text-muted small mb-1"><i class="fa-solid fa-clipboard-check me-1 text-success"></i>Kết Quả Bảo Vệ:</div>
                                            <div class="fw-bold text-dark">
                                                @if(in_array($currentHoSo->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh']))
                                                    <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>ĐẠT BẢO VỆ KHÓA LUẬN</span>
                                                @elseif($currentHoSo->TrangThaiKetQua === 'CoYeuCauChinhSua' || !empty($currentHoSo->YeuCauChinhSuaSauBaoVe))
                                                    <span class="text-warning-emphasis"><i class="fa-solid fa-triangle-exclamation me-1"></i>ĐẠT CÓ ĐIỀU KIỆN (Yêu cầu chỉnh sửa hoàn thiện)</span>
                                                @elseif($currentHoSo->TrangThaiKetQua === 'ChuaDat')
                                                    <span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>KHÔNG ĐẠT</span>
                                                @else
                                                    <span class="text-secondary"><i class="fa-solid fa-clock me-1"></i>Đã hoàn thành phiên bảo vệ (Chờ công bố điểm chính thức)</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted mt-1">
                                                Người kiểm tra hoàn thiện: <strong>{{ $currentHoSo->NguoiKiemTraChinhSua ?? ($nhomCurrent->deTai?->giangVien?->HoTen ?? 'GVHD') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Nội dung Hội đồng yêu cầu chỉnh sửa & Thời hạn nộp lại -->
                                @if(!empty($currentHoSo->YeuCauChinhSuaSauBaoVe))
                                <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <span class="fw-bold text-dark small text-uppercase">
                                            <i class="fa-solid fa-clipboard-question text-warning me-1"></i>Nội Dung Hội Đồng Yêu Cầu Chỉnh Sửa, Bổ Sung:
                                        </span>
                                        @if($hanNopSauBaoVe)
                                            <span class="badge {{ $isQuaHanNopSauBaoVe ? 'bg-danger' : 'bg-primary' }} rounded-pill px-3 py-1 font-monospace">
                                                <i class="fa-regular fa-clock me-1"></i> Hạn chót: {{ $hanNopSauBaoVe->format('d/m/Y H:i') }}
                                                {{ $isQuaHanNopSauBaoVe ? '(Hết hạn)' : '(' . $hanNopSauBaoVe->diffForHumans() . ')' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="bg-white p-3 rounded-3 border text-dark small" style="line-height: 1.6;">
                                        {!! \App\Helpers\HtmlHelper::renderRichText($currentHoSo->YeuCauChinhSuaSauBaoVe) !!}
                                    </div>
                                </div>
                                @endif

                                <!-- 3. Phiên bản báo cáo đã nộp gần nhất & Ý kiến phản hồi -->
                                @if($latestBaoCaoSauBaoVe)
                                <div class="p-3 rounded-3 bg-light border border-slate-200 mb-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="fw-bold small text-dark mb-1">
                                                <i class="fa-solid fa-file-pdf text-danger me-1"></i>Phiên Bản Đã Nộp Gần Nhất (Lần {{ $latestBaoCaoSauBaoVe->LanChinhSua }}):
                                            </div>
                                            <div class="fw-semibold text-primary text-truncate">{{ $latestBaoCaoSauBaoVe->TenFile }}</div>
                                            <div class="small text-muted mt-1">
                                                Thời điểm nộp: <strong>{{ \Carbon\Carbon::parse($latestBaoCaoSauBaoVe->NgayNop)->format('d/m/Y H:i') }}</strong>
                                                &bull; Người nộp: <strong>{{ $latestBaoCaoSauBaoVe->sinhVienNop->HoTen ?? $latestBaoCaoSauBaoVe->NguoiNop }}</strong>
                                            </div>
                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;"
                                                    onclick="quickPreviewOutline('{{ route('baocao_sau_baove.pdf', $latestBaoCaoSauBaoVe->id) }}', '{{ addslashes($latestBaoCaoSauBaoVe->TenFile) }}', 'Báo cáo hoàn thiện - {{ addslashes($currentHoSo->nhom->deTai->TenDeTai ?? $currentHoSo->MaHoSo) }}', { downloadUrl: '{{ route('baocao_sau_baove.download', $latestBaoCaoSauBaoVe->id) }}' })">
                                                    <i class="fa-solid fa-eye me-1"></i> Xem Báo Cáo PDF
                                                </button>
                                                @if($latestBaoCaoSauBaoVe->FileGiaiTrinh)
                                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;"
                                                    onclick="quickPreviewOutline('{{ route('baocao_sau_baove.pdf', ['id' => $latestBaoCaoSauBaoVe->id, 'type' => 'giai_trinh']) }}', '{{ addslashes($latestBaoCaoSauBaoVe->TenFileGiaiTrinh ?: 'Ban_Giai_Trinh.pdf') }}', 'Bản Giải Trình', { downloadUrl: '{{ route('baocao_sau_baove.download', ['id' => $latestBaoCaoSauBaoVe->id, 'type' => 'giai_trinh']) }}' })">
                                                    <i class="fa-solid fa-file-lines me-1"></i> Bản Giải Trình
                                                </button>
                                                @endif
                                                @if($latestBaoCaoSauBaoVe->FileMinhChung)
                                                <a href="{{ route('baocao_sau_baove.download', ['id' => $latestBaoCaoSauBaoVe->id, 'type' => 'minh_chung']) }}" download="{{ $latestBaoCaoSauBaoVe->TenFileMinhChung ?: 'MinhChung.zip' }}" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                                                    <i class="fa-solid fa-paperclip me-1"></i> Minh Chứng
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-bold small text-dark mb-1">
                                                <i class="fa-solid fa-comment-dots text-info me-1"></i>Ý Kiến Phản Hồi Của Người Kiểm Tra:
                                            </div>
                                            @if(!empty($latestBaoCaoSauBaoVe->NhanXetGV))
                                                <div class="p-2 bg-white rounded-3 border text-dark small" style="line-height: 1.5;">
                                                    {!! \App\Helpers\HtmlHelper::renderRichText($latestBaoCaoSauBaoVe->NhanXetGV) !!}
                                                </div>
                                                <div class="small text-muted mt-1" style="font-size: 0.725rem;">
                                                    <i class="fa-regular fa-clock me-1"></i> {{ $latestBaoCaoSauBaoVe->NgayKiemTra ? \Carbon\Carbon::parse($latestBaoCaoSauBaoVe->NgayKiemTra)->format('d/m/Y H:i') : '' }} &bull; Đánh giá bởi: <strong>{{ $latestBaoCaoSauBaoVe->NguoiKiemTra }}</strong>
                                                </div>
                                            @else
                                                <div class="p-2 bg-white rounded-3 border text-muted small fst-italic">
                                                    Giảng viên đang tiếp nhận kiểm tra nội dung bản báo cáo chỉnh sửa.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- 4. Lịch sử các lần nộp lại & Nút thao tác nộp lại -->
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                                    <div>
                                        <span class="text-muted small">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Lịch sử nộp lại: <strong>{{ $lichSuBaoCaoSauBaoVe->count() }} lần</strong>
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-semibold">
                                            <i class="fa-solid fa-list-check me-1"></i> Chi Tiết &amp; Lịch Sử Nộp
                                        </a>

                                        @if($trangThaiSuaBV !== 'DaHoanThanh' && !$isQuaHanNopSauBaoVe && $isLeaderOfCurrentGroup)
                                            <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-sm btn-cyan rounded-pill px-4 fw-bold shadow-xs">
                                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Nộp Lại Báo Cáo Chỉnh Sửa
                                            </a>
                                        @elseif($trangThaiSuaBV === 'DaHoanThanh')
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-semibold" disabled>
                                                <i class="fa-solid fa-check-double me-1"></i> Đã Hoàn Thành Toàn Bộ
                                            </button>
                                        @elseif($isQuaHanNopSauBaoVe)
                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Đã quá hạn nộp lại">
                                                <i class="fa-solid fa-clock me-1"></i> Đã Hết Hạn Nộp Lại
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    @else
                        <!-- TRƯỜNG HỢP CHƯA THAM GIA NHÓM -->
                        <div class="text-center py-5 px-3">
                            <div class="avatar-circle bg-light text-secondary border fs-1 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%;">
                                <i class="fa-solid fa-users-slash"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: #003366;">Bạn Chưa Có Nhóm Cho Học Phần Này</h5>
                            <p class="text-muted mb-4 px-3" style="max-width: 520px; margin: 0 auto; font-size: 0.875rem;">
                                Bạn hiện chưa thuộc nhóm nào cho học phần này trong học kỳ đang chọn. Hãy tạo nhóm mới hoặc tìm kiếm nhóm mở để tham gia.
                            </p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                @if($isTaoNhomOpen ?? true)
                                    <button type="button" class="btn btn-cyan rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                        <i class="fa-solid fa-plus-circle me-2"></i>Tạo Nhóm Mới Ngay
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center" disabled>
                                        <i class="fa-solid fa-lock me-2"></i>Hết Hạn Tạo Nhóm
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-cyan rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center" onclick="switchWorkspaceTab('tab-timnhom')">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i>Tìm Nhóm Đang Mở
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
                                <h5 class="fw-bold mb-1" style="color: #003366; font-size: 1rem;">
                                    <i class="fa-solid fa-user-group me-2" style="color: #0072CE;"></i>Quản Trị Thành Viên Nhóm ({{ $nhomCurrent->thanhViens->count() }}/3)
                                </h5>
                                <div class="text-muted" style="font-size: 0.85rem;">Danh sách thành viên chính thức và chức năng mời thành viên cùng ngành</div>
                            </div>
                            @if($isNhomLocked)
                                <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="fa-solid fa-lock me-2"></i>Nhóm đã khóa thành viên</span>
                            @else
                                <span class="badge {{ $nhomCurrent->thanhViens->count() === 3 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-users me-2"></i>{{ $nhomCurrent->thanhViens->count() === 3 ? 'Đã đủ 3/3 thành viên' : 'Còn thiếu ' . (3 - $nhomCurrent->thanhViens->count()) . ' thành viên' }}
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
                                                <span class="badge badge-neutral px-3 py-1"><code>{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}</code></span>
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
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2">Trưởng nhóm</span>
                                            @else
                                                <span class="badge badge-neutral rounded-pill px-3 py-2">Thành viên</span>
                                            @endif
                                        </td>
                                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked)
                                        <td class="text-center">
                                            @if($tv->MaSV !== $sinhVien->MaSV)
                                                <form action="{{ route('sinhvien.nhom.khaiTru', ['id' => $nhomCurrent->MaNhom, 'maSV' => $tv->MaSV]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn KHAI TRỪ sinh viên {{ $tv->sinhVien->HoTen }} khỏi nhóm?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 d-inline-flex align-items-center" title="Khai trừ thành viên" style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-user-minus me-2"></i>Khai Trừ
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
                                <h6 class="fw-bold mb-3" style="color: #003366; font-size: 0.95rem;">
                                    <i class="fa-solid fa-user-plus me-2" style="color: #0072CE;"></i>Mời Sinh Viên Cùng Ngành Vào Nhóm Bằng MSSV
                                </h6>

                                @if(!($isTaoNhomOpen ?? false))
                                    <div class="alert alert-warning p-3 rounded-3 mb-0" style="font-size: 0.85rem;">
                                        <i class="fa-solid fa-clock me-2"></i>{{ $groupPhaseState['message'] ?? 'Cổng quản lý và ghép nhóm hiện đang đóng.' }}
                                    </div>
                                @else
                                    @if(session('invite_success'))
                                    <div class="alert alert-success alert-dismissible fade show p-3 mb-3 border-0 rounded-3" role="alert" style="font-size: 0.85rem;">
                                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('invite_success') }}
                                        <button type="button" class="btn-close p-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    @endif

                                    <div id="stepSearchArea">
                                        <div class="d-flex gap-2">
                                            <input type="text" id="inputMSSVSearch" class="form-control py-2 px-3" placeholder="Nhập chính xác MSSV cùng ngành (ví dụ: 2001210060)..." style="font-size: 0.875rem;">
                                            <button class="btn btn-cyan px-4 fw-bold d-inline-flex align-items-center flex-shrink-0" type="button" id="btnDoSearch">
                                                <i class="fa-solid fa-magnifying-glass me-2"></i>Tra Cứu
                                            </button>
                                        </div>
                                        <div class="form-text text-muted mt-2" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-shield-halved me-2" style="color: #0072CE;"></i>Hệ thống tự động kiểm tra điều kiện cùng ngành và điều kiện làm khóa luận trước khi mời.
                                        </div>
                                        <div id="searchErrorMessage" class="alert alert-danger p-3 mt-3 d-none" style="font-size: 0.85rem;"></div>
                                    </div>

                                    <!-- Thẻ xác nhận thông tin sinh viên -->
                                    <div id="stepVerificationCard" class="d-none mt-4">
                                        <div class="card border border-slate-300 bg-light p-4 rounded-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;"><i class="fa-solid fa-id-card me-2" style="color: #0072CE;"></i>Xác Nhận Sinh Viên</h6>
                                                <span id="cardBadgeStatus" class="badge rounded-pill px-3 py-1" style="font-size: 0.75rem;"></span>
                                            </div>
                                            <div class="mb-3">
                                                <div class="fw-bold text-dark" id="cardHoTen" style="font-size: 0.95rem;"></div>
                                                <div class="text-muted mt-1" style="font-size: 0.8rem;">MSSV: <strong id="cardMSSV" class="text-dark"></strong></div>
                                            </div>
                                            <div class="row g-3 mb-3" style="font-size: 0.8rem;">
                                                <div class="col-6"><span class="text-muted">Lớp:</span> <strong id="cardLop" class="text-dark"></strong></div>
                                                <div class="col-6"><span class="text-muted">Ngành:</span> <strong id="cardNganh" class="text-dark"></strong></div>
                                            </div>
                                            <div class="p-3 rounded-3 bg-white border mb-3" id="cardStatusDetailBox" style="font-size: 0.8rem;">
                                                <div class="text-muted mb-1">Trạng thái:</div>
                                                <div class="fw-bold text-dark" id="cardStatusText"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center" id="btnBackSearch" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                                                </button>
                                                <form action="{{ route('sinhvien.nhom.moiThanhVien') }}" method="POST" id="formConfirmInvite">
                                                    @csrf
                                                    <input type="hidden" name="MaNhom" value="{{ $nhomCurrent->MaNhom }}">
                                                    <input type="hidden" name="MaSV" id="hiddenMaSV" value="">
                                                    <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center" id="btnSubmitInvite" style="font-size: 0.8rem;">
                                                        <i class="fa-solid fa-paper-plane me-2"></i>Xác Nhận & Gửi Lời Mời
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
                                <button type="button" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                    <i class="fa-solid fa-plus-circle me-2"></i>Tạo Nhóm Mới
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center" disabled>
                                    <i class="fa-solid fa-lock me-2"></i>Hết Hạn Tạo Nhóm
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
                        <h5 class="fw-bold mb-1" style="color: #003366; font-size: 1rem;"><i class="fa-solid fa-envelope-open-text me-2" style="color: #0072CE;"></i>Quản Trị Lời Mời &amp; Yêu Cầu Ghép Nhóm</h5>
                        <div class="text-muted" style="font-size: 0.85rem;">Theo dõi và phản hồi các lời mời gia nhập nhóm hoặc yêu cầu xin vào nhóm</div>
                    </div>

                    <!-- 1. LỜI MỜI GIA NHẬP NHÓM GỬI TỚI BẠN -->
                    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">
                                <i class="fa-regular fa-envelope-open me-2" style="color: #0072CE;"></i>Lời Mời Tham Gia Nhóm Gửi Tới Bạn
                            </h6>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                {{ isset($loiMois) ? $loiMois->count() : 0 }} lời mời
                            </span>
                        </div>
                        <div class="card-body p-4">
                            @if(isset($loiMois) && $loiMois->count() > 0)
                                <div class="loi-moi-scroll-container">
                                    @foreach($loiMois as $lm)
                                    <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded-3 mb-3 border border-slate-200 shadow-xs">
                                        <div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                                                {{ $lm->nhom->TenNhom ?? 'Nhóm Khóa Luận' }}
                                                @if($lm->nhom && $lm->nhom->hocPhan)
                                                    <span class="badge badge-neutral ms-2 px-3 py-1" style="font-size: 0.725rem;">{{ $lm->nhom->hocPhan->TenHocPhan }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                Từ Trưởng nhóm: <strong>{{ $lm->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (<code>{{ $lm->nhom->truongNhom->taiKhoan->TenDangNhap ?? $lm->nhom->MaTruongNhom }}</code>)
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-2 mt-sm-0">
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-view-group-detail bg-white d-inline-flex align-items-center" data-manhom="{{ $lm->MaNhom }}" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-eye me-2" style="color: #0072CE;"></i>Xem
                                            </button>
                                            <form action="{{ route('sinhvien.nhom.xacNhanLoiMoi', $lm->MaNhom) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-check me-2"></i>Chấp nhận
                                                </button>
                                            </form>
                                            <form action="{{ route('sinhvien.nhom.tuChoiLoiMoi', $lm->MaNhom) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3" style="font-size: 0.8rem;">Từ chối</button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-inbox fs-3 opacity-50 mb-2 d-block me-2"></i>
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
                                <div class="card-body p-3">
                                    @forelse($yeuCauXinVao as $yc)
                                    <div class="d-flex justify-content-between align-items-center p-3 mb-3 border rounded-3 bg-white" style="font-size: 0.825rem;">
                                        <div>
                                            <div class="fw-bold text-dark mb-1">{{ $yc->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">MSSV: <code>{{ $yc->sinhVien->taiKhoan->TenDangNhap ?? $yc->MaSV }}</code></div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <form action="{{ route('sinhvien.nhom.duyetYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 py-2" title="Duyệt">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('sinhvien.nhom.tuChoiYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2" title="Từ chối">
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
                                <div class="card-body p-3">
                                    @forelse($loiMoiDaGui as $lm)
                                    <div class="d-flex justify-content-between align-items-center p-3 mb-3 border rounded-3 bg-white" style="font-size: 0.825rem;">
                                        <div>
                                            <div class="fw-semibold text-dark mb-1">{{ $lm->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">MSSV: <code>{{ $lm->sinhVien->taiKhoan->TenDangNhap ?? $lm->MaSV }}</code></div>
                                        </div>
                                        <form action="{{ route('sinhvien.nhom.huyLoiMoiDaGui', ['id' => $nhomCurrent->MaNhom, 'maSV' => $lm->MaSV]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center" title="Thu hồi">
                                                <i class="fa-solid fa-rotate-left me-2"></i> Thu Hồi
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
                        <div class="card-body p-3">
                            @foreach($yeuCauDaGui as $maNhom => $yc)
                            <div class="d-flex justify-content-between align-items-center p-3 mb-3 border rounded-3 bg-white" style="font-size: 0.85rem;">
                                <div>
                                    <div class="fw-bold text-dark mb-1">{{ $yc->nhom->TenNhom ?? 'Nhóm ' . $maNhom }}</div>
                                    <div class="text-muted" style="font-size: 0.80rem;">Trưởng nhóm: <strong>{{ $yc->nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong></div>
                                </div>
                                <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $maNhom) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 py-2 text-dark fw-bold d-inline-flex align-items-center" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-xmark me-2"></i>Hủy Yêu Cầu
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
                            <h5 class="fw-bold mb-1" style="color: #003366; font-size: 1rem;">
                                <i class="fa-solid fa-users-viewfinder me-2" style="color: #0072CE;"></i>Danh Sách Nhóm Khóa Luận Đang Mở
                            </h5>
                            <div class="text-muted" style="font-size: 0.85rem;">Danh sách các nhóm khóa luận cùng ngành đang mở và còn trống chỗ</div>
                        </div>
                        @if($isTaoNhomOpen ?? true)
                            <button type="button" class="btn btn-cyan btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                <i class="fa-solid fa-plus-circle me-2"></i>+ Tạo Nhóm Mới
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center" disabled>
                                <i class="fa-solid fa-lock me-2"></i>Đã Khóa Tạo Nhóm
                            </button>
                        @endif
                    </div>

                    <!-- SỐ LƯỢNG NHÓM & BỘ LỌC ĐANG ÁP DỤNG -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="badge badge-neutral px-3 py-2" id="badge-group-count" style="font-size: 0.8rem;">
                            Tìm thấy {{ count($nhomsOpen ?? []) }} nhóm
                        </span>
                        @if(request('q') || (isset($trangThaiDeTai) && $trangThaiDeTai !== 'ALL') || (isset($choTrong) && $choTrong !== 'ALL'))
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Đang áp dụng bộ lọc</span>
                                <a href="{{ route('sinhvien.nhom.index', ['hoc_ky' => $maHocKy, 'hoc_phan' => $selectedHocPhan, 'bo_mon' => $selectedBoMon, 'tab' => 'tab-timnhom']) }}" 
                                   class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-rotate-left me-1"></i>Xóa bộ lọc
                                </a>
                            </div>
                        @endif
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
                        <div class="card rounded-4 p-3 shadow-xs border group-item-card {{ $isMyGroup ? 'bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                        <h6 class="fw-bold mb-0 text-dark" style="color: #003366 !important; font-size: 0.95rem;">{{ $no->TenNhom }}</h6>
                                        @if($isMyGroup)
                                            <span class="badge bg-secondary text-white rounded-pill px-3 py-1" style="font-size: 0.725rem;">Nhóm của bạn</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        @if($no->hocPhan)
                                            <span class="badge badge-neutral px-3 py-1" style="font-size: 0.725rem;"><i class="fa-solid fa-graduation-cap me-2 text-secondary"></i>{{ $no->hocPhan->TenHocPhan }}</span>
                                        @endif
                                        @if($no->hocKy)
                                            <span class="badge badge-neutral px-3 py-1" style="font-size: 0.725rem;"><i class="fa-solid fa-calendar me-2 text-secondary"></i>{{ $no->hocKy->TenHocKy }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($hasPdk)
                                        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size: 0.725rem;"><i class="fa-solid fa-book-bookmark me-2"></i>Đã Chọn Đề Tài</span>
                                    @elseif($isFull)
                                        <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size: 0.725rem;"><i class="fa-solid fa-lock me-2"></i>Đã Đủ 3/3</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2" style="font-size: 0.725rem;"><i class="fa-solid fa-user-plus me-2"></i>Còn {{ 3 - $memberCount }} Chỗ</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3" style="font-size: 0.85rem;">
                                <div class="text-dark">
                                    <i class="fa-solid fa-crown text-warning me-2"></i>Trưởng nhóm: <strong>{{ $no->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                    <code class="ms-1">({{ $no->truongNhom->taiKhoan->TenDangNhap ?? $no->MaTruongNhom }})</code>
                                </div>
                                <div class="text-muted mt-1">
                                    Lớp: {{ $no->truongNhom->lop->TenLop ?? 'Chưa rõ' }} &bull; Thành viên: <strong>{{ $memberCount }} / 3</strong>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 btn-view-group-detail bg-white d-inline-flex align-items-center" data-manhom="{{ $no->MaNhom }}" style="font-size: 0.8rem;">
                                    <i class="fa-solid fa-eye me-2" style="color: #0072CE;"></i>Chi tiết
                                </button>

                                @if($isMyGroup)
                                    <span class="badge badge-neutral px-3 py-2" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-check me-2 text-primary"></i>Bạn đang ở trong nhóm này
                                    </span>
                                @elseif($hasRequestedThis)
                                    <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $no->MaNhom) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 py-2 text-dark fw-bold d-inline-flex align-items-center" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-clock me-2"></i>Đang Chờ Duyệt (Hủy)
                                        </button>
                                    </form>
                                @elseif($hasPdk)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-lock me-2"></i>Khóa Đề Tài
                                    </button>
                                @elseif($isFull)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-ban me-2"></i>Đã Đủ Thành Viên
                                    </button>
                                @elseif(!($isTaoNhomOpen ?? true))
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-lock me-2"></i>Hết Hạn
                                    </button>
                                @elseif($alreadyInThisCourseGroup)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center" disabled style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-ban me-2"></i>Đã Có Nhóm Môn Này
                                    </button>
                                @else
                                    <form action="{{ route('sinhvien.nhom.xinGiaNhap', $no->MaNhom) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3 py-2 fw-bold shadow-xs d-inline-flex align-items-center" onclick="return confirm('Bạn có chắc muốn gửi yêu cầu xin gia nhập nhóm {{ $no->TenNhom }}?');" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-hand me-2"></i>Xin Gia Nhập
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted" style="font-size: 0.875rem;">
                            <i class="fa-solid fa-users-slash fs-1 opacity-50 mb-3 d-block"></i>
                            <p class="mb-2 fw-semibold" style="color: #003366; font-size: 1rem;">Không tìm thấy nhóm phù hợp</p>
                            <p class="small text-secondary mb-3">Không có nhóm nào khớp với bộ lọc trạng thái hoặc từ khóa tìm kiếm đã chọn.</p>
                            @if(request('q') || (isset($trangThaiDeTai) && $trangThaiDeTai !== 'ALL') || (isset($choTrong) && $choTrong !== 'ALL'))
                                <a href="{{ route('sinhvien.nhom.index', ['hoc_ky' => $maHocKy, 'hoc_phan' => $selectedHocPhan, 'bo_mon' => $selectedBoMon, 'tab' => 'tab-timnhom']) }}" 
                                   class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-rotate-left me-2"></i>Xóa bộ lọc &amp; Xem tất cả nhóm
                                </a>
                            @endif
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
            <div class="modal-header text-white py-3 px-4" style="background-color: #003366 !important;">
                <div>
                    <h5 class="modal-title fw-bold" id="groupDetailName"><i class="fa-solid fa-users me-2"></i>Nhóm Khóa Luận</h5>
                    <div class="opacity-75" id="groupDetailSub" style="font-size: 0.8rem;">Chi tiết thành viên trong nhóm</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem;"><i class="fa-solid fa-calendar-alt me-2 text-secondary"></i>Học kỳ:</span>
                            <span class="badge badge-neutral text-wrap text-start px-3 py-1" id="groupDetailHocKy">--</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem;"><i class="fa-solid fa-book-open me-2 text-secondary"></i>Môn học / Học phần:</span>
                            <span class="badge badge-neutral text-wrap text-start px-3 py-1" id="groupDetailHocPhan">--</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="text-muted" style="font-size: 0.8rem;">Quy mô:</span>
                        <strong class="text-dark ms-1" id="groupDetailCount">--</strong>
                    </div>
                    <div id="groupDetailStatusBadge"></div>
                </div>

                <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;"><i class="fa-solid fa-user-group text-secondary me-2"></i>DANH SÁCH THÀNH VIÊN</h6>
                <div id="groupDetailMembersList" class="d-flex flex-column gap-2 mb-3"></div>
                <div class="p-3 rounded-3 bg-light text-center text-muted" id="groupDetailSlotMessage" style="font-size: 0.825rem;"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between px-4 py-3 bg-light border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2" data-bs-dismiss="modal">Đóng</button>
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
            <div class="modal-header text-white py-3 px-4" style="background-color: #003366 !important;">
                <h6 class="modal-title fw-bold" id="modalProfileTitle"><i class="fa-solid fa-user-graduate me-2"></i>Hồ Sơ Sinh Viên</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="avatar-circle bg-light text-secondary border fs-2 fw-bold mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-radius: 50%;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" id="profileModalHoTen" style="font-size: 1.1rem;">--</h5>
                    <span class="badge badge-neutral px-3 py-1" id="profileModalMSSV">--</span>
                </div>
                <div class="list-group list-group-flush border-top border-bottom my-3" style="font-size: 0.85rem;">
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-chalkboard me-2"></i>Lớp học:</span>
                        <strong id="profileModalLop" class="text-dark">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-graduation-cap me-2"></i>Chuyên ngành:</span>
                        <strong id="profileModalNganh" class="text-dark">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-2"></i>Email:</span>
                        <span id="profileModalEmail" class="text-dark">--</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-users me-2"></i>Tình trạng nhóm:</span>
                        <span id="profileModalGroupStatus">--</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-2" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL CHI TIẾT ĐỀ TÀI NHÓM (CHUẨN 3 NHÓM THÔNG TIN A, B, C) -->
<!-- ======================================================== -->
@if(isset($nhomCurrent) && ($nhomCurrent->deTai || $nhomCurrent->dangKyDeTai?->deTai))
@php
    $dtDetail = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
    $hkCur = $nhomCurrent->hocKy ?? $dtDetail->hocKy;
    $hpCur = $nhomCurrent->hocPhan ?? $dtDetail->hocPhanRef;

    // 1. Ngày bắt đầu và Ngày kết thúc học phần: Lấy đúng từ học phần tương ứng
    $ngayBatDauHP = $hpCur?->NgayBatDau;
    $ngayKetThucHP = $hpCur?->NgayKetThuc;

    // 2. Mốc thời gian thực hiện đề tài theo Kế hoạch khóa luận
    $mocThucHien = null;
    if ($hkCur && $hkCur->keHoachKhoaLuans) {
        foreach ($hkCur->keHoachKhoaLuans as $khItem) {
            if ($khItem->mocThoiGians) {
                $foundMoc = $khItem->mocThoiGians->first(fn($m) => $m->LoaiGiaiDoan === 'THUC_HIEN' || str_contains(mb_strtolower($m->TenMoc ?? ''), 'thực hiện'));
                if ($foundMoc) {
                    $mocThucHien = $foundMoc;
                    break;
                }
            }
        }
    }
    $ngayBatDauKH = $mocThucHien?->NgayBatDau;
    $ngayKetThucKH = $mocThucHien?->NgayKetThuc;

    // 3. Ngày tạo nhóm & Ngày đăng ký đề tài
    $ngayTaoNhom = $nhomCurrent->NgayTao ?? $nhomCurrent->created_at;
    $ngayDangKyDeTai = $nhomCurrent->dangKyDeTai?->NgayDangKy ?? $nhomCurrent->dangKyDeTai?->created_at;

    $tenBoMon = $dtDetail->giangVien?->boMon?->TenBoMon ?? $hpCur?->boMon?->TenBoMon ?? 'Bộ môn chuyên ngành';
@endphp
<div class="modal fade" id="modalTopicDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="font-size: 0.875rem;">
            <!-- Modal Header -->
            <div class="modal-header text-white py-3 px-4" style="background-color: #003366 !important;">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1">
                        <i class="fa-solid fa-book-bookmark me-2 text-warning"></i>Chi Tiết Đề Tài Khóa Luận Nhóm
                    </h5>
                    <div class="opacity-75" style="font-size: 0.825rem;">Thông tin chính thức về đề tài, học phần, thời gian thực hiện &amp; nhóm sinh viên</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body (Tăng khoảng cách nền tổng p-4 lên p-4 và khoảng cách lưới g-3 lên g-4) -->
            <div class="modal-body p-4 bg-light">
                <div class="row g-4">
                    
                    <!-- NHÓM A: THÔNG TIN ĐỀ TÀI -->
                    <div class="col-12 col-lg-7">
                        <div class="card border border-slate-200 rounded-3 shadow-none h-100 bg-white">
                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.9rem;">
                                    <i class="fa-solid fa-circle-info text-primary me-2"></i>A. THÔNG TIN ĐỀ TÀI
                                </h6>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-check me-2"></i>{{ $dtDetail->TrangThai }}
                                </span>
                            </div>
                            <!-- Tăng padding bên trong thẻ card lên p-4 -->
                            <div class="card-body p-4"> 
                                <div class="mb-4 pb-3 border-bottom">
                                    <div class="text-muted small mb-2">Mã đề tài: <span class="badge-code ms-1">{{ $dtDetail->MaDeTai }}</span></div>
                                    <h5 class="fw-bold mb-3" style="color: #003366; font-size: 1.05rem; line-height: 1.5;">
                                        {{ $dtDetail->TenDeTai }}
                                    </h5>
                                    @if($dtDetail->TenDeTaiTiengAnh)
                                        <div class="text-secondary small fst-italic">{{ $dtDetail->TenDeTaiTiengAnh }}</div>
                                    @endif
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-sm-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small mb-2"><i class="fa-solid fa-user-tie text-secondary me-2"></i>Giảng viên hướng dẫn:</div>
                                            <strong class="text-dark">{{ $dtDetail->giangVien->HoTen ?? 'Chưa phân công' }}</strong>
                                            @if($dtDetail->giangVien?->Email)
                                                <div class="text-muted small mt-2" style="font-size: 0.775rem;"><code>{{ $dtDetail->giangVien->Email }}</code></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small mb-2"><i class="fa-solid fa-building-columns text-secondary me-2"></i>Bộ môn quản lý:</div>
                                            <strong class="text-dark">{{ $tenBoMon }}</strong>
                                            @if($dtDetail->LinhVuc)
                                                <div class="text-muted small mt-2" style="font-size: 0.775rem;">Lĩnh vực: {{ $dtDetail->LinhVuc }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="fw-bold text-muted small text-uppercase mb-2 d-block" style="font-size: 0.75rem;">Mô tả đề tài:</label>
                                    <div class="p-4 bg-light rounded-3 border text-dark small" style="line-height: 1.65; max-height: 150px; overflow-y: auto;">
                                        @if($dtDetail->MoTa)
                                            {!! \App\Helpers\HtmlHelper::clean($dtDetail->MoTa) !!}
                                        @else
                                            <span class="text-muted fst-italic">Chưa có thông tin mô tả chi tiết đề tài.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NHÓM B: THÔNG TIN HỌC PHẦN & THỜI GIAN THỰC HIỆN -->
                    <div class="col-12 col-lg-5">
                        <div class="card border border-slate-200 rounded-3 shadow-none h-100 bg-white">
                            <div class="card-header bg-white py-3 px-4 border-bottom">
                                <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.9rem;">
                                    <i class="fa-regular fa-calendar-check text-primary me-2"></i>B. HỌC PHẦN &amp; THỜI GIAN THỰC HIỆN
                                </h6>
                            </div>
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Nới rộng khoảng cách list-group và padding các mục bên trong -->
                                    <ul class="list-group list-group-flush small mb-3 px-1">
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-muted"><i class="fa-solid fa-graduation-cap text-secondary me-2"></i>Tên học phần:</span>
                                            <strong class="text-dark text-end" style="max-width: 220px;">{{ $hpCur->TenHocPhan ?? $dtDetail->HocPhan ?? 'Khóa luận tốt nghiệp' }}</strong>
                                        </li>
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-muted"><i class="fa-solid fa-barcode text-secondary me-2"></i>Mã học phần:</span>
                                            <span class="badge-code">{{ $hpCur->MaHocPhan ?? $dtDetail->MaHocPhan ?? 'KLTN_01' }}</span>
                                        </li>
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-muted"><i class="fa-regular fa-calendar text-secondary me-2"></i>Học kỳ thực hiện:</span>
                                            <strong class="text-dark">{{ $hkCur->TenHocKy ?? 'Học kỳ hiện tại' }} {{ $hkCur?->NamHoc ? '(' . $hkCur->NamHoc . ')' : '' }}</strong>
                                        </li>
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-muted"><i class="fa-solid fa-calendar-plus text-primary me-2"></i>Ngày bắt đầu học phần:</span>
                                            @if($ngayBatDauHP)
                                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($ngayBatDauHP)->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Chưa cấu hình</span>
                                            @endif
                                        </li>
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-muted"><i class="fa-solid fa-calendar-xmark text-danger me-2"></i>Ngày kết thúc học phần:</span>
                                            @if($ngayKetThucHP)
                                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($ngayKetThucHP)->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Chưa cấu hình</span>
                                            @endif
                                        </li>
                                        @if($ngayDangKyDeTai)
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                            <span class="text-muted"><i class="fa-regular fa-calendar-check text-secondary me-2"></i>Ngày đăng ký đề tài:</span>
                                            <span class="text-secondary">{{ \Carbon\Carbon::parse($ngayDangKyDeTai)->format('d/m/Y') }}</span>
                                        </li>
                                        @endif
                                        @if($ngayTaoNhom)
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="text-muted"><i class="fa-regular fa-clock text-secondary me-2"></i>Ngày tạo nhóm:</span>
                                            <span class="text-secondary">{{ \Carbon\Carbon::parse($ngayTaoNhom)->format('d/m/Y') }}</span>
                                        </li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="small fw-bold text-dark mb-2">
                                        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Thời gian thực hiện đề tài theo kế hoạch:
                                    </div>
                                    <div class="small text-secondary" style="font-size: 0.825rem; line-height: 1.6;">
                                        @if($ngayBatDauKH && $ngayKetThucKH)
                                            Từ <strong>{{ \Carbon\Carbon::parse($ngayBatDauKH)->format('d/m/Y') }}</strong> đến <strong>{{ \Carbon\Carbon::parse($ngayKetThucKH)->format('d/m/Y') }}</strong> (theo Kế hoạch khóa luận).
                                        @elseif($hkCur?->NgayDiHoc && $hkCur?->NgayKetThuc)
                                            Từ <strong>{{ \Carbon\Carbon::parse($hkCur->NgayDiHoc)->format('d/m/Y') }}</strong> đến <strong>{{ \Carbon\Carbon::parse($hkCur->NgayKetThuc)->format('d/m/Y') }}</strong> (theo khung học kỳ).
                                        @else
                                            Theo mốc thời gian công bố trong Kế hoạch khóa luận của học kỳ.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NHÓM C: THÔNG TIN THÀNH VIÊN NHÓM -->
                    <div class="col-12">
                        <div class="card border border-slate-200 rounded-3 shadow-none bg-white">
                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-0" style="color: #003366; font-size: 0.9rem;">
                                    <i class="fa-solid fa-users text-primary me-2"></i>C. DANH SÁCH THÀNH VIÊN NHÓM THỰC HIỆN ({{ $nhomCurrent->thanhViens->count() }}/3)
                                </h6>
                                <span class="badge bg-light text-secondary border px-3 py-1">Nhóm: {{ $nhomCurrent->TenNhom ?? $nhomCurrent->MaNhom }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light text-muted">
                                            <tr>
                                                <th class="text-center py-3 ps-4" style="width: 50px;">STT</th>
                                                <th class="py-3" style="width: 140px;">Mã Sinh Viên</th>
                                                <th class="py-3">Họ Và Tên</th>
                                                <th class="py-3" style="width: 160px;">Lớp Hành Chính</th>
                                                <th class="py-3" style="width: 180px;">Ngành Đào Tạo</th>
                                                <th class="text-center py-3" style="width: 150px;">Vai Trò</th>
                                                <th class="py-3 pe-4">Email / Liên Hệ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($nhomCurrent->thanhViens as $stt => $tv)
                                                @php
                                                    $sv = $tv->sinhVien;
                                                    $isLeader = ($tv->MaSV === $nhomCurrent->MaTruongNhom || $tv->VaiTro === 'truong_nhom');
                                                @endphp
                                                <tr>
                                                    <td class="text-center fw-semibold text-muted py-3 ps-4">{{ $stt + 1 }}</td>
                                                    <td class="py-3"><span class="badge-code">{{ $tv->MaSV }}</span></td>
                                                    <td class="py-3"><strong class="text-dark">{{ $sv->HoTen ?? 'N/A' }}</strong></td>
                                                    <td class="py-3"><span class="text-secondary">{{ $sv->lop->TenLop ?? $sv->MaLop ?? 'N/A' }}</span></td>
                                                    <td class="py-3"><span class="text-secondary">{{ $sv->lop->nganh->TenNganh ?? $sv->nganh->TenNganh ?? 'Công nghệ thông tin' }}</span></td>
                                                    <td class="text-center py-3">
                                                        @if($isLeader)
                                                            <span class="badge px-3 py-2 text-white fw-bold" style="background-color: #003366 !important; border-radius: 999px; font-size: 0.725rem;">
                                                                <i class="fa-solid fa-crown text-warning me-2"></i>Nhóm trưởng
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold" style="border-radius: 999px; font-size: 0.725rem;">
                                                                <i class="fa-solid fa-user me-2"></i>Thành viên
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 pe-4">
                                                        @if($sv?->Email)
                                                            <code class="text-dark">{{ $sv->Email }}</code>
                                                        @elseif($sv?->taiKhoan?->Email)
                                                            <code class="text-dark">{{ $sv->taiKhoan->Email }}</code>
                                                        @else
                                                            <span class="text-muted fst-italic">Chưa cập nhật</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">Chưa có thành viên nào trong nhóm.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer px-4 py-3 bg-white border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
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
                <div class="modal-header border-bottom px-4 py-3 text-white" style="background-color: #003366 !important;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fa-solid fa-users-gear me-2"></i>Tạo Nhóm Khóa Luận / Đồ Án Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- BƯỚC 1: CHỌN HỌC KỲ -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2" style="font-size: 0.875rem;">
                            <span><span class="badge badge-neutral px-3 py-1 me-2">Bước 1</span> Chọn Học Kỳ Áp Dụng <span class="text-danger">*</span></span>
                        </label>
                        <select name="MaHocKy" id="create_group_hocky" class="form-select border shadow-xs py-2" required style="font-size: 0.875rem;">
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
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2" style="font-size: 0.875rem;">
                            <span><span class="badge badge-neutral px-3 py-1 me-2">Bước 2</span> Chọn Học Phần Khóa Luận <span class="text-danger">*</span></span>
                            <span class="text-muted fw-normal" id="step2_badge_status" style="font-size: 0.8rem;"><i class="fa-solid fa-lock me-2"></i>Chọn Bước 1 trước</span>
                        </label>
                        <select name="MaHocPhan" id="create_group_hocphan" class="form-select border shadow-xs py-2" required disabled style="font-size: 0.875rem;">
                            <option value="">-- Vui lòng chọn học kỳ ở bước 1 trước --</option>
                        </select>
                        <div class="form-text text-muted mt-2" id="create_group_hocphan_help" style="font-size: 0.8rem;">
                            Học phần khóa luận chuyên ngành mở trong kỳ.
                        </div>
                    </div>

                    <!-- THÔNG TIN NHÓM TỰ SINH -->
                    <div class="card border border-slate-200 bg-white rounded-3 p-3 shadow-xs" style="font-size: 0.85rem;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="text-muted d-block mb-1" style="font-size: 0.8rem;">Tên nhóm tự sinh:</span>
                                <div class="fw-bold text-dark fs-6" style="color: #003366 !important;">
                                    <i class="fa-solid fa-users-rectangle me-2" style="color: #0072CE;"></i>Nhóm {{ $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV }}
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

                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">Hủy Bỏ</button>
                    @if($isTaoNhomOpen ?? true)
                        <button type="submit" id="btn_submit_create_group" class="btn btn-cyan rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center">
                            <i class="fa-solid fa-check-circle me-2"></i>Xác Nhận Tạo Nhóm
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 fw-bold" disabled>Tạm Khóa</button>
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
    if (bm && bm !== 'ALL') url.searchParams.set('bo_mon', bm);
    if (hp && hp !== 'ALL') url.searchParams.set('hoc_phan', hp);

    // Giữ lại các tham số lọc trạng thái & tìm kiếm nếu có
    const currentParams = new URLSearchParams(window.location.search);
    if (currentParams.get('TrangThaiDeTai')) url.searchParams.set('TrangThaiDeTai', currentParams.get('TrangThaiDeTai'));
    if (currentParams.get('ChoTrong')) url.searchParams.set('ChoTrong', currentParams.get('ChoTrong'));
    if (currentParams.get('q')) url.searchParams.set('q', currentParams.get('q'));
    if (currentParams.get('tab')) url.searchParams.set('tab', currentParams.get('tab'));

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
            const tabBadge = document.querySelector('#tab-timnhom-tab .badge');
            if (tabBadge) tabBadge.textContent = visibleCount;
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
                                <span class="badge ${tv.IsLeader ? 'bg-warning text-dark' : 'badge-neutral'} px-3 py-1" style="font-size: 0.725rem;">${tv.VaiTro}</span>
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
                        document.getElementById('profileModalGroupStatus').innerHTML = `<span class="badge ${data.badge_class} px-3 py-1">${data.status_text}</span>`;
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
