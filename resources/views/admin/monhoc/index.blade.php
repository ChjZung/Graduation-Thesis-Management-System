@extends('layouts.admin')
@section('page_title', 'Danh Sách Môn Học')
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
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-book-open text-primary me-2"></i>Quản Lý Môn Học</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'monhoc') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
            </a>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-excel me-1"></i>Import Excel
            </button>
            <a href="{{ route('monhoc.create') }}" class="btn btn-success btn-sm rounded-pill px-3"><i class="fa-solid fa-plus me-1"></i>Thêm Mới</a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-custom mb-0">
            <thead><tr><th>Mã Môn</th><th>Tên Môn</th><th>Mã Bộ Môn</th><th>Tín Chỉ</th><th class="text-center">Thao Tác</th></tr></thead>
            <tbody>
                @foreach($monhocs as $item)
                <tr>
                    <td><span class="badge bg-light text-dark fw-bold border">{{ $item->MaMon }}</span></td>
                    <td class="fw-medium">{{ $item->TenMon }}</td>
                    <td>{{ $item->MaBoMon }}</td>
                    <td><span class="badge bg-info text-dark">{{ $item->SoTinChi }} tín chỉ</span></td>
                    <td class="text-center">
                        <a href="{{ route('monhoc.edit', $item->MaMon) }}" class="btn btn-sm btn-light text-primary rounded-circle"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('monhoc.destroy', $item->MaMon) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa môn học này?');">
                            @csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-light text-danger rounded-circle"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($monhocs, 'hasPages') && $monhocs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $monhocs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.monhoc.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Môn Học</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i> Tải <a href="{{ route('admin.import.template', 'monhoc') }}" class="fw-bold">File mẫu .xlsx</a> để nhập đúng định dạng dữ liệu.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Tải Lên & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection