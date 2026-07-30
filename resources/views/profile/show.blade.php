@extends($layout)
@section('page_title', 'Thông Tin Tài Khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-address-card text-primary me-2"></i> Hồ Sơ Cá Nhân</span>
                <span class="badge bg-primary">{{ $role }}</span>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ $profile->HoTen ?? 'Admin' }}&background=e9f2ff&color=3699ff&size=100" class="rounded-circle shadow-sm mb-3" alt="Avatar">
                    <h4 class="fw-bold">{{ $profile->HoTen ?? $user->TenDangNhap }}</h4>
                    <p class="text-muted">{{ $user->TenDangNhap }}</p>
                </div>

                <div class="row g-4">
                    @if($role === 'Sinh viên')
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Mã Sinh Viên</label>
                            <p class="fw-medium mb-0">{{ $user->TenDangNhap }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Lớp</label>
                            <p class="fw-medium mb-0">{{ $profile->lop->TenLop ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Email</label>
                            <p class="fw-medium mb-0">{{ $profile->Email ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Số Điện Thoại</label>
                            <p class="fw-medium mb-0">{{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}</p>
                        </div>
                    @elseif($role === 'Giảng viên')
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Mã Giảng Viên</label>
                            <p class="fw-medium mb-0">{{ $user->TenDangNhap }}</p>
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
                        <div class="col-md-12 text-center">
                            <p class="text-muted">Tài khoản quản trị viên không có thông tin chi tiết.</p>
                        </div>
                    @endif
                </div>

                <hr class="my-4">
                <div class="text-center">
                    <a href="{{ route('password.change') }}" class="btn btn-outline-primary btn-custom rounded-pill">
                        <i class="fa-solid fa-key me-2"></i> Đổi Mật Khẩu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
