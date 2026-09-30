@extends('layouts.giangvien')

@section('page_title', 'Quản Lý Thông Báo')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-primary-custom">
                <i class="fa-solid fa-bullhorn text-primary me-2"></i>Quản Lý Thông Báo & Nhắc Nhở Nhóm
            </h4>
            <p class="text-muted small mb-0">Tạo và gửi thông báo nhắc nhở tiến độ đến tất cả các nhóm hoặc từng nhóm đồ án hướng dẫn.</p>
        </div>
        <div>
            <a href="{{ route('giangvien.thongbao.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                <i class="fa-solid fa-paper-plane me-2"></i>Tạo Thông Báo Cho Nhóm
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- 3 KPI Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary h-100">
                <div class="text-muted small fw-medium mb-1">Thông báo đã gửi cho nhóm</div>
                <div class="fs-4 fw-bold text-primary">{{ $stats['da_gui'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-info h-100">
                <div class="text-muted small fw-medium mb-1">Thông báo nhận từ Nhà trường</div>
                <div class="fs-4 fw-bold text-info">{{ $stats['nhan_duoc'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success h-100">
                <div class="text-muted small fw-medium mb-1">Số nhóm đang hướng dẫn</div>
                <div class="fs-4 fw-bold text-success">{{ $stats['tong_so_nhom'] }} nhóm</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 fw-bold {{ $tab === 'da_gui' ? 'active shadow-sm' : 'bg-white text-secondary border' }}" 
               href="{{ route('giangvien.thongbao.index', ['tab' => 'da_gui']) }}">
                <i class="fa-solid fa-paper-plane me-2"></i>Thông Báo Đã Gửi Cho Nhóm ({{ $stats['da_gui'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 fw-bold {{ $tab === 'nhan_duoc' ? 'active shadow-sm' : 'bg-white text-secondary border' }}" 
               href="{{ route('giangvien.thongbao.index', ['tab' => 'nhan_duoc']) }}">
                <i class="fa-solid fa-inbox me-2"></i>Hộp Thư Từ Khoa / Nhà Trường ({{ $stats['nhan_duoc'] }})
            </a>
        </li>
    </ul>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('giangvien.thongbao.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tiêu đề, nội dung, đối tượng nhận..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <select name="LoaiThongBao" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả loại thông báo --</option>
                        <option value="Nhắc nhở" {{ request('LoaiThongBao') == 'Nhắc nhở' ? 'selected' : '' }}>Nhắc nhở tiến độ</option>
                        <option value="Lịch hẹn" {{ request('LoaiThongBao') == 'Lịch hẹn' ? 'selected' : '' }}>Lịch hẹn hướng dẫn</option>
                        <option value="Góp ý đề tài" {{ request('LoaiThongBao') == 'Góp ý đề tài' ? 'selected' : '' }}>Góp ý đề tài</option>
                        <option value="Kế hoạch" {{ request('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch</option>
                        <option value="Hệ thống" {{ request('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Lọc</button>
                    @if(request()->anyFilled(['search', 'LoaiThongBao']))
                        <a href="{{ route('giangvien.thongbao.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border" title="Xóa lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách Thông Báo -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small">
                        <tr>
                            <th width="5%" class="text-center">STT</th>
                            <th width="35%">Tiêu Đề</th>
                            <th width="15%">Loại Tin</th>
                            <th width="20%">Đối Tượng Nhận</th>
                            <th width="15%">Thời Gian</th>
                            <th width="10%" class="text-center">Chi Tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thongbaos as $idx => $tb)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $thongbaos->firstItem() + $idx }}</td>
                            <td>
                                <a href="{{ route('giangvien.thongbao.show', $tb->MaThongBao) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $tb->TieuDe }}
                                </a>
                                <div class="text-muted small text-truncate" style="max-width: 380px;">
                                    {{ Str::limit(strip_tags($tb->NoiDung), 80) }}
                                </div>
                                @if($tb->FileDinhKem)
                                    <span class="badge bg-light text-secondary border mt-1" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-paperclip me-1"></i>Có tệp đính kèm
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($tb->LoaiThongBao) {
                                        'Nhắc nhở' => 'bg-warning text-dark',
                                        'Lịch hẹn' => 'bg-info text-white',
                                        'Góp ý đề tài' => 'bg-primary',
                                        'Kế hoạch' => 'bg-success',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }} rounded-pill px-3 py-1">
                                    {{ $tb->LoaiThongBao ?? 'Thông báo' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border rounded-pill px-3 py-1 fw-semibold">
                                    <i class="fa-solid fa-users me-1"></i>{{ $tb->DoiTuongNhan ?? 'Tất cả' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <div><i class="fa-regular fa-clock me-1"></i>{{ $tb->created_at ? $tb->created_at->format('H:i d/m/Y') : ($tb->NgayTao ?? '') }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('giangvien.thongbao.show', $tb->MaThongBao) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-eye me-1"></i> Xem
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-envelope-open text-secondary fs-1 mb-2 d-block opacity-50"></i>
                                Chưa có thông báo nào trong mục này.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($thongbaos->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $thongbaos->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
