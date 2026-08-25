@extends('layouts.admin')

@section('page_title', 'Thêm Mới Sinh Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-user-plus text-primary me-2"></i>Thêm Mới Sinh Viên
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('sinhvien.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">MSSV / Tên Đăng Nhập <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control" value="{{ old('TenDangNhap') }}" required placeholder="VD: sv51, 2001200123...">
                            <div class="form-text small">Mật khẩu khởi tạo mặc định là <code>123456</code>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ Và Tên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen') }}" required placeholder="VD: Nguyễn Văn B">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lớp Học <span class="text-danger">*</span></label>
                            <select name="MaLop" class="form-select" required>
                                <option value="">-- Chọn Lớp Học --</option>
                                @foreach($lops as $l)
                                    <option value="{{ $l->MaLop }}" {{ old('MaLop') == $l->MaLop ? 'selected' : '' }}>
                                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control" value="{{ old('Email') }}" required placeholder="sinhvien@huit.edu.vn">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Số Điện Thoại</label>
                        <input type="text" name="SoDienThoai" class="form-control" value="{{ old('SoDienThoai') }}" placeholder="VD: 0912345678">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sinhvien.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Sinh Viên</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection