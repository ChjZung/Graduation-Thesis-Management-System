@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Giảng Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-pen-to-square text-white me-1"></i> Chỉnh Sửa Thông Tin Giảng Viên: {{ $giangvien->HoTen }}
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('giangvien.update', $giangvien->MaGV) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Giảng Viên (MaGV)</label>
                            <input type="text" class="form-control form-control-huit bg-light" value="{{ $giangvien->MaGV }}" readonly>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Họ Và Tên Giảng Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control form-control-huit" value="{{ old('HoTen', $giangvien->HoTen) }}" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Học Vị <span class="text-danger">*</span></label>
                            <select name="HocVi" class="form-select form-select-huit" required>
                                @foreach(['Thạc sĩ', 'Tiến sĩ', 'Phó Giáo sư', 'Giáo sư', 'Kỹ sư', 'Cử nhân'] as $hv)
                                    <option value="{{ $hv }}" {{ old('HocVi', $giangvien->HocVi) == $hv ? 'selected' : '' }}>{{ $hv }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Bộ Môn Trực Thuộc <span class="text-danger">*</span></label>
                            <select name="MaBoMon" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon', $giangvien->MaBoMon) == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? 'Khoa CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control form-control-huit" value="{{ old('Email', $giangvien->Email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control form-control-huit" value="{{ old('SoDienThoai', $giangvien->SoDienThoai) }}">
                        </div>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Cập Nhật Giảng Viên
                        </button>
                        <a href="{{ route('giangvien.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection