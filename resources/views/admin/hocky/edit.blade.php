@extends('layouts.admin')
@section('page_title', 'Sửa Học Kỳ')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('hocky.update', $hocky->MaHocKy) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3"><label>Tên Học Kỳ</label><input type="text" name="TenHocKy" class="form-control" value="{{ $hocky->TenHocKy }}" required></div>
        <div class="mb-3"><label>Năm Học</label><input type="text" name="NamHoc" class="form-control" value="{{ $hocky->NamHoc }}" required></div>
        <div class="mb-3"><label>Ngày Bắt Đầu</label><input type="date" name="NgayBatDau" class="form-control" value="{{ $hocky->NgayBatDau }}" required></div>
        <div class="mb-3"><label>Ngày Kết Thúc</label><input type="date" name="NgayKetThuc" class="form-control" value="{{ $hocky->NgayKetThuc }}" required></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Cập Nhật</button></div>
    </form>
</div></div>
@endsection