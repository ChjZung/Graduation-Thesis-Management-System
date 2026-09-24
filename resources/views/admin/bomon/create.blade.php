@extends('layouts.admin')

@section('page_title', 'Thêm Mới Bộ Môn Chuyên Môn')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-plus text-white me-1"></i> Thêm Mới Bộ Môn Chuyên Môn
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('bomon.store') }}">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Bộ Môn <span class="text-danger">*</span></label>
                            <input type="text" name="MaBoMon" class="form-control form-control-huit" value="{{ old('MaBoMon') }}" placeholder="BM_IoT" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Bộ Môn Chuyên Môn <span class="text-danger">*</span></label>
                            <input type="text" name="TenBoMon" class="form-control form-control-huit" value="{{ old('TenBoMon') }}" placeholder="Bộ môn Internet vạn vật (IoT) & Nhúng" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Thuộc Khoa Đào Tạo <span class="text-danger">*</span></label>
                            <select name="MaKhoa" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Khoa Đào Tạo --</option>
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ old('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                                        {{ $k->TenKhoa }} ({{ $k->MaKhoa }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Trưởng Bộ Môn</label>
                            <select name="TruongBoMon" class="form-select form-select-huit">
                                <option value="">-- Chọn giảng viên --</option>
                                @if(isset($giangViens))
                                    @foreach($giangViens as $gv)
                                        @php $val = ($gv->HocVi ? $gv->HocVi . '. ' : '') . $gv->HoTen . " ({$gv->MaGV})"; @endphp
                                        <option value="{{ $val }}" {{ old('TruongBoMon') == $val ? 'selected' : '' }}>
                                            {{ $val }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-huit">Trạng Thái Hoạt Động</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="Active" selected>🟢 Hoạt động bình thường (Active)</option>
                            <option value="Inactive">🔴 Tạm ngưng</option>
                        </select>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Bộ Môn
                        </button>
                        <a href="{{ route('bomon.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
