@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Học Kỳ')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Top Actions -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-calendar-days text-primary me-2"></i>
                <span>Chỉnh Sửa Học Kỳ</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-6 fw-bold">{{ $hocky->MaHocKy }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('hocky.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách
            </a>
            <a href="{{ route('hocky.show', $hocky->MaHocKy) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-eye me-2"></i> Xem chi tiết
            </a>
        </div>
    </div>

    @php
        $tenHkClean = $hocky->TenHocKy;
        // Tránh lặp lại niên khóa nếu trong TenHocKy đã có NamHoc
        $displayTitle = str_contains($tenHkClean, $hocky->NamHoc)
            ? $tenHkClean
            : $tenHkClean . ' (' . $hocky->NamHoc . ')';

        $curStatus = trim((string)old('TrangThai', $hocky->TrangThai));
        $isEnded = in_array(mb_strtolower($curStatus), ['0', 'da ket thuc', 'đã kết thúc', 'completed', 'ended', 'closed']);
        $isUpcoming = in_array(mb_strtolower($curStatus), ['upcoming', 'chua bat dau', 'chưa bắt đầu']);
    @endphp

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card card-create-huit shadow-sm border rounded-4 overflow-hidden">
                <!-- Header Khung nổi bật thương hiệu HUIT -->
                <div class="card-header-huit d-flex flex-wrap align-items-center justify-content-between p-3 p-md-4" style="background: linear-gradient(135deg, #004b9c 0%, #002b5c 100%) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(4px);">
                            <i class="fa-solid fa-pen-to-square fs-5"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-white lh-sm">Chỉnh Sửa Thông Tin Học Kỳ: {{ $displayTitle }}</div>
                            <div class="small text-white-50 mt-1 fw-normal">Cập nhật niên khóa, tên đợt khóa luận và mốc thời gian áp dụng hệ thống</div>
                        </div>
                    </div>
                    <div>
                        @if(!$isEnded && !$isUpcoming)
                            <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm fs-7">
                                <i class="fa-solid fa-circle-check me-2"></i> Đang diễn ra (Hoạt động)
                            </span>
                        @elseif($isUpcoming)
                            <span class="badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm fs-7 fw-semibold">
                                <i class="fa-solid fa-clock me-2"></i> Chưa bắt đầu (Sắp diễn ra)
                            </span>
                        @else
                            <span class="badge bg-secondary text-white px-3 py-2 rounded-pill shadow-sm fs-7">
                                <i class="fa-solid fa-box-archive me-2"></i> Đã kết thúc (Lưu trữ)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Form Nội Dung -->
                <div class="card-body p-4 p-md-5 bg-white">
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger p-3 rounded-3 mb-4 shadow-sm border-0" style="border-left: 5px solid #dc3545 !important;">
                            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Vui lòng kiểm tra lại thông tin:</div>
                            <ul class="mb-0 small ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('hocky.update', $hocky->MaHocKy) }}" id="formEditHocKy">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4 mb-4">
                            <!-- Mã Học Kỳ -->
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-barcode text-primary me-2"></i> Mã Học Kỳ (Hệ Thống)
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                    <input type="text" class="form-control form-control-huit border-start-0 bg-light font-monospace fw-bold text-primary" value="{{ $hocky->MaHocKy }}" readonly>
                                </div>
                                <div class="form-text text-muted small mt-1">
                                    <i class="fa-solid fa-circle-info me-2 text-info"></i>Mã định danh duy nhất (không thể thay đổi)
                                </div>
                            </div>

                            <!-- Tên Học Kỳ -->
                            <div class="col-md-5">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-graduation-cap text-primary me-2"></i> Tên Học Kỳ <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="text" name="TenHocKy" id="inputTenHocKy" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('TenHocKy', $hocky->TenHocKy) }}" placeholder="VD: Học kỳ 1 (2025-2026)" required>
                                <div class="d-flex align-items-center gap-1 mt-2 flex-wrap">
                                    <span class="small text-muted me-1 fw-medium" style="font-size: 0.78rem;">Chọn nhanh:</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setTenHk('Học kỳ 1')">Học kỳ 1</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setTenHk('Học kỳ 2')">Học kỳ 2</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setTenHk('Học kỳ 3')">Học kỳ 3</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setTenHk('Học kỳ hè')">Học kỳ hè</button>
                                </div>
                            </div>

                            <!-- Năm Học -->
                            <div class="col-md-3">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-calendar text-primary me-2"></i> Năm Học <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="text" name="NamHoc" id="inputNamHoc" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('NamHoc', $hocky->NamHoc) }}" placeholder="2025-2026" required>
                                <div class="form-text text-muted small mt-1">
                                    <i class="fa-regular fa-clock me-2 text-muted"></i>Định dạng niên khóa: <code>YYYY-YYYY</code>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Thời Gian Bắt Đầu -->
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-calendar-day text-primary me-2"></i> Thời Gian Bắt Đầu <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="date" name="NgayBatDau" id="inputNgayBatDau" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('NgayBatDau', $hocky->NgayBatDau ? \Carbon\Carbon::parse($hocky->NgayBatDau)->format('Y-m-d') : '') }}" required>
                            </div>

                            <!-- Thời Gian Kết Thúc -->
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-calendar-check text-primary me-2"></i> Thời Gian Kết Thúc <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="date" name="NgayKetThuc" id="inputNgayKetThuc" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('NgayKetThuc', $hocky->NgayKetThuc ? \Carbon\Carbon::parse($hocky->NgayKetThuc)->format('Y-m-d') : '') }}" required>
                            </div>

                            <!-- Trạng Thái Học Kỳ -->
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-toggle-on text-primary me-2"></i> Trạng Thái Học Kỳ <span class="text-danger fw-bold">*</span>
                                </label>
                                <select name="TrangThai" class="form-select form-select-huit fw-semibold text-dark">
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
                                <div class="form-text text-muted small mt-1">
                                    <i class="fa-solid fa-circle-question me-2 text-muted"></i>Cập nhật tình trạng hoạt động đợt khóa luận
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Hành Động -->
                        <div class="card-footer-huit d-flex flex-wrap align-items-center justify-content-between p-3 p-md-4 mt-5 rounded-3 border-top bg-light">
                            <a href="{{ route('hocky.index') }}" class="btn btn-cancel-huit d-inline-flex align-items-center">
                                <i class="fa-solid fa-arrow-left me-2"></i> Hủy & Quay Lại
                            </a>
                            <div class="d-flex align-items-center gap-2">
                                <button type="submit" class="btn btn-save-huit shadow-sm d-inline-flex align-items-center">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Cập Nhật Học Kỳ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setTenHk(val) {
    var namHoc = document.getElementById('inputNamHoc').value.trim();
    var input = document.getElementById('inputTenHocKy');
    if (namHoc) {
        input.value = val + ' (' + namHoc + ')';
    } else {
        input.value = val;
    }
}
</script>
@endpush