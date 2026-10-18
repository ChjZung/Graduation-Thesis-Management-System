@extends($layout ?? 'layouts.sinhvien')

@section('page_title', 'Danh Sách Thông Báo')

@push('styles')
<style>
    :root {
        --student-primary: #1e3a8a; /* Đồng bộ mã màu xanh đậm đặc trưng từ giao diện mẫu */
        --student-primary-hover: #1e40af;
        --student-bg-subtle: #eff6ff;
    }
    .kpi-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    .unread-indicator-dot {
        width: 8px;
        height: 8px;
        background-color: #ef4444;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
        animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }
    .hover-title:hover {
        color: var(--student-primary) !important;
        text-decoration: underline !important;
    }
    /* Đồng bộ kiểu nút bấm theo mẫu */
    .btn-student-primary {
        background-color: var(--student-primary);
        border-color: var(--student-primary);
        color: #fff;
        font-weight: 500;
    }
    .btn-student-primary:hover {
        background-color: var(--student-primary-hover);
        border-color: var(--student-primary-hover);
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-exclamation fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. Header Section & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-bullhorn me-2" style="color: var(--student-primary);"></i>
                <span>
                    @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                        Quản Lý &amp; Công Bố Thông Báo
                    @else
                        Kênh Thông Báo &amp; Học Vụ
                    @endif
                </span>
            </h4>
            <p class="text-muted small mb-0">Hệ thống thông báo, kế hoạch khóa luận và hướng dẫn chuyên môn chính thức.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!empty($stats['unread_count']) && $stats['unread_count'] > 0)
                <form action="{{ route('thongbao.markAllReadGlobal') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center" title="Đánh dấu tất cả là đã đọc">
                        <i class="fa-solid fa-check-double me-2" style="color: var(--student-primary);"></i> Đọc tất cả ({{ $stats['unread_count'] }})
                    </button>
                </form>
            @endif

            @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                <a href="{{ route('thongbao.create') }}" class="btn btn-student-primary btn-sm rounded-3 px-3 shadow-sm fw-semibold d-inline-flex align-items-center">
                    <i class="fa-solid fa-plus me-2"></i> Tạo Thông Báo Mới
                </a>
            @endif
        </div>
    </div>

    <!-- 2. KPI Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white h-100 kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Tổng Thông Báo</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_thongbao'] ?? $thongbaos->total()) }}</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: var(--student-bg-subtle); color: var(--student-primary);">
                        <i class="fa-solid fa-bullhorn fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white h-100 kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Đã Phát Hành</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($stats['active_count'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white h-100 kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Tin Chưa Đọc</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4 fw-bold {{ ($stats['unread_count'] ?? 0) > 0 ? 'text-danger' : 'text-dark' }}">
                                {{ number_format($stats['unread_count'] ?? 0) }}
                            </span>
                            @if(($stats['unread_count'] ?? 0) > 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small px-2 py-1" style="font-size: 0.72rem;">
                                    Cần xem
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-envelope-open-text fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white h-100 kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                        <div>
                            <div class="text-muted small fw-medium mb-1">Bản Nháp / Hẹn Giờ</div>
                            <div class="fs-4 fw-bold text-warning">{{ number_format($stats['draft_schedule_count'] ?? 0) }}</div>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                        </div>
                    @else
                        <div>
                            <div class="text-muted small fw-medium mb-1">Tin Toàn Trường</div>
                            <div class="fs-4 fw-bold" style="color: var(--student-primary);">{{ number_format($stats['target_all_count'] ?? 0) }}</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: var(--student-bg-subtle); color: var(--student-primary);">
                            <i class="fa-solid fa-globe fs-5"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Bộ Lọc Giao Diện Đồng Bộ Theo Mẫu (Filter & Search Form) -->
    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('sinhvien.thongbao.index') }}" id="filterForm">
                
                <!-- Hàng 1: Thanh Tìm Kiếm Lớn + Nút Tìm + Nút Hành Động Phải -->
                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-12 col-lg">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0 py-2 shadow-none" placeholder="Tìm tiêu đề, nội dung, mã thông báo..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-student-primary px-4">Tìm</button>
                        </div>
                    </div>
                    <div class="col-12 col-lg-auto d-flex gap-2">
                        @if(request()->anyFilled(['search', 'LoaiThongBao', 'TrangThai', 'is_read', 'DoiTuongNhan', 'NguoiTao', 'tu_ngay', 'den_ngay']))
                            <a href="{{ route('sinhvien.thongbao.index') }}" class="btn btn-light border text-danger px-3 py-2 shadow-xs d-flex align-items-center gap-1" title="Xóa toàn bộ bộ lọc">
                                <i class="fa-solid fa-rotate-left me-2"></i> Đặt lại
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Hàng 2: Các Dropdown Lọc Dàn Đều (Giống mẫu ảnh) -->
                <div class="row g-2">
                    <div class="col-6 col-md-4 col-lg">
                        <select name="LoaiThongBao" class="form-select form-select-sm py-2 shadow-none" onchange="this.form.submit()">
                            <option value="">-- Tất cả phân loại --</option>
                            <option value="Kế hoạch" {{ request('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch khóa luận</option>
                            <option value="Hệ thống" {{ request('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống &amp; Kỹ thuật</option>
                            <option value="Báo cáo" {{ request('LoaiThongBao') == 'Báo cáo' ? 'selected' : '' }}>Báo cáo tiến độ</option>
                            <option value="Hội đồng" {{ request('LoaiThongBao') == 'Hội đồng' ? 'selected' : '' }}>Hội đồng bảo vệ</option>
                            <option value="Thông báo chung" {{ request('LoaiThongBao') == 'Thông báo chung' ? 'selected' : '' }}>Thông báo chung</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <select name="TrangThai" class="form-select form-select-sm py-2 shadow-none" onchange="this.form.submit()">
                            <option value="">-- Trạng thái phát hành --</option>
                            <option value="ĐÃ GỬI" {{ request('TrangThai') == 'ĐÃ GỬI' ? 'selected' : '' }}>Đã phát hành</option>
                            @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                                <option value="ĐÃ LÊN LỊCH" {{ request('TrangThai') == 'ĐÃ LÊN LỊCH' ? 'selected' : '' }}>Đã lên lịch</option>
                                <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>Bản nháp</option>
                                <option value="ĐÃ HỦY" {{ request('TrangThai') == 'ĐÃ HỦY' ? 'selected' : '' }}>Đã hủy</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <select name="is_read" class="form-select form-select-sm py-2 shadow-none" onchange="this.form.submit()">
                            <option value="">-- Trạng thái đọc --</option>
                            <option value="unread" {{ request('is_read') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
                            <option value="read" {{ request('is_read') == 'read' ? 'selected' : '' }}>Đã đọc</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-6 col-lg">
                        <select name="DoiTuongNhan" class="form-select form-select-sm py-2 shadow-none" onchange="this.form.submit()">
                            <option value="">-- Đối tượng nhận --</option>
                            <option value="Toàn trường" {{ request('DoiTuongNhan') == 'Toàn trường' ? 'selected' : '' }}>Toàn trường (Tất cả)</option>
                            <option value="Sinh viên" {{ request('DoiTuongNhan') == 'Sinh viên' ? 'selected' : '' }}>Sinh viên</option>
                            <option value="Giảng viên" {{ request('DoiTuongNhan') == 'Giảng viên' ? 'selected' : '' }}>Giảng viên</option>
                            <option value="Trưởng bộ môn" {{ request('DoiTuongNhan') == 'Trưởng bộ môn' ? 'selected' : '' }}>Trưởng bộ môn</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-6 col-lg">
                        <select name="NguoiTao" class="form-select form-select-sm py-2 shadow-none" onchange="this.form.submit()">
                            <option value="">-- Nguồn phát hành --</option>
                            <option value="Khoa" {{ request('NguoiTao') == 'Khoa' ? 'selected' : '' }}>Khoa / Ban Giáo vụ</option>
                            <option value="GiangVien" {{ request('NguoiTao') == 'GiangVien' ? 'selected' : '' }}>Giảng viên phụ trách</option>
                        </select>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- 4. Danh Sách Thông Báo (Table Card) -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-regular fa-clipboard fs-5 me-2" style="color: var(--student-primary);"></i>
                <span class="fw-bold text-dark fs-6">Danh Sách Thông Báo Chính Thức</span>
            </div>
            <div class="small text-muted">
                Tổng cộng: <strong class="text-dark">{{ $thongbaos->total() }}</strong> thông báo
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary small" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th class="ps-3" style="width: 130px;">Mã TB / Phân loại</th>
                            <th style="min-width: 340px;">Tiêu Đề &amp; Trích Yếu Nội Dung</th>
                            <th class="text-center" style="width: 140px;">Đối Tượng Nhận</th>
                            <th style="width: 160px;">Nguồn Ban Hành</th>
                            <th class="text-center" style="width: 125px;">Ngày Đăng</th>
                            <th class="text-center" style="width: 110px;">Trạng Thái</th>
                            <th class="pe-3 text-center" style="width: 115px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thongbaos as $tb)
                            @php
                                $isRead = in_array($tb->MaThongBao, $readIds ?? []);
                                $nguoiTaoStr = $tb->giaoVu?->HoTen ?? $tb->giangVien?->HoTen ?? 'Ban Giáo vụ Khoa';
                            @endphp
                            <tr class="{{ !$isRead ? 'bg-light-subtle' : '' }}">
                                <!-- Mã TB & Phân loại -->
                                <td class="ps-3">
                                    <div class="font-monospace fw-semibold text-dark small">{{ $tb->MaThongBao }}</div>
                                    <span class="badge bg-light text-secondary border rounded-pill mt-1" style="font-size: 0.7rem;">
                                        {{ $tb->LoaiThongBao ?? 'Thông báo' }}
                                    </span>
                                </td>

                                <!-- Tiêu đề & Trích yếu -->
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        @if(!$isRead)
                                            <span class="unread-indicator-dot mt-2 me-1" title="Chưa đọc"></span>
                                        @endif
                                        <div class="flex-grow-1">
                                            <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao) }}" class="fw-semibold text-dark text-decoration-none hover-title d-inline-block mb-1" style="font-size: 0.9rem; line-height: 1.4;">
                                                {{ $tb->TieuDe }}
                                            </a>

                                            @if($tb->FileDinhKem)
                                                @php
                                                    $ext = strtolower(pathinfo($tb->FileDinhKem, PATHINFO_EXTENSION));
                                                    $isXlsx = in_array($ext, ['xlsx', 'xls', 'csv']);
                                                @endphp
                                                <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao) }}" class="badge {{ $isXlsx ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill px-2 py-1 ms-1 text-decoration-none small" style="font-size: 0.72rem;">
                                                    <i class="fa-solid {{ $isXlsx ? 'fa-file-excel' : 'fa-file-pdf' }} me-2"></i>
                                                    {{ $isXlsx ? 'Bảng tính Excel' : 'Công văn PDF' }}
                                                </a>
                                            @endif

                                            @php
                                                $shortContent = trim(preg_replace('/\s+/', ' ', strip_tags($tb->NoiDung ?? '')));
                                            @endphp
                                            @if(!empty($shortContent))
                                                <div class="text-muted small text-truncate" style="max-width: 460px; font-size: 0.8rem;" title="{{ $shortContent }}">
                                                    {{ Str::limit($shortContent, 100, '...') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Đối tượng nhận -->
                                <td class="text-center">
                                    @if($tb->DoiTuongNhan === 'Giảng viên')
                                        <span class="badge rounded-pill px-3 py-1" style="font-size: 0.76rem; background-color: var(--student-bg-subtle); color: var(--student-primary);">
                                            <i class="fa-solid fa-chalkboard-user me-2"></i> Giảng viên
                                        </span>
                                    @elseif($tb->DoiTuongNhan === 'Sinh viên')
                                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                            <i class="fa-solid fa-user-graduate me-2"></i> Sinh viên
                                        </span>
                                    @elseif(in_array($tb->DoiTuongNhan, ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV']) || empty($tb->DoiTuongNhan))
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                            <i class="fa-solid fa-users me-2 text-muted"></i> Toàn trường
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark border rounded-pill px-3 py-1" style="font-size: 0.76rem;" title="Người nhận: {{ $tb->DoiTuongNhan }}">
                                            <i class="fa-solid fa-user me-2 text-muted"></i>{{ Str::limit($tb->DoiTuongNhan, 15) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Nguồn ban hành -->
                                <td>
                                    <div class="d-flex align-items-center gap-2 small text-dark">
                                        <i class="fa-solid fa-building-columns me-2" style="color: var(--student-primary); font-size: 0.78rem;"></i>
                                        <span class="fw-medium text-truncate" style="max-width: 140px;" title="{{ $nguoiTaoStr }}">
                                            {{ $nguoiTaoStr }}
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Khoa CNTT</div>
                                </td>

                                <!-- Ngày đăng -->
                                <td class="text-center text-muted small" style="font-size: 0.8rem;">
                                    <div class="fw-semibold text-dark">
                                        {{ date('d/m/Y', strtotime($tb->NgayGui ?? $tb->NgayTao ?? $tb->created_at)) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        {{ date('H:i', strtotime($tb->NgayGui ?? $tb->NgayTao ?? $tb->created_at)) }}
                                    </div>
                                </td>

                                <!-- Trạng thái phát hành -->
                                <td class="text-center">
                                    @if(in_array($tb->TrangThai, ['ĐÃ GỬI', 'ACTIVE', 'da_gui', 'Đã phát hành']))
                                        <span class="badge bg-success text-white rounded-pill px-3 py-1" style="font-size: 0.74rem;">
                                            <i class="fa-solid fa-check me-2"></i> Đã gửi
                                        </span>
                                    @elseif($tb->TrangThai === 'ĐÃ LÊN LỊCH')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size: 0.74rem;">
                                            <i class="fa-regular fa-clock me-2"></i> Lên lịch
                                        </span>
                                    @elseif($tb->TrangThai === 'NHÁP')
                                        <span class="badge bg-secondary text-white rounded-pill px-3 py-1" style="font-size: 0.74rem;">
                                            <i class="fa-solid fa-file-lines me-2"></i> Bản nháp
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1" style="font-size: 0.74rem;">
                                            {{ $tb->TrangThai }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Thao tác -->
                                <td class="pe-3 text-center">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <a href="{{ route('sinhvien.thongbao.show', $tb->MaThongBao) }}" class="btn btn-sm btn-light border px-2 py-1 rounded-3 shadow-xs" style="color: var(--student-primary);" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if(!$isRead)
                                            <button type="button" class="btn btn-sm btn-light border text-secondary px-2 py-1 rounded-3 shadow-xs d-inline-flex align-items-center" title="Đánh dấu đã đọc" onclick="markSingleRead('{{ $tb->MaThongBao }}', this)">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                                            @if(in_array($tb->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']))
                                                <a href="{{ route('thongbao.edit', $tb->MaThongBao) }}" class="btn btn-sm btn-light border text-warning px-2 py-1 rounded-3 shadow-xs" title="Chỉnh sửa">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <form action="{{ route('thongbao.sendNow', $tb->MaThongBao) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn phát hành ngay thông báo này?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light border text-success px-2 py-1 rounded-3 shadow-xs" title="Phát hành ngay">
                                                        <i class="fa-solid fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button" class="btn btn-sm btn-light border text-danger px-2 py-1 rounded-3 shadow-xs d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $tb->MaThongBao }}" title="Xóa thông báo">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                                        <!-- Modal Delete Confirmation -->
                                        <div class="modal fade text-start" id="deleteModal{{ $tb->MaThongBao }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form action="{{ route('thongbao.destroy', $tb->MaThongBao) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header border-bottom pb-3">
                                                            <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-triangle-exclamation me-2"></i> Xác Nhận Xóa Thông Báo
                                                            </h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body py-3">
                                                            <p class="mb-2 text-dark">Bạn có chắc chắn muốn xóa thông báo này khỏi hệ thống?</p>
                                                            <div class="p-3 bg-light rounded-3 border small">
                                                                <div class="fw-bold text-dark mb-1">{{ $tb->TieuDe }}</div>
                                                                <div class="text-muted">Mã TB: <code style="color: var(--student-primary);">{{ $tb->MaThongBao }}</code> | Trạng thái: <strong>{{ $tb->TrangThai }}</strong></div>
                                                            </div>
                                                            <div class="alert alert-warning border-0 small mt-3 mb-0 d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-circle-info text-warning fs-5"></i>
                                                                <div>Hành động này không thể hoàn tác sau khi xác nhận.</div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top pt-2">
                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Xóa</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State khi không có dữ liệu -->
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                            <i class="fa-solid fa-bell-slash fs-2 opacity-50"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Không Tìm Thấy Thông Báo Nào</h6>
                                        <p class="text-muted small mb-3">Hiện tại chưa có thông báo nào phù hợp với điều kiện tìm kiếm hoặc phân quyền của bạn.</p>
                                        @if(request()->anyFilled(['search', 'LoaiThongBao', 'TrangThai', 'is_read', 'DoiTuongNhan', 'NguoiTao', 'tu_ngay', 'den_ngay']))
                                            <a href="{{ route('sinhvien.thongbao.index') }}" class="btn btn-sm rounded-3 px-3 fw-semibold btn-student-primary d-inline-flex align-items-center">
                                                <i class="fa-solid fa-rotate-left me-2"></i> Xóa Bộ Lọc Để Xem Toàn Bộ
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Phân Trang & Thống Kê Bản Ghi -->
        @if($thongbaos->hasPages() || $thongbaos->total() > 0)
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Hiển thị từ <strong>{{ $thongbaos->firstItem() ?? 0 }}</strong> đến <strong>{{ $thongbaos->lastItem() ?? 0 }}</strong> trong tổng số <strong>{{ $thongbaos->total() }}</strong> thông báo
            </span>
            <div>
                {{ $thongbaos->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    function markSingleRead(id, btn) {
        fetch('{{ url("/thongbao/mark-read") }}/' + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const tr = btn.closest('tr');
                if (tr) {
                    tr.classList.remove('bg-light-subtle');
                    const dot = tr.querySelector('.unread-indicator-dot');
                    if (dot) dot.remove();
                }
                btn.remove();
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush