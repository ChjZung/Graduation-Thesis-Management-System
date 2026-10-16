@extends('layouts.admin')

@section('page_title', 'Quy Định & Tiêu Chuẩn Khóa Luận')

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">

    <!-- Page Header -->
    <div class="row align-items-center gy-3 mb-4">
        <div class="col-12 col-md">
           
            <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <span class="p-2 bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-scale-balanced fs-5"></i>
                </span>
                <span>Quy Định &amp; Tiêu Chuẩn Khóa Luận</span>
            </h3>
        </div>
        <div class="col-12 col-md-auto d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.calendar') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-calendar-week me-1.5"></i> Lịch Quy Trình
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalInitDefaults">
                <i class="fa-solid fa-rotate me-1.5"></i> Nạp 8 Quy Định Chuẩn
            </button>
            <a href="{{ route('admin.quydinh.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 fw-semibold shadow-sm">
                <i class="fa-solid fa-plus me-1.5"></i> Thêm Quy Định
            </a>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Card 1 -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 position-relative overflow-hidden">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Tổng Quy Định</span>
                        <div class="h3 fw-bold text-dark mb-1">{{ $stats['total_quydinh'] }}</div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-normal small">
                            <i class="fa-solid fa-check me-1"></i>Đang hiệu lực
                        </span>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-scale-balanced fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 position-relative overflow-hidden">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Kế Hoạch Áp Dụng</span>
                        <div class="h3 fw-bold text-dark mb-1">{{ $stats['so_kehoach'] }}</div>
                        <span class="text-muted small"><i class="fa-solid fa-calendar-check text-info me-1"></i>Đợt khóa luận</span>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-calendar-days fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 position-relative overflow-hidden">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Chuẩn Tín Chỉ</span>
                        <div class="h3 fw-bold text-success mb-1">{{ $stats['tc_chuan'] }} <span class="fs-6 fw-normal text-muted">TC</span></div>
                        <span class="text-muted small">Điều kiện tích lũy tối thiểu</span>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-graduation-cap fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 position-relative overflow-hidden">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">GPA Tích Lũy</span>
                        <div class="h3 fw-bold text-warning mb-1">{{ $stats['gpa_chuan'] }} <span class="fs-6 fw-normal text-muted">/ 4.0</span></div>
                        <span class="text-muted small">Thang điểm tối thiểu</span>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-chart-line fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Actions Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.quydinh.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-start-0 ps-0" placeholder="Tìm theo tên quy định, giá trị tham số...">
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <select name="make_hoach" class="form-select bg-light border" onchange="this.form.submit()">
                        <option value="">-- Tất cả Kế hoạch khóa luận --</option>
                        @foreach($keHoachs as $kh)
                            <option value="{{ $kh->MakeHoach }}" {{ request('make_hoach') == $kh->MakeHoach ? 'selected' : '' }}>
                                {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MakeHoach }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    @if(request()->hasAny(['search', 'make_hoach']))
                        <a href="{{ route('admin.quydinh.index') }}" class="btn btn-light border rounded-3 px-3 text-secondary d-flex align-items-center justify-content-center" title="Xóa bộ lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold text-dark mb-0">Danh Sách Quy Định &amp; Tiêu Chuẩn</h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5">{{ $quyDinhs->total() }}</span>
            </div>
            @if(request('make_hoach'))
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">
                    <i class="fa-solid fa-circle-info me-1"></i>Kế hoạch: {{ request('make_hoach') }}
                </span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom text-uppercase fs-8 text-muted fw-bold" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3" width="5%">#</th>
                        <th class="py-3" width="12%">Mã Quy Định</th>
                        <th class="py-3" width="35%">Tên Quy Định / Tiêu Chuẩn</th>
                        <th class="py-3 text-center" width="18%">Giá Trị Áp Dụng</th>
                        <th class="py-3" width="18%">Kế Hoạch Áp Dụng</th>
                        <th class="pe-4 py-3 text-end" width="12%">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($quyDinhs as $idx => $qd)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted fs-7">{{ $quyDinhs->firstItem() + $idx }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-7 rounded-2">{{ $qd->MaQuyDinh }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0.5">{{ $qd->TenQuyDinh }}</div>
                                @if($qd->MoTa)
                                    <div class="small text-muted text-truncate" style="max-width: 380px;" title="{{ $qd->MoTa }}">
                                        {{ $qd->MoTa }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-bold fs-7 rounded-pill">
                                    {{ $qd->GiaTri }}
                                </span>
                            </td>
                            <td>
                                @if($qd->keHoachKhoaLuan)
                                    <div class="fw-semibold text-dark small">{{ $qd->keHoachKhoaLuan->TenKeHoach }}</div>
                                    <div class="small text-muted"><i class="fa-regular fa-calendar me-1"></i>{{ $qd->keHoachKhoaLuan->hocKy->TenHocKy ?? '' }}</div>
                                @else
                                    <span class="badge bg-light text-muted border border-dashed font-normal">Chung toàn trường</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group gap-1" role="group">
                                    <a href="{{ route('admin.quydinh.show', $qd->MaQuyDinh) }}" class="btn btn-sm btn-icon btn-light border text-primary rounded-2" data-bs-toggle="tooltip" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.quydinh.edit', $qd->MaQuyDinh) }}" class="btn btn-sm btn-icon btn-light border text-warning rounded-2" data-bs-toggle="tooltip" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.quydinh.destroy', $qd->MaQuyDinh) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa quy định này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light border text-danger rounded-2" data-bs-toggle="tooltip" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <div class="avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                        <i class="fa-solid fa-folder-open fs-2"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Chưa tìm thấy quy định phù hợp</h6>
                                    <p class="small text-muted mb-4" style="max-width: 420px; margin: 0 auto;">Bạn có thể đồng bộ nhanh 8 quy định chuẩn của Khoa CNTT hoặc khởi tạo quy định mới thủ công.</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalInitDefaults">
                                            <i class="fa-solid fa-rotate me-1.5"></i> Nạp 8 Quy Định Chuẩn
                                        </button>
                                        <a href="{{ route('admin.quydinh.create') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                                            <i class="fa-solid fa-plus me-1.5"></i> Tạo Thủ Công
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Card Footer Pagination -->
        @if($quyDinhs->total() > 0)
            <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-muted">
                    Hiển thị <span class="fw-semibold text-dark">{{ $quyDinhs->firstItem() ?? 0 }}</span> – <span class="fw-semibold text-dark">{{ $quyDinhs->lastItem() ?? 0 }}</span> trong tổng số <span class="fw-semibold text-dark">{{ $quyDinhs->total() }}</span> quy định
                </span>
                @if($quyDinhs->hasPages())
                    <div>{{ $quyDinhs->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Modal Nạp 8 Quy Định Chuẩn -->
<div class="modal fade" id="modalInitDefaults" tabindex="-1" aria-labelledby="modalInitDefaultsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.quydinh.initDefaults') }}" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2" id="modalInitDefaultsLabel">
                        <i class="fa-solid fa-rotate"></i> Nạp 8 Quy Định &amp; Tiêu Chuẩn Chuẩn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis rounded-3 p-3 mb-3 small">
                        <i class="fa-solid fa-circle-info me-1.5"></i>
                        Hệ thống sẽ đồng bộ <strong>8 tham số tiêu chuẩn</strong> (Tín chỉ min: 115, GPA min: 2.0, Nhóm 2-3 SV, Đề tài/GV: 5, Turnitin &le; 20%, Điểm đạt &ge; 5.0, Thời gian: 12 tuần,...).
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small mb-1">Chọn Kế Hoạch Áp Dụng <span class="text-danger">*</span></label>
                        <select name="MakeHoach" class="form-select rounded-3" required>
                            @forelse($keHoachs as $kh)
                                <option value="{{ $kh->MakeHoach }}" {{ request('make_hoach') == $kh->MakeHoach ? 'selected' : '' }}>
                                    {{ $kh->TenKeHoach }} ({{ $kh->hocKy->TenHocKy ?? $kh->MakeHoach }})
                                </option>
                            @empty
                                <option value="" disabled>Chưa có kế hoạch nào trong hệ thống</option>
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                        <i class="fa-solid fa-check me-1.5"></i> Xác Nhận Đồng Bộ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* CSS Tối ưu tùy chỉnh thêm */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-7 { font-size: 0.85rem; }
    .fs-8 { font-size: 0.75rem; }
</style>
@endsection