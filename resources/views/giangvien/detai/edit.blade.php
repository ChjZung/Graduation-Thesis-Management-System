@extends('layouts.giangvien')

@section('page_title', 'Chỉnh Sửa Đề Tài')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Đề Tài Đề Xuất</span>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.update', $detai->MaDeTai) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="TenDeTai" class="form-label fw-bold">Tên Đề Tài <span class="text-danger">*</span></label>
                        <input type="text" name="TenDeTai" id="TenDeTai" class="form-control" value="{{ old('TenDeTai', $detai->TenDeTai) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $detai->MaHocKy == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="SoLuongSinhVienToiDa" class="form-label fw-bold">Số SV Tối Đa <span class="text-danger">*</span></label>
                            <select name="SoLuongSinhVienToiDa" id="SoLuongSinhVienToiDa" class="form-select" required>
                                <option value="1" {{ $detai->SoLuongSinhVienToiDa == 1 ? 'selected' : '' }}>1 Sinh viên</option>
                                <option value="2" {{ $detai->SoLuongSinhVienToiDa == 2 ? 'selected' : '' }}>2 Sinh viên</option>
                                <option value="3" {{ $detai->SoLuongSinhVienToiDa == 3 ? 'selected' : '' }}>3 Sinh viên</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="LinhVuc" class="form-label fw-bold">Lĩnh Vực</label>
                            <input type="text" name="LinhVuc" id="LinhVuc" class="form-control" value="{{ old('LinhVuc', $detai->LinhVuc) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="MoTa" class="form-label fw-bold">Mô Tả Đề Tài</label>
                        <textarea name="MoTa" id="MoTa" class="form-control" rows="3">{{ old('MoTa', $detai->MoTa) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="YeuCau" class="form-label fw-bold">Yêu Cầu Kỹ Thuật</label>
                        <textarea name="YeuCau" id="YeuCau" class="form-control" rows="3">{{ old('YeuCau', $detai->YeuCau) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="fa-solid fa-save me-1"></i> Cập Nhật Đề Tài
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection