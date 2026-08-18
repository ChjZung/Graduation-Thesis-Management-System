@extends('layouts.admin')
@section('page_title', 'Thành Lập Hội Đồng Mới')
@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.hoidong.store') }}" method="POST">
@csrf
<div class="row g-4">
    <!-- Thông tin Hội đồng -->
    <div class="col-md-6">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <i class="fa-solid fa-landmark me-2 text-primary"></i>Thông Tin Hội Đồng
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên Hội Đồng <span class="text-danger">*</span></label>
                    <input type="text" name="TenHoiDong" class="form-control" value="{{ old('TenHoiDong') }}"
                        placeholder="VD: Hội đồng bảo vệ khóa luận HK1 2025-2026" required>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">Thời Gian Bắt Đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="ThoiGianBatDau" class="form-control" value="{{ old('ThoiGianBatDau') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Thời Gian Kết Thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="ThoiGianKetThuc" class="form-control" value="{{ old('ThoiGianKetThuc') }}" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">Địa Điểm</label>
                    <input type="text" name="DiaDiem" class="form-control" value="{{ old('DiaDiem') }}"
                        placeholder="VD: Phòng A.101, Tòa nhà A">
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">Ghi Chú</label>
                    <textarea name="GhiChu" class="form-control" rows="2" placeholder="Ghi chú thêm...">{{ old('GhiChu') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm thành viên -->
    <div class="col-md-6">
        <div class="card card-premium h-100">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-users me-2 text-primary"></i>Thành Viên Hội Đồng <span class="text-danger">*</span></span>
                <button type="button" id="themThanhVien" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="fa-solid fa-plus me-1"></i>Thêm
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 mb-3" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Cần tối thiểu <strong>3 Giảng viên</strong>. Nên có: 1 Chủ tịch, 1 Thư ký, 1+ Thành viên.
                </div>
                <div id="danhSachThanhVien">
                    @for($i = 0; $i < 3; $i++)
                    <div class="row g-2 mb-2 dong-thanh-vien">
                        <div class="col-7">
                            <select name="thanh_viens[{{ $i }}][MaGV]" class="form-select form-select-sm" required>
                                <option value="">— Chọn Giảng viên —</option>
                                @foreach($giangViens as $gv)
                                <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->HocVi ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="thanh_viens[{{ $i }}][VaiTro]" class="form-select form-select-sm" required>
                                @if($i === 0) <option value="Chủ tịch" selected>Chủ tịch</option> @endif
                                @if($i === 1) <option value="Thư ký" selected>Thư ký</option> @endif
                                @if($i >= 2) <option value="Thành viên" selected>Thành viên</option> @endif
                                @if($i === 0)
                                    <option value="Thư ký">Thư ký</option>
                                    <option value="Thành viên">Thành viên</option>
                                    <option value="Phản biện">Phản biện</option>
                                @elseif($i === 1)
                                    <option value="Chủ tịch">Chủ tịch</option>
                                    <option value="Thành viên">Thành viên</option>
                                    <option value="Phản biện">Phản biện</option>
                                @else
                                    <option value="Chủ tịch">Chủ tịch</option>
                                    <option value="Thư ký">Thư ký</option>
                                    <option value="Phản biện">Phản biện</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-1">
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
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-3">
    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">
        <i class="fa-solid fa-landmark me-2"></i>Thành Lập Hội Đồng
    </button>
    <a href="{{ route('admin.hoidong.index') }}" class="btn btn-light rounded-pill px-4">Hủy</a>
</div>
</form>

<script>
let soTv = 3;
document.getElementById('themThanhVien').addEventListener('click', function() {
    const container = document.getElementById('danhSachThanhVien');
    const html = `
    <div class="row g-2 mb-2 dong-thanh-vien">
        <div class="col-7">
            <select name="thanh_viens[${soTv}][MaGV]" class="form-select form-select-sm" required>
                <option value="">— Chọn Giảng viên —</option>
                @foreach($giangViens as $gv)
                <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4">
            <select name="thanh_viens[${soTv}][VaiTro]" class="form-select form-select-sm" required>
                <option value="Thành viên" selected>Thành viên</option>
                <option value="Phản biện">Phản biện</option>
                <option value="Chủ tịch">Chủ tịch</option>
                <option value="Thư ký">Thư ký</option>
            </select>
        </div>
        <div class="col-1">
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
@endsection
