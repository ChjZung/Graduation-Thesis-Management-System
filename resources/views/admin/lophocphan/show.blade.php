@extends('layouts.admin')
@section('page_title', 'Chi Tiết Lớp Học Phần: ' . $lopHocPhan->TenLopHP)

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

<!-- THÔNG TIN TỔNG QUAN LỚP HỌC PHẦN -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Thông Tin Lớp Học Phần</span>
        <div>
            <a href="{{ route('admin.lophocphan.edit', $lopHocPhan->MaLopHP) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">
                <i class="fa-solid fa-pen me-1"></i>Sửa thông tin
            </a>
            <a href="{{ route('admin.lophocphan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Tên Lớp Học Phần</span>
                    <h5 class="mb-0 fw-bold text-primary">{{ $lopHocPhan->TenLopHP }}</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Môn Học</span>
                    <h6 class="mb-0 fw-bold text-dark">{{ $lopHocPhan->monHoc->TenMon ?? 'N/A' }} <span class="badge bg-info text-dark ms-1">{{ $lopHocPhan->monHoc->SoTinChi ?? 0 }} tín chỉ</span></h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Học Kỳ</span>
                    <h6 class="mb-0 fw-bold text-dark">{{ $lopHocPhan->hocKy->TenHocKy ?? 'N/A' }} ({{ $lopHocPhan->hocKy->NamHoc ?? 'N/A' }})</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Giảng Viên Hướng Dẫn</span>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-user-tie text-secondary me-1"></i>{{ $lopHocPhan->giangVien->HoTen ?? 'Chưa phân công' }}</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Sĩ Số / Tối Đa</span>
                    <h6 class="mb-0 fw-bold text-dark">
                        @php $siSoCurrent = $lopHocPhan->sinhVienLopHocPhans->count(); @endphp
                        <span class="badge {{ $siSoCurrent >= $lopHocPhan->SiSoToiDa ? 'bg-danger' : 'bg-success' }} px-2 py-1">
                            {{ $siSoCurrent }} / {{ $lopHocPhan->SiSoToiDa }} Sinh viên
                        </span>
                    </h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Trạng Thái Lớp</span>
                    <h6 class="mb-0 fw-bold">
                        @if($lopHocPhan->TrangThai === 'Đang mở')
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-lock-open me-1"></i>Đang mở</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1"><i class="fa-solid fa-lock me-1"></i>Đã đóng</span>
                        @endif
                    </h6>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM THÊM SINH VIÊN VÀO LỚP HP -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-user-plus text-success me-2"></i>Thêm Sinh Viên Vào Lớp Học Phần</span>
    </div>
    <div class="card-body p-3">
        <form action="{{ route('admin.lophocphan.addStudent', $lopHocPhan->MaLopHP) }}" method="POST" class="row g-2 align-items-center">
            @csrf
            <div class="col-md-8">
                <select name="MaSV" class="form-select select2" required>
                    <option value="">-- Chọn Sinh Viên Cần Thêm Về Lớp HP Này --</option>
                    @foreach($availableStudents as $sv)
                        <option value="{{ $sv->MaSV }}">
                            MSSV: {{ $sv->MaSV }} - {{ $sv->HoTen }} (Lớp HC: {{ $sv->lop->TenLop ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4" {{ $siSoCurrent >= $lopHocPhan->SiSoToiDa ? 'disabled' : '' }}>
                    <i class="fa-solid fa-plus me-1"></i>Thêm Vào Lớp HP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH SINH VIÊN ĐÃ ĐĂNG KÝ HỌC PHẦN NÀY -->
<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-users text-primary me-2"></i>Danh Sách Sinh Viên Thuộc Lớp HP ({{ $lopHocPhan->sinhVienLopHocPhans->count() }} SV)</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'sinhvien_lophocphan') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="fa-solid fa-file-excel me-1"></i>File Mẫu .xlsx
            </a>
            <button class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importStudentModal">
                <i class="fa-solid fa-file-import me-1"></i>Import Excel
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>MSSV</th>
                        <th>Họ Và Tên</th>
                        <th>Lớp Hành Chính</th>
                        <th>Email</th>
                        <th>Ngày Đăng Ký HP</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lopHocPhan->sinhVienLopHocPhans as $index => $svHp)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $svHp->sinhVien->MaSV ?? 'N/A' }}</span></td>
                        <td class="fw-bold text-dark">{{ $svHp->sinhVien->HoTen ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border">
                                {{ $svHp->sinhVien->lop->TenLop ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $svHp->sinhVien->Email ?? '—' }}</td>
                        <td>{{ $svHp->NgayDangKy ? \Carbon\Carbon::parse($svHp->NgayDangKy)->format('d/m/Y') : '—' }}</td>
                        <td class="text-center">
                            <form action="{{ route('admin.lophocphan.removeStudent', [$lopHocPhan->MaLopHP, $svHp->MaSV]) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa sinh viên này khỏi Lớp Học Phần?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa khỏi lớp HP">
                                    <i class="fa-solid fa-user-minus"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-user-slash fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            Lớp học phần này chưa có sinh viên nào. Vui lòng thêm sinh viên ở khung phía trên hoặc dùng nút Import Excel.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL IMPORT SINH VIÊN VÀO LỚP HP -->
<div class="modal fade" id="importStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.lophocphan.importStudents', $lopHocPhan->MaLopHP) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Danh Sách Sinh Viên Về Lớp HP</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 p-3 small mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i><strong>Hướng dẫn:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>Tải file mẫu Excel bằng nút <strong>File Mẫu .xlsx</strong> để xem cấu trúc chuẩn.</li>
                            <li>Nhập danh sách MSSV (hoặc Mã Đăng Nhập) cần thêm vào Lớp HP này.</li>
                            <li>Hệ thống sẽ tự động bỏ qua nếu sinh viên đã có trong lớp HP này.</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-upload me-1"></i>Bắt Đầu Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
