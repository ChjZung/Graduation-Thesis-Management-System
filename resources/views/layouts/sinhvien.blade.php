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
    
    <!-- Student component overrides and aliases to the shared admin palette -->
    <style>
        :root {
            /* Keep component aliases pointed at the shared admin palette. */
            --sv-midnight:       var(--admin-primary-dark);
            --sv-navy-dark:      var(--admin-text-primary);
            --sv-slate-teal:     var(--admin-primary);
            --sv-cyan-btn:       var(--admin-primary);
            --sv-pacific-cyan:   var(--admin-primary-hover);
            --sv-teal:           var(--admin-success);
            --sv-mint:           #34D399;
            --sv-light-green:    #7AE582;
            --sv-aquamarine:     #9FFFCB;
            --sv-pastel-yellow:  #FFFFAB;
            --sv-cyan-bg:        var(--admin-primary-soft);
            --sv-mint-bg:        var(--admin-success-soft);
            --sv-warm-bg:        var(--admin-warning-soft);
            --sv-border:         var(--admin-border);
            --sv-text-main:      var(--admin-body-text);
            --sv-text-muted:     var(--admin-text-secondary);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--admin-page-bg);
            color: var(--sv-text-main);
            font-size: 14px;
        }

        /* Khung nội dung chính: Tinh gọn padding để hiển thị trọn vẹn trên 1 màn hình */
        .content-body {
            padding: 16px 24px !important;
            min-height: calc(100vh - 140px);
        }
        @media (max-width: 768px) {
            .content-body {
                padding: 12px 14px !important;
            }
        }

        /* Thẻ Card: Nhỏ gọn, đường viền thanh lịch, không bị thô dày */
        .card, .admin-kpi-card, .card-premium {
            border: 1px solid var(--admin-border) !important;
            border-radius: 12px !important;
            box-shadow: none !important;
            background: var(--admin-surface);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .card-header, .card-header-premium {
            background-color: var(--admin-surface) !important;
            border-bottom: 1px solid var(--admin-border-soft) !important;
            padding: 12px 18px !important;
            font-weight: 600;
        }
        .card-body {
            padding: 16px 18px !important;
        }
        @media (max-width: 576px) {
            .card-body {
                padding: 14px !important;
            }
        }

        /* Typography uses the shared admin text and primary colors. */
        h1, h2, h3, h4, h5, h6 {
            color: var(--sv-navy-dark);
            letter-spacing: -0.01em;
        }
        .text-primary, .text-primary-custom {
            color: var(--sv-slate-teal) !important;
        }
        .text-sv-midnight {
            color: var(--sv-midnight) !important;
        }
        .text-sv-navy {
            color: var(--sv-navy-dark) !important;
        }
        .text-sv-teal {
            color: var(--sv-teal) !important;
        }

        /* Tables / Bảng dữ liệu */
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background-color: var(--admin-table-header) !important;
            color: var(--admin-surface) !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 12px 16px !important;
            border-bottom: 1px solid var(--admin-border) !important;
        }
        .table tbody td {
            padding: 12px 16px !important;
            font-size: 13.5px !important;
            color: var(--sv-text-main);
            vertical-align: middle;
            border-bottom: 1px solid var(--admin-border) !important;
            line-height: 1.5;
        }

        /* Nút giữ hình dạng hiện tại, dùng màu primary chung với admin. */
        .btn {
            font-size: 13.5px !important;
            font-weight: 600;
            padding: 7px 18px;
            border-radius: 50rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.16s ease-in-out;
        }
        .btn-sm {
            padding: 6px 14px !important;
            font-size: 13px !important;
        }
        .btn-lg {
            padding: 10px 24px !important;
            font-size: 14px !important;
        }
        .btn-primary, .btn-primary-custom {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-dark) 100%) !important;
            border-color: var(--admin-primary-dark) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0, 114, 206, 0.25) !important;
        }
        .btn-primary:hover, .btn-primary-custom:hover {
            background: linear-gradient(135deg, var(--admin-primary-hover) 0%, var(--admin-primary-hover-dark) 100%) !important;
            border-color: var(--admin-primary-hover-dark) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 114, 206, 0.35) !important;
        }
        .btn-success {
            background: linear-gradient(135deg, var(--admin-success) 0%, var(--admin-success-dark) 100%) !important;
            border-color: var(--admin-success-dark) !important;
            color: #ffffff !important;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, var(--admin-success-dark) 0%, #047857 100%) !important;
            border-color: #047857 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            color: var(--admin-primary) !important;
            border-color: var(--admin-primary) !important;
            background-color: var(--admin-primary-button-soft) !important;
        }
        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: var(--admin-primary) !important;
            border-color: transparent !important;
            color: var(--admin-surface) !important;
        }
        .btn-outline-secondary {
            color: var(--admin-secondary-button-text) !important;
            border-color: var(--admin-neutral-border) !important;
            background-color: var(--admin-secondary-button-bg) !important;
        }
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus,
        .btn-outline-secondary:active {
            background-color: var(--admin-secondary-button-hover) !important;
            border-color: transparent !important;
            color: var(--admin-surface) !important;
            box-shadow: 0 4px 12px var(--admin-secondary-button-shadow) !important;
        }

        /* Huy Hiệu Badges */
        .badge {
            font-size: 12.5px !important;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 50rem;
        }
        .badge.bg-primary {
            background-color: var(--admin-badge-primary-bg) !important;
            color: var(--admin-badge-primary-text) !important;
            border: 1px solid var(--admin-badge-primary-border) !important;
        }
        .badge.bg-primary-subtle {
            background-color: var(--admin-badge-primary-bg) !important;
            color: var(--admin-badge-primary-text) !important;
            border: 1px solid var(--admin-badge-primary-border) !important;
        }
        .badge.bg-success {
            background-color: var(--admin-badge-success-bg) !important;
            color: var(--admin-badge-success-text) !important;
            border: 1px solid var(--admin-badge-success-border) !important;
        }
        .badge.bg-success-subtle {
            background-color: var(--admin-badge-success-bg) !important;
            color: var(--admin-badge-success-text) !important;
            border: 1px solid var(--admin-badge-success-border) !important;
        }
        .badge.bg-warning-subtle {
            background-color: var(--admin-badge-warning-bg) !important;
            color: var(--admin-badge-warning-text) !important;
            border: 1px solid var(--admin-badge-warning-border) !important;
        }
        .badge-neutral {
            background-color: var(--admin-neutral-soft) !important;
            color: var(--admin-neutral-text) !important;
            border: 1px solid var(--admin-neutral-border) !important;
        }

        /* Form Inputs */
        .form-control, .form-select {
            font-size: 13.5px !important;
            border-radius: 8px !important;
            border-color: var(--admin-border-input) !important;
            padding: 8px 14px !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--admin-primary) !important;
            box-shadow: 0 0 0 3px var(--admin-focus-ring) !important;
        }
        .input-group-text {
            font-size: 13.5px !important;
            border-radius: 8px !important;
            border-color: var(--admin-border-input) !important;
            padding: 8px 14px !important;
        }

        /* Alerts: Dịu mắt, không gắt */
        .alert {
            border-radius: 10px !important;
            padding: 12px 18px !important;
            font-size: 13.5px !important;
        }
        .alert-warning {
            background-color: var(--admin-alert-warning-bg) !important;
            border-color: var(--admin-alert-warning-border) !important;
            color: var(--admin-alert-warning-text) !important;
        }
        .alert-success {
            background-color: var(--admin-alert-success-bg) !important;
            border-color: var(--admin-alert-success-border) !important;
            color: var(--admin-alert-success-text) !important;
        }
        .alert-primary {
            background-color: var(--admin-alert-primary-bg) !important;
            border-color: var(--admin-alert-primary-border) !important;
            color: var(--admin-alert-primary-text) !important;
        }
        .alert-info {
            background-color: var(--admin-alert-info-bg) !important;
            border-color: var(--admin-alert-info-border) !important;
            color: var(--admin-alert-info-text) !important;
        }

        /* Hiệu ứng mượt mà khi load trang - Không giữ transform sau khi hoàn tất để tránh kẹt stacking context */
        .sv-page-transition {
            animation: svPageFadeIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes svPageFadeIn {
            from {
                opacity: 0;
                transform: translateY(3px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }

        /* Đảm bảo Modal luôn nằm trên Backdrop mờ */
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal {
            z-index: 1055 !important;
        }

        /* Khung cuộn mượt đồng bộ chuẩn màu đại dương cho các danh sách dài */
        .sv-smooth-scroll {
            scrollbar-width: thin;
            scrollbar-color: var(--admin-scrollbar) var(--admin-border-soft);
        }
        .sv-smooth-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .sv-smooth-scroll::-webkit-scrollbar-track {
            background: var(--admin-border-soft);
            border-radius: 4px;
        }
        .sv-smooth-scroll::-webkit-scrollbar-thumb {
            background: var(--admin-scrollbar);
            border-radius: 4px;
        }
        .sv-smooth-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--admin-primary-dark);
        }

        /* Bắt buộc font chữ tối thiểu 13px */
        .small, small, .badge, .form-text, .text-muted, code, .btn-sm, .form-select-sm, .form-control-sm, .phase-pill {
            font-size: 13px !important;
        }
    </style>
    
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

        <!-- Topbar / Breadcrumb Bar -->
        <div class="admin-top-bar">
            <div class="admin-breadcrumb">
                <i class="fa-solid fa-angle-right" style="color: var(--admin-text-muted); font-size: 13px;"></i>
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
                                <div style="font-size: 13px; font-weight: 600; color: var(--admin-text-muted);">Xin chào!</div>
                                <div style="font-size: 14px; font-weight: 700; color: var(--admin-user-text);">{{ $svDisplayName }}</div>
                                <div style="font-size: 13px; color: var(--admin-primary); margin-top: 2px;"><i class="fa-solid fa-user-graduate me-1"></i>Sinh viên</div>
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

            <div class="sv-page-transition">
                @yield('content')
            </div>
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
        // Tự động chuyển tất cả Modal ra document.body để triệt tiêu lỗi bị Backdrop mờ đè lên Modal
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
    });
</script>

@stack('scripts')
@include('partials.modal_preview_decuong')
@include('components.time-machine')
</body>
</html>
