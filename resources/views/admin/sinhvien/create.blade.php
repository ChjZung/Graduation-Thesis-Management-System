@extends('layouts.admin')

@section('page_title', 'Thêm Mới Sinh Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-plus text-white me-1"></i> Thêm Mới Sinh Viên Hồ Sơ Khóa Luận
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('sinhvien.store') }}">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Số Sinh Viên (MSSV) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control form-control-huit" value="{{ old('TenDangNhap') }}" placeholder="22110001" required>
                            <div class="form-text small text-muted">MSSV dùng làm tài khoản đăng nhập. Mật khẩu mặc định: <code>123456</code></div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Họ Và Tên Sinh Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control form-control-huit" value="{{ old('HoTen') }}" placeholder="Nguyễn Văn B" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Lớp Sinh Hoạt <span class="text-danger">*</span></label>
                            <select name="MaLop" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Lớp Học --</option>
                                @foreach($lops as $l)
                                    <option value="{{ $l->MaLop }}" {{ old('MaLop') == $l->MaLop ? 'selected' : '' }}>
                                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? 'CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control form-control-huit" value="{{ old('Email') }}" placeholder="sinhvien@huit.edu.vn" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-huit">Số Điện Thoại</label>
                        <input type="text" name="SoDienThoai" class="form-control form-control-huit" value="{{ old('SoDienThoai') }}" placeholder="0912345678">
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Sinh Viên
                        </button>
                        <a href="{{ route('sinhvien.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection