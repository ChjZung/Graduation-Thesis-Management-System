@extends($layout)
@section('page_title', 'Hồ Sơ Cá Nhân')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 text-primary-custom fw-bold"><i class="fa-solid fa-address-card me-2"></i>Hồ Sơ Cá Nhân</h4>
        <p class="text-muted small mb-0">Quản lý thông tin định danh, liên hệ và trạng thái tài khoản hệ thống</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="fa-solid fa-user-pen me-1"></i> Chỉnh sửa thông tin
        </button>
        <a href="{{ route('password.change') }}" class="btn btn-primary rounded-pill px-3 shadow-sm fw-semibold">
            <i class="fa-solid fa-key me-1"></i> Đổi mật khẩu
        </a>
    </div>
</div>



<!-- Hero Header Card -->
<div class="card card-premium shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #0072CE 0%, #003B73 100%); color: #fff;">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row align-items-center gap-4">
            <div class="position-relative">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($profile->HoTen ?? $user->TenDangNhap) }}&background=E5F0FA&color=0072CE&size=120&bold=true"
                     class="rounded-circle shadow border border-3 border-white"
                     alt="Avatar" style="width: 100px; height: 100px; object-fit: cover;">
                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Đang hoạt động"></span>
            </div>
            <div class="text-center text-md-start flex-grow-1">
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-white">{{ $profile->HoTen ?? $user->TenDangNhap }}</h3>
                    <span class="badge bg-light text-primary rounded-pill px-3 py-1 fw-bold fs-7">{{ $role }}</span>
                </div>
                <p class="mb-2 text-white-50 small">
                    <i class="fa-solid fa-id-badge me-1"></i> Tên đăng nhập / Mã: <code class="text-warning bg-dark bg-opacity-25 px-2 py-0.5 rounded">{{ $user->TenDangNhap }}</code>
                    @if(!empty($profile->Email))
                        <span class="mx-2">•</span>
                        <i class="fa-solid fa-envelope me-1"></i> {{ $profile->Email }}
                    @endif
                    @if(!empty($profile->SoDienThoai))
                        <span class="mx-2">•</span>
                        <i class="fa-solid fa-phone me-1"></i> {{ $profile->SoDienThoai }}
                    @endif
                </p>
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-1">
                        <i class="fa-solid fa-shield-check me-1"></i> Tài khoản ACTIVE
                    </span>
                    @if($user->NgayDoiMatKhau)
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Đổi mật khẩu: {{ \Carbon\Carbon::parse($user->NgayDoiMatKhau)->format('d/m/Y H:i') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Cột Trái: Thông tin chi tiết cá nhân & công tác/học vụ -->
    <div class="col-lg-8">
        <!-- Card Thông Tin Cá Nhân & Đào Tạo -->
        <div class="card card-premium shadow-sm mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-user-gear text-primary me-2"></i> Thông Tin Chi Tiết Hồ Sơ</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                    {{ $role }}
                </span>
            </div>
            <div class="card-body p-4">
                @if($role === 'Sinh viên')
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Mã Số Sinh Viên (MSSV)</label>
                            <p class="fw-bold fs-6 text-dark mb-0"><code>{{ $profile->MaSV ?? $user->TenDangNhap }}</code></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Họ Và Tên</label>
                            <p class="fw-semibold text-dark mb-0">{{ $profile->HoTen ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Giới Tính</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->GioiTinh ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Ngày Sinh</label>
                            <p class="fw-medium text-dark mb-0">
                                {{ $profile->NgaySinh ? \Carbon\Carbon::parse($profile->NgaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Lớp Học</label>
                            <p class="fw-semibold text-primary mb-0">{{ $profile->lop->TenLop ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Ngành Đào Tạo</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->nganh->TenNganh ?? ($profile->lop->nganh->TenNganh ?? 'Chưa cập nhật') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Khoa Trực Thuộc</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->khoa->TenKhoa ?? ($profile->lop->khoa->TenKhoa ?? 'Khoa Công Nghệ Thông Tin') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Khóa Học / Tuyển Sinh</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->KhoaHoc ?? 'K12 (2021 - 2025)' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Tín Chỉ Tích Lũy</label>
                            <p class="fw-bold text-dark mb-0">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded">{{ $profile->SoTinChiTichLuy ?? 0 }} tín chỉ</span>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Điểm Tích Lũy (Hệ 4)</label>
                            <p class="fw-bold text-dark mb-0">
                                <span class="badge bg-primary px-2 py-1 rounded">{{ number_format($profile->DiemTichLuy ?? 0, 2) }}</span> / 4.0
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Email Sinh Viên</label>
                            <p class="fw-medium text-dark mb-0">
                                <i class="fa-solid fa-envelope text-primary me-1"></i> {{ $profile->Email ?? 'Chưa cập nhật' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Số Điện Thoại Liên Hệ</label>
                            <p class="fw-medium text-dark mb-0">
                                <i class="fa-solid fa-phone text-success me-1"></i> {{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Trạng Thái Học Tập</label>
                            <p class="mb-0">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-check me-1"></i>{{ $profile->TrangThai ?? 'Đang học' }}
                                </span>
                            </p>
                        </div>
                    </div>

                @elseif($role === 'Giảng viên')
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Mã Giảng Viên</label>
                            <p class="fw-bold fs-6 text-dark mb-0"><code>{{ $profile->MaGV ?? $user->TenDangNhap }}</code></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Họ Và Tên</label>
                            <p class="fw-semibold text-dark mb-0">{{ $profile->HoTen ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Học Hàm / Học Vị</label>
                            <p class="fw-bold text-primary mb-0">
                                {{ trim(($profile->HocHam ? $profile->HocHam . ' ' : '') . ($profile->HocVi ?? '')) ?: 'Giảng viên' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Giới Tính</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->GioiTinh ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Ngày Sinh</label>
                            <p class="fw-medium text-dark mb-0">
                                {{ $profile->NgaySinh ? \Carbon\Carbon::parse($profile->NgaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Bộ Môn</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->boMon->TenBoMon ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Khoa</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->boMon->khoa->TenKhoa ?? 'Khoa Công Nghệ Thông Tin' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Trạng Thái Công Tác</label>
                            <p class="mb-0">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-check me-1"></i>{{ $profile->TrangThai ?? 'Đang công tác' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Email Công Vụ</label>
                            <p class="fw-medium text-dark mb-0">
                                <i class="fa-solid fa-envelope text-primary me-1"></i> {{ $profile->Email ?? 'Chưa cập nhật' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Số Điện Thoại Liên Hệ</label>
                            <p class="fw-medium text-dark mb-0">
                                <i class="fa-solid fa-phone text-success me-1"></i> {{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}
                            </p>
                        </div>
                    </div>

                @else
                    <!-- Giáo Vụ / Admin -->
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Mã Cán Bộ / Giáo Vụ</label>
                            <p class="fw-bold fs-6 text-dark mb-0"><code>{{ $profile->MaGVu ?? $user->TenDangNhap }}</code></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Họ Và Tên Cán Bộ</label>
                            <p class="fw-semibold text-dark mb-0">{{ $profile->HoTen ?? 'Ban Quản Trị Hệ Thống' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Chức Vụ</label>
                            <p class="fw-bold text-primary mb-0">{{ $profile->ChucVu ?? 'Giáo vụ Khoa' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Khoa Phụ Trách</label>
                            <p class="fw-medium text-dark mb-0">{{ $profile->khoa->TenKhoa ?? 'Khoa Công Nghệ Thông Tin' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Email Liên Hệ</label>
                            <p class="fw-medium text-dark mb-0">
                                <i class="fa-solid fa-envelope text-primary me-1"></i> {{ $profile->Email ?? 'admin@huit.edu.vn' }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Số Điện Thoại</label>
                            <p class="fw-medium text-dark mb-0">
                                <i class="fa-solid fa-phone text-success me-1"></i> {{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Bổ Sung Theo Vai Trò -->
        @if($role === 'Sinh viên')
            <div class="card card-premium shadow-sm">
                <div class="card-header-premium d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-users text-primary me-2"></i> Thông Tin Nhóm &amp; Khóa Luận Tốt Nghiệp</span>
                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-sm btn-link text-decoration-none">
                        Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    @php
                        $curNhom = $extraData['currentNhom'] ?? null;
                    @endphp
                    @if($curNhom)
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <span class="text-muted small fw-bold text-uppercase">Tên nhóm:</span>
                                <div class="fw-bold text-primary fs-6">{{ $curNhom->TenNhom }} ({{ $curNhom->MaNhom }})</div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small fw-bold text-uppercase">Trạng thái nhóm:</span>
                                <div>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill">
                                        {{ $curNhom->TrangThai ?? 'Đang hoạt động' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small fw-bold text-uppercase">Đề tài đăng ký:</span>
                                @if($curNhom->deTai)
                                    <div class="fw-semibold text-dark mt-1">
                                        <i class="fa-solid fa-book-bookmark text-primary me-1"></i>
                                        {{ $curNhom->deTai->TenDeTai }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        GVHD: <strong>{{ $curNhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong>
                                        <span class="mx-2">•</span>
                                        Trạng thái đề tài: <span class="badge bg-success rounded-pill px-2">{{ $curNhom->deTai->TrangThai }}</span>
                                    </div>
                                @else
                                    <div class="text-muted small fst-italic mt-1">
                                        Chưa đăng ký đề tài. <a href="{{ route('sinhvien.dangky.index') }}" class="fw-semibold">Đăng ký đề tài ngay</a>.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Danh sách thành viên nhóm -->
                        <div class="mt-3">
                            <span class="text-muted small fw-bold text-uppercase mb-2 d-block">Thành viên nhóm:</span>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>MSSV</th>
                                            <th>Họ và Tên</th>
                                            <th>Vai Trò</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($curNhom->thanhViens as $tv)
                                            <tr>
                                                <td><code>{{ $tv->MaSV }}</code></td>
                                                <td>{{ $tv->sinhVien->HoTen ?? 'N/A' }}</td>
                                                <td>
                                                    @if(str_contains(mb_strtolower($tv->VaiTro), 'trưởng'))
                                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="fa-solid fa-crown me-1"></i>{{ $tv->VaiTro }}</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">{{ $tv->VaiTro }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa-solid fa-users-slash text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-3">Bạn hiện chưa tham gia vào nhóm làm khóa luận tốt nghiệp nào.</p>
                            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                                <i class="fa-solid fa-plus me-1"></i> Tạo nhóm hoặc Tìm nhóm
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($role === 'Giảng viên')
            <div class="card card-premium shadow-sm">
                <div class="card-header-premium d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-chalkboard-user text-primary me-2"></i> Thống Kê Hướng Dẫn &amp; Đề Tài</span>
                    <a href="{{ route('giangvien.detai.index') }}" class="btn btn-sm btn-link text-decoration-none">
                        Quản lý đề tài <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Tổng đề tài đề xuất</div>
                                <div class="fs-3 fw-bold text-primary">{{ $extraData['deTaiCount'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Đề tài đã duyệt</div>
                                <div class="fs-3 fw-bold text-success">{{ $extraData['deTaiDaDuyet'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Nhóm SV đã đăng ký</div>
                                <div class="fs-3 fw-bold text-warning">{{ $extraData['deTaiDaDangKy'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Thống kê Admin -->
            <div class="card card-premium shadow-sm">
                <div class="card-header-premium d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-chart-pie text-primary me-2"></i> Thống Kê Tổng Quan Hệ Thống</span>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-link text-decoration-none">
                        Về Dashboard <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Tổng số đề tài</div>
                                <div class="fs-3 fw-bold text-primary">{{ $extraData['totalDeTai'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Tổng giảng viên</div>
                                <div class="fs-3 fw-bold text-success">{{ $extraData['totalGiangVien'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Tổng sinh viên</div>
                                <div class="fs-3 fw-bold text-info">{{ $extraData['totalSinhVien'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Cột Phải: Bảo Mật & Thông Tin Đăng Nhập -->
    <div class="col-lg-4">
        <div class="card card-premium shadow-sm mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-shield-halved text-primary me-2"></i> Bảo Mật &amp; Đăng Nhập</span>
                <span class="badge bg-success rounded-pill px-2 py-1"><i class="fa-solid fa-check me-1"></i>ACTIVE</span>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Tên đăng nhập:</span>
                        <code class="fw-bold text-primary">{{ $user->TenDangNhap }}</code>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Phân quyền vai trò:</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $role }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Trạng thái mật khẩu:</span>
                        <span class="badge bg-success rounded-pill px-2">{{ $user->TrangThaiMatKhau ?? 'ACTIVE' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Lần đổi MK gần nhất:</span>
                        <span class="small fw-medium text-dark">
                            {{ $user->NgayDoiMatKhau ? \Carbon\Carbon::parse($user->NgayDoiMatKhau)->format('d/m/Y H:i') : 'Chưa cập nhật' }}
                        </span>
                    </li>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Lần đăng nhập cuối:</span>
                        <span class="small fw-medium text-dark">
                            {{ $user->LanDangNhapCuoi ? \Carbon\Carbon::parse($user->LanDangNhapCuoi)->format('d/m/Y H:i') : 'Hiện tại' }}
                        </span>
                    </li>
                </ul>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-lock text-primary"></i>
                        <strong class="small text-dark">Mật khẩu tài khoản</strong>
                    </div>
                    <p class="text-muted small mb-0">
                        Nên đổi mật khẩu định kỳ ít nhất 6 tháng một lần để bảo vệ an toàn cho dữ liệu khóa luận và tài khoản.
                    </p>
                </div>

                <a href="{{ route('password.change') }}" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                    <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu ngay
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHỈNH SỬA THÔNG TIN CÁ NHÂN -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editProfileModalLabel">
                    <i class="fa-solid fa-user-pen me-2"></i>Cập Nhật Thông Tin Hồ Sơ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    @if($role === 'Admin' || $role === 'Giáo vụ')
                        <!-- Giáo vụ fields -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="HoTen" value="{{ old('HoTen', $profile->HoTen ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email Liên Hệ <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="Email" value="{{ old('Email', $profile->Email ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Số Điện Thoại</label>
                            <input type="text" class="form-control" name="SoDienThoai" value="{{ old('SoDienThoai', $profile->SoDienThoai ?? '') }}" placeholder="09xxxxxxxx (10 số)">
                        </div>
                    @elseif($role === 'Giảng viên')
                        <!-- Giảng viên fields -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Mã Giảng Viên &amp; Họ Tên</label>
                            <input type="text" class="form-control bg-light" value="{{ $profile->MaGV ?? '' }} - {{ $profile->HoTen ?? '' }}" disabled>
                            <div class="form-text small">Thông tin học vụ do Khoa / Ban Giám hiệu quản lý.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email Liên Hệ <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="Email" value="{{ old('Email', $profile->Email ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Số Điện Thoại</label>
                            <input type="text" class="form-control" name="SoDienThoai" value="{{ old('SoDienThoai', $profile->SoDienThoai ?? '') }}" placeholder="09xxxxxxxx (10 số)">
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-dark">Giới Tính</label>
                                <select class="form-select" name="GioiTinh">
                                    <option value="">-- Chọn --</option>
                                    <option value="Nam" {{ old('GioiTinh', $profile->GioiTinh ?? '') === 'Nam' ? 'selected' : '' }}>Nam</option>
                                    <option value="Nữ" {{ old('GioiTinh', $profile->GioiTinh ?? '') === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-dark">Ngày Sinh</label>
                                <input type="date" class="form-control" name="NgaySinh" value="{{ old('NgaySinh', $profile->NgaySinh ? \Carbon\Carbon::parse($profile->NgaySinh)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    @else
                        <!-- Sinh viên fields -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">MSSV &amp; Họ Tên</label>
                            <input type="text" class="form-control bg-light" value="{{ $profile->MaSV ?? '' }} - {{ $profile->HoTen ?? '' }}" disabled>
                            <div class="form-text small">Thông tin định danh và lớp học vụ do Phòng Đào tạo quản lý.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email Cá Nhân <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="Email" value="{{ old('Email', $profile->Email ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Số Điện Thoại</label>
                            <input type="text" class="form-control" name="SoDienThoai" value="{{ old('SoDienThoai', $profile->SoDienThoai ?? '') }}" placeholder="09xxxxxxxx (10 số)">
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-dark">Giới Tính</label>
                                <select class="form-select" name="GioiTinh">
                                    <option value="">-- Chọn --</option>
                                    <option value="Nam" {{ old('GioiTinh', $profile->GioiTinh ?? '') === 'Nam' ? 'selected' : '' }}>Nam</option>
                                    <option value="Nữ" {{ old('GioiTinh', $profile->GioiTinh ?? '') === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-dark">Ngày Sinh</label>
                                <input type="date" class="form-control" name="NgaySinh" value="{{ old('NgaySinh', $profile->NgaySinh ? \Carbon\Carbon::parse($profile->NgaySinh)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

