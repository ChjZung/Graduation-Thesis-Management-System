@extends('layouts.giangvien')

@section('page_title', 'Chi Tiết Thông Báo & Văn Bản')

@push('styles')
<style>
    .doc-viewer-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 170px);
        min-height: 680px;
    }
    .doc-viewer-header {
        background: #1e293b;
        color: #f8fafc;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .doc-viewer-body {
        flex-grow: 1;
        position: relative;
        background: #525659;
        overflow: hidden;
    }
    .doc-viewer-body iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
    .noti-detail-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 170px);
        min-height: 680px;
    }
    .noti-detail-scroll {
        overflow-y: auto;
        padding: 20px;
        flex-grow: 1;
    }
    .doc-fullscreen-active {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('giangvien.thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách thông báo
        </a>
        @if(!empty($fileUrl))
            <a href="{{ asset($fileUrl) }}" download class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm d-inline-flex align-items-center">
                <i class="fa-solid fa-download me-2"></i> Tải Công Văn Đính Kèm
            </a>
        @endif
    </div>

    @if(!empty($fileUrl))
    {{-- Giao diện Song Song (Split-view) 2 bên khi CÓ TỆP ĐÍNH KÈM --}}
    <div class="row g-3">
        {{-- Cột Trái: Trình Xem File Văn Bản (58%) --}}
        <div class="col-12 col-xl-7">
            <div class="doc-viewer-card" id="gvDocViewerCard">
                <div class="doc-viewer-header">
                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                        <i class="fa-solid {{ ($fileType ?? '') === 'image' ? 'fa-file-image text-warning' : 'fa-file-pdf text-danger' }} fs-5 me-2"></i>
                        <span class="fw-semibold text-truncate small" title="{{ !empty($fileUrl) ? basename($fileUrl) : 'VanBan_ThongBao.pdf' }}">
                            {{ !empty($fileUrl) ? basename($fileUrl) : 'Văn Bản Công Văn Thông Báo (Khoa CNTT)' }}
                        </span>
                        <span class="badge bg-secondary rounded-pill px-2 small">{{ strtoupper($fileType ?? 'PDF') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        @if(!empty($fileUrl))
                            <a href="{{ asset($fileUrl) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 d-inline-flex align-items-center" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Mở tab mới
                            </a>
                            <a href="{{ asset($fileUrl) }}" download class="btn btn-sm btn-light text-dark rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-download me-2"></i> Tải về
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-light rounded-circle p-1" style="width: 32px; height: 32px;" onclick="toggleGvDocFullscreen()" title="Toàn màn hình">
                            <i class="fa-solid fa-expand" id="gvFullscreenIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="doc-viewer-body">
                    @if(($fileType ?? '') === 'image')
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center p-3 overflow-auto" style="background: #334155;">
                            <img src="{{ asset($fileUrl) }}" alt="Ảnh công văn thông báo" class="img-fluid rounded shadow" style="max-height: 100%; object-fit: contain;">
                        </div>
                    @else
                        <iframe src="{{ asset($fileUrl) }}#toolbar=1&navpanes=0"></iframe>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cột Phải: Thông Tin Thông Báo Chi Tiết (42%) --}}
        <div class="col-12 col-xl-5">
            <div class="noti-detail-card">
                <div class="noti-detail-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold">
                            {{ $thongBao->LoaiThongBao ?? 'Thông báo' }}
                        </span>
                        <span class="small text-muted">
                            <i class="fa-regular fa-clock me-2"></i>{{ date('d/m/Y H:i', strtotime($thongBao->NgayTao ?? $thongBao->created_at)) }}
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-3" style="line-height: 1.45;">{{ $thongBao->TieuDe }}</h5>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="text-muted">Người gửi:</div>
                                <div class="fw-bold text-dark mt-1">
                                    @if($thongBao->giangVien)
                                        GV {{ $thongBao->giangVien->HoTen }}
                                    @elseif($thongBao->giaoVu)
                                        {{ $thongBao->giaoVu->HoTen }} (Khoa)
                                    @else
                                        Khoa CNTT
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Phạm vi:</div>
                                <div class="fw-bold text-dark mt-1">{{ $thongBao->DoiTuongNhan ?? 'Giảng viên' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-align-left text-primary me-2"></i> Nội Dung Thông Báo:</h6>
                        <div class="p-3 rounded-3 border bg-white text-dark shadow-xs" style="font-size: 0.92rem; line-height: 1.75; border-left: 4px solid #0072ce !important;">
                            {!! \App\Helpers\HtmlHelper::clean($thongBao->NoiDung) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Giao diện Thuần Thông Tin khi KHÔNG CÓ TỆP ĐÍNH KÈM --}}
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9 col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                            <i class="fa-solid fa-bullhorn me-2"></i> {{ $thongBao->LoaiThongBao ?? 'Thông báo' }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ $thongBao->TrangThai ?? 'Đã phát hành' }}
                        </span>
                    </div>
                    <span class="text-muted small">
                        <i class="fa-regular fa-clock me-1"></i> {{ date('d/m/Y H:i', strtotime($thongBao->NgayTao ?? $thongBao->created_at)) }}
                    </span>
                </div>

                <h4 class="fw-bold text-dark mb-4" style="line-height: 1.45;">{{ $thongBao->TieuDe }}</h4>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-3 small">
                        <div class="col-md-4 col-sm-6">
                            <div class="text-muted">Cơ quan / Người gửi:</div>
                            <div class="fw-bold text-dark mt-1">
                                <i class="fa-solid fa-building-columns text-primary me-2"></i>
                                @if($thongBao->giangVien)
                                    GV {{ $thongBao->giangVien->HoTen }}
                                @elseif($thongBao->giaoVu)
                                    {{ $thongBao->giaoVu->HoTen }} (Khoa)
                                @else
                                    Khoa CNTT
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="text-muted">Phạm vi đối tượng:</div>
                            <div class="fw-bold text-dark mt-1">
                                <i class="fa-solid fa-users text-primary me-2"></i>
                                {{ $thongBao->DoiTuongNhan ?? 'Giảng viên' }}
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="text-muted">Mã số thông báo:</div>
                            <div class="fw-bold text-dark mt-1 font-monospace">
                                <i class="fa-solid fa-hashtag text-primary me-2"></i>
                                {{ $thongBao->MaThongBao }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-align-left text-primary me-2"></i>
                        <span>Nội Dung Chi Tiết</span>
                    </h6>
                    <div class="p-4 rounded-3 border bg-light-subtle text-dark shadow-xs" style="font-size: 0.98rem; line-height: 1.85; border-left: 5px solid #0072ce !important; min-height: 160px;">
                        {!! \App\Helpers\HtmlHelper::clean($thongBao->NoiDung) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function toggleGvDocFullscreen() {
        const card = document.getElementById('gvDocViewerCard');
        const icon = document.getElementById('gvFullscreenIcon');
        if (card.classList.contains('doc-fullscreen-active')) {
            card.classList.remove('doc-fullscreen-active');
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        } else {
            card.classList.add('doc-fullscreen-active');
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        }
    }
</script>
@endpush
@endsection
