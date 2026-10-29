@extends($layout)
@section('title', 'Đổi Mật Khẩu')
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
        font-size: 0.85rem;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 3px;
    }
    .checklist-item.valid {
        color: #16A34A;
        font-weight: 600;
    }
    .strength-meter {
        height: 5px;
        background: #E2E8F0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 6px;
        margin-bottom: 8px;
    }
    .strength-bar {
        height: 100%;
        width: 0%;
        transition: width .3s, background-color .3s;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 text-primary-custom fw-bold"><i class="fa-solid fa-key me-2"></i>Đổi Mật Khẩu</h4>
    </div>
    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-semibold d-inline-flex align-items-center">
        <i class="fa-solid fa-arrow-left me-2"></i> Quay lại hồ sơ
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card card-premium shadow-sm">
            <div class="card-body p-4">


                <form method="POST" action="{{ route('password.change.post') }}" id="formChangePassword">
                    @csrf
                    
                    <!-- MẬT KHẨU HIỆN TẠI -->
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-bold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <div class="input-icon-wrap">
                            <input type="password" class="form-control password-input" name="current_password" id="current_password" required placeholder="Nhập mật khẩu đang dùng...">
                            <button type="button" class="toggle-password" data-target="current_password" aria-label="Hiện mật khẩu" aria-pressed="false" title="Hiện mật khẩu">
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
                    </div>
                    
                    <!-- MẬT KHẨU MỚI -->
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-bold">Mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-icon-wrap">
                            <input type="password" class="form-control password-input" name="new_password" id="new_password" required placeholder="Nhập mật khẩu mới...">
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

                        <!-- STRENGTH BAR -->
                        <div class="strength-meter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted" style="font-size: 0.8rem;">Độ mạnh:</span>
                            <strong class="small" id="strengthText" style="font-size: 0.8rem; color: #64748B;">Chưa nhập</strong>
                        </div>

                        <!-- CHECKLIST -->
                        <div class="p-2 bg-light rounded-2 border mb-2">
                            <div class="checklist-item" id="ruleLength">
                                <i class="fa-regular fa-circle me-2"></i> Ít nhất 8 ký tự
                            </div>
                            <div class="checklist-item" id="ruleUpper">
                                <i class="fa-regular fa-circle me-2"></i> Có chữ in hoa (A-Z)
                            </div>
                            <div class="checklist-item" id="ruleLower">
                                <i class="fa-regular fa-circle me-2"></i> Có chữ thường (a-z)
                            </div>
                            <div class="checklist-item" id="ruleNumber">
                                <i class="fa-regular fa-circle me-2"></i> Có chữ số (0-9)
                            </div>
                        </div>
                    </div>
                    
                    <!-- XÁC NHẬN MẬT KHẨU MỚI -->
                    <div class="mb-4">
                        <label class="form-label text-dark small fw-bold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-icon-wrap">
                            <input type="password" class="form-control password-input" name="new_password_confirmation" id="new_password_confirmation" required placeholder="Nhập lại mật khẩu mới...">
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
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-save me-2"></i>Cập nhật mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Toggle Password Visibility ──
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

    // ── Realtime Checklist & Strength ──
    const pwdInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('new_password_confirmation');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const confirmFeedback = document.getElementById('confirmFeedback');

    const ruleLength = document.getElementById('ruleLength');
    const ruleUpper = document.getElementById('ruleUpper');
    const ruleLower = document.getElementById('ruleLower');
    const ruleNumber = document.getElementById('ruleNumber');

    function checkPassword() {
        const val = pwdInput.value;
        let score = 0;

        const hasLength = val.length >= 8;
        updateRule(ruleLength, hasLength);
        if (hasLength) score += 25;

        const hasUpper = /[A-Z]/.test(val);
        updateRule(ruleUpper, hasUpper);
        if (hasUpper) score += 25;

        const hasLower = /[a-z]/.test(val);
        updateRule(ruleLower, hasLower);
        if (hasLower) score += 25;

        const hasNumber = /[0-9]/.test(val);
        updateRule(ruleNumber, hasNumber);
        if (hasNumber) score += 25;

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
            strengthBar.style.width = '100%';
            strengthBar.style.backgroundColor = '#16A34A';
            strengthText.textContent = 'Tốt';
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
@endsection
