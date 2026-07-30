@extends('layouts.admin')

@section('page_title', 'Cập Nhật Bộ Môn')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Chỉnh Sửa Thông Tin
            </div>
            <div class="card-body p-4">
                <form action="{{ route('bomon.update', $bomon->MaBoMon) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mã Bộ Môn</label>
                        <input type="text" class="form-control bg-light" value="{{ $bomon->MaBoMon }}" readonly disabled>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tên Bộ Môn <span class="text-danger">*</span></label>
                        <input type="text" name="TenBoMon" class="form-control form-control-lg bg-light" value="{{ $bomon->TenBoMon }}" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mô Tả</label>
                        <textarea name="MoTa" rows="4" class="form-control bg-light">{{ $bomon->MoTa }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('bomon.index') }}" class="btn btn-light me-2 px-4 py-2">Hủy Bỏ</a>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2">
                            <i class="fa-solid fa-save me-1"></i> Cập Nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
