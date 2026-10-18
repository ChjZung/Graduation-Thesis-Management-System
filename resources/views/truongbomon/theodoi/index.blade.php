@extends('layouts.truongbomon')

@section('page_title', 'Theo Dõi Tiến Độ Bộ Môn')

@push('styles')
<style>
    /* ── MAIN TABS SWITCHER ── */
    #theoDoiMainTabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
    }
    #theoDoiMainTabs .nav-link {
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 8px 22px;
        border-radius: 999px;
        border: none;
        transition: all 0.2s ease;
    }
    #theoDoiMainTabs .nav-link:hover {
        color: #002855;
        background: rgba(255, 255, 255, 0.6);
    }
    #theoDoiMainTabs .nav-link.active {
        background: #002855;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 40, 85, 0.2);
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
        background: #003366 !important;
        color: #ffffff !important;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 12px !important;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-academic tbody td {
        padding: 10px 12px !important;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 13px;
        vertical-align: middle;
    }
    .table-academic tbody tr:hover {
        background-color: #f0f7ff;
    }

    /* 6 Stage Cards */
    .stage-card {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #ffffff;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.25rem;
    }
    .stage-card:hover {
        border-color: #0284c7;
        box-shadow: 0 8px 20px rgba(2, 132, 199, 0.08);
        transform: translateY(-3px);
    }
    .stage-icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .stage-description-box {
        background-color: #f8fafc;
        border-left: 3px solid #0284c7;
        border-radius: 0 0.5rem 0.5rem 0;
        padding: 0.6rem 0.8rem;
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.45;
        min-height: 48px;
        display: flex;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="page-main-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Theo Dõi Tiến Độ Đề Tài &amp; GVHD – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
            </h4>
            <div class="text-muted small mt-1">
                Kế hoạch hiện hành: <strong class="text-dark">{{ $activePlan->TenKeHoach ?? 'Khóa luận tốt nghiệp' }}</strong>
                @if($currentPhase)
                    &bull; Mốc hiện tại: <span class="badge bg-primary-subtle text-primary border">{{ $currentPhase->TenMoc }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongbomon.duyet_detai.index', ['tab' => 'thongke']) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-chart-column me-2"></i> Thống Kê Bộ Môn
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold d-inline-flex align-items-center">
                <i class="fa-solid fa-clipboard-check me-2"></i> Duyệt Đề Tài
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ═══ 6 THẺ KPI TIẾN ĐỘ CHUẨN BỘ MÔN ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #4f46e5 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Học Phần KLTN</div>
                    <h3 class="fw-bold my-1 text-indigo" style="color: #4f46e5;">{{ $stats['total_courses'] ?? count($hocPhanStats) }}</h3>
                    <div class="small text-muted">Bộ môn phụ trách</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Số Nhóm</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total_nhom'] }}</h3>
                    <div class="small text-muted">{{ $stats['total_gv'] }} GV &bull; {{ $stats['total_detai'] }} đề tài</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Đang Đúng Hạn</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['dang_thuc_hien'] }}</h3>
                    <div class="small text-muted">Đúng tiến độ quy định</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Chờ Duyệt / Xử Lý</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['cho_xu_ly'] }}</h3>
                    <div class="small text-muted">Cần Bộ môn/GV duyệt</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Quá Hạn / Chậm</div>
                    <h3 class="fw-bold my-1 text-danger">{{ $stats['qua_han'] }}</h3>
                    <div class="small text-muted">Cần nhắc nhở gấp</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0284c7 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Đã Hoàn Thành</div>
                    <h3 class="fw-bold my-1 text-info">{{ $stats['hoan_thanh'] }}</h3>
                    <div class="small text-muted">Bảo vệ thành công</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NAVIGATION TABS ═══ -->
    <div class="mb-4">
        <ul class="nav nav-pills d-inline-flex flex-wrap gap-1" id="theoDoiMainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nhom-tab" data-bs-toggle="pill" data-bs-target="#nhom-pane" type="button" role="tab">
                    @if($isOverviewMode)
                        <i class="fa-solid fa-graduation-cap me-2"></i> Tổng Quan Theo Học Phần ({{ count($hocPhanStats) }})
                    @else
                        <i class="fa-solid fa-users me-2"></i> Nhóm Học Phần: {{ $selectedHocPhan->TenHocPhan ?? 'Học phần' }} ({{ $nhoms->total() }})
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="warning-tab" data-bs-toggle="pill" data-bs-target="#warning-pane" type="button" role="tab">
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Cảnh Báo &amp; Đôn Đốc
                    @if($warningNhoms->count() > 0)
                        <span class="badge bg-danger rounded-pill ms-1">{{ $warningNhoms->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="stages-tab" data-bs-toggle="pill" data-bs-target="#stages-pane" type="button" role="tab">
                    <i class="fa-solid fa-diagram-project me-2"></i> Tiến Trình 6 Giai Đoạn
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gv-tab" data-bs-toggle="pill" data-bs-target="#gv-pane" type="button" role="tab">
                    <i class="fa-solid fa-chalkboard-user me-2"></i> Giám Sát GVHD ({{ $stats['total_gv'] }})
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="theodoiTabContent">
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- TAB 1: TỔNG QUAN THEO HỌC PHẦN (CẤP 1 & 2) HOẶC CHI TIẾT NHÓM (CẤP 3) -->
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="nhom-pane" role="tabpanel">

            @if($isOverviewMode)
                {{-- ──────── CẤP 1 & 2: TỔNG QUAN THEO HỌC PHẦN (KHÔNG DUMP TẤT CẢ NHÓM) ──────── --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-book-open-reader text-primary me-2"></i>
                                Danh Sách Học Phần Khóa Luận / Đồ Án Bộ Môn Phụ Trách
                            </h5>
                            <span class="text-muted small">
                                Chọn một học phần để xem danh sách chi tiết các nhóm sinh viên, đánh giá tiến độ và gửi nhắc nhở.
                            </span>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace">
                            {{ count($hocPhanStats) }} Học phần
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-academic table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 130px;">Mã Học Phần</th>
                                        <th style="min-width: 260px;">Tên Học Phần</th>
                                        <th class="text-center" style="width: 100px;">Số Tín Chỉ</th>
                                        <th class="text-center" style="width: 110px;">Số Nhóm</th>
                                        <th style="min-width: 200px;">Tiến Độ Hoàn Thành TB</th>
                                        <th class="text-center" style="width: 120px;">Quá Hạn</th>
                                        <th class="text-center" style="width: 150px;">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hocPhanStats as $hpCode => $item)
                                        @php
                                            $hp = $item['hoc_phan'];
                                            $total = $item['total_nhom'];
                                            $avg = $item['avg_progress'];
                                            $overdue = $item['qua_han'];
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge-code font-monospace fw-bold">{{ $hp->MaHocPhan }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $hp->TenHocPhan }}</div>
                                                <div class="small text-muted">{{ $hp->LoaiHocPhan ?? 'Khóa luận tốt nghiệp' }}</div>
                                            </td>
                                            <td class="text-center font-monospace">
                                                {{ $hp->SoTinChi ?? 10 }} TC
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary border px-2.5 py-1 fw-bold fs-6">
                                                    {{ $total }} nhóm
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 8px; border-radius: 6px;">
                                                        <div class="progress-bar {{ $avg >= 80 ? 'bg-success' : ($avg >= 40 ? 'bg-primary' : 'bg-warning') }}"
                                                             role="progressbar" style="width: {{ $avg }}%;"></div>
                                                    </div>
                                                    <span class="fw-bold font-monospace small text-dark" style="min-width: 40px;">{{ $avg }}%</span>
                                                </div>
                                                <div class="small text-muted mt-1" style="font-size: 11.5px;">
                                                    <span class="text-success">{{ $item['dang_thuc_hien'] }} đúng hạn</span> &bull; 
                                                    <span class="text-warning">{{ $item['cho_xu_ly'] }} chờ xử lý</span> &bull; 
                                                    <span class="text-info">{{ $item['hoan_thanh'] }} hoàn thành</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($overdue > 0)
                                                    <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $overdue }} nhóm
                                                    </span>
                                                @else
                                                    <span class="text-muted small">0 nhóm</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('truongbomon.theodoi.index', ['MaHocPhan' => $hp->MaHocPhan]) }}" 
                                                   class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm d-inline-flex align-items-center gap-1">
                                                    Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted fst-italic">
                                                Chưa có thông tin học phần nào thuộc bộ môn.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- DANH SÁCH NHÓM CẦN LƯU Ý GẤP (CẢNH BÁO NHANH CHO TBM) --}}
                @if($warningNhoms->count() > 0)
                    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-danger border-4">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-danger mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Nhóm Cần Quan Tâm &amp; Đôn Đốc Ngay Trong Bộ Môn
                            </h6>
                            <span class="badge bg-danger rounded-pill px-3 py-1">{{ $warningNhoms->count() }} nhóm</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px;">Mã Nhóm</th>
                                            <th>Đề Tài / Học Phần</th>
                                            <th>GVHD</th>
                                            <th>GĐ Hiện Tại</th>
                                            <th>Lý Do Cảnh Báo</th>
                                            <th class="text-center" style="width: 130px;">Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($warningNhoms->take(5) as $wn)
                                            <tr>
                                                <td><span class="badge-code">{{ $wn->MaNhom }}</span></td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $wn->deTai->TenDeTai ?? 'Chưa đăng ký đề tài' }}</div>
                                                    <span class="badge bg-light text-secondary border mt-1" style="font-size: 11px;">
                                                        {{ $wn->ten_hoc_phan ?? 'Khóa luận' }}
                                                    </span>
                                                </td>
                                                <td>{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa có GVHD' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary rounded-pill px-2 py-1">GĐ {{ $wn->current_stage ?? 1 }}</span>
                                                </td>
                                                <td>
                                                    <ul class="mb-0 ps-3 text-danger small">
                                                        @foreach($wn->warning_reasons as $reason)
                                                            <li>{{ $reason }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('truongbomon.theodoi.show', $wn->MaNhom) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="Xem chi tiết">
                                                        <i class="fa-regular fa-eye me-1"></i> Xem
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning text-dark rounded-3 px-2 py-1 fw-bold btn-open-remind" 
                                                            data-manhom="{{ $wn->MaNhom }}"
                                                            data-tennhom="{{ $wn->TenNhom }}"
                                                            data-tendetai="{{ $wn->deTai->TenDeTai ?? 'Chưa gán đề tài' }}"
                                                            data-gvhd="{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}"
                                                            data-stage="{{ $wn->current_stage ?? 1 }}"
                                                            data-stagelabel="Giai đoạn {{ $wn->current_stage ?? 1 }}"
                                                            title="Gửi yêu cầu nhắc nhở">
                                                        <i class="fa-solid fa-bell"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                {{-- ──────── CẤP 3: CHI TIẾT DANH SÁCH NHÓM THEO HỌC PHẦN ĐÃ CHỌN ──────── --}}
                
                {{-- Header quay lại & Tên Học Phần Đang Xem --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light bg-opacity-50 border-start border-primary border-4">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('truongbomon.theodoi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center">
                                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại tổng quan học phần
                            </a>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">
                                    Học phần: {{ $selectedHocPhan->TenHocPhan ?? request('MaHocPhan') }}
                                </h5>
                                <div class="text-muted small">
                                    Mã HP: <strong class="text-primary font-monospace">{{ $selectedHocPhan->MaHocPhan ?? request('MaHocPhan') }}</strong> 
                                    @if(!empty($selectedHocPhan->SoTinChi)) &bull; {{ $selectedHocPhan->SoTinChi }} Tín chỉ @endif
                                    &bull; Đang hiển thị <strong>{{ $nhoms->total() }}</strong> nhóm
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-semibold">
                                Cấp 3: Chi tiết nhóm học phần
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Filter and Search cho Học Phần -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('truongbomon.theodoi.index') }}" class="row g-2 align-items-center">
                            <input type="hidden" name="MaHocPhan" value="{{ request('MaHocPhan') }}">
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" 
                                           placeholder="Mã nhóm, tên nhóm, đề tài, MSSV..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="MaGV" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Tất cả GVHD Bộ môn --</option>
                                    @foreach($giangViens as $gv)
                                        <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                            {{ $gv->HoTen }} ({{ $gv->nhoms_count }} nhóm)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Tất cả giai đoạn (1..6) --</option>
                                    @foreach($stageDefinitions as $stk => $stm)
                                        <option value="{{ $stk }}" {{ request('stage') == $stk ? 'selected' : '' }}>
                                            GĐ {{ $stk }}: {{ $stm['short'] ?? $stm['ten'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status_code" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Tất cả trạng thái --</option>
                                    <option value="DANG_THUC_HIEN" {{ request('status_code') == 'DANG_THUC_HIEN' ? 'selected' : '' }}>Đang thực hiện</option>
                                    <option value="CHO_XU_LY" {{ request('status_code') == 'CHO_XU_LY' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="QUA_HAN" {{ request('status_code') == 'QUA_HAN' ? 'selected' : '' }}>Quá hạn</option>
                                    <option value="HOAN_THANH" {{ request('status_code') == 'HOAN_THANH' ? 'selected' : '' }}>Hoàn thành</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold w-100 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-filter me-2"></i> Lọc
                                </button>
                                @if(request()->anyFilled(['search', 'MaGV', 'stage', 'status_code']))
                                    <a href="{{ route('truongbomon.theodoi.index', ['MaHocPhan' => request('MaHocPhan')]) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại bộ lọc">
                                        <i class="fa-solid fa-rotate"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Groups Tracking Table Trong Học Phần -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-table-list text-primary me-2"></i> Danh Sách Nhóm Sinh Viên Trong Học Phần
                        </h5>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                            Tổng: <strong>{{ $nhoms->total() }}</strong> nhóm
                        </span>
                    </div>

                    <div class="card-body p-0">
                        @if($nhoms->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-users fa-3x mb-3 text-secondary opacity-50"></i>
                                <h6 class="fw-semibold">Không tìm thấy nhóm sinh viên nào theo điều kiện tìm kiếm.</h6>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-academic table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 90px;">Mã Nhóm</th>
                                            <th style="min-width: 220px;">Đề Tài Đăng Ký</th>
                                            <th style="min-width: 170px;">Thành Viên Nhóm</th>
                                            <th style="width: 150px;">GV Hướng Dẫn</th>
                                            <th class="text-center" style="width: 120px;">Giai Đoạn</th>
                                            <th style="min-width: 150px;">Tiến Độ (%)</th>
                                            <th class="text-center" style="width: 115px;">Trạng Thái</th>
                                            <th style="width: 105px;">Hạn Xử Lý</th>
                                            <th class="text-center" style="width: 140px;">Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nhoms as $nhom)
                                            @php
                                                $dt = $nhom->deTai;
                                                $gvhd = $dt->giangVien ?? null;
                                                $stNum = $nhom->current_stage ?? 1;
                                                $stMeta = $stageDefinitions[$stNum] ?? [];
                                                $pct = (int)($nhom->progress_percent ?? 0);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge-code">{{ $nhom->MaNhom }}</span>
                                                    <div class="small text-muted mt-1">{{ $nhom->TenNhom }}</div>
                                                </td>
                                                <td>
                                                    @if($dt)
                                                        <div class="fw-bold text-dark" title="{{ $dt->TenDeTai }}">{{ $dt->TenDeTai }}</div>
                                                        <div class="small text-muted mt-1">
                                                            <span class="badge-code">{{ $dt->MaDeTai }}</span>
                                                            <span class="ms-1">{{ $dt->hocKy->TenHocKy ?? '' }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted fst-italic">Chưa có đề tài</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($nhom->truongNhom)
                                                        <div class="small fw-semibold text-dark">
                                                            <i class="fa-solid fa-crown text-warning me-1" title="Trưởng nhóm"></i>
                                                            {{ $nhom->truongNhom->HoTen }} ({{ $nhom->truongNhom->MaSV }})
                                                        </div>
                                                    @endif
                                                    @foreach($nhom->thanhViens as $tv)
                                                        @if($tv->sinhVien && (!$nhom->truongNhom || $tv->sinhVien->MaSV !== $nhom->truongNhom->MaSV))
                                                            <div class="small text-muted">
                                                                <i class="fa-solid fa-user me-1 text-secondary opacity-75"></i>
                                                                {{ $tv->sinhVien->HoTen }} ({{ $tv->sinhVien->MaSV }})
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if($gvhd)
                                                        <div class="fw-semibold text-dark">{{ $gvhd->HoTen }}</div>
                                                        <div class="small text-muted">{{ $gvhd->HocVi ?? 'Giảng viên' }}</div>
                                                    @else
                                                        <span class="text-muted small">Chưa phân công</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge rounded-pill text-white fw-bold px-3 py-1"
                                                          style="background-color: {{ $stMeta['color'] ?? '#0284c7' }}; font-size: 11px;">
                                                        GĐ {{ $stNum }}
                                                    </span>
                                                    <div class="small text-muted mt-1" style="font-size: 11px;">
                                                        {{ $stMeta['short'] ?? $stMeta['ten'] }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 7px; border-radius: 4px;">
                                                            <div class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 40 ? 'bg-primary' : 'bg-warning') }}"
                                                                 style="width: {{ $pct }}%;"></div>
                                                        </div>
                                                        <span class="fw-bold font-monospace small text-dark">{{ $pct }}%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($nhom->status_code === 'HOAN_THANH')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                            <i class="fa-solid fa-check me-1"></i> Hoàn thành
                                                        </span>
                                                    @elseif($nhom->status_code === 'QUA_HAN')
                                                        <span class="badge bg-danger text-white rounded-pill px-2 py-1">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Quá hạn
                                                        </span>
                                                    @elseif($nhom->status_code === 'CHO_XU_LY')
                                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1">
                                                            <i class="fa-solid fa-hourglass-half me-1"></i> Chờ xử lý
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1">
                                                            <i class="fa-solid fa-spinner me-1"></i> Đang làm
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="small font-monospace text-muted">
                                                        {{ $nhom->tien_do_nghiep_vu?->NgayHoanThanh ? \Carbon\Carbon::parse($nhom->tien_do_nghiep_vu->NgayHoanThanh)->format('d/m/Y') : 'Theo kế hoạch' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex gap-1 align-items-center">
                                                        {{-- Chi tiết --}}
                                                        <a href="{{ route('truongbomon.theodoi.show', $nhom->MaNhom) }}" 
                                                           class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="Chi tiết tiến độ">
                                                            <i class="fa-regular fa-eye"></i>
                                                        </a>
                                                        {{-- Lịch sử --}}
                                                        <a href="{{ route('truongbomon.theodoi.lichSu', $nhom->MaNhom) }}" 
                                                           class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" title="Xem lịch sử & nhắc nhở">
                                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                                        </a>
                                                        {{-- Gửi nhắc nhở --}}
                                                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-3 px-2 py-1 fw-bold btn-open-remind" 
                                                                data-manhom="{{ $nhom->MaNhom }}"
                                                                data-tennhom="{{ $nhom->TenNhom }}"
                                                                data-tendetai="{{ $dt->TenDeTai ?? 'Chưa gán đề tài' }}"
                                                                data-gvhd="{{ $gvhd->HoTen ?? 'Chưa phân công' }}"
                                                                data-stage="{{ $stNum }}"
                                                                data-stagelabel="Giai đoạn {{ $stNum }}: {{ $stMeta['ten'] ?? '' }}"
                                                                title="Gửi yêu cầu nhắc nhở tiến độ">
                                                            <i class="fa-solid fa-bell"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($nhoms->hasPages())
                                <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="text-muted small">
                                        Hiển thị <strong>{{ $nhoms->firstItem() }}</strong> - <strong>{{ $nhoms->lastItem() }}</strong> trên tổng <strong>{{ $nhoms->total() }}</strong> nhóm
                                    </div>
                                    <div>
                                        {{ $nhoms->appends(request()->all())->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- TAB 2: CẢNH BÁO TIẾN ĐỘ & ĐÔN ĐỐC (warningNhoms) -->
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="warning-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                            Nhóm Có Dấu Hiệu Chậm Tiến Độ Cần Đôn Đốc
                        </h5>
                        <span class="text-muted small">Bao gồm nhóm quá hạn nộp báo cáo, hồ sơ chờ duyệt lâu ngày hoặc thiếu GVHD</span>
                    </div>
                    <span class="badge bg-danger text-white rounded-pill px-3 py-2 fw-bold">
                        {{ $warningNhoms->count() }} trường hợp
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($warningNhoms->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-circle-check text-success fa-3x mb-3 opacity-75"></i>
                            <h6 class="fw-bold text-dark">Rất tốt! Bộ môn hiện không có nhóm nào bị chậm hoặc quá hạn tiến độ.</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-academic table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">STT</th>
                                        <th style="width: 90px;">Mã Nhóm</th>
                                        <th style="min-width: 200px;">Đề Tài &amp; GVHD</th>
                                        <th style="width: 130px;" class="text-center">Giai Đoạn</th>
                                        <th style="min-width: 250px;">Lý Do Cảnh Báo</th>
                                        <th style="width: 110px;" class="text-center">Tình Trạng</th>
                                        <th style="width: 140px;" class="text-center">Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($warningNhoms as $wIdx => $wn)
                                        @php
                                            $stNum = $wn->current_stage ?? 1;
                                            $stMeta = $stageDefinitions[$stNum] ?? [];
                                        @endphp
                                        <tr>
                                            <td class="text-center font-monospace text-muted">{{ $wIdx + 1 }}</td>
                                            <td>
                                                <span class="badge-code">{{ $wn->MaNhom }}</span>
                                                <div class="small text-muted mt-1">{{ $wn->TenNhom }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" title="{{ $wn->deTai->TenDeTai ?? 'Chưa đăng ký' }}">
                                                    {{ $wn->deTai->TenDeTai ?? 'Chưa đăng ký đề tài' }}
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    GVHD: <strong>{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill text-white fw-bold px-3 py-1"
                                                      style="background-color: {{ $stMeta['color'] ?? '#0284c7' }}; font-size: 11px;">
                                                    GĐ {{ $stNum }}
                                                </span>
                                            </td>
                                            <td>
                                                <ul class="mb-0 ps-3 text-danger small" style="line-height: 1.45;">
                                                    @foreach($wn->warning_reasons as $reason)
                                                        <li>{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td class="text-center">
                                                @if($wn->status_code === 'QUA_HAN')
                                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1">Quá hạn</span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Chờ xử lý</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1 align-items-center">
                                                    <a href="{{ route('truongbomon.theodoi.show', $wn->MaNhom) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="Chi tiết">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning text-dark rounded-3 px-2 py-1 fw-bold btn-open-remind" 
                                                            data-manhom="{{ $wn->MaNhom }}"
                                                            data-tennhom="{{ $wn->TenNhom }}"
                                                            data-tendetai="{{ $wn->deTai->TenDeTai ?? 'Chưa gán đề tài' }}"
                                                            data-gvhd="{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}"
                                                            data-stage="{{ $stNum }}"
                                                            data-stagelabel="Giai đoạn {{ $stNum }}: {{ $stMeta['ten'] ?? '' }}"
                                                            title="Gửi yêu cầu nhắc nhở">
                                                        <i class="fa-solid fa-bell me-1"></i> Nhắc
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- TAB 3: TIẾN TRÌNH 6 GIAI ĐOẠN NGHIỆP VỤ BỘ MÔN -->
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="stages-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-diagram-project text-primary me-2"></i>
                            Tiến Trình 6 Giai Đoạn Quy Chuẩn – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
                        </h5>
                        <span class="text-muted small">Theo dõi phân bố số lượng nhóm và tỷ lệ hoàn thành qua các giai đoạn</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($stageDefinitions as $stNum => $stMeta)
                            @php
                                $stData = $stageStats[$stNum] ?? [];
                                $totalInStage = $stData['total_in_stage'] ?? 0;
                                $rate = $stData['completion_rate'] ?? 0;
                            @endphp
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="stage-card">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="stage-icon-circle" style="background-color: {{ $stMeta['color'] }}15; color: {{ $stMeta['color'] }};">
                                                    <i class="fa-solid {{ $stMeta['icon'] }}"></i>
                                                </div>
                                                <div>
                                                    <span class="badge rounded-pill fw-bold px-3 py-1" style="background-color: {{ $stMeta['color'] }}20; color: {{ $stMeta['color'] }}; font-size: 11px;">
                                                        Giai đoạn {{ $stNum }}
                                                    </span>
                                                    <h6 class="fw-bold text-dark mb-0 mt-1" style="font-size: 0.95rem;">
                                                        {{ $stMeta['ten'] }}
                                                    </h6>
                                                </div>
                                            </div>
                                            @if(($stData['qua_han'] ?? 0) > 0)
                                                <span class="badge bg-danger text-white rounded-pill px-2 py-1 small" title="Có nhóm trễ hạn">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $stData['qua_han'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="stage-description-box mb-3" style="border-left-color: {{ $stMeta['color'] }};">
                                            <span><i class="fa-solid fa-circle-info me-2 opacity-50" style="color: {{ $stMeta['color'] }};"></i>{{ $stMeta['mo_ta'] ?? 'Triển khai công việc theo quy định chuyên môn.' }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2 text-muted small">
                                            <span>Số nhóm ở GĐ này:</span>
                                            <strong class="text-dark font-monospace fs-6">{{ $totalInStage }} nhóm</strong>
                                        </div>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center text-muted small mb-1">
                                                <span>Hoàn thành điều kiện:</span>
                                                <span class="fw-bold text-dark font-monospace">{{ $rate }}%</span>
                                            </div>
                                            <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                                <div class="progress-bar rounded-pill" role="progressbar" 
                                                     style="width: {{ $rate }}%; background-color: {{ $stMeta['color'] }};"></div>
                                            </div>
                                        </div>
                                        <a href="{{ route('truongbomon.theodoi.index', ['stage' => $stNum]) }}" 
                                           class="btn btn-sm btn-light border w-100 rounded-pill fw-semibold text-primary py-2" style="font-size: 12px;">
                                            Xem nhóm Giai đoạn {{ $stNum }} <i class="fa-solid fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- TAB 4: GIÁM SÁT GIẢNG VIÊN HƯỚNG DẪN (GVHD) -->
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="gv-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                        Danh Sách Giảng Viên Thuộc Bộ Môn &amp; Tình Trạng Hướng Dẫn
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-academic table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="10%">Mã GV</th>
                                    <th width="24%">Họ và Tên Giảng Viên</th>
                                    <th width="16%">Học Vị / Học Hàm</th>
                                    <th width="12%" class="text-center">Đề Tài Đề Xuất</th>
                                    <th width="12%" class="text-center">Đã Công Bố</th>
                                    <th width="14%" class="text-center">Nhóm Đang Hướng Dẫn</th>
                                    <th width="12%" class="text-center">Tình Trạng Tải</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($giangViens as $gv)
                                    @php
                                        $quota = 5; // Định mức tối đa khuyến nghị: 5 nhóm
                                        $percent = min(100, round(($gv->nhoms_count / $quota) * 100));
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge-code">{{ $gv->MaGV }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $gv->HoTen }}</div>
                                            <div class="small text-muted">{{ $gv->Email ?? 'Chưa cập nhật email' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">
                                                {{ $gv->HocVi ?? 'Giảng viên' }}
                                                @if($gv->HocHam) &bull; {{ $gv->HocHam }} @endif
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $gv->de_tais_count }}</td>
                                        <td class="text-center fw-bold text-success">{{ $gv->de_tais_cong_bo_count }}</td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ $gv->nhoms_count >= $quota ? 'text-danger' : 'text-primary' }}">
                                                {{ $gv->nhoms_count }} / {{ $quota }} nhóm
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($gv->nhoms_count >= $quota)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Đầy tải (100%)</span>
                                            @elseif($gv->nhoms_count > 0)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">{{ $percent }}% tải</span>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1">Còn chỗ (0%)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════ -->
<!-- MODAL GỬI YÊU CẦU NHẮC NHỞ TIẾN ĐỘ CHO TRƯỞNG BỘ MÔN -->
<!-- ═════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="remindModalTBM" tabindex="-1" aria-labelledby="remindModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="remindForm" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #003366; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="remindModalLabel">
                        <i class="fa-solid fa-bell text-warning"></i> Gửi Yêu Cầu Nhắc Nhở Tiến Độ – Cấp Bộ Môn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Thông tin nhóm & đề tài tự động điền --}}
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <span class="text-muted small d-block">Nhóm sinh viên:</span>
                                <strong class="text-dark" id="modalNhomText">---</strong>
                            </div>
                            <div class="col-md-5">
                                <span class="text-muted small d-block">Đề tài:</span>
                                <strong class="text-dark" id="modalDeTaiText">---</strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">GVHD:</span>
                                <strong class="text-dark" id="modalGVHDText">---</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Chọn đối tượng nhận --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            Người nhận yêu cầu nhắc nhở: <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="recGVHD" value="gvhd" checked>
                                <label class="form-check-label fw-semibold text-dark" for="recGVHD">
                                    <i class="fa-solid fa-chalkboard-user text-primary me-2"></i> Giảng viên hướng dẫn (GVHD)
                                    <span class="text-muted small d-block ms-4">Nhắc nhở GVHD cập nhật đánh giá, nhận xét đợt nộp hoặc đôn đốc nhóm sinh viên</span>
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="recSV" value="sinh_vien">
                                <label class="form-check-label fw-semibold text-dark" for="recSV">
                                    <i class="fa-solid fa-users text-success me-2"></i> Nhóm sinh viên / Trưởng nhóm
                                    <span class="text-muted small d-block ms-4">Yêu cầu nhóm sinh viên khẩn trương nộp báo cáo tiến độ, nộp file đề cương hoặc chỉnh sửa</span>
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="recBoth" value="ca_hai">
                                <label class="form-check-label fw-semibold text-dark" for="recBoth">
                                    <i class="fa-solid fa-paper-plane text-warning me-2"></i> Cả Giảng viên hướng dẫn &amp; Nhóm sinh viên
                                    <span class="text-muted small d-block ms-4">Đồng gửi thông báo tới cả 2 phía để phối hợp xử lý tiến độ</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Giai đoạn tiến độ --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Giai đoạn cần nhắc nhở:</label>
                            <select name="giai_doan" id="modalStageSelect" class="form-select">
                                @foreach($stageDefinitions as $stNum => $stMeta)
                                    <option value="{{ $stNum }}">GĐ {{ $stNum }}: {{ $stMeta['ten'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Thời hạn xử lý --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Thời hạn thực hiện / Phản hồi:</label>
                            <input type="date" name="han_xu_ly" id="modalHanXuLy" class="form-control" value="{{ \Carbon\Carbon::now()->addDays(3)->format('Y-m-d') }}">
                            <div class="form-text small">Thời hạn người nhận cần hoàn thành yêu cầu đôn đốc.</div>
                        </div>
                    </div>

                    {{-- Lý do nhắc nhở --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Lý do nhắc nhở: <span class="text-danger">*</span></label>
                        <select id="modalReasonPreset" class="form-select mb-2" onchange="applyReasonPreset(this.value)">
                            <option value="">-- Chọn lý do mẫu hoặc tự nhập --</option>
                            <option value="Chưa nộp báo cáo tiến độ đúng hạn theo quy định">Chưa nộp báo cáo tiến độ đúng hạn theo quy định</option>
                            <option value="Báo cáo tiến độ chưa đạt yêu cầu chuyên môn, cần chỉnh sửa bổ sung gấp">Báo cáo tiến độ chưa đạt yêu cầu chuyên môn, cần chỉnh sửa bổ sung gấp</option>
                            <option value="Giảng viên hướng dẫn chưa cập nhật đánh giá / nhận xét tiến độ cho sinh viên">Giảng viên hướng dẫn chưa cập nhật đánh giá / nhận xét tiến độ cho sinh viên</option>
                            <option value="Chậm nộp đề cương chi tiết hoặc tài liệu nghiệm thu giai đoạn">Chậm nộp đề cương chi tiết hoặc tài liệu nghiệm thu giai đoạn</option>
                            <option value="Tình trạng thực hiện đề tài chậm hơn kế hoạch chung của Khoa & Bộ môn">Tình trạng thực hiện đề tài chậm hơn kế hoạch chung của Khoa & Bộ môn</option>
                        </select>
                        <input type="text" name="ly_do" id="modalReasonInput" class="form-control" placeholder="Nhập lý do nhắc nhở..." required>
                    </div>

                    {{-- Nội dung chi tiết --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Nội dung yêu cầu cụ thể: <span class="text-danger">*</span></label>
                        <textarea name="noi_dung" id="modalContentInput" rows="4" class="form-control" placeholder="Ghi rõ nội dung chỉ đạo, yêu cầu thực hiện và các lưu ý cần thiết..." required></textarea>
                    </div>

                    {{-- Xác nhận trước khi gửi --}}
                    <div class="alert alert-info py-2 px-3 small border-0 mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Thông báo sẽ được gửi trực tiếp vào tài khoản hệ thống của người nhận và lưu lại vào sổ lưu vết <strong>Lịch sử tiến độ</strong> của nhóm để đối chiếu.
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light border px-4 rounded-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4 rounded-3 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i> Xác Nhận Gửi Yêu Cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const remindModal = new bootstrap.Modal(document.getElementById('remindModalTBM'));
        const form = document.getElementById('remindForm');

        document.querySelectorAll('.btn-open-remind').forEach(btn => {
            btn.addEventListener('click', function () {
                const maNhom = this.getAttribute('data-manhom');
                const tenNhom = this.getAttribute('data-tennhom');
                const tenDeTai = this.getAttribute('data-tendetai');
                const gvhd = this.getAttribute('data-gvhd');
                const stage = this.getAttribute('data-stage');

                document.getElementById('modalNhomText').textContent = `${tenNhom} (${maNhom})`;
                document.getElementById('modalDeTaiText').textContent = tenDeTai;
                document.getElementById('modalGVHDText').textContent = gvhd;

                const stageSelect = document.getElementById('modalStageSelect');
                if (stageSelect && stage) stageSelect.value = stage;

                // Pre-fill reason and message
                document.getElementById('modalReasonPreset').value = "Chưa nộp báo cáo tiến độ đúng hạn theo quy định";
                applyReasonPreset("Chưa nộp báo cáo tiến độ đúng hạn theo quy định", tenNhom, maNhom);

                // Form action
                form.action = "{{ url('truongbomon/theo-doi') }}/" + maNhom + "/remind";

                remindModal.show();
            });
        });
    });

    function applyReasonPreset(preset, tenNhom, maNhom) {
        if (!preset) return;
        document.getElementById('modalReasonInput').value = preset;
        const currentNhom = tenNhom || document.getElementById('modalNhomText').textContent;
        const msg = `Kính gửi đối tượng liên quan thuộc nhóm ${currentNhom}.\n` +
                    `Ban Chủ nhiệm Bộ môn ghi nhận vấn đề: "${preset}".\n` +
                    `Đề nghị quý Thầy/Cô và các bạn sinh viên khẩn trương rà soát, hoàn thiện và cập nhật tiến độ báo cáo lên hệ thống theo đúng hạn định.`;
        document.getElementById('modalContentInput').value = msg;
    }
</script>
@endpush
