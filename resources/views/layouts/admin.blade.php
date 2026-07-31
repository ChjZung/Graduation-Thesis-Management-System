<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Admin') – Hệ Thống QLĐA | HUIT</title>
    <meta name="description" content="Hệ thống quản lý đồ án  – Trường Đại Học Công Thương TP.HCM">
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
            <div class="sidebar-brand">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">HUIT – Hệ Thống QLĐA</span>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-shield-halved"></i>
                Quản Trị Viên
            </div>
        </div>

        <ul class="list-unstyled components">
            <!-- Dashboard -->
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>

            <!-- Quản Lý Tài Khoản -->
            <li class="nav-section-label">Quản Lý Tài Khoản</li>
            <li class="{{ request()->routeIs('sinhvien.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.index') }}">
                    <i class="fa-solid fa-user-graduate"></i> Quản lý Sinh viên
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.index') }}">
                    <i class="fa-solid fa-chalkboard-user"></i> Quản lý Giảng viên
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.yeucau.*') ? 'active' : '' }}">
                <a href="{{ route('admin.yeucau.index') }}">
                    <i class="fa-solid fa-key"></i> Duyệt Quên Mật Khẩu
                </a>
            </li>

            <!-- Quản Lý Danh Mục -->
            <li class="nav-section-label">Quản Lý Danh Mục</li>
            <li class="{{ request()->routeIs('bomon.*') ? 'active' : '' }}">
                <a href="{{ route('bomon.index') }}">
                    <i class="fa-solid fa-building"></i> Quản lý Bộ môn
                </a>
            </li>
            <li class="{{ request()->routeIs('nganh.*') ? 'active' : '' }}">
                <a href="{{ route('nganh.index') }}">
                    <i class="fa-solid fa-book-open"></i> Quản lý Ngành
                </a>
            </li>
            <li class="{{ request()->routeIs('lop.*') ? 'active' : '' }}">
                <a href="{{ route('lop.index') }}">
                    <i class="fa-solid fa-users-rectangle"></i> Quản lý Lớp Hành Chính
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.lophocphan.*') ? 'active' : '' }}">
                <a href="{{ route('admin.lophocphan.index') }}">
                    <i class="fa-solid fa-graduation-cap"></i> Quản lý Lớp Học Phần
                </a>
            </li>
            <li class="{{ request()->routeIs('monhoc.*') ? 'active' : '' }}">
                <a href="{{ route('monhoc.index') }}">
                    <i class="fa-solid fa-book"></i> Quản lý Môn học
                </a>
            </li>
            <li class="{{ request()->routeIs('hocky.*') ? 'active' : '' }}">
                <a href="{{ route('hocky.index') }}">
                    <i class="fa-solid fa-calendar-days"></i> Quản lý Học kỳ
                </a>
            </li>
            <li class="{{ request()->routeIs('phancong.*') ? 'active' : '' }}">
                <a href="{{ route('phancong.index') }}">
                    <i class="fa-solid fa-sitemap"></i> Phân công hướng dẫn
                </a>
            </li>

            <!-- Thông Tin Hệ Thống -->
            <li class="nav-section-label">Thông Tin Hệ Thống</li>
            <li class="{{ request()->routeIs('admin.sanpham.*') ? 'active' : '' }}">
                <a href="{{ route('admin.sanpham.index') }}">
                    <i class="fa-solid fa-box-archive"></i> Quản lý Sản phẩm
                </a>
            </li>
            <li class="{{ request()->routeIs('thongbao.*') ? 'active' : '' }}">
                <a href="{{ route('thongbao.index') }}">
                    <i class="fa-solid fa-bell"></i> Quản lý Thông Báo
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.ketqua.*') ? 'active' : '' }}">
                <a href="{{ route('admin.ketqua.index') }}">
                    <i class="fa-solid fa-square-poll-vertical"></i> Quản lý Kết Quả
                </a>
            </li>
        </ul>
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
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="adminUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->TenDangNhap ?? 'Admin') }}&background=E5F0FA&color=0072CE&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ Auth::user()->TenDangNhap ?? 'Admin' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1" style="border-bottom: 1px solid var(--columbia-blue);">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--huit-blue-dark);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark);">{{ Auth::user()->TenDangNhap ?? 'Admin' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-shield-halved me-1" style="color: var(--huit-blue);"></i>Quản trị viên</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Alerts + Content -->
        <div class="content-body">

            @if ($errors->any())
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
    // Auto-hide success alerts after 5s
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.querySelectorAll('.alert-success').forEach(function (el) {
                let a = bootstrap.Alert.getOrCreateInstance(el);
                a.close();
            });
        }, 5000);
    });
</script>

@stack('scripts')
</body>
</html>
