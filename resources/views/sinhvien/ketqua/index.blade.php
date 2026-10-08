@extends('layouts.sinhvien')
@section('page_title', 'Kết Quả Khóa Luận')
@section('content')

@if(!$ketQua)
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-hourglass-half fa-3x text-warning mb-3"></i>
        <h5 class="text-muted">Kết Quả Chưa Được Công Bố</h5>
        <p class="text-muted">Kết quả chấm điểm sẽ được cập nhật sau khi Hội đồng hoàn tất phiên bảo vệ và ký duyệt biên bản.</p>
        @if($nhom)
        <div class="mt-3 p-3 rounded-3" style="background: #f8f9fa;">
            <strong>Đề tài:</strong> {{ $nhom->deTai->TenDeTai ?? 'Chưa rõ' }}
        </div>
        @endif
    </div>
</div>
@else
<!-- Bảng điểm -->
@php
    $diem = (float) ($ketQua->DiemTongKet ?? $ketQua->DiemHoiDongTB ?? 0);
    $xepLoai = $ketQua->KetQua;
    $gradientColor = match(true) {
        $diem >= 9.0 => 'linear-gradient(135deg, #0d8a4f, #10b981)',
        $diem >= 8.0 => 'linear-gradient(135deg, #0072ce, #0284c7)',
        $diem >= 7.0 => 'linear-gradient(135deg, #0284c7, #38bdf8)',
        $diem >= 5.0 => 'linear-gradient(135deg, #d97706, #f59e0b)',
        default      => 'linear-gradient(135deg, #dc2626, #ef4444)',
    };
    $loai = $ketQua->LoaiKhoaLuan ?? (str_contains(mb_strtolower($nhom?->deTai?->TenDeTai ?? ''), 'kỹ sư') ? 'KLKS' : 'KLCN');
@endphp

<div class="row g-4">
    <!-- Điểm tổng kết nổi bật -->
    <div class="col-md-4">
        <div class="card border-0 text-white text-center shadow-sm" style="background: {{ $gradientColor }}; border-radius: 16px;">
            <div class="card-body py-5">
                <i class="fa-solid fa-award fa-3x mb-3" style="opacity: 0.9;"></i>
                <div style="font-size: 3.8rem; font-weight: 800; line-height: 1;">{{ number_format($diem, 2) }}</div>
                <div style="font-size: 1.1rem; opacity: 0.9; font-weight: 600;">/ 10.00</div>
                <div class="mt-3">
                    <span class="badge bg-white text-dark rounded-pill px-4 py-2" style="font-size: 1rem; font-weight: 700;">
                        {{ $xepLoai }}
                    </span>
                </div>
                <div class="mt-3 small" style="opacity: 0.9;">
                    Điểm chữ: <strong>{{ $ketQua->diem_chu }}</strong> (Hệ 4: <strong>{{ number_format($ketQua->diem_he4, 1) }}</strong>)
                </div>
                <div class="mt-2 text-white-50 small">
                    Loại: {{ $loai === 'KLKS' ? 'Khóa luận Kỹ sư (KLKS)' : 'Khóa luận Cử nhân (KLCN)' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết điểm -->
    <div class="col-md-8">
        <div class="card card-premium h-100 shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 fw-bold text-dark">
                <i class="fa-solid fa-chart-column me-2 text-primary"></i>Chi Tiết Điểm Đánh Giá Khóa Luận
            </div>
            <div class="card-body">
                @if($nhom)
                <div class="mb-3 p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <small class="text-muted">Đề tài khóa luận:</small>
                    <div class="fw-bold text-primary fs-6">{{ $nhom->deTai->TenDeTai ?? '' }}</div>
                    <div class="small text-muted mt-1">
                        GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? '' }}</strong> &bull;
                        Nhóm: <strong>{{ $nhom->TenNhom ?? '' }}</strong>
                    </div>
                </div>
                @endif

                <!-- Điểm Hội đồng bảo vệ (100%) -->
                <div class="p-3 mb-3 rounded-3 border bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-dark">
                            <i class="fa-solid fa-landmark text-primary me-2"></i>Điểm Đánh Giá Hội Đồng Bảo Vệ (100%)
                        </div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($diem, 2) }} / 10.0</div>
                    </div>
                    <div class="progress rounded-pill mb-2" style="height: 12px;">
                        <div class="progress-bar rounded-pill bg-primary" role="progressbar"
                            style="width: {{ ($diem / 10) * 100 }}%;"
                            aria-valuenow="{{ $diem }}" aria-valuemin="0" aria-valuemax="10">
                        </div>
                    </div>
                    <div class="small text-muted">
                        Đánh giá dựa trên phiếu chấm Rubric chuẩn hóa Khoa CNTT (chất lượng nội dung, sản phẩm thực nghiệm, báo cáo, phản biện).
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-white border">
                    <div>
                        <strong>Quy đổi Tín chỉ:</strong>
                        <span class="ms-2">Điểm chữ: <strong class="text-success fs-6">{{ $ketQua->diem_chu }}</strong> &bull; Thang 4.0: <strong class="text-primary fs-6">{{ number_format($ketQua->diem_he4, 1) }}</strong></span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                        {{ $xepLoai }}
                    </span>
                </div>

                @if($ketQua->NhanXetChung)
                <div class="mt-3 p-3 rounded-3" style="background: #f0f7ff; font-size: 0.88rem; border: 1px solid #d0e2ff;">
                    <strong class="text-primary"><i class="fa-solid fa-comment-dots me-1"></i>Nhận xét từ Hội đồng:</strong>
                    <div class="text-dark mt-1">{{ $ketQua->NhanXetChung }}</div>
                </div>
                @endif

                <div class="mt-3 small text-muted">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i>
                    <em>Quy chế Khoa CNTT - HUIT: Điểm khóa luận tốt nghiệp được tính 100% từ điểm đánh giá của Hội đồng bảo vệ.</em>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
