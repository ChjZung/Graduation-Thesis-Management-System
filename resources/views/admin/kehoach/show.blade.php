@extends('layouts.admin')

@section('page_title', 'Chi Tiết Kế Hoạch Khóa Luận')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
    
    <!-- Action Workflow Controls -->
    <div class="d-flex gap-2">
        <form action="{{ route('admin.kehoach.updateStatus', $keHoach->MaKeHoach) }}" method="POST" class="d-inline">
            @csrf
            @if($keHoach->TrangThai === 'NHÁP')
                <button type="submit" name="TrangThai" value="CHỜ DUYỆT" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-paper-plane me-1"></i> Gửi Chờ Duyệt
                </button>
                <button type="submit" name="TrangThai" value="ĐÃ CÔNG BỐ" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Kế Hoạch Ngay
                </button>
            @elseif($keHoach->TrangThai === 'CHỜ DUYỆT')
                <button type="submit" name="TrangThai" value="ĐÃ DUYỆT" class="btn btn-info text-white btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-check me-1"></i> Duyệt Kế Hoạch
                </button>
            @elseif($keHoach->TrangThai === 'ĐÃ DUYỆT')
                <button type="submit" name="TrangThai" value="ĐÃ CÔNG BỐ" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Kế Hoạch
                </button>
            @elseif($keHoach->TrangThai === 'ĐÃ CÔNG BỐ')
                <button type="submit" name="TrangThai" value="ĐANG THỰC HIỆN" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-play me-1"></i> Chuyển Sang Đang Thực Hiện
                </button>
            @elseif($keHoach->TrangThai === 'ĐANG THỰC HIỆN')
                <button type="submit" name="TrangThai" value="HOÀN THÀNH" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-flag-checkered me-1"></i> Đánh Dấu Hoàn Thành
                </button>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Header Banner Kế Hoạch -->
<div class="card card-premium mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill">{{ $keHoach->MaKeHoach }}</span>
                    @php
                        $badgeClass = match($keHoach->TrangThai) {
                            'NHÁP'          => 'bg-secondary',
                            'CHỜ DUYỆT'     => 'bg-warning text-dark',
                            'ĐÃ DUYỆT'      => 'bg-info text-white',
                            'ĐÃ CÔNG BỐ'    => 'bg-primary',
                            'ĐANG THỰC HIỆN'=> 'bg-success',
                            'HOÀN THÀNH'     => 'bg-dark',
                            default         => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6 px-3 py-1 rounded-pill">{{ $keHoach->TrangThai }}</span>
                </div>
                <h3 class="fw-bold text-primary mb-2">{{ $keHoach->TenKeHoach }}</h3>
                <p class="text-muted mb-0"><i class="fa-solid fa-graduation-cap me-1"></i> <strong>Học Kỳ:</strong> {{ $keHoach->hocKy->TenHocKy ?? $keHoach->MaHocKy }} (Năm học {{ $keHoach->NamHoc ?? '2026-2027' }}) | <strong>Phạm vi:</strong> {{ $keHoach->khoa->TenKhoa ?? 'Toàn trường / Khoa CNTT' }}</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="small text-muted mb-1"><i class="fa-regular fa-calendar-check me-1"></i>Thời gian thực hiện kế hoạch:</div>
                    <div class="fw-bold fs-6 text-dark">{{ $keHoach->NgayBatDau ? date('d/m/Y', strtotime($keHoach->NgayBatDau)) : '01/09/2026' }} <i class="fa-solid fa-arrow-right mx-1 text-muted"></i> {{ $keHoach->NgayKetThuc ? date('d/m/Y', strtotime($keHoach->NgayKetThuc)) : '10/12/2026' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timeline 12 Giai Đoạn Trực Quan -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-timeline text-primary me-2"></i> Timeline Visual & 12 Mốc Thời Gian Quy Trình</span>
        <span class="badge bg-light text-dark border rounded-pill px-3">{{ $keHoach->mocThoiGians->count() }} Giai đoạn</span>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="30%">Tên Giai Đoạn / Mốc Thời Gian</th>
                        <th width="15%">Đối Tượng</th>
                        <th width="20%">Thời Gian Bắt Đầu - Kết Thúc</th>
                        <th width="15%">Trạng Thái Mở</th>
                        <th width="15%">Mô Tả Chức Năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keHoach->mocThoiGians as $index => $moc)
                        @php
                            $statusInfo = \App\Services\PlanPhaseService::getPhaseStatus($moc);
                        @endphp
                        <tr class="{{ $statusInfo['is_open'] ? 'table-success bg-opacity-25' : '' }}">
                            <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-primary">{{ $moc->TenMoc }}</div>
                                <span class="badge bg-light text-secondary border small">{{ $moc->LoaiGiaiDoan }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border rounded-pill px-2">{{ $moc->DoiTuongThucHien ?? 'Tất cả' }}</span></td>
                            <td>
                                <div class="fw-semibold small"><i class="fa-regular fa-calendar-days me-1 text-primary"></i> {{ date('d/m/Y', strtotime($moc->NgayBatDau)) }}</div>
                                <div class="small text-muted"><i class="fa-solid fa-arrow-right me-1"></i> {{ date('d/m/Y', strtotime($moc->NgayKetThuc)) }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $statusInfo['badge'] }} rounded-pill px-3 py-2">
                                    {{ $statusInfo['label'] }}
                                </span>
                                @if($statusInfo['is_open'])
                                    <div class="small text-success fw-bold mt-1"><i class="fa-solid fa-clock me-1"></i>Còn {{ $statusInfo['days_left'] }} ngày</div>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $moc->MoTa ?? 'Thao tác giai đoạn tương ứng.' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Chưa có mốc thời gian nào được tạo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
