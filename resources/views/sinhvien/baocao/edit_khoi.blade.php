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
        <a href="{{ route('sinhvien.baocao.show_khoi', $khoi->MaKhoi) }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 13px;">
            <i class="fa-solid fa-arrow-left me-1"></i>Hủy chỉnh sửa
        </a>
    </div>

    @if($khoi->NhanXetGV)
    <div class="alert alert-warning border mb-4">
        <strong><i class="fa-solid fa-comment-dots me-1"></i>Yêu cầu chỉnh sửa từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }}:</strong>
        <p class="mb-0 mt-1" style="white-space: pre-line;">{{ $khoi->NhanXetGV }}</p>
    </div>
    @endif

    <div class="card shadow-sm border">
        <div class="card-body p-4">
            <form action="{{ route('sinhvien.baocao.update_khoi', $khoi->MaKhoi) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Tiêu đề báo cáo nhỏ <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TieuDe" class="form-control" value="{{ old('TieuDe', $khoi->TieuDe) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            % Tiến độ hoàn thành:
                        </label>
                        <div class="input-group">
                            <input type="number" name="PhanTramHoanThanh" class="form-control text-center fw-bold" 
                                   min="0" max="100" value="{{ old('PhanTramHoanThanh', $khoi->PhanTramHoanThanh ?? 50) }}">
                            <span class="input-group-text bg-light fw-bold">%</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            1. Kết quả đạt được &amp; Công việc đã hoàn thành <span class="text-danger">*</span>
                        </label>
                        <textarea name="KetQuaDatDuoc" rows="4" class="form-control" required>{{ old('KetQuaDatDuoc', $khoi->KetQuaDatDuoc ?: $khoi->NoiDungBaoCao) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            2. Khó khăn &amp; Rào cản gặp phải:
                        </label>
                        <textarea name="KhoKhan" rows="3" class="form-control">{{ old('KhoKhan', $khoi->KhoKhan) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            3. Hướng giải quyết đề xuất:
                        </label>
                        <textarea name="HuongGiaiQuyet" rows="3" class="form-control">{{ old('HuongGiaiQuyet', $khoi->HuongGiaiQuyet) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            4. Kế hoạch giai đoạn tiếp theo:
                        </label>
                        <textarea name="KeHoachTiepTheo" rows="3" class="form-control">{{ old('KeHoachTiepTheo', $khoi->KeHoachTiepTheo) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                            Tệp đính kèm (PDF):
                        </label>
                        <input type="file" name="FileBaoCao" class="form-control" accept=".pdf">
                        <div class="form-text text-muted" style="font-size: 12px;">
                            @if($khoi->TenFile)
                                <span class="text-success"><i class="fa-solid fa-file-pdf me-1"></i>Đang có: {{ $khoi->TenFile }}</span> (Chọn file mới nếu muốn thay thế).
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
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>Cập Nhật &amp; Nộp Lại Báo Cáo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
