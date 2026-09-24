@extends('layouts.admin')

@section('page_title', 'Quản Lý Bộ Môn')

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

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-layer-group text-primary me-2"></i>Quản Lý Danh Sách Bộ Môn Trực Thuộc Các Khoa Đào Tạo</h4>
            <p class="text-muted small mb-0">Quản lý mã bộ môn, tên bộ môn chuyên môn, phân bổ giảng viên và liên kết đề tài tốt nghiệp cho sinh viên.</p>
        </div>
        <div>
            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small">
                <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Khoa Công nghệ Thông tin — Học kỳ 1 (2026-2027)
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Bộ Môn Toàn Trường</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total_bomon'] ?? $bomons->total() }}</span>
                        <span class="small text-muted fw-medium">bộ môn</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-sitemap fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Bộ Môn Khoa CNTT</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['bomon_cntt'] ?? 4 }}</span>
                        <span class="small text-muted fw-medium">bộ môn chuyên môn</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-laptop-code fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Giảng Viên Phụ Trách</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['total_gv'] ?? 18 }}</span>
                        <span class="small text-muted fw-medium">giảng viên CNTT</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-user-graduate fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Trạng Thái Hoạt Động</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-info">{{ $stats['status'] ?? '100%' }}</span>
                        <span class="small text-muted fw-medium">Đã phân bổ</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('bomon.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 240px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên bộ môn, mã bộ môn...">
                </div>
                <select name="ma_khoa" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Khoa trực thuộc --</option>
                    @if(isset($khoas))
                        @foreach($khoas as $k)
                            <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                                {{ $k->TenKhoa }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @if(request('search') || request('ma_khoa'))
                    <a href="{{ route('bomon.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.import.template', 'bomon') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                    <i class="fa-solid fa-download me-1"></i> File Mẫu
                </a>
                <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import Excel
                </button>
                <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Bộ Môn
                </button>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Các Bộ Môn Chuyên Môn Trực Thuộc Khoa Đào Tạo</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" width="14%">MÃ BỘ MÔN</th>
                            <th class="py-3" width="26%">TÊN BỘ MÔN CHUYÊN MÔN</th>
                            <th class="py-3" width="24%">TRƯỞNG BỘ MÔN</th>
                            <th class="py-3" width="20%">KHOA TRỰC THUỘC</th>
                            <th class="py-3 text-center" width="8%">SỐ GV</th>
                            <th class="py-3 text-center" width="8%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($bomons as $bomon)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="badge-code">{{ $bomon->MaBoMon }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="fw-bold text-dark fs-6">{{ $bomon->TenBoMon }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                            <i class="fa-solid fa-user-gear"></i>
                                        </div>
                                        <span class="small fw-semibold text-dark">{{ $bomon->TruongBoMon ?: 'Chưa bổ nhiệm' }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-1.5 fw-medium">
                                        <i class="fa-solid fa-university me-1 text-primary"></i>{{ $bomon->khoa->TenKhoa ?? 'Chưa gán Khoa' }}
                                    </span>
                                </td>
                                <td class="text-center py-3">
                                    <span class="fw-bold text-dark">{{ $bomon->giang_viens_count ?? 0 }} GV</span>
                                </td>
                                <td class="text-center py-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                                    </span>
                                </td>
                                <td class="pe-4 text-center py-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('bomon.show', $bomon->MaBoMon) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('bomon.edit', $bomon->MaBoMon) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('bomon.destroy', $bomon->MaBoMon) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ môn này?');">
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
                                        <i class="fa-solid fa-layer-group text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có dữ liệu Bộ môn nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng thêm mới hoặc nhập từ file Excel.</p>
                                        <a href="{{ route('bomon.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Bộ Môn Mới</a>
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
                Hiển thị {{ $bomons->firstItem() ?? 0 }}–{{ $bomons->lastItem() ?? 0 }} trên tổng số {{ $bomons->total() }} bộ môn hệ thống
            </span>
            @if($bomons->hasPages())
                <div>
                    {{ $bomons->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.bomon.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="importModalLabel">
                        <i class="fa-solid fa-file-excel me-2 text-success"></i>Import Dữ Liệu Bộ Môn Từ Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3 border-0">
                        <i class="fa-solid fa-info-circle me-1"></i>Vui lòng dùng <a href="{{ route('admin.import.template', 'bomon') }}" class="fw-bold text-decoration-underline">File Mẫu Chuẩn</a> để nhập thông tin đúng định dạng.
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

<!-- Modal Thêm Mới Bộ Môn Chuyên Môn (Matching Reference UI Image 4) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-huit">
        <div class="modal-content modal-content-huit">
            <div class="modal-header-huit">
                <div class="modal-title-huit">
                    + Thêm Mới Bộ Môn Chuyên Môn
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bomon.store') }}">
                @csrf
                <div class="modal-body modal-create-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Bộ Môn <span class="text-danger">*</span></label>
                            <input type="text" name="MaBoMon" class="form-control form-control-huit" value="BM_IoT" placeholder="BM_IoT" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Bộ Môn Chuyên Môn <span class="text-danger">*</span></label>
                            <input type="text" name="TenBoMon" class="form-control form-control-huit" value="Bộ môn Internet vạn vật (IoT) & Nhúng" placeholder="Bộ môn Internet vạn vật (IoT) & Nhúng" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Thuộc Khoa Đào Tạo <span class="text-danger">*</span></label>
                            <select name="MaKhoa" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Khoa Đào Tạo --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }} (CNTT)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Trưởng Bộ Môn</label>
                            <select name="TruongBoMon" class="form-select form-select-huit">
                                <option value="">-- Chọn giảng viên --</option>
                                @if(isset($giangViens))
                                    @foreach($giangViens as $gv)
                                        @php $val = ($gv->HocVi ? $gv->HocVi . '. ' : '') . $gv->HoTen . " ({$gv->MaGV})"; @endphp
                                        <option value="{{ $val }}">
                                            {{ $val }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label-huit">Trạng Thái Hoạt Động</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="Active" selected>🟢 Hoạt động bình thường (Active)</option>
                            <option value="Inactive">🔴 Tạm ngưng</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer-huit">
                    <button type="submit" class="btn btn-save-huit">
                        <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Bộ Môn
                    </button>
                    <button type="button" class="btn btn-cancel-huit" data-bs-dismiss="modal">Hủy Bỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection