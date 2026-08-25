@extends('layouts.admin')

@section('page_title', 'Quản Lý Khoa')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('import_result'))
    <div class="alert alert-success alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3" role="alert">
        {!! session('import_result') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('import_warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3" role="alert">
        {!! session('import_warning') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('import_danger'))
    <div class="alert alert-danger alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3" role="alert">
        {!! session('import_danger') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card card-premium">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-university me-2"></i> Danh Sách Các Khoa Quản Lý</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'khoa') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Tải file mẫu Excel">
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="15%">Mã Khoa</th>
                        <th width="45%">Tên Khoa</th>
                        <th width="15%" class="text-center">Số Bộ Môn</th>
                        <th width="15%" class="text-center">Số Ngành</th>
                        <th width="10%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($khoas as $khoa)
                        <tr>
                            <td><span class="badge-code">{{ $khoa->MaKhoa }}</span></td>
                            <td class="fw-bold text-primary">{{ $khoa->TenKhoa }}</td>
                            <td class="text-center"><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3">{{ $khoa->bo_mons_count ?? 0 }} Bộ môn</span></td>
                            <td class="text-center"><span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $khoa->nganhs_count ?? 0 }} Ngành</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('khoa.edit', $khoa->MaKhoa) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('khoa.destroy', $khoa->MaKhoa) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khoa này?');">
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
                                    <i class="fa-solid fa-university"></i>
                                    <h6>Chưa có dữ liệu Khoa nào</h6>
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
    @if($khoas->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $khoas->links('pagination::bootstrap-5') }}
        </div>
    @endif
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
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
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
