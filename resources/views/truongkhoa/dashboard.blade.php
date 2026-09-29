@extends('layouts.truongkhoa')

@section('page_title', 'Bàn Làm Việc Trưởng Khoa')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Greeting -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-graduation-cap me-2 text-danger"></i>
                Bàn Làm Việc Trưởng Khoa {{ $khoa->TenKhoa ?? '' }}
            </h4>
            <p class="text-muted mb-0 small">
                Phê duyệt danh mục đề tài khóa luận cấp Khoa và giám sát chất lượng đào tạo, hội đồng toàn khoa.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-stamp me-1"></i> Phê Duyệt Đề Tài ({{ $stats['cho_duyet_khoa'] }})
            </a>
            <a href="{{ route('truongkhoa.theodoi.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chart-line me-1"></i> Theo Dõi Tiến Độ Toàn Khoa
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important; border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Chờ Trưởng Khoa Duyệt</div>
                            <h3 class="fw-bold my-1 text-danger">{{ $stats['cho_duyet_khoa'] }}</h3>
                            <div class="small text-muted">Bộ môn đã thông qua</div>
                        </div>
                        <div class="p-3 bg-danger-subtle text-danger rounded-circle">
                            <i class="fa-solid fa-stamp fa-2x"></i>
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
                            <div class="text-muted small fw-semibold text-uppercase">Trưởng Khoa Đã Duyệt</div>
                            <h3 class="fw-bold my-1 text-success">{{ $stats['truong_khoa_da_duyet'] }}</h3>
                            <div class="small text-muted">Chờ Giáo vụ công bố</div>
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
                            <div class="text-muted small fw-semibold text-uppercase">Đề Tài Đã Công Bố</div>
                            <h3 class="fw-bold my-1 text-primary">{{ $stats['da_cong_bo'] }}</h3>
                            <div class="small text-muted">Sinh viên đang thực hiện</div>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                            <i class="fa-solid fa-bullhorn fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important; border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Đang Xử Lý Tại Bộ Môn</div>
                            <h3 class="fw-bold my-1 text-warning">{{ $stats['cho_duyet_bm'] + $stats['dang_phan_bien'] }}</h3>
                            <div class="small text-muted">Chờ phản biện & TBM</div>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                            <i class="fa-solid fa-clock-rotate-left fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Faculty Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Bộ Môn</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['so_bo_mon'] }}</h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Giảng Viên</div>
                <h4 class="fw-bold text-dark my-1">{{ $stats['so_giang_vien'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                <div class="text-muted small">Sinh Viên Đủ ĐK</div>
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

    <!-- Topics Awaiting Faculty Approval -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-stamp text-danger me-2"></i>
                Đề Tài Đang Chờ Trưởng Khoa Phê Duyệt
            </h6>
            <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="btn btn-outline-danger btn-sm">
                Xem Tất Cả ({{ $stats['cho_duyet_khoa'] }}) <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            @if($deTaiCanDuyet->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-circle-check text-success fa-3x mb-3"></i>
                    <h6 class="text-muted">Hiện tại không có đề tài nào đang chờ Trưởng khoa phê duyệt!</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã ĐT</th>
                                <th>Tên Đề Tài & Bộ Môn</th>
                                <th>GV Hướng Dẫn</th>
                                <th>Ý Kiến Phản Biện Đề Cương</th>
                                <th>Ngày TBM Duyệt</th>
                                <th class="text-end" style="width: 140px;">Thao Tác</th>
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
                                                <span class="badge bg-success" style="font-size: 0.7rem;"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                            @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Cần sửa</span>
                                            @elseif($pb->KetQua === 'Không đạt')
                                                <span class="badge bg-danger" style="font-size: 0.7rem;">Không đạt</span>
                                            @else
                                                <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Chưa đánh giá</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Chưa ghi nhận</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $dt->NgayDuyetBM ? \Carbon\Carbon::parse($dt->NgayDuyetBM)->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('truongkhoa.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-stamp me-1"></i> Xem & Duyệt
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
