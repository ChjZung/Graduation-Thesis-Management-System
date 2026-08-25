@extends('layouts.admin')

@section('page_title', 'Chi Tiết Thông Báo System')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách thông báo
    </a>
    
    <div class="d-flex gap-2">
        @if(in_array($thongBao->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']))
            <a href="{{ route('thongbao.edit', $thongBao->MaThongBao) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa
            </a>
            <form action="{{ route('thongbao.sendNow', $thongBao->MaThongBao) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận phát hành ngay thông báo này?');">
                @csrf
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-paper-plane me-1"></i> Phát Hành Ngay
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Header Metadata Banner -->
<div class="card card-premium mb-4">
    <div class="card-body p-4">
        @php
            $badgeClass = match($thongBao->TrangThai) {
                'NHÁP'       => 'bg-secondary',
                'ĐÃ LÊN LỊCH' => 'bg-warning text-dark',
                'ĐÃ GỬI'     => 'bg-success',
                'ĐÃ HỦY'     => 'bg-danger',
                default      => 'bg-info',
            };
        @endphp
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="badge-code me-2">{{ $thongBao->MaThongBao }}</span>
                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 fs-6">{{ $thongBao->TrangThai }}</span>
                <h3 class="fw-bold text-primary mt-2 mb-2">{{ $thongBao->TieuDe }}</h3>
                <div class="d-flex gap-3 text-muted small">
                    <div><i class="fa-solid fa-user me-1"></i>Người phát hành: <strong>{{ $thongBao->MaGVu ?? 'ADMIN' }}</strong></div>
                    <div><i class="fa-regular fa-clock me-1"></i>Ngày tạo: <strong>{{ date('d/m/Y H:i', strtotime($thongBao->NgayTao)) }}</strong></div>
                    @if($thongBao->NgayGui)
                        <div class="text-success"><i class="fa-solid fa-paper-plane me-1"></i>Ngày gửi: <strong>{{ date('d/m/Y H:i', strtotime($thongBao->NgayGui)) }}</strong></div>
                    @endif
                </div>
            </div>

            @if($thongBao->FileDinhKem)
            <div>
                <a href="{{ asset($thongBao->FileDinhKem) }}" target="_blank" class="btn btn-danger btn-lg rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-file-pdf me-2"></i> Xem File Đính Kèm Gốc
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Notification Body & Recipient Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <span><i class="fa-solid fa-align-left me-2 text-primary"></i> Nội Dung Chi Tiết Thông Báo</span>
                <span class="badge bg-light text-primary border">{{ $thongBao->LoaiThongBao }}</span>
            </div>
            <div class="card-body p-4 fs-6 text-dark leading-relaxed" style="white-space: pre-line;">
                {{ $thongBao->NoiDung }}
            </div>
            @if($thongBao->keHoach->TenKeHoach)
            <div class="card-footer bg-light p-3">
                <div class="small fw-bold text-muted mb-1"><i class="fa-solid fa-link me-1 text-primary"></i> Kế Hoạch Liên Kết:</div>
                <div class="fw-bold text-primary">{{ $thongBao->keHoach->TenKeHoach }} ({{ $thongBao->keHoach->MaHocKy }})</div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <!-- Stat Card Recipient Log -->
        <div class="card card-premium mb-4">
            <div class="card-header-premium">
                <span><i class="fa-solid fa-chart-pie me-2 text-primary"></i> Thống Kê Phân Phối Người Nhận</span>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="small text-muted fw-bold">Đối Tượng Nhận:</label>
                    <div class="fw-bold text-dark fs-6"><i class="fa-solid fa-users me-1 text-primary"></i>{{ $thongBao->DoiTuongNhan }}</div>
                </div>

                <div class="row text-center g-2 pt-2 border-top">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded-3 border">
                            <div class="fs-5 fw-bold text-primary">{{ $totalSent }}</div>
                            <div class="small text-muted" style="font-size:0.7rem;">Tổng gửi</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-success bg-opacity-10 rounded-3 border border-success">
                            <div class="fs-5 fw-bold text-success">{{ $readCount }}</div>
                            <div class="small text-muted" style="font-size:0.7rem;">Đã đọc</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-warning bg-opacity-10 rounded-3 border border-warning">
                            <div class="fs-5 fw-bold text-warning-emphasis">{{ $unreadCount }}</div>
                            <div class="small text-muted" style="font-size:0.7rem;">Chưa đọc</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
