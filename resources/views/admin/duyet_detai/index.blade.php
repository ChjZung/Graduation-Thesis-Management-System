@extends('layouts.admin')

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
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--card-accent, #0072ce);
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
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-academic tbody td {
        padding: 12px 14px;
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
            <i class="fa-solid fa-book-bookmark text-primary"></i> Quản Lý &amp; Thống Kê Đề Tài Khóa Luận
        </h4>
        <p class="text-muted small mb-0">
            Theo dõi tiến độ xét duyệt chuyên môn, quản lý danh sách, công bố đề tài và phân tích báo cáo thống kê theo học kỳ.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        {{-- Nút Công Bố Đề Tài Đã Duyệt --}}
        <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#publishModal">
            <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Đề Tài Đã Duyệt
        </button>

        {{-- Nút Xuất Báo Cáo Thống Kê --}}
        <a href="{{ route('admin.thongke.detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-file-excel me-1"></i> Xuất Báo Cáo Thống Kê
        </a>

        {{-- Nút Xuất File Excel Danh Sách Đề Tài --}}
        <a href="{{ route('admin.duyet_detai.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-download me-1"></i> Xuất Danh Sách Đề Tài
        </a>
    </div>
</div>

{{-- ── TAB CHUYỂN ĐỔI: QUẢN LÝ vs THỐNG KÊ ── --}}
<div class="mb-4">
    <ul class="nav nav-pills d-inline-flex" id="deTaiMainTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') !== 'thongke' ? 'active' : '' }} text-nowrap" 
                    id="tab-quanly-btn" data-bs-toggle="pill" data-bs-target="#pane-quanly" type="button" role="tab" aria-controls="pane-quanly" aria-selected="{{ request('tab') !== 'thongke' ? 'true' : 'false' }}">
                <i class="fa-solid fa-list-check me-2"></i>Quản Lý &amp; Công Bố Đề Tài
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'thongke' ? 'active' : '' }} text-nowrap" 
                    id="tab-thongke-btn" data-bs-toggle="pill" data-bs-target="#pane-thongke" type="button" role="tab" aria-controls="pane-thongke" aria-selected="{{ request('tab') === 'thongke' ? 'true' : 'false' }}">
                <i class="fa-solid fa-chart-pie me-2"></i>Thống Kê Đề Tài Khóa Luận
            </button>
        </li>
    </ul>
</div>

{{-- ── TAB CONTENT ── --}}
<div class="tab-content" id="deTaiMainTabContent">

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: QUẢN LÝ & CÔNG BỐ ĐỀ TÀI                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab') !== 'thongke' ? 'show active' : '' }}" id="pane-quanly" role="tabpanel" aria-labelledby="tab-quanly-btn">

        {{-- ── KPI SUMMARY TIÊU ĐIỂM (SCOPED THEO HỌC KỲ / BỘ LỌC) ── --}}
        <div class="row g-3 mb-4">
            {{-- Card 1: Tổng đề tài --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card" style="--card-accent: #004a8f;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Tổng Đề Tài (Học Kỳ)</div>
                            <div class="kpi-value">{{ $counts['total'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #e8f1fa; color: #004a8f;">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        <span class="badge bg-light text-dark border"><i class="fa-regular fa-clock me-1 text-primary"></i>HK đang chọn</span>
                        <span>Toàn bộ đề tài trong kỳ</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Đã công bố cho SV --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card" style="--card-accent: #0284c7;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Đã Công Bố Cho Sinh Viên</div>
                            <div class="kpi-value text-primary">{{ $counts['da_cong_bo'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #e0f2fe; color: #0284c7;">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        @php $pctCb = $counts['total'] > 0 ? round(($counts['da_cong_bo'] / $counts['total']) * 100, 1) : 0; @endphp
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">{{ $pctCb }}%</span>
                        <span>Sinh viên được phép đăng ký</span>
                    </div>
                </div>
            </div>

            {{-- Card 3: TK Đã duyệt (Chờ công bố) --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card" style="--card-accent: #10b981;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">TK Đã Duyệt (Chờ Công Bố)</div>
                            <div class="kpi-value text-success">{{ $counts['truong_khoa_da_duyet'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #dcfce7; color: #16a34a;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        @if($counts['truong_khoa_da_duyet'] > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">Sẵn sàng công bố</span>
                        @else
                            <span class="badge bg-light text-muted border">Không có đề tài chờ</span>
                        @endif
                        <span>Đã thông qua cấp Khoa</span>
                    </div>
                </div>
            </div>

            {{-- Card 4: Đang trong luồng duyệt --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-metric-card" style="--card-accent: #f59e0b;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Đang Xét Duyệt Chuyên Môn</div>
                            <div class="kpi-value text-warning">{{ $counts['cho_duyet'] }}</div>
                        </div>
                        <div class="kpi-icon-box" style="background: #fef3c7; color: #d97706;">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="kpi-subtext mt-2">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold">{{ $counts['cho_duyet_bm'] }} BM / {{ $counts['cho_duyet_khoa'] }} Khoa</span>
                        <span>Đang xử lý thẩm định</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PILL TABS TRẠNG THÁI ĐỒNG BỘ (THANH TRẠNG THÁI CHUYÊN NGHIỆP) ── --}}
        <div class="admin-pill-tabs mb-3" role="navigation" aria-label="Bộ lọc trạng thái đề tài">
            {{-- 1. Tất Cả --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'ALL', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai', 'ALL') === 'ALL' ? 'active' : '' }}" title="Xem toàn bộ đề tài trong học kỳ">
                <i class="fa-solid fa-layer-group text-primary {{ request('TrangThai', 'ALL') === 'ALL' ? 'text-white' : '' }}"></i>
                <span>Tất Cả</span>
                <span class="pill-count">{{ $counts['total'] }}</span>
            </a>

            {{-- 2. Chờ BM Duyệt --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Chờ duyệt cấp Bộ môn', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Chờ duyệt cấp Bộ môn' ? 'active' : '' }}" title="Đề tài đang chờ Bộ môn chuyên môn thẩm định">
                <span class="pill-dot" style="background:#f59e0b;"></span>
                <span>Chờ BM Duyệt</span>
                <span class="pill-count">{{ $counts['cho_duyet_bm'] }}</span>
            </a>

            {{-- 3. Đang Phản Biện Đề Cương --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Đang phản biện đề cương', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Đang phản biện đề cương' ? 'active' : '' }}" title="Đề cương đang được phân công thẩm định phản biện">
                <span class="pill-dot" style="background:#06b6d4;"></span>
                <span>Đang Phản Biện</span>
                <span class="pill-count">{{ $counts['dang_phan_bien'] }}</span>
            </a>

            {{-- 4. Chờ Khoa Duyệt --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Chờ duyệt cấp Khoa', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Chờ duyệt cấp Khoa' ? 'active' : '' }}" title="Bộ môn đã thông qua, đang chờ Ban Chủ nhiệm Khoa phê duyệt">
                <span class="pill-dot" style="background:#ef4444;"></span>
                <span>Chờ Khoa Duyệt</span>
                <span class="pill-count">{{ $counts['cho_duyet_khoa'] }}</span>
            </a>

            {{-- 5. Trưởng Khoa Đã Duyệt --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Trưởng khoa đã duyệt', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Trưởng khoa đã duyệt' ? 'active' : '' }}" title="Trưởng khoa đã phê duyệt - Sẵn sàng để công bố cho sinh viên">
                <span class="pill-dot" style="background:#10b981;"></span>
                <span>TK Đã Duyệt</span>
                <span class="pill-count">{{ $counts['truong_khoa_da_duyet'] }}</span>
            </a>

            {{-- 6. Đã Công Bố --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Đã công bố', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Đã công bố' ? 'active' : '' }}" title="Đã công bố chính thức - Sinh viên được phép xem và đăng ký">
                <span class="pill-dot" style="background:#0072ce;"></span>
                <span>Đã Công Bố</span>
                <span class="pill-count">{{ $counts['da_cong_bo'] }}</span>
            </a>

            {{-- 7. Đã Đăng Ký (Có Nhóm 3 SV) --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Đã đăng ký', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Đã đăng ký' ? 'active' : '' }}" title="Đã có nhóm sinh viên đăng ký đề tài (chuẩn 3 SV/nhóm)">
                <span class="pill-dot" style="background:#6366f1;"></span>
                <span>Đã Có Nhóm (3 SV)</span>
                <span class="pill-count">{{ $counts['da_dang_ky'] }}</span>
            </a>

            {{-- 8. Hoàn Thành --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Hoàn thành', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Hoàn thành' ? 'active' : '' }}" title="Đề tài đã hoàn thành bảo vệ và tổng kết điểm">
                <span class="pill-dot" style="background:#334155;"></span>
                <span>Hoàn Thành</span>
                <span class="pill-count">{{ $counts['hoan_thanh'] }}</span>
            </a>

            {{-- 9. Cần Chỉnh Sửa --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Yêu cầu chỉnh sửa', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Yêu cầu chỉnh sửa' ? 'active' : '' }}" title="Hội đồng yêu cầu giảng viên chỉnh sửa đề cương">
                <span class="pill-dot" style="background:#ea580c;"></span>
                <span>Cần Chỉnh Sửa</span>
                <span class="pill-count">{{ $counts['yeu_cau_sua'] }}</span>
            </a>

            {{-- 10. Từ Chối --}}
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Từ chối', 'tab' => 'quanly'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Từ chối' ? 'active' : '' }}" title="Đề tài không được duyệt">
                <span class="pill-dot" style="background:#64748b;"></span>
                <span>Từ Chối</span>
                <span class="pill-count">{{ $counts['tu_choi'] }}</span>
            </a>
        </div>

        {{-- ── THANH BỘ LỌC TÌM KIẾM THỐNG NHẤT ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.duyet_detai.index') }}" class="row g-2 align-items-center">
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
                                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }}) {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Khoa --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-landmark me-1"></i>Khoa:</label>
                        <select name="MaKhoa" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả Khoa --</option>
                            @foreach($khoas as $k)
                                <option value="{{ $k->MaKhoa }}" {{ request('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>{{ $k->TenKhoa }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Bộ môn --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-sitemap me-1"></i>Bộ Môn:</label>
                        <select name="MaBoMon" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả Bộ Môn --</option>
                            @foreach($boMons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>{{ $bm->TenBoMon }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Lĩnh vực --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-tags me-1"></i>Lĩnh Vực:</label>
                        <select name="LinhVuc" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả Lĩnh Vực --</option>
                            @foreach($linhVucs as $lv)
                                <option value="{{ $lv }}" {{ request('LinhVuc') == $lv ? 'selected' : '' }}>{{ $lv }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Môn học phần --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-graduation-cap me-1"></i>Học Phần:</label>
                        <select name="HocPhan" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả Học Phần --</option>
                            @foreach($hocPhans as $hp)
                                <option value="{{ $hp }}" {{ request('HocPhan') == $hp ? 'selected' : '' }}>{{ $hp }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 6. Tìm kiếm & Nút bấm --}}
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
                                <a href="{{ route('admin.duyet_detai.index', ['tab' => 'quanly']) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
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
                        <i class="fa-solid fa-table-list text-primary"></i> Danh Sách Đề Tài Khóa Luận
                    </h5>
                    <div class="text-muted small mt-1">
                        Hiển thị đề tài thuộc <strong>{{ $currentHocKy?->TenHocKy ?? $selectedHocKy }}</strong> &bull; Phân trang chuẩn 5 đề tài/trang
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
                            <th width="20%">Giảng Viên Hướng Dẫn</th>
                            <th width="14%" class="text-center">Đăng Ký SV</th>
                            <th width="12%" class="text-center">Trạng Thái</th>
                            <th width="8%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detais as $idx => $dt)
                        @php
                            $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
                            $regApproved = $dt->phieuDangKys ? $dt->phieuDangKys->where('TrangThai', 'Đã duyệt') : collect();
                            $regNhom = $regApproved->first()?->nhom ?? $dt->phieuDangKys->first()?->nhom;
                            $regSV = 0;
                            if ($regNhom && $regNhom->thanhVienNhoms) {
                                $regSV = $regNhom->thanhVienNhoms->count();
                            } elseif ($regApproved->count() > 0) {
                                $regSV = $regApproved->count();
                            }
                            $percent = min(100, round(($regSV / max(1, $maxSV)) * 100));

                            $statusBadgeClass = match($dt->TrangThai) {
                                'Trưởng khoa đã duyệt'     => 'bg-success-subtle text-success border border-success-subtle',
                                'Đã công bố'               => 'bg-primary-subtle text-primary border border-primary-subtle',
                                'Đã đăng ký'               => 'bg-indigo-subtle text-indigo border border-indigo-subtle',
                                'Chờ duyệt cấp Khoa'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'Chờ duyệt cấp Bộ môn'     => 'bg-warning-subtle text-warning border border-warning-subtle',
                                'Đang phản biện đề cương'  => 'bg-info-subtle text-info border border-info-subtle',
                                'Hoàn thành'               => 'bg-dark-subtle text-dark border border-dark-subtle',
                                'Yêu cầu chỉnh sửa'        => 'bg-warning-subtle text-warning border border-warning-subtle',
                                'Từ chối'                  => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                default                    => 'bg-light text-muted border',
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
                                    @if($dt->giangVien?->boMon?->khoa)
                                        &bull; {{ $dt->giangVien->boMon->khoa->TenKhoa }}
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold {{ $regSV >= $maxSV ? 'text-success' : ($regSV > 0 ? 'text-primary' : 'text-muted') }}" style="font-size: 0.85rem;">
                                    {{ $regSV }} / {{ $maxSV }} SV
                                </div>
                                <div class="progress my-1 mx-auto" style="height: 5px; width: 75px;">
                                    <div class="progress-bar {{ $regSV >= $maxSV ? 'bg-success' : ($regSV > 0 ? 'bg-primary' : 'bg-secondary') }}" 
                                         role="progressbar" style="width: {{ $percent }}%;"></div>
                                </div>
                                @if($regNhom)
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.72rem;" title="Mã & Tên nhóm: {{ $regNhom->MaNhom }}">
                                            <i class="fa-solid fa-users me-1"></i>{{ $regNhom->MaNhom }}
                                        </span>
                                    </div>
                                @else
                                    <div>
                                        <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.68rem;">
                                            Chưa có nhóm
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-2.5 py-1 fw-semibold {{ $statusBadgeClass }}" style="font-size: 0.75rem;">
                                    {{ $dt->TrangThai }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    {{-- Nút xem chi tiết modal --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" 
                                            data-bs-toggle="modal" data-bs-target="#deTaiDetailModal_{{ $dt->MaDeTai }}" title="Xem chi tiết đề tài">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                Không có đề tài nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang Bootstrap 5 chuẩn 5 đề tài/trang --}}
            @if($detais->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Hiển thị <strong>{{ $detais->firstItem() ?? 1 }}–{{ $detais->lastItem() ?? $detais->count() }}</strong> trên tổng số <strong>{{ $detais->total() }}</strong> đề tài
                </div>
                <div>
                    {{ $detais->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: THỐNG KÊ ĐỀ TÀI KHÓA LUẬN                                  --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab') === 'thongke' ? 'show active' : '' }}" id="pane-thongke" role="tabpanel" aria-labelledby="tab-thongke-btn">

        {{-- ── BỘ LỌC THỐNG KÊ ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route('admin.duyet_detai.index') }}" id="statsFilterForm">
                    <input type="hidden" name="tab" value="thongke">
                    <div class="row g-3 align-items-end">
                        <!-- Học kỳ -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-regular fa-calendar me-1"></i>Học kỳ / Năm học</label>
                            <select name="MaHocKy" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} ({{ $hk->NamHoc }}) {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Khoa -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-landmark me-1"></i>Khoa</label>
                            <select name="MaKhoa" id="statsSelectKhoa" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">-- Tất cả các khoa --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ request('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bộ môn -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-sitemap me-1"></i>Bộ môn</label>
                            <select name="MaBoMon" id="statsSelectBoMon" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">-- Tất cả bộ môn --</option>
                                @foreach($boMons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lĩnh vực -->
                        <div class="col-12 col-sm-6 col-lg-3">
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
                        <a href="{{ route('admin.duyet_detai.index', ['tab' => 'thongke']) }}" class="btn btn-sm btn-light border rounded-3 px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i> Đặt Lại
                        </a>
                        <a href="{{ route('admin.thongke.detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-bold">
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
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-primary">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng Đề Tài</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['total']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="fa-regular fa-folder-open"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Đã công bố -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-info">
                    <div>
                        <div class="text-muted small fw-semibold">Đã Công Bố</div>
                        <div class="fs-4 fw-bold text-info mt-1">{{ number_format($statsKPIs['da_cong_bo']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #e0e7ff; color: #4338ca;">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Đang đăng ký -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-warning">
                    <div>
                        <div class="text-muted small fw-semibold">Đang Đăng Ký</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($statsKPIs['dang_dang_ky']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Đã đủ nhóm -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-success">
                    <div>
                        <div class="text-muted small fw-semibold">Đã Đủ Nhóm</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ number_format($statsKPIs['da_du_nhom']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                </div>
            </div>

            <!-- 5. Đang chờ duyệt -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-secondary">
                    <div>
                        <div class="text-muted small fw-semibold">Đang Chờ Duyệt</div>
                        <div class="fs-4 fw-bold text-secondary mt-1">{{ number_format($statsKPIs['dang_cho_duyet']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f1f5f9; color: #475569;">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- 6. Đã hoàn thành -->
            <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-dark">
                    <div>
                        <div class="text-muted small fw-semibold">Đã Hoàn Thành</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statsKPIs['da_hoan_thanh']) }}</div>
                    </div>
                    <div class="stat-kpi-icon" style="background: #f3e8ff; color: #7e22ce;">
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

            <!-- Bar Chart: Số lượng đề tài theo bộ môn -->
            <div class="col-12 col-lg-7">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-chart-column text-success"></i> Số Lượng Đề Tài Theo Bộ Môn</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartBoMon"></canvas>
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

            <!-- Bar Chart: Quy mô nhóm đăng ký -->
            <div class="col-12 col-lg-5">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-user-group text-warning"></i> Đề Tài Theo Quy Mô Nhóm Sinh Viên</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartQuyMoNhom"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── THỐNG KÊ TÌNH HÌNH ĐĂNG KÝ ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-list-check text-primary"></i> Tình Hình Đăng Ký Đề Tài Của Sinh Viên
                    </h6>
                    <span class="small text-muted">Số liệu tính trên các đề tài mở công bố đăng ký trong học kỳ</span>
                </div>

                <div class="row g-3 text-center">
                    <div class="col-12 col-sm-6 col-md">
                        <div class="reg-stat-pill">
                            <div class="small text-muted fw-semibold mb-1">Đã Công Bố</div>
                            <div class="fs-4 fw-bold text-dark">{{ $dangKyStats['tong_cong_bo'] }}</div>
                            <div class="small text-muted">mở đăng ký cho SV</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="reg-stat-pill">
                            <div class="small text-muted fw-semibold mb-1">Có Nhóm Đăng Ký</div>
                            <div class="fs-4 fw-bold text-primary">{{ $dangKyStats['co_nhom_dk'] }}</div>
                            <div class="small text-primary">đã phát sinh đăng ký</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="reg-stat-pill">
                            <div class="small text-muted fw-semibold mb-1">Còn Chỗ</div>
                            <div class="fs-4 fw-bold text-warning">{{ $dangKyStats['con_cho'] }}</div>
                            <div class="small text-warning">chưa đủ số lượng SV</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="reg-stat-pill">
                            <div class="small text-muted fw-semibold mb-1">Đã Đủ Sinh Viên</div>
                            <div class="fs-4 fw-bold text-success">{{ $dangKyStats['da_du_sv'] }}</div>
                            <div class="small text-success">đã kín chỗ 100%</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="reg-stat-pill">
                            <div class="small text-muted fw-semibold mb-1">Chưa Có SV ĐK</div>
                            <div class="fs-4 fw-bold text-danger">{{ $dangKyStats['chua_co_sv'] }}</div>
                            <div class="small text-danger">cần đẩy mạnh đăng ký</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ── MODAL CHI TIẾT ĐỀ TÀI CHO TỪNG ĐỀ TÀI TRONG DANH SÁCH ── --}}
@foreach($detais as $dt)
<div class="modal fade" id="deTaiDetailModal_{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <div>
                    <span class="badge-code">{{ $dt->MaDeTai }}</span>
                    <span class="badge rounded-pill ms-2 px-2.5 py-1 {{ $statusBadgeClass ?? 'bg-light text-dark' }}">
                        {{ $dt->TrangThai }}
                    </span>
                    <h5 class="modal-title fw-bold text-dark mt-2">{{ $dt->TenDeTai }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 p-3 bg-light rounded-3 mb-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Giảng Viên Hướng Dẫn:</div>
                        <div class="fw-bold text-dark">{{ $dt->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                        <div class="small text-muted">{{ $dt->giangVien?->Email }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Đơn Vị Chuyên Môn:</div>
                        <div class="fw-semibold text-dark">{{ $dt->giangVien?->boMon?->TenBoMon ?? 'N/A' }}</div>
                        <div class="small text-muted">{{ $dt->giangVien?->boMon?->khoa?->TenKhoa }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="fw-bold small text-muted text-uppercase mb-1">Mục Tiêu &amp; Nội Dung Tóm Tắt:</div>
                    <p class="text-dark small mb-0">{{ $dt->MoTa ?? 'Chưa cập nhật nội dung tóm tắt.' }}</p>
                </div>

                <div class="mb-3">
                    <div class="fw-bold small text-muted text-uppercase mb-1">Yêu Cầu Kỹ Thuật / Nền Tảng:</div>
                    <p class="text-dark small mb-0">{{ $dt->YeuCau ?? 'Không có yêu cầu kỹ thuật đặc biệt.' }}</p>
                </div>

                {{-- THÔNG TIN QUY MÔ & NHÓM SINH VIÊN THỰC HIỆN --}}
                @php
                    $mRegApproved = $dt->phieuDangKys ? $dt->phieuDangKys->where('TrangThai', 'Đã duyệt') : collect();
                    $mRegNhom = $mRegApproved->first()?->nhom ?? $dt->phieuDangKys->first()?->nhom;
                @endphp
                <div class="card border border-light-subtle rounded-3 p-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold small text-dark text-uppercase">
                            <i class="fa-solid fa-users text-primary me-1"></i> Quy Mô &amp; Nhóm Sinh Viên Thực Hiện:
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                            Quy định: {{ $dt->SoLuongSinhVienToiDa ?? 3 }} SV / đề tài
                        </span>
                    </div>

                    @if($mRegNhom)
                        <div class="p-2.5 bg-white rounded-3 border mb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <span class="text-muted small">Mã nhóm:</span>
                                    <strong class="font-monospace text-primary ms-1 fs-6">{{ $mRegNhom->MaNhom }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted small">Tên nhóm:</span>
                                    <strong class="text-dark ms-1">{{ $mRegNhom->TenNhom }}</strong>
                                </div>
                                <div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i>Đã đủ 3 sinh viên
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($mRegNhom->thanhVienNhoms && $mRegNhom->thanhVienNhoms->count() > 0)
                            <div class="table-responsive bg-white rounded-3 border">
                                <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.82rem;">
                                    <thead class="table-light border-bottom">
                                        <tr>
                                            <th width="8%" class="text-center text-muted">#</th>
                                            <th width="22%">MSSV</th>
                                            <th width="35%">Họ và Tên</th>
                                            <th width="20%">Lớp</th>
                                            <th width="15%" class="text-center">Vai Trò</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mRegNhom->thanhVienNhoms as $mTvIdx => $mTv)
                                            @php
                                                $mSv = $mTv->sinhVien;
                                                $isLeader = str_contains(mb_strtolower($mTv->VaiTro ?? ''), 'trưởng') || ($mSv && $mRegNhom->MaNhom == 'NHOM' . $mSv->MaSV);
                                            @endphp
                                            <tr class="{{ $loop->last ? '' : 'border-bottom border-light' }}">
                                                <td class="text-center text-muted fw-semibold">{{ $mTvIdx + 1 }}</td>
                                                <td><span class="badge-code">{{ $mSv?->MaSV ?? $mTv->MaSV }}</span></td>
                                                <td class="fw-semibold text-dark">{{ $mSv?->HoTen ?? 'Sinh viên ' . ($mTvIdx + 1) }}</td>
                                                <td class="text-muted">{{ $mSv?->lop?->TenLop ?? 'CNTT' }}</td>
                                                <td class="text-center">
                                                    @if($isLeader)
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                                            <i class="fa-solid fa-crown me-1 text-warning"></i>Trưởng nhóm
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                                            Thành viên
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <div class="p-3 bg-white rounded-3 border text-center text-muted small">
                            <i class="fa-regular fa-user me-1 opacity-75"></i> Đề tài hiện tại chưa có nhóm sinh viên đăng ký.
                        </div>
                    @endif
                </div>

                <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i>Tệp Đề Cương Đính Kèm:</div>
                        @if($dt->FileDeCuong)
                            <div class="small text-success mt-1">Tệp đã nộp: {{ basename($dt->FileDeCuong) }}</div>
                        @else
                            <div class="small text-muted mt-1">Giảng viên chưa nộp tệp đề cương mẫu đính kèm.</div>
                        @endif
                    </div>
                    @if($dt->FileDeCuong)
                        @php
                            $filePath = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                            $fileName = basename($dt->FileDeCuong);
                        @endphp
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                    onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($dt->TenDeTai) }}')">
                                <i class="fa-solid fa-eye me-1"></i> Xem Nhanh
                            </button>
                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i> Tải Về
                            </a>
                        </div>
                    @endif
                </div>

                @if($dt->LyDoTuChoi)
                    <div class="mt-3 p-3 bg-danger-subtle rounded-3 border border-danger-subtle text-danger small">
                        <strong class="d-block mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i>Ý Kiến Phản Hồi / Lý Do Từ Chối:</strong>
                        {{ $dt->LyDoTuChoi }}
                    </div>
                @endif
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- ── MODAL CÔNG BỐ ĐỀ TÀI ĐÃ DUYỆT TOÀN KHOA ── --}}
<div class="modal fade" id="publishModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.duyet_detai.publish') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bullhorn"></i> Công Bố Đề Tài Khóa Luận Chính Thức
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary fw-semibold">Số đề tài đã duyệt sẵn sàng công bố:</span>
                            <span class="fs-4 fw-bold text-success">{{ $counts['truong_khoa_da_duyet'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Chọn Học Kỳ Áp Dụng:</label>
                        <select name="MaHocKy" class="form-select">
                            <option value="">-- Tất cả đề tài đã duyệt --</option>
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Sau khi công bố, sinh viên đủ điều kiện làm khóa luận sẽ được phép nhìn thấy và đăng ký đề tài trên cổng thông tin sinh viên.
                    </div>
                </div>
                <div class="modal-footer border-top pt-2">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-semibold" {{ ($counts['truong_khoa_da_duyet'] ?? 0) == 0 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Công Bố
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------------------
    // 1. Tab Switching & URL Synchronization
    // -------------------------------------------------------------
    const tabButtons = document.querySelectorAll('#deTaiMainTabs button[data-bs-toggle="pill"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            const tabName = target === '#pane-thongke' ? 'thongke' : 'quanly';
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);

            if (target === '#pane-thongke') {
                renderChartsOnce();
            }
        });
    });

    // -------------------------------------------------------------
    // 2. AJAX Cascading Dropdown: Khoa -> Bộ môn (Stats Tab)
    // -------------------------------------------------------------
    const statsSelectKhoa = document.getElementById('statsSelectKhoa');
    const statsSelectBoMon = document.getElementById('statsSelectBoMon');

    if (statsSelectKhoa && statsSelectBoMon) {
        statsSelectKhoa.addEventListener('change', function () {
            const maKhoa = this.value;
            statsSelectBoMon.innerHTML = '<option value="">-- Đang tải bộ môn... --</option>';

            if (!maKhoa) {
                statsSelectBoMon.innerHTML = '<option value="">-- Tất cả bộ môn --</option>';
                return;
            }

            fetch(`/api/khoa/${maKhoa}/bomons`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Tất cả bộ môn --</option>';
                    data.forEach(bm => {
                        html += `<option value="${bm.MaBoMon}">${bm.TenBoMon}</option>`;
                    });
                    statsSelectBoMon.innerHTML = html;
                })
                .catch(() => {
                    statsSelectBoMon.innerHTML = '<option value="">-- Tất cả bộ môn --</option>';
                });
        });
    }

    // -------------------------------------------------------------
    // 3. Khởi tạo biểu đồ Chart.js
    // -------------------------------------------------------------
    let chartsInitialized = false;

    function renderChartsOnce() {
        if (chartsInitialized) return;
        chartsInitialized = true;

        const statusData = @json($statusChartCounts);
        const boMonData = @json($boMonChartCounts);
        const linhVucData = @json($topLinhVucChartCounts);
        const quyMoData = @json($quyMoNhomChartCounts);

        // A. Donut Chart - Trạng thái đề tài
        const ctxStatus = document.getElementById('chartTrangThai');
        if (ctxStatus) {
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#0284c7', '#4338ca', '#f59e0b', '#334155', '#94a3b8'],
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

        // B. Bar Chart - Bộ môn
        const ctxBoMon = document.getElementById('chartBoMon');
        if (ctxBoMon) {
            new Chart(ctxBoMon, {
                type: 'bar',
                data: {
                    labels: Object.keys(boMonData),
                    datasets: [{
                        label: 'Số lượng đề tài',
                        data: Object.values(boMonData),
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

        // D. Bar Chart - Quy mô nhóm
        const ctxQuyMo = document.getElementById('chartQuyMoNhom');
        if (ctxQuyMo) {
            new Chart(ctxQuyMo, {
                type: 'bar',
                data: {
                    labels: Object.keys(quyMoData),
                    datasets: [{
                        label: 'Số đề tài',
                        data: Object.values(quyMoData),
                        backgroundColor: ['#38bdf8', '#34d399', '#fbbf24'],
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
