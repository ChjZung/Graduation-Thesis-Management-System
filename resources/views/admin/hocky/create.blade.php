@extends('layouts.admin')
@section('page_title', 'Thêm Học Kỳ')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('hocky.store') }}" method="POST">
        @csrf
        <div class="mb-3"><label>Tên Học Kỳ (vd: Học kỳ 1)</label><input type="text" name="TenHocKy" class="form-control" required></div>
        <div class="mb-3"><label>Năm Học (vd: 2023-2024)</label><input type="text" name="NamHoc" class="form-control" required></div>
        <div class="mb-3"><label>Ngày Bắt Đầu</label><input type="date" name="NgayBatDau" class="form-control" required></div>
        <div class="mb-3"><label>Ngày Kết Thúc</label><input type="date" name="NgayKetThuc" class="form-control" required></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Lưu</button></div>
    </form>
</div></div>
@endsection