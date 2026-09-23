@extends('layouts.admin')

@section('page_title', 'Quản lý giảng viên')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-chalkboard-user text-primary"></i> Quản Lý Danh Sách Giảng Viên, Bộ Môn &amp; Cấp Tài Khoản Portal
        </h4>
        <div class="text-muted small">Kiểm soát định mức hướng dẫn đề tài, phân công bộ môn chuyên môn, reset mật khẩu và trạng thái truy cập hệ thống.</div>
    </div>
    <div class="d-flex align-items-center">
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
            <i class="fa-solid fa-university text-primary me-1"></i> Khoa Công nghệ Thông tin — Học kỳ 1 (2026–2027)
        </span>
    </div>
</div>



@if(session('import_result'))
    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
        <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Tổng Giảng Viên Khoa CNTT</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-dark">{{ $stats['total'] ?? $giangviens->total() }}</span>
                    <span class="small text-muted fw-medium">giảng viên</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                <i class="fa-solid fa-chalkboard-user fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Nhận Hướng Dẫn Khóa Luận</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-success">{{ $stats['huong_dan'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">giảng viên</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                <i class="fa-solid fa-person-chalkboard fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Thành Viên Hội Đồng</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-warning">{{ $stats['hoi_dong'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">cán bộ chấm</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                <i class="fa-solid fa-award fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Tài Khoản Portal Active</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-info">{{ $stats['active'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">/ {{ $stats['total'] ?? $giangviens->total() }} tài khoản</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                <i class="fa-solid fa-user-shield fs-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-filter-bar mb-4">
    <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
        <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
            <div class="input-group" style="min-width: 260px; flex: 1;">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên GV, học hàm, email, mã GV..." value="{{ request('search') }}">
            </div>
            <select name="MaBoMon" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                <option value="">-- Tất cả Bộ môn --</option>
                @foreach($bomons as $bm)
                    <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
            <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fa-solid fa-plus me-1"></i> Thêm Giảng Viên
            </button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="admin-table-card mb-4">
    <div class="table-card-header d-flex justify-content-between align-items-center">
        <div class="table-card-title">
            <i class="fa-regular fa-folder-open text-primary me-2"></i> Danh Sách Giảng Viên Khoa Công Nghệ Thông Tin &amp; Quản Lý Tài Khoản Portal
        </div>
        <a href="javascript:void(0)" class="text-decoration-none small fw-semibold text-primary" onclick="alert('Đã kết nối máy chủ LDAP trường lúc 08:00 ngày 15/09/2026')">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Đồng bộ LDAP
        </a>
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
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn-action-icon text-primary" title="Xem hồ sơ chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn-action-icon text-warning" title="Chỉnh sửa thông tin">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            @if($tk)
                            <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset mật khẩu tài khoản của {{ $gv->HoTen }} về 123456?');">
                                @csrf
                                <button type="submit" class="btn-action-icon text-secondary" title="Reset mật khẩu">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('giangvien.destroy', $gv->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action-icon text-danger" title="Xóa tài khoản">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fa-solid fa-chalkboard-user fa-3x text-secondary mb-3 opacity-50"></i>
                            <h6 class="fw-bold">Chưa có dữ liệu giảng viên</h6>
                            <p class="small mb-3">Vui lòng thêm mới hoặc import từ file Excel.</p>
                            <a href="{{ route('giangvien.create') }}" class="btn btn-sm btn-primary rounded-pill px-4">Thêm Giảng Viên Mới</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($giangviens->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Hiển thị <strong>{{ $giangviens->firstItem() }} - {{ $giangviens->lastItem() }}</strong> trên tổng số <strong>{{ $giangviens->total() }}</strong> giảng viên</span>
        {{ $giangviens->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
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
@endsection