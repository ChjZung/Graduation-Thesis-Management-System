@extends('layouts.giangvien')

@section('page_title', 'Chi Tiết Thông Báo')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-3">
        <a href="{{ route('giangvien.thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách thông báo
        </a>
    </div>

    <div class="card card-premium shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                @php
                    $badgeColor = match($thongBao->LoaiThongBao) {
                        'Nhắc nhở' => 'bg-warning text-dark',
                        'Lịch hẹn' => 'bg-info text-white',
                        'Góp ý đề tài' => 'bg-primary',
                        'Kế hoạch' => 'bg-success',
                        default => 'bg-secondary',
                    };
                @endphp
                <span class="badge {{ $badgeColor }} rounded-pill px-3 py-1">
                    {{ $thongBao->LoaiThongBao ?? 'Thông báo' }}
                </span>
                <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                    <i class="fa-solid fa-users me-1"></i>{{ $thongBao->DoiTuongNhan }}
                </span>
            </div>
            <div class="text-muted small">
                <i class="fa-regular fa-clock me-1"></i>Ngày đăng: {{ $thongBao->created_at ? $thongBao->created_at->format('H:i d/m/Y') : ($thongBao->NgayTao ?? '') }}
            </div>
        </div>
        <div class="card-body p-4">
            <h4 class="fw-bold text-dark mb-3">{{ $thongBao->TieuDe }}</h4>

            <div class="mb-4 text-muted small pb-3 border-bottom d-flex align-items-center gap-3">
                <div>
                    <i class="fa-solid fa-user-circle me-1 text-primary"></i>
                    <strong>Người gửi:</strong> 
                    @if($thongBao->giangVien)
                        Giảng viên {{ $thongBao->giangVien->HoTen }} ({{ $thongBao->giangVien->MaGV }})
                    @elseif($thongBao->giaoVu)
                        {{ $thongBao->giaoVu->HoTen }} (Giáo vụ Khoa)
                    @else
                        Ban Quản Trị Hệ Thống
                    @endif
                </div>
                <div>
                    <i class="fa-solid fa-tag me-1 text-secondary"></i>
                    <strong>Mã thông báo:</strong> <span class="badge bg-light text-secondary border">{{ $thongBao->MaThongBao }}</span>
                </div>
            </div>

            <!-- Nội dung thông báo -->
            <div class="announcement-content p-3 bg-light rounded-3 mb-4" style="line-height: 1.8; font-size: 1rem; white-space: pre-line;">
                {{ $thongBao->NoiDung }}
            </div>

            <!-- Tệp đính kèm -->
            @if($thongBao->FileDinhKem)
            <div class="p-3 bg-white border rounded-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                        <i class="fa-solid fa-file-lines fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Tệp tài liệu đính kèm</div>
                        <div class="small text-muted">{{ basename($thongBao->FileDinhKem) }}</div>
                    </div>
                </div>
                <div>
                    <a href="{{ asset($thongBao->FileDinhKem) }}" target="_blank" download class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Tải về
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
