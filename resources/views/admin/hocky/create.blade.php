@extends('layouts.admin')

@section('page_title', 'Thêm Mới Học Kỳ Đào Tạo')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Top Actions -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hocky.index') }}" class="text-decoration-none">Khóa luận tốt nghiệp</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thêm Mới Học Kỳ</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-primary"></i>
                <span>Thêm Mới Học Kỳ Đào Tạo</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('hocky.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card card-create-huit shadow-sm border rounded-4 overflow-hidden">
                <div class="card-header-huit d-flex flex-wrap align-items-center justify-content-between p-3 p-md-4" style="background: linear-gradient(135deg, #004b9c 0%, #002b5c 100%) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(4px);">
                            <i class="fa-solid fa-plus fs-5"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-white lh-sm">Khởi Tạo Học Kỳ & Niên Khóa Mới</div>
                            <div class="small text-white-50 mt-1 fw-normal">Thiết lập học kỳ đào tạo khóa luận và thời gian hoạt động</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger p-3 rounded-3 mb-4 shadow-sm border-0" style="border-left: 5px solid #dc3545 !important;">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Vui lòng kiểm tra lại thông tin:</div>
                        <ul class="mb-0 small ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('hocky.store') }}">
                        @csrf
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-barcode text-primary me-1"></i> Mã Học Kỳ <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="text" name="MaHocKy" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('MaHocKy') }}" placeholder="VD: HK2_2627" required>
                                <div class="form-text text-muted small mt-1">Mã định danh hệ thống (tối đa 10 ký tự)</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Tên Học Kỳ & Niên Khóa <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="text" name="TenHocKy" id="createInputTenHk" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('TenHocKy') }}" placeholder="VD: Học kỳ 2 — Năm học 2026–2027" required>
                                <div class="d-flex align-items-center gap-1 mt-2 flex-wrap">
                                    <span class="small text-muted me-1 fw-medium" style="font-size: 0.78rem;">Gợi ý:</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setCreateTenHk('Học kỳ 1')">Học kỳ 1</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setCreateTenHk('Học kỳ 2')">Học kỳ 2</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.75rem;" onclick="setCreateTenHk('Học kỳ hè')">Học kỳ hè</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-calendar text-primary me-1"></i> Năm Học <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="text" name="NamHoc" id="createInputNamHoc" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('NamHoc', '2026-2027') }}" placeholder="2026-2027" required>
                                <div class="form-text text-muted small mt-1">Định dạng: <code>YYYY-YYYY</code></div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-calendar-day text-primary me-1"></i> Thời Gian Bắt Đầu <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="date" name="NgayBatDau" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('NgayBatDau', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-calendar-check text-primary me-1"></i> Thời Gian Kết Thúc <span class="text-danger fw-bold">*</span>
                                </label>
                                <input type="date" name="NgayKetThuc" class="form-control form-control-huit fw-semibold text-dark" value="{{ old('NgayKetThuc', date('Y-m-d', strtotime('+5 months'))) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-huit">
                                    <i class="fa-solid fa-toggle-on text-primary me-1"></i> Trạng Thái Học Kỳ Hệ Thống
                                </label>
                                <select name="TrangThai" class="form-select form-select-huit fw-semibold text-dark">
                                    <option value="1" selected>🟢 Đang diễn ra (Active)</option>
                                    <option value="UPCOMING">🔵 Chuẩn bị kích hoạt (Upcoming)</option>
                                    <option value="0">⚪ Đã kết thúc (Completed)</option>
                                </select>
                                <div class="form-text text-muted small mt-1">Cập nhật tình trạng hoạt động đợt khóa luận</div>
                            </div>
                        </div>

                        <div class="card-footer-huit d-flex flex-wrap align-items-center justify-content-between p-3 p-md-4 mt-5 rounded-3 border-top bg-light">
                            <a href="{{ route('hocky.index') }}" class="btn btn-cancel-huit">
                                <i class="fa-solid fa-arrow-left me-1"></i> Hủy & Quay Lại
                            </a>
                            <button type="submit" class="btn btn-save-huit shadow-sm">
                                <i class="fa-solid fa-plus me-1"></i> Lưu & Thêm Học Kỳ
                            </button>
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
function setCreateTenHk(val) {
    var namHoc = document.getElementById('createInputNamHoc').value.trim();
    var input = document.getElementById('createInputTenHk');
    if (namHoc) {
        input.value = val + ' — Năm học ' + namHoc;
    } else {
        input.value = val;
    }
}
</script>
@endpush