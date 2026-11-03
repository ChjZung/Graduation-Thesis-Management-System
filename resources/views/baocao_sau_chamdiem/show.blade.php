@extends($scope['layout'])

@section('page_title', 'Chi Tiết Báo Cáo Chỉnh Sửa — ' . ($hoSo->nhom->TenNhom ?? $hoSo->MaHoSo))

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

/* Chống tràn văn bản */
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

/* Line clamp 4 dòng cho nội dung dài */
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

/* Nhóm link thao tác tệp thẳng hàng, icon chuẩn kích thước */
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
<div class="container-fluid px-0" style="padding-bottom: 60px;">
    <!-- Read-only Banner Notice -->
    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-3">
        <div class="fs-4 text-primary">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold text-dark">Chế độ xem chỉ đọc dành cho {{ $scope['roleTitle'] }}</div>
            <div class="small text-muted">
                Thẩm quyền kiểm tra, thẩm định và duyệt báo cáo hoàn thiện thuộc về Giảng viên hướng dẫn / Giảng viên được Hội đồng phân công kiểm tra.
            </div>
        </div>
        <span class="badge bg-primary text-white px-3 py-2 rounded-pill font-monospace">READ-ONLY</span>
    </div>

    <!-- Header Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="mb-1">
                <a href="{{ route($scope['routePrefix'] . '.index') }}" class="text-decoration-none small text-muted">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh Sách Báo Cáo Sau Chấm Điểm
                </a>
            </div>
            <h4 class="fw-bold mb-0" style="color: var(--huit-blue-dark, #00529b);">
                <i class="fa-solid fa-file-signature me-2 text-primary"></i>
                Hồ Sơ Hoàn Thiện Báo Cáo Sau Bảo Vệ
            </h4>
        </div>
        <div>
            @if($hoSo->TrangThaiChinhSuaSauBaoVe === 'DaHoanThanh')
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Báo Cáo Đã Xác Nhận Đạt Chuẩn Lưu Trữ</span>
                </span>
            @elseif($hoSo->TrangThaiChinhSuaSauBaoVe === 'DaNop')
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-clock"></i>
                    <span>Đã Nộp Bản Mới (Chờ GVHD Kiểm Tra)</span>
                </span>
            @elseif($hoSo->TrangThaiChinhSuaSauBaoVe === 'YeuCauSuaTiep')
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Chưa Đạt (Yêu Cầu Chỉnh Sửa Tiếp)</span>
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-bold">
                    <span>Chưa Nộp Lại Báo Cáo</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Thông Tin Hồ Sơ & Đề Tài -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-primary text-white">{{ $hoSo->MaHoSo }}</span>
                        <span class="badge bg-light text-secondary border font-monospace">{{ $hoSo->LoaiKhoaLuan ?? 'KLCN' }}</span>
                        <span class="badge bg-light text-dark border">{{ $hoSo->hoiDong->hocKy->TenHocKy ?? $hoSo->nhom->hocKy->TenHocKy ?? 'Học kỳ' }}</span>
                    </div>
                    <h5 class="fw-bold text-primary mb-2 text-break-anywhere">{{ $hoSo->nhom->deTai->TenDeTai ?? 'Chưa xác định' }}</h5>
                    <div class="small text-muted d-flex flex-wrap gap-3">
                        <span>Nhóm: <strong>{{ $hoSo->nhom->TenNhom ?? '' }}</strong></span> &bull; 
                        <span>GVHD: <strong>{{ $hoSo->nhom->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong> ({{ $hoSo->nhom->deTai->giangVien->boMon->TenBoMon ?? '' }})</span> &bull; 
                        <span>Hội đồng: <strong>{{ $hoSo->hoiDong->TenHoiDong ?? '' }}</strong> ({{ $hoSo->PhongBaoVe ?? ($hoSo->hoiDong->DiaDiem ?? 'Phòng B.304') }})</span>
                    </div>
                </div>

                <div class="col-12 pt-3 border-top">
                    <div class="small fw-bold text-secondary mb-2">Danh sách thành viên thực hiện đề tài:</div>
                    <div class="row g-2">
                        @if($hoSo->nhom && $hoSo->nhom->thanhViens->isNotEmpty())
                            @foreach($hoSo->nhom->thanhViens as $idx => $tv)
                            <div class="col-md-4">
                                <div class="p-2 bg-light rounded-2 border">
                                    <div class="fw-bold text-dark small">{{ $tv->sinhVien->HoTen ?? $tv->MaSV }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        MSSV: <strong>{{ $tv->sinhVien->MaSoSinhVien ?? $tv->sinhVien->MaSV ?? $tv->MaSV }}</strong> &bull; {{ $tv->sinhVien->lop->TenLop ?? 'Lớp' }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-muted small">Không tìm thấy thông tin thành viên nhóm.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối 1: Yêu Cầu Chỉnh Sửa Ban Đầu Của Hội Đồng -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" style="border-left: 5px solid #0072ce !important;">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Nội Dung Hội Đồng Bảo Vệ Yêu Cầu Chỉnh Sửa
            </span>
        </div>
        <div class="card-body p-4">
            <div class="p-3 rounded-3 bg-light border text-dark mb-3 rich-comment-box" style="line-height: 1.6;">
                {!! \App\Helpers\HtmlHelper::renderRichText($hoSo->YeuCauChinhSuaSauBaoVe ?: 'Nhóm sinh viên thực hiện rà soát, chỉnh sửa và hoàn thiện toàn văn báo cáo theo ý kiến góp ý của Hội đồng bảo vệ khóa luận tốt nghiệp.') !!}
            </div>

            <div class="row g-2 small text-muted">
                <div class="col-md-6">
                    <span>Thời hạn nộp lại hoàn thiện:</span>
                    <strong class="text-dark ms-1">
                        {{ $hoSo->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') : 'Chưa thiết lập' }}
                    </strong>
                </div>
                <div class="col-md-6">
                    <span>Người được phân công kiểm tra, thẩm định:</span>
                    <strong class="text-dark ms-1">{{ $hoSo->NguoiKiemTraChinhSua ?? 'Giảng viên hướng dẫn' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối 2: Bản Báo Cáo Chỉnh Sửa Mới Nhất Do Nhóm Nộp -->
    @if(!$latestBaoCao)
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white mb-4 text-muted">
        <i class="fa-solid fa-file-arrow-up fa-3x mb-3 text-secondary opacity-50"></i>
        <h6>Nhóm sinh viên hiện chưa nộp lại bản báo cáo chỉnh sửa sau bảo vệ.</h6>
        <div class="small">Hệ thống sẽ cập nhật ngay khi sinh viên tải lên bản báo cáo hoàn thiện.</div>
    </div>
    @else
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" style="border-left: 5px solid {{ $latestBaoCao->TrangThai === 'Đạt' ? '#198754' : ($latestBaoCao->TrangThai === 'Yêu cầu sửa tiếp' ? '#dc3545' : '#ffc107') }} !important;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-file-pdf text-danger me-2"></i>Bản Báo Cáo Chỉnh Sửa Mới Nhất (Phiên Bản Lần {{ $latestBaoCao->LanChinhSua }})
            </span>
            <span class="badge {{ $latestBaoCao->TrangThai === 'Đạt' ? 'bg-success' : ($latestBaoCao->TrangThai === 'Yêu cầu sửa tiếp' ? 'bg-danger' : 'bg-warning text-dark') }} rounded-pill px-3 py-2 fw-bold">
                {{ $latestBaoCao->TrangThai ?? 'Đã nộp' }}
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <!-- Tệp đính kèm -->
                <div class="col-md-7">
                    <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small"><i class="fa-solid fa-paperclip me-1 text-primary"></i> Tệp Báo Cáo Đính Kèm:</span>
                                <span class="text-muted small">{{ $latestBaoCao->DungLuong ? round($latestBaoCao->DungLuong / 1024 / 1024, 2) . ' MB' : '' }}</span>
                            </div>
                            <div class="fw-bold text-primary mb-3 text-break-anywhere">
                                <i class="fa-solid fa-file-pdf text-danger me-1"></i>{{ $latestBaoCao->TenFile }}
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm"
                                    onclick="quickPreviewOutline('{{ route('baocao_sau_baove.pdf', $latestBaoCao->id) }}', '{{ addslashes($latestBaoCao->TenFile) }}', 'Báo cáo hoàn thiện - {{ addslashes($hoSo->nhom->deTai->TenDeTai ?? $hoSo->MaHoSo) }}', { downloadUrl: '{{ route('baocao_sau_baove.download', $latestBaoCao->id) }}', title: 'Báo Cáo Sau Bảo Vệ (Lần {{ $latestBaoCao->LanChinhSua }}): {{ addslashes($hoSo->nhom->deTai->TenDeTai ?? $hoSo->MaHoSo) }}' })">
                                    <i class="fa-solid fa-eye me-1"></i> Mở Xem Báo Cáo PDF
                                </button>
                                <a href="{{ route('baocao_sau_baove.download', $latestBaoCao->id) }}" download="{{ $latestBaoCao->TenFile }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-download me-1"></i> Tải Báo Cáo
                                </a>
                                @if($latestBaoCao->FileGiaiTrinh)
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold"
                                    onclick="quickPreviewOutline('{{ route('baocao_sau_baove.pdf', ['id' => $latestBaoCao->id, 'type' => 'giai_trinh']) }}', '{{ addslashes($latestBaoCao->TenFileGiaiTrinh ?: 'Ban_Giai_Trinh.pdf') }}', 'Bản Giải Trình - {{ addslashes($hoSo->nhom->deTai->TenDeTai ?? $hoSo->MaHoSo) }}', { downloadUrl: '{{ route('baocao_sau_baove.download', ['id' => $latestBaoCao->id, 'type' => 'giai_trinh']) }}' })">
                                    <i class="fa-solid fa-file-lines me-1"></i> Xem Bản Giải Trình
                                </button>
                                @endif
                                @if($latestBaoCao->FileMinhChung)
                                <a href="{{ route('baocao_sau_baove.download', ['id' => $latestBaoCao->id, 'type' => 'minh_chung']) }}" download="{{ $latestBaoCao->TenFileMinhChung ?: 'MinhChung.zip' }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-paperclip me-1"></i> Tải Minh Chứng
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="small text-muted mt-3 pt-2 border-top">
                            <i class="fa-regular fa-clock me-1"></i> Nộp lúc: <strong>{{ \Carbon\Carbon::parse($latestBaoCao->NgayNop)->format('d/m/Y H:i') }}</strong> 
                            &bull; Người nộp: <strong>{{ $latestBaoCao->sinhVienNop->HoTen ?? $latestBaoCao->NguoiNop }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Tóm tắt tiếp thu của nhóm -->
                <div class="col-md-5">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="fw-bold text-dark small mb-2"><i class="fa-solid fa-quote-left text-primary me-1"></i> Tóm Tắt Nội Dung Tiếp Thu &amp; Chỉnh Sửa Của Nhóm:</div>
                        <div class="text-secondary small rich-comment-box" style="line-height: 1.55;">
                            {!! \App\Helpers\HtmlHelper::renderRichText($latestBaoCao->TomTatChinhSua ?: 'Nhóm không ghi chú tóm tắt.') !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ý Kiến Đánh Giá Của Giảng Viên Kiểm Tra -->
            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-comments text-primary me-2"></i>Ý Kiến Đánh Giá &amp; Kết Quả Của Giảng Viên Kiểm Tra:
                </h6>
                <div class="p-3 rounded-3 bg-light border">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div>
                            <span class="small text-muted">Kết luận thẩm định:</span>
                            @if($latestBaoCao->TrangThai === 'Đạt')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1 ms-1 fw-bold">
                                    <i class="fa-solid fa-circle-check me-1"></i> ĐẠT YÊU CẦU (Đã hoàn thành lưu trữ)
                                </span>
                            @elseif($latestBaoCao->TrangThai === 'Yêu cầu sửa tiếp')
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 ms-1 fw-bold">
                                    <i class="fa-solid fa-rotate me-1"></i> CHƯA ĐẠT (Yêu cầu nhóm chỉnh sửa tiếp)
                                </span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 ms-1 fw-bold">
                                    <i class="fa-solid fa-clock me-1"></i> ĐANG CHỜ KIỂM TRA
                                </span>
                            @endif
                        </div>
                        @if($latestBaoCao->NgayKiemTra)
                        <div class="small text-muted">
                            <i class="fa-regular fa-clock me-1"></i> Đã thẩm định lúc: <strong>{{ \Carbon\Carbon::parse($latestBaoCao->NgayKiemTra)->format('d/m/Y H:i') }}</strong>
                            &bull; Người đánh giá: <strong>{{ $latestBaoCao->NguoiKiemTra ?? $hoSo->nhom->deTai->giangVien->HoTen ?? 'GVHD' }}</strong>
                        </div>
                        @endif
                    </div>
                    <div class="small text-dark mt-2" style="line-height: 1.6;">
                        <strong>Nhận xét:</strong>
                        <div class="mt-1 text-secondary rich-comment-box">
                            {!! \App\Helpers\HtmlHelper::renderRichText($latestBaoCao->NhanXetGV ?: 'Chưa có ý kiến nhận xét của Giảng viên.') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Khối 3: Lịch Sử Toàn Bộ Các Phiên Bản Nộp Lại -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch Sử Các Lần Nộp Lại Của Nhóm ({{ $lichSuNop->count() }} phiên bản)
            </span>
        </div>
        <div class="card-body p-0">
            @if($lichSuNop->isEmpty())
                <div class="text-center py-4 text-muted small">Chưa có lịch sử phiên bản nào được nộp.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-history-versions" style="font-size: 0.84rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 85px;" class="text-center align-middle">Phiên bản</th>
                                <th style="width: 220px;" class="align-middle">Tệp Báo Cáo &amp; Đính Kèm</th>
                                <th style="width: 22%;" class="align-middle">Nội dung tiếp thu</th>
                                <th style="width: 135px;" class="align-middle">Thời gian nộp</th>
                                <th style="width: 125px;" class="text-center align-middle">Trạng thái</th>
                                <th style="width: auto; min-width: 260px;" class="align-middle">Nhận xét Giảng viên</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lichSuNop as $b)
                            @php
                                $rawTomTat = strip_tags($b->TomTatChinhSua ?? '');
                                $isTomTatLong = mb_strlen($rawTomTat) > 140;

                                $rawNhanXet = strip_tags($b->NhanXetGV ?? '');
                                $isNhanXetLong = mb_strlen($rawNhanXet) > 140;
                            @endphp
                            <tr>
                                {{-- 1. Phiên bản --}}
                                <td class="text-center">
                                    <span class="badge bg-primary text-white rounded-pill px-3 py-1">Lần {{ $b->LanChinhSua }}</span>
                                </td>

                                {{-- 2. Tệp Báo Cáo & Đính Kèm --}}
                                <td>
                                    <x-file-actions :b="$b" :hoSo="$hoSo" />
                                </td>

                                {{-- 3. Nội dung tiếp thu --}}
                                <td>
                                    @if(!empty($b->TomTatChinhSua))
                                        <div class="rich-comment-box {{ $isTomTatLong ? 'line-clamp-4' : '' }}" title="{{ $rawTomTat }}">
                                            {!! \App\Helpers\HtmlHelper::renderRichText($b->TomTatChinhSua) !!}
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

                                {{-- 4. Thời gian nộp --}}
                                <td>
                                    <div class="small fw-semibold text-dark text-nowrap">{{ \Carbon\Carbon::parse($b->NgayNop)->format('d/m/Y H:i') }}</div>
                                    <div class="small text-muted text-break-anywhere mt-1" title="{{ $b->sinhVienNop->HoTen ?? $b->NguoiNop }}">
                                        <i class="fa-regular fa-user me-1"></i>{{ $b->sinhVienNop->HoTen ?? $b->NguoiNop }}
                                    </div>
                                </td>

                                {{-- 5. Trạng thái --}}
                                <td class="text-center">
                                    @if($b->TrangThai === 'Đạt')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold text-nowrap">Đạt Yêu Cầu</span>
                                    @elseif($b->TrangThai === 'Yêu cầu sửa tiếp')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fw-bold text-nowrap">Chưa Đạt</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold text-nowrap">Đã Nộp</span>
                                    @endif
                                </td>

                                {{-- 6. Nhận xét Giảng viên --}}
                                <td>
                                    @if($b->NhanXetGV)
                                        <div class="rich-comment-box fw-semibold text-dark {{ $isNhanXetLong ? 'line-clamp-4' : '' }}" title="{{ $rawNhanXet }}">
                                            {!! \App\Helpers\HtmlHelper::renderRichText($b->NhanXetGV) !!}
                                        </div>
                                        @if($isNhanXetLong)
                                        <button type="button" class="btn btn-link btn-xs p-0 text-primary text-decoration-none fw-semibold btn-toggle-expand mt-1 d-inline-flex align-items-center" onclick="toggleExpandRowText(this)">
                                            Xem thêm <i class="fa-solid fa-angle-down ms-1"></i>
                                        </button>
                                        @endif
                                        <div class="text-muted mt-1 d-flex flex-wrap align-items-center gap-1" style="font-size: 0.73rem;">
                                            @if($b->NgayKiemTra)
                                            <span><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($b->NgayKiemTra)->format('d/m/Y H:i') }}</span>
                                            <span>&bull;</span>
                                            @endif
                                            <span class="text-secondary text-break-anywhere">{{ $b->NguoiKiemTra }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">--</span>
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

@include('partials.modal_preview_decuong')
@endsection