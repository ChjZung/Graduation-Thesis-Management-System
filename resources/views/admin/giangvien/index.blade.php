@extends('layouts.admin')
@section('page_title', 'Danh Sách Giảng Viên')
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
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
        <span><i class="fa-solid fa-chalkboard-user me-2"></i>Quản Lý Giảng Viên ({{ $giangviens->total() }})</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
            </a>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-excel me-1"></i>Import Excel
            </button>
            <a href="{{ route('giangvien.create') }}" class="btn btn-success btn-sm rounded-pill px-3"><i class="fa-solid fa-plus me-1"></i>Thêm GV Mới</a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-custom table-hover mb-0">
            <thead><tr><th>Mã GV</th><th>Tài Khoản</th><th>Họ Tên</th><th>Học Vị</th><th>Bộ Môn</th><th>Trạng Thái</th><th class="text-center">Thao Tác</th></tr></thead>
            <tbody>
                @foreach($giangviens as $item)
                <tr>
                    <td>{{ $item->MaGV }}</td>
                    <td><span class="badge bg-warning text-dark">{{ $item->taiKhoan->TenDangNhap ?? '' }}</span></td>
                    <td class="fw-bold">{{ $item->HoTen }}</td>
                    <td>{{ $item->HocVi }}</td>
                    <td>{{ $item->boMon->TenBoMon ?? '' }}</td>
                    <td>
                        @if($item->taiKhoan && $item->taiKhoan->TrangThai)
                            <span class="badge bg-success">Hoạt động</span>
                        @else
                            <span class="badge bg-danger">Đã khóa</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item->taiKhoan)
                        <form action="{{ route('admin.taikhoan.toggleLock', $item->taiKhoan->MaTK) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái khóa tài khoản này?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light {{ $item->taiKhoan->TrangThai ? 'text-warning' : 'text-success' }} rounded-circle" title="{{ $item->taiKhoan->TrangThai ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                <i class="fa-solid {{ $item->taiKhoan->TrangThai ? 'fa-lock' : 'fa-unlock' }}"></i>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('giangvien.edit', $item->MaGV) }}" class="btn btn-sm btn-light text-primary rounded-circle"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('giangvien.destroy', $item->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa GV này (sẽ xóa luôn tài khoản)?');">
                            @csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-light text-danger rounded-circle"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $giangviens->links('pagination::bootstrap-5') }}
</div>

@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show mt-3">
    <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Giảng Viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex gap-2 align-items-start">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <div>
                            <strong>Định dạng cột bắt buộc:</strong>
                            <code>TenDangNhap, HoTen, Email, SoDienThoai, HocVi, MaBoMon</code><br>
                            Mật khẩu mặc định sau import: <strong>123456</strong><br>
                            <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-sm btn-outline-success mt-2 rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i>Tải file mẫu .xlsx
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file Excel/CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".csv,.xlsx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-upload me-1"></i>Tải Lên & Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection