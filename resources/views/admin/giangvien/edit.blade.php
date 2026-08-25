@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Giảng Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-user-pen text-primary me-2"></i>Chỉnh Sửa Giảng Viên: {{ $giangvien->HoTen }}
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('giangvien.update', $giangvien->MaGV) }}">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mã GV</label>
                            <input type="text" class="form-control bg-light" value="{{ $giangvien->MaGV }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên Đăng Nhập</label>
                            <input type="text" name="TenDangNhap" class="form-control" value="{{ old('TenDangNhap', $giangvien->taiKhoan->TenDangNhap ?? '') }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ Và Tên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen', $giangvien->HoTen) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Học Vị <span class="text-danger">*</span></label>
                            <select name="HocVi" class="form-select" required>
                                @foreach(['Thạc sĩ', 'Tiến sĩ', 'Phó Giáo sư', 'Giáo sư', 'Kỹ sư', 'Cử nhân'] as $hv)
                                    <option value="{{ $hv }}" {{ old('HocVi', $giangvien->HocVi) == $hv ? 'selected' : '' }}>{{ $hv }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bộ Môn Trực Thuộc <span class="text-danger">*</span></label>
                            <select name="MaBoMon" class="form-select" required>
                                <option value="">-- Chọn Bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon', $giangvien->MaBoMon) == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control" value="{{ old('Email', $giangvien->Email) }}" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Số Điện Thoại</label>
                        <input type="text" name="SoDienThoai" class="form-control" value="{{ old('SoDienThoai', $giangvien->SoDienThoai) }}">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('giangvien.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection