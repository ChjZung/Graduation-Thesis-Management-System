@extends('layouts.sinhvien')

@section('page_title', 'Chi Tiết Báo Cáo Nhỏ - ' . $khoi->TieuDe)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Breadcrumb & Tiêu đề -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none text-muted transition-all hover-primary">Báo Cáo Tiến Độ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none text-muted transition-all hover-primary">Giai Đoạn {{ $khoi->LanBaoCao }}</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Lần nộp #{{ $khoi->LanNop }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-file-lines text-primary me-2"></i>{{ $khoi->TieuDe }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center transition-all hover-scale" style="font-size: 13px;">
                <i class="fa-solid fa-arrow-left me-2"></i>Về danh sách
            </a>
            @if($khoi->TrangThai !== 'Đạt')
                <a href="{{ route('sinhvien.baocao.edit_khoi', $khoi->MaKhoi) }}" class="btn btn-warning rounded-pill px-3.5 py-2 fw-semibold shadow-xs transition-all hover-scale" style="font-size: 13px;">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Chỉnh sửa báo cáo
                </a>
            @endif
        </div>
    </div>

    <!-- Trạng thái & Nhận xét của Giảng viên -->
    <div class="card mb-4 border-0 shadow-sm transition-all hover-shadow" style="background: {{ $khoi->TrangThai === 'Đạt' ? '#f0fdf4' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? '#fffbeb' : '#f8fafc') }}; border-left: 5px solid {{ $khoi->TrangThai === 'Đạt' ? '#16a34a' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? '#d97706' : '#64748b') }} !important; border-radius: 12px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                    <span class="badge {{ $khoi->TrangThai === 'Đạt' ? 'bg-primary' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill px-3 py-2 fw-bold shadow-xs">
                        {{ $khoi->TrangThai }}
                    </span>
                    <span class="text-muted" style="font-size: 13px;">
                        <i class="fa-regular fa-clock me-1.5 text-primary"></i>Nộp lúc: <strong>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}</strong>
                    </span>
                    <span class="badge bg-white text-dark border rounded-pill px-3 py-1.5 shadow-xs">
                        Tiến độ: <strong class="text-primary">{{ $khoi->PhanTramHoanThanh ?? 0 }}%</strong>
                    </span>
                </div>
                @if($khoi->NhanXetGV)
                    <span class="text-muted" style="font-size: 12.5px;">
                        Đánh giá bởi: <strong>{{ $khoi->NguoiNhanXet ?? 'GVHD' }}</strong> ({{ \Carbon\Carbon::parse($khoi->NgayNhanXet)->format('d/m/Y') }})
                    </span>
                @endif
            </div>

            @if($khoi->NhanXetGV)
                <div class="p-3.5 bg-white rounded-3 border mt-2 shadow-xs">
                    <strong class="text-dark d-block mb-1.5"><i class="fa-solid fa-comment-dots text-primary me-2"></i>Nhận xét từ Giảng viên hướng dẫn:</strong>
                    <div class="mb-0 text-dark" style="font-size: 13.5px; line-height: 1.6;">{!! \App\Helpers\HtmlHelper::clean($khoi->NhanXetGV) !!}</div>
                </div>
            @else
                <div class="text-secondary small mt-1 py-1">
                    <i class="fa-solid fa-circle-info me-2 text-muted"></i>Báo cáo đang chờ Giảng viên hướng dẫn xem xét và đánh giá.
                </div>
            @endif
        </div>
    </div>

    <!-- Nội dung báo cáo sinh viên đã trình bày -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden transition-all hover-shadow" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3.5 px-4 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-file-waveform text-primary me-2"></i>Nội Dung Báo Cáo Chi Tiết
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-2.5" style="font-size: 13.5px;">
                            <i class="fa-solid fa-circle-check me-2"></i>1. Kết quả đạt được &amp; Công việc đã làm:
                        </h6>
                        <div class="p-3.5 rounded-3 bg-light border text-dark transition-all hover-shadow-sm" style="font-size: 13.5px; line-height: 1.6;">{!! \App\Helpers\HtmlHelper::clean($khoi->KetQuaDatDuoc ?: ($khoi->NoiDungBaoCao ?: 'Chưa cập nhật')) !!}</div>
                    </div>

                    @if($khoi->KhoKhan)
                    <div class="mb-4">
                        <h6 class="fw-bold text-warning-emphasis mb-2.5" style="font-size: 13.5px;">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>2. Khó khăn &amp; Rào cản gặp phải:
                        </h6>
                        <div class="p-3.5 rounded-3 bg-light border text-dark transition-all hover-shadow-sm" style="font-size: 13.5px; line-height: 1.6;">{!! \App\Helpers\HtmlHelper::clean($khoi->KhoKhan) !!}</div>
                    </div>
                    @endif

                    @if($khoi->HuongGiaiQuyet)
                    <div class="mb-4">
                        <h6 class="fw-bold text-info mb-2.5" style="font-size: 13.5px;">
                            <i class="fa-solid fa-lightbulb me-2"></i>3. Hướng giải quyết đề xuất:
                        </h6>
                        <div class="p-3.5 rounded-3 bg-light border text-dark transition-all hover-shadow-sm" style="font-size: 13.5px; line-height: 1.6;">{!! \App\Helpers\HtmlHelper::clean($khoi->HuongGiaiQuyet) !!}</div>
                    </div>
                    @endif

                    @if($khoi->KeHoachTiepTheo)
                    <div class="mb-2">
                        <h6 class="fw-bold text-primary mb-2.5" style="font-size: 13.5px;">
                            <i class="fa-solid fa-calendar-check me-2"></i>4. Kế hoạch giai đoạn tiếp theo:
                        </h6>
                        <div class="p-3.5 rounded-3 bg-light border text-dark transition-all hover-shadow-sm" style="font-size: 13.5px; line-height: 1.6;">{!! \App\Helpers\HtmlHelper::clean($khoi->KeHoachTiepTheo) !!}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột Phải: Tài liệu đính kèm & Tóm tắt AI -->
        <div class="col-lg-5">
            <!-- Tài liệu & Source Code -->
            <div class="card mb-4 shadow-sm border-0 rounded-4 overflow-hidden transition-all hover-shadow" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3.5 px-4 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-paperclip text-primary me-2"></i>Minh Chứng Đính Kèm
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3.5">
                        <div class="text-muted small mb-2 fw-medium">Tài liệu báo cáo:</div>
                        @if($khoi->DuongDanFile)
                            @include('partials.report_attachment', ['report' => $khoi])
                        @else
                            <div class="p-3 rounded-3 bg-light text-muted fst-italic small text-center border">Không có tệp đính kèm.</div>
                        @endif
                    </div>

                    @if($khoi->LinkCode)
                    <div>
                        <div class="text-muted small mb-2 fw-medium">Mã nguồn (Source Code):</div>
                        <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-outline-dark w-100 text-start rounded-3 p-3 transition-all hover-scale d-flex align-items-center">
                            <i class="fa-brands fa-github me-3 fs-3"></i>
                            <div class="text-truncate">
                                <span class="fw-semibold d-block text-truncate" style="font-size: 13.5px;">{{ $khoi->LinkCode }}</span>
                                <span class="text-muted small" style="font-size: 11px;">Truy cập repository Git</span>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tóm tắt AI 3 Nhóm -->
            @php
                $tomTatData = $khoi->tomTat?->TomTatJson ?? ($khoi->TomTatJson ?? null);
            @endphp
            @if($tomTatData)
                <div class="transition-all hover-shadow rounded-4">
                    @include('partials.ai_summary_11_sections', ['pb' => $khoi])
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Các lớp CSS tùy chỉnh hiệu ứng & khoảng cách --}}
<style>
    .transition-all {
        transition: all 0.25s ease-in-out;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }
    .hover-shadow-sm:hover {
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.04) !important;
        background-color: #ffffff !important;
    }
    .hover-scale:hover {
        transform: translateY(-1px);
    }
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>
@endsection