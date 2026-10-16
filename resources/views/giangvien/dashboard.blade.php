@extends('layouts.giangvien')

@section('page_title', 'Tổng Quan Giảng Viên Hướng Dẫn')

@push('styles')
<style>
    /* CSS Tùy chỉnh Giao diện Clean & Professional */
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
    }

    .kpi-card {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 0.85rem;
        padding: 1.25rem;
        transition: all 0.2s ease-in-out;
    }
    
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    }

    .kpi-icon-avatar {
        width: 46px;
        height: 46px;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .card-dashboard {
        border: 1px solid var(--gv-border);
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .card-dashboard .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid var(--gv-border);
        padding: 1rem 1.25rem;
    }

    /* Nút Review AI nổi bật */
    .btn-ai-review {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #ffffff !important;
        border: none;
        transition: opacity 0.2s ease;
    }

    .btn-ai-review:hover {
        opacity: 0.9;
        color: #ffffff;
    }

    /* CSS Bảng */
    .table-modern tbody tr {
        transition: background-color 0.15s ease;
    }
    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Cắt chữ tiêu đề dài */
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<!-- Header & Welcome Banner -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Tổng Quan Giảng Viên Hướng Dẫn
        </h3>
        <p class="text-muted small mb-0">Quản lý danh sách nhóm hướng dẫn, tiến độ báo cáo và lịch họp.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('giangvien.detai.create') }}" class="btn btn-primary rounded-pill px-3 py-2 shadow-sm fw-medium">
            <i class="fa-solid fa-plus me-1.5"></i> Đề xuất Đề tài mới
        </a>
        <a href="{{ route('giangvien.calendar') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-medium">
            <i class="fa-regular fa-calendar-days me-1.5"></i> Lịch của tôi
        </a>
    </div>
</div>

<!-- 4 Stat Cards (Cập nhật giao diện thẻ mượt mà, chuyên nghiệp) -->
<div class="row g-3 mb-4">
    <!-- Stat 1: Đề tài đảm nhận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-medium mb-1">Đề tài đảm nhận</div>
                    <div class="h2 fw-bold text-dark mb-1">{{ $soDeTai }}</div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                        Đang mở: {{ $deTaiDangMo }}
                    </span>
                </div>
                <div class="kpi-icon-avatar bg-primary-subtle text-primary">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Nhóm HD chính thức -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="w-100 me-2">
                    <div class="text-muted small fw-medium mb-1">Nhóm HD chính thức</div>
                    <div class="h2 fw-bold text-dark mb-1">{{ $soNhomHD }} <span class="fs-6 text-muted fw-normal">/ {{ $soNhomToiDa }} nhóm</span></div>
                    @php
                        $percentHD = min(100, round(($soNhomHD / max(1, $soNhomToiDa)) * 100));
                    @endphp
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ $percentHD }}%;"></div>
                    </div>
                    <div class="text-muted mt-1.5" style="font-size: 0.78rem;">{{ $percentHD }}% chỉ tiêu phân bổ</div>
                </div>
                <div class="kpi-icon-avatar bg-info-subtle text-info">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Báo cáo chờ đánh giá -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-medium mb-1">Báo cáo chờ đánh giá</div>
                    <div class="h2 fw-bold text-dark mb-1">{{ $soBaoCaoChoDuyet }}</div>
                    @if($soBaoCaoChoDuyet > 0)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                            <i class="fa-solid fa-bell me-1"></i>Cần xử lý
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            <i class="fa-solid fa-check me-1"></i>Đã duyệt hết
                        </span>
                    @endif
                </div>
                <div class="kpi-icon-avatar bg-warning-subtle text-warning">
                    <i class="fa-solid fa-file-circle-exclamation"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 4: Nhóm rủi ro trễ tiến độ -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-medium mb-1">Nhóm rủi ro trễ tiến độ</div>
                    <div class="h2 fw-bold text-dark mb-1">{{ $soNhomTreHan }}</div>
                    @if($soNhomTreHan > 0)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Cần đôn đốc
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            <i class="fa-solid fa-shield-check me-1"></i>Đúng tiến độ
                        </span>
                    @endif
                </div>
                <div class="kpi-icon-avatar bg-danger-subtle text-danger">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Biểu Đồ Thống Kê & Phân Tích Tiến Độ Nhóm Hướng Dẫn -->
<div class="row g-4 mb-4">
    <!-- Biểu đồ 1: Tiến độ các mốc của từng nhóm -->
    <div class="col-lg-7">
        <div class="card card-dashboard h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-chart-simple text-primary me-2"></i>
                    Tiến Độ Mốc Báo Cáo Của Từng Nhóm Hướng Dẫn
                </h6>
                <span class="badge bg-light text-secondary border rounded-pill small">Mục Tiêu 5 Mốc</span>
            </div>
            <div class="card-body p-3 p-md-4">
                <div style="position: relative; height: 230px; width: 100%;">
                    <canvas id="gvGroupProgressChart"></canvas>
                </div>
                <div class="mt-3 small text-muted text-center">
                    Số lượng mốc báo cáo đã hoàn thành đạt chuẩn trên quy chuẩn 5 mốc
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ 2: Tình trạng đánh giá báo cáo -->
    <div class="col-lg-5">
        <div class="card card-dashboard h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-chart-pie text-success me-2"></i>
                    Tình Trạng Đánh Giá Báo Cáo
                </h6>
                <span class="badge bg-light text-secondary border rounded-pill small">Tiến Độ Thẩm Định</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-3 p-md-4">
                <div style="position: relative; height: 230px; width: 100%;">
                    <canvas id="gvReportStatusChart"></canvas>
                </div>
                <div class="mt-3 small text-muted text-center">
                    Tổng hợp tình trạng các bài nộp của {{ $soNhomHD }} nhóm đang hướng dẫn
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content: 2 Columns -->
<div class="row g-4">
    <!-- Cột Trái (8 phần): Nhóm SV Đang Hướng Dẫn -->
    <div class="col-lg-8">
        <div class="card card-dashboard mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-users text-primary"></i>
                    <h5 class="fw-bold mb-0 text-dark">Nhóm Sinh Viên Đang Hướng Dẫn</h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill fw-semibold">{{ $nhoms->count() }}</span>
                </div>
                <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold">
                    Xem tất cả tiến độ →
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-modern align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-3" width="12%">Mã nhóm</th>
                            <th width="32%">Đề tài hướng dẫn</th>
                            <th width="20%">Trưởng nhóm</th>
                            <th width="16%">Tiến độ</th>
                            <th width="20%" class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nhoms as $nhom)
                        @php
                            $soBaoCaoDat = $nhom->baoCaos->where('TrangThai', 'Đạt')->count();
                            $percentTienDo = min(100, round(($soBaoCaoDat / 5) * 100));
                            $lastBaoCao = $nhom->baoCaos->first();
                            $truongNhomSV = $nhom->thanhViens->firstWhere('VaiTro', 'Trưởng nhóm')?->sinhVien 
                                ?? $nhom->thanhViens->first()?->sinhVien;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-light text-dark fw-bold border">
                                    {{ $nhom->MaNhom }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark line-clamp-1" title="{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}">
                                    {{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}
                                </div>
                                <div class="small text-muted">
                                    {{ $nhom->thanhViens->count() }} thành viên | Nhóm: {{ $nhom->TenNhom }}
                                </div>
                            </td>
                            <td>
                                @if($truongNhomSV)
                                    <div class="fw-semibold text-dark">{{ $truongNhomSV->HoTen }}</div>
                                    <small class="text-muted">MSSV: {{ $truongNhomSV->MaSV }}</small>
                                @else
                                    <span class="text-muted fst-italic">Chưa có trưởng nhóm</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success rounded-pill" style="width: {{ $percentTienDo }}%;"></div>
                                    </div>
                                    <span class="small fw-bold text-dark">{{ $percentTienDo }}%</span>
                                </div>
                                <div class="small text-muted" style="font-size: 0.72rem;">
                                    Đã duyệt: {{ $soBaoCaoDat }}/5 mốc
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    @if($lastBaoCao)
                                        <a href="{{ route('giangvien.baocao.show', $lastBaoCao->MaBaoCao) }}" 
                                           class="btn btn-sm btn-ai-review rounded-pill px-2.5 d-inline-flex align-items-center gap-1 shadow-sm">
                                            <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                            <span class="small fw-bold">Review (AI)</span>
                                        </a>
                                    @else
                                        <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-sm btn-light border rounded-pill px-2.5">
                                            <i class="fa-regular fa-eye me-1"></i>Chi tiết
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-0">Hiện tại chưa có nhóm nào được phân công hướng dẫn.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cột Phải (4 phần): Lịch Làm Việc & Hội Đồng -->
    <div class="col-lg-4">
        <!-- Lịch Làm Việc & Hội Đồng -->
        <div class="card card-dashboard mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-primary"></i>
                    <h6 class="fw-bold mb-0 text-dark">Lịch Làm Việc & Hội Đồng</h6>
                </div>
                <a href="{{ route('giangvien.calendar') }}" class="text-primary small fw-semibold text-decoration-none">
                    Xem lịch →
                </a>
            </div>
            <div class="card-body p-3">
                <!-- Danh sách lịch hẹn sắp tới -->
                <div class="d-flex flex-column gap-3">
                    @forelse($lichGaps as $lich)
                    <div class="d-flex gap-3 p-2 rounded-3 bg-light border-start border-4 border-primary">
                        <div class="text-center px-2 py-1 bg-white rounded shadow-xs border flex-shrink-0" style="min-width: 50px;">
                            <div class="fw-bold text-primary small">{{ \Carbon\Carbon::parse($lich->ThoiGianBatDau)->format('d/m') }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($lich->ThoiGianBatDau)->format('H:i') }}</div>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-dark text-truncate small mb-1">
                                {{ $lich->NoiDung ?? 'Họp tiến độ hướng dẫn' }}
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <span><i class="fa-solid fa-users me-1 text-secondary"></i>Nhóm {{ $lich->MaNhom }}</span>
                                <span><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $lich->DiaDiem ?? 'Phòng Lab' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-2 rounded-3 bg-light border-start border-4 border-primary">
                        <div class="fw-semibold small text-dark mb-1">
                            <i class="fa-solid fa-clock me-1 text-primary"></i>14:00 - 15:30 (18/03)
                        </div>
                        <div class="small text-muted">Họp tiến độ Khóa luận đợt 2 (Phòng Lab 3)</div>
                    </div>
                    @endforelse

                    @forelse($hoiDongs as $hd)
                    <div class="d-flex gap-3 p-2 rounded-3 bg-light border-start border-4 border-success">
                        <div class="text-center px-2 py-1 bg-white rounded shadow-xs border flex-shrink-0" style="min-width: 50px;">
                            <div class="fw-bold text-success small">{{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('d/m') }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i') }}</div>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-dark text-truncate small mb-1">
                                {{ $hd->TenHoiDong }}
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <span><i class="fa-solid fa-landmark me-1 text-success"></i>Hội đồng bảo vệ</span>
                                <span><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $hd->DiaDiem ?? 'Hội trường B' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-2 rounded-3 bg-light border-start border-4 border-success">
                        <div class="fw-semibold small text-dark mb-1">
                            <i class="fa-solid fa-award me-1 text-success"></i>08:00 - 11:30 (25/03)
                        </div>
                        <div class="small text-muted">Hội đồng phản biện đợt 1 (Hội trường B)</div>
                    </div>
                    @endforelse
                </div>

                <hr class="my-3 text-muted opacity-25">

                <!-- Thông báo nhanh -->
                <div class="bg-primary-subtle p-3 rounded-3 border border-primary-subtle">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-circle-info text-primary"></i>
                        <span class="fw-bold small text-primary">Lưu ý nghiệp vụ</span>
                    </div>
                    <p class="small text-secondary mb-0" style="font-size: 0.8rem;">
                        Giảng viên cần hoàn tất nhận xét báo cáo tiến độ đợt 2 trước <strong>23:59 ngày 28/03</strong> để tổng hợp danh sách đủ điều kiện bảo vệ.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Bar Chart: Tiến độ các nhóm
    const groupCtx = document.getElementById('gvGroupProgressChart');
    if (groupCtx) {
        new Chart(groupCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartNhomLabels ?? []) !!},
                datasets: [{
                    label: 'Mốc đã đạt (trên 5)',
                    data: {!! json_encode($chartNhomProgress ?? []) !!},
                    backgroundColor: '#2563eb',
                    hoverBackgroundColor: '#1d4ed8',
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
                        max: 5,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        ticks: { font: { size: 11 }, maxRotation: 25, minRotation: 0 }
                    }
                }
            }
        });
    }

    // 2. Doughnut Chart: Trạng thái báo cáo
    const reportCtx = document.getElementById('gvReportStatusChart');
    if (reportCtx) {
        new Chart(reportCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đã duyệt', 'Chờ GV duyệt', 'Cần nộp lại'],
                datasets: [{
                    data: [
                        {{ $chartBaoCaoStatus['da_duyet'] ?? 0 }},
                        {{ $chartBaoCaoStatus['cho_duyet'] ?? 0 }},
                        {{ $chartBaoCaoStatus['can_nop_lai'] ?? 0 }}
                    ],
                    backgroundColor: ['#198754', '#dc3545', '#ffc107'],
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