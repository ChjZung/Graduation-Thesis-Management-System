@extends('layouts.truongkhoa')

@section('page_title', 'Hội Đồng Bảo Vệ Khóa Luận')

@push('styles')
<style>
    .huit-card {
        background: #ffffff;
        border: 1px solid var(--gv-border, #e2e8f0);
        border-radius: 1rem;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        height: 100%;
        min-width: 0;
        overflow: hidden;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .huit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .hover-primary:hover {
        color: var(--huit-blue, #0072ce) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: #00305a;">
                <i class="fa-solid fa-landmark text-primary me-2"></i>Quản Lý Hội Đồng Bảo Vệ Khóa Luận
            </h4>
            <div class="text-muted small mt-1">
                Trưởng khoa lập Hội đồng, phân công thành viên, tạo văn bản quyết định và công bố chính thức — {{ $khoa->TenKhoa ?? 'Khoa' }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('truongkhoa.hoidong.create') }}" class="btn btn-primary shadow-xs fw-semibold d-inline-flex align-items-center rounded-pill px-4">
                <i class="fa-solid fa-plus me-2"></i> Lập Hội Đồng Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Số Hội Đồng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-landmark fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDongs->total() }}</span>
                    <span class="small text-muted fw-medium">hội đồng</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đã Công Bố Chính Thức</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-bullhorn fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $hoiDongs->where('TrangThaiCongBo', 'Đã công bố')->count() }}</span>
                    <span class="small text-muted fw-medium">hội đồng công bố</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đề Tài / Nhóm Đã Gắn</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-graduation-cap fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDongs->sum(fn($h) => $h->hoSoBaoVes->count()) }}</span>
                    <span class="small text-muted fw-medium">đề tài bảo vệ</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Chưa Công Bố (Nháp)</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-file-pen fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-primary lh-1">{{ $hoiDongs->where('TrangThaiCongBo', '!=', 'Đã công bố')->count() }}</span>
                    <span class="small text-muted fw-medium">đang chuẩn bị</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 bg-white">
        <form method="GET" action="{{ route('truongkhoa.hoidong.index') }}" class="d-flex flex-wrap align-items-center gap-2 w-100">
            <div class="input-group" style="min-width: 240px; flex: 2;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm tên Hội đồng, mã HĐ, địa điểm, số QĐ...">
            </div>

            <select name="MaHocKy" class="form-select" style="min-width: 170px; flex: 1;" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                @foreach($hocKys as $hk)
                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                </option>
                @endforeach
            </select>

            <select name="TrangThaiCongBo" class="form-select" style="min-width: 170px; flex: 1;" onchange="this.form.submit()">
                <option value="">-- Trạng thái công bố --</option>
                <option value="Đã công bố" {{ request('TrangThaiCongBo') === 'Đã công bố' ? 'selected' : '' }}>🟢 Đã công bố</option>
                <option value="Chưa công bố" {{ request('TrangThaiCongBo') === 'Chưa công bố' ? 'selected' : '' }}>🟡 Chưa công bố (Nháp)</option>
                <option value="Đã thu hồi" {{ request('TrangThaiCongBo') === 'Đã thu hồi' ? 'selected' : '' }}>🔴 Đã thu hồi</option>
            </select>

            <select name="TrangThai" class="form-select" style="min-width: 160px; flex: 1;" onchange="this.form.submit()">
                <option value="">-- Trạng thái diễn ra --</option>
                <option value="Chưa diễn ra" {{ request('TrangThai') === 'Chưa diễn ra' ? 'selected' : '' }}>Chưa diễn ra</option>
                <option value="Đang diễn ra" {{ request('TrangThai') === 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="Đã kết thúc" {{ request('TrangThai') === 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
            </select>

            @if(request('search') || request('TrangThai') || request('TrangThaiCongBo') || request('MaHocKy'))
                <a href="{{ route('truongkhoa.hoidong.index') }}" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center">
                    <i class="fa-solid fa-xmark me-2"></i>Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Danh Sách Hội Đồng Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <span class="fw-bold text-dark fs-6">
            <i class="fa-regular fa-folder-open me-2 text-primary"></i>Danh Sách Hội Đồng Thuộc {{ $khoa->TenKhoa ?? 'Khoa' }}
        </span>
        <span class="small text-muted">Tổng số: <strong>{{ $hoiDongs->total() }}</strong> hội đồng</span>
    </div>

    <!-- Lưới Card Hội Đồng (2 Cột trên Desktop, 1 Cột trên Mobile / Tablet) -->
    <div class="row g-3 mb-4">
        @forelse($hoiDongs as $hd)
            @php
                $soDeTai = $hd->hoSoBaoVes->count();
                $sortedMembers = $hd->sorted_thanh_viens;
            @endphp
            <div class="col-12 col-xl-6 min-w-0 d-flex flex-column">
                <div class="huit-card p-3 p-md-4 min-w-0 d-flex flex-column h-100 flex-grow-1 bg-white">
                    
                    <!-- Hàng đầu: Mã HĐ + Badges Trạng thái -->
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2 min-w-0 flex-wrap">
                        <span class="badge bg-light text-primary border fw-bold px-2.5 py-1 font-monospace" style="font-size: 0.85rem;">
                            {{ $hd->MaHoiDong }}
                        </span>

                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            {{-- Trạng thái công bố --}}
                            @if($hd->TrangThaiCongBo === 'Đã công bố')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold small">
                                    <i class="fa-solid fa-circle-check me-1"></i> Đã công bố
                                </span>
                            @elseif($hd->TrangThaiCongBo === 'Đã thu hồi')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold small">
                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Đã thu hồi
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-semibold small">
                                    <i class="fa-solid fa-file-pen me-1"></i> Chưa công bố
                                </span>
                            @endif

                            {{-- Trạng thái diễn ra --}}
                            @if($hd->trang_thai_thoi_gian === 'DaKetThuc')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small fw-bold">
                                    <i class="fa-solid fa-check me-1"></i> Đã Kết Thúc
                                </span>
                            @elseif($hd->trang_thai_thoi_gian === 'DangDienRa')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small fw-bold">
                                    <i class="fa-solid fa-spinner fa-spin me-1"></i> Đang Diễn Ra
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 small fw-bold">
                                    <i class="fa-regular fa-clock me-1"></i> Sắp Diễn Ra
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Tên Hội Đồng -->
                    <h5 class="fw-bold text-dark my-2 line-clamp-2 text-break" title="{{ $hd->TenHoiDong }}" style="overflow-wrap: anywhere; word-break: break-word; line-height: 1.4; font-size: 1.05rem;">
                        <a href="{{ route('truongkhoa.hoidong.show', $hd->MaHoiDong) }}" class="text-decoration-none text-dark hover-primary">
                            {{ $hd->TenHoiDong }}
                        </a>
                    </h5>

                    <!-- Dòng Meta: Học kỳ, Đợt, Số QĐ -->
                    <div class="small text-muted mb-3 d-flex flex-wrap gap-2 align-items-center min-w-0">
                        <span><i class="fa-regular fa-calendar-days me-1 text-primary"></i>{{ $hd->hocKy?->TenHocKy ?? $hd->MaHocKy }}</span>
                        <span>•</span>
                        <span>Đợt: <strong>{{ $hd->DotBaoVe ?? '1' }}</strong></span>
                        @if($hd->SoQuyetDinh)
                            <span class="badge bg-light text-secondary border font-monospace text-truncate" title="Số QĐ: {{ $hd->SoQuyetDinh }}" style="max-width: 100%;">
                                Số QĐ: {{ $hd->SoQuyetDinh }}
                            </span>
                        @endif
                    </div>

                    <!-- Khối Thời Gian & Phòng -->
                    <div class="p-2.5 px-3 bg-light rounded-3 border mb-3 small min-w-0">
                        <div class="row g-2 align-items-center">
                            <div class="col-sm-6 text-truncate">
                                <i class="fa-regular fa-clock me-1 text-primary"></i>
                                <strong>{{ $hd->NgayBaoVe ? \Carbon\Carbon::parse($hd->NgayBaoVe)->format('d/m/Y') : (\Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('d/m/Y')) }}</strong>
                                <span class="text-muted">({{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i') }} - {{ \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('H:i') }})</span>
                            </div>
                            <div class="col-sm-6 text-truncate line-clamp-2" title="{{ $hd->DiaDiem ?? 'Chưa xếp phòng' }}" style="overflow-wrap: anywhere;">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                <span class="fw-semibold text-dark">{{ $hd->DiaDiem ?? 'Chưa xếp phòng' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Khối Thành Viên Hội Đồng -->
                    <div class="mb-3 min-w-0">
                        <div class="small text-muted fw-semibold mb-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                            <span><i class="fa-solid fa-users text-primary me-1"></i> Thành viên hội đồng:</span>
                            <span class="badge {{ $soDeTai > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border' }} rounded-pill px-2.5 py-1">
                                <i class="fa-solid fa-users-rectangle me-1"></i>{{ $soDeTai > 0 ? $soDeTai . ' đề tài' : 'Chưa có đề tài' }}
                            </span>
                        </div>
                        <div class="d-flex flex-column gap-1.5" style="font-size: 0.82rem;">
                            @forelse($sortedMembers->take(4) as $tv)
                                <div class="d-flex align-items-center gap-2 text-nowrap min-w-0">
                                    <span class="badge {{ $tv->badge_class }} border px-2 py-1 fw-semibold flex-shrink-0" style="font-size: 0.72rem; min-width: 68px; text-align: center;">
                                        {{ $tv->short_vai_tro }}
                                    </span>
                                    <span class="fw-semibold text-dark text-truncate" title="{{ $tv->giangVien->HoTen ?? '' }} ({{ $tv->VaiTro }})">
                                        {{ $tv->giangVien->HoTen ?? '—' }}
                                    </span>
                                </div>
                            @empty
                                <span class="text-muted fst-italic small">Chưa phân công thành viên</span>
                            @endforelse
                            @if($sortedMembers->count() > 4)
                                @php
                                    $remainingNames = $sortedMembers->slice(4)->map(fn($t) => ($t->giangVien->HoTen ?? '—') . ' (' . $t->short_vai_tro . ')')->join(', ');
                                @endphp
                                <div class="text-muted small ps-1" style="font-size: 0.75rem;" title="{{ $remainingNames }}">
                                    +{{ $sortedMembers->count() - 4 }} thành viên khác: <span class="fst-italic">{{ Str::limit($remainingNames, 45) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Chân Card: Nút Chi tiết & Các nút Icon Thao Tác -->
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-3 border-top w-100 flex-wrap">
                        <div>
                            <a href="{{ route('truongkhoa.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold d-inline-flex align-items-center shadow-xs" title="Xem chi tiết & Điều chỉnh">
                                <i class="fa-solid fa-eye me-1.5"></i> Chi tiết
                            </a>
                        </div>

                        <div class="d-flex align-items-center gap-1.5">
                            @if($hd->TrangThaiCongBo !== 'Đã công bố')
                                <form action="{{ route('truongkhoa.hoidong.congBo', $hd->MaHoiDong) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return handleConfirmAction(this, 'Xác nhận công bố chính thức Hội đồng {{ addslashes($hd->TenHoiDong) }}? Văn bản PDF sẽ được ban hành và gửi thông báo tới các bên liên quan.');">
                                    @csrf
                                    <button type="submit" class="btn-card-icon text-success" title="Công bố chính thức Hội đồng">
                                        <i class="fa-solid fa-bullhorn text-success"></i>
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('truongkhoa.hoidong.previewPdf', $hd->MaHoiDong) }}" target="_blank" class="btn-card-icon" title="Xem trước PDF Quyết định">
                                <i class="fa-solid fa-file-pdf text-danger"></i>
                            </a>

                            <a href="{{ route('truongkhoa.hoidong.downloadPdf', $hd->MaHoiDong) }}" class="btn-card-icon" title="Tải file PDF Quyết định">
                                <i class="fa-solid fa-download text-primary"></i>
                            </a>

                            <form action="{{ route('truongkhoa.hoidong.destroy', $hd->MaHoiDong) }}" method="POST" class="d-inline m-0 p-0 form-delete-hoidong" onsubmit="return handleConfirmDelete(this, 'Bạn có chắc chắn muốn xóa Hội đồng {{ addslashes($hd->TenHoiDong) }}? Các đề tài sẽ được giải phóng trở về trạng thái đủ điều kiện bảo vệ.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-card-icon btn-card-icon-delete" title="Xóa Hội đồng">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="fa-solid fa-landmark text-muted mb-3 opacity-50" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold text-secondary">Không tìm thấy Hội đồng nào phù hợp</h5>
                    <p class="small text-muted mb-4">Vui lòng thử điều chỉnh bộ lọc hoặc tạo Hội đồng bảo vệ khóa luận mới cho Khoa.</p>
                    <div>
                        <a href="{{ route('truongkhoa.hoidong.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                            <i class="fa-solid fa-plus me-2"></i> Lập Hội Đồng Mới
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Phân Trang -->
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <span class="small text-muted">
            Hiển thị {{ $hoiDongs->firstItem() ?? 0 }}–{{ $hoiDongs->lastItem() ?? 0 }} trên tổng số {{ $hoiDongs->total() }} hội đồng
        </span>
        @if($hoiDongs->hasPages())
            <div>
                {{ $hoiDongs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function handleConfirmDelete(form, message) {
    if (confirm(message)) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        }
        return true;
    }
    return false; // Bấm Cancel: không khóa nút, không submit form
}

function handleConfirmAction(form, message) {
    if (confirm(message)) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        }
        return true;
    }
    return false; // Bấm Cancel: không khóa nút, không submit form
}
</script>
@endpush
@endsection
