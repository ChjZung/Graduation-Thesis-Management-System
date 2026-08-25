@extends('layouts.admin')

@section('page_title', 'Thêm Mới Lớp Học')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i>Thêm Mới Lớp Học
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('lop.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Lớp <span class="text-muted small">(để trống để tự động sinh)</span></label>
                        <input type="text" name="MaLop" class="form-control" value="{{ old('MaLop') }}" placeholder="VD: L01, 12DHTH01...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Lớp <span class="text-danger">*</span></label>
                        <input type="text" name="TenLop" class="form-control" value="{{ old('TenLop') }}" required placeholder="VD: 12DHTH01...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ngành Học <span class="text-danger">*</span></label>
                        <select name="MaNganh" class="form-select" required>
                            <option value="">-- Chọn Ngành Học --</option>
                            @foreach($nganhs as $n)
                                <option value="{{ $n->MaNganh }}" {{ old('MaNganh') == $n->MaNganh ? 'selected' : '' }}>
                                    {{ $n->TenNganh }} ({{ $n->khoa->TenKhoa ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Khóa Học <span class="text-danger">*</span></label>
                        <input type="text" name="KhoaHoc" class="form-control" value="{{ old('KhoaHoc', '2022-2026') }}" required placeholder="VD: 2022-2026, K12...">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('lop.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Lớp Học</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection