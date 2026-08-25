@extends('layouts.admin')

@section('page_title', 'Lịch Sử Phiên Bản Văn Bản Thông Báo')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách kế hoạch
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Header Banner -->
<div class="card card-premium mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge-code me-2">Mã VB: {{ $vanBan->MaVanBan }}</span>
                <span class="badge bg-success rounded-pill px-3 py-1">Phiên bản v{{ $vanBan->PhienBanHienTai }}</span>
                <h3 class="fw-bold text-primary mt-2 mb-1">{{ $vanBan->TenVanBan }}</h3>
                <p class="text-muted mb-0"><i class="fa-solid fa-file-pdf text-danger me-1"></i>Số thông báo: <strong>{{ $vanBan->SoThongBao ?? 'N/A' }}</strong> | Kế hoạch: <strong>{{ $vanBan->keHoach->TenKeHoach ?? 'Khóa luận' }}</strong></p>
            </div>
            <div>
                <a href="{{ asset($vanBan->FileGocPath) }}" target="_blank" class="btn btn-danger btn-lg rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-download me-1"></i> Tải File Gốc Mới Nhất
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Version History Audit List -->
<div class="card card-premium mb-4">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-timeline text-primary me-2"></i> Lịch Sử Thay Đổi Các Phiên Bản (Version Audit Log)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="10%">Phiên Bản</th>
                        <th width="18%">Thời Gian Ban Hành</th>
                        <th width="12%">Người Upload</th>
                        <th width="35%">Chi Tiết Thay Đổi So Với Phiên Bản Trước</th>
                        <th width="25%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vanBan->versions as $ver)
                    <tr class="{{ $ver->PhienBan == $vanBan->PhienBanHienTai ? 'bg-primary bg-opacity-10' : '' }}">
                        <td>
                            <span class="badge {{ $ver->PhienBan == $vanBan->PhienBanHienTai ? 'bg-primary' : 'bg-secondary' }} fs-6 px-3 py-1 rounded-pill">v{{ $ver->PhienBan }}</span>
                            @if($ver->PhienBan == $vanBan->PhienBanHienTai)
                                <div class="small text-primary fw-bold mt-1">Đang áp dụng</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><i class="fa-regular fa-clock me-1"></i>{{ $ver->created_at ? $ver->created_at->format('d/m/Y H:i') : '---' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $ver->NguoiThayDoi ?? 'ADMIN' }}</span>
                        </td>
                        <td>
                            <div class="small fw-bold text-secondary mb-1">{{ $ver->LyDoThayDoi ?? 'Nạp phiên bản văn bản' }}</div>
                            @if(!empty($ver->ThayDoiSoVoiTruocJson))
                                <ul class="mb-0 small text-danger ps-3">
                                    @foreach($ver->ThayDoiSoVoiTruocJson as $d)
                                        <li>{{ $d['phase_name'] ?? $d['TenMoc'] }}: {{ $d['old_range'] ?? $d['Truoc'] }} ➔ <strong class="text-success">{{ $d['new_range'] ?? $d['Sau'] }}</strong></li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small">Ban hành phiên bản khởi tạo ban đầu.</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ asset($ver->FileGocPath) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                    <i class="fa-solid fa-file-pdf me-1"></i> File v{{ $ver->PhienBan }}
                                </a>

                                @if($ver->PhienBan != $vanBan->PhienBanHienTai)
                                <form action="{{ route('admin.kehoach.documentRollback', [$vanBan->MaVanBan, $ver->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận khôi phục mốc thời gian kế hoạch về phiên bản v{{ $ver->PhienBan }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục v{{ $ver->PhienBan }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Chưa có lịch sử phiên bản nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
