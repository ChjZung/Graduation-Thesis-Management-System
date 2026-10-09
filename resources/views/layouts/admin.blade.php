<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Admin') – Quản Lý Khóa Luận Tốt Nghiệp | HUIT</title>
    <meta name="description" content="Hệ thống quản lý công tác khóa luận tốt nghiệp – Trường Đại Học Công Thương TP.HCM">
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
                    <span class="sidebar-subtitle">HUIT – Quản Lý Khóa Luận</span>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-shield-halved"></i>
                Quản Trị Viên
            </div>
        </div>

        <div class="px-2 py-2">
            <!-- 1. TỔNG QUAN -->
            <div class="sidebar-menu-group">
                @php
                    $isTongQuanActive = request()->routeIs('admin.dashboard') || request()->routeIs('thongbao.*');
                @endphp
                <button class="menu-parent-btn {{ $isTongQuanActive ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tongquan" aria-expanded="{{ $isTongQuanActive ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Tổng Quan</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTongQuanActive ? 'show' : '' }}" id="sub-tongquan">
                    <a href="{{ route('admin.dashboard') }}" class="submenu-link {{ request()->routeIs('admin.dashboard') ? 'active-sub' : '' }}">
                        <span>Bảng tổng quan</span>
                    </a>
                    <a href="{{ route('thongbao.index') }}" class="submenu-link {{ request()->routeIs('thongbao.*') ? 'active-sub' : '' }}">
                        <span>Thông báo hệ thống</span>
                    </a>
                </div>
            </div>

            <!-- 2. QUẢN LÝ KHÓA LUẬN -->
            <div class="sidebar-menu-group">
                @php
                    $isQLKLActive = request()->routeIs('admin.kehoach.*') || request()->routeIs('admin.quydinh.*') || request()->routeIs('admin.calendar') || request()->routeIs('admin.theodoi.*') || request()->routeIs('admin.duyet_detai.*') || request()->routeIs('admin.duyet_dangky.*');
                @endphp
                <button class="menu-parent-btn {{ $isQLKLActive ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-qlkl" aria-expanded="{{ $isQLKLActive ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>Quản Lý Khóa Luận</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isQLKLActive ? 'show' : '' }}" id="sub-qlkl">
                    <a href="{{ route('admin.kehoach.index') }}" class="submenu-link {{ request()->routeIs('admin.kehoach.*') ? 'active-sub' : '' }}">
                        <span>Kế hoạch khóa luận</span>
                    </a>
                    <a href="{{ route('admin.quydinh.index') }}" class="submenu-link {{ request()->routeIs('admin.quydinh.*') ? 'active-sub' : '' }}">
                        <span>Quy định khóa luận</span>
                    </a>
                    <a href="{{ route('admin.calendar') }}" class="submenu-link {{ request()->routeIs('admin.calendar') ? 'active-sub' : '' }}">
                        <span>Lịch quy trình</span>
                    </a>
                    <a href="{{ route('admin.theodoi.index') }}" class="submenu-link {{ request()->routeIs('admin.theodoi.*') ? 'active-sub' : '' }}">
                        <span>Theo dõi tiến độ</span>
                    </a>
                    <a href="{{ route('admin.duyet_detai.index') }}" class="submenu-link {{ request()->routeIs('admin.duyet_detai.*') ? 'active-sub' : '' }}">
                        <span>Quản lý đề tài</span>
                    </a>
                    <a href="{{ route('admin.duyet_dangky.index') }}" class="submenu-link {{ request()->routeIs('admin.duyet_dangky.*') ? 'active-sub' : '' }}">
                        <span>Xem đăng ký đề tài</span>
                    </a>
                </div>
            </div>

            <!-- 3. BẢO VỆ & HỘI ĐỒNG -->
            <div class="sidebar-menu-group">
                @php
                    $isHoiDongActive = request()->routeIs('admin.hoidong.*') || request()->routeIs('admin.hosoBaoVe.*') || request()->routeIs('admin.ketqua.*');
                @endphp
                <button class="menu-parent-btn {{ $isHoiDongActive ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-hoidong" aria-expanded="{{ $isHoiDongActive ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-landmark"></i>
                        <span>Bảo Vệ &amp; Hội Đồng</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isHoiDongActive ? 'show' : '' }}" id="sub-hoidong">
                    <a href="{{ route('admin.hoidong.index') }}" class="submenu-link {{ request()->routeIs('admin.hoidong.*') ? 'active-sub' : '' }}">
                        <span>Hội đồng bảo vệ</span>
                    </a>
                    <a href="{{ route('admin.hosoBaoVe.index') }}" class="submenu-link {{ request()->routeIs('admin.hosoBaoVe.*') ? 'active-sub' : '' }}">
                        <span>Hồ sơ bảo vệ</span>
                    </a>
                    <a href="{{ route('admin.ketqua.index') }}" class="submenu-link {{ request()->routeIs('admin.ketqua.*') ? 'active-sub' : '' }}">
                        <span>Bảng điểm &amp; Kết quả</span>
                    </a>
                </div>
            </div>

            <!-- 4. NGƯỜI DÙNG & HỒ SƠ -->
            <div class="sidebar-menu-group">
                @php
                    $isUserActive = request()->routeIs('sinhvien.*') || request()->routeIs('admin.sinhvien.*') || request()->routeIs('giangvien.*') || request()->routeIs('admin.taikhoan.*') || request()->routeIs('admin.yeucau.*');
                @endphp
                <button class="menu-parent-btn {{ $isUserActive ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-user" aria-expanded="{{ $isUserActive ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>Người Dùng &amp; Hồ Sơ</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isUserActive ? 'show' : '' }}" id="sub-user">
                    <a href="{{ route('sinhvien.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.*') || request()->routeIs('admin.sinhvien.*') ? 'active-sub' : '' }}">
                        <span>Quản lý Sinh viên</span>
                    </a>
                    <a href="{{ route('giangvien.index') }}" class="submenu-link {{ request()->routeIs('giangvien.*') ? 'active-sub' : '' }}">
                        <span>Quản lý Giảng viên</span>
                    </a>
                    <a href="{{ route('admin.taikhoan.index') }}" class="submenu-link {{ request()->routeIs('admin.taikhoan.*') ? 'active-sub' : '' }}">
                        <span>Tài khoản hệ thống</span>
                    </a>
                    <a href="{{ route('admin.yeucau.index') }}" class="submenu-link {{ request()->routeIs('admin.yeucau.*') ? 'active-sub' : '' }}">
                        <span>Đổi mật khẩu</span>
                    </a>
                </div>
            </div>

            <!-- 5. DANH MỤC HỌC VỤ -->
            <div class="sidebar-menu-group">
                @php
                    $isHocVuActive = request()->routeIs('hocky.*') || request()->routeIs('khoa.*') || request()->routeIs('bomon.*') || request()->routeIs('nganh.*') || request()->routeIs('lop.*') || request()->routeIs('hocphan.*');
                @endphp
                <button class="menu-parent-btn {{ $isHocVuActive ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-hocvu" aria-expanded="{{ $isHocVuActive ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>Danh Mục Học Vụ</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isHocVuActive ? 'show' : '' }}" id="sub-hocvu">
                    <a href="{{ route('hocky.index') }}" class="submenu-link {{ request()->routeIs('hocky.*') ? 'active-sub' : '' }}">
                        <span>Học kỳ</span>
                    </a>
                    <a href="{{ route('khoa.index') }}" class="submenu-link {{ request()->routeIs('khoa.*') ? 'active-sub' : '' }}">
                        <span>Khoa</span>
                    </a>
                    <a href="{{ route('bomon.index') }}" class="submenu-link {{ request()->routeIs('bomon.*') ? 'active-sub' : '' }}">
                        <span>Bộ môn</span>
                    </a>
                    <a href="{{ route('nganh.index') }}" class="submenu-link {{ request()->routeIs('nganh.*') ? 'active-sub' : '' }}">
                        <span>Ngành</span>
                    </a>
                    <a href="{{ route('lop.index') }}" class="submenu-link {{ request()->routeIs('lop.*') ? 'active-sub' : '' }}">
                        <span>Lớp</span>
                    </a>
                    <a href="{{ route('hocphan.index') }}" class="submenu-link {{ request()->routeIs('hocphan.*') ? 'active-sub' : '' }}">
                        <span>Học phần</span>
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

        <!-- Topbar / Breadcrumb Bar -->
        <div class="admin-top-bar">
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-angle-right" style="color: #64748b; font-size: 0.75rem;"></i>
                <a href="{{ route('admin.dashboard') }}">Trang chủ</a>
                <span class="separator">&gt;</span>
                <span>Khóa luận tốt nghiệp</span>
                <span class="separator">&gt;</span>
                <span class="current-page">@yield('page_title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Bell Unified -->
                @include('partials.header_notifications')

                <!-- Admin Profile Pill -->
                <div class="dropdown">
                    <a class="admin-user-pill dropdown-toggle text-decoration-none"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       id="adminUserDropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->TenDangNhap ?? 'Admin') }}&background=0072CE&color=FFFFFF&bold=true&size=64"
                             alt="Avatar">
                        <span>{{ Auth::user()->TenDangNhap ?? 'Admin' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px; min-width: 220px;" aria-labelledby="adminUserDropdown">
                        <li>
                            <div class="px-3 py-2 mb-1 border-bottom">
                                <div style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Xin chào!</div>
                                <div style="font-size: 0.88rem; font-weight: 700; color: #003b73;">{{ Auth::user()->TenDangNhap ?? 'Admin' }}</div>
                                <div style="font-size: 0.72rem; color: #10b981; margin-top: 1px;"><i class="fa-solid fa-shield-halved me-1"></i>Quản trị viên</div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.show') }}">
                                <i class="fa-solid fa-user me-2 text-primary"></i> Hồ sơ cá nhân
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('password.change') }}">
                                <i class="fa-solid fa-key me-2 text-primary"></i> Đổi mật khẩu
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                            </a>
                        </li>
                        <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                </div>
            </div>
        </div>

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
    // Auto-hide success alerts after 5s
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.querySelectorAll('.alert-success').forEach(function (el) {
                let a = bootstrap.Alert.getOrCreateInstance(el);
                a.close();
            });
        }, 5000);

        // Chống Double Submit trên tất cả các form POST trong Admin
        document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const btn = form.querySelector('button[type="submit"]:not([disabled])');
                if (btn) {
                    setTimeout(function () {
                        btn.disabled = true;
                        if (!btn.dataset.originalHtml) {
                            btn.dataset.originalHtml = btn.innerHTML;
                        }
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang xử lý...';
                    }, 30);
                }
            });
        });
    });
</script>

<!-- Modal Xem Nhanh Đề Cương Chi Tiết -->
@include('partials.modal_preview_decuong')

@stack('scripts')
@include('components.time-machine')
</body>
</html>
