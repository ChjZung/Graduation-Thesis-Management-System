@extends('layouts.sinhvien')
@section('page_title', 'Kết Quả Khóa Luận & Nộp Lưu Trữ Khoa')
@section('content')

@php
    $diem = null;
    if ($ketQua) {
        $diem = (float) ($ketQua->DiemTongKet ?? $ketQua->DiemHoiDongTB ?? 0);
    } elseif ($phieuChamDiems && $phieuChamDiems->isNotEmpty()) {
        $diem = round((float) $phieuChamDiems->avg('Diem'), 2);
    }

    $xepLoai = $ketQua?->KetQua ?? ($diem !== null ? \App\Services\GradeCalculationService::xepLoai($diem) : 'Đang xử lý');
    $cardBgColor = match(true) {
        $diem === null => '#475569',
        $diem >= 8.5   => '#00529b', // Xanh Navy HUIT trang trọng
        $diem >= 7.0   => '#0f766e', // Xanh Slate
        $diem >= 5.0   => '#b45309', // Hổ phách
        default        => '#991b1b', // Đỏ Chưa đạt
    };

    $diemChu = $ketQua?->diem_chu ?? match(true) {
        $diem >= 9.0 => 'A+',
        $diem >= 8.5 => 'A',
        $diem >= 8.0 => 'B+',
        $diem >= 7.0 => 'B',
        $diem >= 6.5 => 'C+',
        $diem >= 5.5 => 'C',
        $diem >= 5.0 => 'D+',
        $diem >= 4.0 => 'D',
        default      => 'F',
    };

    $diemHe4 = $ketQua?->diem_he4 ?? match(true) {
        $diem >= 8.5 => 4.0,
        $diem >= 8.0 => 3.5,
        $diem >= 7.0 => 3.0,
        $diem >= 6.5 => 2.5,
        $diem >= 5.5 => 2.0,
        $diem >= 5.0 => 1.5,
        $diem >= 4.0 => 1.0,
        default      => 0.0,
    };

    $loai = $ketQua?->LoaiKhoaLuan ?? (str_contains(mb_strtolower($nhom?->deTai?->TenDeTai ?? ''), 'kỹ sư') ? 'KLKS' : 'KLCN');
    $isDaLuuTru = ($hoSo?->TrangThai === 'Đã lưu trữ');
@endphp

<!-- Header Trang -->
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-award text-primary"></i> Kết Quả Khóa Luận Tốt Nghiệp &amp; Nộp Lưu Trữ Khoa
        </h4>
        <p class="text-muted small mb-0">
            Xem điểm đánh giá chính thức từ Hội đồng bảo vệ và nộp lại toàn bộ các sản phẩm khóa luận hoàn thiện cuối cùng để lưu trữ Khoa.
        </p>
    </div>
    @if($isDaLuuTru)
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-box-archive"></i> Đã Lưu Trữ Chính Thức Tại Khoa
        </span>
    @elseif($hoSo?->FileBanChinhSua)
        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i> Đã Nộp Bản Hoàn Chỉnh &bull; Chờ Giáo Vụ Lưu Trữ
        </span>
    @endif
</div>

<!-- Alert Thông báo -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5 text-success"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation fs-5 text-danger"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(!empty($hoSo?->YeuCauChinhSuaSauBaoVe))
    <div class="alert alert-warning border-warning shadow-sm rounded-3 p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-3 mt-1"></i>
            <div>
                <div class="fw-bold text-warning-emphasis fs-6 mb-1">Hội Đồng Yêu Cầu Chỉnh Sửa Báo Cáo Sau Bảo Vệ</div>
                <div class="small text-secondary mb-1">{{ Str::limit($hoSo->YeuCauChinhSuaSauBaoVe, 200) }}</div>
                <div class="small text-muted">
                    <span>Hạn nộp: <strong>{{ \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') }}</strong></span> &bull; 
                    <span>Trạng thái: <strong>{{ $hoSo->TrangThaiChinhSuaSauBaoVe ?? 'Chờ nộp bản sửa' }}</strong></span>
                </div>
            </div>
        </div>
        <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-warning rounded-pill px-4 fw-bold shadow-xs">
            <i class="fa-solid fa-file-pen me-2"></i> Hoàn Thiện Báo Cáo Ngay
        </a>
    </div>
@endif

<!-- KHỐI 1: ĐIỂM SỐ & ĐÁNH GIÁ CỦA HỘI ĐỒNG BẢO VỆ -->
<div class="row g-4 align-items-stretch mb-4">
    <!-- Cột trái: Thẻ Điểm Tổng Kết Nổi Bật -->
    <div class="col-lg-4">
        <div class="card border-0 text-white text-center shadow-sm h-100" style="background-color: {{ $cardBgColor }}; border-radius: 14px;">
            <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center">
                <div class="mb-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 text-white" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-award fa-2x"></i>
                    </span>
                </div>
                
                @if($daCongBoChinhThuc && $diem !== null)
                    <div class="display-4 fw-bold mb-0" style="line-height: 1.1;">
                        {{ number_format($diem, 2) }}
                    </div>
                    <div class="text-white-50 fw-semibold mb-3" style="font-size: 13.5px;">/ 10.00 điểm (Điểm Chính Thức)</div>

                    <div class="mb-3">
                        <span class="badge bg-white text-dark rounded-pill px-4 py-2 fw-bold" style="font-size: 14px; letter-spacing: 0.3px;">
                            {{ $xepLoai }}
                        </span>
                    </div>

                    <div class="p-3 rounded-3 bg-white bg-opacity-10 text-white w-100" style="font-size: 13.5px;">
                        <div>Điểm chữ: <strong>{{ $diemChu }}</strong> &bull; Hệ 4: <strong>{{ number_format($diemHe4, 1) }}</strong></div>
                        <div class="text-white-50 mt-1" style="font-size: 12.5px;">
                            {{ $loai === 'KLKS' ? 'Khóa luận Kỹ sư (KLKS)' : 'Khóa luận Cử nhân (KLCN)' }}
                        </div>
                    </div>
                @elseif($daBaoCaoXong)
                    <h5 class="fw-bold mb-2">Chưa Công Bố Chính Thức</h5>
                    <p class="text-white-50 small mb-0 px-2">
                        Phiên bảo vệ đã hoàn tất. Điểm số chính thức đang được Hội đồng và Trưởng khoa thẩm định trước khi công bố công khai.
                    </p>
                @else
                    <h5 class="fw-bold mb-2">Chưa Có Điểm Bảo Vệ</h5>
                    <p class="text-white-50 small mb-0 px-2">
                        Kết quả đánh giá và điểm số sẽ được công bố sau khi nhóm hoàn thành phiên bảo vệ trước Hội đồng.
                    </p>
                @endif

                @if($hoSo && ($hoSo->ThoiGianBaoVe || $hoSo->PhongBaoVe))
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25 w-100 text-start small">
                        @if($hoSo->ThoiGianBaoVe)
                            <div class="text-white-50">
                                <i class="fa-regular fa-calendar-check me-2"></i>Bảo vệ: <strong class="text-white">{{ \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe)->format('H:i &bull; d/m/Y') }}</strong>
                            </div>
                        @endif
                        @if($hoSo->PhongBaoVe)
                            <div class="text-white-50 mt-1">
                                <i class="fa-solid fa-location-dot me-2"></i>Địa điểm: <strong class="text-white">{{ $hoSo->PhongBaoVe }}</strong>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cột phải: Chi Tiết Đánh Giá Hội Đồng & Thông Tin Đề Tài -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-column text-primary fs-5"></i>
                    <h5 class="mb-0 fw-bold text-dark fs-6">Chi Tiết Đánh Giá Hội Đồng Bảo Vệ</h5>
                </div>
                @if($hoSo?->hoiDong)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold small">
                        {{ $hoSo->hoiDong->TenHoiDong }}
                    </span>
                @endif
            </div>
            <div class="card-body p-4">
                @if($nhom)
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <div class="text-muted mb-1" style="font-size: 12.5px;">Đề tài khóa luận tốt nghiệp:</div>
                    <div class="fw-bold text-primary fs-6">{{ $nhom->deTai->TenDeTai ?? 'Chưa xác định' }}</div>
                    <div class="text-muted mt-2 d-flex flex-wrap gap-4" style="font-size: 13px;">
                        <span><i class="fa-solid fa-user-tie text-secondary me-2"></i>GVHD: <strong class="text-dark">{{ $nhom->deTai->giangVien->HoTen ?? 'N/A' }}</strong></span>
                        <span><i class="fa-solid fa-users text-secondary me-2"></i>Nhóm: <strong class="text-dark">{{ $nhom->TenNhom ?? '' }}</strong></span>
                        @if($nhom->truongNhom)
                            <span><i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm: <strong class="text-dark">{{ $nhom->truongNhom->HoTen }}</strong></span>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Hội Đồng & Các Thành Viên Chấm Điểm -->
                @if($hoSo?->hoiDong && $hoSo->hoiDong->thanhViens->isNotEmpty())
                <div class="mb-3">
                    <div class="fw-bold text-dark mb-2 small d-flex align-items-center gap-2">
                        <i class="fa-solid fa-people-group text-primary"></i>
                        <span>Thành viên Hội đồng bảo vệ ({{ $hoSo->hoiDong->thanhViens->count() }} giảng viên):</span>
                    </div>
                    <div class="row g-2">
                        @foreach($hoSo->hoiDong->thanhViens as $tvHd)
                            @php
                                $badgeRole = match($tvHd->VaiTro) {
                                    'Chủ tịch'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'Thư ký'    => 'bg-info-subtle text-info border border-info-subtle',
                                    'Phản biện' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                    default     => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                };
                                $phieuOfGv = $phieuChamDiems->firstWhere('MaGV', $tvHd->MaGV);
                            @endphp
                            <div class="col-md-6">
                                <div class="p-2 px-3 border rounded-3 bg-white d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold text-dark small">{{ $tvHd->giangVien->HoTen ?? $tvHd->MaGV }}</div>
                                        <div class="text-muted" style="font-size: 11.5px;">{{ $tvHd->giangVien->boMon->TenBoMon ?? 'Bộ môn' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $badgeRole }} rounded-pill px-2 py-1" style="font-size: 11px;">{{ $tvHd->VaiTro }}</span>
                                        @if($phieuOfGv && $phieuOfGv->Diem)
                                            <div class="fw-bold text-primary mt-1" style="font-size: 12px;">{{ number_format($phieuOfGv->Diem, 2) }} đ</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Nhận xét chung của Hội đồng -->
                @if($ketQua?->NhanXetChung || $phieuChamDiems->whereNotNull('NhanXet')->isNotEmpty())
                <div class="p-3 rounded-3 bg-light border mb-3" style="font-size: 13.5px;">
                    <div class="fw-bold text-primary mb-1">
                        <i class="fa-solid fa-comments me-2"></i>Ý kiến đóng góp &amp; Nhận xét của Hội đồng:
                    </div>
                    @if($ketQua?->NhanXetChung)
                        <div class="text-dark mb-2">{!! \App\Helpers\HtmlHelper::clean($ketQua->NhanXetChung) !!}</div>
                    @endif
                    @foreach($phieuChamDiems->whereNotNull('NhanXet') as $pcd)
                        @if(!empty(trim($pcd->NhanXet)))
                            <div class="border-top pt-2 mt-2 small text-muted">
                                <strong class="text-dark">{{ $pcd->giangVien->HoTen ?? 'Giám khảo' }}:</strong>
                                <span class="text-secondary">{!! \App\Helpers\HtmlHelper::clean($pcd->NhanXet) !!}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @endif

                <div class="text-muted small">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    <em>Quy chế Khoa CNTT - HUIT: Điểm khóa luận tốt nghiệp được tính 100% từ điểm đánh giá trung bình của các thành viên trong Hội đồng bảo vệ.</em>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KHỐI 2: NỘP BẢN KHÓA LUẬN HOÀN CHỈNH SAU BẢO VỆ (LƯU TRỮ KHOA) & TẤT CẢ KẾT QUẢ KHÓA LUẬN -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="fw-bold text-dark mb-0 fs-6">
            <i class="fa-solid fa-file-circle-check text-success me-2"></i>
            Nộp Bản Khóa Luận Hoàn Chỉnh Sau Bảo Vệ (Lưu Trữ Khoa) &amp; Kết Quả Khóa Luận
        </h6>
        @if($hoSo?->FileBanChinhSua)
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold small">
                <i class="fa-solid fa-circle-check me-1"></i> Đã Nộp Bản Hoàn Chỉnh
            </span>
        @endif
    </div>
    <div class="card-body p-4">
        <div class="alert alert-light border border-secondary-subtle p-3 rounded-3 mb-4 text-dark" style="font-size: 13.5px; background-color: #f8fafc;">
            <div class="fw-bold text-primary mb-1">
                <i class="fa-solid fa-circle-info me-2"></i>Quy định lưu trữ hồ sơ khóa luận sau bảo vệ:
            </div>
            Sau khi bảo vệ thành công trước Hội đồng, nhóm tiếp thu toàn bộ ý kiến đóng góp của Hội đồng, tiến hành chỉnh sửa và tải lên bản khóa luận hoàn thiện cuối cùng cùng các liên kết mã nguồn, sản phẩm thực nghiệm, slide thuyết trình để lưu trữ tại Khoa và xét cấp bằng tốt nghiệp.
        </div>

        <!-- THÔNG TIN KẾT QUẢ ĐÃ NỘP HIỆN TẠI -->
        @if($hoSo && $hoSo->FileBanChinhSua)
            <div class="border rounded-3 p-3 mb-4 bg-success-subtle border-success-subtle">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success text-white" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-check fa-lg"></i>
                        </span>
                        <div>
                            <div class="fw-bold text-success fs-6">Nhóm đã nộp bản khóa luận hoàn chỉnh sau bảo vệ!</div>
                            <div class="text-muted small mt-1">
                                Thời gian nộp: <strong>{{ $hoSo->NgayNopBanChinhSua ? \Carbon\Carbon::parse($hoSo->NgayNopBanChinhSua)->format('H:i &bull; d/m/Y') : 'Chưa ghi nhận' }}</strong>
                                &bull; Trạng thái lưu trữ: 
                                @if($isDaLuuTru)
                                    <span class="badge bg-success text-white rounded-pill px-2 py-0">Đã lưu trữ chính thức</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0">Chờ Giáo vụ xác nhận</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ asset('storage/' . $hoSo->FileBanChinhSua) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold d-inline-flex align-items-center">
                            <i class="fa-solid fa-download me-2"></i>Tải Về Bản Hoàn Chỉnh
                        </a>
                    </div>
                </div>

                <!-- Các liên kết sản phẩm đã lưu trữ -->
                <div class="row g-2 mt-3 pt-3 border-top border-success-subtle small">
                    <div class="col-md-4">
                        <span class="text-muted d-block">Source Code:</span>
                        @if($hoSo->LinkSourceCode)
                            <a href="{{ $hoSo->LinkSourceCode }}" target="_blank" class="text-primary fw-semibold text-break">
                                <i class="fa-brands fa-github me-1"></i>{{ Str::limit($hoSo->LinkSourceCode, 35) }}
                            </a>
                        @else
                            <span class="text-muted italic">Chưa cập nhật</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Deploy / Sản phẩm:</span>
                        @if($hoSo->LinkDeploy)
                            <a href="{{ $hoSo->LinkDeploy }}" target="_blank" class="text-primary fw-semibold text-break">
                                <i class="fa-solid fa-globe me-1"></i>{{ Str::limit($hoSo->LinkDeploy, 35) }}
                            </a>
                        @else
                            <span class="text-muted italic">Chưa cập nhật</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Tệp bổ sung / Slide:</span>
                        @php
                            $extraCount = is_array($hoSo->LienKetKhac) ? count($hoSo->LienKetKhac) : 0;
                        @endphp
                        <span class="fw-semibold text-dark">{{ $extraCount }} mục tài liệu</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- NỘP / CẬP NHẬT KẾT QUẢ KHÓA LUẬN -->
        @if(!$daBaoCaoXong)
            <!-- CHƯA BÁO CÁO XONG HOẶC CHƯA CÓ HỘI ĐỒNG: CỔNG NỘP ĐÓNG -->
            <div class="card border border-warning-subtle bg-warning-subtle p-4 rounded-3 text-center my-2 shadow-xs">
                <div class="mb-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning text-white shadow-xs" style="width: 56px; height: 56px;">
                        <i class="fa-solid fa-lock fa-2x"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-2">Cổng Nộp Bản Khóa Luận Hoàn Chỉnh (Lưu Trữ Khoa) Chưa Mở</h5>
                <p class="text-muted mx-auto mb-3" style="max-width: 650px; font-size: 13.5px;">
                    Theo quy định, nhóm tiếp thu toàn bộ ý kiến góp ý của Hội đồng trong phiên bảo vệ để hoàn thiện báo cáo và sản phẩm. Cổng nộp bản khóa luận hoàn chỉnh và các kết quả sau bảo vệ sẽ tự động mở sau khi nhóm hoàn tất phiên báo cáo bảo vệ trước Hội đồng.
                </p>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-white rounded-pill border border-warning-subtle text-dark small fw-semibold mx-auto">
                    <i class="fa-solid fa-clock-rotate-left text-warning"></i>
                    <span>Tình trạng hiện tại: 
                        @if($hoSo && $hoSo->hoiDong)
                            Đã xếp Hội đồng <strong>{{ $hoSo->hoiDong->TenHoiDong }}</strong> &bull; Đang chờ diễn ra phiên báo cáo bảo vệ
                        @else
                            Hồ sơ đã được GVHD duyệt &bull; Đang chờ Khoa sắp xếp Hội đồng bảo vệ
                        @endif
                    </span>
                </div>
            </div>
        @elseif(!$isDaLuuTru)
            @if($isLeader)
                <form action="{{ route('sinhvien.ketqua.nopKetQuaHoanChinh') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="MaNhom" value="{{ $nhom->MaNhom }}">

                    <div class="row g-3">
                        <!-- 1. Tệp Bản Khóa Luận Hoàn Chỉnh (Bắt buộc nếu chưa có file) -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small">
                                <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                Tệp Báo Cáo Toàn Văn Khóa Luận Hoàn Chỉnh (Sau Chỉnh Sửa) 
                                @if(empty($hoSo?->FileBanChinhSua))
                                    <span class="text-danger">*</span>
                                @else
                                    <span class="badge bg-light text-muted border ms-1">Đã có tệp &bull; Tải lên tệp mới nếu muốn thay thế</span>
                                @endif
                            </label>
                            <input type="file" name="FileBanChinhSua" class="form-control" 
                                   {{ empty($hoSo?->FileBanChinhSua) ? 'required' : '' }} 
                                   accept=".pdf,.doc,.docx,.zip,.rar">
                            <div class="form-text text-muted small">
                                Định dạng hợp lệ: <strong>.pdf, .doc, .docx, .zip, .rar</strong> (Dung lượng tối đa: 50MB). Bản khóa luận đã tích hợp đầy đủ bìa, nhận xét, biên bản và nội dung đã chỉnh sửa.
                            </div>
                        </div>

                        <!-- 2. Link Mã Nguồn Hoàn Thiện -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">
                                <i class="fa-brands fa-github text-dark me-1"></i>
                                Đường Dẫn Mã Nguồn Hoàn Thiện (Source Code)
                            </label>
                            <input type="url" name="LinkSourceCode" class="form-control" 
                                   placeholder="https://github.com/..." 
                                   value="{{ old('LinkSourceCode', $hoSo?->LinkSourceCode) }}">
                            <div class="form-text text-muted small">Liên kết Git repository hoặc Google Drive chứa mã nguồn hoàn chỉnh.</div>
                        </div>

                        <!-- 3. Link Triển Khai Thực Nghiệm / Deploy -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">
                                <i class="fa-solid fa-globe text-primary me-1"></i>
                                Đường Dẫn Triển Khai / Ứng Dụng Chạy Thử Nghiệm (Deploy)
                            </label>
                            <input type="url" name="LinkDeploy" class="form-control" 
                                   placeholder="https://your-app-domain.com" 
                                   value="{{ old('LinkDeploy', $hoSo?->LinkDeploy) }}">
                            <div class="form-text text-muted small">Đường dẫn trang web hoặc ứng dụng đang hoạt động trực tuyến (nếu có).</div>
                        </div>

                        <!-- 4. Link Video Demo / Slide Thuyết Trình Hoàn Thiện -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small">
                                <i class="fa-solid fa-video text-danger me-1"></i>
                                Đường Dẫn Video Demo &amp; Slide Thuyết Trình Hoàn Thiện
                            </label>
                            <input type="url" name="LinkDemo" class="form-control" 
                                   placeholder="https://youtube.com/... hoặc Google Drive link" 
                                   value="{{ old('LinkDemo') }}">
                            <div class="form-text text-muted small">Video quay demo chi tiết hệ thống và slide báo cáo cuối cùng của nhóm.</div>
                        </div>

                        <!-- 5. Ghi Chú Giải Trình Tiếp Thu Ý Kiến Hội Đồng -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small">
                                <i class="fa-solid fa-pen-to-square text-success me-1"></i>
                                Báo Cáo Giải Trình &amp; Tiếp Thu Ý Kiến Đóng Góp Của Hội Đồng
                            </label>
                            <textarea name="GhiChuChinhSua" rows="3" class="form-control" 
                                      placeholder="Tóm tắt ngắn gọn các nội dung nhóm đã tiếp thu, chỉnh sửa và hoàn thiện theo yêu cầu trong phiên bảo vệ trước Hội đồng...">{{ old('GhiChuChinhSua') }}</textarea>
                            <div class="form-text text-muted small">Nội dung này sẽ được chuyển đến Giáo vụ Khoa và Giảng viên hướng dẫn để đối chiếu trước khi lưu trữ.</div>
                        </div>

                        <!-- Nút Nộp -->
                        <div class="col-12 text-end pt-2">
                            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                                    onclick="return confirm('Xác nhận nộp bản khóa luận hoàn chỉnh và các kết quả sau bảo vệ để lưu trữ tại Khoa?');">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                {{ $hoSo?->FileBanChinhSua ? 'Cập Nhật Toàn Bộ Kết Quả Lưu Trữ' : 'Xác Nhận Nộp Toàn Bộ Kết Quả Lưu Trữ Khoa' }}
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <!-- Alert thông báo dành cho thành viên không phải nhóm trưởng -->
                <div class="alert alert-info border-info-subtle bg-info-subtle p-3 rounded-3 mb-0 small text-dark d-flex align-items-center gap-3">
                    <i class="fa-solid fa-user-lock text-info fs-4"></i>
                    <div>
                        <strong>Quyền Nộp Hồ Sơ Hoàn Chỉnh Dành Riêng Cho Nhóm Trưởng:</strong>
                        <div class="text-muted mt-1">
                            Theo quy định của Khoa, chỉ Nhóm trưởng mới có quyền đại diện nhóm tải lên Bản khóa luận hoàn chỉnh sau bảo vệ và cập nhật các liên kết sản phẩm để lưu trữ. Các thành viên khác có quyền xem và tải tệp về kiểm tra đối chiếu.
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- Đã lưu trữ chính thức -->
            <div class="alert alert-secondary border-0 p-3 rounded-3 mb-0 small text-dark d-flex align-items-center gap-3">
                <i class="fa-solid fa-lock text-secondary fs-4"></i>
                <div>
                    <strong>Hồ sơ khóa luận đã được Giáo vụ Khoa xác nhận lưu trữ chính thức.</strong>
                    <div class="text-muted mt-1">
                        Tất cả các tệp và liên kết kết quả khóa luận đã được đóng băng để phục vụ công tác xét tốt nghiệp và lưu trữ tài liệu của Trường.
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
