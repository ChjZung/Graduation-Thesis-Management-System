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
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-premium">
        <div class="card-header-premium d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-chalkboard-user text-primary me-2"></i> Quản Lý Giảng Viên</span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.import.template', 'giangvien') }}"
                    class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
                </a>
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal"
                    data-bs-target="#importModal">
                    <i class="fa-solid fa-file-excel me-1"></i>Import Excel
                </button>
                <a href="{{ route('giangvien.create') }}" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Mới
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3 bg-light border-bottom">
                <form method="GET" action="{{ route('giangvien.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên, email, mã GV, tài khoản..." value="{{ request('search') }}">
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
                <table class="table table-custom table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th width="12%">Mã GV</th>
                            <th width="25%">Họ Và Tên</th>
                            <th width="15%">Học Vị</th>
                            <th width="23%">Bộ Môn / Khoa</th>
                            <th width="15%">Liên Hệ</th>
                            <th width="10%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($giangviens as $gv)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark fw-bold border">{{ $gv->MaGV }}</span>
                                    <div class="small text-muted">TK: <code>{{ $gv->taiKhoan->TenDangNhap ?? 'N/A' }}</code></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary-custom">{{ $gv->HoTen }}</div>
                                    <div class="small text-muted">{{ $gv->Email }}</div>
                                </td>
                                <td><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3">{{ $gv->HocVi ?? 'Giảng viên' }}</span></td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $gv->boMon->TenBoMon ?? 'Chưa gán' }}</div>
                                    <div class="small text-muted">{{ $gv->boMon->khoa->TenKhoa ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $gv->SoDienThoai ?? 'Chưa cập nhật' }}</div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('giangvien.edit', $gv->MaGV) }}"
                                        class="btn btn-sm btn-light text-primary me-1 rounded-circle" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('giangvien.destroy', $gv->MaGV) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?');">
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
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                                    Chưa có dữ liệu giảng viên
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
        <div class="modal-dialog">
            <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Giảng Viên</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                        </div>
                        <div class="alert alert-light border small text-muted mb-0">
                            <i class="fa-solid fa-circle-info me-1"></i> Tải <a
                                href="{{ route('admin.import.template', 'giangvien') }}" class="fw-bold">File mẫu .xlsx</a> để
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