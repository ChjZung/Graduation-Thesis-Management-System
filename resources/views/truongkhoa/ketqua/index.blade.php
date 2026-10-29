@extends('layouts.truongkhoa')

@section('page_title', 'Tổng Hợp & Công Bố Kết Quả Khóa Luận')

@section('content')
<style>
/* Ẩn triệt để nút x mặc định của webkit/trình duyệt */
input::-webkit-search-decoration,
input::-webkit-search-cancel-button,
input::-webkit-search-results-button,
input::-webkit-search-results-decoration {
    -webkit-appearance: none;
    appearance: none;
    display: none !important;
}

/* Chống vỡ layout và tràn khung */
.text-break-anywhere {
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    min-width: 0 !important;
}

.kpi-card-hover {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.kpi-card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.08) !important;
}

/* ════ VÙNG CUỘN VÀ BẢNG KẾT QUẢ / TIẾN ĐỘ KHÓA LUẬN ════ */
.table-scroll-wrapper {
    position: relative;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
}

/* Thanh cuộn ngang luôn hiện (overflow-x: scroll), rõ ràng ở đáy bảng */
.custom-table-scroll {
    overflow-x: auto !important;
    overflow-x: scroll !important;
    overflow-y: auto !important;
    max-height: 70vh !important;
    max-width: 100% !important;
    -webkit-overflow-scrolling: touch;
    border-bottom: 1px solid #dee2e6;
}

.custom-table-scroll::-webkit-scrollbar {
    height: 11px;
    width: 8px;
}
.custom-table-scroll::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 4px;
}
.custom-table-scroll::-webkit-scrollbar-thumb {
    background: #0072ce;
    border-radius: 4px;
}
.custom-table-scroll::-webkit-scrollbar-thumb:hover {
    background: #00569c;
}

/* Header Bảng Khóa Luận (Nền xanh đậm #0b3a6e, Chữ trắng, Sticky Top) */
#tableToanKhoa thead th,
#tableHoiDong thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 3 !important;
    background-color: #0b3a6e !important;
    color: #ffffff !important;
    border-bottom: 2px solid #082a50 !important;
    border-top: none !important;
    vertical-align: middle !important;
    font-weight: 600 !important;
    letter-spacing: 0.2px;
}

/* Các cột sticky bên trái trong Header: z-index: 4, background #0b3a6e */
#tableToanKhoa thead th.sticky-col-stt,
#tableToanKhoa thead th.sticky-col-masv,
#tableToanKhoa thead th.sticky-col-hoten,
#tableHoiDong thead th.sticky-col-hd-stt,
#tableHoiDong thead th.sticky-col-hd-name {
    z-index: 4 !important;
    background-color: #0b3a6e !important;
    color: #ffffff !important;
}

/* Cố định cột STT + Mã SV + Họ và Tên cho Bảng Toàn Khoa */
#tableToanKhoa .sticky-col-stt {
    position: sticky !important;
    left: 0;
    width: 50px;
    min-width: 50px;
    max-width: 50px;
}
#tableToanKhoa .sticky-col-masv {
    position: sticky !important;
    left: 50px;
    width: 110px;
    min-width: 110px;
    max-width: 110px;
}
#tableToanKhoa .sticky-col-hoten {
    position: sticky !important;
    left: 160px;
    width: 210px;
    min-width: 210px;
    max-width: 210px;
    box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.15);
    border-right: 1px solid #082a50;
}

/* Các cột sticky bên trái trong Body: z-index: 2, background #ffffff */
#tableToanKhoa tbody td.sticky-col-stt,
#tableToanKhoa tbody td.sticky-col-masv,
#tableToanKhoa tbody td.sticky-col-hoten {
    z-index: 2 !important;
    background-color: #ffffff !important;
}
#tableToanKhoa tbody td.sticky-col-hoten {
    box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08);
    border-right: 1px solid #dee2e6;
}

#tableToanKhoa tbody tr:hover td.sticky-col-stt,
#tableToanKhoa tbody tr:hover td.sticky-col-masv,
#tableToanKhoa tbody tr:hover td.sticky-col-hoten {
    background-color: #f1f5f9 !important;
}

/* Cố định cột STT + Tên Hội Đồng cho Bảng Hội Đồng */
#tableHoiDong .sticky-col-hd-stt {
    position: sticky !important;
    left: 0;
    width: 50px;
    min-width: 50px;
    max-width: 50px;
}
#tableHoiDong .sticky-col-hd-name {
    position: sticky !important;
    left: 50px;
    width: 220px;
    min-width: 220px;
    max-width: 220px;
    box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.15);
    border-right: 1px solid #082a50;
}

#tableHoiDong tbody td.sticky-col-hd-stt,
#tableHoiDong tbody td.sticky-col-hd-name {
    z-index: 2 !important;
    background-color: #ffffff !important;
}
#tableHoiDong tbody td.sticky-col-hd-name {
    box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08);
    border-right: 1px solid #dee2e6;
}

#tableHoiDong tbody tr:hover td.sticky-col-hd-stt,
#tableHoiDong tbody tr:hover td.sticky-col-hd-name {
    background-color: #f1f5f9 !important;
}

/* Các ô td luôn có overflow: hidden */
#tableToanKhoa td,
#tableHoiDong td {
    overflow: hidden;
    vertical-align: middle;
}

/* Line clamps chống tràn tuyệt đối với chuỗi ký tự không khoảng trắng */
.line-clamp-1 {
    display: -webkit-box !important;
    -webkit-line-clamp: 1 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    overflow-wrap: anywhere !important;
    word-break: break-all !important;
}
.line-clamp-2 {
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    overflow-wrap: anywhere !important;
    word-break: break-all !important;
    line-height: 1.35 !important;
}
.line-clamp-3 {
    display: -webkit-box !important;
    -webkit-line-clamp: 3 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    overflow-wrap: anywhere !important;
    word-break: break-all !important;
    line-height: 1.35 !important;
}
</style>

<div class="container-fluid px-0" style="padding-bottom: 90px !important;">
    <!-- ════ HEADER & ACTION BAR ════ -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-award me-2 text-primary"></i>
                Quản Lý Kết Quả Bảo Vệ &amp; Bảng Điểm Khóa Luận — {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <div class="text-muted small">
                Thẩm định tiến độ chấm điểm, kiểm tra độ lệch điểm, công bố kết quả 3 mức (đề tài, hội đồng, toàn đợt) và truy vết kiểm toán.
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Nút Công Bố Toàn Đợt --}}
            <button type="button" class="btn btn-sm btn-success rounded-3 px-3 fw-bold shadow-xs btn-congbo-toandot">
                <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Toàn Đợt
            </button>

            {{-- Nút Thu Hồi Toàn Đợt --}}
            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-semibold shadow-xs btn-thuhoi-toandot">
                <i class="fa-solid fa-undo me-1"></i> Thu Hồi Toàn Đợt
            </button>

            {{-- Nút Xem Lịch Sử Kiểm Toán --}}
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold shadow-xs btn-xem-audit-log">
                <i class="fa-solid fa-history me-1"></i> Lịch Sử Công Bố
            </button>

            {{-- Nút In / Báo Cáo Tổng Hợp PDF --}}
            <a href="{{ route('truongkhoa.ketqua.in_baocao', request()->all()) }}" id="btn-print-report" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold shadow-xs">
                <i class="fa-solid fa-print me-1"></i> In / Báo Cáo PDF
            </a>

            {{-- Nút Xuất Excel / CSV --}}
            <a href="{{ route('truongkhoa.ketqua.export', request()->all()) }}" id="btn-export-excel" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold shadow-xs">
                <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel (CSV)
            </a>
        </div>
    </div>

    <!-- Alert Thông báo -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ═══ SUMMARY KPI CARDS (MỞ RỘNG TOÀN DIỆN 8 CHỈ SỐ) ═══ -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Đề Tài & Tiến Độ Chấm -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 kpi-card-hover" style="border-radius: 12px; border-left: 4px solid #0072ce !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-bold text-uppercase">Tổng Số Đề Tài</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                            {{ $stats['ty_le_hoan_thanh'] }}% hoàn tất
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold my-1 text-primary mb-0" id="stat-tong-de-tai">{{ $stats['tong_de_tai'] }}</h3>
                        <span class="text-muted small">đề tài ({{ $stats['so_hoi_dong'] }} HĐ)</span>
                    </div>
                    <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between">
                        <span>Đã đủ phiếu: <strong class="text-success">{{ $stats['de_tai_da_cham'] }}</strong></span>
                        <span>Đang chấm: <strong class="text-danger">{{ $stats['de_tai_chua_cham'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 2: Sinh Viên & Tỷ Lệ Đạt -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 kpi-card-hover" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-bold text-uppercase">Sinh Viên &amp; Tỷ Lệ Đạt</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            {{ $stats['ty_le_dat'] }}% Đạt
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold my-1 text-success mb-0" id="stat-total-sv">{{ $stats['tong_sinh_vien'] }}</h3>
                        <span class="text-muted small">sinh viên bảo vệ</span>
                    </div>
                    <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between">
                        <span>Đạt yêu cầu: <strong class="text-success">{{ $stats['so_dat'] }}</strong></span>
                        <span>Chưa đạt: <strong class="text-danger">{{ $stats['so_khong_dat'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Điểm Trung Bình, Cao Nhất & Thấp Nhất -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 kpi-card-hover" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-bold text-uppercase">Điểm Trung Bình Đợt</span>
                        <span class="badge bg-light text-secondary border rounded-pill">Thang 10</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold my-1 text-primary mb-0" id="stat-diem-tb">
                            {{ number_format($stats['diem_trung_binh'], 2) }}
                        </h3>
                        <span class="text-muted small">/ 10.00 điểm</span>
                    </div>
                    <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between">
                        <span>Cao nhất: <strong class="text-primary">{{ number_format($stats['diem_cao_nhat'], 2) }}</strong></span>
                        <span>Thấp nhất: <strong class="text-secondary">{{ number_format($stats['diem_thap_nhat'], 2) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 4: Tình Trạng Công Bố Điểm -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 kpi-card-hover" style="border-radius: 12px; border-left: 4px solid #b45309 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-bold text-uppercase">Tình Trạng Công Bố</span>
                        <span class="badge {{ $stats['de_tai_da_cong_bo'] > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill">
                            {{ $stats['tong_de_tai'] > 0 ? round(($stats['de_tai_da_cong_bo'] / $stats['tong_de_tai']) * 100, 1) : 0 }}% công bố
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold my-1 text-warning mb-0" id="stat-da-cong-bo">{{ $stats['de_tai_da_cong_bo'] }}</h3>
                        <span class="text-muted small">/ {{ $stats['tong_de_tai'] }} đề tài đã công bố</span>
                    </div>
                    <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between">
                        <span>Đã công bố: <strong class="text-success">{{ $stats['de_tai_da_cong_bo'] }}</strong></span>
                        <span>Chưa công bố: <strong class="text-warning-emphasis">{{ $stats['de_tai_chua_cong_bo'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ BIỂU ĐỒ TRỰC QUAN (PHÂN BỐ THANG ĐIỂM & TIẾN ĐỘ THEO HỘI ĐỒNG) ═══ -->
    <div class="row g-3 mb-4">
        <!-- Biểu đồ 1: Phân bố xếp loại điểm sinh viên -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-bold text-dark small text-uppercase">
                        <i class="fa-solid fa-chart-column text-primary me-2"></i>Biểu Đồ Phân Bố Thang Điểm (A, B+, B, C+, C, D+, D, F)
                    </span>
                    <span class="badge bg-light text-secondary border">Tổng {{ $stats['tong_sinh_vien'] }} SV</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 240px; position: relative;">
                        <canvas id="chartDiemPhanBo"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 2: Tiến độ đề tài theo từng Hội đồng -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-bold text-dark small text-uppercase">
                        <i class="fa-solid fa-chart-pie text-success me-2"></i>Tiến Độ Nộp Phiếu Chấm Điểm Theo Hội Đồng
                    </span>
                    <span class="badge bg-light text-secondary border">{{ $hoiDongs->count() }} Hội đồng</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 240px; position: relative;">
                        <canvas id="chartHoiDongTienDo"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ CẢNH BÁO ĐỀ TÀI THIẾU ĐIỂM (NẾU CÓ) ═══ -->
    @if(!empty($stats['de_tai_thieu_diem']))
        @php
            $allWarnings = $stats['de_tai_thieu_diem'] ?? [];
            $totalWarnings = count($allWarnings);
            $firstWarnings = array_slice($allWarnings, 0, 3);
            $extraWarnings = array_slice($allWarnings, 3);
            $extraCount = count($extraWarnings);
        @endphp
        <div class="alert alert-warning border-warning shadow-sm rounded-3 p-3 mb-4 warning-de-tai-container"
             id="alertDeTaiThieuDiemContainer"
             style="min-width: 0; max-width: 100%; box-sizing: border-box; overflow-wrap: anywhere; word-break: break-word; font-family: inherit;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2" style="min-width: 0; width: 100%;">
                <div class="fw-bold text-warning-emphasis d-flex align-items-center gap-2" style="min-width: 0; flex: 1 1 280px; overflow-wrap: anywhere; word-break: break-word;">
                    <i class="fa-solid fa-triangle-exclamation fs-5 flex-shrink-0 text-warning"></i>
                    <span style="overflow-wrap: anywhere; word-break: break-word;">
                        Cảnh báo: Phát hiện {{ $totalWarnings }} đề tài chưa đủ 100% phiếu chấm hợp lệ từ các giám khảo!
                    </span>
                </div>
                <span class="badge bg-danger text-white rounded-pill px-3 py-1 flex-shrink-0 align-self-start" 
                      style="white-space: nowrap; font-size: 0.8rem; font-family: inherit;">
                    Chặn công bố tự động
                </span>
            </div>
            <div class="small text-secondary mb-3" style="overflow-wrap: anywhere; word-break: break-word; white-space: normal;">
                Theo quy chế, Trưởng khoa không thể duyệt công bố kết quả cho các đề tài này cho đến khi toàn bộ thành viên trong Hội đồng hoàn tất ký nộp phiếu.
            </div>
            
            <div class="d-flex flex-column gap-2" style="min-width: 0; width: 100%;">
                @foreach($firstWarnings as $itemW)
                    @include('truongkhoa.ketqua.partials._warning_topic_card', ['itemW' => $itemW])
                @endforeach

                @if($extraCount > 0)
                    <div id="collapseMoreWarningItems" class="collapse mt-1" style="min-width: 0; width: 100%;">
                        <div class="d-flex flex-column gap-2 p-1" style="max-height: 240px; overflow-y: auto; overflow-x: hidden; min-width: 0; scrollbar-width: thin;">
                            @foreach($extraWarnings as $itemW)
                                @include('truongkhoa.ketqua.partials._warning_topic_card', ['itemW' => $itemW])
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-2 text-start">
                        <button type="button" 
                                class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3 py-1 shadow-none d-inline-flex align-items-center gap-1"
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseMoreWarningItems" 
                                aria-expanded="false" 
                                aria-controls="collapseMoreWarningItems"
                                id="btnToggleWarningMore" 
                                style="font-size: 0.8rem; background: #ffffff; border-color: #f59e0b;">
                            <i class="fa-solid fa-chevron-down" id="iconWarningMore"></i>
                            <span id="textWarningMore">Xem thêm ({{ $extraCount }})</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ═══ NAV TABS CHUYỂN ĐỔI CHẾ ĐỘ XEM ═══ -->
    <div class="d-flex border-bottom mb-4">
        <a href="{{ route('truongkhoa.ketqua.index', array_filter(array_merge(request()->except(['view', 'page', 'search_hd']), ['view' => 'toankhoa']))) }}" 
           class="px-4 py-2 text-decoration-none fw-bold {{ $viewMode === 'toankhoa' ? 'border-bottom border-primary border-3 text-primary' : 'text-secondary' }}">
            <i class="fa-solid fa-list-ol me-2"></i> Danh Sách Sinh Viên Theo Đề Tài <span id="tab-counter-toankhoa">({{ $ketQuas->total() }})</span>
        </a>
        <a href="{{ route('truongkhoa.ketqua.index', array_filter(array_merge(request()->except(['view', 'page', 'search']), ['view' => 'hoidong']))) }}" 
           class="px-4 py-2 text-decoration-none fw-bold {{ $viewMode === 'hoidong' ? 'border-bottom border-primary border-3 text-primary' : 'text-secondary' }}">
            <i class="fa-solid fa-users-viewfinder me-2"></i> Danh Sách Hội Đồng &amp; Tiến Độ <span id="tab-counter-hoidong">({{ $hoiDongs->count() }})</span>
        </a>
    </div>

    <!-- ═══ BỘ LỌC TỔNG HỢP LINH HOẠT ═══ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form id="filter-form" method="GET" action="{{ route('truongkhoa.ketqua.index') }}" class="d-flex flex-wrap gap-2 align-items-center w-100">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <!-- Ô tìm kiếm (Wrapper tương đối, icon kính lúp, không có nút xóa x) -->
                <div class="position-relative flex-grow-1 min-w-0" style="min-width: 240px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary" style="pointer-events: none; z-index: 4;"></i>
                    <input type="text" 
                           name="{{ $viewMode === 'hoidong' ? 'search_hd' : 'search' }}" 
                           id="input-search"
                           class="form-control rounded-3 shadow-xs" 
                           style="padding-left: 2.75rem !important; padding-right: 0.75rem !important; min-height: 42px !important;"
                           placeholder="{{ $viewMode === 'hoidong' ? 'Tìm theo tên hội đồng, phòng, mã...' : 'Tìm theo mã SV, họ tên, nhóm, đề tài...' }}" 
                           value="{{ $viewMode === 'hoidong' ? request('search_hd') : request('search') }}"
                           autocomplete="off">
                </div>

                <!-- Dropdown Học kỳ -->
                <select name="MaHocKy" id="select-hocky" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 170px; max-width: 220px; min-height: 42px !important;">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ (request('MaHocKy') == $hk->MaHocKy || (!request()->filled('MaHocKy') && $currentMaHocKy == $hk->MaHocKy)) ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>

                <!-- Dropdown Hội đồng -->
                <select name="MaHoiDong" id="select-hoidong" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 170px; max-width: 220px; min-height: 42px !important;">
                    <option value="">-- Tất cả hội đồng --</option>
                    @foreach($allHoiDongs as $ahd)
                        <option value="{{ $ahd->MaHoiDong }}" {{ request('MaHoiDong') == $ahd->MaHoiDong ? 'selected' : '' }}>
                            {{ $ahd->TenHoiDong }}
                        </option>
                    @endforeach
                </select>

                <!-- Dropdown Bộ môn (chỉ tab toàn khoa) -->
                @if($viewMode === 'toankhoa')
                <select name="MaBoMon" id="select-bomon" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 160px; max-width: 200px; min-height: 42px !important;">
                    <option value="">-- Tất cả bộ môn --</option>
                    @foreach($boMons as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                            {{ $bm->TenBoMon }}
                        </option>
                    @endforeach
                </select>
                @endif

                <!-- Dropdown Trạng thái công bố -->
                <select name="trang_thai_cong_bo" id="select-trang-thai" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 160px; max-width: 190px; min-height: 42px !important;">
                    <option value="">-- Trạng thái công bố --</option>
                    <option value="DaCongBo" {{ request('trang_thai_cong_bo') == 'DaCongBo' ? 'selected' : '' }}>Đã công bố</option>
                    <option value="ChuaCongBo" {{ request('trang_thai_cong_bo') == 'ChuaCongBo' ? 'selected' : '' }}>Chưa công bố</option>
                    <option value="ChuaDuPhieu" {{ request('trang_thai_cong_bo') == 'ChuaDuPhieu' ? 'selected' : '' }}>Chưa đủ phiếu chấm</option>
                </select>

                <!-- Dropdown Xếp loại (chỉ tab toàn khoa) -->
                @if($viewMode === 'toankhoa')
                <select name="xep_loai" id="select-xep-loai" class="form-select rounded-3 py-2 shadow-xs flex-grow-1 flex-md-grow-0" style="min-width: 140px; max-width: 170px; min-height: 42px !important;">
                    <option value="">-- Xếp loại --</option>
                    <option value="XuatSac" {{ request('xep_loai') == 'XuatSac' ? 'selected' : '' }}>Xuất sắc (&ge; 9.0)</option>
                    <option value="Gioi" {{ request('xep_loai') == 'Gioi' ? 'selected' : '' }}>Giỏi (8.0 - 8.9)</option>
                    <option value="Kha" {{ request('xep_loai') == 'Kha' ? 'selected' : '' }}>Khá (7.0 - 7.9)</option>
                    <option value="TrungBinh" {{ request('xep_loai') == 'TrungBinh' ? 'selected' : '' }}>Trung bình (5.0 - 6.9)</option>
                    <option value="KhongDat" {{ request('xep_loai') == 'KhongDat' ? 'selected' : '' }}>Không đạt (&lt; 5.0)</option>
                </select>
                @endif

                <!-- Nút Lọc và Đặt lại -->
                <div class="d-flex gap-2 ms-auto">
                    <button type="submit" id="btn-submit-filter" class="btn btn-primary rounded-3 px-3 fw-bold shadow-xs d-flex align-items-center gap-1" style="min-height: 42px !important;">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    <a href="{{ route('truongkhoa.ketqua.index', ['view' => $viewMode]) }}" class="btn btn-outline-secondary rounded-3 px-3 shadow-xs d-flex align-items-center gap-1" style="min-height: 42px !important;">
                        Làm mới
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ NỘI DUNG TAB ═══ -->
    <div id="ketqua-result-container" style="min-width: 0; max-width: 100%; width: 100%; overflow: hidden;">
        @if($viewMode === 'hoidong')
            @include('truongkhoa.ketqua.partials._hoidong_list', ['hoiDongs' => $hoiDongs, 'tienDoHoiDongs' => $tienDoHoiDongs])
        @else
            @include('truongkhoa.ketqua.partials._toankhoa_table', ['ketQuas' => $ketQuas])
        @endif
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CÁC MODAL HỆ THỐNG: CÔNG BỐ, THU HỒI, CHI TIẾT RUBRIC & AUDIT LOG       -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->

<!-- Modal 1: Xác Nhận Công Bố (3 Mức: Đề tài, Hội đồng, Toàn đợt) -->
<div class="modal fade" id="modalCongBoConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-bullhorn fs-5"></i>
                    <h5 class="modal-title fw-bold fs-6 mb-0" id="modalCongBoTitle">Xác Nhận Công Bố Kết Quả Bảo Vệ</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCongBoAction" method="POST" action="">
                @csrf
                <input type="hidden" name="MaHoSo" id="cbInputMaHoSo" value="">
                <input type="hidden" name="MaHoiDong" id="cbInputMaHoiDong" value="">
                <input type="hidden" name="MaHocKy" id="cbInputMaHocKy" value="{{ $currentMaHocKy }}">

                <div class="modal-body p-4">
                    <div id="cbCheckLoading" class="text-center py-4">
                        <div class="spinner-border text-success mb-2" role="status"></div>
                        <div class="small text-muted">Đang kiểm tra điều kiện phiếu chấm hợp lệ...</div>
                    </div>

                    <div id="cbCheckContent" style="display: none;">
                        <div id="cbAlertWarning" class="alert alert-warning border-warning rounded-3 small p-3 mb-3" style="display: none;">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                            <span id="cbWarningText"></span>
                        </div>

                        <div id="cbSuccessBox" class="p-3 bg-success bg-opacity-10 rounded-3 border border-success-subtle mb-3">
                            <div class="fw-bold text-success mb-1" id="cbTargetText">Đề tài đủ điều kiện công bố</div>
                            <div class="small text-secondary" id="cbSummaryText">Điểm chính thức sẽ được khóa bảo vệ và hiển thị cho sinh viên.</div>
                        </div>

                        <div class="small text-muted">
                            <ul class="mb-0 ps-3">
                                <li>Sinh viên và Giảng viên hướng dẫn sẽ nhận được thông báo chuông.</li>
                                <li>Điểm số sẽ được chuyển thành Điểm chính thức và xuất hiện trên cổng Sinh viên.</li>
                                <li>Hệ thống tự động ghi nhật ký kiểm toán (Audit Log).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" id="btnSubmitCongBo" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Công Bố
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Thu Hồi Quyết Định Công Bố (Bắt buộc nhập lý do) -->
<div class="modal fade" id="modalThuHoiConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-undo fs-5"></i>
                    <h5 class="modal-title fw-bold fs-6 mb-0" id="modalThuHoiTitle">Thu Hồi Quyết Định Công Bố Điểm</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formThuHoiAction" method="POST" action="">
                @csrf
                <input type="hidden" name="MaHoSo" id="thInputMaHoSo" value="">
                <input type="hidden" name="MaHoiDong" id="thInputMaHoiDong" value="">
                <input type="hidden" name="MaHocKy" id="thInputMaHocKy" value="{{ $currentMaHocKy }}">

                <div class="modal-body p-4">
                    <div class="alert alert-danger border-danger rounded-3 small p-3 mb-3">
                        <i class="fa-solid fa-triangle-exclamation text-danger me-1"></i>
                        <strong>Lưu ý quan trọng:</strong> Điểm số sẽ được chuyển về trạng thái <em>Chưa công bố</em>. Sinh viên và Giảng viên sẽ tạm thời không xem được điểm cho đến khi được duyệt công bố lại.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">
                            Lý do thu hồi công bố <span class="text-danger">*</span>:
                        </label>
                        <textarea name="ly_do" id="thInputLyDo" class="form-control rounded-3" rows="3" 
                            placeholder="Nhập lý do chi tiết (ví dụ: Phát hiện sai sót trong tổng hợp điểm, giám khảo đề nghị chấm phúc khảo...)" required minlength="5"></textarea>
                        <div class="form-text small">Lý do thu hồi sẽ được lưu vết kiểm toán và gửi thông báo đến các bên liên quan.</div>
                    </div>
                </div>

                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-undo me-1"></i> Xác Nhận Thu Hồi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Chi Tiết Điểm Rubric Đề Tài (Bảng 3 - Tải qua AJAX) -->
<div class="modal fade" id="modalRubricDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden" id="modalRubricContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="small text-muted mt-2">Đang tải bảng điểm chi tiết theo rubric...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Lịch Sử Audit Log (Tải qua AJAX) -->
<div class="modal fade" id="modalAuditLog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden" id="modalAuditContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-secondary" role="status"></div>
                <div class="small text-muted mt-2">Đang tải nhật ký kiểm toán...</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ════ 1. KHỞI TẠO BIỂU ĐỒ CHART.JS ════
    const distData = @json($stats['chart_distribution']);
    const councilData = @json($stats['chart_councils']);
    let chartDistInstance = null;
    let chartCouncilInstance = null;

    // Biểu đồ 1: Phân bố thang điểm A, B+, B, C+, C, D+, D, F
    const ctxDiem = document.getElementById('chartDiemPhanBo');
    if (ctxDiem && typeof Chart !== 'undefined') {
        chartDistInstance = new Chart(ctxDiem, {
            type: 'bar',
            data: {
                labels: ['A (≥8.5)', 'B+ (8.0-8.4)', 'B (7.0-7.9)', 'C+ (6.5-6.9)', 'C (5.5-6.4)', 'D+ (5.0-5.4)', 'D (4.0-4.9)', 'F (<4.0)'],
                datasets: [{
                    label: 'Số sinh viên',
                    data: [
                        distData.A || 0,
                        distData.B_plus || 0,
                        distData.B || 0,
                        distData.C_plus || 0,
                        distData.C || 0,
                        distData.D_plus || 0,
                        distData.D || 0,
                        distData.F || 0
                    ],
                    backgroundColor: [
                        '#00529b', '#0d6efd', '#0dcaf0', '#198754', 
                        '#ffc107', '#fd7e14', '#6c757d', '#dc3545'
                    ],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Biểu đồ 2: Tiến độ đề tài theo từng Hội đồng (ngăn xoay chéo cắt chữ và chống tràn nhãn trục x)
    const ctxHd = document.getElementById('chartHoiDongTienDo');
    if (ctxHd && typeof Chart !== 'undefined' && councilData.length > 0) {
        chartCouncilInstance = new Chart(ctxHd, {
            type: 'bar',
            data: {
                labels: councilData.map(c => c.name),
                datasets: [
                    {
                        label: 'Đề tài đủ phiếu',
                        data: councilData.map(c => c.duPhieu),
                        backgroundColor: '#198754',
                        borderRadius: 4,
                    },
                    {
                        label: 'Đang chấm / Thiếu phiếu',
                        data: councilData.map(c => Math.max(0, c.total - c.duPhieu)),
                        backgroundColor: '#ffc107',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                if (!items || !items.length) return '';
                                const idx = items[0].dataIndex;
                                return councilData[idx]?.fullName || councilData[idx]?.name || items[0].label;
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        stacked: true,
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            autoSkip: false,
                            callback: function(val, index) {
                                const lbl = this.getLabelForValue(val);
                                if (typeof lbl === 'string' && lbl.length > 14) {
                                    return lbl.substring(0, 13) + '…';
                                }
                                return lbl;
                            }
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Xử lý nút Xem thêm / Thu gọn cảnh báo đề tài thiếu điểm
    const collapseWarningEl = document.getElementById('collapseMoreWarningItems');
    const textWarningMore = document.getElementById('textWarningMore');
    const iconWarningMore = document.getElementById('iconWarningMore');
    if (collapseWarningEl && textWarningMore && iconWarningMore) {
        collapseWarningEl.addEventListener('show.bs.collapse', function () {
            textWarningMore.textContent = 'Thu gọn';
            iconWarningMore.classList.remove('fa-chevron-down');
            iconWarningMore.classList.add('fa-chevron-up');
        });
        collapseWarningEl.addEventListener('hide.bs.collapse', function () {
            const countExtra = collapseWarningEl.querySelectorAll('.warning-topic-card').length;
            textWarningMore.textContent = 'Xem thêm (' + countExtra + ')';
            iconWarningMore.classList.remove('fa-chevron-up');
            iconWarningMore.classList.add('fa-chevron-down');
        });
    }

    // ════ 2. XỬ LÝ SỰ KIỆN CÔNG BỐ & THU HỒI 3 MỨC ════
    const modalCongBoEl = document.getElementById('modalCongBoConfirm');
    const modalCongBo = modalCongBoEl ? new bootstrap.Modal(modalCongBoEl) : null;
    const formCongBo = document.getElementById('formCongBoAction');

    const modalThuHoiEl = document.getElementById('modalThuHoiConfirm');
    const modalThuHoi = modalThuHoiEl ? new bootstrap.Modal(modalThuHoiEl) : null;
    const formThuHoi = document.getElementById('formThuHoiAction');

    // Hàm gọi AJAX kiểm tra điều kiện trước khi công bố
    function prepareCongBoModal(phamVi, params) {
        document.getElementById('cbCheckLoading').style.display = 'block';
        document.getElementById('cbCheckContent').style.display = 'none';
        document.getElementById('btnSubmitCongBo').disabled = true;
        document.getElementById('cbAlertWarning').style.display = 'none';

        const queryParams = new URLSearchParams({ pham_vi: phamVi, ...params });
        fetch(`{{ route('truongkhoa.ketqua.kiemtra_dieukien') }}?${queryParams.toString()}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('cbCheckLoading').style.display = 'none';
                document.getElementById('cbCheckContent').style.display = 'block';

                const info = data.data;
                const canPublish = info.du_dieu_kien || (phamVi === 'TOAN_DOT' && info.so_de_tai_du > 0) || (phamVi === 'HOI_DONG' && info.de_tai_du > 0);

                if (!canPublish) {
                    document.getElementById('cbAlertWarning').style.display = 'block';
                    document.getElementById('cbWarningText').textContent = info.ly_do || 'Chưa đủ điều kiện công bố kết quả.';
                    document.getElementById('btnSubmitCongBo').disabled = true;
                } else {
                    document.getElementById('btnSubmitCongBo').disabled = false;
                    if (info.ly_do && !info.du_dieu_kien) {
                        document.getElementById('cbAlertWarning').style.display = 'block';
                        document.getElementById('cbWarningText').textContent = info.ly_do;
                    }
                }

                if (phamVi === 'DE_TAI') {
                    document.getElementById('cbTargetText').textContent = `Đề tài: ${info.ten_de_tai}`;
                    document.getElementById('cbSummaryText').textContent = `Hội đồng: ${info.ten_hoi_dong} • ${info.so_gv_hoan_thanh}/${info.tong_gv} giám khảo đã nộp phiếu.`;
                } else if (phamVi === 'HOI_DONG') {
                    document.getElementById('cbTargetText').textContent = `Hội đồng: ${info.ten_hoi_dong}`;
                    document.getElementById('cbSummaryText').textContent = `${info.de_tai_du}/${info.tong_de_tai} đề tài đã có đủ 100% phiếu chấm hợp lệ.`;
                } else {
                    document.getElementById('cbTargetText').textContent = `Toàn bộ đợt học kỳ: ${info.ma_hoc_ky}`;
                    document.getElementById('cbSummaryText').textContent = `Có ${info.so_de_tai_du}/${info.tong_de_tai} đề tài (${info.tong_sinh_vien} sinh viên) đủ điều kiện công bố.`;
                }
            })
            .catch(err => {
                document.getElementById('cbCheckLoading').style.display = 'none';
                document.getElementById('cbCheckContent').style.display = 'block';
                document.getElementById('cbAlertWarning').style.display = 'block';
                document.getElementById('cbWarningText').textContent = 'Lỗi kiểm tra điều kiện công bố: ' + err.message;
            });
    }

    // Nút công bố từng đề tài
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-congbo-detai');
        if (btn) {
            const maHoSo = btn.getAttribute('data-mahoso');
            formCongBo.action = "{{ route('truongkhoa.ketqua.congbo_detai') }}";
            document.getElementById('cbInputMaHoSo').value = maHoSo;
            document.getElementById('modalCongBoTitle').textContent = 'Công Bố Kết Quả Cho Từng Đề Tài';
            modalCongBo.show();
            prepareCongBoModal('DE_TAI', { MaHoSo: maHoSo });
        }
    });

    // Nút thu hồi từng đề tài
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-thuhoi-detai');
        if (btn) {
            const maHoSo = btn.getAttribute('data-mahoso');
            const tenDeTai = btn.getAttribute('data-tendetai');
            formThuHoi.action = "{{ route('truongkhoa.ketqua.thuhoi_detai') }}";
            document.getElementById('thInputMaHoSo').value = maHoSo;
            document.getElementById('modalThuHoiTitle').textContent = `Thu Hồi Công Bố Đề Tài: ${tenDeTai}`;
            document.getElementById('thInputLyDo').value = '';
            modalThuHoi.show();
        }
    });

    // Nút công bố Hội đồng
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-congbo-hoidong');
        if (btn) {
            const maHoiDong = btn.getAttribute('data-mahoidong');
            formCongBo.action = "{{ route('truongkhoa.ketqua.congbo_hoidong') }}";
            document.getElementById('cbInputMaHoiDong').value = maHoiDong;
            document.getElementById('modalCongBoTitle').textContent = 'Công Bố Kết Quả Hội Đồng Bảo Vệ';
            modalCongBo.show();
            prepareCongBoModal('HOI_DONG', { MaHoiDong: maHoiDong });
        }
    });

    // Nút thu hồi Hội đồng
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-thuhoi-hoidong');
        if (btn) {
            const maHoiDong = btn.getAttribute('data-mahoidong');
            const tenHd = btn.getAttribute('data-tenhoidong');
            formThuHoi.action = "{{ route('truongkhoa.ketqua.thuhoi_hoidong') }}";
            document.getElementById('thInputMaHoiDong').value = maHoiDong;
            document.getElementById('modalThuHoiTitle').textContent = `Thu Hồi Công Bố Hội Đồng: ${tenHd}`;
            document.getElementById('thInputLyDo').value = '';
            modalThuHoi.show();
        }
    });

    // Nút công bố Toàn đợt
    const btnCbToanDot = document.querySelector('.btn-congbo-toandot');
    if (btnCbToanDot) {
        btnCbToanDot.addEventListener('click', function () {
            formCongBo.action = "{{ route('truongkhoa.ketqua.congbo_toandot') }}";
            document.getElementById('modalCongBoTitle').textContent = 'Công Bố Kết Quả Toàn Bộ Đợt Bảo Vệ';
            modalCongBo.show();
            prepareCongBoModal('TOAN_DOT', { MaHocKy: document.getElementById('select-hocky').value });
        });
    }

    // Nút thu hồi Toàn đợt
    const btnThToanDot = document.querySelector('.btn-thuhoi-toandot');
    if (btnThToanDot) {
        btnThToanDot.addEventListener('click', function () {
            formThuHoi.action = "{{ route('truongkhoa.ketqua.thuhoi_toandot') }}";
            document.getElementById('modalThuHoiTitle').textContent = 'Thu Hồi Công Bố Kết Quả Toàn Bộ Đợt Bảo Vệ';
            document.getElementById('thInputLyDo').value = '';
            modalThuHoi.show();
        });
    }

    // ════ 3. MODAL CHI TIẾT RUBRIC & AUDIT LOG ════
    const modalRubricEl = document.getElementById('modalRubricDetail');
    const modalRubric = modalRubricEl ? new bootstrap.Modal(modalRubricEl) : null;
    const modalRubricContainer = document.getElementById('modalRubricContainer');

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-xem-rubric');
        if (btn) {
            const maHoSo = btn.getAttribute('data-mahoso');
            modalRubricContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải bảng điểm chi tiết theo rubric...</div>
                </div>
            `;
            modalRubric.show();
            fetch(`{{ url('truongkhoa/ket-qua/chi-tiet-rubric') }}/${maHoSo}`)
                .then(res => res.text())
                .then(html => {
                    modalRubricContainer.innerHTML = html;
                })
                .catch(err => {
                    modalRubricContainer.innerHTML = `
                        <div class="p-4 text-center text-danger">
                            <i class="fa-solid fa-circle-exclamation fa-2x mb-2"></i>
                            <div>Lỗi khi tải chi tiết rubric: ${err.message}</div>
                        </div>
                    `;
                });
        }
    });

    const modalAuditEl = document.getElementById('modalAuditLog');
    const modalAudit = modalAuditEl ? new bootstrap.Modal(modalAuditEl) : null;
    const modalAuditContainer = document.getElementById('modalAuditContainer');

    const btnAudit = document.querySelector('.btn-xem-audit-log');
    if (btnAudit) {
        btnAudit.addEventListener('click', function () {
            modalAuditContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải nhật ký kiểm toán...</div>
                </div>
            `;
            modalAudit.show();
            fetch(`{{ route('truongkhoa.ketqua.lichsu') }}`)
                .then(res => res.text())
                .then(html => {
                    modalAuditContainer.innerHTML = html;
                })
                .catch(err => {
                    modalAuditContainer.innerHTML = `
                        <div class="p-4 text-center text-danger">
                            <i class="fa-solid fa-circle-exclamation fa-2x mb-2"></i>
                            <div>Lỗi khi tải nhật ký: ${err.message}</div>
                        </div>
                    `;
                });
        });
    }

    // ════ 4. AJAX LIVE FILTER VÀ PHÂN TRANG ════
    const filterForm = document.getElementById('filter-form');
    const searchInput = document.getElementById('input-search');
    const selectHocKy = document.getElementById('select-hocky');
    const selectHoiDong = document.getElementById('select-hoidong');
    const selectBoMon = document.getElementById('select-bomon');
    const selectTrangThai = document.getElementById('select-trang-thai');
    const selectXepLoai = document.getElementById('select-xep-loai');
    const resultContainer = document.getElementById('ketqua-result-container');
    const exportBtn = document.getElementById('btn-export-excel');
    const printBtn = document.getElementById('btn-print-report');

    let debounceTimer = null;
    let currentAbortController = null;
    let lastQueryString = '';

    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerFetch(true, null, true);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                triggerFetch(false, null, true);
            }, 300);
        });
    }

    [selectHocKy, selectHoiDong, selectBoMon, selectTrangThai, selectXepLoai].forEach(function (el) {
        if (el) {
            el.addEventListener('change', function () {
                triggerFetch(true, null, true);
            });
        }
    });

    function triggerFetch(resetPage = false, customUrl = null, force = false) {
        let url;
        if (customUrl) {
            url = new URL(customUrl, window.location.origin);
        } else {
            url = new URL(filterForm.action, window.location.origin);
            const formData = new FormData(filterForm);
            for (let [k, v] of formData.entries()) {
                if (v && v.trim() !== '') {
                    url.searchParams.set(k, v.trim());
                }
            }
            if (resetPage) {
                url.searchParams.delete('page');
            }
        }

        const currentQuery = url.searchParams.toString();
        if (!force && !customUrl && currentQuery === lastQueryString) {
            return;
        }
        lastQueryString = currentQuery;

        window.history.replaceState({ path: url.toString() }, '', url.toString());

        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        if (resultContainer) {
            resultContainer.style.opacity = '0.55';
            resultContainer.style.pointerEvents = 'none';
            resultContainer.style.transition = 'opacity 0.2s ease';
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: currentAbortController.signal
        })
        .then(res => res.json())
        .then(data => {
            if (resultContainer && data.html !== undefined) {
                resultContainer.innerHTML = data.html;
            }

            if (data.total !== undefined) {
                const viewMode = data.viewMode || 'toankhoa';
                const counter = document.getElementById(viewMode === 'hoidong' ? 'tab-counter-hoidong' : 'tab-counter-toankhoa');
                if (counter) counter.textContent = `(${data.total})`;
            }

            // Cập nhật thẻ KPI
            if (data.stats) {
                const s = data.stats;
                const elTongDeTai = document.getElementById('stat-tong-de-tai');
                if (elTongDeTai && s.tong_de_tai !== undefined) elTongDeTai.textContent = s.tong_de_tai;

                const elTotalSv = document.getElementById('stat-total-sv');
                if (elTotalSv && s.tong_sinh_vien !== undefined) elTotalSv.textContent = s.tong_sinh_vien;

                const elDiemTb = document.getElementById('stat-diem-tb');
                if (elDiemTb && s.diem_trung_binh !== undefined) elDiemTb.textContent = Number(s.diem_trung_binh).toFixed(2);

                const elDaCongBo = document.getElementById('stat-da-cong-bo');
                if (elDaCongBo && s.de_tai_da_cong_bo !== undefined) elDaCongBo.textContent = s.de_tai_da_cong_bo;

                // Cập nhật biểu đồ phân bố điểm
                if (chartDistInstance && s.chart_distribution) {
                    const d = s.chart_distribution;
                    chartDistInstance.data.datasets[0].data = [
                        d.A || 0, d.B_plus || 0, d.B || 0, d.C_plus || 0,
                        d.C || 0, d.D_plus || 0, d.D || 0, d.F || 0
                    ];
                    chartDistInstance.update();
                }

                // Cập nhật biểu đồ tiến độ hội đồng
                if (chartCouncilInstance && s.chart_councils) {
                    const cd = s.chart_councils;
                    councilData.length = 0;
                    cd.forEach(c => councilData.push(c));
                    chartCouncilInstance.data.labels = cd.map(c => c.name);
                    chartCouncilInstance.data.datasets[0].data = cd.map(c => c.duPhieu);
                    chartCouncilInstance.data.datasets[1].data = cd.map(c => Math.max(0, c.total - c.duPhieu));
                    chartCouncilInstance.update();
                }
            }

            // Đồng bộ URL nút Xuất Excel & In Báo cáo
            if (exportBtn) {
                const exportUrl = new URL(exportBtn.href, window.location.origin);
                exportUrl.search = '';
                for (let [k, v] of url.searchParams.entries()) {
                    exportUrl.searchParams.set(k, v);
                }
                exportBtn.href = exportUrl.toString();
            }

            if (printBtn) {
                const printUrl = new URL(printBtn.href, window.location.origin);
                printUrl.search = '';
                for (let [k, v] of url.searchParams.entries()) {
                    printUrl.searchParams.set(k, v);
                }
                printBtn.href = printUrl.toString();
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            console.error('Fetch filter error:', err);
        })
        .finally(() => {
            if (resultContainer) {
                resultContainer.style.opacity = '1';
                resultContainer.style.pointerEvents = 'auto';
            }
        });
    }

    if (resultContainer) {
        resultContainer.addEventListener('click', function (e) {
            const paginationLink = e.target.closest('.ajax-pagination-wrapper a, .pagination a');
            if (paginationLink && paginationLink.href) {
                e.preventDefault();
                triggerFetch(false, paginationLink.href, true);
            }
        });
    }
});
</script>
@endpush
