@extends('layouts.admin')
@section('page_title', 'Sửa Sinh Viên')
@section('content')
<div class="card card-premium w-75 mx-auto"><div class="card-body p-4">
    <form action="{{ route('sinhvien.update', $sinhvien->MaSV) }}" method="POST">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-3"><label>Tên Đăng Nhập</label><input type="text" name="TenDangNhap" class="form-control" value="{{ $sinhvien->taiKhoan->TenDangNhap ?? '' }}" required></div>
            <div class="col-md-6 mb-3"><label>Họ Tên SV</label><input type="text" name="HoTen" class="form-control" value="{{ $sinhvien->HoTen }}" required></div>
            <div class="col-md-6 mb-3"><label>Lớp</label>
                <select name="MaLop" class="form-select" required>
                    @foreach($lops as $lop) <option value="{{ $lop->MaLop }}" {{ $sinhvien->MaLop == $lop->MaLop ? 'selected' : '' }}>{{ $lop->TenLop }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="Email" class="form-control" value="{{ $sinhvien->Email }}" required></div>
            <div class="col-md-6 mb-3"><label>Số Điện Thoại</label><input type="text" name="SoDienThoai" class="form-control" value="{{ $sinhvien->SoDienThoai }}" required></div>
        </div>
        <div class="text-end mt-3"><button type="submit" class="btn btn-primary-custom">Cập Nhật</button></div>
    </form>
</div></div>
@endsection