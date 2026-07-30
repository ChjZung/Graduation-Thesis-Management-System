@extends('layouts.admin')
@section('page_title', 'Thêm Lớp')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <form action="{{ route('lop.store') }}" method="POST">
        @csrf
        <div class="mb-3"><label>Tên Lớp</label><input type="text" name="TenLop" class="form-control" required></div>
        <div class="mb-3"><label>Ngành</label>
            <select name="MaNganh" class="form-select" required>
                <option value="">-- Chọn ngành --</option>
                @foreach($nganhs as $nganh) <option value="{{ $nganh->MaNganh }}">{{ $nganh->TenNganh }}</option> @endforeach
            </select>
        </div>
        <div class="mb-3"><label>Khóa Học</label><input type="text" name="KhoaHoc" class="form-control" required></div>
        <div class="text-end"><button type="submit" class="btn btn-primary-custom">Lưu</button></div>
    </form>
</div></div>
@endsection