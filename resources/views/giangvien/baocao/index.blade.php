@extends('layouts.giangvien')

@section('page_title', 'Quản Lý Tiến Độ Khóa Luận — Giảng Viên Hướng Dẫn')

@push('styles')
<style>
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
    }

    .card-dashboard {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .stat-card-kpi {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--gv-border);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease-in-out;
    }
    .stat-card-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    }
    .stat-card-kpi.border-l-primary { border-left: 4px solid #2563eb; }
    .stat-card-kpi.border-l-warning { border-left: 4px solid #f59e0b; }
    .stat-card-kpi.border-l-success { border-left: 4px solid #10b981; }
    .stat-card-kpi.border-l-danger  { border-left: 4px solid #ef4444; }

    .group-selector-container {
        max-height: 750px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .group-selector-container::-webkit-scrollbar {
        width: 5px;
    }
    .group-selector-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .group-item-card {
        text-align: left;
        border: 1.5px solid var(--gv-border);
        background: #ffffff;
        border-radius: 0.85rem;
        padding: 1.1rem;
        transition: all 0.2s ease-in-out;
        width: 100%;
        cursor: pointer;
    }
    .group-item-card:hover {
        border-color: #cbd5e1;
        background-color: #f8fafc;
        transform: translateY(-1px);
    }
    .group-item-card.active {
        background-color: #f0f7ff;
        border-color: var(--huit-accent);
        box-shadow: 0 8px 16px -3px rgba(2, 132, 199, 0.15);
    }

    .stage-card-item {
        border: 1px solid var(--gv-border);
        border-radius: 0.85rem;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .stage-card-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.04);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Stepper & Accordion */
    .plan-stepper-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
    }
    .plan-stepper-scroll {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
        padding-bottom: 4px;
    }
    .plan-stepper-scroll::-webkit-scrollbar {
        height: 4px;
    }
    .plan-stepper-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .plan-step-item {
        min-width: 120px;
        flex: 1 1 0;
        cursor: pointer;
        padding: 6px 4px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .plan-step-item:hover {
        background-color: #ffffff;
    }
    .plan-step-item.active {
        background-color: #e0f2fe;
    }
    .plan-step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        margin: 0 auto 4px auto;
        transition: all 0.2s ease;
    }

    .stage-accordion-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px !important;
        background: #ffffff;
        margin-bottom: 10px;
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .stage-accordion-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 51, 102, 0.04);
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
    .dot-nop-row {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.15s ease;
    }
    .dot-nop-row:hover {
        border-color: #94a3b8;
        background-color: #fbfcfe;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary me-2"></i>
                <span>Quản Lý Giai Đoạn &amp; Đợt Nộp Báo Cáo Khóa Luận</span>
            </h4>
            <p class="text-muted small mb-0">
                Thiết lập kế hoạch theo dõi tiến độ chi tiết theo từng đề tài, tạo các đợt nộp và nhận xét theo từng phiên bản (version).
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('giangvien.my_tasks') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm fw-medium small d-inline-flex align-items-center">
                <i class="fa-solid fa-table-list me-2 text-primary"></i> Bảng Công Việc
            </a>
            <a href="{{ route('giangvien.calendar') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm fw-medium small d-inline-flex align-items-center">
                <i class="fa-regular fa-calendar-days me-2 text-primary"></i> Lịch Công Việc
            </a>
        </div>
    </div>

    <!-- 4 THẺ KPI THỐNG KÊ (ĐỒNG BỘ 4 CỘT) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-primary d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Tổng Số Nhóm Hướng Dẫn</span>
                    <h3 class="fw-bold text-primary mb-0">{{ $tongSoNhom }}</h3>
                </div>
                <div class="text-primary fs-2 opacity-25">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-warning d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Báo Cáo Chờ Nhận Xét</span>
                    <h3 class="fw-bold text-warning mb-0">{{ $baocaoChoDuyet }}</h3>
                </div>
                <div class="text-warning fs-2 opacity-25">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-success d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Đợt Báo Cáo Đã Đạt</span>
                    <h3 class="fw-bold text-success mb-0">{{ $baocaoDaDat }}</h3>
                </div>
                <div class="text-success fs-2 opacity-25">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-danger d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Nhóm Cần Nhắc Nhở</span>
                    <h3 class="fw-bold text-danger mb-0">{{ $nhomCanNhacNho }}</h3>
                </div>
                <div class="text-danger fs-2 opacity-25">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>
    </div>

    @if($nhoms->isEmpty())
    <div class="card-dashboard p-5 text-center">
        <div class="py-4">
            <i class="fa-solid fa-inbox fa-3x text-secondary mb-3 opacity-50"></i>
            <h5 class="text-dark fw-bold mb-1">Chưa có nhóm nào đăng ký đề tài hướng dẫn</h5>
            <p class="text-muted small mb-0">Danh sách nhóm hướng dẫn sẽ hiển thị tự động tại đây khi có phân công chính thức.</p>
        </div>
    </div>
    @else

    <!-- BỐ CỤC 2 CỘT TƯƠNG TÁC THÔNG MINH -->
    <div class="row g-4">
        
        <!-- CỘT TRÁI (35%): DANH SÁCH NHÓM & TỶ LỆ TIẾN ĐỘ -->
        <div class="col-lg-4">
            <div class="card-dashboard h-100">
                <div class="bg-white border-bottom p-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-users text-primary me-2"></i>Nhóm Hướng Dẫn ({{ $nhoms->count() }})
                    </h6>
                </div>

                {{-- Ô tìm kiếm nhanh nhóm --}}
                <div class="p-3 border-bottom bg-light">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="searchGroupInput" class="form-control border-start-0" 
                               placeholder="Tìm mã nhóm, đề tài, sinh viên...">
                    </div>
                </div>

                {{-- Danh sách các nhóm cuộn độc lập --}}
                <div class="p-3 group-selector-container d-flex flex-column gap-2" id="groupListContainer">
                    @foreach($nhoms as $index => $nhom)
                    @php
                        $prog = $nhom->progress_percent ?? 0;
                        $hasPending = $nhom->baoCaos->contains(function($b) {
                            return $b->TrangThai === 'Chờ duyệt' || ($b->khoiBaoCaos && $b->khoiBaoCaos->where('TrangThai', 'Chờ duyệt')->isNotEmpty());
                        });
                        $searchData = mb_strtolower($nhom->MaNhom . ' ' . $nhom->TenNhom . ' ' . ($nhom->deTai->TenDeTai ?? '') . ' ' . ($nhom->truongNhom->HoTen ?? ''));
                    @endphp
                    <div class="group-item-card {{ $loop->first ? 'active' : '' }}"
                         data-group-id="{{ $nhom->MaNhom }}"
                         data-search="{{ $searchData }}"
                         onclick="selectGroup('{{ $nhom->MaNhom }}')">
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-primary border font-monospace px-2 py-1 fw-bold" style="font-size: 11px;">
                                {{ $nhom->MaNhom }}
                            </span>
                            @if($hasPending)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1 small fw-bold">
                                    <i class="fa-solid fa-bell me-2"></i>Có bài mới
                                </span>
                            @endif
                        </div>

                        <div class="fw-bold text-dark line-clamp-2 mb-1" style="font-size: 13px; line-height: 1.4;" title="{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}">
                            {{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài chính thức' }}
                        </div>

                        <div class="small text-muted mb-3 d-flex align-items-center gap-1" style="font-size: 11.5px;">
                            <i class="fa-solid fa-crown text-warning me-2"></i>
                            <span>{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</span>
                            <span>•</span>
                            <span>{{ $nhom->thanhViens->count() }} SV</span>
                        </div>

                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                <div class="progress-bar {{ $prog >= 100 ? 'bg-success' : ($prog >= 50 ? 'bg-primary' : 'bg-info') }}" 
                                     role="progressbar" 
                                     style="width: {{ $prog }}%; border-radius: 4px;"></div>
                            </div>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 11.5px;">
                                {{ $prog }}%
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI (65%): CÁC GIAI ĐOẠN & ĐỢT NỘP CỦA ĐỀ TÀI / NHÓM ĐƯỢC CHỌN -->
        <div class="col-lg-8" id="groupDetailContainer">
            @foreach($nhoms as $index => $nhom)
            @php
                $deTai = $nhom->deTai;
                $giaiDoans = $deTai ? $deTai->giaiDoanThucHiens()->with('dotNops.khoiBaoCaos')->get() : collect();
                $groupProgress = $nhom->progress_percent ?? 0;
                $tienDoNghiepVu = \App\Models\TienDoNghiepVuKhoaLuan::where('MaNhom', $nhom->MaNhom)->first();
                $daMoCongBaoVe = ($tienDoNghiepVu && $tienDoNghiepVu->GiaiDoanHienTai >= 6) 
                    || ($deTai && \App\Models\BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)->where('LanBaoCao', 5)->where('TrangThai', 'Đạt')->exists());
            @endphp

            <div class="card-dashboard mb-0 h-100 group-detail-panel {{ $loop->first ? '' : 'd-none' }}" 
                 id="panel-group-{{ $nhom->MaNhom }}">
                
                {{-- Header nhóm bên phải --}}
                <div class="bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary text-white fw-bold px-3 py-1 font-monospace" style="font-size: 11px;">
                                    {{ $nhom->MaNhom }}
                                </span>
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                                    {{ $deTai->MaDeTai ?? 'Chưa có mã ĐT' }}
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.1rem; line-height: 1.4;">
                                {{ $deTai->TenDeTai ?? 'Chưa gán đề tài chính thức' }}
                            </h5>
                        </div>

                        {{-- Các nút thao tác thêm giai đoạn / đợt nộp & mở cổng bảo vệ --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            @if($deTai)
                                @if($daMoCongBaoVe)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold d-inline-flex align-items-center" style="font-size: 12px;">
                                        <i class="fa-solid fa-circle-check me-1.5"></i> Đã mở cổng nộp bảo vệ
                                    </span>
                                    <form method="POST" action="{{ route('giangvien.tiendo.nhom.thu_hoi_bao_ve', $nhom->MaNhom) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn thu hồi xác nhận mở cổng nộp hồ sơ bảo vệ của nhóm {{ $nhom->TenNhom }} không?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" title="Thu hồi xác nhận nếu cần nhóm bổ sung/chỉnh sửa tiến độ">
                                            <i class="fa-solid fa-rotate-left me-1"></i> Thu hồi
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center" 
                                            data-bs-toggle="modal" data-bs-target="#modalChoPhepBaoVe{{ $nhom->MaNhom }}"
                                            title="Xác nhận nhóm hoàn thành thực hiện và cho phép nộp hồ sơ bảo vệ khóa luận">
                                        <i class="fa-solid fa-graduation-cap me-2"></i>Xác Nhận Cho Nộp Hồ Sơ Bảo Vệ
                                    </button>
                                @endif

                                @if($giaiDoans->isEmpty())
                                    <form method="POST" action="{{ route('giangvien.tiendo.giai_doan.khoi_tao', $deTai->MaDeTai) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center">
                                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Khởi tạo 6 Giai Đoạn Mẫu
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-inline-flex align-items-center" 
                                        onclick="openAddGiaiDoanModal('{{ $deTai->MaDeTai }}')">
                                    <i class="fa-solid fa-plus me-2"></i>Thêm Giai Đoạn
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Danh sách thành viên nhóm --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top text-muted small" style="font-size: 12px;">
                        <span><i class="fa-solid fa-users me-2 text-primary"></i>Thành viên:</span>
                        @foreach($nhom->thanhViens as $tv)
                            <span class="badge bg-light text-dark border px-3 py-1">
                                {{ $tv->sinhVien->HoTen ?? $tv->MaSV }}
                                @if(str_contains(mb_strtolower($tv->VaiTro ?? ''), 'trưởng'))
                                    <i class="fa-solid fa-crown text-warning ms-1"></i>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Thân: Danh sách các Giai đoạn thực hiện do GVHD thiết lập --}}
                <div class="p-4 flex-grow-1 overflow-auto d-flex flex-column gap-3" style="max-height: 720px;">
                    @if($giaiDoans->isEmpty())
                        <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                            <i class="fa-solid fa-diagram-project fa-3x text-muted opacity-50 mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">Chưa thiết lập lộ trình giai đoạn cho đề tài này</h6>
                            <p class="text-muted small mb-3">Bạn có thể bấm nút khởi tạo lộ trình 6 giai đoạn kỹ thuật mẫu hoặc tự tạo giai đoạn riêng phù hợp với đặc thù đề tài.</p>
                            @if($deTai)
                                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('giangvien.tiendo.giai_doan.khoi_tao', $deTai->MaDeTai) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center">
                                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Khởi Tạo Lộ Trình 6 Giai Đoạn Mẫu
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center"
                                            onclick="openAddGiaiDoanModal('{{ $deTai->MaDeTai }}')">
                                        <i class="fa-solid fa-plus me-2"></i>Thêm Giai Đoạn Tùy Chỉnh
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        @php
                            $allDotNopsGV = $giaiDoans->flatMap->dotNops;
                            $tongDotGV = $allDotNopsGV->count();
                            $soDotDaNopGV = $allDotNopsGV->filter(fn($d) => $d->khoiBaoCaos->isNotEmpty())->count();
                            $soDotChuaNopGV = $allDotNopsGV->filter(fn($d) => $d->khoiBaoCaos->isEmpty())->count();
                            $currentStageItemGV = $giaiDoans->firstWhere('TrangThai', 'DANG_THUC_HIEN');
                            $currentStageNumGV = $currentStageItemGV ? $currentStageItemGV->ThuTu : ($giaiDoans->first()->ThuTu ?? 1);
                        @endphp

                        {{-- Toolbar kế hoạch & Mở rộng/Thu gọn --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold text-dark" style="font-size: 14px; color: #002244 !important;">
                                    <i class="fa-solid fa-list-check text-primary me-1"></i>Kế hoạch theo dõi tiến độ
                                </span>
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                    {{ $giaiDoans->count() }} Giai đoạn &bull; {{ $tongDotGV }} Đợt nộp
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                    Đã nộp: {{ $soDotDaNopGV }}
                                </span>
                                @if($soDotChuaNopGV > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                        Chưa nộp: {{ $soDotChuaNopGV }}
                                    </span>
                                @endif
                            </div>

                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary px-2.5 py-1 fw-medium" onclick="toggleAllStagesGV('{{ $nhom->MaNhom }}', true)">
                                    <i class="fa-solid fa-angles-down me-1 text-primary"></i>Mở tất cả
                                </button>
                                <button type="button" class="btn btn-outline-secondary px-2.5 py-1 fw-medium" onclick="toggleAllStagesGV('{{ $nhom->MaNhom }}', false)">
                                    <i class="fa-solid fa-angles-up me-1 text-secondary"></i>Thu gọn
                                </button>
                            </div>
                        </div>

                        {{-- Thanh tiến trình Stepper 6 giai đoạn --}}
                        <div class="plan-stepper-box mb-2">
                            <div class="plan-stepper-scroll">
                                <div class="d-flex justify-content-between flex-nowrap gap-1" style="min-width: 600px;">
                                    @foreach($giaiDoans as $gd)
                                        @php
                                            $isDone = ($gd->TrangThai === 'HOAN_THANH');
                                            $isCurrent = ($gd->TrangThai === 'DANG_THUC_HIEN');
                                            $isOverdue = (!$isDone && $gd->NgayKetThuc && now()->gt($gd->NgayKetThuc));
                                            $shortName = preg_replace('/^Giai\s+đoạn\s+\d+[\s:.-]*/ui', '', $gd->TenGiaiDoan);
                                        @endphp
                                        <div class="plan-step-item text-center {{ $isCurrent ? 'active' : '' }}" 
                                             onclick="scrollToStageGV('{{ $nhom->MaNhom }}', {{ $gd->ThuTu }})"
                                             title="Bấm để cuộn và mở Giai đoạn {{ $gd->ThuTu }}">
                                            <div class="plan-step-circle {{ $isDone ? 'bg-success text-white' : ($isOverdue ? 'bg-danger text-white' : ($isCurrent ? 'bg-primary text-white shadow-xs' : 'bg-light text-secondary border')) }}">
                                                @if($isDone)
                                                    <i class="fa-solid fa-check"></i>
                                                @elseif($isOverdue)
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                @else
                                                    {{ $gd->ThuTu }}
                                                @endif
                                            </div>
                                            <div class="fw-bold text-truncate px-1 {{ $isCurrent ? 'text-primary' : ($isDone ? 'text-success' : 'text-dark') }}" style="font-size: 11px;" title="{{ $shortName }}">
                                                {{ $shortName }}
                                            </div>
                                            <div class="mt-1">
                                                @if($isDone)
                                                    <span class="badge bg-success rounded-pill" style="font-size: 9px;">Đạt</span>
                                                @elseif($isOverdue)
                                                    <span class="badge bg-danger rounded-pill" style="font-size: 9px;">Quá hạn</span>
                                                @elseif($isCurrent)
                                                    <span class="badge bg-primary text-white rounded-pill" style="font-size: 9px;">Đang làm</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill" style="font-size: 9px;">Chưa mở</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Accordion danh sách 6 giai đoạn của nhóm --}}
                        <div class="accordion stage-accordion-group d-flex flex-column gap-2" id="stageAccordionGV_{{ $nhom->MaNhom }}">
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

                                <div class="stage-accordion-item {{ $isCurrent ? 'is-current-stage' : '' }}" id="stageItemGV_{{ $nhom->MaNhom }}_{{ $gd->ThuTu }}">
                                    <div class="stage-accordion-header p-3 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2 {{ $isCurrent ? '' : 'collapsed' }}"
                                         data-bs-toggle="collapse"
                                         data-bs-target="#collapseStageGV_{{ $nhom->MaNhom }}_{{ $gd->ThuTu }}"
                                         aria-expanded="{{ $isCurrent ? 'true' : 'false' }}"
                                         aria-controls="collapseStageGV_{{ $nhom->MaNhom }}_{{ $gd->ThuTu }}">
                                        
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge {{ $isCurrent ? 'bg-primary' : ($isDone ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-secondary')) }} text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                                GĐ {{ $gd->ThuTu }}
                                            </span>
                                            <span class="fw-bold text-dark" style="font-size: 13.5px; color: #003366 !important;">
                                                {{ $cleanTitle }}
                                            </span>

                                            @if($isDone)
                                                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Đã hoàn thành
                                                </span>
                                            @elseif($isOverdue)
                                                <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Quá hạn
                                                </span>
                                            @elseif($isCurrent)
                                                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                                    <i class="fa-solid fa-spinner fa-spin me-1"></i>Đang thực hiện
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                                    Chưa bắt đầu
                                                </span>
                                            @endif

                                            @if($gd->NgayBatDau || $gd->NgayKetThuc)
                                                <span class="text-muted small ms-1 d-none d-md-inline" style="font-size: 11px;">
                                                    {{ $gd->NgayBatDau ? \Carbon\Carbon::parse($gd->NgayBatDau)->format('d/m/Y') : '---' }} &ndash; {{ $gd->NgayKetThuc ? \Carbon\Carbon::parse($gd->NgayKetThuc)->format('d/m/Y') : '---' }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-2 ms-auto">
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 d-inline-flex align-items-center" 
                                                    style="font-size: 11px;"
                                                    title="Tạo đợt nộp báo cáo trong giai đoạn này"
                                                    onclick="event.stopPropagation(); openAddDotNopModal({{ $gd->MaGiaiDoan }}, '{{ addslashes($gd->TenGiaiDoan) }}')">
                                                <i class="fa-solid fa-plus me-1"></i>Thêm Đợt Nộp
                                            </button>
                                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                                {{ $countDots }} đợt
                                            </span>
                                            <i class="fa-solid fa-chevron-down stage-chevron-icon text-muted ms-1" style="font-size: 11px;"></i>
                                        </div>
                                    </div>

                                    <div id="collapseStageGV_{{ $nhom->MaNhom }}_{{ $gd->ThuTu }}" 
                                         class="collapse stage-accordion-collapse-gv-{{ $nhom->MaNhom }} {{ $isCurrent ? 'show' : '' }}" 
                                         data-bs-parent="#stageAccordionGV_{{ $nhom->MaNhom }}">
                                        <div class="p-3 bg-white border-top">
                                            @if($gd->MoTa)
                                                <p class="text-muted small mb-2" style="font-size: 12px;">{{ $gd->MoTa }}</p>
                                            @endif

                                            @if($stageDots->isEmpty())
                                                <div class="p-3 bg-light rounded-3 text-center text-muted small fst-italic">
                                                    Chưa có đợt nộp báo cáo nào trong giai đoạn này. Bấm "Thêm Đợt Nộp" ở trên để tạo mốc cho sinh viên.
                                                </div>
                                            @else
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($stageDots as $dot)
                                                        @php
                                                            $tt = $dot->getTrangThaiHienThi();
                                                            $latest = $dot->phienBanMoiNhat;
                                                            $countVersions = $dot->khoiBaoCaos->count();
                                                        @endphp
                                                        <div class="dot-nop-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <div class="flex-grow-1" style="min-width: 240px;">
                                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                                    <span class="fw-bold text-dark" style="font-size: 13px; color: #003366 !important;">
                                                                        {{ $dot->TenDotNop }}
                                                                    </span>
                                                                    <span class="badge {{ $tt['badge'] }} rounded-pill" style="font-size: 10px;">
                                                                        {{ $tt['label'] }}
                                                                    </span>
                                                                </div>
                                                                <div class="text-muted small" style="font-size: 11.5px;">
                                                                    <i class="fa-regular fa-clock me-1 text-danger"></i>Hạn nộp: 
                                                                    <strong>{{ $dot->HanNop ? \Carbon\Carbon::parse($dot->HanNop)->format('H:i d/m/Y') : 'Không giới hạn' }}</strong> &bull; 
                                                                    Đã nộp: <strong class="text-primary">{{ $countVersions }} phiên bản</strong>
                                                                </div>
                                                                @if($latest && $latest->NhanXetGV)
                                                                    <div class="text-primary small mt-1" style="font-size: 11.5px;">
                                                                        <strong>Nhận xét gần nhất:</strong> {{ \Illuminate\Support\Str::limit($latest->NhanXetGV, 90) }}
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="d-flex align-items-center gap-1">
                                                                @if($latest && $latest->DuongDanFile)
                                                                    <a href="{{ asset('storage/' . $latest->DuongDanFile) }}" target="_blank" 
                                                                       class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" style="font-size: 11px;" title="Xem file PDF">
                                                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                                                    </a>
                                                                @endif
                                                                @if($latest && $latest->LinkCode)
                                                                    <a href="{{ $latest->LinkCode }}" target="_blank" 
                                                                       class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1" style="font-size: 11px;" title="Source code Git">
                                                                        <i class="fa-brands fa-github me-1"></i>Git
                                                                    </a>
                                                                @endif

                                                                <a href="{{ route('giangvien.tiendo.dot_nop.show', $dot->MaDotNop) }}" 
                                                                   class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center" style="font-size: 11.5px;">
                                                                    <i class="fa-solid fa-pen-to-square me-1"></i>Xem &amp; Nhận Xét ({{ $countVersions }})
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
                    @endif
                </div>

                {{-- Modal Cho Phép Nộp Hồ Sơ Bảo Vệ Cho Nhóm Này --}}
                <div class="modal fade" id="modalChoPhepBaoVe{{ $nhom->MaNhom }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <form method="POST" action="{{ route('giangvien.tiendo.nhom.cho_phep_bao_ve', $nhom->MaNhom) }}">
                                @csrf
                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="fa-solid fa-graduation-cap fa-lg"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-0">Xác Nhận Cho Phép Nộp Hồ Sơ Bảo Vệ</h5>
                                            <small class="text-muted">Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $nhom->MaNhom }})</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body px-4 py-3">
                                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                                        <i class="fa-solid fa-circle-info me-1"></i>
                                        Hành động này sẽ:
                                        <ul class="mb-0 mt-1 ps-3">
                                            <li>Xác nhận nhóm sinh viên đã hoàn thành đủ khối lượng thực hiện đề tài.</li>
                                            <li>Chuyển trạng thái quy trình sang <strong>Giai đoạn 6: Hoàn thiện &amp; Bảo vệ</strong>.</li>
                                            <li>Mở khóa giao diện nộp hồ sơ bảo vệ để nhóm nộp báo cáo chính thức và link demo/mã nguồn.</li>
                                            <li>Gửi thông báo tự động cho tất cả thành viên trong nhóm.</li>
                                        </ul>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-secondary small">Nhận xét / Lời dặn dò cho nhóm (tùy chọn):</label>
                                        <textarea name="LyDo" class="form-control rounded-3" rows="3" placeholder="Nhóm đã hoàn thành tốt khối lượng đề tài, đồng ý cho phép nộp hồ sơ bảo vệ trước Hội đồng..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="fa-solid fa-check me-2"></i>Xác Nhận Mở Cổng Nộp Bảo Vệ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

    </div>
    @endif

</div>

{{-- MODAL THÊM GIAI ĐOẠN --}}
<div class="modal fade" id="modalAddGiaiDoan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="{{ route('giangvien.tiendo.giai_doan.store') }}">
                @csrf
                <input type="hidden" name="MaDeTai" id="inputAddGiaiDoanMaDeTai">

                <div class="modal-header border-bottom px-4 py-3">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i>Thêm Giai Đoạn Thực Hiện Khóa Luận
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Tên giai đoạn <span class="text-danger">*</span></label>
                        <input type="text" name="TenGiaiDoan" class="form-control form-control-sm" placeholder="VD: Giai đoạn 4: Xây dựng & Tích hợp hệ thống" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Ngày bắt đầu</label>
                            <input type="date" name="NgayBatDau" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Ngày kết thúc dự kiến</label>
                            <input type="date" name="NgayKetThuc" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Mô tả mục tiêu</label>
                        <textarea name="MoTa" class="form-control form-control-sm" rows="2" placeholder="Mục tiêu cốt lõi cần đạt được..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Nội dung công việc sinh viên cần làm</label>
                        <textarea name="NoiDungCongViec" class="form-control form-control-sm" rows="2" placeholder="Các đầu việc cụ thể..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark">Kết quả đầu ra dự kiến</label>
                        <input type="text" name="KetQuaDauRa" class="form-control form-control-sm" placeholder="VD: Tài liệu thiết kế CSDL, Mã nguồn API...">
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-2">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">Lưu Giai Đoạn</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL THÊM ĐỢT NỘP BÁO CÁO --}}
<div class="modal fade" id="modalAddDotNop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="{{ route('giangvien.tiendo.dot_nop.store') }}">
                @csrf
                <input type="hidden" name="MaGiaiDoan" id="inputAddDotNopMaGiaiDoan">

                <div class="modal-header border-bottom px-4 py-3">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-file-circle-plus text-success me-2"></i>Tạo Đợt Nộp Báo Cáo
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="alert alert-light border small mb-3">
                        <span class="text-muted">Thuộc giai đoạn:</span>
                        <strong class="text-dark d-block" id="labelAddDotNopTenGiaiDoan">---</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Tên đợt nộp báo cáo <span class="text-danger">*</span></label>
                        <input type="text" name="TenDotNop" class="form-control form-control-sm" placeholder="VD: Đợt 1: Báo cáo kết quả phân tích & Usecase" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Ngày mở nộp</label>
                            <input type="datetime-local" name="NgayMoNop" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Hạn nộp (Deadline)</label>
                            <input type="datetime-local" name="HanNop" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Nội dung yêu cầu sinh viên báo cáo</label>
                        <textarea name="NoiDungYeuCau" class="form-control form-control-sm" rows="3" placeholder="Yêu cầu cụ thể những phần sinh viên phải trình bày trong báo cáo..."></textarea>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="YeuCauSourceCode" value="1" id="switchYeuCauCode" checked>
                        <label class="form-check-label small fw-bold text-dark" for="switchYeuCauCode">
                            Yêu cầu cung cấp đường dẫn mã nguồn (Git repository)
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-2">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">Tạo Đợt Nộp</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.selectGroup = function(groupId) {
    document.querySelectorAll('.group-item-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.group-detail-panel').forEach(p => p.classList.add('d-none'));

    const selectedCard = document.querySelector(`.group-item-card[data-group-id="${groupId}"]`);
    const selectedPanel = document.getElementById(`panel-group-${groupId}`);

    if (selectedCard) selectedCard.classList.add('active');
    if (selectedPanel) selectedPanel.classList.remove('d-none');
};

window.openAddGiaiDoanModal = function(maDeTai) {
    const input = document.getElementById('inputAddGiaiDoanMaDeTai');
    if (input) input.value = maDeTai;
    const modalEl = document.getElementById('modalAddGiaiDoan');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
};

window.openAddDotNopModal = function(maGiaiDoan, tenGiaiDoan) {
    const input = document.getElementById('inputAddDotNopMaGiaiDoan');
    const label = document.getElementById('labelAddDotNopTenGiaiDoan');
    if (input) input.value = maGiaiDoan;
    if (label) label.textContent = tenGiaiDoan;
    const modalEl = document.getElementById('modalAddDotNop');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
};

// Tìm kiếm nhanh nhóm
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchGroupInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.group-item-card').forEach(card => {
                const search = card.getAttribute('data-search') || '';
                card.style.display = search.includes(q) ? 'block' : 'none';
            });
        });
    }
});

// Điều khiển Accordion & Stepper Giai Đoạn GVHD
window.scrollToStageGV = function(maNhom, thuTu) {
    const collapseEl = document.getElementById(`collapseStageGV_${maNhom}_${thuTu}`);
    if (collapseEl) {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        bsCollapse.show();
        const headerEl = document.getElementById(`stageItemGV_${maNhom}_${thuTu}`);
        if (headerEl) {
            headerEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
};

window.toggleAllStagesGV = function(maNhom, expand) {
    document.querySelectorAll(`.stage-accordion-collapse-gv-${maNhom}`).forEach(el => {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
        if (expand) {
            bsCollapse.show();
        } else {
            bsCollapse.hide();
        }
    });
};
</script>
@endpush

@endsection