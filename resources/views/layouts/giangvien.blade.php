<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Giảng Viên') – Quản Lý Khóa Luận Tốt Nghiệp | HUIT</title>
    <meta name="description" content="Cổng Giảng Viên – Hệ thống Quản lý Công tác Khóa luận Tốt nghiệp HUIT">
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
            <a href="{{ route('giangvien.my_tasks') }}" class="sidebar-brand text-decoration-none text-white d-flex align-items-center">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">HUIT – Cổng Giảng Viên</span>
                </div>
            </a>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-chalkboard-user"></i>
                Giảng Viên
            </div>
        </div>

        <ul class="list-unstyled components">
            <!-- 0. Tổng quan -->
            <li class="{{ request()->routeIs('giangvien.dashboard') ? 'active' : '' }}">
                <a href="{{ route('giangvien.dashboard') }}">
                    <i class="fa-solid fa-chart-pie"></i> Tổng quan
                </a>
            </li>

            <!-- 1. Công việc hướng dẫn -->
            <li class="{{ request()->routeIs('giangvien.my_tasks') ? 'active' : '' }}">
                <a href="{{ route('giangvien.my_tasks') }}">
                    <i class="fa-solid fa-bars-staggered"></i> Công việc hướng dẫn
                </a>
            </li>

            <!-- 2. Lịch công việc -->
            <li class="{{ request()->routeIs('giangvien.calendar') ? 'active' : '' }}">
                <a href="{{ route('giangvien.calendar') }}">
                    <i class="fa-regular fa-calendar-days"></i> Lịch công việc
                </a>
            </li>

            <!-- 3. Đề tài của tôi -->
            <li class="{{ request()->routeIs('giangvien.detai.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.detai.index') }}">
                    <i class="fa-solid fa-folder-open"></i> Đề tài của tôi
                </a>
            </li>

            <!-- 4. Duyệt báo cáo tiến độ -->
            <li class="{{ request()->routeIs('giangvien.baocao.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.baocao.index') }}">
                    <i class="fa-solid fa-clipboard-check"></i> Duyệt báo cáo tiến độ
                </a>
            </li>

            <!-- 5. Chấm Điểm Hội Đồng -->
            <li class="{{ request()->routeIs('giangvien.chamdiem.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.chamdiem.index') }}">
                    <i class="fa-solid fa-star"></i> Chấm Điểm Hội Đồng
                </a>
            </li>

        @php
            $currentGvUser = Auth::user();
            $currentGvModel = $currentGvUser ? \App\Models\GiangVien::getLoggedInGiangVien($currentGvUser) : null;
            $countDeCuongCanPB = 0;
            if ($currentGvModel) {
                $countDeCuongCanPB = \App\Models\PhanCongPhanBien::where('MaGV', $currentGvModel->MaGV)
                    ->where('VaiTro', 'Phản biện đề cương')
                    ->where(function($q) {
                        $q->whereNull('KetQua')
                          ->orWhere('TrangThai', 'Đã nộp lại đề cương')
                          ->orWhereHas('deTai', fn($dq) => $dq->where('TrangThai', 'Đã cập nhật đề cương - Chờ phản biện lại'));
                    })
                    ->count();
            }
        @endphp

        <div class="px-2 py-2">
            <!-- 1. TỔNG QUAN & LỊCH TRÌNH -->
            <div class="sidebar-menu-group">
                @php
                    $isGvTongQuan = request()->routeIs('giangvien.dashboard') || request()->routeIs('giangvien.my_tasks') || request()->routeIs('giangvien.calendar') || request()->routeIs('giangvien.thongbao.*');
                @endphp
                <button class="menu-parent-btn {{ $isGvTongQuan ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-gv-tongquan" aria-expanded="{{ $isGvTongQuan ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Tổng Quan &amp; Lịch</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isGvTongQuan ? 'show' : '' }}" id="sub-gv-tongquan">
                    <a href="{{ route('giangvien.dashboard') }}" class="submenu-link {{ request()->routeIs('giangvien.dashboard') ? 'active-sub' : '' }}">
                        <span>Bảng tổng quan</span>
                    </a>
                    <a href="{{ route('giangvien.my_tasks') }}" class="submenu-link {{ request()->routeIs('giangvien.my_tasks') ? 'active-sub' : '' }}">
                        <span>Công việc hướng dẫn</span>
                    </a>
                    <a href="{{ route('giangvien.calendar') }}" class="submenu-link {{ request()->routeIs('giangvien.calendar') ? 'active-sub' : '' }}">
                        <span>Lịch công việc</span>
                    </a>
                    <a href="{{ route('giangvien.thongbao.index') }}" class="submenu-link {{ request()->routeIs('giangvien.thongbao.*') ? 'active-sub' : '' }}">
                        <span>Thông báo hệ thống</span>
                    </a>
                </div>
            </div>

            <!-- 2. QUẢN LÝ ĐỀ TÀI -->
            <div class="sidebar-menu-group">
                @php
                    $isGvDeTai = request()->routeIs('giangvien.detai.*') || request()->routeIs('giangvien.phanbien.*');
                @endphp
                <button class="menu-parent-btn {{ $isGvDeTai ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-gv-detai" aria-expanded="{{ $isGvDeTai ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-folder-open"></i>
                        <span>Quản Lý Đề Tài</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isGvDeTai ? 'show' : '' }}" id="sub-gv-detai">
                    <a href="{{ route('giangvien.detai.index') }}" class="submenu-link {{ request()->routeIs('giangvien.detai.*') ? 'active-sub' : '' }}">
                        <span>Đề tài của tôi</span>
                    </a>
                    <a href="{{ route('giangvien.phanbien.index') }}" class="submenu-link {{ request()->routeIs('giangvien.phanbien.*') ? 'active-sub' : '' }}">
                        <span>Phản biện đề cương</span>
                        @if($countDeCuongCanPB > 0)
                            <span class="badge bg-warning text-dark rounded-pill" style="font-size: 11px !important; padding: 2px 6px !important;">{{ $countDeCuongCanPB }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- 3. ĐÁNH GIÁ & TIẾN ĐỘ -->
            <div class="sidebar-menu-group">
                @php
                    $isGvDanhGia = request()->routeIs('giangvien.baocao.*') || request()->routeIs('giangvien.chamdiem.*') || request()->routeIs('giangvien.xacnhan_baove.*');
                @endphp
                <button class="menu-parent-btn {{ $isGvDanhGia ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-gv-danhgia" aria-expanded="{{ $isGvDanhGia ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Đánh Giá &amp; Báo Cáo</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isGvDanhGia ? 'show' : '' }}" id="sub-gv-danhgia">
                    <a href="{{ route('giangvien.baocao.index') }}" class="submenu-link {{ request()->routeIs('giangvien.baocao.*') ? 'active-sub' : '' }}">
                        <span>Duyệt báo cáo tiến độ</span>
                    </a>
                    <a href="{{ route('giangvien.chamdiem.index') }}" class="submenu-link {{ request()->routeIs('giangvien.chamdiem.*') ? 'active-sub' : '' }}">
                        <span>Chấm điểm hội đồng</span>
                    </a>
                    <a href="{{ route('giangvien.xacnhan_baove.index') }}" class="submenu-link {{ request()->routeIs('giangvien.xacnhan_baove.*') ? 'active-sub' : '' }}">
                        <span>Xác nhận hồ sơ BV</span>
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
                    @yield('page_title', 'Giảng Viên')
                </div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <!-- Notification Bell Dropdown Unified -->
                    @include('partials.header_notifications')

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="gvUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->giangVien->HoTen ?? Auth::user()->TenDangNhap) }}&background=E5F0FA&color=0072CE&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ Auth::user()->giangVien->HoTen ?? Auth::user()->TenDangNhap }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="gvUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1" style="border-bottom: 1px solid var(--columbia-blue);">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--huit-blue-dark);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark);">{{ Auth::user()->giangVien->HoTen ?? Auth::user()->TenDangNhap }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-chalkboard-user me-1" style="color: var(--huit-blue);"></i>Giảng Viên</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-gv').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-gv" action="{{ route('logout') }}" method="POST" class="d-none">
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
@include('components.time-machine')
</body>
</html>