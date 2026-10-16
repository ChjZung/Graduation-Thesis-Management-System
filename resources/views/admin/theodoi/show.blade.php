@extends('layouts.admin')

@section('page_title', 'Chi Tiết Tiến Độ Nhóm: ' . ($nhom->TenNhom ?: $nhom->MaNhom))

@section('content')
<div class="container-fluid px-0">
    {{-- ── 1. THANH ĐIỀU HƯỚNG VÀ THAO TÁC HÀNH ĐỘNG ── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.theodoi.index') }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách theo dõi
            </a>
            <span class="text-muted">|</span>
            <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1 font-monospace fw-bold">
                {{ $nhom->MaNhom }}
            </span>
            <span class="badge {{ $nhom->status_badge }} rounded-pill px-3 py-1 fw-bold">
                {{ $nhom->status_label }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('Xác nhận gửi thông báo nhắc nhở tiến độ tới nhóm {{ $nhom->TenNhom ?: $nhom->MaNhom }} và GVHD?');">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm text-dark rounded-2 px-3 fw-bold shadow-sm">
                    <i class="fa-solid fa-bell me-1"></i> Gửi Thông Báo Nhắc Tiến Độ
                </button>
            </form>
            @if($nhom->dangKyDeTai?->deTai)
                @if(Route::has('admin.detai.show'))
                    <a href="{{ route('admin.detai.show', $nhom->dangKyDeTai->deTai->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-2 px-3 fw-semibold">
                        <i class="fa-solid fa-file-lines me-1"></i> Xem đề tài gốc
                    </a>
                @elseif(Route::has('admin.duyet_detai.index'))
                    <a href="{{ route('admin.duyet_detai.index', ['search' => $nhom->dangKyDeTai->deTai->MaDeTai]) }}" class="btn btn-sm btn-outline-primary rounded-2 px-3 fw-semibold">
                        <i class="fa-solid fa-file-lines me-1"></i> Quản lý đề tài
                    </a>
                @endif
            @endif
        </div>
    </div>

    @php
        $gvhd = $nhom->dangKyDeTai?->giangVienHuongDan ?? $nhom->deTai?->giangVien;
        $deTai = $nhom->dangKyDeTai?->deTai ?? $nhom->deTai;
    @endphp

    {{-- ── 2. BANNER TỔNG QUAN NHÓM & KPI TIẾN ĐỘ ── --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center gy-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-primary rounded-pill px-2.5 py-1 fw-bold font-monospace">{{ $nhom->MaNhom }}</span>
                        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-semibold">
                            {{ $nhom->TenNhom ?: 'Nhóm Khóa Luận' }}
                        </span>
                        @if($nhom->hocKy)
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill">
                                <i class="fa-regular fa-calendar-check me-1"></i>{{ $nhom->hocKy->TenHocKy }} ({{ $nhom->hocKy->NamHoc }})
                            </span>
                        @endif
                    </div>
                    
                    <h4 class="fw-bold text-primary mb-2" style="line-height: 1.4;">
                        {{ $deTai->TenDeTai ?? 'Chưa đăng ký đề tài chính thức' }}
                    </h4>

                    <div class="text-secondary small d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <i class="fa-solid fa-crown text-warning me-1"></i>
                            Trưởng nhóm: <strong class="text-dark">{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</strong>
                            @if($nhom->truongNhom)
                                <span class="text-muted">({{ $nhom->truongNhom->MaSoSinhVien ?? $nhom->truongNhom->MaSV ?? $nhom->MaTruongNhom }})</span>
                            @endif
                        </div>
                        <div>
                            <i class="fa-solid fa-chalkboard-user text-primary me-1"></i>
                            GVHD: <strong class="text-dark">{{ $gvhd ? ($gvhd->HocHamHocVi ? $gvhd->HocHamHocVi . ' ' : '') . $gvhd->HoTen : 'Chưa phân công' }}</strong>
                        </div>
                        @if($gvhd && $gvhd->boMon)
                            <div>
                                <i class="fa-solid fa-building-columns text-info me-1"></i>
                                Bộ môn: <span class="text-dark fw-semibold">{{ $gvhd->boMon->TenBoMon }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Thống kê KPI ngắn gọn --}}
                <div class="col-lg-5">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2.5 bg-light rounded-3 border h-100 d-flex flex-column justify-content-center">
                                <div class="text-muted small fw-semibold">Tiến độ</div>
                                <div class="fw-bold fs-4 text-primary mt-1">{{ $nhom->progress_percent }}%</div>
                                <div class="progress rounded-pill mt-1" style="height: 5px; background: #e2e8f0;">
                                    <div class="progress-bar rounded-pill {{ $nhom->progress_percent >= 100 ? 'bg-success' : ($nhom->status_code == 'QUA_HAN' ? 'bg-danger' : 'bg-primary') }}" 
                                         style="width: {{ $nhom->progress_percent }}%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 bg-light rounded-3 border h-100 d-flex flex-column justify-content-center">
                                <div class="text-muted small fw-semibold">Hoàn thành</div>
                                <div class="fw-bold fs-4 text-success mt-1">
                                    {{ $nhom->completed_stages }} <span class="fs-6 text-muted fw-normal">/ 6 mốc</span>
                                </div>
                                <div class="text-muted small" style="font-size: 11px;">Giai đoạn</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 bg-light rounded-3 border h-100 d-flex flex-column justify-content-center">
                                <div class="text-muted small fw-semibold">GĐ hiện tại</div>
                                <div class="fw-bold fs-5 text-dark mt-1 text-truncate" title="{{ $nhom->current_stage_label }}">
                                    GĐ {{ $nhom->current_stage }}
                                </div>
                                <div class="text-muted small text-truncate" style="font-size: 11px;">{{ $nhom->current_stage_label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. CHI TIẾT 6 GIAI ĐOẠN TIẾN ĐỘ KHÓA LUẬN (CARD TIMELINE TOÀN CẢNH) ── --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                <i class="fa-solid fa-bars-progress text-primary me-1.5"></i>
                Chi Tiết Lộ Trình 6 Giai Đoạn Giám Sát Tiến Độ Khóa Luận
            </span>
            <span class="badge bg-light text-secondary border px-2.5 py-1 small">
                6 Mốc Quy Chuẩn HUIT
            </span>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                @foreach($nhom->stages as $sNum => $stItem)
                @php
                    $isFinished = ($stItem['status'] === 'DAT');
                    $isPending = ($stItem['status'] === 'CHO_DUYET');
                    $isReSubmit = ($stItem['status'] === 'NOP_LAI');
                    $isCurrent = ($nhom->current_stage === $sNum);
                @endphp
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="p-3 rounded-3 border h-100 position-relative transition-all {{ $isCurrent ? 'border-primary bg-primary-subtle bg-opacity-25 shadow-sm' : 'bg-light' }}">
                        @if($isCurrent)
                            <span class="position-absolute top-0 end-0 badge bg-primary text-white rounded-bottom-start-2 rounded-top-0 px-2 py-1 small" style="font-size: 10.5px;">
                                <i class="fa-solid fa-crosshairs me-1"></i>Đang thực hiện
                            </span>
                        @endif

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge {{ $isFinished ? 'bg-success' : ($isPending ? 'bg-warning text-dark' : ($isReSubmit ? 'bg-info text-dark' : 'bg-secondary')) }} rounded-circle p-2" 
                                  style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">
                                @if($isFinished)
                                    <i class="fa-solid fa-check"></i>
                                @else
                                    {{ $sNum }}
                                @endif
                            </span>
                            <div class="flex-grow-1 overflow-hidden pe-3">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;" title="{{ $stItem['name'] }}">
                                    {{ $stItem['name'] }}
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-0.5">
                                    <span class="badge {{ $stItem['badge'] }} px-2 py-0.5" style="font-size: 11px;">
                                        {{ $stItem['status_label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Thông tin chi tiết mốc --}}
                        <div class="pt-2 border-top small text-muted">
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fa-regular fa-calendar me-1"></i>Ngày nộp:</span>
                                <strong class="text-dark">{{ $stItem['date'] ?? 'Chưa nộp' }}</strong>
                            </div>

                            @if(!empty($stItem['ti_le_hoan_thanh']))
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="fa-solid fa-percent me-1"></i>Tỉ lệ hoàn thành:</span>
                                    <strong class="text-primary">{{ $stItem['ti_le_hoan_thanh'] }}%</strong>
                                </div>
                            @endif

                            @if(!empty($stItem['file']))
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span><i class="fa-solid fa-paperclip me-1"></i>File đính kèm:</span>
                                    <a href="{{ asset('storage/' . ltrim($stItem['file'], '/')) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-download me-1"></i>Tải file
                                    </a>
                                </div>
                            @endif

                            @if(!empty($stItem['nhan_xet']))
                                <div class="mt-2 p-2 bg-white rounded border small">
                                    <div class="text-secondary fw-semibold mb-0.5" style="font-size: 11px;">
                                        <i class="fa-regular fa-comment-dots text-primary me-1"></i>Nhận xét từ {{ $stItem['gv_nhan_xet'] ?? 'GVHD' }}:
                                    </div>
                                    <div class="text-dark fst-italic" style="font-size: 11.5px; line-height: 1.35;">
                                        "{{ $stItem['nhan_xet'] }}"
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── 4. THÔNG TIN 2 CỘT: THÀNH VIÊN NHÓM & THÔNG TIN ĐỀ TÀI / GVHD ── --}}
    <div class="row g-3 mb-4">
        {{-- Cột Trái: Danh Sách Thành Viên Nhóm --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-users text-primary me-1.5"></i>
                        Thành Viên Nhóm ({{ $nhom->thanhViens->count() }} Sinh Viên)
                    </span>
                    <span class="badge bg-light text-secondary border px-2 py-0.5 small">
                        Tối đa 3 SV/nhóm
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 105px;">MSSV</th>
                                <th>HỌ VÀ TÊN</th>
                                <th style="width: 100px;">LỚP</th>
                                <th class="text-center" style="width: 115px;">VAI TRÒ</th>
                                <th>LIÊN HỆ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nhom->thanhViens as $tv)
                            @php
                                $sv = $tv->sinhVien;
                                $isLeader = ($nhom->MaTruongNhom === $tv->MaSV || str_contains(mb_strtolower($tv->VaiTro ?? ''), 'trưởng'));
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace px-1.5 py-0.5">
                                        {{ $sv->MaSoSinhVien ?? $sv->MaSV ?? $tv->MaSV }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sv->HoTen ?? 'Chưa rõ họ tên' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $sv->lop?->nganh?->TenNganh ?? 'Công nghệ thông tin' }}</div>
                                </td>
                                <td>{{ $sv->lop?->TenLop ?? '—' }}</td>
                                <td class="text-center">
                                    @if($isLeader)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-bold">
                                            👑 Trưởng nhóm
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5">
                                            Thành viên
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small text-truncate" style="max-width: 140px;" title="{{ $sv->Email ?? '' }}">
                                        <i class="fa-regular fa-envelope me-1"></i>{{ $sv->Email ?? '—' }}
                                    </div>
                                    @if(!empty($sv->SoDienThoai))
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-phone me-1"></i>{{ $sv->SoDienThoai }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Chưa có thành viên nào trong nhóm.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cột Phải: Thông Tin Đề Tài & Giảng Viên Hướng Dẫn --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-book-bookmark text-primary me-1.5"></i>
                        Thông Tin Đề Tài &amp; GVHD Phụ Trách
                    </span>
                    @if($deTai)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 small">
                            {{ $deTai->TrangThai ?? 'Đã duyệt' }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if($deTai)
                        <div class="mb-3">
                            <div class="text-muted small">Tên đề tài khóa luận:</div>
                            <div class="fw-bold text-primary fs-6 mt-0.5">{{ $deTai->TenDeTai }}</div>
                            <div class="text-muted small mt-1 d-flex flex-wrap gap-2">
                                <span><strong>Mã ĐT:</strong> <code>{{ $deTai->MaDeTai }}</code></span>
                                <span>&bull;</span>
                                <span><strong>Lĩnh vực:</strong> {{ $deTai->LinhVuc ?? 'CNPM & AI' }}</span>
                                <span>&bull;</span>
                                <span><strong>Học phần:</strong> {{ $nhom->hocPhan->TenHocPhan ?? 'Khóa luận tốt nghiệp' }}</span>
                            </div>
                        </div>

                        {{-- Thông tin Giảng viên hướng dẫn --}}
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-chalkboard-user text-primary"></i> Giảng Viên Hướng Dẫn
                            </div>
                            @if($gvhd)
                                <div class="row g-2 small">
                                    <div class="col-sm-6">
                                        <div class="text-muted">Họ và tên:</div>
                                        <div class="fw-bold text-dark mt-0.5">
                                            {{ $gvhd->HocHamHocVi ? $gvhd->HocHamHocVi . ' ' : '' }}{{ $gvhd->HoTen }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-muted">Bộ môn:</div>
                                        <div class="fw-bold text-dark mt-0.5">{{ $gvhd->boMon->TenBoMon ?? 'Khoa CNTT' }}</div>
                                    </div>
                                    <div class="col-sm-6 mt-1">
                                        <div class="text-muted">Email liên hệ:</div>
                                        <div class="fw-semibold text-primary mt-0.5">{{ $gvhd->Email ?? 'Chưa cập nhật' }}</div>
                                    </div>
                                    <div class="col-sm-6 mt-1">
                                        <div class="text-muted">Số điện thoại:</div>
                                        <div class="fw-semibold text-dark mt-0.5">{{ $gvhd->SoDienThoai ?? '—' }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted fst-italic small">Chưa phân công giảng viên hướng dẫn.</div>
                            @endif
                        </div>

                        {{-- Hồ sơ bảo vệ nếu có --}}
                        @if($nhom->hoSoBaoVe)
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-file-shield text-success"></i> Hồ Sơ Bảo Vệ Khóa Luận
                            </div>
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <div class="text-muted">Tỷ lệ Turnitin:</div>
                                    <div class="fw-bold mt-0.5 {{ ($nhom->hoSoBaoVe->TyLeTrungLap ?? 0) <= 20 ? 'text-success' : 'text-danger' }}">
                                        {{ $nhom->hoSoBaoVe->TyLeTrungLap !== null ? $nhom->hoSoBaoVe->TyLeTrungLap . '%' : 'Chưa quét' }}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted">GVHD xác nhận:</div>
                                    <div class="fw-bold mt-0.5 {{ $nhom->hoSoBaoVe->XacNhanGVHD ? 'text-success' : 'text-warning' }}">
                                        {{ $nhom->hoSoBaoVe->XacNhanGVHD ? '✓ Đã ký duyệt cho bảo vệ' : '○ Chờ duyệt' }}
                                    </div>
                                </div>
                                @if($nhom->hoSoBaoVe->hoiDong)
                                <div class="col-12 mt-1">
                                    <div class="text-muted">Hội đồng chấm:</div>
                                    <div class="fw-semibold text-dark mt-0.5">{{ $nhom->hoSoBaoVe->hoiDong->TenHoiDong }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                            Nhóm chưa đăng ký đề tài hoặc chưa được duyệt đề tài chính thức.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── 5. NHẬT KÝ BÁO CÁO TIẾN ĐỘ & LỊCH GẶP HƯỚNG DẪN ── --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                <i class="fa-solid fa-clock-rotate-left text-primary me-1.5"></i>
                Lịch Sử Báo Cáo Tiến Độ Nhóm Đã Nộp ({{ $nhom->baoCaos->count() }} lần)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 100px;">LẦN BÁO CÁO</th>
                        <th style="min-width: 200px;">NỘI DUNG BÁO CÁO</th>
                        <th class="text-center" style="width: 110px;">HOÀN THÀNH</th>
                        <th style="width: 120px;">NGÀY NỘP</th>
                        <th style="min-width: 220px;">Ý KIẾN ĐÁNH GIÁ CỦA GVHD</th>
                        <th class="text-center" style="width: 110px;">FILE ĐÍNH KÈM</th>
                        <th class="text-center" style="width: 110px;">TRẠNG THÁI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nhom->baoCaos as $bc)
                    @php $nx = $bc->nhanXets?->first(); @endphp
                    <tr>
                        <td>
                            <span class="badge bg-primary rounded-pill px-2.5 py-1 fw-bold">
                                Lần {{ $bc->LanBaoCao }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $bc->TieuDe ?? ('Báo cáo tiến độ lần ' . $bc->LanBaoCao) }}</div>
                            <div class="text-muted small mt-0.5">{{ Str::limit($bc->NoiDungBaoCao ?? $bc->NoiDung ?? '—', 80) }}</div>
                        </td>
                        <td class="text-center">
                            @if($bc->TiLeHoanThanh !== null)
                                <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 fw-bold">
                                    {{ $bc->TiLeHoanThanh }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted small">
                                {{ $bc->NgayNop ? date('d/m/Y', strtotime($bc->NgayNop)) : '—' }}
                            </span>
                        </td>
                        <td>
                            @if($nx)
                                <div class="text-dark small">
                                    <i class="fa-regular fa-comment-dots text-primary me-1"></i>{{ $nx->NoiDung }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Bởi: {{ $nx->giangVien?->HoTen ?? 'GVHD' }} ({{ $nx->created_at ? $nx->created_at->format('d/m/Y') : '' }})
                                </div>
                            @else
                                <span class="text-muted fst-italic small">Chưa có nhận xét</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($bc->FileBaoCao))
                                <a href="{{ asset('storage/' . ltrim($bc->FileBaoCao, '/')) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-2" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-download me-1"></i>Tải file
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($bc->TrangThai === 'Đạt')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    ✓ Đạt
                                </span>
                            @elseif($bc->TrangThai === 'Chờ duyệt')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    Chờ duyệt
                                </span>
                            @elseif($bc->TrangThai === 'Yêu cầu nộp lại')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    Cần nộp lại
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5">
                                    {{ $bc->TrangThai ?? 'Chưa đánh giá' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                            Nhóm chưa nộp báo cáo tiến độ nào trên hệ thống.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
