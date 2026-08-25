@extends('layouts.admin')

@section('page_title', 'Quản Lý Sinh Viên')

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
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-premium">
        <div class="card-header-premium d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-user-graduate text-primary me-2"></i> Quản Lý Sinh Viên</span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.import.template', 'sinhvien') }}"
                    class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
                </a>
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal"
                    data-bs-target="#importModal">
                    <i class="fa-solid fa-file-excel me-1"></i>Import Excel
                </button>
                <a href="{{ route('sinhvien.create') }}" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Mới
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3 bg-light border-bottom">
                <form method="GET" action="{{ route('sinhvien.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên, email, MSSV, tài khoản..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="MaLop" class="form-select form-select-sm">
                            <option value="">-- Tất cả Lớp học --</option>
                            @foreach($lops as $l)
                                <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                                    {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-magnifying-glass me-1"></i>Tìm</button>
                        <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Xóa lọc</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="15%">MSSV / Mã SV</th>
                            <th width="25%">Họ Và Tên</th>
                            <th width="25%">Lớp / Ngành</th>
                            <th width="20%">Liên Hệ</th>
                            <th width="15%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sinhviens as $sv)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark fw-bold border">{{ $sv->MaSoSinhVien ?? $sv->MaSV }}</span>
                                    <div class="small text-muted">TK: <code>{{ $sv->taiKhoan->TenDangNhap ?? 'N/A' }}</code></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary-custom">{{ $sv->HoTen }}</div>
                                    <div class="small text-muted">{{ $sv->Email }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sv->lop->TenLop ?? 'Chưa xếp lớp' }}</div>
                                    <div class="small text-muted">{{ $sv->lop->nganh->TenNganh ?? '' }} ({{ $sv->lop->nganh->khoa->TenKhoa ?? '' }})</div>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $sv->SoDienThoai ?? 'Chưa cập nhật' }}</div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('sinhvien.edit', $sv->MaSV) }}"
                                        class="btn btn-sm btn-light text-primary me-1 rounded-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('sinhvien.destroy', $sv->MaSV) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                                    Chưa có dữ liệu sinh viên
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sinhviens->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $sinhviens->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- MODAL IMPORT -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.sinhvien.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Sinh Viên</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                        </div>
                        <div class="alert alert-light border small text-muted mb-0">
                            <i class="fa-solid fa-circle-info me-1"></i> Tải <a
                                href="{{ route('admin.import.template', 'sinhvien') }}" class="fw-bold">File mẫu .xlsx</a> để
                                nhập đúng định dạng dữ liệu.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4"><i
                                class="fa-solid fa-upload me-1"></i>Tải Lên & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection