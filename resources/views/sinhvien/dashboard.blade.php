@extends('layouts.sinhvien')

@section('page_title', 'Tổng Quan Khóa Luận')

@section('content')
<style>
    /* Bảng màu chuẩn từ người dùng: Midnight Navy, Ocean Cyan, Persian Green, Mint */
    .dashboard-container {
        font-size: 13.5px;
    }
    .kpi-metric-box {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        height: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }
    .kpi-metric-box:hover {
        transform: translateY(-1.5px);
        border-color: #00838C;
        box-shadow: 0 4px 10px -2px rgba(0, 78, 100, 0.08);
    }
    .kpi-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #e6f7fa;
        color: #00838C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .metric-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #52637a;
    }
    .metric-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #003F5F;
        line-height: 1.2;
    }
    .dashboard-card-panel {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 63, 95, 0.04);
        transition: border-color 0.16s ease;
    }
    .panel-header-compact {
        padding: 10px 16px;
        background-color: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-body-compact {
        padding: 14px 16px;
    }
    .progress-bar-cyan {
        background: linear-gradient(90deg, #00838C 0%, #25A18E 100%);
    }
</style>

<div class="dashboard-container">

    {{-- ── 1. TIÊU ĐỀ VÀ THÔNG TIN SINH VIÊN (DẠNG THANH NGANG GỌN GÀNG) ── --}}
    <div class="d-flex justify-content-between align-items-center mb-2.5 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color: #003F5F;">
                <i class="fa-solid fa-chart-pie" style="color: #00838C;"></i>
                <span>Tổng Quan Khóa Luận</span>
                <span class="badge badge-neutral" style="font-size: 12px;">
                    <i class="fa-regular fa-calendar-check me-1" style="color: #00838C;"></i>{{ $hocKyHienTai->TenHocKy ?? 'Học kỳ hiện tại' }}
                </span>
            </h5>
            <div class="text-muted mt-0.5" style="font-size: 13px;">
                Xin chào, <strong style="color: #003F5F;">{{ $sinhVien->HoTen }}</strong> &bull; MSSV: <code>{{ $sinhVien->MaSV }}</code> &bull; Lớp: <strong>{{ $sinhVien->lop->TenLop ?? 'Chưa phân lớp' }}</strong>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fa-solid fa-calendar-day me-1"></i>Lịch báo cáo
            </a>
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fa-solid fa-upload me-1"></i>Nộp báo cáo
            </a>
        </div>
    </div>

    {{-- ── 2. THÔNG BÁO LỜI MỜI NHÓM (NẾU CÓ - DẠNG THANH COMPACT) ── --}}
    @if(isset($loiMois) && $loiMois->count() > 0)
    <div class="alert alert-warning border-warning shadow-xs py-2 px-3 mb-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-envelope-open-text fs-5 text-warning"></i>
            <div>
                <strong class="text-dark">Bạn có {{ $loiMois->count() }} lời mời tham gia nhóm khóa luận:</strong>
                <span class="text-muted" style="font-size: 13px;">
                    Từ Trưởng nhóm <strong>{{ $loiMois->first()->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> (Nhóm: {{ $loiMois->first()->nhom->TenNhom ?? '' }}).
                </span>
            </div>
        </div>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold">
            <i class="fa-solid fa-users me-1"></i>Xem &amp; Phản Hồi
        </a>
    </div>
    @endif

    {{-- ── 3. KHU VỰC 4 THẺ CHỈ SỐ NHANH (KPI METRIC STRIP - 72PX CAO ĐỒNG BỘ) ── --}}
    <div class="row g-2 mb-3">
        <!-- 1. Điều Kiện Khóa Luận -->
        <div class="col-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Điều Kiện Khóa Luận</div>
                    <div class="metric-value mt-0.5">
                        {{ $sinhVien->SoTinChiTichLuy ?? 0 }} <span class="fs-6 text-muted fw-normal">TC</span>
                    </div>
                    <div class="mt-1">
                        @if($isDuDieuKien)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-2">
                                <i class="fa-solid fa-circle-check me-1"></i>Đủ ĐK (GPA: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }})
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill py-0.5 px-2">
                                <i class="fa-solid fa-circle-xmark me-1"></i>Thiếu ({{ $sinhVien->SoTinChiTichLuy ?? 0 }}/115 TC)
                            </span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>

        <!-- 2. Đề Tài Khóa Luận -->
        <div class="col-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Đề Tài Khóa Luận</div>
                    <div class="metric-value mt-0.5 text-truncate" style="max-width: 140px;" title="{{ $deTai ? $deTai->MaDeTai : '' }}">
                        {{ $deTai ? $deTai->MaDeTai : ($nhom ? 'Chờ đăng ký' : 'Chưa có') }}
                    </div>
                    <div class="mt-1">
                        @if($deTai)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-2">
                                <i class="fa-solid fa-check me-1"></i>Đã duyệt
                            </span>
                        @elseif($nhom)
                            <a href="{{ route('sinhvien.dangky.index') }}" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-0.5 px-2 text-decoration-none">
                                <i class="fa-solid fa-arrow-right me-1"></i>Đăng ký
                            </a>
                        @else
                            <a href="{{ route('sinhvien.nhom.index') }}" class="badge badge-neutral rounded-pill py-0.5 px-2 text-decoration-none">
                                <i class="fa-solid fa-user-plus me-1"></i>Vào nhóm
                            </a>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>
        </div>

        <!-- 3. Nhóm Khóa Luận -->
        <div class="col-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Nhóm Khóa Luận</div>
                    <div class="metric-value mt-0.5 text-truncate" style="max-width: 140px;" title="{{ $nhom ? $nhom->TenNhom : '' }}">
                        {{ $nhom ? $nhom->TenNhom : 'Chưa có nhóm' }}
                    </div>
                    <div class="mt-1">
                        @if($nhom)
                            <span class="badge bg-primary-subtle rounded-pill py-0.5 px-2">
                                <i class="fa-solid fa-users me-1"></i>{{ $thanhViens->count() }}/3 Thành viên
                            </span>
                        @else
                            <a href="{{ route('sinhvien.nhom.index') }}" class="badge badge-neutral rounded-pill py-0.5 px-2 text-decoration-none">
                                <i class="fa-solid fa-plus me-1"></i>Tạo nhóm
                            </a>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <!-- 4. Tiến Độ Khóa Luận -->
        <div class="col-6 col-lg-3">
            <div class="kpi-metric-box">
                <div>
                    <div class="metric-title">Tiến Độ Khóa Luận</div>
                    <div class="metric-value mt-0.5" style="color: {{ $nhom ? '#25A18E' : '#64748b' }};">
                        {{ $nhom ? $tienDoPhanTram . '%' : '--' }}
                    </div>
                    <div class="mt-1">
                        @if(!$nhom)
                            <span class="badge badge-neutral rounded-pill py-0.5 px-2">Chưa có nhóm</span>
                        @elseif($ketQua && $ketQua->DiemTongKet > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-2">
                                Điểm: {{ $ketQua->DiemTongKet }} ({{ $ketQua->XepLoai }})
                            </span>
                        @elseif($hoSoBaoVe)
                            <span class="badge bg-primary-subtle rounded-pill py-0.5 px-2">
                                {{ $hoSoBaoVe->TrangThai }}
                            </span>
                        @else
                            <span class="badge badge-neutral rounded-pill py-0.5 px-2">
                                {{ $soBaoCaoDat }}/5 đợt đạt
                            </span>
                        @endif
                    </div>
                </div>
                <div class="kpi-icon-circle">
                    <i class="fa-solid fa-bars-progress"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. BỐ CỤC CHÍNH 2 CỘT TỐI ƯU 1 MÀN HÌNH (BALANCED DUAL-COLUMN) ── --}}
    <div class="row g-2.5">

        {{-- ── CỘT TRÁI (7 COLS): THÔNG TIN ĐỀ TÀI & HÀNH ĐỘNG TIẾP THEO ── --}}
        <div class="col-12 col-lg-7 d-flex flex-column gap-2.5">

            <!-- CARD 1: THÔNG TIN ĐỀ TÀI & HƯỚNG DẪN -->
            <div class="dashboard-card-panel flex-grow-1">
                <div class="panel-header-compact">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        @if($deTai)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                <i class="fa-solid fa-circle-check me-1"></i>Đã Phê Duyệt
                            </span>
                        @elseif($nhom)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">
                                <i class="fa-solid fa-users me-1"></i>Đã Có Nhóm &bull; Chờ Đăng Ký
                            </span>
                        @else
                            <span class="badge badge-neutral rounded-pill">
                                <i class="fa-solid fa-circle-info me-1"></i>Chưa Có Nhóm
                            </span>
                        @endif

                        @if($nhom && $nhom->hocPhan)
                            <span class="badge bg-primary-subtle rounded-pill">
                                <i class="fa-solid fa-book me-1"></i>{{ $nhom->hocPhan->TenHocPhan }}
                            </span>
                        @endif

                        <span class="badge badge-neutral rounded-pill">
                            <i class="fa-solid fa-building-columns me-1" style="color: #00838C;"></i>{{ $sinhVien->lop->nganh->TenNganh ?? 'Công nghệ thông tin' }}
                        </span>
                    </div>

                    <div class="fw-bold" style="color: #003F5F; font-size: 13px;">
                        Tiến độ: <span style="color: #25A18E;">{{ $tienDoPhanTram }}%</span>
                    </div>
                </div>

                <div class="panel-body-compact">
                    <!-- Tên Đề Tài -->
                    <h6 class="fw-bold mb-2 text-truncate-2" style="color: #003F5F; line-height: 1.35; min-height: 38px;">
                        {{ $deTai->TenDeTai ?? ($nhom ? 'Nhóm chưa đăng ký đề tài khóa luận. Vui lòng chọn đề tài trong danh sách mở.' : 'Sinh viên chưa tham gia nhóm. Hãy tạo nhóm mới hoặc tìm kiếm nhóm mở.') }}
                    </h6>

                    <!-- 2 Cột nhỏ: GVHD & Thành Viên -->
                    <div class="row g-2 pt-2 border-top">
                        <!-- Giảng viên hướng dẫn -->
                        <div class="col-12 col-sm-5 border-end-sm">
                            <div class="text-muted fw-semibold mb-0.5" style="font-size: 12px;">
                                <i class="fa-solid fa-chalkboard-user me-1" style="color: #00838C;"></i>GV Hướng dẫn:
                            </div>
                            <div class="fw-bold" style="color: #003F5F; font-size: 13.5px;">
                                @if($gvhd)
                                    {{ ($gvhd->HocHam ? $gvhd->HocHam . '.' : '') . ($gvhd->HocVi ? $gvhd->HocVi . '.' : '') . ' ' . $gvhd->HoTen }}
                                @else
                                    <span class="text-muted fw-normal fst-italic">Chưa phân công</span>
                                @endif
                            </div>
                            <div class="text-muted mt-0.5 text-truncate" style="font-size: 12px;" title="{{ $gvhd->Email ?? '' }}">
                                <i class="fa-regular fa-envelope me-1"></i>{{ $gvhd->Email ?? 'Chưa có email' }}
                            </div>
                        </div>

                        <!-- Thành viên nhóm -->
                        <div class="col-12 col-sm-7">
                            <div class="text-muted fw-semibold mb-1" style="font-size: 12px;">
                                <i class="fa-solid fa-user-group me-1" style="color: #00838C;"></i>Thành viên nhóm (Tối đa 3 SV):
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @if($truongNhom)
                                    <span class="badge bg-primary-subtle rounded-pill py-0.5 px-2" style="font-size: 11.5px;">
                                        👑 {{ $truongNhom->HoTen }} (Trưởng nhóm)
                                    </span>
                                @endif

                                @forelse($thanhViens as $tv)
                                    @if(!$truongNhom || $tv->MaSV !== $truongNhom->MaSV)
                                        <span class="badge badge-neutral rounded-pill py-0.5 px-2" style="font-size: 11.5px;">
                                            {{ $tv->sinhVien->HoTen ?? $tv->MaSV }}
                                        </span>
                                    @endif
                                @empty
                                    @if(!$truongNhom)
                                        <span class="text-muted fst-italic" style="font-size: 12px;">Chưa có thành viên</span>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Thanh Progress Bar Mảnh -->
                    @if($nhom)
                    <div class="mt-2.5 pt-2 border-top">
                        <div class="progress" style="height: 5px; background-color: #f1f5f9;">
                            <div class="progress-bar progress-bar-cyan" role="progressbar" 
                                 style="width: {{ $tienDoPhanTram }}%;"
                                 aria-valuenow="{{ $tienDoPhanTram }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- CARD 2: KẾ HOẠCH & CÔNG VIỆC TIẾP THEO (GỌN GÀNG, HÀNH ĐỘNG NHANH) -->
            <div class="dashboard-card-panel">
                <div class="panel-header-compact">
                    <div class="fw-bold d-flex align-items-center gap-1.5" style="color: #003F5F; font-size: 13.5px;">
                        <i class="fa-solid fa-list-check" style="color: #00838C;"></i>
                        <span>Kế Hoạch &amp; Công Việc Tiếp Theo</span>
                    </div>

                    @if($nhom && $nextMoc)
                        <span class="badge {{ $daysRemaining !== null && $daysRemaining < 0 ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle' }} rounded-pill py-0.5 px-2.5" style="font-size: 12px;">
                            <i class="fa-regular fa-clock me-1"></i>
                            @if($daysRemaining !== null)
                                {{ $daysRemaining >= 0 ? "Còn {$daysRemaining} ngày" : "Quá hạn " . abs($daysRemaining) . " ngày" }}
                            @else
                                Hạn: {{ \Carbon\Carbon::parse($nextMoc->NgayKetThuc)->format('d/m/Y') }}
                            @endif
                        </span>
                    @endif
                </div>

                <div class="panel-body-compact">
                    @if($nhom && $nextMoc)
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold mb-0.5" style="color: #003F5F; font-size: 13.5px;">
                                    {{ $nextMoc->TenMoc ?? ('Báo Cáo Tiến Độ Đợt ' . $mocTiepTheo) }}
                                </div>
                                <div class="text-muted" style="font-size: 12.5px;">
                                    <i class="fa-regular fa-calendar-xmark me-1 text-danger"></i>Hạn chót: <strong>{{ \Carbon\Carbon::parse($nextMoc->NgayKetThuc)->format('d/m/Y') }}</strong>
                                    &bull; Giai đoạn: <span class="badge badge-neutral py-0 px-1.5" style="font-size: 11px;">{{ $nextMoc->LoaiGiaiDoan ?? 'Thực hiện' }}</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                @if(($nextMoc->LoaiGiaiDoan ?? '') === 'TAO_NHOM')
                                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
                                        <i class="fa-solid fa-users me-1"></i>Quản lý nhóm
                                    </a>
                                @elseif(($nextMoc->LoaiGiaiDoan ?? '') === 'DANG_KY_DE_TAI')
                                    <a href="{{ route('sinhvien.dangky.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
                                        <i class="fa-solid fa-book-bookmark me-1"></i>Đăng ký đề tài
                                    </a>
                                @elseif(in_array($nextMoc->LoaiGiaiDoan ?? '', ['THUC_HIEN', 'NOP_BAO_CAO']))
                                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
                                        <i class="fa-solid fa-upload me-1"></i>Nộp báo cáo ngay
                                    </a>
                                @else
                                    <a href="{{ route('sinhvien.calendar') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs">
                                        <i class="fa-solid fa-calendar-check me-1"></i>Xem kế hoạch
                                    </a>
                                @endif
                                <a href="{{ route('sinhvien.calendar') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-calendar-week me-1"></i>Lịch
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold mb-0.5" style="color: #003F5F;">Chưa có nhóm khóa luận</div>
                                <div class="text-muted" style="font-size: 12.5px;">Thời hạn nộp báo cáo sẽ kích hoạt sau khi bạn tạo hoặc gia nhập nhóm.</div>
                            </div>
                            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-users me-1"></i>Gia nhập nhóm
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── CỘT PHẢI (5 COLS): NHẬN XÉT GVHD & LỊCH HẸN BẢO VỆ ── --}}
        <div class="col-12 col-lg-5">
            <div class="dashboard-card-panel h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="panel-header-compact">
                        <div class="fw-bold d-flex align-items-center gap-1.5" style="color: #003F5F; font-size: 13.5px;">
                            <i class="fa-solid fa-comments" style="color: #00838C;"></i>
                            <span>Hoạt Động &amp; Nhận Xét GVHD</span>
                        </div>
                        <a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none fw-semibold" style="font-size: 12.5px; color: #00838C;">
                            Xem tất cả &rarr;
                        </a>
                    </div>

                    <div class="panel-body-compact">
                        <!-- PHẦN 1: NHẬN XÉT MỚI NHẤT -->
                        <div class="p-2.5 rounded-3 border border-slate-200 mb-2.5" style="background-color: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="badge bg-primary-subtle rounded-pill py-0.5 px-2" style="font-size: 11px;">
                                    {{ $latestFeedback ? 'Đợt ' . $latestFeedback->LanBaoCao . ': ' . Str::limit($latestFeedback->TieuDe, 24) : 'Phản hồi mới nhất' }}
                                </span>
                                @if($latestFeedback && $latestFeedback->TrangThai === 'Đạt')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-2" style="font-size: 11px;">
                                        <i class="fa-solid fa-check me-1"></i>Đạt
                                    </span>
                                @elseif($latestFeedback)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-0.5 px-2" style="font-size: 11px;">
                                        {{ $latestFeedback->TrangThai }}
                                    </span>
                                @endif
                            </div>

                            @if($latestFeedback && $latestFeedback->NhanXet)
                                <div class="text-dark fst-italic" style="font-size: 13px; line-height: 1.45;">
                                    "{!! Str::limit($latestFeedback->NhanXet, 120) !!}"
                                </div>
                                <div class="text-muted mt-1.5 d-flex justify-content-between align-items-center" style="font-size: 11.5px;">
                                    <span><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($latestFeedback->updated_at)->format('H:i - d/m/Y') }}</span>
                                    @if($latestFeedback->DiemSo)
                                        <span class="fw-bold" style="color: #00838C;">Điểm: {{ $latestFeedback->DiemSo }}/10</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted text-center py-2" style="font-size: 12.5px;">
                                    <i class="fa-regular fa-comment-dots text-secondary me-1"></i>Chưa có nhận xét mới từ Giảng viên hướng dẫn.
                                </div>
                            @endif
                        </div>

                        <!-- PHẦN 2: LỊCH HẸN GẶP & LỊCH HỘI ĐỒNG BẢO VỆ (KHUNG CUỘN MƯỢT TỐI ĐA 150PX) -->
                        <div class="fw-semibold mb-1 d-flex align-items-center justify-content-between" style="font-size: 12.5px; color: #003F5F;">
                            <span><i class="fa-regular fa-calendar-check me-1" style="color: #25A18E;"></i>Lịch hẹn gặp &amp; Bảo vệ Hội đồng:</span>
                            <a href="{{ route('sinhvien.calendar') }}" class="text-muted text-decoration-none" style="font-size: 11.5px;">Lịch biểu &rarr;</a>
                        </div>

                        <div class="sv-smooth-scroll" style="max-height: 145px; overflow-y: auto; padding-right: 4px;">
                            <!-- Lịch Hội Đồng Bảo Vệ (Nếu có) -->
                            @if($hoSoBaoVe && $hoSoBaoVe->hoiDong)
                                <div class="p-2 rounded-2 border mb-1.5" style="background-color: #e6f7fa; border-color: #b3e5ed !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-0.5">
                                        <strong style="color: #004E64; font-size: 12.5px;">
                                            <i class="fa-solid fa-building-columns me-1"></i>{{ $hoSoBaoVe->hoiDong->TenHoiDong }}
                                        </strong>
                                        <span class="badge bg-success rounded-pill py-0.5 px-2" style="font-size: 11px;">Bảo Vệ</span>
                                    </div>
                                    <div class="text-dark" style="font-size: 12px;">
                                        <span><i class="fa-regular fa-clock me-1 text-primary"></i>{{ $hoSoBaoVe->hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoSoBaoVe->hoiDong->ThoiGianBatDau)->format('H:i - d/m/Y') : 'Chưa xếp lịch' }}</span>
                                        &bull; <span>Phòng: <strong>{{ $hoSoBaoVe->hoiDong->DiaDiem ?? $hoSoBaoVe->PhongBaoVe ?? 'Chưa công bố' }}</strong></span>
                                    </div>
                                </div>
                            @endif

                            <!-- Lịch Hướng Dẫn Sắp Tới -->
                            @forelse($lichGaps as $lg)
                                <div class="d-flex align-items-center gap-2 py-1.5 {{ !$loop->last ? 'border-bottom' : '' }}" style="font-size: 12.5px;">
                                    <span class="badge badge-neutral py-1 px-1.5 rounded text-center" style="min-width: 42px; font-size: 11px;">
                                        {{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->format('d/m') }}
                                    </span>
                                    <div class="flex-grow-1 text-truncate">
                                        <div class="fw-semibold text-dark text-truncate">{{ $lg->NoiDung ?? 'Trao đổi tiến độ đề tài' }}</div>
                                        <div class="text-muted text-truncate" style="font-size: 11.5px;">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $lg->DiaDiem ?? 'Bộ môn' }} &bull; {{ \Carbon\Carbon::parse($lg->ThoiGianBatDau)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                @if(!$hoSoBaoVe || !$hoSoBaoVe->hoiDong)
                                    <div class="text-muted text-center py-2" style="font-size: 12px;">
                                        <i class="fa-solid fa-info-circle me-1" style="color: #00838C;"></i>Chưa có lịch hẹn gặp mới với GVHD.
                                    </div>
                                @endif
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="panel-header-compact border-top bg-light" style="border-bottom: none; border-radius: 0 0 12px 12px;">
                    <a href="{{ route('sinhvien.hoso.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 w-50 justify-content-center">
                        <i class="fa-solid fa-file-circle-check me-1"></i>Hồ sơ bảo vệ
                    </a>
                    <a href="{{ route('sinhvien.calendar') }}" class="btn btn-primary btn-sm rounded-pill px-3 w-50 justify-content-center">
                        <i class="fa-solid fa-calendar-days me-1"></i>Xem toàn bộ lịch
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
