@extends('layouts.admin')

@section('page_title', 'Thêm Mới Học Kỳ')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i>Thêm Mới Học Kỳ
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-3">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('hocky.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Học Kỳ <span class="text-muted small">(để trống để tự động sinh)</span></label>
                        <input type="text" name="MaHocKy" class="form-control" value="{{ old('MaHocKy') }}" placeholder="VD: HK01, HK2425...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Học Kỳ <span class="text-danger">*</span></label>
                        <input type="text" name="TenHocKy" class="form-control" value="{{ old('TenHocKy') }}" required placeholder="VD: Học kỳ 1, Học kỳ 2, Học kỳ Hè...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Năm Học <span class="text-danger">*</span></label>
                        <input type="text" name="NamHoc" class="form-control" value="{{ old('NamHoc', '2025-2026') }}" required placeholder="VD: 2025-2026">
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                            <input type="date" name="NgayBatDau" class="form-control" value="{{ old('NgayBatDau', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày Kết Thúc <span class="text-danger">*</span></label>
                            <input type="date" name="NgayKetThuc" class="form-control" value="{{ old('NgayKetThuc', date('Y-m-d', strtotime('+5 months'))) }}" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('hocky.index') }}" class="btn btn-light rounded-pill px-4">Quay Lại</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Học Kỳ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection