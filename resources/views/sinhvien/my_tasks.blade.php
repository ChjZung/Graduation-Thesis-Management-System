@extends('layouts.sinhvien')

@section('page_title', 'Công Việc & Tiến Độ Của Tôi')

@section('content')
<style>
    /* Bảng màu chuẩn: Midnight Navy (#003366), Ocean Cyan (#0072CE), Persian Green (#10B981), Mint/Soft Aqua (#E5F0FA) */
    .task-container {
        font-size: 0.85rem;
        color: #334155;
    }

    /* Common Card Panels */
    .task-card-panel {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 51, 102, 0.04);
        overflow: hidden;
    }

    .panel-header-compact {
        padding: 14px 20px;
        background-color: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-body-compact {
        padding: 18px 20px;
    }

    /* Custom Badges & Buttons */
    .badge-neutral {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-cyan {
        background-color: #0072CE;
        border-color: #0072CE;
        color: #ffffff;
    }
    .btn-cyan:hover, .btn-cyan:focus {
        background-color: #006b73;
        border-color: #006b73;
        color: #ffffff;
    }

    .btn-outline-cyan {
        border-color: #0072CE;
        color: #0072CE;
    }
    .btn-outline-cyan:hover {
        background-color: #0072CE;
        color: #ffffff;
    }

    /* Table Styling */
    .task-table {
        margin-bottom: 0;
    }
    .task-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .task-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .task-table tbody tr:last-child td {
        border-bottom: none;
    }
    .task-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .kpi-summary-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
    }
</style>

<div class="task-container">

    {{-- ── 1. TIÊU ĐỀ TRANG ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: #003366;">
                <i class="fa-solid fa-list-check me-2" style="color: #0072CE;"></i>
                <span>Công Việc &amp; Tiến Độ Khóa Luận</span>
            </h5>
            <div class="text-secondary" style="font-size: 0.825rem;">
                Theo dõi kế hoạch thực hiện, lịch nộp báo cáo tiến độ và các mốc đánh giá quan trọng.
            </div>
        </div>
        @if($nhom)
        <div>
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-cyan btn-sm rounded-pill px-3 d-inline-flex align-items-center">
                <i class="fa-solid fa-upload me-2"></i>Nộp báo cáo
            </a>
        </div>
        @endif
    </div>

    @if($nhom)
    {{-- ── 2. THÔNG TIN NHÓM & ĐỀ TÀI TỔNG QUAN ── --}}
    <div class="task-card-panel mb-3">
        <div class="panel-body-compact">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                            {{ $nhom->MaNhom }}
                        </span>
                        <span class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $nhom->TenNhom }}</span>
                    </div>
                    <h6 class="fw-bold mb-2 text-truncate-2" style="color: #003366; line-height: 1.4; font-size: 1rem;">
                        {{ $nhom->deTai->TenDeTai ?? 'Chưa được phân công đề tài' }}
                    </h6>
                    <div class="text-muted d-flex flex-wrap gap-3" style="font-size: 0.8rem;">
                        <span><i class="fa-solid fa-chalkboard-user me-2" style="color: #0072CE;"></i>GVHD: <strong style="color: #003366;">{{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen ?? 'Chưa phân công' }}</strong></span>
                        <span><i class="fa-solid fa-crown text-warning me-2"></i>Trưởng nhóm: <strong style="color: #003366;">{{ $nhom->truongNhom->HoTen ?? 'Chưa có' }}</strong></span>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <div class="kpi-summary-box d-inline-block text-start w-100" style="max-width: 280px;">
                        <div class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">
                            <i class="fa-solid fa-bars-progress me-2" style="color: #0072CE;"></i>Báo cáo tiến độ
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-4 fw-bold" style="color: #10B981;">{{ $nhom->baoCaos->count() }}</span>
                            <span class="text-muted" style="font-size: 0.8rem;">/ 5 mốc theo quy định</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. DANH SÁCH MỐC THỜI GIAN & CÔNG VIỆC ── --}}
    <div class="task-card-panel mb-4">
        <div class="panel-header-compact">
            <div class="fw-bold d-flex align-items-center gap-2" style="color: #003366; font-size: 0.875rem;">
                <i class="fa-solid fa-clock-rotate-left me-2" style="color: #0072CE;"></i>
                <span>Kế Hoạch &amp; Hạn Nộp Báo Cáo</span>
            </div>
            <span class="badge badge-neutral rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                Tổng cộng: {{ count($taskList) }} mốc
            </span>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="table task-table align-middle">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="32%">Công Việc / Mốc Kế Hoạch</th>
                            <th width="25%">Thời Gian Thực Hiện</th>
                            <th width="20%">Trạng Thái</th>
                            <th width="18%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taskList as $idx => $task)
                        <tr>
                            <td class="text-center fw-bold text-muted" style="font-size: 0.8rem;">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.875rem;">{{ $task['moc']->TenMoc }}</div>
                                <span class="badge badge-neutral rounded-pill mt-1 py-1 px-2" style="font-size: 0.725rem;">
                                    {{ $task['moc']->LoaiGiaiDoan }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size: 0.8rem; color: #003366;">
                                    <i class="fa-regular fa-calendar-days me-2" style="color: #0072CE;"></i>Từ: {{ date('d/m/Y', strtotime($task['moc']->NgayBatDau)) }}
                                </div>
                                <div class="text-danger mt-1" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-clock me-2"></i>Đến: {{ date('d/m/Y', strtotime($task['moc']->NgayKetThuc)) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $task['badge_class'] }} rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.75rem;">
                                    {{ $task['status_label'] }}
                                </span>
                                @if($task['warning'])
                                    <div class="text-danger fw-semibold mt-1" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $task['warning'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(str_contains($task['moc']->LoaiGiaiDoan, 'BAO_CAO_TIEN_DO'))
                                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-sm btn-cyan rounded-pill px-3 d-inline-flex align-items-center">
                                        <i class="fa-solid fa-upload me-2"></i>Nộp báo cáo
                                    </a>
                                @elseif($task['moc']->LoaiGiaiDoan === 'DAO_VAN')
                                    <a href="{{ route('sinhvien.hosoBaoVe.index') }}" class="btn btn-sm btn-outline-cyan rounded-pill px-3 d-inline-flex align-items-center">
                                        <i class="fa-solid fa-file-shield me-2"></i>Nộp Turnitin
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size: 0.85rem;">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-50 me-2"></i>
                                <span style="font-size: 0.85rem;">Chưa có mốc thời gian kế hoạch nào được ghi nhận.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    {{-- ── 4. TRẠNG THÁI CHƯA CÓ NHÓM (EMPTY STATE) ── --}}
    <div class="task-card-panel">
        <div class="panel-body-compact text-center py-5 px-4">
            <div class="mb-3">
                <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px; background-color: #E5F0FA; color: #0072CE;">
                    <i class="fa-solid fa-users-slash fa-2x"></i>
                </span>
            </div>
            <h6 class="fw-bold mb-2" style="color: #003366; font-size: 1.05rem;">Bạn chưa tham gia nhóm đồ án nào</h6>
            <p class="text-muted mx-auto mb-4" style="max-width: 480px; font-size: 0.825rem; line-height: 1.5;">
                Vui lòng tạo nhóm mới hoặc gia nhập nhóm khóa luận để theo dõi toàn bộ kế hoạch, nhận xét của GVHD và nộp báo cáo theo quy định.
            </p>
            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-cyan rounded-pill px-4 d-inline-flex align-items-center">
                <i class="fa-solid fa-users me-2"></i>Đến Quản lý nhóm
            </a>
        </div>
    </div>
    @endif

</div>
@endsection