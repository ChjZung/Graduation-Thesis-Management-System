@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Tài Khoản - ' . $taiKhoan->TenDangNhap)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa-solid fa-user-pen text-primary me-2"></i>Chỉnh Sửa Tài Khoản: {{ $taiKhoan->TenDangNhap }}
                </div>
                <a href="{{ route('admin.taikhoan.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger p-3 rounded-3 mb-4">
                        <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.taikhoan.update', $taiKhoan->MaTK) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên Đăng Nhập</label>
                            <input type="text" class="form-control bg-light" value="{{ $taiKhoan->TenDangNhap }}" readonly>
                            <div class="form-text small">Tên đăng nhập không thể thay đổi sau khi tạo.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vai Trò Phân Quyền <span class="text-danger">*</span></label>
                            <select name="MaVaiTro" class="form-select" required>
                                @foreach($vaiTros as $vt)
                                    <option value="{{ $vt->MaVaiTro }}" {{ old('MaVaiTro', $taiKhoan->MaVaiTro) == $vt->MaVaiTro ? 'selected' : '' }}>
                                        {{ $vt->TenVaiTro }} ({{ $vt->MaVaiTro }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng Thái Hoạt Động <span class="text-danger">*</span></label>
                            <select name="TrangThai" class="form-select" required>
                                <option value="1" {{ old('TrangThai', $taiKhoan->TrangThai) ? 'selected' : '' }}>Hoạt động (Active)</option>
                                <option value="0" {{ !old('TrangThai', $taiKhoan->TrangThai) ? 'selected' : '' }}>Đang khóa (Locked)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Đổi Mật Khẩu Mới (Nếu cần)</label>
                            <input type="password" name="MatKhauMoi" class="form-control" placeholder="Để trống nếu không muốn đổi">
                            <div class="form-text small">Tối thiểu 6 ký tự. Để trống nếu giữ nguyên mật khẩu cũ.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="BatBuocDoiMatKhau" id="chkBatBuoc" value="1" {{ old('BatBuocDoiMatKhau', $taiKhoan->BatBuocDoiMatKhau) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark" for="chkBatBuoc">
                                Bắt buộc người dùng phải đổi mật khẩu khi đăng nhập lần tới (BR02)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.taikhoan.index') }}" class="btn btn-light border px-4 rounded-pill">Quay Lại</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                            <i class="fa-solid fa-save me-1"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
