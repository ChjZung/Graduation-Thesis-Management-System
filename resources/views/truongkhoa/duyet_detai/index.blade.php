@extends('layouts.truongkhoa')

@section('page_title', 'Quản Lý & Thống Kê Đề Tài Khóa Luận')

@push('styles')
<style>
    /* Chuẩn giao diện Admin: flat, không box-shadow, font tối thiểu 13px */
    body, .admin-table, .form-select, .form-control, .btn {
        font-size: 13.5px;
    }
    .kpi-card-admin {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem 1.15rem;
        height: 100%;
        box-shadow: none !important;
    }
    .kpi-card-admin .kpi-label {
        font-size: 13px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .kpi-card-admin .kpi-number {
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .kpi-card-admin .kpi-subtext {
        font-size: 13px;
        color: #64748b;
        margin-top: 0.25rem;
    }
    .admin-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: none !important;
    }
    .admin-table-header {
        padding: 12px 18px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .admin-table-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .admin-table {
        width: 100%;
        margin-bottom: 0;
    }
    .admin-table thead th {
        background-color: #003366 !important;
        color: #ffffff !important;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 12px !important;
        white-space: nowrap;
    }
    .admin-table tbody td {
        padding: 10px 12px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #1e293b;
    }
    .badge-code {
        background: #f1f5f9;
        color: #0f172a;
        font-family: monospace;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
    }
    #deTaiMainTabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
    }
    #deTaiMainTabs .nav-link {
        color: #475569;
        font-weight: 700;
        font-size: 13.5px;
        padding: 8px 24px;
        border-radius: 999px;
        border: none;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }
    #deTaiMainTabs .nav-link:hover {
        color: #002855;
        background: rgba(255, 255, 255, 0.6);
    }
    #deTaiMainTabs .nav-link.active {
        background: #002855;
        color: #ffffff;
        box-shadow: none !important;
    }
    .stat-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        box-shadow: none !important;
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
        box-shadow: none !important;
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
            Quản Lý &amp; Phê Duyệt Đề Tài Khóa Luận
        </h4>
        <p class="text-muted small mb-0">
            Theo dõi tiến độ xét duyệt chuyên môn theo bộ môn thuộc {{ $khoa->TenKhoa ?? 'Khoa' }} và phân tích báo cáo thống kê theo học kỳ.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        {{-- Nút Xuất Báo Cáo Thống Kê --}}
        <a href="{{ route('truongkhoa.thongke.detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-file-excel me-2"></i> Xuất Báo Cáo Thống Kê
        </a>

        {{-- Nút Xuất File CSV/Excel Danh Sách Đề Tài --}}
        <a href="{{ route('truongkhoa.duyet_detai.export', request()->all()) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-download me-2"></i> Xuất Danh Sách Đề Tài
        </a>
    </div>
</div>

{{-- ── TAB CHUYỂN ĐỔI: QUẢN LÝ vs THỐNG KÊ ── --}}
<div class="mb-4">
    <ul class="nav nav-pills d-inline-flex" id="deTaiMainTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'quanly') !== 'thongke' ? 'active' : '' }} text-nowrap" 
                    id="tab-quanly-btn" data-bs-toggle="pill" data-bs-target="#pane-quanly" type="button" role="tab" aria-controls="pane-quanly" aria-selected="{{ request('tab', 'quanly') !== 'thongke' ? 'true' : 'false' }}">
                <i class="fa-solid fa-list-check me-2"></i>Quản Lý Bộ Môn &amp; Phê Duyệt
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
    {{-- TAB 1: DANH SÁCH BỘ MÔN VÀ PHÊ DUYỆT ĐỀ TÀI CẤP KHOA (P3.2)         --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab', 'quanly') !== 'thongke' ? 'show active' : '' }}" id="pane-quanly" role="tabpanel" aria-labelledby="tab-quanly-btn">

        {{-- ── 5 THẺ THỐNG KÊ PHÍA TRÊN (CHUẨN P3.2) ── --}}
        <div class="row g-3 mb-4">
            <!-- 1. Tổng số đề tài chờ phê duyệt cấp khoa -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl">
                <div class="kpi-card-admin {{ $summaryKPIs['cho_duyet_khoa'] > 0 ? 'border-warning border-opacity-50' : '' }}" style="{{ $summaryKPIs['cho_duyet_khoa'] > 0 ? 'background: #fffdf5;' : '' }}">
                    <div class="kpi-label {{ $summaryKPIs['cho_duyet_khoa'] > 0 ? 'text-warning-emphasis' : '' }}">Đề tài chờ duyệt cấp khoa</div>
                    <div class="kpi-number {{ $summaryKPIs['cho_duyet_khoa'] > 0 ? 'text-warning-emphasis' : 'text-dark' }}">
                        {{ $summaryKPIs['cho_duyet_khoa'] }}
                    </div>
                    <div class="kpi-subtext">
                        @if($summaryKPIs['cho_duyet_khoa'] > 0)
                            <span class="text-danger fw-semibold">Cần thẩm định &amp; phê duyệt</span>
                        @else
                            <span>Đã xét duyệt hết</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Tổng số bộ môn -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl">
                <div class="kpi-card-admin">
                    <div class="kpi-label">Tổng số bộ môn</div>
                    <div class="kpi-number text-dark">{{ $summaryKPIs['tong_bo_mon'] }}</div>
                    <div class="kpi-subtext">Trực thuộc {{ $khoa->TenKhoa ?? 'Khoa' }}</div>
                </div>
            </div>

            <!-- 3. Số giảng viên tham gia đề xuất -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl">
                <div class="kpi-card-admin">
                    <div class="kpi-label">Giảng viên đề xuất</div>
                    <div class="kpi-number text-dark">{{ $summaryKPIs['so_gv_de_xuat'] }}</div>
                    <div class="kpi-subtext">Giảng viên có đề tài trong kỳ</div>
                </div>
            </div>

            <!-- 4. Số đề tài đã được phê duyệt -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl">
                <div class="kpi-card-admin">
                    <div class="kpi-label text-success">Đề tài đã phê duyệt</div>
                    <div class="kpi-number text-success">{{ $summaryKPIs['so_detai_da_duyet'] }}</div>
                    <div class="kpi-subtext">Đã thông qua / Công bố</div>
                </div>
            </div>

            <!-- 5. Số trường hợp cần xem xét -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl">
                <div class="kpi-card-admin {{ $summaryKPIs['so_truong_hop_can_xem_xet'] > 0 ? 'border-danger border-opacity-50' : '' }}">
                    <div class="kpi-label {{ $summaryKPIs['so_truong_hop_can_xem_xet'] > 0 ? 'text-danger' : '' }}">Cần xem xét</div>
                    <div class="kpi-number {{ $summaryKPIs['so_truong_hop_can_xem_xet'] > 0 ? 'text-danger' : 'text-dark' }}">
                        {{ $summaryKPIs['so_truong_hop_can_xem_xet'] }}
                    </div>
                    <div class="kpi-subtext">
                        @if($summaryKPIs['so_truong_hop_can_xem_xet'] > 0)
                            <span class="text-danger fw-semibold">Cần sửa, từ chối hoặc vượt chỉ tiêu</span>
                        @else
                            <span>Không có trường hợp lỗi</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── KHU VỰC TÌM KIẾM VÀ LỌC (Đồng bộ tỷ lệ và mẫu màu #1e3a8a chuẩn Admin) ── --}}
        <div class="admin-filter-bar mb-4 p-3 bg-white border rounded-3 shadow-sm">
            <form method="GET" action="{{ route('truongkhoa.duyet_detai.index') }}" class="w-100">
                <input type="hidden" name="tab" value="quanly">

                <div class="d-flex flex-column gap-2">
                    <!-- Hàng 1: Ô tìm kiếm -->
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 bg-light" 
                               placeholder="Nhập tên bộ môn hoặc mã bộ môn..." 
                               value="{{ request('search') }}">
                        <button class="btn px-4 fw-semibold text-white" type="submit" style="background-color: #1e3a8a;">Tìm</button>
                    </div>

                    <!-- Hàng 2: Các ô lọc dữ liệu -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select name="MaHocKy" class="form-select form-select-sm" style="width: auto; min-width: 200px; flex: 1;" onchange="this.form.submit()">
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} {{ $hk->TrangThai == 'Đang diễn ra' ? '(Hiện tại)' : '' }}
                                </option>
                            @endforeach
                        </select>

                        <select name="tinh_trang_chi_tieu" class="form-select form-select-sm" style="width: auto; min-width: 200px; flex: 1;" onchange="this.form.submit()">
                            <option value="tat_ca">-- Tất cả tình trạng chỉ tiêu --</option>
                            <option value="con_chi_tieu" {{ request('tinh_trang_chi_tieu') == 'con_chi_tieu' ? 'selected' : '' }}>Còn chỉ tiêu</option>
                            <option value="het_chi_tieu" {{ request('tinh_trang_chi_tieu') == 'het_chi_tieu' ? 'selected' : '' }}>Hết chỉ tiêu</option>
                            <option value="vuot_chi_tieu" {{ request('tinh_trang_chi_tieu') == 'vuot_chi_tieu' ? 'selected' : '' }}>Vượt chỉ tiêu</option>
                        </select>

                        @if(request()->anyFilled(['search', 'tinh_trang_chi_tieu']))
                            <a href="{{ route('truongkhoa.duyet_detai.index', ['MaHocKy' => $selectedHocKy, 'tab' => 'quanly']) }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                                <i class="fa-solid fa-xmark me-2"></i>Xóa lọc
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── BẢNG DANH SÁCH BỘ MÔN THUỘC KHOA (CHUẨN P3.2) ── --}}
        <div class="admin-table-card mb-4">
            <div class="admin-table-header">
                <h5 class="admin-table-title">
                    Danh Sách Bộ Môn Trực Thuộc Khoa
                </h5>
                <span class="text-muted small">
                    Hiển thị <strong>{{ $filteredBoMonRows->count() }}</strong> bộ môn
                </span>
            </div>

            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="width: 250px;">Tên Bộ Môn</th>
                            <th class="text-center" style="width: 170px;">Giảng Viên Đề Xuất</th>
                            <th class="text-center" style="width: 140px;">Tổng Số Đề Tài</th>
                            <th class="text-center" style="width: 170px;">Chờ Duyệt Cấp Khoa</th>
                            <th class="text-center" style="width: 190px;">Tình Trạng Chỉ Tiêu</th>
                            <th class="text-center" style="width: 180px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filteredBoMonRows as $index => $row)
                        @php
                            $badgeChiTieu = match($row['tinh_trang_chi_tieu']) {
                                'vuot_chi_tieu' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'het_chi_tieu'  => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                default         => 'bg-success-subtle text-success border border-success-subtle',
                            };
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $row['TenBoMon'] }}</div>
                                <div class="mt-1">
                                    <span class="badge-code">{{ $row['MaBoMon'] }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold text-dark">{{ $row['so_gv_de_xuat'] }}</span>
                                <span class="text-muted small">/ {{ $row['tong_so_gv'] }} GV</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark fs-6">{{ $row['tong_detai'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($row['cho_duyet_khoa'] > 0)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fw-bold" style="font-size: 13px;">
                                        {{ $row['cho_duyet_khoa'] }} đề tài
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 13px;">
                                        0
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="small fw-semibold text-dark mb-1">
                                    {{ $row['da_dung_chi_tieu'] }}/{{ $row['tong_chi_tieu'] }} chỉ tiêu
                                </div>
                                <span class="badge {{ $badgeChiTieu }} px-2 py-1" style="font-size: 12.5px;">
                                    {{ $row['tinh_trang_label'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('truongkhoa.duyet_detai.bomon', ['maBoMon' => $row['MaBoMon'], 'MaHocKy' => $selectedHocKy]) }}" 
                                   class="btn btn-sm btn-outline-primary fw-semibold px-3 py-1">
                                    <i class="fa-solid fa-list me-2"></i> Danh sách đề tài
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <h6 class="fw-semibold">Không tìm thấy bộ môn nào phù hợp với bộ lọc hiện tại!</h6>
                                <p class="small text-muted mb-0">Thử thay đổi từ khóa tìm kiếm hoặc chọn tất cả tình trạng chỉ tiêu.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: THỐNG KÊ ĐỀ TÀI KHÓA LUẬN TOÀN KHOA                         --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab') === 'thongke' ? 'show active' : '' }}" id="pane-thongke" role="tabpanel" aria-labelledby="tab-thongke-btn">

        {{-- ── THANH BỘ LỌC THỐNG KÊ ── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('truongkhoa.duyet_detai.index') }}" id="statsFilterForm">
                    <input type="hidden" name="tab" value="thongke">
                    <div class="row g-2 align-items-center">
                        <!-- Học kỳ -->
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-regular fa-calendar me-2"></i>Học kỳ / Năm học</label>
                            <select name="MaHocKy" id="statsSelectHocKy" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bộ môn -->
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-sitemap me-2"></i>Bộ môn</label>
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
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-tags me-2"></i>Lĩnh vực nghiên cứu</label>
                            <select name="LinhVuc" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="">-- Tất cả lĩnh vực --</option>
                                @foreach($linhVucs as $lv)
                                    <option value="{{ $lv }}" {{ request('LinhVuc') == $lv ? 'selected' : '' }}>{{ $lv }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-bold d-inline-flex align-items-center">
                            <i class="fa-solid fa-filter me-2"></i> Lọc Thống Kê
                        </button>
                        <a href="{{ route('truongkhoa.duyet_detai.index', ['tab' => 'thongke']) }}" class="btn btn-sm btn-light border rounded-3 px-3">
                            <i class="fa-solid fa-rotate-left me-2"></i> Đặt Lại
                        </a>
                        <a href="{{ route('truongkhoa.thongke.detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-bold">
                            <i class="fa-solid fa-file-excel me-2"></i> Xuất Báo Cáo Excel
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
                    <h6><i class="fa-solid fa-chart-pie text-primary me-2"></i> Phân Bố Trạng Thái Đề Tài</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartTrangThai"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bar Chart: Số lượng đề tài theo bộ môn -->
            <div class="col-12 col-lg-7">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-chart-column text-success me-2"></i> Số Lượng Đề Tài Theo Bộ Môn</h6>
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
                    <h6><i class="fa-solid fa-bars-staggered text-info me-2"></i> Phân Bố Đề Tài Theo Lĩnh Vực Nghiên Cứu</h6>
                    <div style="height: 260px; position: relative;">
                        <canvas id="chartLinhVuc"></canvas>
                    </div>
                </div>
            </div>

            <!-- Registration Progress Breakdown -->
            <div class="col-12 col-lg-5">
                <div class="chart-box">
                    <h6><i class="fa-solid fa-chart-simple text-warning me-2"></i> Tỷ Lệ Đề Tài Đã Được Nhóm Đăng Ký</h6>
                    <div class="d-flex flex-column gap-3 justify-content-center h-75 pt-2">
                        <div class="reg-stat-pill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fa-solid fa-circle-check text-success me-2"></i> Đã đủ 3 SV / Đủ chỉ tiêu</span>
                                <span class="fw-bold text-success">{{ $dangKyStats['da_du_sv'] }} đề tài</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $pctDu = $dangKyStats['tong_cong_bo'] > 0 ? round(($dangKyStats['da_du_sv'] / $dangKyStats['tong_cong_bo']) * 100) : 0; @endphp
                                <div class="progress-bar bg-success" style="width: {{ $pctDu }}%"></div>
                            </div>
                        </div>

                        <div class="reg-stat-pill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fa-solid fa-user-clock text-warning me-2"></i> Đang đăng ký (còn chỗ)</span>
                                <span class="fw-bold text-warning">{{ $dangKyStats['con_cho'] }} đề tài</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $pctCho = $dangKyStats['tong_cong_bo'] > 0 ? round(($dangKyStats['con_cho'] / $dangKyStats['tong_cong_bo']) * 100) : 0; @endphp
                                <div class="progress-bar bg-warning" style="width: {{ $pctCho }}%"></div>
                            </div>
                        </div>

                        <div class="reg-stat-pill">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fa-solid fa-circle-xmark text-secondary me-2"></i> Chưa có nhóm đăng ký</span>
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
        const boMonData = @json($boMonChartCounts);
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
    }

    @if(request('tab') === 'thongke')
        renderChartsOnce();
    @endif
});
</script>
@endpush
