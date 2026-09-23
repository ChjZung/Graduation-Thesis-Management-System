@extends('layouts.admin')

@section('page_title', 'Chi Tiết Lớp - ' . $lop->TenLop)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('lop.index') }}" class="text-decoration-none">Danh Mục Lớp</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $lop->MaLop }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-rectangle text-primary"></i>
                <span>Lớp {{ $lop->TenLop }}</span>
                <span class="badge-code ms-2">{{ $lop->MaLop }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('lop.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('lop.edit', $lop->MaLop) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-blue">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Sĩ Số Lớp</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_sv'] }}</div>
                        <div class="small text-muted mt-1">Sinh viên chính quy</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Đủ ĐK Khóa Luận</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['sv_du_dk'] }}</div>
                        <div class="small text-success mt-1 fw-medium"><i class="fa-solid fa-check me-1"></i>Tích lũy &ge; 115 TC</div>
                    </div>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Chưa Đủ Điều Kiện</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['sv_chua_du_dk'] }}</div>
                        <div class="small text-warning mt-1 fw-medium"><i class="fa-solid fa-clock me-1"></i>Dưới 115 tín chỉ</div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tỷ Lệ Đạt Chuẩn</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['pct_du_dk'] }}%</div>
                        <div class="small text-muted mt-1">Đủ chuẩn làm KLTN</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left: Danh sách Sinh viên -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-graduate text-primary"></i>
                        <span>Danh Sách Sinh Viên Trong Lớp ({{ $lop->sinhViens->count() }})</span>
                    </h6>
                    <a href="{{ route('sinhvien.create') }}?MaLop={{ $lop->MaLop }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-user-plus me-1"></i> Thêm Sinh Viên
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="16%">MSSV</th>
                                <th class="py-3">HỌ VÀ TÊN</th>
                                <th class="py-3 text-center" width="12%">GIỚI TÍNH</th>
                                <th class="py-3 text-center" width="14%">TÍN CHỈ</th>
                                <th class="py-3 text-center" width="14%">ĐIỂM GPA</th>
                                <th class="py-3 text-center" width="16%">ĐIỀU KIỆN KLTN</th>
                                <th class="pe-4 py-3 text-center" width="10%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lop->sinhViens as $sv)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $sv->MaSV }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $sv->HoTen }}</div>
                                        <div class="small text-muted">{{ $sv->Email ?? 'Chưa có email' }}</div>
                                    </td>
                                    <td class="text-center small text-muted">
                                        {{ $sv->GioiTinh ?? 'Nam' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ ($sv->SoTinChiTichLuy >= 115) ? 'text-success' : 'text-secondary' }}">
                                            {{ $sv->SoTinChiTichLuy ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                            {{ number_format($sv->DiemTichLuy ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if(($sv->SoTinChiTichLuy ?? 0) >= 115)
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                                <i class="fa-solid fa-check me-1"></i> Đủ ĐK
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 small">
                                                <i class="fa-solid fa-xmark me-1"></i> Chưa đủ
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết sinh viên">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có sinh viên nào trong lớp này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Thông tin Lớp Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông Tin Lớp Học
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Lớp</span>
                            <span class="badge-code">{{ $lop->MaLop }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Lớp</span>
                            <span class="fw-bold text-dark text-end">{{ $lop->TenLop }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khóa Tuyển Sinh</span>
                            <span class="text-dark fw-medium">{{ $lop->KhoaHoc ?? 'K11' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngành Đào Tạo</span>
                            <a href="{{ route('nganh.show', $lop->MaNganh) }}" class="fw-bold text-primary text-decoration-none text-end">
                                {{ $lop->nganh->TenNganh ?? $lop->MaNganh }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khoa Quản Lý</span>
                            <span class="text-dark text-end">{{ $lop->nganh->khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Đang học
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- CVHT Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Cố Vấn Học Tập (CVHT)
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-user-tie fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">Ban Cố Vấn Học Tập</div>
                            <div class="small text-muted">Khoa {{ $lop->nganh->khoa->TenKhoa ?? 'CNTT' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
