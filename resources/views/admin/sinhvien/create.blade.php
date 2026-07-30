@extends('layouts.admin')
@section('page_title', 'Thêm Sinh Viên')
@section('content')
<div class="card card-premium w-75 mx-auto"><div class="card-body p-4">
    <div class="alert alert-info">Mật khẩu tài khoản sẽ được tạo mặc định là <b>123456</b></div>
    <form action="{{ route('sinhvien.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3"><label>Tên Đăng Nhập (Tài khoản)</label><input type="text" name="TenDangNhap" class="form-control" required placeholder="VD: SV001"></div>
            <div class="col-md-6 mb-3"><label>Họ Tên SV</label><input type="text" name="HoTen" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label>Lớp</label>
                <select name="MaLop" class="form-select" required>
                    @foreach($lops as $lop) <option value="{{ $lop->MaLop }}">{{ $lop->TenLop }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="Email" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label>Số Điện Thoại</label><input type="text" name="SoDienThoai" class="form-control" required></div>
        </div>
        <div class="text-end mt-3"><button type="submit" class="btn btn-primary-custom">Thêm Sinh Viên</button></div>
    </form>
</div></div>
@endsection