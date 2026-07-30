@extends('layouts.app')

@section('content')
<style>
    nav.navbar { display: none !important; }
    main.py-4 { padding: 0 !important; }
    
    body {
        background: linear-gradient(-45deg, #224abe, #4e73df, #23a6d5, #3f51b5);
        background-size: 400% 400%;
        animation: gradientBG 12s ease infinite;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
    }
    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .reset-container {
        width: 100%;
        max-width: 500px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: 50px 40px;
        text-align: center;
    }
    .reset-icon {
        font-size: 4rem;
        color: #4e73df;
        margin-bottom: 20px;
    }
    .reset-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
    }
    .reset-desc {
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 30px;
    }
    .form-control-custom {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
        background: #f9f9f9;
        font-size: 0.95rem;
        transition: all 0.3s;
    }
    .form-control-custom:focus {
        border-color: #4e73df;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }
    .btn-reset {
        background: #4e73df;
        color: white;
        font-weight: 600;
        padding: 12px;
        border-radius: 10px;
        transition: all 0.3s;
        border: none;
        width: 100%;
        margin-top: 15px;
    }
    .btn-reset:hover {
        background: #224abe;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
    }
    .back-link {
        display: inline-block;
        margin-top: 25px;
        color: #666;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s;
    }
    .back-link:hover {
        color: #4e73df;
    }
</style>

<div class="reset-container">
    <i class="fa-solid fa-unlock-keyhole reset-icon"></i>
    <div class="reset-title">Khôi Phục Mật Khẩu</div>
    <div class="reset-desc">Nhập địa chỉ Email của bạn để nhận đường dẫn đặt lại mật khẩu.</div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="text-start">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-bold text-muted" style="font-size: 0.85rem;">Địa chỉ Email</label>
            <input id="email" type="email" class="form-control form-control-custom @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="ví dụ: sinhvien@gmail.com">
            
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-reset">
            Gửi Link Khôi Phục <i class="fa-solid fa-paper-plane ms-1"></i>
        </button>
    </form>
    
    <a href="{{ route('login') }}" class="back-link">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Đăng nhập
    </a>
</div>
@endsection
