@extends('layouts.admin')

@section('page_title', 'Thêm Mới Giảng Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-user-plus text-primary me-2"></i>Thêm Mới Giảng Viên
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('giangvien.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mã GV / Tên Đăng Nhập <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control" value="{{ old('TenDangNhap') }}" required placeholder="VD: gv11, gv_nguyenvana...">
                            <div class="form-text small">Mật khẩu khởi tạo mặc định là <code>123456</code>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ Và Tên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen') }}" required placeholder="VD: Nguyễn Văn A">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Học Vị <span class="text-danger">*</span></label>
                            <select name="HocVi" class="form-select" required>
                                <option value="Thạc sĩ" {{ old('HocVi') == 'Thạc sĩ' ? 'selected' : '' }}>Thạc sĩ</option>
                                <option value="Tiến sĩ" {{ old('HocVi') == 'Tiến sĩ' ? 'selected' : '' }}>Tiến sĩ</option>
                                <option value="Phó Giáo sư" {{ old('HocVi') == 'Phó Giáo sư' ? 'selected' : '' }}>Phó Giáo sư</option>
                                <option value="Giáo sư" {{ old('HocVi') == 'Giáo sư' ? 'selected' : '' }}>Giáo sư</option>
                                <option value="Kỹ sư" {{ old('HocVi') == 'Kỹ sư' ? 'selected' : '' }}>Kỹ sư</option>
                                <option value="Cử nhân" {{ old('HocVi') == 'Cử nhân' ? 'selected' : '' }}>Cử nhân</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bộ Môn Trực Thuộc <span class="text-danger">*</span></label>
                            <select name="MaBoMon" class="form-select" required>
                                <option value="">-- Chọn Bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control" value="{{ old('Email') }}" required placeholder="email@huit.edu.vn">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control" value="{{ old('SoDienThoai') }}" placeholder="VD: 0912345678">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('giangvien.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Giảng Viên</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection