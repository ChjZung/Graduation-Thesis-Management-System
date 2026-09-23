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

        <ul class="list-unstyled components">
            <!-- Dashboard -->
            <li class="nav-section-label">Tổng Quan</li>
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>

            <!-- Kế Hoạch & Lịch -->
            <li class="nav-section-label">Kế Hoạch & Tiến Độ</li>
            <li class="{{ request()->routeIs('admin.kehoach.*') ? 'active' : '' }}">
                <a href="{{ route('admin.kehoach.index') }}">
                    <i class="fa-solid fa-calendar-check"></i> Quản lý kế hoạch
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.quydinh.*') ? 'active' : '' }}">
                <a href="{{ route('admin.quydinh.index') }}">
                    <i class="fa-solid fa-scale-balanced"></i> Quy định khóa luận
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.theodoi.*') ? 'active' : '' }}">
                <a href="{{ route('admin.theodoi.index') }}">
                    <i class="fa-solid fa-chart-line"></i> Theo dõi tiến độ
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.calendar') ? 'active' : '' }}">
                <a href="{{ route('admin.calendar') }}">
                    <i class="fa-regular fa-calendar-days"></i> Lịch quy trình
                </a>
            </li>

            <!-- Xét Duyệt -->
            <li class="nav-section-label">Xét Duyệt Đề Tài</li>
            <li class="{{ request()->routeIs('admin.duyet_detai.*') ? 'active' : '' }}">
                <a href="{{ route('admin.duyet_detai.index') }}">
                    <i class="fa-solid fa-file-signature"></i> Duyệt đề tài GV
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.duyet_dangky.*') ? 'active' : '' }}">
                <a href="{{ route('admin.duyet_dangky.index') }}">
                    <i class="fa-solid fa-user-check"></i> Duyệt đăng ký SV
                </a>
            </li>

            <!-- Bảo Vệ & Hội Đồng -->
            <li class="nav-section-label">Bảo Vệ & Hội Đồng</li>
            <li class="{{ request()->routeIs('admin.hosoBaoVe.*') ? 'active' : '' }}">
                <a href="{{ route('admin.hosoBaoVe.index') }}">
                    <i class="fa-solid fa-folder-check"></i> Hồ sơ bảo vệ
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.hoidong.*') ? 'active' : '' }}">
                <a href="{{ route('admin.hoidong.index') }}">
                    <i class="fa-solid fa-landmark"></i> Hội đồng bảo vệ
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.ketqua.*') ? 'active' : '' }}">
                <a href="{{ route('admin.ketqua.index') }}">
                    <i class="fa-solid fa-square-poll-vertical"></i> Bảng điểm &amp; Kết quả
                </a>
            </li>

            <!-- Quản Lý Tài Khoản -->
            <li class="nav-section-label">Quản Lý Người Dùng</li>
            <li class="{{ request()->routeIs('admin.taikhoan.*') ? 'active' : '' }}">
                <a href="{{ route('admin.taikhoan.index') }}">
                    <i class="fa-solid fa-users-gear"></i> Tài khoản hệ thống
                </a>
            </li>
            <li class="{{ request()->routeIs('sinhvien.index') || request()->routeIs('sinhvien.create') || request()->routeIs('sinhvien.edit') || request()->routeIs('sinhvien.show') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.index') }}">
                    <i class="fa-solid fa-user-graduate"></i> Quản lý Sinh viên
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.sinhvien.dieu_kien') ? 'active' : '' }}">
                <a href="{{ route('admin.sinhvien.dieu_kien') }}">
                    <i class="fa-solid fa-user-check"></i> SV Đủ điều kiện KL
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.index') }}">
                    <i class="fa-solid fa-chalkboard-user"></i> Giảng viên
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.yeucau.*') ? 'active' : '' }}">
                <a href="{{ route('admin.yeucau.index') }}">
                    <i class="fa-solid fa-key"></i> Đổi mật khẩu
                </a>
            </li>

            <!-- Quản Lý Danh Mục -->
            <li class="nav-section-label">Danh Mục Hệ Thống</li>
            <li class="{{ request()->routeIs('khoa.*') ? 'active' : '' }}">
                <a href="{{ route('khoa.index') }}">
                    <i class="fa-solid fa-university"></i> Khoa
                </a>
            </li>
            <li class="{{ request()->routeIs('bomon.*') ? 'active' : '' }}">
                <a href="{{ route('bomon.index') }}">
                    <i class="fa-solid fa-building"></i> Bộ môn
                </a>
            </li>
            <li class="{{ request()->routeIs('nganh.*') ? 'active' : '' }}">
                <a href="{{ route('nganh.index') }}">
                    <i class="fa-solid fa-book-open"></i> Ngành
                </a>
            </li>
            <li class="{{ request()->routeIs('lop.*') ? 'active' : '' }}">
                <a href="{{ route('lop.index') }}">
                    <i class="fa-solid fa-users-rectangle"></i> Lớp
                </a>
            </li>
            <li class="{{ request()->routeIs('hocky.*') ? 'active' : '' }}">
                <a href="{{ route('hocky.index') }}">
                    <i class="fa-solid fa-clock"></i> Học kỳ
                </a>
            </li>

            <!-- Thông Tin Hệ Thống -->
            <li class="nav-section-label">Thông Báo</li>
            <li class="{{ request()->routeIs('thongbao.*') ? 'active' : '' }}">
                <a href="{{ route('thongbao.index') }}">
                    <i class="fa-solid fa-bell"></i> Thông báo hệ thống
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
                <!-- Notification Bell Admin -->
                @php
                    $adminRecentNoti = \App\Models\ThongBao::where('TrangThai', 'Đã phát hành')->orderBy('created_at', 'desc')->limit(6)->get();
                    $adminUnread = $adminRecentNoti->count();
                @endphp
                <div class="dropdown">
                    <a href="#" class="position-relative text-decoration-none dropdown-toggle no-caret" data-bs-toggle="dropdown" aria-expanded="false" style="color: #0072ce;">
                        <i class="fa-solid fa-bell" style="font-size: 1.25rem;"></i>
                        @if($adminUnread > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;padding:3px 5px;">{{ $adminUnread }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow border-0" style="width:360px;max-height:420px;overflow-y:auto;border-radius:12px;">
                        <div class="px-3 py-2 border-bottom"><strong style="font-size:.85rem;color:#003b73;">Thông Báo Hệ Thống</strong></div>
                        @forelse($adminRecentNoti as $noti)
                        <div class="dropdown-item px-3 py-2 border-bottom">
                            <div class="d-flex gap-2 align-items-start">
                                <i class="fa-solid fa-bullhorn text-primary mt-1" style="font-size:.85rem;flex-shrink:0;"></i>
                                <div>
                                    <div class="fw-semibold text-wrap" style="font-size:.82rem;">{{ $noti->TieuDe }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ \Carbon\Carbon::parse($noti->created_at ?? $noti->NgayTao)->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted" style="font-size:.82rem;">Chưa có thông báo nào.</div>
                        @endforelse
                    </div>
                </div>

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
