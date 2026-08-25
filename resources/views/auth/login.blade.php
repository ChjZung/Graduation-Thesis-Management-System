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
       BODY
    ==========================*/
    body {
        font-family: "Times New Roman", Times, serif;
        background: linear-gradient(135deg, #003B73 0%, #0072CE 55%, #A2D2FF 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    /* Background */
    body::before {
        content: "";
        position: fixed;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle,
                rgba(0, 114, 206, .3) 0%,
                transparent 70%);
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
        background: radial-gradient(circle,
                rgba(161, 210, 255, .25) 0%,
                transparent 70%);
        bottom: -150px;
        left: -100px;
        border-radius: 50%;
        animation: blobMove 10s ease-in-out infinite alternate-reverse;
        pointer-events: none;
    }

    @keyframes blobMove {
        from {
            transform: translate(0, 0) scale(1);
        }

        to {
            transform: translate(30px, 20px) scale(1.08);
        }
    }

    /* =========================
       LOGIN CARD
    ==========================*/

    .login-card {
        width: 100%;
        max-width: 1300px;
        min-height: 760px;
        display: flex;
        overflow: hidden;
        border-radius: 24px;
        background: white;
        box-shadow:
            0 30px 80px rgba(0, 40, 120, .35),
            0 0 0 1px rgba(255, 255, 255, .08);
        animation: cardIn .5s ease;
        position: relative;
        z-index: 1;
    }

    @keyframes cardIn {
        from {
            opacity: 0;
            transform: scale(.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* =========================
       LEFT
    ==========================*/

    .login-left {
        width: 55%;
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
        background: linear-gradient(160deg,
                rgba(0, 50, 110, .88),
                rgba(0, 114, 206, .55));
    }

    .left-inner {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 60px;
        color: white;
        text-align: center;
    }

    .school-logo {
        width: 140px;
        height: 140px;
        border-radius: 20px;
        border: 4px solid rgba(255, 255, 255, .35);
        margin-bottom: 30px;
    }

    .login-left h5 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .tagline {
        font-size: 1.2rem;
        line-height: 1.8;
        max-width: 450px;
        margin-bottom: 30px;
    }

    .info-box {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(8px);
        border-radius: 15px;
        padding: 20px 30px;
        font-size: 1.1rem;
        line-height: 2;
    }

    /* =========================
       RIGHT
    ==========================*/

    .login-right {
        width: 45%;
        padding: 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: white;
    }

    .brand-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 40px;
    }

    .brand-row img {
        width: 65px;
        height: 65px;
    }

    .brand-text {
        font-size: 1.15rem;
        font-weight: bold;
        color: #0066B2;
        line-height: 1.5;
    }

    .brand-text span {
        font-size: .9rem !important;
    }

    .login-right h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        color: #003B73;
        font-weight: bold;
    }

    .welcome-sub {
        font-size: 1.2rem;
        margin-bottom: 40px;
        color: #5B7A9D;
    }

    /* =========================
       FORM
    ==========================*/

    .field-group {
        margin-bottom: 28px;
    }

    .field-group label {
        font-size: 1.15rem;
        font-weight: bold;
        margin-bottom: 10px;
        display: block;
        color: #0058A5;
    }

    .input-icon-wrap {
        position: relative;
    }

    .field-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: #0072CE;
    }

    .input-icon-wrap input {
        width: 100%;
        height: 60px;
        border-radius: 12px;
        border: 2px solid #BDE0FE;
        background: #F3F8FC;
        padding-left: 55px;
        font-size: 1.1rem;
        transition: .3s;
        font-family: "Times New Roman", Times, serif;
    }

    .input-icon-wrap input:focus {
        border-color: #0072CE;
        background: white;
        outline: none;
        box-shadow: 0 0 8px rgba(0,114,206,.25);
    }

    /* =========================
       META
    ==========================*/

    .form-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        font-size: 1rem;
    }

    .form-check-label,
    .link-forgot {
        font-size: 1rem;
    }

    /* =========================
       BUTTON
    ==========================*/

    .btn-login-huit {
        width: 100%;
        height: 62px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg,#0072CE,#004A8F);
        color: white;
        font-size: 1.2rem;
        font-weight: bold;
        transition: .3s;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0,114,206,.3);
    }

    .btn-login-huit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0,114,206,.35);
    }

    .btn-login-huit i {
        margin-right: 8px;
    }

    /* Footer */

    .login-right p:last-child {
        margin-top: 35px;
        text-align: center;
        font-size: .9rem !important;
    }

    /* =========================
       TABLET
    ==========================*/

    @media(max-width:1200px){

        .login-card{

            max-width:1000px;
            min-height:650px;

        }

        .login-right{

            padding:50px;

        }

    }

    /* =========================
       MOBILE
    ==========================*/

    @media(max-width:768px){

        body{

            padding:15px;

        }

        .login-card{

            flex-direction:column;
            min-height:auto;

        }

        .login-left{

            width:100%;
            min-height:300px;

        }

        .login-right{

            width:100%;
            padding:35px;

        }

        .school-logo{

            width:100px;
            height:100px;

        }

        .login-left h5{

            font-size:1.5rem;

        }

        .login-right h1{

            font-size:2rem;

        }

    }

    /* Alerts */
    .alert-locked {
        display: flex;
        align-items: center;
        gap: 15px;
        background: #FFF0F0;
        border: 2px solid #FF4D4D;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 25px;
        animation: pulseAlert 2s infinite ease-in-out;
    }

    .alert-locked-icon {
        width: 45px;
        height: 45px;
        background: #FF4D4D;
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .alert-locked-text {
        font-size: 0.95rem;
        color: #D32F2F;
        line-height: 1.5;
    }

    .alert-error {
        background: #FFF5F5;
        border-left: 4px solid #E53E3E;
        color: #C53030;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }

    .alert-warning-box {
        background: #FEFCBF;
        border-left: 4px solid #D69E2E;
        color: #744210;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }

    @keyframes pulseAlert {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(255, 77, 77, 0); }
    }

</style>

<div class="login-card">

    {{-- ── LEFT: School Gate Photo ── --}}
    <div class="login-left">
        <img src="{{ asset('images/hinhcongtruong.jpg') }}" alt="Cổng Trường HUIT" class="bg-photo">
        <div class="overlay"></div>
        <div class="left-inner">
            <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="school-logo">
            <h5>Trường Đại Học Công Thương TP. Hồ Chí Minh</h5>
            <p class="tagline">
                Hệ thống quản lý đồ án &amp; khóa luận tốt nghiệp trực tuyến dành riêng cho Giảng Viên và Sinh Viên.
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
                HỆ THỐNG QUẢN LÝ ĐỒ ÁN<br>
                <span style="font-weight: 400; color: #5B7A9D; font-size: 0.72rem;">Ho Chi Minh City University of Industry and Trade</span>
            </div>
        </div>

        <h1>Đăng Nhập</h1>
        <p class="welcome-sub">Vui lòng nhập thông tin tài khoản của bạn để tiếp tục</p>

        {{-- ── ALERT KHÓA TÀI KHOẢN ── --}}
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
                    <i class="fa-solid fa-id-badge me-1"></i>Tên Đăng Nhập (Mã SV / Mã GV)
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
                <div class="input-icon-wrap" style="position: relative;">
                    <i class="fa-solid fa-key field-icon"></i>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Nhập mật khẩu..."
                        required
                        autocomplete="current-password"
                        style="padding-right: 50px;"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" id="btnToggleLoginPassword" style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748B; cursor: pointer; font-size: 1.15rem; padding: 0;" title="Hiện/Ẩn mật khẩu">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Remember + Forgot --}}
            <div class="form-meta">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                           {{ old('remember') ? 'checked' : '' }}
                           style="border-color: #BDE0FE; accent-color: #0072CE;">
                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="link-forgot">Quên mật khẩu?</a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login-huit" id="btn-submit-login">
                <i class="fa-solid fa-right-to-bracket"></i>
                ĐĂNG NHẬP HỆ THỐNG
            </button>
        </form>

        <p style="font-size: 0.65rem; color: #94B4CC; text-align: center; margin-top: 24px;">
            <i class="fa-solid fa-shield-halved me-1" style="color: #BDE0FE;"></i>
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

