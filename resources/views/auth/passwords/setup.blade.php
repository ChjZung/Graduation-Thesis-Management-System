@extends($layout)

@section('page_title', 'Thiết Lập Mật Khẩu Cá Nhân')

@section('content')
<style>
    .input-icon-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        overflow: visible;
    }
    .input-icon-wrap input.form-control,
    .input-icon-wrap input {
        width: 100% !important;
        box-sizing: border-box !important;
        padding-right: 48px !important;
    }

    /* Ẩn triệt để icon reveal mật khẩu mặc định của trình duyệt Edge và Internet Explorer */
    .input-icon-wrap input::-ms-reveal,
    .input-icon-wrap input::-ms-clear,
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Ngăn icon validation mặc định đè lên nút con mắt */
    .input-icon-wrap .form-control.is-invalid,
    .input-icon-wrap .form-control.is-valid {
        background-image: none !important;
    }

    .toggle-password {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 0;
        padding: 0;
        cursor: pointer;
        z-index: 2;
        color: #64748B;
        border-radius: 6px;
        transition: color .15s ease, background-color .15s ease;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }
    .toggle-password:hover {
        color: #0072CE;
        background-color: rgba(0, 114, 206, 0.08);
    }
    .toggle-password:focus-visible {
        outline: 2px solid #0072CE;
        outline-offset: 1px;
    }
    .toggle-password .eye-icon {
        width: 18px;
        height: 18px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        pointer-events: none;
        display: inline-block;
        flex-shrink: 0;
    }
    .toggle-password i {
        font-size: 16px;
        line-height: 1;
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    .checklist-item {
        font-size: 0.88rem;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        transition: color .2s;
    }
    .checklist-item.valid {
        color: #16A34A;
        font-weight: 600;
    }
    .checklist-item.valid i {
        color: #16A34A;
    }
    .strength-meter {
        height: 6px;
        background: #E2E8F0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
        margin-bottom: 8px;
    }
    .strength-bar {
        height: 100%;
        width: 0%;
        transition: width .3s, background-color .3s;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 text-primary-custom fw-bold"><i class="fa-solid fa-shield-halved me-2"></i>Thiết Lập Mật Khẩu Cá Nhân</h4>
    </div>
    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold">
        <i class="fa-solid fa-user-lock me-2"></i>Lần Đầu Đăng Nhập
    </span>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <!-- BANNER CHÀO MỪNG -->
        <div class="alert alert-primary border-primary-subtle shadow-sm d-flex align-items-start gap-3 p-3 rounded-3 mb-4">
            <div class="fs-3 text-primary"><i class="fa-solid fa-circle-info"></i></div>
            <div>
                <h6 class="fw-bold mb-1 text-primary-custom">Chào mừng bạn đến với Cổng Quản Lý Khóa Luận HUIT!</h6>
                <div class="small text-secondary">
                    Tài khoản của bạn (<code>{{ $user->TenDangNhap }}</code>) đang sử dụng mật khẩu khởi tạo ban đầu. Vui lòng thiết lập mật khẩu cá nhân mới để kích hoạt tài khoản và tiếp tục sử dụng đầy đủ các tính năng.
                </div>
            </div>
        </div>

        <div class="card card-premium shadow-sm">
            <div class="card-header-premium">
                <i class="fa-solid fa-key text-primary me-2"></i>Nhập Mật Khẩu Cá Nhân Mới
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show p-3 rounded-3 mb-4" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('password.setup.post') }}" id="formSetupPassword">
                    @csrf

                    <!-- MẬT KHẨU MỚI -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label text-dark small fw-bold">
                            Mật khẩu cá nhân mới <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <input type="password" id="new_password" name="new_password" class="form-control password-input" required placeholder="Nhập mật khẩu mới..." autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="new_password" aria-label="Hiện mật khẩu" aria-pressed="false" title="Hiện mật khẩu">
                                <svg class="eye-icon eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed d-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>

                        <!-- THANH ĐO ĐỘ MẠNH -->
                        <div class="strength-meter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="small text-muted" style="font-size: 0.8rem;">Độ an toàn:</span>
                            <strong class="small" id="strengthText" style="font-size: 0.8rem; color: #64748B;">Chưa nhập</strong>
                        </div>

                        <!-- CHECKLIST TIÊU CHÍ BẢO MẬT -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="small fw-bold text-dark mb-2"><i class="fa-solid fa-list-check me-2 text-primary"></i>Quy chuẩn mật khẩu an toàn:</div>
                            <div class="checklist-item" id="ruleLength">
                                <i class="fa-regular fa-circle me-2"></i> Ít nhất 8 ký tự
                            </div>
                            <div class="checklist-item" id="ruleUpper">
                                <i class="fa-regular fa-circle me-2"></i> Có ít nhất 1 chữ in hoa (A-Z)
                            </div>
                            <div class="checklist-item" id="ruleLower">
                                <i class="fa-regular fa-circle me-2"></i> Có ít nhất 1 chữ thường (a-z)
                            </div>
                            <div class="checklist-item" id="ruleNumber">
                                <i class="fa-regular fa-circle me-2"></i> Có ít nhất 1 chữ số (0-9)
                            </div>
                            <div class="checklist-item" id="ruleSpecial">
                                <i class="fa-regular fa-circle me-2"></i> Ký tự đặc biệt (!@#$%^&*...) (khuyến khích)
                            </div>
                        </div>
                    </div>

                    <!-- XÁC NHẬN MẬT KHẨU -->
                    <div class="mb-4">
                        <label for="new_password_confirmation" class="form-label text-dark small fw-bold">
                            Xác nhận lại mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control password-input" required placeholder="Nhập lại mật khẩu mới..." autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="new_password_confirmation" aria-label="Hiện mật khẩu" aria-pressed="false" title="Hiện mật khẩu">
                                <svg class="eye-icon eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed d-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <div id="confirmFeedback" class="small mt-1 d-none"></div>
                    </div>

                    <!-- NÚT THỰC HIỆN -->
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center" id="btnSubmitSetup">
                        <i class="fa-solid fa-check-circle me-2"></i>Thiết Lập Mật Khẩu &amp; Bắt Đầu Sử Dụng
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── 1. TOGGLE PASSWORD VISIBILITY ──
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            const isPassword = (input.type === 'password');
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');
            const faIcon = this.querySelector('i');

            if (isPassword) {
                input.type = 'text';
                this.setAttribute('aria-label', 'Ẩn mật khẩu');
                this.setAttribute('title', 'Ẩn mật khẩu');
                this.setAttribute('aria-pressed', 'true');
                if (eyeOpen && eyeClosed) {
                    eyeOpen.classList.add('d-none');
                    eyeClosed.classList.remove('d-none');
                }
                if (faIcon) {
                    faIcon.classList.remove('fa-eye');
                    faIcon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                this.setAttribute('aria-label', 'Hiện mật khẩu');
                this.setAttribute('title', 'Hiện mật khẩu');
                this.setAttribute('aria-pressed', 'false');
                if (eyeOpen && eyeClosed) {
                    eyeClosed.classList.add('d-none');
                    eyeOpen.classList.remove('d-none');
                }
                if (faIcon) {
                    faIcon.classList.remove('fa-eye-slash');
                    faIcon.classList.add('fa-eye');
                }
            }
        });
    });

    // ── 2. REALTIME PASSWORD VALIDATION & STRENGTH METER ──
    const pwdInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('new_password_confirmation');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const confirmFeedback = document.getElementById('confirmFeedback');

    const ruleLength = document.getElementById('ruleLength');
    const ruleUpper = document.getElementById('ruleUpper');
    const ruleLower = document.getElementById('ruleLower');
    const ruleNumber = document.getElementById('ruleNumber');
    const ruleSpecial = document.getElementById('ruleSpecial');

    function checkPassword() {
        const val = pwdInput.value;
        let score = 0;

        // Rule 1: Độ dài >= 8
        const hasLength = val.length >= 8;
        updateRule(ruleLength, hasLength);
        if (hasLength) score += 25;

        // Rule 2: Chữ hoa
        const hasUpper = /[A-Z]/.test(val);
        updateRule(ruleUpper, hasUpper);
        if (hasUpper) score += 25;

        // Rule 3: Chữ thường
        const hasLower = /[a-z]/.test(val);
        updateRule(ruleLower, hasLower);
        if (hasLower) score += 25;

        // Rule 4: Số
        const hasNumber = /[0-9]/.test(val);
        updateRule(ruleNumber, hasNumber);
        if (hasNumber) score += 25;

        // Rule 5: Ký tự đặc biệt (bonus)
        const hasSpecial = /[^A-Za-z0-9]/.test(val);
        updateRule(ruleSpecial, hasSpecial);

        // Update strength bar
        if (val.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.style.backgroundColor = '#E2E8F0';
            strengthText.textContent = 'Chưa nhập';
            strengthText.style.color = '#64748B';
        } else if (score < 50) {
            strengthBar.style.width = '25%';
            strengthBar.style.backgroundColor = '#EF4444';
            strengthText.textContent = 'Rất yếu';
            strengthText.style.color = '#EF4444';
        } else if (score < 75) {
            strengthBar.style.width = '50%';
            strengthBar.style.backgroundColor = '#F59E0B';
            strengthText.textContent = 'Trung bình';
            strengthText.style.color = '#F59E0B';
        } else if (score < 100) {
            strengthBar.style.width = '75%';
            strengthBar.style.backgroundColor = '#3B82F6';
            strengthText.textContent = 'Khá';
            strengthText.style.color = '#3B82F6';
        } else {
            strengthBar.style.width = hasSpecial ? '100%' : '90%';
            strengthBar.style.backgroundColor = '#16A34A';
            strengthText.textContent = hasSpecial ? 'Rất mạnh' : 'Tốt';
            strengthText.style.color = '#16A34A';
        }

        checkMatch();
    }

    function updateRule(el, valid) {
        if (valid) {
            el.classList.add('valid');
            el.querySelector('i').className = 'fa-solid fa-circle-check';
        } else {
            el.classList.remove('valid');
            el.querySelector('i').className = 'fa-regular fa-circle';
        }
    }

    function checkMatch() {
        if (!confirmInput.value) {
            confirmFeedback.classList.add('d-none');
            return;
        }

        confirmFeedback.classList.remove('d-none');
        if (confirmInput.value === pwdInput.value) {
            confirmFeedback.className = 'small mt-1 text-success fw-semibold';
            confirmFeedback.innerHTML = '<i class="fa-solid fa-check me-2"></i>Mật khẩu xác nhận khớp.';
        } else {
            confirmFeedback.className = 'small mt-1 text-danger fw-semibold';
            confirmFeedback.innerHTML = '<i class="fa-solid fa-xmark me-2"></i>Mật khẩu xác nhận chưa khớp.';
        }
    }

    pwdInput.addEventListener('input', checkPassword);
    confirmInput.addEventListener('input', checkMatch);
});
</script>
@endpush
@endsection
