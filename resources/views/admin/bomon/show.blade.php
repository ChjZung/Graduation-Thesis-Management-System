@extends('layouts.admin')

@section('page_title', 'Chi Tiết Bộ Môn - ' . $bomon->TenBoMon)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bomon.index') }}" class="text-decoration-none">Danh Mục Bộ Môn</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $bomon->MaBoMon }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-building text-primary"></i>
                <span>{{ $bomon->TenBoMon }}</span>
                <span class="badge-code ms-2">{{ $bomon->MaBoMon }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bomon.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('bomon.edit', $bomon->MaBoMon) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
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
                        <div class="text-muted small fw-medium">Tổng Giảng Viên</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_gv'] }}</div>
                        <div class="small text-muted mt-1">Biên chế bộ môn</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">GV Hướng Dẫn KLTN</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['gv_huong_dan'] }}</div>
                        <div class="small text-muted mt-1">Có nhận đề tài</div>
                    </div>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Số Đề Tài</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_detai'] }}</div>
                        <div class="small text-muted mt-1">Đã đề xuất & thực hiện</div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Khoa Trực Thuộc</div>
                        <div class="fs-5 fw-bold text-dark mt-1 text-truncate" style="max-width: 160px;" title="{{ $bomon->khoa->TenKhoa ?? 'Chưa rõ' }}">
                            {{ $bomon->khoa->TenKhoa ?? 'Chưa rõ' }}
                        </div>
                        <div class="small text-muted mt-1"><span class="badge-code">{{ $bomon->MaKhoa }}</span></div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-university"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left: Giảng viên trực thuộc table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chalkboard-user text-primary"></i>
                        <span>Đội Ngũ Giảng Viên Trực Thuộc</span>
                    </h6>
                    <a href="{{ route('giangvien.create') }}?MaBoMon={{ $bomon->MaBoMon }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-user-plus me-1"></i> Thêm Giảng Viên
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="15%">MÃ GV</th>
                                <th class="py-3">HỌ VÀ TÊN</th>
                                <th class="py-3" width="15%">HỌC VỊ</th>
                                <th class="py-3 text-center" width="15%">ĐỀ TÀI HD</th>
                                <th class="py-3 text-center" width="15%">TRẠNG THÁI</th>
                                <th class="pe-4 py-3 text-center" width="12%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bomon->giangViens as $gv)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $gv->MaGV }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $gv->HoTen }}</div>
                                        <div class="small text-muted">{{ $gv->Email ?? 'Chưa cập nhật email' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                            {{ $gv->HocVi ?? 'Giảng viên' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                            {{ $gv->de_tais_count ?? 0 }} đề tài
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                            {{ $gv->TrangThai ?? 'Đang công tác' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có giảng viên nào thuộc bộ môn này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Thông tin Bộ Môn Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông Tin Bộ Môn
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Bộ Môn</span>
                            <span class="badge-code">{{ $bomon->MaBoMon }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Bộ Môn</span>
                            <span class="fw-bold text-dark text-end">{{ $bomon->TenBoMon }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khoa Quản Lý</span>
                            <a href="{{ route('khoa.show', $bomon->MaKhoa) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $bomon->khoa->TenKhoa ?? $bomon->MaKhoa }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Hoạt động
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngày Khởi Tạo</span>
                            <span class="text-dark">{{ $bomon->created_at ? $bomon->created_at->format('d/m/Y') : 'Mặc định' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Hướng dẫn & Lưu ý -->
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="fa-solid fa-lightbulb me-1"></i> Nghiệp vụ Khóa luận
                    </h6>
                    <p class="small text-muted mb-0">
                        Giảng viên thuộc bộ môn sẽ đề xuất đề tài thuộc định hướng chuyên ngành của bộ môn và thẩm định đề cương khóa luận sinh viên trước khi gửi lên Hội đồng.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
