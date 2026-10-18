@extends('layouts.sinhvien')

@push('styles')
<style>
    /* ── Progress Plan Modern Styles ── */
    .stat-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .stat-kpi-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 51, 102, 0.05);
        transform: translateY(-1px);
    }
    .stat-kpi-card.kpi-primary { border-left: 4px solid #0072CE; }
    .stat-kpi-card.kpi-info { border-left: 4px solid #0284c7; }
    .stat-kpi-card.kpi-warning { border-left: 4px solid #f59e0b; }
    .stat-kpi-card.kpi-success { border-left: 4px solid #10b981; }
    .stat-kpi-card.kpi-danger { border-left: 4px solid #ef4444; }

    /* Stepper */
    .plan-stepper-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0, 51, 102, 0.03);
    }
    .plan-stepper-scroll {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
        padding-bottom: 6px;
    }
    .plan-stepper-scroll::-webkit-scrollbar {
        height: 5px;
    }
    .plan-stepper-scroll::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 4px;
    }
    .plan-stepper-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .plan-step-item {
        min-width: 135px;
        flex: 1 1 0;
        cursor: pointer;
        padding: 8px 6px;
        border-radius: 10px;
        transition: all 0.2s ease;
        position: relative;
    }
    .plan-step-item:hover {
        background-color: #f8fafc;
    }
    .plan-step-item.active {
        background-color: #f0f7ff;
    }
    .plan-step-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        margin: 0 auto 6px auto;
        transition: all 0.2s ease;
    }

    /* Accordion Custom */
    .stage-accordion-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px !important;
        background: #ffffff;
        margin-bottom: 12px;
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .stage-accordion-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(0, 51, 102, 0.05);
    }
    .stage-accordion-item.is-current-stage {
        border-left: 4px solid #0072CE !important;
    }
    .stage-accordion-header {
        background: #ffffff;
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
        border: none;
        width: 100%;
        text-align: left;
    }
    .stage-accordion-header:hover {
        background-color: #f8fafc;
    }
    .stage-chevron-icon {
        transition: transform 0.25s ease-in-out;
    }
    .stage-accordion-header[aria-expanded="true"] .stage-chevron-icon {
        transform: rotate(180deg);
    }

    /* Sub-report rows */
    .dot-nop-row {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 10px;
        padding: 14px 16px;
        transition: all 0.15s ease;
    }
    .dot-nop-row:hover {
        border-color: #94a3b8;
        background-color: #fbfcfe;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
</style>
@endpush

@section('content')
@if(isset($error) && $error)
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Tiêu đề trang -->
<div class="mb-4">
    <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.5rem;">
        <i class="fa-solid fa-timeline me-2 text-primary"></i>Báo Cáo Tiến Độ Khóa Luận
    </h4>
</div>

{{-- ── BỘ CHUYỂN ĐỔI NHÓM / HỌC PHẦN (CHO SINH VIÊN THAM GIA NHIỀU ĐỀ TÀI) ── --}}
@if(isset($allStudentMemberships) && $allStudentMemberships->count() > 1)
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white" style="width: 44px; height: 44px; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                            Bạn đang tham gia {{ $allStudentMemberships->count() }} đề tài / học phần trong học kỳ
                        </h6>
                        <span class="text-secondary small">
                            Chọn đề tài cần nộp báo cáo hoặc cập nhật tiến độ. Lịch sử và hồ sơ lưu trữ tách biệt hoàn toàn.
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @foreach($allStudentMemberships as $mb)
                        @php
                            $mNhom = $mb->nhom;
                            $mHp = $mNhom->hocPhan;
                            $mDt = $mNhom->dangKyDeTai?->deTai;
                            $isActive = ($nhom && $nhom->MaNhom === $mNhom->MaNhom);
                        @endphp
                        <a href="{{ route('sinhvien.baocao.index', ['nhom_id' => $mNhom->MaNhom]) }}" 
                           class="btn btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 transition-all {{ $isActive ? 'btn-primary shadow-sm' : 'btn-outline-primary bg-white' }}">
                            @if($isActive)
                                <i class="fa-solid fa-circle-check"></i>
                            @else
                                <i class="fa-regular fa-circle"></i>
                            @endif
                            <span>{{ $mHp->TenHocPhan ?? $mNhom->TenNhom }}</span>
                            @if($mDt)
                                <span class="badge {{ $isActive ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} rounded-pill font-monospace" style="font-size: 10px;">
                                    {{ $mDt->MaDeTai }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

@if(isset($nhom) && $nhom && $nhom->deTai)
<!-- Tóm tắt Đề tài & Giảng viên hướng dẫn -->
<div class="card mb-4 bg-light border-0 shadow-xs">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="fw-bold text-dark fs-6 mb-1">
                    <i class="fa-solid fa-book-bookmark text-primary me-2"></i>{{ $nhom->TenNhom }} — {{ $nhom->deTai->TenDeTai }}
                </div>
                <div class="text-muted d-flex flex-wrap gap-3" style="font-size: 13px;">
                    <span><i class="fa-solid fa-chalkboard-user me-2 text-secondary"></i>GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span>
                    @if($nhom->deTai->giangVien?->Email)
                        <span><i class="fa-regular fa-envelope me-2 text-secondary"></i>{{ $nhom->deTai->giangVien->Email }}</span>
                    @endif
                </div>
            </div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-2">
                <i class="fa-solid fa-circle-check me-2"></i>Đề tài đã phê duyệt
            </span>
        </div>
    </div>
</div>

{{-- ── THANH TIẾN ĐỘ 6 GIAI ĐOẠN KHÓA LUẬN (PERCENTAGE & PROGRESS BARS) ── --}}
@if(!empty($stageBreakdown))
    <div class="mb-4">
        @include('partials.stage_progress_bars', ['stages' => $stageBreakdown, 'showOverall' => true])
    </div>
@endif

@php
    $hasGvStages = isset($giaiDoans) && $giaiDoans->isNotEmpty();
    $allCompleted = ($baoCaos->count() >= 6 && isset($baoCaos[6]) && $baoCaos[6]->TrangThai === 'Đạt');
    $bcHienTai = $baoCaos[$mocHienTai] ?? null;

    if ($hasGvStages) {
        $tongGiaiDoan = $giaiDoans->count();
        $allDotNops = $giaiDoans->flatMap->dotNops;
        $tongDotBaoCao = $allDotNops->count();
        $soDotDaNop = $allDotNops->filter(fn($d) => $d->khoiBaoCaos->isNotEmpty())->count();
        $soDotChuaNop = $allDotNops->filter(function($d) {
            return $d->khoiBaoCaos->isEmpty() && (!$d->HanNop || now()->lte($d->HanNop));
        })->count();
        $soDotQuaHan = $allDotNops->filter(function($d) {
            return $d->khoiBaoCaos->isEmpty() && $d->HanNop && now()->gt($d->HanNop);
        })->count();

        // Tìm giai đoạn đang thực hiện
        $currentStageItem = $giaiDoans->firstWhere('TrangThai', 'DANG_THUC_HIEN');
        $currentStageNumber = $currentStageItem ? $currentStageItem->ThuTu : ($giaiDoans->first()->ThuTu ?? 1);
    }
@endphp

@if($hasGvStages)
{{-- ── 1. KHU VỰC TỔNG QUAN KẾ HOẠCH & 5 THẺ THỐNG KÊ KPI ── --}}
<div class="card mb-4 border-0 shadow-sm rounded-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom">
        <div>
            <h5 class="fw-bold mb-1 text-dark" style="color: #002244 !important;">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Kế hoạch theo dõi tiến độ
            </h5>
            <p class="text-muted small mb-0">
                Quản lý các giai đoạn thực hiện và đợt nộp báo cáo của đề tài do giảng viên hướng dẫn thiết lập.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group btn-group-sm shadow-xs" role="group">
                <button type="button" class="btn btn-outline-secondary px-3 py-1.5 fw-medium d-inline-flex align-items-center" onclick="toggleAllStages(true)">
                    <i class="fa-solid fa-angles-down me-2 text-primary"></i>Mở rộng tất cả
                </button>
                <button type="button" class="btn btn-outline-secondary px-3 py-1.5 fw-medium d-inline-flex align-items-center" onclick="toggleAllStages(false)">
                    <i class="fa-solid fa-angles-up me-2 text-secondary"></i>Thu gọn tất cả
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        {{-- 5 Thẻ thống kê nhỏ dạng KPI thực tế --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="stat-kpi-card kpi-primary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-medium">Tổng số giai đoạn</span>
                        <i class="fa-solid fa-diagram-project text-primary opacity-50"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h3 class="fw-bold text-dark mb-0">{{ $tongGiaiDoan }}</h3>
                        <span class="text-muted small">giai đoạn</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="stat-kpi-card kpi-info">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-medium">Tổng số đợt báo cáo</span>
                        <i class="fa-solid fa-layer-group text-info opacity-50"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h3 class="fw-bold text-dark mb-0">{{ $tongDotBaoCao }}</h3>
                        <span class="text-muted small">đợt</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="stat-kpi-card kpi-warning">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-medium">Số đợt chưa nộp</span>
                        <i class="fa-solid fa-hourglass-half text-warning opacity-50"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h3 class="fw-bold text-warning mb-0">{{ $soDotChuaNop }}</h3>
                        <span class="text-muted small">đợt</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="stat-kpi-card kpi-success">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-medium">Số đợt đã nộp</span>
                        <i class="fa-solid fa-circle-check text-success opacity-50"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h3 class="fw-bold text-success mb-0">{{ $soDotDaNop }}</h3>
                        <span class="text-muted small">đợt</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 col-xl">
                <div class="stat-kpi-card kpi-danger">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-medium">Số đợt quá hạn</span>
                        <i class="fa-solid fa-triangle-exclamation text-danger opacity-50"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <h3 class="fw-bold {{ $soDotQuaHan > 0 ? 'text-danger' : 'text-muted' }} mb-0">{{ $soDotQuaHan }}</h3>
                        <span class="text-muted small">đợt</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. THANH TIẾN TRÌNH TỔNG QUAN 6 GIAI ĐOẠN (HORIZONTAL STEPPER) ── --}}
        <div class="plan-stepper-box mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="small fw-bold text-dark text-uppercase d-flex align-items-center gap-2" style="font-size: 11.5px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-timeline text-primary"></i>
                    <span>Tiến trình 6 giai đoạn kế hoạch thực hiện:</span>
                </span>
                <span class="text-muted small" style="font-size: 11.5px;">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i>Bấm vào giai đoạn để xem chi tiết
                </span>
            </div>

            <div class="plan-stepper-scroll">
                <div class="d-flex justify-content-between flex-nowrap gap-2" style="min-width: 720px;">
                    @foreach($giaiDoans as $gd)
                        @php
                            $isDone = ($gd->TrangThai === 'HOAN_THANH');
                            $isCurrent = ($gd->TrangThai === 'DANG_THUC_HIEN');
                            $isOverdue = (!$isDone && $gd->NgayKetThuc && now()->gt($gd->NgayKetThuc));
                            $shortName = preg_replace('/^Giai\s+đoạn\s+\d+[\s:.-]*/ui', '', $gd->TenGiaiDoan);
                        @endphp
                        <div class="plan-step-item text-center {{ $isCurrent ? 'active' : '' }}" 
                             onclick="scrollToStage({{ $gd->ThuTu }})" 
                             title="Bấm để cuộn và mở Giai đoạn {{ $gd->ThuTu }}">
                            <div class="plan-step-circle {{ $isDone ? 'bg-success text-white' : ($isOverdue ? 'bg-danger text-white' : ($isCurrent ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border')) }}">
                                @if($isDone)
                                    <i class="fa-solid fa-check"></i>
                                @elseif($isOverdue)
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                @elseif($isCurrent)
                                    <span class="fw-bold">{{ $gd->ThuTu }}</span>
                                @else
                                    <span class="text-muted">{{ $gd->ThuTu }}</span>
                                @endif
                            </div>
                            <div class="fw-bold text-truncate px-1 {{ $isCurrent ? 'text-primary' : ($isDone ? 'text-success' : 'text-dark') }}" style="font-size: 12px;" title="{{ $shortName }}">
                                {{ $shortName }}
                            </div>
                            <div class="mt-1">
                                @if($isDone)
                                    <span class="badge bg-success rounded-pill" style="font-size: 9.5px;">Đã hoàn thành</span>
                                @elseif($isOverdue)
                                    <span class="badge bg-danger rounded-pill" style="font-size: 9.5px;">Quá hạn</span>
                                @elseif($isCurrent)
                                    <span class="badge bg-primary text-white rounded-pill" style="font-size: 9.5px;">Đang thực hiện</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill" style="font-size: 9.5px;">Chưa bắt đầu</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── 3. DANH SÁCH 6 GIAI ĐOẠN DẠNG ACCORDION THU GỌN / MỞ RỘNG ── --}}
        <div class="accordion stage-accordion-group d-flex flex-column gap-3" id="stageAccordion">
            @foreach($giaiDoans as $gd)
                @php
                    $isCurrent = ($gd->TrangThai === 'DANG_THUC_HIEN');
                    $isDone = ($gd->TrangThai === 'HOAN_THANH');
                    $isOverdue = (!$isDone && $gd->NgayKetThuc && now()->gt($gd->NgayKetThuc));
                    $stageDots = $gd->dotNops;
                    $countDots = $stageDots->count();
                    $countSubmitted = $stageDots->filter(fn($d) => $d->khoiBaoCaos->isNotEmpty())->count();
                    $countUnsubmitted = $stageDots->filter(fn($d) => $d->khoiBaoCaos->isEmpty())->count();
                    $cleanTitle = preg_replace('/^Giai\s+đoạn\s+\d+[\s:.-]*/ui', '', $gd->TenGiaiDoan);
                @endphp

                <div class="stage-accordion-item {{ $isCurrent ? 'is-current-stage' : '' }}" id="stageItem{{ $gd->ThuTu }}">
                    {{-- 3.2. Tiêu đề giai đoạn (Accordion Header) --}}
                    <div class="stage-accordion-header p-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2 {{ $isCurrent ? '' : 'collapsed' }}"
                         data-bs-toggle="collapse"
                         data-bs-target="#collapseStage{{ $gd->ThuTu }}"
                         aria-expanded="{{ $isCurrent ? 'true' : 'false' }}"
                         aria-controls="collapseStage{{ $gd->ThuTu }}">
                         
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge {{ $isCurrent ? 'bg-primary' : ($isDone ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-secondary')) }} text-white rounded-pill px-3 py-1 fw-bold" style="font-size: 11.5px;">
                                Giai đoạn {{ $gd->ThuTu }}
                            </span>
                            <span class="fw-bold text-dark fs-6" style="color: #003366 !important;">
                                {{ $cleanTitle }}
                            </span>

                            @if($isDone)
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                    <i class="fa-solid fa-circle-check me-1"></i>Đã hoàn thành
                                </span>
                            @elseif($isOverdue)
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Quá hạn
                                </span>
                            @elseif($isCurrent)
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                    <i class="fa-solid fa-spinner fa-spin me-1"></i>Đang thực hiện
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                    <i class="fa-regular fa-circle me-1"></i>Chưa bắt đầu
                                </span>
                            @endif

                            @if($gd->NgayBatDau || $gd->NgayKetThuc)
                                <span class="text-muted small ms-2 d-none d-md-inline" style="font-size: 12px;">
                                    <i class="fa-regular fa-calendar me-1"></i>
                                    {{ $gd->NgayBatDau ? \Carbon\Carbon::parse($gd->NgayBatDau)->format('d/m/Y') : '---' }} &ndash; {{ $gd->NgayKetThuc ? \Carbon\Carbon::parse($gd->NgayKetThuc)->format('d/m/Y') : '---' }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5" style="font-size: 11px;">
                                <i class="fa-solid fa-list-ul me-1 text-primary"></i>{{ $countDots }} đợt báo cáo
                            </span>
                            @if($countDots > 0)
                                @if($countUnsubmitted > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1.5" style="font-size: 11px;">
                                        Chưa nộp: {{ $countUnsubmitted }}
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5" style="font-size: 11px;">
                                        Đã nộp: {{ $countSubmitted }}/{{ $countDots }}
                                    </span>
                                @endif
                            @endif
                            <i class="fa-solid fa-chevron-down stage-chevron-icon text-muted ms-1"></i>
                        </div>
                    </div>

                    {{-- 3.3. Phần nội dung khi mở rộng (Accordion Body) --}}
                    <div id="collapseStage{{ $gd->ThuTu }}" 
                         class="collapse stage-accordion-collapse {{ $isCurrent ? 'show' : '' }}" 
                         data-bs-parent="#stageAccordion">
                        <div class="p-3 px-4 bg-white border-top">
                            {{-- Thông tin mô tả và đầu ra của giai đoạn --}}
                            @if($gd->MoTa || $gd->NoiDungCongViec || $gd->KetQuaDauRa)
                                <div class="mb-3 p-3 rounded-3 bg-light border small text-secondary">
                                    @if($gd->MoTa)
                                        <div class="text-dark fw-medium mb-1">{{ $gd->MoTa }}</div>
                                    @endif
                                    @if($gd->NoiDungCongViec)
                                        <div><strong class="text-dark">Nội dung công việc:</strong> {{ $gd->NoiDungCongViec }}</div>
                                    @endif
                                    @if($gd->KetQuaDauRa)
                                        <div class="mt-1"><strong class="text-dark">Kết quả đầu ra:</strong> {{ $gd->KetQuaDauRa }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- 4. Danh sách các đợt nộp báo cáo bên trong giai đoạn (Dạng thẻ gọn) --}}
                            @if($stageDots->isEmpty())
                                <div class="p-3 bg-light rounded-3 text-center text-muted small fst-italic">
                                    Giảng viên chưa tạo đợt nộp báo cáo trong giai đoạn này.
                                </div>
                            @else
                                <div class="d-flex flex-column gap-2">
                                    @foreach($stageDots as $dot)
                                        @php
                                            $tt = $dot->getTrangThaiHienThi();
                                            $latest = $dot->phienBanMoiNhat;
                                            $countVersions = $dot->khoiBaoCaos->count();
                                            $isDotOverdue = ($dot->HanNop && now()->gt($dot->HanNop) && $countVersions === 0);
                                        @endphp
                                        <div class="dot-nop-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="flex-grow-1" style="min-width: 260px;">
                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                    <span class="fw-bold text-dark" style="font-size: 13.5px; color: #003366 !important;">
                                                        {{ $dot->TenDotNop }}
                                                    </span>
                                                    <span class="badge {{ $tt['badge'] }} rounded-pill" style="font-size: 11px;">
                                                        {{ $tt['label'] }}
                                                    </span>
                                                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 11px;">
                                                        {{ $countVersions > 0 ? $countVersions . ' phiên bản đã nộp' : 'Chưa nộp phiên bản nào' }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                    <span>
                                                        <i class="fa-regular fa-clock me-1 text-danger"></i>Hạn nộp: 
                                                        <strong class="{{ $isDotOverdue ? 'text-danger' : 'text-dark' }}">
                                                            {{ $dot->HanNop ? \Carbon\Carbon::parse($dot->HanNop)->format('H:i · d/m/Y') : 'Không giới hạn' }}
                                                        </strong>
                                                    </span>
                                                    @if($dot->DinhDangChoPhep)
                                                        <span><i class="fa-regular fa-file-lines me-1 text-primary"></i>Định dạng: <strong>{{ strtoupper($dot->DinhDangChoPhep) }}</strong></span>
                                                    @endif
                                                    @if($dot->YeuCauSourceCode)
                                                        <span class="text-success"><i class="fa-brands fa-github me-1"></i>Yêu cầu kèm Git Code</span>
                                                    @endif
                                                </div>
                                                @if($latest && $latest->NhanXetGV)
                                                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 p-2 px-3 mt-2 mb-0 small text-primary" style="font-size: 12px;">
                                                        <i class="fa-solid fa-comment-dots me-2"></i><strong>Nhận xét GVHD:</strong> {{ $latest->NhanXetGV }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('sinhvien.baocao.dot_nop.show', $dot->MaDotNop) }}" 
                                                   class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-xs d-inline-flex align-items-center" style="font-size: 12.5px;">
                                                    <i class="fa-solid fa-file-arrow-up me-2"></i>{{ $countVersions > 0 ? 'Xem & Cập Nhật (v' . ($countVersions + 1) . ')' : 'Xem & Nộp Bài' }}
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else

<!-- Stepper Lộ Trình Mặc Định (Chỉ hiển thị khi GVHD chưa thiết lập các giai đoạn riêng) -->
<div class="card mb-4">
    <div class="card-body p-4">
        <div class="row g-2 text-center position-relative">
            <!-- Thanh nối ngang giữa các mốc -->
            <div class="position-absolute top-50 start-0 translate-middle-y w-100 d-none d-md-block" style="height: 3px; background: #e2e8f0; z-index: 1; margin-top: -16px;">
                @php
                    $completedCount = 0;
                    for ($i = 1; $i <= 6; $i++) {
                        if (isset($baoCaos[$i]) && $baoCaos[$i]->TrangThai === 'Đạt') {
                            $completedCount++;
                        }
                    }
                    $progressPercent = ($completedCount / 6) * 100;
                @endphp
                <div style="height: 100%; width: {{ $progressPercent }}%; background: #002244; transition: width 0.4s ease;"></div>
            </div>

            @foreach($mocs as $soMoc => $moc)
            @php
                $bc = $baoCaos[$soMoc] ?? null;
                $isDone = ($bc && $bc->TrangThai === 'Đạt');
                $isCurrent = ($soMoc === $mocHienTai && !$allCompleted);
                $isPending = ($bc && $bc->TrangThai === 'Chờ duyệt');
                $isResubmit = ($bc && $bc->TrangThai === 'Yêu cầu nộp lại');
            @endphp
            <div class="col position-relative" style="z-index: 2;">
                @if($isDone)
                    <div class="mx-auto rounded-circle text-white mb-2 shadow-xs"
                         style="width: 40px; height: 40px; background-color: #0072CE; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-check fw-bold fs-6"></i>
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $moc['ten'] }}</div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 11.5px;">
                        Đạt
                    </span>
                @elseif($isCurrent)
                    <div class="mx-auto rounded-circle text-white mb-2 shadow-sm"
                         style="width: 42px; height: 42px; background-color: {{ $isPending ? '#f59e0b' : ($isResubmit ? '#0072CE' : '#002244') }}; margin-top: -1px; display: flex; align-items: center; justify-content: center;">
                        @if($isPending)
                            <i class="fa-solid fa-hourglass-half fs-6 me-2"></i>
                        @elseif($isResubmit)
                            <i class="fa-solid fa-rotate-left fs-6 me-2"></i>
                        @else
                            <span class="fw-bold fs-6">{{ $soMoc }}</span>
                        @endif
                    </div>
                    <div class="fw-bold text-primary" style="font-size: 13px;">{{ $moc['ten'] }}</div>
                    @if($isPending)
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 11.5px;">
                            Chờ duyệt
                        </span>
                    @elseif($isResubmit)
                        <span class="badge bg-info text-dark rounded-pill" style="font-size: 11.5px;">
                            Cần nộp lại
                        </span>
                    @elseif(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                        <span class="badge bg-secondary text-white rounded-pill" style="font-size: 11.5px;">
                            Chưa mở
                        </span>
                    @else
                        <span class="badge bg-primary text-white rounded-pill" style="font-size: 11.5px;">
                            Đang mở
                        </span>
                    @endif
                @else
                    <div class="mx-auto rounded-circle text-secondary border mb-2 bg-white"
                         style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <span class="fw-bold text-muted" style="font-size: 13px;">{{ $soMoc }}</span>
                    </div>
                    <div class="fw-semibold text-muted" style="font-size: 13px;">{{ $moc['ten'] }}</div>
                    <span class="badge bg-light text-muted border rounded-pill" style="font-size: 11.5px;">
                        Chưa mở
                    </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@if($allCompleted)
<!-- Trạng thái hoàn thành tất cả các mốc -->
<div class="card mb-4 bg-primary bg-opacity-10 border-primary">
    <div class="card-body p-4 text-center">
        <i class="fa-solid fa-award fa-3x text-primary mb-2"></i>
        <h5 class="fw-bold text-primary mb-1">Chúc mừng nhóm của bạn!</h5>
        <p class="text-muted mb-3" style="font-size: 13.5px;">
            Nhóm đã hoàn thành xuất sắc toàn bộ các mốc báo cáo tiến độ và đủ điều kiện nghiệm thu.
        </p>
        <a href="{{ route('sinhvien.ketqua.index') }}" class="btn btn-success rounded-pill px-4 fw-bold d-inline-flex align-items-center">
            <i class="fa-solid fa-graduation-cap me-2"></i>Xem Kết Quả Khóa Luận
        </a>
    </div>
</div>
@else

<!-- Khung Nộp Báo Cáo Giai Đoạn Hiện Tại -->
<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-pen-ruler text-primary"></i>
            <h6 class="fw-bold mb-0 text-dark">{{ $mocs[$mocHienTai]['ten'] }}</h6>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                <span class="badge bg-secondary text-white rounded-pill px-3 py-1">
                    <i class="fa-solid fa-clock me-2"></i>Chưa mở nộp {{ !empty($timeCheckHienTai['start_date']) ? '(Từ ' . \Carbon\Carbon::parse($timeCheckHienTai['start_date'])->format('d/m/Y') . ')' : '' }}
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Chờ duyệt')
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                    <i class="fa-solid fa-hourglass-half me-2"></i>Đang chờ GVHD đánh giá (Có thể cập nhật)
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                <span class="badge bg-info text-dark rounded-pill px-3 py-1">
                    <i class="fa-solid fa-rotate-left me-2"></i>GVHD yêu cầu nộp lại
                </span>
            @else
                <span class="badge bg-primary rounded-pill px-3 py-1">
                    <i class="fa-solid fa-lock-open me-2"></i>Đang mở nộp bài
                </span>
            @endif

            @if(!empty($timeCheckHienTai['end_date']))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                    <i class="fa-regular fa-clock me-2"></i>Hạn chót: {{ \Carbon\Carbon::parse($timeCheckHienTai['end_date'])->format('d/m/Y') }}
                </span>
            @elseif(isset($mocDeadlines[$mocHienTai]))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                    <i class="fa-regular fa-clock me-2"></i>Hạn chót: {{ \Carbon\Carbon::parse($mocDeadlines[$mocHienTai])->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Hướng dẫn nộp ngắn gọn -->
        <div class="p-3 rounded-3 bg-light border mb-4" style="font-size: 13px;">
            <div class="fw-semibold text-dark mb-1">
                <i class="fa-solid fa-circle-info text-primary me-2"></i>Yêu cầu nộp bài:
            </div>
            <div class="text-secondary">
                {{ $mocs[$mocHienTai]['mo_ta'] }} 
                (Định dạng tệp: <strong>PDF</strong> tối đa 20MB @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git'])), kèm đường dẫn <strong>GitHub/GitLab</strong> @endif).
            </div>
        </div>

        @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
            <div class="alert alert-warning border mb-3" style="font-size: 13.5px;">
                <i class="fa-solid fa-calendar-xmark text-warning me-2"></i>{{ $timeCheckHienTai['message'] }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-layer-group text-primary me-2"></i>Các Báo Cáo Tiến Độ Nhỏ Trong Giai Đoạn {{ $mocHienTai }}
            </span>
            <a href="{{ route('sinhvien.baocao.create_khoi', $mocHienTai) }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-bold shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>+ Tạo Báo Cáo Nhỏ Mới (Tích hợp AI)
            </a>
        </div>

        @if($bcHienTai && $bcHienTai->khoiBaoCaos && $bcHienTai->khoiBaoCaos->isNotEmpty())
            <!-- Timeline các lần nộp bài của mốc hiện tại -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark" style="font-size: 13.5px;">
                        <i class="fa-solid fa-timeline text-primary me-2"></i>Lịch Sử Các Báo Cáo Nhỏ — GĐ {{ $mocHienTai }}
                    </span>
                    <span class="badge bg-light text-primary border">
                        {{ $bcHienTai->khoiBaoCaos->count() }} báo cáo
                    </span>
                </div>
                <div class="p-3 bg-light rounded-3 border">
                    <div class="position-relative ps-3" style="border-left: 2px solid #cbd5e1; margin-left: 10px;">
                        @foreach($bcHienTai->khoiBaoCaos as $khoi)
                        <div class="position-relative mb-3 pb-1">
                            <div class="position-absolute rounded-circle {{ $khoi->TrangThai === 'Đạt' ? 'bg-primary' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning' : 'bg-primary') }}" 
                                 style="width: 14px; height: 14px; left: -24px; top: 6px; border: 2px solid #fff;"></div>
                            <div class="card border bg-white p-3 rounded-2 shadow-none" style="font-size: 13px;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white fw-semibold">Đợt nộp #{{ $khoi->LanNop }}</span>
                                        <span class="fw-bold text-dark">{{ $khoi->TieuDe }}</span>
                                        <span class="text-muted" style="font-size: 12px;">
                                            <i class="fa-regular fa-clock me-2"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($khoi->PhanTramHoanThanh)
                                            <span class="badge bg-light text-dark border">{{ $khoi->PhanTramHoanThanh }}%</span>
                                        @endif
                                        @if($khoi->TrangThai === 'Đạt')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">✅ Đạt</span>
                                        @elseif($khoi->TrangThai === 'Yêu cầu nộp lại')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">🔄 Yêu cầu chỉnh sửa</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">⏳ Đang chờ đánh giá</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($khoi->TenFile)
                                            <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" style="font-size: 12px;">
                                                <i class="fa-solid fa-file-pdf me-2"></i>{{ $khoi->TenFile }}
                                            </a>
                                        @endif
                                        @if($khoi->LinkCode)
                                            <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 12px;">
                                                <i class="fa-brands fa-github me-2"></i>Source Code
                                            </a>
                                        @endif
                                        @if($khoi->TomTatJson || $khoi->TomTatAI)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill py-1">
                                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>AI Trích Xuất
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('sinhvien.baocao.show_khoi', $khoi->MaKhoi) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 12px;">
                                            <i class="fa-solid fa-eye me-2"></i>Xem chi tiết
                                        </a>
                                        @if($khoi->TrangThai !== 'Đạt')
                                            <a href="{{ route('sinhvien.baocao.edit_khoi', $khoi->MaKhoi) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1" style="font-size: 12px;">
                                                <i class="fa-solid fa-pen me-2"></i>Sửa
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @if($khoi->KetQuaDatDuoc || $khoi->NoiDungBaoCao)
                                    <div class="text-secondary mb-1" style="font-size: 12.5px;">
                                        <strong>Kết quả:</strong> {{ Str::limit(strip_tags($khoi->KetQuaDatDuoc ?: $khoi->NoiDungBaoCao), 160) }}
                                    </div>
                                @endif
                                @if($khoi->NhanXetGV)
                                    <div class="mt-2 p-2 rounded bg-light border-start border-3 {{ $khoi->TrangThai === 'Đạt' ? 'border-primary' : 'border-warning' }}">
                                        <div class="fw-bold text-dark">
                                            <i class="fa-solid fa-comment-dots text-primary me-2"></i>Nhận xét từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }}:
                                        </div>
                                        <div class="text-dark mt-1">{!! \App\Helpers\HtmlHelper::clean($khoi->NhanXetGV) !!}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="LanBaoCao" value="{{ $mocHienTai }}">
            <input type="hidden" name="MaNhom" value="{{ $nhom->MaNhom }}">

            <div class="row g-3 mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Tiêu đề tiến độ <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="TieuDe" class="form-control" 
                           value="{{ $bcHienTai?->TieuDe ?? ('Báo cáo tiến độ ' . $mocs[$mocHienTai]['ten']) }}" required>
                </div>

                @if(in_array($mocs[$mocHienTai]['loai'], ['pdf', 'pdf_git']))
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Tệp báo cáo (PDF) 
                        @if(!$bcHienTai || !$bcHienTai->DuongDanFile) <span class="text-danger">*</span> @endif
                    </label>
                    <input type="file" name="FileBaoCao" class="form-control" accept=".pdf" {{ (!$bcHienTai || !$bcHienTai->DuongDanFile) ? 'required' : '' }}>
                    <div class="form-text text-muted" style="font-size: 12.5px;">
                        @if($bcHienTai && $bcHienTai->TenFile)
                            <span class="text-primary fw-semibold"><i class="fa-solid fa-file-pdf me-2"></i>Đã có: {{ $bcHienTai->TenFile }}</span> (Chọn file mới nếu muốn cập nhật)
                        @else
                            Định dạng: .pdf (Dung lượng tối đa 20MB)
                        @endif
                    </div>
                </div>
                @endif

                @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git']))
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Đường dẫn Source Code (GitHub / GitLab)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-brands fa-github"></i></span>
                        <input type="url" name="LinkCode" class="form-control" 
                               placeholder="https://github.com/username/repository" 
                               value="{{ $bcHienTai?->LinkCode ?? '' }}">
                    </div>
                    <div class="form-text text-muted" style="font-size: 12.5px;">Đường dẫn repository mã nguồn công khai</div>
                </div>
                @endif

                <div class="col-12">
                    @include('partials.rich_text_editor', [
                        'name' => 'NoiDungBaoCao',
                        'id' => 'NoiDungBaoCao',
                        'label' => 'Nội dung công việc & Ghi chú gửi Giảng viên hướng dẫn',
                        'value' => old('NoiDungBaoCao', $bcHienTai?->NoiDungBaoCao ?? ''),
                        'required' => true,
                        'minHeight' => '130px',
                        'placeholder' => 'Mô tả tóm tắt các công việc đã hoàn thành, khó khăn gặp phải (tối thiểu 20 ký tự)...'
                    ])
                    @error('NoiDungBaoCao')
                        <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold d-inline-flex align-items-center" disabled>
                        <i class="fa-solid fa-lock me-2"></i>Chưa Đến Hạn Nộp
                    </button>
                @else
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs d-inline-flex align-items-center">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>
                        @if($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                            Nộp Lại Báo Cáo (Lần nộp {{ ($bcHienTai->khoiBaoCaos->max('LanNop') ?? 1) + 1 }})
                        @elseif($bcHienTai)
                            Cập Nhật Báo Cáo Mốc {{ $mocHienTai }}
                        @else
                            Nộp Báo Cáo Tiến Độ Mốc {{ $mocHienTai }}
                        @endif
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endif

<!-- Bảng Lịch Sử Các Báo Cáo Đã Nộp -->
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary"></i>
        <h6 class="fw-bold mb-0 text-dark">Lịch Sử Các Mốc Báo Cáo Đã Nộp</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="18%">Mốc Nộp</th>
                    <th width="12%">Ngày Nộp</th>
                    <th width="24%">Tệp &amp; Source Code</th>
                    <th width="24%">Đánh Giá Của GVHD</th>
                    <th width="12%">Timeline</th>
                    <th width="10%" class="text-end pe-4">Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($baoCaos as $bc)
                <tr>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold border">
                            Mốc {{ $bc->LanBaoCao }}
                        </span>
                        <div class="fw-bold text-dark mt-1">{{ $bc->TieuDe }}</div>
                    </td>
                    <td class="text-muted" style="font-size: 13px;">
                        <i class="fa-regular fa-calendar me-2"></i>{{ $bc->NgayNop ? \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        @if($bc->TenFile)
                            <div class="d-inline-block me-1 mb-1">
                                <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-file-pdf me-2"></i>{{ Str::limit($bc->TenFile, 18) }}
                                </a>
                            </div>
                        @endif
                        @if($bc->LinkCode)
                            <div class="d-inline-block mb-1">
                                <a href="{{ $bc->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1">
                                    <i class="fa-brands fa-github me-2"></i>GitHub
                                </a>
                            </div>
                        @endif
                        @if(!$bc->TenFile && !$bc->LinkCode)
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($bc->NhanXet)
                            <div class="text-dark fst-italic" style="font-size: 13px; line-height: 1.4;">
                                {!! \App\Helpers\HtmlHelper::clean($bc->NhanXet) !!}
                            </div>
                        @else
                            <span class="text-muted fst-italic" style="font-size: 13px;">Đang chờ GVHD đánh giá...</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 d-inline-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#modalTimeline{{ $bc->MaBaoCao }}" title="Xem toàn bộ các khối nộp bài">
                                <i class="fa-solid fa-timeline me-2"></i>Timeline ({{ $bc->khoiBaoCaos->count() }})
                            </button>
                            @if($bc->tomTatBaoCao)
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center"
                                        data-bs-toggle="modal" data-bs-target="#modalAi{{ $bc->MaBaoCao }}" title="Xem trích yếu nội dung báo cáo">
                                    <i class="fa-solid fa-file-lines text-primary"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        @if($bc->TrangThai === 'Đạt')
                            <span class="badge bg-primary rounded-pill px-3 py-1">Đạt</span>
                        @elseif($bc->TrangThai === 'Chờ duyệt')
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Chờ duyệt</span>
                        @else
                            <span class="badge bg-info text-dark rounded-pill px-3 py-1">Nộp lại</span>
                        @endif
                    </td>
                </tr>

                <!-- Modal Timeline cho từng Mốc Báo Cáo -->
                <div class="modal fade" id="modalTimeline{{ $bc->MaBaoCao }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                            <div class="modal-header bg-primary text-white py-3 px-4">
                                <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-timeline me-2"></i>
                                    <span>Lịch Sử Các Lần Nộp — Mốc {{ $bc->LanBaoCao }}</span>
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 bg-light">
                                <div class="position-relative ps-3" style="border-left: 2px solid #cbd5e1; margin-left: 10px;">
                                    @forelse($bc->khoiBaoCaos as $khoi)
                                    <div class="position-relative mb-3 pb-1">
                                        <div class="position-absolute rounded-circle {{ $khoi->TrangThai === 'Đạt' ? 'bg-primary' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning' : 'bg-primary') }}" 
                                             style="width: 14px; height: 14px; left: -24px; top: 6px; border: 2px solid #fff;"></div>
                                        <div class="card border bg-white p-3 rounded-2 shadow-none" style="font-size: 13px;">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                <div>
                                                    <span class="badge bg-primary text-white fw-bold">Lần nộp #{{ $khoi->LanNop }}</span>
                                                    <span class="text-muted ms-2">
                                                        <i class="fa-regular fa-clock me-2"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                                    </span>
                                                </div>
                                                <div>
                                                    @if($khoi->TrangThai === 'Đạt')
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">✅ Đạt</span>
                                                    @elseif($khoi->TrangThai === 'Yêu cầu nộp lại')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold">🔄 Yêu cầu chỉnh sửa</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border fw-bold">⏳ Đang chờ đánh giá</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                @if($khoi->TenFile)
                                                    <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        <i class="fa-solid fa-file-pdf me-2"></i>{{ $khoi->TenFile }}
                                                    </a>
                                                @endif
                                                @if($khoi->LinkCode)
                                                    <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                        <i class="fa-brands fa-github me-2"></i>Source Code
                                                    </a>
                                                @endif
                                            </div>
                                            @if($khoi->NoiDungBaoCao)
                                                <div class="text-secondary mb-1">
                                                    <strong>Ghi chú:</strong> {{ $khoi->NoiDungBaoCao }}
                                                </div>
                                            @endif
                                            @if($khoi->NhanXetGV)
                                                <div class="mt-2 p-2 rounded bg-light border-start border-3 {{ $khoi->TrangThai === 'Đạt' ? 'border-primary' : 'border-warning' }}">
                                                    <div class="fw-bold text-dark">
                                                        <i class="fa-solid fa-comment-dots text-primary me-2"></i>Đánh giá từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }}:
                                                    </div>
                                                    <div class="text-dark mt-1">{!! \App\Helpers\HtmlHelper::clean($khoi->NhanXetGV) !!}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-muted" style="font-size: 13px;">Chưa có khối nộp bài nào.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="modal-footer py-2 bg-white">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Trích Yếu Báo Cáo cho sinh viên -->
                @if($bc->tomTatBaoCao)
                @php $tt = $bc->tomTatBaoCao; @endphp
                <div class="modal fade" id="modalAi{{ $bc->MaBaoCao }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                            <div class="modal-header text-white py-3 px-4" style="background-color: #002244;">
                                <h6 class="modal-title fw-bold">
                                    <i class="fa-solid fa-file-lines me-2"></i>Trích Yếu Báo Cáo Tiến Độ — Mốc {{ $bc->LanBaoCao }}
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted" style="font-size: 13px;">
                                        <i class="fa-regular fa-clock me-2"></i>Trích xuất lúc: {{ \Carbon\Carbon::parse($tt->NgayTomTat)->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                        Mức độ hoàn thiện: {{ number_format($tt->DoTinCayAI, 0) }}%
                                    </span>
                                </div>
                                <div class="row g-3" style="font-size: 14px;">
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border-start border-primary border-4" style="background: #f0fdf4; padding: 18px 20px;">
                                            <div class="fw-bold text-primary mb-2"><i class="fa-solid fa-circle-check me-2"></i>1. Nội dung đã thực hiện:</div>
                                            <div class="text-dark">{{ $tt->CongViecDaHoanThanh }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border-start border-warning border-4" style="background: #fffbeb; padding: 18px 20px;">
                                            <div class="fw-bold text-warning-emphasis mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>2. Khó khăn &amp; Vướng mắc:</div>
                                            <div class="text-dark">{{ $tt->KhoKhan }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border-start border-primary border-4" style="background: #eff6ff; padding: 18px 20px;">
                                            <div class="fw-bold text-primary mb-2"><i class="fa-solid fa-calendar-check me-2"></i>3. Kế hoạch giai đoạn tiếp theo:</div>
                                            <div class="text-dark">{{ $tt->KeHoachTuanToi }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 bg-light border-top">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-2 mb-2 text-muted opacity-50 d-block"></i>
                        <div>Nhóm của bạn chưa nộp bài báo cáo tiến độ nào.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-user-group fs-1 text-muted mb-3 d-block opacity-50"></i>
        <h5 class="fw-bold text-dark mb-2">Chưa Đủ Điều Kiện Báo Cáo Tiến Độ</h5>
        <p class="text-muted mb-4" style="font-size: 13.5px; max-width: 500px; margin: 0 auto;">
            Để nộp báo cáo tiến độ, bạn cần có Nhóm và Đề tài khóa luận được phê duyệt chính thức.
        </p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center">
            <i class="fa-solid fa-users me-2"></i>Quản lý Nhóm &amp; Đề tài
        </a>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function scrollToStage(thuTu) {
    const collapseEl = document.getElementById('collapseStage' + thuTu);
    if (collapseEl) {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        bsCollapse.show();
        const headerEl = document.getElementById('stageItem' + thuTu);
        if (headerEl) {
            headerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function toggleAllStages(expand) {
    document.querySelectorAll('.stage-accordion-collapse').forEach(el => {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
        if (expand) {
            bsCollapse.show();
        } else {
            bsCollapse.hide();
        }
    });
}
</script>
@endpush

