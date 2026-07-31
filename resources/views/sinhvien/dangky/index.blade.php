@extends('layouts.sinhvien')
@section('page_title', 'Đăng Ký Đề Tài')
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

@if($myLopHocPhans->isNotEmpty())
<div class="card card-premium mb-4">
    <div class="card-body p-3">
        <form action="{{ route('sinhvien.dangky.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-2 m-0">
            <label class="fw-bold text-dark text-nowrap mb-0 me-2">
                <i class="fa-solid fa-graduation-cap text-primary me-2"></i>Chọn Lớp Học Phần (Tín Chỉ):
            </label>
            <select name="MaLopHP" class="form-select border-primary fw-bold text-primary rounded-pill flex-grow-1" onchange="this.form.submit()" style="max-width: 550px;">
                @foreach($myLopHocPhans as $lhp)
                    <option value="{{ $lhp->MaLopHP }}" {{ $selectedMaLopHP == $lhp->MaLopHP ? 'selected' : '' }}>
                        [{{ $lhp->TenLopHP }}] — {{ $lhp->monHoc->TenMon ?? 'Môn Học' }} (GV: {{ $lhp->giangVien->HoTen ?? 'Chưa gán' }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>
@endif

@if(!$nhom && $currentLopHP)
<div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
    <div class="d-flex align-items-center">
        <i class="fa-solid fa-triangle-exclamation fa-2x text-warning me-3"></i>
        <div>
            <h6 class="fw-bold mb-1">Bạn chưa có nhóm trong Lớp Học Phần <u>{{ $currentLopHP->TenLopHP }}</u> ({{ $currentLopHP->monHoc->TenMon ?? '' }})</h6>
            <p class="mb-0 small text-dark">Vui lòng tạo hoặc tham gia nhóm thuộc Lớp Học Phần này trước khi thực hiện đăng ký đề tài.</p>
        </div>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-sm btn-warning rounded-pill px-3 ms-auto text-nowrap fw-bold">
            <i class="fa-solid fa-users me-1"></i>Đến Trang Quản Lý Nhóm
        </a>
    </div>
</div>
@endif

@if($dangky)
<div class="alert alert-{{ $dangky->TrangThai == 'Đã duyệt' ? 'success' : ($dangky->TrangThai == 'Chờ duyệt' ? 'warning' : 'danger') }} border-0 shadow-sm p-4 rounded-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-circle-info me-2"></i> Trạng thái đăng ký hiện tại: <span class="badge bg-{{ $dangky->TrangThai == 'Đã duyệt' ? 'success' : ($dangky->TrangThai == 'Chờ duyệt' ? 'warning text-dark' : 'danger') }} fs-6 ms-1">{{ $dangky->TrangThai }}</span></h5>
        @if($dangky->TrangThai == 'Chờ duyệt' && $nhom && $nhom->TruongNhom == $sinhVien->MaSV)
            <form action="{{ route('sinhvien.dangky.destroy', $dangky->MaDangKy) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn HỦY đăng ký đề tài này để chọn đề tài khác?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                    <i class="fa-solid fa-xmark me-1"></i>Hủy đăng ký (Đổi đề tài)
                </button>
            </form>
        @endif
    </div>
    <hr class="my-2">
    <p class="mb-1"><strong>Đề tài:</strong> {{ $dangky->deTai->TenDeTai ?? '' }}</p>
    <p class="mb-1"><strong>Giảng viên:</strong> {{ $dangky->deTai->giangVien->HoTen ?? 'Chưa xác định' }}</p>
    <p class="mb-1"><strong>Ngày đăng ký:</strong> {{ date('d/m/Y', strtotime($dangky->NgayDangKy)) }}</p>
    @if($dangky->TrangThai == 'Đã duyệt')
        <div class="alert alert-light border text-success small mb-0 mt-2 rounded-3">
            <i class="fa-solid fa-circle-check me-1"></i>Nhóm của bạn đã được duyệt chính thức đề tài này! Các đề tài khác trong Lớp Học Phần sẽ được khóa nút đăng ký.
        </div>
    @elseif($dangky->TrangThai == 'Từ chối' && $dangky->LyDoTuChoi)
        <p class="mb-0 text-danger mt-2"><strong>Lý do từ chối:</strong> <i>"{{ $dangky->LyDoTuChoi }}"</i></p>
    @endif
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span>
            <i class="fa-solid fa-list text-primary me-2"></i>Danh Sách Đề Tài Lớp Học Phần: 
            <strong>{{ $currentLopHP->TenLopHP ?? 'Chưa chọn' }}</strong> 
            @if($currentLopHP && $currentLopHP->monHoc)
                ({{ $currentLopHP->monHoc->TenMon }})
            @endif
        </span>
        <span class="badge bg-info text-dark">Chỉ hiện đề tài Lớp Học Phần này</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">Mã ĐT</th>
                    <th width="35%">Tên Đề Tài</th>
                    <th>Giảng Viên Phụ Trách</th>
                    <th>Hạn Đăng Ký</th>
                    <th class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detais as $dt)
                @php
                    $isExpired = $dt->HanDangKy && date('Y-m-d') > $dt->HanDangKy;
                @endphp
                <tr>
                    <td class="px-4 fw-bold text-muted">{{ $dt->MaDeTai }}</td>
                    <td class="fw-bold text-primary">{{ $dt->TenDeTai }}</td>
                    <td>{{ $dt->giangVien->HoTen ?? 'Chưa xác định' }}</td>
                    <td>
                        @if($dt->HanDangKy)
                            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }}">
                                <i class="fa-regular fa-clock me-1"></i>{{ date('d/m/Y', strtotime($dt->HanDangKy)) }}
                            </span>
                        @else
                            <span class="text-muted small">Không giới hạn</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isExpired)
                            <span class="badge bg-secondary py-2 px-3 rounded-pill">Hết hạn đăng ký</span>
                        @elseif($dangky && $dangky->MaDeTai == $dt->MaDeTai)
                            @if($dangky->TrangThai == 'Đã duyệt')
                                <span class="badge bg-success py-2 px-3 rounded-pill" title="Đề tài nhóm bạn đã được duyệt"><i class="fa-solid fa-circle-check me-1"></i>Đã được duyệt</span>
                            @elseif($dangky->TrangThai == 'Chờ duyệt')
                                <span class="badge bg-warning text-dark py-2 px-3 rounded-pill" title="Đang chờ giảng viên duyệt"><i class="fa-solid fa-clock me-1"></i>Đang chờ duyệt</span>
                            @else
                                <span class="badge bg-danger py-2 px-3 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i>Bị từ chối</span>
                            @endif
                        @elseif($dangky && $dangky->TrangThai == 'Đã duyệt')
                            <span class="badge bg-secondary opacity-75 py-2 px-3 rounded-pill" title="Nhóm bạn đã có đề tài được duyệt chính thức"><i class="fa-solid fa-lock me-1"></i>Nhóm đã có đề tài</span>
                        @elseif($dangky && $dangky->TrangThai == 'Chờ duyệt')
                            <span class="badge bg-secondary opacity-75 py-2 px-3 rounded-pill" title="Nhóm bạn đang chờ duyệt đề tài khác"><i class="fa-solid fa-lock me-1"></i>Chờ duyệt ĐT khác</span>
                        @elseif(!$nhom)
                            <span class="badge bg-light text-dark border py-2 px-3 rounded-pill"><i class="fa-solid fa-users me-1"></i>Chưa có nhóm HP này</span>
                        @elseif($nhom->TruongNhom != $sinhVien->MaSV)
                            <span class="badge bg-light text-dark border py-2 px-3 rounded-pill"><i class="fa-solid fa-user-shield me-1"></i>Chỉ Trưởng nhóm</span>
                        @else
                            <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="form-dangky">
                                @csrf
                                <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                <button type="button" class="btn btn-sm btn-primary-custom rounded-pill px-3 btn-dangky">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Đăng Ký
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Hiện không có đề tài nào mở đăng ký cho Lớp Học Phần này.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $detais->links('pagination::bootstrap-5') }}
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-dangky').forEach(button => {
            button.addEventListener('click', function() {
                let form = this.closest('form');
                Swal.fire({
                    title: 'Xác nhận đăng ký?',
                    text: "Bạn đại diện cho nhóm đăng ký đề tài này?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3699ff',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Đồng ý Đăng Ký',
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