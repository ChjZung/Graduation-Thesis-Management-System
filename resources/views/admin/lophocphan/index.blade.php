@extends('layouts.admin')
@section('page_title', 'Quản Lý Lớp Học Phần')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- BỘ LỌC TÌM KIẾM -->
<div class="card card-premium mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.lophocphan.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="ma_mon" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả Môn học --</option>
                    @foreach($monHocs as $m)
                        <option value="{{ $m->MaMon }}" {{ request('ma_mon') == $m->MaMon ? 'selected' : '' }}>
                            {{ $m->TenMon }} ({{ $m->SoTinChi }} tín chỉ)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="ma_hoc_ky" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('ma_hoc_ky') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="ma_gv" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả Giảng viên --</option>
                    @foreach($giangViens as $gv)
                        <option value="{{ $gv->MaGV }}" {{ request('ma_gv') == $gv->MaGV ? 'selected' : '' }}>
                            {{ $gv->HoTen }} ({{ $gv->HocVi ?? 'GV' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                @if(request()->hasAny(['ma_mon', 'ma_hoc_ky', 'ma_gv']))
                    <a href="{{ route('admin.lophocphan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Xóa lọc</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH LỚP HỌC PHẦN -->
<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Danh Sách Lớp Học Phần (Lớp Tín Chỉ)</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'lophocphan') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
            </a>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importLhpModal">
                <i class="fa-solid fa-file-excel me-1"></i>Import Excel
            </button>
            <a href="{{ route('admin.lophocphan.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus me-1"></i>Tạo Lớp HP Mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        @if(session('import_result'))
        <div class="alert alert-info alert-dismissible fade show m-3">
            <i class="fa-solid fa-circle-info me-2"></i>
            {!! session('import_result') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Mã HP</th>
                        <th>Tên Lớp Học Phần</th>
                        <th>Môn Học</th>
                        <th>Học Kỳ</th>
                        <th>Giảng Viên Phụ Trách</th>
                        <th class="text-center">Sĩ Số</th>
                        <th class="text-center">Trạng Thái</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lopHocPhans as $item)
                    @php $siSoHienTai = $item->sinhVienLopHocPhans->count(); @endphp
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">HP#{{ $item->MaLopHP }}</span></td>
                        <td>
                            <a href="{{ route('admin.lophocphan.show', $item->MaLopHP) }}" class="fw-bold text-decoration-none text-primary">
                                {{ $item->TenLopHP }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-soft-info text-info border border-info px-2 py-1">
                                {{ $item->monHoc->TenMon ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if($item->hocKy)
                                <span class="badge bg-light text-dark border">
                                    {{ $item->hocKy->TenHocKy }} ({{ $item->hocKy->NamHoc }})
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($item->giangVien)
                                <span class="fw-medium"><i class="fa-solid fa-user-tie text-secondary me-1"></i>{{ $item->giangVien->HoTen }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $siSoHienTai >= $item->SiSoToiDa ? 'bg-danger' : 'bg-primary' }} rounded-pill px-2 py-1">
                                {{ $siSoHienTai }} / {{ $item->SiSoToiDa }} SV
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->TrangThai === 'Đang mở')
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-lock-open me-1"></i>Đang mở</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1"><i class="fa-solid fa-lock me-1"></i>Đã đóng</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.lophocphan.show', $item->MaLopHP) }}" class="btn btn-sm btn-light text-info rounded-circle me-1" title="Chi tiết & Danh sách sinh viên">
                                <i class="fa-solid fa-users text-primary"></i>
                            </a>
                            <a href="{{ route('admin.lophocphan.edit', $item->MaLopHP) }}" class="btn btn-sm btn-light text-primary rounded-circle me-1" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.lophocphan.destroy', $item->MaLopHP) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa Lớp Học Phần này? Dữ liệu danh sách sinh viên thuộc lớp cũng sẽ bị xóa!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-folder-open fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            Chưa có Lớp Học Phần nào được tạo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lopHocPhans->hasPages())
    <div class="card-footer bg-white d-flex justify-content-end">
        {{ $lopHocPhans->links() }}
    </div>
    @endif
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="importLhpModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.lophocphan.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Lớp Học Phần</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Tải file mẫu Excel chuẩn bên dưới, điền thông tin các Lớp Học Phần rồi upload tại đây.</p>
                    <div class="mb-3">
                        <a href="{{ route('admin.import.template', 'lophocphan') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fa-solid fa-download me-1"></i>Tải file mẫu Template_LopHocPhan.xlsx
                        </a>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Tải Lên & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
