@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Lớp Hành Chính')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-pen-to-square text-white me-1"></i> Chỉnh Sửa Thông Tin Lớp: {{ $lop->TenLop }}
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('lop.update', $lop->MaLop) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Lớp Học</label>
                            <input type="text" class="form-control form-control-huit bg-light" value="{{ $lop->MaLop }}" readonly>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Lớp Hành Chính <span class="text-danger">*</span></label>
                            <input type="text" name="TenLop" class="form-control form-control-huit" value="{{ old('TenLop', $lop->TenLop) }}" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Ngành Đào Tạo <span class="text-danger">*</span></label>
                            <select name="MaNganh" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Ngành Đào Tạo --</option>
                                @foreach($nganhs as $n)
                                    <option value="{{ $n->MaNganh }}" {{ old('MaNganh', $lop->MaNganh) == $n->MaNganh ? 'selected' : '' }}>
                                        {{ $n->TenNganh }} ({{ $n->khoa->TenKhoa ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Khóa Học (Niên Khóa) <span class="text-danger">*</span></label>
                            <input type="text" name="KhoaHoc" class="form-control form-control-huit" value="{{ old('KhoaHoc', $lop->KhoaHoc) }}" placeholder="Khóa 12 (2021-2025)" required>
                        </div>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Cập Nhật Lớp Học
                        </button>
                        <a href="{{ route('lop.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection