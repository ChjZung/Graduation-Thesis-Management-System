@extends('layouts.admin')

@section('page_title', 'Thêm Quy Định Khóa Luận')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa-solid fa-scale-balanced text-primary me-2"></i>Thêm Quy Định / Chuẩn Khóa Luận Mới
                </div>
                <a href="{{ route('admin.quydinh.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger p-3 rounded-3 mb-4">
                        <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.quydinh.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kế Hoạch Khóa Luận Áp Dụng <span class="text-danger">*</span></label>
                        <select name="MakeHoach" class="form-select" required>
                            <option value="">-- Chọn Kế hoạch khóa luận --</option>
                            @foreach($keHoachs as $kh)
                                <option value="{{ $kh->MakeHoach }}" {{ (old('MakeHoach') == $kh->MakeHoach || request('make_hoach') == $kh->MakeHoach) ? 'selected' : '' }}>
                                    {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MakeHoach }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Tên Quy Định / Tiêu Chuẩn <span class="text-danger">*</span></label>
                            <input type="text" name="TenQuyDinh" class="form-control" value="{{ old('TenQuyDinh') }}" required placeholder="VD: Tín chỉ tích lũy tối thiểu, Tỷ lệ Turnitin tối đa...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mã Quy Định (Tùy chọn)</label>
                            <input type="text" name="MaQuyDinh" class="form-control" value="{{ old('MaQuyDinh') }}" placeholder="VD: QD_TINCHI_K14">
                            <div class="form-text small">Để trống hệ thống sẽ tự sinh mã.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá Trị Tham Số Quy Chế <span class="text-danger">*</span></label>
                        <input type="text" name="GiaTri" class="form-control" value="{{ old('GiaTri') }}" required placeholder="VD: 115 Tín chỉ, 2.0 GPA, 5 Đề tài, <= 20%...">
                        <div class="form-text small">Giá trị này dùng để đối soát tự động khi sinh viên đăng ký đề tài hoặc nộp hồ sơ.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Mô Tả Chi Tiết &amp; Căn Cứ Văn Bản</label>
                        <textarea name="MoTa" class="form-control" rows="4" placeholder="Nhập trích dẫn quyết định hoặc nội dung chi tiết điều khoản quy định...">{{ old('MoTa') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.quydinh.index') }}" class="btn btn-light border px-4 rounded-pill">Quay Lại</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                            <i class="fa-solid fa-save me-1"></i> Lưu Quy Định
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
