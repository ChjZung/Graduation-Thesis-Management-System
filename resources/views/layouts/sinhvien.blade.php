<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Sinh Viên') – Quản Lý Khóa Luận Tốt Nghiệp | HUIT</title>
    <meta name="description" content="Cổng Sinh Viên – Hệ thống Quản lý Công tác Khóa luận Tốt nghiệp HUIT">
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
            <a href="{{ route('sinhvien.dashboard') }}" class="sidebar-brand text-decoration-none text-white d-flex align-items-center">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">HUIT – Cổng Sinh Viên</span>
                </div>
            </a>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-user-graduate"></i>
                Sinh Viên
            </div>
        </div>

        <div class="px-2 py-2">
            <!-- 1. TỔNG QUAN & LỊCH TRÌNH -->
            <div class="sidebar-menu-group">
                @php
                    $isSvTongQuan = request()->routeIs('sinhvien.dashboard') || request()->routeIs('sinhvien.calendar') || request()->routeIs('sinhvien.thongbao.*');
                @endphp
                <button class="menu-parent-btn {{ $isSvTongQuan ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-sv-tongquan" aria-expanded="{{ $isSvTongQuan ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Tổng Quan &amp; Lịch</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isSvTongQuan ? 'show' : '' }}" id="sub-sv-tongquan">
                    <a href="{{ route('sinhvien.dashboard') }}" class="submenu-link {{ request()->routeIs('sinhvien.dashboard') ? 'active-sub' : '' }}">
                        <span>Bảng tổng quan</span>
                    </a>
                    <a href="{{ route('sinhvien.calendar') }}" class="submenu-link {{ request()->routeIs('sinhvien.calendar') ? 'active-sub' : '' }}">
                        <span>Lịch báo cáo</span>
                    </a>
                    <a href="{{ route('sinhvien.thongbao.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.thongbao.*') ? 'active-sub' : '' }}">
                        <span>Thông báo hệ thống</span>
                    </a>
                </div>
            </div>

            <!-- 2. NHÓM & ĐỀ TÀI -->
            <div class="sidebar-menu-group">
                @php
                    $isSvDeTai = request()->routeIs('sinhvien.nhom.*') || request()->routeIs('sinhvien.dangky.*');
                @endphp
                <button class="menu-parent-btn {{ $isSvDeTai ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-sv-detai" aria-expanded="{{ $isSvDeTai ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-users"></i>
                        <span>Nhóm &amp; Đề Tài</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isSvDeTai ? 'show' : '' }}" id="sub-sv-detai">
                    <a href="{{ route('sinhvien.nhom.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.nhom.*') ? 'active-sub' : '' }}">
                        <span>Nhóm của tôi</span>
                    </a>
                    <a href="{{ route('sinhvien.dangky.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.dangky.*') ? 'active-sub' : '' }}">
                        <span>Đăng ký đề tài</span>
                    </a>
                </div>
            </div>

            <!-- 3. THỰC HIỆN & KẾT QUẢ -->
            <div class="sidebar-menu-group">
                @php
                    $isSvBaoCao = request()->routeIs('sinhvien.baocao.*') || request()->routeIs('sinhvien.hoso.*') || request()->routeIs('sinhvien.ketqua.*');
                @endphp
                <button class="menu-parent-btn {{ $isSvBaoCao ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-sv-baocao" aria-expanded="{{ $isSvBaoCao ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Thực Hiện &amp; Kết Quả</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isSvBaoCao ? 'show' : '' }}" id="sub-sv-baocao">
                    <a href="{{ route('sinhvien.baocao.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.baocao.*') ? 'active-sub' : '' }}">
                        <span>Báo cáo tiến độ</span>
                    </a>
                    <a href="{{ route('sinhvien.hoso.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.hoso.*') ? 'active-sub' : '' }}">
                        <span>Hồ sơ bảo vệ</span>
                    </a>
                    <a href="{{ route('sinhvien.ketqua.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.ketqua.*') ? 'active-sub' : '' }}">
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
                    <!-- Notification Bell Dropdown Unified -->
                    @include('partials.header_notifications')


                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="svUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->sinhVien->HoTen ?? Auth::user()->TenDangNhap) }}&background=E5F0FA&color=0072CE&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ Auth::user()->sinhVien->HoTen ?? Auth::user()->TenDangNhap }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="svUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1" style="border-bottom: 1px solid var(--columbia-blue);">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--huit-blue-dark);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark);">{{ Auth::user()->sinhVien->HoTen ?? Auth::user()->TenDangNhap }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-user-graduate me-1" style="color: var(--huit-blue);"></i>Sinh Viên</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-sv').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-sv" action="{{ route('logout') }}" method="POST" class="d-none">
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

@stack('scripts')
@include('components.time-machine')
</body>
</html>