@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Quy Định - ' . $quyDinh->TenQuyDinh)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Quy Định: {{ $quyDinh->TenQuyDinh }}
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

                <form method="POST" action="{{ route('admin.quydinh.update', $quyDinh->MaQuyDinh) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Quy Định</label>
                        <input type="text" class="form-control bg-light" value="{{ $quyDinh->MaQuyDinh }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kế Hoạch Khóa Luận Áp Dụng <span class="text-danger">*</span></label>
                        <select name="MakeHoach" class="form-select" required>
                            @foreach($keHoachs as $kh)
                                <option value="{{ $kh->MakeHoach }}" {{ old('MakeHoach', $quyDinh->MakeHoach) == $kh->MakeHoach ? 'selected' : '' }}>
                                    {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MakeHoach }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Quy Định / Tiêu Chuẩn <span class="text-danger">*</span></label>
                        <input type="text" name="TenQuyDinh" class="form-control" value="{{ old('TenQuyDinh', $quyDinh->TenQuyDinh) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá Trị Tham Số Quy Chế <span class="text-danger">*</span></label>
                        <input type="text" name="GiaTri" class="form-control" value="{{ old('GiaTri', $quyDinh->GiaTri) }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Mô Tả Chi Tiết &amp; Căn Cứ Văn Bản</label>
                        <textarea name="MoTa" class="form-control" rows="4">{{ old('MoTa', $quyDinh->MoTa) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.quydinh.index') }}" class="btn btn-light border px-4 rounded-pill">Quay Lại</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                            <i class="fa-solid fa-save me-1"></i> Lưu Cập Nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
