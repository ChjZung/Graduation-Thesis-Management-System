@extends('layouts.admin')
@section('page_title', 'Thêm Ngành')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('nganh.store') }}" method="POST">
        @csrf
        <div class="mb-3"><label>Tên Ngành</label><input type="text" name="TenNganh" class="form-control" required></div>
        <div class="mb-3"><label>Mô Tả</label><textarea name="MoTa" class="form-control"></textarea></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Lưu</button></div>
    </form>
</div></div>
@endsection