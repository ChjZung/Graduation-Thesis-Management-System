@extends($layout)

@section('page_title', 'Lịch Quy Trình & Mốc Thời Gian Khóa Luận Theo Tuần')

@push('styles')
<style>
    /* Styling Chuẩn Thời Khóa Biểu Theo Tuần (Image 1 Style) */
    .timetable-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        padding: 18px;
        margin-bottom: 24px;
        overflow-x: auto;
    }
    .timetable-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 12px;
    }
    .timetable-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Quick Phase Shortcuts */
    .timetable-quick-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 14px;
    }
    .phase-pill {
        font-size: 0.76rem;
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        color: #475569;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    .phase-pill:hover {
        background: #e0f2fe;
        color: #0284c7;
        border-color: #bae6fd;
    }
    .phase-pill.active {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    }

    /* Macro Phase Banner (12 tuần thực hiện) */
    .macro-phase-banner {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-left: 5px solid #64748b;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Timetable Grid Table */
    .timetable-table {
        width: 100%;
        min-width: 1020px;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .timetable-table th {
        border: 1px solid #cbd5e1;
        padding: 8px 4px;
        vertical-align: middle;
        text-align: center;
        background: #e0f2fe;
        color: #0284c7;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .timetable-table th.col-doituong {
        width: 130px;
        background: #e0f2fe;
        color: #0284c7;
    }
    .timetable-table th.is-today {
        background: #bae6fd;
        color: #0369a1;
        border-top: 3px solid #0284c7;
    }

    /* Row Header (Đối tượng) */
    .shift-header-cell {
        width: 130px;
        border: 1px solid #cbd5e1;
        font-weight: 700;
        text-align: center;
        vertical-align: middle !important;
        padding: 12px 6px !important;
    }
    .shift-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .shift-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .shift-name {
        font-size: 0.86rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .shift-desc {
        font-size: 0.68rem;
        font-weight: 500;
        opacity: 0.85;
        line-height: 1.2;
    }

    /* CSS Grid Container for the 7 Days */
    .grid-track-cell {
        border: 1px solid #cbd5e1;
        padding: 0 !important;
        vertical-align: top;
        position: relative;
    }
    .days-grid-container {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        min-height: 110px;
        position: relative;
        grid-auto-flow: row dense;
        gap: 6px;
        padding: 6px;
    }
    /* Background Day Dividers */
    .day-col-background {
        position: absolute;
        top: 0;
        bottom: 0;
        border-right: 1px solid #f1f5f9;
        pointer-events: none;
        z-index: 1;
    }
    .day-col-background.is-today-col {
        background-color: rgba(240, 249, 255, 0.6);
        border-left: 1px solid #bae6fd;
        border-right: 1px solid #bae6fd;
    }

    /* Thẻ Sự Kiện / Mốc Thời Gian (Spanning Event Card) */
    .tt-span-card {
        z-index: 2;
        border-radius: 7px;
        padding: 7px 10px;
        font-size: 0.76rem;
        line-height: 1.35;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .tt-span-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        z-index: 5;
    }
    .tt-card-badge {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #ffffff;
    }
    .tt-card-range {
        font-size: 0.7rem;
        font-weight: 600;
        opacity: 0.85;
    }
    .tt-card-title {
        font-weight: 700;
        font-size: 0.78rem;
        margin: 3px 0 4px 0;
        line-height: 1.35;
    }
    .tt-card-meta {
        font-size: 0.7rem;
        opacity: 0.9;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Chú giải Legend Footer */
    .timetable-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px;
        padding-top: 14px;
        margin-top: 14px;
        border-top: 1px solid #f1f5f9;
        font-size: 0.76rem;
        color: #475569;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .legend-circle {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Nút điều hướng tuần */
    .btn-tt-blue {
        background: #0284c7;
        color: #ffffff;
        border: 1px solid #0284c7;
        font-weight: 600;
        font-size: 0.8rem;
        border-radius: 6px;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .btn-tt-blue:hover {
        background: #0369a1;
        color: #ffffff;
    }

    /* Fullscreen view */
    .timetable-fullscreen-active {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        border-radius: 0 !important;
        padding: 20px !important;
        margin: 0 !important;
        overflow: auto !important;
        background: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── 1. GIAO DIỆN LỊCH QUY TRÌNH THEO TUẦN (CHUẨN HUIT MẪU ẢNH) ── --}}
    <div class="timetable-wrapper" id="weeklyTimetableContainer">
        {{-- Toolbar trên cùng --}}
        <div class="timetable-toolbar">
            {{-- Tiêu đề trang & Radio Bộ Lọc theo Nhóm Đối Tượng --}}
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h5 class="timetable-title">
                    <i class="fa-solid fa-calendar-week text-primary"></i> Lịch quy trình & mốc khóa luận theo tuần
                </h5>
                <div class="d-flex align-items-center gap-2.5 small text-muted ms-2 flex-wrap">
                    <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer">
                        <input type="radio" name="filter_role" value="ALL" class="form-check-input mt-0" checked onchange="filterMilestoneRole('ALL')">
                        Tất cả
                    </label>
                    <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer">
                        <input type="radio" name="filter_role" value="SINH_VIEN" class="form-check-input mt-0" onchange="filterMilestoneRole('SINH_VIEN')">
                        Sinh viên
                    </label>
                    <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer">
                        <input type="radio" name="filter_role" value="GIANG_VIEN" class="form-check-input mt-0" onchange="filterMilestoneRole('GIANG_VIEN')">
                        GVHD
                    </label>
                    <label class="form-check-label d-flex align-items-center gap-1 cursor-pointer">
                        <input type="radio" name="filter_role" value="KHOA_HDS" class="form-check-input mt-0" onchange="filterMilestoneRole('KHOA_HDS')">
                        Khoa & Hội đồng
                    </label>
                </div>
            </div>

            {{-- Bộ chọn ngày & Các nút điều hướng tuần --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Ô chọn ngày --}}
                <form method="GET" action="{{ url()->current() }}" id="formDateFilter" class="d-flex align-items-center gap-2 m-0">
                    <div class="input-group input-group-sm" style="width: 145px;">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $currentDate ?? '2026-10-09' }}" onchange="document.getElementById('formDateFilter').submit()">
                    </div>
                </form>

                {{-- Nút Hiện tại --}}
                <a href="{{ url()->current() }}?date={{ now()->format('Y-m-d') }}" class="btn-tt-blue" title="Về tuần hiện tại">
                    <i class="fa-solid fa-calendar-day"></i> Hiện tại
                </a>

                {{-- Nút In lịch --}}
                <button type="button" class="btn-tt-blue" onclick="window.print()" title="In lịch tuần">
                    <i class="fa-solid fa-print"></i> In lịch
                </button>

                {{-- Nút < Trở về --}}
                <a href="{{ url()->current() }}?date={{ $prevWeekDate }}" class="btn-tt-blue" title="Tuần trước">
                    <i class="fa-solid fa-chevron-left"></i> Trở về
                </a>

                {{-- Nút Tiếp > --}}
                <a href="{{ url()->current() }}?date={{ $nextWeekDate }}" class="btn-tt-blue" title="Tuần tiếp theo">
                    Tiếp <i class="fa-solid fa-chevron-right"></i>
                </a>

                {{-- Nút Phóng to / Thu nhỏ --}}
                <button type="button" class="btn-tt-blue" onclick="toggleTimetableFullscreen()" title="Toàn màn hình">
                    <i class="fa-solid fa-expand" id="iconFullscreen"></i>
                </button>
            </div>
        </div>

        {{-- Thanh phím tắt nhảy nhanh đến các giai đoạn trọng tâm của khóa luận --}}
        <div class="timetable-quick-nav">
            <span class="text-muted small fw-semibold"><i class="fa-solid fa-bolt text-warning me-1"></i>Chuyển nhanh tuần:</span>
            <a href="{{ url()->current() }}?date=2026-08-10" class="phase-pill {{ request('date') == '2026-08-10' ? 'active' : '' }}">
                1. Đăng ký đề tài (10/08 - 16/08)
            </a>
            <a href="{{ url()->current() }}?date={{ now()->format('Y-m-d') }}" class="phase-pill {{ (!request('date') || request('date') == now()->format('Y-m-d')) ? 'active' : '' }}">
                2. Tuần hiện tại ({{ now()->format('d/m/Y') }})
            </a>
            <a href="{{ url()->current() }}?date=2026-11-09" class="phase-pill {{ request('date') == '2026-11-09' ? 'active' : '' }}">
                3. Nộp khóa luận (09/11 - 15/11)
            </a>
            <a href="{{ url()->current() }}?date=2026-11-16" class="phase-pill {{ request('date') == '2026-11-16' ? 'active' : '' }}">
                4. Bảo vệ Hội đồng (16/11 - 28/11)
            </a>
        </div>

        {{-- Banner Giai Đoạn Vĩ Mô (Mốc 6: 12 Tuần Thực Hiện Đồ Án) - Tách riêng, không gây nhiễu từng ngày --}}
        @if($macroPhase)
        <div class="macro-phase-banner">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-2.5 py-1" style="background: {{ $macroPhase['palette']['badge'] }}; color: #ffffff;">
                    Mốc {{ $macroPhase['moc_index'] }}
                </span>
                <span class="fw-bold text-dark">{{ $macroPhase['title'] }}</span>
                <span class="text-muted small">
                    ({{ $macroPhase['start_str'] }} &rarr; {{ $macroPhase['end_str'] }} &bull; {{ $macroPhase['weeks'] }} tuần)
                </span>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                <i class="fa-solid fa-circle-check me-1"></i>Đang trong giai đoạn thực hiện đồ án
            </span>
        </div>
        @endif

        {{-- Bảng Ma Trận Lịch Tuần (Table Grid với Spanning Bar) --}}
        <div class="table-responsive">
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th class="col-doituong">Đối tượng / Quy trình</th>
                        @foreach($weekDays as $day)
                            <th class="{{ $day['is_current'] || $day['is_today'] ? 'is-today' : '' }}">
                                <div class="text-uppercase" style="font-size: 0.82rem; letter-spacing: 0.3px;">{{ $day['day_name'] }}</div>
                                <div class="fw-normal" style="font-size: 0.78rem; opacity: 0.85;">{{ $day['date_str'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- 3 HÀNG PHÂN LOẠI: SINH VIÊN, GV HƯỚNG DẪN, KHOA & HỘI ĐỒNG (THAY CHO SÁNG, CHIỀU, TỐI) --}}
                    @foreach($shifts as $shiftKey => $shiftInfo)
                    @php
                        $events = $shiftEvents[$shiftKey] ?? [];
                    @endphp
                    <tr class="shift-row-container" data-shift-row="{{ $shiftKey }}">
                        {{-- Cột tiêu đề Đối tượng: Màu sắc dịu nhẹ, phân biệt rõ ràng --}}
                        <td class="shift-header-cell" style="background: {{ $shiftInfo['bg'] ?? '#f8fafc' }} !important; color: {{ $shiftInfo['color'] ?? '#1e293b' }} !important;">
                            <div class="shift-box">
                                <div class="shift-icon-wrap" style="color: {{ $shiftInfo['color'] ?? '#1e293b' }};">
                                    <i class="{{ $shiftInfo['icon'] }}"></i>
                                </div>
                                <div class="shift-name">{{ $shiftInfo['name'] }}</div>
                                <div class="shift-desc">{{ $shiftInfo['desc'] }}</div>
                            </div>
                        </td>

                        {{-- 7 Cột Ngày Tích Hợp CSS Grid: Hỗ Trợ Span Dải Thời Hạn (Không Lặp Mỗi Ngày 1 Cục) --}}
                        <td colspan="7" class="grid-track-cell">
                            <div class="days-grid-container">
                                {{-- 7 Dải Cột Nền Để Phân Cách Ngày & Highlight Hôm Nay --}}
                                @for($c = 0; $c < 7; $c++)
                                    @php
                                        $d = $weekDays[$c];
                                        $isToday = ($d['is_current'] || $d['is_today']);
                                        $leftPercent = ($c / 7) * 100;
                                        $widthPercent = 100 / 7;
                                    @endphp
                                    <div class="day-col-background {{ $isToday ? 'is-today-col' : '' }}" 
                                         style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;">
                                    </div>
                                @endfor

                                {{-- Các Mốc Quy Trình Được Trải Theo Độ Dài Thời Hạn (Spanning Event Bar) --}}
                                @if(!empty($events))
                                    @foreach($events as $item)
                                        @php
                                            $colStartGrid = $item['col_start'] + 1; // 1-indexed trong CSS Grid
                                            $spanGrid = $item['span'];
                                            $pal = $item['palette'];
                                        @endphp
                                        <div class="tt-span-card milestone-item" 
                                             data-role="{{ $shiftKey }}"
                                             style="grid-column: {{ $colStartGrid }} / span {{ $spanGrid }}; background: {{ $pal['bg'] }}; border: 1px solid {{ $pal['border'] }}; border-left: 4px solid {{ $pal['border'] }}; color: {{ $pal['text'] }};">
                                            
                                            {{-- Header: Tag Mốc & Khoảng thời gian --}}
                                            <div class="d-flex align-items-center justify-content-between gap-1">
                                                <span class="tt-card-badge" style="background: {{ $pal['badge'] }};">
                                                    Mốc {{ $item['moc_index'] }}
                                                </span>
                                                <span class="tt-card-range">
                                                    <i class="fa-regular fa-calendar me-1"></i>{{ $item['date_range'] }}
                                                    @if($item['span'] > 1)
                                                        ({{ $item['span'] }} ngày)
                                                    @endif
                                                </span>
                                            </div>

                                            {{-- Tiêu đề mốc --}}
                                            <div class="tt-card-title">{{ $item['title'] }}</div>

                                            {{-- Chi tiết cụ thể (Đã lọc bỏ thông tin nhiễu) --}}
                                            @if($item['time_slot'] || $item['room'])
                                            <div class="tt-card-meta">
                                                @if($item['time_slot'])
                                                    <div><i class="fa-regular fa-clock me-1"></i>{{ $item['time_slot'] }}</div>
                                                @endif
                                                @if($item['room'])
                                                    <div><i class="fa-solid fa-location-dot me-1"></i>{{ $item['room'] }}</div>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="d-flex align-items-center justify-content-center text-muted small py-4 fst-italic" style="grid-column: 1 / span 7; z-index: 2; opacity: 0.6;">
                                        Không có mốc sự kiện trong tuần này
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Thanh Chú Thích 9 Màu Nhận Diện Riêng Biệt Cho Từng Mốc Quy Trình --}}
        <div class="timetable-legend">
            <span class="fw-bold text-dark me-2"><i class="fa-solid fa-palette text-primary me-1"></i>Nhận diện mốc:</span>
            <div class="legend-item">
                <span class="legend-circle" style="background: #8b5cf6;"></span>
                <span>Mốc 1 (HUIT-STUDENT)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #0284c7;"></span>
                <span>Mốc 2 (Đăng ký đề tài)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #ea580c;"></span>
                <span>Mốc 3 (Ngoại lệ VPK)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #0d9488;"></span>
                <span>Mốc 4 (Công bố đề tài & GVHD)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #16a34a;"></span>
                <span>Mốc 5 (Liên hệ GVHD)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #64748b;"></span>
                <span>Mốc 6 (12 tuần thực hiện)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #e11d48;"></span>
                <span>Mốc 7 (Nộp khóa luận)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #d97706;"></span>
                <span>Mốc 8 (Lịch Hội đồng)</span>
            </div>
            <div class="legend-item">
                <span class="legend-circle" style="background: #9333ea;"></span>
                <span>Mốc 9 (Bảo vệ Hội đồng)</span>
            </div>
        </div>
    </div>

    {{-- ── 2. DANH SÁCH TOÀN BỘ CÁC MỐC QUY TRÌNH TOÀN KHÓA ── --}}
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-timeline text-primary"></i>
                <span>Danh Mục Toàn Bộ Các Mốc Thời Gian Quy Trình (Kế Hoạch Đang Áp Dụng)</span>
            </h6>
            <span class="badge bg-light text-primary border rounded-pill px-3">{{ $allMilestones->count() }} Mốc quy định</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="35%">Nội Dung Quy Trình / Giai Đoạn</th>
                        <th width="18%">Đối Tượng Thực Hiện</th>
                        <th width="22%">Thời Gian Áp Dụng</th>
                        <th width="20%" class="text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allMilestones as $idx => $m)
                    @php
                        $today = now()->format('Y-m-d');
                        $statusBadge = 'bg-secondary';
                        $statusText = 'Chưa diễn ra';
                        if ($m->NgayKetThuc < $today) {
                            $statusBadge = 'bg-success';
                            $statusText = 'Đã hoàn thành';
                        } elseif ($today >= $m->NgayBatDau && $today <= $m->NgayKetThuc) {
                            $statusBadge = 'bg-warning text-dark';
                            $statusText = 'Đang diễn ra';
                        }
                        $mPalette = $milestonePalette[$idx + 1] ?? ['badge' => '#0072ce'];
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">
                            <span class="badge rounded-pill text-white" style="background: {{ $mPalette['badge'] }};">
                                Mốc {{ $idx + 1 }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $m->TenMoc }}</div>
                            <div class="small text-muted font-monospace">{{ $m->MaMoc }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $m->DoiTuongThucHien ?? 'Sinh viên & GVHD' }}</span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">
                                {{ date('d/m/Y', strtotime($m->NgayBatDau)) }} &rarr; {{ date('d/m/Y', strtotime($m->NgayKetThuc)) }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $statusBadge }} rounded-pill px-3 py-1">{{ $statusText }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Bộ lọc hiển thị theo Nhóm đối tượng (Tất cả / Sinh viên / GVHD / Khoa & Hội đồng)
    function filterMilestoneRole(role) {
        const rows = document.querySelectorAll('.shift-row-container');
        rows.forEach(row => {
            const shiftKey = row.getAttribute('data-shift-row');
            if (role === 'ALL' || role === shiftKey) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Phóng to toàn màn hình thời khóa biểu
    function toggleTimetableFullscreen() {
        const container = document.getElementById('weeklyTimetableContainer');
        const icon = document.getElementById('iconFullscreen');
        if (container.classList.contains('timetable-fullscreen-active')) {
            container.classList.remove('timetable-fullscreen-active');
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        } else {
            container.classList.add('timetable-fullscreen-active');
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        }
    }

    // Thoát fullscreen khi nhấn phím ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const container = document.getElementById('weeklyTimetableContainer');
            if (container && container.classList.contains('timetable-fullscreen-active')) {
                toggleTimetableFullscreen();
            }
        }
    });
</script>
@endpush
@endsection
