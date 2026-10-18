@extends('layouts.truongkhoa')

@section('page_title', 'Thành Lập Hội Đồng Mới')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-11 col-xl-10">
            <div class="card card-create-huit border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header-huit p-4 text-white" style="background: linear-gradient(135deg, #00305a 0%, #0072ce 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="card-title-huit fw-bold fs-5">
                            <i class="fa-solid fa-landmark text-white me-2"></i> Trưởng Khoa Ban Hành Quyết Định Thành Lập Hội Đồng Khóa Luận
                        </div>
                        <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold">{{ $khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger p-3 rounded-3 mb-4">
                        <ul class="mb-0 small">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <form action="{{ route('truongkhoa.hoidong.store') }}" method="POST">
                        @csrf

                        <!-- Section 1: Thông tin hội đồng -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i class="fa-solid fa-circle-info text-primary me-2"></i>1. Thông Tin Chung Về Hội Đồng Bảo Vệ
                            </h6>
                            <div class="row g-4 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-dark small">Tên Hội Đồng <span class="text-danger">*</span></label>
                                    <input type="text" name="TenHoiDong" class="form-control" value="{{ old('TenHoiDong') }}"
                                        placeholder="VD: Hội đồng chuyên ngành Công nghệ phần mềm 01" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark small">Địa Điểm / Phòng Bảo Vệ</label>
                                    <input type="text" name="DiaDiem" class="form-control" value="{{ old('DiaDiem') }}"
                                        placeholder="VD: Phòng B.304 (Tòa nhà B - 140 Lê Trọng Tấn)">
                                </div>
                            </div>

                            <div class="row g-4 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Thời Gian Bắt Đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ThoiGianBatDau" class="form-control" value="{{ old('ThoiGianBatDau') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Thời Gian Kết Thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ThoiGianKetThuc" class="form-control" value="{{ old('ThoiGianKetThuc') }}" required>
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-bold text-dark small">Ghi Chú &amp; Hướng Dẫn Hội Đồng</label>
                                <textarea name="GhiChu" class="form-control" rows="2" style="min-height: 80px;" placeholder="Ghi chú thêm về yêu cầu trang thiết bị, quy cách chấm điểm hoặc lịch trình chi tiết...">{{ old('GhiChu') }}</textarea>
                            </div>
                        </div>

                        <!-- Section 2: Thành viên hội đồng -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-users text-primary me-2"></i>2. Phân Công Thành Viên Hội Đồng (Tối thiểu 3 Giảng Viên) <span class="text-danger">*</span>
                                </h6>
                                <button type="button" id="themThanhVien" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center">
                                    <i class="fa-solid fa-plus me-2"></i>Thêm Thành Viên
                                </button>
                            </div>

                            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 border-0 bg-info-subtle text-info-emphasis">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                Cơ cấu Hội đồng tiêu chuẩn: <strong>1 Chủ tịch</strong>, <strong>1 Thư ký</strong> và các <strong>Ủy viên / Giảng viên phản biện</strong>.
                            </div>

                            <div id="danhSachThanhVien">
                                @for($i = 0; $i < 3; $i++)
                                <div class="row g-2 mb-2 dong-thanh-vien align-items-center">
                                    <div class="col-md-7">
                                        <select name="thanh_viens[{{ $i }}][MaGV]" class="form-select" required>
                                            <option value="">— Chọn Giảng viên thuộc Khoa —</option>
                                            @foreach($giangViens as $gv)
                                            <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->boMon->TenBoMon ?? 'Bộ môn' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="thanh_viens[{{ $i }}][VaiTro]" class="form-select" required>
                                            <option value="Chủ tịch" {{ $i === 0 ? 'selected' : '' }}>Chủ tịch</option>
                                            <option value="Thư ký" {{ $i === 1 ? 'selected' : '' }}>Thư ký</option>
                                            <option value="Thành viên" {{ $i >= 2 ? 'selected' : '' }}>Thành viên</option>
                                            <option value="Phản biện">Phản biện</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        @if($i >= 3)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-xoa-dong">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 border-top pt-4 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs d-inline-flex align-items-center">
                                <i class="fa-solid fa-check me-2"></i> Ký Duyệt &amp; Thành Lập Hội Đồng
                            </button>
                            <a href="{{ route('truongkhoa.hoidong.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Hủy Bỏ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let soTv = 3;
document.getElementById('themThanhVien')?.addEventListener('click', function() {
    const container = document.getElementById('danhSachThanhVien');
    const html = `
    <div class="row g-2 mb-2 dong-thanh-vien align-items-center">
        <div class="col-md-7">
            <select name="thanh_viens[${soTv}][MaGV]" class="form-select" required>
                <option value="">— Chọn Giảng viên thuộc Khoa —</option>
                @foreach($giangViens as $gv)
                <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->boMon->TenBoMon ?? 'Bộ môn' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select name="thanh_viens[${soTv}][VaiTro]" class="form-select" required>
                <option value="Thành viên" selected>Thành viên</option>
                <option value="Phản biện">Phản biện</option>
                <option value="Chủ tịch">Chủ tịch</option>
                <option value="Thư ký">Thư ký</option>
            </select>
        </div>
        <div class="col-md-1 text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-xoa-dong">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    soTv++;
    bindXoaDong();
});

function bindXoaDong() {
    document.querySelectorAll('.btn-xoa-dong').forEach(btn => {
        btn.onclick = function() { this.closest('.dong-thanh-vien').remove(); };
    });
}
bindXoaDong();
</script>
@endpush
@endsection
