@extends('layouts.admin')

@section('page_title', 'Cấp Tài Khoản Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa-solid fa-user-plus text-primary me-2"></i>Cấp Tài Khoản &amp; Phân Vai Trò Mới
                </div>
                <a href="{{ route('admin.taikhoan.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger p-3 rounded-3 mb-4">
                        <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.taikhoan.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên Đăng Nhập (Username) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control" value="{{ old('TenDangNhap') }}" required placeholder="VD: gv_cntt01, canbogiaovu, 2001230107...">
                            <div class="form-text small">Tên đăng nhập duy nhất dùng đăng nhập vào hệ thống.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vai Trò Người Dùng <span class="text-danger">*</span></label>
                            <select name="MaVaiTro" id="selectVaiTro" class="form-select" required>
                                <option value="">-- Chọn vai trò --</option>
                                @foreach($vaiTros as $vt)
                                    <option value="{{ $vt->MaVaiTro }}" {{ old('MaVaiTro') == $vt->MaVaiTro ? 'selected' : '' }}>
                                        {{ $vt->TenVaiTro }} ({{ $vt->MaVaiTro }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ Và Tên Chủ Tài Khoản <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen') }}" required placeholder="VD: Nguyễn Văn A">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Liên Hệ</label>
                            <input type="email" name="Email" class="form-control" value="{{ old('Email') }}" placeholder="email@huit.edu.vn">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control" value="{{ old('SoDienThoai') }}" placeholder="0901234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mật Khẩu Ban Đầu</label>
                            <input type="password" name="MatKhau" class="form-control" placeholder="Để trống để lấy mặc định: 123456">
                            <div class="form-text small">Tài khoản mới sẽ bắt buộc đổi mật khẩu trong lần đăng nhập đầu tiên (BR02).</div>
                        </div>
                    </div>

                    <div class="mb-4" id="khoaSection">
                        <label class="form-label fw-bold">Khoa Quản Lý <span class="text-muted small">(Dành riêng cho tài khoản Giáo vụ)</span></label>
                        <select name="MaKhoa" class="form-select">
                            <option value="">-- Chọn khoa trực thuộc (tùy chọn) --</option>
                            @foreach($khoas as $k)
                                <option value="{{ $k->MaKhoa }}" {{ old('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                                    {{ $k->TenKhoa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.taikhoan.index') }}" class="btn btn-light border px-4 rounded-pill">Quay Lại</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                            <i class="fa-solid fa-save me-1"></i> Tạo &amp; Kích Hoạt Tài Khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
