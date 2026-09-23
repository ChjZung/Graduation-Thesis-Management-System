@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Biểu Mẫu Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">Chỉnh Sửa Biểu Mẫu: {{ $bieuMau->MaBieuMau }}</h4>
            <p class="text-muted small mb-0">Cập nhật tên biểu mẫu, kế hoạch áp dụng hoặc thay đổi file mẫu hướng dẫn.</p>
        </div>
        <a href="{{ route('admin.bieumau.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại Danh Sách
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.bieumau.update', $bieuMau->MaBieuMau) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Tên Biểu Mẫu <span class="text-danger">*</span></label>
                    <input type="text" name="TenBieuMau" class="form-control" value="{{ old('TenBieuMau', $bieuMau->TenBieuMau) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Kế Hoạch Khóa Luận Áp Dụng <span class="text-danger">*</span></label>
                    <select name="MakeHoach" class="form-select" required>
                        @foreach($keHoachs as $kh)
                            <option value="{{ $kh->MakeHoach }}" {{ old('MakeHoach', $bieuMau->MakeHoach) == $kh->MakeHoach ? 'selected' : '' }}>
                                {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Thay Đổi File Đính Kèm (.doc, .docx, .pdf, .xls, .xlsx, .zip)</label>
                    <input type="file" name="File" class="form-control">
                    <div class="form-text small">File hiện tại: <code>{{ $bieuMau->DuongDanFile }}</code></div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Đường Dẫn File (URL / Link ngoài)</label>
                    <input type="text" name="DuongDanFile" class="form-control" value="{{ old('DuongDanFile', $bieuMau->DuongDanFile) }}">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.bieumau.index') }}" class="btn btn-light rounded-pill px-4">Hủy</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật Biểu Mẫu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
