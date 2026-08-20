<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Giảng Viên') – Hệ Thống QLĐA | HUIT</title>
    <meta name="description" content="Cổng Giảng Viên – Hệ thống quản lý đồ án  HUIT">
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
                    <span class="sidebar-subtitle">HUIT – Cổng Giảng Viên</span>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-chalkboard-user"></i>
                Giảng Viên
            </div>
        </div>

        <ul class="list-unstyled components">
            <li class="{{ request()->routeIs('giangvien.calendar') ? 'active' : '' }}">
                <a href="{{ route('giangvien.calendar') }}">
                    <i class="fa-regular fa-calendar-days"></i> Lịch Báo Cáo & Timeline
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.detai.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.detai.index') }}">
                    <i class="fa-solid fa-folder-open"></i> Đề tài của tôi
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.baocao.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.baocao.index') }}">
                    <i class="fa-solid fa-clipboard-check"></i> Duyệt báo cáo tiến độ
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.chamdiem.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.chamdiem.index') }}">
                    <i class="fa-solid fa-star-half-stroke"></i> Chấm Điểm Hội Đồng
                </a>
            </li>

            @php
                $user = Auth::user();
                $unreadGvNoti = 0;
                if ($user) {
                    $unreadGvNoti = \App\Models\NguoiNhanThongBao::where('MaTK', $user->MaTK)->where('DaDoc', false)->count();
                }
            @endphp
            <li class="{{ request()->routeIs('giangvien.thongbao.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.thongbao.index') }}" class="d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-bell"></i> Thông báo</span>
                    @if($unreadGvNoti > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">{{ $unreadGvNoti }}</span>
                    @endif
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
                    <!-- Notification Bell Dropdown GV -->
                    <div class="dropdown">
                        <a href="#" class="position-relative text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--huit-blue);">
                            <i class="fa-solid fa-bell" style="font-size: 1.2rem;"></i>
                            @if($unreadGvNoti > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;padding:3px 5px;">{{ $unreadGvNoti }}</span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow" style="width:360px;max-height:420px;overflow-y:auto;border-radius:12px;">
                            <div class="px-3 py-2 border-bottom"><strong style="font-size:.85rem;">Thông Báo</strong></div>
                            @php
                                $gvRecentNoti = \App\Models\NguoiNhanThongBao::where('MaTK', Auth::user()->MaTK)->orderBy('created_at','desc')->limit(6)->get();
                            @endphp
                            @forelse($gvRecentNoti as $noti)
                            <a href="#" class="dropdown-item px-3 py-2 border-bottom" style="{{ !$noti->DaDoc ? 'background:#f0f7ff;' : '' }}">
                                <div class="d-flex gap-2 align-items-start">
                                    <i class="fa-solid {{ $noti->icon }} mt-1" style="font-size:.85rem;flex-shrink:0;"></i>
                                    <div>
                                        <div class="fw-semibold" style="font-size:.82rem;">{{ $noti->TieuDe }}</div>
                                        <div class="text-muted" style="font-size:.72rem;">{{ \Carbon\Carbon::parse($noti->created_at)->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                            @empty
                            <div class="text-center py-4 text-muted" style="font-size:.82rem;">Chưa có thông báo nào.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- User Dropdown -->

                    <!-- Notification bell -->
                    @if($unreadGvNoti > 0)
                    <a href="{{ route('giangvien.thongbao.index') }}" class="position-relative text-decoration-none"
                       style="color: var(--eight-blue);" title="{{ $unreadGvNoti }} thông báo chưa đọc">
                        <i class="fa-solid fa-bell" style="font-size: 1.1rem; color: var(--huit-blue);"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size: 0.6rem; padding: 3px 5px;">{{ $unreadGvNoti }}</span>
                    </a>
                    @endif

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