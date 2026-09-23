@extends('layouts.admin')

@section('page_title', 'Quản Lý Ngành Học')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('import_result'))
        <div class="alert alert-info alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
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
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Quản Lý Danh Sách Ngành Đào Tạo Trực Thuộc Các Khoa</h4>
            <p class="text-muted small mb-0">Theo dõi mã ngành, tên chuyên ngành, tổng số tín chỉ yêu cầu và phân bổ sinh viên thực hiện đồ án tốt nghiệp.</p>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Ngành Đào Tạo</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total_nganh'] ?? $nganhs->total() }}</span>
                        <span class="small text-muted">ngành</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-graduation-cap fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Ngành Khoa CNTT</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['nganh_cntt'] ?? 4 }}</span>
                        <span class="small text-muted">ngành chuyên sâu</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-laptop-code fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Sinh Viên Đồ Án</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['total_sv'] ?? 48 }}</span>
                        <span class="small text-muted">sinh viên</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-user-group fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Trạng Thái Đào Tạo</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-info">{{ $stats['status'] ?? '100%' }}</span>
                        <span class="small text-muted">Chuẩn tín chỉ</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fa-solid fa-bolt fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar & Actions -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <form method="GET" action="{{ route('nganh.index') }}" class="d-flex flex-wrap gap-2 flex-grow-1" style="max-width: 750px;">
            <div class="input-group" style="flex: 1 1 300px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên ngành, mã ngành...">
            </div>
            <select name="ma_khoa" class="form-select" style="max-width: 240px;" onchange="this.form.submit()">
                <option value="">-- Chọn Khoa trực thuộc --</option>
                @if(isset($khoas))
                    @foreach($khoas as $k)
                        <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                            {{ $k->TenKhoa }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if(request('search') || request('ma_khoa'))
                <a href="{{ route('nganh.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
            @endif
            <button type="submit" class="btn btn-primary px-3">Tìm kiếm</button>
        </form>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.import.template', 'nganh') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('nganh.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Ngành Mới
            </a>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Các Ngành Đào Tạo Trực Thuộc Khoa Đào Tạo</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                        <tr>
                            <th class="ps-4 py-3" width="18%">MÃ NGÀNH</th>
                            <th class="py-3" width="30%">TÊN NGÀNH ĐÀO TẠO</th>
                            <th class="py-3" width="22%">KHOA QUẢN LÝ</th>
                            <th class="py-3 text-center" width="14%">TÍN CHỈ YÊU CẦU</th>
                            <th class="py-3 text-center" width="10%">TRẠNG THÁI</th>
                            <th class="pe-4 py-3 text-center" width="10%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($nganhs as $nganh)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge-code">{{ $nganh->MaNganh }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $nganh->TenNganh }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-university me-1 text-primary"></i>{{ $nganh->khoa->TenKhoa ?? 'Chưa gán Khoa' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="fw-bold text-dark">150 TC</span>
                                        <span class="small text-muted">{{ $nganh->lops_count ?? 0 }} lớp học</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Hoạt động
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('nganh.show', $nganh->MaNganh) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('nganh.edit', $nganh->MaNganh) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('nganh.destroy', $nganh->MaNganh) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngành này?');">
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
                                        <i class="fa-solid fa-graduation-cap text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có dữ liệu Ngành học nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng thêm mới hoặc nhập từ file Excel.</p>
                                        <a href="{{ route('nganh.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Ngành Mới</a>
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
                Hiển thị {{ $nganhs->firstItem() ?? 0 }}–{{ $nganhs->lastItem() ?? 0 }} trên tổng số {{ $nganhs->total() }} ngành hệ thống
            </span>
            @if($nganhs->hasPages())
                <div>
                    {{ $nganhs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.nganh.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="importModalLabel">
                        <i class="fa-solid fa-file-excel me-2 text-success"></i>Import Dữ Liệu Ngành Từ Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3 border-0">
                        <i class="fa-solid fa-info-circle me-1"></i>Vui lòng dùng <a href="{{ route('admin.import.template', 'nganh') }}" class="fw-bold text-decoration-underline">File Mẫu Chuẩn</a> để nhập thông tin đúng định dạng.
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