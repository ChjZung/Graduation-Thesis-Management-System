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
                <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao ?? $tb->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 text-primary fw-semibold">
                    <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                </a>
                @if(!$tb->DaDoc)
                    <span class="badge bg-primary rounded-circle" style="width:8px;height:8px;padding:0;display:inline-block;" title="Chưa đọc"></span>
                @endif
            </div>
        </div>

        {{-- Modal Chi Tiết Thông Báo (Hỗ trợ xem trực tiếp công văn) --}}
        @php
            $modalFile = $tb->FileDinhKem;
            if (!empty($modalFile)) {
                $modalFile = ltrim($modalFile, '/');
                if (!str_starts_with($modalFile, 'storage/') && !str_starts_with($modalFile, 'http')) {
                    $modalFile = 'storage/' . $modalFile;
                }
            }
        @endphp
        <div class="modal fade" id="tbModal{{ $tb->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered {{ !empty($modalFile) ? 'modal-xl' : 'modal-lg' }}">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3 py-1">{{ $tb->Loai ?? 'Thông báo' }}</span>
                            <h6 class="modal-title fw-bold text-dark mb-0">
                                {{ $tb->TieuDe }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if(!empty($modalFile))
                            <div class="row g-3">
                                {{-- 1 Bên: Xem Công Văn Trực Tiếp --}}
                                <div class="col-12 col-lg-7">
                                    <div class="border rounded-3 overflow-hidden" style="height: 520px; background: #525659;">
                                        <iframe src="{{ asset($modalFile) }}#toolbar=1" style="width: 100%; height: 100%; border: none;"></iframe>
                                    </div>
                                </div>
                                {{-- 1 Bên: Thông Tin Thông Báo --}}
                                <div class="col-12 col-lg-5 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="text-muted small mb-2">
                                            <i class="fa-regular fa-clock me-1"></i> Ngày đăng: {{ \Carbon\Carbon::parse($tb->created_at)->format('H:i d/m/Y') }}
                                        </div>
                                        <div class="p-3 bg-light rounded-3 border text-dark mb-3" style="white-space: pre-line; font-size: 0.92rem; line-height: 1.7; max-height: 420px; overflow-y: auto;">
                                            {{ $tb->NoiDung }}
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ asset($modalFile) }}" download class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                                            <i class="fa-solid fa-download me-1"></i> Tải file công văn
                                        </a>
                                        <a href="{{ asset($modalFile) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở tab mới
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted small mb-3">
                                <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($tb->created_at)->format('d/m/Y H:i') }}
                            </div>
                            <div class="p-3 bg-light rounded-3 border text-secondary" style="white-space: pre-line; font-size: 0.9rem; line-height: 1.6;">
                                {{ $tb->NoiDung }}
                            </div>
                        @endif
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
