@extends('layouts.admin')

@section('page_title', 'Lịch Sử Nghiệp Vụ & Nhắc Nhở — Nhóm ' . ($nhom->TenNhom ?: $nhom->MaNhom))

@push('styles')
<style>
    :root {
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
        --border-color: #e2e8f0;
    }

    .card-history {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 0.85rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .table-history thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 2px solid var(--border-color);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    .table-history tbody td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-history tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Role Badges */
    .actor-badge-tbm {
        background-color: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #d8b4fe;
    }
    .actor-badge-gvhd {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .actor-badge-phanbien {
        background-color: #ccfbf1;
        color: #0f766e;
        border: 1px solid #99f6e4;
    }
    .actor-badge-system {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .actor-badge-giaovu {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .actor-badge-default {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── 1. HEADER & ĐIỀU HƯỚNG ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('admin.theodoi.show', $nhom->MaNhom) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 11.5px;">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại chi tiết đề tài
                </a>
                <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted" style="font-size: 11.5px;">
                    Danh sách theo dõi
                </a>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 11.5px;">
                    Mã nhóm: {{ $nhom->MaNhom }}
                </span>
            </div>

            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                Toàn Bộ Lịch Sử Nghiệp Vụ &amp; Nhắc Nhở
            </h4>
            <p class="text-muted small mb-0">
                Đề tài: <strong>{{ $nhom->deTai->TenDeTai ?? $nhom->TenNhom }}</strong> • 
                GVHD: <strong>{{ $nhom->deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</strong>
            </p>
        </div>

        {{-- Badge thông tin giai đoạn hiện tại --}}
        <div class="d-flex align-items-center gap-2">
            <div class="card-history px-3 py-2 bg-white text-center shadow-xs">
                <span class="text-muted small fw-semibold d-block" style="font-size: 10.5px;">Giai đoạn hiện tại</span>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold">
                    GĐ {{ $nhom->current_stage }}: {{ $nhom->current_stage_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── 2. BỘ LỌC LỊCH SỬ NÂNG CAO ── --}}
    <div class="card-history p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('admin.theodoi.lichSu', $nhom->MaNhom) }}" id="historyFilterForm">
            <div class="row g-2 align-items-center">
                {{-- Lọc Loại hoạt động --}}
                <div class="col-lg-3 col-md-6 col-12">
                    <label class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Loại hoạt động:</label>
                    <select name="action" class="form-select form-select-sm" onchange="document.getElementById('historyFilterForm').submit()">
                        <option value="">-- Tất cả hoạt động --</option>
                        @foreach($availableActions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                {{ $act }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc Giai đoạn --}}
                <div class="col-lg-3 col-md-6 col-12">
                    <label class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Giai đoạn nghiệp vụ:</label>
                    <select name="stage" class="form-select form-select-sm" onchange="document.getElementById('historyFilterForm').submit()">
                        <option value="">-- Tất cả giai đoạn --</option>
                        @foreach($stageDefinitions as $sNum => $sInfo)
                            <option value="{{ $sNum }}" {{ request('stage') == $sNum ? 'selected' : '' }}>
                                GĐ {{ $sNum }}: {{ $sInfo['short'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lọc Từ ngày --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Từ ngày:</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}" onchange="document.getElementById('historyFilterForm').submit()">
                </div>

                {{-- Lọc Đến ngày --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Đến ngày:</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}" onchange="document.getElementById('historyFilterForm').submit()">
                </div>

                {{-- Nút lọc & Xóa lọc --}}
                <div class="col-lg-2 col-md-12 col-12 d-flex gap-2 align-self-end mt-2 mt-lg-0">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold flex-grow-1 align-items-center" style="font-size: 12px;">
                        <i class="fa-solid fa-filter me-2"></i>Lọc
                    </button>
                    @if(request()->anyFilled(['action', 'stage', 'from_date', 'to_date']))
                        <a href="{{ route('admin.theodoi.lichSu', $nhom->MaNhom) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── 3. BẢNG DỮ LIỆU LỊCH SỬ (PHÂN TRANG 5 BẢN GHI / TRANG) ── --}}
    <div class="card-history bg-white mb-4">
        <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>
                    Nhật Ký Nghiệp Vụ Khóa Luận (Chỉ đọc)
                </h6>
                <span class="text-muted small" style="font-size: 11.5px;">
                    Hiển thị <strong>{{ $logs->firstItem() ?? 0 }}</strong> - <strong>{{ $logs->lastItem() ?? 0 }}</strong> 
                    trong tổng số <strong>{{ $logs->total() }}</strong> bản ghi lưu vết
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border fw-normal" style="font-size: 11px;">
                    <i class="fa-solid fa-shield-halved me-2 text-primary"></i>Giáo vụ chỉ giám sát
                </span>
            </div>
        </div>

        @if($logs->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa-regular fa-folder-open fa-3x mb-3 opacity-50 d-block"></i>
                <h6 class="fw-bold text-dark">Chưa có bản ghi hoạt động nào phù hợp</h6>
                <p class="small mb-0">Các hoạt động phê duyệt, chuyển mốc hoặc nhắc nhở sẽ được hệ thống lưu vết tự động.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-history mb-0 align-middle" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Thời Gian</th>
                            <th style="width: 180px;">Loại Hoạt Động</th>
                            <th style="width: 170px;">Giai Đoạn Nghiệp Vụ</th>
                            <th style="width: 220px;">Người Thực Hiện</th>
                            <th style="width: 180px;">Trạng Thái Trước &rarr; Sau</th>
                            <th style="min-width: 250px;">Nội Dung Chi Tiết / Lý Do</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $nth = $log->NguoiThucHien;
                                $bClass = 'actor-badge-default';
                                $bIcon = 'fa-user';
                                if (str_contains($nth, 'Trưởng bộ môn') || str_contains($nth, 'Bộ môn')) {
                                    $bClass = 'actor-badge-tbm';
                                    $bIcon = 'fa-building-columns';
                                } elseif (str_contains($nth, 'GVHD') || str_contains($nth, 'hướng dẫn')) {
                                    $bClass = 'actor-badge-gvhd';
                                    $bIcon = 'fa-chalkboard-user';
                                } elseif (str_contains($nth, 'phản biện') || str_contains($nth, 'Phản biện')) {
                                    $bClass = 'actor-badge-phanbien';
                                    $bIcon = 'fa-clipboard-check';
                                } elseif (str_contains($nth, 'Hệ thống')) {
                                    $bClass = 'actor-badge-system';
                                    $bIcon = 'fa-robot';
                                } elseif (str_contains($nth, 'Giáo vụ')) {
                                    $bClass = 'actor-badge-giaovu';
                                    $bIcon = 'fa-bell';
                                }
                                $gdSauMeta = $stageDefinitions[$log->GiaiDoanSau] ?? [];
                            @endphp
                            <tr>
                                {{-- Thời gian --}}
                                <td class="font-monospace text-muted small">
                                    <i class="fa-regular fa-clock me-2 text-secondary"></i>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('H:i d/m/Y') }}
                                </td>

                                {{-- Loại thao tác --}}
                                <td>
                                    <span class="fw-bold text-dark">{{ $log->HanhDong }}</span>
                                </td>

                                {{-- Giai đoạn --}}
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 11px;">
                                        GĐ {{ $log->GiaiDoanSau }}: {{ $gdSauMeta['short'] ?? "GĐ {$log->GiaiDoanSau}" }}
                                    </span>
                                </td>

                                {{-- Người thực hiện --}}
                                <td>
                                    <span class="badge {{ $bClass }} rounded-pill px-3 py-1" style="font-size: 11px;">
                                        <i class="fa-solid {{ $bIcon }} me-2"></i>{{ $log->NguoiThucHien }}
                                    </span>
                                </td>

                                {{-- Trạng thái trước & sau --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1 small" style="font-size: 11px;">
                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                            {{ $log->TrangThaiTruoc ?: 'Khởi tạo' }}
                                        </span>
                                        <i class="fa-solid fa-arrow-right text-muted me-2" style="font-size: 9px;"></i>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            {{ $log->TrangThaiSau }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Nội dung / Lý do --}}
                                <td>
                                    <div class="text-secondary small" style="line-height: 1.45;">
                                        {{ strip_tags($log->LyDo ?: 'Cập nhật tự động từ hệ thống') }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Phân trang (5 dòng / trang) --}}
            @if($logs->hasPages())
                <div class="p-3 px-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small" style="font-size: 12px;">
                        Trang {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                    </span>
                    <div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Ghi chú bảo đảm quyền Giáo vụ --}}
    <div class="alert alert-light border small text-muted d-flex align-items-center gap-2 mb-4">
        <i class="fa-solid fa-circle-info text-primary fs-5 me-2"></i>
        <span>
            <strong>Lưu ý quyền hạn:</strong> Giao diện này cung cấp chức năng tra cứu và theo dõi nhật ký nghiệp vụ dành cho Giáo vụ. 
            Mọi thao tác thay đổi trạng thái đều được thực hiện từ quy trình chuyên môn tương ứng và ghi vết kiểm toán tự động.
        </span>
    </div>

</div>
@endsection
