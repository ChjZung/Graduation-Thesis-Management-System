@extends('layouts.admin')

@section('page_title', $hocphan->isDungChung() ? 'Chỉnh Sửa Học Phần Dùng Chung' : 'Chỉnh Sửa Học Phần Theo Bộ Môn')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">
                @if($hocphan->isDungChung())
                    Chỉnh sửa học phần dùng chung
                @else
                    Chỉnh sửa học phần theo bộ môn
                @endif
            </h4>
            <p class="text-muted small mb-0">Học phần: <strong>{{ $hocphan->TenHocPhan }}</strong> (<code>{{ $hocphan->MaHocPhan }}</code>)</p>
        </div>
        <a href="{{ route('hocphan.index', ['tab' => $hocphan->isDungChung() ? 'dung_chung' : 'bomon']) }}" class="btn btn-sm btn-outline-secondary px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>


    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-file-pen me-2 text-primary"></i> 
                {{ $hocphan->isDungChung() ? 'Thông tin học phần dùng chung' : 'Thông tin học phần theo bộ môn' }}
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('hocphan.update', $hocphan->MaHocPhan) }}" method="POST">
                @csrf
                @method('PUT')

                @if($hocphan->isDungChung())
                    <input type="hidden" name="is_dung_chung" value="1">
                    <input type="hidden" name="MaBoMon" value="">
                    <div class="alert alert-info py-2 px-3 small border-0 mb-4" style="background-color: #f0f9ff; color: #0369a1;">
                        <i class="fa-solid fa-circle-info me-1"></i> Học phần này là <strong>Học phần dùng chung</strong> trong Khoa và không thuộc riêng một bộ môn nào.
                    </div>
                @else
                    <input type="hidden" name="is_dung_chung" value="0">
                @endif

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small text-secondary">Mã học phần</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $hocphan->MaHocPhan }}" readonly>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold small text-dark">Tên học phần *</label>
                        <input type="text" name="TenHocPhan" class="form-control form-control-sm" value="{{ old('TenHocPhan', $hocphan->TenHocPhan) }}" required>
                    </div>

                    @if(!$hocphan->isDungChung())
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Bộ môn *</label>
                            <select name="MaBoMon" class="form-select form-select-sm" required>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon', $hocphan->MaBoMon) == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="{{ $hocphan->isDungChung() ? 'col-md-3' : 'col-md-6' }}">
                        <label class="form-label fw-semibold small text-dark">Số tín chỉ *</label>
                        <input type="number" name="SoTinChi" class="form-control form-control-sm" value="{{ old('SoTinChi', $hocphan->SoTinChi) }}" min="1" max="30" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Số tiết lý thuyết</label>
                        <input type="number" name="SoTietLT" class="form-control form-control-sm" value="{{ old('SoTietLT', $hocphan->SoTietLT ?? 0) }}" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Số tiết thực hành</label>
                        <input type="number" name="SoTietTH" class="form-control form-control-sm" value="{{ old('SoTietTH', $hocphan->SoTietTH ?? 0) }}" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Số tiết khác</label>
                        <input type="number" name="SoTietKhac" class="form-control form-control-sm" value="{{ old('SoTietKhac', $hocphan->SoTietKhac ?? 0) }}" min="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark">Trạng thái</label>
                        <select name="TrangThai" class="form-select form-select-sm">
                            <option value="Đang sử dụng" {{ old('TrangThai', $hocphan->TrangThai) === 'Đang sử dụng' || old('TrangThai', $hocphan->TrangThai) === 'Đang áp dụng' || $hocphan->TrangThai == '1' || $hocphan->TrangThai === true ? 'selected' : '' }}>Đang sử dụng</option>
                            <option value="Ngừng sử dụng" {{ old('TrangThai', $hocphan->TrangThai) === 'Ngừng sử dụng' || old('TrangThai', $hocphan->TrangThai) === 'Tạm ngưng' ? 'selected' : '' }}>Ngừng sử dụng</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Mô tả</label>
                        <textarea name="MoTa" rows="3" class="form-control form-control-sm">{{ old('MoTa', $hocphan->MoTa) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-4" style="background-color: #00305a; border-color: #00305a;">
                        Lưu thay đổi
                    </button>
                    <a href="{{ route('hocphan.index', ['tab' => $hocphan->isDungChung() ? 'dung_chung' : 'bomon']) }}" class="btn btn-sm btn-outline-secondary px-3">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
