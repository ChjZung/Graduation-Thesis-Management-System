@extends('layouts.admin')

@section('page_title', 'Quản Lý Khoa')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
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
                        <span class="small text-muted">khoa chuyên môn</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-university fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Khoa Công Nghệ Thông Tin</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['bomon_cntt'] ?? 4 }}</span>
                        <span class="small text-muted">bộ môn trực thuộc</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-check fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-danger d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Giảng Viên Khoa</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-danger">{{ $stats['total_gv'] ?? 0 }}</span>
                        <span class="small text-muted">giảng viên</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fee2e2; color: #dc2626;">
                    <i class="fa-solid fa-user-tie fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Trạng Thái Hoạt Động</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['status'] ?? '100%' }}</span>
                        <span class="small text-muted">Chuẩn kiểm định</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-certificate fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar & Actions -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <form method="GET" action="{{ route('khoa.index') }}" class="d-flex flex-grow-1" style="max-width: 600px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên khoa, mã khoa, trưởng khoa...">
                @if(request('search'))
                    <a href="{{ route('khoa.index') }}" class="btn btn-outline-secondary border-start-0"><i class="fa-solid fa-xmark"></i></a>
                @endif
                <button type="submit" class="btn btn-primary px-3">Tìm kiếm</button>
            </div>
        </form>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.import.template', 'khoa') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('khoa.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Khoa Mới
            </a>
        </div>
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
                    <thead style="background: #f8fafc; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                        <tr>
                            <th class="ps-4 py-3" width="18%">MÃ KHOA</th>
                            <th class="py-3" width="30%">TÊN KHOA CHUYÊN MÔN</th>
                            <th class="py-3" width="22%">TRƯỞNG KHOA / QUẢN LÝ</th>
                            <th class="py-3 text-center" width="15%">QUY MÔ TRỰC THUỘC</th>
                            <th class="py-3 text-center" width="10%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="10%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($khoas as $khoa)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge-code">{{ $khoa->MaKhoa }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $khoa->TenKhoa }}</span>
                                </td>
                                <td>
                                    @php
                                        $gv = $khoa->giaoVus->first();
                                    @endphp
                                    @if($gv)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-regular fa-user text-muted small"></i>
                                            <span class="small fw-medium text-dark">{{ $gv->HoTen ?? $gv->TenGV ?? 'ThS. Quản trị Khoa' }}</span>
                                        </div>
                                    @else
                                        <span class="small text-muted">Ban Chủ nhiệm Khoa</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <span class="badge bg-light text-primary border rounded-pill px-2 py-1 small">
                                            {{ $khoa->bo_mons_count ?? 0 }} Bộ môn
                                        </span>
                                        <span class="badge bg-light text-info border rounded-pill px-2 py-1 small">
                                            {{ $khoa->nganhs_count ?? 0 }} Ngành
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Hoạt động
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
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
@endsection
