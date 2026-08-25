@extends($layout)

@section('page_title', 'Thiết Lập Mật Khẩu Cá Nhân')

@section('content')
<style>
    .input-icon-wrap {
        position: relative;
    }
    .input-icon-wrap input {
        padding-right: 45px;
    }
    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748B;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        font-size: 1.1rem;
        z-index: 10;
    }
    .toggle-password:hover {
        color: #0072CE;
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
        <h4 class="mb-1 text-primary-custom fw-bold"><i class="fa-solid fa-shield-halved me-2"></i>Thiết Lập Mật Khẩu Cá Nhân</h4>
        <p class="text-muted small mb-0">Bước kích hoạt và bảo mật tài khoản cho lần đầu đăng nhập hệ thống</p>
    </div>
    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold">
        <i class="fa-solid fa-user-lock me-1"></i>Lần Đầu Đăng Nhập
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
                            <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Nhập mật khẩu mới..." autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="new_password" title="Hiện/Ẩn mật khẩu">
                                <i class="fa-solid fa-eye"></i>
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
                            <div class="small fw-bold text-dark mb-2"><i class="fa-solid fa-list-check me-1 text-primary"></i>Quy chuẩn mật khẩu an toàn:</div>
                            <div class="checklist-item" id="ruleLength">
                                <i class="fa-regular fa-circle"></i> Ít nhất 8 ký tự
                            </div>
                            <div class="checklist-item" id="ruleUpper">
                                <i class="fa-regular fa-circle"></i> Có ít nhất 1 chữ in hoa (A-Z)
                            </div>
                            <div class="checklist-item" id="ruleLower">
                                <i class="fa-regular fa-circle"></i> Có ít nhất 1 chữ thường (a-z)
                            </div>
                            <div class="checklist-item" id="ruleNumber">
                                <i class="fa-regular fa-circle"></i> Có ít nhất 1 chữ số (0-9)
                            </div>
                            <div class="checklist-item" id="ruleSpecial">
                                <i class="fa-regular fa-circle"></i> Ký tự đặc biệt (!@#$%^&*...) (khuyến khích)
                            </div>
                        </div>
                    </div>

                    <!-- XÁC NHẬN MẬT KHẨU -->
                    <div class="mb-4">
                        <label for="new_password_confirmation" class="form-label text-dark small fw-bold">
                            Xác nhận lại mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu mới..." autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="new_password_confirmation" title="Hiện/Ẩn mật khẩu">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div id="confirmFeedback" class="small mt-1 d-none"></div>
                    </div>

                    <!-- NÚT THỰC HIỆN -->
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm" id="btnSubmitSetup">
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
            confirmFeedback.innerHTML = '<i class="fa-solid fa-check me-1"></i>Mật khẩu xác nhận khớp.';
        } else {
            confirmFeedback.className = 'small mt-1 text-danger fw-semibold';
            confirmFeedback.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Mật khẩu xác nhận chưa khớp.';
        }
    }

    pwdInput.addEventListener('input', checkPassword);
    confirmInput.addEventListener('input', checkMatch);
});
</script>
@endpush
@endsection
