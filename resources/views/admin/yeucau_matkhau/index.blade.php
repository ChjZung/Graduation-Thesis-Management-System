@extends('layouts.admin')

@section('page_title', 'Đổi mật khẩu & Quản lý tài khoản')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Quản Lý An Toàn Mật Khẩu &amp; Trạng Thái Khóa / Mở Tài Khoản Portal</h4>
            <p class="text-muted small mb-0">Kiểm soát yêu cầu cấp lại mật khẩu từ Giảng viên &amp; Sinh viên, chống brute-force và quản trị trạng thái tài khoản.</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-normal">
                <i class="fa-solid fa-lock text-primary me-1"></i> Chính sách bảo mật HUIT: Reset về <code>123456</code>
            </span>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Yêu Cầu Chờ Duyệt</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-bell fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-warning lh-1">{{ $stats['cho_duyet'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">yêu cầu cấp lại</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Tài Khoản Portal</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['total_tk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">tài khoản hệ thống</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đang Hoạt Động (Active)</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $stats['active_tk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">tài khoản mở</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đang Bị Khóa (Locked)</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fee2e2; color: #dc2626;">
                        <i class="fa-solid fa-lock fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-danger lh-1">{{ $stats['locked_tk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">tài khoản hạn chế</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 1: YÊU CẦU CẤP LẠI MẬT KHẨU ĐANG CHỜ DUYỆT -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-envelope-open-text text-warning"></i>
                <span>Yêu Cầu Cấp Lại Mật Khẩu Đang Chờ Duyệt</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1 fw-semibold">{{ $yeuCauChoDuyet->count() }}</span>
            </span>
            <span class="small text-muted">Tự động gửi email thông báo sau khi Quản trị viên duyệt</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3" width="16%">MÃ SỐ (MSSV / GV)</th>
                        <th class="py-3" width="24%">HỌ VÀ TÊN &amp; EMAIL</th>
                        <th class="py-3" width="14%">VAI TRÒ</th>
                        <th class="py-3" width="26%">LÝ DO YÊU CẦU</th>
                        <th class="py-3" width="12%">THỜI GIAN GỬI</th>
                        <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($yeuCauChoDuyet as $yc)
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="badge-code">{{ $yc->TenDangNhap }}</span>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-darkfs-6">{{ $yc->HoTen }}</div>
                            @if($yc->Email)
                                <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $yc->Email }}</div>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($yc->Role === 'Giảng viên')
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    Giảng viên
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    Sinh viên
                                </span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="small text-secondary">{{ $yc->LyDo ?? 'Quên mật khẩu đăng nhập' }}</span>
                        </td>
                        <td class="py-3">
                            <span class="small text-muted"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($yc->NgayGui)->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="pe-4 text-center py-3">
                            <div class="d-flex justify-content-center gap-1">
                                <form action="{{ route('admin.yeucau.approve', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận phê duyệt cấp lại mật khẩu (reset về 123456) cho {{ $yc->HoTen }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light text-success btn-action-circle border" title="Duyệt reset mật khẩu về 123456">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.yeucau.reject', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận từ chối yêu cầu của {{ $yc->HoTen }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Từ chối yêu cầu">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state-box">
                                <i class="fa-regular fa-circle-check text-success mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold text-secondary">Hiện không có yêu cầu nào chờ duyệt</h6>
                                <p class="small text-muted mb-0">Hệ thống đang hoạt động an toàn và không có yêu cầu cấp lại mật khẩu mới.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2: QUẢN LÝ TOÀN BỘ TÀI KHOẢN HỆ THỐNG -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-solid fa-user-lock text-primary me-2"></i> Quản Lý Toàn Bộ Tài Khoản Hệ Thống &amp; Khóa / Mở Khóa</span>
            <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $taiKhoans->total() }} tài khoản</span>
        </div>

        <!-- Filter Bar -->
        <div class="admin-filter-bar border-bottom">
            <form method="GET" action="{{ route('admin.yeucau.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                    <div class="input-group" style="min-width: 240px; flex: 1;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm theo Tên đăng nhập / MSSV / Mã GV..." value="{{ request('search') }}">
                    </div>
                    <select name="MaVaiTro" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">-- Tất cả Vai trò --</option>
                        @foreach($vaiTros as $vt)
                            <option value="{{ $vt->MaVaiTro }}" {{ request('MaVaiTro') == $vt->MaVaiTro ? 'selected' : '' }}>
                                {{ $vt->TenVaiTro }}
                            </option>
                        @endforeach
                    </select>
                    <select name="TrangThai" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                        <option value="">-- Trạng thái tài khoản --</option>
                        <option value="1" {{ request('TrangThai') === '1' ? 'selected' : '' }}>🟢 Đang hoạt động</option>
                        <option value="0" {{ request('TrangThai') === '0' ? 'selected' : '' }}>🔴 Đang bị khóa</option>
                    </select>
                    @if(request()->anyFilled(['search', 'MaVaiTro', 'TrangThai']))
                        <a href="{{ route('admin.yeucau.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3" width="14%">MÃ TK</th>
                        <th class="py-3" width="26%">TÊN ĐĂNG NHẬP</th>
                        <th class="py-3" width="18%">VAI TRÒ HỆ THỐNG</th>
                        <th class="py-3 text-center" width="14%">TRẠNG THÁI MK</th>
                        <th class="py-3 text-center" width="14%">TÀI KHOẢN</th>
                        <th class="pe-4 py-3 text-center" width="14%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($taiKhoans as $tk)
                    <tr>
                        <td class="ps-4 py-3"><span class="badge-code">{{ $tk->MaTK }}</span></td>
                        <td class="py-3">
                            <span class="fw-bold text-primary"><code>{{ $tk->TenDangNhap }}</code></span>
                            @if($tk->SoLanDangNhapSai > 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1" title="Số lần đăng nhập sai">
                                    Sai {{ $tk->SoLanDangNhapSai }} lần
                                </span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">{{ $tk->vaiTro->TenVaiTro ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center py-3">
                            @if($tk->TrangThaiMatKhau === 'ACTIVE' || $tk->password_status === 'ACTIVE')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold"><i class="fa-solid fa-check me-1"></i>ACTIVE</span>
                            @elseif($tk->TrangThaiMatKhau === 'INITIAL' || $tk->password_status === 'INITIAL')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold"><i class="fa-solid fa-clock me-1"></i>INITIAL</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-semibold">EXPIRED</span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if($tk->TrangThai)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #dc2626;"></i> Đã khóa
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-center py-3">
                            <div class="d-flex justify-content-center gap-1">
                                <!-- NÚT RESET MẬT KHẨU THỦ CÔNG -->
                                <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận reset mật khẩu tài khoản \'{{ $tk->TenDangNhap }}\' về 123456?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Reset mật khẩu về 123456">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                </form>

                                <!-- NÚT KHÓA / MỞ KHÓA -->
                                @if($tk->MaTK !== auth()->user()?->MaTK)
                                <form action="{{ route('admin.yeucau.reject', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận {{ $tk->TrangThai ? 'khóa' : 'mở khóa' }} tài khoản \'{{ $tk->TenDangNhap }}\'?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light {{ $tk->TrangThai ? 'text-danger' : 'text-success' }} btn-action-circle border" title="{{ $tk->TrangThai ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                        <i class="fa-solid {{ $tk->TrangThai ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-shield-halved text-muted mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold text-secondary">Không tìm thấy tài khoản nào</h6>
                                <p class="small text-muted mb-3">Thử thay đổi từ khóa hoặc xóa bộ lọc tìm kiếm.</p>
                                @if(request()->anyFilled(['search', 'MaVaiTro', 'TrangThai']))
                                    <a href="{{ route('admin.yeucau.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $taiKhoans->firstItem() ?? 0 }}–{{ $taiKhoans->lastItem() ?? 0 }} trên tổng số {{ $taiKhoans->total() }} tài khoản hệ thống
            </span>
            @if($taiKhoans->hasPages())
                <div>
                    {{ $taiKhoans->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
