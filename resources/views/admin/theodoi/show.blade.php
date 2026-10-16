@extends('layouts.admin')

@section('page_title', 'Dashboard Tiến Độ Nhóm: ' . ($nhom->TenNhom ?: $nhom->MaNhom))

@section('content')
<div class="dashboard-viewport">

    {{-- ── 1. HEADER TOP (ĐỘ CAO ~60PX CỐ ĐỊNH) ── --}}
    <header class="dashboard-top-header d-flex flex-wrap justify-content-between align-items-center px-3 py-2 bg-white rounded-3 border shadow-sm mb-2 gap-2">
        {{-- Tiêu đề kế hoạch & Mốc hiện tại --}}
        <div class="d-flex align-items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" title="Quay lại danh sách">
                <i class="fa-solid fa-arrow-left me-1"></i>Danh sách
            </a>

            <div class="d-flex align-items-center gap-2 border-start ps-2.5">
                <span class="fw-bold text-dark fs-6 text-nowrap">
                    <i class="fa-solid fa-graduation-cap text-primary me-1.5"></i>{{ $activePlan->TenKeHoach ?? 'Kế hoạch Khóa luận HK1 (2026 - 2027)' }}
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold d-none d-md-inline-flex align-items-center gap-1.5" style="font-size: 11.5px;">
                    <span class="pulse-dot-sm"></span>
                    {{ $currentPhase->TenMoc ?? 'Sinh viên tạo nhóm trên HUIT-STUDENT (3 SV/đề tài)' }}
                </span>
            </div>
        </div>

        {{-- Bộ đếm nhanh KPI & Nút tác vụ --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-1.5 small me-2">
                <span class="kpi-tag kpi-tag-success" title="Số nhóm đúng tiến độ">
                    <i class="fa-solid fa-circle-check text-success"></i> Đúng tiến độ: <strong>{{ $stats['dung_tien_do'] ?? 6 }}</strong>
                </span>
                <span class="kpi-tag kpi-tag-warning" title="Số nhóm sắp đến hạn">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i> Sắp đến hạn: <strong>{{ $stats['sap_den_han'] ?? 0 }}</strong>
                </span>
                <span class="kpi-tag kpi-tag-danger" title="Số nhóm quá hạn">
                    <i class="fa-solid fa-circle-exclamation text-danger"></i> Quá hạn: <strong>{{ $stats['qua_han'] ?? 0 }}</strong>
                </span>
            </div>

            <form action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Xác nhận gửi thông báo nhắc tiến độ khẩn cấp tới nhóm {{ $nhom->TenNhom ?: $nhom->MaNhom }} và GVHD?');">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm text-dark rounded-pill px-3 py-1 fw-bold shadow-xs">
                    <i class="fa-solid fa-bell me-1"></i>Nhắc Tiến Độ
                </button>
            </form>
        </div>
    </header>

    @php
        $gvhd = $nhom->dangKyDeTai?->giangVienHuongDan ?? $nhom->deTai?->giangVien;
        $deTai = $nhom->dangKyDeTai?->deTai ?? $nhom->deTai;
        $thanhViens = $nhom->thanhViens ?? collect();
    @endphp

    {{-- ── 2. THÂN MÀN HÌNH SPLIT-SCREEN (CHIỀU CAO 100VH - HEADER, OVERFLOW HIDDEN) ── --}}
    <div class="dashboard-body-split">

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- ── CỘT TRÁI: NỘI DUNG CHÍNH (MAIN CONTENT - CHIẾM 68%) ── --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="split-col-main">

            {{-- KHỐI 1: THÔNG TIN ĐỀ TÀI & NHÓM (FIXED CARD Ở ĐẦU) --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-2 flex-shrink-0">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 fw-bold font-monospace">
                                Mã nhóm: {{ $nhom->MaNhom }}
                            </span>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 fw-semibold">
                                Mã ĐT: {{ $deTai->MaDeTai ?? 'DT01' }}
                            </span>
                            <span class="badge {{ $nhom->status_badge }} rounded-pill px-2.5 py-0.5 fw-bold">
                                {{ $nhom->status_label }}
                            </span>
                            @if($nhom->hocKy)
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-0.5">
                                    {{ $nhom->hocKy->TenHocKy }} ({{ $nhom->hocKy->NamHoc }})
                                </span>
                            @endif
                        </div>

                        <h5 class="fw-bold text-primary mb-1.5" style="line-height: 1.35; font-size: 1.05rem;">
                            {{ $deTai->TenDeTai ?? 'Chưa đăng ký đề tài chính thức' }}
                        </h5>

                        <div class="d-flex flex-wrap align-items-center gap-3 text-secondary small" style="font-size: 12.5px;">
                            <span>
                                <i class="fa-solid fa-chalkboard-user text-primary me-1"></i>
                                GVHD: <strong class="text-dark">{{ $gvhd ? ($gvhd->HocHamHocVi ? $gvhd->HocHamHocVi . ' ' : '') . $gvhd->HoTen : 'TS. Lê Hoàng Cường' }}</strong>
                            </span>
                            @if($gvhd && $gvhd->boMon)
                                <span>
                                    <i class="fa-solid fa-building-columns text-info me-1"></i>
                                    {{ $gvhd->boMon->TenBoMon }}
                                </span>
                            @endif
                            @if($gvhd && $gvhd->Email)
                                <span>
                                    <i class="fa-regular fa-envelope text-secondary me-1"></i>
                                    {{ $gvhd->Email }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Thẻ Tiến Độ KPI --}}
                    <div class="kpi-mini-card p-2 px-3 rounded-3 bg-light border text-center flex-shrink-0" style="min-width: 140px;">
                        <div class="text-muted small fw-semibold" style="font-size: 11px;">Tiến độ hoàn thành</div>
                        <div class="fw-bold fs-5 text-primary">{{ $nhom->progress_percent }}%</div>
                        <div class="text-secondary fw-semibold mt-0.5" style="font-size: 11px;">
                            {{ $nhom->completed_stages ?? 0 }} / 6 giai đoạn
                        </div>
                        <div class="progress rounded-pill mt-1" style="height: 4px; background: #e2e8f0;">
                            <div class="progress-bar rounded-pill {{ $nhom->progress_percent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                 style="width: {{ $nhom->progress_percent }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KHỐI 2: TABS NỘI DUNG (THÀNH VIÊN, BÁO CÁO, GHI CHÚ) --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white flex-grow-1 d-flex flex-column overflow-hidden">
                {{-- Header Tabs Navigation --}}
                <div class="card-header bg-white border-bottom p-0 flex-shrink-0">
                    <ul class="nav nav-tabs nav-tabs-modern px-3 pt-2" id="groupDetailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="members-tab" data-bs-toggle="tab" data-bs-target="#tab-members" type="button" role="tab">
                                <i class="fa-solid fa-users me-1.5"></i>Thành viên ({{ $thanhViens->count() }} SV)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#tab-reports" type="button" role="tab">
                                <i class="fa-solid fa-folder-open me-1.5"></i>Báo cáo & Tài liệu
                                @if($nhom->baoCaos && $nhom->baoCaos->count() > 0)
                                    <span class="badge bg-primary text-white rounded-pill ms-1" style="font-size: 10px;">{{ $nhom->baoCaos->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#tab-notes" type="button" role="tab">
                                <i class="fa-solid fa-comments me-1.5"></i>Ghi chú & Thảo luận
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Tab Contents (Scrollable độc lập) --}}
                <div class="tab-content flex-grow-1 overflow-auto p-3" id="groupDetailTabsContent">

                    {{-- ── TAB 1: DANH SÁCH THÀNH VIÊN (3 SV) ── --}}
                    <div class="tab-pane fade show active" id="tab-members" role="tabpanel">
                        <div class="row g-3">
                            @forelse($thanhViens as $tv)
                                @php
                                    $sv = $tv->sinhVien;
                                    $isLeader = ($nhom->MaTruongNhom === $tv->MaSV || str_contains(mb_strtolower($tv->VaiTro ?? ''), 'trưởng'));
                                @endphp
                                <div class="col-md-4">
                                    <div class="member-card p-3 rounded-3 border h-100 {{ $isLeader ? 'border-primary bg-primary-subtle bg-opacity-25' : 'bg-light' }}">
                                        <div class="d-flex align-items-center gap-2.5 mb-2">
                                            <div class="member-avatar {{ $isLeader ? 'bg-primary text-white' : 'bg-white text-secondary border' }}">
                                                <i class="fa-solid {{ $isLeader ? 'fa-crown text-warning' : 'fa-user' }}"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-bold text-dark text-truncate" title="{{ $sv->HoTen ?? 'Sinh viên' }}">
                                                    {{ $sv->HoTen ?? 'Chưa cập nhật tên' }}
                                                </div>
                                                <div class="text-muted font-monospace small" style="font-size: 11px;">
                                                    MSSV: {{ $sv->MaSV ?? $tv->MaSV }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            @if($isLeader)
                                                <span class="badge bg-warning text-dark border border-warning rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10.5px;">
                                                    👑 Trưởng nhóm
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 10.5px;">
                                                    Thành viên
                                                </span>
                                            @endif
                                            @if($sv && $sv->lop)
                                                <span class="badge bg-white text-dark border rounded-pill px-2 py-0.5 ms-1" style="font-size: 10.5px;">
                                                    {{ $sv->lop->TenLop ?? $sv->lop->MaLop }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="small text-secondary" style="font-size: 11.5px; line-height: 1.6;">
                                            <div class="text-truncate" title="{{ $sv->Email ?? '' }}">
                                                <i class="fa-regular fa-envelope me-1 text-muted"></i>{{ $sv->Email ?: '—' }}
                                            </div>
                                            <div>
                                                <i class="fa-solid fa-phone me-1 text-muted"></i>{{ $sv->SoDienThoai ?: '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4 text-muted">
                                    <i class="fa-solid fa-users-slash fa-2x mb-2 text-secondary"></i>
                                    <div>Chưa có thành viên nào trong danh sách nhóm.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ── TAB 2: BÁO CÁO & TÀI LIỆU (TÍCH HỢP XEM TÓM TẮT AI) ── --}}
                    <div class="tab-pane fade" id="tab-reports" role="tabpanel">
                        <div class="table-responsive border rounded-2 bg-white">
                            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 80px;">Giai đoạn</th>
                                        <th>Tiêu đề & Nội dung</th>
                                        <th class="text-center" style="width: 100px;">Tiến độ</th>
                                        <th style="width: 110px;">Ngày nộp</th>
                                        <th class="text-center" style="width: 170px;">Minh chứng</th>
                                        <th class="text-center" style="width: 110px;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nhom->baoCaos as $bc)
                                        @php
                                            $tomTat = $bc->tomTatBaoCao;
                                            $tomTatData = $tomTat?->TomTatJson ?? null;
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge bg-primary rounded-pill px-2 py-0.5 fw-bold font-monospace">
                                                    GĐ {{ $bc->LanBaoCao }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $bc->TieuDe ?? ('Báo cáo giai đoạn ' . $bc->LanBaoCao) }}</div>
                                                <div class="text-muted small mt-0.5 text-truncate" style="max-width: 320px;">
                                                    {{ Str::limit($bc->NoiDungBaoCao ?: 'Chưa cập nhật nội dung chi tiết', 65) }}
                                                </div>

                                                {{-- Khối báo cáo nhỏ & Badge AI --}}
                                                <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap">
                                                    @if($bc->khoiBaoCaos && $bc->khoiBaoCaos->count() > 0)
                                                        <span class="badge bg-light text-secondary border rounded-pill py-0.5 px-2" style="font-size: 10px;">
                                                            <i class="fa-solid fa-layer-group me-1 text-primary"></i>{{ $bc->khoiBaoCaos->count() }} khối nộp bài
                                                        </span>
                                                    @endif

                                                    @if($tomTat)
                                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 fw-semibold d-inline-flex align-items-center gap-1"
                                                                style="font-size: 10.5px;"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#aiSummaryModal"
                                                                data-gd="{{ $bc->LanBaoCao }}"
                                                                data-title="{{ $bc->TieuDe }}"
                                                                data-summary="{{ $tomTat->NoiDungAI ?: ($tomTat->CongViecDaHoanThanh ?: 'Hoàn thành các công việc theo kế hoạch.') }}"
                                                                data-work="{{ $tomTat->CongViecDaHoanThanh }}"
                                                                data-diff="{{ $tomTat->KhoKhan }}"
                                                                data-plan="{{ $tomTat->KeHoachTuanToi }}"
                                                                data-confidence="{{ $tomTat->DoTinCayAI ?? 95 }}">
                                                            <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                                            <span>Tóm tắt AI</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 fw-bold font-monospace">
                                                    {{ $bc->PhanTramHoanThanh ?? 0 }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    {{ $bc->NgayNop ? date('d/m/Y', strtotime($bc->NgayNop)) : '—' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    @if(!empty($bc->DuongDanFile) || !empty($bc->TenFile))
                                                        <a href="{{ asset('storage/' . ltrim($bc->DuongDanFile, '/')) }}" target="_blank" 
                                                           class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                                        </a>
                                                    @endif
                                                    @if($bc->LinkCode)
                                                        <a href="{{ $bc->LinkCode }}" target="_blank" 
                                                           class="btn btn-xs btn-outline-dark rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                            <i class="fa-brands fa-github me-1"></i>Code
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($bc->TrangThai === 'Đạt')
                                                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                                        ✓ Đạt
                                                    </span>
                                                @elseif($bc->TrangThai === 'Chờ duyệt')
                                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                                        Chờ duyệt
                                                    </span>
                                                @elseif($bc->TrangThai === 'Yêu cầu nộp lại')
                                                    <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                                        Nộp lại
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                        {{ $bc->TrangThai ?? 'Chưa đánh giá' }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fa-regular fa-folder-open fa-2x mb-2 text-secondary"></i>
                                                <div>Nhóm chưa nộp báo cáo tiến độ nào cho học kỳ này.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ── TAB 3: GHI CHÚ & THẢO LUẬN ── --}}
                    <div class="tab-pane fade" id="tab-notes" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <h6 class="fw-bold text-dark mb-2" style="font-size: 13px;">
                                    <i class="fa-solid fa-comment-dots text-primary me-1"></i>Nhận xét từ Giảng viên hướng dẫn:
                                </h6>
                                @php
                                    $hasReviews = false;
                                @endphp
                                <div class="list-group list-group-flush border rounded-3 bg-white">
                                    @foreach($nhom->baoCaos as $bcItem)
                                        @if(!empty($bcItem->NhanXet))
                                            @php $hasReviews = true; @endphp
                                            <div class="list-group-item p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10.5px;">
                                                        GĐ {{ $bcItem->LanBaoCao }}
                                                    </span>
                                                    <span class="text-muted small" style="font-size: 11px;">
                                                        {{ $bcItem->NgayXacNhan ? date('d/m/Y H:i', strtotime($bcItem->NgayXacNhan)) : '' }}
                                                    </span>
                                                </div>
                                                <div class="text-dark small" style="white-space: pre-line;">"{{ $bcItem->NhanXet }}"</div>
                                                <div class="text-muted mt-1" style="font-size: 11px;">
                                                    Bởi: <strong>{{ $bcItem->NguoiXacNhan ?: ($gvhd->HoTen ?? 'GVHD') }}</strong>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if(!$hasReviews)
                                        <div class="p-4 text-center text-muted small">
                                            <i class="fa-regular fa-message fa-2x mb-1 text-secondary"></i>
                                            <div>Chưa có nhận xét chính thức nào từ Giảng viên hướng dẫn.</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-5">
                                <h6 class="fw-bold text-dark mb-2" style="font-size: 13px;">
                                    <i class="fa-solid fa-calendar-check text-info me-1"></i>Nhật ký gặp GVHD:
                                </h6>
                                <div class="border rounded-3 p-3 bg-light" style="font-size: 12px;">
                                    @if($nhom->lichGaps && $nhom->lichGaps->count() > 0)
                                        <ul class="list-unstyled mb-0">
                                            @foreach($nhom->lichGaps as $lg)
                                                <li class="mb-2 pb-2 border-bottom">
                                                    <strong>{{ date('d/m/Y H:i', strtotime($lg->ThoiGian)) }}</strong>:
                                                    <div class="text-secondary">{{ $lg->NoiDung ?: 'Gặp mặt định kỳ' }}</div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="text-muted text-center py-3">Chưa có lịch gặp nào được ghi nhận.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- ── CỘT PHẢI: CỐ ĐỊNH LỊCH SỬ & TIMELINE (FIXED RIGHT PANEL - 32%) ── --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="split-col-timeline">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column overflow-hidden">
                {{-- Header cột Timeline --}}
                <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-timeline text-primary me-1.5"></i>Giám Sát 6 Giai Đoạn Khóa Luận
                    </span>
                    <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 fw-bold" style="font-size: 10.5px;">
                        GĐ Hiện tại: {{ $nhom->current_stage ?? 1 }}
                    </span>
                </div>

                {{-- Thân Timeline cuộn độc lập --}}
                <div class="timeline-scroll-body flex-grow-1 overflow-auto p-3">

                    @php
                        $stageSchedules = [
                            1 => ['dates' => '14/09 - 18/09', 'name' => 'Nhận đề cương & CSDL'],
                            2 => ['dates' => '12/10 - 16/10', 'name' => 'Thiết kế hệ thống & Chức năng'],
                            3 => ['dates' => '02/11 - 06/11', 'name' => 'Kiểm thử & Báo cáo dự thảo'],
                            4 => ['dates' => '11/11 - 16/11', 'name' => 'Turnitin (<20%) & Duyệt cuốn'],
                            5 => ['dates' => '05/12 - 18/12', 'name' => 'Bảo vệ trước Hội đồng'],
                            6 => ['dates' => '22/12 - 05/01', 'name' => 'Nhận kết quả & Lưu trữ'],
                        ];
                    @endphp

                    {{-- Vertical Stepper Thu Nhỏ --}}
                    <div class="compact-vertical-stepper position-relative mb-4">
                        <div class="stepper-vertical-line"></div>

                        @foreach($stageSchedules as $stNum => $stSched)
                            @php
                                $stageData = $nhom->stages[$stNum] ?? null;
                                $isCompleted = ($stageData && $stageData['status'] === 'DAT');
                                $isCurrent = ($nhom->current_stage === $stNum && !$isCompleted);
                                $isOverdue = ($stageData && $stageData['status'] === 'QUA_HAN');
                            @endphp
                            <div class="stepper-item d-flex gap-2.5 mb-3 position-relative">
                                {{-- Node icon tròn --}}
                                <div class="stepper-node {{ $isCompleted ? 'node-completed' : ($isOverdue ? 'node-overdue' : ($isCurrent ? 'node-current' : 'node-pending')) }}">
                                    @if($isCompleted)
                                        <i class="fa-solid fa-check"></i>
                                    @elseif($isOverdue)
                                        <i class="fa-solid fa-exclamation"></i>
                                    @elseif($isCurrent)
                                        <i class="fa-solid fa-play" style="font-size: 9px; margin-left: 1px;"></i>
                                    @else
                                        <span style="font-size: 10px;">{{ $stNum }}</span>
                                    @endif
                                </div>

                                {{-- Nội dung bước --}}
                                <div class="stepper-content flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-baseline gap-1">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 12px;">
                                            GĐ {{ $stNum }}: {{ $stSched['name'] }}
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-0.5">
                                        <span class="text-muted font-monospace" style="font-size: 10.5px;">
                                            <i class="fa-regular fa-calendar me-1"></i>{{ $stSched['dates'] }}
                                        </span>
                                        @if($isCompleted)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 10px;">
                                                Đạt yêu cầu
                                            </span>
                                        @elseif($isOverdue)
                                            <span class="badge bg-danger text-white rounded-pill" style="font-size: 10px;">
                                                Quá hạn
                                            </span>
                                        @elseif($isCurrent)
                                            <span class="badge bg-primary text-white rounded-pill" style="font-size: 10px;">
                                                Đang mở
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 10px;">
                                                Chưa hoàn thành
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Khối Lịch sử hoạt động (Activity Log) --}}
                    <div class="activity-log-section border-top pt-3">
                        <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5" style="font-size: 12px;">
                            <i class="fa-solid fa-clock-rotate-left text-primary"></i>Nhật Ký Hoạt Động (Activity Log)
                        </div>

                        <div class="activity-list small" style="font-size: 11.5px;">
                            <div class="activity-item d-flex gap-2 pb-2 mb-2 border-bottom">
                                <i class="fa-solid fa-circle-dot text-primary mt-1" style="font-size: 8px;"></i>
                                <div>
                                    <div class="fw-semibold text-dark">Nhóm đăng ký thành công trên HUIT-STUDENT</div>
                                    <div class="text-muted" style="font-size: 10.5px;">Gồm 3 thành viên chính thức • Trưởng nhóm: {{ $nhom->truongNhom->HoTen ?? 'Nguyễn Hoàng Long' }}</div>
                                </div>
                            </div>

                            @if($nhom->dangKyDeTai)
                            <div class="activity-item d-flex gap-2 pb-2 mb-2 border-bottom">
                                <i class="fa-solid fa-circle-dot text-success mt-1" style="font-size: 8px;"></i>
                                <div>
                                    <div class="fw-semibold text-dark">Phê duyệt đề tài chính thức</div>
                                    <div class="text-muted" style="font-size: 10.5px;">Đề tài: {{ Str::limit($deTai->TenDeTai ?? '', 45) }} • GVHD: {{ $gvhd->HoTen ?? 'GVHD' }}</div>
                                </div>
                            </div>
                            @endif

                            @foreach($nhom->baoCaos as $bcLog)
                            <div class="activity-item d-flex gap-2 pb-2 mb-2 border-bottom">
                                <i class="fa-solid fa-circle-dot text-info mt-1" style="font-size: 8px;"></i>
                                <div>
                                    <div class="fw-semibold text-dark">Nộp báo cáo Giai đoạn {{ $bcLog->LanBaoCao }}</div>
                                    <div class="text-muted" style="font-size: 10.5px;">
                                        Ngày nộp: {{ $bcLog->NgayNop ? date('d/m/Y', strtotime($bcLog->NgayNop)) : 'Ghi nhận' }} • 
                                        Trạng thái: <strong>{{ $bcLog->TrangThai }}</strong>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

{{-- ── 3. MODAL XEM CHI TIẾT TÓM TẮT AI MAP-REDUCE ── --}}
<div class="modal fade" id="aiSummaryModal" tabindex="-1" aria-labelledby="aiSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 text-white" style="background: linear-gradient(135deg, #003366 0%, #0072ce 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning fa-lg"></i>
                    <h6 class="modal-title fw-bold mb-0" id="aiSummaryModalLabel">Chi Tiết Trích Xuất AI Map-Reduce</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4 bg-light">
                <div class="alert alert-info py-2 px-3 mb-3 rounded-2" style="font-size: 13px;" id="modalAiSummaryBox">
                    <strong><i class="fa-solid fa-circle-info me-1"></i>Tổng quan AI:</strong>
                    <span id="modalAiSummaryText">Đang tải dữ liệu tóm tắt...</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100 border shadow-xs">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <h6 class="fw-bold text-success mb-0" style="font-size: 13px;">
                                    <i class="fa-solid fa-circle-check me-1"></i>Hạng mục công việc hoàn thành:
                                </h6>
                            </div>
                            <div class="card-body p-3 small text-dark" id="modalAiWorkText" style="white-space: pre-line; font-size: 12.5px;">
                                —
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border shadow-xs">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <h6 class="fw-bold text-warning-emphasis mb-0" style="font-size: 13px;">
                                    <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Khó khăn & Kế hoạch tiếp theo:
                                </h6>
                            </div>
                            <div class="card-body p-3 small text-dark" id="modalAiDiffText" style="white-space: pre-line; font-size: 12.5px;">
                                —
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-white justify-content-between">
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" id="modalAiConfidenceBadge">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i>AI Verified 95%
                </span>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- ── 4. STYLING TUÂN THỦ STRICT NO-SCROLL SINGLE VIEW ── --}}
<style>
/* Dashboard Viewport: Single screen layout */
.dashboard-viewport {
    height: calc(100vh - 75px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.dashboard-top-header {
    min-height: 54px;
}

.dashboard-body-split {
    flex-grow: 1;
    min-height: 0;
    display: flex;
    gap: 12px;
    overflow: hidden;
}

.split-col-main {
    flex: 0 0 68%;
    max-width: 68%;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.split-col-timeline {
    flex: 0 0 32%;
    max-width: 32%;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

/* KPI tags in header */
.kpi-tag {
    font-size: 12px;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    white-space: nowrap;
}
.kpi-tag-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.kpi-tag-warning { background: #fefce8; border-color: #fef08a; color: #854d0e; }
.kpi-tag-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

/* Tabs styling */
.nav-tabs-modern {
    border-bottom: none;
    gap: 4px;
}
.nav-tabs-modern .nav-link {
    border: none;
    border-bottom: 2.5px solid transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    padding: 8px 14px;
    border-radius: 0;
    transition: all 0.2s;
}
.nav-tabs-modern .nav-link:hover {
    color: #0072ce;
    border-color: #cbd5e1;
}
.nav-tabs-modern .nav-link.active {
    color: #0072ce;
    border-bottom-color: #0072ce;
    background: transparent;
}

/* Member card */
.member-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

/* Compact Vertical Stepper */
.compact-vertical-stepper {
    padding-left: 2px;
}
.stepper-vertical-line {
    position: absolute;
    left: 11px;
    top: 14px;
    bottom: 14px;
    width: 2px;
    background: #e2e8f0;
    z-index: 1;
}
.stepper-node {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    z-index: 2;
    flex-shrink: 0;
    transition: all 0.2s;
}
.node-completed { background: #16a34a; color: #ffffff; }
.node-current   { background: #0072ce; color: #ffffff; box-shadow: 0 0 0 3px rgba(0, 114, 206, 0.2); }
.node-overdue   { background: #dc2626; color: #ffffff; }
.node-pending   { background: #f1f5f9; color: #64748b; border: 1.5px solid #cbd5e1; }

/* Pulse animation */
.pulse-dot-sm {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #198754;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
    animation: pulseDot 1.8s infinite;
}
@keyframes pulseDot {
    0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
    70%  { transform: scale(1); box-shadow: 0 0 0 5px rgba(25, 135, 84, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}

@media (max-width: 991px) {
    .dashboard-viewport { height: auto; overflow: visible; }
    .dashboard-body-split { flex-direction: column; height: auto; overflow: visible; }
    .split-col-main, .split-col-timeline { flex: 0 0 100%; max-width: 100%; }
}
</style>

{{-- ── 5. JAVASCRIPT ĐIỀU KHIỂN MODAL AI TỨC THÌ ── --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var aiModal = document.getElementById('aiSummaryModal');
    if (aiModal) {
        aiModal.addEventListener('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            if (!btn) return;

            var gd = btn.getAttribute('data-gd') || '1';
            var title = btn.getAttribute('data-title') || ('Giai đoạn ' + gd);
            var summary = btn.getAttribute('data-summary') || 'Không có bản tóm tắt';
            var work = btn.getAttribute('data-work') || 'Chưa ghi nhận công việc';
            var diff = btn.getAttribute('data-diff') || 'Không có khó khăn ghi nhận';
            var plan = btn.getAttribute('data-plan') || 'Tiếp tục theo kế hoạch';
            var confidence = btn.getAttribute('data-confidence') || '95';

            document.getElementById('aiSummaryModalLabel').innerHTML = '<i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i>Trích Xuất AI Báo Cáo GĐ ' + gd + ': ' + title;
            document.getElementById('modalAiSummaryText').textContent = summary;
            document.getElementById('modalAiWorkText').textContent = work;
            document.getElementById('modalAiDiffText').textContent = diff + '\n\n• Kế hoạch tiếp theo:\n' + plan;
            document.getElementById('modalAiConfidenceBadge').innerHTML = '<i class="fa-solid fa-shield-halved text-success me-1"></i>AI Verified ' + confidence + '%';
        });
    }
});
</script>
@endsection
