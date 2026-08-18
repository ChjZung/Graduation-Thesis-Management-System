@extends('layouts.admin')
@section('page_title', 'Chi Tiết Hội Đồng — ' . $hoiDong->TenHoiDong)
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Thông tin Hội đồng -->
    <div class="col-md-5">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-landmark me-2 text-primary"></i>{{ $hoiDong->TenHoiDong }}</span>
                @php
                    $badgeClass = match($hoiDong->TrangThai) {
                        'Đang diễn ra' => 'bg-success',
                        'Đã kết thúc'  => 'bg-secondary',
                        default        => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} rounded-pill">{{ $hoiDong->TrangThai }}</span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted fw-semibold">Bắt đầu:</td><td>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Kết thúc:</td><td>{{ \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Địa điểm:</td><td>{{ $hoiDong->DiaDiem ?? '—' }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Ghi chú:</td><td>{{ $hoiDong->GhiChu ?? '—' }}</td></tr>
                </table>

                <!-- Cập nhật trạng thái -->
                <form action="{{ route('admin.hoidong.updateTrangThai', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex gap-2 mt-2">
                    @csrf
                    <select name="TrangThai" class="form-select form-select-sm">
                        <option {{ $hoiDong->TrangThai === 'Chưa diễn ra' ? 'selected' : '' }}>Chưa diễn ra</option>
                        <option {{ $hoiDong->TrangThai === 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                        <option {{ $hoiDong->TrangThai === 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Lưu</button>
                </form>
            </div>
        </div>

        <!-- Thành viên Hội đồng -->
        <div class="card card-premium mt-3">
            <div class="card-header-premium"><i class="fa-solid fa-users me-2 text-primary"></i>Thành Viên Hội Đồng</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Giảng Viên</th><th>Vai Trò</th></tr></thead>
                    <tbody>
                        @foreach($hoiDong->thanhViens as $tv)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $tv->giangVien->HoTen ?? '' }}</div>
                                <div class="small text-muted">{{ $tv->giangVien->HocVi ?? '' }}</div>
                            </td>
                            <td>
                                @php
                                    $vc = match($tv->VaiTro) {
                                        'Chủ tịch' => 'bg-danger',
                                        'Thư ký'   => 'bg-info text-dark',
                                        'Phản biện'=> 'bg-warning text-dark',
                                        default    => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $vc }} rounded-pill">{{ $tv->VaiTro }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danh sách nhóm bảo vệ -->
    <div class="col-md-7">
        <div class="card card-premium">
            <div class="card-header-premium"><i class="fa-solid fa-folder-open me-2 text-primary"></i>Nhóm Sinh Viên Bảo Vệ</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nhóm / Đề Tài</th>
                            <th>Trưởng Nhóm</th>
                            <th class="text-center">Hồ Sơ</th>
                            <th class="text-center">Phân Công GV PB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hoiDong->hoSoBaoVes as $hoSo)
                        <tr>
                            <td>
                                <div class="fw-bold text-primary-custom">{{ $hoSo->nhom->TenNhom ?? '' }}</div>
                                <div class="small text-muted">{{ $hoSo->nhom->deTai->TenDeTai ?? '' }}</div>
                            </td>
                            <td>{{ $hoSo->nhom->truongNhom->HoTen ?? '' }}</td>
                            <td class="text-center">
                                @if($hoSo->MinhChungDaoVan)
                                <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill">PDF</a>
                                @endif
                                <div class="small mt-1">Turnitin: <strong>{{ $hoSo->TyLeTrungLap }}%</strong></div>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="MaNhom" value="{{ $hoSo->MaNhom }}">
                                    <select name="MaGVPhanBien" class="form-select form-select-sm mb-1">
                                        <option value="">— Chọn GV Phản biện —</option>
                                        @foreach($giangViens as $gv)
                                        <option value="{{ $gv->MaGV }}" {{ $hoSo->MaGVPhanBien === $gv->MaGV ? 'selected' : '' }}>{{ $gv->HoTen }}</option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="btn btn-xs btn-primary rounded-pill" style="font-size: 0.75rem; padding: 2px 10px;">Lưu</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Chưa có nhóm nào được phân công vào Hội đồng này.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('admin.hoidong.index') }}" class="btn btn-light rounded-pill">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
    </a>
</div>
@endsection
