@extends('layouts.sinhvien')

@section('page_title', 'Chỉnh Sửa Báo Cáo Nhỏ - ' . $khoi->TieuDe)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none text-muted">Báo Cáo Tiến Độ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.show_khoi', $khoi->MaKhoi) }}" class="text-decoration-none text-muted">Lần nộp #{{ $khoi->LanNop }}</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Báo Cáo Nhỏ (Lần nộp #{{ $khoi->LanNop }})
            </h4>
        </div>
        <a href="{{ route('sinhvien.baocao.show_khoi', $khoi->MaKhoi) }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
            <i class="fa-solid fa-arrow-left me-2"></i>Hủy chỉnh sửa
        </a>
    </div>

    @if($khoi->NhanXetGV)
    <div class="alert alert-warning border mb-4">
        <strong><i class="fa-solid fa-comment-dots me-2"></i>Yêu cầu chỉnh sửa từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }}:</strong>
        <p class="mb-0 mt-1" style="white-space: pre-line;">{{ $khoi->NhanXetGV }}</p>
    </div>
    @endif

    <div class="card shadow-sm border">
        <div class="card-body p-4">
            <form action="{{ route('sinhvien.baocao.update_khoi', $khoi->MaKhoi) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Tiêu đề báo cáo nhỏ <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TieuDe" class="form-control" value="{{ old('TieuDe', $khoi->TieuDe) }}" required>
                        <div class="form-text text-muted mt-1" style="font-size: 12px;">Tỷ lệ hoàn thành do hệ thống AI tự động phân tích và thẩm định theo nội dung báo cáo.</div>
                    </div>

                    <div class="col-12">
                        @include('partials.rich_text_editor', [
                            'name' => 'KetQuaDatDuoc',
                            'id' => 'KetQuaDatDuoc',
                            'value' => old('KetQuaDatDuoc', $khoi->KetQuaDatDuoc ?: $khoi->NoiDungBaoCao),
                            'label' => '1. Kết quả đạt được & Công việc đã hoàn thành',
                            'required' => true,
                            'minHeight' => '130px',
                            'compact' => true,
                            'placeholder' => 'Mô tả cụ thể các module, tính năng, hoặc tài liệu đã hoàn thiện (kèm số trang dẫn chứng nếu có)...'
                        ])
                    </div>

                    <div class="col-md-6">
                        @include('partials.rich_text_editor', [
                            'name' => 'KhoKhan',
                            'id' => 'KhoKhan',
                            'value' => old('KhoKhan', $khoi->KhoKhan),
                            'label' => '2. Khó khăn & Rào cản gặp phải',
                            'minHeight' => '110px',
                            'compact' => true,
                            'placeholder' => 'Mô tả các vấn đề kỹ thuật, tích hợp hoặc dữ liệu gặp trở ngại...'
                        ])
                    </div>
                    <div class="col-md-6">
                        @include('partials.rich_text_editor', [
                            'name' => 'HuongGiaiQuyet',
                            'id' => 'HuongGiaiQuyet',
                            'value' => old('HuongGiaiQuyet', $khoi->HuongGiaiQuyet),
                            'label' => '3. Hướng giải quyết đề xuất',
                            'minHeight' => '110px',
                            'compact' => true,
                            'placeholder' => 'Phương án khắc phục, tìm giải pháp thay thế hoặc tham khảo ý kiến GVHD...'
                        ])
                    </div>

                    <div class="col-12">
                        @include('partials.rich_text_editor', [
                            'name' => 'KeHoachTiepTheo',
                            'id' => 'KeHoachTiepTheo',
                            'value' => old('KeHoachTiepTheo', $khoi->KeHoachTiepTheo),
                            'label' => '4. Kế hoạch giai đoạn tiếp theo',
                            'minHeight' => '110px',
                            'compact' => true,
                            'placeholder' => 'Các công việc cụ thể nhóm dự kiến triển khai trong tuần tới / đợt tiếp theo...'
                        ])
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Tệp đính kèm (PDF hoặc DOCX):
                        </label>
                        <input type="file" name="FileBaoCao" class="form-control" accept=".pdf,.docx">
                        <div class="form-text text-muted" style="font-size: 12px;">
                            @if($khoi->TenFile)
                                <span class="text-primary"><i class="fa-solid fa-file-pdf me-2"></i>Đang có: {{ $khoi->TenFile }}</span> (Chọn file mới nếu muốn thay thế).
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Đường dẫn Source Code (GitHub / GitLab):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-brands fa-github"></i></span>
                            <input type="url" name="LinkCode" class="form-control" value="{{ old('LinkCode', $khoi->LinkCode) }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                    <a href="{{ route('sinhvien.baocao.show_khoi', $khoi->MaKhoi) }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs d-inline-flex align-items-center">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Cập Nhật &amp; Nộp Lại Báo Cáo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
