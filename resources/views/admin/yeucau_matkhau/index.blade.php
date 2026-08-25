@extends('layouts.admin')

@section('page_title', 'Quản Lý Mật Khẩu & Khóa Tài Khoản')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-premium">
        <div class="card-header-premium d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-key text-primary me-2"></i> Quản Lý An Toàn Mật Khẩu &amp; Tài Khoản</span>
        </div>
        <div class="card-body p-0">
            <div class="p-3 bg-light border-bottom">
                <form method="GET" action="{{ route('admin.yeucau.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên đăng nhập / MSSV / Mã GV..." value="{{ request('search') }}">
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
                <table class="table table-custom table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="12%">Mã TK</th>
                            <th width="20%">Tên Đăng Nhập</th>
                            <th width="15%">Vai Trò</th>
                            <th width="18%" class="text-center">Trạng Thái MK</th>
                            <th width="15%" class="text-center">Tài Khoản</th>
                            <th width="20%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taiKhoans as $tk)
                            <tr>
                                <td><span class="badge bg-light text-dark fw-bold border">{{ $tk->MaTK }}</span></td>
                                <td class="fw-bold text-primary-custom">
                                    <code>{{ $tk->TenDangNhap }}</code>
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
                                    @if($tk->password_status === 'ACTIVE')
                                        <span class="badge bg-success rounded-pill px-3"><i class="fa-solid fa-check me-1"></i>ACTIVE</span>
                                    @elseif($tk->password_status === 'INITIAL')
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
                                <td class="text-center">
                                    <!-- NÚT RESET MẬT KHẨU -->
                                    <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Xác nhận reset mật khẩu tài khoản \'{{ $tk->TenDangNhap }}\' về 123456?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 me-1" title="Reset mật khẩu về 123456">
                                            <i class="fa-solid fa-key me-1"></i>Reset MK
                                        </button>
                                    </form>

                                    <!-- NÚT KHÓA / MỞ KHÓA -->
                                    @if($tk->MaTK !== auth()->user()->MaTK)
                                    <form action="{{ route('admin.yeucau.reject', $tk->MaTK) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Xác nhận {{ $tk->TrangThai ? 'khóa' : 'mở khóa' }} tài khoản \'{{ $tk->TenDangNhap }}\'?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $tk->TrangThai ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill px-2" title="{{ $tk->TrangThai ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                            <i class="fa-solid {{ $tk->TrangThai ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                                    Không tìm thấy tài khoản nào
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
