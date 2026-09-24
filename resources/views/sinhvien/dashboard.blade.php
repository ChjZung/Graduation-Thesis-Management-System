@extends('layouts.sinhvien')

@section('page_title', 'Dashboard Sinh Viên')

@section('content')
<!-- Hero Navy Card (Theo chuẩn Mockup Ảnh 4) -->
<div class="card shadow-lg border-0 text-white rounded-4 mb-4 overflow-hidden"
     style="background: linear-gradient(145deg, #0b1329 0%, #1e293b 100%); border: 1px solid rgba(255,255,255,0.1) !important;">
    <div class="card-body p-4 p-md-5">
        <!-- Badge trạng thái điều kiện & duyệt đề tài -->
        <!-- Badge trạng thái điều kiện & duyệt đề tài -->
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
            @if($isDuDieuKien)
                <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-circle-check me-1"></i> ĐỦ ĐIỀU KIỆN LÀM KHÓA LUẬN ({{ $sinhVien->SoTinChiTichLuy ?? 0 }} TC &bull; GPA: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }})
                </span>
            @else
                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-circle-xmark me-1"></i> CHƯA ĐỦ ĐIỀU KIỆN ({{ $sinhVien->SoTinChiTichLuy ?? 0 }}/115 TC &bull; GPA: {{ number_format($sinhVien->DiemTichLuy ?? 0, 2) }}/2.0)
                </span>
            @endif

            @if($deTai)
                <span class="badge bg-info bg-opacity-20 text-info border border-info px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-check me-1"></i> ĐÃ DUYỆT ĐỀ TÀI
                </span>
            @elseif($nhom)
                <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-users me-1"></i> ĐÃ CÓ NHÓM - CHỜ ĐĂNG KÝ ĐỀ TÀI
                </span>
            @else
                <span class="badge bg-secondary bg-opacity-20 text-light border border-secondary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-user me-1"></i> CHƯA CÓ NHÓM KHÓA LUẬN
                </span>
            @endif
        </div>

        <!-- Tên Đề Tài Lớn -->
        <h2 class="fw-bold text-white mb-3" style="line-height: 1.3;">
            {{ $deTai->TenDeTai ?? ($nhom ? 'Chưa đăng ký đề tài khóa luận' : 'Chưa tham gia nhóm khóa luận') }}
        </h2>

        <!-- Metadata tags -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            @if($deTai)
            <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-1 rounded-pill small">
                <i class="fa-solid fa-hashtag me-1 text-info"></i>Mã đề tài: <strong>{{ $deTai->MaDeTai }}</strong>
            </span>
            @endif
            @if($nhom)
            <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-1 rounded-pill small">
                <i class="fa-solid fa-users me-1 text-info"></i>Mã nhóm: <strong>{{ $nhom->MaNhom }} ({{ $nhom->TenNhom }})</strong>
            </span>
            @endif
            <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-1 rounded-pill small">
                <i class="fa-solid fa-graduation-cap me-1 text-info"></i>Lớp: <strong>{{ $sinhVien->lop->TenLop ?? 'Chưa phân lớp' }}</strong>
            </span>
            <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-1 rounded-pill small">
                <i class="fa-regular fa-calendar me-1 text-info"></i>Học kỳ: <strong>{{ $hocKyHienTai->TenHocKy ?? 'HK2' }} ({{ $hocKyHienTai->NamHoc ?? '2025-2026' }})</strong>
            </span>
        </div>

        <div class="row g-3 pt-3 border-top border-white border-opacity-10">
            <!-- Giảng viên hướng dẫn -->
            <div class="col-md-5">
                <div class="text-white-50 small mb-1">
                    <i class="fa-solid fa-chalkboard-user me-1 text-info"></i>Giảng viên hướng dẫn:
                </div>
                <div class="fw-bold text-white fs-6">
                    {{ $gvhd ? (($gvhd->HocHam ? $gvhd->HocHam . '.' : '') . ($gvhd->HocVi ? $gvhd->HocVi . '.' : '') . ' ' . $gvhd->HoTen) : 'TS. Nguyễn Văn Thắng' }}
                </div>
                <div class="small text-white-50">
                    <i class="fa-regular fa-envelope me-1"></i>{{ $gvhd->Email ?? 'thangnv@huit.edu.vn' }}
                </div>
            </div>

            <!-- Thành viên nhóm -->
            <div class="col-md-7">
                <div class="text-white-50 small mb-1">
                    <i class="fa-solid fa-user-group me-1 text-info"></i>Thành viên nhóm:
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if($truongNhom)
                        <span class="badge bg-primary bg-opacity-25 text-white border border-primary border-opacity-50 px-2 py-1 rounded-pill small">
                            👑 {{ $truongNhom->HoTen }} (Nhóm trưởng) - {{ $truongNhom->MaSV }}
                        </span>
                    @else
                        <span class="badge bg-primary bg-opacity-25 text-white border border-primary border-opacity-50 px-2 py-1 rounded-pill small">
                            👑 Nguyễn Văn A (Nhóm trưởng) - 2001200001
                        </span>
                    @endif

                    @forelse($thanhViens as $tv)
                        @if(!$truongNhom || $tv->MaSV !== $truongNhom->MaSV)
                            <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-2 py-1 rounded-pill small">
                                {{ $tv->sinhVien->HoTen ?? $tv->MaSV }} (Thành viên) - {{ $tv->MaSV }}
                            </span>
                        @endif
                    @empty
                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-2 py-1 rounded-pill small">
                            Trần Thị B (Thành viên) - 2001200002
                        </span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tiến độ hoàn thành khóa luận (75%) -->
        <div class="mt-4 pt-3 border-top border-white border-opacity-10">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-white fw-semibold">
                    <i class="fa-solid fa-bars-progress me-1 text-info"></i>Tiến độ hoàn thành khóa luận:
                </span>
                <span class="fs-5 fw-bold text-info">{{ $tienDoPhanTram }}%</span>
            </div>
            <div class="progress rounded-pill bg-white bg-opacity-10" style="height: 10px;">
                <div class="progress-bar rounded-pill" role="progressbar" 
                     style="width: {{ $tienDoPhanTram }}%; background: linear-gradient(90deg, #06b6d4 0%, #10b981 100%); box-shadow: 0 0 12px rgba(6, 182, 212, 0.6);"
                     aria-valuenow="{{ $tienDoPhanTram }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Lower Section: 2 Columns (Theo chuẩn Mockup Ảnh 4) -->
<div class="row g-4">
    <!-- Cột Trái (7 phần): Việc Cần Làm Tiếp Theo -->
    <div class="col-lg-7">
        <div class="card card-premium shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary fa-lg"></i>
                <h5 class="fw-bold mb-0">Việc Cần Làm Tiếp Theo</h5>
            </div>
            <div class="card-body p-4">
                <!-- Action Card: Nộp Báo Cáo Tiến Độ Đợt 2 -->
                <div class="p-3 rounded-3 bg-light border border-primary-subtle mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-regular fa-clock me-1"></i>Hạn chót: 23:59 - 25/03/2026 (Còn lại 4 ngày)
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 small">
                            Chưa nộp
                        </span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2">
                        Báo Cáo Tiến Độ Đợt {{ $mocTiepTheo }}: Thiết kế hệ thống & CSDL
                    </h6>
                    <p class="small text-secondary mb-3" style="line-height: 1.5;">
                        Sinh viên cần nộp file PDF báo cáo tiến độ chi tiết, đính kèm link Source code Git / Figma và tóm tắt tiến độ tuần qua cho Giảng viên hướng dẫn duyệt.
                    </p>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2"
                           style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                            <i class="fa-solid fa-rocket"></i>
                            <span class="fw-bold">Nộp bài báo cáo ngay</span>
                        </a>
                        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            Xem quy định mốc
                        </a>
                    </div>
                </div>

                <!-- Lịch hẹn hướng dẫn sắp tới -->
                <div class="p-3 rounded-3 bg-white border">
                    <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-success"></i>
                        <span>Lịch hẹn hướng dẫn sắp tới</span>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="p-2 rounded bg-success bg-opacity-10 text-success text-center" style="min-width: 55px;">
                            <div class="fw-bold fs-6">27</div>
                            <div style="font-size: 0.65rem;">TH 3</div>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark small">
                                Thứ Sáu, 27/03/2026 (09:00 - 10:30)
                            </div>
                            <div class="small text-muted mb-1">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i>Văn phòng Bộ môn CNTT (Phòng B204)
                            </div>
                            <div class="small text-secondary" style="font-size: 0.8rem;">
                                Nội dung: Thảo luận thiết kế kiến trúc microservices và tích hợp cổng thanh toán.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột Phải (5 phần): Phản Hồi AI Analytics Mới Nhất -->
    <div class="col-lg-5">
        <div class="card card-premium shadow-sm border-0">
            <div class="card-header py-3 px-3 d-flex justify-content-between align-items-center text-white"
                 style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-brain fa-lg text-warning"></i>
                    <h6 class="fw-bold mb-0">Phản Hồi AI Analytics Mới Nhất</h6>
                </div>
                <span class="badge bg-white text-primary rounded-pill px-2 py-1 shadow-xs fw-bold" style="font-size: 0.72rem;">
                    AI v2.4
                </span>
            </div>
            <div class="card-body p-4 bg-white">
                <!-- Điểm đánh giá AI Rating -->
                <div class="text-center p-3 rounded-3 bg-light border mb-4">
                    <div class="small text-muted mb-1">Điểm đánh giá chất lượng tài liệu</div>
                    <div class="display-4 fw-bold text-primary mb-1">
                        9.2 <span class="fs-5 text-muted fw-normal">/ 10</span>
                    </div>
                    <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                        Đánh giá dựa trên báo cáo đợt 1
                    </div>
                </div>

                <!-- Các điểm nhận xét từ AI -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div class="small text-dark" style="line-height: 1.4;">
                            <strong>Cấu trúc tài liệu rõ ràng:</strong> Đã tuân thủ chuẩn định dạng IEEE/HUIT, phân chia mục lục và danh mục bảng biểu khoa học.
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div class="small text-dark" style="line-height: 1.4;">
                            <strong>Mục tiêu khả thi:</strong> Phạm vi đề tài được xác định cụ thể, có phân tích đối thủ cạnh tranh chi tiết.
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-lightbulb text-warning mt-1"></i>
                        <div class="small text-dark" style="line-height: 1.4;">
                            <strong>Khuyến nghị AI:</strong> Cần bổ sung phân tích kiến trúc Microservices và kiểm thử tải cho module giỏ hàng trong đợt báo cáo 2.
                        </div>
                    </div>
                </div>

                <!-- Nút xem chi tiết -->
                <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-primary rounded-pill w-100 btn-sm fw-bold">
                    <i class="fa-solid fa-magnifying-glass-chart me-1"></i>Xem phân tích chi tiết AI
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
