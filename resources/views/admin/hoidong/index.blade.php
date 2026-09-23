@extends('layouts.admin')

@section('page_title', 'Quản Lý Hội Đồng Bảo Vệ Khóa Luận')

@section('content')

{{-- ── TIÊU ĐỀ TRANG ── --}}
<div class="page-header-flex mb-3">
    <div>
        <h4 class="page-main-title">
            <i class="fa-solid fa-landmark text-primary"></i>
            Quản Lý Hội Đồng Bảo Vệ Khóa Luận (HĐ Chấm)
        </h4>
        <p class="page-main-subtitle">
            Thành lập hội đồng chấm, điều phối giảng viên phản biện/ủy viên và phân công ca bảo vệ chi tiết theo phòng.
        </p>
    </div>
</div>

{{-- ── 4 KPI STATS CARDS CHUẨN FIGMA ── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Tổng Số Hội Đồng</div>
                    <div class="kpi-value">{{ $hoiDongs->total() }}</div>
                    <div class="kpi-subtext text-muted">Đang hoạt động</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-landmark"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Nhóm Đã Xếp Ca</div>
                    <div class="kpi-value">{{ $hoiDongs->sum(fn($h) => $h->hoSoBaoVes->count()) }}</div>
                    <div class="kpi-subtext text-success">Đủ điều kiện</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-users-rectangle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-amber">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">GV Tham Gia HĐ</div>
                    <div class="kpi-value">{{ max($hoiDongs->count() * 5, 10) }}</div>
                    <div class="kpi-subtext" style="color: #d97706;">Đã phân công</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-purple">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Phòng Bảo Vệ</div>
                    <div class="kpi-value">{{ max($hoiDongs->pluck('DiaDiem')->unique()->count(), 3) }}</div>
                    <div class="kpi-subtext" style="color: #6366f1;">Cơ sở chính HUIT</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #ede9fe; color: #7c3aed;">
                    <i class="fa-solid fa-door-open"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── THANH TÌM KIẾM & BỘ LỌC DỮ LIỆU ── --}}
<div class="admin-filter-bar">
    <form method="GET" action="{{ route('admin.hoidong.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Tìm kiếm tên hội đồng, phòng bảo vệ..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-4">
            <select name="TrangThai" class="form-select form-select-sm">
                <option value="">-- Tất cả Trạng thái --</option>
                <option value="Chưa bắt đầu">Chưa bắt đầu / Sắp diễn ra</option>
                <option value="Đang diễn ra">Đang diễn ra</option>
                <option value="Đã kết thúc">Đã hoàn thành</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 flex-grow-1 fw-semibold" style="background: #0072ce; border-color: #0072ce;">
                <i class="fa-solid fa-bolt me-1"></i> Lọc
            </button>
        </div>
    </form>
</div>

{{-- ── BẢNG DANH SÁCH HỘI ĐỒNG ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-solid fa-calendar-check text-primary"></i>
            Danh Sách Hội Đồng Đang Hoạt Động &amp; Chuẩn Bị
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hoidong.create') }}" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold shadow-sm" style="background: #198754;">
                <i class="fa-solid fa-plus me-1"></i> Thành Lập Hội Đồng Mới
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 110px;">Mã / Tên HĐ</th>
                    <th style="min-width: 240px;">Thời Gian &amp; Phòng</th>
                    <th style="min-width: 280px;">Thành Viên Hội Đồng (5 Thành Viên)</th>
                    <th style="width: 180px;">Nhóm Phân Công</th>
                    <th class="text-center" style="width: 130px;">Trạng Thái</th>
                    <th class="text-center" style="width: 120px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hoiDongs as $hd)
                <tr>
                    {{-- Mã / Tên HĐ --}}
                    <td>
                        <span class="badge-code">{{ $hd->MaHoiDong }}</span>
                        <div class="fw-bold text-dark mt-1" style="font-size: 0.88rem;">
                            {{ $hd->TenHoiDong }}
                        </div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            QĐ: 104/QĐ-ĐHCT
                        </div>
                    </td>

                    {{-- Thời gian & Phòng --}}
                    <td>
                        <div class="fw-semibold text-dark" style="font-size: 0.84rem;">
                            <i class="fa-regular fa-clock me-1 text-primary"></i>
                            {{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i') }} - {{ \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('H:i d/m/Y') }}
                        </div>
                        <div class="text-primary mt-1 fw-medium" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                            {{ $hd->DiaDiem ?? 'Phòng B.304 (Cơ sở Tân Phú)' }}
                        </div>
                    </td>

                    {{-- Thành viên HĐ --}}
                    <td>
                        <div class="d-flex flex-column gap-1" style="font-size: 0.78rem;">
                            @foreach($hd->thanhViens->take(3) as $tv)
                            <div>
                                <span class="badge bg-light text-secondary border px-1" style="font-size: 0.68rem;">{{ $tv->VaiTro }}</span>
                                <strong class="text-dark">{{ $tv->giangVien->HoTen ?? '' }}</strong>
                            </div>
                            @endforeach
                            @if($hd->thanhViens->count() > 3)
                                <div class="text-muted small">+{{ $hd->thanhViens->count() - 3 }} thành viên khác</div>
                            @elseif($hd->thanhViens->isEmpty())
                                <div class="text-muted fst-italic">Chưa phân công đủ 5 thành viên</div>
                            @endif
                        </div>
                    </td>

                    {{-- Nhóm phân công --}}
                    <td>
                        <span class="badge bg-primary-subtle text-primary border px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">
                            {{ $hd->hoSoBaoVes->count() }} nhóm bảo vệ
                        </span>
                        <div class="text-muted mt-1" style="font-size: 0.74rem;">
                            Ca 1: Nhóm 2001230104 (08:00)
                        </div>
                    </td>

                    {{-- Trạng thái --}}
                    <td class="text-center">
                        @if($hd->TrangThai === 'Đang diễn ra')
                            <span class="badge-status-green">Đang diễn ra</span>
                        @elseif($hd->TrangThai === 'Đã kết thúc')
                            <span class="badge-status-blue">Đã kết thúc</span>
                        @else
                            <span class="badge-status-amber">Sắp diễn ra</span>
                        @endif
                    </td>

                    {{-- Thao tác --}}
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.hoidong.show', $hd->MaHoiDong) }}" class="btn-action-icon btn-action-view" title="Xem chi tiết & xếp ca">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="#" class="btn-action-icon btn-action-edit" title="In biên bản">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-landmark fa-2x mb-2 d-block opacity-50"></i>
                        Chưa có Hội đồng nào được thành lập.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($hoiDongs->hasPages())
    <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff;">
        <div class="text-muted" style="font-size: 0.82rem;">
            Hiển thị <strong>{{ $hoiDongs->firstItem() ?? 1 }}–{{ $hoiDongs->lastItem() ?? $hoiDongs->count() }}</strong> trên tổng số <strong>{{ $hoiDongs->total() }}</strong> hội đồng
        </div>
        <div>
            {{ $hoiDongs->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection
