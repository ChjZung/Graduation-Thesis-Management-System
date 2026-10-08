@extends('layouts.admin')

@section('page_title', 'Quy Định & Tiêu Chuẩn Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kehoach.index') }}" class="text-decoration-none">Kế Hoạch Khóa Luận</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Quy Định &amp; Tiêu Chuẩn</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-primary"></i>
                <span>Quy Định &amp; Tiêu Chuẩn Khóa Luận Tốt Nghiệp</span>
            </h4>
            <div class="text-muted small mt-1">Quản lý các chỉ số điều kiện, tiêu chuẩn thực hiện và bảo vệ khóa luận tốt nghiệp theo quy định của Khoa &amp; Trường.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.calendar') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="fa-solid fa-calendar-week me-1"></i> Xem Lịch Quy Trình Theo Tuần
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalInitDefaults">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Nạp 8 Quy Định Chuẩn từ Kế Hoạch
            </button>
            <a href="{{ route('admin.quydinh.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="fa-solid fa-plus me-1"></i> Thêm Quy Định Mới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Tổng Quy Định</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_quydinh'] }}</div>
                        <div class="small text-success mt-1"><i class="fa-solid fa-check-double me-1"></i>Đang có hiệu lực</div>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-scale-balanced fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Kế Hoạch Áp Dụng</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['so_kehoach'] }}</div>
                        <div class="small text-primary mt-1"><i class="fa-solid fa-calendar-check me-1"></i>Đợt khóa luận</div>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-calendar-days fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Chuẩn Tín Chỉ</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ $stats['tc_chuan'] }}</div>
                        <div class="small text-muted mt-1">Điều kiện tích lũy làm KLTN</div>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-certificate fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Chuẩn Điểm Tích Lũy</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ $stats['gpa_chuan'] }}</div>
                        <div class="small text-muted mt-1">Thang điểm 4.0 tối thiểu</div>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-chart-line fs-5"></i>
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
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-3 fw-semibold">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    @if(request()->hasAny(['search', 'make_hoach']))
                        <a href="{{ route('admin.quydinh.index') }}" class="btn btn-sm btn-light border rounded-3 px-2 text-secondary" title="Xóa lọc">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Danh Sách Quy Định &amp; Tiêu Chuẩn Khóa Luận ({{ $quyDinhs->total() }} Quy định)
            </span>
            @if(request('make_hoach'))
                <span class="badge bg-light text-primary border small">Lọc theo: {{ request('make_hoach') }}</span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3" width="5%">#</th>
                        <th class="py-3" width="15%">MÃ QUY ĐỊNH</th>
                        <th class="py-3" width="30%">TÊN QUY ĐỊNH / TIÊU CHUẨN</th>
                        <th class="py-3 text-center" width="18%">GIÁ TRỊ THAM SỐ</th>
                        <th class="py-3" width="20%">KẾ HOẠCH ÁP DỤNG</th>
                        <th class="pe-4 py-3 text-center" width="12%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quyDinhs as $idx => $qd)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $quyDinhs->firstItem() + $idx }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $qd->MaQuyDinh }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $qd->TenQuyDinh }}</div>
                                @if($qd->MoTa)
                                    <div class="small text-muted mt-1" style="line-height: 1.4;">
                                        {{ $qd->MoTa }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fw-bold fs-7 rounded-pill">
                                    {{ $qd->GiaTri }}
                                </span>
                            </td>
                            <td>
                                @if($qd->keHoachKhoaLuan)
                                    <div class="fw-semibold text-dark small">{{ $qd->keHoachKhoaLuan->TenKeHoach }}</div>
                                    <div class="small text-muted"><i class="fa-regular fa-calendar me-1"></i>{{ $qd->keHoachKhoaLuan->hocKy->TenHocKy ?? '' }}</div>
                                @else
                                    <span class="text-muted small">Quy chế áp dụng chung</span>
                                @endif
                            </td>
                            <td class="pe-4 text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.quydinh.show', $qd->MaQuyDinh) }}" class="btn btn-sm btn-light border text-primary rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.quydinh.edit', $qd->MaQuyDinh) }}" class="btn btn-sm btn-light border text-warning rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.quydinh.destroy', $qd->MaQuyDinh) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa quy định này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Xóa quy định">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fa-solid fa-scale-unbalanced text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Chưa có quy định nào được thiết lập cho kế hoạch này</h6>
                                    <p class="small text-muted mb-3">Bạn có thể tự động nạp 8 quy định chuẩn của Khoa CNTT hoặc tạo mới quy định thủ công.</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalInitDefaults">
                                            <i class="fa-solid fa-arrows-rotate me-1"></i> Nạp 8 Quy Định Chuẩn Ngay
                                        </button>
                                        <a href="{{ route('admin.quydinh.create') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                                            <i class="fa-solid fa-plus me-1"></i> Thêm Quy Định Thủ Công
                                        </a>
                                    </div>
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

<!-- Modal Nạp 8 Quy Định Chuẩn -->
<div class="modal fade" id="modalInitDefaults" tabindex="-1" aria-labelledby="modalInitDefaultsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('admin.quydinh.initDefaults') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-primary" id="modalInitDefaultsLabel">
                        <i class="fa-solid fa-scale-balanced me-2"></i>Nạp 8 Quy Định &amp; Tiêu Chuẩn Chuẩn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Hệ thống sẽ đồng bộ <strong>8 Quy định &amp; Tiêu chuẩn chuẩn</strong> (Tín chỉ tối thiểu: 115 TC, GPA: 2.0, Nhóm 2-3 SV, Định mức GVHD: 5 đề tài, Turnitin &le; 20%, Điểm đạt &ge; 5.0, Thời gian: 12 tuần, Hồ sơ nộp) vào Kế hoạch khóa luận được chọn:
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Chọn Kế Hoạch Khóa Luận Áp Dụng: <span class="text-danger">*</span></label>
                        <select name="MakeHoach" class="form-select" required>
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
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Đồng Bộ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
