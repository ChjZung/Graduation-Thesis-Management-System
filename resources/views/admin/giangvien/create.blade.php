@extends('layouts.admin')
@section('page_title', 'Thêm Giảng Viên')
@section('content')
<div class="card card-premium w-75 mx-auto"><div class="card-body p-4">
    <div class="alert alert-info">Mật khẩu tài khoản sẽ được tạo mặc định là <b>123456</b></div>
    <form action="{{ route('giangvien.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3"><label>Tên Đăng Nhập (Tài khoản)</label><input type="text" name="TenDangNhap" class="form-control" required placeholder="VD: GV001"></div>
            <div class="col-md-6 mb-3"><label>Họ Tên GV</label><input type="text" name="HoTen" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label>Học Vị</label><input type="text" name="HocVi" class="form-control" required placeholder="Thạc sĩ, Tiến sĩ..."></div>
            <div class="col-md-6 mb-3"><label>Bộ Môn</label>
                <select name="MaBoMon" class="form-select" required>
                    @foreach($bomons as $bomon) <option value="{{ $bomon->MaBoMon }}">{{ $bomon->TenBoMon }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="Email" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label>Số Điện Thoại</label><input type="text" name="SoDienThoai" class="form-control" required></div>
        </div>
        <div class="text-end mt-3"><button type="submit" class="btn btn-primary-custom">Thêm Giảng Viên</button></div>
    </form>
</div></div>
@endsection