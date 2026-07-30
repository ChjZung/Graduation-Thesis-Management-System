@extends('layouts.admin')
@section('page_title', 'Sửa Ngành')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('nganh.update', $nganh->MaNganh) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3"><label>Tên Ngành</label><input type="text" name="TenNganh" class="form-control" value="{{ $nganh->TenNganh }}" required></div>
        <div class="mb-3"><label>Mô Tả</label><textarea name="MoTa" class="form-control">{{ $nganh->MoTa }}</textarea></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Cập Nhật</button></div>
    </form>
</div></div>
@endsection