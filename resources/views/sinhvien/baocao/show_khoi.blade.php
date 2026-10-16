@extends('layouts.sinhvien')

@section('page_title', 'Chi Tiết Báo Cáo Nhỏ - ' . $khoi->TieuDe)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Tiêu đề -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none text-muted">Báo Cáo Tiến Độ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none text-muted">Giai Đoạn {{ $khoi->LanBaoCao }}</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Lần nộp #{{ $khoi->LanNop }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-file-lines text-primary me-2"></i>{{ $khoi->TieuDe }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 13px;">
                <i class="fa-solid fa-arrow-left me-1"></i>Về danh sách
            </a>
            @if($khoi->TrangThai !== 'Đạt')
                <a href="{{ route('sinhvien.baocao.edit_khoi', $khoi->MaKhoi) }}" class="btn btn-warning rounded-pill px-3 py-1.5 fw-semibold shadow-xs" style="font-size: 13px;">
                    <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh sửa báo cáo
                </a>
            @endif
        </div>
    </div>

    <!-- Trạng thái & Nhận xét của Giảng viên -->
    <div class="card mb-4 border-0 shadow-xs" style="background: {{ $khoi->TrangThai === 'Đạt' ? 'var(--admin-success-soft)' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'var(--admin-warning-soft)' : 'var(--admin-neutral-soft)') }}; border-left: 5px solid {{ $khoi->TrangThai === 'Đạt' ? 'var(--admin-success)' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'var(--admin-warning-text)' : 'var(--admin-text-muted)') }} !important;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $khoi->TrangThai === 'Đạt' ? 'bg-success' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill px-3 py-1.5">
                        {{ $khoi->TrangThai }}
                    </span>
                    <span class="text-muted" style="font-size: 13px;">
                        <i class="fa-regular fa-clock me-1"></i>Nộp lúc: {{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                    </span>
                    <span class="badge bg-light text-dark border rounded-pill">
                        Tiến độ: {{ $khoi->PhanTramHoanThanh ?? 0 }}%
                    </span>
                </div>
                @if($khoi->NhanXetGV)
                    <span class="text-muted" style="font-size: 12.5px;">
                        Đánh giá bởi: <strong>{{ $khoi->NguoiNhanXet ?? 'GVHD' }}</strong> ({{ \Carbon\Carbon::parse($khoi->NgayNhanXet)->format('d/m/Y') }})
                    </span>
                @endif
            </div>

            @if($khoi->NhanXetGV)
                <div class="p-3 bg-white rounded-2 border mt-2">
                    <strong class="text-dark"><i class="fa-solid fa-comment-dots text-primary me-1"></i>Nhận xét từ Giảng viên hướng dẫn:</strong>
                    <p class="mb-0 mt-1 text-dark" style="white-space: pre-line;">{{ $khoi->NhanXetGV }}</p>
                </div>
            @else
                <div class="text-secondary small mt-1">
                    <i class="fa-solid fa-circle-info me-1 text-muted"></i>Báo cáo đang chờ Giảng viên hướng dẫn xem xét và đánh giá.
                </div>
            @endif
        </div>
    </div>

    <!-- Nội dung báo cáo sinh viên đã trình bày -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm border">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-file-waveform text-primary me-2"></i>Nội Dung Báo Cáo Chi Tiết
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold text-success mb-2" style="font-size: 13.5px;">
                            <i class="fa-solid fa-circle-check me-1"></i>1. Kết quả đạt được &amp; Công việc đã làm:
                        </h6>
                        <div class="p-3 rounded-2 bg-light border text-dark" style="white-space: pre-line; font-size: 13.5px;">{{ $khoi->KetQuaDatDuoc ?: ($khoi->NoiDungBaoCao ?: 'Chưa cập nhật') }}</div>
                    </div>

                    @if($khoi->KhoKhan)
                    <div class="mb-4">
                        <h6 class="fw-bold text-warning-emphasis mb-2" style="font-size: 13.5px;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>2. Khó khăn &amp; Rào cản gặp phải:
                        </h6>
                        <div class="p-3 rounded-2 bg-light border text-dark" style="white-space: pre-line; font-size: 13.5px;">{{ $khoi->KhoKhan }}</div>
                    </div>
                    @endif

                    @if($khoi->HuongGiaiQuyet)
                    <div class="mb-4">
                        <h6 class="fw-bold text-info mb-2" style="font-size: 13.5px;">
                            <i class="fa-solid fa-lightbulb me-1"></i>3. Hướng giải quyết đề xuất:
                        </h6>
                        <div class="p-3 rounded-2 bg-light border text-dark" style="white-space: pre-line; font-size: 13.5px;">{{ $khoi->HuongGiaiQuyet }}</div>
                    </div>
                    @endif

                    @if($khoi->KeHoachTiepTheo)
                    <div class="mb-3">
                        <h6 class="fw-bold text-primary mb-2" style="font-size: 13.5px;">
                            <i class="fa-solid fa-calendar-check me-1"></i>4. Kế hoạch giai đoạn tiếp theo:
                        </h6>
                        <div class="p-3 rounded-2 bg-light border text-dark" style="white-space: pre-line; font-size: 13.5px;">{{ $khoi->KeHoachTiepTheo }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột Phải: Tài liệu đính kèm & Tóm tắt AI -->
        <div class="col-lg-5">
            <!-- Tài liệu & Source Code -->
            <div class="card mb-4 shadow-sm border">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-paperclip text-primary me-2"></i>Minh Chứng Đính Kèm
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Tài liệu báo cáo PDF:</div>
                        @if($khoi->DuongDanFile)
                            <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-outline-danger w-100 text-start rounded-2 py-2">
                                <i class="fa-solid fa-file-pdf me-2 fs-5 text-danger"></i>
                                <span class="fw-semibold">{{ $khoi->TenFile ?: 'Tai_lieu_bao_cao.pdf' }}</span>
                            </a>
                        @else
                            <div class="text-muted fst-italic small">Không đính kèm file PDF.</div>
                        @endif
                    </div>

                    @if($khoi->LinkCode)
                    <div>
                        <div class="text-muted small mb-1">Mã nguồn (Source Code):</div>
                        <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-outline-dark w-100 text-start rounded-2 py-2">
                            <i class="fa-brands fa-github me-2 fs-5"></i>
                            <span class="fw-semibold text-truncate">{{ $khoi->LinkCode }}</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tóm tắt AI Map-Reduce (nếu có) -->
            @php
                $tomTatData = $khoi->tomTat?->TomTatJson ?? ($khoi->TomTatJson ?? null);
            @endphp
            @if($tomTatData)
            <div class="card shadow-sm border border-primary border-opacity-25">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Dữ Liệu Trích Xuất AI
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                        Map-Reduce
                    </span>
                </div>
                <div class="card-body p-3 small">
                    @if(!empty($tomTatData['data']['overall_summary']))
                        <div class="alert alert-light border p-2 mb-3">
                            <strong>Tổng quan:</strong> {{ $tomTatData['data']['overall_summary'] }}
                        </div>
                    @endif

                    @if(!empty($tomTatData['data']['completed_work']))
                        <div class="fw-bold text-success mb-1">Hạng mục đã hoàn thành:</div>
                        <ul class="ps-3 mb-3 text-secondary">
                            @foreach($tomTatData['data']['completed_work'] as $item)
                                <li class="mb-1">
                                    {{ $item['content'] ?? '' }}
                                    @if(!empty($item['source_pages']))
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">[Trang {{ implode(', ', (array)$item['source_pages']) }}]</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($tomTatData['data']['difficulties']))
                        <div class="fw-bold text-warning-emphasis mb-1">Khó khăn ghi nhận:</div>
                        <ul class="ps-3 mb-0 text-secondary">
                            @foreach($tomTatData['data']['difficulties'] as $item)
                                <li class="mb-1">
                                    {{ $item['content'] ?? '' }}
                                    @if(!empty($item['source_pages']))
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">[Trang {{ implode(', ', (array)$item['source_pages']) }}]</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
