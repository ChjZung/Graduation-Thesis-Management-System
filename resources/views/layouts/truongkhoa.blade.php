<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Trưởng Khoa') – Quản Lý Khóa Luận Tốt Nghiệp | HUIT</title>
    <meta name="description" content="Cổng Trưởng Khoa – Hệ thống Quản lý Công tác Khóa luận Tốt nghiệp HUIT">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- HUIT Theme -->
    <link rel="stylesheet" href="{{ asset('css/huit_theme.css') }}">
    
    @stack('styles')
</head>
<body>

<div class="wrapper d-flex">

    <!-- ═══ SIDEBAR ═══ -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('truongkhoa.dashboard') }}" class="sidebar-brand text-decoration-none text-white d-flex align-items-center">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">HUIT – Trưởng Khoa</span>
                </div>
            </a>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-graduation-cap"></i>
                Trưởng Khoa
            </div>
        </div>

        @php
            $sidebarUser = Auth::user();
            $sidebarGv = \App\Models\GiangVien::getLoggedInGiangVien($sidebarUser);
            $sidebarKhoa = null;
            if ($sidebarUser && preg_match('/^TK_([A-Z0-9]+)_/i', $sidebarUser->TenDangNhap, $m)) {
                $sidebarKhoa = \App\Models\Khoa::where('MaKhoa', $m[1])->first();
            } elseif ($sidebarGv && $sidebarGv->boMon && $sidebarGv->boMon->khoa) {
                $sidebarKhoa = $sidebarGv->boMon->khoa;
            }
            $sidebarMaKhoa = $sidebarKhoa ? $sidebarKhoa->MaKhoa : 'CNTT';
            $countChoDuyetKhoa = \App\Models\DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $sidebarMaKhoa))
                ->where('TrangThai', 'Chờ duyệt cấp Khoa')
                ->count();
        @endphp

        <div class="px-2 py-2">
            <!-- 1. TỔNG QUAN -->
            <div class="sidebar-menu-group">
                @php
                    $isTkTongQuan = request()->routeIs('truongkhoa.dashboard') || request()->routeIs('thongbao.*');
                @endphp
                <button class="menu-parent-btn {{ $isTkTongQuan ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tk-tongquan" aria-expanded="{{ $isTkTongQuan ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Tổng Quan</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTkTongQuan ? 'show' : '' }}" id="sub-tk-tongquan">
                    <a href="{{ route('truongkhoa.dashboard') }}" class="submenu-link {{ request()->routeIs('truongkhoa.dashboard') ? 'active-sub' : '' }}">
                        <span>Bảng tổng quan</span>
                    </a>
                    <a href="{{ route('thongbao.index') }}" class="submenu-link {{ request()->routeIs('thongbao.*') ? 'active-sub' : '' }}">
                        <span>Thông báo hệ thống</span>
                    </a>
                </div>
            </div>

            <!-- 2. QUẢN LÝ & PHÊ DUYỆT ĐỀ TÀI -->
            <div class="sidebar-menu-group">
                @php
                    $isTkDeTai = request()->routeIs('truongkhoa.duyet_detai.*') || request()->routeIs('truongkhoa.theodoi.*');
                @endphp
                <button class="menu-parent-btn {{ $isTkDeTai ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tk-detai" aria-expanded="{{ $isTkDeTai ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-stamp"></i>
                        <span>Quản Lý Đề Tài</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTkDeTai ? 'show' : '' }}" id="sub-tk-detai">
                    <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="submenu-link {{ request()->routeIs('truongkhoa.duyet_detai.*') ? 'active-sub' : '' }}">
                        <span>Phê duyệt đề tài</span>
                        @if($countChoDuyetKhoa > 0)
                            <span class="badge bg-danger rounded-pill" style="font-size: 11px !important; padding: 2px 6px !important;">{{ $countChoDuyetKhoa }}</span>
                        @endif
                    </a>
                    <a href="{{ route('truongkhoa.theodoi.index') }}" class="submenu-link {{ request()->routeIs('truongkhoa.theodoi.*') ? 'active-sub' : '' }}">
                        <span>Theo dõi tiến độ</span>
                    </a>
                </div>
            </div>

            <!-- 3. KẾ HOẠCH & KẾT QUẢ -->
            <div class="sidebar-menu-group">
                @php
                    $isTkKeHoach = request()->routeIs('truongkhoa.kehoach.*') || request()->routeIs('truongkhoa.ketqua.*');
                @endphp
                <button class="menu-parent-btn {{ $isTkKeHoach ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tk-kehoach" aria-expanded="{{ $isTkKeHoach ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Kế Hoạch &amp; Kết Quả</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTkKeHoach ? 'show' : '' }}" id="sub-tk-kehoach">
                    <a href="{{ route('truongkhoa.kehoach.index') }}" class="submenu-link {{ request()->routeIs('truongkhoa.kehoach.*') ? 'active-sub' : '' }}">
                        <span>Kế hoạch khóa luận</span>
                    </a>
                    <a href="{{ route('truongkhoa.ketqua.index') }}" class="submenu-link {{ request()->routeIs('truongkhoa.ketqua.*') ? 'active-sub' : '' }}">
                        <span>Kết quả khóa luận</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- /SIDEBAR -->

    <!-- ═══ MAIN CONTENT ═══ -->
    <div id="content">

        <!-- Banner -->
        <div class="huit-banner-wrap">
            <img src="{{ asset('images/anhbanner.png') }}" alt="Banner HUIT">
        </div>

        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <div class="page-title-text">
                    <i class="fa-solid fa-angle-right"></i>
                    @yield('page_title', 'Dashboard')
                </div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    @php
                        $tkGv = \App\Models\GiangVien::getLoggedInGiangVien();
                        $tkName = $tkGv->HoTen ?? (Auth::user()->TenDangNhap ?? 'Trưởng Khoa');
                    @endphp

                    <!-- Notification Bell Dropdown Unified -->
                    @include('partials.header_notifications')

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="tkUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($tkName) }}&background=FEE2E2&color=DC2626&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ $tkName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="tkUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1" style="border-bottom: 1px solid var(--columbia-blue);">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--huit-blue-dark);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark);">{{ $tkName }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-graduation-cap me-1 text-danger"></i>Trưởng Khoa</div>
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="fa-solid fa-user text-huit-blue"></i> Hồ sơ cá nhân
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('password.change') }}">
                                    <i class="fa-solid fa-key text-huit-blue"></i> Đổi mật khẩu
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-tk').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-tk" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Alerts + Content -->
        <div class="content-body">

            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #dc3545 !important; border-radius: 10px;">
                    <i class="fa-solid fa-circle-xmark me-2"></i>
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size: 0.875rem;">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #198754 !important; border-radius: 10px;">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #dc3545 !important; border-radius: 10px;">
                    <i class="fa-solid fa-circle-xmark me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #f59e0b !important; border-radius: 10px;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        @include('partials.footer')

    </div>
    <!-- /MAIN CONTENT -->

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.querySelectorAll('.alert-success').forEach(function (el) {
                let a = bootstrap.Alert.getOrCreateInstance(el);
                a.close();
            });
        }, 5000);
    });
</script>

<!-- Modal Xem Nhanh Đề Cương Chi Tiết -->
@include('partials.modal_preview_decuong')

@stack('scripts')
</body>
</html>
