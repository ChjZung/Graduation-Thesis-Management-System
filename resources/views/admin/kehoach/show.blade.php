@extends('layouts.admin')

@section('page_title', 'Chi Tiết Kế Hoạch Khóa Luận')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
    
    <!-- Action Workflow Controls -->
    <div class="d-flex gap-2 flex-wrap align-items-center">
        {{-- Nút Công Bố Kế Hoạch (Gửi thông báo kèm file cho toàn hệ thống) --}}
        <form action="{{ route('admin.kehoach.publish', $keHoach->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận Công Bố Kế Hoạch này? Hệ thống sẽ phát hành thông báo kèm file công văn đính kèm cho toàn thể người dùng.');">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">
                <i class="fa-solid fa-bullhorn me-1"></i> {{ in_array($keHoach->TrangThai, ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN']) ? 'Phát Lại Thông Báo Công Bố' : 'Công Bố Kế Hoạch (Gửi Toàn Hệ Thống)' }}
            </button>
        </form>

        {{-- Cập nhật bằng upload file mới --}}
        <a href="{{ route('admin.kehoach.importDocument') }}?ma_hoc_ky={{ $keHoach->MaHocKy }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Cập Nhật Qua File Mới
        </a>

        {{-- Tải file công văn gốc nếu có --}}
        @if($keHoach->FileDinhKem)
        <a href="{{ asset('storage/' . $keHoach->FileDinhKem) }}" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> Tải File Công Văn Gốc
        </a>
        @endif

        {{-- Xóa kế hoạch --}}
        <form action="{{ route('admin.kehoach.destroy', $keHoach->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kế hoạch này?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-trash me-1"></i> Xóa
            </button>
        </form>
    </div>
</div>



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
                <p class="text-muted mb-0"><i class="fa-solid fa-graduation-cap me-1"></i> <strong>Học Kỳ:</strong> {{ $keHoach->hocKy->TenHocKy ?? $keHoach->MaHocKy }} | <strong>Phạm vi:</strong> {{ $keHoach->khoa->TenKhoa ?? 'Toàn trường / Khoa CNTT' }}</p>
                @if($keHoach->FileDinhKem)
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $keHoach->FileDinhKem) }}" target="_blank" class="badge bg-light text-danger border rounded-pill px-3 py-2 text-decoration-none shadow-sm">
                        <i class="fa-solid fa-file-pdf me-1"></i> Văn bản công văn gốc đính kèm: {{ basename($keHoach->FileDinhKem) }} (Bấm để tải về)
                    </a>
                </div>
                @endif
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

<!-- Timeline Các Mốc Thời Gian Quy Trình Trực Quan -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-timeline text-primary me-2"></i> Timeline Visual &amp; Các Mốc Thời Gian Quy Trình (Chuẩn Thông Báo Khoa)</span>
        <span class="badge bg-light text-dark border rounded-pill px-3">{{ $keHoach->mocThoiGians->count() }} Mốc quy trình</span>
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

<!-- Khối Tiêu Chuẩn & Quy Định Kế Hoạch -->
<div class="card border-0 shadow-sm rounded-3 mt-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-primary"></i>
            <span>Quy Định &amp; Tiêu Chuẩn Áp Dụng Cho Kế Hoạch Này</span>
        </h6>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.quydinh.create') }}?make_hoach={{ $keHoach->MakeHoach }}" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="fa-solid fa-plus me-1"></i> Thêm Quy Định
            </a>
            <a href="{{ route('admin.quydinh.index', ['make_hoach' => $keHoach->MakeHoach]) }}" class="btn btn-sm btn-light border rounded-pill">
                Xem Tất Cả Quy Định
            </a>
        </div>
    </div>
    <div class="card-body p-3">
        @if($keHoach->quyDinhs && $keHoach->quyDinhs->count() > 0)
            <div class="row g-3">
                @foreach($keHoach->quyDinhs as $qd)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded bg-light border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge-code">{{ $qd->MaQuyDinh }}</span>
                                    <span class="badge bg-primary rounded-pill px-2 py-1">{{ $qd->GiaTri }}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ $qd->TenQuyDinh }}</h6>
                                <p class="small text-muted mb-0" style="line-height: 1.5;">{{ $qd->MoTa ?? 'Không có mô tả chi tiết.' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="fa-solid fa-scale-unbalanced fs-3 mb-2 opacity-50"></i>
                <div>Chưa thiết lập tiêu chuẩn quy chế riêng cho kế hoạch này.</div>
                <form action="{{ route('admin.quydinh.initDefaults') }}" method="POST" class="d-inline mt-2">
                    @csrf
                    <input type="hidden" name="MakeHoach" value="{{ $keHoach->MakeHoach }}">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 mt-2 fw-semibold">
                        <i class="fa-solid fa-scale-balanced me-1"></i> Nạp 8 Quy Định &amp; Tiêu Chuẩn Chuẩn
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
