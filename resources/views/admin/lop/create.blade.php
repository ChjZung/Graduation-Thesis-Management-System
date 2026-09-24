@extends('layouts.admin')

@section('page_title', 'Thêm Mới Lớp Học Sinh Hoạt')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-plus text-white me-1"></i> Thêm Mới Lớp Học Sinh Hoạt
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('lop.store') }}">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Lớp Học <span class="text-danger">*</span></label>
                            <input type="text" name="TenLop" class="form-control form-control-huit" value="{{ old('TenLop') }}" placeholder="14DHTH03" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Lớp Sinh Hoạt <span class="text-danger">*</span></label>
                            <input type="text" name="TenLopFull" class="form-control form-control-huit" value="{{ old('TenLopFull') }}" placeholder="Đại học Kỹ thuật Phần mềm 03">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Chuyên Ngành Đào Tạo <span class="text-danger">*</span></label>
                            <select name="MaNganh" class="form-select form-select-huit" required>
                                <option value="">-- Chọn ngành đào tạo --</option>
                                @foreach($nganhs as $ng)
                                    <option value="{{ $ng->MaNganh }}">{{ $ng->TenNganh }} ({{ $ng->khoa->TenKhoa ?? 'CNTT' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Sĩ Số Sinh Viên Dự Kiến</label>
                            <input type="text" name="KhoaHoc" class="form-control form-control-huit" value="{{ old('KhoaHoc', '50 SV') }}" placeholder="50 SV">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-huit">Cố Vấn Học Tập (CVHT)</label>
                        <select name="CVHT" class="form-select form-select-huit">
                            <option value="">-- Chọn CVHT / Giảng viên phụ trách --</option>
                            <option value="ThS. Vũ Hoàng Nam" selected>ThS. Vũ Hoàng Nam</option>
                            <option value="TS. Lê Văn Hải">TS. Lê Văn Hải</option>
                            <option value="TS. Nguyễn Văn A">TS. Nguyễn Văn A</option>
                        </select>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Lớp
                        </button>
                        <a href="{{ route('lop.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection