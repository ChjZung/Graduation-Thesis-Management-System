@extends('layouts.truongkhoa')

@section('page_title', 'Hội Đồng Bảo Vệ Khóa Luận')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: #00305a;">
                <i class="fa-solid fa-landmark text-primary me-2"></i>Quản Lý Hội Đồng Bảo Vệ Khóa Luận
            </h4>
            <div class="text-muted small mt-1">
                Quyết định thành lập Hội đồng và phân công các nhóm sinh viên vào Hội đồng bảo vệ — {{ $khoa->TenKhoa ?? 'Khoa' }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('truongkhoa.hoidong.create') }}" class="btn btn-success shadow-xs fw-semibold d-inline-flex align-items-center">
                <i class="fa-solid fa-plus me-2"></i> Thành Lập Hội Đồng Mới
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
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nhóm Đã Phân Công</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-users-rectangle fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $hoiDongs->sum(fn($h) => $h->hoSoBaoVes->count()) }}</span>
                    <span class="small text-muted fw-medium">nhóm bảo vệ</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">GV Tham Gia HĐ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-chalkboard-user fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ max($hoiDongs->count() * 5, 5) }}</span>
                    <span class="small text-muted fw-medium">lượt thành viên</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Phòng Bảo Vệ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-door-open fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-primary lh-1">{{ max($hoiDongs->pluck('DiaDiem')->filter()->unique()->count(), 1) }}</span>
                    <span class="small text-muted fw-medium">phòng hội đồng</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 bg-white">
        <form method="GET" action="{{ route('truongkhoa.hoidong.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 240px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm kiếm tên hội đồng, mã HĐ, địa điểm...">
                </div>
                <select name="TrangThai" class="form-select" style="min-width: 180px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Chưa diễn ra" {{ request('TrangThai') === 'Chưa diễn ra' ? 'selected' : '' }}>🔵 Chưa diễn ra / Sắp diễn ra</option>
                    <option value="Đang diễn ra" {{ request('TrangThai') === 'Đang diễn ra' ? 'selected' : '' }}>🟢 Đang diễn ra</option>
                    <option value="Đã kết thúc" {{ request('TrangThai') === 'Đã kết thúc' ? 'selected' : '' }}>⚪ Đã kết thúc</option>
                </select>
                @if(request('search') || request('TrangThai'))
                    <a href="{{ route('truongkhoa.hoidong.index') }}" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center"><i class="fa-solid fa-xmark me-2"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('truongkhoa.hoidong.create') }}" class="btn btn-success shadow-xs fw-semibold d-inline-flex align-items-center">
                    <i class="fa-solid fa-plus me-2"></i> Thêm Hội Đồng
                </a>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Hội Đồng Thuộc {{ $khoa->TenKhoa ?? 'Khoa' }}</span>
            <span class="small text-muted">Tổng số: <strong>{{ $hoiDongs->total() }}</strong> hội đồng</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 140px;">MÃ HỘI ĐỒNG</th>
                            <th style="min-width: 230px;">TÊN HỘI ĐỒNG &amp; ĐỊA ĐIỂM</th>
                            <th style="min-width: 200px;">THỜI GIAN DIỄN RA</th>
                            <th style="min-width: 270px;">THÀNH VIÊN HỘI ĐỒNG</th>
                            <th class="text-center" style="width: 140px;">NHÓM BẢO VỆ</th>
                            <th class="text-center" style="width: 140px;">TRẠNG THÁI</th>
                            <th class="pe-3 text-center" style="width: 100px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($hoiDongs as $hd)
                        <tr>
                            {{-- Mã HĐ --}}
                            <td class="ps-3 py-3">
                                <span class="badge bg-light text-primary border fw-bold px-2 py-1">{{ $hd->MaHoiDong }}</span>
                            </td>

                            {{-- Tên Hội đồng & Địa điểm --}}
                            <td class="py-3">
                                <div class="fw-bold text-dark fs-6">{{ $hd->TenHoiDong }}</div>
                                <div class="small text-muted mt-1">
                                    <i class="fa-solid fa-location-dot me-2 text-danger"></i>
                                    {{ $hd->DiaDiem ?? 'Phòng B.304' }}
                                </div>
                            </td>

                            {{-- Thời gian --}}
                            <td class="py-3">
                                <div class="small fw-medium text-dark">
                                    <i class="fa-regular fa-clock me-2 text-primary"></i>
                                    {{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i d/m/Y') }}
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="fa-solid fa-arrow-right-long me-2 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('H:i d/m/Y') }}
                                </div>
                            </td>

                            {{-- Thành viên Hội đồng --}}
                            <td class="py-3">
                                <div class="d-flex flex-column gap-1" style="font-size: 0.8rem;">
                                    @forelse($hd->thanhViens->take(3) as $tv)
                                    <div>
                                        <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 0.7rem;">{{ $tv->VaiTro }}</span>
                                        <strong class="text-dark">{{ $tv->giangVien->HoTen ?? '' }}</strong>
                                    </div>
                                    @empty
                                    <span class="text-muted fst-italic small">Chưa phân công thành viên</span>
                                    @endforelse
                                    @if($hd->thanhViens->count() > 3)
                                        <div class="text-muted small">+{{ $hd->thanhViens->count() - 3 }} thành viên khác</div>
                                    @endif
                                </div>
                            </td>

                            {{-- Nhóm bảo vệ --}}
                            <td class="text-center py-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-users me-2"></i>{{ $hd->hoSoBaoVes->count() }} nhóm
                                </span>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center py-3">
                                @php
                                    $st = mb_strtolower(trim((string)$hd->TrangThai));
                                    $isEnded = in_array($st, ['đã kết thúc', 'da ket thuc', 'completed', 'ended']);
                                    $isRunning = in_array($st, ['đang diễn ra', 'dang dien ra', 'active', 'running']);
                                @endphp
                                @if($isEnded)
                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 fw-medium">
                                        Đã kết thúc
                                    </span>
                                @elseif($isRunning)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-circle me-2" style="font-size: 0.45rem; color: #16a34a;"></i> Đang diễn ra
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2 fw-medium">
                                        <i class="fa-solid fa-circle me-2" style="font-size: 0.45rem; color: #0284c7;"></i> Sắp diễn ra
                                    </span>
                                @endif
                            </td>

                            {{-- Thao tác --}}
                            <td class="pe-3 text-center py-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('truongkhoa.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center" title="Xem chi tiết & Phân công nhóm">
                                        <i class="fa-solid fa-users-gear me-1"></i> Phân công
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-landmark text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Chưa có Hội đồng nào được thành lập trong Khoa</h6>
                                    <p class="small text-muted mb-3">Trưởng khoa có thẩm quyền ra quyết định thành lập các Hội đồng đánh giá khóa luận tốt nghiệp.</p>
                                    <a href="{{ route('truongkhoa.hoidong.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Thành Lập Hội Đồng Mới</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
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
</div>
@endsection
