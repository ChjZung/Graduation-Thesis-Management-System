@php
    if (empty($layout)) {
        $uRole = Auth::user()?->vaiTro?->TenVaiTro ?? '';
        $layout = match($uRole) {
            'Sinh viên'     => 'layouts.sinhvien',
            'Giảng viên'    => 'layouts.giangvien',
            'Trưởng bộ môn' => 'layouts.truongbomon',
            'Trưởng khoa'   => 'layouts.truongkhoa',
            default         => 'layouts.admin',
        };
    }
@endphp
@extends($layout)

@section('page_title', 'Chi Tiết Thông Báo & Xem Văn Bản Công Văn')

@push('styles')
<style>
    .doc-viewer-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 170px);
        min-height: 680px;
    }
    .doc-viewer-header {
        background: #1e293b;
        color: #f8fafc;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .doc-viewer-body {
        flex-grow: 1;
        position: relative;
        background: #525659;
        overflow: hidden;
    }
    .doc-viewer-body iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
    .noti-detail-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 170px);
        min-height: 680px;
    }
    .noti-detail-scroll {
        overflow-y: auto;
        padding: 20px;
        flex-grow: 1;
    }
    /* Fullscreen mode for doc viewer */
    .doc-fullscreen-active {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
    /* Authentic Excel Spreadsheet View Styles */
    .excel-window-container {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        font-family: 'Segoe UI', Calibri, Arial, sans-serif;
        color: #0f172a;
        height: 100%;
        overflow: hidden;
    }
    .excel-formula-bar {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border-bottom: 1px solid #cbd5e1;
        padding: 5px 12px;
        gap: 8px;
        font-size: 12px;
        flex-shrink: 0;
    }
    .excel-name-box {
        width: 65px;
        height: 26px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #475569;
        font-size: 12px;
        border-radius: 2px;
    }
    .excel-fx-icon {
        font-style: italic;
        font-weight: 700;
        color: #94a3b8;
        font-size: 13px;
        padding: 0 4px;
        user-select: none;
    }
    .excel-formula-display {
        flex-grow: 1;
        height: 26px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        padding: 3px 10px;
        font-size: 12px;
        color: #1e293b;
        font-family: 'Segoe UI', Calibri, Arial, sans-serif;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border-radius: 2px;
    }
    .excel-canvas-scroll {
        flex-grow: 1;
        overflow: auto;
        background: #ffffff;
        position: relative;
    }
    .excel-sheet-table {
        border-collapse: collapse;
        width: 100%;
        table-layout: auto;
        font-size: 12.5px;
        background: #ffffff;
        user-select: text;
    }
    .excel-sheet-table th, 
    .excel-sheet-table td {
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        white-space: nowrap;
        font-family: 'Segoe UI', Calibri, Arial, sans-serif;
    }
    .excel-col-head {
        background: #f1f5f9;
        color: #64748b;
        font-weight: 600;
        text-align: center;
        font-size: 11px;
        height: 24px;
        padding: 2px 6px;
        border: 1px solid #cbd5e1 !important;
        position: sticky;
        top: 0;
        z-index: 10;
        user-select: none;
    }
    .excel-row-num {
        background: #f1f5f9;
        color: #64748b;
        font-weight: 600;
        text-align: center;
        font-size: 11px;
        width: 38px;
        min-width: 38px;
        max-width: 38px;
        border: 1px solid #cbd5e1 !important;
        user-select: none;
        position: sticky;
        left: 0;
        z-index: 5;
    }
    .excel-col-head:first-child {
        position: sticky;
        left: 0;
        z-index: 20;
        background: #e2e8f0;
    }
    .excel-sheet-table td:not(.excel-row-num) {
        cursor: cell;
    }
    .excel-banner-org {
        font-size: 11.5px;
        font-weight: 700;
        color: #1E3A8A;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        padding: 8px 10px 4px 10px;
    }
    .excel-banner-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0072ce;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        padding: 2px 10px 4px 10px;
    }
    .excel-banner-sub {
        font-size: 9.5px;
        font-style: italic;
        color: #64748B;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        padding: 2px 10px 10px 10px;
    }
    .excel-blue-header th {
        background-color: #0072ce !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        height: 28px !important;
        border: 1px solid #005bb5 !important;
        text-align: inherit;
    }
    .excel-active-cell {
        border: 2px solid #107c41 !important;
        position: relative;
        background-color: rgba(16, 124, 65, 0.03);
    }
    .excel-active-cell::after {
        content: '';
        position: absolute;
        bottom: -3px;
        right: -3px;
        width: 6px;
        height: 6px;
        background: #107c41;
    }
    .excel-sheet-footer-bar {
        display: flex;
        align-items: center;
        background: #f1f5f9;
        border-top: 1px solid #cbd5e1;
        height: 34px;
        padding: 0 8px;
        flex-shrink: 0;
        user-select: none;
        overflow-x: auto;
    }
    .excel-sheet-nav-btn {
        background: none;
        border: none;
        color: #64748b;
        font-size: 12px;
        padding: 2px 8px;
        cursor: pointer;
    }
    .excel-sheet-tab-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 16px;
        height: 28px;
        background: #e2e8f0;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-bottom: none;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        border-radius: 4px 4px 0 0;
        margin-right: 2px;
    }
    .excel-sheet-tab-item.active {
        background: #ffffff;
        color: #107c41;
        font-weight: 700;
        border-top: 2.5px solid #107c41;
        border-bottom: 1px solid #ffffff;
    }
    .excel-sheet-tab-item:hover:not(.active) {
        background: #f8fafc;
        color: #1e293b;
    }
    .excel-status-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 3px 14px;
        font-size: 11px;
        color: #64748b;
        height: 22px;
        flex-shrink: 0;
        user-select: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    {{-- Thanh điều hướng & Action bar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ $backUrl ?? route('thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách thông báo
            </a>
            <span class="text-muted small">|</span>
            <span class="badge bg-light text-primary border rounded-pill px-3 py-1 font-monospace">{{ $thongBao->MaThongBao }}</span>
            <span class="badge {{ in_array($thongBao->TrangThai, ['ĐÃ GỬI', 'Đã phát hành']) ? 'bg-success' : 'bg-primary' }} rounded-pill px-3 py-1">
                {{ $thongBao->TrangThai }}
            </span>
        </div>
        
        <div class="d-flex gap-2">
            @if(!empty($fileUrl))
                @if(($fileType ?? '') === 'excel')
                    <a href="{{ asset($fileUrl) }}" download class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-semibold text-white d-inline-flex align-items-center">
                        <i class="fa-solid fa-file-excel me-2"></i> Tải Danh Sách Excel (.xlsx)
                    </a>
                @else
                    <a href="{{ asset($fileUrl) }}" download class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold d-inline-flex align-items-center">
                        <i class="fa-solid fa-download me-2"></i> Tải Công Văn Gốc
                    </a>
                @endif
            @endif
            @if(in_array($thongBao->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']) && Route::has('thongbao.edit') && (!isset($layout) || $layout === 'layouts.admin'))
                <a href="{{ route('thongbao.edit', $thongBao->MaThongBao) }}" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                    <i class="fa-solid fa-pen me-2"></i> Chỉnh Sửa
                </a>
            @endif
        </div>
    </div>

    {{-- KIỂM TRA THÔNG BÁO CÓ FILE ĐÍNH KÈM HOẶC BẢNG DỮ LIỆU CHÍNH THỨC HAY KHÔNG --}}
    @if(!empty($fileUrl) || (isset($officialListData) && !empty($officialListData['isOfficial'])))
    {{-- GIAO DIỆN SONG SONG 2 BÊN (SPLIT VIEW): 1 BÊN CÔNG VĂN - 1 BÊN THÔNG TIN THÔNG BÁO --}}
    <div class="row g-3">
        {{-- CỘT TRÁI: KHUNG XEM TRỰC TIẾP FILE CÔNG VĂN / ẢNH (58%) --}}
        <div class="col-12 col-xl-7">
            <div class="doc-viewer-card" id="docViewerCard">
                <div class="doc-viewer-header">
                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                        @if(isset($officialListData) && !empty($officialListData['isOfficial']))
                            <i class="fa-solid fa-table-list text-warning fs-5 me-2"></i>
                            <span class="fw-semibold text-truncate small text-white">
                                Danh Sách Chính Thức: Đề Tài, Nhóm SV &amp; GVHD
                            </span>
                            <span class="badge bg-success rounded-pill px-2 small" style="font-size: 0.7rem;">
                                CHÍNH THỨC
                            </span>
                        @elseif(($fileType ?? '') === 'image')
                            <i class="fa-solid fa-file-image text-warning fs-5 me-2"></i>
                            <span class="fw-semibold text-truncate small" title="{{ !empty($fileUrl) ? basename($fileUrl) : 'VanBan_ThongBao.pdf' }}">
                                {{ !empty($fileUrl) ? basename($fileUrl) : 'Văn Bản Công Văn Thông Báo (Khoa CNTT)' }}
                            </span>
                            <span class="badge bg-warning text-dark rounded-pill px-2 small" style="font-size: 0.7rem;">
                                IMAGE
                            </span>
                        @elseif(($fileType ?? '') === 'excel' || (isset($officialListData) && !empty($officialListData['isOfficial'])))
                            <i class="fa-solid fa-file-excel text-success fs-5 me-2"></i>
                            <span class="fw-semibold text-truncate small" title="{{ !empty($fileUrl) ? basename($fileUrl) : 'DanhSach_ChinhThuc_DeTai_Nhom_GVHD.xlsx' }}">
                                {{ !empty($fileUrl) ? basename($fileUrl) : 'DanhSach_ChinhThuc_DeTai_Nhom_GVHD.xlsx' }}
                            </span>
                            <span class="badge bg-success rounded-pill px-2 small" style="font-size: 0.7rem;">
                                XLSX
                            </span>
                        @else
                            <i class="fa-solid fa-file-pdf text-danger fs-5 me-2"></i>
                            <span class="fw-semibold text-truncate small" title="{{ !empty($fileUrl) ? basename($fileUrl) : 'VanBan_ThongBao.pdf' }}">
                                {{ !empty($fileUrl) ? basename($fileUrl) : 'Văn Bản Công Văn Thông Báo (Khoa CNTT)' }}
                            </span>
                            <span class="badge bg-secondary rounded-pill px-2 small" style="font-size: 0.7rem;">
                                PDF
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        @if(isset($officialListData) && !empty($officialListData['isOfficial']))
                            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1 fw-bold shadow-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalPopupXemNhanh" title="Mở bảng tính xem nhanh toàn màn hình">
                                <i class="fa-solid fa-up-right-and-down-left-from-center me-2"></i> Popup Xem Nhanh
                            </button>
                        @endif
                        @if(!empty($fileUrl))
                            <a href="{{ asset($fileUrl) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 d-inline-flex align-items-center" style="font-size: 0.78rem;" title="Mở trong cửa sổ riêng">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Mở tab mới
                            </a>
                            <a href="{{ asset($fileUrl) }}" download class="btn btn-sm btn-success text-white rounded-pill px-3 py-1 fw-bold shadow-sm d-inline-flex align-items-center" style="font-size: 0.78rem;" title="Xuất file Excel về máy">
                                <i class="fa-solid fa-file-excel me-2"></i> Xuất Excel (.{{ strtoupper(pathinfo($fileUrl, PATHINFO_EXTENSION)) }})
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-light rounded-circle p-1" style="width: 32px; height: 32px;" onclick="toggleDocFullscreen()" title="Toàn màn hình">
                            <i class="fa-solid fa-expand" id="fullscreenIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="doc-viewer-body">
                    @if(isset($officialListData) && !empty($officialListData['isOfficial']))
                        @php
                            $isNhomOfficial = str_contains(mb_strtolower($thongBao->TieuDe ?? ''), 'sinh viên đăng ký') 
                                || str_contains(mb_strtolower($thongBao->TieuDe ?? ''), 'danh sách nhóm') 
                                || str_contains(mb_strtolower($thongBao->TieuDe ?? ''), 'nhóm');
                        @endphp
                        {{-- Hiển thị trực tiếp Bảng Tính Excel Xem Nhanh (Đề Tài - Nhóm SV - GVHD) --}}
                        <div class="excel-window-container w-100 h-100">
                            <!-- Excel Formula Bar -->
                            <div class="excel-formula-bar">
                                <div class="excel-name-box" id="paneActiveCell">A1</div>
                                <div class="excel-fx-icon">fx</div>
                                <div class="excel-formula-display" id="paneFormulaText">{{ $isNhomOfficial ? 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI & GVHD' : 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN' }}</div>
                                <div class="input-group input-group-sm ms-auto" style="max-width: 240px;">
                                    <span class="input-group-text bg-white border-end-0 py-0"><i class="fa-solid fa-magnifying-glass text-muted small"></i></span>
                                    <input type="text" id="paneExcelSearch" class="form-control form-control-sm border-start-0 py-1" placeholder="Tìm kiếm trong sheet..." onkeyup="filterPaneExcelTable()">
                                </div>
                            </div>

                            <!-- Excel Canvas Scroll -->
                            <div class="excel-canvas-scroll flex-grow-1 bg-white">
                                <div class="tab-content h-100" id="paneExcelTabContent">
                                    <!-- SHEET 1: ĐỀ TÀI ĐĂNG KÝ -->
                                    <div class="tab-pane fade {{ !$isNhomOfficial ? 'show active' : '' }}" id="pane-sheet-detai" role="tabpanel">
                                        <table class="excel-sheet-table" id="paneTableSheet1">
                                            <thead>
                                                <tr>
                                                    <th class="excel-col-head" style="width: 38px;"></th>
                                                    <th class="excel-col-head" style="width: 55px;">A</th>
                                                    <th class="excel-col-head" style="width: 95px;">B</th>
                                                    <th class="excel-col-head" style="width: 440px;">C</th>
                                                    <th class="excel-col-head" style="width: 170px;">D</th>
                                                    <th class="excel-col-head" style="width: 140px;">E</th>
                                                    <th class="excel-col-head" style="width: 180px;">F</th>
                                                    <th class="excel-col-head" style="width: 180px;">G</th>
                                                    <th class="excel-col-head" style="width: 110px;">H</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Row 1: Banner Org -->
                                                <tr>
                                                    <td class="excel-row-num">1</td>
                                                    <td colspan="8" class="excel-banner-org">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN</td>
                                                </tr>
                                                <!-- Row 2: Title -->
                                                <tr>
                                                    <td class="excel-row-num">2</td>
                                                    <td colspan="8" class="excel-banner-title">DANH SÁCH ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP CHÍNH THỨC - {{ $officialListData['stats']['tenHocKy'] }}</td>
                                                </tr>
                                                <!-- Row 3: Subtitle -->
                                                <tr>
                                                    <td class="excel-row-num">3</td>
                                                    <td colspan="8" class="excel-banner-sub">Ngày xuất file: {{ date('d/m/Y H:i') }} | Tiêu chuẩn: Đã duyệt 2 cấp &amp; Được nhóm sinh viên đăng ký</td>
                                                </tr>
                                                <!-- Row 4: Empty row -->
                                                <tr>
                                                    <td class="excel-row-num">4</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                </tr>
                                                <!-- Row 5: Table Header (#0072ce Blue) -->
                                                <tr class="excel-blue-header">
                                                    <td class="excel-row-num" style="background: #e2e8f0;">5</td>
                                                    <th class="text-center" style="width: 55px;">STT</th>
                                                    <th class="text-center" style="width: 95px;">Mã ĐT</th>
                                                    <th>Tên Đề Tài Khóa Luận Tốt Nghiệp</th>
                                                    <th>Lĩnh Vực Nghiên Cứu</th>
                                                    <th>Bộ Môn / Ngành</th>
                                                    <th>Giảng Viên Hướng Dẫn</th>
                                                    <th>Email GVHD</th>
                                                    <th class="text-center" style="width: 110px;">Trạng Thái</th>
                                                </tr>
                                                <!-- Rows 6+: Data rows -->
                                                @forelse($officialListData['deTaiList'] as $idx => $dt)
                                                    <tr class="excel-data-row">
                                                        <td class="excel-row-num">{{ 6 + $idx }}</td>
                                                        <td class="text-center">{{ $idx + 1 }}</td>
                                                        <td class="text-center font-monospace fw-bold text-dark">{{ $dt->MaDeTai }}</td>
                                                        <td>{{ $dt->TenDeTai }}</td>
                                                        <td>{{ $dt->LinhVuc ?? 'Công nghệ phần mềm' }}</td>
                                                        <td>{{ $dt->giangVien?->boMon?->TenBoMon ?? $dt->nganh?->TenNganh ?? 'Khoa CNTT' }}</td>
                                                        <td>{{ $dt->giangVien?->HoTen ?? 'Chưa phân công' }}</td>
                                                        <td>{{ $dt->giangVien?->Email ?? '' }}</td>
                                                        <td class="text-center">{{ $dt->TrangThai }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="excel-row-num">6</td>
                                                        <td colspan="8" class="text-center text-muted py-3">Chưa có đề tài nào</td>
                                                    </tr>
                                                @endforelse

                                                <!-- Blank filler grid rows matching user screenshot -->
                                                @for($r = 6 + count($officialListData['deTaiList']); $r <= 22; $r++)
                                                    <tr>
                                                        <td class="excel-row-num">{{ $r }}</td>
                                                        @if($r === 10)
                                                            <td></td><td></td>
                                                            <td colspan="3" class="excel-active-cell"></td>
                                                            <td></td><td></td><td></td>
                                                        @else
                                                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                        @endif
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- SHEET 2: NHÓM SINH VIÊN -->
                                    <div class="tab-pane fade {{ $isNhomOfficial ? 'show active' : '' }}" id="pane-sheet-nhom" role="tabpanel">
                                        <table class="excel-sheet-table" id="paneTableSheet2">
                                            <thead>
                                                <tr>
                                                    <th class="excel-col-head" style="width: 38px;"></th>
                                                    <th class="excel-col-head" style="width: 50px;">A</th>
                                                    <th class="excel-col-head" style="width: 90px;">B</th>
                                                    <th class="excel-col-head" style="width: 170px;">C</th>
                                                    <th class="excel-col-head" style="width: 110px;">D</th>
                                                    <th class="excel-col-head" style="width: 180px;">E</th>
                                                    <th class="excel-col-head" style="width: 95px;">F</th>
                                                    <th class="excel-col-head" style="width: 110px;">G</th>
                                                    <th class="excel-col-head" style="width: 95px;">H</th>
                                                    <th class="excel-col-head" style="width: 380px;">I</th>
                                                    <th class="excel-col-head" style="width: 170px;">J</th>
                                                    <th class="excel-col-head" style="width: 100px;">K</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="excel-row-num">1</td>
                                                    <td colspan="11" class="excel-banner-org">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN</td>
                                                </tr>
                                                <tr>
                                                    <td class="excel-row-num">2</td>
                                                    <td colspan="11" class="excel-banner-title">DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI &amp; GVHD - {{ $officialListData['stats']['tenHocKy'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="excel-row-num">3</td>
                                                    <td colspan="11" class="excel-banner-sub">Ngày xuất file: {{ date('d/m/Y H:i') }} | Tổng số nhóm: {{ $officialListData['stats']['soNhom'] }} | Tổng số SV: {{ $officialListData['stats']['soSV'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="excel-row-num">4</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                </tr>
                                                <tr class="excel-blue-header">
                                                    <td class="excel-row-num" style="background: #e2e8f0;">5</td>
                                                    <th class="text-center" style="width: 50px;">STT</th>
                                                    <th class="text-center" style="width: 90px;">Mã Nhóm</th>
                                                    <th>Tên Nhóm</th>
                                                    <th class="text-center" style="width: 110px;">Mã SV</th>
                                                    <th>Họ Và Tên Sinh Viên</th>
                                                    <th class="text-center" style="width: 95px;">Lớp</th>
                                                    <th class="text-center" style="width: 110px;">Vai Trò</th>
                                                    <th class="text-center" style="width: 95px;">Mã ĐT</th>
                                                    <th>Tên Đề Tài Khóa Luận</th>
                                                    <th>GVHD Phụ Trách</th>
                                                    <th class="text-center" style="width: 100px;">Trạng Thái ĐK</th>
                                                </tr>
                                                @php $curRowP2 = 6; $sttNP2 = 1; @endphp
                                                @forelse($officialListData['nhomList'] as $nh)
                                                    @php
                                                        $pdk = $nh->dangKyDeTai;
                                                        $deTai = $pdk?->deTai ?? $nh->deTai;
                                                        $gv = $pdk?->giangVienHuongDan ?? $deTai?->giangVien;
                                                    @endphp
                                                    @if($nh->thanhViens && $nh->thanhViens->isNotEmpty())
                                                        @foreach($nh->thanhViens as $tv)
                                                            @php $sv = $tv->sinhVien; @endphp
                                                            <tr class="excel-data-row">
                                                                <td class="excel-row-num">{{ $curRowP2++ }}</td>
                                                                <td class="text-center">{{ $sttNP2++ }}</td>
                                                                <td class="text-center fw-bold">{{ $nh->MaNhom }}</td>
                                                                <td>{{ $nh->TenNhom }}</td>
                                                                <td class="text-center font-monospace fw-bold">{{ $tv->MaSV }}</td>
                                                                <td>{{ $sv?->HoTen ?? 'N/A' }}</td>
                                                                <td class="text-center">{{ $sv?->lop?->TenLop ?? '' }}</td>
                                                                <td class="text-center">{{ $tv->VaiTro ?? 'Thành viên' }}</td>
                                                                <td class="text-center font-monospace">{{ $deTai?->MaDeTai ?? '' }}</td>
                                                                <td>{{ $deTai?->TenDeTai ?? 'Chưa gán đề tài' }}</td>
                                                                <td>{{ $gv?->HoTen ?? '' }}</td>
                                                                <td class="text-center">{{ $pdk?->TrangThai ?? 'Đã duyệt' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr class="excel-data-row">
                                                            <td class="excel-row-num">{{ $curRowP2++ }}</td>
                                                            <td class="text-center">{{ $sttNP2++ }}</td>
                                                            <td class="text-center fw-bold">{{ $nh->MaNhom }}</td>
                                                            <td>{{ $nh->TenNhom }}</td>
                                                            <td class="text-center font-monospace fw-bold">{{ $nh->truongNhom?->MaSV ?? '' }}</td>
                                                            <td>{{ $nh->truongNhom?->HoTen ?? 'N/A' }}</td>
                                                            <td class="text-center">{{ $nh->truongNhom?->lop?->TenLop ?? '' }}</td>
                                                            <td class="text-center">Trưởng nhóm</td>
                                                            <td class="text-center font-monospace">{{ $deTai?->MaDeTai ?? '' }}</td>
                                                            <td>{{ $deTai?->TenDeTai ?? 'Chưa gán đề tài' }}</td>
                                                            <td>{{ $gv?->HoTen ?? '' }}</td>
                                                            <td class="text-center">{{ $pdk?->TrangThai ?? 'Đã duyệt' }}</td>
                                                        </tr>
                                                    @endif
                                                @empty
                                                    <tr>
                                                        <td class="excel-row-num">6</td>
                                                        <td colspan="11" class="text-center text-muted py-3">Chưa có nhóm nào</td>
                                                    </tr>
                                                @endforelse

                                                @for($r = $curRowP2; $r <= 22; $r++)
                                                    <tr>
                                                        <td class="excel-row-num">{{ $r }}</td>
                                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- SHEET 3: GV HƯỚNG DẪN -->
                                    <div class="tab-pane fade" id="pane-sheet-gvhd" role="tabpanel">
                                        <table class="excel-sheet-table" id="paneTableSheet3">
                                            <thead>
                                                <tr>
                                                    <th class="excel-col-head" style="width: 38px;"></th>
                                                    <th class="excel-col-head" style="width: 55px;">A</th>
                                                    <th class="excel-col-head" style="width: 100px;">B</th>
                                                    <th class="excel-col-head" style="width: 220px;">C</th>
                                                    <th class="excel-col-head" style="width: 150px;">D</th>
                                                    <th class="excel-col-head" style="width: 180px;">E</th>
                                                    <th class="excel-col-head" style="width: 200px;">F</th>
                                                    <th class="excel-col-head" style="width: 120px;">G</th>
                                                    <th class="excel-col-head" style="width: 140px;">H</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="excel-row-num">1</td>
                                                    <td colspan="8" class="excel-banner-org">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN</td>
                                                </tr>
                                                <tr>
                                                    <td class="excel-row-num">2</td>
                                                    <td colspan="8" class="excel-banner-title">DANH SÁCH GIẢNG VIÊN HƯỚNG DẪN KHÓA LUẬN - {{ $officialListData['stats']['tenHocKy'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="excel-row-num">3</td>
                                                    <td colspan="8" class="excel-banner-sub">Ngày xuất file: {{ date('d/m/Y H:i') }} | Tổng số GVHD phụ trách: {{ $officialListData['stats']['soGVHD'] }} giảng viên</td>
                                                </tr>
                                                <tr>
                                                    <td class="excel-row-num">4</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                </tr>
                                                <tr class="excel-blue-header">
                                                    <td class="excel-row-num" style="background: #e2e8f0;">5</td>
                                                    <th class="text-center" style="width: 55px;">STT</th>
                                                    <th class="text-center" style="width: 100px;">Mã GV</th>
                                                    <th>Họ Và Tên Giảng Viên</th>
                                                    <th>Học Vị / Học Hàm</th>
                                                    <th>Bộ Môn Chuyên Môn</th>
                                                    <th>Email Liên Hệ</th>
                                                    <th class="text-center" style="width: 120px;">Số Điện Thoại</th>
                                                    <th class="text-center" style="width: 140px;">Số Đề Tài Hướng Dẫn</th>
                                                </tr>
                                                @php $curRowP3 = 6; @endphp
                                                @forelse($officialListData['gvhdList'] as $idx => $gv)
                                                    @php
                                                        $hasTitleInNamePane = preg_match('/^(PGS|GS|TS|ThS|CN|KS)\b/i', trim($gv->HoTen ?? ''));
                                                        $displayTitlePane = (!$hasTitleInNamePane && !empty($gv->HocVi)) ? $gv->HocVi . '. ' : '';
                                                    @endphp
                                                    <tr class="excel-data-row">
                                                        <td class="excel-row-num">{{ $curRowP3++ }}</td>
                                                        <td class="text-center">{{ $idx + 1 }}</td>
                                                        <td class="text-center font-monospace fw-bold">{{ $gv->MaGV }}</td>
                                                        <td class="fw-semibold">{{ $displayTitlePane }}{{ $gv->HoTen }}</td>
                                                        <td>{{ trim(($gv->HocHam ? $gv->HocHam . ' - ' : '') . ($gv->HocVi ?? '')) }}</td>
                                                        <td>{{ $gv->boMon?->TenBoMon ?? 'Khoa CNTT' }}</td>
                                                        <td>{{ $gv->Email }}</td>
                                                        <td class="text-center">{{ $gv->SoDienThoai ?? '-' }}</td>
                                                        <td class="text-center fw-bold text-dark">{{ $gv->soDeTaiHuongDan ?? ($gv->deTais ? $gv->deTais->count() : 0) }} đề tài</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="excel-row-num">6</td>
                                                        <td colspan="8" class="text-center text-muted py-3">Chưa có giảng viên hướng dẫn</td>
                                                    </tr>
                                                @endforelse

                                                @for($r = $curRowP3; $r <= 22; $r++)
                                                    <tr>
                                                        <td class="excel-row-num">{{ $r }}</td>
                                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Excel Sheet Tabs Navigation Bar (At Bottom of Pane) -->
                            <div class="excel-sheet-footer-bar">
                                <button type="button" class="excel-sheet-nav-btn" title="Đầu tiên"><i class="fa-solid fa-angles-left"></i></button>
                                <button type="button" class="excel-sheet-nav-btn" title="Trước"><i class="fa-solid fa-angle-left"></i></button>
                                <button type="button" class="excel-sheet-nav-btn" title="Tiếp"><i class="fa-solid fa-angle-right"></i></button>
                                <button type="button" class="excel-sheet-nav-btn" title="Cuối cùng"><i class="fa-solid fa-angles-right"></i></button>
                                <div class="d-flex align-items-center ms-2" role="tablist">
                                    <button class="excel-sheet-tab-item {{ $isNhomOfficial ? 'active' : '' }}" id="pane-tab-btn-2" data-bs-toggle="tab" data-bs-target="#pane-sheet-nhom" type="button" role="tab" onclick="updateFormulaBar('DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI & GVHD', 'pane')">
                                        <i class="fa-solid fa-file-excel text-success me-2"></i> {{ $isNhomOfficial ? '1.' : '2.' }} Nhóm SV &amp; Đề Tài &amp; GVHD
                                    </button>
                                    <button class="excel-sheet-tab-item {{ !$isNhomOfficial ? 'active' : '' }}" id="pane-tab-btn-1" data-bs-toggle="tab" data-bs-target="#pane-sheet-detai" type="button" role="tab" onclick="updateFormulaBar('DANH SÁCH ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP CHÍNH THỨC', 'pane')">
                                        <i class="fa-solid fa-file-excel text-success me-2"></i> {{ $isNhomOfficial ? '2.' : '1.' }} Đề Tài Đăng Ký
                                    </button>
                                    <button class="excel-sheet-tab-item" id="pane-tab-btn-3" data-bs-toggle="tab" data-bs-target="#pane-sheet-gvhd" type="button" role="tab" onclick="updateFormulaBar('DANH SÁCH GIẢNG VIÊN HƯỚNG DẪN KHÓA LUẬN', 'pane')">
                                        <i class="fa-solid fa-file-excel text-success me-2"></i> 3. GV Hướng Dẫn
                                    </button>
                                </div>
                                <span class="ms-2 text-muted small cursor-pointer" title="Thêm sheet mới"><i class="fa-solid fa-plus"></i></span>
                            </div>

                            <!-- Excel Status Bar -->
                            <div class="excel-status-bar">
                                <div class="d-flex align-items-center gap-3">
                                    <span>Sẵn sàng</span>
                                    <span class="d-none d-sm-inline">&bull;</span>
                                    <span class="d-none d-sm-inline">Tổng: {{ $officialListData['stats']['soDeTai'] }} đề tài, {{ $officialListData['stats']['soNhom'] }} nhóm</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    @if(!empty($fileUrl))
                                        <a href="{{ asset($fileUrl) }}" download class="text-success text-decoration-none fw-bold" style="font-size: 11px;">
                                            <i class="fa-solid fa-download me-2"></i> Tải về (.xlsx)
                                        </a>
                                    @endif
                                    <span>100%</span>
                                </div>
                            </div>
                        </div>
                    @elseif(!empty($fileUrl))
                        @if(($fileType ?? '') === 'image')
                            {{-- Hiển thị trực tiếp Ảnh công văn --}}
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center p-3 overflow-auto" style="background: #334155;">
                                <img src="{{ asset($fileUrl) }}" alt="Ảnh công văn thông báo" class="img-fluid rounded shadow" style="max-height: 100%; object-fit: contain;">
                            </div>
                        @elseif(($fileType ?? '') === 'excel')
                            {{-- Hiển thị trực tiếp Bảng tính Excel / Danh sách sinh viên đủ điều kiện --}}
                            <div class="w-100 h-100 d-flex flex-column bg-white">
                                <!-- Excel Control Bar -->
                                <div class="p-2 px-3 border-bottom bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-file-excel text-success fs-5 me-2"></i>
                                        <span class="fw-bold small text-dark">
                                            @if(isset($duDieuKienStats))
                                                Danh Sách Sinh Viên Đủ Điều Kiện - {{ $duDieuKienStats['tenHocKy'] }}
                                            @else
                                                Bảng Tính Dữ Liệu Đính Kèm (.xlsx)
                                            @endif
                                        </span>
                                        @if(isset($duDieuKienStats))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2">
                                                <i class="fa-solid fa-check-double me-2"></i> {{ $duDieuKienStats['eligible'] }}/{{ $duDieuKienStats['total'] }} SV Đủ điều kiện
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(isset($duDieuKienList) && $duDieuKienList->isNotEmpty())
                                            <div class="input-group input-group-sm" style="max-width: 200px;">
                                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted small"></i></span>
                                                <input type="text" id="excelSearchInput" class="form-control form-control-sm border-start-0 ps-0" placeholder="Tìm MSSV, họ tên..." onkeyup="filterExcelTable()">
                                            </div>
                                        @endif
                                        <a href="{{ asset($fileUrl) }}" download class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold text-white shadow-sm d-inline-flex align-items-center" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-file-excel me-2"></i> Tải File Excel (.xlsx)
                                        </a>
                                    </div>
                                </div>

                                <!-- Excel Table Container -->
                                <div class="flex-grow-1 overflow-auto p-0" style="max-height: calc(100vh - 280px); min-height: 380px;">
                                    @if(isset($duDieuKienList) && $duDieuKienList->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered table-sm align-middle mb-0 text-nowrap" id="excelTable" style="font-size: 0.82rem;">
                                                <thead class="table-light sticky-top" style="z-index: 5;">
                                                    <tr class="text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                                        <th class="text-center" style="width: 45px;">STT</th>
                                                        <th class="text-center" style="width: 105px;">Mã Sinh Viên</th>
                                                        <th>Họ Và Tên</th>
                                                        <th class="text-center">Lớp</th>
                                                        <th>Ngành Học</th>
                                                        <th class="text-center" style="width: 80px;">Tín Chỉ TL</th>
                                                        <th class="text-center" style="width: 80px;">ĐTB TL</th>
                                                        <th class="text-center" style="width: 110px;">Trạng Thái</th>
                                                        <th>Tiêu Chí &amp; Ghi Chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($duDieuKienList as $idx => $item)
                                                        @php
                                                            $sv = $item->sinhVien;
                                                            $isEligible = ($item->TrangThai === 'Đủ điều kiện');
                                                        @endphp
                                                        <tr class="excel-row">
                                                            <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                                                            <td class="text-center fw-bold text-primary">{{ $item->MaSV }}</td>
                                                            <td class="fw-semibold text-dark">{{ $sv->HoTen ?? 'N/A' }}</td>
                                                            <td class="text-center">{{ $sv->lop->TenLop ?? '' }}</td>
                                                            <td>{{ $sv->lop->nganh->TenNganh ?? '' }}</td>
                                                            <td class="text-center fw-semibold">{{ $sv->SoTinChiTichLuy ?? 0 }}</td>
                                                            <td class="text-center fw-semibold {{ (float)($sv->DiemTichLuy ?? 0) >= 2.0 ? 'text-success' : 'text-danger' }}">
                                                                {{ number_format((float)($sv->DiemTichLuy ?? 0), 2) }}
                                                            </td>
                                                            <td class="text-center">
                                                                @if($isEligible)
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                                        <i class="fa-solid fa-circle-check me-2"></i> Đủ điều kiện
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                                        <i class="fa-solid fa-circle-xmark me-2"></i> Chưa đủ ĐK
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $item->DieuKien }} {{ $item->GhiChu }}">
                                                                {{ $item->DieuKien }} ({{ $item->GhiChu }})
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @elseif(!empty($excelRows))
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered table-sm align-middle mb-0 text-nowrap" style="font-size: 0.82rem;">
                                                @foreach($excelRows as $rIdx => $row)
                                                    @if($rIdx === 0)
                                                        <thead class="table-light sticky-top">
                                                            <tr class="text-secondary text-uppercase" style="font-size: 0.72rem;">
                                                                @foreach($row as $cell)
                                                                    <th>{{ $cell }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                    @else
                                                        <tr>
                                                            @foreach($row as $cell)
                                                                <td>{{ $cell }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                                            <i class="fa-solid fa-file-excel text-success display-4 mb-3"></i>
                                            <h6 class="fw-bold text-dark mb-2">Tệp Bảng Tính Đính Kèm (.XLSX)</h6>
                                            <p class="text-muted small mb-3">Tệp danh sách đã sẵn sàng để tải về và mở trên Microsoft Excel.</p>
                                            <a href="{{ asset($fileUrl) }}" download class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center">
                                                <i class="fa-solid fa-download me-2"></i> Tải Danh Sách Excel (.xlsx)
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Nhúng trực tiếp PDF Viewer native của trình duyệt --}}
                            <iframe id="docViewerIframe" src="{{ asset($fileUrl) }}#toolbar=1&navpanes=0"></iframe>
                        @endif
                    @else
                        {{-- Giao diện văn bản công văn chính thức khi chưa upload file PDF --}}
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-4" style="background: #f8fafc;">
                            <div class="rounded-circle bg-primary-subtle text-primary p-4 mb-3">
                                <i class="fa-solid fa-file-circle-check fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Văn Bản Thông Báo Chính Thức</h5>
                            <p class="text-muted small max-w-md mb-3" style="max-width: 420px;">
                                Văn bản thông báo số <strong>{{ $thongBao->keHoach->NoiDung ?? '27/TB-KCNTT' }}</strong> đã được lưu hành chính thức trên toàn hệ thống học vụ.
                            </p>
                            @if($thongBao->keHoach && auth()->check() && auth()->user()->MaVaiTro === 'VT01')
                                <a href="{{ route('admin.kehoach.show', $thongBao->keHoach->MaKeHoach) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold">
                                    <i class="fa-solid fa-calendar-check me-2"></i> Xem Chi Tiết Kế Hoạch &amp; Mốc Thời Gian
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: BẢNG THÔNG TIN THÔNG BÁO & CHI TIẾT QUY TRÌNH (42%) --}}
        <div class="col-12 col-xl-5">
            <div class="noti-detail-card">
                <div class="noti-detail-scroll">
                    <!-- Badge phân loại & Trạng thái -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold">
                            <i class="fa-solid fa-bullhorn me-2"></i> {{ $thongBao->LoaiThongBao ?? 'Thông báo Khóa luận' }}
                        </span>
                        <span class="small text-muted">
                            <i class="fa-regular fa-calendar-check me-2"></i> {{ date('d/m/Y H:i', strtotime($thongBao->NgayTao)) }}
                        </span>
                    </div>

                    <!-- Tiêu đề thông báo -->
                    <h5 class="fw-bold text-dark mb-3" style="line-height: 1.45;">
                        {{ $thongBao->TieuDe }}
                    </h5>

                    <!-- Thẻ Metadata Tóm Tắt -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="text-muted">Cơ quan ban hành:</div>
                                <div class="fw-bold text-dark mt-1">
                                    <i class="fa-solid fa-building-columns text-primary me-2"></i> Khoa CNTT
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Đối tượng tiếp nhận:</div>
                                <div class="fw-bold text-dark mt-1">
                                    <i class="fa-solid fa-users text-primary me-2"></i> {{ $thongBao->DoiTuongNhan ?? 'Toàn thể hệ thống' }}
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="text-muted">Người phát hành:</div>
                                <div class="fw-bold text-dark mt-1">
                                    <i class="fa-solid fa-user-check text-primary me-2"></i> {{ $thongBao->giaoVu->HoTen ?? $thongBao->MaGVu ?? 'Ban Giáo vụ' }}
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="text-muted">Trạng thái công bố:</div>
                                <div class="fw-bold text-success mt-1">
                                    <i class="fa-solid fa-circle-check text-success me-2"></i> Có hiệu lực ngay
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($officialListData) && !empty($officialListData['isOfficial']))
                    <!-- Thẻ Tóm Tắt Dữ Liệu Excel Chính Thức -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small">
                                <i class="fa-solid fa-file-excel text-success me-2"></i>
                                Bảng Dữ Liệu Khóa Luận Chính Thức
                            </span>
                            @if(!empty($fileUrl))
                                <a href="{{ asset($fileUrl) }}" download class="badge bg-success text-white text-decoration-none px-3 py-1">
                                    <i class="fa-solid fa-download me-2"></i> Xuất .xlsx
                                </a>
                            @endif
                        </div>
                        <div class="row g-2 text-center small">
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-2 border">
                                    <div class="fw-bold text-primary fs-6">{{ $officialListData['stats']['soNhom'] }}</div>
                                    <div class="text-muted" style="font-size: 11px;">Nhóm Sinh Viên</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-2 border">
                                    <div class="fw-bold text-success fs-6">{{ $officialListData['stats']['soDeTai'] }}</div>
                                    <div class="text-muted" style="font-size: 11px;">Đề Tài Đăng Ký</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-2 border">
                                    <div class="fw-bold text-info fs-6">{{ $officialListData['stats']['soGVHD'] }}</div>
                                    <div class="text-muted" style="font-size: 11px;">GV Hướng Dẫn</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Nội dung thông báo chi tiết -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-align-left text-primary me-2"></i>
                            <span>Nội Dung Thông Báo Chính Thức</span>
                        </h6>
                        <div class="p-3 rounded-3 border bg-white text-dark shadow-xs" style="font-size: 0.92rem; line-height: 1.75; border-left: 4px solid #0072ce !important;">
                            {!! \App\Helpers\HtmlHelper::clean($thongBao->NoiDung) !!}
                        </div>
                    </div>

                    <!-- Kế hoạch & Học kỳ liên quan nếu có -->
                    @if($thongBao->keHoach)
                    <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted fw-semibold">Kế hoạch Khóa luận liên kết:</div>
                                <div class="fw-bold text-primary">{{ $thongBao->keHoach->TenKeHoach }}</div>
                                <div class="small text-muted mt-1">Học kỳ: {{ $thongBao->keHoach->hocKy->TenHocKy ?? $thongBao->keHoach->MaHocKy }}</div>
                            </div>
                            <a href="{{ route('admin.kehoach.show', $thongBao->keHoach->MaKeHoach) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                Xem Kế Hoạch <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Thống kê phân phối người nhận (Chỉ hiển thị cho Quản trị viên/Giáo vụ) -->
                    @if(isset($totalSent) && (!isset($layout) || $layout === 'layouts.admin'))
                    <div class="mt-auto pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-bold text-uppercase" style="letter-spacing: 0.5px;">Phân phối thông báo:</span>
                            <span class="badge bg-light text-primary border rounded-pill px-3">{{ $totalSent ?? 0 }} Tài khoản tiếp nhận</span>
                        </div>
                        <div class="row g-2 text-center small">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 border">
                                    <div class="fw-bold text-primary fs-6">{{ $totalSent ?? 0 }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Đã gửi</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-success-subtle text-success rounded-3 border border-success-subtle">
                                    <div class="fw-bold fs-6">{{ $readCount ?? 0 }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Đã xem</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 border border-warning-subtle">
                                    <div class="fw-bold fs-6">{{ $unreadCount ?? 0 }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Chờ xem</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- GIAO DIỆN THUẦN THÔNG TIN KHI THÔNG BÁO KHÔNG CÓ FILE ĐÍNH KÈM --}}
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9 col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                            <i class="fa-solid fa-bullhorn me-2"></i> {{ $thongBao->LoaiThongBao ?? 'Thông báo Khóa luận' }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ $thongBao->TrangThai ?? 'Đã phát hành' }}
                        </span>
                    </div>
                    <span class="text-muted small">
                        <i class="fa-regular fa-clock me-1"></i> {{ date('d/m/Y H:i', strtotime($thongBao->NgayTao ?? $thongBao->created_at)) }}
                    </span>
                </div>

                <h4 class="fw-bold text-dark mb-4" style="line-height: 1.45;">
                    {{ $thongBao->TieuDe }}
                </h4>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-3 small">
                        <div class="col-md-4 col-sm-6">
                            <div class="text-muted">Cơ quan / Người phát hành:</div>
                            <div class="fw-bold text-dark mt-1">
                                <i class="fa-solid fa-building-columns text-primary me-2"></i>
                                {{ $thongBao->giangVien ? 'GV ' . $thongBao->giangVien->HoTen : ($thongBao->giaoVu->HoTen ?? 'Khoa Công nghệ Thông tin') }}
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="text-muted">Đối tượng tiếp nhận:</div>
                            <div class="fw-bold text-dark mt-1">
                                <i class="fa-solid fa-users text-primary me-2"></i>
                                {{ $thongBao->DoiTuongNhan ?? 'Toàn thể sinh viên & giảng viên' }}
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="text-muted">Mã số thông báo:</div>
                            <div class="fw-bold text-dark mt-1 font-monospace">
                                <i class="fa-solid fa-hashtag text-primary me-2"></i>
                                {{ $thongBao->MaThongBao }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-align-left text-primary me-2"></i>
                        <span>Nội Dung Thông Báo</span>
                    </h6>
                    <div class="p-4 rounded-3 border bg-light-subtle text-dark shadow-xs" style="font-size: 0.98rem; line-height: 1.85; border-left: 5px solid #0072ce !important; min-height: 160px;">
                        {!! \App\Helpers\HtmlHelper::clean($thongBao->NoiDung) !!}
                    </div>
                </div>

                @if($thongBao->keHoach)
                <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="small text-muted fw-semibold">Kế hoạch Khóa luận liên kết:</div>
                            <div class="fw-bold text-primary">{{ $thongBao->keHoach->TenKeHoach }}</div>
                            <div class="small text-muted mt-1">Học kỳ: {{ $thongBao->keHoach->hocKy->TenHocKy ?? $thongBao->keHoach->MaHocKy }}</div>
                        </div>
                        @if(auth()->check() && auth()->user()->MaVaiTro === 'VT01')
                        <a href="{{ route('admin.kehoach.show', $thongBao->keHoach->MaKeHoach) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                            Xem Kế Hoạch <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if(isset($totalSent) && (!isset($layout) || $layout === 'layouts.admin'))
                <div class="pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted fw-bold text-uppercase" style="letter-spacing: 0.5px;">Phân phối thông báo:</span>
                        <span class="badge bg-light text-primary border rounded-pill px-3">{{ $totalSent ?? 0 }} Tài khoản tiếp nhận</span>
                    </div>
                    <div class="row g-2 text-center small">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded-3 border">
                                <div class="fw-bold text-primary fs-6">{{ $totalSent ?? 0 }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Đã gửi</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-success-subtle text-success rounded-3 border border-success-subtle">
                                <div class="fw-bold fs-6">{{ $readCount ?? 0 }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Đã xem</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 border border-warning-subtle">
                                <div class="fw-bold fs-6">{{ $unreadCount ?? 0 }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Chờ xem</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@if(isset($officialListData) && !empty($officialListData['isOfficial']))
{{-- MODAL POPUP XEM NHANH DANH SÁCH CHÍNH THỨC TOÀN CẢNH --}}
<div class="modal fade" id="modalPopupXemNhanh" tabindex="-1" aria-labelledby="modalPopupXemNhanhLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 96vw;">
        <div class="modal-content border-0 shadow-2xl rounded-3 overflow-hidden excel-window-container" style="height: 90vh;">
            <!-- Excel Window Header (Ribbon Top Bar) -->
            <div class="modal-header text-white p-2 px-3 d-flex align-items-center justify-content-between" style="background: #107c41 !important;">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <i class="fa-solid fa-file-excel fs-4 text-white"></i>
                    <div class="text-truncate">
                        <span class="fw-bold small text-white" id="modalPopupXemNhanhLabel">
                            {{ !empty($fileUrl) ? basename($fileUrl) : 'DanhSach_ChinhThuc_DeTai_Nhom_GVHD.xlsx' }} - Excel Xem Nhanh
                        </span>
                        <span class="badge bg-white text-success px-2 py-0 ms-2 small" style="font-size: 0.68rem; font-weight: 700;">CHÍNH THỨC</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    @if(!empty($fileUrl))
                        <a href="{{ asset($fileUrl) }}" download class="btn btn-sm btn-light text-success fw-bold rounded-1 px-3 py-1 shadow-sm d-inline-flex align-items-center" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-download me-2"></i> Xuất File Excel (.xlsx)
                        </a>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-light rounded-1 px-2 py-1" onclick="window.print()" title="In bảng tính">
                        <i class="fa-solid fa-print"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Excel Formula Bar -->
            <div class="excel-formula-bar">
                <div class="excel-name-box" id="modalActiveCell">A1</div>
                <div class="excel-fx-icon">fx</div>
                <div class="excel-formula-display" id="modalFormulaText">{{ $isNhomOfficial ? 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI & GVHD' : 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN' }}</div>
                <div class="input-group input-group-sm ms-auto" style="max-width: 260px;">
                    <span class="input-group-text bg-white border-end-0 py-0"><i class="fa-solid fa-magnifying-glass text-muted small"></i></span>
                    <input type="text" id="modalExcelSearch" class="form-control form-control-sm border-start-0 py-1" placeholder="Tìm kiếm trong sheet..." onkeyup="filterModalExcelTable()">
                </div>
            </div>

            <!-- Modal Body: Excel Grid Canvas -->
            <div class="modal-body p-0 excel-canvas-scroll bg-white">
                <div class="tab-content h-100" id="modalExcelTabContent">
                    <!-- SHEET 1: ĐỀ TÀI ĐĂNG KÝ -->
                    <div class="tab-pane fade {{ !$isNhomOfficial ? 'show active' : '' }}" id="modal-sheet-detai" role="tabpanel">
                        <table class="excel-sheet-table" id="modalTableSheet1">
                            <thead>
                                <tr>
                                    <th class="excel-col-head" style="width: 38px;"></th>
                                    <th class="excel-col-head" style="width: 55px;">A</th>
                                    <th class="excel-col-head" style="width: 95px;">B</th>
                                    <th class="excel-col-head" style="width: 480px;">C</th>
                                    <th class="excel-col-head" style="width: 170px;">D</th>
                                    <th class="excel-col-head" style="width: 140px;">E</th>
                                    <th class="excel-col-head" style="width: 180px;">F</th>
                                    <th class="excel-col-head" style="width: 180px;">G</th>
                                    <th class="excel-col-head" style="width: 110px;">H</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1: Banner Org -->
                                <tr>
                                    <td class="excel-row-num">1</td>
                                    <td colspan="8" class="excel-banner-org">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN</td>
                                </tr>
                                <!-- Row 2: Title -->
                                <tr>
                                    <td class="excel-row-num">2</td>
                                    <td colspan="8" class="excel-banner-title">DANH SÁCH ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP CHÍNH THỨC - {{ $officialListData['stats']['tenHocKy'] }}</td>
                                </tr>
                                <!-- Row 3: Subtitle -->
                                <tr>
                                    <td class="excel-row-num">3</td>
                                    <td colspan="8" class="excel-banner-sub">Ngày xuất file: {{ date('d/m/Y H:i') }} | Tiêu chuẩn: Đã duyệt 2 cấp &amp; Được nhóm sinh viên đăng ký</td>
                                </tr>
                                <!-- Row 4: Empty row -->
                                <tr>
                                    <td class="excel-row-num">4</td>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                                <!-- Row 5: Table Header (#0072ce Blue) -->
                                <tr class="excel-blue-header">
                                    <td class="excel-row-num" style="background: #e2e8f0;">5</td>
                                    <th class="text-center" style="width: 55px;">STT</th>
                                    <th class="text-center" style="width: 95px;">Mã ĐT</th>
                                    <th>Tên Đề Tài Khóa Luận Tốt Nghiệp</th>
                                    <th>Lĩnh Vực Nghiên Cứu</th>
                                    <th>Bộ Môn / Ngành</th>
                                    <th>Giảng Viên Hướng Dẫn</th>
                                    <th>Email GVHD</th>
                                    <th class="text-center" style="width: 110px;">Trạng Thái</th>
                                </tr>
                                <!-- Rows 6+: Data rows -->
                                @forelse($officialListData['deTaiList'] as $idx => $dt)
                                    <tr class="excel-data-row">
                                        <td class="excel-row-num">{{ 6 + $idx }}</td>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td class="text-center font-monospace fw-bold text-dark">{{ $dt->MaDeTai }}</td>
                                        <td>{{ $dt->TenDeTai }}</td>
                                        <td>{{ $dt->LinhVuc ?? 'Công nghệ phần mềm' }}</td>
                                        <td>{{ $dt->giangVien?->boMon?->TenBoMon ?? $dt->nganh?->TenNganh ?? 'Khoa CNTT' }}</td>
                                        <td>{{ $dt->giangVien?->HoTen ?? 'Chưa phân công' }}</td>
                                        <td>{{ $dt->giangVien?->Email ?? '' }}</td>
                                        <td class="text-center">{{ $dt->TrangThai }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="excel-row-num">6</td>
                                        <td colspan="8" class="text-center text-muted py-3">Chưa có đề tài nào</td>
                                    </tr>
                                @endforelse

                                <!-- Blank filler grid rows matching user screenshot -->
                                @for($r = 6 + count($officialListData['deTaiList']); $r <= 22; $r++)
                                    <tr>
                                        <td class="excel-row-num">{{ $r }}</td>
                                        @if($r === 10)
                                            <td></td><td></td>
                                            <td colspan="3" class="excel-active-cell"></td>
                                            <td></td><td></td><td></td>
                                        @else
                                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- SHEET 2: NHÓM SINH VIÊN -->
                    <div class="tab-pane fade {{ $isNhomOfficial ? 'show active' : '' }}" id="modal-sheet-nhom" role="tabpanel">
                        <table class="excel-sheet-table" id="modalTableSheet2">
                            <thead>
                                <tr>
                                    <th class="excel-col-head" style="width: 38px;"></th>
                                    <th class="excel-col-head" style="width: 50px;">A</th>
                                    <th class="excel-col-head" style="width: 90px;">B</th>
                                    <th class="excel-col-head" style="width: 170px;">C</th>
                                    <th class="excel-col-head" style="width: 110px;">D</th>
                                    <th class="excel-col-head" style="width: 180px;">E</th>
                                    <th class="excel-col-head" style="width: 95px;">F</th>
                                    <th class="excel-col-head" style="width: 110px;">G</th>
                                    <th class="excel-col-head" style="width: 95px;">H</th>
                                    <th class="excel-col-head" style="width: 380px;">I</th>
                                    <th class="excel-col-head" style="width: 170px;">J</th>
                                    <th class="excel-col-head" style="width: 100px;">K</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="excel-row-num">1</td>
                                    <td colspan="11" class="excel-banner-org">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN</td>
                                </tr>
                                <tr>
                                    <td class="excel-row-num">2</td>
                                    <td colspan="11" class="excel-banner-title">DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI &amp; GVHD - {{ $officialListData['stats']['tenHocKy'] }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-row-num">3</td>
                                    <td colspan="11" class="excel-banner-sub">Ngày xuất file: {{ date('d/m/Y H:i') }} | Tổng số nhóm: {{ $officialListData['stats']['soNhom'] }} | Tổng số SV: {{ $officialListData['stats']['soSV'] }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-row-num">4</td>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                                <tr class="excel-blue-header">
                                    <td class="excel-row-num" style="background: #e2e8f0;">5</td>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th class="text-center" style="width: 90px;">Mã Nhóm</th>
                                    <th>Tên Nhóm</th>
                                    <th class="text-center" style="width: 110px;">Mã SV</th>
                                    <th>Họ Và Tên Sinh Viên</th>
                                    <th class="text-center" style="width: 95px;">Lớp</th>
                                    <th class="text-center" style="width: 110px;">Vai Trò</th>
                                    <th class="text-center" style="width: 95px;">Mã ĐT</th>
                                    <th>Tên Đề Tài Khóa Luận</th>
                                    <th>GVHD Phụ Trách</th>
                                    <th class="text-center" style="width: 100px;">Trạng Thái ĐK</th>
                                </tr>
                                @php $curRow = 6; $sttN = 1; @endphp
                                @forelse($officialListData['nhomList'] as $nh)
                                    @php
                                        $pdk = $nh->dangKyDeTai;
                                        $deTai = $pdk?->deTai ?? $nh->deTai;
                                        $gv = $pdk?->giangVienHuongDan ?? $deTai?->giangVien;
                                    @endphp
                                    @if($nh->thanhViens && $nh->thanhViens->isNotEmpty())
                                        @foreach($nh->thanhViens as $tv)
                                            @php $sv = $tv->sinhVien; @endphp
                                            <tr class="excel-data-row">
                                                <td class="excel-row-num">{{ $curRow++ }}</td>
                                                <td class="text-center">{{ $sttN++ }}</td>
                                                <td class="text-center fw-bold">{{ $nh->MaNhom }}</td>
                                                <td>{{ $nh->TenNhom }}</td>
                                                <td class="text-center font-monospace fw-bold">{{ $tv->MaSV }}</td>
                                                <td>{{ $sv?->HoTen ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $sv?->lop?->TenLop ?? '' }}</td>
                                                <td class="text-center">{{ $tv->VaiTro ?? 'Thành viên' }}</td>
                                                <td class="text-center font-monospace">{{ $deTai?->MaDeTai ?? '' }}</td>
                                                <td>{{ $deTai?->TenDeTai ?? 'Chưa gán đề tài' }}</td>
                                                <td>{{ $gv?->HoTen ?? '' }}</td>
                                                <td class="text-center">{{ $pdk?->TrangThai ?? 'Đã duyệt' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="excel-data-row">
                                            <td class="excel-row-num">{{ $curRow++ }}</td>
                                            <td class="text-center">{{ $sttN++ }}</td>
                                            <td class="text-center fw-bold">{{ $nh->MaNhom }}</td>
                                            <td>{{ $nh->TenNhom }}</td>
                                            <td class="text-center font-monospace fw-bold">{{ $nh->truongNhom?->MaSV ?? '' }}</td>
                                            <td>{{ $nh->truongNhom?->HoTen ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $nh->truongNhom?->lop?->TenLop ?? '' }}</td>
                                            <td class="text-center">Trưởng nhóm</td>
                                            <td class="text-center font-monospace">{{ $deTai?->MaDeTai ?? '' }}</td>
                                            <td>{{ $deTai?->TenDeTai ?? 'Chưa gán đề tài' }}</td>
                                            <td>{{ $gv?->HoTen ?? '' }}</td>
                                            <td class="text-center">{{ $pdk?->TrangThai ?? 'Đã duyệt' }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td class="excel-row-num">6</td>
                                        <td colspan="11" class="text-center text-muted py-3">Chưa có nhóm nào</td>
                                    </tr>
                                @endforelse

                                @for($r = $curRow; $r <= 22; $r++)
                                    <tr>
                                        <td class="excel-row-num">{{ $r }}</td>
                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- SHEET 3: GV HƯỚNG DẪN -->
                    <div class="tab-pane fade" id="modal-sheet-gvhd" role="tabpanel">
                        <table class="excel-sheet-table" id="modalTableSheet3">
                            <thead>
                                <tr>
                                    <th class="excel-col-head" style="width: 38px;"></th>
                                    <th class="excel-col-head" style="width: 55px;">A</th>
                                    <th class="excel-col-head" style="width: 100px;">B</th>
                                    <th class="excel-col-head" style="width: 220px;">C</th>
                                    <th class="excel-col-head" style="width: 150px;">D</th>
                                    <th class="excel-col-head" style="width: 180px;">E</th>
                                    <th class="excel-col-head" style="width: 200px;">F</th>
                                    <th class="excel-col-head" style="width: 120px;">G</th>
                                    <th class="excel-col-head" style="width: 140px;">H</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="excel-row-num">1</td>
                                    <td colspan="8" class="excel-banner-org">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN</td>
                                </tr>
                                <tr>
                                    <td class="excel-row-num">2</td>
                                    <td colspan="8" class="excel-banner-title">DANH SÁCH GIẢNG VIÊN HƯỚNG DẪN KHÓA LUẬN - {{ $officialListData['stats']['tenHocKy'] }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-row-num">3</td>
                                    <td colspan="8" class="excel-banner-sub">Ngày xuất file: {{ date('d/m/Y H:i') }} | Tổng số GVHD phụ trách: {{ $officialListData['stats']['soGVHD'] }} giảng viên</td>
                                </tr>
                                <tr>
                                    <td class="excel-row-num">4</td>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                                <tr class="excel-blue-header">
                                    <td class="excel-row-num" style="background: #e2e8f0;">5</td>
                                    <th class="text-center" style="width: 55px;">STT</th>
                                    <th class="text-center" style="width: 100px;">Mã GV</th>
                                    <th>Họ Và Tên Giảng Viên</th>
                                    <th>Học Vị / Học Hàm</th>
                                    <th>Bộ Môn Chuyên Môn</th>
                                    <th>Email Liên Hệ</th>
                                    <th class="text-center" style="width: 120px;">Số Điện Thoại</th>
                                    <th class="text-center" style="width: 140px;">Số Đề Tài Hướng Dẫn</th>
                                </tr>
                                @php $curRowG = 6; @endphp
                                @forelse($officialListData['gvhdList'] as $idx => $gv)
                                    @php
                                        $hasTitleInNameModal = preg_match('/^(PGS|GS|TS|ThS|CN|KS)\b/i', trim($gv->HoTen ?? ''));
                                        $displayTitleModal = (!$hasTitleInNameModal && !empty($gv->HocVi)) ? $gv->HocVi . '. ' : '';
                                    @endphp
                                    <tr class="excel-data-row">
                                        <td class="excel-row-num">{{ $curRowG++ }}</td>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td class="text-center font-monospace fw-bold">{{ $gv->MaGV }}</td>
                                        <td class="fw-semibold">{{ $displayTitleModal }}{{ $gv->HoTen }}</td>
                                        <td>{{ trim(($gv->HocHam ? $gv->HocHam . ' - ' : '') . ($gv->HocVi ?? '')) }}</td>
                                        <td>{{ $gv->boMon?->TenBoMon ?? 'Khoa CNTT' }}</td>
                                        <td>{{ $gv->Email }}</td>
                                        <td class="text-center">{{ $gv->SoDienThoai ?? '-' }}</td>
                                        <td class="text-center fw-bold text-dark">{{ $gv->soDeTaiHuongDan ?? ($gv->deTais ? $gv->deTais->count() : 0) }} đề tài</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="excel-row-num">6</td>
                                        <td colspan="8" class="text-center text-muted py-3">Chưa có giảng viên hướng dẫn</td>
                                    </tr>
                                @endforelse

                                @for($r = $curRowG; $r <= 22; $r++)
                                    <tr>
                                        <td class="excel-row-num">{{ $r }}</td>
                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Excel Sheet Tabs Navigation Bar (At Bottom of Modal) -->
            <div class="excel-sheet-footer-bar">
                <button type="button" class="excel-sheet-nav-btn" title="Đầu tiên"><i class="fa-solid fa-angles-left"></i></button>
                <button type="button" class="excel-sheet-nav-btn" title="Trước"><i class="fa-solid fa-angle-left"></i></button>
                <button type="button" class="excel-sheet-nav-btn" title="Tiếp"><i class="fa-solid fa-angle-right"></i></button>
                <button type="button" class="excel-sheet-nav-btn" title="Cuối cùng"><i class="fa-solid fa-angles-right"></i></button>
                <div class="d-flex align-items-center ms-2" role="tablist">
                    <button class="excel-sheet-tab-item {{ $isNhomOfficial ? 'active' : '' }}" id="modal-tab-btn-2" data-bs-toggle="tab" data-bs-target="#modal-sheet-nhom" type="button" role="tab" onclick="updateFormulaBar('DANH SÁCH NHÓM SINH VIÊN ĐĂNG KÝ ĐỀ TÀI & GVHD', 'modal')">
                        <i class="fa-solid fa-file-excel text-success me-2"></i> {{ $isNhomOfficial ? '1.' : '2.' }} Nhóm SV &amp; Đề Tài &amp; GVHD
                    </button>
                    <button class="excel-sheet-tab-item {{ !$isNhomOfficial ? 'active' : '' }}" id="modal-tab-btn-1" data-bs-toggle="tab" data-bs-target="#modal-sheet-detai" type="button" role="tab" onclick="updateFormulaBar('DANH SÁCH ĐỀ TÀI KHÓA LUẬN TỐT NGHIỆP CHÍNH THỨC', 'modal')">
                        <i class="fa-solid fa-file-excel text-success me-2"></i> {{ $isNhomOfficial ? '2.' : '1.' }} Đề Tài Đăng Ký
                    </button>
                    <button class="excel-sheet-tab-item" id="modal-tab-btn-3" data-bs-toggle="tab" data-bs-target="#modal-sheet-gvhd" type="button" role="tab" onclick="updateFormulaBar('DANH SÁCH GIẢNG VIÊN HƯỚNG DẪN KHÓA LUẬN', 'modal')">
                        <i class="fa-solid fa-file-excel text-success me-2"></i> 3. GV Hướng Dẫn
                    </button>
                </div>
                <span class="ms-2 text-muted small cursor-pointer" title="Thêm sheet mới"><i class="fa-solid fa-plus"></i></span>
            </div>

            <!-- Excel Status Bar -->
            <div class="excel-status-bar">
                <div class="d-flex align-items-center gap-3">
                    <span>Sẵn sàng</span>
                    <span class="d-none d-sm-inline">&bull;</span>
                    <span class="d-none d-sm-inline">Tổng: {{ $officialListData['stats']['soDeTai'] }} đề tài, {{ $officialListData['stats']['soNhom'] }} nhóm</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($fileUrl))
                        <a href="{{ asset($fileUrl) }}" download class="text-success text-decoration-none fw-bold" style="font-size: 11px;">
                            <i class="fa-solid fa-download me-2"></i> Tải về (.xlsx)
                        </a>
                    @endif
                    <span>100%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function toggleDocFullscreen() {
        const card = document.getElementById('docViewerCard');
        const icon = document.getElementById('fullscreenIcon');
        if (card.classList.contains('doc-fullscreen-active')) {
            card.classList.remove('doc-fullscreen-active');
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        } else {
            card.classList.add('doc-fullscreen-active');
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        }
    }

    // Thoát fullscreen khi nhấn ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const card = document.getElementById('docViewerCard');
            if (card && card.classList.contains('doc-fullscreen-active')) {
                toggleDocFullscreen();
            }
        }
    });

    // Cập nhật Formula Bar khi chuyển sheet hoặc chọn ô
    function updateFormulaBar(text, target = 'modal') {
        const el = document.getElementById(target === 'pane' ? 'paneFormulaText' : 'modalFormulaText');
        if (el) el.innerText = text;
        const cellEl = document.getElementById(target === 'pane' ? 'paneActiveCell' : 'modalActiveCell');
        if (cellEl) cellEl.innerText = 'A1';
    }

    // Lọc bảng danh sách trong Modal Popup Xem Nhanh
    function filterModalExcelTable() {
        const input = document.getElementById('modalExcelSearch');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const activePane = document.querySelector('#modalExcelTabContent .tab-pane.active');
        if (!activePane) return;
        const rows = activePane.querySelectorAll('.excel-data-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // Lọc bảng danh sách trong Khung Trái Xem Nhanh
    function filterPaneExcelTable() {
        const input = document.getElementById('paneExcelSearch');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const activePane = document.querySelector('#paneExcelTabContent .tab-pane.active');
        if (!activePane) return;
        const rows = activePane.querySelectorAll('.excel-data-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // Lọc bảng danh sách Excel trực tiếp (file đính kèm thông thường)
    function filterExcelTable() {
        const input = document.getElementById('excelSearchInput');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#excelTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // Tương tác nhấp chuột chọn ô bảng tính như Microsoft Excel thật
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const td = e.target.closest('.excel-sheet-table td:not(.excel-row-num)');
            if (!td) return;
            
            const table = td.closest('.excel-sheet-table');
            if (!table) return;

            const isPane = table.id && table.id.startsWith('pane');
            const activeCellId = isPane ? 'paneActiveCell' : 'modalActiveCell';
            const formulaTextId = isPane ? 'paneFormulaText' : 'modalFormulaText';

            // Xóa highlight active cell cũ trong bảng
            table.querySelectorAll('.excel-active-cell').forEach(el => el.classList.remove('excel-active-cell'));
            
            // Đánh dấu ô đang được chọn
            td.classList.add('excel-active-cell');

            // Tính toán toạ độ ô (ví dụ: B6, C10, A1)
            const row = td.closest('tr');
            const rowNumCell = row.querySelector('.excel-row-num');
            const rowNum = rowNumCell ? rowNumCell.innerText.trim() : '';

            const colIndex = Array.from(row.children).indexOf(td);
            const theadRow = table.querySelector('thead tr');
            let colLetter = '';
            if (theadRow && theadRow.children[colIndex]) {
                colLetter = theadRow.children[colIndex].innerText.trim();
            }

            const cellCoord = (colLetter && rowNum) ? `${colLetter}${rowNum}` : 'A1';
            const nameBox = document.getElementById(activeCellId);
            if (nameBox) nameBox.innerText = cellCoord;

            const formulaBar = document.getElementById(formulaTextId);
            if (formulaBar) formulaBar.innerText = td.innerText.trim();
        });
    });
</script>
@endpush
@endsection
