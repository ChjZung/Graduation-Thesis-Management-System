@extends('layouts.giangvien')

@section('page_title', 'Công Việc Hướng Dẫn & Tiến Độ Nhóm')

@push('styles')
<style>
    .stat-card-widget {
        background: #ffffff;
        border-radius: 12px;
        padding: 18px 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 114, 206, 0.1);
    }
    .stat-card-widget.border-l-primary {
        border-left: 5px solid #0072ce;
    }
    .stat-card-widget.border-l-warning {
        border-left: 5px solid #f59e0b;
    }
    .stat-card-widget.border-l-danger {
        border-left: 5px solid #ef4444;
    }
    .stat-card-widget.border-l-success {
        border-left: 5px solid #10b981;
    }

    .table-tasks {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    }
    .table-tasks th {
        background: #f8fafc;
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
    }
    .table-tasks td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- BREADCRUMB -->
    <div class="text-muted small mb-2 d-flex align-items-center gap-1">
        <i class="fa-solid fa-angle-right text-muted" style="font-size: 0.75rem;"></i>
        <span>Trang chủ</span>
        <i class="fa-solid fa-angle-right text-muted" style="font-size: 0.75rem;"></i>
        <span>Khóa luận tốt nghiệp</span>
        <i class="fa-solid fa-angle-right text-muted" style="font-size: 0.75rem;"></i>
        <span class="text-primary fw-bold">Công việc hướng dẫn</span>
    </div>

    <!-- HEADER TITLE & ACTION BUTTON -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Công Việc Hướng Dẫn &amp; Tiến Độ Các Nhóm
            </h5>
            <p class="text-muted small mb-0">
                Hệ thống nhắc nhở công việc duyệt đề tài, nhận xét báo cáo tiến độ và xác nhận hồ sơ đủ điều kiện bảo vệ cho GVHD.
            </p>
        </div>
        <div>
            <a href="{{ route('giangvien.calendar') }}" class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-2 fw-medium">
                <i class="fa-regular fa-calendar-days me-1"></i> Xem Lịch Công Việc &amp; Hội Đồng &rarr;
            </a>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget border-l-primary text-center">
                <div class="text-muted small fw-bold mb-1">Nhóm Đang Hướng Dẫn</div>
                <h3 class="fw-bold text-primary mb-0">{{ $nhoms->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget border-l-warning text-center">
                <div class="text-muted small fw-bold mb-1">Báo Cáo Sắp Đến Hạn</div>
                <h3 class="fw-bold text-warning mb-0">{{ $stats['sap_den_han'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget border-l-danger text-center">
                <div class="text-muted small fw-bold mb-1">Nhóm Quá Hạn Báo Cáo</div>
                <h3 class="fw-bold text-danger mb-0">{{ $stats['qua_han'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget border-l-success text-center">
                <div class="text-muted small fw-bold mb-1">Báo Cáo Chờ Nhận Xét</div>
                <h3 class="fw-bold text-success mb-0">{{ $stats['cho_nhan_xet'] ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <!-- LIST OF MANAGED GROUPS -->
    <div class="table-tasks mb-4">
        <div class="bg-white p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-users text-primary me-2 fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">Danh Sách Nhóm Đồ Án Hướng Dẫn &amp; Tiến Độ Nộp</h6>
            </div>
            <span class="badge bg-light text-muted border px-2 py-1">
                Tổng cộng: {{ $nhoms->count() }} nhóm
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="12%" class="ps-3">Mã Nhóm</th>
                        <th width="32%">Tên Đề Tài / Trưởng Nhóm</th>
                        <th width="20%">Báo Cáo Tiến Độ (1, 2, 3)</th>
                        <th width="18%">Hồ Sơ Bảo Vệ (Turnitin)</th>
                        <th width="18%" class="text-center pe-3">Thao Tác GVHD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nhoms as $nhom)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-primary fw-bold border fs-6 px-2 py-1">{{ $nhom->MaNhom }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}</div>
                            <div class="small text-muted mt-1"><i class="fa-solid fa-user-tie me-1"></i>Trưởng nhóm: {{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @for($l = 1; $l <= 3; $l++)
                                    @php $bc = $nhom->baoCaos->where('LanBaoCao', $l)->first(); @endphp
                                    @if($bc)
                                        <span class="badge bg-success rounded-pill" title="Lần {{ $l }}: Đã nộp {{ date('d/m', strtotime($bc->NgayNop)) }}">Lần {{ $l }} &check;</span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill" title="Lần {{ $l }}: Chưa nộp">Lần {{ $l }} -</span>
                                    @endif
                                @endfor
                            </div>
                        </td>
                        <td>
                            @if($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null)
                                <span class="badge bg-info text-white rounded-pill">Turnitin: {{ $nhom->hoSoBaoVe->TyLeTrungLap }}%</span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill">Chưa nộp Turnitin</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Nhận xét
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            Hiện tại bạn chưa được phân công hướng dẫn nhóm đồ án nào trong hệ thống.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
