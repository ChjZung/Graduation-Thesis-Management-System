@extends('layouts.truongkhoa')

@section('page_title', 'Lịch Sử Nghiệp Vụ & Chỉ Đạo — Nhóm ' . ($nhom->TenNhom ?: $nhom->MaNhom))

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
    .actor-badge-truongkhoa {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }
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
                <a href="{{ route('truongkhoa.theodoi.show', $nhom->MaNhom) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 11.5px;">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại chi tiết đề tài
                </a>
                <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted" style="font-size: 11.5px;">
                    Danh sách theo dõi
                </a>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 11.5px;">
                    Mã nhóm: {{ $nhom->MaNhom }}
                </span>
                <span class="badge bg-light text-secondary border rounded-pill font-monospace" style="font-size: 11.5px;">
                    {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
                </span>
            </div>

            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                Lịch Sử Nghiệp Vụ &amp; Chỉ Đạo Tiến Độ Cấp Khoa
            </h4>
            <p class="text-muted small mb-0">
                Đề tài: <strong>{{ $nhom->deTai->TenDeTai ?? $nhom->TenNhom }}</strong> • 
                Bộ môn: <strong>{{ $nhom->deTai?->giangVien?->boMon?->TenBoMon ?? 'N/A' }}</strong> • 
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
        <form method="GET" action="{{ route('truongkhoa.theodoi.lichSu', $nhom->MaNhom) }}" id="historyFilterFormTK">
            <div class="row g-2 align-items-center">
                {{-- Lọc Loại hoạt động --}}
                <div class="col-lg-3 col-md-6 col-12">
                    <label class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Loại hoạt động:</label>
                    <select name="action" class="form-select form-select-sm" onchange="document.getElementById('historyFilterFormTK').submit()">
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
                    <select name="stage" class="form-select form-select-sm" onchange="document.getElementById('historyFilterFormTK').submit()">
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
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}" onchange="document.getElementById('historyFilterFormTK').submit()">
                </div>

                {{-- Lọc Đến ngày --}}
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Đến ngày:</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}" onchange="document.getElementById('historyFilterFormTK').submit()">
                </div>

                {{-- Nút Đặt lại --}}
                <div class="col-lg-2 col-md-12 col-12 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-3 shadow-none fw-semibold" style="height: 31px; font-size: 12px;">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['action', 'stage', 'from_date', 'to_date']))
                        <a href="{{ route('truongkhoa.theodoi.lichSu', $nhom->MaNhom) }}" class="btn btn-sm btn-outline-secondary rounded-3 shadow-none" style="height: 31px;" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── 3. BẢNG DỮ LIỆU LỊCH SỬ NGHIỆP VỤ & CHỈ ĐẠO ── --}}
    <div class="card-history mb-4 overflow-hidden">
        <div class="p-3 px-4 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-list-ul text-primary me-2"></i>
                    Nhật Ký Chuyển Giai Đoạn &amp; Chỉ Đạo Tiến Độ
                </h6>
                <span class="text-muted small">
                    Hiển thị <strong>{{ $logs->firstItem() ?? 0 }}</strong> - <strong>{{ $logs->lastItem() ?? 0 }}</strong> 
                    trên tổng số <strong>{{ $logs->total() }}</strong> lượt ghi nhận
                </span>
            </div>
            <span class="badge bg-light text-secondary border font-monospace px-3 py-2">
                5 bản ghi / trang
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-history mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th style="width: 140px;">Thời Gian</th>
                        <th style="width: 150px;">Người Thực Hiện</th>
                        <th style="width: 160px;">Hành Động Nghiệp Vụ</th>
                        <th class="text-center" style="width: 130px;">Giai Đoạn</th>
                        <th class="text-center" style="width: 120px;">Trạng Thái</th>
                        <th>Chi Tiết / Lý Do / Ý Kiến Chỉ Đạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $item)
                        @php
                            $stt = ($logs->currentPage() - 1) * $logs->perPage() + $index + 1;
                            $nguoi = $item->NguoiThucHien ?? 'Hệ thống';
                            $actorClass = 'actor-badge-default';
                            if (str_contains($nguoi, 'Trưởng khoa') || str_contains($nguoi, 'TK')) {
                                $actorClass = 'actor-badge-truongkhoa';
                            } elseif (str_contains($nguoi, 'Trưởng bộ môn') || str_contains($nguoi, 'TBM')) {
                                $actorClass = 'actor-badge-tbm';
                            } elseif (str_contains($nguoi, 'GVHD') || str_contains($nguoi, 'Giảng viên')) {
                                $actorClass = 'actor-badge-gvhd';
                            } elseif (str_contains($nguoi, 'Phản biện')) {
                                $actorClass = 'actor-badge-phanbien';
                            } elseif (str_contains($nguoi, 'Giáo vụ') || str_contains($nguoi, 'Admin')) {
                                $actorClass = 'actor-badge-giaovu';
                            } elseif (str_contains($nguoi, 'Hệ thống')) {
                                $actorClass = 'actor-badge-system';
                            }
                        @endphp
                        <tr>
                            <td class="text-center font-monospace text-muted">{{ $stt }}</td>
                            <td>
                                <div class="fw-semibold text-dark font-monospace" style="font-size: 12.5px;">
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('H:i:s') }}
                                </div>
                                <div class="text-muted small font-monospace" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-1 font-monospace {{ $actorClass }}" style="font-size: 11px;">
                                    {{ $nguoi }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 12.5px;">
                                    @if(str_contains($item->HanhDong, 'nhắc'))
                                        <i class="fa-solid fa-bullhorn text-danger me-1"></i>
                                    @endif
                                    {{ $item->HanhDong }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item->GiaiDoanTruoc && $item->GiaiDoanTruoc != $item->GiaiDoanSau)
                                    <div class="d-inline-flex align-items-center gap-1 font-monospace" style="font-size: 11px;">
                                        <span class="badge bg-light text-secondary border px-2 py-1">GĐ {{ $item->GiaiDoanTruoc }}</span>
                                        <i class="fa-solid fa-arrow-right text-muted" style="font-size: 9px;"></i>
                                        <span class="badge bg-primary text-white px-2 py-1">GĐ {{ $item->GiaiDoanSau }}</span>
                                    </div>
                                @else
                                    <span class="badge bg-light text-primary border font-monospace px-2 py-1" style="font-size: 11px;">
                                        GĐ {{ $item->GiaiDoanSau }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->TrangThaiSau === 'HOAN_THANH')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 10.5px;">Hoàn thành</span>
                                @elseif($item->TrangThaiSau === 'QUA_HAN')
                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 10.5px;">Quá hạn</span>
                                @elseif($item->TrangThaiSau === 'CHO_XU_LY')
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10.5px;">Chờ xử lý</span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1" style="font-size: 10.5px;">Đang làm</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small lh-base" style="word-break: break-word;">
                                    {!! \App\Helpers\HtmlHelper::clean($item->LyDo ?: 'Không có nội dung bổ sung') !!}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50 d-block"></i>
                                Không có dữ liệu lịch sử nào theo tiêu chí lọc này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3 px-4 bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Trang <strong>{{ $logs->currentPage() }}</strong> / <strong>{{ $logs->lastPage() }}</strong>
                </span>
                <div>
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
