@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Ngành Học')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Ngành: {{ $nganh->TenNganh }}
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('nganh.update', $nganh->MaNganh) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Ngành</label>
                        <input type="text" class="form-control bg-light" value="{{ $nganh->MaNganh }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Ngành <span class="text-danger">*</span></label>
                        <input type="text" name="TenNganh" class="form-control" value="{{ old('TenNganh', $nganh->TenNganh) }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Khoa Trực Thuộc <span class="text-danger">*</span></label>
                        <select name="MaKhoa" class="form-select" required>
                            <option value="">-- Chọn Khoa --</option>
                            @foreach($khoas as $k)
                                <option value="{{ $k->MaKhoa }}" {{ old('MaKhoa', $nganh->MaKhoa) == $k->MaKhoa ? 'selected' : '' }}>
                                    {{ $k->TenKhoa }} ({{ $k->MaKhoa }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('nganh.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection