@extends('layouts.admin')

@section('page_title', 'Quy Định & Chuẩn Khóa Luận Tốt Nghiệp')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item">Kế Hoạch &amp; Tiến Độ</li>
                    <li class="breadcrumb-item active" aria-current="page">Quy Định Khóa Luận</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-primary"></i>
                <span>Quy Định &amp; Tiêu Chuẩn Khóa Luận Tốt Nghiệp</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.quydinh.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Quy Định Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-blue">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Quy Định</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_quydinh'] }}</div>
                        <div class="small text-muted mt-1">Đang thiết lập</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-purple">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Kế Hoạch Áp Dụng</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['so_kehoach'] }}</div>
                        <div class="small text-muted mt-1">Đợt khóa luận</div>
                    </div>
                    <div class="kpi-icon-circle bg-purple-subtle text-purple" style="background: rgba(147, 51, 234, 0.1); color: #9333ea;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Chuẩn Tín Chỉ Tối Thiểu</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ $stats['tc_chuan'] }}</div>
                        <div class="small text-muted mt-1">Điều kiện tiên quyết SV</div>
                    </div>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Chuẩn Điểm Tích Lũy</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['gpa_chuan'] }}</div>
                        <div class="small text-muted mt-1">Thang điểm 4.0</div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.quydinh.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Tìm theo tên quy định, giá trị tham số...">
                    </div>
                </div>
                <div class="col-md-5">
                    <select name="make_hoach" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả Kế hoạch khóa luận --</option>
                        @foreach($keHoachs as $kh)
                            <option value="{{ $kh->MakeHoach }}" {{ request('make_hoach') == $kh->MakeHoach ? 'selected' : '' }}>
                                {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MakeHoach }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill">Lọc</button>
                    @if(request()->hasAny(['search', 'make_hoach']))
                        <a href="{{ route('admin.quydinh.index') }}" class="btn btn-sm btn-light border rounded-pill" title="Xóa lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" width="16%">MÃ QUY ĐỊNH</th>
                        <th class="py-3" width="28%">TÊN QUY ĐỊNH / NỘI DUNG</th>
                        <th class="py-3 text-center" width="16%">GIÁ TRỊ THAM SỐ</th>
                        <th class="py-3" width="22%">KẾ HOẠCH ÁP DỤNG</th>
                        <th class="pe-4 py-3 text-center" width="12%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quyDinhs as $qd)
                        <tr>
                            <td class="ps-4">
                                <span class="badge-code">{{ $qd->MaQuyDinh }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $qd->TenQuyDinh }}</div>
                                @if($qd->MoTa)
                                    <div class="small text-muted text-truncate" style="max-width: 320px;" title="{{ $qd->MoTa }}">
                                        {{ $qd->MoTa }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 fw-bold fs-6">
                                    {{ $qd->GiaTri }}
                                </span>
                            </td>
                            <td>
                                @if($qd->keHoachKhoaLuan)
                                    <div class="fw-medium text-dark">{{ $qd->keHoachKhoaLuan->TenKeHoach }}</div>
                                    <div class="small text-muted">{{ $qd->keHoachKhoaLuan->hocKy->TenHocKy ?? '' }}</div>
                                @else
                                    <span class="text-muted small">Quy chế dùng chung</span>
                                @endif
                            </td>
                            <td class="pe-4 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.quydinh.show', $qd->MaQuyDinh) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.quydinh.edit', $qd->MaQuyDinh) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.quydinh.destroy', $qd->MaQuyDinh) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa quy định này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa quy định">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-scale-unbalanced text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Chưa có quy định nào cho kế hoạch này</h6>
                                    <p class="small text-muted mb-3">Bạn có thể tạo mới quy định cho kế hoạch khóa luận này.</p>
                                    <a href="{{ route('admin.quydinh.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                                        <i class="fa-solid fa-plus me-1"></i> Thêm Quy Định Mới
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $quyDinhs->firstItem() ?? 0 }}–{{ $quyDinhs->lastItem() ?? 0 }} trên tổng số {{ $quyDinhs->total() }} quy định
            </span>
            @if($quyDinhs->hasPages())
                <div>{{ $quyDinhs->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
