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

        $isTkTongQuan = request()->routeIs('truongkhoa.dashboard') || request()->routeIs('thongbao.*');
        $isPheDuyetTkActive = request()->routeIs('truongkhoa.duyet_detai.*') || request()->routeIs('truongkhoa.bomon.*') || request()->routeIs('truongkhoa.phe_duyet_detai.*') || request()->routeIs('truongkhoa.thongke.*');
        $isTkDeTai = $isPheDuyetTkActive || request()->routeIs('truongkhoa.theodoi.*');
        $isTkKeHoach = request()->routeIs('truongkhoa.kehoach.*') || request()->routeIs('truongkhoa.ketqua.*');

        $tkMenuGroups = [
            [
                'id' => 'sub-tk-tongquan',
                'title' => 'Tổng Quan',
                'icon' => 'fa-solid fa-chart-pie',
                'active' => $isTkTongQuan,
                'items' => [
                    [
                        'title' => 'Bảng tổng quan',
                        'url' => route('truongkhoa.dashboard'),
                        'active' => request()->routeIs('truongkhoa.dashboard'),
                    ],
                    [
                        'title' => 'Thông báo hệ thống',
                        'url' => route('truongkhoa.thongbao.index'),
                        'active' => request()->routeIs('truongkhoa.thongbao.*') || request()->routeIs('thongbao.*'),
                    ],
                ],
            ],
            [
                'id' => 'sub-tk-detai',
                'title' => 'Quản Lý Đề Tài',
                'icon' => 'fa-solid fa-stamp',
                'active' => $isTkDeTai,
                'items' => [
                    [
                        'title' => 'Phê duyệt đề tài',
                        'url' => route('truongkhoa.duyet_detai.index'),
                        'active' => $isPheDuyetTkActive,
                        'badge_count' => $countChoDuyetKhoa,
                        'badge_class' => 'bg-danger',
                    ],
                    [
                        'title' => 'Theo dõi tiến độ',
                        'url' => route('truongkhoa.theodoi.index'),
                        'active' => request()->routeIs('truongkhoa.theodoi.*'),
                    ],
                ],
            ],
            [
                'id' => 'sub-tk-hoidong',
                'title' => 'Hội Đồng Bảo Vệ',
                'icon' => 'fa-solid fa-landmark',
                'active' => request()->routeIs('truongkhoa.hoidong.*'),
                'items' => [
                    [
                        'title' => 'Quản lý hội đồng',
                        'url' => route('truongkhoa.hoidong.index'),
                        'active' => request()->routeIs('truongkhoa.hoidong.*'),
                    ],
                ],
            ],
            [
                'id' => 'sub-tk-kehoach',
                'title' => 'Kế Hoạch & Kết Quả',
                'icon' => 'fa-solid fa-calendar-check',
                'active' => $isTkKeHoach,
                'items' => [
                    [
                        'title' => 'Kế hoạch khóa luận',
                        'url' => route('truongkhoa.kehoach.index'),
                        'active' => request()->routeIs('truongkhoa.kehoach.*'),
                    ],
                    [
                        'title' => 'Kết quả khóa luận',
                        'url' => route('truongkhoa.ketqua.index'),
                        'active' => request()->routeIs('truongkhoa.ketqua.*'),
                    ],
                ],
            ],
        ];
    @endphp

    @include('partials.sidebar_canbo', [
        'dashboardRoute' => route('truongkhoa.dashboard'),
        'roleSubtitle' => 'HUIT – Trưởng Khoa',
        'roleBadgeIcon' => 'fa-solid fa-graduation-cap',
        'roleBadgeText' => 'Trưởng Khoa',
        'menuGroups' => $tkMenuGroups,
    ])
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
                    <a href="{{ route('truongkhoa.dashboard') }}">Trang chủ</a>
                    <span class="separator">&gt;</span>
                    <span>Khóa luận tốt nghiệp</span>
                    <span class="separator">&gt;</span>
                    <span class="current-page">@yield('page_title', 'Trưởng Khoa')</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @php
                        $tkGv = \App\Models\GiangVien::getLoggedInGiangVien();
                        $tkName = $tkGv->HoTen ?? (Auth::user()->TenDangNhap ?? 'Trưởng Khoa');
                    @endphp

                    <!-- Notification Bell Dropdown Unified -->
                    @include('partials.header_notifications')

                    <!-- User Profile Pill -->
                    <div class="dropdown">
                        <a class="admin-user-pill dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="tkUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($tkName) }}&background=0072CE&color=FFFFFF&bold=true&size=64"
                                 alt="Avatar">
                            <span>{{ $tkName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border" style="border-radius: 8px; min-width: 220px;" aria-labelledby="tkUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1 border-bottom">
                                    <div style="font-size: 13px; font-weight: 600; color: #64748b;">Xin chào!</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #003b73;">{{ $tkName }}</div>
                                    <div style="font-size: 13px; color: #dc2626; margin-top: 2px;"><i class="fa-solid fa-graduation-cap me-2"></i>Trưởng Khoa</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-tk').submit();">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form-tk" action="{{ route('logout') }}" method="POST" class="d-none">
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
