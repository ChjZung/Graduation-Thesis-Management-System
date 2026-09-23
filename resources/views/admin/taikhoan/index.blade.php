@extends('layouts.admin')

@section('page_title', 'Quản Lý Tài Khoản & Vai Trò')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item">Quản Lý Người Dùng</li>
                    <li class="breadcrumb-item active" aria-current="page">Tài Khoản &amp; Vai Trò</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-gear text-primary"></i>
                <span>Quản Lý Tài Khoản &amp; Phân Quyền Vai Trò</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('admin.taikhoan.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-user-plus me-1"></i> Cấp Tài Khoản Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-blue">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Tài Khoản</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($stats['total']) }}</div>
                        <div class="small text-muted mt-1">Toàn hệ thống</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-purple">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Giáo Vụ / Admin</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['admin'] }}</div>
                        <div class="small text-muted mt-1">Quyền quản trị</div>
                    </div>
                    <div class="kpi-icon-circle bg-purple-subtle text-purple" style="background: rgba(147, 51, 234, 0.1); color: #9333ea;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Giảng Viên</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['giangvien'] }}</div>
                        <div class="small text-muted mt-1">Cán bộ hướng dẫn</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card {{ $stats['locked'] > 0 ? 'kpi-orange' : 'kpi-green' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tài Khoản Đang Khóa</div>
                        <div class="fs-4 fw-bold {{ $stats['locked'] > 0 ? 'text-danger' : 'text-success' }} mt-1">
                            {{ $stats['locked'] }}
                        </div>
                        <div class="small text-muted mt-1">{{ $stats['sinhvien'] }} tài khoản SV</div>
                    </div>
                    <div class="kpi-icon-circle {{ $stats['locked'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                        <i class="fa-solid {{ $stats['locked'] > 0 ? 'fa-user-lock' : 'fa-check-double' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.taikhoan.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Tìm theo Username, họ tên, email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="vai_tro" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả vai trò --</option>
                        @foreach($vaiTros as $vt)
                            <option value="{{ $vt->MaVaiTro }}" {{ request('vai_tro') == $vt->MaVaiTro ? 'selected' : '' }}>
                                {{ $vt->TenVaiTro }} ({{ $vt->MaVaiTro }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="trang_thai" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Trạng thái --</option>
                        <option value="1" {{ request('trang_thai') === '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ request('trang_thai') === '0' ? 'selected' : '' }}>Đang khóa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="mat_khau_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Trạng thái MK --</option>
                        <option value="INITIAL" {{ request('mat_khau_status') === 'INITIAL' ? 'selected' : '' }}>Chưa đổi (INITIAL)</option>
                        <option value="ACTIVE" {{ request('mat_khau_status') === 'ACTIVE' ? 'selected' : '' }}>Đã kích hoạt (ACTIVE)</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill">Lọc</button>
                    @if(request()->hasAny(['search', 'vai_tro', 'trang_thai', 'mat_khau_status']))
                        <a href="{{ route('admin.taikhoan.index') }}" class="btn btn-sm btn-light border rounded-pill" title="Xóa lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" width="18%">TÊN ĐĂNG NHẬP</th>
                        <th class="py-3">NGƯỜI DÙNG LIÊN KẾT</th>
                        <th class="py-3 text-center" width="14%">VAI TRÒ</th>
                        <th class="py-3 text-center" width="14%">MẬT KHẨU</th>
                        <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                        <th class="py-3 text-center" width="14%">ĐĂNG NHẬP CUỐI</th>
                        <th class="pe-4 py-3 text-center" width="12%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taiKhoans as $tk)
                        @php
                            $userProfile = $tk->giangVien ?? $tk->sinhVien ?? $tk->giaoVu;
                            $roleName = $tk->vaiTro->TenVaiTro ?? 'Người dùng';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                        {{ mb_substr($userProfile->HoTen ?? $tk->TenDangNhap, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $tk->TenDangNhap }}</div>
                                        <div class="small text-muted font-monospace" style="font-size: 0.75rem;">{{ $tk->MaTK }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($userProfile)
                                    <div class="fw-bold text-dark">{{ $userProfile->HoTen }}</div>
                                    <div class="small text-muted">{{ $userProfile->Email ?? 'Chưa có email' }}</div>
                                @else
                                    <span class="text-muted small">Tài khoản quản trị viên độc lập</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(str_contains(strtolower($roleName), 'admin') || str_contains(strtolower($roleName), 'giáo vụ'))
                                    <span class="badge bg-purple-subtle text-purple border rounded-pill px-3 py-1" style="background: rgba(147, 51, 234, 0.1); color: #9333ea;">
                                        <i class="fa-solid fa-user-shield me-1"></i> {{ $roleName }}
                                    </span>
                                @elseif(str_contains(strtolower($roleName), 'giảng viên'))
                                    <span class="badge bg-info-subtle text-info border rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-chalkboard-user me-1"></i> {{ $roleName }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-user-graduate me-1"></i> {{ $roleName }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tk->TrangThaiMatKhau === 'INITIAL' || $tk->BatBuocDoiMatKhau)
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 small" title="Cần đổi mật khẩu khi đăng nhập">
                                        <i class="fa-solid fa-key me-1"></i> Mặc định
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đã đổi MK
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tk->TrangThai)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">
                                        <i class="fa-solid fa-lock me-1"></i> Đang khóa
                                    </span>
                                @endif
                            </td>
                            <td class="text-center small text-muted">
                                {{ $tk->LanDangNhapCuoi ? \Carbon\Carbon::parse($tk->LanDangNhapCuoi)->format('d/m/Y H:i') : 'Chưa có' }}
                            </td>
                            <td class="pe-4 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.taikhoan.show', $tk->MaTK) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.taikhoan.edit', $tk->MaTK) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Sửa vai trò">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.taikhoan.toggleLock', $tk->MaTK) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light {{ $tk->TrangThai ? 'text-secondary' : 'text-success' }} btn-action-circle border" title="{{ $tk->TrangThai ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                            <i class="fa-solid {{ $tk->TrangThai ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.taikhoan.resetPassword', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Đặt lại mật khẩu của {{ $tk->TenDangNhap }} về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light text-info btn-action-circle border" title="Reset về 123456">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>
                                    @if($tk->MaTK !== auth()->id())
                                        <form action="{{ route('admin.taikhoan.destroy', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa tài khoản">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-users-slash text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Không tìm thấy tài khoản nào</h6>
                                    <p class="small text-muted mb-3">Vui lòng thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $taiKhoans->firstItem() ?? 0 }}–{{ $taiKhoans->lastItem() ?? 0 }} trên tổng số {{ $taiKhoans->total() }} tài khoản
            </span>
            @if($taiKhoans->hasPages())
                <div>{{ $taiKhoans->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.taikhoan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold text-dark" id="importModalLabel">
                        <i class="fa-solid fa-file-import text-primary me-2"></i>Import Danh Sách Tài Khoản
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải về file Excel mẫu chuẩn HUIT để điền thông tin danh sách tài khoản cần cấp phát.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Chọn Tệp Excel (.xlsx, .xls) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-sm" required accept=".xlsx,.xls,.csv">
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <a href="{{ route('admin.import.template', 'taikhoan') }}" class="small text-primary text-decoration-none">
                            <i class="fa-solid fa-download me-1"></i> Tải file mẫu Excel
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải Lên &amp; Xử Lý
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
