@extends('layouts.admin')

@section('page_title', 'Chi Tiết Học Kỳ - ' . $hocky->TenHocKy)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hocky.index') }}" class="text-decoration-none">Danh Mục Học Kỳ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $hocky->MaHocKy }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock text-primary"></i>
                <span>{{ $hocky->TenHocKy }} - Năm Học {{ $hocky->NamHoc }}</span>
                <span class="badge-code ms-2">{{ $hocky->MaHocKy }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('hocky.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('hocky.edit', $hocky->MaHocKy) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
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
                        <div class="text-muted small fw-medium">Kế Hoạch Khóa Luận</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_kehoach'] }}</div>
                        <div class="small text-muted mt-1">Đã ban hành</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Đề Tài KLTN</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_detai'] }}</div>
                        <div class="small text-muted mt-1">Đăng ký trong kỳ</div>
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
                        <div class="text-muted small fw-medium">Hội Đồng Bảo Vệ</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_hoidong'] }}</div>
                        <div class="small text-muted mt-1">Được thành lập</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">SV Đủ Điều Kiện</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_sv'] }}</div>
                        <div class="small text-muted mt-1">Xét duyệt trong kỳ</div>
                    </div>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left Column: Kế hoạch Khóa luận trong kỳ -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-primary"></i>
                        <span>Kế Hoạch Khóa Luận Trong Học Kỳ</span>
                    </h6>
                    <a href="{{ route('admin.kehoach.create') }}?MaHocKy={{ $hocky->MaHocKy }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-plus me-1"></i> Lập Kế Hoạch Mới
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" width="18%">MÃ KH</th>
                                <th class="py-3">TÊN KẾ HOẠCH</th>
                                <th class="py-3 text-center" width="15%">SỐ MỐC</th>
                                <th class="py-3 text-center" width="20%">TRẠNG THÁI</th>
                                <th class="pe-4 py-3 text-center" width="12%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hocky->keHoachKhoaLuans as $kh)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge-code">{{ $kh->MakeHoach }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $kh->TenKeHoach }}</div>
                                        <div class="small text-muted">
                                            {{ $kh->NgayBatDau ? \Carbon\Carbon::parse($kh->NgayBatDau)->format('d/m/Y') : '' }}
                                            &rarr;
                                            {{ $kh->NgayKetThuc ? \Carbon\Carbon::parse($kh->NgayKetThuc)->format('d/m/Y') : '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                            {{ $kh->mocThoiGians ? $kh->mocThoiGians->count() : 5 }} Mốc
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($kh->TrangThai === 'ĐÃ CÔNG BỐ')
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Đã công bố
                                            </span>
                                        @elseif($kh->TrangThai === 'ĐANG THỰC HIỆN')
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-spinner fa-spin me-1 small"></i> Đang thực hiện
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                                                {{ $kh->TrangThai ?? 'Bản nháp' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="{{ route('admin.kehoach.show', $kh->MakeHoach) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết kế hoạch">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-calendar-xmark mb-2" style="font-size: 1.5rem;"></i>
                                        <div>Chưa có kế hoạch khóa luận nào được tạo trong học kỳ này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Info Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông Tin Học Kỳ
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Học Kỳ</span>
                            <span class="badge-code">{{ $hocky->MaHocKy }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Tên Học Kỳ</span>
                            <span class="fw-bold text-dark text-end">{{ $hocky->TenHocKy }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Năm Học</span>
                            <span class="text-dark fw-bold">{{ $hocky->NamHoc }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngày Bắt Đầu</span>
                            <span class="text-dark">{{ $hocky->NgayBatDau ? \Carbon\Carbon::parse($hocky->NgayBatDau)->format('d/m/Y') : 'Chưa thiết lập' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngày Kết Thúc</span>
                            <span class="text-dark">{{ $hocky->NgayKetThuc ? \Carbon\Carbon::parse($hocky->NgayKetThuc)->format('d/m/Y') : 'Chưa thiết lập' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái</span>
                            @if($hocky->TrangThai)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle me-1 small" style="font-size: 0.5rem;"></i> Đang hoạt động
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                                    Đã kết thúc
                                </span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Lịch toàn diện Link -->
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-3 text-center">
                    <i class="fa-regular fa-calendar-days text-primary fs-3 mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1">Lịch Quy Trình Đào Tạo</h6>
                    <p class="small text-muted mb-3">Xem toàn bộ lịch trình FullCalendar và ma trận tiến độ của học kỳ này.</p>
                    <a href="{{ route('admin.calendar') }}" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Lịch Toàn Trường
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
