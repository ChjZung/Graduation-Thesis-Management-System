@extends('layouts.admin')

@section('page_title', 'Chi Tiết Tài Khoản - ' . $taiKhoan->TenDangNhap)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.taikhoan.index') }}" class="text-decoration-none">Tài Khoản</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $taiKhoan->TenDangNhap }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-shield text-primary"></i>
                <span>Tài Khoản: {{ $taiKhoan->TenDangNhap }}</span>
                <span class="badge-code ms-2">{{ $taiKhoan->MaTK }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.taikhoan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('admin.taikhoan.edit', $taiKhoan->MaTK) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa
            </a>
        </div>
    </div>

    @php
        $userProfile = $taiKhoan->giangVien ?? $taiKhoan->sinhVien ?? $taiKhoan->giaoVu;
        $roleName = $taiKhoan->vaiTro->TenVaiTro ?? 'Người dùng';
    @endphp

    <div class="row g-4">
        <!-- Left: Thông tin tài khoản & Lịch sử bảo mật -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-shield-halved text-primary me-2"></i>Trạng Thái Tài Khoản &amp; Bảo Mật
                    </h6>
                    <span class="badge {{ $taiKhoan->TrangThai ? 'bg-success' : 'bg-danger' }} rounded-pill px-3 py-1">
                        {{ $taiKhoan->TrangThai ? 'Đang Hoạt Động' : 'Đang Bị Khóa' }}
                    </span>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Tài Khoản</span>
                            <span class="badge-code">{{ $taiKhoan->MaTK }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Đăng Nhập</span>
                            <span class="fw-bold text-dark">{{ $taiKhoan->TenDangNhap }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Vai Trò Phân Quyền</span>
                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 fw-bold">
                                {{ $roleName }} ({{ $taiKhoan->MaVaiTro }})
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái Mật Khẩu (BR02)</span>
                            <span class="badge {{ $taiKhoan->TrangThaiMatKhau === 'INITIAL' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }} rounded-pill px-2 py-1">
                                {{ $taiKhoan->TrangThaiMatKhau ?? 'ACTIVE' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Bắt Buộc Đổi Mật Khẩu</span>
                            <span class="fw-medium {{ $taiKhoan->BatBuocDoiMatKhau ? 'text-warning' : 'text-muted' }}">
                                {{ $taiKhoan->BatBuocDoiMatKhau ? 'Có (Phải đổi khi đăng nhập)' : 'Không' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Số Lần Đăng Nhập Sai (BR01)</span>
                            <span class="fw-bold {{ $taiKhoan->SoLanDangNhapSai >= 5 ? 'text-danger' : 'text-dark' }}">
                                {{ $taiKhoan->SoLanDangNhapSai ?? 0 }} / 5 lần tối đa
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Lần Đăng Nhập Đầu Tiên</span>
                            <span class="text-dark">
                                {{ $taiKhoan->LanDangNhapDau ? \Carbon\Carbon::parse($taiKhoan->LanDangNhapDau)->format('d/m/Y H:i:s') : 'Chưa đăng nhập lần nào' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Đăng Nhập Gần Nhất</span>
                            <span class="text-dark">
                                {{ $taiKhoan->LanDangNhapCuoi ? \Carbon\Carbon::parse($taiKhoan->LanDangNhapCuoi)->format('d/m/Y H:i:s') : 'Chưa có' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Đổi Mật Khẩu Gần Nhất</span>
                            <span class="text-dark">
                                {{ $taiKhoan->NgayDoiMatKhau ? \Carbon\Carbon::parse($taiKhoan->NgayDoiMatKhau)->format('d/m/Y H:i:s') : 'Chưa đổi mật khẩu' }}
                            </span>
                        </li>
                        @if($taiKhoan->NgayKhoa)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-danger fw-bold">Thời Điểm Bị Khóa</span>
                                <span class="text-danger fw-bold">
                                    {{ \Carbon\Carbon::parse($taiKhoan->NgayKhoa)->format('d/m/Y H:i:s') }}
                                </span>
                            </li>
                        @endif
                    </ul>

                    <div class="row g-2 pt-3 border-top">
                        <div class="col-sm-6">
                            <form action="{{ route('admin.taikhoan.toggleLock', $taiKhoan->MaTK) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $taiKhoan->TrangThai ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 rounded-pill">
                                    <i class="fa-solid {{ $taiKhoan->TrangThai ? 'fa-lock' : 'fa-lock-open' }} me-1"></i>
                                    {{ $taiKhoan->TrangThai ? 'Khóa Tài Khoản Này' : 'Mở Khóa Tài Khoản' }}
                                </button>
                            </form>
                        </div>
                        <div class="col-sm-6">
                            <form action="{{ route('admin.taikhoan.resetPassword', $taiKhoan->MaTK) }}" method="POST" onsubmit="return confirm('Đặt lại mật khẩu của {{ $taiKhoan->TenDangNhap }} về 123456?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info w-100 rounded-pill">
                                    <i class="fa-solid fa-key me-1"></i> Reset Pass Về 123456
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Hồ sơ Người Dùng Thực Thể Gắn Liền -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-id-card-clip text-primary me-2"></i>Hồ Sơ Thực Thể Liên Kết
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($taiKhoan->sinhVien)
                        @php $sv = $taiKhoan->sinhVien; @endphp
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $sv->HoTen }}</h6>
                                <span class="badge-code">{{ $sv->MaSV }}</span> &bull;
                                <span class="small text-muted">Lớp {{ $sv->lop->TenLop ?? $sv->MaLop }}</span>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Ngành Học</span>
                                <span class="text-dark">{{ $sv->nganh->TenNganh ?? '' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Khoa Quản Lý</span>
                                <span class="text-dark">{{ $sv->khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Tín Chỉ Tích Lũy</span>
                                <span class="fw-bold {{ $sv->SoTinChiTichLuy >= 115 ? 'text-success' : 'text-danger' }}">
                                    {{ $sv->SoTinChiTichLuy ?? 0 }} TC ({{ $sv->SoTinChiTichLuy >= 115 ? 'Đủ chuẩn KLTN' : 'Chưa đủ chuẩn' }})
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Email</span>
                                <span class="text-dark">{{ $sv->Email ?? 'Chưa có' }}</span>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem Chi Tiết Hồ Sơ Sinh Viên
                            </a>
                        </div>

                    @elseif($taiKhoan->giangVien)
                        @php $gv = $taiKhoan->giangVien; @endphp
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}</h6>
                                <span class="badge-code">{{ $gv->MaGV }}</span> &bull;
                                <span class="small text-muted">{{ $gv->boMon->TenBoMon ?? 'Bộ môn' }}</span>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Học Vị</span>
                                <span class="text-dark">{{ $gv->HocVi ?? 'Giảng viên' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Khoa Quản Lý</span>
                                <span class="text-dark">{{ $gv->boMon->khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Email Liên Hệ</span>
                                <span class="text-dark">{{ $gv->Email ?? 'Chưa có' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Số Điện Thoại</span>
                                <span class="text-dark">{{ $gv->SoDienThoai ?? 'Chưa có' }}</span>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem Chi Tiết Hồ Sơ Giảng Viên
                            </a>
                        </div>

                    @elseif($taiKhoan->giaoVu)
                        @php $gvu = $taiKhoan->giaoVu; @endphp
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $gvu->HoTen }}</h6>
                                <span class="badge-code">{{ $gvu->MaGVu }}</span> &bull;
                                <span class="small text-muted">{{ $gvu->ChucVu ?? 'Cán bộ giáo vụ' }}</span>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Chức Vụ</span>
                                <span class="text-dark">{{ $gvu->ChucVu ?? 'Giáo vụ khoa' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Khoa Quản Lý</span>
                                <span class="text-dark">{{ $gvu->khoa->TenKhoa ?? 'Toàn trường' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Email</span>
                                <span class="text-dark">{{ $gvu->Email ?? 'Chưa có' }}</span>
                            </li>
                        </ul>

                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-gear fa-3x mb-3 text-secondary opacity-50"></i>
                            <h6 class="fw-bold text-dark">Tài Khoản Quản Trị Hệ Thống Độc Lập</h6>
                            <p class="small mb-0">Tài khoản này có quyền quản trị tối cao, không gán với hồ sơ sinh viên hay giảng viên cụ thể.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
