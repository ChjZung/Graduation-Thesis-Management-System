@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Học Kỳ')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Học Kỳ: {{ $hocky->TenHocKy }} ({{ $hocky->NamHoc }})
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('hocky.update', $hocky->MaHocKy) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Học Kỳ</label>
                        <input type="text" class="form-control bg-light" value="{{ $hocky->MaHocKy }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Học Kỳ <span class="text-danger">*</span></label>
                        <input type="text" name="TenHocKy" class="form-control" value="{{ old('TenHocKy', $hocky->TenHocKy) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Năm Học <span class="text-danger">*</span></label>
                        <input type="text" name="NamHoc" class="form-control" value="{{ old('NamHoc', $hocky->NamHoc) }}" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                            <input type="date" name="NgayBatDau" class="form-control" value="{{ old('NgayBatDau', $hocky->NgayBatDau) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày Kết Thúc <span class="text-danger">*</span></label>
                            <input type="date" name="NgayKetThuc" class="form-control" value="{{ old('NgayKetThuc', $hocky->NgayKetThuc) }}" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('hocky.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection