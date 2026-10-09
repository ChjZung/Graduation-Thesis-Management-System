@extends('layouts.truongbomon')

@section('page_title', 'Quản Lý & Thống Kê Đề Tài Khóa Luận')

@push('styles')
<style>
    /* ── MAIN TABS SWITCHER ── */
    #deTaiMainTabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
    }
    #deTaiMainTabs .nav-link {
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 8px 24px;
        border-radius: 999px;
        border: none;
        transition: all 0.2s ease;
    }
    #deTaiMainTabs .nav-link:hover {
        color: #002855;
        background: rgba(255, 255, 255, 0.6);
    }
    #deTaiMainTabs .nav-link.active {
        background: #002855;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 40, 85, 0.2);
    }

    /* ── KPI METRIC CARDS ── */
    .kpi-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .kpi-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px -2px rgba(0, 40, 85, 0.08);
    }
    .kpi-metric-card::before {
        display: none !important;
    }
    .kpi-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .kpi-label {
        font-size: 0.73rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 5px;
    }
    .kpi-value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.5px;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .kpi-subtext {
        font-size: 0.77rem;
        color: #64748b;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    /* ── ACADEMIC PILL TABS (SLEEK HORIZONTAL STATUS RIBBON) ── */
    .admin-pill-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        padding: 8px 12px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow-x: auto;
        white-space: nowrap;
        flex-wrap: nowrap;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        -webkit-overflow-scrolling: touch;
    }
    .admin-pill-tabs::-webkit-scrollbar {
        height: 5px;
    }
    .admin-pill-tabs::-webkit-scrollbar-track {
        background: transparent;
    }
    .admin-pill-tabs::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 999px;
    }
    .admin-pill-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 13px;
        font-size: 0.81rem;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.18s ease;
    }
    .admin-pill-tab:hover {
        background: #f1f5f9;
        color: #002855;
        border-color: #cbd5e1;
    }
    .admin-pill-tab.active {
        background: #002855;
        color: #ffffff;
        border-color: #002855;
        box-shadow: 0 2px 6px rgba(0, 40, 85, 0.18);
    }
    .admin-pill-tab .pill-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .admin-pill-tab .pill-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        font-size: 0.73rem;
        font-weight: 700;
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        border: 1px solid #e2e8f0;
        line-height: 1;
    }
    .admin-pill-tab.active .pill-count {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
    }

    /* ── TABLE STYLING ── */
    .badge-code {
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 0.78rem;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }
    .table-academic thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-academic tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.88rem;
    }
    .table-academic tbody tr:hover {
        background-color: #f8fafc;
    }

    /* ── STATS DASHBOARD CARDS & CHARTS ── */
    .stat-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0,0,0,0.06);
    }
    .stat-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .chart-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .chart-box h6 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .reg-stat-pill {
        border-radius: 10px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }
</style>
@endpush

@section('content')

{{-- ── TIÊU ĐỀ TRANG & NÚT THAO TÁC ── --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="page-main-title mb-1 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-stamp text-primary"></i> Quản Lý &amp; Phê Duyệt Đề Tài – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
        </h4>
        <p class="text-muted small mb-0">
            Thẩm định chuyên môn, xem xét ý kiến phản biện đề cương, phê duyệt đề tài và phân tích báo cáo thống kê theo học kỳ.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        {{-- Nút Sang Phân Công Phản Biện --}}
        <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-user-plus me-1"></i> Sang Phân Công Phản Biện
        </a>

        {{-- Nút Sang Duyệt Đề Cương --}}
        <a href="{{ route('truongbomon.duyet_decuong.index') }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-file-circle-check me-1"></i> Sang Duyệt Đề Cương
        </a>

        {{-- Nút Xuất Báo Cáo Thống Kê --}}
        <a href="{{ route('truongbomon.thongke.detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-file-excel me-1"></i> Xuất Báo Cáo Thống Kê
        </a>

        {{-- Nút Xuất File CSV/Excel Danh Sách Đề Tài --}}
        <a href="{{ route('truongbomon.duyet_detai.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-download me-1"></i> Xuất Danh Sách Đề Tài
        </a>
    </div>
</div>

{{-- ── TAB CHUYỂN ĐỔI: QUẢN LÝ vs THỐNG KÊ ── --}}
<div class="mb-4">
    <ul class="nav nav-pills d-inline-flex" id="deTaiMainTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'quanly') !== 'thongke' ? 'active' : '' }} text-nowrap" 
                    id="tab-quanly-btn" data-bs-toggle="pill" data-bs-target="#pane-quanly" type="button" role="tab" aria-controls="pane-quanly" aria-selected="{{ request('tab', 'quanly') !== 'thongke' ? 'true' : 'false' }}">
                <i class="fa-solid fa-list-check me-2"></i>Quản Lý &amp; Phê Duyệt Đề Tài
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'thongke' ? 'active' : '' }} text-nowrap" 
                    id="tab-thongke-btn" data-bs-toggle="pill" data-bs-target="#pane-thongke" type="button" role="tab" aria-controls="pane-thongke" aria-selected="{{ request('tab') === 'thongke' ? 'true' : 'false' }}">
                <i class="fa-solid fa-chart-pie me-2"></i>Thống Kê Số Liệu Bộ Môn
            </button>
        </li>
    </ul>
</div>

{{-- ── TAB CONTENT ── --}}
<div class="tab-content" id="deTaiMainTabContent">

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: QUẢN LÝ & PHÊ DUYỆT ĐỀ TÀI CẤP BỘ MÔN                      --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab', 'quanly') !== 'thongke' ? 'show active' : '' }}" id="pane-quanly" role="tabpanel" aria-labelledby="tab-quanly-btn">

        {{-- ── KPI SUMMARY TIÊU ĐIỂM (SCOPED THEO HỌC KỲ / BỘ LỌC) ── --}}
        <div class="row g-3 mb-4">
            {{-- Card 1: Tổng đề tài Bộ môn --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Tổng Đề Tài (Bộ Môn)</div>
                            <div class="kpi-value">{{ $counts['total'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        <span class="badge bg-light text-dark border"><i class="fa-regular fa-clock me-1 text-primary"></i>HK đang chọn</span>
                        <span>Đề tài thuộc Bộ môn</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Đã công bố cho SV --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Đã Phê Duyệt / Công Bố</div>
                            <div class="kpi-value">{{ $counts['da_cong_bo'] + $counts['truong_khoa_da_duyet'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        @php $pctCb = $counts['total'] > 0 ? round((($counts['da_cong_bo'] + $counts['truong_khoa_da_duyet']) / $counts['total']) * 100, 1) : 0; @endphp
                        <span class="badge bg-light text-primary border fw-semibold">{{ $pctCb }}%</span>
                        <span>{{ $counts['da_cong_bo'] }} đã công bố &bull; {{ $counts['truong_khoa_da_duyet'] }} TK đã duyệt</span>
                    </div>
                </div>
            </div>

            {{-- Card 3: Cần Bộ môn xử lý duyệt --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Cần Bộ Môn Xử Lý Duyệt</div>
                            <div class="kpi-value">{{ $counts['can_duyet'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        @if($counts['can_duyet'] > 0)
                            <span class="badge bg-light text-primary border fw-bold">{{ $counts['da_phan_bien'] }} đã PB xong &bull; {{ $counts['cho_duyet_bm'] }} chờ BM</span>
                        @else
                            <span class="badge bg-light text-muted border">Không có đề tài chờ</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card 4: Đang phản biện đề cương --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Đang Phản Biện Đề Cương</div>
                            <div class="kpi-value">{{ $counts['dang_phan_bien'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        <span class="badge bg-light text-secondary border fw-semibold">Đang thẩm định</span>
                        <span>GV phản biện đang nhận xét</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-pill-tabs mb-4" role="navigation" aria-label="Bộ lọc trạng thái đề tài">
            {{-- 1. Tất Cả --}}
            <a href="{{ route('truongbomon.duyet_detai.index', array_merge(request()->except(['page', 'TrangThai']), ['TrangThai' => 'ALL', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ !request('TrangThai') || request('TrangThai') === 'ALL' ? 'active' : '' }}" title="Xem toàn bộ đề tài của Bộ môn trong học kỳ">
                <i class="fa-solid fa-layer-group {{ !request('TrangThai') || request('TrangThai') === 'ALL' ? 'text-white' : 'text-primary' }}"></i>
                <span>Tất Cả Đề Tài</span>
                <span class="pill-count">{{ $counts['total'] }}</span>
            </a>

            {{-- 2. Đang Xét Duyệt --}}
            <a href="{{ route('truongbomon.duyet_detai.index', array_merge(request()->except(['page', 'TrangThai']), ['TrangThai' => 'dang_xet_duyet', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'dang_xet_duyet' || request('TrangThai') === 'can_duyet' ? 'active' : '' }}" title="Đề tài cần thẩm định, phân công PB hoặc chờ duyệt">
                <span class="pill-dot" style="background:#f59e0b;"></span>
                <span>Đang Xét Duyệt</span>
                <span class="pill-count">{{ $counts['dang_xet_duyet'] }}</span>
            </a>

            {{-- 3. Đã Phê Duyệt & Công Bố --}}
            <a href="{{ route('truongbomon.duyet_detai.index', array_merge(request()->except(['page', 'TrangThai']), ['TrangThai' => 'da_cong_bo', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'da_cong_bo' ? 'active' : '' }}" title="Đề tài đã hoàn tất phê duyệt / công bố cho sinh viên">
                <span class="pill-dot" style="background:#10b981;"></span>
                <span>Đã Công Bố &amp; Đã Duyệt</span>
                <span class="pill-count">{{ $counts['da_cong_bo_tab'] }}</span>
            </a>

            {{-- 4. Cần Chỉnh Sửa / Từ Chối --}}
            <a href="{{ route('truongbomon.duyet_detai.index', array_merge(request()->except(['page', 'TrangThai']), ['TrangThai' => 'tu_choi_can_sua', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'tu_choi_can_sua' ? 'active' : '' }}" title="Đề tài yêu cầu chỉnh sửa hoặc từ chối">
                <span class="pill-dot" style="background:#ef4444;"></span>
                <span>Cần Chỉnh Sửa / Từ Chối</span>
                <span class="pill-count">{{ $counts['tu_choi_can_sua'] }}</span>
            </a>
        </div>

        {{-- ── THANH BỘ LỌC TÌM KIẾM THỐNG NHẤT ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('truongbomon.duyet_detai.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="quanly">
                    @if(request('TrangThai') && request('TrangThai') !== 'ALL')
                        <input type="hidden" name="TrangThai" value="{{ request('TrangThai') }}">
                    @endif

                    {{-- 1. Học kỳ --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-regular fa-calendar me-1"></i>Học Kỳ:</label>
                        <select name="MaHocKy" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Giảng viên đề xuất --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-chalkboard-user me-1"></i>GV Đề Xuất:</label>
                        <select name="MaGV" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả GV trong Bộ môn --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                    {{ $gv->HoTen }} ({{ $gv->MaGV }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Lĩnh vực --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-tags me-1"></i>Lĩnh Vực:</label>
                        <select name="LinhVuc" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả Lĩnh Vực --</option>
                            @foreach($linhVucs as $lv)
                                <option value="{{ $lv }}" {{ request('LinhVuc') == $lv ? 'selected' : '' }}>{{ $lv }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Môn học phần --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-graduation-cap me-1"></i>Học Phần:</label>
                        <select name="HocPhan" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả Học Phần --</option>
                            @foreach($hocPhans as $hp)
                                <option value="{{ $hp }}" {{ request('HocPhan') == $hp ? 'selected' : '' }}>{{ $hp }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Tìm kiếm & Nút bấm --}}
                    <div class="col-12 mt-2">
                        <div class="d-flex flex-column flex-md-row gap-2 align-items-center">
                            <div class="input-group input-group-sm flex-grow-1">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" 
                                       placeholder="Tìm kiếm theo mã đề tài, tên đề tài, tên giảng viên hướng dẫn..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                                    <i class="fa-solid fa-filter me-1"></i> Lọc Dữ Liệu
                                </button>
                                <a href="{{ route('truongbomon.duyet_detai.index', ['tab' => 'quanly']) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                                    <i class="fa-solid fa-rotate me-1"></i> Đặt Lại
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── BẢNG DANH SÁCH ĐỀ TÀI (5 ĐỀ TÀI / 1 TRANG THEO CHUẨN THỐNG NHẤT) ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-table-list text-primary"></i> Danh Sách Đề Tài Khóa Luận – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
                    </h5>
                    <div class="text-muted small mt-1">
                        @php
                            $selectedHkObj = $hocKies->firstWhere('MaHocKy', $selectedHocKy);
                        @endphp
                        Hiển thị đề tài thuộc <strong>{{ $selectedHkObj?->TenHocKy ?? $selectedHocKy }}</strong> &bull; Phân trang chuẩn 5 đề tài/trang
                    </div>
                </div>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                    Tổng: <strong>{{ $detais->total() }}</strong> đề tài
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-academic table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="4%" class="text-center">#</th>
                            <th width="10%">Mã Đề Tài</th>
                            <th width="32%">Tên Đề Tài &amp; Lĩnh Vực</th>
                            <th width="18%">Giảng Viên Đề Xuất</th>
                            <th width="12%" class="text-center">Phản Biện ĐC</th>
                            <th width="12%" class="text-center">Trạng Thái</th>
                            <th width="12%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detais as $idx => $dt)
                        @php
                            $pb = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                            $statusBadgeClass = match($dt->TrangThai) {
                                'Trưởng khoa đã duyệt'                     => 'bg-success-subtle text-success border border-success-subtle',
                                'Trưởng khoa đã duyệt - Chờ nộp đề cương' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                'Đã nộp đề cương - Chờ phân công PB'      => 'bg-info-subtle text-info border border-info-subtle',
                                'Đã công bố'                               => 'bg-primary-subtle text-primary border border-primary-subtle',
                                'Đã đăng ký'                               => 'bg-indigo-subtle text-indigo border border-indigo-subtle',
                                'Chờ duyệt cấp Khoa'                       => 'bg-info-subtle text-info border border-info-subtle',
                                'Chờ duyệt cấp Bộ môn'                     => 'bg-warning-subtle text-warning border border-warning-subtle',
                                'Đã phản biện - Chờ duyệt BM',
                                'Đã phản biện - Chờ TBM duyệt đề cương'    => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'Đang phản biện đề cương'                  => 'bg-info-subtle text-info border border-info-subtle',
                                'Hoàn thành'                               => 'bg-dark-subtle text-dark border border-dark-subtle',
                                'Yêu cầu chỉnh sửa',
                                'Yêu cầu chỉnh sửa đề cương'               => 'bg-warning-subtle text-warning border border-warning-subtle',
                                'Không đạt phản biện',
                                'Từ chối'                                  => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                default                                    => 'bg-light text-muted border',
                            };
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $detais->firstItem() + $idx }}</td>
                            <td>
                                <span class="badge-code">{{ $dt->MaDeTai }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    @if($dt->LinhVuc)
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-tag me-1 text-primary"></i>{{ $dt->LinhVuc }}
                                        </span>
                                    @endif
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                        {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $dt->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                                <div class="text-muted small" style="font-size: 0.78rem;">
                                    {{ $dt->giangVien?->boMon?->TenBoMon ?? 'N/A' }} 
                                    @if($dt->giangVien?->HocVi)
                                        &bull; {{ $dt->giangVien->HocVi }}
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if($pb)
                                    <div class="small fw-semibold text-dark">{{ $pb->giangVien->HoTen ?? 'GV PB' }}</div>
                                    @if($pb->KetQua)
                                        <span class="badge {{ $pb->KetQua === 'Đạt' ? 'bg-success-subtle text-success border border-success-subtle' : ($pb->KetQua === 'Yêu cầu chỉnh sửa' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') }} px-2 py-0.5" style="font-size: 0.72rem;">
                                            {{ $pb->KetQua }}
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5" style="font-size: 0.72rem;">
                                            Đang thẩm định
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.72rem;">
                                        Chưa phân công
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadgeClass }} px-2 py-1" style="font-size: 0.76rem; border-radius: 6px;">
                                    {{ $dt->TrangThai }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                    <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" 
                                        class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap shadow-xs">
                                        <i class="fa-solid fa-stamp me-1"></i> Duyệt Đề Xuất
                                    </a>
                                @elseif(in_array($dt->TrangThai, ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương']))
                                    <a href="{{ route('truongbomon.phancong.index', ['tab' => 'da_phan_bien', 'search' => $dt->MaDeTai]) }}" 
                                        class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold text-nowrap shadow-xs" title="Sang mục Phân công phản biện để duyệt đề cương">
                                        <i class="fa-solid fa-bullhorn me-1"></i> Duyệt ĐC
                                    </a>
                                @else
                                    <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 text-nowrap">
                                        <i class="fa-solid fa-eye me-1"></i> Chi Tiết
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <h6 class="fw-semibold">Không tìm thấy đề tài nào phù hợp với bộ lọc hiện tại!</h6>
                                <p class="small text-muted mb-0">Thử thay đổi học kỳ, giảng viên hoặc từ khóa tìm kiếm.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($detais->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Hiển thị <strong>{{ $detais->firstItem() }}</strong> - <strong>{{ $detais->lastItem() }}</strong> trên tổng <strong>{{ $detais->total() }}</strong> đề tài
                </div>
                <div>
                    {{ $detais->appends(request()->all())->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: THỐNG KÊ ĐỀ TÀI BỘ MÔN                                     --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab') === 'thongke' ? 'show active' : '' }}" id="pane-thongke" role="tabpanel" aria-labelledby="tab-thongke-btn">

        {{-- ── THANH BỘ LỌC THỐNG KÊ ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('truongbomon.duyet_detai.index') }}" id="statsFilterForm">
                    <input type="hidden" name="tab" value="thongke">
                    <div class="row g-2 align-items-center">
                        <!-- Học kỳ -->
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-regular fa-calendar me-1"></i>Học kỳ / Năm học</label>
                            <select name="MaHocKy" id="statsSelectHocKy" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Giảng viên -->
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên đề xuất</label>
                            <select name="MaGV" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">-- Tất cả GV trong Bộ môn --</option>
                                @foreach($giangViens as $gv)
                                    <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                        {{ $gv->HoTen }} ({{ $gv->MaGV }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lĩnh vực -->
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-tags me-1"></i>Lĩnh vực nghiên cứu</label>
                            <select name="LinhVuc" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">-- Tất cả lĩnh vực --</option>
                                @foreach($linhVucs as $lv)
                                    <option value="{{ $lv }}" {{ request('LinhVuc') == $lv ? 'selected' : '' }}>{{ $lv }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-bold">
                            <i class="fa-solid fa-filter me-1"></i> Lọc Thống Kê
                        </button>
                        <a href="{{ route('truongbomon.duyet_detai.index', ['tab' => 'thongke']) }}" class="btn btn-sm btn-light border rounded-3 px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i> Đặt Lại
                        </a>
                        <a href="{{ route('truongbomon.thongke.detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-bold">
                            <i class="fa-solid fa-file-excel me-1"></i> Xuất Báo Cáo Excel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 6 KPI CARDS HỌC THUẬT ── --}}
        <div class="row g-3 mb-4">
            <!-- 1. Tổng đề tài -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng Đề Tài</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['total']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-regular fa-folder-open"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Đã công bố -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border">
                    <div>
                        <div class="text-muted small fw-semibold">Đã Công Bố</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['da_cong_bo']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Đang đăng ký -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border">
                    <div>
                        <div class="text-muted small fw-semibold">Đang Đăng Ký</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['dang_dang_ky']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Đã đủ nhóm -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border">
                    <div>
                        <div class="text-muted small fw-semibold">Đã Đủ Nhóm</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['da_du_nhom']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                </div>
            </div>

            <!-- 5. Đang chờ duyệt -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border">
                    <div>
                        <div class="text-muted small fw-semibold">Đang Chờ Duyệt</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['dang_cho_duyet']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- 6. Đã hoàn thành -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border">
                    <div>
                        <div class="text-muted small fw-semibold">Đã Hoàn Thành</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['da_hoan_thanh']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 4 BIỂU ĐỒ THỐNG KÊ ── --}}
        <div class="row g-3 mb-4">
            <!-- Donut Chart: Phân bố trạng thái đề tài -->
            <div class="col-12 col-lg-5">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-chart-pie text-primary"></i> Phân Bố Trạng Thái Đề Tài</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartTrangThai"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bar Chart: Số lượng đề tài theo giảng viên trong bộ môn -->
            <div class="col-12 col-lg-7">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-chart-column text-success"></i> Số Lượng Đề Tài Theo Giảng Viên</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartGiangVien"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Horizontal Bar: Phân bố đề tài theo lĩnh vực -->
            <div class="col-12 col-lg-7">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-bars-staggered text-info"></i> Phân Bố Đề Tài Theo Lĩnh Vực Nghiên Cứu</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartLinhVuc"></canvas>
                    </div>
                </div>
            </div>

            <!-- Registration Progress Breakdown -->
            <div class="col-12 col-lg-5">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-chart-simple text-warning"></i> Tỷ Lệ Đề Tài Đã Được Nhóm Đăng Ký</h6>
                    <div class="d-flex flex-column gap-3 justify-content-center h-75 pt-2">
                        <div class="reg-stat-pill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fa-solid fa-circle-check text-success me-1"></i> Đã đủ 3 SV / Đủ chỉ tiêu</span>
                                <span class="fw-bold text-success">{{ $dangKyStats['da_du_sv'] }} đề tài</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $pctDu = $dangKyStats['tong_cong_bo'] > 0 ? round(($dangKyStats['da_du_sv'] / $dangKyStats['tong_cong_bo']) * 100) : 0; @endphp
                                <div class="progress-bar bg-success" style="width: {{ $pctDu }}%"></div>
                            </div>
                        </div>

                        <div class="reg-stat-pill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fa-solid fa-user-clock text-warning me-1"></i> Đang đăng ký (còn chỗ)</span>
                                <span class="fw-bold text-warning">{{ $dangKyStats['con_cho'] }} đề tài</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $pctCho = $dangKyStats['tong_cong_bo'] > 0 ? round(($dangKyStats['con_cho'] / $dangKyStats['tong_cong_bo']) * 100) : 0; @endphp
                                <div class="progress-bar bg-warning" style="width: {{ $pctCho }}%"></div>
                            </div>
                        </div>

                        <div class="reg-stat-pill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fa-solid fa-circle-xmark text-secondary me-1"></i> Chưa có nhóm đăng ký</span>
                                <span class="fw-bold text-secondary">{{ $dangKyStats['chua_co_sv'] }} đề tài</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $pctChua = $dangKyStats['tong_cong_bo'] > 0 ? round(($dangKyStats['chua_co_sv'] / $dangKyStats['tong_cong_bo']) * 100) : 0; @endphp
                                <div class="progress-bar bg-secondary" style="width: {{ $pctChua }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Lưu tab vào URL khi chuyển tab
    const tabBtns = document.querySelectorAll('#deTaiMainTabs button[data-bs-toggle="pill"]');
    tabBtns.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            const tabParam = target === '#pane-thongke' ? 'thongke' : 'quanly';
            const url = new URL(window.location);
            url.searchParams.set('tab', tabParam);
            window.history.replaceState({}, '', url);

            if (tabParam === 'thongke') {
                renderChartsOnce();
            }
        });
    });

    let chartsRendered = false;

    function renderChartsOnce() {
        if (chartsRendered) return;
        chartsRendered = true;

        const statusData = @json($statusChartCounts);
        const gvData = @json($giangVienChartCounts);
        const linhVucData = @json($topLinhVucChartCounts);

        // A. Donut Chart - Trạng thái
        const ctxTrangThai = document.getElementById('chartTrangThai');
        if (ctxTrangThai) {
            new Chart(ctxTrangThai, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#0284c7', '#6366f1', '#f59e0b', '#10b981', '#94a3b8'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // B. Bar Chart - Giảng viên
        const ctxGiangVien = document.getElementById('chartGiangVien');
        if (ctxGiangVien) {
            new Chart(ctxGiangVien, {
                type: 'bar',
                data: {
                    labels: Object.keys(gvData),
                    datasets: [{
                        label: 'Số lượng đề tài',
                        data: Object.values(gvData),
                        backgroundColor: '#0284c7',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 11 } }
                        },
                        x: {
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        }

        // C. Horizontal Bar - Lĩnh vực
        const ctxLinhVuc = document.getElementById('chartLinhVuc');
        if (ctxLinhVuc) {
            new Chart(ctxLinhVuc, {
                type: 'bar',
                data: {
                    labels: Object.keys(linhVucData),
                    datasets: [{
                        label: 'Số đề tài',
                        data: Object.values(linhVucData),
                        backgroundColor: '#6366f1',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 11 } }
                        },
                        y: {
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
    }

    @if(request('tab') === 'thongke')
        renderChartsOnce();
    @endif
});
</script>
@endpush
