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
    
    <!-- Student Portal Unified Theme (Tone Màu Thủy Triều Hải Quân - Teal/Cyan/Slate Chuẩn Theo Bảng Màu) -->
    <style>
        :root {
            /* Tone màu người dùng cung cấp: Deep Teal / Ocean Cyan / Persian Green / Mint / Slate Matrix */
            --sv-midnight:       #004E64; /* Xanh ngọc bóng đêm / Tiêu đề chính */
            --sv-navy-dark:      #003F5F; /* Xanh than thủy triều / Text nhấn mạnh */
            --sv-slate-teal:     #00617A; /* Xanh slate than / Tiêu đề phụ */
            --sv-cyan-btn:       #00838C; /* Xanh đại dương trầm / Nút bấm chính & Icon */
            --sv-pacific-cyan:   #00A5CF; /* Xanh Thái Bình Dương / Nút bấm hành động */
            --sv-teal:           #25A18E; /* Xanh Ba Tư / Trạng thái thành công & Tiến độ */
            --sv-mint:           #78C59B; /* Xanh bạc hà dịu / Điểm nhấn nhẹ nhàng */
            --sv-light-green:    #7AE582; /* Xanh non tươi mới */
            --sv-aquamarine:     #9FFFCB; /* Xanh ngọc pastel */
            --sv-pastel-yellow:  #FFFFAB; /* Vàng kem dịu / Chờ duyệt */
            --sv-cyan-bg:        #e6f7fa; /* Nền xanh pastel dịu mắt */
            --sv-mint-bg:        #e8f8f2; /* Nền xanh mint pastel */
            --sv-warm-bg:        #fefce8; /* Nền cảnh báo pastel nhẹ */
            --sv-border:         #e2e8f0;
            --sv-text-main:      #1e293b;
            --sv-text-muted:     #52637a;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #f8fafc;
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
            border: 1px solid var(--sv-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px 0 rgba(0, 63, 95, 0.04), 0 1px 2px -1px rgba(0, 63, 95, 0.02) !important;
            background: #ffffff;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .card-header, .card-header-premium {
            background-color: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
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

        /* Typography - Tông màu trầm sang trọng từ bảng màu người dùng */
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
            background-color: #f8fafc !important;
            color: var(--sv-slate-teal) !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 12px 16px !important;
            border-bottom: 1px solid var(--sv-border) !important;
        }
        .table tbody td {
            padding: 12px 16px !important;
            font-size: 13.5px !important;
            color: var(--sv-text-main);
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9 !important;
            line-height: 1.5;
        }

        /* Nút Bấm: Chuẩn tone màu Teal / Ocean Cyan, không dùng màu AI */
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
            background-color: var(--sv-cyan-btn) !important;
            border-color: var(--sv-cyan-btn) !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(0, 131, 140, 0.18) !important;
        }
        .btn-primary:hover, .btn-primary-custom:hover {
            background-color: var(--sv-slate-teal) !important;
            border-color: var(--sv-slate-teal) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 97, 122, 0.22) !important;
        }
        .btn-success {
            background-color: var(--sv-teal) !important;
            border-color: var(--sv-teal) !important;
            color: #ffffff !important;
        }
        .btn-success:hover {
            background-color: #1d8172 !important;
            border-color: #1d8172 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            color: var(--sv-slate-teal) !important;
            border-color: var(--sv-cyan-btn) !important;
            background-color: #ffffff;
        }
        .btn-outline-primary:hover {
            background-color: var(--sv-cyan-bg) !important;
            border-color: var(--sv-slate-teal) !important;
            color: var(--sv-midnight) !important;
        }
        .btn-outline-secondary {
            color: var(--sv-text-muted) !important;
            border-color: #cbd5e1 !important;
            background-color: #ffffff;
        }
        .btn-outline-secondary:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: var(--sv-text-main) !important;
        }

        /* Huy Hiệu Badges */
        .badge {
            font-size: 12.5px !important;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 50rem;
        }
        .badge.bg-primary {
            background-color: var(--sv-cyan-btn) !important;
            color: #ffffff !important;
        }
        .badge.bg-primary-subtle {
            background-color: var(--sv-cyan-bg) !important;
            color: var(--sv-midnight) !important;
            border: 1px solid #b3e5ed !important;
        }
        .badge.bg-success {
            background-color: var(--sv-teal) !important;
            color: #ffffff !important;
        }
        .badge.bg-success-subtle {
            background-color: var(--sv-mint-bg) !important;
            color: #115e59 !important;
            border: 1px solid #b7ebd9 !important;
        }
        .badge.bg-warning-subtle {
            background-color: var(--sv-warm-bg) !important;
            color: #854d0e !important;
            border: 1px solid #fef08a !important;
        }
        .badge-neutral {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Form Inputs */
        .form-control, .form-select {
            font-size: 13.5px !important;
            border-radius: 8px !important;
            border-color: #cbd5e1 !important;
            padding: 8px 14px !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--sv-cyan-btn) !important;
            box-shadow: 0 0 0 3px rgba(0, 131, 140, 0.15) !important;
        }
        .input-group-text {
            font-size: 13.5px !important;
            border-radius: 8px !important;
            border-color: #cbd5e1 !important;
            padding: 8px 14px !important;
        }

        /* Alerts: Dịu mắt, không gắt */
        .alert {
            border-radius: 10px !important;
            padding: 12px 18px !important;
            font-size: 13.5px !important;
        }
        .alert-warning {
            background-color: var(--sv-warm-bg) !important;
            border-color: #fef08a !important;
            color: #854d0e !important;
        }
        .alert-success {
            background-color: var(--sv-mint-bg) !important;
            border-color: #b7ebd9 !important;
            color: #115e59 !important;
        }
        .alert-primary, .alert-info {
            background-color: var(--sv-cyan-bg) !important;
            border-color: #b3e5ed !important;
            color: var(--sv-midnight) !important;
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
            scrollbar-color: var(--sv-cyan-btn) #f1f5f9;
        }
        .sv-smooth-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .sv-smooth-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .sv-smooth-scroll::-webkit-scrollbar-thumb {
            background: var(--sv-cyan-btn);
            border-radius: 4px;
        }
        .sv-smooth-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--sv-slate-teal);
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
                <i class="fa-solid fa-angle-right" style="color: #64748b; font-size: 13px;"></i>
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
                                <div style="font-size: 13px; color: #0284c7; margin-top: 2px;"><i class="fa-solid fa-user-graduate me-1"></i>Sinh viên</div>
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