@extends('layouts.admin')

@section('page_title', 'Chi Tiết Ngành - ' . $nganh->TenNganh)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('nganh.index') }}" class="text-decoration-none">Danh Mục Ngành</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $nganh->MaNganh }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-book-open text-primary"></i>
                <span>{{ $nganh->TenNganh }}</span>
                <span class="badge-code ms-2">{{ $nganh->MaNganh }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('nganh.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('nganh.edit', $nganh->MaNganh) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
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
                        <div class="text-muted small fw-medium">Tổng Lớp Học</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_lop'] }}</div>
                        <div class="small text-muted mt-1">Lớp hành chính</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Sinh Viên</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_sv'] }}</div>
                        <div class="small text-muted mt-1">Đang theo học</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Đủ Điều Kiện KLTN</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['sv_du_dk'] }}</div>
                        <div class="small text-success mt-1 fw-medium"><i class="fa-solid fa-check-circle me-1"></i>Tích lũy &ge; 115 tín chỉ</div>
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
                        <div class="text-muted small fw-medium">Tỷ Lệ Đạt Chuẩn KLTN</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['pct_du_dk'] }}%</div>
                        <div class="small text-muted mt-1">Trên tổng số sinh viên</div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left Column: Danh sách Lớp hành chính -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-users-rectangle text-primary"></i>
                        <span>Danh Sách Lớp Hành Chính Trực Thuộc</span>
                    </h6>
                    <a href="{{ route('lop.create') }}?MaNganh={{ $nganh->MaNganh }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Lớp Mới
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="18%">MÃ LỚP</th>
                                <th class="py-3">TÊN LỚP</th>
                                <th class="py-3" width="18%">KHÓA TUYỂN SINH</th>
                                <th class="py-3 text-center" width="18%">SĨ SỐ SINH VIÊN</th>
                                <th class="pe-4 py-3 text-center" width="15%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nganh->lops as $lop)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $lop->MaLop }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $lop->TenLop }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                            {{ $lop->KhoaHoc ?? 'K11-K14' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border rounded-pill px-3 py-1 fw-bold">
                                            {{ $lop->sinh_viens_count ?? 0 }} SV
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('lop.show', $lop->MaLop) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết lớp">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('lop.edit', $lop->MaLop) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có lớp hành chính nào thuộc ngành đào tạo này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Chuyên ngành (nếu có) -->
            @if($nganh->chuyenNganhs && $nganh->chuyenNganhs->count() > 0)
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-diagram-project text-primary"></i>
                            <span>Chuyên Ngành Đào Tạo Hẹp</span>
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($nganh->chuyenNganhs as $cn)
                                <span class="badge bg-light text-dark border p-2 rounded-3">
                                    <i class="fa-solid fa-circle-dot text-primary me-1"></i> {{ $cn->TenChuyenNganh ?? $cn->MaChuyenNganh }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Info Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông Tin Ngành Đào Tạo
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Ngành</span>
                            <span class="badge-code">{{ $nganh->MaNganh }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Ngành</span>
                            <span class="fw-bold text-dark text-end">{{ $nganh->TenNganh }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khoa Quản Lý</span>
                            <a href="{{ route('khoa.show', $nganh->MaKhoa) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $nganh->khoa->TenKhoa ?? $nganh->MaKhoa }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Chuẩn Tín Chỉ Tối Thiểu</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">115 Tín chỉ</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Hoạt động
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Ghi chú đào tạo -->
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="fa-solid fa-graduation-cap me-1"></i> Quy chế Khóa luận tốt nghiệp
                    </h6>
                    <p class="small text-muted mb-0">
                        Sinh viên ngành này cần tích lũy tối thiểu 115 tín chỉ và không bị nợ các môn điều kiện tiên quyết để được cấp quyền lập nhóm đăng ký đề tài KLTN.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
