@extends($layout)
@section('page_title', 'Thông Tin Tài Khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card card-premium shadow-sm">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-address-card text-primary me-2"></i> Hồ Sơ Cá Nhân</span>
                <span class="badge bg-primary rounded-pill px-3">{{ $role }}</span>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($profile->HoTen ?? $user->TenDangNhap) }}&background=e9f2ff&color=3699ff&size=100" class="rounded-circle shadow-sm mb-3" alt="Avatar">
                    <h4 class="fw-bold mb-1">{{ $profile->HoTen ?? $user->TenDangNhap }}</h4>
                    <p class="text-muted small">Tên đăng nhập: <code>{{ $user->TenDangNhap }}</code></p>
                </div>

                <h6 class="fw-bold text-primary-custom mb-3"><i class="fa-solid fa-user me-2"></i>Thông Tin Cá Nhân</h6>
                <div class="row g-3 mb-4">
                    @if($role === 'Sinh viên')
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Mã Sinh Viên (MSSV)</label>
                            <p class="fw-medium mb-0"><code>{{ $user->TenDangNhap }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Lớp Học</label>
                            <p class="fw-medium mb-0">{{ $profile->lop->TenLop ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Email Sinh Viên</label>
                            <p class="fw-medium mb-0">{{ $profile->Email ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Số Điện Thoại</label>
                            <p class="fw-medium mb-0">{{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}</p>
                        </div>
                    @elseif($role === 'Giảng viên')
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Mã Giảng Viên</label>
                            <p class="fw-medium mb-0"><code>{{ $user->TenDangNhap }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Bộ Môn</label>
                            <p class="fw-medium mb-0">{{ $profile->boMon->TenBoMon ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Học Vị</label>
                            <p class="fw-medium mb-0">{{ $profile->HocVi ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Email</label>
                            <p class="fw-medium mb-0">{{ $profile->Email ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Số Điện Thoại</label>
                            <p class="fw-medium mb-0">{{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}</p>
                        </div>
                    @else
                        <div class="col-md-12">
                            <p class="text-muted small mb-0">Tài khoản Quản trị viên / Giáo vụ hệ thống.</p>
                        </div>
                    @endif
                </div>

                <hr class="my-4">

                <!-- PHẦN BẢO MẬT TÀI KHOẢN -->
                <h6 class="fw-bold text-primary-custom mb-3"><i class="fa-solid fa-shield-halved me-2"></i>Bảo Mật &amp; Mật Khẩu</h6>
                <div class="p-3 bg-light rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold text-dark mb-1">Mật khẩu: <span class="text-muted ms-2">••••••••••••</span></div>
                        <div class="small text-muted">
                            Trạng thái: <span class="badge bg-success rounded-pill px-2 py-1"><i class="fa-solid fa-check me-1"></i>Đang hoạt động (ACTIVE)</span>
                            @if($user->password_changed_at)
                                <span class="ms-2">Cập nhật lần cuối: {{ $user->password_changed_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('password.change') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-key me-1"></i> Đổi Mật Khẩu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
