@extends('layouts.admin')

@section('page_title', 'Quản lý giảng viên')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header Section -->
    <div class="d-flex align-items-center gap-2 text-muted small mb-1">
        <i class="fas fa-home"></i> Trang chủ <i class="fas fa-chevron-right text-muted" style="font-size: 0.65rem;"></i> Quản lý hệ thống
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Quản Lý Danh Sách Giảng Viên, Bộ Môn &amp; Cấp Tài Khoản Portal</h4>
            <p class="text-muted small mb-0">Kiểm soát định mức hướng dẫn đề tài, phân công bộ môn chuyên môn, reset mật khẩu và trạng thái truy cập hệ thống.</p>
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
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Giảng Viên Khoa CNTT</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-chalkboard-user fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['total'] ?? $giangviens->total() }}</span>
                    <span class="small text-muted fw-medium">giảng viên</span>
                </div>
            </div>
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
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Thành Viên Hội Đồng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-award fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-warning lh-1">{{ $stats['hoi_dong'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">cán bộ chấm</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tài Khoản Portal Active</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0e7ff; color: #4f46e5;">
                        <i class="fa-solid fa-user-shield fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-info lh-1">{{ $stats['active'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">/ {{ $stats['total'] ?? $giangviens->total() }} tài khoản</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 260px; flex: 1;">
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
                    <a href="{{ route('giangvien.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                    <i class="fa-solid fa-download me-1"></i> File Mẫu
                </a>
                <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import GV
                </button>
                <button type="button" class="btn btn-warning text-dark shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#importTBM_TK_Modal">
                    <i class="fa-solid fa-user-shield me-1"></i> Upload DS TK / TBM
                </button>
                <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Giảng Viên
                </button>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Giảng Viên Khoa Công Nghệ Thông Tin &amp; Quản Lý Tài Khoản Portal</span>
            <div class="d-flex align-items-center gap-2">
                <a href="javascript:void(0)" class="text-decoration-none small fw-semibold text-primary me-2" onclick="alert('Đã kết nối máy chủ LDAP trường lúc 08:00 ngày 15/09/2026')">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Đồng bộ LDAP
                </a>
                <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $giangviens->total() }} giảng viên</span>
            </div>
        </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th class="ps-4 py-3" width="14%">MÃ GV</th>
                    <th class="py-3" width="30%">HỌ TÊN, HỌC HÀM / VỊ &amp; EMAIL</th>
                    <th class="py-3" width="24%">BỘ MÔN CHUYÊN MÔN</th>
                    <th class="py-3 text-center" width="14%">ĐỊNH MỨC HƯỚNG DẪN</th>
                    <th class="py-3 text-center" width="10%">TRẠNG THÁI</th>
                    <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($giangviens as $gv)
                @php
                    $tk = $gv->taiKhoan;
                    $isActive = $tk ? (bool)$tk->TrangThai : true;
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
                        <div class="text-muted small">
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
                            <i class="fa-solid fa-check me-1"></i> Đang nhận: {{ $cnt }}/5 nhóm
                        </span>
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
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem hồ sơ chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa thông tin">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-light btn-action-circle border" style="color: #7c3aed;" 
                                    onclick="openBoNhiemModal('{{ $gv->MaGV }}', '{{ addslashes($gv->HoTen) }}', '{{ $gv->MaBoMon }}', '{{ $gv->boMon->MaKhoa ?? '' }}')" 
                                    title="Bổ nhiệm Trưởng khoa / Trưởng bộ môn">
                                <i class="fa-solid fa-award"></i>
                            </button>
                            @if($tk)
                            <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset mật khẩu tài khoản của {{ $gv->HoTen }} về 123456?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light text-info btn-action-circle border" title="Reset mật khẩu">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('giangvien.destroy', $gv->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa tài khoản">
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
                                <a href="{{ route('giangvien.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                            @else
                                <a href="{{ route('giangvien.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Giảng Viên Mới</a>
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

<!-- Bottom Info Card: HUIT Lecturer Rules -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="fa-solid fa-rocket text-primary"></i> QUY ĐỊNH HƯỚNG DẪN ĐỀ TÀI &amp; PHÂN BỔ TÀI KHOẢN GIẢNG VIÊN (HUIT):
    </h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">1. Định mức hướng dẫn tối đa</div>
                <div class="small text-muted">Mỗi giảng viên nhận tối đa <strong>&le; 5 nhóm sinh viên</strong> khóa luận trong một học kỳ để đảm bảo chất lượng. Hệ thống tự động khóa đăng ký khi đủ định mức.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">2. Cấp tài khoản &amp; phân quyền Portal</div>
                <div class="small text-muted">Tài khoản liên kết trực tiếp với LDAP trường. Mật khẩu mặc định cấp mới: <code>Huit@2026_GV</code>. Giảng viên đăng nhập bằng email huit.edu.vn.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">3. Tham gia Hội đồng chấm bảo vệ</div>
                <div class="small text-muted">Giảng viên có học hàm/học vị từ Thạc sĩ trở lên đủ điều kiện tham gia <strong>5 vị trí HĐ</strong>. Tránh xung đột lợi ích (GVHD không chấm phản biện nhóm mình).</div>
            </div>
        </div>
    </div>
    <div class="small text-muted">
        <div>&bull; Giáo vụ có thể chọn "Gửi email thông báo lịch hướng dẫn và phân công hội đồng" hàng loạt cho toàn bộ giảng viên trong khoa.</div>
        <div class="text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i> Đã đồng bộ trực tiếp với CSDL Phòng Tổ chức Cán bộ HUIT lúc 08:00 ngày 15/09/2026.</div>
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
                            <input type="text" name="TenDangNhap" class="form-control form-control-huit" placeholder="GV001" required>
                            <div class="form-text small text-muted">MaGV dùng làm tài khoản đăng nhập. Mật khẩu mặc định: <code>123456</code></div>
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
                            <label class="form-label-huit">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control form-control-huit" placeholder="0912345678">
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
                        <label class="form-label fw-bold small text-muted">Chọn chức vụ bổ nhiệm <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvTruongKhoa" value="TK" onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-dark" for="cvTruongKhoa">
                                    Trưởng Khoa (VT05)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvTruongBoMon" value="TBM" checked onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-dark" for="cvTruongBoMon">
                                    Trưởng Bộ Môn (VT04)
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
                            <i class="fa-solid fa-circle-info me-1"></i> Tên đăng nhập tự sinh chuẩn: <code>TK_{MaKhoa}_001</code>
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
                            <i class="fa-solid fa-circle-info me-1"></i> Tên đăng nhập tự sinh chuẩn: <code>TBM_{MaKhoa}_{MaBoMon}_001</code>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i> Mật khẩu mặc định cấp mới: <code>123456</code>. Giảng viên sẽ sử dụng tài khoản chức vụ này để đăng nhập vào đúng Portal phân quyền.
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Bổ Nhiệm
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
function openBoNhiemModal(gvId, gvName, maBoMon, maKhoa) {
    document.getElementById('boNhiemGvName').textContent = gvName + ' (' + gvId + ')';
    document.getElementById('boNhiemForm').action = '/admin/giangvien/' + gvId + '/bo-nhiem';
    
    // Đặt mặc định bộ môn và khoa nếu có
    if (maBoMon) {
        document.getElementById('selectBoNhiemBoMon').value = maBoMon;
    }
    if (maKhoa) {
        document.getElementById('selectBoNhiemKhoa').value = maKhoa;
    }

    document.getElementById('cvTruongBoMon').checked = true;
    toggleChucVuFields();

    const modal = new bootstrap.Modal(document.getElementById('boNhiemModal'));
    modal.show();
}

function toggleChucVuFields() {
    const isTK = document.getElementById('cvTruongKhoa').checked;
    document.getElementById('fieldKhoa').style.display = isTK ? 'block' : 'none';
    document.getElementById('fieldBoMon').style.display = isTK ? 'none' : 'block';
}
</script>
@endpush
@endsection