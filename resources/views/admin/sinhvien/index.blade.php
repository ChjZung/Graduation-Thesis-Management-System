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

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('import_result'))
    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
        <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-blue d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Tổng Sinh Viên Khóa 14DHTH</div>
                <div class="kpi-value text-primary">{{ $stats['total'] ?? $sinhviens->total() }} <span class="fs-6 text-muted font-normal">sinh viên</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(0, 114, 206, 0.1); color: #0072ce;">
                <i class="fa-solid fa-user-group"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-green d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Đủ ĐK Khóa Luận (&ge; 115 TC)</div>
                <div class="kpi-value text-success">{{ $stats['du_dk'] ?? 0 }} <span class="fs-6 text-muted font-normal">/ {{ $stats['total'] ?? $sinhviens->total() }} SV ({{ $stats['pct_du_dk'] ?? 0 }}%)</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-orange d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Chưa Đủ Điều Kiện Tín Chỉ</div>
                <div class="kpi-value text-danger">{{ $stats['thieu_dk'] ?? 0 }} <span class="fs-6 text-muted font-normal">sinh viên (Học lại)</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-purple d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Tài Khoản Đang Kích Hoạt</div>
                <div class="kpi-value text-purple" style="color: #6366f1;">{{ $stats['active'] ?? 0 }} <span class="fs-6 text-muted font-normal">/ {{ $stats['total'] ?? $sinhviens->total() }} Active</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-filter-bar mb-4">
    <form method="GET" action="{{ route('sinhvien.index') }}" class="row g-2 align-items-center w-100">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên, MSSV, email, số điện thoại..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="MaLop" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả Lớp --</option>
                @foreach($lops as $l)
                    <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="dieu_kien" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Đủ điều kiện làm KL --</option>
                <option value="du" {{ request('dieu_kien') == 'du' ? 'selected' : '' }}>✓ Đủ điều kiện (&ge; 115 TC)</option>
                <option value="chua" {{ request('dieu_kien') == 'chua' ? 'selected' : '' }}>✕ Chưa đủ điều kiện</option>
            </select>
        </div>
        <div class="col-md-3 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.import.template', 'sinhvien') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('sinhvien.create') }}" class="btn btn-sm btn-success rounded-pill px-3 shadow-xs fw-semibold">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới
            </a>
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
            <thead>
                <tr>
                    <th width="12%">MSSV / TÀI KHOẢN</th>
                    <th width="22%">HỌ VÀ TÊN &amp; EMAIL HUIT</th>
                    <th width="18%">LỚP / CHUYÊN NGÀNH</th>
                    <th width="18%">TÍN CHỈ / ĐIỀU KIỆN KL</th>
                    <th width="15%">NHÓM KHÓA LUẬN</th>
                    <th width="10%">TRẠNG THÁI TK</th>
                    <th width="12%" class="text-center">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sinhviens as $sv)
                @php
                    $tk = $sv->taiKhoan;
                    $isActive = $tk ? (bool)$tk->TrangThai : true;
                    $tc = $sv->SoTinChiTichLuy ?? 125;
                    $isDuDk = $tc >= 115;
                    $nhom = $sv->nhoms->first();
                @endphp
                <tr>
                    <td>
                        <span class="badge-code">{{ $sv->MaSoSinhVien ?? $sv->MaSV }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $sv->HoTen }}</div>
                        <div class="text-muted small">
                            <i class="fa-regular fa-envelope me-1"></i>{{ $sv->Email ?? ($sv->MaSV . '@st.huit.edu.vn') }}
                            @if($sv->SoDienThoai)
                                &bull; <i class="fa-solid fa-phone ms-1 me-1"></i>{{ $sv->SoDienThoai }}
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-secondary">{{ $sv->lop->TenLop ?? '14DHTH01' }}</div>
                        <div class="text-muted small">{{ $sv->lop->nganh->TenNganh ?? 'Kỹ thuật phần mềm' }}</div>
                    </td>
                    <td>
                        @if($isDuDk)
                            <span class="badge px-3 py-2 fw-semibold rounded-pill" style="background: #e6f9f0; color: #0d8a4f;">
                                <i class="fa-solid fa-check me-1"></i> {{ $tc }} TC (Đủ điều kiện)
                            </span>
                        @else
                            <span class="badge px-3 py-2 fw-semibold rounded-pill" style="background: #fee2e2; color: #dc2626;">
                                <i class="fa-solid fa-xmark me-1"></i> {{ $tc }} TC (Thiếu {{ 115 - $tc }} TC)
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($nhom)
                            <span class="badge-code">{{ $nhom->TenNhom }}</span>
                        @elseif($isDuDk)
                            <span class="text-muted small fst-italic">Chưa đăng ký nhóm</span>
                        @else
                            <span class="badge-status badge-status-rejected">Không đủ điều kiện</span>
                        @endif
                    </td>
                    <td>
                        @if($isActive && $isDuDk)
                            <span class="text-success fw-semibold small d-inline-flex align-items-center gap-1">
                                <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span> Hoạt động
                            </span>
                        @elseif(!$isActive)
                            <span class="text-danger fw-semibold small d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Đã khóa
                            </span>
                        @else
                            <span class="text-warning fw-semibold small d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Khóa chọn ĐT
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
@endsection