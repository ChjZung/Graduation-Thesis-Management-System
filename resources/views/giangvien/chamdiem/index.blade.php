@extends('layouts.giangvien')

@section('page_title', 'Phiếu Chấm Điểm Khóa Luận Tốt Nghiệp — HUIT')

@push('styles')
<style>
@media print {
    /* 1. Ẩn hoàn toàn tất cả thành phần giao diện web */
    body, html {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    #sidebar, .sidebar-header, header, nav, .navbar, .alert, .btn, .dropdown,
    .modal-backdrop, .modal-header, .modal-footer,
    #main-content > *:not(#modalXemTruoc),
    .container-fluid > *:not(#modalXemTruoc),
    .time-machine-container, footer, .footer {
        display: none !important;
    }

    /* 2. Chỉ hiển thị duy nhất tờ giấy A4 chuẩn của modalXemTruoc */
    #modalXemTruoc {
        display: block !important;
        position: static !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    #modalXemTruoc .modal-dialog {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: none !important;
    }
    #modalXemTruoc .modal-content {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    #modalXemTruoc .modal-body {
        padding: 0 !important;
        background: #fff !important;
    }
    #printPaperA4 {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        display: block !important;
        visibility: visible !important;
    }
    #printPaperA4 * {
        visibility: visible !important;
    }
    @page {
        size: A4 portrait;
        margin: 12mm 15mm 15mm 15mm;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    @if($hoiDongs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-secondary">Bạn chưa được phân công vào Hội đồng bảo vệ nào</h5>
            <p class="text-muted small mb-0">Khi Trưởng khoa thành lập Hội đồng và thêm Thầy/Cô vào danh sách thành viên, các ca bảo vệ sẽ hiển thị tại đây.</p>
        </div>
    </div>
    @else

    @php
        $activeNhom = $activeHoSo?->nhom;
        $activeDeTai = $activeNhom?->deTai;
        $activeThanhViens = $activeNhom?->thanhViens ?? collect();
        $sinhViens = $activeThanhViens->pluck('sinhVien')->filter();

        // Vai trò của giảng viên trong hội đồng này
        $tvRecord = $activeHoiDong?->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTroGV = $tvRecord?->VaiTro ?? 'Thành viên Hội đồng';

        $isKLKS = ($loaiKhoaLuan === 'KLKS');
        $rubric = $isKLKS ? $rubricKLKS : $rubricKLCN;
        $tenBieuMau = $isKLKS ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN';
        $coThongTinChucNang = !empty(trim($activeHoSo?->TieuChiChucNang ?? ''));
    @endphp

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: #00305a;">
                <i class="fa-solid fa-file-signature text-primary me-2"></i>{{ $tenBieuMau }}
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Toggle chọn mẫu KLCN hoặc KLKS -->
            <div class="btn-group btn-group-sm shadow-xs" role="group">
                <a href="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLCN']) }}" 
                   class="btn {{ !$isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-graduation-cap me-2"></i> Mẫu Cử Nhân (KLCN)
                </a>
                <a href="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLKS']) }}" 
                   class="btn {{ $isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-microchip me-2"></i> Mẫu Kỹ Sư (KLKS)
                </a>
            </div>

            <!-- In & Xuất Phiếu Chuẩn Khoa CNTT -->
            <div class="btn-group btn-group-sm shadow-xs" role="group">
                @if($coThongTinChucNang)
                    <a href="{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" 
                       target="_blank" 
                       class="btn btn-danger fw-bold d-inline-flex align-items-center"
                       title="Mở và in phiếu điểm PDF chuẩn mẫu của Khoa">
                        <i class="fa-solid fa-print me-1 text-white"></i> In Phiếu (PDF)
                    </a>
                @else
                    <button type="button" 
                            onclick="alertChuaCoChucNang()" 
                            class="btn btn-outline-secondary text-muted fw-semibold d-inline-flex align-items-center"
                            title="Chưa thể in phiếu do chưa có cấu hình mục 6">
                        <i class="fa-solid fa-print me-1"></i> In Phiếu <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Chưa có mục 6</span>
                    </button>
                @endif
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Tùy chọn in/xuất</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2" style="min-width: 255px;">
                    <li class="dropdown-header text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">
                        Biểu mẫu chuẩn Khoa CNTT
                    </li>
                    @if($coThongTinChucNang)
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" target="_blank">
                                <i class="fa-solid fa-file-pdf text-danger me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">In Phiếu Chuẩn (PDF)</div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">Mẫu chuẩn Khoa có Logo, A4, chữ ký</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center" href="{{ route('giangvien.chamdiem.xuatWord', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}">
                                <i class="fa-solid fa-file-word text-primary me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">Xuất File Word (.docx)</div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">Theo đúng file mẫu Word của Khoa</div>
                                </div>
                            </a>
                        </li>
                    @else
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center text-muted" href="javascript:void(0)" onclick="alertChuaCoChucNang()">
                                <i class="fa-solid fa-file-pdf text-muted me-2 fs-5"></i>
                                <div>
                                    <div class="fw-semibold">In Phiếu Chuẩn (PDF)</div>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Chưa có mục 6</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center text-muted" href="javascript:void(0)" onclick="alertChuaCoChucNang()">
                                <i class="fa-solid fa-file-word text-muted me-2 fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Xuất File Word (.docx)</div>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Chưa có mục 6</span>
                                </div>
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalXemTruoc">
                            <i class="fa-solid fa-eye text-primary me-2 fs-5"></i>
                            <div>
                                <div class="fw-semibold">Xem Trước Bản In A4</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">Xem trang giấy Word trực quan</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @if(!empty($canhBaoChuaXacDinhLoai))
    <div class="alert alert-warning border-warning rounded-3 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
        <div class="small">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i>
            <strong>Cảnh báo phân loại khóa luận:</strong> Hệ thống chưa tự động nhận diện được loại khóa luận (Cử nhân hay Kỹ sư) từ tên học phần hoặc ngành của nhóm này. Vui lòng kiểm tra lại với Trưởng khoa trước khi chốt phiếu điểm.
        </div>
    </div>
    @endif

    @if($daKhoaPhieu)
    <div class="alert alert-success border-success rounded-3 shadow-sm py-3 px-4 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-4 text-success"></i>
            <div>
                <strong class="d-block text-success-emphasis">Phiếu chấm điểm đã được KÝ XÁC NHẬN & KHÓA</strong>
                <span class="small text-secondary">
                    Thời điểm ký: <strong>{{ \Carbon\Carbon::parse($phieuCu->NgayXacNhan ?? $phieuCu->updated_at)->format('d/m/Y H:i') }}</strong>. 
                    Dữ liệu đã được bảo đảm toàn vẹn và chuyển sang bảng tổng hợp điểm sơ bộ của Hội đồng.
                </span>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalYeuCauMoKhoa">
            <i class="fa-solid fa-unlock-keyhole me-2"></i> Yêu Cầu Mở Khóa Chỉnh Sửa
        </button>
    </div>
    @endif

    <!-- Thanh Bộ Chọn Hội Đồng & Chọn Nhóm Bảo Vệ -->
    <div class="admin-filter-bar mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-landmark text-primary me-2"></i> Hội Đồng Bảo Vệ:
                </label>
                <select class="form-select form-select-sm" onchange="location = this.value;">
                    @foreach($hoiDongs as $hd)
                        <option value="{{ route('giangvien.chamdiem.index', ['hd' => $hd->MaHoiDong, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoiDong && $hd->MaHoiDong === $activeHoiDong->MaHoiDong ? 'selected' : '' }}>
                            {{ $hd->TenHoiDong }} ({{ $hd->DiaDiem ?? 'Phòng B.304' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 col-lg-5">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-users-rectangle text-success me-2"></i> Nhóm Sinh Viên Bảo Vệ:
                </label>
                <select class="form-select form-select-sm" onchange="location = this.value;">
                    @forelse($activeHoiDong->hoSoBaoVes as $hs)
                        <option value="{{ route('giangvien.chamdiem.index', ['hd' => $activeHoiDong->MaHoiDong, 'hoso' => $hs->MaHoSo, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoSo && $hs->MaHoSo === $activeHoSo->MaHoSo ? 'selected' : '' }}>
                            {{ $hs->nhom->TenNhom ?? $hs->MaHoSo }} — {{ Str::limit($hs->nhom->deTai->TenDeTai ?? 'Chưa có tên đề tài', 45) }}
                        </option>
                    @empty
                        <option value="">Chưa có nhóm nào trong hội đồng này</option>
                    @endforelse
                </select>
            </div>

            <div class="col-md-12 col-lg-2 text-lg-end pt-lg-3">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                    <i class="fa-solid fa-user-check me-2"></i>{{ $vaiTroGV }}
                </span>
            </div>
        </div>
    </div>

    @if(!$activeHoSo)
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
        <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
        <h6>Hội đồng này hiện chưa có nhóm sinh viên nào được phân công bảo vệ.</h6>
    </div>
    @else

    <!-- Card Thông Tin Phiếu Chấm Điểm Chuẩn Văn Bản Khoa CNTT -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <div class="text-center">
                <div class="text-uppercase fw-bold text-secondary small" style="letter-spacing: 1px;">KHOA CÔNG NGHỆ THÔNG TIN &bull; TRƯỜNG ĐH CÔNG THƯƠNG TP.HCM</div>
                <h5 class="fw-bold mb-1 text-primary mt-1">{{ $tenBieuMau }}</h5>
                <div class="text-muted small fst-italic">(Hướng ứng dụng — Thang điểm 10.0)</div>
            </div>
        </div>
        <div class="card-body p-4 bg-light-subtle">
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-2 bg-white rounded-3 border">
                        <span class="fw-bold text-dark">Tên đề tài:</span>
                        <span class="text-primary fw-semibold fs-6 ms-1">{{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</span>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Giảng viên hướng dẫn:</div>
                        <div class="fw-bold text-dark mt-1">{{ $activeDeTai->giangVien->HoTen ?? 'Chưa phân công' }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Hội đồng:</div>
                        <div class="fw-bold text-dark mt-1">{{ $activeHoiDong->TenHoiDong }}</div>
                        <div class="text-muted small mt-2">Phòng bảo vệ:</div>
                        <div class="fw-bold text-primary mt-1">
                            <i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Chưa xếp phòng') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Ngày chấm:</div>
                        <div class="fw-bold text-dark mt-1">{{ \Carbon\Carbon::parse($activeHoSo->ThoiGianBaoVe ?? ($activeHoiDong->ThoiGianBatDau ?? now()))->format('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="p-2 bg-white rounded-3 border h-100">
                        <div class="text-muted small">Giám khảo chấm điểm:</div>
                        <div class="fw-bold text-dark mt-1">{{ $giangVien->HoTen }} ({{ $vaiTroGV }})</div>
                    </div>
                </div>

                <!-- Danh sách sinh viên thực hiện đề tài (SV1, SV2, SV3) -->
                <div class="col-12">
                    <div class="p-3 bg-white rounded-3 border">
                        <div class="fw-bold text-dark small mb-2">
                            <i class="fa-solid fa-user-graduate text-primary me-2"></i> Danh Sách Nhóm Sinh Viên Thực Hiện:
                        </div>
                        <div class="row g-2">
                            @forelse($sinhViens as $idx => $sv)
                            <div class="col-md-4">
                                <div class="p-2 bg-light rounded-2 border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary text-white">SV{{ $idx + 1 }}</span>
                                        <span class="badge-code">{{ $sv->MaSoSinhVien ?? $sv->MaSV }}</span>
                                    </div>
                                    <div class="fw-bold text-dark mt-1">{{ $sv->HoTen }}</div>
                                    <div class="text-muted small">Lớp: {{ $sv->lop->TenLop ?? '14DHTH01' }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-muted small fst-italic">Chưa có danh sách sinh viên trong nhóm.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM NHẬP ĐIỂM THEO RUBRIC CHUẨN KHOA CNTT -->
    <form id="formChamDiem" action="{{ route('giangvien.chamdiem.store') }}" method="POST">
        @csrf
        <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong }}">
        <input type="hidden" name="MaHoSo" value="{{ $activeHoSo->MaHoSo }}">
        <input type="hidden" name="LoaiKhoaLuan" value="{{ $loaiKhoaLuan }}">
        <input type="hidden" name="action_type" id="inputActionType" value="xac_nhan_hoan_thanh">
        <input type="hidden" name="chu_ky" id="inputChuKy" value="{{ $chuKyHienTai }}">

        <!-- Bảng Rubric Tiêu Chí Đánh Giá -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Bảng Tiêu Chí Đánh Giá &amp; Điểm Chấm Cho Từng Sinh Viên (Thang 10.0)
                </span>
                <div class="d-flex align-items-center gap-2">
                    @if($isChuTich || (Auth::user() && (Auth::user()->isTruongKhoa() || Auth::user()->isAdmin())))
                    <button type="button" class="btn btn-xs btn-outline-warning text-dark fw-bold rounded-pill px-3 py-1 d-inline-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCaiDatChucNang" style="font-size: 0.78rem; background: #fff8e6; border-color: #f59e0b;">
                        <i class="fa-solid fa-pen-to-square text-warning-emphasis me-1"></i> Cài đặt chức năng theo yêu cầu bài toán (Chủ tịch)
                    </button>
                    @elseif(!empty($customChucNang))
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-check-double me-1"></i> Đã đồng bộ yêu cầu bài toán từ Chủ tịch HĐ
                    </span>
                    @endif
                    @if(!$daKhoaPhieu)
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center" onclick="datDiemVeKhong()" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-rotate-left me-1"></i> Đặt về 0
                    </button>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-primary text-center">
                            <tr>
                                <th rowspan="2" class="text-center align-middle" style="width: 45px; background: #0072ce; color: white;">STT</th>
                                <th rowspan="2" class="text-start align-middle" style="min-width: 340px; background: #0072ce; color: white;">NỘI DUNG ĐÁNH GIÁ</th>
                                <th rowspan="2" class="text-center align-middle" style="width: 80px; background: #0072ce; color: white;">CLO</th>
                                <th rowspan="2" class="text-center align-middle" style="width: 100px; background: #0072ce; color: white;">ĐIỂM TỐI ĐA</th>
                                <th colspan="{{ max(1, $sinhViens->count()) }}" class="text-center py-2" style="background: #005bb5; color: white;">ĐIỂM CHẤM</th>
                            </tr>
                            <tr>
                                @foreach($sinhViens as $idx => $sv)
                                <th class="text-center py-1" style="min-width: 130px; background: #00569e; color: white;">
                                    <div class="fw-bold">SV{{ $idx + 1 }}</div>
                                    <div class="text-white small fw-normal text-truncate" style="max-width: 140px;" title="{{ $sv->HoTen }}">
                                        {{ $sv->HoTen }}
                                    </div>
                                    <div class="text-white-50" style="font-size: 0.7rem;">{{ $sv->MaSV }}</div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($rubric as $criterion)
                            @php
                                $isBonus = !empty($criterion['is_bonus']);
                                $cId = $criterion['id'];
                                $cMax = (float)$criterion['max'];
                                $lines = explode("\n", $criterion['noi_dung']);
                            @endphp
                            <tr class="{{ $isBonus ? 'table-warning-subtle' : '' }}">
                                <td class="text-center align-top py-2 fw-bold {{ $isBonus ? 'text-warning-emphasis' : 'text-secondary' }}">
                                    {{ $criterion['stt'] }}
                                </td>
                                <td class="align-top py-2">
                                    @if($cId === 'tc7')
                                        <div class="fw-bold text-dark mb-2">
                                            <i class="fa-solid fa-laptop-code text-primary me-1"></i> Xây dựng các chức năng cơ bản &amp; Cài đặt chức năng theo yêu cầu bài toán
                                        </div>
                                        <div class="p-2 mb-2 bg-light rounded border border-secondary-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-semibold text-dark small">
                                                    <span class="badge bg-secondary text-white font-monospace me-1">CB</span> 1. Chức năng cơ bản ứng dụng:
                                                </span>
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">0.50 điểm</span>
                                            </div>
                                            <div class="ps-2 text-dark small" style="line-height: 1.45;">
                                                &bull; Đăng nhập, đăng xuất, đổi mật khẩu, phân quyền, backup &amp; restore,...
                                            </div>
                                        </div>

                                        <div class="p-2 bg-primary-subtle rounded border border-primary-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-semibold text-primary small">
                                                    <i class="fa-solid fa-sliders text-primary me-1"></i> 2. Chức năng theo yêu cầu bài toán:
                                                </span>
                                                <span class="badge bg-primary text-white" style="font-size: 0.65rem;">{{ $loaiKhoaLuan === 'KLKS' ? '3.50đ (0.50đ/YC)' : '3.50đ (0.50đ/YC)' }}</span>
                                            </div>

                                            @if(!empty($functionalRequirements))
                                                @php
                                                    $groupedReqs = collect($functionalRequirements)->groupBy('group');
                                                    $ycCounter = 1;
                                                @endphp
                                                <div class="space-y-1">
                                                    @foreach($groupedReqs as $groupName => $reqItems)
                                                        @if($groupName !== 'Chức năng cơ bản')
                                                        <div class="mt-1 pt-1 border-top border-primary-subtle">
                                                            <div class="fw-bold text-dark mb-1" style="font-size: 0.78rem;">
                                                                &bull; {{ $groupName }}:
                                                            </div>
                                                            @foreach($reqItems as $r)
                                                            @if($r['id'] !== 'tc7_co_ban')
                                                            <div class="ps-2 py-0.5 d-flex align-items-baseline gap-1" style="font-size: 0.75rem; line-height: 1.4;">
                                                                <span class="badge bg-primary text-white font-monospace" style="font-size: 0.62rem; min-width: 32px;">YC{{ $ycCounter++ }}</span>
                                                                <span class="text-dark">{{ $r['label'] }}</span>
                                                                <span class="text-muted ms-auto text-nowrap" style="font-size: 0.65rem;">(0.50đ)</span>
                                                            </div>
                                                            @endif
                                                            @endforeach
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-1">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-lock me-1"></i> Cấu hình riêng cho Đề tài: {{ Str::limit($activeDeTai->TenDeTai ?? '', 45) }}
                                            </span>
                                            @if($isChuTich)
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#modalCaiDatChucNang" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa chức năng
                                            </button>
                                            @endif
                                        </div>
                                    @else
                                        @foreach($lines as $lIdx => $line)
                                            @php
                                                $trimmed = trim($line);
                                                $isSubSubBullet = str_starts_with($trimmed, '*');
                                                $isSubBullet = str_starts_with($trimmed, '+');
                                                $isBullet = str_starts_with($trimmed, '-');
                                            @endphp
                                            @if($isSubSubBullet)
                                                <div class="ps-4 text-secondary small" style="line-height: 1.45;">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                                            @elseif($isSubBullet)
                                                <div class="ps-3 text-secondary small fw-semibold" style="line-height: 1.45;">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                                            @elseif($isBullet)
                                                <div class="ps-2 text-dark small" style="line-height: 1.45;">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                                            @else
                                                <div class="{{ $lIdx === 0 ? 'fw-bold text-dark' : 'text-dark small' }}" style="line-height: 1.45;">{{ $trimmed }}</div>
                                            @endif
                                        @endforeach
                                        @if($isBonus)
                                            <div class="badge bg-warning text-dark mt-1" style="font-size: 0.68rem;">Điểm cộng khuyến khích (NCKH / Bài báo)</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center align-top py-2">
                                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">{{ $criterion['clo'] }}</span>
                                </td>
                                <td class="text-center align-top py-2" style="width: 110px;">
                                    {{-- Khung Điểm Tổng --}}
                                    <div class="px-2 py-1 rounded {{ $isBonus ? 'bg-warning-subtle text-warning-emphasis border border-warning' : 'bg-primary-subtle text-primary border border-primary-subtle' }} fw-bold mb-1 text-center shadow-sm">
                                        <span class="d-block text-secondary" style="font-size: 0.65rem; text-transform: uppercase;">Điểm tổng</span>
                                        <span class="fs-6 lh-1">{{ number_format($cMax, 2) }}</span>
                                    </div>

                                    {{-- Khung Điểm Chi Tiết Các Dòng --}}
                                    @if(isset($criterion['diem_toi_da_lines']) && is_array($criterion['diem_toi_da_lines']))
                                    <div class="p-1 rounded bg-white border border-secondary-subtle text-center">
                                        <span class="d-block text-muted fw-semibold" style="font-size: 0.62rem; border-bottom: 1px dashed #dee2e6; padding-bottom: 2px; margin-bottom: 2px; text-transform: uppercase;">Chi tiết</span>
                                        @foreach($criterion['diem_toi_da_lines'] as $dLine)
                                            <div class="text-secondary fw-semibold lh-sm py-0.5" style="font-size: 0.75rem;">{{ $dLine }}</div>
                                        @endforeach
                                    </div>
                                    @endif
                                </td>

                                {{-- Cột điểm cho từng sinh viên --}}
                                @foreach($sinhViens as $sIdx => $sv)
                                @php
                                    $daChamSV = $diemDaCham->get($sv->MaSV);
                                    $svChiTiet = $daChamSV?->ChiTietDiem;
                                    $oldScore = isset($svChiTiet[$cId]) ? (float)$svChiTiet[$cId] : 0.0;
                                @endphp
                                <td class="text-center p-2 align-top" style="background: #f8fbff; min-width: 100px; max-width: 120px;">
                                    @if($cId === 'tc7' && !empty($functionalRequirements))
                                        {{-- Khung Điểm Tổng TC7 --}}
                                        <div class="mb-2">
                                            <div class="text-primary fw-bold" style="font-size: 0.65rem; text-transform: uppercase;">
                                                Tổng TC7
                                            </div>
                                            <input type="number" 
                                                   step="0.05" 
                                                   min="0" 
                                                   max="{{ $cMax }}" 
                                                   name="diems[{{ $sv->MaSV }}][ChiTietDiem][{{ $cId }}]" 
                                                   id="input_tc7_{{ $sv->MaSV }}" 
                                                   data-max="{{ $cMax }}" 
                                                   data-sv="{{ $sv->MaSV }}" 
                                                   data-bonus="0" 
                                                   class="form-control form-control-sm text-center fw-bold text-primary score-criterion score-sv-{{ $sv->MaSV }} mx-auto" 
                                                   style="max-width: 85px; font-size: 0.95rem; border-color: #0072ce;" 
                                                   value="{{ number_format((float)$oldScore, 2) }}" 
                                                   {{ $daKhoaPhieu ? 'readonly' : '' }}
                                                   required>
                                        </div>

                                        {{-- Các ô chấm điểm từng yêu cầu (Chỉ hiển thị ô chấm kèm mã ngắn, không có văn bản mô tả, không tràn màn hình) --}}
                                        <div class="d-flex flex-column gap-1 mx-auto" style="max-width: 85px;">
                                            @php $ycIndex = 1; @endphp
                                            @foreach($functionalRequirements as $req)
                                            @php
                                                $subKey = $req['id'];
                                                $subScore = isset($svChiTiet[$subKey]) ? (float)$svChiTiet[$subKey] : 0.0;
                                                $isCB = ($subKey === 'tc7_co_ban');
                                                $codeTag = $isCB ? 'CB' : 'YC' . ($ycIndex++);
                                            @endphp
                                            <div class="input-group input-group-sm" title="{{ $isCB ? 'Chức năng cơ bản' : $req['label'] }} (Tối đa {{ $req['max'] }}đ)">
                                                <span class="input-group-text py-0 px-1 bg-light text-secondary font-monospace" style="font-size: 0.65rem; width: 32px; justify-content: center; padding-left: 2px; padding-right: 2px;">
                                                    {{ $codeTag }}
                                                </span>
                                                <input type="number" 
                                                       step="0.05" 
                                                       min="0" 
                                                       max="{{ $req['max'] }}" 
                                                       name="diems[{{ $sv->MaSV }}][ChiTietDiem][{{ $subKey }}]" 
                                                       value="{{ number_format($subScore, 2) }}" 
                                                       data-sv="{{ $sv->MaSV }}" 
                                                       data-req-max="{{ $req['max'] }}" 
                                                       class="form-control form-control-sm text-center fw-bold text-dark sub-req-score sub-req-sv-{{ $sv->MaSV }} p-0" 
                                                       style="font-size: 0.75rem;" 
                                                       {{ $daKhoaPhieu ? 'readonly' : '' }}>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <input type="number" 
                                               step="0.05" 
                                               min="0" 
                                               max="{{ $cMax }}" 
                                               name="diems[{{ $sv->MaSV }}][ChiTietDiem][{{ $cId }}]" 
                                               data-max="{{ $cMax }}" 
                                               data-sv="{{ $sv->MaSV }}" 
                                               data-bonus="{{ $isBonus ? '1' : '0' }}" 
                                               class="form-control form-control-sm text-center fw-bold text-primary score-criterion score-sv-{{ $sv->MaSV }} mx-auto" 
                                               style="max-width: 85px;" 
                                               value="{{ number_format((float)$oldScore, 2) }}" 
                                               {{ $daKhoaPhieu ? 'readonly' : '' }}
                                               required>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach

                            {{-- Hàng TỔNG CỘNG --}}
                            <tr style="background: #e6f2ff; border-top: 2px solid #0072ce;">
                                <td colspan="3" class="text-end fw-bold text-dark py-3 fs-6">
                                    <i class="fa-solid fa-calculator text-primary me-2"></i>TỔNG CỘNG (Thang Điểm 10.0):
                                </td>
                                <td class="text-center fw-bold text-primary py-3 fs-6">
                                    10.00
                                </td>
                                @foreach($sinhViens as $sIdx => $sv)
                                @php
                                    $daChamSV = $diemDaCham->get($sv->MaSV);
                                    $curDiem = $daChamSV ? (float)$daChamSV->Diem : 0.0;
                                @endphp
                                <td class="text-center py-3" style="background: #dbeafe;">
                                    <div class="fs-5 fw-bold text-primary" id="totalScore_{{ $sv->MaSV }}">
                                        {{ number_format($curDiem, 2) }}
                                    </div>
                                    <input type="hidden" name="diems[{{ $sv->MaSV }}][Diem]" id="inputTotalScore_{{ $sv->MaSV }}" value="{{ number_format($curDiem, 2) }}">
                                    <div class="small fw-semibold {{ $curDiem > 0 ? 'text-success' : 'text-muted' }} mt-1" id="badgeGrade_{{ $sv->MaSV }}">
                                        {{ $curDiem > 0 ? 'Điểm chữ: A+ (4.0)' : 'Chưa chấm điểm' }}
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Nhận xét của Giám khảo & Thẻ Tổng Hợp Điểm Realtime -->
        <div class="row g-4 mb-4">
            <!-- Cột trái: Nhận xét chi tiết của Giám khảo -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <span class="fw-bold text-dark">
                            <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Nhận Xét Chi Tiết Của Giám Khảo Chấm Điểm
                        </span>
                    </div>
                    <div class="card-body p-3">
                        @foreach($sinhViens as $idx => $sv)
                        @php
                            $daChamSV = $diemDaCham->get($sv->MaSV);
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-user me-2 text-primary"></i> Nhận xét cho SV{{ $idx + 1 }}: <strong>{{ $sv->HoTen }}</strong> ({{ $sv->MaSoSinhVien ?? $sv->MaSV }})</span>
                            </label>
                            <textarea name="diems[{{ $sv->MaSV }}][NhanXet]" rows="3" class="form-control" style="font-size: 0.88rem;"
                                {{ $daKhoaPhieu ? 'readonly' : '' }}
                                placeholder="Nhập nhận xét về mức độ đóng góp, tính chủ động, khả năng trả lời phản biện của sinh viên...">{{ $daChamSV?->NhanXet ?? '' }}</textarea>
                        </div>
                        @endforeach

                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 border-0 bg-info-subtle text-info-emphasis">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <strong>Lưu ý:</strong> Điểm của Thầy/Cô sau khi Ký xác nhận sẽ được tổng hợp tự động vào bảng điểm trung bình Hội đồng và gửi báo cáo về Trưởng khoa để chuẩn bị công bố chính thức.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Bảng Thống Kê Tổng Kết Học Lực Realtime -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <span class="fw-bold text-dark">
                            <i class="fa-solid fa-award text-primary me-2"></i>Tổng Hợp Kết Quả Đánh Giá Realtime
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            @foreach($sinhViens as $idx => $sv)
                            @php
                                $daChamSV = $diemDaCham->get($sv->MaSV);
                                $curDiem = $daChamSV ? (float)$daChamSV->Diem : 0.0;
                            @endphp
                            <div class="p-3 rounded-3 border bg-light-subtle" id="cardSummary_{{ $sv->MaSV }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary text-white">SV{{ $idx + 1 }}</span>
                                        <strong class="text-dark ms-1">{{ $sv->HoTen }}</strong>
                                        <div class="small text-muted">{{ $sv->MaSoSinhVien ?? $sv->MaSV }} &bull; {{ $sv->lop->TenLop ?? 'Lớp' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-4 fw-bold {{ $curDiem > 0 ? 'text-primary' : 'text-muted' }} lh-1" id="displaySummaryScore_{{ $sv->MaSV }}">{{ number_format($curDiem, 2) }}</span>
                                        <div class="small text-muted">/ 10.0</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="badge {{ $curDiem > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill px-3 py-1" id="badgeSummaryResult_{{ $sv->MaSV }}">
                                        {{ $curDiem > 0 ? 'ĐÃ CHẤM' : 'Chưa chấm điểm' }}
                                    </span>
                                    <span class="small fw-semibold text-secondary" id="badgeSummaryScale_{{ $sv->MaSV }}">
                                        {{ $curDiem > 0 ? 'Điểm hệ 10' : 'Chưa có điểm' }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="p-3 bg-warning-subtle border border-warning-subtle rounded-3 mt-3">
                            <div class="small fw-bold text-warning-emphasis mb-1">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> Quy Chế Điểm Khóa Luận (HUIT):
                            </div>
                            <div class="small text-secondary" style="font-size: 0.78rem; line-height: 1.4;">
                                Điểm cuối cùng của sinh viên được tính bằng <strong>100% điểm trung bình do Hội đồng bảo vệ chấm</strong>. Các phiếu chấm chỉ được tính khi đã Ký xác nhận &amp; Khóa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php 
            $coThongTinChucNang = !empty(trim($activeHoSo?->TieuChiChucNang ?? '')); 
        @endphp

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 bg-white rounded-3 border shadow-sm">
            @if($daKhoaPhieu)
                <div class="d-flex align-items-center text-success fw-bold">
                    <i class="fa-solid fa-lock me-2"></i> Phiếu chấm đã khóa bảo vệ toàn vẹn dữ liệu
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalXemTruoc">
                        <i class="fa-solid fa-eye me-2"></i> Xem Lại Phiếu Chấm Gốc
                    </button>
                    @if($coThongTinChucNang)
                        <a href="{{ route('giangvien.chamdiem.xuatWord', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" class="btn btn-outline-primary rounded-pill px-4 d-inline-flex align-items-center fw-bold shadow-sm">
                            <i class="fa-solid fa-file-word me-2 text-primary"></i> Xuất File Word (.docx)
                        </a>
                        <a href="{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" target="_blank" class="btn btn-outline-danger rounded-pill px-4 d-inline-flex align-items-center fw-bold shadow-sm">
                            <i class="fa-solid fa-file-pdf me-2 text-danger"></i> Xuất / In Phiếu PDF
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center fw-semibold text-muted" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-file-word me-2"></i> Xuất File Word (.docx) <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Chưa có mục 6</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center fw-semibold text-muted" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-file-pdf me-2"></i> Xuất PDF <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Chưa có mục 6</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-warning rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalYeuCauMoKhoa">
                        <i class="fa-solid fa-key me-2"></i> Yêu Cầu Mở Khóa
                    </button>
                </div>
            @else
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center" onclick="submitChamDiem('luu_nhap')">
                        <i class="fa-regular fa-floppy-disk me-2"></i> Lưu Nháp Điểm
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalXemTruoc">
                        <i class="fa-solid fa-eye me-2"></i> Xem Trước Phiếu Chấm
                    </button>
                    @if($coThongTinChucNang)
                        <a href="{{ route('giangvien.chamdiem.xuatWord', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" class="btn btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center fw-bold shadow-sm">
                            <i class="fa-solid fa-file-word me-1 text-primary"></i> Xuất File Word (.docx)
                        </a>
                        <a href="{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" target="_blank" class="btn btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center fw-bold shadow-sm">
                            <i class="fa-solid fa-print me-1 text-danger"></i> In / Xuất PDF
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center text-muted fw-semibold" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-file-word me-1"></i> Xuất Word <span class="badge bg-warning text-dark ms-1" style="font-size: 0.62rem;">Chưa có mục 6</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center text-muted fw-semibold" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-print me-1"></i> In / Xuất PDF <span class="badge bg-warning text-dark ms-1" style="font-size: 0.62rem;">Chưa có mục 6</span>
                        </button>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- BƯỚC 1: KÝ PHIẾU ĐIỂM (CHỮ KÝ ĐIỆN TỬ VẼ TRÊN HỆ THỐNG) --}}
                    @if($daKyTen)
                        <button type="button" class="btn btn-outline-success rounded-pill px-3 fw-bold d-inline-flex align-items-center shadow-xs" data-bs-toggle="modal" data-bs-target="#modalKyTen" id="btnKyTen">
                            <i class="fa-solid fa-signature me-1"></i> Đã Ký Tên <span class="badge bg-success-subtle text-success border border-success ms-1" style="font-size: 0.65rem;">Ký lại</span>
                        </button>
                    @else
                        <button type="button" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalKyTen" id="btnKyTen">
                            <i class="fa-solid fa-pen-nib me-2"></i> Bước 1: Ký Phiếu Điểm
                        </button>
                    @endif

                    {{-- BƯỚC 2: XÁC NHẬN & KHÓA PHIẾU CHẤM (CHỈ MỞ KHI ĐÃ KÝ TÊN) --}}
                    @if($daKyTen)
                        <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm d-inline-flex align-items-center" id="btnXacNhanKhoa" onclick="submitChamDiem('xac_nhan_hoan_thanh')" style="background: #0072ce; border-color: #0072ce;">
                            <i class="fa-solid fa-lock me-2"></i> Bước 2: Xác Nhận &amp; Khóa Phiếu Chấm
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center opacity-75" id="btnXacNhanKhoa" disabled title="Thầy/Cô vui lòng thực hiện Bước 1: Ký Phiếu Điểm trước khi Xác Nhận & Khóa">
                            <i class="fa-solid fa-lock me-2"></i> Bước 2: Xác Nhận &amp; Khóa Phiếu <span class="badge bg-light text-dark ms-2" style="font-size: 0.65rem;">Cần ký trước</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </form>
    @endif
    @endif
</div>

{{-- MODAL XEM TRƯỚC PHIẾU CHẤM (CHÍNH XÁC THEO BIỂU MẪU WORD KHOA CNTT) --}}
<div class="modal fade" id="modalXemTruoc" tabindex="-1" aria-labelledby="modalXemTruocLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-light border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                        <i class="fa-solid fa-file-word"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="modalXemTruocLabel">
                            Xem Trước Phiếu Chấm Điểm Chuẩn Khoa CNTT (Theo Mẫu Word Chính Thức)
                        </h6>
                        <span class="text-muted" style="font-size: 0.72rem;">Bố cục, font chữ, khung điểm và chi tiết các mục đúng theo văn bản Word mẫu của Khoa</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(isset($activeHoiDong) && isset($activeHoSo))
                        @if($coThongTinChucNang)
                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm" onclick="inPhieuTrucTiep()">
                            <i class="fa-solid fa-print me-1"></i> In Phiếu Chuẩn (PDF)
                        </button>
                        <a href="{{ route('giangvien.chamdiem.xuatWord', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm">
                            <i class="fa-solid fa-file-word me-1"></i> Xuất Word (.docx)
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold shadow-sm" onclick="window.print()">
                            <i class="fa-solid fa-print me-1"></i> In Trực Tiếp (A4)
                        </button>
                        @else
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-muted fw-semibold" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-print me-1"></i> In Phiếu (Chưa có mục 6)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-muted fw-semibold" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-file-word me-1"></i> Xuất Word (Chưa có mục 6)
                        </button>
                        @endif
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4 bg-secondary-subtle">
                {{-- Khung Trang Giấy A4 Mô Phỏng Chính Xác Bản Word Mẫu --}}
                <div class="mx-auto bg-white p-4 p-md-5 border shadow-sm rounded-1 print-paper-a4" id="printPaperA4" style="max-width: 900px; font-family: 'Times New Roman', Times, serif; color: #000; line-height: 1.45;">
                    
                    {{-- Header mẫu Word: Logo & Tên Trường / Khoa --}}
                    <div class="row align-items-center mb-3 pb-2 border-bottom">
                        <div class="col-8">
                            <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                                TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH
                            </div>
                            <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #0072ce;">
                                KHOA CÔNG NGHỆ THÔNG TIN
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" style="max-height: 60px; width: auto;" class="p-1 border rounded bg-white">
                        </div>
                    </div>

                    {{-- Tiêu đề phiếu chấm --}}
                    <div class="text-center my-3">
                        <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase;">
                            {{ $tenBieuMau ?? ($loaiKhoaLuan === 'KLKS' ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN') }}
                        </div>
                        <div style="font-size: 11pt; font-style: italic;">
                            (Hướng ứng dụng)
                        </div>
                    </div>

                    {{-- Thông tin đề tài & nhóm sinh viên theo đúng bản Word mẫu --}}
                    <div class="mb-3" style="font-size: 11pt; line-height: 1.6;">
                        <div>
                            <strong>Tên đề tài:</strong> <span class="fw-bold">{{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</span>
                        </div>
                        <div class="mt-1">
                            <strong>Nhóm sinh viên thực hiện:</strong>
                        </div>
                        @if(isset($sinhViens) && $sinhViens->isNotEmpty())
                            @foreach($sinhViens as $idx => $sv)
                            <div class="ps-4">
                                {{ $idx + 1 }}. Mã SV: <strong>{{ $sv->MaSV }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                                Họ tên: <strong>{{ $sv->HoTen }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                                Lớp: <strong>{{ $sv->lop?->TenLop ?? ($sv->MaLop ?? '') }}</strong>
                            </div>
                            @endforeach
                        @else
                            <div class="ps-4 text-muted fst-italic">Chưa có danh sách sinh viên</div>
                        @endif
                        <div class="mt-1">
                            <strong>Giảng viên hướng dẫn:</strong> {{ $gvhdHoTen ?? 'Chưa phân công' }}
                        </div>
                        <div class="mt-1">
                            <div><strong>Hội đồng:</strong> {{ $activeHoiDong->TenHoiDong ?? $activeHoiDong->MaHoiDong }}</div>
                            <div class="mt-1 d-flex flex-wrap gap-4">
                                <div><strong>Phòng:</strong> {{ $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Phòng B.304') }}</div>
                                <div><strong>Ngày chấm:</strong> {{ now()->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Bảng Rubric chính thức chuẩn biểu mẫu Word --}}
                    @if(isset($sinhViens) && $sinhViens->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0" style="font-size: 10pt; border-color: #000; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f2f2f2; font-weight: bold;">
                                    <th rowspan="2" class="align-middle text-center" style="width: 40px; border: 1px solid #000;">STT</th>
                                    <th rowspan="2" class="text-start align-middle" style="min-width: 320px; border: 1px solid #000;">Nội dung</th>
                                    <th rowspan="2" class="align-middle text-center" style="width: 70px; border: 1px solid #000;">CLO</th>
                                    <th rowspan="2" class="align-middle text-center" style="width: 85px; border: 1px solid #000;">Điểm tối đa</th>
                                    <th colspan="{{ $sinhViens->count() }}" class="text-center py-1" style="border: 1px solid #000;">Điểm chấm</th>
                                </tr>
                                <tr style="background-color: #f2f2f2; font-weight: bold;">
                                    @foreach($sinhViens as $idx => $sv)
                                    <th style="min-width: 75px; border: 1px solid #000;" class="py-1">SV{{ $idx + 1 }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($rubric ?? []) as $cr)
                                @php
                                    $lines = explode("\n", $cr['noi_dung']);
                                    $isBonus = !empty($cr['is_bonus']);
                                @endphp
                                <tr>
                                    <td class="text-center align-top py-2" style="border: 1px solid #000;">
                                        {{ $cr['stt'] }}
                                    </td>
                                    <td class="text-start align-top py-2" style="border: 1px solid #000; line-height: 1.4;">
                                        @if($cr['id'] === 'tc7')
                                            {{-- Hiển thị mục 6 / Cài đặt chức năng chính xác theo nội dung đã chỉnh sửa --}}
                                            <div class="fw-bold">Xây dựng các chức năng cơ bản của ứng dụng: đăng nhập, đăng xuất, backup &amp; restore,…</div>
                                            <div class="fw-bold mt-1">Cài đặt chức năng theo yêu cầu của bài toán:</div>
                                            @if(!empty($functionalRequirements))
                                                @php $groupedReqs = collect($functionalRequirements)->groupBy('group'); @endphp
                                                @foreach($groupedReqs as $groupName => $reqItems)
                                                    @if($groupName !== 'Chức năng cơ bản')
                                                    <div class="fw-bold ps-2 mt-1">&bull; {{ $groupName }}:</div>
                                                    @foreach($reqItems as $r)
                                                        @if($r['id'] !== 'tc7_co_ban')
                                                        <div class="ps-4 text-secondary">&ndash; {{ $r['label'] }} (0.50đ)</div>
                                                        @endif
                                                    @endforeach
                                                    @endif
                                                @endforeach
                                            @elseif(!empty($customChucNang))
                                                <div class="ps-2 text-dark font-monospace" style="white-space: pre-line; font-size: 9.5pt;">{{ $customChucNang }}</div>
                                            @else
                                                <div class="ps-2 text-muted fst-italic">Chưa có thông tin yêu cầu chức năng bài toán.</div>
                                            @endif
                                        @else
                                            @foreach($lines as $lIdx => $line)
                                                @php
                                                    $trimmed = trim($line);
                                                    $isSubSubBullet = str_starts_with($trimmed, '*');
                                                    $isSubBullet = str_starts_with($trimmed, '+');
                                                    $isBullet = str_starts_with($trimmed, '-');
                                                @endphp
                                                @if($isSubSubBullet)
                                                    <div class="ps-4 text-secondary">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                                                @elseif($isSubBullet)
                                                    <div class="ps-3 text-secondary fw-semibold">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                                                @elseif($isBullet)
                                                    <div class="ps-2 text-dark">&bull; {{ trim(substr($trimmed, 1)) }}</div>
                                                @else
                                                    <div class="{{ $lIdx === 0 ? 'fw-bold' : '' }}">{{ $trimmed }}</div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="text-center align-top py-2" style="border: 1px solid #000; font-size: 9pt;">
                                        {{ $cr['clo'] }}
                                    </td>
                                    <td class="text-center align-top py-2" style="border: 1px solid #000;">
                                        {{-- Khung Điểm Tổng --}}
                                        <div class="fw-bold">{{ number_format($cr['max'], 2) }}</div>
                                        {{-- Khung Điểm Chi Tiết --}}
                                        @if(isset($cr['diem_toi_da_lines']) && is_array($cr['diem_toi_da_lines']))
                                        <div class="small text-muted mt-1" style="font-size: 8pt; border-top: 1px dotted #999; padding-top: 2px;">
                                            @foreach($cr['diem_toi_da_lines'] as $dLine)
                                                <div>{{ $dLine }}</div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </td>
                                    @foreach($sinhViens as $idx => $sv)
                                    @php
                                        $daChamSV = $diemDaCham->get($sv->MaSV);
                                        $svChiTiet = $daChamSV?->ChiTietDiem;
                                        $cellScore = isset($svChiTiet[$cr['id']]) ? (float)$svChiTiet[$cr['id']] : 0.0;
                                    @endphp
                                    <td class="preview-cell-{{ $sv->MaSV }}-{{ $cr['id'] }} align-top py-2 fw-bold text-center" style="border: 1px solid #000;">
                                        {{ number_format($cellScore, 2) }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach

                                {{-- Hàng Tổng Cộng --}}
                                <tr style="background-color: #f2f2f2; font-weight: bold; border-top: 2px solid #000;">
                                    <td colspan="3" class="text-end py-2" style="border: 1px solid #000; padding-right: 15px;">
                                        Tổng cộng:
                                    </td>
                                    <td class="text-center py-2" style="border: 1px solid #000;">
                                        10.0
                                    </td>
                                    @foreach($sinhViens as $idx => $sv)
                                    @php
                                        $daChamSV = $diemDaCham->get($sv->MaSV);
                                        $curDiem = $daChamSV ? (float)$daChamSV->Diem : 0.0;
                                    @endphp
                                    <td class="preview-total-{{ $sv->MaSV }} text-center py-2 fs-6 fw-bold" style="border: 1px solid #000;">
                                        {{ number_format($curDiem, 2) }}
                                    </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted small fst-italic">
                        Chưa có danh sách sinh viên thực hiện để hiển thị bảng điểm xem trước.
                    </div>
                    @endif

                    {{-- Phần ký tên ở cuối trang y hệt biểu mẫu Word --}}
                    <div class="mt-4 row justify-content-end">
                        <div class="col-6 text-center">
                            <div class="fst-italic" style="font-size: 10.5pt; margin-bottom: 3px;">
                                TP. Hồ Chí Minh, ngày {{ now()->format('d/m/Y') }}
                            </div>
                            <div class="fw-bold text-uppercase" style="font-size: 11pt;">
                                Giám khảo chấm điểm
                            </div>
                            <div class="fst-italic text-muted" style="font-size: 10pt; margin-bottom: 4px;">
                                (Ký và ghi rõ họ tên)
                            </div>
                            <div id="previewChuKyContainer" class="d-flex align-items-center justify-content-center" style="height: 60px; margin: 4px 0;">
                                @if(!empty($chuKyHienTai))
                                    <img src="{{ $chuKyHienTai }}" alt="Chữ ký" id="previewChuKyImg" style="max-height: 55px; max-width: 160px;">
                                @else
                                    <span class="text-muted small fst-italic border border-dashed rounded px-3 py-1" id="previewChuKyPlaceholder">[ Chưa ký tên ]</span>
                                @endif
                            </div>
                            <div class="fw-bold" style="font-size: 11.5pt;">
                                {{ $giangVien->HoTen }}
                            </div>
                            <div class="text-muted" style="font-size: 10pt;">
                                ({{ $vaiTroGV ?? 'Thành viên Hội đồng' }})
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                @if(isset($activeHoiDong) && isset($activeHoSo))
                    @if($coThongTinChucNang)
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-sm" onclick="inPhieuTrucTiep()">
                        <i class="fa-solid fa-print me-1"></i> In Phiếu Chuẩn (PDF)
                    </button>
                    <a href="{{ route('giangvien.chamdiem.xuatWord', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm">
                        <i class="fa-solid fa-file-word me-1"></i> Xuất File Word (.docx)
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold shadow-sm" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i> In Trực Tiếp (A4)
                    </button>
                    @else
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-muted fw-semibold" onclick="alertChuaCoChucNang()">
                        <i class="fa-solid fa-print me-1"></i> In Phiếu (Chưa có mục 6)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-muted fw-semibold" onclick="alertChuaCoChucNang()">
                        <i class="fa-solid fa-file-word me-1"></i> Xuất Word (Chưa có mục 6)
                    </button>
                    @endif
                @endif
                @if(!$daKhoaPhieu)
                    @if($daKyTen)
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" onclick="submitChamDiem('xac_nhan_hoan_thanh')">
                        <i class="fa-solid fa-lock me-2"></i> Xác Nhận &amp; Khóa Phiếu Chấm
                    </button>
                    @else
                    <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalKyTen">
                        <i class="fa-solid fa-signature me-2"></i> Ký Phiếu Điểm
                    </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL VẼ CHỮ KÝ ĐIỆN TỬ TRÊN HỆ THỐNG --}}
<div class="modal fade" id="modalKyTen" tabindex="-1" aria-labelledby="modalKyTenLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #00305a 0%, #0072ce 100%);">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-signature"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="modalKyTenLabel">
                            Ký Tên Điện Tử Cho Phiếu Chấm Điểm
                        </h6>
                        <span class="text-white-50" style="font-size: 0.72rem;">Vẽ chữ ký tay trực tiếp của Giám khảo trên hệ thống</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-dark">
                        <i class="fa-solid fa-pen-nib text-primary me-1"></i> Khung vẽ chữ ký:
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted" style="font-size: 0.72rem;">Màu mực:</span>
                        <button type="button" class="btn btn-sm p-1 rounded-circle border border-2 border-primary active-color-btn" id="btnColorBlue" onclick="setPenColor('#002d72')" style="width: 22px; height: 22px; background: #002d72;" title="Mực xanh đậm"></button>
                        <button type="button" class="btn btn-sm p-1 rounded-circle border border-2 border-secondary" id="btnColorBlack" onclick="setPenColor('#000000')" style="width: 22px; height: 22px; background: #000000;" title="Mực đen"></button>
                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0 ms-2" onclick="clearSignatureCanvas()" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-eraser me-1"></i> Xóa &amp; Vẽ lại
                        </button>
                    </div>
                </div>

                {{-- Khung Canvas Vẽ Chữ Ký --}}
                <div class="position-relative bg-white border rounded-3 shadow-inner overflow-hidden" style="height: 200px; cursor: crosshair;">
                    <canvas id="canvasChuKy" width="530" height="200" style="width: 100%; height: 100%; touch-action: none;"></canvas>
                    
                    {{-- Đường Kẻ Mờ Dấu Mốc Ký Tên --}}
                    <div class="position-absolute w-75 start-50 translate-middle-x text-center pointer-events-none" style="bottom: 35px; border-bottom: 1px dashed #c0d4ed; pointer-events: none;">
                        <span class="text-muted fw-light fst-italic" style="font-size: 0.68rem; letter-spacing: 1px;">(Đường kẻ ký tên)</span>
                    </div>

                    {{-- Nhắc nhở khi chưa vẽ --}}
                    <div id="hintChuaVe" class="position-absolute top-50 start-50 translate-middle text-center text-muted opacity-50 pointer-events-none" style="pointer-events: none;">
                        <i class="fa-solid fa-arrow-pointer mb-1 d-block"></i>
                        <span style="font-size: 0.8rem;">Dùng chuột hoặc ngón tay / bút cảm ứng vẽ chữ ký tại đây</span>
                    </div>
                </div>

                {{-- Chữ ký cũ đã lưu nếu có --}}
                @if(!empty($giangVien->ChuKy))
                <div class="mt-2 p-2 bg-white rounded border d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $giangVien->ChuKy }}" alt="Chữ ký đã lưu" style="max-height: 35px; max-width: 100px;" class="border rounded bg-light p-1">
                        <span class="small text-muted" style="font-size: 0.75rem;">Bạn đã có chữ ký lưu trước đó</span>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" onclick="useSavedSignature('{{ $giangVien->ChuKy }}')" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Sử dụng chữ ký này
                    </button>
                </div>
                @endif

                {{-- Tùy chọn lưu mặc định --}}
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="chkLuuChuKyMacDinh" checked>
                    <label class="form-check-label small text-secondary" for="chkLuuChuKyMacDinh">
                        Lưu chữ ký này làm chữ ký cá nhân mặc định trên hệ thống cho các lần sau
                    </label>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnXacNhanLuuChuKy" onclick="hoanTatKyTen()">
                    <i class="fa-solid fa-check me-1"></i> Hoàn Tất &amp; Áp Dụng Chữ Ký
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CÀI ĐẶT CHỨC NĂNG THEO YÊU CẦU BÀI TOÁN (DÀNH CHO CHỦ TỊCH HỘI ĐỒNG) --}}
@if($isChuTich || (Auth::user() && (Auth::user()->isTruongKhoa() || Auth::user()->isAdmin())))
<div class="modal fade" id="modalCaiDatChucNang" tabindex="-1" aria-labelledby="modalCaiDatChucNangLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="{{ route('giangvien.chamdiem.capNhatChucNang') }}" method="POST">
            @csrf
            <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong }}">
            <input type="hidden" name="MaHoSo" value="{{ $activeHoSo->MaHoSo }}">

            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning-subtle text-dark border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-pen-ruler"></i>
                        </span>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="modalCaiDatChucNangLabel">
                                Cài Đặt Chức Năng Theo Yêu Cầu Bài Toán (Dành Cho Chủ Tịch Hội Đồng)
                            </h6>
                            <span class="text-muted" style="font-size: 0.75rem;">
                                Tùy chỉnh chi tiết các chức năng thực tế của đề tài - Đồng bộ cho tất cả Giám khảo trong Hội đồng
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    {{-- Banner thông báo phạm vi lưu chỉ riêng cho nhóm đề tài này --}}
                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3 border-0 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <strong>Phạm vi áp dụng:</strong> Cấu hình này <strong>chỉ lưu và áp dụng riêng cho Nhóm đề tài này</strong> (Mã hồ sơ: <strong>#{{ $activeHoSo->MaHoSo }}</strong>), hoàn toàn không ảnh hưởng đến các đề tài hay nhóm khác trong Hội đồng.
                        </div>
                        <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                            {{ $loaiKhoaLuan === 'KLKS' ? 'Hệ Kỹ Sư (4.0đ)' : 'Hệ Cử Nhân (3.5đ)' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Đề tài đang chấm điểm:</label>
                        <div class="p-2 bg-light rounded border small fw-semibold text-primary">
                            <i class="fa-solid fa-book-bookmark me-2"></i>{{ $activeDeTai->TenDeTai ?? '' }}
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Cột Trái: Trình Soạn Thảo & Thanh Công Cụ Định Dạng --}}
                        <div class="col-lg-7">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-keyboard me-1 text-secondary"></i> Soạn thảo nội dung chức năng &amp; yêu cầu:
                                </label>
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">0.50đ / mỗi yêu cầu</span>
                            </div>

                            {{-- Thanh công cụ định dạng nhanh --}}
                            <div class="p-2 bg-light rounded-top border border-bottom-0 d-flex flex-wrap gap-1 align-items-center">
                                <button type="button" class="btn btn-xs btn-outline-primary fw-semibold rounded px-2 py-1" onclick="insertToolbarText('+ Chức năng: ')" style="font-size: 0.72rem;" title="Thêm nhóm chức năng cấp 1">
                                    <i class="fa-solid fa-folder-plus me-1"></i> + Nhóm Chức Năng
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success fw-semibold rounded px-2 py-1" onclick="insertToolbarText('  * Yêu cầu: ')" style="font-size: 0.72rem;" title="Thêm yêu cầu chức năng con có ô chấm điểm (0.50đ)">
                                    <i class="fa-solid fa-circle-plus me-1"></i> + Yêu Cầu Con (0.5đ)
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold rounded px-2 py-1" onclick="insertToolbarText('- Xây dựng các chức năng cơ bản của ứng dụng: Đăng nhập, đăng xuất, phân quyền, backup & restore\n')" style="font-size: 0.72rem;" title="Thêm chức năng cơ bản">
                                    <i class="fa-solid fa-shield me-1"></i> + Chức Năng Cơ Bản
                                </button>
                                <div class="ms-auto d-flex gap-1">
                                    <button type="button" class="btn btn-xs btn-outline-info text-dark fw-semibold rounded px-2 py-1" onclick="loadSampleTemplate('KLCN')" style="font-size: 0.72rem;">
                                        Mẫu Cử Nhân (3.5đ)
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-info text-dark fw-semibold rounded px-2 py-1" onclick="loadSampleTemplate('KLKS')" style="font-size: 0.72rem;">
                                        Mẫu Kỹ Sư (4.0đ)
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-danger fw-semibold rounded px-2 py-1" onclick="clearTextarea()" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>

                            @php
                                $defaultPlaceholder = ($loaiKhoaLuan === 'KLKS')
                                    ? "- Xây dựng các chức năng cơ bản của ứng dụng:\n  + Đăng nhập, đăng xuất, đổi mật khẩu,\n  + Phân quyền ứng dụng (...)\n- Cài đặt chức năng nghiệp vụ trên nền tảng Web:\n  + Chức năng 1...\n  + Chức năng 2...\n- Cài đặt chức năng nghiệp vụ trên nền tảng Mobile:\n  + Chức năng 1...\n  + Chức năng 2..."
                                    : "  + Chức năng 01: Quản lý và xử lý đơn hàng tự động\n    * Yêu cầu 1: Tiếp nhận và phân loại đơn hàng theo trạng thái\n    * Yêu cầu 2: Tích hợp cổng thanh toán trực tuyến\n  + Chức năng 02: Quản lý danh mục sản phẩm và kho hàng\n    * Yêu cầu 1: Cập nhật tồn kho theo thời gian thực\n    * Yêu cầu 2: Báo cáo thống kê doanh thu và hàng tồn kho\n  + Chức năng 03: Phân hệ người dùng và thông báo đẩy\n    * Yêu cầu 1: Đăng nhập đa kênh và xác thực OTP\n    * Yêu cầu 2: Gửi thông báo đơn hàng qua Email/Zalo";
                            @endphp
                            <textarea id="textareaChucNang" 
                                      name="TieuChiChucNang" 
                                      rows="12" 
                                      class="form-control rounded-bottom font-monospace border" 
                                      style="font-size: 0.83rem; line-height: 1.6; border-top-left-radius: 0; border-top-right-radius: 0;" 
                                      placeholder="{{ $defaultPlaceholder }}">{{ $customChucNang ?? '' }}</textarea>

                            {{-- Hướng dẫn cú pháp định dạng --}}
                            <div class="mt-2 p-2 bg-light rounded border text-muted" style="font-size: 0.72rem; line-height: 1.5;">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="fa-solid fa-circle-question text-info me-1"></i> Quy ước định dạng chuẩn tự động tách ô chấm điểm:
                                </div>
                                <div>&bull; Bắt đầu bằng <code>+</code> hoặc <code>-</code> (hoặc chữ <code>Chức năng</code>) để đặt tên <strong>Nhóm chức năng lớn</strong>.</div>
                                <div>&bull; Bắt đầu bằng <code>*</code> hoặc <code>-</code> (hoặc chữ <code>Yêu cầu</code>) để tạo <strong>Từng ô chấm điểm riêng biệt (0.50đ)</strong> cho Giám khảo chấm.</div>
                                <div>&bull; Để trống ô này nếu muốn sử dụng danh sách 7 yêu cầu chức năng chuẩn mặc định của Khoa CNTT.</div>
                            </div>
                        </div>

                        {{-- Cột Phải: Live Preview trực tiếp cấu trúc phân bổ điểm & khung chấm --}}
                        <div class="col-lg-5">
                            <div class="card h-100 border rounded-3 bg-light-subtle shadow-sm">
                                <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark small">
                                        <i class="fa-solid fa-eye text-primary me-1"></i> Xem Trước Cấu Trúc Khung Chấm
                                    </span>
                                    <span id="badgeTargetScore" class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">
                                        Mục tiêu: {{ $loaiKhoaLuan === 'KLKS' ? '4.00' : '3.50' }}đ
                                    </span>
                                </div>
                                <div class="card-body p-3" style="max-height: 380px; overflow-y: auto;">
                                    {{-- Thống kê nhanh số lượng yêu cầu và điểm tương ứng --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded border">
                                        <div>
                                            <div class="text-muted" style="font-size: 0.68rem;">SỐ YÊU CẦU ĐÃ TẠO:</div>
                                            <div class="fw-bold text-primary fs-6" id="previewReqCount">0 mục</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted" style="font-size: 0.68rem;">TỔNG ĐIỂM DỰ KIẾN:</div>
                                            <div class="fw-bold text-success fs-6" id="previewTotalScore">0.00 đ</div>
                                        </div>
                                    </div>

                                    {{-- Danh sách yêu cầu phân tích theo nhóm --}}
                                    <div id="previewParsedList" class="space-y-2">
                                        {{-- Sẽ được JavaScript render theo thời gian thực --}}
                                    </div>
                                </div>
                                <div class="card-footer bg-white py-2 border-top text-muted small" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-info-circle me-1 text-primary"></i> Mỗi dòng yêu cầu tương ứng 1 ô chấm điểm (thang 0.5đ) hiển thị trên phiếu chấm của các Giám khảo.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Lưu &amp; Cập Nhật Cho Đề Tài Này
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL YÊU CẦU MỞ KHÓA PHIẾU CHẤM --}}
<div class="modal fade" id="modalYeuCauMoKhoa" tabindex="-1" aria-labelledby="modalYeuCauMoKhoaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('giangvien.chamdiem.yeuCauMoKhoa') }}" method="POST">
            @csrf
            <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong ?? '' }}">
            <input type="hidden" name="MaHoSo" value="{{ $activeHoSo->MaHoSo ?? '' }}">

            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header bg-warning-subtle text-warning-emphasis border-bottom py-3">
                    <h6 class="modal-title fw-bold" id="modalYeuCauMoKhoaLabel">
                        <i class="fa-solid fa-unlock-keyhole me-2"></i> Gửi Yêu Cầu Mở Khóa Phiếu Chấm Điểm
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="small text-secondary mb-3">
                        Sau khi phiếu chấm đã Ký xác nhận, việc chỉnh sửa phải có sự phê duyệt của Trưởng khoa nhằm đảm bảo tính minh bạch và toàn vẹn dữ liệu.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Lý do yêu cầu điều chỉnh điểm <span class="text-danger">*</span>:</label>
                        <textarea name="LyDo" rows="4" class="form-control" placeholder="Ví dụ: Nhập nhầm điểm tiêu chí 7, cập nhật thêm minh chứng điểm khuyến khích NCKH..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-2"></i> Gửi Yêu Cầu Đến Trưởng Khoa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const svInputs = document.querySelectorAll('.score-criterion');
    const subReqInputs = document.querySelectorAll('.sub-req-score');

    // Tự động tính tổng điểm và cập nhật giao diện
    function calculateScores() {
        const svMap = {};

        // Nhóm inputs theo sinh viên
        svInputs.forEach(input => {
            const svId = input.getAttribute('data-sv');
            if (!svMap[svId]) svMap[svId] = { total: 0, bonus: 0 };

            let val = parseFloat(input.value) || 0;
            const maxVal = parseFloat(input.getAttribute('data-max')) || 0;
            const isBonus = input.getAttribute('data-bonus') === '1';

            if (val > maxVal) {
                val = maxVal;
                input.value = maxVal.toFixed(2);
            }
            if (val < 0) {
                val = 0;
                input.value = '0.00';
            }

            if (isBonus) {
                svMap[svId].bonus += val;
            } else {
                svMap[svId].total += val;
            }

            // Đồng bộ dữ liệu sang Modal Xem Trước
            const nameMatch = input.getAttribute('name').match(/ChiTietDiem\]\[([^\]]+)\]/);
            if (nameMatch) {
                const cId = nameMatch[1];
                const previewCell = document.querySelector(`.preview-cell-${svId}-${cId}`);
                if (previewCell) {
                    previewCell.textContent = val.toFixed(2);
                }
            }
        });

        // Cập nhật từng sinh viên
        Object.keys(svMap).forEach(svId => {
            let bonus = svMap[svId].bonus;
            if (bonus > 1.0) bonus = 1.0;

            let finalScore = svMap[svId].total + bonus;
            // Tổng không vượt quá 10.0
            if (finalScore > 10.0) finalScore = 10.0;
            const formatted = finalScore.toFixed(2);

            const totalEl = document.getElementById('totalScore_' + svId);
            const inputEl = document.getElementById('inputTotalScore_' + svId);
            const badgeGradeEl = document.getElementById('badgeGrade_' + svId);
            const summaryScoreEl = document.getElementById('displaySummaryScore_' + svId);
            const summaryResultEl = document.getElementById('badgeSummaryResult_' + svId);
            const summaryScaleEl = document.getElementById('badgeSummaryScale_' + svId);

            if (totalEl) totalEl.textContent = formatted;
            if (inputEl) inputEl.value = formatted;
            if (summaryScoreEl) summaryScoreEl.textContent = formatted;

            const previewTotal = document.querySelector(`.preview-total-${svId}`);
            if (previewTotal) previewTotal.textContent = formatted;

            // Tính điểm chữ, hệ 4 và xếp loại
            let letter = 'F';
            let scale4 = '0.0';
            let xepLoai = 'Chưa chấm điểm';
            let badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';

            if (finalScore >= 9.0) {
                letter = 'A+'; scale4 = '4.0'; xepLoai = 'ĐẠT (Xuất sắc)';
                badgeClass = 'bg-success-subtle text-success border border-success-subtle';
            } else if (finalScore >= 8.5) {
                letter = 'A'; scale4 = '3.7'; xepLoai = 'ĐẠT (Giỏi)';
                badgeClass = 'bg-success-subtle text-success border border-success-subtle';
            } else if (finalScore >= 8.0) {
                letter = 'B+'; scale4 = '3.5'; xepLoai = 'ĐẠT (Khá giỏi)';
                badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            } else if (finalScore >= 7.0) {
                letter = 'B'; scale4 = '3.0'; xepLoai = 'ĐẠT (Khá)';
                badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            } else if (finalScore >= 6.5) {
                letter = 'C+'; scale4 = '2.5'; xepLoai = 'ĐẠT (Trung bình khá)';
                badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
            } else if (finalScore >= 5.5) {
                letter = 'C'; scale4 = '2.0'; xepLoai = 'ĐẠT (Trung bình)';
                badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
            } else if (finalScore >= 5.0) {
                letter = 'D+'; scale4 = '1.5'; xepLoai = 'ĐẠT (Trung bình yếu)';
                badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
            } else if (finalScore >= 4.0) {
                letter = 'D'; scale4 = '1.0'; xepLoai = 'ĐẠT (Yếu)';
                badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
            } else if (finalScore > 0) {
                letter = 'F'; scale4 = '0.0'; xepLoai = 'Không đạt';
                badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
            }

            if (badgeGradeEl) {
                if (finalScore > 0) {
                    badgeGradeEl.textContent = `Điểm chữ: ${letter} (${scale4})`;
                    badgeGradeEl.className = 'small fw-semibold text-primary mt-1';
                } else {
                    badgeGradeEl.textContent = 'Chưa chấm điểm';
                    badgeGradeEl.className = 'small fw-semibold text-muted mt-1';
                }
            }
            if (summaryScaleEl) {
                summaryScaleEl.textContent = finalScore > 0 ? `Điểm chữ: ${letter} | Hệ 4: ${scale4}` : 'Chưa có điểm';
            }
            if (summaryResultEl) {
                summaryResultEl.textContent = xepLoai;
                summaryResultEl.className = `badge rounded-pill px-3 py-1 ${badgeClass}`;
            }
        });
    }

    // Sự kiện nhập điểm cho từng yêu cầu chức năng con (Tự động cộng dồn lên Tiêu chí 7)
    subReqInputs.forEach(input => {
        input.addEventListener('input', function() {
            const svId = this.getAttribute('data-sv');
            let totalSub = 0;
            document.querySelectorAll(`.sub-req-sv-${svId}`).forEach(sub => {
                let v = parseFloat(sub.value) || 0;
                const maxV = parseFloat(sub.getAttribute('data-req-max')) || 0.5;
                if (v > maxV) { v = maxV; sub.value = maxV.toFixed(2); }
                if (v < 0) { v = 0; sub.value = '0.00'; }
                totalSub += v;
            });

            // Cập nhật vào ô Tổng TC7 của sinh viên
            const tc7Input = document.getElementById(`input_tc7_${svId}`);
            if (tc7Input) {
                const maxTc7 = parseFloat(tc7Input.getAttribute('data-max')) || 4.0;
                tc7Input.value = Math.min(totalSub, maxTc7).toFixed(2);
            }
            calculateScores();
        });
    });

    svInputs.forEach(input => {
        input.addEventListener('input', calculateScores);
    });

    window.datDiemVeKhong = function() {
        svInputs.forEach(input => {
            input.value = '0.00';
        });
        subReqInputs.forEach(input => {
            input.value = '0.00';
        });
        calculateScores();
    };

    window.submitChamDiem = function(actionType) {
        const form = document.getElementById('formChamDiem');
        document.getElementById('inputActionType').value = actionType;

        if (actionType === 'xac_nhan_hoan_thanh') {
            const inputChuKy = document.getElementById('inputChuKy');
            if (!inputChuKy || !inputChuKy.value) {
                alert('CHƯA THỂ XÁC NHẬN & KHÓA PHIẾU:\n\nThầy/Cô chưa hoàn tất ký tên điện tử vào phiếu chấm điểm.\n\nVui lòng bấm nút "Bước 1: Ký Phiếu Điểm" để vẽ và xác nhận chữ ký trước khi khóa phiếu!');
                return;
            }

            let hasZero = false;
            document.querySelectorAll('input[id^="inputTotalScore_"]').forEach(el => {
                if (parseFloat(el.value) === 0) hasZero = true;
            });
            let msg = 'Thầy/Cô có chắc chắn muốn XÁC NHẬN & KHÓA phiếu chấm điểm này?\n\nSau khi khóa, phiếu sẽ được bảo đảm toàn vẹn và chuyển sang bảng tổng hợp điểm Hội đồng. Việc sửa đổi sau này phải thông qua Trưởng khoa.';
            if (hasZero) {
                msg = 'Lưu ý: Có sinh viên đang có tổng điểm là 0.00 (chưa chấm hoặc chấm 0 điểm).\n\n' + msg;
            }
            if (!confirm(msg)) {
                return;
            }
        }
        form.submit();
    };

    calculateScores();

    window.alertChuaCoChucNang = function() {
        alert('CHƯA THỂ XUẤT / IN PHIẾU CHẤM ĐIỂM:\n\nĐề tài này chưa được thiết lập chi tiết mục 6 ("Cài đặt chức năng theo yêu cầu bài toán").\n\nQuy định của Khoa CNTT: Phiếu chấm điểm chỉ được phép xuất File Word hoặc in PDF sau khi Chủ tịch Hội đồng đã cấu hình đầy đủ các yêu cầu chức năng riêng cho đề tài này.\n\nVui lòng liên hệ Chủ tịch Hội đồng nhấn nút "Cài đặt chức năng" để cập nhật!');
    };

    window.inPhieuTrucTiep = function() {
        @if(isset($activeHoiDong) && isset($activeHoSo))
            @if($coThongTinChucNang)
                window.open("{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}", "_blank");
            @else
                alertChuaCoChucNang();
            @endif
        @else
            alert('Chưa có thông tin hội đồng hoặc hồ sơ bảo vệ.');
        @endif
    };

    // ── XỬ LÝ CANVAS CHỮ KÝ ĐIỆN TỬ VẼ TRÊN HỆ THỐNG ──
    const canvas = document.getElementById('canvasChuKy');
    const hintChuaVe = document.getElementById('hintChuaVe');
    let ctx = null;
    let isDrawing = false;
    let hasDrawn = false;
    let penColor = '#002d72';

    if (canvas) {
        ctx = canvas.getContext('2d');
        ctx.strokeStyle = penColor;
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            if (rect.width > 0 && canvas.width !== rect.width) {
                const oldData = hasDrawn ? canvas.toDataURL() : null;
                canvas.width = rect.width;
                canvas.height = 200;
                ctx.strokeStyle = penColor;
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                if (oldData) {
                    const img = new Image();
                    img.onload = () => ctx.drawImage(img, 0, 0);
                    img.src = oldData;
                }
            }
        }

        const modalKyTenEl = document.getElementById('modalKyTen');
        if (modalKyTenEl) {
            modalKyTenEl.addEventListener('shown.bs.modal', function () {
                resizeCanvas();
            });
        }

        function getCanvasPos(e) {
            const rect = canvas.getBoundingClientRect();
            let clientX, clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasDrawn = true;
            if (hintChuaVe) hintChuaVe.style.display = 'none';
            const pos = getCanvasPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            if (e.cancelable) e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getCanvasPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            if (e.cancelable) e.preventDefault();
        }

        function stopDrawing(e) {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
            }
            if (e && e.cancelable) e.preventDefault();
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        // Touch events cho màn hình cảm ứng & iPad
        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing, { passive: false });
    }

    window.setPenColor = function(color) {
        penColor = color;
        if (ctx) ctx.strokeStyle = color;
        const btnBlue = document.getElementById('btnColorBlue');
        const btnBlack = document.getElementById('btnColorBlack');
        if (color === '#002d72') {
            btnBlue?.classList.add('border-primary');
            btnBlack?.classList.remove('border-primary');
        } else {
            btnBlack?.classList.add('border-primary');
            btnBlue?.classList.remove('border-primary');
        }
    };

    window.clearSignatureCanvas = function() {
        if (!ctx || !canvas) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
        if (hintChuaVe) hintChuaVe.style.display = 'block';
    };

    window.useSavedSignature = function(dataUrl) {
        if (!ctx || !canvas) return;
        const img = new Image();
        img.onload = function() {
            clearSignatureCanvas();
            ctx.drawImage(img, 30, 20, 260, 140);
            hasDrawn = true;
            if (hintChuaVe) hintChuaVe.style.display = 'none';
        };
        img.src = dataUrl;
    };

    window.hoanTatKyTen = function() {
        if (!hasDrawn) {
            alert('Vui lòng dùng chuột hoặc bút cảm ứng vẽ chữ ký của Thầy/Cô vào khung trước khi hoàn tất!');
            return;
        }

        const dataUrl = canvas.toDataURL('image/png');
        const inputChuKy = document.getElementById('inputChuKy');
        if (inputChuKy) inputChuKy.value = dataUrl;

        const btnSave = document.getElementById('btnXacNhanLuuChuKy');
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu chữ ký...';
        }

        const chkLuuMacDinh = document.getElementById('chkLuuChuKyMacDinh');

        fetch("{{ route('giangvien.chamdiem.kyTen') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                MaHoiDong: '{{ $activeHoiDong->MaHoiDong }}',
                MaHoSo: '{{ $activeHoSo?->MaHoSo }}',
                chu_ky: dataUrl,
                luu_mac_dinh: chkLuuMacDinh ? chkLuuMacDinh.checked : true
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Đóng modal ký tên
                const modalEl = document.getElementById('modalKyTen');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                // Cập nhật nút Ký Tên
                const btnKyTen = document.getElementById('btnKyTen');
                if (btnKyTen) {
                    btnKyTen.className = 'btn btn-outline-success rounded-pill px-3 fw-bold d-inline-flex align-items-center shadow-xs';
                    btnKyTen.innerHTML = '<i class="fa-solid fa-signature me-1"></i> Đã Ký Tên <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Ký lại</span>';
                }

                // MỞ KHÓA NÚT BƯỚC 2: XÁC NHẬN & KHÓA
                const btnXacNhanKhoa = document.getElementById('btnXacNhanKhoa');
                if (btnXacNhanKhoa) {
                    btnXacNhanKhoa.disabled = false;
                    btnXacNhanKhoa.className = 'btn btn-primary rounded-pill px-5 fw-bold shadow-sm d-inline-flex align-items-center';
                    btnXacNhanKhoa.style.background = '#0072ce';
                    btnXacNhanKhoa.style.borderColor = '#0072ce';
                    btnXacNhanKhoa.removeAttribute('title');
                    btnXacNhanKhoa.setAttribute('onclick', "submitChamDiem('xac_nhan_hoan_thanh')");
                    btnXacNhanKhoa.innerHTML = '<i class="fa-solid fa-lock me-2"></i> Bước 2: Xác Nhận &amp; Khóa Phiếu Chấm';
                }

                // Cập nhật chữ ký trong Modal Xem Trước
                const previewChuKyContainer = document.getElementById('previewChuKyContainer');
                if (previewChuKyContainer) {
                    previewChuKyContainer.innerHTML = `<img src="${dataUrl}" alt="Chữ ký" style="max-height: 55px; max-width: 160px;">`;
                }

                alert('KÝ TÊN THÀNH CÔNG!\n\nChữ ký điện tử của Thầy/Cô đã được áp dụng vào phiếu chấm điểm.\n\nBây giờ Thầy/Cô có thể nhấn "Bước 2: Xác Nhận & Khóa Phiếu Chấm" để hoàn tất.');
            } else {
                alert('Có lỗi khi lưu chữ ký: ' + (data.message || 'Vui lòng thử lại.'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Không thể kết nối đến máy chủ để lưu chữ ký.');
        })
        .finally(() => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fa-solid fa-check me-1"></i> Hoàn Tất &amp; Áp Dụng Chữ Ký';
            }
        });
    };

    // ── XỬ LÝ SOẠN THẢO VÀ LIVE PREVIEW MODAL CÀI ĐẶT CHỨC NĂNG ──
    const textareaChucNang = document.getElementById('textareaChucNang');
    const previewReqCount = document.getElementById('previewReqCount');
    const previewTotalScore = document.getElementById('previewTotalScore');
    const previewParsedList = document.getElementById('previewParsedList');

    window.insertToolbarText = function(text) {
        if (!textareaChucNang) return;
        const start = textareaChucNang.selectionStart;
        const end = textareaChucNang.selectionEnd;
        const currentVal = textareaChucNang.value;
        const before = currentVal.substring(0, start);
        const after = currentVal.substring(end, currentVal.length);
        
        // Thêm dòng mới nếu chưa xuống dòng
        let prefix = (before.length > 0 && !before.endsWith('\n')) ? '\n' : '';
        textareaChucNang.value = before + prefix + text + after;
        textareaChucNang.focus();
        textareaChucNang.selectionStart = textareaChucNang.selectionEnd = start + prefix.length + text.length;
        updateLivePreview();
    };

    window.clearTextarea = function() {
        if (!textareaChucNang) return;
        if (confirm('Thầy/Cô có chắc muốn xóa toàn bộ nội dung đã soạn thảo?')) {
            textareaChucNang.value = '';
            updateLivePreview();
        }
    };

    window.loadSampleTemplate = function(loai) {
        if (!textareaChucNang) return;
        if (loai === 'KLKS') {
            textareaChucNang.value = `- Xây dựng các chức năng cơ bản của ứng dụng:\n  + Đăng nhập, đăng xuất, đổi mật khẩu, phân quyền ứng dụng\n- Cài đặt chức năng nghiệp vụ trên nền tảng Web:\n  * Yêu cầu 1: Quản trị danh mục và dữ liệu hệ thống (0.5đ)\n  * Yêu cầu 2: Quy trình xử lý nghiệp vụ chính trên Web (0.5đ)\n  * Yêu cầu 3: Báo cáo thống kê số liệu và xuất tệp Excel/PDF (0.5đ)\n- Cài đặt chức năng nghiệp vụ trên nền tảng Mobile:\n  * Yêu cầu 1: Giao diện người dùng Mobile và xác thực sinh trắc học (0.5đ)\n  * Yêu cầu 2: Thao tác tác nghiệp thực địa qua thiết bị di động (0.5đ)\n  * Yêu cầu 3: Nhận thông báo đẩy (Push Notification) tức thời (0.5đ)\n  * Yêu cầu 4: Đồng bộ trạng thái và dữ liệu thời gian thực giữa Web và Mobile (0.5đ)`;
        } else {
            textareaChucNang.value = `+ Chức năng 01: Quản lý và xử lý đơn hàng tự động\n  * Yêu cầu 1: Tiếp nhận và phân loại đơn hàng theo trạng thái\n  * Yêu cầu 2: Tích hợp cổng thanh toán trực tuyến\n+ Chức năng 02: Quản lý danh mục sản phẩm và kho hàng\n  * Yêu cầu 1: Cập nhật tồn kho theo thời gian thực\n  * Yêu cầu 2: Báo cáo thống kê doanh thu và hàng tồn kho\n+ Chức năng 03: Phân hệ người dùng và thông báo đẩy\n  * Yêu cầu 1: Đăng nhập đa kênh và xác thực OTP\n  * Yêu cầu 2: Gửi thông báo đơn hàng qua Email/Zalo\n  * Yêu cầu 3: Phân quyền vai trò và nhật ký thao tác hệ thống`;
        }
        updateLivePreview();
    };

    function updateLivePreview() {
        if (!textareaChucNang || !previewParsedList) return;
        const text = textareaChucNang.value.trim();
        const lines = text.split('\n');

        let currentGroup = 'Chức năng bài toán';
        let groups = {};
        let totalReqs = 0;

        lines.forEach(line => {
            const trimmed = line.trim();
            if (!trimmed) return;

            // Nhận diện dòng nhóm chức năng
            if (/^(Chức năng\s*\d+|Cài đặt chức năng|\+?\s*Chức năng|\-?\s*Cài đặt|\-?\s*Xây dựng)/i.test(trimmed)) {
                currentGroup = trimmed.replace(/^[\+\-\*\s]+/, '');
                if (!groups[currentGroup]) groups[currentGroup] = [];
                return;
            }

            // Nhận diện dòng yêu cầu
            const reqLabel = trimmed.replace(/^[\+\-\*\s]+/, '');
            if (!groups[currentGroup]) groups[currentGroup] = [];
            groups[currentGroup].push(reqLabel);
            totalReqs++;
        });

        // Cập nhật số liệu
        const totalPts = (totalReqs * 0.5).toFixed(2);
        if (previewReqCount) previewReqCount.textContent = `${totalReqs} yêu cầu`;
        if (previewTotalScore) previewTotalScore.textContent = `${totalPts} đ`;

        // Render cấu trúc nhóm
        let html = '';
        const groupKeys = Object.keys(groups);
        if (groupKeys.length === 0 || totalReqs === 0) {
            html = `<div class="text-center py-4 text-muted small fst-italic">
                Chưa có yêu cầu nào được soạn thảo.<br>Bấm các nút công cụ hoặc nhập văn bản bên trái để xem cấu trúc tự động.
            </div>`;
        } else {
            groupKeys.forEach(grp => {
                const reqs = groups[grp];
                if (reqs.length === 0) return;
                html += `<div class="mb-2 p-2 bg-white rounded border">
                    <div class="fw-bold text-primary small mb-1 border-bottom pb-1">
                        <i class="fa-solid fa-folder me-1"></i> ${grp}
                    </div>
                    <div class="space-y-1">`;
                reqs.forEach((r, idx) => {
                    html += `<div class="d-flex justify-content-between align-items-center py-1 ps-2 border-bottom border-light" style="font-size: 0.73rem;">
                        <span class="text-dark text-truncate me-2" title="${r}">
                            &bull; ${r}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.65rem;">+0.50đ</span>
                    </div>`;
                });
                html += `</div></div>`;
            });
        }
        previewParsedList.innerHTML = html;
    }

    if (textareaChucNang) {
        textareaChucNang.addEventListener('input', updateLivePreview);
        // Khởi tạo live preview khi mở trang
        updateLivePreview();
    }
});
</script>
@endpush
@endsection
