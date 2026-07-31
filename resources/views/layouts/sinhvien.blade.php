<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Sinh Viên') – Hệ Thống QLĐA | HUIT</title>
    <meta name="description" content="Cổng Sinh Viên – Hệ thống quản lý đồ án  HUIT">
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
                <i class="fa-solid fa-user-graduate"></i>
                Sinh Viên
            </div>
        </div>

        <ul class="list-unstyled components">
            <li class="{{ request()->routeIs('sinhvien.nhom.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.nhom.index') }}">
                    <i class="fa-solid fa-users"></i> Nhóm của tôi
                </a>
            </li>
            <li class="{{ request()->routeIs('sinhvien.dangky.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.dangky.index') }}">
                    <i class="fa-solid fa-clipboard-list"></i> Đăng ký đề tài
                </a>
            </li>
            <li class="{{ request()->routeIs('sinhvien.baocao.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.baocao.index') }}">
                    <i class="fa-solid fa-file-invoice"></i> Báo cáo tiến độ
                </a>
            </li>
            <li class="{{ request()->routeIs('sinhvien.sanpham.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.sanpham.index') }}">
                    <i class="fa-solid fa-box-open"></i> Nộp sản phẩm
                </a>
            </li>

            @php
                $user = Auth::user();
                $sv = $user ? \App\Models\SinhVien::where('MaTK', $user->MaTK)->first() : null;
                $unreadNotiCount = 0;
                if ($sv) {
                    $adminTKs = \App\Models\TaiKhoan::where('MaVaiTro', 1)->pluck('MaTK')->toArray();
                    $studentLhpIds = \App\Models\SinhVienLopHocPhan::where('MaSV', $sv->MaSV)->pluck('MaLopHP')->toArray();
                    
                    $lecturerGvIdsFromLh = \App\Models\PhanCongHuongDanLop::where('MaLop', $sv->MaLop)->pluck('MaGV')->toArray();
                    $lecturerGvIdsFromLhp = \App\Models\LopHocPhan::whereIn('MaLopHP', $studentLhpIds)->whereNotNull('MaGV')->pluck('MaGV')->toArray();
                    $allGvIds = array_unique(array_merge($lecturerGvIdsFromLh, $lecturerGvIdsFromLhp));
                    $lecturerTKs = \App\Models\GiangVien::whereIn('MaGV', $allGvIds)->pluck('MaTK')->toArray();

                    $unreadNotiCount = \App\Models\ThongBao::where(function($q) use ($adminTKs, $sv, $studentLhpIds, $lecturerTKs) {
                        $q->whereIn('MaTK', $adminTKs);
                        if ($sv->MaLop) {
                            $q->orWhere('MaLop', $sv->MaLop);
                        }
                        if (!empty($studentLhpIds)) {
                            $q->orWhereIn('MaLopHP', $studentLhpIds);
                        }
                        if (!empty($lecturerTKs)) {
                            $q->orWhere(function($subQ) use ($lecturerTKs) {
                                $subQ->whereIn('MaTK', $lecturerTKs)
                                     ->whereNull('MaLop')
                                     ->whereNull('MaLopHP');
                            });
                        }
                    })
                    ->where('DaDoc', false)
                    ->count();
                }
            @endphp
            <li class="{{ request()->routeIs('sinhvien.thongbao.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.thongbao.index') }}" class="d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-bell"></i> Thông báo</span>
                    @if($unreadNotiCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">{{ $unreadNotiCount }}</span>
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
                    <!-- Notification bell -->
                    @if($unreadNotiCount > 0)
                    <a href="{{ route('sinhvien.thongbao.index') }}" class="position-relative text-decoration-none"
                       title="{{ $unreadNotiCount }} thông báo chưa đọc">
                        <i class="fa-solid fa-bell" style="font-size: 1.1rem; color: var(--huit-blue);"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size: 0.6rem; padding: 3px 5px;">{{ $unreadNotiCount }}</span>
                    </a>
                    @endif

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