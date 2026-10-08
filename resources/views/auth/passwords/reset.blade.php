@extends('layouts.app')

@section('content')
<style>
    nav.navbar {
        display: none !important;
    }

    main.py-4 {
        padding: 0 !important;
    }

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

    .reset-card {
        width: 100%;
        max-width: 540px;
        border-radius: 24px;
        background: white;
        box-shadow: 0 30px 80px rgba(0, 40, 120, .35), 0 0 0 1px rgba(255, 255, 255, .08);
        padding: 45px 40px;
        animation: cardIn .5s ease;
        position: relative;
        z-index: 1;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: scale(.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .brand-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
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
    }

    .brand-text span {
        font-size: 0.72rem !important;
        font-weight: 400;
        color: #5B7A9D;
        display: block;
    }

    .reset-card h1 {
        font-size: 1.8rem;
        margin-bottom: 8px;
        color: #003B73;
        font-weight: 700;
    }

    .reset-sub {
        font-size: 0.95rem;
        margin-bottom: 25px;
        color: #5B7A9D;
        line-height: 1.5;
    }

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
    }

    .btn-submit-reset {
        width: 100%;
        height: 54px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #0072CE, #004A8F);
        color: white;
        font-size: 1.05rem;
        font-weight: 700;
        transition: .3s;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0, 114, 206, .3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-submit-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0, 114, 206, .38);
    }

    .alert-error-box {
        background: #FFF5F5;
        border-left: 4px solid #E53E3E;
        color: #C53030;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.92rem;
    }

    .pwd-requirements {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 22px;
        font-size: 0.85rem;
        color: #475569;
    }

    .pwd-requirements ul {
        margin: 5px 0 0 0;
        padding-left: 18px;
    }

    .back-login {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0072CE;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        margin-top: 20px;
        transition: color .2s;
    }

    .back-login:hover {
        color: #004A8F;
        text-decoration: underline;
    }
</style>

<div class="reset-card">
    <div class="brand-row">
        <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT">
        <div class="brand-text">
            HỆ THỐNG QUẢN LÝ KHÓA LUẬN TỐT NGHIỆP<br>
            <span>Trường Đại Học Công Thương TP. Hồ Chí Minh</span>
        </div>
    </div>

    <h1>Đặt Lại Mật Khẩu</h1>
    <p class="reset-sub">Vui lòng nhập mật khẩu mới bảo mật cho tài khoản của bạn.</p>

    @if ($errors->any())
        <div class="alert-error-box">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="pwd-requirements">
        <strong><i class="fa-solid fa-shield-halved me-1"></i>Yêu cầu mật khẩu mới:</strong>
        <ul>
            <li>Ít nhất 8 ký tự</li>
            <li>Có chứa ít nhất 1 chữ cái viết hoa (A-Z)</li>
            <li>Có chứa ít nhất 1 chữ cái viết thường (a-z)</li>
            <li>Có chứa ít nhất 1 chữ số (0-9)</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field-group">
            <label for="email">
                <i class="fa-solid fa-envelope me-1"></i>Email tài khoản
            </label>
            <div class="input-icon-wrap">
                <i class="fa-solid fa-at field-icon"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ $email ?? old('email') }}"
                    required
                    readonly
                    style="background-color: #E2E8F0; color: #475569; cursor: not-allowed;"
                >
            </div>
        </div>

        <div class="field-group">
            <label for="password">
                <i class="fa-solid fa-lock me-1"></i>Mật khẩu mới
            </label>
            <div class="input-icon-wrap">
                <i class="fa-solid fa-key field-icon"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Nhập mật khẩu mới..."
                    required
                    autofocus
                >
                <button type="button" id="btnToggleNewPwd" class="btn-toggle-pwd" title="Hiện/Ẩn mật khẩu">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="field-group">
            <label for="password_confirmation">
                <i class="fa-solid fa-lock me-1"></i>Xác nhận mật khẩu mới
            </label>
            <div class="input-icon-wrap">
                <i class="fa-solid fa-check-double field-icon"></i>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Nhập lại mật khẩu mới..."
                    required
                >
                <button type="button" id="btnToggleConfirmPwd" class="btn-toggle-pwd" title="Hiện/Ẩn mật khẩu">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-submit-reset">
            <i class="fa-solid fa-check-circle"></i>
            XÁC NHẬN ĐẶT LẠI MẬT KHẨU
        </button>
    </form>

    <div style="text-align: center;">
        <a href="{{ route('login') }}" class="back-login">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại trang Đăng nhập
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupToggle(btnId, inputId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        if (btn && input) {
            btn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    }
    setupToggle('btnToggleNewPwd', 'password');
    setupToggle('btnToggleConfirmPwd', 'password_confirmation');
});
</script>
@endsection
