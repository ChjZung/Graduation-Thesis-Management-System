@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Ngành Đào Tạo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-pen-to-square text-white me-1"></i> Chỉnh Sửa Thông Tin Ngành Đào Tạo: {{ $nganh->TenNganh }}
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('nganh.update', $nganh->MaNganh) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Ngành (Theo Bộ GD&ĐT)</label>
                            <input type="text" class="form-control form-control-huit bg-light" value="{{ $nganh->MaNganh }}" readonly>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Ngành Đào Tạo <span class="text-danger">*</span></label>
                            <input type="text" name="TenNganh" class="form-control form-control-huit" value="{{ old('TenNganh', $nganh->TenNganh) }}" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Thuộc Khoa Đào Tạo <span class="text-danger">*</span></label>
                            <select name="MaKhoa" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Khoa Đào Tạo --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ old('MaKhoa', $nganh->MaKhoa) == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }} ({{ $k->MaKhoa }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Tổng Số Tín Chỉ <span class="text-danger">*</span></label>
                            <input type="text" name="SoTinChi" class="form-control form-control-huit" value="{{ old('SoTinChi', '150 TC') }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-huit">Trạng Thái Hoạt Động</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="Active" selected>🟢 Hoạt động bình thường (Active)</option>
                            <option value="Pause">🔴 Tạm dừng tuyển sinh</option>
                        </select>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Cập Nhật Ngành
                        </button>
                        <a href="{{ route('nganh.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection