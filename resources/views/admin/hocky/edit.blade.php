@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Học Kỳ')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit shadow-sm">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-pen-to-square text-white me-2"></i> Chỉnh Sửa Thông Tin Học Kỳ: {{ $hocky->TenHocKy }} ({{ $hocky->NamHoc }})
                </div>
            </div>
            <div class="card-body p-4 p-md-5">

                <form method="POST" action="{{ route('hocky.update', $hocky->MaHocKy) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label-huit">Mã Học Kỳ (Hệ Thống)</label>
                            <input type="text" class="form-control form-control-huit bg-light font-monospace fw-bold text-primary" value="{{ $hocky->MaHocKy }}" readonly>
                            <div class="form-text text-muted small mt-1">Mã định danh không thay đổi</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Tên Học Kỳ <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select id="quickSelectTenHk" class="form-select form-select-huit" style="max-width: 140px;" onchange="if(this.value !== 'custom') document.getElementById('inputTenHocKy').value = this.value;">
                                    <option value="" disabled>-- Chọn nhanh --</option>
                                    <option value="Học kỳ 1" {{ old('TenHocKy', $hocky->TenHocKy) === 'Học kỳ 1' ? 'selected' : '' }}>Học kỳ 1</option>
                                    <option value="Học kỳ 2" {{ old('TenHocKy', $hocky->TenHocKy) === 'Học kỳ 2' ? 'selected' : '' }}>Học kỳ 2</option>
                                    <option value="Học kỳ 3" {{ old('TenHocKy', $hocky->TenHocKy) === 'Học kỳ 3' ? 'selected' : '' }}>Học kỳ 3</option>
                                    <option value="Học kỳ hè" {{ old('TenHocKy', $hocky->TenHocKy) === 'Học kỳ hè' ? 'selected' : '' }}>Học kỳ hè</option>
                                    <option value="custom">Khác...</option>
                                </select>
                                <input type="text" name="TenHocKy" id="inputTenHocKy" class="form-control form-control-huit" value="{{ old('TenHocKy', $hocky->TenHocKy) }}" placeholder="Học kỳ 1" required>
                            </div>
                   
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-huit">Năm Học <span class="text-danger">*</span></label>
                            <input type="text" name="NamHoc" class="form-control form-control-huit" value="{{ old('NamHoc', $hocky->NamHoc) }}" placeholder="2026-2027" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label-huit">Thời Gian Bắt Đầu <span class="text-danger">*</span></label>
                            <input type="date" name="NgayBatDau" class="form-control form-control-huit" value="{{ old('NgayBatDau', $hocky->NgayBatDau ? \Carbon\Carbon::parse($hocky->NgayBatDau)->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-huit">Thời Gian Kết Thúc <span class="text-danger">*</span></label>
                            <input type="date" name="NgayKetThuc" class="form-control form-control-huit" value="{{ old('NgayKetThuc', $hocky->NgayKetThuc ? \Carbon\Carbon::parse($hocky->NgayKetThuc)->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-4">
                            @php
                                $curStatus = trim((string)old('TrangThai', $hocky->TrangThai));
                                $isEnded = in_array(mb_strtolower($curStatus), ['0', 'da ket thuc', 'đã kết thúc', 'completed', 'ended', 'closed']);
                                $isUpcoming = in_array(mb_strtolower($curStatus), ['upcoming', 'chua bat dau', 'chưa bắt đầu']);
                            @endphp
                            <label class="form-label-huit">Trạng Thái Học Kỳ <span class="text-danger">*</span></label>
                            <select name="TrangThai" class="form-select form-select-huit">
                                <option value="Đang diễn ra" {{ (!$isEnded && !$isUpcoming) ? 'selected' : '' }}>
                                    🟢 Đang diễn ra (Hoạt động)
                                </option>
                                <option value="Chưa bắt đầu" {{ $isUpcoming ? 'selected' : '' }}>
                                    🔵 Chưa bắt đầu (Sắp diễn ra)
                                </option>
                                <option value="Đã kết thúc" {{ $isEnded ? 'selected' : '' }}>
                                    ⚪ Đã kết thúc (Lưu trữ)
                                </option>
                            </select>
                            <div class="form-text text-muted small mt-1">Cập nhật tình trạng hoạt động đợt khóa luận</div>
                        </div>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Cập Nhật Học Kỳ
                        </button>
                        <a href="{{ route('hocky.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection