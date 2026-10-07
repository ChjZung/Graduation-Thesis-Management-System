@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Học Phần')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Học Phần</h4>
            <p class="text-muted small mb-0">Cập nhật thông tin chi tiết học phần: <strong>{{ $hocphan->TenHocPhan }}</strong> ({{ $hocphan->MaHocPhan }})</p>
        </div>
        <a href="{{ route('hocphan.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark"><i class="fa-solid fa-file-pen me-2 text-primary"></i> Thông Tin Học Phần</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('hocphan.update', $hocphan->MaHocPhan) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã Học Phần</label>
                        <input type="text" class="form-control bg-light" value="{{ $hocphan->MaHocPhan }}" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold text-danger">Tên Học Phần *</label>
                        <input type="text" name="TenHocPhan" class="form-control" value="{{ old('TenHocPhan', $hocphan->TenHocPhan) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-danger">Số Tín Chỉ *</label>
                        <input type="number" name="SoTinChi" class="form-control" value="{{ old('SoTinChi', $hocphan->SoTinChi) }}" min="1" max="30" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Khoa Trực Thuộc</label>
                        <select name="MaKhoa" class="form-select">
                            @foreach($khoas as $k)
                                <option value="{{ $k->MaKhoa }}" {{ old('MaKhoa', $hocphan->MaKhoa) == $k->MaKhoa ? 'selected' : '' }}>{{ $k->TenKhoa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bộ Môn Phụ Trách</label>
                        <select name="MaBoMon" class="form-select">
                            <option value="">-- Học phần dùng chung (Không thuộc riêng BM) --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon', $hocphan->MaBoMon) == $bm->MaBoMon ? 'selected' : '' }}>{{ $bm->TenBoMon }} ({{ $bm->MaBoMon }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">Để trống nếu là học phần dùng chung cấp Khoa/Trường.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Loại Học Phần</label>
                        <select name="LoaiHocPhan" class="form-select">
                            <option value="Chuyên ngành" {{ old('LoaiHocPhan', $hocphan->LoaiHocPhan) === 'Chuyên ngành' ? 'selected' : '' }}>Chuyên ngành</option>
                            <option value="Khóa luận" {{ old('LoaiHocPhan', $hocphan->LoaiHocPhan) === 'Khóa luận' ? 'selected' : '' }}>Khóa luận tốt nghiệp</option>
                            <option value="Đồ án" {{ old('LoaiHocPhan', $hocphan->LoaiHocPhan) === 'Đồ án' ? 'selected' : '' }}>Đồ án tốt nghiệp / Đồ án chuyên ngành</option>
                            <option value="Dùng chung" {{ old('LoaiHocPhan', $hocphan->LoaiHocPhan) === 'Dùng chung' ? 'selected' : '' }}>Dùng chung cấp Khoa/Trường</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trạng Thái</label>
                        <select name="TrangThai" class="form-select">
                            <option value="Đang áp dụng" {{ old('TrangThai', $hocphan->TrangThai) === 'Đang áp dụng' ? 'selected' : '' }}>Đang áp dụng</option>
                            <option value="Tạm ngưng" {{ old('TrangThai', $hocphan->TrangThai) === 'Tạm ngưng' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Mô Tả / Ghi Chú</label>
                        <textarea name="MoTa" rows="4" class="form-control">{{ old('MoTa', $hocphan->MoTa) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Cập Nhật Học Phần
                    </button>
                    <a href="{{ route('hocphan.index') }}" class="btn btn-secondary rounded-pill px-4">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
