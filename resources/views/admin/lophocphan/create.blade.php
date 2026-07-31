@extends('layouts.admin')
@section('page_title', 'Tạo Lớp Học Phần Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-plus-circle text-primary me-2"></i>Thêm Mới Lớp Học Phần (Lớp Tín Chỉ)</span>
                <a href="{{ route('admin.lophocphan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('admin.lophocphan.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Lớp Học Phần <span class="text-danger">*</span></label>
                        <input type="text" name="TenLopHP" class="form-control" value="{{ old('TenLopHP') }}" placeholder="Ví dụ: 21DTH01_WEB_N01" required>
                        <div class="form-text">Tên phân biệt các lớp học phần cùng mở trong học kỳ (Ví dụ: Nhóm 1, Nhóm 2...).</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Môn Học <span class="text-danger">*</span></label>
                            <select name="MaMon" class="form-select" required>
                                <option value="">-- Chọn Môn Học --</option>
                                @foreach($monHocs as $m)
                                    <option value="{{ $m->MaMon }}" {{ old('MaMon') == $m->MaMon ? 'selected' : '' }}>
                                        {{ $m->TenMon }} ({{ $m->SoTinChi }} tín chỉ)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Học Kỳ <span class="text-danger">*</span></label>
                            <select name="MaHocKy" class="form-select" required>
                                <option value="">-- Chọn Học Kỳ --</option>
                                @foreach($hocKies as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giảng Viên Hướng Dẫn / Giảng Dạy <span class="text-danger">*</span></label>
                            <select name="MaGV" class="form-select" required>
                                <option value="">-- Chọn Giảng Viên --</option>
                                @foreach($giangViens as $gv)
                                    <option value="{{ $gv->MaGV }}" {{ old('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                        {{ $gv->HoTen }} ({{ $gv->HocVi ?? 'GV' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Sĩ Số Tối Đa <span class="text-danger">*</span></label>
                            <input type="number" name="SiSoToiDa" class="form-control" value="{{ old('SiSoToiDa', 40) }}" min="1" max="200" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Trạng Thái <span class="text-danger">*</span></label>
                            <select name="TrangThai" class="form-select" required>
                                <option value="Đang mở" {{ old('TrangThai') == 'Đang mở' ? 'selected' : '' }}>Đang mở</option>
                                <option value="Đã đóng" {{ old('TrangThai') == 'Đã đóng' ? 'selected' : '' }}>Đã đóng</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.lophocphan.index') }}" class="btn btn-light px-4 rounded-pill">Hủy</a>
                        <button type="submit" class="btn btn-success px-4 rounded-pill"><i class="fa-solid fa-save me-1"></i>Lưu Lớp Học Phần</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
