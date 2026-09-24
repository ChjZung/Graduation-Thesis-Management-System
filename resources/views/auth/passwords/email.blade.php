@extends('layouts.app')

@section('content')
<style>
    /* Hide default app navbar & padding */
    nav.navbar {
        display: none !important;
    }

    main.py-4 {
        padding: 0 !important;
    }

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

    /* Background blobs */
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

    .reset-card {
        width: 100%;
        max-width: 480px;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 30px 80px rgba(0, 40, 120, .35), 0 0 0 1px rgba(255, 255, 255, .08);
        padding: 45px 40px;
        animation: cardIn .5s ease;
        position: relative;
        z-index: 1;
        text-align: center;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: scale(.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .icon-badge {
        width: 54px;
        height: 54px;
        background: #EBF3FA;
        color: #0072CE;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 114, 206, 0.15);
    }

    .reset-title {
        font-size: 1.8rem;
        font-weight: bold;
        color: #003B73;
        margin-bottom: 8px;
    }

    .reset-sub {
        font-size: 0.95rem;
        color: #5B7A9D;
        margin-bottom: 30px;
        line-height: 1.5;
    }

    .field-group {
        margin-bottom: 22px;
        text-align: left;
    }

    .field-group label {
        font-size: 0.85rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0058A5;
        margin-bottom: 8px;
        display: block;
    }

    .custom-input {
        width: 100%;
        height: 52px;
        border-radius: 12px;
        border: 2px solid #BDE0FE;
        background: #F3F8FC;
        padding-left: 18px;
        padding-right: 18px;
        font-size: 1rem;
        transition: .3s;
        font-family: "Times New Roman", Times, serif;
    }

    .custom-input:focus {
        border-color: #0072CE;
        background: white;
        outline: none;
        box-shadow: 0 0 8px rgba(0, 114, 206, .25);
    }

    .btn-submit-reset {
        width: 100%;
        height: 54px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #0072CE, #004A8F);
        color: white;
        font-size: 1.05rem;
        font-weight: bold;
        transition: .3s;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0, 114, 206, .3);
        margin-top: 10px;
        margin-bottom: 25px;
    }

    .btn-submit-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0, 114, 206, .38);
    }

    .link-back {
        color: #5B7A9D;
        text-decoration: none;
        font-size: 0.95rem;
        transition: .2s;
    }

    .link-back:hover {
        color: #0072CE;
    }

    .link-back strong {
        color: #0072CE;
    }

    .alert-custom-info {
        background: #EBF8FF;
        border-left: 4px solid #3182CE;
        color: #2B6CB0;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.92rem;
        text-align: left;
    }
</style>

<div class="reset-card">
    <div class="icon-badge">
        <i class="fa-solid fa-lock"></i>
    </div>

    <div class="reset-title">Khôi Phục Mật Khẩu</div>
    <div class="reset-sub">Vui lòng điền thông tin để gửi yêu cầu reset mật khẩu tới Admin.</div>

    @if (session('status'))
        <div class="alert-custom-info">
            <i class="fa-solid fa-circle-info me-2"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.send_request') }}">
        @csrf

        {{-- Đối tượng tài khoản --}}
        <div class="field-group">
            <label for="Role">ĐỐI TƯỢNG TÀI KHOẢN</label>
            <select id="Role" name="Role" class="custom-input @error('Role') is-invalid @enderror" required>
                <option value="Sinh viên" {{ old('Role') == 'Sinh viên' ? 'selected' : '' }}>Sinh viên</option>
                <option value="Giảng viên" {{ old('Role') == 'Giảng viên' ? 'selected' : '' }}>Giảng viên</option>
            </select>
            @error('Role')
                <span class="text-danger small mt-1 d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Mã số --}}
        <div class="field-group">
            <label for="TenDangNhap">MÃ SỐ (MSSV / MÃ GV) *</label>
            <input
                id="TenDangNhap"
                type="text"
                name="TenDangNhap"
                value="{{ old('TenDangNhap') }}"
                placeholder="Ví dụ: 2001201234"
                required
                class="custom-input @error('TenDangNhap') is-invalid @enderror"
            >
            @error('TenDangNhap')
                <span class="text-danger small mt-1 d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Họ và tên --}}
        <div class="field-group">
            <label for="HoTen">HỌ VÀ TÊN *</label>
            <input
                id="HoTen"
                type="text"
                name="HoTen"
                value="{{ old('HoTen') }}"
                placeholder="Ví dụ: Nguyễn Văn A"
                required
                class="custom-input @error('HoTen') is-invalid @enderror"
            >
            @error('HoTen')
                <span class="text-danger small mt-1 d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <button type="submit" class="btn-submit-reset">
            GỬI YÊU CẦU CHO ADMIN
        </button>

        <div>
            <a href="{{ route('login') }}" class="link-back">
                Đã nhớ lại mật khẩu? <strong>Quay lại Đăng nhập</strong>
            </a>
        </div>
    </form>
</div>
@endsection
