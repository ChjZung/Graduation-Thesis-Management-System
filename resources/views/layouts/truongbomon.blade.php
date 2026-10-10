<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Trưởng Bộ Môn') – Quản Lý Khóa Luận Tốt Nghiệp | HUIT</title>
    <meta name="description" content="Cổng Trưởng Bộ Môn – Hệ thống Quản lý Công tác Khóa luận Tốt nghiệp HUIT">
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
            <a href="{{ route('truongbomon.dashboard') }}" class="sidebar-brand text-decoration-none text-white d-flex align-items-center">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">HUIT – Trưởng Bộ Môn</span>
                </div>
            </a>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-user-tie"></i>
                Trưởng Bộ Môn
            </div>
        </div>

        @php
            $sidebarUser = Auth::user();
            $sidebarGv = \App\Models\GiangVien::getLoggedInGiangVien($sidebarUser);
            $sidebarBm = null;
            if ($sidebarGv && $sidebarGv->MaBoMon) {
                $sidebarBm = \App\Models\BoMon::where('MaBoMon', $sidebarGv->MaBoMon)->first();
            } elseif ($sidebarUser && preg_match('/^TBM_[A-Z0-9]+_([A-Z0-9]+)_/i', $sidebarUser->TenDangNhap, $m)) {
                $sidebarBm = \App\Models\BoMon::where('MaBoMon', $m[1])->first();
            }
            $sidebarMaBm = $sidebarBm ? $sidebarBm->MaBoMon : 'CNPM';
            $sidebarGvIds = \App\Models\GiangVien::where('MaBoMon', $sidebarMaBm)->pluck('MaGV');
            
            // Đếm đề tài đề xuất sơ bộ chờ duyệt cấp Bộ môn
            $countChoDuyetDeXuat = \App\Models\DeTai::whereIn('MaGV', $sidebarGvIds)
                ->where('TrangThai', 'Chờ duyệt cấp Bộ môn')
                ->count();

            // Đếm đề tài đã nộp đề cương chưa phân công phản biện
            $countChuaPhanCong = \App\Models\DeTai::whereIn('MaGV', $sidebarGvIds)
                ->whereIn('TrangThai', ['Đã nộp đề cương - Chờ phân công PB', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])
                ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'))
                ->count();

            // Đếm đề tài đã phản biện xong đang chờ TBM duyệt công bố
            $countChoDuyetDeCuong = \App\Models\DeTai::whereIn('MaGV', $sidebarGvIds)
                ->where(function($q) {
                    $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                      ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Đạt'));
                })->where('TrangThai', '!=', 'Đã công bố')
                ->count();
        @endphp

        <div class="px-2 py-2">
            <!-- 1. TỔNG QUAN -->
            <div class="sidebar-menu-group">
                @php
                    $isTbmTongQuan = request()->routeIs('truongbomon.dashboard') || request()->routeIs('thongbao.*');
                @endphp
                <button class="menu-parent-btn {{ $isTbmTongQuan ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tbm-tongquan" aria-expanded="{{ $isTbmTongQuan ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Tổng Quan</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTbmTongQuan ? 'show' : '' }}" id="sub-tbm-tongquan">
                    <a href="{{ route('truongbomon.dashboard') }}" class="submenu-link {{ request()->routeIs('truongbomon.dashboard') ? 'active-sub' : '' }}">
                        <span>Bảng tổng quan</span>
                    </a>
                    <a href="{{ route('thongbao.index') }}" class="submenu-link {{ request()->routeIs('thongbao.*') ? 'active-sub' : '' }}">
                        <span>Thông báo hệ thống</span>
                    </a>
                </div>
            </div>

            <!-- 2. QUẢN LÝ & XỬ LÝ ĐỀ TÀI -->
            <div class="sidebar-menu-group">
                @php
                    $isPheDuyetDeTaiActive = request()->routeIs('truongbomon.phe_duyet_detai.*') || request()->routeIs('truongbomon.duyet_detai.*');
                    $isTbmDeTai = $isPheDuyetDeTaiActive || request()->routeIs('truongbomon.phancong.*') || request()->routeIs('truongbomon.duyet_decuong.*');
                @endphp
                <button class="menu-parent-btn {{ $isTbmDeTai ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tbm-detai" aria-expanded="{{ $isTbmDeTai ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Xử Lý Đề Tài</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTbmDeTai ? 'show' : '' }}" id="sub-tbm-detai">
                    <a href="{{ route('truongbomon.phe_duyet_detai.index') }}" class="submenu-link {{ $isPheDuyetDeTaiActive ? 'active-sub' : '' }}">
                        <span>Phê duyệt đề tài</span>
                        @if($countChoDuyetDeXuat > 0)
                            <span class="badge bg-danger rounded-pill" style="font-size: 11px !important; padding: 2px 6px !important;">{{ $countChoDuyetDeXuat }}</span>
                        @endif
                    </a>
                    <a href="{{ route('truongbomon.phancong.index') }}" class="submenu-link {{ request()->routeIs('truongbomon.phancong.*') ? 'active-sub' : '' }}">
                        <span>Phân công phản biện</span>
                        @if($countChuaPhanCong > 0)
                            <span class="badge bg-warning text-dark rounded-pill" style="font-size: 11px !important; padding: 2px 6px !important;">{{ $countChuaPhanCong }}</span>
                        @endif
                    </a>
                    <a href="{{ route('truongbomon.duyet_decuong.index') }}" class="submenu-link {{ request()->routeIs('truongbomon.duyet_decuong.*') ? 'active-sub' : '' }}">
                        <span>Duyệt đề cương</span>
                        @if($countChoDuyetDeCuong > 0)
                            <span class="badge bg-success rounded-pill" style="font-size: 11px !important; padding: 2px 6px !important;">{{ $countChoDuyetDeCuong }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- 3. GIÁM SÁT TIẾN ĐỘ -->
            <div class="sidebar-menu-group">
                @php
                    $isTbmTheoDoi = request()->routeIs('truongbomon.theodoi.*');
                @endphp
                <button class="menu-parent-btn {{ $isTbmTheoDoi ? 'active-parent' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sub-tbm-theodoi" aria-expanded="{{ $isTbmTheoDoi ? 'true' : 'false' }}">
                    <span class="parent-icon-title">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Giám Sát Bộ Môn</span>
                    </span>
                    <i class="fa-solid fa-chevron-down chevron-arrow"></i>
                </button>
                <div class="collapse submenu-collapse {{ $isTbmTheoDoi ? 'show' : '' }}" id="sub-tbm-theodoi">
                    <a href="{{ route('truongbomon.theodoi.index') }}" class="submenu-link {{ request()->routeIs('truongbomon.theodoi.*') ? 'active-sub' : '' }}">
                        <span>Theo dõi tiến độ</span>
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
                        $tbmGv = \App\Models\GiangVien::getLoggedInGiangVien();
                        $tbmName = $tbmGv->HoTen ?? (Auth::user()->TenDangNhap ?? 'Trưởng Bộ Môn');
                    @endphp

                    <!-- Notification Bell Dropdown Unified -->
                    @include('partials.header_notifications')

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="tbmUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($tbmName) }}&background=E0F2FE&color=0072CE&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ $tbmName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="tbmUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1" style="border-bottom: 1px solid var(--columbia-blue);">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--huit-blue-dark);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark);">{{ $tbmName }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-user-tie me-1 text-warning"></i>Trưởng Bộ Môn</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-tbm').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-tbm" action="{{ route('logout') }}" method="POST" class="d-none">
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
