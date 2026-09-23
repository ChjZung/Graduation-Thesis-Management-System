@extends('layouts.admin')

@section('page_title', 'Đổi mật khẩu & Quản lý tài khoản')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-primary"></i> Quản Lý An Toàn Mật Khẩu &amp; Trạng Thái Khóa / Mở Tài Khoản Portal
        </h4>
        <div class="text-muted small">Kiểm soát yêu cầu cấp lại mật khẩu từ Giảng viên &amp; Sinh viên, chống brute-force và quản trị trạng thái tài khoản.</div>
    </div>
    <div class="d-flex align-items-center">
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
            <i class="fa-solid fa-lock text-primary me-1"></i> Chính sách bảo mật HUIT: Reset về <code>123456</code>
        </span>
    </div>
</div>



<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-orange d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Yêu Cầu Chờ Duyệt</div>
                <div class="kpi-value text-warning">{{ $stats['cho_duyet'] ?? 0 }} <span class="fs-6 text-muted font-normal">yêu cầu</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-blue d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Tổng Tài Khoản Portal</div>
                <div class="kpi-value text-primary">{{ $stats['total_tk'] ?? 0 }} <span class="fs-6 text-muted font-normal">tài khoản</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(0, 114, 206, 0.1); color: #0072ce;">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-green d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Đang Hoạt Động (Active)</div>
                <div class="kpi-value text-success">{{ $stats['active_tk'] ?? 0 }} <span class="fs-6 text-muted font-normal">tài khoản</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-purple d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Đang Bị Khóa (Locked)</div>
                <div class="kpi-value text-danger">{{ $stats['locked_tk'] ?? 0 }} <span class="fs-6 text-muted font-normal">tài khoản</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fa-solid fa-lock"></i>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: YÊU CẦU CẤP LẠI MẬT KHẨU ĐANG CHỜ DUYỆT -->
<div class="admin-table-card mb-4">
    <div class="table-card-header d-flex justify-content-between align-items-center">
        <div class="table-card-title d-flex align-items-center gap-2">
            <i class="fa-solid fa-envelope-open-text text-warning"></i>
            <span>Yêu Cầu Cấp Lại Mật Khẩu Đang Chờ Duyệt</span>
            <span class="badge bg-danger rounded-pill ms-2">{{ $yeuCauChoDuyet->count() }}</span>
        </div>
        <small class="text-muted">Tự động gửi email thông báo sau khi Quản trị viên duyệt</small>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th width="15%">MÃ SỐ (MSSV / GV)</th>
                    <th width="22%">HỌ VÀ TÊN &amp; EMAIL</th>
                    <th width="14%">VAI TRÒ</th>
                    <th width="25%">LÝ DO YÊU CẦU</th>
                    <th width="12%">THỜI GIAN GỬI</th>
                    <th width="12%" class="text-center">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($yeuCauChoDuyet as $yc)
                <tr>
                    <td>
                        <span class="badge-code">{{ $yc->TenDangNhap }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $yc->HoTen }}</div>
                        @if($yc->Email)
                            <div class="small text-muted">{{ $yc->Email }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status {{ $yc->Role === 'Giảng viên' ? 'badge-status-approved' : 'badge-status-submitted' }}">
                            {{ $yc->Role }}
                        </span>
                    </td>
                    <td>
                        <span class="small text-secondary">{{ $yc->LyDo ?? 'Quên mật khẩu đăng nhập' }}</span>
                    </td>
                    <td>
                        <span class="small text-muted">{{ \Carbon\Carbon::parse($yc->NgayGui)->format('d/m/Y H:i') }}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <form action="{{ route('admin.yeucau.approve', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận phê duyệt cấp lại mật khẩu (reset về 123456) cho {{ $yc->HoTen }}?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-xs fw-semibold" title="Duyệt reset mật khẩu">
                                    <i class="fa-solid fa-check me-1"></i> Duyệt
                                </button>
                            </form>
                            <form action="{{ route('admin.yeucau.reject', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận từ chối yêu cầu của {{ $yc->HoTen }}?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Từ chối yêu cầu">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fa-regular fa-circle-check fa-2x text-success mb-2"></i>
                        <div class="small">Hiện không có yêu cầu cấp lại mật khẩu nào đang chờ duyệt.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 2: QUẢN LÝ TOÀN BỘ TÀI KHOẢN HỆ THỐNG -->
<div class="admin-table-card mb-4">
    <div class="table-card-header d-flex justify-content-between align-items-center">
        <div class="table-card-title">
            <i class="fa-solid fa-user-lock text-primary me-2"></i> Quản Lý Toàn Bộ Tài Khoản Hệ Thống &amp; Khóa / Mở Khóa
        </div>
        <span class="badge bg-light text-muted border small">Chống Brute-force &bull; Bảo mật OAuth / LDAP</span>
    </div>

    <!-- Filter Bar -->
    <div class="p-3 bg-light border-bottom">
        <form method="GET" action="{{ route('admin.yeucau.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo Tên đăng nhập / MSSV / Mã GV..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="MaVaiTro" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả Vai trò --</option>
                    @foreach($vaiTros as $vt)
                        <option value="{{ $vt->MaVaiTro }}" {{ request('MaVaiTro') == $vt->MaVaiTro ? 'selected' : '' }}>
                            {{ $vt->TenVaiTro }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="TrangThai" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Trạng thái tài khoản --</option>
                    <option value="1" {{ request('TrangThai') === '1' ? 'selected' : '' }}>🟢 Đang hoạt động</option>
                    <option value="0" {{ request('TrangThai') === '0' ? 'selected' : '' }}>🔴 Đang bị khóa</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-magnifying-glass me-1"></i>Lọc</button>
                <a href="{{ route('admin.yeucau.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Xóa</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th width="15%">MÃ TK</th>
                    <th width="22%">TÊN ĐĂNG NHẬP</th>
                    <th width="15%">VAI TRÒ</th>
                    <th width="15%" class="text-center">TRẠNG THÁI MK</th>
                    <th width="15%" class="text-center">TÀI KHOẢN</th>
                    <th width="18%" class="text-center">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taiKhoans as $tk)
                <tr>
                    <td><span class="badge-code">{{ $tk->MaTK }}</span></td>
                    <td>
                        <span class="fw-bold text-primary"><code>{{ $tk->TenDangNhap }}</code></span>
                        @if($tk->SoLanDangNhapSai > 0)
                            <span class="badge bg-danger-subtle text-danger rounded-pill ms-1" title="Số lần đăng nhập sai">
                                Sai {{ $tk->SoLanDangNhapSai }} lần
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status badge-status-submitted">{{ $tk->vaiTro->TenVaiTro ?? 'N/A' }}</span>
                    </td>
                    <td class="text-center">
                        @if($tk->TrangThaiMatKhau === 'ACTIVE' || $tk->password_status === 'ACTIVE')
                            <span class="badge-status badge-status-approved"><i class="fa-solid fa-check me-1"></i>ACTIVE</span>
                        @elseif($tk->TrangThaiMatKhau === 'INITIAL' || $tk->password_status === 'INITIAL')
                            <span class="badge-status badge-status-warning"><i class="fa-solid fa-clock me-1"></i>INITIAL</span>
                        @else
                            <span class="badge-status badge-status-rejected">EXPIRED</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($tk->TrangThai)
                            <span class="text-success fw-semibold small d-inline-flex align-items-center gap-1">
                                <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span> Hoạt động
                            </span>
                        @else
                            <span class="text-danger fw-semibold small d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Đã khóa
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <!-- NÚT RESET MẬT KHẨU THỦ CÔNG -->
                            <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận reset mật khẩu tài khoản \'{{ $tk->TenDangNhap }}\' về 123456?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2" title="Reset mật khẩu về 123456">
                                    <i class="fa-solid fa-key me-1"></i>Reset MK
                                </button>
                            </form>

                            <!-- NÚT KHÓA / MỞ KHÓA -->
                            @if($tk->MaTK !== auth()->user()?->MaTK)
                            <form action="{{ route('admin.yeucau.reject', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận {{ $tk->TrangThai ? 'khóa' : 'mở khóa' }} tài khoản \'{{ $tk->TenDangNhap }}\'?');">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $tk->TrangThai ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill px-3" title="{{ $tk->TrangThai ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                    <i class="fa-solid {{ $tk->TrangThai ? 'fa-lock me-1' : 'fa-lock-open me-1' }}"></i>{{ $tk->TrangThai ? 'Khóa' : 'Mở' }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-shield-halved fa-2x mb-2 opacity-50"></i>
                        <h6>Không tìm thấy tài khoản nào</h6>
                        <p class="small text-muted mb-0">Thử thay đổi từ khóa hoặc bộ lọc tìm kiếm.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($taiKhoans->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Hiển thị <strong>{{ $taiKhoans->firstItem() }} - {{ $taiKhoans->lastItem() }}</strong> trên tổng số <strong>{{ $taiKhoans->total() }}</strong> tài khoản</span>
        {{ $taiKhoans->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
