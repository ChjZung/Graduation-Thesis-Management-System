@extends('layouts.sinhvien')
@section('page_title', 'Kết Quả Khóa Luận')
@section('content')

@if(!$ketQua)
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-hourglass-half fa-3x text-warning mb-3"></i>
        <h5 class="text-muted">Kết Quả Chưa Có</h5>
        <p class="text-muted">Kết quả chấm điểm sẽ được cập nhật sau khi Hội đồng hoàn tất phiên bảo vệ.</p>
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
    $diem = (float) $ketQua->DiemTongKet;
    $xepLoai = $ketQua->KetQua;
    $gradientColor = match(true) {
        $diem >= 9.0 => 'linear-gradient(135deg, #f093fb, #f5576c)',
        $diem >= 8.0 => 'linear-gradient(135deg, #4facfe, #00f2fe)',
        $diem >= 7.0 => 'linear-gradient(135deg, #43e97b, #38f9d7)',
        $diem >= 6.0 => 'linear-gradient(135deg, #fa709a, #fee140)',
        default      => 'linear-gradient(135deg, #a8c0ff, #3f2b96)',
    };
@endphp

<div class="row g-4">
    <!-- Điểm tổng kết nổi bật -->
    <div class="col-md-4">
        <div class="card border-0 text-white text-center" style="background: {{ $gradientColor }}; border-radius: 16px;">
            <div class="card-body py-5">
                <i class="fa-solid fa-award fa-3x mb-3" style="opacity: 0.8;"></i>
                <div style="font-size: 4rem; font-weight: 800; line-height: 1;">{{ number_format($diem, 2) }}</div>
                <div style="font-size: 1.2rem; opacity: 0.9; font-weight: 600;">/ 10.00</div>
                <div class="mt-3">
                    <span class="badge bg-white text-dark rounded-pill px-4 py-2" style="font-size: 1rem; font-weight: 700;">
                        {{ $xepLoai }}
                    </span>
                </div>
                <div class="mt-3 small" style="opacity: 0.85;">
                    Kết quả Khóa luận Tốt nghiệp
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết điểm -->
    <div class="col-md-8">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Chi Tiết Điểm Số
            </div>
            <div class="card-body">
                @if($nhom)
                <div class="mb-3 p-2 rounded" style="background: #f8f9fa;">
                    <small class="text-muted">Đề tài:</small>
                    <div class="fw-bold">{{ $nhom->deTai->TenDeTai ?? '' }}</div>
                    <small class="text-muted">GVHD: {{ $nhom->deTai->giangVien->HoTen ?? '' }}</small>
                </div>
                @endif

                <!-- Thanh điểm từng thành phần -->
                @php
                    $components = [
                        ['label' => 'Giảng viên Hướng dẫn (30%)', 'diem' => $ketQua->DiemHuongDan ?? 0, 'color' => '#4facfe'],
                        ['label' => 'Giảng viên Phản biện (30%)', 'diem' => $ketQua->DiemPhanBien ?? 0, 'color' => '#43e97b'],
                        ['label' => 'Hội đồng Trung bình (40%)', 'diem' => $ketQua->DiemHoiDongTB ?? 0, 'color' => '#f093fb'],
                    ];
                @endphp
                @foreach($components as $comp)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold">{{ $comp['label'] }}</small>
                        <small class="fw-bold">{{ number_format((float)$comp['diem'], 2) }} / 10</small>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px;">
                        <div class="progress-bar rounded-pill" role="progressbar"
                            style="width: {{ ($comp['diem'] / 10) * 100 }}%; background: {{ $comp['color'] }};"
                            aria-valuenow="{{ $comp['diem'] }}" aria-valuemin="0" aria-valuemax="10">
                        </div>
                    </div>
                </div>
                @endforeach

                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Điểm Tổng Kết:</strong>
                    <span class="fw-bold fs-4 text-primary">{{ number_format($diem, 2) }} / 10</span>
                </div>

                @if($ketQua->NhanXetChung)
                <div class="mt-3 p-2 rounded" style="background: #f0f7ff; font-size: 0.88rem;">
                    <strong>Nhận xét chung:</strong> {{ $ketQua->NhanXetChung }}
                </div>
                @endif

                <div class="text-muted mt-3" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-calendar me-1"></i>Ngày chấm: {{ $ketQua->NgayCham }}
                    &nbsp;|&nbsp; Học kỳ: {{ $ketQua->hocKy->TenHocKy ?? '' }} {{ $ketQua->hocKy->NamHoc ?? '' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
