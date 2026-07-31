@extends('layouts.admin')
@section('page_title', 'Sửa Lớp')
@section('content')
<div class="card card-premium w-50 mx-auto"><div class="card-body p-4">
    <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Cập Nhật Thông Tin Lớp</h5>
    <form action="{{ route('lop.update', $lop->MaLop) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3"><label class="fw-bold small text-muted">Tên Lớp <span class="text-danger">*</span></label><input type="text" name="TenLop" class="form-control" value="{{ $lop->TenLop }}" required></div>
        <div class="mb-3"><label class="fw-bold small text-muted">Ngành <span class="text-danger">*</span></label>
            <select name="MaNganh" class="form-select" required>
                @foreach($nganhs as $nganh) <option value="{{ $nganh->MaNganh }}" {{ $lop->MaNganh == $nganh->MaNganh ? 'selected' : '' }}>{{ $nganh->TenNganh }}</option> @endforeach
            </select>
        </div>

        <div class="mb-3"><label class="fw-bold small text-muted">Khóa Học <span class="text-danger">*</span></label><input type="text" name="KhoaHoc" class="form-control" value="{{ $lop->KhoaHoc }}" required></div>
        <div class="text-end mt-4"><a href="{{ route('lop.index') }}" class="btn btn-light rounded-pill px-3 me-2">Hủy</a><button type="submit" class="btn btn-primary-custom rounded-pill px-4">Cập Nhật</button></div>
    </form>
</div></div>
@endsection