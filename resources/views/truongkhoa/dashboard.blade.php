@extends('layouts.truongkhoa')

@section('page_title', 'Bàn Làm Việc Trưởng Khoa')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Greeting -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>
                Bàn Làm Việc Trưởng Khoa – {{ $khoa->TenKhoa ?? 'Khoa Công Nghệ Thông Tin' }}
            </h4>
            <p class="text-muted mb-0 small">
                Phê duyệt danh mục đề tài khóa luận cấp Khoa và giám sát chất lượng đào tạo, tiến độ, kết quả toàn khoa.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="btn btn-primary btn-sm rounded-3 shadow-sm fw-semibold">
                <i class="fa-solid fa-stamp me-1"></i> Phê Duyệt Đề Tài ({{ $stats['cho_duyet_khoa'] }})
            </a>
            <a href="{{ route('truongkhoa.theodoi.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-chart-line me-1"></i> Theo Dõi Tiến Độ
            </a>
            <a href="{{ route('truongkhoa.kehoach.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-calendar-check me-1"></i> Kế Hoạch Khóa Luận
            </a>
            <a href="{{ route('truongkhoa.ketqua.index') }}" class="btn btn-outline-success btn-sm">
                <i class="fa-solid fa-award me-1"></i> Kết Quả Toàn Khoa
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards (Đồng bộ chuẩn học thuật - Không màu sặc sỡ) -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Chờ Trưởng Khoa Duyệt</div>
                        <div class="kpi-value">{{ $stats['cho_duyet_khoa'] }}</div>
                        <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="small text-decoration-none text-primary fw-semibold">
                            Xem phê duyệt ngay <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Đề Tài Đã Công Bố</div>
                        <div class="kpi-value">{{ $stats['da_cong_bo'] }}</div>
                        <div class="kpi-subtext text-muted">Sinh viên được phép đăng ký</div>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Đang Xử Lý Tại Bộ Môn</div>
                        <div class="kpi-value">{{ $stats['cho_duyet_bm'] + $stats['dang_phan_bien'] }}</div>
                        <div class="kpi-subtext text-muted">Bộ môn thẩm định chuyên môn</div>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Tổng Đề Tài Toàn Khoa</div>
                        <div class="kpi-value">{{ $stats['total_detai'] }}</div>
                        <div class="kpi-subtext text-muted">Khóa luận học kỳ hiện tại</div>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Faculty Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Bộ Môn Trực Thuộc</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['so_bo_mon'] }}</h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Giảng Viên Toàn Khoa</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['so_giang_vien'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Sinh Viên Đủ ĐK Khóa Luận</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['so_sinh_vien'] }}</h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Nhóm Khóa Luận</div>
                <h4 class="fw-bold text-primary my-1">{{ $stats['so_nhom'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Hội Đồng Bảo Vệ</div>
                <h4 class="fw-bold text-success my-1">{{ $stats['so_hoi_dong'] }}</h4>
            </div>
        </div>
    </div>

    <!-- Biểu đồ phân tích & Thống kê Khoa -->
    <div class="row g-4 mb-4">
        <!-- Biểu đồ 1: Đề tài theo Bộ môn -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chart-column text-primary me-2"></i>
                        Phân Bổ Đề Tài Theo Các Bộ Môn Trực Thuộc Khoa
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">Toàn Khoa</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="position: relative; height: 240px; width: 100%;">
                        <canvas id="tkBoMonChart"></canvas>
                    </div>
                    <div class="mt-3 small text-muted text-center">
                        Số lượng đề tài do các Bộ môn thuộc khoa triển khai
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 2: Trạng thái đề tài cấp Khoa -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chart-pie text-danger me-2"></i>
                        Trạng Thái Phê Duyệt Cấp Khoa
                    </h6>
                    <span class="badge bg-light text-secondary border rounded-pill small">Tiến Độ Thẩm Định</span>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-3 p-md-4">
                    <div style="position: relative; height: 240px; width: 100%;">
                        <canvas id="tkStatusChart"></canvas>
                    </div>
                    <div class="mt-3 small text-muted text-center">
                        Tổng hợp tiến độ phê duyệt trên tổng số {{ $stats['so_de_tai'] }} đề tài của Khoa
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Topics Awaiting Faculty Approval -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-stamp text-danger me-2"></i>
                Đề Tài Đang Chờ Trưởng Khoa Phê Duyệt
            </h6>
            <a href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'dang_xet_duyet']) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold">
                Xem Tất Cả ({{ $stats['cho_duyet_khoa'] }}) <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            @if($deTaiCanDuyet->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-circle-check text-success fa-3x mb-3"></i>
                    <h6 class="text-muted">Hiện tại không có đề tài nào đang chờ Trưởng khoa phê duyệt!</h6>
                    <div class="small text-muted mt-1">Tất cả đề tài cấp Bộ môn chuyển lên đã được xử lý.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã ĐT</th>
                                <th>Tên Đề Tài &amp; Bộ Môn</th>
                                <th>GV Hướng Dẫn</th>
                                <th>Ý Kiến Phản Biện Đề Cương</th>
                                <th>Ngày TBM Duyệt</th>
                                <th class="text-end" style="width: 160px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deTaiCanDuyet as $dt)
                                @php
                                    $pb = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                                @endphp
                                <tr>
                                    <td class="fw-semibold text-danger">{{ $dt->MaDeTai }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-building-columns me-1"></i>{{ $dt->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>{{ $dt->giangVien->HoTen ?? 'N/A' }}</td>
                                    <td>
                                        @if($pb)
                                            <div class="small fw-semibold">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                            @if($pb->KetQua === 'Đạt')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.72rem;"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                            @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill" style="font-size: 0.72rem;">Cần sửa</span>
                                            @elseif($pb->KetQua === 'Không đạt')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.72rem;">Không đạt</span>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.72rem;">Chưa đánh giá</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Chưa ghi nhận</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $dt->NgayDuyetBM ? \Carbon\Carbon::parse($dt->NgayDuyetBM)->format('d/m/Y H:i') : 'Đã duyệt' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                            <a href="{{ route('truongkhoa.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold text-nowrap shadow-xs">
                                                <i class="fa-solid fa-stamp me-1"></i> Xem &amp; Duyệt
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Bar Chart: Đề tài theo Bộ môn
    const bmCtx = document.getElementById('tkBoMonChart');
    if (bmCtx) {
        new Chart(bmCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartBmLabels ?? []) !!},
                datasets: [{
                    label: 'Số đề tài',
                    data: {!! json_encode($chartBmCounts ?? []) !!},
                    backgroundColor: '#002855',
                    borderRadius: 6,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    x: {
                        ticks: { font: { size: 11 }, maxRotation: 25, minRotation: 0 }
                    }
                }
            }
        });
    }

    // 2. Doughnut Chart: Trạng thái phê duyệt cấp Khoa
    const statusCtx = document.getElementById('tkStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Chờ Khoa duyệt', 'TK đã duyệt', 'Đã công bố', 'Đang xử lý tại BM', 'Cần sửa / Từ chối'],
                datasets: [{
                    data: [
                        {{ $chartStatusKhoa['cho_duyet_khoa'] ?? 0 }},
                        {{ $chartStatusKhoa['truong_khoa_duyet'] ?? 0 }},
                        {{ $chartStatusKhoa['da_cong_bo'] ?? 0 }},
                        {{ $chartStatusKhoa['dang_xu_ly_bm'] ?? 0 }},
                        {{ $chartStatusKhoa['can_sua_tu_choi'] ?? 0 }}
                    ],
                    backgroundColor: ['#dc3545', '#198754', '#0d6efd', '#ffc107', '#6c757d'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush
