@extends('layouts.app')

@section('content')
<style>
    /* Hide default app navbar */
    nav.navbar {
        display: none !important;
    }

    main.py-4 {
        padding: 0 !important;
    }

    /* =========================
       BODY & TYPOGRAPHY
    ==========================*/
    body {
        font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
        background: linear-gradient(135deg, #003B73 0%, #0072CE 55%, #A2D2FF 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    /* Background Blobs */
    body::before {
        content: "";
        position: fixed;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(0, 114, 206, .3) 0%, transparent 70%);
        top: -150px;
        right: -100px;
        border-radius: 50%;
        animation: blobMove 8s ease-in-out infinite alternate;
        pointer-events: none;
    }

    body::after {
        content: "";
        position: fixed;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(161, 210, 255, .25) 0%, transparent 70%);
        bottom: -150px;
        left: -100px;
        border-radius: 50%;
        animation: blobMove 10s ease-in-out infinite alternate-reverse;
        pointer-events: none;
    }

    @keyframes blobMove {
        from { transform: translate(0, 0) scale(1); }
        to { transform: translate(30px, 20px) scale(1.08); }
    }

    /* =========================
       LOGIN CARD
    ==========================*/
    .login-card {
        width: 100%;
        max-width: 1200px;
        min-height: 700px;
        display: flex;
        overflow: hidden;
        border-radius: 24px;
        background: white;
        box-shadow: 0 30px 80px rgba(0, 40, 120, .35), 0 0 0 1px rgba(255, 255, 255, .08);
        animation: cardIn .5s ease;
        position: relative;
        z-index: 1;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: scale(.95); }
        to { opacity: 1; transform: scale(1); }
    }

    /* =========================
       LEFT PANEL
    ==========================*/
    .login-left {
        width: 52%;
        position: relative;
        background: #003B73;
        overflow: hidden;
    }

    .bg-photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .55;
    }

    .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(0, 50, 110, .88), rgba(0, 114, 206, .55));
    }

    .left-inner {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 50px 40px;
        color: white;
        text-align: center;
    }

    .school-logo {
        width: 120px;
        height: 120px;
        border-radius: 18px;
        border: 4px solid rgba(255, 255, 255, .35);
        margin-bottom: 24px;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
    }

    .login-left h5 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 16px;
        line-height: 1.3;
    }

    .tagline {
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 440px;
        margin-bottom: 28px;
        color: rgba(255, 255, 255, .9);
    }

    .info-box {
        background: rgba(255, 255, 255, .15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 14px;
        padding: 18px 25px;
        font-size: 0.95rem;
        line-height: 1.9;
        text-align: left;
    }

    .info-box i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
        color: #A2D2FF;
    }

    /* =========================
       RIGHT PANEL
    ==========================*/
    .login-right {
        width: 48%;
        padding: 45px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: white;
    }

    .brand-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .brand-row img {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
    }

    .brand-text {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0066B2;
        line-height: 1.35;
        white-space: nowrap;
    }

    .brand-text span {
        font-size: 0.72rem !important;
        font-weight: 400;
        color: #5B7A9D;
        display: block;
    }

    .login-right h1 {
        font-size: 2.2rem;
        margin-bottom: 8px;
        color: #003B73;
        font-weight: 700;
    }

    .welcome-sub {
        font-size: 1rem;
        margin-bottom: 30px;
        color: #5B7A9D;
    }

    /* =========================
       FORM & INPUTS
    ==========================*/
    .field-group {
        margin-bottom: 22px;
    }

    .field-group label {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
        color: #0058A5;
    }

    .input-icon-wrap {
        position: relative;
        width: 100%;
    }

    .field-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.1rem;
        color: #0072CE;
        pointer-events: none;
    }

    .input-icon-wrap input {
        width: 100%;
        height: 54px;
        border-radius: 12px;
        border: 2px solid #BDE0FE;
        background: #F3F8FC;
        padding-left: 50px;
        padding-right: 50px;
        font-size: 1rem;
        transition: .3s;
        box-sizing: border-box;
    }

    .input-icon-wrap input:focus {
        border-color: #0072CE;
        background: white;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 114, 206, .25);
    }

    /* Password Toggle Button */
    .btn-toggle-pwd {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #0072CE;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color .2s;
    }

    .btn-toggle-pwd:hover {
        color: #004A8F;
    }

    /* META */
    .form-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        font-size: 0.95rem;
    }

    .form-check-label {
        color: #334155;
        cursor: pointer;
    }

    .link-forgot {
        color: #0072CE;
        text-decoration: none;
        font-weight: 600;
        transition: color .2s;
    }

    .link-forgot:hover {
        color: #004A8F;
        text-decoration: underline;
    }

    /* BUTTON */
    .btn-login-huit {
        width: 100%;
        height: 56px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #0072CE, #004A8F);
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        transition: .3s;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0, 114, 206, .3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-login-huit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0, 114, 206, .38);
    }

    /* ALERTS */
    .alert-locked {
        display: flex;
        align-items: center;
        gap: 15px;
        background: #FFF0F0;
        border: 2px solid #FF4D4D;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 22px;
        animation: pulseAlert 2s infinite ease-in-out;
    }

    .alert-locked-icon {
        width: 40px;
        height: 40px;
        background: #FF4D4D;
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .alert-locked-text {
        font-size: 0.92rem;
        color: #D32F2F;
        line-height: 1.4;
    }

    .alert-error {
        background: #FFF5F5;
        border-left: 4px solid #E53E3E;
        color: #C53030;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.92rem;
    }

    .alert-warning-box {
        background: #FEFCBF;
        border-left: 4px solid #D69E2E;
        color: #744210;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.92rem;
    }

    @keyframes pulseAlert {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(255, 77, 77, 0); }
    }

    /* RESPONSIVE */
    @media(max-width: 992px) {
        .login-card {
            flex-direction: column;
            min-height: auto;
        }
        .login-left, .login-right {
            width: 100%;
        }
        .login-left {
            padding: 35px 20px;
        }
        .login-right {
            padding: 40px 30px;
        }
    }
</style>

<div class="login-card">

    {{-- ── LEFT: School Photo & Branding ── --}}
    <div class="login-left">
        <img src="{{ asset('images/hinhcongtruong.jpg') }}" alt="Cổng Trường HUIT" class="bg-photo">
        <div class="overlay"></div>
        <div class="left-inner">
            <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="school-logo">
            <h5>Trường Đại Học Công Thương TP. Hồ Chí Minh</h5>
            <p class="tagline">
                Hệ thống quản lý công tác khóa luận tốt nghiệp trực tuyến dành riêng cho Giảng Viên và Sinh Viên.
            </p>
            <div class="info-box">
                <div><i class="fa-solid fa-location-dot"></i> 140 Lê Trọng Tấn, Q. Tân Phú, TP.HCM</div>
                <div><i class="fa-solid fa-phone"></i> (028) 38 163 318</div>
                <div><i class="fa-solid fa-envelope"></i> info@huit.edu.vn</div>
                <div><i class="fa-solid fa-globe"></i> www.huit.edu.vn</div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Login Form ── --}}
    <div class="login-right">
        <div class="brand-row">
            <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT">
            <div class="brand-text">
                HỆ THỐNG QUẢN LÝ CÔNG TÁC KHÓA LUẬN TỐT NGHIỆP<br>
                <span>Ho Chi Minh City University of Industry and Trade</span>
            </div>
        </div>

        <h1>Đăng Nhập</h1>
        <p class="welcome-sub">Vui lòng nhập thông tin tài khoản của bạn để tiếp tục</p>

        {{-- ── ALERTS ── --}}
        @if ($errors->has('TenDangNhap') && str_contains($errors->first('TenDangNhap'), 'bị khóa'))
        <div class="alert-locked">
            <div class="alert-locked-icon"><i class="fa-solid fa-lock"></i></div>
            <div class="alert-locked-text">
                <strong>Tài khoản bị khóa!</strong><br>
                {{ $errors->first('TenDangNhap') }}
            </div>
        </div>
        @elseif ($errors->has('TenDangNhap'))
        <div class="alert-error">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            {{ $errors->first('TenDangNhap') }}
        </div>
        @endif

        @if ($errors->has('password'))
        <div class="alert-error">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            {{ $errors->first('password') }}
        </div>
        @endif

        @if (session('warning'))
        <div class="alert-warning-box">
            <i class="fa-solid fa-exclamation-circle me-2"></i>
            {{ session('warning') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Tên đăng nhập --}}
            <div class="field-group">
                <label for="TenDangNhap">
                    <i class="fa-solid fa-id-badge me-1"></i>Tên Đăng Nhập
                </label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-user field-icon"></i>
                    <input
                        id="TenDangNhap"
                        type="text"
                        name="TenDangNhap"
                        value="{{ old('TenDangNhap') }}"
                        placeholder="Nhập tên đăng nhập..."
                        required
                        autocomplete="username"
                        autofocus
                        class="{{ $errors->has('TenDangNhap') ? 'is-invalid' : '' }}"
                    >
                </div>
                @error('TenDangNhap')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Mật khẩu --}}
            <div class="field-group">
                <label for="password">
                    <i class="fa-solid fa-lock me-1"></i>Mật Khẩu
                </label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-key field-icon"></i>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Nhập mật khẩu..."
                        required
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" id="btnToggleLoginPassword" class="btn-toggle-pwd" title="Hiện/Ẩn mật khẩu">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Remember + Forgot --}}
            <div class="form-meta">
                <div class="form-check d-flex align-items-center gap-2">
                    <input class="form-check-input mt-0" type="checkbox" name="remember" id="remember"
                           {{ old('remember') ? 'checked' : '' }}
                           style="border-color: #BDE0FE; accent-color: #0072CE; width: 18px; height: 18px;">
                    <label class="form-check-label mb-0" for="remember">Ghi nhớ đăng nhập</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="link-forgot">Quên mật khẩu?</a>
                @endif
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn-login-huit" id="btn-submit-login">
                <i class="fa-solid fa-right-to-bracket"></i>
                ĐĂNG NHẬP HỆ THỐNG
            </button>
        </form>

        <p style="font-size: 0.8rem; color: #94B4CC; text-align: center; margin-top: 25px;">
            <i class="fa-solid fa-shield-halved me-1" style="color: #0072CE;"></i>
            Hệ thống được bảo mật theo tiêu chuẩn nhà trường &bull; HUIT &copy; {{ date('Y') }}
        </p>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('btnToggleLoginPassword');
    const pwdInput = document.getElementById('password');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
});
</script>
@endsection
