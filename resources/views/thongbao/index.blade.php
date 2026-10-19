@extends($layout ?? 'layouts.admin')

@section('page_title', 'Quản Lý & Danh Sách Thông Báo')

@push('styles')
<style>
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
        color: #0072ce !important;
        text-decoration: underline !important;
    }
    .filter-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">



    <!-- 1. Header Section & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            
            
             <h4 class="fw-bold mb-0 text-dark">
             <i class="fa-solid fa-bullhorn text-primary me-2"></i>
                <span>
                    @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                        Quản Lý &amp; Công Bố Thông Báo
                    @else
                        Kênh Thông Báo &amp; Học Vụ
                    @endif
                </span>
        </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!empty($stats['unread_count']) && $stats['unread_count'] > 0)
                <form action="{{ route('thongbao.markAllReadGlobal') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center" title="Đánh dấu tất cả là đã đọc">
                        <i class="fa-solid fa-check-double me-2 text-primary"></i> Đọc tất cả ({{ $stats['unread_count'] }})
                    </button>
                </form>
            @endif

            @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                <a href="{{ route('thongbao.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold d-inline-flex align-items-center">
                    <i class="fa-solid fa-plus me-2"></i> Tạo Thông Báo Mới
                </a>
            @endif
        </div>
    </div>

    <!-- 2. KPI Summary Cards (4 Thẻ thống kê trực quan) -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white h-100 kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Tổng Thông Báo</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_thongbao'] ?? $thongbaos->total()) }}</div>
                    </div>
                    <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
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
                            <div class="fs-4 fw-bold text-primary">{{ number_format($stats['target_all_count'] ?? 0) }}</div>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-globe fs-5"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Quản Lý Thông Báo (Đồng bộ thiết kế tỷ lệ 9 - 3 và nút khối lớn #1e3a8a với Sinh Viên & Giảng Viên) -->
    <div class="admin-filter-bar mb-4 p-3 bg-white border rounded-3 shadow-sm">
        <form method="GET" action="{{ route('thongbao.index') }}" class="w-100">
            <div class="row g-3 align-items-center">
                <!-- BÊN TRÁI: 9 PHẦN (2 hàng: Tìm kiếm + Các ô lọc) -->
                <div class="col-12 col-xl-9">
                    <div class="d-flex flex-column gap-2">
                        <!-- Hàng 1: Ô tìm kiếm -->
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0 bg-light" placeholder="Tìm theo tiêu đề, nội dung, mã thông báo..." value="{{ request('search') }}">
                            <button class="btn px-4 fw-semibold text-white" type="submit" style="background-color: #1e3a8a;">Tìm</button>
                        </div>

                        <!-- Hàng 2: Các ô lọc dữ liệu (Select) -->
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <!-- Bộ lọc Loại Thông Báo -->
                            <select name="LoaiThongBao" class="form-select form-select-sm" style="width: auto; min-width: 140px; flex: 1;" onchange="this.form.submit()">
                                <option value="">-- Tất cả phân loại --</option>
                                <option value="Kế hoạch" {{ request('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch khóa luận</option>
                                <option value="Hệ thống" {{ request('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống &amp; Kỹ thuật</option>
                                <option value="Báo cáo" {{ request('LoaiThongBao') == 'Báo cáo' ? 'selected' : '' }}>Báo cáo tiến độ</option>
                                <option value="Hội đồng" {{ request('LoaiThongBao') == 'Hội đồng' ? 'selected' : '' }}>Hội đồng bảo vệ</option>
                                <option value="Thông báo chung" {{ request('LoaiThongBao') == 'Thông báo chung' ? 'selected' : '' }}>Thông báo chung</option>
                            </select>

                            <!-- Bộ lọc Trạng Thái -->
                            <select name="TrangThai" class="form-select form-select-sm" style="width: auto; min-width: 130px; flex: 1;" onchange="this.form.submit()">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="ĐÃ GỬI" {{ request('TrangThai') == 'ĐÃ GỬI' ? 'selected' : '' }}>Đã phát hành</option>
                                @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                                    <option value="ĐÃ LÊN LỊCH" {{ request('TrangThai') == 'ĐÃ LÊN LỊCH' ? 'selected' : '' }}>Đã lên lịch</option>
                                    <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>Bản nháp</option>
                                    <option value="ĐÃ HỦY" {{ request('TrangThai') == 'ĐÃ HỦY' ? 'selected' : '' }}>Đã hủy</option>
                                @endif
                            </select>

                            <!-- Bộ lọc Đối Tượng Nhận -->
                            <select name="DoiTuongNhan" class="form-select form-select-sm" style="width: auto; min-width: 130px; flex: 1;" onchange="this.form.submit()">
                                <option value="">-- Tất cả đối tượng --</option>
                                <option value="Toàn trường" {{ request('DoiTuongNhan') == 'Toàn trường' ? 'selected' : '' }}>Toàn trường (Tất cả)</option>
                                <option value="Sinh viên" {{ request('DoiTuongNhan') == 'Sinh viên' ? 'selected' : '' }}>Sinh viên</option>
                                <option value="Giảng viên" {{ request('DoiTuongNhan') == 'Giảng viên' ? 'selected' : '' }}>Giảng viên</option>
                                <option value="Trưởng bộ môn" {{ request('DoiTuongNhan') == 'Trưởng bộ môn' ? 'selected' : '' }}>Trưởng bộ môn</option>
                            </select>

                            <!-- Bộ lọc Nguồn Phát Hành -->
                            <select name="NguoiTao" class="form-select form-select-sm" style="width: auto; min-width: 130px; flex: 1;" onchange="this.form.submit()">
                                <option value="">-- Nguồn phát hành --</option>
                                <option value="Khoa" {{ request('NguoiTao') == 'Khoa' ? 'selected' : '' }}>Khoa / Ban Giáo vụ</option>
                                <option value="GiangVien" {{ request('NguoiTao') == 'GiangVien' ? 'selected' : '' }}>Giảng viên phụ trách</option>
                            </select>

                            <!-- Bộ lọc Trạng Thái Đọc -->
                            <select name="is_read" class="form-select form-select-sm" style="width: auto; min-width: 120px; flex: 1;" onchange="this.form.submit()">
                                <option value="">-- Trạng thái đọc --</option>
                                <option value="unread" {{ request('is_read') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
                                <option value="read" {{ request('is_read') == 'read' ? 'selected' : '' }}>Đã đọc</option>
                            </select>

                            <!-- Nút Xóa lọc -->
                            @if(request()->anyFilled(['search', 'LoaiThongBao', 'TrangThai', 'DoiTuongNhan', 'NguoiTao', 'is_read', 'tu_ngay', 'den_ngay']))
                                <a href="{{ route('thongbao.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap d-inline-flex align-items-center">
                                    <i class="fa-solid fa-xmark me-2"></i>Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- BÊN PHẢI: 3 PHẦN (Nút khối lớn đồng bộ chiều cao và màu #1e3a8a với ảnh mẫu) -->
                <div class="col-12 col-xl-3">
                    @if(in_array($role ?? '', ['Admin', 'Giáo vụ']))
                        <a href="{{ route('thongbao.create') }}" class="btn shadow fw-bold rounded-3 w-100 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none" style="background-color: #1e3a8a; border-color: #1e3a8a; min-height: 82px; padding: 10px 15px;">
                            <span style="font-size: 0.95rem;"><i class="fa-solid fa-plus-circle me-2"></i>Tạo Thông Báo Mới</span>
                        </a>
                    @else
                        <form action="{{ route('thongbao.markAllReadGlobal') }}" method="POST" class="w-100 m-0">
                            @csrf
                            <button type="submit" class="btn shadow fw-bold rounded-3 w-100 d-flex flex-column align-items-center justify-content-center text-white" style="background-color: #1e3a8a; border-color: #1e3a8a; min-height: 82px; padding: 10px 15px;">
                                <span style="font-size: 0.95rem;"><i class="fa-solid fa-check-double me-2"></i>Đánh Dấu Đã Đọc Tất Cả</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- 4. Danh Sách Thông Báo (Table Card) -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-regular fa-clipboard text-primary fs-5 me-2"></i>
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
                                <!-- Cột 1: Mã TB & Phân loại -->
                                <td class="ps-3">
                                    <div class="font-monospace fw-semibold text-dark small">{{ $tb->MaThongBao }}</div>
                                    <span class="badge bg-light text-secondary border rounded-pill mt-1" style="font-size: 0.7rem;">
                                        {{ $tb->LoaiThongBao ?? 'Thông báo' }}
                                    </span>
                                </td>

                                <!-- Cột 2: Tiêu đề & Trích yếu -->
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        @if(!$isRead)
                                            <span class="unread-indicator-dot mt-2 me-1" title="Chưa đọc"></span>
                                        @endif
                                        <div class="flex-grow-1">
                                            <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="fw-semibold text-dark text-decoration-none hover-title d-inline-block mb-1" style="font-size: 0.9rem; line-height: 1.4;">
                                                {{ $tb->TieuDe }}
                                            </a>

                                            @if($tb->FileDinhKem)
                                                @php
                                                    $ext = strtolower(pathinfo($tb->FileDinhKem, PATHINFO_EXTENSION));
                                                    $isXlsx = in_array($ext, ['xlsx', 'xls', 'csv']);
                                                @endphp
                                                <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="badge {{ $isXlsx ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill px-2 py-1 ms-1 text-decoration-none small" style="font-size: 0.72rem;">
                                                    <i class="fa-solid {{ $isXlsx ? 'fa-file-excel' : 'fa-file-pdf' }} me-2"></i>
                                                    {{ $isXlsx ? 'Bảng tính Excel' : 'Công văn PDF' }}
                                                </a>
                                            @endif

                                            <!-- Trích yếu 1-2 dòng ngắn gọn -->
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

                                <!-- Cột 3: Đối tượng nhận -->
                                <td class="text-center">
                                    @if($tb->DoiTuongNhan === 'Giảng viên')
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1" style="font-size: 0.76rem;">
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

                                <!-- Cột 4: Nguồn ban hành -->
                                <td>
                                    <div class="d-flex align-items-center gap-2 small text-dark">
                                        <i class="fa-solid fa-building-columns text-primary me-2" style="font-size: 0.78rem;"></i>
                                        <span class="fw-medium text-truncate" style="max-width: 140px;" title="{{ $nguoiTaoStr }}">
                                            {{ $nguoiTaoStr }}
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Khoa CNTT</div>
                                </td>

                                <!-- Cột 5: Ngày đăng -->
                                <td class="text-center text-muted small" style="font-size: 0.8rem;">
                                    <div class="fw-semibold text-dark">
                                        {{ date('d/m/Y', strtotime($tb->NgayGui ?? $tb->NgayTao ?? $tb->created_at)) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        {{ date('H:i', strtotime($tb->NgayGui ?? $tb->NgayTao ?? $tb->created_at)) }}
                                    </div>
                                </td>

                                <!-- Cột 6: Trạng thái phát hành -->
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

                                <!-- Cột 7: Thao tác -->
                                <td class="pe-3 text-center">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <!-- Xem chi tiết -->
                                        <a href="{{ route('thongbao.show', $tb->MaThongBao) }}" class="btn btn-sm btn-light border text-primary px-2 py-1 rounded-3 shadow-xs" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <!-- Đánh dấu đã đọc nếu chưa đọc -->
                                        @if(!$isRead)
                                            <button type="button" class="btn btn-sm btn-light border text-secondary px-2 py-1 rounded-3 shadow-xs d-inline-flex align-items-center" title="Đánh dấu đã đọc" onclick="markSingleRead('{{ $tb->MaThongBao }}', this)">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        <!-- Các thao tác quản trị cho Admin/Giáo vụ -->
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
                                                                <div class="text-muted">Mã TB: <code class="text-primary">{{ $tb->MaThongBao }}</code> | Trạng thái: <strong>{{ $tb->TrangThai }}</strong></div>
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
                            <!-- Empty State tinh tế khi không có dữ liệu -->
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                            <i class="fa-solid fa-bell-slash fs-2 opacity-50"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Không Tìm Thấy Thông Báo Nào</h6>
                                        <p class="text-muted small mb-3">Hiện tại chưa có thông báo nào phù hợp với điều kiện tìm kiếm hoặc phân quyền của bạn.</p>
                                        @if(request()->anyFilled(['search', 'LoaiThongBao', 'TrangThai', 'is_read', 'DoiTuongNhan', 'NguoiTao', 'tu_ngay', 'den_ngay']))
                                            <a href="{{ route('thongbao.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center">
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
                // Ẩn indicator dot và đổi nút
                const tr = btn.closest('tr');
                if (tr) {
                    tr.classList.remove('bg-light-subtle');
                    const dot = tr.querySelector('.unread-indicator-dot');
                    if (dot) dot.remove();
                }
                btn.remove();

                // Cập nhật số đếm chuông thông báo header nếu có
                const bellBadge = document.getElementById('headerNotificationBadge');
                if (bellBadge) {
                    let cur = parseInt(bellBadge.textContent || '0', 10);
                    if (cur > 1) {
                        bellBadge.textContent = (cur - 1) > 99 ? '99+' : (cur - 1);
                    } else {
                        bellBadge.textContent = '0';
                        bellBadge.classList.add('d-none');
                        bellBadge.style.display = 'none';
                        const ping = document.getElementById('headerBellPing');
                        if (ping) {
                            ping.classList.add('d-none');
                            ping.style.display = 'none';
                        }
                    }
                }
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush
