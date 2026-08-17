@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Khoa')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Chỉnh Sửa Khoa
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('khoa.update', $khoa->MaKhoa) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Khoa</label>
                        <input type="text" class="form-control bg-light" value="{{ $khoa->MaKhoa }}" disabled>
                    </div>

                    <div class="mb-4">
                        <label for="TenKhoa" class="form-label fw-bold">Tên Khoa <span class="text-danger">*</span></label>
                        <input type="text" name="TenKhoa" id="TenKhoa" class="form-control" value="{{ old('TenKhoa', $khoa->TenKhoa) }}" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">
                            <i class="fa-solid fa-save me-1"></i> Cập Nhật
                        </button>
                        <a href="{{ route('khoa.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
