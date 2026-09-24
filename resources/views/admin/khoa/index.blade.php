@extends('layouts.admin')

@section('page_title', 'Quản Lý Khoa')

@section('content')
<div class="container-fluid px-0">
    @if(session('import_result'))
        <div class="alert alert-success alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert">
            {!! session('import_result') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('import_warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert">
            {!! session('import_warning') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('import_danger'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert">
            {!! session('import_danger') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Page Title & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">Quản Lý Danh Sách Các Khoa Đào Tạo Trực Thuộc Trường</h4>
            <p class="text-muted small mb-0">Theo dõi mã khoa, tên khoa chuyên môn, trưởng khoa quản lý và tổng số ngành/bộ môn trực thuộc.</p>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Khoa Đào Tạo</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total_khoa'] ?? $khoas->total() }}</span>
                        <span class="small text-muted fw-medium">khoa chuyên môn</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-building-columns fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Khoa Công Nghệ Thông Tin</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['bomon_cntt'] ?? 4 }}</span>
                        <span class="small text-muted fw-medium">bộ môn trực thuộc</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-sitemap fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-danger d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Giảng Viên Khoa</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-danger">{{ $stats['total_gv'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">giảng viên</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fee2e2; color: #dc2626;">
                    <i class="fa-solid fa-chalkboard-user fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Trạng Thái Hoạt Động</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['status'] ?? '100%' }}</span>
                        <span class="small text-muted fw-medium">Chuẩn kiểm định</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-shield-halved fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('khoa.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 260px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên khoa, mã khoa...">
                    @if(request('search'))
                        <a href="{{ route('khoa.index') }}" class="btn btn-outline-secondary border-start-0"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.import.template', 'khoa') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                    <i class="fa-solid fa-download me-1"></i> File Mẫu
                </a>
                <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import Excel
                </button>
                <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Khoa Mới
                </button>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Quản Lý Danh Sách Khoa & Trạng Thái / Đồng Bộ Đào Tạo</span>
            <a href="#" class="text-decoration-none small text-primary fw-medium" onclick="alert('Tính năng đồng bộ LDAP đang cập nhật.'); return false;">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Đồng bộ LDAP
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" width="20%">MÃ KHOA</th>
                            <th class="py-3" width="50%">TÊN KHOA CHUYÊN MÔN</th>
                            <th class="py-3 text-center" width="20%">QUY MÔ TRỰC THUỘC</th>
                            <th class="py-3 text-center" width="10%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="10%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($khoas as $khoa)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="badge-code">{{ $khoa->MaKhoa }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="fw-bold text-dark fs-6">{{ $khoa->TenKhoa }}</span>
                                </td>
                                <td class="text-center py-3">
                                    <div class="d-flex justify-content-center gap-2">
                                        <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1 small">
                                            {{ $khoa->bo_mons_count ?? 0 }} Bộ môn
                                        </span>
                                        <span class="badge bg-light text-info border rounded-pill px-2.5 py-1 small">
                                            {{ $khoa->nganhs_count ?? 0 }} Ngành
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center py-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                                    </span>
                                </td>
                                <td class="pe-4 text-center py-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('khoa.show', $khoa->MaKhoa) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('khoa.edit', $khoa->MaKhoa) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('khoa.destroy', $khoa->MaKhoa) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khoa này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa">
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
                                        <i class="fa-solid fa-university text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có dữ liệu Khoa nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng thêm khoa mới hoặc import từ file Excel.</p>
                                        <a href="{{ route('khoa.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Khoa Mới</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $khoas->firstItem() ?? 0 }}–{{ $khoas->lastItem() ?? 0 }} trên tổng số {{ $khoas->total() }} khoa hệ thống
            </span>
            @if($khoas->hasPages())
                <div>
                    {{ $khoas->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.khoa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="importModalLabel">
                        <i class="fa-solid fa-file-excel me-2 text-success"></i>Import Dữ Liệu Khoa Từ Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3 border-0">
                        <i class="fa-solid fa-info-circle me-1"></i>Vui lòng dùng <a href="{{ route('admin.import.template', 'khoa') }}" class="fw-bold text-decoration-underline">File Mẫu Chuẩn</a> để nhập thông tin đúng định dạng.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-upload me-1"></i> Bắt Đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thêm Mới Khoa Đào Tạo & Phân Công Bộ Môn (Matching Reference UI Image 5) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-huit">
        <div class="modal-content modal-content-huit">
            <div class="modal-header-huit">
                <div class="modal-title-huit">
                    + Thêm Mới Khoa Đào Tạo & Phân Công Bộ Môn
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('khoa.store') }}">
                @csrf
                <div class="modal-body modal-create-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Khoa Viết Tắt <span class="text-danger">*</span></label>
                            <input type="text" name="MaKhoa" class="form-control form-control-huit" value="CNTT" placeholder="CNTT" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Khoa Đào Tạo <span class="text-danger">*</span></label>
                            <input type="text" name="TenKhoa" class="form-control form-control-huit" value="Khoa Công nghệ Thông tin" placeholder="Khoa Công nghệ Thông tin" required>
                        </div>
                    </div>




                    <div class="mb-4">
                        <label class="form-label-huit">Trạng Thái Hoạt Động Hệ Thống</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="Active" selected>🟢 Hoạt động bình thường (Active)</option>
                            <option value="Inactive">🔴 Tạm ngưng</option>
                        </select>
                    </div>

                    <div class="alert-info-huit mb-2">
                        <i class="fa-solid fa-circle-info me-2 text-primary"></i>Thông tin khoa và các bộ môn trực thuộc sẽ tự động liên kết với phân hệ phân công đề tài tốt nghiệp.
                    </div>
                </div>
                <div class="modal-footer-huit">
                    <button type="submit" class="btn btn-save-huit">
                        <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Khoa
                    </button>
                    <button type="button" class="btn btn-cancel-huit" data-bs-dismiss="modal">Hủy Bỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
