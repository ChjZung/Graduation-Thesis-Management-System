@extends('layouts.admin')

@section('page_title', 'Chi Tiết Khoa - ' . $khoa->TenKhoa)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('khoa.index') }}" class="text-decoration-none">Danh Mục Khoa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $khoa->MaKhoa }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-university text-primary"></i>
                <span>{{ $khoa->TenKhoa }}</span>
                <span class="badge-code ms-2">{{ $khoa->MaKhoa }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('khoa.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('khoa.edit', $khoa->MaKhoa) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
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
                        <div class="text-muted small fw-medium">Tổng Bộ Môn</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_bomon'] }}</div>
                        <div class="small text-muted mt-1">Trực thuộc khoa</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Ngành Đào Tạo</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_nganh'] }}</div>
                        <div class="small text-muted mt-1">Chương trình chính quy</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Lớp Hành Chính</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_lop'] }}</div>
                        <div class="small text-muted mt-1">Đang hoạt động</div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Giảng Viên</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_gv'] }}</div>
                        <div class="small text-muted mt-1">{{ $stats['total_sv'] }} sinh viên</div>
                    </div>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left Column: Department & Major Lists -->
        <div class="col-lg-8">
            <!-- Bộ môn trực thuộc -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building text-primary"></i>
                        <span>Danh Sách Bộ Môn Trực Thuộc</span>
                    </h6>
                    <a href="{{ route('bomon.create') }}?MaKhoa={{ $khoa->MaKhoa }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Bộ Môn
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="20%">MÃ BỘ MÔN</th>
                                <th class="py-3">TÊN BỘ MÔN</th>
                                <th class="py-3 text-center" width="20%">SỐ GIẢNG VIÊN</th>
                                <th class="pe-4 py-3 text-center" width="15%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($khoa->boMons as $bm)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $bm->MaBoMon }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $bm->TenBoMon }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-user-tie me-1"></i> {{ $bm->giang_viens_count ?? 0 }} GV
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('bomon.show', $bm->MaBoMon) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('bomon.edit', $bm->MaBoMon) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có bộ môn nào trực thuộc khoa này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ngành học trực thuộc -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-book-open text-primary"></i>
                        <span>Danh Sách Ngành Đào Tạo</span>
                    </h6>
                    <a href="{{ route('nganh.create') }}?MaKhoa={{ $khoa->MaKhoa }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Ngành
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="20%">MÃ NGÀNH</th>
                                <th class="py-3">TÊN NGÀNH</th>
                                <th class="py-3 text-center" width="20%">SỐ LỚP HỌC</th>
                                <th class="pe-4 py-3 text-center" width="15%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($khoa->nganhs as $nganh)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $nganh->MaNganh }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $nganh->TenNganh }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-info border rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-users-rectangle me-1"></i> {{ $nganh->lops_count ?? 0 }} Lớp
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('nganh.show', $nganh->MaNganh) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('nganh.edit', $nganh->MaNganh) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có ngành học nào trực thuộc khoa này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: General Info & Officials -->
        <div class="col-lg-4">
            <!-- Thông tin Khoa Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông Tin Khoa
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Khoa</span>
                            <span class="badge-code">{{ $khoa->MaKhoa }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Khoa</span>
                            <span class="fw-bold text-dark text-end">{{ $khoa->TenKhoa }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Hoạt động
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngày Khởi Tạo</span>
                            <span class="text-dark">{{ $khoa->created_at ? $khoa->created_at->format('d/m/Y') : 'Mặc định hệ thống' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Ban Giáo Vụ / Phụ Trách -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-user-shield text-primary me-2"></i>Ban Quản Lý & Giáo Vụ
                    </h6>
                </div>
                <div class="card-body p-3">
                    @forelse($khoa->giaoVus as $gv)
                        <div class="d-flex align-items-center gap-3 p-2 rounded bg-light mb-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">{{ $gv->HoTen ?? 'Cán bộ giáo vụ' }}</div>
                                <div class="small text-muted text-truncate">{{ $gv->Email ?? $gv->ChucVu ?? 'Giáo vụ khoa' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted small">
                            <i class="fa-regular fa-id-badge mb-2" style="font-size: 1.5rem;"></i>
                            <div>Chưa gán thông tin cán bộ giáo vụ riêng cho khoa này.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
