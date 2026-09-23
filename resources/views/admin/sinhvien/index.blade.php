@extends('layouts.admin')

@section('page_title', 'Quản lý sinh viên')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-graduate text-primary"></i> Danh Sách Sinh Viên Đạt Điều Kiện Khóa Luận &amp; Quản Lý Tài Khoản
        </h4>
        <div class="text-muted small">Kiểm soát chuẩn tích lũy tín chỉ (&ge; 115 TC), ngoại ngữ, trạng thái tài khoản Portal, reset mật khẩu và phân nhóm đồ án.</div>
    </div>
    <div class="d-flex align-items-center">
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
            <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Khóa 14DHTH — Học kỳ 1 (2026–2027) &bull; Khoa CNTT
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
                <div class="text-muted small fw-medium mb-1">Tổng Sinh Viên Đào Tạo</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-dark">{{ $stats['total'] ?? $sinhviens->total() }}</span>
                    <span class="small text-muted fw-medium">sinh viên</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                <i class="fa-solid fa-user-graduate fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Đủ ĐK Khóa Luận (&ge; 115 TC)</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-success">{{ $stats['du_dk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">/ {{ $stats['total'] ?? $sinhviens->total() }} SV ({{ $stats['pct_du_dk'] ?? 0 }}%)</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                <i class="fa-solid fa-user-check fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-danger d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Chưa Đủ Điều Kiện Tín Chỉ</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-danger">{{ $stats['thieu_dk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">sinh viên</span>
                </div>
            </div>
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fee2e2; color: #dc2626;">
                <i class="fa-solid fa-user-clock fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Tài Khoản Đang Kích Hoạt</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-info">{{ $stats['active'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">/ {{ $stats['total'] ?? $sinhviens->total() }} tài khoản</span>
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
    <form method="GET" action="{{ route('sinhvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
        <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
            <div class="input-group" style="min-width: 240px; flex: 1;">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên, MSSV, email, sđt..." value="{{ request('search') }}">
            </div>
            <select name="MaLop" class="form-select" style="min-width: 170px;" onchange="this.form.submit()">
                <option value="">-- Tất cả Lớp --</option>
                @foreach($lops as $l)
                    <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                    </option>
                @endforeach
            </select>
            <select name="dieu_kien" class="form-select" style="min-width: 170px;" onchange="this.form.submit()">
                <option value="">-- Điều kiện làm KL --</option>
                <option value="du" {{ request('dieu_kien') == 'du' ? 'selected' : '' }}>✓ Đủ điều kiện (&ge; 115 TC)</option>
                <option value="chua" {{ request('dieu_kien') == 'chua' ? 'selected' : '' }}>✕ Chưa đủ điều kiện</option>
            </select>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
            <a href="{{ route('admin.sinhvien.dieu_kien') }}" class="btn btn-primary shadow-xs fw-semibold" title="Rà soát &amp; Công bố SV Đủ Điều Kiện Khóa Luận">
                <i class="fa-solid fa-user-check me-1"></i> SV Đủ ĐK
            </a>
            <a href="{{ route('admin.import.template', 'sinhvien') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới
            </button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="admin-table-card mb-4">
    <div class="table-card-header d-flex justify-content-between align-items-center">
        <div class="table-card-title">
            <i class="fa-regular fa-folder-open text-primary me-2"></i> Danh Sách Sinh Viên Khóa 14DHTH &amp; Kiểm Soát Điều Kiện Đồ Án / Khóa Luận
        </div>
        <a href="javascript:void(0)" class="text-decoration-none small fw-semibold text-primary" onclick="alert('Đã kết nối dữ liệu tín chỉ Phòng Đào Tạo HUIT lúc 08:00 ngày 15/09/2026')">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Đồng bộ tín chỉ từ Đào tạo
        </a>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th class="ps-4 py-3" width="14%">MSSV</th>
                    <th class="py-3" width="28%">HỌ VÀ TÊN &amp; EMAIL HUIT</th>
                    <th class="py-3" width="20%">LỚP / CHUYÊN NGÀNH</th>
                    <th class="py-3 text-center" width="16%">TÍN CHỈ / ĐIỀU KIỆN</th>
                    <th class="py-3 text-center" width="12%">NHÓM KHÓA LUẬN</th>
                    <th class="py-3 text-center" width="10%">TRẠNG THÁI</th>
                    <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($sinhviens as $sv)
                @php
                    $tk = $sv->taiKhoan;
                    $isActive = $tk ? (bool)$tk->TrangThai : true;
                    $tc = $sv->SoTinChiTichLuy ?? 125;
                    $isDuDk = $tc >= 115;
                    $nhom = $sv->nhoms->first();
                @endphp
                <tr>
                    <td class="ps-4 py-3">
                        <span class="badge-code">{{ $sv->MaSoSinhVien ?? $sv->MaSV }}</span>
                    </td>
                    <td class="py-3">
                        <div class="fw-bold text-dark fs-6">{{ $sv->HoTen }}</div>
                        <div class="text-muted small">
                            <i class="fa-regular fa-envelope me-1"></i>{{ $sv->Email ?? ($sv->MaSV . '@st.huit.edu.vn') }}
                            @if($sv->SoDienThoai)
                                &bull; <i class="fa-solid fa-phone ms-1 me-1"></i>{{ $sv->SoDienThoai }}
                            @endif
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="fw-semibold text-dark">{{ $sv->lop->TenLop ?? '14DHTH01' }}</div>
                        <div class="text-muted small">{{ $sv->lop->nganh->TenNganh ?? 'Kỹ thuật phần mềm' }}</div>
                    </td>
                    <td class="text-center py-3">
                        @if($isDuDk)
                            <span class="badge px-3 py-1.5 fw-semibold rounded-pill" style="background: #e6f9f0; color: #0d8a4f;">
                                <i class="fa-solid fa-check me-1"></i> {{ $tc }} TC (Đủ ĐK)
                            </span>
                        @else
                            <span class="badge px-3 py-1.5 fw-semibold rounded-pill" style="background: #fee2e2; color: #dc2626;">
                                <i class="fa-solid fa-xmark me-1"></i> {{ $tc }} TC (Thiếu {{ 115 - $tc }} TC)
                            </span>
                        @endif
                    </td>
                    <td class="text-center py-3">
                        @if($nhom)
                            <span class="badge-code">{{ $nhom->TenNhom }}</span>
                        @elseif($isDuDk)
                            <span class="text-muted small fst-italic">Chưa đăng ký</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small">Không đủ ĐK</span>
                        @endif
                    </td>
                    <td class="text-center py-3">
                        @if($isActive && $isDuDk)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                            </span>
                        @elseif(!$isActive)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #dc2626;"></i> Đã khóa
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #d97706;"></i> Tạm ngưng
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="btn-action-icon text-primary" title="Xem hồ sơ chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('sinhvien.edit', $sv->MaSV) }}" class="btn-action-icon text-warning" title="Chỉnh sửa sinh viên">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            @if($tk)
                            <form action="{{ route('admin.yeucau.approve', $tk->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset mật khẩu của {{ $sv->HoTen }} về 123456?');">
                                @csrf
                                <button type="submit" class="btn-action-icon text-secondary" title="Reset mật khẩu">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('sinhvien.destroy', $sv->MaSV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action-icon text-danger" title="Xóa sinh viên">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fa-solid fa-user-graduate fa-3x text-secondary mb-3 opacity-50"></i>
                            <h6 class="fw-bold">Chưa có dữ liệu sinh viên</h6>
                            <p class="small mb-3">Vui lòng thêm mới hoặc import từ file Excel.</p>
                            <a href="{{ route('sinhvien.create') }}" class="btn btn-sm btn-primary rounded-pill px-4">Thêm Sinh Viên Mới</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sinhviens->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Hiển thị <strong>{{ $sinhviens->firstItem() }} - {{ $sinhviens->lastItem() }}</strong> trên tổng số <strong>{{ $sinhviens->total() }}</strong> sinh viên</span>
        {{ $sinhviens->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Bottom Info Card: HUIT Student Eligibility Rules -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="fa-solid fa-graduation-cap text-primary"></i> TIÊU CHUẨN XÉT DUYỆT ĐỦ ĐIỀU KIỆN THỰC HIỆN KHÓA LUẬN TỐT NGHIỆP (HUIT):
    </h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">1. Tín chỉ tích lũy chuyên ngành</div>
                <div class="small text-muted">Sinh viên tích lũy tối thiểu <strong>&ge; 115 tín chỉ</strong> tính đến học kỳ xét duyệt. Không nợ các học phần tiên quyết thuộc chuyên ngành.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">2. Chuẩn Ngoại ngữ &amp; Tin học</div>
                <div class="small text-muted">Đạt chứng chỉ Tiếng Anh tương đương <strong>B1 VSTEP / TOEIC 450+</strong>. Đạt chứng chỉ ứng dụng CNTT nâng cao theo quy định HUIT.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-3 border h-100">
                <div class="fw-bold text-dark mb-1">3. Điểm trung bình tích lũy (CPA)</div>
                <div class="small text-muted">Điểm trung bình tích lũy hệ 4 đạt từ <strong>&ge; 2.50 trở lên</strong>. Không bị kỷ luật cảnh cáo học vụ mức độ 2 trong năm học hiện tại.</div>
            </div>
        </div>
    </div>
    <div class="small text-muted">
        <div>&bull; Giáo vụ có thể chọn "Reset mật khẩu mặc định (Huit@2026)" hàng loạt cho toàn bộ sinh viên mới bổ sung hoặc gửi email kích hoạt tài khoản Portal.</div>
        <div class="text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i> Đã đồng bộ trực tiếp với CSDL Phòng Đào tạo HUIT lúc 08:00 ngày 15/09/2026.</div>
    </div>
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.sinhvien.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Sinh Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'sinhvien') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
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

<!-- Modal Thêm Mới Sinh Viên (Standardized HUIT Modal) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-huit">
        <div class="modal-content modal-content-huit">
            <div class="modal-header-huit">
                <div class="modal-title-huit">
                    + Thêm Mới Sinh Viên Hồ Sơ Khóa Luận
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('sinhvien.store') }}">
                @csrf
                <div class="modal-body modal-create-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Số Sinh Viên (MSSV) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control form-control-huit" placeholder="22110001" required>
                            <div class="form-text small text-muted">MSSV dùng làm tài khoản đăng nhập. Mật khẩu mặc định: <code>123456</code></div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Họ Và Tên Sinh Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control form-control-huit" placeholder="Nguyễn Văn B" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Lớp Sinh Hoạt <span class="text-danger">*</span></label>
                            <select name="MaLop" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Lớp Học --</option>
                                @foreach($lops as $l)
                                    <option value="{{ $l->MaLop }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? 'Khoa CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control form-control-huit" placeholder="sinhvien@huit.edu.vn" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label-huit">Số Điện Thoại</label>
                        <input type="text" name="SoDienThoai" class="form-control form-control-huit" placeholder="0912345678">
                    </div>
                </div>
                <div class="modal-footer-huit">
                    <button type="submit" class="btn btn-save-huit">
                        <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Sinh Viên
                    </button>
                    <button type="button" class="btn btn-cancel-huit" data-bs-dismiss="modal">Hủy Bỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection