@extends('layouts.admin')

@section('page_title', 'Thêm Mới Giảng Viên')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-plus text-white me-2"></i> Thêm Mới Giảng Viên Hướng Dẫn & Phản Biện
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('giangvien.store') }}">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Giảng Viên (MaGV) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control form-control-huit @error('TenDangNhap') is-invalid @enderror" value="{{ old('TenDangNhap') }}" placeholder="GV00000001" minlength="10" maxlength="10" pattern="^GV[0-9]{8}$" title="Mã giảng viên phải gồm tiền tố GV và 8 chữ số (ví dụ: GV00000001)" required>
                            @error('TenDangNhap')
                                <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">MaGV dùng làm tài khoản đăng nhập (10 ký tự, định dạng GV + 8 số). Mật khẩu: <code>123456</code></div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Họ Và Tên Giảng Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control form-control-huit @error('HoTen') is-invalid @enderror" value="{{ old('HoTen') }}" placeholder="Nguyễn Văn A" minlength="2" maxlength="100" pattern="^[A-Za-zÀ-ỹ\s]+$" title="Họ tên chỉ được chứa chữ cái và khoảng trắng" required>
                            @error('HoTen')
                                <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Học Vị <span class="text-danger">*</span></label>
                            <select name="HocVi" class="form-select form-select-huit @error('HocVi') is-invalid @enderror" required>
                                <option value="Thạc sĩ" {{ old('HocVi') == 'Thạc sĩ' ? 'selected' : '' }}>Thạc sĩ</option>
                                <option value="Tiến sĩ" {{ old('HocVi', 'Tiến sĩ') == 'Tiến sĩ' ? 'selected' : '' }}>Tiến sĩ</option>
                                <option value="Phó Giáo sư" {{ old('HocVi') == 'Phó Giáo sư' ? 'selected' : '' }}>Phó Giáo sư</option>
                                <option value="Giáo sư" {{ old('HocVi') == 'Giáo sư' ? 'selected' : '' }}>Giáo sư</option>
                                <option value="Kỹ sư" {{ old('HocVi') == 'Kỹ sư' ? 'selected' : '' }}>Kỹ sư</option>
                                <option value="Cử nhân" {{ old('HocVi') == 'Cử nhân' ? 'selected' : '' }}>Cử nhân</option>
                            </select>
                            @error('HocVi')
                                <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Bộ Môn Trực Thuộc <span class="text-danger">*</span></label>
                            <select name="MaBoMon" class="form-select form-select-huit @error('MaBoMon') is-invalid @enderror" required>
                                <option value="">-- Chọn Bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? 'Khoa CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('MaBoMon')
                                <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control form-control-huit @error('Email') is-invalid @enderror" value="{{ old('Email') }}" placeholder="email@huit.edu.vn" required>
                            @error('Email')
                                <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control form-control-huit @error('SoDienThoai') is-invalid @enderror" value="{{ old('SoDienThoai') }}" placeholder="0912345678" pattern="^0[0-9]{9}$" maxlength="10" inputmode="numeric" title="Số điện thoại gồm đúng 10 chữ số bắt đầu bằng số 0">
                            @error('SoDienThoai')
                                <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit d-inline-flex align-items-center">
                            <i class="fa-solid fa-check me-2"></i> Lưu & Thêm Giảng Viên
                        </button>
                        <a href="{{ route('giangvien.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection