@extends('layouts.truongkhoa')

@section('page_title', 'Theo Dõi Tiến Độ Toàn Khoa')

@push('styles')
<style>
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
        padding: 8px 24px;
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
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-chart-line text-primary me-2"></i>
                Giám Sát Tiến Độ &amp; Tình Hình Bảo Vệ – {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <div class="text-muted small mt-1">
                Kế hoạch hiện hành: <strong class="text-dark">{{ $activePlan->TenKeHoach ?? 'Khóa luận tốt nghiệp' }}</strong>
                @if($currentPhase)
                    &bull; Mốc hiện tại: <span class="badge bg-primary-subtle text-primary border">{{ $currentPhase->TenMoc }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- ═══ SUMMARY KPI CARDS TOÀN KHOA ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #4f46e5 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Bộ Môn &bull; Học Phần</div>
                    <h4 class="fw-bold my-1 text-indigo" style="color: #4f46e5;">{{ $stats['total_bomon'] ?? count($departmentStats) }} <span class="fs-6 fw-normal text-muted">BM</span> &bull; {{ $stats['total_courses'] ?? count($hocPhanStats) }} <span class="fs-6 fw-normal text-muted">HP</span></h4>
                    <div class="small text-muted">Quy mô quản lý khoa</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Số Nhóm</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total_nhom'] }}</h3>
                    <div class="small text-muted">{{ $stats['total_sv'] }} SV &bull; {{ $stats['total_detai'] }} đề tài</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Đang Đúng Hạn</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['dang_thuc_hien'] }}</h3>
                    <div class="small text-muted">{{ $stats['detai_cong_bo'] }} đề tài công bố</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Chờ Xử Lý / Duyệt</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['cho_xu_ly'] }}</h3>
                    <div class="small text-muted">Chờ Bộ môn/GV duyệt</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Quá Hạn / Chậm</div>
                    <h3 class="fw-bold my-1 text-danger">{{ $stats['qua_han'] }}</h3>
                    <div class="small text-muted">Cần Khoa đôn đốc</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0284c7 !important;">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Đã Hoàn Thành</div>
                    <h3 class="fw-bold my-1 text-info">{{ $stats['hoan_thanh'] }}</h3>
                    <div class="small text-muted">{{ $stats['hoso_du_dieu_kien'] }} đủ ĐK bảo vệ</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NAVIGATION TABS ═══ -->
    <div class="mb-4">
        <ul class="nav nav-pills d-inline-flex" id="theoDoiMainTabs">
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'tien_do') === 'tien_do' ? 'active' : '' }}" 
                   href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}">
                    @if($navLevel === 1)
                        <i class="fa-solid fa-building-columns me-2"></i> Tổng Quan Tiến Độ Toàn Khoa
                    @elseif($navLevel === 2)
                        <i class="fa-solid fa-diagram-project me-2"></i> Tiến Độ Bộ Môn: {{ $selectedBoMon->TenBoMon ?? 'Bộ môn' }}
                    @else
                        <i class="fa-solid fa-users me-2"></i> Nhóm Học Phần: {{ $selectedHocPhan->TenHocPhan ?? 'Học phần' }}
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'tien_do') === 'bao_ve' ? 'active' : '' }}" 
                   href="{{ route('truongkhoa.theodoi.index', ['tab' => 'bao_ve']) }}">
                    <i class="fa-solid fa-shield-halved me-2"></i> Tình Hình Bảo Vệ &amp; Hội Đồng Chấm
                </a>
            </li>
        </ul>
    </div>

    @if(($tab ?? 'tien_do') === 'tien_do')
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- TAB 1: TIẾN ĐỘ THEO CẤP BẬC (CẤP 1 -> CẤP 2 -> CẤP 3) -->
        <!-- ═══════════════════════════════════════════════════════════════ -->

        @if($navLevel === 1)
            {{-- ──────── CẤP 1: TỔNG QUAN TIẾN ĐỘ THEO BỘ MÔN (TOÀN KHOA) ──────── --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-building-columns text-primary me-2"></i>
                            Tổng Hợp Tình Hình Tiến Độ Theo Từng Bộ Môn Trong Khoa
                        </h6>
                        <span class="text-muted small">Bấm "Xem chi tiết bộ môn" để theo dõi danh sách các học phần và nhóm thực hiện.</span>
                    </div>
                    <span class="badge bg-light text-secondary border font-monospace px-3 py-1">
                        {{ count($departmentStats) }} Bộ môn
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 220px;">Bộ Môn</th>
                                    <th class="text-center" style="width: 100px;">Số Học Phần</th>
                                    <th class="text-center" style="width: 110px;">Tổng Số Nhóm</th>
                                    <th style="min-width: 200px;">Tiến Độ Tổng Quan TB</th>
                                    <th class="text-center" style="width: 120px;">Quá Hạn</th>
                                    <th class="text-center" style="width: 140px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departmentStats as $bmCode => $bmData)
                                    @php
                                        $avg = $bmData['avg_progress'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $bmData['bomon']->TenBoMon }}</div>
                                            <div class="text-muted small">TBM: {{ $bmData['bomon']->TruongBoMon ?? 'Chưa cập nhật' }} &bull; Mã: <span class="badge-code">{{ $bmCode }}</span></div>
                                        </td>
                                        <td class="text-center font-monospace fw-semibold">{{ $bmData['total_courses'] }} HP</td>
                                        <td class="text-center fw-bold text-primary fs-6">{{ $bmData['total'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 6px;">
                                                    <div class="progress-bar {{ $avg >= 80 ? 'bg-success' : ($avg >= 40 ? 'bg-primary' : 'bg-warning') }}"
                                                         role="progressbar" style="width: {{ $avg }}%;"></div>
                                                </div>
                                                <span class="fw-bold font-monospace small text-dark" style="min-width: 38px;">{{ $avg }}%</span>
                                            </div>
                                            <div class="small text-muted mt-1" style="font-size: 11.5px;">
                                                <span class="text-success">{{ $bmData['dang_thuc_hien'] }} đúng hạn</span> &bull; 
                                                <span class="text-warning">{{ $bmData['cho_xu_ly'] }} chờ xử lý</span> &bull; 
                                                <span class="text-info">{{ $bmData['hoan_thanh'] }} hoàn thành</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($bmData['qua_han'] > 0)
                                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $bmData['qua_han'] }} nhóm
                                                </span>
                                            @else
                                                <span class="text-muted small">0 nhóm</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do', 'MaBoMon' => $bmCode]) }}" 
                                               class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm" style="font-size: 12.5px;">
                                                Xem chi tiết BM <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CẢNH BÁO CÁC NHÓM CHẬM TIẾN ĐỘ / QUÁ HẠN TOÀN KHOA --}}
            @if($warningNhoms->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-danger border-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                                Các Trường Hợp Chậm Tiến Độ / Cần Trưởng Khoa Chỉ Đạo &amp; Đôn Đốc
                            </h6>
                            <span class="text-muted small">Tập trung các nhóm quá hạn, chậm nộp hồ sơ hoặc cần phối hợp Trưởng bộ môn</span>
                        </div>
                        <span class="badge bg-danger text-white rounded-pill px-3 py-2 fw-bold">
                            {{ $warningNhoms->count() }} trường hợp
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-academic table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">STT</th>
                                        <th style="width: 90px;">Mã Nhóm</th>
                                        <th style="min-width: 220px;">Đề Tài &amp; Bộ Môn</th>
                                        <th style="width: 140px;">GV Hướng Dẫn</th>
                                        <th class="text-center" style="width: 110px;">Giai Đoạn</th>
                                        <th style="min-width: 240px;">Lý Do Cảnh Báo</th>
                                        <th class="text-center" style="width: 140px;">Chỉ Đạo Nhắc Nhở</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($warningNhoms->take(8) as $wIdx => $wn)
                                        @php
                                            $stNum = $wn->current_stage ?? 1;
                                            $stMeta = $stageDefinitions[$stNum] ?? [];
                                            $wnBm = $wn->deTai?->giangVien?->boMon ?? ($wn->deTai?->hocPhan?->boMon ?? null);
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
                                                    <i class="fa-solid fa-building-columns me-1 text-primary"></i>
                                                    {{ $wnBm->TenBoMon ?? 'Bộ môn' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                                                <div class="small text-muted">{{ $wn->deTai?->giangVien?->HocVi ?? '' }}</div>
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
                                                <div class="d-inline-flex gap-1 align-items-center">
                                                    <a href="{{ route('truongkhoa.theodoi.show', $wn->MaNhom) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="Chi tiết">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning text-dark rounded-3 px-2 py-1 fw-bold btn-open-remind-tk" 
                                                            data-manhom="{{ $wn->MaNhom }}"
                                                            data-tennhom="{{ $wn->TenNhom }}"
                                                            data-tendetai="{{ $wn->deTai->TenDeTai ?? 'Chưa gán đề tài' }}"
                                                            data-gvhd="{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}"
                                                            data-bomon="{{ $wnBm->TenBoMon ?? 'Bộ môn' }}"
                                                            data-tbm="{{ $wnBm->TruongBoMon ?? 'Trưởng bộ môn' }}"
                                                            data-stage="{{ $stNum }}"
                                                            title="Gửi chỉ đạo đôn đốc">
                                                        <i class="fa-solid fa-bell me-1"></i> Chỉ đạo
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        @elseif($navLevel === 2)
            {{-- ──────── CẤP 2: CHI TIẾT BỘ MÔN (DANH SÁCH HỌC PHẦN TRONG BỘ MÔN) ──────── --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light bg-opacity-50 border-start border-primary border-4">
                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center">
                            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tổng quan Khoa
                        </a>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">
                                Bộ Môn: {{ $selectedBoMon->TenBoMon ?? request('MaBoMon') }}
                            </h5>
                            <div class="text-muted small">
                                Mã BM: <strong class="text-primary font-monospace">{{ $selectedBoMon->MaBoMon ?? request('MaBoMon') }}</strong>
                                &bull; Trưởng bộ môn: <strong>{{ $selectedBoMon->TruongBoMon ?? 'Chưa cập nhật' }}</strong>
                                &bull; Quản lý <strong>{{ count($hocPhanStats) }}</strong> học phần
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-semibold">
                        Cấp 2: Chi tiết bộ môn
                    </span>
                </div>
            </div>

            {{-- Bảng Học Phần thuộc Bộ môn --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-book-bookmark text-primary me-2"></i>
                            Danh Sách Học Phần Thuộc Bộ Môn {{ $selectedBoMon->TenBoMon ?? '' }}
                        </h6>
                        <span class="text-muted small">Bấm "Xem danh sách nhóm" để theo dõi chi tiết từng nhóm sinh viên.</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-academic table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Mã Học Phần</th>
                                    <th style="min-width: 250px;">Tên Học Phần</th>
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
                                        <td><span class="badge-code font-monospace fw-bold">{{ $hp->MaHocPhan }}</span></td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $hp->TenHocPhan }}</div>
                                            <div class="small text-muted">{{ $hp->LoaiHocPhan ?? 'Khóa luận tốt nghiệp' }}</div>
                                        </td>
                                        <td class="text-center font-monospace">{{ $hp->SoTinChi ?? 10 }} TC</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary border px-2.5 py-1 fw-bold fs-6">
                                                {{ $total }} nhóm
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 6px;">
                                                    <div class="progress-bar {{ $avg >= 80 ? 'bg-success' : ($avg >= 40 ? 'bg-primary' : 'bg-warning') }}"
                                                         style="width: {{ $avg }}%;"></div>
                                                </div>
                                                <span class="fw-bold font-monospace small text-dark" style="min-width: 38px;">{{ $avg }}%</span>
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
                                            <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do', 'MaBoMon' => request('MaBoMon'), 'MaHocPhan' => $hp->MaHocPhan]) }}" 
                                               class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm">
                                                Xem nhóm <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted fst-italic">
                                            Chưa có thông tin học phần nào trong bộ môn này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Cảnh báo nhóm trong bộ môn --}}
            @if($warningNhoms->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-danger border-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                            Nhóm Có Dấu Hiệu Chậm Tiến Độ Thuộc Bộ Môn ({{ $warningNhoms->count() }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Mã Nhóm</th>
                                        <th>Đề Tài</th>
                                        <th>GVHD</th>
                                        <th class="text-center">Giai Đoạn</th>
                                        <th>Lý Do</th>
                                        <th class="text-center" style="width: 130px;">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($warningNhoms as $wn)
                                        <tr>
                                            <td><span class="badge-code">{{ $wn->MaNhom }}</span></td>
                                            <td><div class="fw-bold text-dark">{{ $wn->deTai->TenDeTai ?? 'Chưa đăng ký' }}</div></td>
                                            <td>{{ $wn->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</td>
                                            <td class="text-center"><span class="badge bg-secondary rounded-pill px-2 py-1">GĐ {{ $wn->current_stage ?? 1 }}</span></td>
                                            <td>
                                                <ul class="mb-0 ps-3 text-danger small">
                                                    @foreach($wn->warning_reasons as $reason)
                                                        <li>{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('truongkhoa.theodoi.show', $wn->MaNhom) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
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
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light bg-opacity-50 border-start border-primary border-4">
                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do', 'MaBoMon' => request('MaBoMon')]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center">
                            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Danh sách học phần
                        </a>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">
                                Học phần: {{ $selectedHocPhan->TenHocPhan ?? request('MaHocPhan') }}
                            </h5>
                            <div class="text-muted small">
                                Mã HP: <strong class="text-primary font-monospace">{{ $selectedHocPhan->MaHocPhan ?? request('MaHocPhan') }}</strong>
                                @if($selectedBoMon) &bull; Bộ môn: <strong>{{ $selectedBoMon->TenBoMon }}</strong> @endif
                                &bull; Đang hiển thị <strong>{{ $nhoms->total() }}</strong> nhóm
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-semibold">
                        Cấp 3: Danh sách nhóm học phần
                    </span>
                </div>
            </div>

            {{-- Bộ lọc cho nhóm trong học phần --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('truongkhoa.theodoi.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="tien_do">
                        <input type="hidden" name="MaBoMon" value="{{ request('MaBoMon') }}">
                        <input type="hidden" name="MaHocPhan" value="{{ request('MaHocPhan') }}">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" 
                                       placeholder="Mã nhóm, tên nhóm, đề tài, MSSV..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả giai đoạn (1..6) --</option>
                                @foreach($stageDefinitions as $stk => $stm)
                                    <option value="{{ $stk }}" {{ request('stage') == $stk ? 'selected' : '' }}>
                                        GĐ {{ $stk }}: {{ $stm['short'] ?? $stm['ten'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
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
                            @if(request()->anyFilled(['search', 'stage', 'status_code']))
                                <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do', 'MaBoMon' => request('MaBoMon'), 'MaHocPhan' => request('MaHocPhan')]) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại bộ lọc">
                                    <i class="fa-solid fa-rotate"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bảng Nhóm Khóa Luận Học Phần --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-table-list text-primary me-2"></i>
                        Danh Sách Nhóm Sinh Viên Trong Học Phần
                    </h6>
                    <span class="badge bg-light text-secondary border font-monospace px-3 py-2">
                        Tổng: <strong>{{ $nhoms->total() }}</strong> nhóm
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($nhoms->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted fa-3x mb-3"></i>
                            <h6 class="text-muted">Không tìm thấy nhóm khóa luận nào phù hợp!</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-academic table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">Mã Nhóm</th>
                                        <th style="min-width: 210px;">Đề Tài Đăng Ký</th>
                                        <th style="min-width: 170px;">Thành Viên Nhóm</th>
                                        <th style="width: 140px;">GV Hướng Dẫn</th>
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
                                            $gvhd = $nhom->dangKyDeTai->giangVienHuongDan ?? ($dt->giangVien ?? null);
                                            $stNum = $nhom->current_stage ?? 1;
                                            $stMeta = $stageDefinitions[$stNum] ?? [];
                                            $bm = $dt?->giangVien?->boMon ?? ($dt?->hocPhan?->boMon ?? null);
                                            $pct = (int)($nhom->progress_percent ?? 0);
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge-code">{{ $nhom->MaNhom }}</span>
                                                <div class="small text-muted mt-1">{{ $nhom->TenNhom }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $dt->TenDeTai ?? 'Chưa gán đề tài' }}</div>
                                                <div class="small text-muted mt-1">
                                                    <i class="fa-solid fa-building-columns text-primary me-1"></i>
                                                    {{ $bm->TenBoMon ?? 'N/A' }}
                                                </div>
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
                                                <div class="fw-semibold text-dark">{{ $gvhd->HoTen ?? 'Chưa phân công' }}</div>
                                                <div class="small text-muted">{{ $gvhd->HocVi ?? '' }}</div>
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
                                                    <a href="{{ route('truongkhoa.theodoi.show', $nhom->MaNhom) }}" 
                                                       class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="Chi tiết tiến độ">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('truongkhoa.theodoi.lichSu', $nhom->MaNhom) }}" 
                                                       class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" title="Lịch sử nghiệp vụ & nhắc nhở">
                                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning text-dark rounded-3 px-2 py-1 fw-bold btn-open-remind-tk" 
                                                            data-manhom="{{ $nhom->MaNhom }}"
                                                            data-tennhom="{{ $nhom->TenNhom }}"
                                                            data-tendetai="{{ $dt->TenDeTai ?? 'Chưa gán đề tài' }}"
                                                            data-gvhd="{{ $gvhd->HoTen ?? 'Chưa phân công' }}"
                                                            data-bomon="{{ $bm->TenBoMon ?? 'Bộ môn' }}"
                                                            data-tbm="{{ $bm->TruongBoMon ?? 'Trưởng bộ môn' }}"
                                                            data-stage="{{ $stNum }}"
                                                            title="Gửi chỉ đạo đôn đốc tiến độ">
                                                        <i class="fa-solid fa-bell"></i>
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
            </div>
        @endif

    @else
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- TAB 2: TÌNH HÌNH BẢO VỆ & HỘI ĐỒNG CHẤM (GIỮ NGUYÊN) -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body">
                <form method="GET" action="{{ route('truongkhoa.theodoi.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="bao_ve">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search_baove" class="form-control border-start-0" 
                                   placeholder="Tìm kiếm theo mã hồ sơ, mã nhóm, tên nhóm, tên đề tài..." value="{{ request('search_baove') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="MaBoMon" class="form-select">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($boMons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold text-nowrap w-100 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-filter me-2"></i> Lọc
                        </button>
                        <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'bao_ve']) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-shield-halved text-danger me-2"></i>
                    Danh Sách Hồ Sơ Bảo Vệ Khóa Luận ({{ $hoSoBaoVes->total() }} hồ sơ)
                </h6>
                <div class="small text-muted">Trang {{ $hoSoBaoVes->currentPage() }}/{{ $hoSoBaoVes->lastPage() }}</div>
            </div>
            <div class="card-body p-0">
                @if($hoSoBaoVes->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-file-shield text-muted fa-3x mb-3"></i>
                        <h6 class="text-muted">Không tìm thấy hồ sơ bảo vệ nào phù hợp!</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 100px;">Mã Hồ Sơ</th>
                                    <th>Nhóm Sinh Viên & Đề Tài</th>
                                    <th>Bộ Môn</th>
                                    <th>Xác Nhận GVHD</th>
                                    <th>Hội Đồng Bảo Vệ</th>
                                    <th>Kết Quả Bảo Vệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hoSoBaoVes as $hs)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace">{{ $hs->MaHoSo }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $hs->deTai->TenDeTai ?? 'Chưa có đề tài' }}</div>
                                            <div class="small text-muted">
                                                Nhóm: <span class="fw-semibold">{{ $hs->nhom->TenNhom ?? $hs->MaNhom }}</span>
                                                ({{ $hs->nhom->thanhViens->count() }} thành viên)
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border">
                                                {{ $hs->deTai->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($hs->XacNhanGVHD)
                                                <span class="badge bg-success text-white">
                                                    <i class="fa-solid fa-check me-1"></i> Đủ ĐK bảo vệ
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fa-solid fa-hourglass-half me-1"></i> Chờ duyệt
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hs->hoiDong)
                                                <div class="fw-semibold text-primary">{{ $hs->hoiDong->TenHoiDong }}</div>
                                                <div class="small text-muted">{{ $hs->hoiDong->thanhViens->count() }} ủy viên</div>
                                            @else
                                                <span class="badge bg-light text-muted border">Chưa xếp HĐ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hs->ketQuaSinhViens && $hs->ketQuaSinhViens->isNotEmpty())
                                                @php $diemTB = $hs->ketQuaSinhViens->avg('DiemTongKet'); @endphp
                                                <span class="fw-bold text-success">{{ number_format($diemTB, 2) }} / 10</span>
                                            @else
                                                <span class="text-muted small fst-italic">Chưa có điểm</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($hoSoBaoVes->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $hoSoBaoVes->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- ═════════════════════════════════════════════════════════════════════ -->
<!-- MODAL GỬI CHỈ ĐẠO / YÊU CẦU NHẮC NHỞ TIẾN ĐỘ CHO TRƯỞNG KHOA -->
<!-- ═════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="remindModalTK" tabindex="-1" aria-labelledby="remindModalTKLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="remindFormTK" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #002855; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="remindModalTKLabel">
                        <i class="fa-solid fa-bullhorn text-warning"></i> Chỉ Đạo &amp; Đôn Đốc Tiến Độ – Cấp Trưởng Khoa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Thông tin nhóm & đề tài tự động điền --}}
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <span class="text-muted small d-block">Nhóm sinh viên:</span>
                                <strong class="text-dark" id="modalTkNhomText">---</strong>
                            </div>
                            <div class="col-md-5">
                                <span class="text-muted small d-block">Đề tài:</span>
                                <strong class="text-dark" id="modalTkDeTaiText">---</strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Bộ môn &amp; GVHD:</span>
                                <strong class="text-dark" id="modalTkBmText">---</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Chọn đối tượng nhận cho Trưởng khoa --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            Đối tượng tiếp nhận chỉ đạo đôn đốc: <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="tkTbm" value="tbm" checked>
                                <label class="form-check-label fw-semibold text-dark" for="tkTbm">
                                    <i class="fa-solid fa-building-columns text-primary me-2"></i> Trưởng bộ môn phụ trách (<span id="modalTkTbmLabel">Trưởng bộ môn</span>)
                                    <span class="text-muted small d-block ms-4">Yêu cầu Ban Chủ nhiệm Bộ môn kiểm tra, rà soát và đôn đốc tiến độ của nhóm</span>
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="tkGvhd" value="gvhd">
                                <label class="form-check-label fw-semibold text-dark" for="tkGvhd">
                                    <i class="fa-solid fa-chalkboard-user text-info me-2"></i> Giảng viên hướng dẫn (<span id="modalTkGvhdLabel">GVHD</span>)
                                    <span class="text-muted small d-block ms-4">Trực tiếp yêu cầu GVHD cập nhật và thẩm định tiến độ thực hiện của nhóm</span>
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="tkSv" value="sinh_vien">
                                <label class="form-check-label fw-semibold text-dark" for="tkSv">
                                    <i class="fa-solid fa-users text-success me-2"></i> Nhóm sinh viên thực hiện
                                    <span class="text-muted small d-block ms-4">Yêu cầu nhóm sinh viên khẩn trương nộp báo cáo và giải trình tiến độ</span>
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="tkAll" value="tat_ca">
                                <label class="form-check-label fw-semibold text-dark" for="tkAll">
                                    <i class="fa-solid fa-paper-plane text-warning me-2"></i> Tất cả các bên (Trưởng bộ môn + GVHD + Nhóm sinh viên)
                                    <span class="text-muted small d-block ms-4">Đồng gửi chỉ đạo để toàn bộ các mắt xích phối hợp xử lý dứt điểm</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Giai đoạn cần đôn đốc:</label>
                            <select name="giai_doan" id="modalTkStageSelect" class="form-select">
                                @foreach($stageDefinitions as $stNum => $stMeta)
                                    <option value="{{ $stNum }}">GĐ {{ $stNum }}: {{ $stMeta['ten'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Thời hạn xử lý / Báo cáo lại Khoa:</label>
                            <input type="date" name="han_xu_ly" class="form-control" value="{{ \Carbon\Carbon::now()->addDays(3)->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Lý do chỉ đạo đôn đốc: <span class="text-danger">*</span></label>
                        <select id="modalTkReasonPreset" class="form-select mb-2" onchange="applyTkReasonPreset(this.value)">
                            <option value="">-- Chọn lý do mẫu hoặc tự nhập --</option>
                            <option value="Nhóm chậm tiến độ nộp báo cáo giai đoạn theo kế hoạch chung toàn Khoa">Nhóm chậm tiến độ nộp báo cáo giai đoạn theo kế hoạch chung toàn Khoa</option>
                            <option value="Yêu cầu Trưởng bộ môn kiểm tra, rà soát và đôn đốc tiến độ thực hiện đề tài">Yêu cầu Trưởng bộ môn kiểm tra, rà soát và đôn đốc tiến độ thực hiện đề tài</option>
                            <option value="Hồ sơ nghiệm thu giai đoạn của nhóm đang bị chậm trễ, cần xử lý dứt điểm">Hồ sơ nghiệm thu giai đoạn của nhóm đang bị chậm trễ, cần xử lý dứt điểm</option>
                            <option value="Yêu cầu GVHD và nhóm sinh viên báo cáo giải trình tình hình thực hiện">Yêu cầu GVHD và nhóm sinh viên báo cáo giải trình tình hình thực hiện</option>
                        </select>
                        <input type="text" name="ly_do" id="modalTkReasonInput" class="form-control" placeholder="Nhập lý do chỉ đạo..." required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Ý kiến chỉ đạo cụ thể của Trưởng khoa: <span class="text-danger">*</span></label>
                        <textarea name="noi_dung" id="modalTkContentInput" rows="4" class="form-control" placeholder="Ghi rõ ý kiến chỉ đạo, yêu cầu hoàn thành và thời hạn phản hồi..." required></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 small border-0 mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Thông báo sẽ được chuyển trực tiếp tới hệ thống thông báo nội bộ của các đối tượng đã chọn và lưu vào sổ lưu vết <strong>Lịch sử tiến độ</strong> của nhóm.
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light border px-4 rounded-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i> Xác Nhận Gửi Chỉ Đạo
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
        const remindModal = new bootstrap.Modal(document.getElementById('remindModalTK'));
        const form = document.getElementById('remindFormTK');

        document.querySelectorAll('.btn-open-remind-tk').forEach(btn => {
            btn.addEventListener('click', function () {
                const maNhom = this.getAttribute('data-manhom');
                const tenNhom = this.getAttribute('data-tennhom');
                const tenDeTai = this.getAttribute('data-tendetai');
                const gvhd = this.getAttribute('data-gvhd');
                const bomon = this.getAttribute('data-bomon');
                const tbm = this.getAttribute('data-tbm');
                const stage = this.getAttribute('data-stage');

                document.getElementById('modalTkNhomText').textContent = `${tenNhom} (${maNhom})`;
                document.getElementById('modalTkDeTaiText').textContent = tenDeTai;
                document.getElementById('modalTkBmText').textContent = `${bomon} - GVHD: ${gvhd}`;
                document.getElementById('modalTkTbmLabel').textContent = tbm ? `${tbm} (${bomon})` : bomon;
                document.getElementById('modalTkGvhdLabel').textContent = gvhd;

                const stageSelect = document.getElementById('modalTkStageSelect');
                if (stageSelect && stage) stageSelect.value = stage;

                // Pre-fill reason and directive
                const defaultPreset = "Nhóm chậm tiến độ nộp báo cáo giai đoạn theo kế hoạch chung toàn Khoa";
                document.getElementById('modalTkReasonPreset').value = defaultPreset;
                applyTkReasonPreset(defaultPreset, tenNhom, bomon);

                // Form action
                form.action = "{{ url('truongkhoa/theo-doi') }}/" + maNhom + "/remind";

                remindModal.show();
            });
        });
    });

    function applyTkReasonPreset(preset, tenNhom, bomon) {
        if (!preset) return;
        document.getElementById('modalTkReasonInput').value = preset;
        const currentNhom = tenNhom || document.getElementById('modalTkNhomText').textContent;
        const currentBm = bomon || document.getElementById('modalTkBmText').textContent;
        const msg = `Ý kiến chỉ đạo từ Ban Chủ nhiệm Khoa:\n` +
                    `- Đề nghị Trưởng bộ môn ${currentBm} và GVHD trực tiếp rà soát tình hình thực hiện đề tài của nhóm ${currentNhom}.\n` +
                    `- Vấn đề ghi nhận: "${preset}".\n` +
                    `- Đề nghị các bên khẩn trương đôn đốc sinh viên hoàn thành, cập nhật báo cáo tiến độ và báo cáo lại Ban Chủ nhiệm Khoa trước thời hạn quy định.`;
        document.getElementById('modalTkContentInput').value = msg;
    }
</script>
@endpush
