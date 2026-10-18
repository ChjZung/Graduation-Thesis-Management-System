@extends('layouts.sinhvien')
@section('page_title', 'Kết Quả Khóa Luận')
@section('content')

<!-- Header Trang -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-0">
        <i class="fa-solid fa-award text-primary me-2"></i>Kết Quả Khóa Luận Tốt Nghiệp
    </h4>
</div>

@if(!$ketQua)
{{-- Khi chưa có kết quả công bố --}}
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 px-4">
        <div class="mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning" style="width: 72px; height: 72px;">
                <i class="fa-solid fa-hourglass-half fa-2x"></i>
            </span>
        </div>
        <h5 class="fw-bold text-dark mb-2">Kết quả chưa được công bố</h5>
        <p class="text-muted mx-auto mb-4" style="max-width: 520px; font-size: 13.5px;">
            Kết quả đánh giá sẽ được cập nhật sau khi Hội đồng hoàn tất phiên bảo vệ và Ban Chủ nhiệm Khoa phê duyệt biên bản chính thức.
        </p>
        @if($nhom && $nhom->deTai)
        <div class="p-3 bg-light rounded-3 border d-inline-block text-start" style="max-width: 600px; font-size: 13.5px;">
            <div class="text-muted mb-1" style="font-size: 13px;">Đề tài bảo vệ:</div>
            <div class="fw-bold text-primary">{{ $nhom->deTai->TenDeTai }}</div>
        </div>
        @endif
    </div>
</div>
@else
@php
    $diem = (float) ($ketQua->DiemTongKet ?? $ketQua->DiemHoiDongTB ?? 0);
    $xepLoai = $ketQua->KetQua;
    $cardBgColor = match(true) {
        $diem >= 8.5 => '#00529b', // Xanh Navy HUIT trang trọng (Xuất sắc / Giỏi)
        $diem >= 7.0 => '#0f766e', // Xanh Slate Trầm học thuật (Khá)
        $diem >= 5.0 => '#b45309', // Hổ phách đậm (Trung bình)
        default      => '#991b1b', // Đỏ sẫm trang nghiêm (Chưa đạt)
    };
    $loai = $ketQua->LoaiKhoaLuan ?? (str_contains(mb_strtolower($nhom?->deTai?->TenDeTai ?? ''), 'kỹ sư') ? 'KLKS' : 'KLCN');
@endphp

<div class="row g-4 align-items-stretch">
    <!-- Cột trái: Thẻ Điểm Tổng Kết Nổi Bật -->
    <div class="col-lg-4">
        <div class="card border-0 text-white text-center shadow-sm h-100" style="background-color: {{ $cardBgColor }}; border-radius: 14px;">
            <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center" style="padding: 28px !important;">
                <i class="fa-solid fa-award fa-3x mb-3" style="opacity: 0.95;"></i>
                
                <div class="display-4 fw-bold mb-0" style="line-height: 1.1;">
                    {{ number_format($diem, 2) }}
                </div>
                <div class="text-white-50 fw-semibold mb-3" style="font-size: 14px;">/ 10.00 điểm</div>

                <div class="mb-3">
                    <span class="badge bg-white text-dark rounded-pill px-4 py-2 fw-bold" style="font-size: 14px;">
                        {{ $xepLoai }}
                    </span>
                </div>

                <div class="p-2 px-3 rounded-3 bg-white bg-opacity-10 text-white w-100" style="font-size: 13.5px;">
                    <div>Điểm chữ: <strong>{{ $ketQua->diem_chu }}</strong> &bull; Hệ 4: <strong>{{ number_format($ketQua->diem_he4, 1) }}</strong></div>
                    <div class="text-white-50 mt-1" style="font-size: 13px;">
                        {{ $loai === 'KLKS' ? 'Khóa luận Kỹ sư (KLKS)' : 'Khóa luận Cử nhân (KLCN)' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Chi Tiết Đánh Giá & Quy Đổi -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-chart-column text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark fs-6">Chi Tiết Đánh Giá Khóa Luận</h5>
            </div>
            <div class="card-body p-4">
                @if($nhom)
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <div class="text-muted mb-1" style="font-size: 13px;">Đề tài khóa luận:</div>
                    <div class="fw-bold text-primary fs-6">{{ $nhom->deTai->TenDeTai ?? '' }}</div>
                    <div class="text-muted mt-2 d-flex flex-wrap gap-3" style="font-size: 13px;">
                        <span><i class="fa-solid fa-user-tie text-secondary me-2"></i>GVHD: <strong class="text-dark">{{ $nhom->deTai->giangVien->HoTen ?? 'N/A' }}</strong></span>
                        <span><i class="fa-solid fa-users text-secondary me-2"></i>Nhóm: <strong class="text-dark">{{ $nhom->TenNhom ?? '' }}</strong></span>
                    </div>
                </div>
                @endif

                <!-- Điểm Hội Đồng Bảo Vệ -->
                <div class="p-3 mb-3 rounded-3 border bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">
                            <i class="fa-solid fa-landmark text-primary me-2"></i>Điểm Đánh Giá Hội Đồng Bảo Vệ (100%)
                        </div>
                        <div class="fs-5 fw-bold text-primary">{{ number_format($diem, 2) }} / 10.0</div>
                    </div>
                    <div class="progress rounded-pill mb-2" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-primary" role="progressbar"
                            style="width: {{ ($diem / 10) * 100 }}%;"
                            aria-valuenow="{{ $diem }}" aria-valuemin="0" aria-valuemax="10">
                        </div>
                    </div>
                    <div class="text-muted" style="font-size: 13px;">
                        Đánh giá dựa trên phiếu chấm Rubric chuẩn hóa Khoa CNTT (nội dung khóa luận, sản phẩm thực nghiệm, kỹ năng thuyết trình và trả lời phản biện).
                    </div>
                </div>

                <!-- Quy đổi tín chỉ -->
                <div class="d-flex flex-wrap justify-content-between align-items-center p-3 rounded-3 bg-white border mb-3 gap-2">
                    <div style="font-size: 13.5px;">
                        <span class="text-muted">Quy đổi Tín chỉ:</span>
                        <span class="ms-2">Điểm chữ: <strong class="text-primary">{{ $ketQua->diem_chu }}</strong> &bull; Thang 4.0: <strong class="text-primary">{{ number_format($ketQua->diem_he4, 1) }}</strong></span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                        {{ $xepLoai }}
                    </span>
                </div>

                @if($ketQua->NhanXetChung)
                <div class="p-3 rounded-3 bg-light border mb-3" style="font-size: 13.5px;">
                    <div class="fw-bold text-primary mb-1">
                        <i class="fa-solid fa-comment-dots me-2"></i>Nhận xét từ Hội đồng:
                    </div>
                    <div class="text-dark">{{ $ketQua->NhanXetChung }}</div>
                </div>
                @endif

                <div class="text-muted" style="font-size: 13px;">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    <em>Quy chế Khoa CNTT - HUIT: Điểm khóa luận tốt nghiệp được tính 100% từ điểm đánh giá của Hội đồng bảo vệ.</em>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
