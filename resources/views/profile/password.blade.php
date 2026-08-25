@extends($layout)
@section('title', 'Đổi Mật Khẩu')
@section('content')
<style>
    .input-icon-wrap {
        position: relative;
    }
    .input-icon-wrap input {
        padding-right: 42px;
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
    }
    .toggle-password:hover {
        color: #0072CE;
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-key me-2"></i>Đổi Mật Khẩu</h4>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card card-premium shadow-sm">
            <div class="card-body p-4">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('password.change.post') }}" id="formChangePassword">
                    @csrf
                    
                    <!-- MẬT KHẨU HIỆN TẠI -->
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-bold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <div class="input-icon-wrap">
                            <input type="password" class="form-control" name="current_password" id="current_password" required placeholder="Nhập mật khẩu đang dùng...">
                            <button type="button" class="toggle-password" data-target="current_password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- MẬT KHẨU MỚI -->
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-bold">Mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-icon-wrap">
                            <input type="password" class="form-control" name="new_password" id="new_password" required placeholder="Nhập mật khẩu mới...">
                            <button type="button" class="toggle-password" data-target="new_password">
                                <i class="fa-solid fa-eye"></i>
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
                                <i class="fa-regular fa-circle"></i> Ít nhất 8 ký tự
                            </div>
                            <div class="checklist-item" id="ruleUpper">
                                <i class="fa-regular fa-circle"></i> Có chữ in hoa (A-Z)
                            </div>
                            <div class="checklist-item" id="ruleLower">
                                <i class="fa-regular fa-circle"></i> Có chữ thường (a-z)
                            </div>
                            <div class="checklist-item" id="ruleNumber">
                                <i class="fa-regular fa-circle"></i> Có chữ số (0-9)
                            </div>
                        </div>
                    </div>
                    
                    <!-- XÁC NHẬN MẬT KHẨU MỚI -->
                    <div class="mb-4">
                        <label class="form-label text-dark small fw-bold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-icon-wrap">
                            <input type="password" class="form-control" name="new_password_confirmation" id="new_password_confirmation" required placeholder="Nhập lại mật khẩu mới...">
                            <button type="button" class="toggle-password" data-target="new_password_confirmation">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div id="confirmFeedback" class="small mt-1 d-none"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
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
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
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
@endsection
