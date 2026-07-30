@extends('layouts.admin')
@section('page_title', 'Sửa Lớp')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('lop.update', $lop->MaLop) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3"><label>Tên Lớp</label><input type="text" name="TenLop" class="form-control" value="{{ $lop->TenLop }}" required></div>
        <div class="mb-3"><label>Ngành</label>
            <select name="MaNganh" class="form-select" required>
                @foreach($nganhs as $nganh) <option value="{{ $nganh->MaNganh }}" {{ $lop->MaNganh == $nganh->MaNganh ? 'selected' : '' }}>{{ $nganh->TenNganh }}</option> @endforeach
            </select>
        </div>
        <div class="mb-3"><label>Khóa Học</label><input type="text" name="KhoaHoc" class="form-control" value="{{ $lop->KhoaHoc }}" required></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Cập Nhật</button></div>
    </form>
</div></div>
@endsection