@extends('layouts.giangvien')

@section('page_title', 'Nộp & Quản Lý Đề Cương - ' . $detai->TenDeTai)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header & Breadcrumb -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giangvien.detai.index') }}" class="text-decoration-none">Đề tài của tôi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nộp đề cương chi tiết</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('giangvien.detai.show', $detai->MaDeTai) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-circle-info me-1"></i> Chi Tiết Đề Tài
                </a>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
                </a>
            </div>
        </div>

        <!-- Card Thông Tin Đề Tài -->
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-file-lines text-primary me-2"></i>
                    Thông Tin Đề Tài & Trạng Thái Đề Cương
                </span>
                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1.5">{{ $detai->MaDeTai }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-primary mb-2">{{ $detai->TenDeTai }}</h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                            <span class="badge bg-primary-subtle text-primary border">
                                <i class="fa-solid fa-book-open me-1"></i>{{ $detai->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-regular fa-calendar-days me-1"></i>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-solid fa-layer-group me-1"></i>{{ $detai->LinhVuc ?? 'CNTT' }}
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-solid fa-users me-1"></i>3 Sinh viên
                            </span>
                        </div>

                        <!-- Nhóm thực hiện nếu có -->
                        @php
                            $dangKy = $detai->dangKyDeTais->firstWhere('TrangThai', 'Đã duyệt') ?? $detai->dangKyDeTais->first();
                            $nhom = $dangKy?->nhom;
                        @endphp
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="small text-muted mb-1 fw-semibold"><i class="fa-solid fa-user-group me-1 text-primary"></i>Nhóm Sinh Viên Thực Hiện:</div>
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
                                <div class="small text-muted fw-semibold mb-1">Trạng thái hiện tại:</div>
                                @php
                                    $tt = trim($detai->TrangThai ?? 'Chờ duyệt');
                                    $badgeMap = [
                                        'Trưởng khoa đã duyệt - Chờ nộp đề cương' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-solid fa-file-arrow-up', 'text' => 'Chờ nộp đề cương'],
                                        'Đã nộp đề cương - Chờ phân công PB' => ['class' => 'bg-info text-white', 'icon' => 'fa-solid fa-clock', 'text' => 'Đã nộp ĐC - Chờ PB'],
                                        'Đang phản biện đề cương' => ['class' => 'bg-info text-white', 'icon' => 'fa-solid fa-spinner', 'text' => 'Đang phản biện ĐC'],
                                        'Đã cập nhật đề cương - Chờ phản biện lại' => ['class' => 'bg-primary text-white', 'icon' => 'fa-solid fa-rotate', 'text' => 'Đã nộp lại ĐC'],
                                        'Yêu cầu chỉnh sửa đề cương' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-solid fa-triangle-exclamation', 'text' => 'Yêu cầu sửa ĐC'],
                                        'Đã phản biện - Chờ duyệt BM' => ['class' => 'bg-primary-subtle text-primary border', 'icon' => 'fa-solid fa-check-double', 'text' => 'Đã PB - Chờ TBM'],
                                        'Đã phản biện - Chờ TBM duyệt đề cương' => ['class' => 'bg-primary-subtle text-primary border', 'icon' => 'fa-solid fa-check-double', 'text' => 'Đã PB - Chờ TBM'],
                                        'Đã công bố' => ['class' => 'bg-success text-white', 'icon' => 'fa-solid fa-bullhorn', 'text' => 'Đã công bố chính thức'],
                                        'Không đạt phản biện' => ['class' => 'bg-danger text-white', 'icon' => 'fa-solid fa-circle-xmark', 'text' => 'Không đạt phản biện'],
                                    ];
                                    $cfg = $badgeMap[$tt] ?? ['class' => 'bg-secondary text-white', 'icon' => 'fa-solid fa-circle-info', 'text' => $tt];
                                @endphp
                                <span class="badge {{ $cfg['class'] }} px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-1 mb-2">
                                    <i class="{{ $cfg['icon'] }}"></i> {{ $cfg['text'] }}
                                </span>
                                <div class="small text-muted">
                                    @if($isLocked)
                                        Đề tài đã hoàn tất quy trình thẩm định đề cương và đã công bố chính thức.
                                    @elseif($isNopLai)
                                        Cán bộ phản biện đã có ý kiến góp ý. Quý Thầy/Cô vui lòng chỉnh sửa và nộp lại file đề cương.
                                    @else
                                        Đề xuất đã được duyệt chủ trương. Giảng viên vui lòng nộp file đề cương chi tiết.
                                    @endif
                                </div>
                            </div>

                            @if($detai->FileDeCuong)
                                @php
                                    $filePathCurrent = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <div class="mt-3 pt-3 border-top">
                                    <div class="small text-muted mb-1">Đề cương hiện tại:</div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3"
                                                onclick="quickPreviewOutline('{{ $filePathCurrent }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')">
                                            <i class="fa-solid fa-eye me-1"></i> Xem Nhanh
                                        </button>
                                        <a href="{{ $filePathCurrent }}" download="{{ basename($detai->FileDeCuong) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fa-solid fa-download me-1"></i> Tải Về
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Tải Lên Đề Cương (Chỉ mở khi CHƯA CÔNG BỐ) -->
        @if(!$isLocked)
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>
                    <i class="fa-solid fa-file-arrow-up text-primary me-2"></i>
                    {{ $isNopLai ? 'Nộp Lại Đề Cương Sau Chỉnh Sửa' : 'Nộp Đề Cương Chi Tiết' }}
                </span>
                
                <!-- Dropdown tải biểu mẫu đề cương chuẩn -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 dropdown-toggle shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-download me-1"></i> Tải Biểu Mẫu Đề Cương (.docx)
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
                <div class="alert alert-warning border-warning-subtle py-2.5 px-3 rounded-3 mb-3 d-flex align-items-center gap-2">
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
                            <i class="fa-solid fa-circle-info me-1"></i>Định dạng cho phép: <strong>.pdf, .doc, .docx</strong>. Dung lượng tối đa: <strong>10MB</strong>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold shadow-xs">
                            <i class="fa-solid fa-paper-plane me-1"></i>
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
                <h5 class="fw-bold text-dark mb-2">Đề Cương Đã Được Công Bố Chính Thức</h5>
                <p class="text-muted small mx-auto mb-3" style="max-width: 620px;">
                    Đề tài đã hoàn tất mọi thủ tục thẩm định và đã được công bố chính thức cho sinh viên. Chức năng nộp và chỉnh sửa đề cương hiện đã được khóa để đảm bảo tính toàn vẹn hồ sơ.
                </p>
                @if($detai->FileDeCuong)
                    @php
                        $filePathLocked = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                    @endphp
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-primary rounded-pill px-4"
                                onclick="quickPreviewOutline('{{ $filePathLocked }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')">
                            <i class="fa-solid fa-eye me-1"></i> Mở Đề Cương Chi Tiết
                        </button>
                        <a href="{{ $filePathLocked }}" download="{{ basename($detai->FileDeCuong) }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fa-solid fa-download me-1"></i> Tải Về Máy
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
                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                        Góp ý / Yêu cầu từ Trưởng Bộ Môn / Ban Chủ Nhiệm Khoa:
                    </div>
                    <div class="text-dark small ps-4">{{ $detai->LyDoTuChoi }}</div>
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
                                            <i class="fa-solid fa-user-tie text-primary"></i>
                                            CB Phản Biện: {{ $pb->giangVien->HoTen ?? 'Chưa rõ' }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-tag me-1"></i>Vai trò: {{ $pb->VaiTro ?? 'Phản biện đề cương' }}
                                            @if($pb->NgayPhanCong)
                                                &bull; Phân công: {{ \Carbon\Carbon::parse($pb->NgayPhanCong)->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @if($pb->KetQua === 'Đạt')
                                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Đạt</span>
                                        @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill"><i class="fa-solid fa-wrench me-1"></i>Yêu Cầu Chỉnh Sửa</span>
                                        @elseif($pb->KetQua === 'Không đạt')
                                            <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i>Không Đạt</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-clock me-1"></i>{{ $pb->KetQua ?? 'Đang thẩm định' }}</span>
                                        @endif
                                        @if($pb->NgayDanhGia)
                                            <div class="small text-muted mt-1">Đánh giá: {{ \Carbon\Carbon::parse($pb->NgayDanhGia)->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label text-muted small fw-semibold mb-1">Nội dung nhận xét / Góp ý chi tiết:</label>
                                    @if($pb->NhanXet)
                                        <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-line;">{{ $pb->NhanXet }}</div>
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
                    {{ $detai->taiLieuNops->count() > 0 ? $detai->taiLieuNops->count() : ($detai->FileDeCuong ? 1 : 0) }} lần nộp
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
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1">
                                            Lần {{ $tl->LanNop ?? 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $tl->TenTaiLieu ?? basename($tl->DuongDan) }}</div>
                                        <span class="small text-muted">{{ $tl->LoaiTaiLieu ?? 'Đề cương chi tiết' }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-secondary">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            {{ $tl->NgayNop ? \Carbon\Carbon::parse($tl->NgayNop)->format('d/m/Y H:i') : ($tl->created_at ? $tl->created_at->format('d/m/Y H:i') : '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $tl->GhiChu ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle"
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
                            @elseif($detai->FileDeCuong)
                                @php
                                    $linkCurrent = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1">
                                            Lần 1
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ basename($detai->FileDeCuong) }}</div>
                                        <span class="small text-muted">Đề cương chi tiết</span>
                                    </td>
                                    <td>
                                        <span class="small text-secondary">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            {{ $detai->updated_at ? $detai->updated_at->format('d/m/Y H:i') : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">Nộp đề cương chi tiết</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle"
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
                                        <i class="fa-solid fa-folder-open fs-2 text-secondary mb-2 d-block"></i>
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
