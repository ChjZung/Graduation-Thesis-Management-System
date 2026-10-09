@extends('layouts.truongbomon')

@section('page_title', 'Tổng Quan Trưởng Bộ Môn')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Greeting -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-building-columns me-2 text-primary"></i>
                Bàn Làm Việc Trưởng Bộ Môn {{ $boMon->TenBoMon ?? '' }}
            </h4>
            <p class="text-muted mb-0 small">
                Kiểm soát chuyên môn, phân công phản biện đề cương, thẩm định phê duyệt đề tài và giám sát tiến độ thực hiện.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Chờ duyệt cấp Bộ môn']) }}" class="btn btn-primary btn-sm rounded-3 shadow-sm fw-semibold">
                <i class="fa-solid fa-clipboard-check me-1"></i> Duyệt Đề Tài ({{ $stats['cho_duyet_bm'] }})
            </a>
            <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-outline-primary btn-sm rounded-3 fw-semibold">
                <i class="fa-solid fa-user-plus me-1"></i> Phân Công PB ({{ $stats['cho_phan_cong_pb'] }})
            </a>
            <a href="{{ route('truongbomon.theodoi.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold">
                <i class="fa-solid fa-list-check me-1"></i> Theo Dõi Tiến Độ
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index', ['view' => 'thongke']) }}" class="btn btn-outline-success btn-sm rounded-3 fw-semibold">
                <i class="fa-solid fa-chart-column me-1"></i> Thống Kê
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards (Đồng bộ chuẩn học thuật - Không màu sặc sỡ) -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Chờ Duyệt Đề Tài (Bộ Môn)</div>
                        <div class="kpi-value">{{ $stats['cho_duyet_bm'] }}</div>
                        <a href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Chờ duyệt cấp Bộ môn']) }}" class="small text-decoration-none text-primary fw-semibold">
                            Duyệt đề tài ngay <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Chờ Phân Công Phản Biện</div>
                        <div class="kpi-value">{{ $stats['cho_phan_cong_pb'] }}</div>
                        <a href="{{ route('truongbomon.phancong.index') }}" class="small text-decoration-none text-primary fw-semibold">
                            Phân công ngay <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Đang Phản Biện Đề Cương</div>
                        <div class="kpi-value">{{ $stats['dang_phan_bien'] }}</div>
                        <div class="kpi-subtext text-muted">Chờ GV phản biện đánh giá</div>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="admin-kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">Đã Chuyển Lên Khoa</div>
                        <div class="kpi-value">{{ $stats['cho_duyet_khoa'] }}</div>
                        <div class="kpi-subtext text-muted">Chờ Trưởng khoa duyệt</div>
                    </div>
                    <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #003366;">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Tổng Giảng Viên Bộ Môn</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['total_gv'] }}</h4>
                <div class="small text-muted">Cán bộ chuyên môn</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Tổng Đề Tài Của BM</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['total_de_tai'] }}</h4>
                <div class="small text-muted">Trong cơ sở dữ liệu</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Đã Được TK Duyệt</div>
                <h4 class="fw-bold text-success my-1">{{ $stats['truong_khoa_da_duyet'] }}</h4>
                <div class="small text-muted">Sẵn sàng công bố</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Đề Tài Đã Công Bố</div>
                <h4 class="fw-bold text-primary my-1">{{ $stats['da_cong_bo'] }}</h4>
                <div class="small text-muted">SV đang thực hiện</div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ phân tích & Thống kê Bộ môn -->
    <div class="row g-4 mb-4">
        <!-- Biểu đồ 1: Phân bổ trạng thái đề tài -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chart-pie text-primary me-2"></i>
                        Phân Bổ Trạng Thái Đề Tài
                    </h6>
                    <span class="badge bg-light text-secondary border rounded-pill small">Bộ Môn</span>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-3 p-md-4">
                    <div style="position: relative; height: 230px; width: 100%;">
                        <canvas id="tbmStatusChart"></canvas>
                    </div>
                    <div class="mt-3 small text-muted text-center">
                        Tổng hợp toàn bộ {{ $stats['total_de_tai'] }} đề tài thuộc Bộ môn
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 2: Đề tài theo Giảng viên -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chart-simple text-primary me-2"></i>
                        Đề Tài Theo Giảng Viên Hướng Dẫn
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">Bộ Môn</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="position: relative; height: 230px; width: 100%;">
                        <canvas id="tbmLecturerChart"></canvas>
                    </div>
                    <div class="mt-3 small text-muted text-center">
                        Số lượng đề tài do các cán bộ/giảng viên trong bộ môn đảm nhiệm
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Action Section: Topics Requiring Processing -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-triangle-exclamation text-primary me-2"></i>
                Đề Tài Cần Xử Lý Ngay Tại Bộ Môn
            </h6>
            <div class="d-flex gap-2">
                <a href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'can_duyet']) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                    Duyệt Đề Tài <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
                <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    Phân Công PB <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($deTaiCanXuLy->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-circle-check text-success fa-3x mb-3"></i>
                    <h6 class="text-muted">Tuyệt vời! Hiện không có đề tài nào đang tồn đọng chờ Bộ môn xử lý.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-academic">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Mã ĐT</th>
                                <th>Tên Đề Tài</th>
                                <th>GV Đề Xuất</th>
                                <th>Trạng Thái</th>
                                <th>Phản Biện Đề Cương</th>
                                <th class="text-end" style="width: 220px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deTaiCanXuLy as $dt)
                                @php
                                    $pb = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                                @endphp
                                <tr>
                                    <td><span class="badge-code">{{ $dt->MaDeTai }}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                        <div class="small text-muted">Học kỳ: {{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}</div>
                                    </td>
                                    <td>{{ $dt->giangVien->HoTen ?? 'Chưa xác định' }}</td>
                                    <td>
                                        @if($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i> Chờ duyệt BM</span>
                                        @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-spinner me-1"></i> Đang phản biện</span>
                                        @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-check-double me-1"></i> Đã phản biện</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">{{ $dt->TrangThai }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-clock me-1"></i> Chờ TBM duyệt đề xuất
                                            </span>
                                        @elseif($pb)
                                            <div class="small fw-semibold">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                            @if($pb->KetQua === 'Đạt')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.75rem;"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                            @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill" style="font-size: 0.75rem;"><i class="fa-solid fa-pen me-1"></i> Cần sửa</span>
                                            @elseif($pb->KetQua === 'Không đạt')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.75rem;"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.75rem;"><i class="fa-solid fa-hourglass-start me-1"></i> Chờ đánh giá</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.75rem;"><i class="fa-solid fa-circle-exclamation me-1"></i> Chưa phân công PB</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                            @if($dt->TrangThai === 'Chờ duyệt cấp Bộ môn' || $dt->TrangThai === 'Đã phản biện - Chờ duyệt BM')
                                                <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold text-nowrap shadow-xs">
                                                    <i class="fa-solid fa-clipboard-check me-1"></i> Duyệt đề tài
                                                </a>
                                            @elseif(!$pb)
                                                <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-sm btn-info text-white rounded-pill px-3 py-1 fw-semibold text-nowrap shadow-xs">
                                                    <i class="fa-solid fa-user-plus me-1"></i> Phân công PB
                                                </a>
                                            @endif
                                            <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 text-nowrap">
                                                <i class="fa-solid fa-eye me-1"></i> Chi tiết
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
    // 1. Doughnut Chart: Trạng thái đề tài
    const statusCtx = document.getElementById('tbmStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Chờ duyệt BM', 'Đang/Đã PB', 'Chờ Khoa duyệt', 'Đã duyệt / Công bố', 'Cần sửa / Từ chối'],
                datasets: [{
                    data: [
                        {{ $chartStatusCounts['cho_duyet_bm'] ?? 0 }},
                        {{ $chartStatusCounts['dang_phan_bien'] ?? 0 }},
                        {{ $chartStatusCounts['cho_duyet_khoa'] ?? 0 }},
                        {{ $chartStatusCounts['da_duyet_cong_bo'] ?? 0 }},
                        {{ $chartStatusCounts['can_sua_tu_choi'] ?? 0 }}
                    ],
                    backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#198754', '#dc3545'],
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

    // 2. Bar Chart: Đề tài theo giảng viên
    const gvCtx = document.getElementById('tbmLecturerChart');
    if (gvCtx) {
        new Chart(gvCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartGvLabels ?? []) !!},
                datasets: [{
                    label: 'Số đề tài',
                    data: {!! json_encode($chartGvCounts ?? []) !!},
                    backgroundColor: '#002855',
                    borderRadius: 6,
                    maxBarThickness: 32
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
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 30,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    .badge-code {
        font-family: var(--font-monospace, monospace);
        background-color: rgba(0, 40, 85, 0.08);
        color: #002855;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .table-academic thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding-top: 12px;
        padding-bottom: 12px;
    }
</style>
@endpush
