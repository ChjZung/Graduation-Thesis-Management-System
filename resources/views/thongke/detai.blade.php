@php
    $layout = match($scope['role_code']) {
        'VT05'  => 'layouts.truongkhoa',
        'VT04'  => 'layouts.truongbomon',
        'VT02'  => 'layouts.giangvien',
        default => 'layouts.admin',
    };

    $exportRoute = match($scope['role_code']) {
        'VT05'  => route('truongkhoa.thongke.detai.export', request()->query()),
        'VT04'  => route('truongbomon.thongke.detai.export', request()->query()),
        'VT02'  => route('giangvien.thongke.detai.export', request()->query()),
        default => route('admin.thongke.detai.export', request()->query()),
    };

    $currentRoute = match($scope['role_code']) {
        'VT05'  => route('truongkhoa.thongke.detai'),
        'VT04'  => route('truongbomon.thongke.detai'),
        'VT02'  => route('giangvien.thongke.detai'),
        default => route('admin.thongke.detai'),
    };

    $manageRoute = match($scope['role_code']) {
        'VT05'  => route('truongkhoa.duyet_detai.index', ['MaHocKy' => request('MaHocKy')]),
        'VT04'  => route('truongbomon.duyet_detai.index', ['MaHocKy' => request('MaHocKy')]),
        'VT02'  => route('giangvien.detai.index', ['MaHocKy' => request('MaHocKy')]),
        default => route('admin.duyet_detai.index', ['MaHocKy' => request('MaHocKy')]),
    };
@endphp

@extends($layout)

@section('page_title', 'Thống kê đề tài khóa luận')

@push('styles')
<style>
    .stat-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0,0,0,0.06);
    }
    .stat-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .chart-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .chart-box h6 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .reg-stat-pill {
        border-radius: 10px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }
    .badge-code {
        font-family: 'Consolas', monospace;
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #334155;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- ═══ HEADER ═══ -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-chart-pie text-primary"></i> Thống Kê Đề Tài Khóa Luận Tốt Nghiệp
            </h4>
            <div class="text-muted small">
                Theo dõi số lượng, trạng thái, lĩnh vực và tình hình đăng ký đề tài theo học kỳ, khoa và bộ môn.
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ $manageRoute }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 shadow-sm fw-semibold">
                <i class="fa-solid fa-book-bookmark me-1"></i> Quản Lý &amp; Công Bố Đề Tài
            </a>
            @if($scope['is_admin'])
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa-solid fa-building-columns me-1"></i> Phạm vi: Toàn trường
                </span>
            @elseif($scope['is_tk'])
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa-solid fa-graduation-cap me-1"></i> Phạm vi: Khoa {{ $scope['force_khoa'] }}
                </span>
            @elseif($scope['is_tbm'])
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa-solid fa-layer-group me-1"></i> Phạm vi: Bộ môn {{ $scope['force_bm'] }}
                </span>
            @else
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa-solid fa-user-tie me-1"></i> Phạm vi: Đề tài của tôi
                </span>
            @endif
        </div>
    </div>

    <!-- ═══ BỘ LỌC LIÊN KẾT (SECTION B) ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ $currentRoute }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Học kỳ -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted mb-1">Học kỳ / Năm học</label>
                        <select name="MaHocKy" class="form-select form-select-sm rounded-3">
                            <option value="">-- Tất cả học kỳ --</option>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Khoa -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted mb-1">
                            Khoa 
                            @if($scope['locked_khoa']) <i class="fa-solid fa-lock text-muted ms-1" title="Khóa theo vai trò"></i> @endif
                        </label>
                        @if($scope['locked_khoa'])
                            <select class="form-select form-select-sm rounded-3 bg-light" disabled>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" selected>{{ $k->TenKhoa }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="MaKhoa" value="{{ $scope['force_khoa'] }}">
                        @else
                            <select name="MaKhoa" id="selectKhoa" class="form-select form-select-sm rounded-3">
                                <option value="">-- Tất cả các khoa --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ request('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Bộ môn (Cascading AJAX) -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted mb-1">
                            Bộ môn
                            @if($scope['locked_bm']) <i class="fa-solid fa-lock text-muted ms-1" title="Khóa theo vai trò"></i> @endif
                        </label>
                        @if($scope['locked_bm'])
                            <select class="form-select form-select-sm rounded-3 bg-light" disabled>
                                @foreach($boMons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" selected>{{ $bm->TenBoMon }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="MaBoMon" value="{{ $scope['force_bm'] }}">
                        @else
                            <select name="MaBoMon" id="selectBoMon" class="form-select form-select-sm rounded-3">
                                <option value="">-- Tất cả bộ môn --</option>
                                @foreach($boMons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Trạng thái đề tài -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted mb-1">Trạng thái đề tài</label>
                        <select name="TrangThai" class="form-select form-select-sm rounded-3">
                            <option value="">-- Tất cả trạng thái --</option>
                            @foreach($trangThais as $tt)
                                <option value="{{ $tt }}" {{ request('TrangThai') == $tt ? 'selected' : '' }}>{{ $tt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lĩnh vực -->
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label small fw-bold text-muted mb-1">Lĩnh vực nghiên cứu</label>
                        <select name="LinhVuc" class="form-select form-select-sm rounded-3">
                            <option value="">-- Tất cả lĩnh vực --</option>
                            @foreach($linhVucs as $lv)
                                <option value="{{ $lv }}" {{ request('LinhVuc') == $lv ? 'selected' : '' }}>{{ $lv }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nút thao tác -->
                    <div class="col-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 w-100 fw-bold">
                            <i class="fa-solid fa-filter me-1"></i> Lọc
                        </button>
                        <a href="{{ $currentRoute }}" class="btn btn-sm btn-light border rounded-3 px-2" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                        <a href="{{ $exportRoute }}" class="btn btn-sm btn-success rounded-3 px-3 text-nowrap fw-bold" title="Xuất báo cáo Excel">
                            <i class="fa-solid fa-file-excel me-1"></i> Xuất
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ 6 KPI CARDS (SECTION C) ═══ -->
    <div class="row g-3 mb-4">
        <!-- 1. Tổng đề tài -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-primary">
                <div>
                    <div class="text-muted small fw-semibold">Tổng Đề Tài</div>
                    <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpis['total']) }}</div>
                </div>
                <div class="stat-kpi-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
            </div>
        </div>

        <!-- 2. Đã công bố -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-info">
                <div>
                    <div class="text-muted small fw-semibold">Đã Công Bố</div>
                    <div class="fs-4 fw-bold text-info mt-1">{{ number_format($kpis['da_cong_bo']) }}</div>
                </div>
                <div class="stat-kpi-icon" style="background: #e0e7ff; color: #4338ca;">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
            </div>
        </div>

        <!-- 3. Đang đăng ký -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-warning">
                <div>
                    <div class="text-muted small fw-semibold">Đang Đăng Ký</div>
                    <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($kpis['dang_dang_ky']) }}</div>
                </div>
                <div class="stat-kpi-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
            </div>
        </div>

        <!-- 4. Đã đủ nhóm -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-success">
                <div>
                    <div class="text-muted small fw-semibold">Đã Đủ Nhóm</div>
                    <div class="fs-4 fw-bold text-success mt-1">{{ number_format($kpis['da_du_nhom']) }}</div>
                </div>
                <div class="stat-kpi-icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
            </div>
        </div>

        <!-- 5. Đang chờ duyệt -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-secondary">
                <div>
                    <div class="text-muted small fw-semibold">Đang Chờ Duyệt</div>
                    <div class="fs-4 fw-bold text-secondary mt-1">{{ number_format($kpis['dang_cho_duyet']) }}</div>
                </div>
                <div class="stat-kpi-icon" style="background: #f1f5f9; color: #475569;">
                    <i class="fa-regular fa-clock"></i>
                </div>
            </div>
        </div>

        <!-- 6. Đã hoàn thành -->
        <div class="col-12 col-sm-6 col-md-4 col-xl-2">
            <div class="stat-kpi-card d-flex align-items-center justify-content-between border-start border-4 border-dark">
                <div>
                    <div class="text-muted small fw-semibold">Đã Hoàn Thành</div>
                    <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($kpis['da_hoan_thanh']) }}</div>
                </div>
                <div class="stat-kpi-icon" style="background: #f3e8ff; color: #7e22ce;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ 4 BIỂU ĐỒ THỐNG KÊ (SECTION D) ═══ -->
    <div class="row g-3 mb-4">
        <!-- Donut Chart: Phân bố trạng thái đề tài -->
        <div class="col-12 col-lg-5">
            <div class="chart-box">
                <h6><i class="fa-solid fa-chart-pie text-primary"></i> Phân Bố Trạng Thái Đề Tài</h6>
                <div style="height: 260px; position: relative;">
                    <canvas id="chartTrangThai"></canvas>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Số lượng đề tài theo bộ môn -->
        <div class="col-12 col-lg-7">
            <div class="chart-box">
                <h6><i class="fa-solid fa-chart-column text-success"></i> Số Lượng Đề Tài Theo Bộ Môn</h6>
                <div style="height: 260px; position: relative;">
                    <canvas id="chartBoMon"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Horizontal Bar: Phân bố đề tài theo lĩnh vực -->
        <div class="col-12 col-lg-7">
            <div class="chart-box">
                <h6><i class="fa-solid fa-bars-staggered text-info"></i> Phân Bố Đề Tài Theo Lĩnh Vực Nghiên Cứu</h6>
                <div style="height: 260px; position: relative;">
                    <canvas id="chartLinhVuc"></canvas>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Quy mô nhóm đăng ký -->
        <div class="col-12 col-lg-5">
            <div class="chart-box">
                <h6><i class="fa-solid fa-user-group text-warning"></i> Đề Tài Theo Quy Mô Nhóm Sinh Viên</h6>
                <div style="height: 260px; position: relative;">
                    <canvas id="chartQuyMoNhom"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ THỐNG KÊ TÌNH HÌNH ĐĂNG KÝ (SECTION E) ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary"></i> Tình Hình Đăng Ký Đề Tài Của Sinh Viên
                </h6>
                <span class="small text-muted">Số liệu tính trên các đề tài mở công bố đăng ký</span>
            </div>

            <div class="row g-3 text-center">
                <div class="col-12 col-sm-6 col-md">
                    <div class="reg-stat-pill">
                        <div class="small text-muted fw-semibold mb-1">Đã Công Bố</div>
                        <div class="fs-4 fw-bold text-dark">{{ $dangKyStats['tong_cong_bo'] }}</div>
                        <div class="small text-muted">đề tài mở</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md">
                    <div class="reg-stat-pill">
                        <div class="small text-muted fw-semibold mb-1">Đã Có Nhóm ĐK</div>
                        <div class="fs-4 fw-bold text-primary">{{ $dangKyStats['co_nhom_dk'] }}</div>
                        <div class="small text-primary">có sinh viên đăng ký</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md">
                    <div class="reg-stat-pill">
                        <div class="small text-muted fw-semibold mb-1">Còn Chỗ</div>
                        <div class="fs-4 fw-bold text-warning">{{ $dangKyStats['con_cho'] }}</div>
                        <div class="small text-warning">chưa đủ số lượng SV</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md">
                    <div class="reg-stat-pill">
                        <div class="small text-muted fw-semibold mb-1">Đã Đủ Sinh Viên</div>
                        <div class="fs-4 fw-bold text-success">{{ $dangKyStats['da_du_sv'] }}</div>
                        <div class="small text-success">đã kín chỗ 100%</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md">
                    <div class="reg-stat-pill">
                        <div class="small text-muted fw-semibold mb-1">Chưa Có SV ĐK</div>
                        <div class="fs-4 fw-bold text-danger">{{ $dangKyStats['chua_co_sv'] }}</div>
                        <div class="small text-danger">cần đẩy mạnh đăng ký</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ BẢNG CHI TIẾT ĐỀ TÀI (SECTION F) ═══ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Danh Sách Chi Tiết Đề Tài Khóa Luận</h5>
                    <div class="text-muted small">Hiển thị thông tin giảng viên hướng dẫn, quy mô số lượng sinh viên và tình trạng xét duyệt.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- Search Input form submission -->
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <input type="text" name="search" form="filterForm" class="form-control rounded-start-pill ps-3" 
                               placeholder="Tìm mã, tên đề tài, GV..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary rounded-end-pill px-3" type="submit" form="filterForm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="bg-light text-muted fw-semibold small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3" width="10%">MÃ ĐỀ TÀI</th>
                        <th class="py-3" width="30%">TÊN ĐỀ TÀI</th>
                        <th class="py-3" width="18%">GIẢNG VIÊN HƯỚNG DẪN</th>
                        <th class="py-3" width="14%">BỘ MÔN / KHOA</th>
                        <th class="text-center py-3" width="12%">SỐ SV ĐĂNG KÝ</th>
                        <th class="text-center py-3" width="10%">TRẠNG THÁI</th>
                        <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($deTais as $dt)
                        @php
                            $maxSV = $dt->SoLuongSinhVienToiDa ?? 3;
                            $regSV = $dt->so_sv_dang_ky ?? 0;
                            $percent = min(100, round(($regSV / max(1, $maxSV)) * 100));

                            $statusBadgeClass = match($dt->TrangThai) {
                                'Trưởng khoa đã duyệt'     => 'bg-success-subtle text-success border-success-subtle',
                                'Đã công bố'               => 'bg-primary-subtle text-primary border-primary-subtle',
                                'Đã đăng ký'               => 'bg-indigo-subtle text-indigo border-indigo-subtle',
                                'Chờ duyệt cấp Khoa'       => 'bg-danger-subtle text-danger border-danger-subtle',
                                'Chờ duyệt cấp Bộ môn'     => 'bg-warning-subtle text-warning border-warning-subtle',
                                'Đang phản biện đề cương'  => 'bg-info-subtle text-info border-info-subtle',
                                'Hoàn thành'               => 'bg-dark-subtle text-dark border-dark-subtle',
                                'Yêu cầu chỉnh sửa'        => 'bg-warning-subtle text-warning border-warning-subtle',
                                'Từ chối'                  => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                default                    => 'bg-light text-muted border',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $dt->MaDeTai }}</span>
                            </td>
                            <td class="py-3">
                                <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                @if($dt->LinhVuc)
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 mt-1 small" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-tag me-1"></i>{{ $dt->LinhVuc }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $dt->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                                <div class="text-muted small">{{ $dt->giangVien?->Email ?? '' }}</div>
                            </td>
                            <td class="py-3">
                                <div class="text-dark">{{ $dt->giangVien?->boMon?->TenBoMon ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $dt->giangVien?->boMon?->khoa?->TenKhoa ?? '' }}</div>
                            </td>
                            <td class="text-center py-3">
                                <div class="fw-bold {{ $regSV >= $maxSV ? 'text-success' : ($regSV > 0 ? 'text-warning' : 'text-muted') }}">
                                    {{ $regSV }} / {{ $maxSV }} SV
                                </div>
                                <div class="progress mt-1 mx-auto" style="height: 5px; width: 80px;">
                                    <div class="progress-bar {{ $regSV >= $maxSV ? 'bg-success' : ($regSV > 0 ? 'bg-warning' : 'bg-secondary') }}" 
                                         role="progressbar" style="width: {{ $percent }}%;"></div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge border rounded-pill px-2.5 py-1 fw-semibold {{ $statusBadgeClass }}" style="font-size: 0.75rem;">
                                    {{ $dt->TrangThai }}
                                </span>
                            </td>
                            <td class="pe-4 text-center py-3">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" 
                                            onclick="showDeTaiModal({{ json_encode($dt) }})" title="Xem chi tiết đề tài">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </button>
                                    @php
                                        $itemManageUrl = match($scope['role_code']) {
                                            'VT05'  => route('truongkhoa.duyet_detai.index', ['search' => $dt->MaDeTai]),
                                            'VT04'  => route('truongbomon.duyet_detai.index', ['search' => $dt->MaDeTai]),
                                            'VT02'  => route('giangvien.detai.index', ['search' => $dt->MaDeTai]),
                                            default => route('admin.duyet_detai.index', ['search' => $dt->MaDeTai]),
                                        };
                                    @endphp
                                    <a href="{{ $itemManageUrl }}" class="btn btn-sm btn-light border rounded-pill px-2.5" title="Mở trong Quản lý đề tài">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-secondary"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-folder-open fa-3x text-secondary mb-3 opacity-50"></i>
                                    <h6 class="fw-bold">Không tìm thấy đề tài nào phù hợp</h6>
                                    <p class="small mb-3">Vui lòng thử điều chỉnh lại bộ lọc hoặc từ khóa tìm kiếm.</p>
                                    <a href="{{ $currentRoute }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">Đặt lại bộ lọc</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deTais->hasPages())
        <div class="p-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Hiển thị <strong>{{ $deTais->firstItem() }} - {{ $deTais->lastItem() }}</strong> trên tổng số <strong>{{ $deTais->total() }}</strong> đề tài
            </span>
            {{ $deTais->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

<!-- ═══ MODAL CHI TIẾT ĐỀ TÀI ═══ -->
<div class="modal fade" id="deTaiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info"></i> Chi Tiết Đề Tài Khóa Luận
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalDeTaiBody">
                <!-- Nội dung được nạp qua JS -->
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------------------
    // 1. AJAX Cascading Dropdown: Khoa -> Bộ môn
    // -------------------------------------------------------------
    const selectKhoa = document.getElementById('selectKhoa');
    const selectBoMon = document.getElementById('selectBoMon');

    if (selectKhoa && selectBoMon) {
        selectKhoa.addEventListener('change', function () {
            const maKhoa = this.value;
            selectBoMon.innerHTML = '<option value="">-- Đang tải bộ môn... --</option>';

            if (!maKhoa) {
                selectBoMon.innerHTML = '<option value="">-- Tất cả bộ môn --</option>';
                return;
            }

            fetch(`/api/khoa/${maKhoa}/bomons`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Tất cả bộ môn --</option>';
                    data.forEach(bm => {
                        html += `<option value="${bm.MaBoMon}">${bm.TenBoMon}</option>`;
                    });
                    selectBoMon.innerHTML = html;
                })
                .catch(err => {
                    console.error('Lỗi nạp bộ môn:', err);
                    selectBoMon.innerHTML = '<option value="">-- Lỗi nạp dữ liệu --</option>';
                });
        });
    }

    // -------------------------------------------------------------
    // 2. KHỞI TẠO CÁC BIỂU ĐỒ CHART.JS
    // -------------------------------------------------------------
    const statusData = @json($statusCounts);
    const boMonData = @json($boMonCounts);
    const linhVucData = @json($topLinhVucCounts);
    const quyMoData = @json($quyMoNhomCounts);

    // A. Donut Chart - Trạng thái
    new Chart(document.getElementById('chartTrangThai'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#3b82f6', // Đã công bố
                    '#10b981', // Đang đăng ký / Đủ nhóm
                    '#f59e0b', // Đang chờ duyệt
                    '#8b5cf6', // Hoàn thành
                    '#ef4444'  // Từ chối / Chỉnh sửa
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            },
            cutout: '65%'
        }
    });

    // B. Bar Chart - Bộ môn
    new Chart(document.getElementById('chartBoMon'), {
        type: 'bar',
        data: {
            labels: Object.keys(boMonData),
            datasets: [{
                label: 'Số lượng đề tài',
                data: Object.values(boMonData),
                backgroundColor: '#0284c7',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                x: {
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });

    // C. Horizontal Bar - Lĩnh vực
    new Chart(document.getElementById('chartLinhVuc'), {
        type: 'bar',
        data: {
            labels: Object.keys(linhVucData),
            datasets: [{
                label: 'Số đề tài',
                data: Object.values(linhVucData),
                backgroundColor: '#6366f1',
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                y: {
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // D. Bar Chart - Quy mô nhóm
    new Chart(document.getElementById('chartQuyMoNhom'), {
        type: 'bar',
        data: {
            labels: Object.keys(quyMoData),
            datasets: [{
                label: 'Số đề tài',
                data: Object.values(quyMoData),
                backgroundColor: ['#38bdf8', '#34d399', '#fbbf24'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                }
            }
        }
    });

});

// -------------------------------------------------------------
// 3. Modal xem chi tiết đề tài
// -------------------------------------------------------------
function showDeTaiModal(dt) {
    const modalBody = document.getElementById('modalDeTaiBody');
    const gv = dt.giang_vien || {};
    const bm = gv.bo_mon || {};
    const kh = bm.khoa || {};
    const hk = dt.hoc_ky || {};

    let groupsHtml = '<div class="text-muted small">Chưa có nhóm nào đăng ký đề tài này.</div>';
    if (dt.phieu_dang_kys && dt.phieu_dang_kys.length > 0) {
        groupsHtml = '<ul class="list-group list-group-flush border rounded-3">';
        dt.phieu_dang_kys.forEach(p => {
            const nhom = p.nhom || {};
            const members = nhom.thanh_vien_nhoms || [];
            let memberBadges = members.map(m => `<span class="badge bg-light text-dark border me-1">${m.MaSV} (${m.VaiTro || 'Thành viên'})</span>`).join('');
            groupsHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${nhom.TenNhom || p.MaNhom}</div>
                        <div class="small text-muted mt-1">${memberBadges || 'Chưa cập nhật thành viên'}</div>
                    </div>
                    <span class="badge ${p.TrangThai === 'Đã duyệt' ? 'bg-success' : 'bg-warning'} rounded-pill">${p.TrangThai}</span>
                </li>
            `;
        });
        groupsHtml += '</ul>';
    }

    modalBody.innerHTML = `
        <div class="mb-3">
            <span class="badge-code">${dt.MaDeTai}</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-2">${dt.TrangThai}</span>
            <h5 class="fw-bold text-dark mt-2 mb-1">${dt.TenDeTai}</h5>
            <div class="text-muted small">Lĩnh vực: <strong class="text-dark">${dt.LinhVuc || 'Chưa xác định'}</strong> &bull; Học kỳ: <strong>${hk.TenHocKy || dt.MaHocKy}</strong></div>
        </div>

        <div class="row g-3 p-3 bg-light rounded-3 mb-3">
            <div class="col-md-6">
                <div class="small text-muted">Giảng viên hướng dẫn:</div>
                <div class="fw-bold text-dark">${gv.HoTen || 'Chưa phân công'}</div>
                <div class="small text-muted">${gv.Email || ''}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Khoa & Bộ môn:</div>
                <div class="fw-semibold text-dark">${bm.TenBoMon || 'N/A'}</div>
                <div class="small text-muted">${kh.TenKhoa || ''}</div>
            </div>
        </div>

        <div class="mb-3">
            <div class="fw-bold small text-muted text-uppercase mb-1">Mô tả tóm tắt:</div>
            <p class="text-dark small mb-0">${dt.MoTa || 'Chưa có mô tả chi tiết cho đề tài này.'}</p>
        </div>

        <div class="mb-3">
            <div class="fw-bold small text-muted text-uppercase mb-1">Yêu cầu kỹ thuật / Nền tảng:</div>
            <p class="text-dark small mb-0">${dt.YeuCau || 'Không có yêu cầu đặc biệt.'}</p>
        </div>

        <div class="mb-3">
            <div class="fw-bold small text-muted text-uppercase mb-1">Quy mô nhóm sinh viên:</div>
            <div class="small text-dark">Quy định quy mô: <strong>${dt.SoLuongSinhVienToiDa || 3} sinh viên / nhóm</strong></div>
        </div>

        <div class="mb-2">
            <div class="fw-bold small text-muted text-uppercase mb-2">Tình hình đăng ký của sinh viên:</div>
            ${groupsHtml}
        </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('deTaiModal'));
    modal.show();
}
</script>
@endpush
