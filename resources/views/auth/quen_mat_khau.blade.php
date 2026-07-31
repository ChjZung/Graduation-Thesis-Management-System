@extends('layouts.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #004B87 0%, #0072CE 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    .card-reset {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        max-width: 480px;
        width: 100%;
        padding: 40px;
    }
</style>

<div class="card-reset">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" style="width: 80px; height: 80px;" class="mb-2">
        <h4 class="fw-bold text-primary mb-1">Quên Mật Khẩu</h4>
        <p class="text-muted small">Gửi yêu cầu tới Admin để reset mật khẩu về <strong>123456</strong></p>
    </div>

    @if(session('info'))
    <div class="alert alert-info border-info d-flex align-items-center gap-2 rounded-3 mb-3">
        <i class="fa-solid fa-circle-info fa-lg text-info"></i>
        <div>{{ session('info') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-user-tag me-1"></i>Bạn là <span class="text-danger">*</span></label>
            <select name="Role" class="form-select rounded-pill" required>
                <option value="Sinh viên" {{ old('Role') == 'Sinh viên' ? 'selected' : '' }}>Sinh viên</option>
                <option value="Giảng viên" {{ old('Role') == 'Giảng viên' ? 'selected' : '' }}>Giảng viên</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-id-badge me-1"></i>Tên Đăng Nhập (Mã SV / Mã GV) <span class="text-danger">*</span></label>
            <input type="text" name="TenDangNhap" class="form-control rounded-pill @error('TenDangNhap') is-invalid @enderror" value="{{ old('TenDangNhap') }}" placeholder="Nhập mã số của bạn..." required>
            @error('TenDangNhap')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-envelope me-1"></i>Email liên hệ (Không bắt buộc)</label>
            <input type="email" name="Email" class="form-control rounded-pill" value="{{ old('Email') }}" placeholder="Nhập email nếu có...">
        </div>

        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold mb-3 shadow-sm">
            <i class="fa-solid fa-paper-plane me-2"></i>GỬI YÊU CẦU CHO ADMIN
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i>Quay lại trang Đăng nhập</a>
        </div>
    </form>
</div>
@endsection
