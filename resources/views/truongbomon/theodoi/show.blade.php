@extends('layouts.truongbomon')

@section('page_title', 'Chi Tiết Tiến Độ — Nhóm ' . ($nhom->TenNhom ?: $nhom->MaNhom))

@push('styles')
<style>
    :root {
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
        --border-color: #e2e8f0;
    }

    body {
        background-color: #f8fafc;
        color: #1e293b;
    }

    .card-detail {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .card-detail:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }

    .card-detail-header {
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 1.15rem 1.35rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .card-body-custom {
        padding: 1.35rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Vertical Stepper Timeline */
    .stepper-progress-track {
        position: relative;
        padding-left: 2.25rem;
    }
    .stepper-progress-track::before {
        content: "";
        position: absolute;
        top: 14px;
        bottom: 14px;
        left: 17px;
        width: 2.5px;
        background-color: #e2e8f0;
        border-radius: 2px;
    }
    .stepper-node-item {
        position: relative;
        margin-bottom: 1.25rem;
    }
    .stepper-node-item:last-child {
        margin-bottom: 0;
    }
    .stepper-node-circle {
        position: absolute;
        left: -2.25rem;
        top: 2px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.825rem;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        color: #64748b;
        z-index: 2;
        transition: all 0.3s ease;
    }
    .stepper-node-circle.status-completed { background: #10b981; border-color: #10b981; color: #ffffff; }
    .stepper-node-circle.status-current { background: #0284c7; border-color: #0284c7; color: #ffffff; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.2); }
    .stepper-node-circle.status-pending { background: #f59e0b; border-color: #f59e0b; color: #ffffff; }
    .stepper-node-circle.status-overdue { background: #ef4444; border-color: #ef4444; color: #ffffff; }

    /* Topic Description */
    .topic-description-container { position: relative; }
    .topic-description-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.15rem;
        font-size: 0.9rem;
        line-height: 1.65;
        color: #334155;
        word-break: break-word;
        transition: max-height 0.4s ease;
    }
    .topic-description-container.collapsed .topic-description-box { max-height: 180px; overflow: hidden; }
    .topic-description-container.collapsed::after {
        content: "";
        position: absolute; bottom: 0; left: 0; right: 0; height: 50px;
        background: linear-gradient(to bottom, rgba(248, 250, 252, 0), rgba(248, 250, 252, 1));
        pointer-events: none;
    }

    .hover-card-effect { transition: all 0.2s ease-in-out; }
    .hover-card-effect:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.03); }

    .gvhd-progress-scroll-container::-webkit-scrollbar { width: 5px; }
    .gvhd-progress-scroll-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .gvhd-progress-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>
@endpush

@php
    $deTai = $nhom->deTai;
    $gvhd = $deTai?->giangVien ?? $nhom->dangKyDeTai?->giangVienHuongDan;
    $tienDo = $nhom->tien_do_nghiep_vu;
    $dieuKien = $nhom->dieu_kien_giai_doan ?? [];
    $tenBoMon = $boMon->TenBoMon ?? ($gvhd?->boMon?->TenBoMon ?? 'Bộ môn');
@endphp

@section('content')
<div class="container-fluid px-0 py-3">

    {{-- ── 1. HEADER & BREADCRUMB ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('truongbomon.theodoi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none d-inline-flex align-items-center" style="font-size: 12px;">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
                </a>
                <span class="badge bg-primary text-white rounded-pill px-3 py-2 font-monospace" style="font-size: 12px;">
                    Mã nhóm: {{ $nhom->MaNhom }}
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 font-monospace" style="font-size: 12px;">
                    Mã ĐT: {{ $nhom->deTai->MaDeTai ?? 'Chưa gán' }}
                </span>
                <span class="badge rounded-pill px-3 py-2 font-monospace" style="font-size: 12px; background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;">
                    <i class="fa-solid fa-building-columns me-2"></i>{{ $tenBoMon }}
                </span>
            </div>
            <h4 class="fw-bold text-dark mb-1">
                {{ $deTai ? $deTai->TenDeTai : 'Nhóm ' . ($nhom->TenNhom ?: $nhom->MaNhom) }}
            </h4>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('truongbomon.theodoi.lichSu', $nhom->MaNhom) }}" 
               class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold shadow-none d-inline-flex align-items-center">
                <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử &amp; Nhật ký
            </a>
            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm d-inline-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#remindModalTBM">
                <i class="fa-solid fa-bell me-2"></i>Gửi Nhắc Nhở Tiến Độ
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ── TIẾN ĐỘ 6 GIAI ĐOẠN CHI TIẾT (PERCENTAGE & PROGRESS BARS) ── --}}
    @if(!empty($nhom->stage_breakdown))
        <div class="mb-4">
            @include('partials.stage_progress_bars', ['stages' => $nhom->stage_breakdown, 'showOverall' => true])
        </div>
    @endif

    {{-- ── 2. TWO COLUMN LAYOUT ── --}}
    <div class="row g-4">

        {{-- CỘT TRÁI (50%) --}}
        <div class="col-xl-6 d-flex flex-column gap-4">

            {{-- A. TÊN ĐỀ TÀI & MÔ TẢ --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-book-bookmark text-primary me-2"></i>A. Thông Tin Đề Tài Khóa Luận</span>
                    <span class="badge {{ $deTai?->TrangThai === 'Đã công bố' ? 'bg-success' : 'bg-primary' }} text-white rounded-pill px-3 py-1" style="font-size: 11.5px;">
                        {{ $deTai->TrangThai ?? 'Chưa công bố' }}
                    </span>
                </div>
                <div class="card-body-custom">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Tên đề tài chính thức:</div>
                        <h6 class="fw-bold text-dark lh-base mb-0">
                            {{ $deTai ? $deTai->TenDeTai : 'Nhóm chưa có đề tài chính thức được phê duyệt' }}
                        </h6>
                    </div>

                    <div class="topic-description-container mb-3" id="topicDescContainer">
                        <div class="text-muted small mb-1">Mục tiêu &amp; Mô tả chi tiết:</div>
                        <div class="topic-description-box" id="topicDescBox">
                            @if($deTai && $deTai->MoTa)
                                {!! nl2br(e($deTai->MoTa)) !!}
                            @else
                                <span class="text-muted fst-italic">Chưa có thông tin mô tả chi tiết đề tài.</span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6">
                            <span class="text-muted small d-block">Học kỳ:</span>
                            <strong class="text-dark small">{{ $nhom->hocKy->TenHocKy ?? $deTai->hocKy->TenHocKy ?? 'HK1 2026-2027' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Tiến độ tổng thể:</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $nhom->progress_percent ?? 0 }}%;"></div>
                                </div>
                                <span class="fw-bold text-dark font-monospace small">{{ $nhom->progress_percent ?? 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- B. NHÓM SINH VIÊN THỰC HIỆN --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-users text-primary me-2"></i>B. Sinh Viên Thực Hiện</span>
                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1" style="font-size: 11.5px;">
                        {{ $nhom->thanhViens->count() }} thành viên
                    </span>
                </div>
                <div class="card-body-custom">
                    <div class="d-flex flex-column gap-2">
                        @forelse($nhom->thanhViens as $tv)
                            @php
                                $sv = $tv->sinhVien;
                                $isLeader = ($nhom->truongNhom && $sv && $nhom->truongNhom->MaSV === $sv->MaSV);
                            @endphp
                            <div class="p-3 rounded-3 border bg-light d-flex justify-content-between align-items-center hover-card-effect">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark">{{ $sv->HoTen ?? 'Chưa cập nhật' }}</span>
                                        @if($isLeader)
                                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0" style="font-size: 10px;">
                                                <i class="fa-solid fa-crown me-1"></i>Trưởng nhóm
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 12px;">
                                        MSSV: <span class="font-monospace fw-semibold">{{ $sv->MaSV ?? '---' }}</span>
                                        @if($sv?->lop) &bull; Lớp: {{ $sv->lop->TenLop }} @endif
                                        @if($sv?->Email) &bull; Email: {{ $sv->Email }} @endif
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                    Đang thực hiện
                                </span>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3 fst-italic small">Chưa có thành viên nào trong nhóm.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- C. GIẢNG VIÊN HƯỚNG DẪN & PHẢN BIỆN --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-chalkboard-user text-primary me-2"></i>C. Giảng Viên Hướng Dẫn &amp; Phản Biện</span>
                </div>
                <div class="card-body-custom">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-light h-100 hover-card-effect">
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 mb-2" style="font-size: 10.5px;">GV Hướng Dẫn</span>
                                @if($gvhd)
                                    <div class="fw-bold text-dark small">{{ $gvhd->HocHamHocVi ? $gvhd->HocHamHocVi . ' ' : '' }}{{ $gvhd->HoTen }}</div>
                                    <div class="text-muted small mt-1" style="font-size: 11.5px;">Bộ môn: {{ $gvhd->boMon->TenBoMon ?? '---' }}</div>
                                    <div class="text-muted small" style="font-size: 11.5px;">Email: {{ $gvhd->Email ?? '---' }}</div>
                                @else
                                    <div class="text-muted fst-italic small">Chưa phân công GVHD.</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-light h-100 hover-card-effect">
                                <span class="badge bg-info text-white rounded-pill px-3 py-1 mb-2" style="font-size: 10.5px;">GV Phản Biện</span>
                                @if($phanBien && $phanBien->giangVien)
                                    <div class="fw-bold text-dark small">{{ $phanBien->giangVien->HocHamHocVi ? $phanBien->giangVien->HocHamHocVi . ' ' : '' }}{{ $phanBien->giangVien->HoTen }}</div>
                                    <div class="text-muted small mt-1" style="font-size: 11.5px;">Đánh giá: <strong class="text-success">{{ $phanBien->KetQua ?: 'Đang phản biện' }}</strong></div>
                                @else
                                    <div class="text-muted fst-italic small">Chưa phân công phản biện.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- CỘT PHẢI (50%) --}}
        <div class="col-xl-6 d-flex flex-column gap-4">

            {{-- D. TIẾN TRÌNH 6 GIAI ĐOẠN NGHIỆP VỤ (STEPPER) --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-diagram-project text-primary me-2"></i>D. Tiến Trình 6 Giai Đoạn Nghiệp Vụ</span>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1" style="font-size: 11.5px;">GĐ {{ $tienDo->GiaiDoanHienTai }}/6</span>
                </div>
                <div class="card-body-custom">
                    <div class="stepper-progress-track py-2">
                        @foreach($stageDefinitions as $stNum => $stMeta)
                            @php
                                $cond = $dieuKien[$stNum] ?? [];
                                $isCompleted = !empty($cond['dat']);
                                $isCurrent = ($tienDo->GiaiDoanHienTai === $stNum && !$isCompleted);
                                $isOverdue = ($isCurrent && $tienDo->TrangThaiGiaiDoan === 'QUA_HAN');
                                $isPending = ($isCurrent && $tienDo->TrangThaiGiaiDoan === 'CHO_XU_LY');
                            @endphp

                            <div class="stepper-node-item">
                                <div class="stepper-node-circle {{ $isCompleted ? 'status-completed' : ($isOverdue ? 'status-overdue' : ($isPending ? 'status-pending' : ($isCurrent ? 'status-current' : ''))) }}">
                                    @if($isCompleted)<i class="fa-solid fa-check"></i>
                                    @elseif($isOverdue)<i class="fa-solid fa-exclamation"></i>
                                    @elseif($isPending)<i class="fa-solid fa-hourglass-half"></i>
                                    @elseif($isCurrent)<i class="fa-solid fa-play" style="font-size: 9px;"></i>
                                    @else<span>{{ $stNum }}</span>@endif
                                </div>

                                <div class="p-3 rounded-3 border hover-card-effect {{ $isCurrent ? 'bg-light border-primary' : ($isCompleted ? 'bg-white' : 'bg-light bg-opacity-50') }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 13px;">GĐ {{ $stNum }}: {{ $stMeta['ten'] }}</span>
                                        @if($isCompleted)<span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 10.5px;">Đạt</span>
                                        @elseif($isOverdue)<span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 10.5px;">Quá hạn</span>
                                        @elseif($isPending)<span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10.5px;">Chờ xử lý</span>
                                        @elseif($isCurrent)<span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 10.5px;">Đang làm</span>
                                        @else<span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 10.5px;">Chưa bắt đầu</span>@endif
                                    </div>
                                    <div class="text-muted small" style="font-size: 11.5px;">
                                        @if($isCompleted)
                                            <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>{{ $cond['mo_ta'] ?? 'Đã hoàn thành' }}</span>
                                        @else
                                            <span class="text-secondary"><i class="fa-regular fa-clock me-1"></i>{{ $cond['mo_ta'] ?? 'Chưa đáp ứng' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- E. TIẾN ĐỘ THỰC HIỆN & BÁO CÁO CỦA GVHD --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-list-check text-primary me-2"></i>E. Tiến Độ Thực Hiện &amp; Báo Cáo GVHD</span>
                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px;">Giám sát liên kết</span>
                </div>
                <div class="card-body-custom">
                    @if($giaiDoansGV->isEmpty())
                        <div class="text-center py-4 bg-light rounded-3 border border-dashed text-muted small">
                            <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50 d-block"></i>
                            GVHD chưa khởi tạo kế hoạch giai đoạn kỹ thuật trên hệ thống.
                        </div>
                    @else
                        <div class="gvhd-progress-scroll-container" style="max-height: 400px; overflow-y: auto; padding-right: 4px;">
                            <div class="d-flex flex-column gap-3">
                                @foreach($giaiDoansGV as $gdGV)
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-dark small">{{ $gdGV->TenGiaiDoan }}</span>
                                            @if($gdGV->TrangThai === 'HOAN_THANH')
                                                <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 10px;">Hoàn thành</span>
                                            @else
                                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1" style="font-size: 10px;">Đang triển khai</span>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0 align-middle" style="font-size: 11.5px;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="padding: 6px;">Đợt Nộp</th>
                                                        <th style="width: 90px; padding: 6px;" class="text-center">Hạn Nộp</th>
                                                        <th style="width: 85px; padding: 6px;" class="text-center">Phiên bản</th>
                                                        <th style="width: 70px; padding: 6px;" class="text-center">Tệp nộp</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($gdGV->dotNops as $dot)
                                                        @php $latestV = $dot->khoiBaoCaos->first(); @endphp
                                                        <tr>
                                                            <td style="padding: 6px;">
                                                                <div class="fw-semibold text-dark">{{ $dot->TenDotNop }}</div>
                                                                @if($latestV && $latestV->tomTat)
                                                                    <div class="text-muted small mt-1" style="font-size: 10.5px;">
                                                                        <i class="fa-solid fa-robot text-primary me-1"></i>AI: {{ Str::limit($latestV->tomTat->TomTatNgan ?? '', 60) }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center font-monospace text-muted" style="padding: 6px; font-size: 10.5px;">
                                                                {{ $dot->HanNop ? \Carbon\Carbon::parse($dot->HanNop)->format('d/m H:i') : '---' }}
                                                            </td>
                                                            <td class="text-center" style="padding: 6px;">
                                                                @if($latestV)
                                                                    <span class="badge bg-success text-white px-2 py-1" style="font-size: 10px;">v{{ $latestV->LanNop }}</span>
                                                                @else
                                                                    <span class="text-muted" style="font-size: 11px;">Chưa nộp</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center" style="padding: 6px;">
                                                                <div class="d-inline-flex gap-2">
                                                                    @if($latestV && $latestV->DuongDanFile)
                                                                        <a href="{{ asset('storage/' . $latestV->DuongDanFile) }}" target="_blank" class="text-danger" title="Tải tệp báo cáo"><i class="fa-solid fa-file-pdf"></i></a>
                                                                    @endif
                                                                    @if($latestV && $latestV->LinkCode)
                                                                        <a href="{{ $latestV->LinkCode }}" target="_blank" class="text-dark" title="Mã nguồn Git"><i class="fa-brands fa-github"></i></a>
                                                                    @endif
                                                                    @if(!$latestV || (!$latestV->DuongDanFile && !$latestV->LinkCode))
                                                                        <span class="text-muted opacity-50">---</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Modal Nhắc nhở cho trang chi tiết -->
<div class="modal fade" id="remindModalTBM" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form method="POST" action="{{ route('truongbomon.theodoi.remind', $nhom->MaNhom) }}">
                @csrf
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #003366; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bell text-warning"></i> Gửi Yêu Cầu Nhắc Nhở Tiến Độ – Cấp Bộ Môn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <span class="text-muted small d-block">Nhóm sinh viên:</span>
                                <strong class="text-dark">{{ $nhom->TenNhom }} ({{ $nhom->MaNhom }})</strong>
                            </div>
                            <div class="col-md-5">
                                <span class="text-muted small d-block">Đề tài:</span>
                                <strong class="text-dark">{{ $deTai->TenDeTai ?? 'Chưa gán đề tài' }}</strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">GVHD:</span>
                                <strong class="text-dark">{{ $gvhd->HoTen ?? 'Chưa phân công' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            Người nhận yêu cầu nhắc nhở: <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="dtGvhd" value="gvhd" checked>
                                <label class="form-check-label fw-semibold text-dark" for="dtGvhd">
                                    <i class="fa-solid fa-chalkboard-user text-primary me-2"></i> Giảng viên hướng dẫn ({{ $gvhd->HoTen ?? 'GVHD' }})
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="dtSv" value="sinh_vien">
                                <label class="form-check-label fw-semibold text-dark" for="dtSv">
                                    <i class="fa-solid fa-users text-success me-2"></i> Nhóm sinh viên (Trưởng nhóm {{ $nhom->truongNhom?->HoTen ?? 'TN' }} &amp; Thành viên)
                                </label>
                            </div>
                            <div class="form-check p-2 border rounded-3 bg-white">
                                <input class="form-check-input ms-0 me-2" type="radio" name="doi_tuong" id="dtBoth" value="ca_hai">
                                <label class="form-check-label fw-semibold text-dark" for="dtBoth">
                                    <i class="fa-solid fa-paper-plane text-warning me-2"></i> Cả Giảng viên hướng dẫn &amp; Nhóm sinh viên
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Giai đoạn cần nhắc nhở:</label>
                            <select name="giai_doan" class="form-select">
                                @foreach($stageDefinitions as $stk => $stm)
                                    <option value="{{ $stk }}" {{ $tienDo->GiaiDoanHienTai == $stk ? 'selected' : '' }}>
                                        GĐ {{ $stk }}: {{ $stm['ten'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Thời hạn thực hiện / Phản hồi:</label>
                            <input type="date" name="han_xu_ly" class="form-control" value="{{ \Carbon\Carbon::now()->addDays(3)->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Lý do nhắc nhở: <span class="text-danger">*</span></label>
                        <input type="text" name="ly_do" class="form-control" value="Chưa nộp báo cáo tiến độ đúng hạn theo quy định" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Nội dung yêu cầu cụ thể: <span class="text-danger">*</span></label>
                        <textarea name="noi_dung" rows="4" class="form-control" required>Đề nghị quý Thầy/Cô và các bạn sinh viên khẩn trương rà soát tình hình thực hiện đề tài, cập nhật báo cáo tiến độ và nghiệm thu giai đoạn lên hệ thống theo đúng quy định.</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light border px-4 rounded-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4 rounded-3 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i> Gửi Yêu Cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
