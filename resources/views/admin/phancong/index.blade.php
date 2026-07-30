@extends('layouts.admin')
@section('title', 'Phân Công Hướng Dẫn Lớp')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-users-gear me-2"></i>Phân Công Hướng Dẫn Lớp</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.import.template', 'phancong') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
        </a>
        <button class="btn btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1"></i>Import Excel
        </button>
        <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-2"></i>Thêm Phân Công
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-info me-2"></i>{!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">ID</th>
                    <th>Giảng Viên</th>
                    <th>Lớp</th>
                    <th>Học Kỳ</th>
                    <th>Ngày Phân Công</th>
                    <th class="text-end px-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phancongs as $pc)
                <tr>
                    <td class="px-4 fw-bold text-muted">#{{ $pc->MaPhanCong }}</td>
                    <td>
                        <span class="fw-bold">{{ $pc->giangVien->HoTen }}</span><br>
                        <small class="text-muted">{{ $pc->giangVien->boMon->TenBoMon ?? '' }}</small>
                    </td>
                    <td>{{ $pc->lop->TenLop }}</td>
                    <td>{{ $pc->hocKy->TenHocKy }} ({{ $pc->hocKy->NamHoc }})</td>
                    <td>{{ date('d/m/Y', strtotime($pc->NgayPhanCong)) }}</td>
                    <td class="text-end px-4">
                        <form action="{{ route('phancong.destroy', $pc->MaPhanCong) }}" method="POST" class="d-inline form-delete">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $phancongs->links('pagination::bootstrap-5') }}
</div>

<!-- Modal Thêm -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('phancong.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Phân Công Hướng Dẫn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Giảng Viên</label>
                        <select name="MaGV" class="form-select" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($giangviens as $gv)
                            <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->boMon->TenBoMon ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Lớp</label>
                        <select name="MaLop" class="form-select" required>
                            <option value="">-- Chọn lớp --</option>
                            @foreach($lops as $lop)
                            <option value="{{ $lop->MaLop }}">{{ $lop->TenLop }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Học Kỳ</label>
                        <select name="MaHocKy" class="form-select" required>
                            <option value="">-- Chọn học kỳ --</option>
                            @foreach($hockys as $hk)
                            <option value="{{ $hk->MaHocKy }}">{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill">Lưu Phân Công</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.phancong.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Phân Công</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i> Tải <a href="{{ route('admin.import.template', 'phancong') }}" class="fw-bold">File mẫu .xlsx</a> để nhập đúng định dạng dữ liệu.
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                let form = this.closest('form');
                Swal.fire({
                    title: 'Xóa phân công?',
                    text: "Bạn không thể hoàn tác hành động này!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Xóa ngay',
                    cancelButtonText: 'Hủy',
                    background: '#fff',
                    borderRadius: '1rem',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });
    });
</script>
@endsection
