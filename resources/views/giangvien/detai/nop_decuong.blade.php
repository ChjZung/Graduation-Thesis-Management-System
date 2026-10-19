@extends('layouts.giangvien')

@section('page_title', 'Nộp & Quản Lý Đề Cương - ' . $detai->MaDeTai)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
       

        <!-- Card Thông Tin Đề Tài -->
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-file-lines text-primary me-2"></i>
                    Thông Tin Đề Tài & Trạng Thái Đề Cương
                </span>
                <span class="badge bg-light text-dark border font-monospace px-3 py-2">{{ $detai->MaDeTai }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-primary mb-2">{{ $detai->TenDeTai }}</h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                            <span class="badge bg-primary-subtle text-primary border">
                                <i class="fa-solid fa-book-open me-2"></i>{{ $detai->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-regular fa-calendar-days me-2"></i>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-solid fa-layer-group me-2"></i>{{ $detai->LinhVuc ?? 'CNTT' }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-solid fa-users me-2"></i>3 Sinh viên
                            </span>
                        </div>

                        <!-- Nhóm thực hiện nếu có -->
                        @php
                            $dangKy = $detai->dangKyDeTais->firstWhere('TrangThai', 'Đã duyệt') ?? $detai->dangKyDeTais->first();
                            $nhom = $dangKy?->nhom;
                        @endphp
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="small text-muted mb-1 fw-semibold"><i class="fa-solid fa-user-group me-2 text-primary"></i>Nhóm Sinh Viên Thực Hiện:</div>
                            @if($nhom)
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <strong class="text-dark">{{ $nhom->TenNhom }}</strong>
                                        <span class="small text-muted ms-2">(Trưởng nhóm: {{ $nhom->truongNhom->HoTen ?? 'Chưa rõ' }})</span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border">
                                        {{ $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/3 thành viên
                                    </span>
                                </div>
                            @else
                                <div class="text-muted small fst-italic">
                                    Chưa gán nhóm sinh viên. (Đề tài sẽ được gán cho nhóm sinh viên sau khi đề cương được thẩm định và công bố).
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="small text-dark fw-bold mb-2 pb-1 border-bottom">
                                    <i class="fa-solid fa-list-check text-primary me-2"></i> Trạng thái quy trình đề tài:
                                </div>
                                @php
                                    // 1. Trạng thái đề tài
                                    $ttDeTai = (!empty($detai->NgayDuyetKhoa) || in_array($detai->TrangThai, ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'])) 
                                        ? 'Đã phê duyệt đề tài' 
                                        : ($detai->TrangThai === 'Từ chối' ? 'Bị từ chối' : ($detai->TrangThai === 'Chờ duyệt cấp Khoa' ? 'Chờ duyệt cấp Khoa' : 'Chờ duyệt cấp Bộ môn'));
                                    
                                    // 2. Trạng thái đề cương
                                    $ttDeCuong = 'Chưa nộp đề cương';
                                    if (!$detai->coTepDeCuong()) {
                                        $ttDeCuong = 'Chưa có tệp đề cương';
                                    } elseif ($detai->daDuyetDeCuong()) {
                                        $ttDeCuong = 'Đã duyệt đề cương';
                                    } elseif ($detai->TrangThai === 'Yêu cầu chỉnh sửa đề cương') {
                                        $ttDeCuong = 'Yêu cầu chỉnh sửa đề cương';
                                    } elseif (in_array($detai->TrangThai, ['Đã phản biện - Chờ TBM duyệt đề cương', 'Đã phản biện - Chờ duyệt BM'])) {
                                        $ttDeCuong = 'Đã phản biện - Chờ Bộ môn duyệt';
                                    } elseif ($detai->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại') {
                                        $ttDeCuong = 'Đã nộp lại đề cương';
                                    } elseif ($detai->FileDeCuong) {
                                        $ttDeCuong = 'Đã nộp đề cương';
                                    }

                                    // 3. Trạng thái phản biện đề cương
                                    $pbMain = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                                    if (!$detai->coTepDeCuong()) {
                                        $ttPhanBien = 'Chưa phản biện (Chờ nộp ĐC)';
                                        $badgePbClass = 'bg-secondary-subtle text-secondary border';
                                    } elseif (!$pbMain) {
                                        $ttPhanBien = 'Chưa phản biện (Chờ phân công)';
                                        $badgePbClass = 'bg-secondary-subtle text-secondary border';
                                    } elseif (empty($pbMain->KetQua) || $pbMain->TrangThai === 'Đang phản biện') {
                                        $ttPhanBien = 'Chưa phản biện (Đang thẩm định)';
                                        $badgePbClass = 'bg-info-subtle text-info border';
                                    } elseif ($pbMain->KetQua === 'Đạt') {
                                        $ttPhanBien = 'Phản biện Đạt yêu cầu';
                                        $badgePbClass = 'bg-success-subtle text-success border';
                                    } elseif ($pbMain->KetQua === 'Yêu cầu chỉnh sửa') {
                                        $ttPhanBien = 'Yêu cầu chỉnh sửa';
                                        $badgePbClass = 'bg-warning-subtle text-warning-emphasis border';
                                    } elseif ($pbMain->KetQua === 'Không đạt') {
                                        $ttPhanBien = 'Không đạt phản biện';
                                        $badgePbClass = 'bg-danger-subtle text-danger border';
                                    } else {
                                        $ttPhanBien = 'Chưa phản biện';
                                        $badgePbClass = 'bg-secondary-subtle text-secondary border';
                                    }
                                @endphp
                                <div class="d-flex flex-column gap-2 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted fw-semibold">1. Đề tài:</span>
                                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-1 small">{{ $ttDeTai }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted fw-semibold">2. Đề cương:</span>
                                        <span class="badge {{ $ttDeCuong === 'Đã duyệt đề cương' ? 'bg-success text-white' : ($ttDeCuong === 'Yêu cầu chỉnh sửa đề cương' ? 'bg-warning-subtle text-warning-emphasis border' : 'bg-primary-subtle text-primary border') }} rounded-pill px-3 py-1 small">{{ $ttDeCuong }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted fw-semibold">3. Phản biện:</span>
                                        <span class="badge {{ $badgePbClass }} rounded-pill px-3 py-1 small">{{ $ttPhanBien }}</span>
                                    </div>
                                </div>
                                <div class="small text-muted border-top pt-2">
                                    @if($detai->TrangThai === 'Hoàn thành')
                                        Đề tài đã hoàn thành. Chức năng nộp đề cương đã khóa.
                                    @elseif($isLocked)
                                        Đề tài đã hoàn tất quy trình thẩm định đề cương và đã công bố chính thức.
                                    @elseif($isNopLai)
                                        Cán bộ phản biện đã có ý kiến góp ý. Quý Thầy/Cô vui lòng chỉnh sửa và nộp lại file đề cương.
                                    @else
                                        Đề xuất đã được duyệt chủ trương. Giảng viên vui lòng nộp file đề cương chi tiết.
                                    @endif
                                </div>
                            </div>

                            @if($detai->coTepDeCuong())
                                @php
                                    $filePathCurrent = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <div class="mt-3 pt-3 border-top">
                                    <div class="small text-muted mb-1">Đề cương hiện tại:</div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 d-inline-flex align-items-center"
                                                onclick="quickPreviewOutline('{{ $filePathCurrent }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')">
                                            <i class="fa-solid fa-eye me-2"></i> Xem Nhanh
                                        </button>
                                        <a href="{{ $filePathCurrent }}" download="{{ basename($detai->FileDeCuong) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fa-solid fa-download me-2"></i> Tải Về
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chỉ khóa nộp khi đề cương đã được duyệt hoặc đề tài đã hoàn thành. -->
        @if(!$isLocked)
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>
                    <i class="fa-solid fa-file-arrow-up text-primary me-2"></i>
                    {{ $isNopLai ? 'Nộp Lại Đề Cương Sau Chỉnh Sửa' : 'Nộp Đề Cương Chi Tiết' }}
                </span>
                
                <!-- Dropdown tải biểu mẫu đề cương chuẩn -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 dropdown-toggle shadow-xs d-inline-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-download me-2"></i> Tải Biểu Mẫu Đề Cương (.docx)
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 small">
                        <li><h6 class="dropdown-header fw-bold text-muted">Chọn biểu mẫu theo chương trình đào tạo</h6></li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('giangvien.detai.download_template', ['type' => 'cu_nhan']) }}">
                                <i class="fa-solid fa-graduation-cap text-primary me-2"></i> Khóa Luận Cử Nhân (.docx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('giangvien.detai.download_template', ['type' => 'ky_su']) }}">
                                <i class="fa-solid fa-gears text-success me-2"></i> Khóa Luận Kỹ Sư (.docx)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-4">
                @if($isNopLai)
                <div class="alert alert-warning border-warning-subtle py-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i>
                    <div class="small">
                        <strong>Lưu ý:</strong> Đề cương đang được yêu cầu chỉnh sửa. Vui lòng cập nhật các nội dung theo nhận xét của Cán bộ phản biện ở bên dưới và tải lên file đề cương mới để gửi lại thẩm định.
                    </div>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.nopDeCuong', $detai->MaDeTai) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="FileDeCuong" class="form-label fw-bold">
                            Chọn Tệp Đề Cương Chi Tiết <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="FileDeCuong" id="FileDeCuong" class="form-control" accept=".pdf,.doc,.docx" required>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-2"></i>Định dạng cho phép: <strong>.pdf, .doc, .docx</strong>. Dung lượng tối đa: <strong>10MB</strong>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold shadow-xs d-inline-flex align-items-center">
                            <i class="fa-solid fa-paper-plane me-2"></i>
                            {{ $isNopLai ? 'Cập Nhật & Nộp Lại Đề Cương' : 'Xác Nhận Nộp Đề Cương' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <!-- Thông Báo Khóa Nộp Đề Cương Khi Đã Công Bố -->
        <div class="card border rounded-3 shadow-xs mb-4 bg-light-subtle">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-2">{{ $detai->TrangThai === 'Hoàn thành' ? 'Đề Tài Đã Hoàn Thành' : 'Đề Cương Đã Được Phê Duyệt' }}</h5>
                <p class="text-muted small mx-auto mb-3" style="max-width: 620px;">
                    Chức năng nộp và chỉnh sửa đề cương đã khóa vì {{ $detai->TrangThai === 'Hoàn thành' ? 'đề tài đã hoàn thành' : 'Trưởng bộ môn đã phê duyệt đề cương' }}.
                </p>
                @if($detai->coTepDeCuong())
                    @php
                        $filePathLocked = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                    @endphp
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center"
                                onclick="quickPreviewOutline('{{ $filePathLocked }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')">
                            <i class="fa-solid fa-eye me-2"></i> Mở Đề Cương Chi Tiết
                        </button>
                        <a href="{{ $filePathLocked }}" download="{{ basename($detai->FileDeCuong) }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fa-solid fa-download me-2"></i> Tải Về Máy
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Nhận Xét & Kết Quả Thẩm Định Của Phản Biện / Bộ Môn -->
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-comment-dots text-primary me-2"></i>
                    Kết Quả Thẩm Định & Nhận Xét Của Phản Biện
                </span>
                <span class="small text-muted">Thẩm định đề cương</span>
            </div>
            <div class="card-body p-4">
                @php
                    $phanBiens = $detai->phanCongPhanBiens ?? collect();
                @endphp

                @if($detai->LyDoTuChoi)
                <div class="alert alert-warning border-warning-subtle p-3 rounded-3 mb-4">
                    <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                        Góp ý / Yêu cầu từ Trưởng Bộ Môn / Ban Chủ Nhiệm Khoa:
                    </div>
                    <div class="text-dark small ps-4">{!! \App\Helpers\HtmlHelper::clean($detai->LyDoTuChoi) !!}</div>
                </div>
                @endif

                @if($phanBiens->count() > 0)
                    <div class="row g-3">
                        @foreach($phanBiens as $pb)
                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-white shadow-xs">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-user-tie text-primary me-2"></i>
                                            CB Phản Biện: {{ $pb->giangVien->HoTen ?? 'Chưa rõ' }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-tag me-2"></i>Vai trò: {{ $pb->VaiTro ?? 'Phản biện đề cương' }}
                                            @if($pb->NgayPhanCong)
                                                 &bull; Phân công: {{ \Carbon\Carbon::parse($pb->NgayPhanCong)->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @if($pb->KetQua === 'Đạt')
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-check me-2"></i>Đạt</span>
                                        @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fa-solid fa-wrench me-2"></i>Yêu Cầu Chỉnh Sửa</span>
                                        @elseif($pb->KetQua === 'Không đạt')
                                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-xmark me-2"></i>Không Đạt</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-2"></i>{{ $pb->KetQua ?? 'Đang thẩm định' }}</span>
                                        @endif
                                        @if($pb->NgayDanhGia)
                                            <div class="small text-muted mt-1">Đánh giá: {{ \Carbon\Carbon::parse($pb->NgayDanhGia)->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label text-muted small fw-semibold mb-1">Nội dung nhận xét / Góp ý chi tiết:</label>
                                    @if($pb->NhanXet)
                                        <div class="p-3 bg-light rounded-3 text-dark small">{!! \App\Helpers\HtmlHelper::clean($pb->NhanXet) !!}</div>
                                    @else
                                        <div class="text-muted small fst-italic p-2 bg-light rounded">Cán bộ phản biện chưa nhập nhận xét chi tiết.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-hourglass-half fs-2 text-secondary mb-2 d-block"></i>
                        <div>Hiện tại đề cương đang trong quá trình tiếp nhận hoặc chờ Trưởng Bộ Môn phân công Cán bộ phản biện.</div>
                        <div class="small mt-1">Kết quả và ý kiến phản biện sẽ được cập nhật tại đây ngay sau khi hoàn tất đánh giá.</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Lịch Sử Nộp Đề Cương -->
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                    Lịch Sử Nộp Đề Cương Chi Tiết
                </span>
                <span class="badge bg-light text-secondary border">
                    {{ $detai->taiLieuNops->count() > 0 ? $detai->taiLieuNops->count() : ($detai->coTepDeCuong() ? 1 : 0) }} lần nộp
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th width="10%" class="text-center">Lần nộp</th>
                                <th width="35%">Tên File Đề Cương</th>
                                <th width="20%">Thời Gian Nộp</th>
                                <th width="20%">Ghi Chú</th>
                                <th width="15%" class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $dsLichSu = $detai->taiLieuNops ?? collect();
                            @endphp

                            @if($dsLichSu->count() > 0)
                                @foreach($dsLichSu as $tl)
                                @php
                                    $linkTl = Str::startsWith($tl->DuongDan, ['http', 'storage/']) ? asset($tl->DuongDan) : asset('storage/' . $tl->DuongDan);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1">
                                            Lần {{ $tl->LanNop ?? 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $tl->TenTaiLieu ?? basename($tl->DuongDan) }}</div>
                                        <span class="small text-muted">{{ $tl->LoaiTaiLieu ?? 'Đề cương chi tiết' }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-secondary">
                                            <i class="fa-regular fa-clock me-2"></i>
                                            {{ $tl->NgayNop ? \Carbon\Carbon::parse($tl->NgayNop)->format('d/m/Y H:i') : ($tl->created_at ? $tl->created_at->format('d/m/Y H:i') : '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $tl->GhiChu ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center"
                                                    onclick="quickPreviewOutline('{{ $linkTl }}', '{{ basename($tl->DuongDan) }}', '{{ addslashes($detai->TenDeTai) }}')"
                                                    title="Xem nhanh">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="{{ $linkTl }}" download="{{ basename($tl->DuongDan) }}" class="btn btn-sm btn-light border text-secondary rounded-circle" title="Tải về máy">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @elseif($detai->coTepDeCuong())
                                @php
                                    $linkCurrent = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1">
                                            Lần 1
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ basename($detai->FileDeCuong) }}</div>
                                        <span class="small text-muted">Đề cương chi tiết</span>
                                    </td>
                                    <td>
                                        <span class="small text-secondary">
                                            <i class="fa-regular fa-clock me-2"></i>
                                            {{ $detai->updated_at ? $detai->updated_at->format('d/m/Y H:i') : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">Nộp đề cương chi tiết</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center"
                                                    onclick="quickPreviewOutline('{{ $linkCurrent }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')"
                                                    title="Xem nhanh">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="{{ $linkCurrent }}" download="{{ basename($detai->FileDeCuong) }}" class="btn btn-sm btn-light border text-secondary rounded-circle" title="Tải về máy">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open fs-2 text-secondary mb-2 d-block me-2"></i>
                                        Chưa có lượt nộp đề cương nào được ghi nhận cho đề tài này.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
