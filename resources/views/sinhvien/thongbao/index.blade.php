@extends('layouts.sinhvien')
@section('page_title', 'Thông Báo Của Tôi')
@section('content')

<!-- Header Trang -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-bell text-primary me-2"></i>Thông Báo Của Tôi
        </h4>
    </div>
    <form action="{{ route('sinhvien.thongbao.readAll') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
        </button>
    </form>
</div>

<!-- Danh Sách Thông Báo -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        @forelse($thongBaos as $tb)
        @php
            $isUnread = !$tb->DaDoc;
            $iconBg = match($tb->LoaiThongBao ?? $tb->Loai ?? '') {
                'Kế hoạch'  => 'bg-info-subtle text-info',
                'Khóa luận' => 'bg-primary-subtle text-primary',
                'Báo cáo'   => 'bg-warning-subtle text-warning',
                'Hội đồng'  => 'bg-danger-subtle text-danger',
                default     => 'bg-light text-secondary',
            };
        @endphp
        <div class="d-flex align-items-center justify-content-between px-4 py-3.5 border-bottom transition-all {{ $isUnread ? 'bg-light' : '' }}" 
             style="{{ $isUnread ? 'background-color: var(--admin-primary-button-soft) !important;' : '' }}; font-size: 13.5px;">
            <div class="d-flex align-items-center gap-3 flex-grow-1 me-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $iconBg }}" 
                     style="width: 42px; height: 42px;">
                    <i class="fa-solid {{ $tb->icon ?? 'fa-bullhorn' }} fs-6"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao ?? $tb->id) }}" 
                           class="fw-bold text-decoration-none {{ $isUnread ? 'text-primary' : 'text-dark' }}" style="font-size: 14px;">
                            {{ $tb->TieuDe }}
                        </a>
                        @if($isUnread)
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 11.5px;">Mới</span>
                        @endif
                    </div>
                    <div class="text-muted mt-1 d-flex align-items-center gap-2 flex-wrap" style="font-size: 13px;">
                        <span><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($tb->created_at)->diffForHumans() }}</span>
                        @if($tb->Loai)
                            <span>&bull;</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5">{{ $tb->Loai }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao ?? $tb->id) }}" 
                   class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-eye me-1"></i>Xem chi tiết
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-5 px-4 text-muted">
            <div class="mb-3">
                <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light text-secondary" style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-bell-slash fa-2x"></i>
                </span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Chưa có thông báo nào</h6>
            <p class="mb-0" style="font-size: 13.5px;">Các thông báo mới từ Khoa và Giảng viên sẽ được hiển thị tại đây.</p>
        </div>
        @endforelse
    </div>
    @if($thongBaos->hasPages())
    <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-center">
        {{ $thongBaos->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
