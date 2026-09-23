@extends('layouts.admin')

@section('page_title', 'Thêm Mới Khoa Đào Tạo & Phân Công Bộ Môn')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-plus text-white me-1"></i> Thêm Mới Khoa Đào Tạo & Phân Công Bộ Môn
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('khoa.store') }}">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Khoa Viết Tắt <span class="text-danger">*</span></label>
                            <input type="text" name="MaKhoa" class="form-control form-control-huit" value="{{ old('MaKhoa') }}" placeholder="CNTT" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Khoa Đào Tạo <span class="text-danger">*</span></label>
                            <input type="text" name="TenKhoa" class="form-control form-control-huit" value="{{ old('TenKhoa') }}" placeholder="Khoa Công nghệ Thông tin" required>
                        </div>
                    </div>



                    <div class="mb-4">
                        <label class="form-label-huit">Trạng Thái Hoạt Động Hệ Thống</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="Active" selected>🟢 Hoạt động bình thường (Active)</option>
                            <option value="Inactive">🔴 Tạm ngưng</option>
                        </select>
                    </div>

                    <div class="alert-info-huit mb-4">
                        <i class="fa-solid fa-circle-info me-2 text-primary"></i>Thông tin khoa và các bộ môn trực thuộc sẽ tự động liên kết với phân hệ phân công đề tài tốt nghiệp.
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Khoa
                        </button>
                        <a href="{{ route('khoa.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
