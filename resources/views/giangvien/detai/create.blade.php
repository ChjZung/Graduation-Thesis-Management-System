@extends('layouts.giangvien')
@section('page_title', 'Thêm Đề Tài Mới')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

{{-- Template download --}}
<div class="alert alert-info d-flex align-items-center gap-3 mb-3 rounded-3">
    <i class="fa-solid fa-file-excel fa-lg text-success"></i>
    <div>
        <strong>Import hàng loạt?</strong>
        Tải file mẫu Excel rồi dùng nút <em>Import Excel/CSV</em> ở trang danh sách.
        <a href="{{ route('admin.import.template', 'detais') }}" class="btn btn-sm btn-outline-success ms-2 rounded-pill px-3">
            <i class="fa-solid fa-download me-1"></i>Tải file mẫu .xlsx
        </a>
    </div>
</div>

<div class="card card-premium">
    <div class="card-header-premium">
        <i class="fa-solid fa-plus-circle me-2 text-primary"></i>Tạo Đề Tài Mới
    </div>
    <div class="card-body p-4">
        <form action="{{ route('giangvien.detai.store') }}" method="POST">
            @csrf

            {{-- Tên đề tài --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên Đề Tài <span class="text-danger">*</span></label>
                <input type="text" name="TenDeTai" class="form-control @error('TenDeTai') is-invalid @enderror"
                       value="{{ old('TenDeTai') }}" required placeholder="Nhập tên đề tài...">
                @error('TenDeTai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Môn học & Học kỳ --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Môn Học <span class="text-danger">*</span></label>
                    <select name="MaMon" class="form-select @error('MaMon') is-invalid @enderror" required>
                        <option value="">— Chọn môn học —</option>
                        @foreach($monhocs as $m)
                            <option value="{{ $m->MaMon }}" {{ old('MaMon') == $m->MaMon ? 'selected' : '' }}>
                                {{ $m->TenMon }}
                            </option>
                        @endforeach
                    </select>
                    @error('MaMon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Học Kỳ <span class="text-danger">*</span></label>
                    <select name="MaHocKy" class="form-select @error('MaHocKy') is-invalid @enderror" required>
                        <option value="">— Chọn học kỳ —</option>
                        @foreach($hockys as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                    @error('MaHocKy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Deadlines --}}
            <div class="p-3 bg-light rounded-3 border mb-3">
                <p class="fw-semibold mb-2"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Cài đặt Thời hạn</p>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Đăng Ký <span class="text-danger">*</span></label>
                        <input type="date" name="HanDangKy" class="form-control @error('HanDangKy') is-invalid @enderror"
                               value="{{ old('HanDangKy') }}" required>
                        @error('HanDangKy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Nộp Báo Cáo <span class="text-danger">*</span></label>
                        <input type="date" name="HanBaoCao" class="form-control @error('HanBaoCao') is-invalid @enderror"
                               value="{{ old('HanBaoCao') }}" required>
                        @error('HanBaoCao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Nộp Sản Phẩm <span class="text-danger">*</span></label>
                        <input type="date" name="HanNopSanPham" class="form-control @error('HanNopSanPham') is-invalid @enderror"
                               value="{{ old('HanNopSanPham') }}" required>
                        @error('HanNopSanPham')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Mô tả & Yêu cầu --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Mô Tả</label>
                <textarea name="MoTa" rows="3" class="form-control" placeholder="Mô tả nội dung đề tài...">{{ old('MoTa') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Yêu Cầu Cụ Thể</label>
                <textarea name="YeuCau" rows="3" class="form-control" placeholder="Các yêu cầu kỹ thuật, công nghệ...">{{ old('YeuCau') }}</textarea>
            </div>

            <div class="text-end mt-4 d-flex gap-2 justify-content-end">
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light rounded-pill px-4">Huỷ</a>
                <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                    <i class="fa-solid fa-check me-1"></i>Tạo Đề Tài
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection