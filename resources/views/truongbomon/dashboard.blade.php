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
            <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-primary btn-sm rounded-3 shadow-sm fw-semibold">
                <i class="fa-solid fa-user-plus me-1"></i> Phân Công PB ({{ $stats['cho_duyet_bm'] }})
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-outline-primary btn-sm rounded-3 fw-semibold">
                <i class="fa-solid fa-clipboard-check me-1"></i> Duyệt Đề Tài ({{ $stats['da_phan_bien'] }})
            </a>
            <a href="{{ route('truongbomon.theodoi.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold">
                <i class="fa-solid fa-list-check me-1"></i> Theo Dõi Tiến Độ
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index', ['view' => 'thongke']) }}" class="btn btn-outline-success btn-sm rounded-3 fw-semibold">
                <i class="fa-solid fa-chart-column me-1"></i> Thống Kê
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important; border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Chờ Phân Công Phản Biện</div>
                            <h3 class="fw-bold my-1 text-warning">{{ $stats['cho_duyet_bm'] }}</h3>
                            <a href="{{ route('truongbomon.phancong.index') }}" class="small text-decoration-none text-warning fw-semibold">
                                Phân công ngay <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="fa-solid fa-user-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important; border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Đang Phản Biện Đề Cương</div>
                            <h3 class="fw-bold my-1 text-info">{{ $stats['dang_phan_bien'] }}</h3>
                            <div class="small text-muted">Chờ GV phản biện cho ý kiến</div>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-circle">
                            <i class="fa-solid fa-file-pen fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important; border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Đã Phản Biện Xong</div>
                            <h3 class="fw-bold my-1 text-success">{{ $stats['da_phan_bien'] }}</h3>
                            <a href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Đã phản biện - Chờ duyệt BM']) }}" class="small text-decoration-none text-success fw-semibold">
                                Xem & duyệt ngay <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-circle">
                            <i class="fa-solid fa-circle-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important; border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Đã Chuyển Lên Khoa</div>
                            <h3 class="fw-bold my-1 text-primary">{{ $stats['cho_duyet_khoa'] }}</h3>
                            <div class="small text-muted">Chờ Trưởng khoa duyệt</div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="fa-solid fa-stamp fa-2x"></i>
                        </div>
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

    <!-- Urgent Action Section: Topics Requiring Processing -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-triangle-exclamation text-primary me-2"></i>
                Đề Tài Cần Xử Lý Ngay Tại Bộ Môn
            </h6>
            <div class="d-flex gap-2">
                <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">
                    Phân Công PB <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
                <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-sm btn-primary rounded-3 fw-semibold">
                    Duyệt Đề Tài <i class="fa-solid fa-arrow-right ms-1"></i>
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
                                <th class="text-end" style="width: 190px;">Thao Tác</th>
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
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="fa-solid fa-clock me-1"></i> Chờ duyệt BM</span>
                                        @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="fa-solid fa-spinner me-1"></i> Đang phản biện</span>
                                        @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-check-double me-1"></i> Đã phản biện</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $dt->TrangThai }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pb)
                                            <div class="small fw-semibold">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                            @if($pb->KetQua === 'Đạt')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.75rem;"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                            @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.75rem;"><i class="fa-solid fa-pen me-1"></i> Cần sửa</span>
                                            @elseif($pb->KetQua === 'Không đạt')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.75rem;"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                            @else
                                                <span class="badge bg-light text-muted border" style="font-size: 0.75rem;"><i class="fa-solid fa-hourglass-start me-1"></i> Chờ đánh giá</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.75rem;"><i class="fa-solid fa-circle-exclamation me-1"></i> Chưa phân công</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$pb)
                                            <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-sm btn-primary rounded-3 px-2 fw-semibold">
                                                <i class="fa-solid fa-user-plus me-1"></i> Phân công PB
                                            </a>
                                        @endif
                                        <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2">
                                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                        </a>
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
