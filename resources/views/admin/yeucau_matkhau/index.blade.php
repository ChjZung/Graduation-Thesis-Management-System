@extends('layouts.admin')

@section('page_title', 'Quản Lý Yêu Cầu Đổi Mật Khẩu & Tài Khoản')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- PHẦN 1: DANH SÁCH YÊU CẦU QUÊN MẬT KHẨU TỪ SINH VIÊN / GIẢNG VIÊN -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-bell text-warning fa-lg animate__animated animate__pulse animate__infinite"></i>
            <h5 class="fw-bold mb-0 text-dark">Yêu Cầu Cấp Lại Mật Khẩu Đang Chờ Duyệt</h5>
            <span class="badge bg-danger rounded-pill">{{ $yeuCauChoDuyet->count() }}</span>
        </div>
        <small class="text-muted">Reset về mật khẩu mặc định <strong>123456</strong></small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th class="ps-3" width="15%">Mã số (MSSV / GV)</th>
                        <th width="20%">Họ và tên</th>
                        <th width="15%">Vai trò</th>
                        <th width="25%">Lý do yêu cầu</th>
                        <th width="12%">Thời gian gửi</th>
                        <th width="13%" class="text-center pe-3">Thao tác duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($yeuCauChoDuyet as $yc)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold border">
                                    {{ $yc->TenDangNhap }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $yc->HoTen }}</strong>
                                @if($yc->Email)
                                    <div class="small text-muted">{{ $yc->Email }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $yc->Role === 'Giảng viên' ? 'bg-info-subtle text-info-emphasis' : 'bg-primary-subtle text-primary' }} rounded-pill px-3 py-1">
                                    {{ $yc->Role }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-secondary">{{ $yc->LyDo ?? 'Quên mật khẩu' }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($yc->NgayGui)->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('admin.yeucau.approve', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận phê duyệt cấp lại mật khẩu (reset về 123456) cho {{ $yc->HoTen }} ({{ $yc->TenDangNhap }})?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 me-1 shadow-xs" title="Duyệt reset mật khẩu">
                                            <i class="fa-solid fa-check me-1"></i>Duyệt
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.yeucau.reject', $yc->MaYeuCau) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận từ chối yêu cầu của {{ $yc->HoTen }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Từ chối yêu cầu">
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
</div>

<!-- PHẦN 2: QUẢN LÝ TOÀN BỘ TÀI KHOẢN HỆ THỐNG & KHÓA / MỞ KHÓA -->
<div class="card card-premium shadow-sm border-0">
    <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-primary fa-lg"></i>
            <h5 class="fw-bold mb-0 text-dark">Quản Lý Toàn Bộ Tài Khoản Hệ Thống</h5>
        </div>
        <span class="badge bg-light text-muted border small">Chống Brute-force & Khóa tài khoản</span>
    </div>
    <div class="card-body p-0">
        <!-- Filter Bar -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" action="{{ route('admin.yeucau.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo Tên đăng nhập / MSSV / Mã GV..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="MaVaiTro" class="form-select form-select-sm">
                        <option value="">-- Tất cả Vai trò --</option>
                        @foreach($vaiTros as $vt)
                            <option value="{{ $vt->MaVaiTro }}" {{ request('MaVaiTro') == $vt->MaVaiTro ? 'selected' : '' }}>
                                {{ $vt->TenVaiTro }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="TrangThai" class="form-select form-select-sm">
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
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="15%" class="ps-3">Mã TK</th>
                        <th width="20%">Tên Đăng Nhập</th>
                        <th width="15%">Vai Trò</th>
                        <th width="15%" class="text-center">Trạng Thái MK</th>
                        <th width="15%" class="text-center">Tài Khoản</th>
                        <th width="20%" class="text-center pe-3">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taiKhoans as $tk)
                        <tr>
                            <td class="ps-3"><span class="badge-code">{{ $tk->MaTK }}</span></td>
                            <td>
                                <span class="fw-bold text-primary"><code>{{ $tk->TenDangNhap }}</code></span>
                                @if($tk->SoLanDangNhapSai > 0)
                                    <span class="badge bg-danger-subtle text-danger rounded-pill ms-1" title="Số lần đăng nhập sai">
                                        Sai {{ $tk->SoLanDangNhapSai }} lần
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $tk->vaiTro->TenVaiTro ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                @if($tk->TrangThaiMatKhau === 'ACTIVE' || $tk->password_status === 'ACTIVE')
                                    <span class="badge bg-success rounded-pill px-3"><i class="fa-solid fa-check me-1"></i>ACTIVE</span>
                                @elseif($tk->TrangThaiMatKhau === 'INITIAL' || $tk->password_status === 'INITIAL')
                                    <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-clock me-1"></i>INITIAL</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3">EXPIRED</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tk->TrangThai)
                                    <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-3">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger text-white fw-bold rounded-pill px-3">Đã khóa</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- NÚT RESET MẬT KHẨU THỦ CÔNG -->
                                    <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận reset mật khẩu tài khoản \'{{ $tk->TenDangNhap }}\' về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2" title="Reset mật khẩu về 123456">
                                            <i class="fa-solid fa-key me-1"></i>Reset MK
                                        </button>
                                    </form>

                                    <!-- NÚT KHÓA / MỞ KHÓA -->
                                    @if($tk->MaTK !== auth()->user()->MaTK)
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
                            <td colspan="6">
                                <div class="empty-state-box text-center py-4 text-muted">
                                    <i class="fa-solid fa-shield-halved fa-2x mb-2"></i>
                                    <h6>Không tìm thấy tài khoản nào</h6>
                                    <p class="small text-muted mb-0">Thử thay đổi từ khóa hoặc bộ lọc tìm kiếm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($taiKhoans->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $taiKhoans->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
