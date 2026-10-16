@extends('layouts.admin')

@section('page_title', 'Theo Dõi Tiến Độ Đồ Án / Khóa Luận')

@section('content')

{{-- ── 1. HEADER & THÔNG TIN KẾ HOẠCH HIỆN TẠI ── --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-chart-line text-primary me-2"></i>Theo Dõi Tiến Độ Khóa Luận Tốt Nghiệp
        </h4>
    </div>
    
    {{-- Banner Thông Tin Kế Hoạch & Giai Đoạn Hiện Tại --}}
    <div class="plan-status-card d-flex align-items-center gap-3 p-2 px-3 bg-white rounded-3 border shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">
                <i class="fa-solid fa-calendar-check me-1"></i>{{ $activePlan->TenKeHoach ?? 'Kế hoạch Khóa luận HK1 2026-2027' }}
            </span>
        </div>
        <div class="border-start ps-3 d-none d-md-flex align-items-center gap-1.5 small">
            <span class="badge-pulsing-green"></span>
            <span class="text-secondary fw-semibold">Giai đoạn hiện tại:</span> 
            <span class="text-dark fw-bold">{{ $currentPhase->TenMoc ?? 'Sinh viên tạo nhóm trên phần mềm HUIT-STUDENT (mỗi nhóm 3 SV/1 đề tài)' }}</span>
        </div>
    </div>
</div>

{{-- ── 2. STEPPER LỘ TRÌNH 6 GIAI ĐOẠN & DẢI LỌC TIẾN ĐỘ ── --}}
<div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
    <div class="card-body p-3 p-md-4">
        {{-- Header của Stepper & Thanh Thống Kê Nhanh --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-3 border-bottom gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                    <i class="fa-solid fa-timeline text-primary me-1.5"></i> 6 Giai Đoạn Giám Sát Tiến Độ Khóa Luận
                </span>
                <a href="{{ route('admin.theodoi.index') }}" 
                   class="badge text-decoration-none {{ !request('stage') && !request('status_code') ? 'bg-primary text-white' : 'bg-light text-secondary border' }} rounded-pill px-2.5 py-1">
                    Tất cả ({{ $stats['total'] }})
                </a>
            </div>

            {{-- Thống kê KPI ngắn gọn dạng Pill --}}
            <div class="d-flex flex-wrap align-items-center gap-2 small">
                <a href="{{ route('admin.theodoi.index', array_merge(request()->query(), ['status_code' => 'DUNG_TIEN_DO'])) }}" 
                   class="kpi-pill kpi-pill-success {{ request('status_code') === 'DUNG_TIEN_DO' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-check"></i> Đúng tiến độ: <strong>{{ $stats['dung_tien_do'] }}</strong>
                </a>
                <a href="{{ route('admin.theodoi.index', array_merge(request()->query(), ['status_code' => 'SAP_DEN_HAN'])) }}" 
                   class="kpi-pill kpi-pill-warning {{ request('status_code') === 'SAP_DEN_HAN' ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> Sắp đến hạn: <strong>{{ $stats['sap_den_han'] }}</strong>
                </a>
                <a href="{{ route('admin.theodoi.index', array_merge(request()->query(), ['status_code' => 'QUA_HAN'])) }}" 
                   class="kpi-pill kpi-pill-danger {{ request('status_code') === 'QUA_HAN' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-exclamation"></i> Quá hạn: <strong>{{ $stats['qua_han'] }}</strong>
                </a>
                @if(request()->anyFilled(['stage', 'status_code', 'search', 'MaGV', 'MaHocKy']))
                    <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 11px;">
                        <i class="fa-solid fa-xmark me-1"></i>Xóa lọc
                    </a>
                @endif
            </div>
        </div>

        {{-- Thanh Stepper Timeline: [1] ─── [2] ─── [3] ─── [4] ─── [5] ─── [6] --}}
        <div class="stepper-timeline-container">
            <div class="stepper-track">
                {{-- Đường kẻ nối giữa các node --}}
                <div class="stepper-connecting-line"></div>

                @php
                    $stagesList = [
                        1 => ['title' => 'Đề cương & CSDL', 'full' => 'Nhận đề cương & Thiết kế CSDL', 'icon' => 'fa-database', 'count' => $stats['gd1']],
                        2 => ['title' => 'Hệ thống & Chức năng', 'full' => 'Thiết kế hệ thống & Xây dựng chức năng', 'icon' => 'fa-code', 'count' => $stats['gd2']],
                        3 => ['title' => 'Kiểm thử & Báo cáo', 'full' => 'Kiểm thử hoàn thiện & Báo cáo dự thảo', 'icon' => 'fa-vial-circle-check', 'count' => $stats['gd3']],
                        4 => ['title' => 'Hồ sơ bảo vệ', 'full' => 'Hoàn thiện hồ sơ bảo vệ & Turnitin', 'icon' => 'fa-file-shield', 'count' => $stats['gd4']],
                        5 => ['title' => 'Bảo vệ Hội đồng', 'full' => 'Tiến hành bảo vệ trước Hội đồng', 'icon' => 'fa-chalkboard-user', 'count' => $stats['gd5']],
                        6 => ['title' => 'Kết quả & Nộp lại', 'full' => 'Nhận kết quả & Nộp lại sau chỉnh sửa', 'icon' => 'fa-award', 'count' => $stats['gd6']],
                    ];
                @endphp

                @foreach($stagesList as $stNum => $stInfo)
                    @php
                        $isSelected = request('stage') == $stNum;
                        $isCurrentPhaseStage = ($stNum === 1); // Mốc hiện tại của đợt đang mở
                    @endphp
                    <a href="{{ route('admin.theodoi.index', array_merge(request()->except('page'), ['stage' => $stNum])) }}" 
                       class="stepper-step {{ $isSelected ? 'is-selected' : '' }} {{ $isCurrentPhaseStage ? 'is-current-phase' : '' }}"
                       title="{{ $stInfo['full'] }}">
                        
                        <div class="step-node-bubble">
                            <span class="step-number">[{{ $stNum }}]</span>
                            <i class="fa-solid {{ $stInfo['icon'] }} step-icon"></i>
                        </div>

                        <div class="step-title-text">{{ $stInfo['title'] }}</div>
                        <div class="step-group-count">{{ $stInfo['count'] }} nhóm</div>

                        @if($isCurrentPhaseStage)
                            <div class="current-phase-marker" title="Giai đoạn hiện tại của Kế hoạch">
                                <i class="fa-solid fa-caret-up text-success marker-arrow"></i>
                                <span class="marker-tag">
                                    <span class="pulse-dot-sm"></span> Hiện tại
                                </span>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Dòng chỉ báo mốc hiện tại chuẩn mockup --}}
            <div class="current-phase-detail-strip mt-3 text-center">
                <span class="current-phase-pill-banner">
                    <i class="fa-solid fa-circle-arrow-right text-success me-1"></i>
                    <strong>GĐ Hiện tại:</strong> {{ $currentPhase->TenMoc ?? 'Đăng ký nhóm trên HUIT-STUDENT (3 SV/đề tài)' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ── 3. THANH TÌM KIẾM & BỘ LỌC DỮ LIỆU ── --}}
<div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
    <div class="card-body p-2 px-3">
        <form method="GET" action="{{ route('admin.theodoi.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="stage" value="{{ request('stage') }}">
            <input type="hidden" name="status_code" value="{{ request('status_code') }}">

            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-1" 
                           placeholder="Tìm theo mã nhóm, tên đề tài, tên SV, MSSV..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="MaGV" class="form-select form-select-sm">
                    <option value="">-- Tất cả Giảng viên hướng dẫn --</option>
                    @foreach($giangViens as $gv)
                        <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                            {{ $gv->HoTen }} ({{ $gv->MaGV }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1.5 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary rounded-2 px-3 fw-semibold text-nowrap flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
                <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-light border rounded-2 px-2 text-secondary" title="Tải lại">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── 4. BẢNG DANH SÁCH NHÓM & 6 GIAI ĐOẠN TIẾN ĐỘ ── --}}
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark small">
            <i class="fa-regular fa-folder-open text-primary me-1.5"></i>
            Danh Sách Nhóm Thực Hiện &amp; 6 Giai Đoạn Tiến Độ
            <span class="text-muted fw-normal ms-1">({{ $processedNhoms->count() }} nhóm)</span>
        </span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.ketqua.export') }}" class="btn btn-sm btn-outline-success border rounded-2 px-2.5 py-1" style="font-size: 12.5px;">
                <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 120px;">MÃ NHÓM</th>
                    <th style="min-width: 280px;">TÊN ĐỀ TÀI &amp; TRƯỞNG NHÓM</th>
                    <th style="min-width: 200px;">GV HƯỚNG DẪN</th>
                    <th style="width: 280px;">6 GIAI ĐOẠN TIẾN ĐỘ</th>
                    <th class="text-center" style="width: 160px;">TRẠNG THÁI</th>
                    <th class="text-center" style="width: 100px;">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($processedNhoms as $nhom)
                @php
                    $isDanger = $nhom->status_code === 'QUA_HAN';
                    $gvhd = $nhom->dangKyDeTai?->giangVienHuongDan ?? $nhom->deTai?->giangVien;
                @endphp
                <tr class="{{ $isDanger ? 'table-danger bg-opacity-25' : '' }}">
                    {{-- 1. Mã Nhóm --}}
                    <td>
                        <div class="fw-bold text-primary" style="font-size: 13.5px;">
                            {{ $nhom->TenNhom ?: $nhom->MaNhom }}
                        </div>
                        <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 11px;">
                            {{ $nhom->MaNhom }}
                        </span>
                    </td>

                    {{-- 2. Tên Đề Tài & Trưởng Nhóm --}}
                    <td>
                        <div class="fw-bold text-dark mb-1" style="font-size: 13.5px; line-height: 1.35;">
                            {{ $nhom->deTai->TenDeTai ?? 'Chưa đăng ký đề tài chính thức' }}
                        </div>
                        <div class="text-secondary small d-flex flex-wrap align-items-center gap-1.5">
                            <span class="text-warning">👑</span>
                            <span>Trưởng nhóm: <strong>{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</strong></span>
                            @if($nhom->truongNhom)
                                <span class="text-muted">({{ $nhom->truongNhom->MaSoSinhVien ?? $nhom->truongNhom->MaSV ?? $nhom->MaTruongNhom }})</span>
                            @endif
                        </div>
                        <div class="text-muted small mt-0.5">
                            <i class="fa-solid fa-users text-primary me-1" style="font-size: 11px;"></i>Thành viên: 
                            <span class="badge bg-light text-dark border px-1.5 py-0.5" style="font-size: 11px;">
                                {{ $nhom->thanhViens->count() }}/3 sinh viên
                            </span>
                        </div>
                    </td>

                    {{-- 3. GV Hướng Dẫn --}}
                    <td>
                        @if($gvhd)
                            <div class="fw-bold text-dark" style="font-size: 13px;">
                                {{ $gvhd->HocHamHocVi ? $gvhd->HocHamHocVi . ' ' : '' }}{{ $gvhd->HoTen }}
                            </div>
                            <div class="mt-0.5">
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-1.5 py-0.5" style="font-size: 11px;">
                                    Bộ môn: {{ $gvhd->boMon->TenBoMon ?? 'CNPM' }}
                                </span>
                            </div>
                        @else
                            <span class="text-muted fst-italic small">Chưa phân công</span>
                        @endif
                    </td>

                    {{-- 4. 6 Giai Đoạn Tiến Độ Track --}}
                    <td>
                        <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 12.5px;">
                            <span class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="GĐ {{ $nhom->current_stage }}: {{ $nhom->current_stage_label }}">
                                <span class="text-primary">GĐ {{ $nhom->current_stage }}/6:</span> {{ $nhom->current_stage_label }}
                            </span>
                            <span class="badge bg-light text-primary border rounded-pill fw-bold px-2 py-0.5" style="font-size: 11.5px;">
                                {{ $nhom->progress_percent }}%
                            </span>
                        </div>
                        <div class="progress rounded-pill shadow-none" style="height: 6px; background: #e2e8f0;">
                            <div class="progress-bar rounded-pill {{ $nhom->progress_percent >= 100 ? 'bg-success' : ($nhom->status_code == 'QUA_HAN' ? 'bg-danger' : 'bg-primary') }}" 
                                 style="width: {{ $nhom->progress_percent }}%;"></div>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 11.5px;">
                            Hoàn thành: <strong>{{ $nhom->completed_stages }}/6</strong> mốc
                        </div>
                    </td>

                    {{-- 5. Trạng Thái / Cảnh Báo (Ngắn gọn, chuẩn màu) --}}
                    <td class="text-center">
                        @if($nhom->status_code === 'DUNG_TIEN_DO')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                <i class="fa-solid fa-circle-check me-1"></i>Đúng tiến độ
                            </span>
                        @elseif($nhom->status_code === 'SAP_DEN_HAN')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Sắp đến hạn
                            </span>
                        @elseif($nhom->status_code === 'QUA_HAN')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>Quá hạn
                            </span>
                        @elseif($nhom->status_code === 'HOAN_THANH')
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                <i class="fa-solid fa-flag-checkered me-1"></i>Hoàn thành
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                                {{ $nhom->status_label }}
                            </span>
                        @endif
                    </td>

                    {{-- 6. Thao Tác (Chuyển trang Chi Tiết Đầy Đủ riêng biệt) --}}
                    <td class="text-center">
                        <a href="{{ route('admin.theodoi.show', $nhom->MaNhom) }}" 
                           class="btn btn-sm btn-action btn-outline-primary text-nowrap" 
                           title="Xem chi tiết tiến độ nhóm">
                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-chart-line fa-2x mb-2 d-block opacity-50"></i>
                        Không có nhóm khóa luận nào phù hợp với điều kiện tìm kiếm.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer Bảng --}}
    <div class="p-2.5 px-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 bg-light">
        <div class="text-muted small">
            Hiển thị <strong>{{ $processedNhoms->count() }}</strong> nhóm khóa luận
        </div>
    </div>
</div>

{{-- ── STYLES RIÊNG BIỆT CHO STEPPER & TIMELINE 6 GIAI ĐOẠN ── --}}
<style>
/* Stepper Timeline Container */
.stepper-timeline-container {
    padding: 10px 5px 5px;
    overflow-x: auto;
}

.stepper-track {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
    min-width: 680px;
    padding-bottom: 25px;
}

/* Connecting Line between [1] ─── [2] ─── [6] */
.stepper-connecting-line {
    position: absolute;
    top: 22px;
    left: 40px;
    right: 40px;
    height: 3px;
    background: #cbd5e1;
    z-index: 1;
}

/* Step Item */
.stepper-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none !important;
    z-index: 2;
    flex: 1;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Node Bubble [1] .. [6] */
.step-node-bubble {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    border: 2.5px solid #0072ce;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #0072ce;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
    margin-bottom: 6px;
}

.step-node-bubble .step-number {
    font-size: 13px;
    line-height: 1;
}

.step-node-bubble .step-icon {
    font-size: 11px;
    margin-top: 1px;
}

/* Node Active / Hover */
.stepper-step:hover .step-node-bubble {
    background: #0072ce;
    color: #ffffff;
    border-color: #003366;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 114, 206, 0.35);
}

.stepper-step.is-selected .step-node-bubble {
    background: #003366;
    color: #ffffff;
    border-color: #0072ce;
    box-shadow: 0 0 0 4px rgba(0, 114, 206, 0.25);
    transform: scale(1.08);
}

.step-title-text {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    text-align: center;
    line-height: 1.25;
    max-width: 120px;
    margin-bottom: 2px;
}

.stepper-step.is-selected .step-title-text {
    color: #0072ce;
}

.step-group-count {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 1px 8px;
    border-radius: 12px;
}

.stepper-step.is-selected .step-group-count {
    background: #0072ce;
    color: #ffffff;
    border-color: #0072ce;
}

/* Current Phase Marker: ▲ GĐ Hiện tại */
.current-phase-marker {
    position: absolute;
    bottom: -22px;
    display: flex;
    flex-direction: column;
    align-items: center;
    pointer-events: none;
}

.marker-arrow {
    margin-bottom: -5px;
    line-height: 1;
}

.marker-tag {
    background: #198754;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
}

/* Current Phase Banner Strip at Bottom */
.current-phase-pill-banner {
    display: inline-flex;
    align-items: center;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    font-size: 12.5px;
    padding: 5px 14px;
    border-radius: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

/* Pulse Animation Dots */
.badge-pulsing-green {
    width: 9px;
    height: 9px;
    background-color: #198754;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
    animation: pulse-green 1.8s infinite;
}

.pulse-dot-sm {
    width: 6px;
    height: 6px;
    background-color: #ffffff;
    border-radius: 50%;
    display: inline-block;
}

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}

/* KPI Filter Pills */
.kpi-pill {
    text-decoration: none !important;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 12px;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.kpi-pill-success {
    background: #dcfce7;
    color: #15803d;
    border-color: #bbf7d0;
}
.kpi-pill-success:hover, .kpi-pill-success.active {
    background: #15803d;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.3);
}

.kpi-pill-warning {
    background: #fef9c3;
    color: #a16207;
    border-color: #fef08a;
}
.kpi-pill-warning:hover, .kpi-pill-warning.active {
    background: #a16207;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(161, 98, 7, 0.3);
}

.kpi-pill-danger {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #fecaca;
}
.kpi-pill-danger:hover, .kpi-pill-danger.active {
    background: #b91c1c;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(185, 28, 28, 0.3);
}
</style>

@endsection
