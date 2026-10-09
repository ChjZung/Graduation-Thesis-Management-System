@extends('layouts.admin')

@section('page_title', 'Quản lý giảng viên')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Quản Lý Danh Sách Giảng Viên, Bộ Môn &amp; Cấp Tài Khoản Portal</h4>
            <p class="text-muted small mb-0">Kiểm soát định mức hướng dẫn đề tài, phân công bộ môn chuyên môn, quản lý chức vụ và trạng thái truy cập hệ thống.</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-normal">
                <i class="fa-solid fa-university text-primary me-1"></i> Khoa Công nghệ Thông tin &mdash; Học kỳ 1 (2026&ndash;2027)
            </span>
        </div>
    </div>

    @if(session('import_result'))
        @php
            $alertType = session('import_alert_type') ?? (session('import_danger') ? 'import_danger' : (session('import_warning') ? 'import_warning' : 'import_result'));
            $alertClass = ($alertType === 'import_danger') ? 'alert-danger' : (($alertType === 'import_warning') ? 'alert-warning' : 'alert-success');
            $borderColor = ($alertType === 'import_danger') ? '#dc3545' : (($alertType === 'import_warning') ? '#f59e0b' : '#198754');
            $bgColor = ($alertType === 'import_danger') ? '#fef2f2' : (($alertType === 'import_warning') ? '#fffbeb' : '#f0fdf4');
        @endphp
        <div class="alert {{ $alertClass }} alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid {{ $borderColor }} !important; background-color: {{ $bgColor }};">
            {!! session('import_result') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('giangvien.index', ['tab' => 'danhsach']) }}" class="text-decoration-none">
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3 text-start" role="button" title="Bấm để xem hồ sơ giảng viên">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Giảng Viên Khoa CNTT</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                            <i class="fa-solid fa-chalkboard-user fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-auto">
                        <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['total'] ?? $giangviens->total() }}</span>
                        <span class="small text-muted fw-medium">giảng viên <i class="fa-solid fa-arrow-right ms-1 text-primary" style="font-size: 0.75rem;"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nhận Hướng Dẫn Khóa Luận</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-person-chalkboard fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $stats['huong_dan'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">giảng viên</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('giangvien.index', ['tab' => 'taikhoan', 'ChucVu' => 'co_chuc_vu']) }}" class="text-decoration-none">
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3 text-start" role="button" title="Bấm để lọc danh sách giảng viên đang giữ chức vụ">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đang Giữ Chức Vụ (TK / TBM)</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                            <i class="fa-solid fa-user-tie fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-auto">
                        <span class="fs-3 fw-bold text-warning lh-1">{{ $stats['chuc_vu'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">cán bộ quản lý <i class="fa-solid fa-arrow-right ms-1 text-warning" style="font-size: 0.75rem;"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('giangvien.index', ['tab' => 'taikhoan']) }}" class="text-decoration-none">
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3 text-start" role="button" title="Bấm để xem danh sách tài khoản Portal">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tài Khoản Portal Active</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0e7ff; color: #4f46e5;">
                            <i class="fa-solid fa-user-shield fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-auto">
                        <span class="fs-3 fw-bold text-info lh-1">{{ $stats['active'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">/ {{ $stats['total'] ?? $giangviens->total() }} tài khoản <i class="fa-solid fa-arrow-right ms-1 text-info" style="font-size: 0.75rem;"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @php
        $activeTab = request('tab', 'danhsach');
    @endphp

    <!-- TAB NAVIGATION: Phong cách Enterprise Portal HUIT -->
    <div class="border-bottom mb-4">
        <ul class="nav nav-tabs border-0" id="gvTab" role="tablist" style="gap: 6px;">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === 'danhsach' ? 'active fw-bold' : 'text-secondary' }} px-4 py-2.5 d-flex align-items-center gap-2" 
                   href="{{ route('giangvien.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'danhsach'])) }}" 
                   style="{{ $activeTab === 'danhsach' ? 'border-top: 3px solid #00305a; background: #ffffff; color: #00305a !important; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;' : 'background: #f8fafc;' }}">
                    <i class="fa-solid fa-address-card"></i>
                    <span>Hồ Sơ Giảng Viên (DSGV)</span>
                    <span class="badge rounded-pill {{ $activeTab === 'danhsach' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }} ms-1">
                        {{ $stats['total'] ?? $giangviens->total() }}
                    </span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === 'taikhoan' ? 'active fw-bold' : 'text-secondary' }} px-4 py-2.5 d-flex align-items-center gap-2" 
                   href="{{ route('giangvien.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'taikhoan'])) }}" 
                   style="{{ $activeTab === 'taikhoan' ? 'border-top: 3px solid #00305a; background: #ffffff; color: #00305a !important; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;' : 'background: #f8fafc;' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Tài Khoản Portal &amp; Phân Quyền Chức Vụ</span>
                    <span class="badge rounded-pill {{ $activeTab === 'taikhoan' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary' }} ms-1">
                        {{ $stats['chuc_vu'] ?? 0 }} cán bộ quản lý
                    </span>
                </a>
            </li>
        </ul>
    </div>

    @if($activeTab === 'danhsach')
    <!-- ========================================== -->
    <!-- TAB 1: DANH SÁCH HỒ SƠ GIẢNG VIÊN (DSGV)   -->
    <!-- ========================================== -->

    <!-- Filter Bar cho DSGV -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <input type="hidden" name="tab" value="danhsach">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 250px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên GV, học hàm, email, mã GV..." value="{{ request('search') }}">
                </div>
                <select name="MaBoMon" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Bộ môn --</option>
                    @foreach($bomons as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                            {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                        </option>
                    @endforeach
                </select>
                @if(request()->anyFilled(['search', 'MaBoMon']))
                    <a href="{{ route('giangvien.index', ['tab' => 'danhsach']) }}" class="btn btn-outline-secondary px-3 flex-shrink-0"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                    <i class="fa-solid fa-download me-1"></i> File Mẫu
                </a>
                <a href="{{ route('admin.giangvien.export', request()->query()) }}" class="btn btn-outline-success shadow-xs fw-semibold" title="Xuất danh sách giảng viên ra Excel/CSV">
                    <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
                </a>
                <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import GV
                </button>
                <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Giảng Viên
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng Hồ Sơ Giảng Viên -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-solid fa-address-card me-2 text-primary"></i> Danh Sách Hồ Sơ Giảng Viên Khoa CNTT</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $giangviens->total() }} giảng viên</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3" width="13%">MÃ GV</th>
                        <th class="py-3" width="31%">HỌ TÊN, HỌC VỊ &amp; LIÊN HỆ</th>
                        <th class="py-3" width="23%">BỘ MÔN CHUYÊN MÔN</th>
                        <th class="py-3 text-center" width="15%">ĐỊNH MỨC HƯỚNG DẪN</th>
                        <th class="py-3 text-center" width="10%">TRẠNG THÁI</th>
                        <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($giangviens as $gv)
                    @php
                        $cnt = $gv->de_tais_count ?? $gv->deTais->count();
                        $dinhMucBadge = match(true) {
                            $cnt >= 5 => 'background: #fef3c7; color: #b45309;',
                            $cnt > 0  => 'background: #e6f9f0; color: #0d8a4f;',
                            default   => 'background: #f1f5f9; color: #64748b;',
                        };
                    @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="badge-code">{{ $gv->MaGV }}</span>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark fs-6">
                                {{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}
                            </div>
                            <div class="text-muted small mt-0.5">
                                <i class="fa-regular fa-envelope me-1"></i>{{ $gv->Email ?? 'chua_co@huit.edu.vn' }}
                                @if($gv->SoDienThoai)
                                    &bull; <i class="fa-solid fa-phone ms-1 me-1"></i>{{ $gv->SoDienThoai }}
                                @endif
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-dark">{{ $gv->boMon->TenBoMon ?? 'Chưa gán bộ môn' }}</div>
                            <div class="text-muted small">{{ $gv->boMon->khoa->TenKhoa ?? 'Khoa CNTT' }}</div>
                        </td>
                        <td class="text-center py-3">
                            <span class="badge px-3 py-1.5 fw-semibold rounded-pill" style="{{ $dinhMucBadge }}">
                                <i class="fa-solid fa-check me-1"></i> Đang nhận: {{ min($cnt, 5) }}/5 nhóm
                            </span>
                        </td>
                        <td class="text-center py-3">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> {{ $gv->TrangThai ?? 'Đang công tác' }}
                            </span>
                        </td>
                        <td class="text-center py-3">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5" style="font-size: 0.8rem;" title="Xem hồ sơ chi tiết">
                                    <i class="fa-solid fa-eye me-1"></i>Xem
                                </a>
                                <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5" style="font-size: 0.8rem;" title="Chỉnh sửa thông tin">
                                    <i class="fa-solid fa-pen me-1"></i>Sửa
                                </a>
                                <form action="{{ route('giangvien.destroy', $gv->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên {{ $gv->HoTen }}? (Chỉ xóa được khi chưa có đề tài, hướng dẫn hoặc hội đồng liên quan)');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 0.8rem;" title="Xóa hồ sơ giảng viên">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-chalkboard-user text-muted mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold text-secondary">Chưa có dữ liệu giảng viên nào</h6>
                                <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc hoặc thêm mới giảng viên.</p>
                                @if(request()->anyFilled(['search', 'MaBoMon']))
                                    <a href="{{ route('giangvien.index', ['tab' => 'danhsach']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                                @else
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createModal">Thêm Giảng Viên Mới</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $giangviens->firstItem() ?? 0 }}–{{ $giangviens->lastItem() ?? 0 }} trên tổng số {{ $giangviens->total() }} giảng viên hệ thống
            </span>
            @if($giangviens->hasPages())
                <div>
                    {{ $giangviens->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    @else
    <!-- ============================================================== -->
    <!-- TAB 2: DS GIẢNG VIÊN THEO TÀI KHOẢN & PHÂN QUYỀN CHỨC VỤ       -->
    <!-- ============================================================== -->

    <!-- Filter Bar cho Tab Tài Khoản -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <input type="hidden" name="tab" value="taikhoan">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 240px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên GV, tên đăng nhập, mã GV..." value="{{ request('search') }}">
                </div>
                <select name="MaBoMon" class="form-select" style="min-width: 190px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Bộ môn --</option>
                    @foreach($bomons as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                            {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                        </option>
                    @endforeach
                </select>
                <!-- Bộ lọc theo Chức vụ -->
                <select name="ChucVu" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Chức vụ / Vai trò --</option>
                    <option value="co_chuc_vu" {{ request('ChucVu') == 'co_chuc_vu' ? 'selected' : '' }}> Đang giữ chức vụ (TK / TBM)</option>
                    <option value="TK" {{ request('ChucVu') == 'TK' ? 'selected' : '' }}> Chỉ Trưởng Khoa (TK)</option>
                    <option value="TBM" {{ request('ChucVu') == 'TBM' ? 'selected' : '' }}> Chỉ Trưởng Bộ Môn (TBM)</option>
                    <option value="GV" {{ request('ChucVu') == 'GV' ? 'selected' : '' }}>Giảng viên thường (Chưa bổ nhiệm)</option>
                </select>
                @if(request()->anyFilled(['search', 'MaBoMon', 'ChucVu']))
                    <a href="{{ route('giangvien.index', ['tab' => 'taikhoan']) }}" class="btn btn-outline-secondary px-3 flex-shrink-0"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <button type="button" class="btn btn-warning text-dark shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#importTBM_TK_Modal">
                    <i class="fa-solid fa-user-shield me-1"></i> Upload DS TK / TBM
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng Tài Khoản & Phân Quyền Chức Vụ -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-solid fa-user-shield me-2 text-primary"></i> Quản Lý Tài Khoản Portal Giảng Viên &amp; Phân Quyền Chức Vụ</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $giangviens->total() }} tài khoản</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3" width="22%">GIẢNG VIÊN / MÃ GV</th>
                        <th class="py-3" width="22%">TÀI KHOẢN GV GỐC (PORTAL GV)</th>
                        <th class="py-3" width="28%">CHỨC VỤ &amp; TÀI KHOẢN CHỨC NĂNG</th>
                        <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                        <th class="pe-4 py-3 text-center" width="16%">THAO TÁC TÀI KHOẢN</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($giangviens as $gv)
                    @php
                        $tk = $gv->taiKhoan;
                        $isActive = $tk ? (bool)$tk->TrangThai : true;

                        // Xác định chức vụ Trưởng khoa / Trưởng bộ môn
                        $isTK = in_array($gv->HoTen, $truongKhoaNames ?? []) 
                            || ($tk && $tk->MaVaiTro === 'VT05') 
                            || (isset($tkChucVus) && $tkChucVus->contains(fn($t) => str_starts_with($t->TenDangNhap, 'TK_') && str_ends_with($t->TenDangNhap, '_' . $gv->MaGV)));
                        
                        $isTBM = in_array($gv->HoTen, $truongBoMonNames ?? []) 
                            || ($tk && $tk->MaVaiTro === 'VT04') 
                            || (isset($tkChucVus) && $tkChucVus->contains(fn($t) => str_starts_with($t->TenDangNhap, 'TBM_') && str_ends_with($t->TenDangNhap, '_' . $gv->MaGV)));
                        
                        $hasChucVu = $isTK || $isTBM;
                        $tkChucVuObj = isset($tkChucVus) ? $tkChucVus->first(fn($t) => str_ends_with($t->TenDangNhap, '_' . $gv->MaGV)) : null;
                    @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark fs-6">
                                {{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}
                            </div>
                            <div class="text-muted small mt-0.5">
                                <span class="badge-code">{{ $gv->MaGV }}</span> &bull; {{ $gv->boMon->TenBoMon ?? 'Chưa gán bộ môn' }}
                            </div>
                        </td>
                        <td class="py-3">
                            <div>
                                <span class="badge bg-light text-primary border px-2.5 py-1 fs-6 font-monospace">
                                    <i class="fa-solid fa-id-badge me-1"></i>{{ $tk->TenDangNhap ?? $gv->MaGV }}
                                </span>
                            </div>
                            <div class="small text-muted mt-1">
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên (VT02)
                                </span>
                            </div>
                        </td>
                        <td class="py-3">
                            @if($isTK)
                                <div>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold fs-7">
                                        <i class="fa-solid fa-crown me-1"></i>Trưởng Khoa (VT05)
                                    </span>
                                </div>
                                <div class="small text-dark font-monospace mt-1">
                                    <i class="fa-solid fa-shield-halved me-1 text-danger"></i>TK chức vụ: <strong>{{ $tkChucVuObj->TenDangNhap ?? ('TK_' . ($gv->boMon->khoa->MaKhoa ?? 'CNTT') . '_' . $gv->MaGV) }}</strong>
                                </div>
                            @elseif($isTBM)
                                <div>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold fs-7">
                                        <i class="fa-solid fa-medal me-1"></i>Trưởng Bộ Môn (VT04)
                                    </span>
                                </div>
                                <div class="small text-dark font-monospace mt-1">
                                    <i class="fa-solid fa-shield me-1 text-warning"></i>TK chức vụ: <strong>{{ $tkChucVuObj->TenDangNhap ?? ('TBM_' . ($gv->MaBoMon ?? 'BM') . '_' . $gv->MaGV) }}</strong>
                                </div>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                                    <i class="fa-solid fa-user me-1 text-muted"></i>Giảng viên thường
                                </span>
                                <div class="small text-muted mt-0.5">Chưa bổ nhiệm chức vụ</div>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if($isActive)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #dc2626;"></i> Đã khóa
                                </span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            <div class="d-inline-flex align-items-center gap-1">
                                <!-- Nút Phân quyền chính -->
                                <button type="button" class="btn btn-sm btn-primary px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs" 
                                        onclick="openBoNhiemModal('{{ $gv->MaGV }}', '{{ addslashes($gv->HoTen) }}', '{{ $gv->MaBoMon }}', '{{ $gv->boMon->MaKhoa ?? '' }}', '{{ $isTK ? 'VT05' : ($isTBM ? 'VT04' : ($tk->MaVaiTro ?? 'VT02')) }}')" 
                                        style="font-size: 0.8rem; background-color: #00305a; border-color: #00305a;"
                                        title="Bổ nhiệm chức vụ Trưởng khoa / Trưởng bộ môn">
                                    <i class="fa-solid fa-user-gear"></i>
                                    <span>Phân quyền</span>
                                </button>

                                <!-- Dropdown các thao tác tài khoản -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split px-2 py-1 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thao tác tài khoản khác">
                                        <span class="visually-hidden">Tùy chọn</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="font-size: 0.83rem; min-width: 210px;">
                                        @if($hasChucVu)
                                        <li>
                                            <form action="{{ route('admin.giangvien.huy_bo_nhiem', $gv->MaGV) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn HỦY CHỨC VỤ của giảng viên {{ $gv->HoTen }}? Hệ thống sẽ thu hồi chức vụ và xóa tài khoản chức năng tương ứng.');">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-danger py-1.5 d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-user-xmark text-danger" style="width: 16px;"></i> Hủy chức vụ đã cấp
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        @endif

                                        @if($tk)
                                        <li>
                                            <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" onsubmit="return confirm('Reset mật khẩu tài khoản của {{ $gv->HoTen }} về 123456?');">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-1.5 d-flex align-items-center gap-2 text-dark">
                                                    <i class="fa-solid fa-key text-warning" style="width: 16px;"></i> Cấp lại mật khẩu (123456)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.giangvien.xoa_tai_khoan', $gv->MaGV) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA TÀI KHOẢN PORTAL của giảng viên {{ $gv->HoTen }}? Hồ sơ giảng viên vẫn được giữ nguyên.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger py-1.5 d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-user-slash" style="width: 16px;"></i> Thu hồi tài khoản Portal
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-user-shield text-muted mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold text-secondary">Chưa có dữ liệu tài khoản nào</h6>
                                <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc tìm kiếm hoặc chức vụ.</p>
                                <a href="{{ route('giangvien.index', ['tab' => 'taikhoan']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $giangviens->firstItem() ?? 0 }}–{{ $giangviens->lastItem() ?? 0 }} trên tổng số {{ $giangviens->total() }} tài khoản hệ thống
            </span>
            @if($giangviens->hasPages())
                <div>
                    {{ $giangviens->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
    @endif

<!-- Bottom Info Card: HUIT Lecturer Rules -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="fa-solid fa-rocket text-primary"></i> QUY ĐỊNH HƯỚNG DẪN ĐỀ TÀI &amp; PHÂN BỔ TÀI KHOẢN GIẢNG VIÊN (HUIT):
    </h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">1. Định mức hướng dẫn tối đa</div>
                <div class="small text-muted">Mỗi giảng viên nhận tối đa <strong>&le; 5 nhóm sinh viên</strong> khóa luận trong một học kỳ để đảm bảo chất lượng. Hệ thống tự động khóa đăng ký khi đủ định mức.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">2. Cấp tài khoản &amp; phân quyền Portal</div>
                <div class="small text-muted">Tài khoản Giảng viên được tạo theo Mã GV (đúng 10 ký tự), mật khẩu mặc định: <code>123456</code>. Khi bổ nhiệm Trưởng khoa / Trưởng bộ môn, hệ thống tự động cấp tài khoản chức vụ riêng biệt (<code>TK_...</code> hoặc <code>TBM_...</code>) tách biệt hoàn toàn với tài khoản GV gốc.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">3. Tham gia Hội đồng chấm bảo vệ</div>
                <div class="small text-muted">Giảng viên có học hàm/học vị từ Thạc sĩ trở lên đủ điều kiện tham gia <strong>5 vị trí HĐ</strong>. Tránh xung đột lợi ích (GVHD không chấm phản biện nhóm mình).</div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Giảng Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'giangvien') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-upload me-1"></i>Import Ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Thêm Mới Giảng Viên (Standardized HUIT Modal) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-huit">
        <div class="modal-content modal-content-huit">
            <div class="modal-header-huit">
                <div class="modal-title-huit">
                    + Thêm Mới Giảng Viên Hướng Dẫn & Phản Biện
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('giangvien.store') }}">
                @csrf
                <div class="modal-body modal-create-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Giảng Viên (MaGV) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control form-control-huit" placeholder="GV00000001" minlength="10" maxlength="10" pattern="[A-Za-z0-9_]{10}" title="Mã giảng viên phải có đúng 10 ký tự" required>
                            <div class="form-text small text-muted">Mã GV bắt buộc đúng 10 ký tự (min=10, max=10, ví dụ: <code>GV00000001</code>). Mật khẩu mặc định: <code>123456</code></div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Họ Và Tên Giảng Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control form-control-huit" placeholder="TS. Nguyễn Văn A" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Học Vị <span class="text-danger">*</span></label>
                            <select name="HocVi" class="form-select form-select-huit" required>
                                <option value="Thạc sĩ">Thạc sĩ</option>
                                <option value="Tiến sĩ" selected>Tiến sĩ</option>
                                <option value="Phó Giáo sư">Phó Giáo sư</option>
                                <option value="Giáo sư">Giáo sư</option>
                                <option value="Kỹ sư">Kỹ sư</option>
                                <option value="Cử nhân">Cử nhân</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Bộ Môn Trực Thuộc <span class="text-danger">*</span></label>
                            <select name="MaBoMon" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? 'Khoa CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-2">
                        <div class="col-md-6">
                            <label class="form-label-huit">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control form-control-huit" placeholder="email@huit.edu.vn" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Số Điện Thoại (10 số)</label>
                            <input type="text" name="SoDienThoai" class="form-control form-control-huit" placeholder="0912345678" minlength="10" maxlength="10" pattern="0[0-9]{9}" title="Số điện thoại phải có đúng 10 chữ số bắt đầu bằng số 0">
                            <div class="form-text small text-muted">Số điện thoại phải có đúng 10 chữ số (min=10, max=10).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-huit">
                    <button type="submit" class="btn btn-save-huit">
                        <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Giảng Viên
                    </button>
                    <button type="button" class="btn btn-cancel-huit" data-bs-dismiss="modal">Hủy Bỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL BỔ NHIỆM TRƯỞNG KHOA / TRƯỞNG BỘ MÔN -->
<div class="modal fade" id="boNhiemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2">
                    <i class="fa-solid fa-award text-warning"></i> Bổ Nhiệm Chức Vụ &amp; Cấp Tài Khoản Portal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="boNhiemForm" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="small text-muted">Giảng viên được bổ nhiệm:</div>
                        <div class="fs-6 fw-bold text-dark" id="boNhiemGvName">TS. Nguyễn Văn A</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Chọn chức vụ bổ nhiệm hoặc thao tác <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvTruongKhoa" value="TK" onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-dark" for="cvTruongKhoa">
                                    <i class="fa-solid fa-crown text-danger me-1"></i> Trưởng Khoa (VT05)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvTruongBoMon" value="TBM" checked onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-dark" for="cvTruongBoMon">
                                    <i class="fa-solid fa-medal text-warning me-1"></i> Trưởng Bộ Môn (VT04)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvHuy" value="HUY" onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-danger" for="cvHuy">
                                    <i class="fa-solid fa-user-xmark me-1"></i> Hủy chức vụ đã cấp (Trả về vai trò Giảng viên thường VT02)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Trường chọn Khoa khi bổ nhiệm Trưởng khoa -->
                    <div class="mb-3" id="fieldKhoa" style="display: none;">
                        <label class="form-label fw-bold small text-muted">Khoa đảm nhiệm <span class="text-danger">*</span></label>
                        <select name="ma_khoa" id="selectBoNhiemKhoa" class="form-select rounded-3">
                            <option value="">-- Chọn Khoa --</option>
                            @php
                                $allKhoas = \App\Models\Khoa::orderBy('TenKhoa')->get();
                            @endphp
                            @foreach($allKhoas as $k)
                                <option value="{{ $k->MaKhoa }}">{{ $k->TenKhoa }} ({{ $k->MaKhoa }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-info mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i> Cập nhật quyền Trưởng khoa cho tài khoản của giảng viên
                        </div>
                    </div>

                    <!-- Trường chọn Bộ môn khi bổ nhiệm Trưởng bộ môn -->
                    <div class="mb-3" id="fieldBoMon">
                        <label class="form-label fw-bold small text-muted">Bộ môn đảm nhiệm <span class="text-danger">*</span></label>
                        <select name="ma_bomon" id="selectBoNhiemBoMon" class="form-select rounded-3">
                            <option value="">-- Chọn Bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}">{{ $bm->TenBoMon }} (Khoa {{ $bm->MaKhoa }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-info mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i> Cập nhật quyền Trưởng bộ môn cho tài khoản của giảng viên
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-0" id="alertHuyChucVu" style="display: none;">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> <strong>Lưu ý:</strong> Khi chọn <em>Hủy chức vụ</em>, quyền quản lý Trưởng khoa / Trưởng bộ môn sẽ được thu hồi và tài khoản chuyển về vai trò Giảng viên bình thường.
                    </div>

                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-0" id="alertMatKhauMacDinh">
                        <i class="fa-solid fa-shield-halved me-1"></i> Giảng viên sẽ sử dụng tài khoản liên kết để đăng nhập vào đúng Portal phân quyền.
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMPORT TÀI KHOẢN TRƯỞNG KHOA / TRƯỞNG BỘ MÔN (08_TaiKhoan_TBM_TK_Mau) -->
<div class="modal fade" id="importTBM_TK_Modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.giangvien.import_tbm_tk') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fa-solid fa-file-excel me-2 text-warning"></i>Upload DS Trưởng Khoa &amp; Trưởng Bộ Môn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 px-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>

                    <div class="alert alert-secondary py-2 px-3 small rounded-3 mb-3">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-question me-1 text-primary"></i>Quy tắc đặt tên đăng nhập:</div>
                        <div>&bull; Trưởng khoa: <code>TK_{MaKhoa}_001</code> (Role: <code>VT05</code>)</div>
                        <div>&bull; Trưởng bộ môn: <code>TBM_{MaKhoa}_{MaBoMon}_001</code> (Role: <code>VT04</code>)</div>
                        <div class="mt-1">Dùng file mẫu: <strong>sample_imports/08_TaiKhoan_TBM_TK_Mau</strong></div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-upload me-1"></i> Upload &amp; Khởi Tạo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openBoNhiemModal(gvId, gvName, maBoMon, maKhoa, currentRole) {
    document.getElementById('boNhiemGvName').textContent = gvName + ' (' + gvId + ')';
    document.getElementById('boNhiemForm').action = '/admin/giangvien/' + gvId + '/bo-nhiem';
    
    // Đặt mặc định bộ môn và khoa nếu có
    if (maBoMon) {
        document.getElementById('selectBoNhiemBoMon').value = maBoMon;
    }
    if (maKhoa) {
        document.getElementById('selectBoNhiemKhoa').value = maKhoa;
    }

    if (currentRole === 'VT05') {
        document.getElementById('cvTruongKhoa').checked = true;
    } else if (currentRole === 'VT04') {
        document.getElementById('cvTruongBoMon').checked = true;
    } else {
        document.getElementById('cvTruongBoMon').checked = true;
    }

    toggleChucVuFields();

    const modal = new bootstrap.Modal(document.getElementById('boNhiemModal'));
    modal.show();
}

function toggleChucVuFields() {
    const isTK = document.getElementById('cvTruongKhoa').checked;
    const isTBM = document.getElementById('cvTruongBoMon').checked;
    const isHuy = document.getElementById('cvHuy').checked;

    document.getElementById('fieldKhoa').style.display = isTK ? 'block' : 'none';
    document.getElementById('fieldBoMon').style.display = isTBM ? 'block' : 'none';
    
    const alertHuy = document.getElementById('alertHuyChucVu');
    const alertMk = document.getElementById('alertMatKhauMacDinh');
    if (alertHuy && alertMk) {
        alertHuy.style.display = isHuy ? 'block' : 'none';
        alertMk.style.display = isHuy ? 'none' : 'block';
    }
}
</script>
@endpush
@endsection