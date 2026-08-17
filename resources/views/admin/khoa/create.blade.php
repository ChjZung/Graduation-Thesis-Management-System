@extends('layouts.admin')

@section('page_title', 'Thêm Mới Khoa')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-plus-circle me-2 text-success"></i>Thêm Mới Khoa
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('khoa.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="MaKhoa" class="form-label fw-bold">Mã Khoa <span class="text-danger">*</span></label>
                        <input type="text" name="MaKhoa" id="MaKhoa" class="form-control" value="{{ old('MaKhoa') }}" placeholder="Ví dụ: CNTT, CK, TP..." required>
                    </div>

                    <div class="mb-4">
                        <label for="TenKhoa" class="form-label fw-bold">Tên Khoa <span class="text-danger">*</span></label>
                        <input type="text" name="TenKhoa" id="TenKhoa" class="form-control" value="{{ old('TenKhoa') }}" placeholder="Nhập tên khoa..." required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-4 rounded-pill">
                            <i class="fa-solid fa-save me-1"></i> Lưu Thông Tin
                        </button>
                        <a href="{{ route('khoa.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
