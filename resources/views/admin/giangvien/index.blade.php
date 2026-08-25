@extends('layouts.admin')

@section('page_title', 'Quản Lý Giảng Viên')

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
        <span><i class="fa-solid fa-chalkboard-user me-2"></i> Danh Sách Giảng Viên</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Tải file mẫu Excel">
                <i class="fa-solid fa-download me-1"></i> File Mẫu
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-import me-1"></i> Import Excel
            </button>
            <a href="{{ route('giangvien.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Filter Bar -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" action="{{ route('giangvien.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên, email, mã GV..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="MaBoMon" class="form-select form-select-sm">
                        <option value="">-- Tất cả Bộ môn --</option>
                        @foreach($bomons as $bm)
                            <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-magnifying-glass me-1"></i>Tìm</button>
                    <a href="{{ route('giangvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Xóa lọc</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="15%">Mã GV / Tài Khoản</th>
                        <th width="25%">Họ Và Tên</th>
                        <th width="15%">Học Vị</th>
                        <th width="23%">Bộ Môn / Khoa</th>
                        <th width="12%">Liên Hệ</th>
                        <th width="10%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giangviens as $gv)
                        <tr>
                            <td>
                                <span class="badge-code">{{ $gv->MaGV }}</span>
                                <div class="small text-muted mt-1">TK: <code>{{ $gv->taiKhoan->TenDangNhap ?? 'N/A' }}</code></div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ $gv->HoTen }}</div>
                                <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $gv->Email }}</div>
                            </td>
                            <td><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3">{{ $gv->HocVi ?? 'Giảng viên' }}</span></td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $gv->boMon->TenBoMon ?? 'Chưa gán' }}</div>
                                <div class="small text-muted">{{ $gv->boMon->khoa->TenKhoa ?? '' }}</div>
                            </td>
                            <td>
                                <div class="small text-dark"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $gv->SoDienThoai ?? 'Chưa có' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('giangvien.destroy', $gv->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?');">
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
                            <td colspan="6">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                    <h6>Chưa có giảng viên nào trong hệ thống</h6>
                                    <p class="small text-muted mb-3">Vui lòng thêm mới giảng viên hoặc import từ file Excel.</p>
                                    <a href="{{ route('giangvien.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thêm Giảng Viên Mới</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($giangviens->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $giangviens->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Giảng Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'giangvien') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
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