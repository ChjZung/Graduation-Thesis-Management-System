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

            {{-- Lớp học phần, Môn học, Học kỳ --}}
            <div class="mb-3 p-3 bg-light rounded border border-primary-subtle">
                <label class="form-label fw-bold text-primary"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp Học Phần (Lớp Tín Chỉ) <span class="text-danger">*</span></label>
                <select name="MaLopHP" id="select_MaLopHP" class="form-select border-primary @error('MaLopHP') is-invalid @enderror" required onchange="onLopHPSelectChange(this)">
                    <option value="">— Chọn Lớp Học Phần —</option>
                    @foreach($lopHocPhans as $lhp)
                        <option value="{{ $lhp->MaLopHP }}" data-mamon="{{ $lhp->MaMon }}" data-mahocky="{{ $lhp->MaHocKy }}" {{ old('MaLopHP') == $lhp->MaLopHP ? 'selected' : '' }}>
                            {{ $lhp->TenLopHP }} — {{ $lhp->monHoc->TenMon ?? '' }} ({{ $lhp->hocKy->TenHocKy ?? '' }} - GV: {{ $lhp->giangVien->HoTen ?? 'Chưa gán' }})
                        </option>
                    @endforeach
                </select>
                @error('MaLopHP')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text text-muted small">Chọn Lớp Học Phần để tự động xác định đúng Môn học và Học kỳ tương ứng.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Môn Học</label>
                    <select name="MaMon" id="select_MaMon" class="form-select @error('MaMon') is-invalid @enderror">
                        <option value="">— Tự động điền theo Lớp HP —</option>
                        @foreach($monhocs as $m)
                            <option value="{{ $m->MaMon }}" {{ old('MaMon') == $m->MaMon ? 'selected' : '' }}>
                                {{ $m->TenMon }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Học Kỳ</label>
                    <select name="MaHocKy" id="select_MaHocKy" class="form-select @error('MaHocKy') is-invalid @enderror">
                        <option value="">— Tự động điền theo Lớp HP —</option>
                        @foreach($hockys as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <script>
            function onLopHPSelectChange(selectEl) {
                const opt = selectEl.options[selectEl.selectedIndex];
                const maMon = opt.getAttribute('data-mamon');
                const maHocKy = opt.getAttribute('data-mahocky');
                if (maMon) {
                    const mSel = document.getElementById('select_MaMon');
                    if (mSel) mSel.value = maMon;
                }
                if (maHocKy) {
                    const hkSel = document.getElementById('select_MaHocKy');
                    if (hkSel) hkSel.value = maHocKy;
                }
            }
            </script>

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