@extends('layouts.sinhvien')
@section('page_title', 'Thông Báo Của Tôi')
@section('content')

<div class="card card-premium shadow-sm border-0 rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fa-solid fa-bell me-2 text-primary"></i>Thông Báo Của Tôi
        </h5>
        <form action="{{ route('sinhvien.thongbao.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        @forelse($thongBaos as $tb)
        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom {{ !$tb->DaDoc ? 'bg-light' : '' }}" style="{{ !$tb->DaDoc ? 'background:#f0f7ff!important;' : '' }}">
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <div style="width: 36px; text-align:center; flex-shrink:0;">
                    <i class="fa-solid {{ $tb->icon ?? 'fa-bullhorn' }} text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold {{ !$tb->DaDoc ? 'text-primary' : 'text-dark' }}" style="font-size: 0.9rem;">
                        {{ $tb->TieuDe }}
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                        <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($tb->created_at)->diffForHumans() }}
                        @if($tb->Loai) &nbsp;&bull;&nbsp; <span class="badge bg-light text-secondary border">{{ $tb->Loai }}</span> @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 text-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#tbModal{{ $tb->id }}">
                    Xem chi tiết
                </button>
                @if(!$tb->DaDoc)
                    <span class="badge bg-primary rounded-circle" style="width:8px;height:8px;padding:0;display:inline-block;" title="Chưa đọc"></span>
                @endif
            </div>
        </div>

        {{-- Modal Chi Tiết Thông Báo --}}
        <div class="modal fade" id="tbModal{{ $tb->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <h6 class="modal-title fw-bold text-primary">
                            <i class="fa-solid fa-circle-info me-2"></i>{{ $tb->TieuDe }}
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="text-muted small mb-3">
                            <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($tb->created_at)->format('d/m/Y H:i') }}
                        </div>
                        <div class="p-3 bg-light rounded-3 border text-secondary" style="white-space: pre-line; font-size: 0.9rem; line-height: 1.6;">
                            {{ $tb->NoiDung }}
                        </div>
                    </div>
                    <div class="modal-footer border-top pt-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-bell-slash fa-2x mb-2 d-block opacity-40"></i>
            Chưa có thông báo nào.
        </div>
        @endforelse
    </div>
    @if($thongBaos->hasPages())
    <div class="card-footer bg-white border-0 py-3">{{ $thongBaos->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@endsection
