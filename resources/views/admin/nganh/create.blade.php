@extends('layouts.admin')

@section('page_title', 'Thêm Mới Ngành Học')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i>Thêm Mới Ngành Học
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('nganh.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Ngành <span class="text-muted small">(để trống để tự động sinh)</span></label>
                        <input type="text" name="MaNganh" class="form-control" value="{{ old('MaNganh') }}" placeholder="VD: NG01, CNTT, KTPM...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Ngành <span class="text-danger">*</span></label>
                        <input type="text" name="TenNganh" class="form-control" value="{{ old('TenNganh') }}" required placeholder="VD: Công nghệ thông tin...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Khoa Trực Thuộc <span class="text-danger">*</span></label>
                        <select name="MaKhoa" class="form-select" required>
                            <option value="">-- Chọn Khoa --</option>
                            @foreach($khoas as $k)
                                <option value="{{ $k->MaKhoa }}" {{ old('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                                    {{ $k->TenKhoa }} ({{ $k->MaKhoa }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('nganh.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Ngành Học</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection