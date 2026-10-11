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

        $isTbmTongQuan = request()->routeIs('truongbomon.dashboard') || request()->routeIs('thongbao.*');
        $isPheDuyetDeTaiActive = request()->routeIs('truongbomon.phe_duyet_detai.*') || request()->routeIs('truongbomon.duyet_detai.*');
        $isTbmDeTai = $isPheDuyetDeTaiActive || request()->routeIs('truongbomon.phancong.*') || request()->routeIs('truongbomon.duyet_decuong.*');
        $isTbmTheoDoi = request()->routeIs('truongbomon.theodoi.*');

        $tbmMenuGroups = [
            [
                'id' => 'sub-tbm-tongquan',
                'title' => 'Tổng Quan',
                'icon' => 'fa-solid fa-chart-pie',
                'active' => $isTbmTongQuan,
                'items' => [
                    [
                        'title' => 'Bảng tổng quan',
                        'url' => route('truongbomon.dashboard'),
                        'active' => request()->routeIs('truongbomon.dashboard'),
                    ],
                    [
                        'title' => 'Thông báo hệ thống',
                        'url' => route('thongbao.index'),
                        'active' => request()->routeIs('thongbao.*'),
                    ],
                ],
            ],
            [
                'id' => 'sub-tbm-detai',
                'title' => 'Xử Lý Đề Tài',
                'icon' => 'fa-solid fa-list-check',
                'active' => $isTbmDeTai,
                'items' => [
                    [
                        'title' => 'Phê duyệt đề tài',
                        'url' => route('truongbomon.phe_duyet_detai.index'),
                        'active' => $isPheDuyetDeTaiActive,
                        'badge_count' => $countChoDuyetDeXuat,
                        'badge_class' => 'bg-danger',
                    ],
                    [
                        'title' => 'Phân công phản biện',
                        'url' => route('truongbomon.phancong.index'),
                        'active' => request()->routeIs('truongbomon.phancong.*'),
                        'badge_count' => $countChuaPhanCong,
                        'badge_class' => 'bg-warning text-dark',
                    ],
                    [
                        'title' => 'Duyệt đề cương',
                        'url' => route('truongbomon.duyet_decuong.index'),
                        'active' => request()->routeIs('truongbomon.duyet_decuong.*'),
                        'badge_count' => $countChoDuyetDeCuong,
                        'badge_class' => 'bg-success',
                    ],
                ],
            ],
            [
                'id' => 'sub-tbm-theodoi',
                'title' => 'Giám Sát Bộ Môn',
                'icon' => 'fa-solid fa-chart-line',
                'active' => $isTbmTheoDoi,
                'items' => [
                    [
                        'title' => 'Theo dõi tiến độ',
                        'url' => route('truongbomon.theodoi.index'),
                        'active' => request()->routeIs('truongbomon.theodoi.*'),
                    ],
                ],
            ],
        ];
    @endphp

    @include('partials.sidebar_canbo', [
        'dashboardRoute' => route('truongbomon.dashboard'),
        'roleSubtitle' => 'HUIT – Trưởng Bộ Môn',
        'roleBadgeIcon' => 'fa-solid fa-user-tie',
        'roleBadgeText' => 'Trưởng Bộ Môn',
        'menuGroups' => $tbmMenuGroups,
    ])
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
                <a href="{{ route('truongbomon.dashboard') }}">Trang chủ</a>
                <span class="separator">&gt;</span>
                <span>Khóa luận tốt nghiệp</span>
                <span class="separator">&gt;</span>
                <span class="current-page">@yield('page_title', 'Trưởng Bộ Môn')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                @php
                    $tbmGv = \App\Models\GiangVien::getLoggedInGiangVien();
                    $tbmName = $tbmGv->HoTen ?? (Auth::user()->TenDangNhap ?? 'Trưởng Bộ Môn');
                @endphp

                <!-- Notification Bell Dropdown Unified -->
                @include('partials.header_notifications')

                <!-- User Profile Pill -->
                <div class="dropdown">
                    <a class="admin-user-pill dropdown-toggle text-decoration-none"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       id="tbmUserDropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($tbmName) }}&background=0072CE&color=FFFFFF&bold=true&size=64"
                             alt="Avatar">
                        <span>{{ $tbmName }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border" style="border-radius: 8px; min-width: 220px;" aria-labelledby="tbmUserDropdown">
                        <li>
                            <div class="px-3 py-2 mb-1 border-bottom">
                                <div style="font-size: 13px; font-weight: 600; color: #64748b;">Xin chào!</div>
                                <div style="font-size: 14px; font-weight: 700; color: #003b73;">{{ $tbmName }}</div>
                                <div style="font-size: 13px; color: #d97706; margin-top: 2px;"><i class="fa-solid fa-user-tie me-1"></i>Trưởng Bộ Môn</div>
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
                               onclick="event.preventDefault(); document.getElementById('logout-form-tbm').submit();">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                    <form id="logout-form-tbm" action="{{ route('logout') }}" method="POST" class="d-none">
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
