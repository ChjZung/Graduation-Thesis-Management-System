@extends('layouts.giangvien')
@section('page_title', 'Sửa Đề Tài')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card card-premium">
    <div class="card-header-premium">
        <i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Cập Nhật Đề Tài
        <small class="text-muted ms-2">#{{ $detai->MaDeTai }}</small>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('giangvien.detai.update', $detai->MaDeTai) }}" method="POST">
            @csrf @method('PUT')

            {{-- Tên đề tài --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên Đề Tài <span class="text-danger">*</span></label>
                <input type="text" name="TenDeTai" class="form-control @error('TenDeTai') is-invalid @enderror"
                       value="{{ old('TenDeTai', $detai->TenDeTai) }}" required>
                @error('TenDeTai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Môn học, Học kỳ, Lớp --}}
            {{-- Lớp học phần --}}
            <div class="mb-3 p-3 bg-light rounded border border-primary-subtle">
                <label class="form-label fw-bold text-primary"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp Học Phần (Lớp Tín Chỉ) <span class="text-danger">*</span></label>
                <select name="MaLopHP" class="form-select border-primary @error('MaLopHP') is-invalid @enderror" required>
                    <option value="">— Chọn Lớp Học Phần —</option>
                    @foreach($lopHocPhans as $lhp)
                        <option value="{{ $lhp->MaLopHP }}" {{ (old('MaLopHP', $detai->MaLopHP) == $lhp->MaLopHP) ? 'selected' : '' }}>
                            {{ $lhp->TenLopHP }} — {{ $lhp->monHoc->TenMon ?? '' }} ({{ $lhp->hocKy->TenHocKy ?? '' }} - GV: {{ $lhp->giangVien->HoTen ?? 'Chưa gán' }})
                        </option>
                    @endforeach
                </select>
                @error('MaLopHP')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Trạng thái --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Trạng Thái</label>
                <select name="TrangThai" class="form-select">
                    <option value="Đang mở đăng ký" {{ $detai->TrangThai == 'Đang mở đăng ký' ? 'selected' : '' }}>Đang mở đăng ký</option>
                    <option value="Đã đăng ký"      {{ $detai->TrangThai == 'Đã đăng ký'      ? 'selected' : '' }}>Đã đăng ký</option>
                    <option value="Đã đóng"          {{ $detai->TrangThai == 'Đã đóng'          ? 'selected' : '' }}>Đã đóng</option>
                </select>
            </div>

            {{-- Deadlines --}}
            <div class="p-3 bg-light rounded-3 border mb-3">
                <p class="fw-semibold mb-2"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Cài đặt Thời hạn</p>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Đăng Ký <span class="text-danger">*</span></label>
                        <input type="date" name="HanDangKy" class="form-control @error('HanDangKy') is-invalid @enderror"
                               value="{{ old('HanDangKy', $detai->HanDangKy ? \Carbon\Carbon::parse($detai->HanDangKy)->format('Y-m-d') : '') }}" required>
                        @error('HanDangKy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Nộp Báo Cáo <span class="text-danger">*</span></label>
                        <input type="date" name="HanBaoCao" class="form-control @error('HanBaoCao') is-invalid @enderror"
                               value="{{ old('HanBaoCao', $detai->HanBaoCao ? \Carbon\Carbon::parse($detai->HanBaoCao)->format('Y-m-d') : '') }}" required>
                        @error('HanBaoCao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Nộp Sản Phẩm <span class="text-danger">*</span></label>
                        <input type="date" name="HanNopSanPham" class="form-control @error('HanNopSanPham') is-invalid @enderror"
                               value="{{ old('HanNopSanPham', $detai->HanNopSanPham ? \Carbon\Carbon::parse($detai->HanNopSanPham)->format('Y-m-d') : '') }}" required>
                        @error('HanNopSanPham')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Mô tả & Yêu cầu --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Mô Tả</label>
                <textarea name="MoTa" rows="3" class="form-control">{{ old('MoTa', $detai->MoTa) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Yêu Cầu Cụ Thể</label>
                <textarea name="YeuCau" rows="3" class="form-control">{{ old('YeuCau', $detai->YeuCau) }}</textarea>
            </div>

            <div class="text-end mt-4 d-flex gap-2 justify-content-end">
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light rounded-pill px-4">Huỷ</a>
                <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Cập Nhật
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection