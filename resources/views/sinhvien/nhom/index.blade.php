@extends('layouts.sinhvien')

@section('page_title', 'Nhóm Khóa Luận Của Tôi')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- LỜI MỜI NHÓM ĐANG CHỜ -->
@if(isset($loiMois) && $loiMois->count() > 0)
<div class="alert alert-warning border-warning shadow-sm mb-4">
    <h5 class="fw-bold"><i class="fa-solid fa-envelope-open-text me-2"></i>Bạn có {{ $loiMois->count() }} lời mời tham gia nhóm khóa luận!</h5>
    @foreach($loiMois as $lm)
    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 mt-2 border">
        <div>
            Mời bởi Trưởng nhóm <strong>{{ $lm->nhom->truongNhom->HoTen ?? 'Bạn học' }}</strong> gia nhập nhóm <strong>"{{ $lm->nhom->TenNhom ?? '' }}"</strong>.
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('sinhvien.nhom.xacNhanLoiMoi', $lm->MaNhom) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-check me-1"></i>Chấp Nhận
                </button>
            </form>
            <form action="{{ route('sinhvien.nhom.tuChoiLoiMoi', $lm->MaNhom) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Từ Chối</button>
            </form>

        </div>
    </div>
    @endforeach
</div>
@endif

@if(!$nhomCurrent)
<!-- CHƯA CÓ NHÓM -> KHỞI TẠO -->
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-premium text-center p-4">
            <div class="card-body">
                <i class="fa-solid fa-users-slash fs-1 text-secondary mb-3 d-block"></i>
                <h4 class="fw-bold text-primary-custom">Bạn Chưa Có Nhóm Khóa Luận</h4>
                <p class="text-muted">Mỗi nhóm khóa luận tối đa 3 sinh viên. Hãy khởi tạo nhóm hoặc chờ Trưởng nhóm gửi lời mời.</p>

                <form action="{{ route('sinhvien.nhom.store') }}" method="POST" class="mt-4 text-start">
                    @csrf
                    <div class="mb-3">
                        <label for="TenNhom" class="form-label fw-bold">Tên Nhóm Khóa Luận <span class="text-danger">*</span></label>
                        <input type="text" name="TenNhom" id="TenNhom" class="form-control" placeholder="Ví dụ: Nhóm N01 - Nghiên cứu Web/AI..." required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold">
                        <i class="fa-solid fa-plus-circle me-1"></i> Khởi Tạo Nhóm Mới
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<!-- ĐÃ CÓ NHÓM -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-users text-primary me-2"></i>Nhóm Khóa Luận: {{ $nhomCurrent->TenNhom }}</span>
        <span class="badge bg-success rounded-pill px-3">{{ $nhomCurrent->TrangThai }}</span>
    </div>
    <div class="card-body p-4">
        @if($nhomCurrent->deTai)
            <div class="p-3 bg-light border-start border-4 border-success rounded-3 mb-4">
                <div class="small text-muted text-uppercase fw-bold">Đề Tài Đã Đăng Ký</div>
                <h5 class="fw-bold text-success mb-1">{{ $nhomCurrent->deTai->TenDeTai }}</h5>
                <div class="small text-secondary">
                    <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên hướng dẫn: <strong>{{ $nhomCurrent->deTai->giangVien->HoTen ?? 'Chưa gán' }}</strong>
                </div>
            </div>
        @else
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                <div>
                    <i class="fa-solid fa-circle-info me-2"></i>Nhóm của bạn chưa đăng ký Đề tài Khóa luận.
                </div>
                <a href="{{ route('sinhvien.dangky.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-clipboard-list me-1"></i>Đăng Ký Đề Tài Ngay
                </a>
            </div>
        @endif

        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="fw-bold text-primary-custom mb-0"><i class="fa-solid fa-user-group me-2"></i>Danh Sách Thành Viên ({{ $nhomCurrent->thanhViens->count() }}/3 SV)</h5>
            </div>
            @if($nhomCurrent->MaTruongNhom === $sinhVien->MaSV && $nhomCurrent->thanhViens->count() < 3)
            <div class="col-md-6">
                <form action="{{ route('sinhvien.nhom.moiThanhVien') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="MaNhom" value="{{ $nhomCurrent->MaNhom }}">
                    <input type="text" name="MSSV_Them" class="form-control form-control-sm" placeholder="Nhập MSSV / Tên đăng nhập bạn học..." required>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill text-nowrap px-3">
                        <i class="fa-solid fa-user-plus me-1"></i>Mời Bạn
                    </button>
                </form>
            </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="15%">MSSV</th>
                        <th width="35%">Họ Và Tên</th>
                        <th width="25%">Lớp Hành Chính</th>
                        <th width="25%" class="text-center">Vai Trò Trong Nhóm</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nhomCurrent->thanhViens as $tv)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $tv->sinhVien->taiKhoan->TenDangNhap ?? $tv->MaSV }}</span></td>
                        <td class="fw-bold">{{ $tv->sinhVien->HoTen ?? '' }}</td>
                        <td>{{ $tv->sinhVien->lop->TenLop ?? '' }}</td>
                        <td class="text-center">
                            @if($tv->VaiTro === 'Trưởng nhóm')
                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-crown me-1"></i>Trưởng nhóm</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3">Thành viên</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection