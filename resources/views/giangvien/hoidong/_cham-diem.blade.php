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

/* Preview controls use the admin theme; document printing keeps its own rules. */
@media screen {
    body:has(#modalXemTruoc.show) #time-machine-widget { visibility: hidden; pointer-events: none; }
    #modalXemTruoc .modal-dialog {
        width: calc(100% - 32px);
        max-width: 1400px;
        height: calc(100vh - 32px);
        height: calc(100dvh - 32px);
        margin: 16px auto;
    }
    #modalXemTruoc .modal-content { height: 100%; max-height: 100%; border-radius: 16px !important; }
    #modalXemTruoc .grading-preview-header { gap: 16px; flex-shrink: 0; }
    #modalXemTruoc .grading-preview-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
    #modalXemTruoc .grading-preview-title-block { min-width: 0; }
    #modalXemTruoc .grading-preview-icon {
        width: 42px; height: 42px; flex: 0 0 42px; display: inline-flex;
        align-items: center; justify-content: center; border-radius: 10px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 19px;
    }
    #modalXemTruoc .modal-title { font-size: 18px !important; line-height: 1.4; text-transform: none !important; margin: 0; }
    #modalXemTruoc .grading-preview-subtitle { color: #dbeafe !important; font-size: 13px !important; line-height: 1.5; margin-top: 3px; overflow-wrap: anywhere; }
    #modalXemTruoc .grading-preview-header .btn-close { flex-shrink: 0; margin: 0; }
    #modalXemTruoc .grading-preview-body {
        flex: 1 1 auto; min-height: 0; max-height: none !important;
        overflow: auto !important; overscroll-behavior: contain;
        background: #f1f5f9 !important; padding: 24px !important;
    }
    #modalXemTruoc #printPaperA4 {
        width: 1120px; min-width: 1120px; max-width: 1120px !important;
        border: 1px solid #dce4ef !important; border-radius: 8px !important;
        box-shadow: 0 3px 14px rgba(15,23,42,.08) !important;
    }
    #modalXemTruoc .grading-preview-footer { justify-content: space-between !important; flex-shrink: 0; flex-wrap: wrap; gap: 12px !important; }
    #modalXemTruoc .grading-preview-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px; }
    #modalXemTruoc .grading-preview-footer .btn {
        min-height: 40px !important; padding: 8px 14px !important;
        border-radius: 8px !important; font-size: 13px !important;
        line-height: 1.4; white-space: nowrap; box-shadow: none !important;
    }
    @media (max-width: 575.98px) {
        #modalXemTruoc .modal-dialog { width: calc(100% - 16px); height: calc(100vh - 16px); height: calc(100dvh - 16px); margin: 8px auto; }
        #modalXemTruoc .grading-preview-header { padding: 16px !important; gap: 10px; }
        #modalXemTruoc .modal-title { font-size: 16px !important; }
        #modalXemTruoc .grading-preview-icon { width: 36px; height: 36px; flex-basis: 36px; font-size: 16px; }
        #modalXemTruoc .grading-preview-body { padding: 12px !important; }
        #modalXemTruoc .grading-preview-footer { padding: 12px !important; }
        #modalXemTruoc .grading-preview-actions { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); width: 100%; gap: 8px; }
        #modalXemTruoc .grading-preview-actions .btn { width: 100%; min-width: 0; height: auto; white-space: normal !important; overflow-wrap: anywhere; }
    }
}
</style>
@endpush

@php 
    $councilsList = $gradingHoiDongs ?? collect(); 
    $allCouncils = $allHoiDongs ?? $hoiDongs ?? collect();
@endphp

@if($allCouncils->isEmpty())
    <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white my-3">
        <div class="py-5">
            <i class="fa-solid fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-secondary">Thầy/Cô Chưa Được Phân Công Vào Hội Đồng Bảo Vệ Nào</h5>
            <p class="text-muted small mb-0">Khi Trưởng khoa thành lập Hội đồng và thêm Thầy/Cô vào danh sách thành viên, các ca bảo vệ sẽ hiển thị tại đây.</p>
        </div>
    </div>
@elseif($councilsList->isEmpty())
    {{-- Trường hợp có hội đồng nhưng chưa đến thời gian chấm điểm (Yêu cầu B) --}}
    <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white my-3">
        <div class="py-5">
            <div class="mb-3">
                <span class="p-3 bg-warning-subtle text-warning-emphasis rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                    <i class="fa-regular fa-clock fa-2x"></i>
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-2">Hiện Chưa Có Hội Đồng Nào Đến Thời Gian Chấm Điểm</h5>
            <p class="text-muted small mx-auto mb-4" style="max-width: 540px;">
                Theo quy chế đào tạo, cổng chấm điểm chỉ mở khi phiên bảo vệ chính thức bắt đầu (trạng thái <strong>Đang diễn ra</strong>) hoặc sau khi kết thúc trong hạn chấm quy định.
            </p>

            @if(!empty($sapDienRaGanNhat))
                <div class="card border border-warning-subtle bg-warning bg-opacity-10 rounded-3 p-3 mx-auto mb-4 text-start shadow-xs" style="max-width: 580px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-3 text-warning">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                Hội đồng gần nhất: {{ $sapDienRaGanNhat->TenHoiDong }}
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fa-regular fa-clock text-primary me-1"></i>
                                Thời gian mở chấm: 
                                <strong class="text-primary">{{ $sapDienRaGanNhat->thoi_diem_bat_dau?->format('H:i \n\g\à\y d/m/Y') }}</strong>
                                @if($sapDienRaGanNhat->dem_nguoc_bat_dau)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill ms-2">
                                        {{ $sapDienRaGanNhat->dem_nguoc_bat_dau }}
                                    </span>
                                @endif
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                Địa điểm: <strong>{{ $sapDienRaGanNhat->DiaDiem ?? 'Phòng Hội đồng' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-xs" onclick="switchHoiDongTab('danhsach')">
                    <i class="fa-solid fa-arrow-left me-2"></i> Quay Lại Danh Sách Hội Đồng
                </button>
            </div>
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
        $reasonCantGrade = '';
        $canGradeCurrent = $activeHoiDong ? $activeHoiDong->canGrade($giangVien, $activeHoSo, $reasonCantGrade) : false;
        $isReadOnly = $daKhoaPhieu || !$canGradeCurrent;
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
                <a href="{{ route('giangvien.hoidong.index', ['tab' => 'chamdiem', 'hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLCN']) }}" 
                   class="btn {{ !$isKLKS ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    <i class="fa-solid fa-graduation-cap me-2"></i> Mẫu Cử Nhân (KLCN)
                </a>
                <a href="{{ route('giangvien.hoidong.index', ['tab' => 'chamdiem', 'hd' => $activeHoiDong->MaHoiDong, 'hoso' => $activeHoSo?->MaHoSo, 'loai' => 'KLKS']) }}" 
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

    @if(!$canGradeCurrent && !$daKhoaPhieu)
    <div class="alert alert-warning border-warning rounded-3 shadow-sm py-3 px-4 mb-3 d-flex align-items-center gap-3">
        <i class="fa-solid fa-lock fs-3 text-warning-emphasis flex-shrink-0"></i>
        <div>
            <strong class="d-block text-warning-emphasis">Chế độ xem — Chưa thể nhập hoặc lưu điểm</strong>
            <span class="small text-secondary">{{ $reasonCantGrade }}</span>
        </div>
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
                    @foreach($councilsList as $hd)
                        <option value="{{ route('giangvien.hoidong.index', ['tab' => 'chamdiem', 'hd' => $hd->MaHoiDong, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoiDong && $hd->MaHoiDong === $activeHoiDong->MaHoiDong ? 'selected' : '' }}>
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
                        <option value="{{ route('giangvien.hoidong.index', ['tab' => 'chamdiem', 'hd' => $activeHoiDong->MaHoiDong, 'hoso' => $hs->MaHoSo, 'loai' => $loaiKhoaLuan]) }}" {{ $activeHoSo && $hs->MaHoSo === $activeHoSo->MaHoSo ? 'selected' : '' }}>
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
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1 d-inline-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalXemDeCuong" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-book-open me-1"></i> Xem Đề Cương Đã Duyệt
                    </button>
                    @if($activeHoiDong && $activeHoSo && ($isThuKy || (Auth::user() && (Auth::user()->isTruongKhoa() || Auth::user()->isGiaoVu() || Auth::user()->isAdmin()))))
                    <button type="button" class="btn btn-xs btn-outline-warning text-dark fw-bold rounded-pill px-3 py-1 d-inline-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCaiDatChucNang" style="font-size: 0.78rem; background: #fff8e6; border-color: #f59e0b;">
                        <i class="fa-solid fa-pen-to-square text-warning-emphasis me-1"></i> Chuẩn Bị Phiếu &amp; Tiêu Chí Chức Năng (Thư Ký)
                    </button>
                    @elseif($isChuTich)
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 rounded-pill" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-crown me-1 text-warning"></i> Chủ tịch HĐ: Điều hành buổi bảo vệ
                    </span>
                    @endif
                    @if(($activeHoSo?->TrangThaiChuanBiPhieu ?? '') === 'DaChuanBi')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill" style="font-size: 0.75rem;" title="Phiếu chấm đã được Thư ký chuẩn bị">
                        <i class="fa-solid fa-check-double me-1"></i> Phiếu chấm: Đã chuẩn bị
                    </span>
                    @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 rounded-pill" style="font-size: 0.75rem;" title="Chờ Thư ký hội đồng khởi tạo tiêu chí phiếu">
                        <i class="fa-solid fa-clock me-1"></i> Chờ Thư ký chuẩn bị phiếu
                    </span>
                    @endif
                    @if(!$isReadOnly)
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
                                                <span class="badge bg-primary text-white" style="font-size: 0.65rem;">{{ $loaiKhoaLuan === 'KLKS' ? 'Tối đa 4.00đ' : 'Tối đa 3.50đ' }}</span>
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
                                                                <span class="text-muted ms-auto text-nowrap font-monospace" style="font-size: 0.65rem;">({{ number_format((float)($r['max'] ?? 0.5), 2) }}đ)</span>
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
                                            @if($isThuKy || $isChuTich || (Auth::user() && (Auth::user()->isTruongKhoa() || Auth::user()->isGiaoVu() || Auth::user()->isAdmin())))
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#modalCaiDatChucNang" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Thiết lập tiêu chí (Thư ký)
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
                                                   {{ $isReadOnly ? 'readonly' : '' }}
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
                                                       {{ $isReadOnly ? 'readonly' : '' }}>
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
                                               {{ $isReadOnly ? 'readonly' : '' }}
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
                                {{ $isReadOnly ? 'readonly' : '' }}
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
                    @endif
                    <button type="button" class="btn btn-outline-warning rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalYeuCauMoKhoa">
                        <i class="fa-solid fa-key me-2"></i> Yêu Cầu Mở Khóa
                    </button>
                </div>
            @elseif(!$canGradeCurrent)
                <div class="d-flex align-items-center text-warning-emphasis fw-bold">
                    <i class="fa-solid fa-lock me-2"></i> Chế độ xem: Chưa đến thời gian hoặc không được phép chấm điểm
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalXemTruoc">
                        <i class="fa-solid fa-eye me-2"></i> Xem Trước Phiếu Chấm
                    </button>
                    @if($coThongTinChucNang)
                        <a href="{{ route('giangvien.chamdiem.xuatPdf', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" target="_blank" class="btn btn-outline-danger rounded-pill px-4 d-inline-flex align-items-center fw-bold shadow-sm">
                            <i class="fa-solid fa-file-pdf me-2 text-danger"></i> In Phiếu PDF
                        </a>
                    @endif
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="switchHoiDongTab('danhsach')">
                        <i class="fa-solid fa-list-check me-1"></i> Về Danh Sách Hội Đồng
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
                    <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm d-inline-flex align-items-center" id="btnXacNhanKhoa" onclick="submitChamDiem('xac_nhan_hoan_thanh')" style="background: #0072ce; border-color: #0072ce;">
                        <i class="fa-solid fa-lock me-2"></i> Xác Nhận &amp; Khóa Phiếu Chấm
                    </button>
                </div>
            @endif
        </div>
    </form>
    @endif
    @endif

@if($activeHoiDong && $activeHoSo)
{{-- MODAL XEM TRƯỚC PHIẾU CHẤM (CHÍNH XÁC THEO BIỂU MẪU WORD KHOA CNTT) --}}
<div class="modal fade" id="modalXemTruoc" tabindex="-1" aria-labelledby="modalXemTruocLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header grading-preview-header">
                <div class="grading-preview-heading">
                    <span class="grading-preview-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></span>
                    <div class="grading-preview-title-block">
                        <h6 class="modal-title" id="modalXemTruocLabel">Xem trước phiếu chấm điểm</h6>
                        <p class="grading-preview-subtitle mb-0">{{ $loaiKhoaLuan === 'KLKS' ? 'Khóa luận kỹ sư' : 'Khóa luận cử nhân' }} · {{ $activeHoiDong->MaHoiDong }} · {{ $activeNhom->TenNhom ?? 'Nhóm sinh viên' }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng xem trước"></button>
            </div>
            <div class="modal-body grading-preview-body">
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
                                (Ký và ghi rõ họ tên sau khi in)
                            </div>
                            <div style="height: 60px; margin: 4px 0;"></div>
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
            <div class="modal-footer grading-preview-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary grading-preview-close" data-bs-dismiss="modal">Đóng</button>
                <div class="grading-preview-actions">
                    @if($coThongTinChucNang)
                        <button type="button" class="btn btn-sm btn-primary" onclick="inPhieuTrucTiep()">
                            <i class="fa-solid fa-file-pdf me-1"></i> Xuất PDF
                        </button>
                        <a href="{{ route('giangvien.chamdiem.xuatWord', ['MaHoiDong' => $activeHoiDong->MaHoiDong, 'MaHoSo' => $activeHoSo->MaHoSo]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-file-word me-1"></i> Xuất Word
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fa-solid fa-print me-1"></i> In A4
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-file-pdf me-1"></i> Xuất PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="alertChuaCoChucNang()">
                            <i class="fa-solid fa-file-word me-1"></i> Xuất Word
                        </button>
                    @endif
                    @if(!$daKhoaPhieu)
                        <button type="button" class="btn btn-sm btn-success" onclick="submitChamDiem('xac_nhan_hoan_thanh')">
                            <i class="fa-solid fa-lock me-1"></i> Xác nhận &amp; khóa phiếu
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL XEM ĐỀ CƯƠNG CHI TIẾT ĐÃ PHÊ DUYỆT (DÀNH CHO THƯ KÝ & THÀNH VIÊN HỘI ĐỒNG - YÊU CẦU 3.2) --}}
<div class="modal fade" id="modalXemDeCuong" tabindex="-1" aria-labelledby="modalXemDeCuongLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: min(900px, 95vw); width: 100%; margin: 1.75rem auto;">
        <div class="modal-content border-0 shadow-lg rounded-3" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 flex-shrink-0" style="min-width: 0;">
                <div class="d-flex align-items-center gap-2" style="min-width: 0; flex: 1 1 auto; overflow: hidden;">
                    <span class="p-2 rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-book-open"></i>
                    </span>
                    <div style="min-width: 0;">
                        <h6 class="modal-title fw-bold mb-0 text-white" id="modalXemDeCuongLabel" style="overflow-wrap: anywhere; word-break: break-word;">
                            Hồ Sơ Đề Cương Khóa Luận Đã Được Phê Duyệt (Chế Độ Xem Chỉ Đọc)
                        </h6>
                        <span class="text-white-50 d-block" style="font-size: 0.72rem; overflow-wrap: anywhere; word-break: break-word;">Căn cứ để Thư ký hội đồng thiết lập tiêu chí chấm điểm và các giám khảo đối chiếu đánh giá</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light" style="overflow-x: hidden; overflow-y: auto; max-height: calc(90vh - 130px); min-width: 0;">
                <!-- Thông tin tổng quan đề tài -->
                <div class="card border-0 rounded-3 shadow-xs p-3 bg-white mb-3" style="min-width: 0;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="flex-wrap: wrap; gap: 8px; min-width: 0;">
                        <div class="d-flex flex-wrap align-items-center" style="flex-wrap: wrap; gap: 8px; min-width: 0;">
                            <span class="badge bg-secondary-subtle text-secondary border font-monospace">Hồ sơ #{{ $activeHoSo->MaHoSo }}</span>
                            <span class="badge bg-primary-subtle text-primary border"><i class="fa-solid fa-graduation-cap me-1"></i>{{ $activeHoSo->ten_nganh }}</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 flex-shrink-0">Đề cương đã duyệt</span>
                    </div>
                    <h5 class="fw-bold text-primary mt-2 mb-1" style="overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</h5>
                    <div class="mt-2 pt-2 border-top small text-muted" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; min-width: 0;">
                        <div style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                            <strong>Cán bộ hướng dẫn:</strong> <span class="text-dark" style="overflow-wrap: anywhere; word-break: break-word;">{{ $gvhdHoTen }}</span> ({{ $activeDeTai?->giangVien?->boMon?->TenBoMon ?? 'Bộ môn' }})
                        </div>
                        <div style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                            <strong>Nhóm thực hiện:</strong> <span class="text-dark" style="overflow-wrap: anywhere; word-break: break-word;">Nhóm {{ $activeNhom?->MaNhom }} ({{ $sinhViens->count() }} SV)</span>
                        </div>
                        <div style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                            <strong>Trạng thái chuẩn bị phiếu:</strong> 
                            @if(($activeHoSo->TrangThaiChuanBiPhieu ?? '') === 'DaChuanBi')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Đã chuẩn bị phiếu</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Chưa chuẩn bị phiếu</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Danh sách sinh viên -->
                <div class="card border-0 rounded-3 shadow-xs p-3 bg-white mb-3" style="min-width: 0;">
                    <h6 class="fw-bold text-dark small text-uppercase mb-2"><i class="fa-solid fa-users me-2 text-primary"></i>Sinh viên thực hiện đề tài</h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; min-width: 0;">
                        @foreach($sinhViens as $idx => $sv)
                        <div class="p-2 border rounded-2 bg-light" style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                            <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $sv->HoTen }}</div>
                            <div class="small text-muted font-monospace" style="overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $sv->MaSV }} &bull; {{ $sv->lop?->TenLop ?? 'Lớp' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Chi tiết nội dung đề cương -->
                <div class="card border-0 rounded-3 shadow-xs p-3 bg-white mb-3" style="min-width: 0;">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3"><i class="fa-solid fa-file-lines me-2 text-primary"></i>Nội dung đề cương chi tiết</h6>
                    <div class="space-y-3" style="min-width: 0;">
                        <div class="mb-3" style="min-width: 0;">
                            <label class="form-label small fw-bold text-secondary mb-1">1. Mục tiêu đề tài:</label>
                            <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $activeDeTai?->MucTieu ?: ($activeDeTai?->ban_nhap_data['muc_tieu'] ?? 'Thực hiện đầy đủ các mục tiêu trong đề cương được phê duyệt bởi Bộ môn.') }}</div>
                        </div>
                        <div class="mb-3" style="min-width: 0;">
                            <label class="form-label small fw-bold text-secondary mb-1">2. Phạm vi thực hiện:</label>
                            <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $activeDeTai?->PhamVi ?: ($activeDeTai?->ban_nhap_data['pham_vi'] ?? 'Phạm vi nghiên cứu, thiết kế và phát triển đồ án tốt nghiệp.') }}</div>
                        </div>
                        <div class="mb-3" style="min-width: 0;">
                            <label class="form-label small fw-bold text-secondary mb-1">3. Mô tả &amp; Nội dung nghiên cứu:</label>
                            <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $activeDeTai?->NoiDung ?: ($activeDeTai?->MoTa ?: 'Khảo sát hiện trạng, phân tích yêu cầu nghiệp vụ, thiết kế kiến trúc hệ thống, cài đặt chức năng và thử nghiệm thực tế.') }}</div>
                        </div>
                        <div class="mb-3" style="min-width: 0;">
                            <label class="form-label small fw-bold text-secondary mb-1">4. Yêu cầu bài toán &amp; Kỹ thuật:</label>
                            <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $activeDeTai?->YeuCau ?: 'Thực hiện đầy đủ các yêu cầu theo phân công và hướng dẫn của cán bộ hướng dẫn.' }}</div>
                        </div>
                        <div class="mb-3" style="min-width: 0;">
                            <label class="form-label small fw-bold text-secondary mb-1">5. Sản phẩm dự kiến:</label>
                            <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; min-width: 0;">{{ $activeDeTai?->SanPhamDuKien ?: ($activeDeTai?->ban_nhap_data['san_pham'] ?? 'Báo cáo toàn văn khóa luận, phần mềm ứng dụng hoàn chỉnh, tài liệu hướng dẫn kỹ thuật.') }}</div>
                        </div>
                        @if(!empty($activeDeTai?->FileDeCuong))
                        @php
                            $dcFileUrl = Str::startsWith($activeDeTai->FileDeCuong, ['http', 'storage/']) ? asset($activeDeTai->FileDeCuong) : asset('storage/' . $activeDeTai->FileDeCuong);
                            $dcFileName = basename($activeDeTai->FileDeCuong);
                        @endphp
                        <div class="mb-2" style="min-width: 0;">
                            <label class="form-label small fw-bold text-secondary mb-1">6. Tệp tài liệu đề cương gốc đã duyệt:</label>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="quickPreviewOutline('{{ $dcFileUrl }}', '{{ $dcFileName }}', '{{ addslashes($activeDeTai->TenDeTai ?? '') }}')">
                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i> Xem Tệp Đề Cương PDF Gốc
                                </button>
                                <a href="{{ $dcFileUrl }}" download="{{ $dcFileName }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="fa-solid fa-download me-1"></i> Tải về
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2 d-flex justify-content-between align-items-center flex-shrink-0" style="min-width: 0;">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                @if($isThuKy || (Auth::user() && (Auth::user()->isTruongKhoa() || Auth::user()->isGiaoVu() || Auth::user()->isAdmin())))
                    <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalCaiDatChucNang">
                        <i class="fa-solid fa-pen-ruler me-1"></i> Bắt Đầu Chuẩn Bị Phiếu Chấm Từ Đề Cương Này
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL CÀI ĐẶT CHỨC NĂNG THEO YÊU CẦU BÀI TOÁN (DÀNH CHO THƯ KÝ HỘI ĐỒNG - YÊU CẦU 3.1 & 3.3) --}}
@if($activeHoiDong && $activeHoSo && ($isThuKy || (Auth::user() && (Auth::user()->isTruongKhoa() || Auth::user()->isGiaoVu() || Auth::user()->isAdmin()))))
@php
    $dtMoTa = $activeDeTai->MoTa ?? ($activeDeTai->NoiDung ?? '');
    $dtYeuCau = $activeDeTai->YeuCau ?? '';
    $dtMucTieu = $activeDeTai->MucTieu ?? ($activeDeTai->ban_nhap_data['muc_tieu'] ?? '');
    $dtPhamVi = $activeDeTai->PhamVi ?? ($activeDeTai->ban_nhap_data['pham_vi'] ?? '');
    $dtSanPham = $activeDeTai->SanPhamDuKien ?? ($activeDeTai->ban_nhap_data['san_pham'] ?? '');
    $dtFile = $activeDeTai->FileDeCuong ?? '';
    $dtFileUrl = '';
    $dtFileName = '';
    if (!empty($dtFile)) {
        $dtFileUrl = Str::startsWith($dtFile, ['http', 'storage/']) ? asset($dtFile) : asset('storage/' . $dtFile);
        $dtFileName = basename($dtFile);
    }
@endphp
<div class="modal fade" id="modalCaiDatChucNang" tabindex="-1" aria-labelledby="modalCaiDatChucNangLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: min(1280px, 96vw);">
        <form id="formCaiDatChucNang" action="{{ route('giangvien.chamdiem.capNhatChucNang') }}" method="POST">
            @csrf
            <input type="hidden" name="MaHoiDong" value="{{ $activeHoiDong->MaHoiDong }}">
            <input type="hidden" name="MaHoSo" value="{{ $activeHoSo->MaHoSo }}">
            <input type="hidden" id="inputLoaiKhoaLuan" name="LoaiKhoaLuan" value="{{ $loaiKhoaLuan }}">

            {{-- Kho dữ liệu đề cương ẩn để nạp nhanh --}}
            <div id="outlineDataStore" class="d-none"
                 data-mo-ta="{{ e($dtMoTa) }}"
                 data-noi-dung="{{ e($dtMoTa) }}"
                 data-yeu-cau="{{ e($dtYeuCau) }}"
                 data-muc-tieu="{{ e($dtMucTieu) }}"
                 data-pham-vi="{{ e($dtPhamVi) }}"
                 data-san-pham="{{ e($dtSanPham) }}"
                 data-file-de-cuong="{{ e($dtFile) }}"
                 data-file-url="{{ e($dtFileUrl) }}"
                 data-file-name="{{ e($dtFileName) }}"
                 data-ten-de-tai="{{ e($activeDeTai->TenDeTai ?? '') }}"
                 data-json-decuong="{{ e(json_encode($activeHoSo->NoiDungDeCuongJson ?? [])) }}"></div>

            <div class="modal-content border-0 shadow-lg rounded-3">
                {{-- Modal Header: Tiêu đề trang trọng, không có phụ đề --}}
                <div class="modal-header bg-warning-subtle text-dark border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-pen-ruler"></i>
                        </span>
                        <h6 class="modal-title fw-bold mb-0 text-dark" id="modalCaiDatChucNangLabel">
                            Thiết Lập Tiêu Chí Phiếu Chấm &amp; Yêu Cầu Chức Năng (Dành Cho Thư Ký Hội Đồng)
                        </h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3 p-md-4 bg-white">
                    {{-- Banner thông báo phạm vi lưu chỉ riêng cho nhóm đề tài này (Không có phụ đề thừa) --}}
                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3 border-0 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <strong>Phạm vi áp dụng:</strong> Cấu hình này <strong>chỉ lưu và áp dụng riêng cho Nhóm đề tài này</strong> (Mã hồ sơ: <strong>#{{ $activeHoSo->MaHoSo }}</strong>).
                        </div>
                        <span class="badge bg-warning text-dark border border-warning px-3 py-1 fw-bold fs-7">
                            {{ $loaiKhoaLuan === 'KLKS' ? 'Hệ Kỹ Sư (Trần: 4.00đ)' : 'Hệ Cử Nhân (Trần: 3.50đ)' }}
                        </span>
                    </div>

                    {{-- Thông tin đề tài --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Đề tài đang chấm điểm:</label>
                        <div class="p-2 bg-light rounded border small fw-semibold text-primary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <i class="fa-solid fa-book-bookmark me-2"></i>{{ $activeDeTai->TenDeTai ?? '' }}
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.7rem;">
                                {{ $activeHoSo->ten_nganh }}
                            </span>
                        </div>
                    </div>

                    {{-- Hàng 3 nút cùng kích thước & kiểu dáng --}}
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3 d-inline-flex align-items-center gap-1 shadow-xs" id="btnImportDeCuong" onclick="importFromDeCuong()" style="height: 34px;">
                            <i class="fa-solid fa-file-import me-1 text-primary"></i> Nhập từ đề cương
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info fw-semibold px-3 d-inline-flex align-items-center gap-1 shadow-xs" id="btnToggleXemDeCuong" onclick="toggleDeCuongTab()" style="height: 34px;">
                            <i class="fa-solid fa-book-open me-1 text-info"></i> Xem đề cương
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3 d-inline-flex align-items-center gap-1 shadow-xs" onclick="loadSampleTemplate('KLCN')" style="height: 34px;" {{ $loaiKhoaLuan === 'KLKS' ? 'disabled' : '' }} title="{{ $loaiKhoaLuan === 'KLKS' ? 'Đề tài này thuộc hệ Kỹ sư, không áp dụng Mẫu Cử nhân' : 'Áp dụng mẫu chuẩn Cử nhân (3.5đ)' }}">
                            <i class="fa-solid fa-file-lines me-1"></i> Mẫu Cử nhân
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3 d-inline-flex align-items-center gap-1 shadow-xs" onclick="loadSampleTemplate('KLKS')" style="height: 34px;" {{ $loaiKhoaLuan === 'KLCN' ? 'disabled' : '' }} title="{{ $loaiKhoaLuan === 'KLCN' ? 'Đề tài này thuộc hệ Cử nhân, không áp dụng Mẫu Kỹ sư' : 'Áp dụng mẫu chuẩn Kỹ sư (4.0đ)' }}">
                            <i class="fa-solid fa-file-lines me-1"></i> Mẫu Kỹ sư
                        </button>
                    </div>

                    {{-- Banner hướng dẫn trực quan khi mở xem đề cương --}}
                    <div id="outlineNoticeBanner" class="alert alert-info py-2 px-3 small rounded-3 mb-3 border-0 bg-info-subtle text-info-emphasis d-none align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-info fs-6"></i>
                            <span id="outlineNoticeText">Đang hiển thị Đề cương chi tiết của đề tài ở cột bên phải. Thầy/Cô có thể đối chiếu nội dung đề cương để tự do nhập/chỉnh sửa thông tin tiêu chí chấm ở khung bên trái.</span>
                        </div>
                        <button type="button" class="btn-close btn-close-sm" onclick="document.getElementById('outlineNoticeBanner').classList.remove('d-flex'); document.getElementById('outlineNoticeBanner').classList.add('d-none');" aria-label="Close"></button>
                    </div>

                    <div class="row g-3">
                        {{-- Cột Trái: Textarea duy nhất --}}
                        <div class="col-lg-6 col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <label for="textareaChucNang" class="form-label small fw-bold text-dark mb-0">
                                        Soạn thảo nội dung chức năng &amp; yêu cầu:
                                    </label>
                                    <a href="javascript:void(0)" class="text-danger small ms-2 text-decoration-none fw-semibold" onclick="clearCriteria()">
                                        Xóa hết
                                    </a>
                                </div>
                                <span class="text-muted small" style="font-size: 0.72rem;">
                                    <kbd>Tab</kbd> để thụt dòng làm yêu cầu con &bull; <kbd>Shift+Tab</kbd> bỏ thụt
                                </span>
                            </div>

                            @php
                                $defaultPlaceholder = "Chức năng 1: Quản lý đơn hàng\n  Tiếp nhận đơn hàng\n  Thanh toán trực tuyến\nChức năng 2: Quản lý sản phẩm\n  Cập nhật tồn kho";
                            @endphp
                            <textarea id="textareaChucNang"
                                      name="TieuChiChucNang"
                                      rows="14"
                                      class="form-control font-monospace border rounded-3 p-3"
                                      style="min-height: 380px; font-size: 0.85rem; line-height: 1.6;"
                                      placeholder="{{ $defaultPlaceholder }}">{{ $customChucNang ?? '' }}</textarea>

                            <div id="textareaInlineAlert" class="small text-danger mt-1 d-none"></div>
                        </div>

                        {{-- Cột Phải: Khung 2 Tab (Đề Cương Đề Tài & Xem Trước Cấu Trúc Khung Chấm) --}}
                        <div class="col-lg-6 col-12">
                            <div class="card h-100 border rounded-3 bg-light-subtle shadow-sm">
                                <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <ul class="nav nav-pills card-header-pills gap-1" id="pillsRightTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link btn-xs py-1 px-3 fw-bold rounded-pill d-inline-flex align-items-center gap-1"
                                                    id="tab-decuong-btn"
                                                    data-bs-toggle="pill"
                                                    data-bs-target="#pane-decuong"
                                                    type="button"
                                                    role="tab"
                                                    aria-controls="pane-decuong"
                                                    aria-selected="false"
                                                    style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-book-open text-primary"></i> <span>Đề Cương Đề Tài</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link btn-xs py-1 px-3 fw-bold active rounded-pill d-inline-flex align-items-center gap-1"
                                                    id="tab-preview-btn"
                                                    data-bs-toggle="pill"
                                                    data-bs-target="#pane-preview"
                                                    type="button"
                                                    role="tab"
                                                    aria-controls="pane-preview"
                                                    aria-selected="true"
                                                    style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-eye text-success"></i> <span>Xem Trước Khung Chấm</span>
                                            </button>
                                        </li>
                                    </ul>
                                    <span id="previewSummaryText" class="small fw-semibold text-primary" style="font-size: 0.75rem;">
                                        0 yêu cầu &bull; mỗi yêu cầu &asymp; 0.00 đ &bull; tổng 0.00 / {{ $loaiKhoaLuan === 'KLKS' ? '4.00' : '3.50' }}đ
                                    </span>
                                </div>

                                <div class="card-body p-0 bg-white tab-content" style="min-height: 380px; max-height: 520px; overflow-y: auto;">
                                    {{-- PANE 1: ĐỀ CƯƠNG ĐỀ TÀI ĐÃ DUYỆT (CHẾ ĐỘ XEM ĐỐI CHIẾU CHO THƯ KÝ) --}}
                                    <div class="tab-pane fade p-3" id="pane-decuong" role="tabpanel" aria-labelledby="tab-decuong-btn">
                                        {{-- Thanh tác vụ nhanh đề cương --}}
                                        <div class="d-flex align-items-center justify-content-between p-2 mb-3 rounded-2 bg-primary-subtle border border-primary-subtle flex-wrap gap-2">
                                            <div class="small fw-bold text-primary">
                                                <i class="fa-solid fa-circle-check text-success me-1"></i> Đề Cương Đã Phê Duyệt
                                            </div>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <button type="button" class="btn btn-xs btn-primary fw-bold px-2 py-1 shadow-sm" onclick="applyOutlineAllToTextarea()" title="Nạp tự động các phân hệ chức năng từ đề cương vào ô soạn thảo">
                                                    <i class="fa-solid fa-bolt me-1"></i> Nạp vào ô soạn thảo
                                                </button>
                                                @if(!empty($dtFileUrl))
                                                <button type="button" class="btn btn-xs btn-outline-danger fw-semibold px-2 py-1 bg-white" onclick="quickPreviewOutline('{{ $dtFileUrl }}', '{{ $dtFileName }}', '{{ addslashes($activeDeTai->TenDeTai ?? '') }}')" title="Xem trực tiếp tệp PDF gốc">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> File PDF gốc
                                                </button>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Tóm tắt đề tài & GVHD --}}
                                        <div class="mb-3 p-2 bg-light rounded border">
                                            <div class="fw-bold text-dark small mb-1">{{ $activeDeTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</div>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                <span><i class="fa-solid fa-user-tie me-1 text-secondary"></i>GVHD: <strong>{{ $gvhdHoTen }}</strong></span>
                                                <span class="mx-1">&bull;</span>
                                                <span><i class="fa-solid fa-users me-1 text-secondary"></i>Nhóm: <strong>{{ $activeNhom?->MaNhom }} ({{ $sinhViens->pluck('HoTen')->implode(', ') }})</strong></span>
                                            </div>
                                        </div>

                                        {{-- 1. Mục tiêu (nếu có) --}}
                                        @if(!empty($dtMucTieu))
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label small fw-bold text-secondary mb-0">
                                                    <i class="fa-solid fa-bullseye text-danger me-1"></i> 1. Mục tiêu đề tài:
                                                </label>
                                                <button type="button" class="btn btn-link btn-xs text-decoration-none p-0 text-primary small" onclick="insertSnippetToTextarea(`{{ addslashes($dtMucTieu) }}`)">
                                                    <i class="fa-solid fa-plus me-1"></i> Chèn vào ô nhập
                                                </button>
                                            </div>
                                            <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; font-size: 0.82rem;">{{ $dtMucTieu }}</div>
                                        </div>
                                        @endif

                                        {{-- 2. Mô tả & Phân hệ chức năng --}}
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label small fw-bold text-secondary mb-0">
                                                    <i class="fa-solid fa-list-check text-primary me-1"></i> 2. Mô tả &amp; Phân hệ chức năng:
                                                </label>
                                                @if(!empty($dtMoTa))
                                                <button type="button" class="btn btn-link btn-xs text-decoration-none p-0 text-primary small" onclick="insertSnippetToTextarea(`{{ addslashes($dtMoTa) }}`)">
                                                    <i class="fa-solid fa-plus me-1"></i> Chèn vào ô nhập
                                                </button>
                                                @endif
                                            </div>
                                            <div class="p-2 bg-light rounded border small text-dark" id="deCuongMoTaBox" style="white-space: pre-wrap; font-size: 0.82rem;">{{ $dtMoTa ?: 'Chưa cập nhật mô tả chi tiết trong đề cương.' }}</div>
                                        </div>

                                        {{-- 3. Yêu cầu bài toán & Kỹ thuật --}}
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label small fw-bold text-secondary mb-0">
                                                    <i class="fa-solid fa-clipboard-check text-success me-1"></i> 3. Yêu cầu bài toán &amp; Kỹ thuật:
                                                </label>
                                                @if(!empty($dtYeuCau))
                                                <button type="button" class="btn btn-link btn-xs text-decoration-none p-0 text-primary small" onclick="insertSnippetToTextarea(`{{ addslashes($dtYeuCau) }}`)">
                                                    <i class="fa-solid fa-plus me-1"></i> Chèn vào ô nhập
                                                </button>
                                                @endif
                                            </div>
                                            <div class="p-2 bg-light rounded border small text-dark" id="deCuongYeuCauBox" style="white-space: pre-wrap; font-size: 0.82rem;">{{ $dtYeuCau ?: 'Thực hiện đầy đủ theo yêu cầu và hướng dẫn của GVHD.' }}</div>
                                        </div>

                                        {{-- 4. Phạm vi & Sản phẩm dự kiến (nếu có) --}}
                                        @if(!empty($dtPhamVi) || !empty($dtSanPham))
                                        <div class="mb-3">
                                            @if(!empty($dtPhamVi))
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-secondary mb-1"><i class="fa-solid fa-compass me-1 text-info"></i> Phạm vi nghiên cứu:</label>
                                                <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; font-size: 0.82rem;">{{ $dtPhamVi }}</div>
                                            </div>
                                            @endif
                                            @if(!empty($dtSanPham))
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-secondary mb-1"><i class="fa-solid fa-box-archive me-1 text-warning"></i> Sản phẩm dự kiến:</label>
                                                <div class="p-2 bg-light rounded border small text-dark" style="white-space: pre-wrap; font-size: 0.82rem;">{{ $dtSanPham }}</div>
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                        {{-- 5. Tệp PDF đề cương gốc (nếu có) --}}
                                        @if(!empty($dtFileUrl))
                                        <div class="p-2 rounded border bg-light d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                                <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                                <div class="small text-truncate">
                                                    <span class="fw-bold text-dark d-block text-truncate">{{ $dtFileName }}</span>
                                                    <span class="text-muted" style="font-size: 0.7rem;">Tài liệu đề cương gốc đã phê duyệt</span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1 flex-shrink-0">
                                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="quickPreviewOutline('{{ $dtFileUrl }}', '{{ $dtFileName }}', '{{ addslashes($activeDeTai->TenDeTai ?? '') }}')">
                                                    <i class="fa-solid fa-eye me-1"></i> Xem PDF
                                                </button>
                                                <a href="{{ $dtFileUrl }}" download="{{ $dtFileName }}" class="btn btn-xs btn-outline-secondary">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- PANE 2: XEM TRƯỚC CẤU TRÚC KHUNG CHẤM --}}
                                    <div class="tab-pane fade show active p-3" id="pane-preview" role="tabpanel" aria-labelledby="tab-preview-btn">
                                        <div id="previewParsedContainer">
                                            {{-- Render danh sách nhóm và yêu cầu --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer bg-light py-2 d-flex justify-content-between align-items-center">
                    <div class="small text-muted" id="footerDraftNotice" style="font-size: 0.75rem;">
                        Sẵn sàng lưu.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" id="btnSubmitChucNang" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu &amp; Cập Nhật
                        </button>
                    </div>
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
        alert('CHƯA THỂ XUẤT / IN PHIẾU CHẤM ĐIỂM:\n\nĐề tài này chưa được thiết lập chi tiết mục 6 ("Cài đặt chức năng theo yêu cầu bài toán").\n\nQuy định của Khoa: Phiếu chấm điểm chỉ được phép xuất File Word hoặc in PDF sau khi Thư ký Hội đồng đã cấu hình và chuẩn bị đầy đủ các yêu cầu chức năng riêng cho đề tài này.\n\nVui lòng liên hệ Thư ký Hội đồng nhấn nút "Cài đặt chức năng (Thư Ký)" để cập nhật!');
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

    // ── XỬ LÝ SOẠN THẢO VÀ LIVE PREVIEW MODAL CÀI ĐẶT CHỨC NĂNG (DÀNH CHO THƯ KÝ) ──
    const modalEl = document.getElementById('modalCaiDatChucNang');
    const textareaChucNang = document.getElementById('textareaChucNang');
    const previewSummaryText = document.getElementById('previewSummaryText');
    const previewParsedContainer = document.getElementById('previewParsedContainer');
    const btnSubmitChucNang = document.getElementById('btnSubmitChucNang');
    const textareaInlineAlert = document.getElementById('textareaInlineAlert');
    const footerDraftNotice = document.getElementById('footerDraftNotice');

    const activeLoaiKhoaLuan = '{{ $loaiKhoaLuan ?? "KLCN" }}';
    const targetScoreCap = (activeLoaiKhoaLuan === 'KLKS') ? 4.00 : 3.50;
    const activeHoSoMa = '{{ $activeHoSo->MaHoSo ?? "" }}';
    const draftStorageKey = 'kltn_chucnang_draft_' + activeHoSoMa;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Phân bổ điểm chính xác theo cent (tổng luôn đúng bằng trần)
    function distributeScoresExact(target, count) {
        if (count <= 0) return [];
        const targetCents = Math.round(target * 100);
        const baseCents = Math.floor(targetCents / count);
        const remainder = targetCents % count;
        const scores = [];
        for (let i = 0; i < count; i++) {
            const cents = baseCents + (i < remainder ? 1 : 0);
            scores.push(parseFloat((cents / 100).toFixed(2)));
        }
        return scores;
    }

    // Tự động nhận diện cấu trúc phân cấp từ văn bản thuần:
    // - Dòng không thụt lề = Nhóm chức năng (hoặc bắt đầu bằng Chức năng, CN, số La Mã, 1., I.)
    // - Dòng có thụt lề (Tab hoặc 2+ space) = Yêu cầu con (hoặc bắt đầu bằng -, *, •, a), 1.1)
    // - Nhóm không có yêu cầu con thì tự coi nhóm đó là một yêu cầu (không báo lỗi).
    function parseTextToGroups(text) {
        const lines = (text || '').split('\n');
        const rawGroups = [];
        let currentGroup = null;

        lines.forEach(rawLine => {
            const trimmed = rawLine.trim();
            if (!trimmed) return;

            const isIndented = /^(\t|\s{2,})/.test(rawLine);
            const isExplicitGroup = /^(Chức năng|CN|Module|Phần|\d+\.|\b[IVXLCDM]+\.)/i.test(trimmed);
            const isExplicitSub = /^([\*\-\•\+]|[a-z]\)|\d+\.\d+)/i.test(trimmed);

            let isGroup = false;
            if (isExplicitSub) {
                isGroup = false;
            } else if (isExplicitGroup) {
                isGroup = true;
            } else if (isIndented) {
                isGroup = false;
            } else {
                isGroup = true;
            }

            if (isGroup) {
                const cleanName = trimmed.replace(/^[\+\-\*\•\#\s]+/, '').trim();
                currentGroup = {
                    name: cleanName,
                    items: []
                };
                rawGroups.push(currentGroup);
            } else {
                let cleanItem = trimmed
                    .replace(/^([\*\-\•\+\#\s]+|[a-z]\)\s*|\d+\.\d+\s*|Yêu cầu\s*\d*:?\s*)/i, '')
                    .replace(/\s*[\(\[]\s*[\d\.\,]+\s*(?:đ|điểm|pts?)\s*[\)\]]$/i, '')
                    .trim();

                if (cleanItem) {
                    if (!currentGroup) {
                        currentGroup = {
                            name: (activeLoaiKhoaLuan === 'KLKS') ? 'Chức năng nghiệp vụ' : 'Chức năng bài toán',
                            items: []
                        };
                        rawGroups.push(currentGroup);
                    }
                    currentGroup.items.push(cleanItem);
                }
            }
        });

        // Nhóm không có yêu cầu con thì tự coi nhóm đó là một yêu cầu
        const finalGroups = [];
        rawGroups.forEach(g => {
            if (g.items.length === 0) {
                finalGroups.push({
                    name: (activeLoaiKhoaLuan === 'KLKS') ? 'Chức năng nghiệp vụ' : 'Chức năng bài toán',
                    items: [g.name]
                });
            } else {
                finalGroups.push(g);
            }
        });

        // Gộp các nhóm liền kề có cùng tên
        const mergedGroups = [];
        finalGroups.forEach(g => {
            if (mergedGroups.length > 0 && mergedGroups[mergedGroups.length - 1].name === g.name) {
                mergedGroups[mergedGroups.length - 1].items.push(...g.items);
            } else {
                mergedGroups.push({ name: g.name, items: [...g.items] });
            }
        });

        let totalReqs = 0;
        mergedGroups.forEach(g => { totalReqs += g.items.length; });

        return { groups: mergedGroups, totalReqs };
    }

    // Cập nhật Live Preview khung xem trước
    function updateLivePreview() {
        if (!textareaChucNang) return;
        const text = textareaChucNang.value.trim();
        const { groups, totalReqs } = parseTextToGroups(text);

        const scores = distributeScoresExact(targetScoreCap, totalReqs);
        let totalScore = 0;
        scores.forEach(s => { totalScore += s; });
        const formattedTotal = totalReqs > 0 ? totalScore.toFixed(2) : '0.00';
        const avgScore = totalReqs > 0 ? (targetScoreCap / totalReqs).toFixed(2) : '0.00';
        const targetFormatted = targetScoreCap.toFixed(2);

        if (previewSummaryText) {
            previewSummaryText.innerHTML = `${totalReqs} yêu cầu &bull; mỗi yêu cầu &asymp; ${avgScore} đ &bull; tổng ${formattedTotal} / ${targetFormatted}đ`;
        }

        if (btnSubmitChucNang) {
            btnSubmitChucNang.disabled = (totalReqs === 0);
            btnSubmitChucNang.title = (totalReqs === 0) ? 'Vui lòng nhập ít nhất 1 yêu cầu chức năng.' : '';
        }

        if (textareaInlineAlert) {
            if (text.length > 0 && totalReqs === 0) {
                textareaInlineAlert.textContent = 'Vui lòng nhập nội dung yêu cầu hợp lệ.';
                textareaInlineAlert.classList.remove('d-none');
            } else {
                textareaInlineAlert.textContent = '';
                textareaInlineAlert.classList.add('d-none');
            }
        }

        if (previewParsedContainer) {
            if (totalReqs === 0) {
                previewParsedContainer.innerHTML = `
                    <div class="text-center py-4 text-muted small fst-italic">
                        Chưa có yêu cầu nào.<br>Hãy gõ danh sách bên trái hoặc chọn <strong>[Nhập từ đề cương]</strong> / <strong>[Mẫu chuẩn]</strong>.
                    </div>
                `;
                return;
            }

            let html = '';
            let scoreIdx = 0;
            groups.forEach(g => {
                html += `
                <div class="mb-2 p-2 bg-light rounded border border-light-subtle">
                    <div class="fw-bold text-dark small mb-1 pb-1 border-bottom d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-folder me-1 text-primary"></i> ${escapeHtml(g.name)}</span>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.65rem;">${g.items.length} YC</span>
                    </div>
                    <div class="space-y-1">
                `;
                g.items.forEach(item => {
                    const s = scores[scoreIdx++] || 0.50;
                    html += `
                    <div class="d-flex justify-content-between align-items-center py-1 ps-2 border-bottom border-light" style="font-size: 0.75rem;">
                        <span class="text-dark text-truncate me-2" title="${escapeHtml(item)}">
                            &bull; ${escapeHtml(item)}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.65rem;">
                            +${s.toFixed(2)}đ
                        </span>
                    </div>
                    `;
                });
                html += `</div></div>`;
            });
            previewParsedContainer.innerHTML = html;
        }
    }

    // Tự động lưu nháp vào LocalStorage
    function autoSaveDraft() {
        if (!textareaChucNang) return;
        try {
            localStorage.setItem(draftStorageKey, textareaChucNang.value);
            if (footerDraftNotice) {
                const now = new Date();
                const timeStr = `${now.getHours()}:${now.getMinutes() < 10 ? '0' : ''}${now.getMinutes()}`;
                footerDraftNotice.innerHTML = `<i class="fa-solid fa-cloud-arrow-up text-success me-1"></i> Đã lưu nháp lúc ${timeStr}`;
            }
        } catch (e) {}
    }

    // Chuyển tab hiển thị Đề Cương Đề Tài (Đã Phê Duyệt)
    window.showOutlineTab = function() {
        const tabBtn = document.getElementById('tab-decuong-btn');
        if (tabBtn && window.bootstrap) {
            const tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
            tab.show();
        }
        const banner = document.getElementById('outlineNoticeBanner');
        if (banner) {
            banner.classList.remove('d-none');
            banner.classList.add('d-flex');
        }
    };

    // Toggle chuyển đổi qua lại giữa Đề Cương và Xem Trước
    window.toggleDeCuongTab = function() {
        const decuongPane = document.getElementById('pane-decuong');
        if (decuongPane && decuongPane.classList.contains('active')) {
            const previewBtn = document.getElementById('tab-preview-btn');
            if (previewBtn && window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(previewBtn).show();
            }
        } else {
            showOutlineTab();
        }
    };

    // Chèn đoạn văn bản vào vị trí con trỏ của ô soạn thảo
    window.insertSnippetToTextarea = function(text) {
        if (!textareaChucNang || !text) return;
        const cleanText = text.trim();
        const start = textareaChucNang.selectionStart;
        const end = textareaChucNang.selectionEnd;
        const currentVal = textareaChucNang.value;
        const prefix = (start > 0 && !currentVal.substring(0, start).endsWith('\n')) ? '\n' : '';
        const suffix = (!currentVal.substring(end).startsWith('\n')) ? '\n' : '';
        
        textareaChucNang.value = currentVal.substring(0, start) + prefix + cleanText + suffix + currentVal.substring(end);
        textareaChucNang.focus();
        updateLivePreview();
        autoSaveDraft();
    };

    // Nạp tự động các phân hệ chức năng từ Đề Cương vào ô soạn thảo
    window.applyOutlineAllToTextarea = function() {
        const store = document.getElementById('outlineDataStore');
        if (!store) return;
        const moTa = store.getAttribute('data-mo-ta') || store.getAttribute('data-noi-dung') || '';
        const yeuCau = store.getAttribute('data-yeu-cau') || '';
        const mucTieu = store.getAttribute('data-muc-tieu') || '';

        const fullSource = `${moTa}\n${yeuCau}\n${mucTieu}`.trim();
        if (!fullSource) {
            alert('Chưa tìm thấy nội dung chi tiết trong đề cương của đề tài này.');
            return;
        }

        const rawLines = fullSource.split('\n');
        const extracted = [];
        rawLines.forEach(l => {
            const t = l.trim().replace(/^[\-\*\+\d\.\)]+/, '').trim();
            if (t.length > 8 && t.length < 250) {
                extracted.push(t);
            }
        });

        if (extracted.length === 0) {
            alert('Không thể nhận diện danh sách chức năng từ đề cương. Đã nạp mẫu theo loại khóa luận.');
            loadSampleTemplate(activeLoaiKhoaLuan);
            return;
        }

        let resultText = '';
        const chunkSize = Math.max(2, Math.ceil(extracted.length / 3));
        for (let i = 0; i < extracted.length; i += chunkSize) {
            const slice = extracted.slice(i, i + chunkSize);
            const gNum = Math.floor(i / chunkSize) + 1;
            resultText += `Phân hệ chức năng ${gNum < 10 ? '0' + gNum : gNum}:\n`;
            slice.forEach(item => {
                resultText += `  ${item}\n`;
            });
        }

        textareaChucNang.value = resultText.trim();
        updateLivePreview();
        autoSaveDraft();
        textareaChucNang.focus();
    };

    // Nhập từ Đề Cương:
    // 1. Luôn mở hiển thị Đề cương của đề tài đó để thư ký xem đối chiếu ngay tại chỗ
    // 2. Tùy theo trạng thái ô nhập: tự động nạp hoặc gợi ý nạp để thư ký dễ dàng dựa vào đó hoàn thiện thông tin
    window.importFromDeCuong = function() {
        showOutlineTab();

        const currentVal = textareaChucNang ? textareaChucNang.value.trim() : '';
        if (!currentVal) {
            applyOutlineAllToTextarea();
        } else {
            if (confirm('Đã mở nội dung Đề cương chi tiết ở cột bên phải để Thầy/Cô đối chiếu.\n\nThầy/Cô có muốn tự động nạp toàn bộ cấu trúc phân hệ từ đề cương vào khung soạn thảo không? (Chọn Hủy nếu Thầy/Cô muốn tự nhập dựa trên đề cương)')) {
                applyOutlineAllToTextarea();
            }
        }
    };

    // Nạp mẫu chuẩn theo loại khóa luận
    window.loadSampleTemplate = function(loai) {
        if (loai === 'KLKS') {
            textareaChucNang.value = "Chức năng cơ bản của ứng dụng:\n  Đăng nhập, đăng xuất, đổi mật khẩu, phân quyền ứng dụng\nCài đặt chức năng nghiệp vụ trên nền tảng Web:\n  Quản trị danh mục và dữ liệu hệ thống\n  Quy trình xử lý nghiệp vụ chính trên Web\n  Báo cáo thống kê số liệu và xuất tệp Excel/PDF\nCài đặt chức năng nghiệp vụ trên nền tảng Mobile:\n  Giao diện người dùng Mobile và xác thực sinh trắc học\n  Thao tác tác nghiệp thực địa qua thiết bị di động\n  Nhận thông báo đẩy (Push Notification) tức thời\n  Đồng bộ trạng thái và dữ liệu thời gian thực giữa Web và Mobile";
        } else {
            textareaChucNang.value = "Chức năng 01: Quản lý và xử lý đơn hàng tự động\n  Tiếp nhận và phân loại đơn hàng theo trạng thái\n  Tích hợp cổng thanh toán trực tuyến\nChức năng 02: Quản lý danh mục sản phẩm và kho hàng\n  Cập nhật tồn kho theo thời gian thực\n  Báo cáo thống kê doanh thu và hàng tồn kho\nChức năng 03: Phân hệ người dùng và thông báo đẩy\n  Đăng nhập đa kênh và xác thực OTP\n  Gửi thông báo đơn hàng qua Email/Zalo\n  Phân quyền vai trò và nhật ký thao tác hệ thống";
        }
        updateLivePreview();
        autoSaveDraft();
    };

    // Xóa hết toàn bộ
    window.clearCriteria = function() {
        if (confirm('Thầy/Cô có chắc chắn muốn xóa toàn bộ nội dung chức năng đã soạn thảo?')) {
            textareaChucNang.value = '';
            updateLivePreview();
            autoSaveDraft();
        }
    };

    // Bắt sự kiện trên Textarea
    if (textareaChucNang) {
        // Tab / Shift+Tab để thụt dòng / lùi dòng mà không mất focus
        textareaChucNang.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const val = this.value;

                if (e.shiftKey) {
                    const beforeStart = val.substring(0, start);
                    const lineStart = beforeStart.lastIndexOf('\n') + 1;
                    const currentLine = val.substring(lineStart, end);

                    if (currentLine.startsWith('  ')) {
                        this.value = val.substring(0, lineStart) + val.substring(lineStart + 2);
                        this.selectionStart = Math.max(lineStart, start - 2);
                        this.selectionEnd = Math.max(lineStart, end - 2);
                    } else if (currentLine.startsWith('\t')) {
                        this.value = val.substring(0, lineStart) + val.substring(lineStart + 1);
                        this.selectionStart = Math.max(lineStart, start - 1);
                        this.selectionEnd = Math.max(lineStart, end - 1);
                    }
                } else {
                    this.value = val.substring(0, start) + '  ' + val.substring(end);
                    this.selectionStart = this.selectionEnd = start + 2;
                }
                updateLivePreview();
                autoSaveDraft();
            }
        });

        textareaChucNang.addEventListener('input', function() {
            updateLivePreview();
            autoSaveDraft();
        });

        // Phục hồi bản nháp nếu ô trống
        try {
            const draft = localStorage.getItem(draftStorageKey);
            if (!textareaChucNang.value.trim() && draft && draft.trim()) {
                textareaChucNang.value = draft;
                if (footerDraftNotice) {
                    footerDraftNotice.innerHTML = `<i class="fa-solid fa-clock-rotate-left text-primary me-1"></i> Đã phục hồi bản nháp gần nhất`;
                }
            }
        } catch (e) {}

        updateLivePreview();
    }

    // Khi nộp form: xóa nháp
    const formCaiDatChucNang = document.getElementById('formCaiDatChucNang');
    if (formCaiDatChucNang) {
        formCaiDatChucNang.addEventListener('submit', function() {
            try { localStorage.removeItem(draftStorageKey); } catch (e) {}
        });
    }
});
</script>
@endpush
@endif
