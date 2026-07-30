@extends('layouts.admin')

@section('page_title', 'Thêm Bộ Môn Mới')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-premium">
            <div class="card-header-premium">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i> Nhập Thông Tin Bộ Môn
            </div>
            <div class="card-body p-4">
                <form action="{{ route('bomon.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tên Bộ Môn <span class="text-danger">*</span></label>
                        <input type="text" name="TenBoMon" class="form-control form-control-lg bg-light" required placeholder="Nhập tên bộ môn (vd: Công nghệ Phần mềm)">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mô Tả</label>
                        <textarea name="MoTa" rows="4" class="form-control bg-light" placeholder="Nhập mô tả chi tiết cho bộ môn này..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('bomon.index') }}" class="btn btn-light me-2 px-4 py-2">Hủy Bỏ</a>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2">
                            <i class="fa-solid fa-save me-1"></i> Lưu Dữ Liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
