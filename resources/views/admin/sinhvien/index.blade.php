@extends('layouts.admin')

@section('page_title', 'Quản lý sinh viên')

@section('content')
<div class="container-fluid px-0">
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

    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-user-graduate text-primary me-2"></i>Quản Lý Sinh Viên</h4>
            <p class="text-muted small mb-0">Quản lý danh sách hồ sơ sinh viên, thông tin lớp học, tích lũy tín chỉ và điều kiện làm khóa luận tốt nghiệp.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalImportSinhVien">
                <i class="fa-solid fa-file-excel me-1"></i> Import Excel
            </button>
            <a href="{{ route('sinhvien.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs">
                <i class="fa-solid fa-user-plus me-1"></i> Thêm Sinh Viên Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Sinh Viên Hồ Sơ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-user-graduate fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['total'] ?? $sinhviens->total() }}</span>
                    <span class="small text-muted fw-medium">sinh viên</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đủ ĐK Khóa Luận (&ge; 115 TC)</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-user-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['du_dk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">/ {{ $statsSv['total'] ?? $sinhviens->total() }} SV ({{ $statsSv['pct_du_dk'] ?? 0 }}%)</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Chưa Đủ Điều Kiện Tín Chỉ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-user-clock fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['thieu_dk'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">sinh viên</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tài Khoản Kích Hoạt</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-id-card-clip fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['active'] ?? 0 }}</span>
                    <span class="small text-muted fw-medium">tài khoản</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('sinhvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 260px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 border-end-0 ps-0" placeholder="Tìm theo tên, MSSV, email, sđt..." value="{{ request('search') }}">
                    <button class="btn btn-primary px-3 fw-semibold" type="submit">Tìm</button>
                </div>
                <select name="MaHocKy_filter" class="form-select" style="min-width: 240px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học Kỳ --</option>
                    @foreach($hocKys as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy_filter') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>
                <select name="MaLop" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Lớp --</option>
                    @foreach($lops as $l)
                        <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                            {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                        </option>
                    @endforeach
                </select>
                <select name="trang_thai_hoc_tap" class="form-select" style="min-width: 190px;" onchange="this.form.submit()">
                    <option value="">-- Trạng thái học tập --</option>
                    <option value="Đang học" {{ request('trang_thai_hoc_tap') == 'Đang học' ? 'selected' : '' }}>Đang học</option>
                    <option value="Tạm dừng" {{ request('trang_thai_hoc_tap') == 'Tạm dừng' ? 'selected' : '' }}>Tạm dừng</option>
                    <option value="Đã tốt nghiệp" {{ request('trang_thai_hoc_tap') == 'Đã tốt nghiệp' ? 'selected' : '' }}>Đã tốt nghiệp</option>
                </select>
                <select name="dieu_kien" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                    <option value="">-- Điều kiện làm KL --</option>
                    <option value="du" {{ request('dieu_kien') == 'du' ? 'selected' : '' }}>Đủ điều kiện (&ge; 115 TC)</option>
                    <option value="chua" {{ request('dieu_kien') == 'chua' ? 'selected' : '' }}>Chưa đủ điều kiện</option>
                </select>
                @if(request()->anyFilled(['search', 'MaHocKy_filter', 'MaLop', 'trang_thai_hoc_tap', 'dieu_kien']))
                    <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('sinhvien.create') }}" class="btn btn-primary shadow-xs fw-semibold rounded-pill px-3">
                    <i class="fa-solid fa-user-plus me-1"></i> Thêm Sinh Viên Mới
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card: Danh Sách Sinh Viên -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Sinh Viên</span>
            <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $sinhviens->total() }} sinh viên</span>
        </div>

        <div class="table-responsive">
            <table class="table admin-table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3" width="13%">MSSV</th>
                        <th class="py-3" width="24%">HỌ VÀ TÊN &amp; LIÊN LẠC</th>
                        <th class="py-3" width="20%">LỚP / NGÀNH / KHÓA</th>
                        <th class="py-3 text-center" width="15%">TÍN CHỈ / GPA</th>
                        <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                        <th class="py-3 text-center" width="10%">ĐIỀU KIỆN KL</th>
                        <th class="pe-4 py-3 text-center" width="6%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($sinhviens as $sv)
                    @php
                        $tk = $sv->taiKhoan;
                        $isActive = $tk ? (bool)$tk->TrangThai : true;
                        $tc = (int)($sv->SoTinChiTichLuy ?? 0);
                        $gpa = (float)($sv->DiemTichLuy ?? 0.0);
                        $isDuDk = $sv->isDuDieuKien();
                        $nhom = $sv->nhoms->first();
                    @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="badge-code">{{ $sv->MaSV }}</span>
                        </td>
                        <td class="py-3">
                            <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6">{{ $sv->HoTen }}</a>
                            <div class="text-muted small">
                                {{ $sv->Email ?? ($sv->MaSV . '@st.huit.edu.vn') }}
                                @if($sv->SoDienThoai)
                                    &bull; {{ $sv->SoDienThoai }}
                                @endif
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-dark">{{ $sv->lop->TenLop ?? 'Chưa phân lớp' }}</div>
                            <div class="small text-muted">{{ $sv->lop->nganh->TenNganh ?? ($sv->nganh->TenNganh ?? 'Khoa CNTT') }} &bull; Khóa: {{ $sv->KhoaHoc ?? ($sv->lop->KhoaHoc ?? '2022') }}</div>
                        </td>
                        <td class="text-center py-3">
                            <div class="fw-bold {{ $tc >= 115 ? 'text-success' : 'text-danger' }}">
                                {{ $tc }} TC
                            </div>
                            <div class="small text-muted">
                                GPA: <span class="fw-semibold text-dark">{{ number_format($gpa, 2) }}</span> / 4.0
                            </div>
                        </td>
                        <td class="text-center py-3">
                            @if($sv->TrangThai === 'Đang học')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                    Đang học
                                </span>
                            @elseif($sv->TrangThai === 'Đủ điều kiện')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                    Đủ điều kiện
                                </span>
                            @elseif($sv->TrangThai === 'Tạm dừng')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                                    Tạm dừng
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">
                                    {{ $sv->TrangThai ?? 'Chưa rõ' }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if($isDuDk)
                                <span class="badge px-2.5 py-1 fw-semibold rounded-pill" style="background: #e6f9f0; color: #0d8a4f; border: 1px solid #bbf7d0;">
                                    Đủ ĐK
                                </span>
                            @else
                                <span class="badge px-2.5 py-1 fw-semibold rounded-pill" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;" title="Thiếu {{ max(0, 115 - $tc) }} TC hoặc GPA < 2.0">
                                    Chưa ĐK
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết 360°">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('sinhvien.edit', $sv->MaSV) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa hồ sơ">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('sinhvien.destroy', $sv->MaSV) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận xóa sinh viên {{ $sv->HoTen }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa sinh viên">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-user-graduate text-muted mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold text-secondary">Chưa có dữ liệu sinh viên phù hợp</h6>
                                <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc hoặc thêm mới sinh viên.</p>
                                @if(request()->anyFilled(['search', 'MaHocKy_filter', 'MaLop', 'trang_thai_hoc_tap', 'dieu_kien']))
                                    <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                                @else
                                    <a href="{{ route('sinhvien.create') }}" class="btn btn-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-user-plus me-1"></i> Thêm Sinh Viên Mới</a>
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
                Hiển thị {{ $sinhviens->firstItem() ?? 0 }}–{{ $sinhviens->lastItem() ?? 0 }} trên tổng số {{ $sinhviens->total() }} sinh viên
            </span>
            @if($sinhviens->hasPages())
                <div>
                    {{ $sinhviens->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Import Danh Sách Sinh Viên -->
<div class="modal fade" id="modalImportSinhVien" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i> Import Danh Sách Sinh Viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.sinhvien.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small">Hỗ trợ các định dạng <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code>.</div>
                    </div>
                    <div class="alert alert-info rounded-3 mb-0 small">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-info me-1"></i> Hướng dẫn nhập liệu:</div>
                        <div>Tải <a href="{{ route('admin.import.template', 'sinhvien') }}" class="fw-bold text-decoration-underline text-primary">File mẫu Excel Sinh Viên</a>.</div>
                        <ul class="mb-0 mt-1 ps-3 text-muted">
                            <li>Cột <code>MSSV</code> và <code>HoTen</code> là bắt buộc.</li>
                            <li>Hệ thống tự động liên kết với lớp và tạo tài khoản đăng nhập portal.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white">
                        <i class="fa-solid fa-upload me-1"></i> Tải Lên &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection