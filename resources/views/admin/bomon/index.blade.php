@extends('layouts.admin')

@section('page_title', 'Quản Lý Bộ Môn')

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
        <span><i class="fa-solid fa-building me-2"></i> Danh Sách Bộ Môn</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'bomon') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('bomon.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="15%">Mã Bộ Môn</th>
                        <th width="40%">Tên Bộ Môn</th>
                        <th width="30%">Khoa Trực Thuộc</th>
                        <th width="15%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bomons as $bomon)
                        <tr>
                            <td><span class="badge-code">{{ $bomon->MaBoMon }}</span></td>
                            <td class="fw-bold text-primary">{{ $bomon->TenBoMon }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">
                                    <i class="fa-solid fa-university me-1"></i>{{ $bomon->khoa->TenKhoa ?? 'Chưa gán Khoa' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('bomon.edit', $bomon->MaBoMon) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('bomon.destroy', $bomon->MaBoMon) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ môn này?');">
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
                            <td colspan="4">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-building"></i>
                                    <h6>Chưa có dữ liệu Bộ môn</h6>
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
    @if($bomons->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $bomons->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.bomon.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Bộ Môn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'bomon') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
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