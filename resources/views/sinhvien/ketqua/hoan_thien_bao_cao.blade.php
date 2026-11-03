@extends('layouts.sinhvien')

@section('page_title', 'Hoàn Thiện Báo Cáo Sau Bảo Vệ')

@push('styles')
<style>
/* ════ BẢNG LỊCH SỬ PHIÊN BẢN NỘP LẠI (CHỐNG TRÀN CHỮ & THANH CUỘN NGANG) ════ */
.table-history-versions {
    table-layout: fixed !important;
    width: 100% !important;
    margin-bottom: 0;
}

.table-history-versions th,
.table-history-versions td {
    min-width: 0 !important;
    vertical-align: middle;
}

.table-history-versions thead th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.text-break-anywhere {
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    min-width: 0;
}

.rich-comment-box {
    white-space: normal;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    line-height: 1.5;
}

.rich-comment-box p {
    margin-bottom: 0.35rem;
}

.rich-comment-box p:last-child {
    margin-bottom: 0;
}

.rich-comment-box img,
.rich-comment-box table,
.rich-comment-box pre {
    max-width: 100% !important;
}

.rich-comment-box pre {
    white-space: pre-wrap !important;
    overflow-wrap: anywhere !important;
}

.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.btn-toggle-expand {
    font-size: 0.73rem !important;
    cursor: pointer;
}

.file-action-link {
    font-size: 0.78rem !important;
    text-decoration: none !important;
    background: transparent;
    border: none;
    padding: 0;
    text-align: left;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}

.file-action-link:hover {
    text-decoration: underline !important;
}

.file-action-icon {
    width: 15px;
    text-align: center;
    flex-shrink: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- ═══ HEADER ═══ -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark, #003b73);">
                <i class="fa-solid fa-file-circle-check me-2 text-primary"></i>
                Hoàn Thiện Báo Cáo Sau Bảo Vệ (Lưu Trữ Khoa)
            </h4>
            <div class="text-muted small">
                Xem điểm số, nhận xét của Hội đồng, chỉnh sửa toàn văn báo cáo và nộp lại bản hoàn chỉnh để xét cấp bằng tốt nghiệp.
            </div>
        </div>
        <div>
            <a href="{{ route('sinhvien.ketqua.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-award me-1"></i> Xem Kết Quả &amp; Bảng Điểm
            </a>
        </div>
    </div>

    <!-- ═══ THÔNG TIN ĐỀ TÀI & HỘI ĐỒNG ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-light text-secondary border font-monospace mb-2">Mã hồ sơ: {{ $hoSo->MaHoSo }}</span>
                    <h5 class="fw-bold text-primary mb-2">{{ $nhom->deTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</h5>
                    <div class="small text-muted d-flex flex-wrap gap-3">
                        <span><i class="fa-solid fa-users me-1 text-primary"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong></span>
                        <span><i class="fa-solid fa-chalkboard-user me-1 text-success"></i>GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span>
                        <span><i class="fa-solid fa-landmark me-1 text-info"></i>Hội đồng: <strong>{{ $hoSo->hoiDong->TenHoiDong ?? 'Chưa có' }}</strong> ({{ $hoSo->PhongBaoVe ?? ($hoSo->hoiDong->DiaDiem ?? 'Phòng B.304') }})</span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    @php
                        $trangThaiSua = $hoSo->TrangThaiChinhSuaSauBaoVe ?? 'ChoSinhVienNop';
                    @endphp
                    @if($trangThaiSua === 'DaHoanThanh')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold fs-6">
                            <i class="fa-solid fa-circle-check me-1"></i> Báo Cáo Đã Đạt Chuẩn Lưu Trữ
                        </span>
                    @elseif($trangThaiSua === 'DaNop')
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-semibold fs-6">
                            <i class="fa-solid fa-hourglass-half me-1"></i> Đã Nộp — Chờ Giảng Viên Kiểm Tra
                        </span>
                    @elseif($trangThaiSua === 'YeuCauSuaTiep')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold fs-6">
                            <i class="fa-solid fa-rotate-right me-1"></i> Yêu Cầu Chỉnh Sửa Lại
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold fs-6">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Chờ Nộp Báo Cáo Hoàn Thiện
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ KHU VỰC 1: KẾT QUẢ ĐIỂM SỐ & GÓP Ý CỦA HỘI ĐỒNG ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-award text-warning me-2"></i>Kết Quả Điểm Số &amp; Nhận Xét Của Hội Đồng Bảo Vệ
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Điểm Chính Thức</div>
                        <div class="display-6 fw-bold text-primary mb-1">
                            {{ number_format((float)($ketQua->DiemChinhThuc ?? $ketQua->DiemTongKet ?? 0), 2) }}
                        </div>
                        <div class="small text-muted">
                            Hội đồng chấm: <strong>{{ number_format((float)($ketQua->DiemHoiDongTB ?? 0), 2) }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Xếp Loại Khóa Luận</div>
                        <div class="h3 fw-bold text-success mb-1">
                            {{ $ketQua->KetQua ?? 'Đạt' }}
                        </div>
                        <div class="small text-muted">Trạng thái: <strong class="text-success">Đã công bố chính thức</strong></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Thời Hạn Hoàn Thiện Báo Cáo</div>
                        @if($hanNop)
                            <div class="h5 fw-bold {{ $isQuaHan ? 'text-danger' : 'text-primary' }} mb-1">
                                <i class="fa-regular fa-clock me-1"></i> {{ $hanNop->format('d/m/Y H:i') }}
                            </div>
                            <div>
                                @if($isQuaHan)
                                    <span class="badge bg-danger">Đã hết hạn nộp lại</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                                        Còn {{ $hanNop->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="fw-semibold text-secondary mb-1">Theo thông báo từ Hội đồng</div>
                            <span class="badge bg-secondary-subtle text-secondary">Chưa chốt hạn chót</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kết luận & Yêu cầu chỉnh sửa chung của Hội đồng -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-2">
                    <i class="fa-solid fa-clipboard-list text-primary me-1"></i>Kết luận &amp; Yêu cầu chỉnh sửa chung của Hội đồng:
                </label>
                @if(!empty($hoSo->YeuCauChinhSuaSauBaoVe))
                    <div class="p-3 rounded-3 bg-light border text-dark" style="line-height: 1.6; font-size: 0.93rem;">
                        {!! \App\Helpers\HtmlHelper::renderRichText($hoSo->YeuCauChinhSuaSauBaoVe) !!}
                    </div>
                @else
                    <div class="p-3 rounded-3 bg-light border text-muted fst-italic">
                        Hội đồng không ghi chú yêu cầu chỉnh sửa riêng, sinh viên hoàn thiện quyển báo cáo theo góp ý tại buổi bảo vệ để nộp lưu chiểu.
                    </div>
                @endif
            </div>

            <!-- Nhận xét chi tiết từ phiếu chấm của từng giám khảo -->
            @if($phieuChamDiems->isNotEmpty())
                <div>
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-2">
                        <i class="fa-solid fa-comments text-info me-1"></i>Ý kiến góp ý &amp; nhận xét chi tiết từ các giám khảo:
                    </label>
                    <div class="row g-3">
                        @foreach($phieuChamDiems as $pcd)
                            @if(!empty(trim($pcd->NhanXet)))
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 border bg-white h-100 shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                            <strong class="text-dark">
                                                <i class="fa-solid fa-user-tie text-secondary me-1"></i>{{ $pcd->giangVien->HoTen ?? 'Giám khảo' }}
                                            </strong>
                                            <span class="badge bg-light text-secondary border small">
                                                {{ $pcd->giangVien->boMon->TenBoMon ?? 'Hội đồng' }}
                                            </span>
                                        </div>
                                        <div class="text-secondary small" style="white-space: pre-line; line-height: 1.5;">
                                            {!! \App\Helpers\HtmlHelper::clean($pcd->NhanXet) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ═══ KHU VỰC 2: BÁO CÁO ĐÃ NỘP TRƯỚC ĐÓ (BẢN NỘP BẢO VỆ) ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-file-lines text-info me-2"></i>Bản Báo Cáo Toàn Văn Đã Nộp Trước Bảo Vệ
            </span>
        </div>
        <div class="card-body p-4">
            @if($tepBaoCaoGoc)
                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light border flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-file-pdf fa-2x text-danger"></i>
                        <div>
                            <div class="fw-bold text-dark">{{ $tepBaoCaoGoc->TenTep ?? ($tepBaoCaoGoc->TenPhienBan ?? 'Bao_cao_toan_van_khoa_luan.pdf') }}</div>
                            <div class="small text-muted">
                                Ngày nộp: {{ $tepBaoCaoGoc->NgayNop ? \Carbon\Carbon::parse($tepBaoCaoGoc->NgayNop)->format('d/m/Y H:i') : ($tepBaoCaoGoc->created_at ? $tepBaoCaoGoc->created_at->format('d/m/Y H:i') : 'Đợt bảo vệ') }}
                                @if(!empty($tepBaoCaoGoc->DungLuong))
                                    &bull; Dung lượng: {{ round($tepBaoCaoGoc->DungLuong / 1024 / 1024, 2) }} MB
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ asset('storage/' . ($tepBaoCaoGoc->DuongDanFile ?? $tepBaoCaoGoc->DuongDanBaoCao)) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                            <i class="fa-solid fa-download me-1"></i> Tải Xem Lại Bản Cũ
                        </a>
                    </div>
                </div>
            @else
                <div class="text-muted small p-3 rounded-3 bg-light border">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> Chưa tìm thấy tệp đính kèm toàn văn trong hồ sơ nộp ban đầu.
                </div>
            @endif
        </div>
    </div>

    <!-- ═══ KHU VỰC 3 & 4: FORM NỘP LẠI BÁO CÁO HOÀN THIỆN ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border-top: 4px solid {{ $trangThaiSua === 'DaHoanThanh' ? '#198754' : ($isQuaHan ? '#dc3545' : '#0072ce') }} !important;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Nộp Báo Cáo Khóa Luận Hoàn Thiện
            </span>
            @if($isQuaHan)
                <span class="badge bg-danger rounded-pill px-3 py-2 fw-semibold">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> ĐÃ HẾT HẠN NỘP LẠI
                </span>
            @endif
        </div>
        <div class="card-body p-4">
            @if($trangThaiSua === 'DaHoanThanh')
                <div class="alert alert-success border-success rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-check fs-2 text-success"></i>
                    <div>
                        <h6 class="fw-bold text-success mb-1">Báo cáo sau bảo vệ của nhóm đã đạt chuẩn lưu trữ!</h6>
                        <div class="small text-secondary">Giảng viên phụ trách đã kiểm tra và xác nhận hoàn tất hồ sơ khóa luận. Nhóm không cần nộp lại.</div>
                    </div>
                </div>
            @elseif($isQuaHan)
                <div class="alert alert-danger border-danger rounded-3 p-3 mb-3 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-clock-rotate-left fs-2 text-danger"></i>
                    <div>
                        <h6 class="fw-bold text-danger mb-1">Đã quá thời hạn nộp lại báo cáo hoàn thiện</h6>
                        <div class="small text-secondary">
                            Thời hạn quy định: <strong>{{ $hanNop->format('d/m/Y H:i') }}</strong>. Hệ thống đã tự động khóa form nộp. Vui lòng liên hệ Giáo vụ Khoa hoặc Giảng viên hướng dẫn để được hỗ trợ.
                        </div>
                    </div>
                </div>
            @endif

            @if(!$isLeader && $trangThaiSua !== 'DaHoanThanh' && !$isQuaHan)
                <div class="alert alert-warning border-warning rounded-3 py-2 px-3 small mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Lưu ý:</strong> Chỉ có <strong>Nhóm trưởng ({{ $nhom->truongNhom->HoTen ?? 'Nhóm trưởng' }})</strong> mới có quyền đại diện nhóm nộp tệp Báo cáo hoàn thiện.
                </div>
            @endif

            <!-- Form nộp -->
            <form action="{{ route('sinhvien.ketqua.nopBaoCaoChinhSua') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="MaHoSo" value="{{ $hoSo->MaHoSo }}">

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-dark">
                            1. Tệp Báo Cáo Toàn Văn Hoàn Thiện (PDF, DOC, DOCX) <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               name="FileBaoCaoChinhSua" 
                               class="form-control" 
                               accept=".pdf,.doc,.docx" 
                               required 
                               {{ ($isQuaHan || $trangThaiSua === 'DaHoanThanh' || !$isLeader) ? 'disabled' : '' }}>
                        <div class="form-text small text-muted">
                            Hỗ trợ định dạng <strong>.PDF, .DOC, .DOCX</strong> (tối đa 50MB). Báo cáo hoàn thiện phải bao gồm đầy đủ trang bìa, chữ ký của GVHD và nội dung đã tiếp thu chỉnh sửa theo Hội đồng.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            2. Bản Giải Trình Tiếp Thu Ý Kiến Của Hội Đồng (Tùy chọn)
                        </label>
                        <input type="file" 
                               name="FileGiaiTrinh" 
                               class="form-control" 
                               accept=".pdf,.doc,.docx"
                               {{ ($isQuaHan || $trangThaiSua === 'DaHoanThanh' || !$isLeader) ? 'disabled' : '' }}>
                        <div class="form-text small text-muted">
                            Đính kèm bảng giải trình chi tiết từng ý kiến của Hội đồng và vị trí nội dung đã chỉnh sửa (.pdf, .doc, .docx - tối đa 50MB).
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            3. Tài Liệu Minh Chứng Kèm Theo (Tùy chọn)
                        </label>
                        <input type="file" 
                               name="FileMinhChung" 
                               class="form-control" 
                               accept=".zip,.rar,.pdf,.doc,.docx"
                               {{ ($isQuaHan || $trangThaiSua === 'DaHoanThanh' || !$isLeader) ? 'disabled' : '' }}>
                        <div class="form-text small text-muted">
                            Dữ liệu thực nghiệm, minh chứng xác nhận, mã nguồn hoặc tệp nén đính kèm (.zip, .rar, .pdf - tối đa 50MB).
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-dark">
                            4. Nội Dung Đã Chỉnh Sửa / Ghi Chú Sinh Viên <span class="text-danger">*</span>
                        </label>
                        <textarea name="TomTatChinhSua" 
                                  rows="4" 
                                  maxlength="1000" 
                                  class="form-control" 
                                  required 
                                  placeholder="Ghi rõ các nội dung nhóm đã tiếp thu và sửa đổi (tối đa 1000 ký tự)..."
                                  {{ ($isQuaHan || $trangThaiSua === 'DaHoanThanh' || !$isLeader) ? 'disabled' : '' }}>{{ old('TomTatChinhSua') }}</textarea>
                        <div class="form-text small text-muted text-end">Tối đa 1000 ký tự</div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" 
                                class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" 
                                style="background: #0072ce; border-color: #0072ce;"
                                {{ ($isQuaHan || $trangThaiSua === 'DaHoanThanh' || !$isLeader) ? 'disabled' : '' }}>
                            <i class="fa-solid fa-paper-plane me-2"></i> Nộp Báo Cáo Hoàn Thiện
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ KHU VỰC 5: BẢNG LỊCH SỬ CÁC LẦN NỘP LẠI ═══ -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch Sử Các Lần Nộp Lại Báo Cáo ({{ $lichSuNop->count() }} lần)
            </span>
        </div>
        <div class="card-body p-0">
            @if($lichSuNop->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6 class="fw-bold">Chưa có bản báo cáo hoàn thiện nào được nộp.</h6>
                    <p class="small text-muted mb-0">Sau khi nộp lại báo cáo, lịch sử các lần nộp sẽ được lưu trữ chi tiết tại đây.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-history-versions" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 85px;" class="text-center align-middle">Phiên bản</th>
                                <th style="width: 220px;" class="align-middle">Hồ sơ nộp (Báo cáo &amp; Đính kèm)</th>
                                <th style="width: 22%;" class="align-middle">Nội dung đã chỉnh sửa</th>
                                <th style="width: 140px;" class="align-middle">Thời gian &amp; Người nộp</th>
                                <th style="width: 130px;" class="text-center align-middle">Trạng thái</th>
                                <th style="width: auto; min-width: 250px;" class="align-middle">Nhận xét Giảng viên kiểm tra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lichSuNop as $item)
                            @php
                                $rawTomTat = strip_tags($item->TomTatChinhSua ?? '');
                                $isTomTatLong = mb_strlen($rawTomTat) > 140;

                                $rawNhanXet = strip_tags($item->NhanXetGV ?? '');
                                $isNhanXetLong = mb_strlen($rawNhanXet) > 140;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                                        v{{ $item->LanChinhSua }}
                                    </span>
                                </td>
                                <td>
                                    <x-file-actions :b="$item" />
                                </td>
                                <td>
                                    @if(!empty($item->TomTatChinhSua))
                                        <div class="rich-comment-box {{ $isTomTatLong ? 'line-clamp-4' : '' }}" title="{{ $rawTomTat }}">
                                            {!! \App\Helpers\HtmlHelper::renderRichText($item->TomTatChinhSua) !!}
                                        </div>
                                        @if($isTomTatLong)
                                        <button type="button" class="btn btn-link btn-xs p-0 text-primary text-decoration-none fw-semibold btn-toggle-expand mt-1 d-inline-flex align-items-center" onclick="toggleExpandRowText(this)">
                                            Xem thêm <i class="fa-solid fa-angle-down ms-1"></i>
                                        </button>
                                        @endif
                                    @else
                                        <span class="text-muted small fst-italic">--</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark text-nowrap">{{ $item->NgayNop ? \Carbon\Carbon::parse($item->NgayNop)->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="small text-muted text-break-anywhere mt-1" title="{{ $item->sinhVienNop->HoTen ?? ($item->NguoiNop ?? 'Sinh viên') }}">
                                        <i class="fa-regular fa-user me-1"></i>{{ $item->sinhVienNop->HoTen ?? ($item->NguoiNop ?? 'Sinh viên') }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($item->TrangThai === 'Đạt' || $item->TrangThai === 'Đã duyệt')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5 badge-check d-inline-flex align-items-center text-nowrap" style="gap: 5px;">
                                            <i class="fa-solid fa-check icon-check" style="font-size: 0.85em;"></i>
                                            <span>Đã duyệt</span>
                                        </span>
                                    @elseif($item->TrangThai === 'Yêu cầu sửa tiếp')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1.5 badge-check d-inline-flex align-items-center text-nowrap" style="gap: 5px;">
                                            <i class="fa-solid fa-rotate-right icon-check" style="font-size: 0.85em;"></i>
                                            <span>Yêu cầu sửa tiếp</span>
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1.5 badge-check d-inline-flex align-items-center text-nowrap" style="gap: 5px;">
                                            <i class="fa-solid fa-hourglass-half icon-check" style="font-size: 0.85em;"></i>
                                            <span>Chờ duyệt</span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($item->NhanXetGV))
                                        <div class="rich-comment-box fw-semibold text-dark {{ $isNhanXetLong ? 'line-clamp-4' : '' }}" title="{{ $rawNhanXet }}">
                                            {!! \App\Helpers\HtmlHelper::renderRichText($item->NhanXetGV) !!}
                                        </div>
                                        @if($isNhanXetLong)
                                        <button type="button" class="btn btn-link btn-xs p-0 text-primary text-decoration-none fw-semibold btn-toggle-expand mt-1 d-inline-flex align-items-center" onclick="toggleExpandRowText(this)">
                                            Xem thêm <i class="fa-solid fa-angle-down ms-1"></i>
                                        </button>
                                        @endif
                                        <div class="small text-muted mt-1" style="font-size: 0.73rem;">
                                            <i class="fa-regular fa-clock me-1"></i>{{ $item->NgayKiemTra ? \Carbon\Carbon::parse($item->NgayKiemTra)->format('d/m/Y H:i') : '' }}
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">Chưa có nhận xét</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleExpandRowText(btn) {
    const container = btn.previousElementSibling;
    if (!container) return;
    const isClamped = container.classList.contains('line-clamp-4');
    if (isClamped) {
        container.classList.remove('line-clamp-4');
        btn.innerHTML = 'Thu gọn <i class="fa-solid fa-angle-up ms-1"></i>';
    } else {
        container.classList.add('line-clamp-4');
        btn.innerHTML = 'Xem thêm <i class="fa-solid fa-angle-down ms-1"></i>';
    }
}
</script>
@endpush
@endsection
