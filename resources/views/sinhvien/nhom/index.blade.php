@extends('layouts.sinhvien')

@section('page_title', 'Quản Trị Nhóm Khóa Luận')

@section('content')
<style>
    /* Bắt buộc font chữ tối thiểu 13px - 14px đồng bộ toàn bộ giao diện Nhóm */
    .nhom-page-root {
        font-size: 14px;
        color: #1e293b;
    }
    .nhom-page-root .small, 
    .nhom-page-root small, 
    .nhom-page-root .badge, 
    .nhom-page-root .form-text,
    .nhom-page-root .text-muted,
    .nhom-page-root code,
    .nhom-page-root .btn-sm,
    .nhom-page-root .form-select-sm,
    .nhom-page-root .form-control-sm {
        font-size: 13px !important;
    }

    /* Thẻ bao quát và hiệu ứng bóng đổ tự nhiên, chân thực */
    .nhom-panel-card {
        border-radius: 0.875rem;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.03);
    }
    .group-item-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
    }
    .group-item-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 6px 14px -2px rgba(15, 23, 42, 0.08), 0 3px 6px -3px rgba(15, 23, 42, 0.04) !important;
    }

    /* Bảng chuẩn phong cách nghiêm túc, trang nhã */
    .table-custom th {
        background-color: #f8fafc !important;
        font-weight: 700;
        font-size: 13px !important;
        padding: 14px 18px !important;
        color: #334155 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        letter-spacing: 0.01em;
    }
    .table-custom td {
        font-size: 14px !important;
        padding: 16px 18px !important;
        vertical-align: middle;
        line-height: 1.5;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Bộ lọc toàn cục: Hộp cân đối, chuẩn chiều cao, không bao giờ bị vỡ dòng hay lệch hàng */
    .filter-item-box {
        display: flex;
        align-items: center;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        height: 38px;
        padding: 0 10px;
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .filter-item-box:hover, .filter-item-box:focus-within {
        border-color: #64748b;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }
    .filter-item-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        margin-right: 6px;
        display: flex;
        align-items: center;
    }
    .filter-item-select {
        border: none !important;
        background-color: transparent !important;
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
        padding: 0 4px;
        box-shadow: none !important;
        cursor: pointer;
        width: 100%;
    }
    .filter-item-select:focus {
        outline: none;
    }
    .filter-btn-invite {
        height: 38px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Thanh điều hướng Tab - Tông Deep Midnight Teal từ bảng màu người dùng (#004E64 / #003F5F) */
    .workspace-nav-pills {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        gap: 6px;
        padding: 5px;
    }
    .workspace-nav-pills .nav-link {
        color: #475569;
        background-color: transparent;
        border: 1px solid transparent;
        transition: all 0.18s ease;
        font-size: 13.5px;
        font-weight: 600;
        padding: 7px 16px;
        border-radius: 9999px;
    }
    .workspace-nav-pills .nav-link:hover {
        color: #003F5F;
        background-color: #e2e8f0;
        border-color: transparent;
    }
    .workspace-nav-pills .nav-link.active {
        color: #ffffff !important;
        background-color: #004E64 !important; /* Xanh ngọc bóng đêm sâu thẳm theo bảng màu */
        box-shadow: 0 2px 6px rgba(0, 78, 100, 0.22);
        border-color: #004E64 !important;
    }
    .workspace-nav-pills .nav-link.active .nav-icon {
        color: #9FFFCB !important; /* Điểm nhấn xanh ngọc pastel */
    }

    /* Tiến độ Timeline mốc chuẩn chỉnh, hài hòa */
    .milestone-step-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.65rem;
        padding: 10px 8px;
        position: relative;
        transition: all 0.16s ease;
    }
    .milestone-step-card:hover {
        border-color: #00838C;
        background-color: #f8fafc;
    }
    .milestone-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        margin-bottom: 6px;
    }

    /* Khung cuộn lời mời */
    .loi-moi-scroll-container {
        max-height: 220px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 6px;
        scrollbar-width: thin;
        scrollbar-color: #00838C #f1f5f9;
    }
    .loi-moi-scroll-container::-webkit-scrollbar {
        width: 5px;
    }
    .loi-moi-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .loi-moi-scroll-container::-webkit-scrollbar-thumb {
        background: #00838C;
        border-radius: 4px;
    }

    /* Khung cuộn danh sách nhóm mở */
    .nhom-open-scroll-container {
        max-height: 420px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 6px;
        scrollbar-width: thin;
        scrollbar-color: #00838C #f1f5f9;
    }
    .nhom-open-scroll-container::-webkit-scrollbar {
        width: 5px;
    }
    .nhom-open-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .nhom-open-scroll-container::-webkit-scrollbar-thumb {
        background: #00838C;
        border-radius: 4px;
    }
    .nhom-open-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #00617A;
    }

    /* Tông màu thẻ trung tính & nghiêm túc (Không sáng chói / Không AI) */
    .badge-neutral {
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .text-slate-800 {
        color: #1e293b !important;
    }
    .text-slate-700 {
        color: #334155 !important;
    }
    .text-slate-600 {
        color: #475569 !important;
    }
</style>

<div class="nhom-page-root">

    <!-- ========================================================================================== -->
    <!-- [1] BẢNG ĐIỀU HƯỚNG BỘ LỌC TẬP TRUNG (GLOBAL HEADER FILTER) -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs rounded-3 mb-3 p-2 bg-white">
        <div class="row g-2 align-items-center">
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
                        class="btn btn-sm w-100 filter-btn-invite border {{ $loiMoiCount > 0 ? 'btn-warning text-dark border-warning' : 'btn-light border-slate-200 text-slate-700' }}"
                        onclick="switchWorkspaceTab('tab-loimoi')">
                    <i class="fa-regular fa-bell {{ $loiMoiCount > 0 ? 'fa-shake text-danger' : 'text-secondary' }} me-1"></i>
                    <span>Lời mời nhóm</span>
                    <span class="badge {{ $loiMoiCount > 0 ? 'bg-danger text-white' : 'badge-neutral' }} rounded-pill ms-1" style="font-size: 11px;">
                        {{ $loiMoiCount }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================================== -->
    <!-- [2] THANH TRẠNG THÁI & CHUYỂN ĐỔI NHÓM NHANH (WORKSPACE SWITCHER) -->
    <!--     [● Nhóm Khóa Luận Web (Hiện tại)]    [Nhóm Đồ Án AI]    [+ Tạo nhóm mới] -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 p-3 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold text-slate-800 d-flex align-items-center">
                    <i class="fa-solid fa-layer-group text-secondary me-2 fs-5"></i>Không Gian Nhóm Của Bạn:
                </span>
                
                @if(isset($sinhVienAllGroups) && $sinhVienAllGroups->count() > 0)
                    @foreach($sinhVienAllGroups as $g)
                        @if($g->nhom)
                            @php
                                $isActiveGroup = ($nhomCurrent && $nhomCurrent->MaNhom === $g->nhom->MaNhom);
                            @endphp
                            <!-- Chỉ dùng màu xanh biển cho nút chọn nhóm hiện tại -->
                            <a href="{{ route('sinhvien.nhom.index', ['nhom_id' => $g->nhom->MaNhom, 'hoc_ky' => $g->nhom->MaHocKy, 'hoc_phan' => $g->nhom->MaHocPhan]) }}"
                               class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 {{ $isActiveGroup ? 'btn-primary shadow-sm' : 'btn-outline-secondary bg-light text-dark' }}">
                                <span class="d-inline-block rounded-circle {{ $isActiveGroup ? 'bg-success' : 'bg-secondary' }}" style="width: 8px; height: 8px;"></span>
                                <span>{{ $g->nhom->TenNhom }} ({{ $g->nhom->hocPhan->TenHocPhan ?? $g->nhom->MaHocPhan }})</span>
                                @if($isActiveGroup)
                                    <span class="badge bg-white text-dark rounded-pill px-2 py-0 ms-1" style="font-size: 11px;">Hiện tại</span>
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

            <!-- NÚT TẠO NHÓM MỚI LUÔN THƯỜNG TRỰC TRÊN WORKSPACE SWITCHER -->
            <div>
                @if($isTaoNhomOpen ?? true)
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                        <i class="fa-solid fa-plus-circle me-1"></i>+ Tạo Nhóm Mới
                    </button>
                @else
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm" disabled title="Tạm thời không trong thời gian tạo nhóm">
                        <i class="fa-solid fa-lock me-1"></i>Hết Hạn Tạo Nhóm
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(!($isTaoNhomOpen ?? true))
        <div class="alert alert-warning border border-warning-subtle shadow-xs rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning bg-opacity-25 p-3 text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                <i class="fa-solid fa-clock-rotate-left fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-slate-800 fs-6 mb-1">
                    <i class="fa-solid fa-lock text-warning me-1"></i>Tạm thời không trong thời gian tạo nhóm
                </div>
                <div class="text-muted" style="font-size: 13.5px;">
                    {{ $groupPhaseState['message'] ?? 'Cổng tạo và ghép nhóm thành viên hiện đang đóng theo Kế hoạch khóa luận. Mọi thao tác tạo nhóm mới tạm thời bị khóa.' }}
                </div>
            </div>
            <div>
                <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-lock me-1"></i>Đã Khóa Tạo Nhóm
                </span>
            </div>
        </div>
    @endif

    <!-- ========================================================================================== -->
    <!-- [3] KHU VỰC NỘI DUNG CHÍNH (MAIN WORKSPACE - TABBED SYSTEM) -->
    <!-- ┌──────────────────────────────────────────────────────────────────────────────────────────┐ -->
    <!-- │ [ 📌 Tổng Quan & Đề Tài ]    [ 👥 Thành Viên (3/3) ]    [ 📩 Lời Mời & Yêu Cầu ]    [ 🔍 Tìm Nhóm Mở ] │ -->
    <!-- └──────────────────────────────────────────────────────────────────────────────────────────┘ -->
    <!-- ========================================================================================== -->
    <div class="card border border-slate-200 shadow-xs nhom-panel-card mb-4 overflow-hidden">
        <!-- HEADER TABS CHÍNH -->
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 px-1 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-slate-800 mb-0">
                        <i class="fa-solid fa-users-gear text-secondary me-2"></i>Quản Trị Nhóm Của Bạn
                    </h5>
                    <div class="text-muted" style="font-size: 13px;">Không gian điều phối thông tin nhóm, đề tài và thành viên khóa luận</div>
                </div>
                @if($nhomCurrent)
                    <span class="badge {{ $isNhomLocked ? 'bg-secondary' : ($nhomCurrent->thanhViens->count() === 3 ? 'bg-success' : 'bg-warning text-dark') }} rounded-pill px-3 py-1">
                        <i class="fa-solid {{ $isNhomLocked ? 'fa-lock' : 'fa-users' }} me-1"></i>
                        {{ $isNhomLocked ? 'Đã Khóa Đề Tài' : $nhomCurrent->thanhViens->count() . '/3 Thành Viên' }}
                    </span>
                @endif
            </div>

            <ul class="nav nav-pills workspace-nav-pills rounded-pill p-1" id="workspaceTabs" role="tablist">
                <!-- TAB 1: TỔNG QUAN & ĐỀ TÀI -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-bold" id="tab-tongquan-tab" data-bs-toggle="pill" data-bs-target="#tab-tongquan" type="button" role="tab" aria-controls="tab-tongquan" aria-selected="true">
                        <i class="fa-solid fa-layer-group nav-icon me-1"></i> Tổng Quan & Đề Tài
                    </button>
                </li>
                <!-- TAB 2: THÀNH VIÊN -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="tab-thanhvien-tab" data-bs-toggle="pill" data-bs-target="#tab-thanhvien" type="button" role="tab" aria-controls="tab-thanhvien" aria-selected="false">
                        <i class="fa-solid fa-users nav-icon me-1"></i> Thành Viên 
                        <span class="badge {{ $nhomCurrent && $nhomCurrent->thanhViens->count() === 3 ? 'bg-success text-white' : 'bg-secondary text-white' }} rounded-pill ms-1">
                            {{ $nhomCurrent ? $nhomCurrent->thanhViens->count() . '/3' : '0/3' }}
                        </span>
                    </button>
                </li>
                <!-- TAB 3: LỜI MỜI & YÊU CẦU -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="tab-loimoi-tab" data-bs-toggle="pill" data-bs-target="#tab-loimoi" type="button" role="tab" aria-controls="tab-loimoi" aria-selected="false">
                        <i class="fa-regular fa-envelope nav-icon me-1"></i> Lời Mời & Yêu Cầu
                        @php
                            $totalInvitesAndReqs = (isset($loiMois) ? $loiMois->count() : 0) + (isset($yeuCauXinVao) ? $yeuCauXinVao->count() : 0);
                        @endphp
                        @if($totalInvitesAndReqs > 0)
                            <span class="badge bg-danger text-white rounded-pill ms-1">{{ $totalInvitesAndReqs }}</span>
                        @endif
                    </button>
                </li>
                <!-- TAB 4: TÌM NHÓM MỞ -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold" id="tab-timnhom-tab" data-bs-toggle="pill" data-bs-target="#tab-timnhom" type="button" role="tab" aria-controls="tab-timnhom" aria-selected="false">
                        <i class="fa-solid fa-magnifying-glass nav-icon me-1"></i> Tìm Nhóm Mở
                        <span class="badge bg-secondary rounded-pill ms-1">{{ count($nhomsOpen ?? []) }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="workspaceTabsContent">

                <!-- ################################################################################## -->
                <!-- TAB 1: TỔNG QUAN & ĐỀ TÀI -->
                <!-- ################################################################################## -->
                <div class="tab-pane fade show active" id="tab-tongquan" role="tabpanel" aria-labelledby="tab-tongquan-tab">
                    @if($nhomCurrent)
                        @php
                            $currentDeTai = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
                            $memberCountCurrent = $nhomCurrent->thanhViens->count();
                            $isFullGroup = ($memberCountCurrent >= 3);
                            $nhomNganh = $nhomCurrent->truongNhom?->nganh?->TenNganh ?? $nhomCurrent->truongNhom?->lop?->nganh?->TenNganh;
                        @endphp

                        <!-- 📊 TIẾN ĐỘ & BẢNG SỰ KIỆN QUA CÁC GIAI ĐOẠN (TIMELINE / MILESTONES ĐẶT ĐẦU TAB GỌN GÀNG) -->
                        <div class="card border border-slate-200 shadow-xs rounded-3 overflow-hidden mb-3">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0" style="color: #003F5F; font-size: 13.5px;">
                                    <i class="fa-solid fa-chart-line me-2" style="color: #00838C;"></i>📊 TIẾN ĐỘ &amp; BẢNG SỰ KIỆN QUA CÁC GIAI ĐOẠN (TIMELINE / MILESTONES)
                                </h6>
                                <span class="badge badge-neutral" style="font-size: 11.5px;">Quy trình đào tạo chuẩn Khóa luận tốt nghiệp</span>
                            </div>
                            <div class="card-body p-2.5 px-3">
                                <div class="row g-2 align-items-center text-center">
                                    <!-- Giai đoạn 1: Mở nhóm -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle bg-success text-white">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                            <div class="fw-bold mb-0.5" style="color: #003F5F; font-size: 12.5px;">1. Mở Nhóm</div>
                                            <span class="badge bg-success rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                <i class="fa-solid fa-check me-1"></i>Hoàn thành
                                            </span>
                                            <div class="text-muted mt-1" style="font-size: 11.5px;">Đã khởi tạo nhóm</div>
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
                                            <div class="fw-bold mb-0.5" style="color: #003F5F; font-size: 12.5px;">2. Đủ 3 SV</div>
                                            @if($isFullGroup)
                                                <span class="badge bg-success rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-check me-1"></i>Đủ 3/3 SV
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-hourglass-half me-1"></i>{{ $memberCountCurrent }}/3 SV
                                                </span>
                                            @endif
                                            <div class="text-muted mt-1" style="font-size: 11.5px;">{{ $isFullGroup ? 'Sẵn sàng đăng ký' : 'Cần thêm ' . (3 - $memberCountCurrent) . ' bạn' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 3: Đăng ký đề tài -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ $currentDeTai ? 'bg-success text-white' : ($isFullGroup ? 'bg-secondary text-white' : 'bg-light text-muted border') }}">
                                                <i class="fa-solid fa-book-bookmark"></i>
                                            </div>
                                            <div class="fw-bold mb-0.5" style="color: #003F5F; font-size: 12.5px;">3. Đăng Ký Đề Tài</div>
                                            @if($currentDeTai)
                                                <span class="badge bg-success rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-check me-1"></i>Đã duyệt
                                                </span>
                                            @elseif($isFullGroup)
                                                <span class="badge bg-secondary rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-arrow-right me-1"></i>Đang đăng ký
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-lock me-1"></i>Chưa mở
                                                </span>
                                            @endif
                                            <div class="text-muted mt-1" style="font-size: 11.5px;">{{ $currentDeTai ? 'Đã gán đề tài' : 'Chờ đủ thành viên' }}</div>
                                        </div>
                                    </div>

                                    <div class="col-auto d-none d-md-block text-muted fs-6">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>

                                    <!-- Giai đoạn 4: Nộp đề cương / Báo cáo -->
                                    <div class="col-12 col-md">
                                        <div class="milestone-step-card">
                                            <div class="milestone-icon-circle {{ ($currentDeTai && $currentDeTai->FileDeCuong) ? 'bg-success text-white' : ($currentDeTai ? 'bg-warning text-dark' : 'bg-light text-muted border') }}">
                                                <i class="fa-solid fa-file-signature"></i>
                                            </div>
                                            <div class="fw-bold mb-0.5" style="color: #003F5F; font-size: 12.5px;">4. Nộp Báo Cáo</div>
                                            @if($currentDeTai && $currentDeTai->FileDeCuong)
                                                <span class="badge bg-success rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-check me-1"></i>Có đề cương
                                                </span>
                                            @elseif($currentDeTai)
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-clock me-1"></i>Chờ nộp
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                    <i class="fa-solid fa-lock me-1"></i>Chưa mở
                                                </span>
                                            @endif
                                            <div class="text-muted mt-1" style="font-size: 11.5px;">Đề cương & Báo cáo</div>
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
                                            <div class="fw-bold mb-0.5" style="color: #003F5F; font-size: 12.5px;">5. Bảo Vệ</div>
                                            <span class="badge bg-secondary rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                <i class="fa-solid fa-lock me-1"></i>Giai đoạn cuối
                                            </span>
                                            <div class="text-muted mt-1" style="font-size: 11.5px;">Hội đồng bảo vệ</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BỐ CỤC 2 HỘP: THÔNG TIN NHÓM & THÔNG TIN ĐỀ TÀI -->
                        <div class="row g-3">
                            <!-- 🏢 THÔNG TIN NHÓM & GVHD -->
                            <div class="col-12 col-lg-6">
                                <div class="card border border-slate-200 shadow-xs rounded-3 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0" style="color: #003F5F; font-size: 13.5px;">
                                            <i class="fa-solid fa-id-card me-2" style="color: #00838C;"></i>🏢 THÔNG TIN NHÓM &amp; GVHD
                                        </h6>
                                        <span class="badge {{ $isNhomLocked ? 'bg-secondary' : ($isFullGroup ? 'bg-success' : 'bg-warning text-dark') }} rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid {{ $isNhomLocked ? 'fa-lock' : 'fa-users' }} me-1"></i>
                                            {{ $isNhomLocked ? 'Đã Khóa Đề Tài' : $memberCountCurrent . '/3 Thành Viên' }}
                                        </span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5">
                                            <div>
                                                <h6 class="fw-bold mb-0.5" style="color: #003F5F; font-size: 14px;">
                                                    <i class="fa-solid fa-users me-1" style="color: #00838C;"></i>{{ $nhomCurrent->TenNhom }}
                                                </h6>
                                                <div class="text-muted" style="font-size: 12.5px;">Mã định danh: <code>{{ $nhomCurrent->MaNhom }}</code></div>
                                            </div>
                                            <div class="d-flex gap-1 flex-wrap">
                                                @if($nhomCurrent->hocKy)
                                                    <span class="badge badge-neutral" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-calendar me-1" style="color: #00838C;"></i>{{ $nhomCurrent->hocKy->TenHocKy }}
                                                    </span>
                                                @endif
                                                @if($nhomCurrent->hocPhan)
                                                    <span class="badge badge-neutral" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-book me-1" style="color: #00838C;"></i>{{ $nhomCurrent->hocPhan->TenHocPhan }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="p-2.5 bg-light rounded-3 border border-slate-200 mb-2.5">
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <span class="text-muted d-block mb-0.5" style="font-size: 11.5px;"><i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm:</span>
                                                    <strong style="color: #003F5F; font-size: 13px;">{{ $nhomCurrent->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                                    <div class="text-muted mt-0.5" style="font-size: 11.5px;">MSSV: <code>{{ $nhomCurrent->truongNhom->taiKhoan->TenDangNhap ?? $nhomCurrent->MaTruongNhom }}</code></div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="text-muted d-block mb-0.5" style="font-size: 11.5px;"><i class="fa-solid fa-chalkboard me-1" style="color: #00838C;"></i>Lớp &amp; Ngành:</span>
                                                    <div class="fw-semibold text-dark" style="font-size: 13px;">{{ $nhomCurrent->truongNhom->lop->TenLop ?? 'Chưa rõ' }}</div>
                                                    <div class="text-muted mt-0.5" style="font-size: 11.5px;">{{ $nhomNganh ?? 'Công nghệ thông tin' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-2.5 bg-white rounded-3 border border-slate-200">
                                            <span class="text-muted d-block mb-0.5" style="font-size: 11.5px;"><i class="fa-solid fa-chalkboard-user me-1" style="color: #00838C;"></i>Giảng viên hướng dẫn (GVHD):</span>
                                            @if($currentDeTai && $currentDeTai->giangVien)
                                                <div class="fw-bold" style="color: #003F5F; font-size: 13.5px;">{{ $currentDeTai->giangVien->HoTen }}</div>
                                                <div class="text-muted mt-0.5" style="font-size: 12px;">
                                                    <span>{{ $currentDeTai->giangVien->HocVi ?? 'Giảng viên' }}</span>
                                                    <span class="mx-1">&bull;</span>
                                                    <span>{{ $currentDeTai->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                                                </div>
                                                @if($currentDeTai->giangVien->Email)
                                                    <div class="text-muted mt-0.5" style="font-size: 11.5px;"><i class="fa-regular fa-envelope me-1"></i><code>{{ $currentDeTai->giangVien->Email }}</code></div>
                                                @endif
                                            @else
                                                <div class="text-muted fst-italic" style="font-size: 12px;">
                                                    <i class="fa-solid fa-circle-info me-1" style="color: #00838C;"></i>Chưa gán GVHD. Giảng viên sẽ được phân công sau khi nhóm hoàn tất đăng ký đề tài.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light px-3 py-2 border-top d-flex justify-content-between align-items-center">
                                        <span class="text-muted" style="font-size: 12px;"><i class="fa-solid fa-user-group me-1"></i>Quy mô: <strong>{{ $memberCountCurrent }}/3 SV</strong></span>
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs" onclick="switchWorkspaceTab('tab-thanhvien')">
                                            <i class="fa-solid fa-users me-1"></i>Xem Thành Viên
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 📄 THÔNG TIN ĐỀ TÀI KHÓA LUẬN -->
                            <div class="col-12 col-lg-6">
                                <div class="card border border-slate-200 shadow-xs rounded-3 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0" style="color: #003F5F; font-size: 13.5px;">
                                            <i class="fa-solid fa-book-bookmark me-2" style="color: #00838C;"></i>📄 THÔNG TIN ĐỀ TÀI KHÓA LUẬN
                                        </h6>
                                        @if($currentDeTai)
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                                                <i class="fa-solid fa-circle-check me-1"></i>{{ $currentDeTai->TrangThai ?? 'Đã duyệt' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        @if($currentDeTai)
                                            <div>
                                                <div class="d-flex align-items-center gap-1.5 mb-1.5 flex-wrap">
                                                    <span class="badge badge-neutral" style="font-size: 11.5px;">Mã: <strong>{{ $currentDeTai->MaDeTai }}</strong></span>
                                                    @if($currentDeTai->LinhVuc)
                                                        <span class="badge badge-neutral" style="font-size: 11.5px;">{{ $currentDeTai->LinhVuc }}</span>
                                                    @endif
                                                </div>
                                                <h6 class="fw-bold mb-1" style="color: #003F5F; font-size: 14px; line-height: 1.35;">
                                                    <a href="javascript:void(0)" class="text-decoration-none" style="color: #003F5F;" data-bs-toggle="modal" data-bs-target="#modalTopicDetail" title="Bấm để xem chi tiết đề tài">
                                                        {{ $currentDeTai->TenDeTai }} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted" style="font-size: 11px;"></i>
                                                    </a>
                                                </h6>
                                                @if($currentDeTai->TenDeTaiTiengAnh)
                                                    <div class="text-muted fst-italic mb-2" style="font-size: 12.5px;">{{ $currentDeTai->TenDeTaiTiengAnh }}</div>
                                                @endif

                                                <!-- Trạng thái đề cương chi tiết -->
                                                <div class="p-2.5 rounded-3 border border-slate-200 mb-2 bg-light">
                                                    <div class="fw-bold mb-1" style="color: #003F5F; font-size: 12.5px;">
                                                        <i class="fa-solid {{ $currentDeTai->FileDeCuong ? 'fa-file-circle-check text-success' : 'fa-file-circle-question text-secondary' }} me-1"></i>
                                                        Đề Cương Chi Tiết:
                                                    </div>
                                                    @if($currentDeTai->FileDeCuong)
                                                        @php
                                                            $fileDcUrl = Str::startsWith($currentDeTai->FileDeCuong, ['http', 'storage/']) ? asset($currentDeTai->FileDeCuong) : asset('storage/' . $currentDeTai->FileDeCuong);
                                                            $fileName = basename($currentDeTai->FileDeCuong);
                                                        @endphp
                                                        <div class="text-muted mb-1.5" style="font-size: 12px;">
                                                            Tên tệp: <code>{{ $fileName }}</code>
                                                        </div>
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 fw-semibold bg-white"
                                                                    onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($currentDeTai->TenDeTai ?? '') }}')">
                                                                <i class="fa-solid fa-eye me-1" style="color: #00838C;"></i>Xem nhanh
                                                            </button>
                                                            <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 fw-semibold bg-white">
                                                                <i class="fa-solid fa-download me-1"></i>Tải về
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="text-muted" style="font-size: 12px;">
                                                            Chưa có tệp đề cương. Nhóm vui lòng chờ Giảng viên hướng dẫn tải lên hoặc duyệt đề cương.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold bg-white" data-bs-toggle="modal" data-bs-target="#modalTopicDetail">
                                                    <i class="fa-solid fa-circle-info me-1" style="color: #00838C;"></i>Chi tiết đề tài
                                                </button>
                                            </div>
                                        @else
                                            <!-- Khi nhóm chưa đăng ký đề tài -->
                                            <div>
                                                @if(!$isFullGroup)
                                                    <div class="alert alert-warning border-warning p-2.5 rounded-3 mb-2">
                                                        <div class="fw-bold mb-0.5" style="font-size: 13px;">
                                                            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Chưa đủ 3 thành viên để đăng ký đề tài
                                                        </div>
                                                        <div class="text-muted" style="font-size: 12.5px;">
                                                            Hiện nhóm có <strong>{{ $memberCountCurrent }}/3 thành viên</strong>. Cần tuyển thêm <strong>{{ 3 - $memberCountCurrent }} sinh viên</strong> nữa để mở quyền đăng ký đề tài.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-success border-success p-2.5 rounded-3 mb-2">
                                                        <div class="fw-bold text-success mb-0.5" style="font-size: 13px;">
                                                            <i class="fa-solid fa-circle-check text-success me-1"></i>Nhóm đã đủ 3 thành viên!
                                                        </div>
                                                        <div class="text-muted" style="font-size: 12.5px;">
                                                            Nhóm đã đủ điều kiện số lượng. Hãy truy cập cổng đăng ký để chọn đề tài khóa luận phù hợp.
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                @if($isFullGroup)
                                                    <a href="{{ route('sinhvien.dangky.index', ['HocPhan' => $nhomCurrent->MaHocPhan ?? 'HP_KLCN', 'HocKy' => $nhomCurrent->MaHocKy]) }}" 
                                                       class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                                        <i class="fa-solid fa-clipboard-list me-1"></i>Đăng Ký Đề Tài Ngay
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 fw-bold" disabled>
                                                        <i class="fa-solid fa-lock me-1"></i>Đăng Ký (Cần 3 SV)
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs" onclick="switchWorkspaceTab('tab-thanhvien')">
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
                        <!-- TRƯỜNG HỢP CHƯA THAM GIA NHÓM NÀO CHO HỌC PHẦN ĐANG CHỌN -->
                        <div class="text-center py-5">
                            <div class="avatar-circle bg-light text-secondary border fs-1 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%;">
                                <i class="fa-solid fa-users-slash"></i>
                            </div>
                            <h4 class="fw-bold text-slate-800 mb-2">Quản Trị Nhóm Của Bạn</h4>
                            <h5 class="fw-semibold text-secondary mb-3">Bạn Chưa Có Nhóm Cho Học Phần Này</h5>
                            <p class="text-muted mb-4 px-3" style="max-width: 540px; margin: 0 auto;">
                                Bạn hiện tại chưa thuộc nhóm nào cho học phần <strong>{{ $selectedHocPhan === 'ALL' ? 'khóa luận' : ($hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? 'này') }}</strong> trong học kỳ đang chọn.
                            </p>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @if($isTaoNhomOpen ?? true)
                                    <button type="button" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                        <i class="fa-solid fa-plus-circle me-2"></i>+ Tạo Nhóm Mới Ngay
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-lg rounded-pill px-4 fw-bold shadow-sm" disabled title="Tạm thời không trong thời gian tạo nhóm">
                                        <i class="fa-solid fa-lock me-2"></i>Tạm Thời Không Trong Thời Gian Tạo Nhóm
                                    </button>
                                @endif
                                <!-- Nút tìm nhóm dùng màu xanh biển chuẩn -->
                                <button type="button" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-sm" onclick="switchWorkspaceTab('tab-timnhom')">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i>Tìm Nhóm Đang Mở Để Gia Nhập
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- ################################################################################## -->
                <!-- TAB 2: THÀNH VIÊN -->
                <!-- ################################################################################## -->
                <div class="tab-pane fade" id="tab-thanhvien" role="tabpanel" aria-labelledby="tab-thanhvien-tab">
                    @if($nhomCurrent)
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold text-slate-800 mb-1">
                                    <i class="fa-solid fa-user-group text-secondary me-2"></i>Quản Trị Thành Viên Nhóm Của Bạn ({{ $nhomCurrent->thanhViens->count() }}/3)
                                </h5>
                                <div class="text-muted">Danh sách thành viên chính thức và chức năng tuyển dụng thành viên cùng ngành</div>
                            </div>
                            @if($isNhomLocked)
                                <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="fa-solid fa-lock me-1"></i>Nhóm đã khóa thành viên</span>
                            @else
                                <span class="badge {{ $nhomCurrent->thanhViens->count() === 3 ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 py-2">
                                    <i class="fa-solid fa-users me-1"></i>{{ $nhomCurrent->thanhViens->count() === 3 ? 'Đã đủ 3/3 thành viên' : 'Còn thiếu ' . (3 - $nhomCurrent->thanhViens->count()) . ' thành viên' }}
                                </span>
                            @endif
                        </div>

                        <!-- BẢNG DANH SÁCH THÀNH VIÊN CHÍNH THỨC -->
                        <div class="table-responsive mb-4 rounded-3 border border-slate-200">
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
                                               data-mssv="{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}"
                                               title="Bấm để xem thông tin chi tiết">
                                                <span class="badge badge-neutral"><code>{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}</code></span>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-slate-800">{{ $tv->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">{{ $tv->sinhVien->Email ?? '' }}</div>
                                        </td>
                                        <td>{{ $tv->sinhVien->lop->TenLop ?? 'Chưa rõ' }}</td>
                                        <td>{{ $tv->sinhVien->lop->nganh->TenNganh ?? $tv->sinhVien->nganh->TenNganh ?? 'Công nghệ thông tin' }}</td>
                                        <td class="text-center">
                                            @if($tv->VaiTro === 'Trưởng nhóm')
                                                <span class="badge bg-warning text-dark rounded-pill px-3">Trưởng nhóm</span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill px-3">Thành viên</span>
                                            @endif
                                        </td>
                                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked)
                                        <td class="text-center">
                                            @if($tv->MaSV !== $sinhVien->MaSV)
                                                <form action="{{ route('sinhvien.nhom.khaiTru', ['id' => $nhomCurrent->MaNhom, 'maSV' => $tv->MaSV]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn KHAI TRỪ sinh viên {{ $tv->sinhVien->HoTen }} khỏi nhóm?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Khai trừ thành viên">
                                                        <i class="fa-solid fa-user-minus me-1"></i>Khai Trừ
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted fst-italic">Trưởng nhóm</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- KHU VỰC QUẢN TRỊ TRƯỞNG NHÓM (KHI CHƯA KHÓA VÀ CÒN CHỖ) -->
                        @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && !$isNhomLocked && $nhomCurrent->thanhViens->count() < 3)
                            <div class="card border border-slate-200 shadow-xs rounded-4 p-4 mt-4 bg-white">
                                <h6 class="fw-bold text-slate-800 mb-3">
                                    <i class="fa-solid fa-user-plus text-secondary me-2"></i>Mời Sinh Viên Cùng Ngành Vào Nhóm Bằng MSSV
                                </h6>

                                @if(!($isTaoNhomOpen ?? false))
                                    <div class="alert alert-warning p-3 rounded-3 mb-0">
                                        <i class="fa-solid fa-clock me-1"></i>{{ $groupPhaseState['message'] ?? 'Cổng quản lý và ghép nhóm thành viên hiện đang đóng.' }}
                                    </div>
                                @else
                                    @if(session('invite_success'))
                                    <div class="alert alert-success alert-dismissible fade show p-2 px-3 mb-3 border-0 rounded-3" role="alert">
                                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('invite_success') }}
                                        <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    @endif

                                    <div id="stepSearchArea">
                                        <div class="input-group">
                                            <input type="text" id="inputMSSVSearch" class="form-control" placeholder="Nhập chính xác MSSV cùng ngành (ví dụ: 2001210060, 2001210001)...">
                                            <!-- Nút Tra cứu màu xanh biển -->
                                            <button class="btn btn-primary px-4 fw-bold" type="button" id="btnDoSearch">
                                                <i class="fa-solid fa-magnifying-glass me-1"></i>Tra Cứu
                                            </button>
                                        </div>
                                        <div class="form-text text-muted mt-1">
                                            <i class="fa-solid fa-shield-halved me-1 text-secondary"></i>Hệ thống tự động kiểm tra điều kiện cùng ngành đào tạo và đủ điều kiện làm khóa luận trước khi cho phép mời.
                                        </div>
                                        <div id="searchErrorMessage" class="alert alert-danger p-2 mt-2 d-none"></div>
                                    </div>

                                    <!-- Thẻ xác nhận thông tin sinh viên -->
                                    <div id="stepVerificationCard" class="d-none mt-3">
                                        <div class="card border border-slate-300 bg-light p-3 rounded-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                <h6 class="fw-bold text-slate-800 mb-0"><i class="fa-solid fa-id-card text-secondary me-2"></i>Xác Nhận Sinh Viên Cùng Ngành</h6>
                                                <span id="cardBadgeStatus" class="badge bg-success rounded-pill"></span>
                                            </div>
                                            <div class="mb-2">
                                                <div class="fs-6 fw-bold text-slate-800" id="cardHoTen"></div>
                                                <div class="text-muted">MSSV: <strong id="cardMSSV" class="text-slate-800"></strong></div>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6"><span class="text-muted">Lớp:</span> <strong id="cardLop" class="text-slate-800"></strong></div>
                                                <div class="col-6"><span class="text-muted">Ngành:</span> <strong id="cardNganh" class="text-slate-800"></strong></div>
                                            </div>
                                            <div class="p-2 rounded-2 bg-white border mb-3" id="cardStatusDetailBox">
                                                <div class="text-muted mb-1">Trạng thái:</div>
                                                <div class="fw-bold text-slate-800" id="cardStatusText"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnBackSearch">
                                                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                                                </button>
                                                <form action="{{ route('sinhvien.nhom.moiThanhVien') }}" method="POST" id="formConfirmInvite">
                                                    @csrf
                                                    <input type="hidden" name="MaNhom" value="{{ $nhomCurrent->MaNhom }}">
                                                    <input type="hidden" name="MaSV" id="hiddenMaSV" value="">
                                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" id="btnSubmitInvite">
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
                            <h5 class="fw-bold text-slate-800 mb-2">Bạn chưa tham gia nhóm nào</h5>
                            <p class="mb-3">Vui lòng tạo nhóm mới hoặc xin gia nhập một nhóm đang mở để quản lý thành viên.</p>
                            @if($isTaoNhomOpen ?? true)
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                    <i class="fa-solid fa-plus-circle me-1"></i>Tạo Nhóm Mới
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 fw-bold shadow-sm" disabled title="Tạm thời không trong thời gian tạo nhóm">
                                    <i class="fa-solid fa-lock me-1"></i>Tạm Thời Không Trong Thời Gian Tạo Nhóm
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- ################################################################################## -->
                <!-- TAB 3: LỜI MỜI & YÊU CẦU -->
                <!-- ################################################################################## -->
                <div class="tab-pane fade" id="tab-loimoi" role="tabpanel" aria-labelledby="tab-loimoi-tab">
                    <div class="mb-4">
                        <h5 class="fw-bold text-slate-800 mb-1"><i class="fa-solid fa-envelope-open-text text-secondary me-2"></i>Quản Trị Lời Mời & Yêu Cầu Ghép Nhóm</h5>
                        <div class="text-muted">Theo dõi và phản hồi các lời mời gia nhập nhóm hoặc các yêu cầu xin vào nhóm của bạn</div>
                    </div>

                    <!-- 1. LỜI MỜI GIA NHẬP NHÓM GỬI TỚI BẠN -->
                    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-slate-800 mb-0">
                                <i class="fa-solid fa-bell text-warning me-2"></i>Lời Mời Tham Gia Nhóm Gửi Tới Bạn
                            </h6>
                            <span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1">
                                {{ isset($loiMois) ? $loiMois->count() : 0 }} lời mời
                            </span>
                        </div>
                        <div class="card-body p-4">
                            @if(isset($loiMois) && $loiMois->count() > 0)
                                <div class="alert alert-warning border-warning shadow-sm rounded-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-envelope-open-text me-2 text-warning fs-5"></i>Bạn có {{ $loiMois->count() }} lời mời tham gia nhóm!
                                        </div>
                                        @if($loiMois->count() > 2)
                                            <span class="badge bg-warning text-dark border border-warning rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-arrows-up-down me-1"></i>Cuộn để xem thêm ({{ $loiMois->count() }} lời mời)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="loi-moi-scroll-container">
                                    @foreach($loiMois as $lm)
                                    <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded-3 mb-2 border border-slate-200 shadow-xs">
                                        <div>
                                            <div class="fw-bold text-slate-800 mb-1">
                                                {{ $lm->nhom->TenNhom ?? 'Nhóm Khóa Luận' }}
                                                @if($lm->nhom && $lm->nhom->hocPhan)
                                                    <span class="badge badge-neutral ms-1">{{ $lm->nhom->hocPhan->TenHocPhan }}</span>
                                                @endif
                                                @if($lm->nhom && $lm->nhom->hocKy)
                                                    <span class="badge badge-neutral ms-1">{{ $lm->nhom->hocKy->TenHocKy }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted">
                                                Từ Trưởng nhóm: <strong>{{ $lm->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (<code>{{ $lm->nhom->truongNhom->taiKhoan->TenDangNhap ?? $lm->nhom->MaTruongNhom }}</code>)
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-2 mt-sm-0">
                                            <!-- Nút Xem Nhóm màu xanh biển -->
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 btn-view-group-detail shadow-xs" data-manhom="{{ $lm->MaNhom }}">
                                                <i class="fa-solid fa-eye me-1"></i>Xem Nhóm
                                            </button>
                                            <form action="{{ route('sinhvien.nhom.xacNhanLoiMoi', $lm->MaNhom) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs">
                                                    <i class="fa-solid fa-check me-1"></i>Chấp Nhận
                                                </button>
                                            </form>
                                            <form action="{{ route('sinhvien.nhom.tuChoiLoiMoi', $lm->MaNhom) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2">Từ Chối</button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fs-2 opacity-50 mb-2 d-block"></i>
                                    Hiện tại bạn không có lời mời tham gia nhóm nào.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. NẾU LÀ TRƯỞNG NHÓM: YÊU CẦU XIN VÀO & LỜI MỜI ĐÃ GỬI -->
                    @if(isset($nhomCurrent) && $nhomCurrent->MaTruongNhom === $sinhVien->MaSV)
                    <div class="row g-4 mb-4">
                        <!-- Yêu Cầu Xin Vào Nhóm Của Bạn -->
                        <div class="col-12 col-md-6">
                            <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-light py-3 px-4 fw-bold text-slate-800 d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-hand-holding-hand me-2 text-secondary"></i>Yêu Cầu Xin Vào Nhóm ({{ $yeuCauXinVao->count() }})</span>
                                </div>
                                <div class="card-body p-3">
                                    @forelse($yeuCauXinVao as $yc)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded-3 bg-white">
                                        <div>
                                            <div class="fw-bold text-slate-800">{{ $yc->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">MSSV: <code>{{ $yc->sinhVien->taiKhoan->TenDangNhap ?? $yc->MaSV }}</code> | Lớp: {{ $yc->sinhVien->lop->TenLop ?? '' }}</div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <form action="{{ route('sinhvien.nhom.duyetYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-2 py-1 shadow-xs" title="Duyệt vào nhóm">
                                                    <i class="fa-solid fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            <form action="{{ route('sinhvien.nhom.tuChoiYeuCau', ['id' => $nhomCurrent->MaNhom, 'maSV' => $yc->MaSV]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" title="Từ chối">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">Chưa có yêu cầu xin gia nhập nào.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Lời Mời Nhóm Đã Gửi Đi -->
                        <div class="col-12 col-md-6">
                            <div class="card border border-slate-200 shadow-xs rounded-4 h-100 overflow-hidden">
                                <div class="card-header bg-light py-3 px-4 fw-bold text-slate-800 d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-paper-plane me-2 text-secondary"></i>Lời Mời Bạn Đã Gửi ({{ $loiMoiDaGui->count() }})</span>
                                </div>
                                <div class="card-body p-3">
                                    @forelse($loiMoiDaGui as $lm)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded-3 bg-white">
                                        <div>
                                            <div class="fw-semibold text-slate-800">{{ $lm->sinhVien->HoTen ?? '' }}</div>
                                            <div class="text-muted">MSSV: <code>{{ $lm->sinhVien->taiKhoan->TenDangNhap ?? $lm->MaSV }}</code></div>
                                        </div>
                                        <form action="{{ route('sinhvien.nhom.huyLoiMoiDaGui', ['id' => $nhomCurrent->MaNhom, 'maSV' => $lm->MaSV]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" title="Thu hồi lời mời">
                                                <i class="fa-solid fa-rotate-left"></i> Thu Hồi
                                            </button>
                                        </form>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">Không có lời mời nào đang chờ phản hồi.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 3. YÊU CẦU GIA NHẬP BẠN ĐÃ GỬI TỚI CÁC NHÓM KHÁC -->
                    @if(isset($yeuCauDaGui) && $yeuCauDaGui->count() > 0)
                    <div class="card border border-slate-200 shadow-xs rounded-4 overflow-hidden">
                        <div class="card-header bg-light py-3 px-4 fw-bold text-slate-800">
                            <i class="fa-solid fa-hourglass-start me-2 text-warning"></i>Yêu Cầu Gia Nhập Bạn Đã Gửi Tới Các Nhóm Khác ({{ $yeuCauDaGui->count() }})
                        </div>
                        <div class="card-body p-3">
                            @foreach($yeuCauDaGui as $maNhom => $yc)
                            <div class="d-flex justify-content-between align-items-center p-3 mb-2 border rounded-3 bg-white">
                                <div>
                                    <div class="fw-bold text-slate-800">{{ $yc->nhom->TenNhom ?? 'Nhóm ' . $maNhom }}</div>
                                    <div class="text-muted">Trưởng nhóm: <strong>{{ $yc->nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong> (<code>{{ $yc->nhom->truongNhom->taiKhoan->TenDangNhap ?? $yc->nhom->MaTruongNhom }}</code>)</div>
                                </div>
                                <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $maNhom) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-bold">
                                        <i class="fa-solid fa-xmark me-1"></i>Hủy Yêu Cầu
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- ################################################################################## -->
                <!-- TAB 4: TÌM NHÓM MỞ -->
                <!-- ################################################################################## -->
                <div class="tab-pane fade" id="tab-timnhom" role="tabpanel" aria-labelledby="tab-timnhom-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-slate-800 mb-1">
                                <i class="fa-solid fa-users-viewfinder text-secondary me-2"></i>Quản Trị Các Nhóm
                            </h5>
                            <div class="text-muted">Danh sách các nhóm khóa luận cùng ngành đang mở và còn trống chỗ</div>
                        </div>
                        @if($isTaoNhomOpen ?? true)
                            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                <i class="fa-solid fa-plus-circle me-1"></i>+ Tạo Nhóm Mới
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 py-2 fw-bold shadow-sm" disabled title="Tạm thời không trong thời gian tạo nhóm">
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
                            <span class="input-group-text bg-white border-end-0 text-secondary">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0 fw-semibold input-search-sync" 
                                   placeholder="🔍 Nhập MSSV hoặc tên nhóm (ví dụ: 2001210060, Nhóm...)..." value="{{ request('q') }}">
                            <!-- Nút Tìm kiếm màu xanh biển -->
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-xs">
                                <i class="fa-solid fa-search me-1"></i>Tìm
                            </button>
                            @if(request('q'))
                            <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $selectedHocPhan, 'hoc_ky' => $maHocKy, 'bo_mon' => $selectedBoMon]) }}" class="btn btn-outline-secondary px-3" title="Xóa tìm kiếm">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                            @endif
                        </div>
                    </form>

                    <!-- TIÊU ĐỀ & SỐ LƯỢNG NHÓM -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <i class="fa-solid fa-list-check text-secondary me-2"></i>Danh Sách Nhóm Khóa Luận Đang Mở
                        </h6>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-neutral px-3 py-2" id="badge-group-count">
                                Tìm thấy {{ count($nhomsOpen ?? []) }} nhóm
                            </span>
                            @if(count($nhomsOpen ?? []) > 2)
                                <span class="badge badge-neutral px-2 py-2" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-arrows-up-down me-1 text-secondary"></i>Cuộn xem thêm
                                </span>
                            @endif
                        </div>
                    </div>

                    @php
                        $userGroups = isset($sinhVienAllGroups) ? $sinhVienAllGroups->pluck('nhom')->filter() : collect();
                        $invitedGroupIds = isset($loiMois) ? $loiMois->pluck('MaNhom')->toArray() : [];
                    @endphp

                    <div class="nhom-open-scroll-container d-flex flex-column gap-3">
                        @forelse($nhomsOpen ?? [] as $no)
                        @php
                            // Bỏ qua nhóm đã gửi lời mời (đã hiển thị riêng ở khung thông báo trên cùng)
                            if (in_array($no->MaNhom, $invitedGroupIds)) {
                                continue;
                            }
                            $memberCount = $no->thanhViens->count();
                            $isFull = ($memberCount >= 3);
                            $hasPdk = ($no->phieuDangKys && $no->phieuDangKys->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])->isNotEmpty()) 
                                      || (!empty($no->MaDeTai));
                            $hasRequestedThis = isset($yeuCauDaGui[$no->MaNhom]);
                            $isMyGroup = ($nhomCurrent && $nhomCurrent->MaNhom === $no->MaNhom) || $userGroups->contains('MaNhom', $no->MaNhom);
                            
                            // Kiểm tra xem SV đã có nhóm cho đúng học phần & học kỳ này chưa
                            $alreadyInThisCourseGroup = $userGroups->first(function($g) use ($no) {
                                return $g && $g->MaHocKy === $no->MaHocKy && $g->MaHocPhan === $no->MaHocPhan;
                            });
                        @endphp
                        <div class="card rounded-3 p-3 shadow-xs border group-item-card {{ $isMyGroup ? 'border-slate-400 bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <h6 class="fw-bold text-slate-800 mb-0">{{ $no->TenNhom }}</h6>
                                        @if($isMyGroup)
                                            <span class="badge bg-secondary text-white rounded-pill px-2 py-1"><i class="fa-solid fa-check-circle me-1"></i>Nhóm của bạn</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        @if($no->hocPhan)
                                            <span class="badge badge-neutral">
                                                <i class="fa-solid fa-graduation-cap me-1 text-secondary"></i>{{ $no->hocPhan->TenHocPhan }}
                                            </span>
                                        @endif
                                        @if($no->hocKy)
                                            <span class="badge badge-neutral">
                                                <i class="fa-solid fa-calendar me-1 text-secondary"></i>{{ $no->hocKy->TenHocKy }}
                                            </span>
                                        @endif
                                        @php
                                            $nhomNganhItem = $no->truongNhom?->nganh?->TenNganh ?? $no->truongNhom?->lop?->nganh?->TenNganh;
                                        @endphp
                                        @if($nhomNganhItem)
                                            <span class="badge badge-neutral">
                                                <i class="fa-solid fa-network-wired me-1 text-secondary"></i>{{ $nhomNganhItem }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($hasPdk)
                                        <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fa-solid fa-book-bookmark me-1"></i>Đã Chọn Đề Tài</span>
                                    @elseif($isFull)
                                        <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fa-solid fa-lock me-1"></i>Đã Đủ 3/3</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3 py-1"><i class="fa-solid fa-user-plus me-1"></i>Còn {{ 3 - $memberCount }} Chỗ</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-slate-800">
                                    <i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm: <strong>{{ $no->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                    <code class="ms-1">({{ $no->truongNhom->taiKhoan->TenDangNhap ?? $no->MaTruongNhom }})</code>
                                </div>
                                <div class="text-muted mt-1 d-flex align-items-center gap-3 flex-wrap">
                                    <span>Lớp: {{ $no->truongNhom->lop->TenLop ?? 'Chưa rõ' }}</span>
                                    <span><i class="fa-solid fa-users text-secondary me-1"></i><strong>{{ $memberCount }} / 3</strong> thành viên</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-view-group-detail bg-white" data-manhom="{{ $no->MaNhom }}">
                                    <i class="fa-solid fa-eye me-1"></i>Xem Chi Tiết
                                </button>

                                @if($isMyGroup)
                                    <span class="badge badge-neutral px-3 py-2">
                                        <i class="fa-solid fa-check me-1 text-success"></i>Bạn đang ở trong nhóm này
                                    </span>
                                @elseif($hasRequestedThis)
                                    <form action="{{ route('sinhvien.nhom.huyXinGiaNhap', $no->MaNhom) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-bold">
                                            <i class="fa-solid fa-clock me-1"></i>Đang Chờ Duyệt (Hủy)
                                        </button>
                                    </form>
                                @elseif($hasPdk)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="Nhóm đã đăng ký đề tài">
                                        <i class="fa-solid fa-lock me-1"></i>Đã Khóa Đề Tài
                                    </button>
                                @elseif($isFull)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled>
                                        <i class="fa-solid fa-ban me-1"></i>Đã Đủ Thành Viên
                                    </button>
                                @elseif(!($isTaoNhomOpen ?? true))
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="{{ $groupPhaseState['message'] ?? 'Cổng tạo và ghép nhóm chưa mở' }}">
                                        <i class="fa-solid fa-lock me-1"></i>Hết Hạn Tạo Nhóm
                                    </button>
                                @elseif($alreadyInThisCourseGroup)
                                    <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="Bạn đã thuộc nhóm '{{ $alreadyInThisCourseGroup->TenNhom }}' cho học phần này rồi">
                                        <i class="fa-solid fa-ban me-1"></i>Đã Có Nhóm Môn Này
                                    </button>
                                @else
                                    <form action="{{ route('sinhvien.nhom.xinGiaNhap', $no->MaNhom) }}" method="POST">
                                        @csrf
                                        <!-- Nút Xin gia nhập dùng màu xanh biển -->
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="return confirm('Bạn có chắc muốn gửi yêu cầu xin gia nhập nhóm {{ $no->TenNhom }}?');">
                                            <i class="fa-solid fa-hand me-1"></i>Xin Gia Nhập
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fs-1 opacity-50 mb-3 d-block"></i>
                            <p class="mb-2">Không tìm thấy nhóm nào phù hợp với bộ lọc hoặc từ khóa tìm kiếm.</p>
                            @if($isTaoNhomOpen ?? true)
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCreateGroup">
                                    <i class="fa-solid fa-plus-circle me-1"></i>Tạo Nhóm Mới Ngay
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 fw-bold" disabled>
                                    <i class="fa-solid fa-lock me-1"></i>Hết Hạn Tạo Nhóm
                                </button>
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
<!-- MODAL XEM CHI TIẾT NHÓM (DÀNH CHO NGƯỜI XIN VÀO HOẶC XEM THÀNH VIÊN) -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGroupDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4" style="background-color: #1e293b !important;">
                <div>
                    <h5 class="modal-title fw-bold" id="groupDetailName"><i class="fa-solid fa-users me-2"></i>Nhóm Khóa Luận</h5>
                    <div class="opacity-75" id="groupDetailSub" style="font-size: 13px;">Chi tiết thành viên trong nhóm</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted d-block mb-1" style="font-size: 13px;"><i class="fa-solid fa-calendar-alt me-1 text-secondary"></i>Học kỳ:</span>
                            <span class="badge badge-neutral text-wrap text-start" id="groupDetailHocKy">--</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block mb-1" style="font-size: 13px;"><i class="fa-solid fa-book-open me-1 text-secondary"></i>Môn học / Học phần:</span>
                            <span class="badge badge-neutral text-wrap text-start" id="groupDetailHocPhan">--</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="text-muted" style="font-size: 13px;">Quy mô:</span>
                        <strong class="text-slate-800 ms-1" id="groupDetailCount">--</strong>
                    </div>
                    <div id="groupDetailStatusBadge"></div>
                </div>

                <h6 class="fw-bold text-slate-800 mb-3"><i class="fa-solid fa-user-group text-secondary me-2"></i>DANH SÁCH THÀNH VIÊN</h6>
                <div id="groupDetailMembersList" class="d-flex flex-column gap-2 mb-3">
                    <!-- Sẽ render động qua JS -->
                </div>

                <div class="p-2 rounded-2 bg-light text-center text-muted" id="groupDetailSlotMessage" style="font-size: 13px;">
                    <!-- Hiển thị còn bao nhiêu vị trí -->
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between px-4 py-3 bg-light border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                <div id="groupDetailActionArea">
                    <!-- Nút Xin gia nhập sẽ được JS đưa vào đây -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL XEM THÔNG TIN PROFILE CHI TIẾT SINH VIÊN -->
<!-- ======================================================== -->
<div class="modal fade" id="modalStudentProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4" style="background-color: #1e293b !important;">
                <h6 class="modal-title fw-bold" id="modalProfileTitle"><i class="fa-solid fa-user-graduate me-2"></i>Hồ Sơ Sinh Viên</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="avatar-circle bg-light text-secondary border fs-2 fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-slate-800" id="profileModalHoTen">--</h5>
                    <span class="badge badge-neutral" id="profileModalMSSV">--</span>
                </div>
                <div class="list-group list-group-flush border-top border-bottom my-3">
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-chalkboard me-2"></i>Lớp học:</span>
                        <strong id="profileModalLop" class="text-slate-800">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-graduation-cap me-2"></i>Chuyên ngành:</span>
                        <strong id="profileModalNganh" class="text-slate-800">--</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-2"></i>Email:</span>
                        <span id="profileModalEmail" class="text-slate-800">--</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-phone me-2"></i>Số điện thoại:</span>
                        <span id="profileModalPhone" class="text-slate-800">--</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-users me-2"></i>Tình trạng nhóm:</span>
                        <span id="profileModalGroupStatus">--</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL CHI TIẾT ĐỀ TÀI ĐÃ GÁN CHO NHÓM -->
<!-- ======================================================== -->
@if(isset($nhomCurrent) && ($nhomCurrent->deTai || $nhomCurrent->dangKyDeTai?->deTai))
@php
    $dtDetail = $nhomCurrent->deTai ?? $nhomCurrent->dangKyDeTai?->deTai;
@endphp
<div class="modal fade" id="modalTopicDetail" tabindex="-1" aria-labelledby="modalTopicDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4" style="background-color: #1e293b !important;">
                <div>
                    <h5 class="modal-title fw-bold" id="modalTopicDetailLabel">
                        <i class="fa-solid fa-book-bookmark me-2"></i>Chi Tiết Đề Tài Khóa Luận
                    </h5>
                    <div class="opacity-75" style="font-size: 13px;">Thông tin đề tài đã được gán chính thức cho nhóm của bạn</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge badge-neutral">Mã: <strong>{{ $dtDetail->MaDeTai }}</strong></span>
                        <span class="badge bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i>{{ $dtDetail->TrangThai }}</span>
                        @if($dtDetail->LinhVuc)
                            <span class="badge badge-neutral">{{ $dtDetail->LinhVuc }}</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-slate-800 mb-1">{{ $dtDetail->TenDeTai }}</h5>
                    @if($dtDetail->TenDeTaiTiengAnh)
                        <div class="text-secondary fst-italic mb-2">{{ $dtDetail->TenDeTaiTiengAnh }}</div>
                    @endif
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h6 class="fw-bold text-slate-800 mb-2"><i class="fa-solid fa-graduation-cap text-secondary me-2"></i>Thông tin môn học</h6>
                            <div class="mb-1">
                                <span class="text-muted">Học kỳ:</span> 
                                <strong class="text-slate-800">{{ $dtDetail->hocKy->TenHocKy ?? $nhomCurrent->hocKy->TenHocKy ?? 'Chưa xác định' }}</strong>
                            </div>
                            <div class="mb-1">
                                <span class="text-muted">Học phần:</span> 
                                <strong class="text-slate-800">{{ $dtDetail->hocPhanRef->TenHocPhan ?? $dtDetail->HocPhan ?? $nhomCurrent->hocPhan->TenHocPhan ?? 'Khóa luận' }}</strong>
                            </div>
                            <div>
                                <span class="text-muted">Quy mô nhóm:</span> 
                                <strong class="text-slate-800">Tối đa {{ $dtDetail->SoLuongSinhVienToiDa ?? 3 }} sinh viên</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h6 class="fw-bold text-slate-800 mb-2"><i class="fa-solid fa-chalkboard-user text-secondary me-2"></i>Giảng viên hướng dẫn</h6>
                            <div class="mb-1">
                                <span class="text-muted">Họ tên:</span> 
                                <strong class="text-slate-800">{{ $dtDetail->giangVien->HoTen ?? 'Chưa gán' }}</strong>
                            </div>
                            <div class="mb-1">
                                <span class="text-muted">Học vị:</span> 
                                <span class="text-slate-800">{{ $dtDetail->giangVien->HocVi ?? 'Giảng viên' }}</span>
                            </div>
                            <div class="mb-1">
                                <span class="text-muted">Bộ môn:</span> 
                                <span class="text-slate-800">{{ $dtDetail->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                            </div>
                            @if($dtDetail->giangVien && $dtDetail->giangVien->Email)
                            <div>
                                <span class="text-muted">Email:</span> 
                                <code>{{ $dtDetail->giangVien->Email }}</code>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($dtDetail->MoTa)
                <div class="mb-3">
                    <label class="fw-bold text-muted text-uppercase mb-1" style="font-size: 13px;"><i class="fa-solid fa-align-left me-1 text-secondary"></i>Mô tả tóm tắt nội dung:</label>
                    <div class="p-3 bg-light rounded-3 border text-slate-800" style="line-height: 1.6;">
                        {!! \App\Helpers\HtmlHelper::clean($dtDetail->MoTa) !!}
                    </div>
                </div>
                @endif

                @if($dtDetail->MucTieu)
                <div class="mb-3">
                    <label class="fw-bold text-muted text-uppercase mb-1" style="font-size: 13px;"><i class="fa-solid fa-bullseye me-1 text-secondary"></i>Mục tiêu & Sản phẩm dự kiến:</label>
                    <div class="p-3 bg-light rounded-3 border text-slate-800" style="white-space: pre-line;">
                        {{ $dtDetail->MucTieu }}
                    </div>
                </div>
                @endif

                @if($dtDetail->YeuCau)
                <div class="mb-3">
                    <label class="fw-bold text-muted text-uppercase mb-1" style="font-size: 13px;"><i class="fa-solid fa-list-check me-1 text-secondary"></i>Yêu cầu đối với sinh viên:</label>
                    <div class="p-3 bg-light rounded-3 border text-slate-800" style="white-space: pre-line;">
                        {{ $dtDetail->YeuCau }}
                    </div>
                </div>
                @endif

                <div class="mb-2">
                    <label class="fw-bold text-muted text-uppercase mb-2" style="font-size: 13px;"><i class="fa-solid fa-file-lines me-1 text-secondary"></i>Đề cương chi tiết:</label>
                    @if($dtDetail->FileDeCuong)
                        @php
                            $fileDcUrl = Str::startsWith($dtDetail->FileDeCuong, ['http', 'storage/']) ? asset($dtDetail->FileDeCuong) : asset('storage/' . $dtDetail->FileDeCuong);
                            $fileName = basename($dtDetail->FileDeCuong);
                        @endphp
                        <div class="p-3 bg-light border rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <div class="fw-bold text-slate-800 mb-1">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i>File đề cương chi tiết đã được cập nhật
                                </div>
                                <div class="text-muted">
                                    <i class="fa-solid fa-paperclip me-1 text-secondary"></i>Tên tệp: <code>{{ $fileName }}</code>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs fw-semibold bg-white"
                                        onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($dtDetail->TenDeTai ?? '') }}')">
                                    <i class="fa-solid fa-eye me-1"></i>Xem Nhanh Đề Cương
                                </button>
                                <a href="{{ $fileDcUrl }}" download="{{ $fileName }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold bg-white">
                                    <i class="fa-solid fa-download me-1"></i>Tải Về
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-light border rounded-3 text-center text-muted">
                            <i class="fa-solid fa-file-circle-xmark text-secondary fs-4 mb-1 d-block"></i>
                            <div class="fw-semibold text-slate-800">Chưa có file đề cương chi tiết</div>
                            <div class="text-muted">Nhóm sinh viên vui lòng chờ Giảng viên hướng dẫn nộp Đề cương chi tiết.</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer px-4 py-3 bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ======================================================== -->
<!-- MODAL TẠO NHÓM MỚI (CHO PHÉP TẠO CHO TỪNG HỌC PHẦN KHÁC NHAU) -->
<!-- ======================================================== -->
<div class="modal fade" id="modalCreateGroup" tabindex="-1" aria-labelledby="modalCreateGroupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('sinhvien.nhom.store') }}" method="POST" id="formCreateGroup">
                @csrf
                <div class="modal-header border-bottom px-4 py-3 bg-dark text-white" style="background-color: #1e293b !important;">
                    <h5 class="modal-title fw-bold text-white" id="modalCreateGroupLabel">
                        <i class="fa-solid fa-users-gear me-2"></i>Tạo Nhóm Khóa Luận / Đồ Án Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if(!($isTaoNhomOpen ?? true))
                        <!-- Cảnh báo khi hết hạn tạo nhóm -->
                        <div class="alert alert-warning border-0 rounded-3 mb-4 p-3 d-flex align-items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation fs-3 text-warning"></i>
                            <div>
                                <div class="fw-bold text-dark">Tạm thời không trong thời gian tạo nhóm</div>
                                <div class="small text-muted">{{ $groupPhaseState['message'] ?? 'Cổng tạo và ghép nhóm thành viên hiện đang đóng theo Kế hoạch khóa luận.' }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Callout hướng dẫn -->
                    <div class="alert alert-light border rounded-3 mb-4 p-3 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-info fs-3 text-secondary mt-1"></i>
                        <div>
                            <div class="fw-bold text-slate-800 mb-1">Quy trình khởi tạo nhóm khóa luận:</div>
                            <div class="text-slate-800">
                                Vui lòng chọn <span class="badge bg-secondary rounded-pill me-1">1</span> <strong>Học kỳ áp dụng</strong> ➔ 
                                <span class="badge bg-secondary rounded-pill me-1">2</span> <strong>Học phần Khóa luận / Đồ án</strong>.
                                <div class="text-muted mt-1">
                                    <i class="fa-solid fa-check text-success me-1"></i>Bạn có thể tạo nhóm cho các học phần khác nhau (hoặc các học kỳ khác nhau).
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BƯỚC 1: CHỌN HỌC KỲ -->
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-slate-800 d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <span class="badge bg-secondary rounded-pill px-2 py-1 me-1">Bước 1</span> 
                                Chọn Học Kỳ Áp Dụng <span class="text-danger">*</span>
                            </span>
                            <span class="text-muted fw-normal"><i class="fa-solid fa-calendar-days me-1"></i>Kỳ học khóa luận</span>
                        </label>
                        <select name="MaHocKy" id="create_group_hocky" class="form-select border shadow-xs" required>
                            <option value="">-- Chọn Học Kỳ --</option>
                            @if(isset($hocKies))
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ (isset($maHocKy) && $maHocKy === $hk->MaHocKy) ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} — [{{ $hk->TrangThai }}]
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text text-muted mt-1">
                            <i class="fa-solid fa-circle-check text-success me-1"></i>Chọn học kỳ mà bạn dự kiến thực hiện học phần.
                        </div>
                    </div>

                    <!-- BƯỚC 2: CHỌN HỌC PHẦN KHÓA LUẬN -->
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-slate-800 d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <span class="badge bg-secondary rounded-pill px-2 py-1 me-1">Bước 2</span> 
                                Chọn Học Phần Khóa Luận / Đồ Án <span class="text-danger">*</span>
                            </span>
                            <span class="text-muted fw-normal" id="step2_badge_status">
                                <i class="fa-solid fa-lock me-1"></i>Khóa cho đến khi chọn Bước 1
                            </span>
                        </label>
                        <select name="MaHocPhan" id="create_group_hocphan" class="form-select border shadow-xs" required disabled>
                            <option value="">-- Bước 2: Vui lòng chọn học kỳ ở bước 1 trước --</option>
                        </select>
                        <div class="form-text text-muted mt-1" id="create_group_hocphan_help">
                            <i class="fa-solid fa-graduation-cap me-1"></i>Học phần: <strong>Khóa luận tốt nghiệp theo Bộ môn</strong> mở trong kỳ này.
                        </div>
                    </div>

                    <!-- THÔNG TIN NHÓM TỰ SINH -->
                    <div class="card border border-slate-200 bg-white rounded-3 p-3 shadow-xs">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="text-muted d-block">Tên nhóm tự sinh theo chuẩn:</span>
                                <div class="fw-bold text-slate-800 fs-5">
                                    <i class="fa-solid fa-users-rectangle text-secondary me-1"></i>Nhóm {{ $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted d-block">Trưởng nhóm sáng lập:</span>
                                <div class="fw-bold text-slate-800">
                                    {{ $sinhVien->HoTen }} <code class="text-muted">({{ $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV }})</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Hủy Bỏ
                    </button>
                    @if($isTaoNhomOpen ?? true)
                        <button type="submit" id="btn_submit_create_group" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-check-circle me-2"></i>Xác Nhận Tạo Nhóm
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary btn-lg rounded-pill px-4 fw-bold shadow-sm" disabled>
                            <i class="fa-solid fa-lock me-2"></i>Tạm Thời Khóa Tạo Nhóm
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── CHUYỂN ĐỔI TAB TRONG WORKSPACE ──
function switchWorkspaceTab(tabId) {
    const triggerEl = document.getElementById(tabId + '-tab');
    if (triggerEl) {
        const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
        tab.show();
        triggerEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// ── DANH SÁCH DỮ LIỆU ĐỂ XỬ LÝ FILTER & MODAL ──
const allHocPhansList = @json($hocPhans ?? []);
// Bản đồ các nhóm sinh viên hiện đang tham gia: dạng chuỗi "MaHocKy_MaHocPhan"
const userJoinedGroupKeys = @json(isset($sinhVienAllGroups) ? $sinhVienAllGroups->filter(fn($g) => $g->nhom)->map(fn($g) => ($g->nhom->MaHocKy ?? '') . '_' . ($g->nhom->MaHocPhan ?? ''))->values() : []);

function populateFilterHocPhans(selectedBm, currentHpVal) {
    const filterHocPhan = document.getElementById('filter_hoc_phan');
    if (!filterHocPhan) return;
    filterHocPhan.innerHTML = '';

    const optAll = document.createElement('option');
    optAll.value = 'ALL';
    optAll.textContent = '-- Tất cả học phần (Xem tất cả nhóm) --';
    if (!currentHpVal || currentHpVal === 'ALL') {
        optAll.selected = true;
    }
    filterHocPhan.appendChild(optAll);

    let filtered = allHocPhansList;
    if (selectedBm && selectedBm !== 'ALL') {
        filtered = allHocPhansList.filter(hp => hp.MaBoMon === selectedBm);
    }

    filtered.forEach(hp => {
        const opt = document.createElement('option');
        opt.value = hp.MaHocPhan;
        opt.textContent = hp.TenHocPhan;
        if (hp.MaHocPhan === currentHpVal) {
            opt.selected = true;
        }
        filterHocPhan.appendChild(opt);
    });
}

function onHocKyFilterChange() {
    applyCascadeFilter();
}

function onHocPhanFilterChange() {
    applyCascadeFilter();
}

function onBoMonChange() {
    const filterBoMon = document.getElementById('filter_bo_mon');
    const selectedBm = filterBoMon ? filterBoMon.value : 'ALL';
    populateFilterHocPhans(selectedBm, null);
    applyCascadeFilter();
}

function applyCascadeFilter() {
    const filterHocKy = document.getElementById('filter_hoc_ky');
    const filterBoMon = document.getElementById('filter_bo_mon');
    const filterHocPhan = document.getElementById('filter_hoc_phan');

    const hk = filterHocKy ? filterHocKy.value : '';
    const bm = filterBoMon ? filterBoMon.value : '';
    const hp = filterHocPhan ? filterHocPhan.value : '';

    const url = new URL("{{ route('sinhvien.nhom.index') }}", window.location.origin);
    if (hk) url.searchParams.set('hoc_ky', hk);
    if (bm) url.searchParams.set('bo_mon', bm);
    if (hp) url.searchParams.set('hoc_phan', hp);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // ── HỖ TRỢ ĐIỀU HƯỚNG TAB TỪ URL HASH HOẶC QUERY PARAMS ──
    const urlParams = new URLSearchParams(window.location.search);
    const targetTab = urlParams.get('tab') || window.location.hash.replace('#', '');
    if (targetTab) {
        switchWorkspaceTab(targetTab);
    }

    // ── KHỞI TẠO OPTIONS BỘ LỌC HỌC PHẦN ──
    populateFilterHocPhans("{{ $selectedBoMon }}", "{{ $selectedHocPhan }}");

    // ── LIVE SEARCH: LỌC TỨC THÌ CÁC THẺ NHÓM TRÊN GIAO DIỆN KHI GÕ TỪ KHÓA ──
    const searchInputs = document.querySelectorAll('.input-search-sync');
    function applyLiveFilter(keyword) {
        const lowerKey = keyword.trim().toLowerCase();
        const cards = document.querySelectorAll('.group-item-card');
        let visibleCount = 0;
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            if (!lowerKey || text.includes(lowerKey)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        const badgeCount = document.getElementById('badge-group-count');
        if (badgeCount) {
            badgeCount.textContent = `Tìm thấy ${visibleCount} nhóm`;
        }
    }

    searchInputs.forEach(inputEl => {
        inputEl.addEventListener('input', function() {
            applyLiveFilter(this.value);
        });
    });

    // ── XỬ LÝ QUY TRÌNH TẠO NHÓM (HỌC KỲ -> HỌC PHẦN) ──
    const hocPhansData = @json($hocPhans ?? []);
    const selectHocKy = document.getElementById('create_group_hocky');
    const selectHocPhan = document.getElementById('create_group_hocphan');
    const btnSubmit = document.getElementById('btn_submit_create_group');
    const helpHocPhan = document.getElementById('create_group_hocphan_help');
    const step2Badge = document.getElementById('step2_badge_status');
    const formCreateGroup = document.getElementById('formCreateGroup');
    const studentBoMon = '{{ str_replace("BM_", "", $sinhVien->getMaBoMon() ?? "") }}';
    const currentFilterHocPhan = '{{ ($selectedHocPhan && $selectedHocPhan !== "ALL") ? $selectedHocPhan : "" }}';

    function checkCanSubmit() {
        if (!selectHocKy || !selectHocPhan) return;
        const currentHk = selectHocKy.value ? selectHocKy.value.trim() : '';
        const currentHp = selectHocPhan.value ? selectHocPhan.value.trim() : '';
        const selectedOpt = selectHocPhan.options[selectHocPhan.selectedIndex];
        const isOptDisabled = selectedOpt ? selectedOpt.disabled : false;
        
        if (btnSubmit) {
            if (currentHk && currentHp && !isOptDisabled) {
                btnSubmit.classList.remove('opacity-75');
            }
        }
    }

    function updateHocPhanByHocKy() {
        if (!selectHocKy || !selectHocPhan) return;
        const currentHk = selectHocKy.value;
        selectHocPhan.innerHTML = '';
        selectHocPhan.classList.remove('is-invalid');

        if (!currentHk) {
            selectHocPhan.disabled = true;
            selectHocPhan.innerHTML = '<option value="">-- Bước 2: Vui lòng chọn học kỳ ở bước 1 trước --</option>';
            if (step2Badge) step2Badge.innerHTML = '<i class="fa-solid fa-lock me-1"></i>Khóa cho đến khi chọn Bước 1';
            if (helpHocPhan) helpHocPhan.innerHTML = '<i class="fa-solid fa-graduation-cap me-1"></i>Học phần: <strong>Khóa luận tốt nghiệp theo Bộ môn</strong> mở trong kỳ này.';
            checkCanSubmit();
            return;
        }

        // Lọc các học phần ĐƯỢC MỞ trong học kỳ đã chọn
        const filtered = hocPhansData.filter(hp => {
            if (hp.hoc_phan_hoc_kies && Array.isArray(hp.hoc_phan_hoc_kies)) {
                return hp.hoc_phan_hoc_kies.some(hphk => hphk.MaHocKy === currentHk && hphk.TrangThai === 'Đang mở');
            }
            return true;
        });

        if (filtered.length === 0) {
            selectHocPhan.disabled = true;
            selectHocPhan.innerHTML = '<option value="">-- Học kỳ này chưa mở học phần khóa luận nào --</option>';
            if (step2Badge) step2Badge.innerHTML = '<span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa mở môn</span>';
            if (helpHocPhan) helpHocPhan.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Học kỳ đã chọn hiện chưa mở đợt đăng ký Khóa luận.</span>';
            checkCanSubmit();
            return;
        }

        selectHocPhan.disabled = false;

        // Phân loại: Môn thuộc Bộ môn của SV vs Môn bộ môn khác
        const myDeptHps = [];
        const otherDeptHps = [];

        filtered.forEach(hp => {
            const cleanHpBm = (hp.MaBoMon || '').replace('BM_', '');
            if (!cleanHpBm || cleanHpBm === studentBoMon) {
                myDeptHps.push(hp);
            } else {
                otherDeptHps.push(hp);
            }
        });

        // Placeholder option
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = '-- Nhấp để chọn học phần (' + filtered.length + ' môn mở) --';
        selectHocPhan.appendChild(defaultOpt);

        let autoSelectedHp = null;

        // Nhóm 1: Môn thuộc bộ môn chuyên ngành của sinh viên (ƯU TIÊN HÀNG ĐẦU)
        if (myDeptHps.length > 0) {
            const groupMy = document.createElement('optgroup');
            groupMy.label = '⭐ Học phần chuyên ngành của bạn (' + (studentBoMon || 'Chính thức') + ')';
            
            myDeptHps.forEach(hp => {
                const opt = document.createElement('option');
                opt.value = hp.MaHocPhan;
                const groupKey = currentHk + '_' + hp.MaHocPhan;
                const alreadyHas = userJoinedGroupKeys.includes(groupKey);

                if (alreadyHas) {
                    opt.disabled = true;
                    opt.textContent = hp.TenHocPhan + ' (Bạn đã có nhóm môn này)';
                } else {
                    opt.textContent = '⭐ ' + hp.TenHocPhan;
                    // Lựa chọn ưu tiên auto-select:
                    if (!autoSelectedHp) {
                        if (currentFilterHocPhan && currentFilterHocPhan === hp.MaHocPhan) {
                            autoSelectedHp = hp;
                        } else if (hp.MaHocPhan.includes('HP_KLTN') || hp.TenHocPhan.toLowerCase().includes('khóa luận tốt nghiệp')) {
                            autoSelectedHp = hp;
                        }
                    }
                }
                groupMy.appendChild(opt);
            });
            selectHocPhan.appendChild(groupMy);
            
            // Nếu chưa tìm thấy KLTN thì lấy môn đầu tiên khả dụng của bộ môn
            if (!autoSelectedHp) {
                autoSelectedHp = myDeptHps.find(hp => !userJoinedGroupKeys.includes(currentHk + '_' + hp.MaHocPhan));
            }
        }

        // Nhóm 2: Các môn thuộc bộ môn khác
        if (otherDeptHps.length > 0) {
            const groupOther = document.createElement('optgroup');
            groupOther.label = '⚠️ Học phần chuyên ngành khác (Chỉ dành cho BM tương ứng)';
            otherDeptHps.forEach(hp => {
                const opt = document.createElement('option');
                opt.value = hp.MaHocPhan;
                opt.disabled = true;
                opt.textContent = hp.TenHocPhan + ' (Khác chuyên ngành - BM ' + (hp.MaBoMon || '').replace('BM_', '') + ')';
                groupOther.appendChild(opt);
            });
            selectHocPhan.appendChild(groupOther);
        }

        // Tự động chọn sẵn môn phù hợp để SV không bị chặn hay bỡ ngỡ
        if (autoSelectedHp) {
            selectHocPhan.value = autoSelectedHp.MaHocPhan;
            if (step2Badge) {
                step2Badge.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Sẵn sàng tạo nhóm</span>';
            }
            if (helpHocPhan) {
                helpHocPhan.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Đã tự động chọn: <strong>' + autoSelectedHp.TenHocPhan + '</strong>. Bấm nút bên dưới để hoàn tất tạo nhóm.</span>';
            }
        } else {
            if (step2Badge) {
                step2Badge.innerHTML = '<span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa chọn môn</span>';
            }
            if (helpHocPhan) {
                helpHocPhan.innerHTML = '<span class="text-muted"><i class="fa-solid fa-hand-pointer me-1"></i>Vui lòng nhấp vào danh sách để chọn học phần khóa luận.</span>';
            }
        }

        checkCanSubmit();
    }

    if (selectHocKy && selectHocPhan) {
        selectHocKy.addEventListener('change', updateHocPhanByHocKy);
        selectHocPhan.addEventListener('change', function() {
            selectHocPhan.classList.remove('is-invalid');
            const selectedOpt = selectHocPhan.options[selectHocPhan.selectedIndex];
            if (selectHocPhan.value && !selectedOpt?.disabled) {
                if (step2Badge) step2Badge.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Đã chọn học phần</span>';
                if (helpHocPhan) helpHocPhan.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Học phần: <strong>' + selectedOpt.text.replace('⭐ ', '') + '</strong></span>';
            } else {
                if (step2Badge) step2Badge.innerHTML = '<span class="text-warning"><i class="fa-solid fa-circle-exclamation me-1"></i>Chưa chọn môn</span>';
                if (helpHocPhan) helpHocPhan.innerHTML = '<span class="text-danger"><i class="fa-solid fa-hand-pointer me-1"></i>Vui lòng chọn 1 học phần hợp lệ.</span>';
            }
            checkCanSubmit();
        });
        if (selectHocKy.value) {
            updateHocPhanByHocKy();
        }
    }

    // Đảm bảo khi mở Modal thì luôn kích hoạt kiểm tra và chọn sẵn môn
    const modalCreateGroup = document.getElementById('modalCreateGroup');
    if (modalCreateGroup) {
        modalCreateGroup.addEventListener('show.bs.modal', function() {
            if (selectHocKy && selectHocKy.value) {
                updateHocPhanByHocKy();
            }
        });
    }

    // Xử lý gửi Form Tạo Nhóm với phản hồi tương tác rõ ràng
    if (formCreateGroup) {
        formCreateGroup.addEventListener('submit', function(e) {
            if (!selectHocKy || !selectHocKy.value) {
                e.preventDefault();
                selectHocKy.focus();
                selectHocKy.classList.add('is-invalid');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Chưa chọn học kỳ',
                        text: 'Vui lòng chọn học kỳ áp dụng ở Bước 1.',
                        confirmButtonText: 'Đã hiểu'
                    });
                } else {
                    alert('Vui lòng chọn học kỳ áp dụng ở Bước 1.');
                }
                return false;
            }

            if (!selectHocPhan || !selectHocPhan.value) {
                e.preventDefault();
                selectHocPhan.focus();
                selectHocPhan.classList.add('is-invalid');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Chưa chọn học phần',
                        text: 'Vui lòng nhấp vào Bước 2 để chọn một học phần khóa luận phù hợp với chuyên ngành của bạn.',
                        confirmButtonText: 'Đã hiểu'
                    });
                } else {
                    alert('Vui lòng chọn một học phần khóa luận ở Bước 2 để tạo nhóm.');
                }
                return false;
            }

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang Khởi Tạo Nhóm...';
            }
        });
    }

    // ── XỬ LÝ TRA CỨU & STUDENT VERIFICATION CARD ──
    const inputMSSVSearch = document.getElementById('inputMSSVSearch');
    const btnDoSearch = document.getElementById('btnDoSearch');
    const stepVerificationCard = document.getElementById('stepVerificationCard');
    const btnBackSearch = document.getElementById('btnBackSearch');
    const searchErrorMessage = document.getElementById('searchErrorMessage');
    const btnSubmitInvite = document.getElementById('btnSubmitInvite');
    const hiddenMaSV = document.getElementById('hiddenMaSV');

    if (btnDoSearch && inputMSSVSearch) {
        function executeSearch() {
            const mssv = inputMSSVSearch.value.trim();
            if (!mssv) {
                showError('Vui lòng nhập Mã số sinh viên (MSSV).');
                return;
            }

            hideError();
            btnDoSearch.disabled = true;
            btnDoSearch.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang Tra Cứu...';

            fetch(`{{ route('sinhvien.nhom.traCuuSinhVien') }}?mssv=${encodeURIComponent(mssv)}&ma_nhom={{ $nhomCurrent->MaNhom ?? '' }}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        showError(data.message || 'Không tìm thấy sinh viên.');
                        stepVerificationCard.classList.add('d-none');
                        return;
                    }

                    const sv = data.student;
                    document.getElementById('cardHoTen').textContent = sv.HoTen;
                    document.getElementById('cardMSSV').textContent = sv.MSSV;
                    document.getElementById('cardLop').textContent = sv.TenLop;
                    document.getElementById('cardNganh').textContent = sv.TenNganh;
                    document.getElementById('cardStatusText').textContent = data.status_text;
                    
                    const badge = document.getElementById('cardBadgeStatus');
                    badge.className = `badge rounded-pill ${data.badge_class}`;
                    badge.textContent = data.can_invite ? 'Có Thể Mời' : 'Không Thể Mời';

                    if (hiddenMaSV) {
                        hiddenMaSV.value = sv.MaSV;
                    }

                    if (btnSubmitInvite) {
                        if (data.can_invite) {
                            btnSubmitInvite.classList.remove('d-none');
                            if (data.is_join_request) {
                                btnSubmitInvite.innerHTML = '<i class="fa-solid fa-check me-1"></i>Xác Nhận & Duyệt Vào Nhóm';
                            } else {
                                btnSubmitInvite.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận & Gửi Lời Mời';
                            }
                        } else {
                            btnSubmitInvite.classList.add('d-none');
                        }
                    }

                    stepVerificationCard.classList.remove('d-none');
                })
                .catch(err => {
                    console.error(err);
                    showError('Có lỗi xảy ra trong quá trình tra cứu.');
                })
                .finally(() => {
                    btnDoSearch.disabled = false;
                    btnDoSearch.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Tra Cứu';
                });
        }

        function showError(msg) {
            if (searchErrorMessage) {
                searchErrorMessage.textContent = msg;
                searchErrorMessage.classList.remove('d-none');
            }
        }

        function hideError() {
            if (searchErrorMessage) {
                searchErrorMessage.classList.add('d-none');
            }
        }

        btnDoSearch.addEventListener('click', executeSearch);
        inputMSSVSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                executeSearch();
            }
        });

        if (btnBackSearch) {
            btnBackSearch.addEventListener('click', function() {
                stepVerificationCard.classList.add('d-none');
                inputMSSVSearch.focus();
            });
        }
    }

    // ── XỬ LÝ XEM CHI TIẾT NHÓM (MODAL) ──
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
                    if (document.getElementById('groupDetailHocKy')) {
                        document.getElementById('groupDetailHocKy').textContent = nhom.TenHocKy || 'Chưa xác định';
                    }
                    if (document.getElementById('groupDetailHocPhan')) {
                        document.getElementById('groupDetailHocPhan').textContent = nhom.TenHocPhan || 'Khóa luận cử nhân';
                    }
                    document.getElementById('groupDetailCount').textContent = `${nhom.SoLuong} / 3 thành viên`;

                    const badgeArea = document.getElementById('groupDetailStatusBadge');
                    if (nhom.DaDu) {
                        badgeArea.innerHTML = `<span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fa-solid fa-lock me-1"></i>Đã Đủ Thành Viên</span>`;
                    } else {
                        badgeArea.innerHTML = `<span class="badge bg-success rounded-pill px-3 py-1"><i class="fa-solid fa-user-plus me-1"></i>Đang Tuyển Thành Viên</span>`;
                    }

                    const membersList = document.getElementById('groupDetailMembersList');
                    membersList.innerHTML = '';

                    data.thanhViens.forEach(tv => {
                        const item = document.createElement('div');
                        item.className = 'p-3 rounded-3 border ' + (tv.IsLeader ? 'bg-warning-subtle border-warning' : 'bg-light');
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-bold text-slate-800">
                                    ${tv.IsLeader ? '<i class="fa-solid fa-crown text-warning me-1"></i>' : '<i class="fa-solid fa-user text-secondary me-1"></i>'}
                                    ${tv.HoTen}
                                </div>
                                <span class="badge ${tv.IsLeader ? 'bg-warning text-dark' : 'bg-secondary'} rounded-pill">${tv.VaiTro}</span>
                            </div>
                            <div class="text-muted" style="font-size: 13px;">
                                MSSV: <code>${tv.MSSV}</code> | Lớp: ${tv.TenLop} | Ngành: ${tv.TenNganh}
                            </div>
                        `;
                        membersList.appendChild(item);
                    });

                    const slotMsg = document.getElementById('groupDetailSlotMessage');
                    if (nhom.DaDu) {
                        slotMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>Nhóm đã đạt số lượng tối đa 3 thành viên.`;
                    } else {
                        slotMsg.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i>Nhóm hiện còn <strong>${nhom.ConSlot} vị trí</strong> tuyển thêm.`;
                    }

                    const actionArea = document.getElementById('groupDetailActionArea');
                    const userStatus = data.userStatus;

                    if (userStatus.isMyGroup) {
                        actionArea.innerHTML = `<span class="badge bg-secondary text-white px-3 py-2">Đây là nhóm của bạn</span>`;
                    } else if (userStatus.hasRequested) {
                        actionArea.innerHTML = `
                            <form action="{{ url('sinhvien/nhom') }}/${nhom.MaNhom}/huy-xin-gia-nhap" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-clock me-1"></i>Hủy Yêu Cầu Đã Gửi
                                </button>
                            </form>
                        `;
                    } else if (userStatus.hasGroup) {
                        actionArea.innerHTML = `<span class="badge bg-secondary px-3 py-2">Bạn đã thuộc một nhóm môn này</span>`;
                    } else if (userStatus.isSameMajor === false) {
                        actionArea.innerHTML = `<span class="badge bg-danger text-white px-3 py-2"><i class="fa-solid fa-ban me-1"></i>Khác ngành đào tạo</span>`;
                    } else if (!nhom.DaDu) {
                        actionArea.innerHTML = `
                            <form action="{{ url('sinhvien/nhom') }}/${nhom.MaNhom}/xin-gia-nhap" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fa-solid fa-hand me-1"></i>Xin Gia Nhập Nhóm
                                </button>
                            </form>
                        `;
                    } else {
                        actionArea.innerHTML = '';
                    }

                    groupDetailModal.show();
                })
                .catch(err => console.error(err));
        });
    });

    // ── XỬ LÝ XEM PROFILE CHI TIẾT SINH VIÊN ──
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
                        document.getElementById('profileModalPhone').textContent = sv.SoDienThoai || 'Chưa cập nhật';
                        document.getElementById('profileModalGroupStatus').innerHTML = `<span class="badge ${data.badge_class}">${data.status_text}</span>`;
                        profileModal.show();
                    }
                })
                .catch(err => console.error(err));
        });
    });
});
</script>
@endpush

@include('partials.modal_preview_decuong')
@endsection