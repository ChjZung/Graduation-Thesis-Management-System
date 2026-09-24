@extends('layouts.admin')

@section('page_title', 'Quản Lý Học Kỳ')

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

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-calendar-days text-primary me-2"></i>Quản Lý Danh Sách Học Kỳ & Niên Khóa Khóa Luận</h4>
            <p class="text-muted small mb-0">Kiểm soát mã học kỳ, tên học kỳ, thời gian đào tạo và trạng thái mở đợt khóa luận.</p>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Học Kỳ</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total_hocky'] ?? $hockys->total() }}</span>
                        <span class="small text-muted fw-medium">học kỳ</span>
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
                    <div class="text-muted small fw-medium mb-1">Học Kỳ Đang Mở</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-success">{{ $stats['active_hk'] ?? 'HK 2026-1' }}</span>
                        <span class="small text-success fw-medium">● Hoạt động</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-calendar-check fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Sinh Viên Đợt Này</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ number_format($stats['total_sv'] ?? 1450) }}</span>
                        <span class="small text-muted fw-medium">sinh viên</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-users-line fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Trạng Thái Hệ Thống</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-info">Ổn Định</span>
                        <span class="small text-muted fw-medium">100%</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fa-solid fa-bolt fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('hocky.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 240px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo mã học kỳ, tên năm học...">
                </div>
                <select name="trang_thai" class="form-select" style="min-width: 180px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" {{ request('trang_thai') === '1' ? 'selected' : '' }}>🟢 Đang mở (Hoạt động)</option>
                    <option value="0" {{ request('trang_thai') === '0' ? 'selected' : '' }}>⚪ Đã kết thúc</option>
                </select>
                @if(request('search') || request('trang_thai') !== null)
                    <a href="{{ route('hocky.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.import.template', 'hocky') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                    <i class="fa-solid fa-download me-1"></i> File Mẫu
                </a>
                <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import Excel
                </button>
                <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Học Kỳ
                </button>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Học Kỳ & Niên Khóa Quản Lý Khóa Luận Tốt Nghiệp</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" width="16%">MÃ HỌC KỲ</th>
                            <th class="py-3" width="36%">TÊN HỌC KỲ &amp; NĂM HỌC</th>
                            <th class="py-3" width="28%">THỜI GIAN ĐÀO TẠO</th>
                            <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($hockys as $hk)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="badge-code">{{ $hk->MaHocKy }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold text-dark fs-6">{{ $hk->TenHocKy }}</div>
                                    <div class="small text-muted">Năm học: <span class="fw-semibold text-primary">{{ $hk->NamHoc }}</span></div>
                                </td>
                                <td class="py-3">
                                    <div class="small fw-medium text-dark">
                                        <i class="fa-regular fa-calendar me-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($hk->NgayBatDau)->format('d/m/Y') }} &mdash; {{ \Carbon\Carbon::parse($hk->NgayKetThuc)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="text-center py-3">
                                    @if($hk->TrangThai)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5 fw-medium">
                                            Đã kết thúc
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center py-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('hocky.show', $hk->MaHocKy) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('hocky.edit', $hk->MaHocKy) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('hocky.destroy', $hk->MaHocKy) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa học kỳ này?');">
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
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state-box">
                                        <i class="fa-solid fa-calendar-days text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có dữ liệu Học kỳ nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng thêm mới hoặc nhập từ file Excel.</p>
                                        <a href="{{ route('hocky.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Học Kỳ Mới</a>
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
                Hiển thị {{ $hockys->firstItem() ?? 0 }}–{{ $hockys->lastItem() ?? 0 }} trên tổng số {{ $hockys->total() }} học kỳ hệ thống
            </span>
            @if($hockys->hasPages())
                <div>
                    {{ $hockys->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.hocky.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="importModalLabel">
                        <i class="fa-solid fa-file-excel me-2 text-success"></i>Import Dữ Liệu Học Kỳ Từ Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3 border-0">
                        <i class="fa-solid fa-info-circle me-1"></i>Vui lòng dùng <a href="{{ route('admin.import.template', 'hocky') }}" class="fw-bold text-decoration-underline">File Mẫu Chuẩn</a> để nhập thông tin đúng định dạng.
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

<!-- Modal Thêm Mới Học Kỳ (Matching Reference UI Image 1) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-huit">
        <div class="modal-content modal-content-huit">
            <div class="modal-header-huit">
                <div class="modal-title-huit">
                    + Thêm Mới Học Kỳ Đào Tạo
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('hocky.store') }}">
                @csrf
                <div class="modal-body modal-create-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label-huit">Mã Học Kỳ <span class="text-danger">*</span></label>
                            <input type="text" name="MaHocKy" class="form-control form-control-huit" value="HK2_2627" placeholder="HK2_2627" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Tên Học Kỳ & Niên Khóa <span class="text-danger">*</span></label>
                            <input type="text" name="TenHocKy" class="form-control form-control-huit" value="Học kỳ 2 — Năm học 2026–2027" placeholder="Học kỳ 2 — Năm học 2026–2027" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-huit">Năm Học <span class="text-danger">*</span></label>
                            <input type="text" name="NamHoc" class="form-control form-control-huit" value="2026-2027" placeholder="2026-2027" required>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Thời Gian Bắt Đầu – Kết Thúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" name="NgayBatDau" class="form-control form-control-huit" value="{{ date('Y-02-15') }}" required>
                                <span class="input-group-text bg-white border-0 text-muted px-2">&ndash;</span>
                                <input type="date" name="NgayKetThuc" class="form-control form-control-huit" value="{{ date('Y-06-30') }}" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Trạng Thái Cổng Đăng Ký</label>
                            <select name="TrangThaiCong" class="form-select form-select-huit">
                                <option value="1" selected>Mở đăng ký đề tài</option>
                                <option value="0">Đóng cổng đăng ký</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-huit">Trạng Thái Học Kỳ Hệ Thống</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="UPCOMING" selected>🟢 Chuẩn bị kích hoạt (Upcoming)</option>
                            <option value="1">🟢 Đang diễn ra (Active)</option>
                            <option value="0">⚪ Đã kết thúc (Completed)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer-huit">
                    <button type="submit" class="btn btn-save-huit">
                        <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Học Kỳ
                    </button>
                    <button type="button" class="btn btn-cancel-huit" data-bs-dismiss="modal">Hủy Bỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection