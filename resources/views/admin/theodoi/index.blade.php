@extends('layouts.admin')

@section('page_title', 'Theo Dõi Tiến Độ Đồ Án / Khóa Luận')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Quick Overview Header Bar with Clickable Filter Buttons -->
<div class="card card-premium mb-4">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-chart-line me-2"></i> Theo Dõi Tiến Độ Đồ Án / Khóa Luận</h5>
                <span class="small text-muted">Giám sát thời gian thực, tiến độ thực hiện và phát hiện nhóm chậm tiến độ.</span>
            </div>
            @if($activePlan)
            <div class="text-end">
                <span class="badge bg-primary rounded-pill px-3 py-1 fs-7"><i class="fa-solid fa-calendar-check me-1"></i>{{ $activePlan->TenKeHoach }}</span>
                @if($currentPhase)
                    <div class="small text-muted mt-1"><i class="fa-solid fa-clock me-1 text-success"></i>Giai đoạn hiện tại: <strong>{{ $currentPhase->TenMoc }}</strong></div>
                @endif
            </div>
            @endif
        </div>

        <!-- 5 Interactive Filter Counters -->
        <div class="row g-2 text-center">
            <div class="col-md">
                <a href="{{ route('admin.theodoi.index') }}" class="btn btn-outline-primary w-100 py-2 rounded-3 {{ !request('status_code') ? 'active bg-primary text-white' : '' }}">
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                    <div class="small">Tất Cả Nhóm</div>
                </a>
            </div>
            <div class="col-md">
                <a href="{{ route('admin.theodoi.index', ['status_code' => 'DUNG_TIEN_DO']) }}" class="btn btn-outline-success w-100 py-2 rounded-3 {{ request('status_code') == 'DUNG_TIEN_DO' ? 'active bg-success text-white' : '' }}">
                    <div class="fs-4 fw-bold">{{ $stats['dung_tien_do'] }}</div>
                    <div class="small">🟢 Đúng Tiến Độ</div>
                </a>
            </div>
            <div class="col-md">
                <a href="{{ route('admin.theodoi.index', ['status_code' => 'SAP_DEN_HAN']) }}" class="btn btn-outline-warning text-dark w-100 py-2 rounded-3 {{ request('status_code') == 'SAP_DEN_HAN' ? 'active bg-warning text-dark' : '' }}">
                    <div class="fs-4 fw-bold">{{ $stats['sap_den_han'] }}</div>
                    <div class="small">🟡 Sắp Đến Hạn</div>
                </a>
            </div>
            <div class="col-md">
                <a href="{{ route('admin.theodoi.index', ['status_code' => 'QUA_HAN']) }}" class="btn btn-outline-danger w-100 py-2 rounded-3 {{ request('status_code') == 'QUA_HAN' ? 'active bg-danger text-white' : '' }}">
                    <div class="fs-4 fw-bold">{{ $stats['qua_han'] }}</div>
                    <div class="small">🔴 Quá Hạn Cảnh Báo</div>
                </a>
            </div>
            <div class="col-md">
                <a href="{{ route('admin.theodoi.index', ['status_code' => 'HOAN_THANH']) }}" class="btn btn-outline-secondary w-100 py-2 rounded-3 {{ request('status_code') == 'HOAN_THANH' ? 'active bg-secondary text-white' : '' }}">
                    <div class="fs-4 fw-bold">{{ $stats['hoan_thanh'] }}</div>
                    <div class="small">🔵 Đã Hoàn Thành</div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Multi-dimensional Search & Filter Bar -->
<div class="card card-premium mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.theodoi.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="status_code" value="{{ request('status_code') }}">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên nhóm, mã nhóm, đề tài, MSSV..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="MaGV" class="form-select form-select-sm">
                    <option value="">-- Tất cả GVHD --</option>
                    @foreach($giangViens as $gv)
                        <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>{{ $gv->HoTen }} ({{ $gv->MaGV }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả Học Kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('admin.theodoi.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Xóa lọc"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Main Priority Sorted Table -->
<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-list-check me-2"></i> Danh Sách Nhóm Thực Hiện &amp; Tiến Độ (Ưu Tiên Cảnh Báo)</span>
        <span class="badge bg-light text-primary border rounded-pill px-3">Hiển thị {{ $processedNhoms->count() }} Nhóm</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="12%">Mã Nhóm</th>
                        <th width="28%">Tên Đề Tài &amp; Trưởng Nhóm</th>
                        <th width="18%">GV Hướng Dẫn</th>
                        <th width="18%">Tiến Độ Real-Time</th>
                        <th width="12%">Trạng Thái / Cảnh Báo</th>
                        <th width="12%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processedNhoms as $nhom)
                    <tr class="{{ $nhom->status_code === 'QUA_HAN' ? 'bg-danger bg-opacity-10' : '' }}">
                        <td>
                            <span class="badge-code">{{ $nhom->MaNhom }}</span>
                            <div class="small text-muted mt-1">{{ $nhom->TenNhom ?? 'Nhóm Khóa Luận' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary mb-1">{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}</div>
                            <div class="small text-dark"><i class="fa-solid fa-user-tag me-1 text-muted"></i>Trưởng nhóm: <strong>{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</strong> ({{ $nhom->MaTruongNhom }})</div>
                            <div class="small text-muted"><i class="fa-solid fa-users me-1"></i>Số thành viên: {{ $nhom->thanhViens->count() + 1 }} SV</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen ?? 'Chưa phân công' }}</div>
                            <div class="small text-muted">{{ $nhom->dangKyDeTai->giangVienHuongDan->MaGV ?? '' }}</div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span>Tiến độ:</span>
                                <span class="text-primary">{{ $nhom->progress_percent }}%</span>
                            </div>
                            <div class="progress shadow-sm mb-1" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $nhom->progress_percent >= 100 ? 'bg-primary' : ($nhom->status_code == 'QUA_HAN' ? 'bg-danger' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ $nhom->progress_percent }}%"></div>
                            </div>
                            <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i>{{ $nhom->nearest_deadline }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $nhom->status_badge }} rounded-pill px-3 py-1">
                                {{ $nhom->status_label }}
                            </span>
                            @if($nhom->warning_msg)
                                <div class="small text-danger fw-bold mt-1" style="font-size: 0.75rem;"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $nhom->warning_msg }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('admin.theodoi.show', $nhom->MaNhom) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Xem Chi Tiết & Timeline 12 Mốc">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Nút Nhắc Nhóm -->
                                <form action="{{ route('admin.theodoi.remind', $nhom->MaNhom) }}" method="POST" class="d-inline" onsubmit="return confirm('Gửi thông báo nhắc nhở tiến độ tới nhóm {{ $nhom->MaNhom }} và GVHD?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light text-warning btn-action-circle" title="Gửi thông báo nhắc nhở tiến độ">
                                        <i class="fa-solid fa-bell"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-chart-line"></i>
                                <h6>Không tìm thấy nhóm đồ án nào phù hợp</h6>
                                <p class="small text-muted mb-0">Thử thay đổi từ khóa hoặc bộ lọc trạng thái.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
