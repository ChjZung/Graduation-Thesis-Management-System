@extends('layouts.admin')
@section('page_title', 'Quản Lý Hội Đồng Bảo Vệ')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-landmark me-2 text-primary"></i>Danh Sách Hội Đồng Bảo Vệ</span>
        <a href="{{ route('admin.hoidong.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i>Thành Lập Hội Đồng Mới
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="5%">Mã</th>
                        <th width="28%">Tên Hội Đồng</th>
                        <th width="20%">Thời Gian</th>
                        <th width="15%">Địa Điểm</th>
                        <th width="12%">Thành Viên</th>
                        <th width="10%" class="text-center">Trạng Thái</th>
                        <th width="10%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hoiDongs as $hd)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $hd->MaHoiDong }}</span></td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $hd->TenHoiDong }}</div>
                            <div class="small text-muted">{{ $hd->hoSoBaoVes->count() }} nhóm bảo vệ</div>
                        </td>
                        <td>
                            <div class="small"><i class="fa-solid fa-play text-success me-1"></i>{{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('d/m/Y H:i') }}</div>
                            <div class="small"><i class="fa-solid fa-stop text-danger me-1"></i>{{ \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>{{ $hd->DiaDiem ?? '—' }}</td>
                        <td>
                            @foreach($hd->thanhViens->take(3) as $tv)
                            <div class="small">{{ $tv->giangVien->HoTen ?? '' }} <span class="text-muted">({{ $tv->VaiTro }})</span></div>
                            @endforeach
                            @if($hd->thanhViens->count() > 3)
                            <div class="small text-muted">+{{ $hd->thanhViens->count() - 3 }} thành viên khác</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $badgeClass = match($hd->TrangThai) {
                                    'Đang diễn ra' => 'bg-success',
                                    'Đã kết thúc'  => 'bg-secondary',
                                    default        => 'bg-warning text-dark',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill">{{ $hd->TrangThai }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="fa-solid fa-eye me-1"></i>Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Chưa có Hội đồng nào được thành lập.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($hoiDongs->hasPages())
    <div class="card-footer bg-white border-0 py-3">{{ $hoiDongs->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
