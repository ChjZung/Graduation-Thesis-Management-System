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

<div class="card card-premium">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-user-graduate me-2"></i> Danh Sách Sinh Viên</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'sinhvien') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('sinhvien.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Filter Bar -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" action="{{ route('sinhvien.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên, email, MSSV..." value="{{ request('search') }}">
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
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="18%">MSSV / Tài Khoản</th>
                        <th width="27%">Họ Và Tên</th>
                        <th width="25%">Lớp / Ngành</th>
                        <th width="18%">Liên Hệ</th>
                        <th width="12%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sinhviens as $sv)
                        <tr>
                            <td>
                                <span class="badge-code">{{ $sv->MaSoSinhVien ?? $sv->MaSV }}</span>
                                <div class="small text-muted mt-1">TK: <code>{{ $sv->taiKhoan->TenDangNhap ?? 'N/A' }}</code></div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ $sv->HoTen }}</div>
                                <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $sv->Email }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $sv->lop->TenLop ?? 'Chưa xếp lớp' }}</div>
                                <div class="small text-muted">{{ $sv->lop->nganh->TenNganh ?? '' }}</div>
                            </td>
                            <td>
                                <div class="small text-dark"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $sv->SoDienThoai ?? 'Chưa có' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('sinhvien.edit', $sv->MaSV) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('sinhvien.destroy', $sv->MaSV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?');">
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
                                    <i class="fa-solid fa-user-graduate"></i>
                                    <h6>Chưa có sinh viên nào trong hệ thống</h6>
                                    <p class="small text-muted mb-3">Vui lòng thêm mới sinh viên hoặc import danh sách từ Excel.</p>
                                    <a href="{{ route('sinhvien.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Sinh Viên Mới</a>
                                </div>
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
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.sinhvien.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Sinh Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'sinhvien') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
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