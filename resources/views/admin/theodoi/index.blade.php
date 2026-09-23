@extends('layouts.admin')

@section('page_title', 'Theo Dõi Tiến Độ Đồ Án / Khóa Luận')

@section('content')

{{-- ── TIÊU ĐỀ TRANG & THÔNG TIN KẾ HOẠCH HIỆN TẠI ── --}}
<div class="page-header-flex mb-3">
    <div>
        <h4 class="page-main-title">
            <i class="fa-solid fa-chart-line text-primary"></i>
            Theo Dõi Tiến Độ Đồ Án / Khóa Luận
        </h4>
        <p class="page-main-subtitle">
            Giám sát thời gian thực, đối chiếu tiến độ với kế hoạch và phát hiện cảnh báo trễ hạn tự động.
        </p>
    </div>
    @if($activePlan)
    <div class="p-2 px-3 rounded-3 border bg-white shadow-sm" style="font-size: 0.82rem;">
        <div class="fw-bold text-primary">{{ $activePlan->TenKeHoach }}</div>
        @if($currentPhase)
            <div class="text-muted mt-1">
                <span class="text-success fw-bold">🟢 Giai đoạn hiện tại:</span> 
                <span class="text-dark fw-semibold">{{ $currentPhase->TenMoc }}</span>
            </div>
        @endif
    </div>
    @endif
</div>

{{-- ── 5 PILL TABS THỐNG KÊ / LỌC TRẠNG THÁI ── --}}
<div class="admin-pill-tabs">
    <a href="{{ route('admin.theodoi.index') }}" 
       class="admin-pill-tab {{ !request('status_code') ? 'active' : '' }}">
        <span class="fw-bold">{{ $stats['total'] }}</span> Tất Cả Nhóm
    </a>
    <a href="{{ route('admin.theodoi.index', ['status_code' => 'DUNG_TIEN_DO']) }}" 
       class="admin-pill-tab {{ request('status_code') == 'DUNG_TIEN_DO' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#10b981;"></span>
        <span class="fw-bold">{{ $stats['dung_tien_do'] }}</span> Đúng Tiến Độ
    </a>
    <a href="{{ route('admin.theodoi.index', ['status_code' => 'SAP_DEN_HAN']) }}" 
       class="admin-pill-tab {{ request('status_code') == 'SAP_DEN_HAN' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>
        <span class="fw-bold">{{ $stats['sap_den_han'] }}</span> Sắp Đến Hạn
    </a>
    <a href="{{ route('admin.theodoi.index', ['status_code' => 'QUA_HAN']) }}" 
       class="admin-pill-tab {{ request('status_code') == 'QUA_HAN' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#ef4444;"></span>
        <span class="fw-bold">{{ $stats['qua_han'] }}</span> Quá Hạn Cảnh Báo
    </a>
    <a href="{{ route('admin.theodoi.index', ['status_code' => 'HOAN_THANH']) }}" 
       class="admin-pill-tab {{ request('status_code') == 'HOAN_THANH' ? 'active' : '' }}">
        <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#94a3b8;"></span>
        <span class="fw-bold">{{ $stats['hoan_thanh'] }}</span> Đã Hoàn Thành
    </a>
</div>

{{-- ── THANH TÌM KIẾM & BỘ LỌC DỮ LIỆU ── --}}
<div class="admin-filter-bar">
    <form method="GET" action="{{ route('admin.theodoi.index') }}" class="row g-2 align-items-center">
        <input type="hidden" name="status_code" value="{{ request('status_code') }}">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Tìm theo tên nhóm, mã nhóm, tên đề tài..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="MaGV" class="form-select form-select-sm">
                <option value="">-- Tất cả GVHD --</option>
                @foreach($giangViens as $gv)
                    <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                        {{ $gv->HoTen }} ({{ $gv->MaGV }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="MaHocKy" class="form-select form-select-sm">
                <option value="">Tất cả Học kỳ</option>
                @foreach($hocKies as $hk)
                    <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                        {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 flex-grow-1 fw-semibold" style="background: #0072ce; border-color: #0072ce;">
                <i class="fa-solid fa-bolt me-1"></i> Lọc Dữ Liệu
            </button>
            <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-light border rounded-3 px-2 text-secondary" title="Tải lại">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── BẢNG DANH SÁCH NHÓM & TIẾN ĐỘ ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-regular fa-folder-open text-primary"></i>
            Danh Sách Nhóm Thực Hiện &amp; Tiến Độ
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.ketqua.export') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-file-excel me-1 text-success"></i> Xuất Excel
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 140px;">Mã Nhóm</th>
                    <th style="min-width: 320px;">Tên Đề Tài &amp; Trưởng Nhóm</th>
                    <th style="min-width: 220px;">GV Hướng Dẫn</th>
                    <th style="width: 230px;">Tiến Độ Real-Time</th>
                    <th style="width: 190px;">Trạng Thái / Cảnh Báo</th>
                    <th class="text-center" style="width: 110px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($processedNhoms as $nhom)
                @php
                    $isDanger = $nhom->status_code === 'QUA_HAN';
                @endphp
                <tr class="{{ $isDanger ? 'bg-danger bg-opacity-10' : '' }}">
                    {{-- Mã Nhóm --}}
                    <td>
                        <div class="fw-bold" style="font-size: 0.95rem; color: #003b73;">
                            {{ $nhom->TenNhom ?: $nhom->MaNhom }}
                        </div>
                        <div class="text-muted" style="font-size: 0.74rem;">
                            {{ $nhom->MaNhom }}
                        </div>
                    </td>

                    {{-- Đề tài & Trưởng nhóm --}}
                    <td>
                        @if($nhom->deTai)
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                {{ $nhom->deTai->TenDeTai }}
                            </div>
                        @else
                            <div class="fw-bold text-muted fst-italic" style="font-size: 0.88rem;">
                                Chưa đăng ký đề tài chính thức
                            </div>
                        @endif

                        <div class="text-secondary mt-1" style="font-size: 0.78rem;">
                            <span class="text-warning">👑</span> Trưởng nhóm: 
                            <strong class="text-dark">{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</strong>
                            @if($nhom->truongNhom)
                                ({{ $nhom->truongNhom->MaSV }})
                            @endif
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.74rem;">
                            Thành viên: {{ $nhom->thanhViens->count() + 1 }}/3 sinh viên
                        </div>
                    </td>

                    {{-- GVHD --}}
                    <td>
                        @if($nhom->dangKyDeTai && $nhom->dangKyDeTai->giangVienHuongDan)
                            <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                                {{ $nhom->dangKyDeTai->giangVienHuongDan->HocHamHocVi ? $nhom->dangKyDeTai->giangVienHuongDan->HocHamHocVi . ' ' : '' }}{{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen }}
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                Bộ môn: {{ $nhom->dangKyDeTai->giangVienHuongDan->boMon->TenBoMon ?? 'CNPM' }}
                            </div>
                        @elseif($nhom->deTai && $nhom->deTai->giangVien)
                            <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                                {{ $nhom->deTai->giangVien->HocHamHocVi ? $nhom->deTai->giangVien->HocHamHocVi . ' ' : '' }}{{ $nhom->deTai->giangVien->HoTen }}
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                Bộ môn: {{ $nhom->deTai->giangVien->boMon->TenBoMon ?? 'CNPM' }}
                            </div>
                        @else
                            <span class="text-muted fst-italic" style="font-size: 0.82rem;">Chưa phân công</span>
                        @endif
                    </td>

                    {{-- Tiến độ Real-time --}}
                    <td>
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.82rem;">
                            <span class="fw-semibold text-secondary">Tiến độ:</span>
                            <span class="fw-bold" style="color: {{ $nhom->progress_percent >= 80 ? '#10b981' : ($nhom->progress_percent >= 40 ? '#0ea5e9' : '#f59e0b') }};">
                                {{ $nhom->progress_percent }}%
                            </span>
                        </div>
                        <div class="progress rounded-pill shadow-none mb-1" style="height: 7px; background: #e2e8f0;">
                            <div class="progress-bar rounded-pill {{ $nhom->progress_percent >= 100 ? 'bg-success' : ($nhom->status_code == 'QUA_HAN' ? 'bg-danger' : 'bg-success') }}" 
                                 style="width: {{ $nhom->progress_percent }}%;"></div>
                        </div>
                        <div class="text-muted" style="font-size: 0.74rem;">
                            @if($nhom->progress_percent >= 50)
                                <span class="text-success fw-semibold"><i class="fa-solid fa-check me-1"></i>Đã nghiệm thu Mốc 1 &amp; Mốc 2</span>
                            @else
                                <i class="fa-regular fa-clock me-1 text-primary"></i>{{ $nhom->nearest_deadline }}
                            @endif
                        </div>
                    </td>

                    {{-- Trạng thái / Cảnh báo --}}
                    <td>
                        @if($nhom->status_code === 'DUNG_TIEN_DO')
                            <span class="badge-status-green">Đúng tiến độ</span>
                        @elseif($nhom->status_code === 'SAP_DEN_HAN')
                            <span class="badge-status-amber"><i class="fa-solid fa-triangle-exclamation"></i> {{ $nhom->status_label }}</span>
                            <div class="text-danger fw-semibold mt-1" style="font-size: 0.72rem;">Sắp đến hạn nộp báo cáo</div>
                        @elseif($nhom->status_code === 'QUA_HAN')
                            <span class="badge-status-red"><i class="fa-solid fa-triangle-exclamation"></i> {{ $nhom->status_label }}</span>
                            <div class="text-danger fw-bold mt-1" style="font-size: 0.72rem;">Quá hạn cảnh báo</div>
                        @else
                            <span class="badge-status-blue">{{ $nhom->status_label }}</span>
                        @endif
                    </td>

                    {{-- Thao tác --}}
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.theodoi.show', $nhom->MaNhom) }}" class="btn-action-icon btn-action-view" title="Xem chi tiết tiến độ">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}" method="POST" class="d-inline" onsubmit="return confirm('Gửi thông báo nhắc nhở tiến độ tới nhóm {{ $nhom->MaNhom }} và GVHD?');">
                                @csrf
                                <button type="submit" class="btn-action-icon btn-action-edit" title="Gửi nhắc nhở">
                                    <i class="fa-solid fa-bell"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-chart-line fa-2x mb-2 d-block opacity-50"></i>
                        Không có nhóm khóa luận nào phù hợp với điều kiện tìm kiếm.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer count --}}
    <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff;">
        <div class="text-muted" style="font-size: 0.82rem;">
            Hiển thị <strong>{{ $processedNhoms->count() }}</strong> nhóm khóa luận
        </div>
    </div>
</div>

@endsection
