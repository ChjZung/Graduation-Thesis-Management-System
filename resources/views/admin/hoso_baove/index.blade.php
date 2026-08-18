@extends('layouts.admin')
@section('page_title', 'Quản Lý Hồ Sơ Bảo Vệ')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-folder-check me-2 text-primary"></i>Hồ Sơ Bảo Vệ Của Các Nhóm Sinh Viên</span>
        <div class="btn-group">
            <a href="{{ route('admin.hosoBaoVe.index') }}" class="btn btn-sm {{ !request('TrangThai') ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}">Tất cả</a>
            <a href="{{ route('admin.hosoBaoVe.index', ['TrangThai' => 'Chờ xác nhận']) }}" class="btn btn-sm {{ request('TrangThai') === 'Chờ xác nhận' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Chờ xác nhận</a>
            <a href="{{ route('admin.hosoBaoVe.index', ['TrangThai' => 'Đã phân công']) }}" class="btn btn-sm {{ request('TrangThai') === 'Đã phân công' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">Đã phân công</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="22%">Nhóm / Đề Tài</th>
                        <th width="12%" class="text-center">Turnitin %</th>
                        <th width="13%">Hội Đồng</th>
                        <th width="15%">GV Phản Biện</th>
                        <th width="10%" class="text-center">Trạng Thái</th>
                        <th width="28%" class="text-center">Phân Công</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hoSos as $hs)
                    <tr>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $hs->nhom->TenNhom ?? '' }}</div>
                            <div class="small text-muted">{{ Str::limit($hs->nhom->deTai->TenDeTai ?? '', 50) }}</div>
                            <div class="small text-muted">TN: {{ $hs->nhom->truongNhom->HoTen ?? '' }}</div>
                        </td>
                        <td class="text-center">
                            @php $pct = (float)$hs->TyLeTrungLap; @endphp
                            <span class="fw-bold fs-5 {{ $pct > 30 ? 'text-danger' : ($pct > 15 ? 'text-warning' : 'text-success') }}">{{ $pct }}%</span>
                            @if($hs->MinhChungDaoVan)
                            <div class="mt-1"><a href="{{ asset('storage/' . $hs->MinhChungDaoVan) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill" style="font-size: 0.72rem; padding: 2px 8px;">Xem PDF</a></div>
                            @endif
                        </td>
                        <td>{{ $hs->hoiDong->TenHoiDong ?? '—' }}</td>
                        <td>{{ $hs->giangVienPhanBien->HoTen ?? '—' }}</td>
                        <td class="text-center">
                            @php
                                $bc = match($hs->TrangThai) {
                                    'Đã phân công' => 'bg-success',
                                    'Đã xác nhận'  => 'bg-primary',
                                    default        => 'bg-warning text-dark',
                                };
                            @endphp
                            <span class="badge {{ $bc }} rounded-pill" style="font-size: 0.72rem;">{{ $hs->TrangThai }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.hosoBaoVe.phanCong', $hs->MaHoSo) }}" method="POST" class="d-flex gap-1 align-items-center">
                                @csrf
                                <select name="MaHoiDong" class="form-select form-select-sm" required>
                                    <option value="">— HĐ —</option>
                                    @foreach($hoiDongs as $hd)
                                    <option value="{{ $hd->MaHoiDong }}" {{ $hs->MaHoiDong === $hd->MaHoiDong ? 'selected' : '' }}>{{ $hd->TenHoiDong }}</option>
                                    @endforeach
                                </select>
                                <select name="MaGVPhanBien" class="form-select form-select-sm">
                                    <option value="">— GV PB —</option>
                                    @foreach($giangViens as $gv)
                                    <option value="{{ $gv->MaGV }}" {{ $hs->MaGVPhanBien === $gv->MaGV ? 'selected' : '' }}>{{ $gv->HoTen }}</option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn btn-sm btn-success rounded-pill flex-shrink-0">Lưu</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Chưa có hồ sơ bảo vệ nào được nộp.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($hoSos->hasPages())
    <div class="card-footer bg-white border-0 py-3">{{ $hoSos->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
