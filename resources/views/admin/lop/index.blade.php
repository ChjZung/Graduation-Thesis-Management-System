@extends('layouts.admin')

@section('page_title', 'Quản Lý Lớp Học')

@section('content')
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

<div class="card card-premium">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-users-rectangle me-2"></i> Danh Sách Lớp Học</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'lop') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('lop.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="35%">Mã Lớp & Tên Lớp</th>
                        <th width="30%">Ngành / Khoa</th>
                        <th width="15%">Khóa Học</th>
                        <th width="10%" class="text-center">Số SV</th>
                        <th width="10%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lops as $lop)
                        <tr>
                            <td>
                                <span class="badge-code me-2">{{ $lop->MaLop }}</span>
                                <span class="fw-bold text-dark">{{ $lop->TenLop }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary">{{ $lop->nganh->TenNganh ?? 'Chưa gán ngành' }}</div>
                                <div class="small text-muted">{{ $lop->nganh->khoa->TenKhoa ?? '' }}</div>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ $lop->KhoaHoc }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3">{{ $lop->sinh_viens_count ?? 0 }} SV</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('lop.edit', $lop->MaLop) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('lop.destroy', $lop->MaLop) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lớp này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-users-rectangle"></i>
                                    <h6>Chưa có dữ liệu Lớp học</h6>
                                    <p class="small text-muted mb-3">Vui lòng thêm mới hoặc nhập từ file Excel.</p>
                                    <a href="{{ route('lop.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Lớp Mới</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lops->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $lops->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.lop.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Lớp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'lop') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
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