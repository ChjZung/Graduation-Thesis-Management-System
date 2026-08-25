@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Sinh Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-user-pen text-primary me-2"></i>Chỉnh Sửa Sinh Viên: {{ $sinhvien->HoTen }}
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('sinhvien.update', $sinhvien->MaSV) }}">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mã SV</label>
                            <input type="text" class="form-control bg-light" value="{{ $sinhvien->MaSV }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">MSSV / Tên Đăng Nhập</label>
                            <input type="text" name="TenDangNhap" class="form-control" value="{{ old('TenDangNhap', $sinhvien->taiKhoan->TenDangNhap ?? '') }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ Và Tên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen', $sinhvien->HoTen) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lớp Học <span class="text-danger">*</span></label>
                            <select name="MaLop" class="form-select" required>
                                <option value="">-- Chọn Lớp Học --</option>
                                @foreach($lops as $l)
                                    <option value="{{ $l->MaLop }}" {{ old('MaLop', $sinhvien->MaLop) == $l->MaLop ? 'selected' : '' }}>
                                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control" value="{{ old('Email', $sinhvien->Email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control" value="{{ old('SoDienThoai', $sinhvien->SoDienThoai) }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sinhvien.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection