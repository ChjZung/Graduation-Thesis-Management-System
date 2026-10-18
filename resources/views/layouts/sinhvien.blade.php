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
            <div class="sidebar-brand">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">HUIT – Cổng Sinh Viên</span>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-user-graduate me-2"></i>
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
                        <i class="fa-solid fa-chart-pie me-2"></i>
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
                        <i class="fa-solid fa-users me-2"></i>
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
            @php
                $isSvBaoCao = request()->routeIs('sinhvien.baocao.*') || request()->routeIs('sinhvien.hoso.*') || request()->routeIs('sinhvien.ketqua.*');
            @endphp
            <div class="sidebar-menu-group">
                <button class="menu-parent-btn {{ $isSvBaoCao ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-sv-baocao" aria-expanded="{{ $isSvBaoCao ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-clipboard-check me-2"></i>
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
                    <a href="{{ route('sinhvien.ketqua.index') }}" class="submenu-link {{ request()->routeIs('sinhvien.ketqua.index') ? 'active-sub' : '' }}">
                        <span>Kết quả khóa luận</span>
                    </a>
                    <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="submenu-link {{ request()->routeIs('sinhvien.ketqua.hoanthien*') ? 'active-sub' : '' }}">
                        <span>Hoàn thiện báo cáo</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- /SIDEBAR -->

    <!-- ═══ MAIN CONTENT ═══ -->
    <div id="content">

        <!-- ═══ HEADER (Sticky Top) ═══ -->
        <header class="huit-header-sticky">
            <!-- Banner -->
            <div class="huit-banner-wrap">
                <img src="{{ asset('images/anhbanner.png') }}" alt="Banner HUIT">
            </div>

            <!-- Topbar / Breadcrumb Bar -->
            <div class="admin-top-bar">
                <div class="admin-breadcrumb">
                    <i class="fa-solid fa-angle-right me-2" style="color: #64748b; font-size: 0.75rem;"></i>
                    <a href="{{ route('sinhvien.dashboard') }}">Trang chủ</a>
                    <span class="separator">&gt;</span>
                    <span>Khóa luận tốt nghiệp</span>
                    <span class="separator">&gt;</span>
                    <span class="current-page">@yield('page_title', 'Sinh Viên')</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Notification Bell Dropdown Unified -->
                    @include('partials.header_notifications')

                    @php
                        $svDisplayName = Auth::user()->sinhVien->HoTen ?? Auth::user()->TenDangNhap;
                    @endphp
                    <!-- User Profile Pill -->
                    <div class="dropdown">
                        <a class="admin-user-pill dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="svUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($svDisplayName) }}&background=0072CE&color=FFFFFF&bold=true&size=64"
                                 alt="Avatar">
                            <span>{{ $svDisplayName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border" style="border-radius: 8px; min-width: 220px;" aria-labelledby="svUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1 border-bottom">
                                    <div style="font-size: 13px; font-weight: 600; color: #64748b;">Xin chào!</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #003b73;">{{ $svDisplayName }}</div>
                                    <div style="font-size: 13px; color: #0284c7; margin-top: 2px;"><i class="fa-solid fa-user-graduate me-2"></i>Sinh viên</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-sv').submit();">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form-sv" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Alerts + Content -->
        <div class="content-body admin-page-transition">

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
    // Auto-hide success alerts after 5s & safely move modals to document.body
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.modal').forEach(function(modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        setTimeout(function () {
            document.querySelectorAll('.alert-success').forEach(function (el) {
                let a = bootstrap.Alert.getOrCreateInstance(el);
                a.close();
            });
        }, 5000);

        // Chống Double Submit trên tất cả các form POST
        document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const btn = form.querySelector('button[type="submit"]:not([disabled])');
                if (btn) {
                    setTimeout(function () {
                        btn.disabled = true;
                        if (!btn.dataset.originalHtml) {
                            btn.dataset.originalHtml = btn.innerHTML;
                        }
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Đang xử lý...';
                    }, 30);
                }
            });
        });

        // Hiệu ứng mượt mà liên tiếp khi chuyển trang
        let progressBar = document.getElementById('huit-top-progress');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'huit-top-progress';
            document.body.prepend(progressBar);
        }

        document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([data-bs-toggle])').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                progressBar.style.opacity = '1';
                progressBar.style.width = '70%';
            });
        });

        window.addEventListener('beforeunload', function () {
            if (progressBar) {
                progressBar.style.opacity = '1';
                progressBar.style.width = '100%';
            }
        });
    });
</script>

<!-- Modal Xem Nhanh Đề Cương Chi Tiết -->
@include('partials.modal_preview_decuong')

@stack('scripts')
@include('components.time-machine')
</body>
</html>