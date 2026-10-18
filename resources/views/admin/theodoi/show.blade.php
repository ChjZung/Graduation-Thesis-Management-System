@extends('layouts.admin')

@section('page_title', 'Chi Tiết Theo Dõi Khóa Luận — Nhóm ' . ($nhom->TenNhom ?: $nhom->MaNhom))

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

    /* Hiệu ứng Card Modern & Mượt mà với Padding tối ưu */
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
        padding: 1.15rem 1.35rem; /* Tăng padding header cho thoáng */
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .card-body-custom {
        padding: 1.35rem; /* Tăng padding thân card để có khoảng thở */
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
    .topic-description-box p { margin-bottom: 0.65rem; }
    .topic-description-box p:last-child { margin-bottom: 0; }

    /* Actor Badge Styles */
    .actor-badge-tbm { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
    .actor-badge-gvhd { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .actor-badge-phanbien { background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
    .actor-badge-system { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .actor-badge-giaovu { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .actor-badge-default { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

    /* Hiệu ứng hover card */
    .hover-card-effect { transition: all 0.2s ease-in-out; }
    .hover-card-effect:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.03); }

    /* Thanh cuộn nội bộ mượt mà cho khối F */
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

    // Học phần & Bộ môn
    $tenHocPhan = $nhom->hocPhan?->TenHocPhan ?? $deTai?->hocPhan?->TenHocPhan ?? $deTai?->HocPhan ?? 'Khóa luận tốt nghiệp';
    $maHocPhan = $nhom->hocPhan?->MaHocPhan ?? $deTai?->hocPhan?->MaHocPhan ?? $nhom->MaHocPhan ?? $deTai?->MaHocPhan;
    $soTinChi = $nhom->hocPhan?->SoTinChi ?? $deTai?->hocPhan?->SoTinChi;
    $tenBoMon = $nhom->hocPhan?->boMon?->TenBoMon 
        ?? $deTai?->hocPhan?->boMon?->TenBoMon 
        ?? $gvhd?->boMon?->TenBoMon 
        ?? $deTai?->giangVien?->boMon?->TenBoMon 
        ?? 'Bộ môn Công nghệ phần mềm';
@endphp

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- ── 1. HEADER & BREADCRUMB ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none d-inline-flex align-items-center" style="font-size: 12px;">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
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
                <span class="badge bg-info-subtle text-info-emphasis border rounded-pill px-3 py-2 font-monospace" style="font-size: 12px;">
                    <i class="fa-solid fa-graduation-cap me-2"></i>{{ $tenHocPhan }}
                </span>
                @if($nhom->tien_do_nghiep_vu->TrangThaiGiaiDoan === 'HOAN_THANH')
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2">Hoàn thành</span>
                @elseif($nhom->tien_do_nghiep_vu->TrangThaiGiaiDoan === 'CHO_XU_LY')
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Chờ xử lý</span>
                @elseif($nhom->tien_do_nghiep_vu->TrangThaiGiaiDoan === 'QUA_HAN')
                    <span class="badge bg-danger text-white rounded-pill px-3 py-2">Quá hạn</span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                        Đang thực hiện
                    </span>
                @endif
            </div>

            <h4 class="fw-bold text-dark mb-0" style="line-height: 1.4;">
                {{ $nhom->deTai->TenDeTai ?? $nhom->TenNhom }}
            </h4>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="card-detail px-3 py-2 bg-white text-center">
                <span class="text-muted small fw-semibold d-block" style="font-size: 11.5px;">Tiến độ nghiệp vụ</span>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <div class="progress flex-grow-1" style="width: 100px; height: 6px; background: #e2e8f0;">
                        <div class="progress-bar {{ $nhom->progress_percent >= 100 ? 'bg-success' : 'bg-primary' }} rounded-pill" 
                             style="width: {{ $nhom->progress_percent }}%;"></div>
                    </div>
                    <span class="fw-bold text-primary font-monospace fs-6">{{ $nhom->progress_percent }}%</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}"
                  onsubmit="return confirm('Xác nhận gửi thông báo nhắc tiến độ đến nhóm {{ $nhom->TenNhom ?: $nhom->MaNhom }} và GVHD?');">
                @csrf
                <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center">
                    <i class="fa-solid fa-bell me-2"></i>Gửi Nhắc Nhở
                </button>
            </form>
        </div>
    </div>

    {{-- BỐ CỤC 2 CỘT 50% - 50% CÂN ĐỐI, CÓ PADDING THOÁNG ĐÃNG --}}
    <div class="row g-4">
        
        {{-- CỘT TRÁI (50%) --}}
        <div class="col-xl-6 d-flex flex-column gap-4">

            {{-- A. THÔNG TIN ĐỀ TÀI --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-book-bookmark text-primary me-2"></i>A. Thông Tin Đề Tài Khóa Luận</span>
                    <span class="badge bg-light text-primary border font-monospace px-3 py-1">{{ $deTai->MaDeTai ?? 'DT--' }}</span>
                </div>
                <div class="card-body-custom">
                    <div class="mb-3">
                        <label class="text-muted small fw-semibold d-block mb-1">Tên đề tài chính thức:</label>
                        <h6 class="fw-bold text-dark mb-0" style="line-height: 1.45;">
                            {{ $deTai->TenDeTai ?? 'Chưa đăng ký đề tài chính thức' }}
                        </h6>
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Học phần khóa luận --}}
                        <div class="col-sm-6">
                            <span class="text-muted small fw-semibold d-block">
                                <i class="fa-solid fa-graduation-cap text-primary me-2"></i>Học phần:
                            </span>
                            <span class="small fw-bold text-primary">
                                {{ $tenHocPhan }}
                            </span>
                            @if($maHocPhan)
                                <span class="badge bg-light text-secondary border font-monospace ms-1" style="font-size: 10.5px;">
                                    {{ $maHocPhan }}
                                </span>
                            @endif
                            @if($soTinChi)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1" style="font-size: 10.5px;">
                                    {{ $soTinChi }} TC
                                </span>
                            @endif
                        </div>

                        {{-- Bộ môn quản lý --}}
                        <div class="col-sm-6">
                            <span class="text-muted small fw-semibold d-block">
                                <i class="fa-solid fa-building-columns text-primary me-2"></i>Bộ môn quản lý:
                            </span>
                            <span class="small fw-bold text-dark">
                                {{ $tenBoMon }}
                            </span>
                        </div>

                        {{-- Học kỳ / Năm học --}}
                        <div class="col-sm-6">
                            <span class="text-muted small fw-semibold d-block">
                                <i class="fa-regular fa-calendar-days text-secondary me-2"></i>Học kỳ / Năm học:
                            </span>
                            <span class="small fw-semibold text-dark">
                                {{ $nhom->hocKy ? $nhom->hocKy->TenHocKy . ' (' . $nhom->hocKy->NamHoc . ')' : ($activePlan->TenKeHoach ?? '---') }}
                            </span>
                        </div>

                        {{-- Ngành / Chuyên ngành --}}
                        <div class="col-sm-6">
                            <span class="text-muted small fw-semibold d-block">
                                <i class="fa-solid fa-code-branch text-secondary me-2"></i>Chuyên ngành:
                            </span>
                            <span class="small fw-semibold text-dark">
                                {{ $deTai?->chuyenNganh?->TenChuyenNganh ?? $nhom->truongNhom?->lop?->nganh?->TenNganh ?? 'Công nghệ thông tin' }}
                            </span>
                        </div>
                    </div>

                    {{-- Mô tả chi tiết --}}
                    <div>
                        <label class="text-muted small fw-semibold d-block mb-2">
                            <i class="fa-solid fa-align-left me-2"></i>Mô tả chi tiết đề tài:
                        </label>
                        <div class="topic-description-container" id="topicDescContainer">
                            <div class="topic-description-box" id="topicDescBox">
                                @if($deTai && $deTai->MoTa)
                                    {!! \App\Services\HtmlSanitizerService::cleanDescription($deTai->MoTa) !!}
                                @else
                                    <span class="text-muted fst-italic">Chưa có văn bản mô tả chi tiết.</span>
                                @endif
                            </div>
                            <div class="text-center mt-2 d-none" id="topicDescToggleWrapper">
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-primary fw-semibold shadow-none d-inline-flex align-items-center" id="topicDescToggleBtn" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-chevron-down me-2" id="topicDescToggleIcon"></i><span id="topicDescToggleText">Xem thêm mô tả</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- B. THÔNG TIN NHÓM SINH VIÊN --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-users text-primary me-2"></i>B. Thông Tin Nhóm Sinh Viên</span>
                    <span class="badge bg-light text-dark border px-3 py-1">{{ $nhom->thanhViens->count() }} thành viên</span>
                </div>
                <div class="card-body-custom">
                    <div class="row g-3">
                        @forelse($nhom->thanhViens as $tv)
                            @php
                                $sv = $tv->sinhVien;
                                $isLeader = ($nhom->MaTruongNhom === $tv->MaSV || str_contains(mb_strtolower($tv->VaiTro ?? ''), 'trưởng'));
                            @endphp
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border h-100 hover-card-effect {{ $isLeader ? 'bg-primary-subtle bg-opacity-25 border-primary' : 'bg-light' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $sv->HoTen ?? 'Chưa rõ' }}</div>
                                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ $tv->MaSV }}</div>
                                        </div>
                                        @if($isLeader)
                                            <span class="badge bg-warning text-dark border rounded-pill px-2 py-1" style="font-size: 10px;">👑 Trưởng nhóm</span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 10px;">Thành viên</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small" style="font-size: 11.5px; line-height: 1.5;">
                                        <div>Lớp: {{ $sv->lop->TenLop ?? '---' }}</div>
                                        <div class="text-truncate">Email: {{ $sv->Email ?: '---' }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-3 text-muted small">Chưa có thành viên nào.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- C. GIẢNG VIÊN (HD & PHẢN BIỆN) --}}
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
                                @else
                                    <div class="text-muted fst-italic small">Chưa phân công.</div>
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
                                    @if($isCompleted)<i class="fa-solid fa-check me-2"></i>
                                    @elseif($isOverdue)<i class="fa-solid fa-exclamation"></i>
                                    @elseif($isPending)<i class="fa-solid fa-hourglass-half me-2"></i>
                                    @elseif($isCurrent)<i class="fa-solid fa-play" style="font-size: 9px;"></i>
                                    @else<span>{{ $stNum }}</span>@endif
                                </div>

                                <div class="p-3 rounded-3 border hover-card-effect {{ $isCurrent ? 'bg-light border-primary' : ($isCompleted ? 'bg-white' : 'bg-light bg-opacity-50') }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 13px;">GĐ {{ $stNum }}: {{ $stMeta['ten'] }}</span>
                                        @if($isCompleted)<span class="badge bg-success text-white rounded-pill px-3 py-1" style="font-size: 10.5px;">Đạt</span>
                                        @elseif($isOverdue)<span class="badge bg-danger text-white rounded-pill px-3 py-1" style="font-size: 10.5px;">Quá hạn</span>
                                        @elseif($isPending)<span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size: 10.5px;">Chờ xử lý</span>
                                        @elseif($isCurrent)<span class="badge bg-primary text-white rounded-pill px-3 py-1" style="font-size: 10.5px;">Đang làm</span>
                                        @else<span class="badge bg-light text-muted border rounded-pill px-3 py-1" style="font-size: 10.5px;">Chưa bắt đầu</span>@endif
                                    </div>
                                    <div class="text-muted small" style="font-size: 11.5px;">
                                        @if($isCompleted)
                                            <span class="text-success"><i class="fa-solid fa-circle-check me-2"></i>{{ $cond['mo_ta'] ?? 'Đã hoàn thành' }}</span>
                                        @else
                                            <span class="text-secondary"><i class="fa-regular fa-clock me-2"></i>{{ $cond['mo_ta'] ?? 'Chưa đáp ứng' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- E. TIẾN ĐỘ GVHD (GIÁM SÁT LIÊN KẾT) --}}
            <div class="card-detail">
                <div class="card-detail-header">
                    <span><i class="fa-solid fa-diagram-next text-primary me-2"></i>E. Tiến Độ Thực Hiện Của GVHD</span>
                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px;">Read-only</span>
                </div>
                <div class="card-body-custom">
                    @if($giaiDoansGV->isEmpty())
                        <div class="text-center py-3 bg-light rounded-3 border border-dashed text-muted small">
                            Chưa khởi tạo kế hoạch giai đoạn kỹ thuật.
                        </div>
                    @else
                        <div class="gvhd-progress-scroll-container" style="max-height: 400px; overflow-y: auto; padding-right: 4px;">
                            <div class="d-flex flex-column gap-2">
                                @foreach($giaiDoansGV as $gdGV)
                                    <div class="border rounded-2 p-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-dark small">{{ $gdGV->TenGiaiDoan }}</span>
                                            @if($gdGV->TrangThai === 'HOAN_THANH')
                                                <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 10px;">Hoàn thành</span>
                                            @else
                                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1" style="font-size: 10px;">Đang làm</span>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0 align-middle" style="font-size: 11.5px;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="padding: 5px;">Đợt Nộp</th>
                                                        <th style="width: 85px; padding: 5px;" class="text-center">Hạn</th>
                                                        <th style="width: 80px; padding: 5px;" class="text-center">Trạng thái</th>
                                                        <th style="width: 60px; padding: 5px;" class="text-center">File/Git</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($gdGV->dotNops as $dot)
                                                        @php $latestV = $dot->khoiBaoCaos->first(); @endphp
                                                        <tr>
                                                            <td style="padding: 5px;">{{ $dot->TenDotNop }}</td>
                                                            <td class="text-center font-monospace text-muted" style="padding: 5px; font-size: 10.5px;">
                                                                {{ $dot->HanNop ? \Carbon\Carbon::parse($dot->HanNop)->format('d/m H:i') : '---' }}
                                                            </td>
                                                            <td class="text-center" style="padding: 5px;">
                                                                @if($latestV)
                                                                    <span class="badge bg-success text-white px-2 py-1" style="font-size: 10px;">v{{ $latestV->LanNop }}</span>
                                                                @else
                                                                    <span class="text-muted" style="font-size: 11px;">Chưa nộp</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center" style="padding: 5px;">
                                                                <div class="d-inline-flex gap-2">
                                                                    @if($latestV && $latestV->DuongDanFile)
                                                                        <a href="{{ asset('storage/' . $latestV->DuongDanFile) }}" target="_blank" class="text-danger" title="PDF"><i class="fa-solid fa-file-pdf"></i></a>
                                                                    @endif
                                                                    @if($latestV && $latestV->LinkCode)
                                                                        <a href="{{ $latestV->LinkCode }}" target="_blank" class="text-dark" title="Git"><i class="fa-brands fa-github"></i></a>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const box = document.getElementById('topicDescBox');
    const container = document.getElementById('topicDescContainer');
    const toggleWrapper = document.getElementById('topicDescToggleWrapper');
    const toggleBtn = document.getElementById('topicDescToggleBtn');
    const toggleIcon = document.getElementById('topicDescToggleIcon');
    const toggleText = document.getElementById('topicDescToggleText');

    if (box && container && box.scrollHeight > 180) {
        container.classList.add('collapsed');
        if (toggleWrapper) toggleWrapper.classList.remove('d-none');

        toggleBtn.addEventListener('click', function () {
            const isCollapsed = container.classList.contains('collapsed');
            if (isCollapsed) {
                container.classList.remove('collapsed');
                toggleIcon.className = 'fa-solid fa-chevron-up me-1';
                toggleText.textContent = 'Thu gọn';
            } else {
                container.classList.add('collapsed');
                toggleIcon.className = 'fa-solid fa-chevron-down me-1';
                toggleText.textContent = 'Xem thêm mô tả';
            }
        });
    }
});
</script>
@endpush