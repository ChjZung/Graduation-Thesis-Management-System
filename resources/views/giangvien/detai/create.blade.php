@extends('layouts.giangvien')

@section('page_title', 'Đề Xuất Đề Tài Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-plus-circle text-success me-2"></i>Đề Xuất Đề Tài Khóa Luận Mới</span>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="TenDeTai" class="form-label fw-bold">Tên Đề Tài (Tiếng Việt) <span class="text-danger">*</span></label>
                        <input type="text" name="TenDeTai" id="TenDeTai" class="form-control" value="{{ old('TenDeTai') }}" placeholder="Nhập tên đề tài khóa luận..." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                <option value="">-- Chọn học kỳ --</option>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}">{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="SoLuongSinhVienToiDa" class="form-label fw-bold">Số SV Tối Đa <span class="text-danger">*</span></label>
                            <select name="SoLuongSinhVienToiDa" id="SoLuongSinhVienToiDa" class="form-select" required>
                                <option value="1">1 Sinh viên</option>
                                <option value="2" selected>2 Sinh viên</option>
                                <option value="3">3 Sinh viên</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="LinhVuc" class="form-label fw-bold">Lĩnh Vực Chuyên Môn</label>
                            <input type="text" name="LinhVuc" id="LinhVuc" class="form-control" value="{{ old('LinhVuc', 'Công Nghệ Phần Mềm') }}" placeholder="Ví dụ: AI, Web, Mobile...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="MoTa" class="form-label fw-bold">Mô Tả Đề Tài & Mục Tiêu Nghiên Cứu</label>
                        <textarea name="MoTa" id="MoTa" class="form-control" rows="3" placeholder="Nhập mô tả chi tiết bài toán, phạm vi nghiên cứu...">{{ old('MoTa') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="YeuCau" class="form-label fw-bold">Yêu Cầu Kỹ Thuật & Công Nghệ</label>
                        <textarea name="YeuCau" id="YeuCau" class="form-control" rows="3" placeholder="Ví dụ: Sử dụng Laravel 11, MySQL, Vue.js / ReactJS, RESTful API...">{{ old('YeuCau') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm">
                            <i class="fa-solid fa-paper-plane me-1"></i> Gửi Đề Xuất Cho Giáo Vụ Duyệt
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection