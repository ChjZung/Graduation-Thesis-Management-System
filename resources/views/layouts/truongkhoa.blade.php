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

        <ul class="list-unstyled components">
            <li class="nav-section-label">Tổng Quan</li>
            <li class="{{ request()->routeIs('truongkhoa.dashboard') ? 'active' : '' }}">
                <a href="{{ route('truongkhoa.dashboard') }}">
                    <i class="fa-solid fa-chart-pie"></i> Tổng quan
                </a>
            </li>

            <li class="nav-section-label">Quản Lý Đề Tài</li>
            <li class="{{ request()->routeIs('truongkhoa.duyet_detai.*') ? 'active' : '' }}">
                <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-stamp"></i> Phê duyệt đề tài</span>
                    @if($countChoDuyetKhoa > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">{{ $countChoDuyetKhoa }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-section-label">Quản Lý Cấp Khoa</li>
            <li class="{{ request()->routeIs('truongkhoa.theodoi.*') ? 'active' : '' }}">
                <a href="{{ route('truongkhoa.theodoi.index') }}">
                    <i class="fa-solid fa-chart-line"></i> Theo dõi tiến độ
                </a>
            </li>
            <li class="{{ request()->routeIs('truongkhoa.kehoach.*') ? 'active' : '' }}">
                <a href="{{ route('truongkhoa.kehoach.index') }}">
                    <i class="fa-solid fa-calendar-check"></i> Kế hoạch khóa luận
                </a>
            </li>
            <li class="{{ request()->routeIs('truongkhoa.ketqua.*') ? 'active' : '' }}">
                <a href="{{ route('truongkhoa.ketqua.index') }}">
                    <i class="fa-solid fa-award"></i> Kết quả khóa luận
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
                    @php
                        $unreadTkNoti = \App\Models\ThongBao::where('TrangThai', 'Đã phát hành')->count();
                        $tkGv = \App\Models\GiangVien::getLoggedInGiangVien();
                        $tkName = $tkGv->HoTen ?? (Auth::user()->TenDangNhap ?? 'Trưởng Khoa');
                    @endphp

                    <!-- Notification Bell Dropdown -->
                    <div class="dropdown">
                        <a href="#" class="position-relative text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--huit-blue);">
                            <i class="fa-solid fa-bell" style="font-size: 1.15rem;"></i>
                            @if($unreadTkNoti > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 3px 5px;">{{ $unreadTkNoti }}</span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow" style="width: 360px; max-height: 420px; overflow-y: auto; border-radius: 12px;">
                            <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <strong style="font-size: .85rem; color: #003b73;">Thông Báo Hệ Thống</strong>
                                <span class="badge bg-danger rounded-pill small">{{ $unreadTkNoti }}</span>
                            </div>
                            @php
                                $recentNotifications = \App\Models\ThongBao::where('TrangThai', 'Đã phát hành')->latest()->limit(5)->get();
                            @endphp
                            @forelse($recentNotifications as $nb)
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-semibold text-dark" style="font-size: 0.82rem;">{{ $nb->TieuDe }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ \Illuminate\Support\Str::limit($nb->NoiDung, 75) }}</div>
                                <div class="text-secondary mt-1" style="font-size: 0.68rem;"><i class="fa-regular fa-clock me-1"></i>{{ $nb->created_at ? $nb->created_at->diffForHumans() : '' }}</div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted small">Không có thông báo mới.</div>
                            @endforelse
                        </div>
                    </div>

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
