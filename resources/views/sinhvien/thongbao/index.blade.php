@extends('layouts.sinhvien')
@section('page_title', 'Thông Báo Của Tôi')
@section('content')

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-bell me-2 text-primary"></i>Thông Báo Của Tôi</span>
        <form action="{{ route('sinhvien.thongbao.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="fa-solid fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        @forelse($thongBaos as $tb)
        <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom {{ !$tb->DaDoc ? 'bg-light' : '' }}" style="{{ !$tb->DaDoc ? 'background:#f0f7ff!important;' : '' }}">
            <div class="mt-1" style="width: 36px; text-align:center; flex-shrink:0;">
                <i class="fa-solid {{ $tb->icon }} fa-lg"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold {{ !$tb->DaDoc ? 'text-primary-custom' : '' }}">{{ $tb->TieuDe }}</div>
                <div class="small text-muted mt-1">{{ $tb->NoiDung }}</div>
                <div class="text-muted mt-1" style="font-size:.72rem;">
                    <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($tb->created_at)->diffForHumans() }}
                    @if($tb->Loai) &nbsp;·&nbsp; <span class="badge bg-secondary rounded-pill">{{ $tb->Loai }}</span> @endif
                </div>
            </div>
            @if(!$tb->DaDoc)
            <div class="flex-shrink-0">
                <span class="badge bg-primary rounded-circle" style="width:8px;height:8px;padding:0;display:inline-block;"></span>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-bell-slash fa-3x mb-3 opacity-30"></i>
            <p>Chưa có thông báo nào.</p>
        </div>
        @endforelse
    </div>
    @if($thongBaos->hasPages())
    <div class="card-footer bg-white border-0 py-3">{{ $thongBaos->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@endsection
