@extends('layouts.admin')
@section('page_title', 'Sửa Giảng Viên')
@section('content')
<div class="card card-premium w-75 mx-auto"><div class="card-body p-4">
    <form action="{{ route('giangvien.update', $giangvien->MaGV) }}" method="POST">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-3"><label>Tên Đăng Nhập</label><input type="text" name="TenDangNhap" class="form-control" value="{{ $giangvien->taiKhoan->TenDangNhap ?? '' }}" required></div>
            <div class="col-md-6 mb-3"><label>Họ Tên GV</label><input type="text" name="HoTen" class="form-control" value="{{ $giangvien->HoTen }}" required></div>
            <div class="col-md-6 mb-3"><label>Học Vị</label><input type="text" name="HocVi" class="form-control" value="{{ $giangvien->HocVi }}" required></div>
            <div class="col-md-6 mb-3"><label>Bộ Môn</label>
                <select name="MaBoMon" class="form-select" required>
                    @foreach($bomons as $bomon) <option value="{{ $bomon->MaBoMon }}" {{ $giangvien->MaBoMon == $bomon->MaBoMon ? 'selected' : '' }}>{{ $bomon->TenBoMon }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="Email" class="form-control" value="{{ $giangvien->Email }}" required></div>
            <div class="col-md-6 mb-3"><label>Số Điện Thoại</label><input type="text" name="SoDienThoai" class="form-control" value="{{ $giangvien->SoDienThoai }}" required></div>
        </div>
        <div class="text-end mt-3"><button type="submit" class="btn btn-primary-custom">Cập Nhật</button></div>
    </form>
</div></div>
@endsection