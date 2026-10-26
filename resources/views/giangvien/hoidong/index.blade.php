@extends('layouts.giangvien')

@section('page_title', 'Hội Đồng Bảo Vệ Của Tôi')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark, #003366);">
                <i class="fa-solid fa-users-rectangle me-2 text-primary"></i>Hội Đồng Bảo Vệ Của Tôi
            </h4>
            <div class="text-muted small">
                Danh sách các hội đồng chấm khóa luận tốt nghiệp Thầy/Cô được phân công tham gia (Chủ tịch, Thư ký, Phản biện, Ủy viên).
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giangvien.chamdiem.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs">
                <i class="fa-solid fa-calculator me-1"></i> Vào Phòng Chấm Điểm
            </a>
        </div>
    </div>

    <!-- 4 Khối KPI Thống Kê Nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Được Phân Công</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ $counts['tong_cong'] }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">1 hội đồng = 1 lượt</div>
                    </div>
                    <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                        <i class="fa-solid fa-clipboard-list fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Sắp Diễn Ra</div>
                        <div class="fs-3 fw-bold text-warning mt-1">{{ $counts['sap_dien_ra'] }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Chuẩn bị hồ sơ</div>
                    </div>
                    <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-circle">
                        <i class="fa-solid fa-calendar-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Đang Diễn Ra</div>
                        <div class="fs-3 fw-bold text-info mt-1">{{ $counts['dang_dien_ra'] }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Phiên bảo vệ hôm nay</div>
                    </div>
                    <div class="p-3 bg-info-subtle text-info-emphasis rounded-circle">
                        <i class="fa-solid fa-spinner fa-spin fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Đã Hoàn Thành</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ $counts['da_hoan_thanh'] }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Đã chấm &amp; tổng hợp</div>
                    </div>
                    <div class="p-3 bg-success-subtle text-success rounded-circle">
                        <i class="fa-solid fa-circle-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh Thống Kê Theo Vai Trò -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small fw-bold text-secondary text-uppercase">
                <i class="fa-solid fa-user-tag me-1 text-primary"></i>Phân Bổ Vai Trò Của Thầy/Cô:
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill font-monospace">
                    Chủ tịch: <strong>{{ $counts['chu_tich'] }}</strong>
                </span>
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2 rounded-pill font-monospace">
                    Thư ký: <strong>{{ $counts['thu_ky'] }}</strong>
                </span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace">
                    Phản biện: <strong>{{ $counts['phan_bien'] }}</strong>
                </span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill font-monospace">
                    Ủy viên: <strong>{{ $counts['uy_vien'] }}</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Bộ Lọc Đa Tiêu Chí -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form action="{{ route('giangvien.hoidong.index') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Tên HĐ, phòng, đề tài..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="MaHocKy" class="form-select form-select-sm">
                            <option value="ALL">-- Tất cả học kỳ --</option>
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="VaiTro" class="form-select form-select-sm">
                            <option value="ALL">-- Tất cả vai trò --</option>
                            <option value="Chủ tịch" {{ request('VaiTro') == 'Chủ tịch' ? 'selected' : '' }}>Chủ tịch</option>
                            <option value="Thư ký" {{ request('VaiTro') == 'Thư ký' ? 'selected' : '' }}>Thư ký</option>
                            <option value="Phản biện" {{ request('VaiTro') == 'Phản biện' ? 'selected' : '' }}>Phản biện</option>
                            <option value="Ủy viên" {{ request('VaiTro') == 'Ủy viên' ? 'selected' : '' }}>Ủy viên</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="TrangThai" class="form-select form-select-sm">
                            <option value="ALL">-- Tất cả trạng thái --</option>
                            <option value="SapDienRa" {{ request('TrangThai') == 'SapDienRa' ? 'selected' : '' }}>Sắp diễn ra</option>
                            <option value="DangDienRa" {{ request('TrangThai') == 'DangDienRa' ? 'selected' : '' }}>Đang diễn ra</option>
                            <option value="DaHoanThanh" {{ request('TrangThai') == 'DaHoanThanh' ? 'selected' : '' }}>Đã hoàn thành</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-grow-1 fw-bold">
                            <i class="fa-solid fa-filter me-1"></i> Lọc
                        </button>
                        <a href="{{ route('giangvien.hoidong.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh Sách Hội Đồng -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>Danh Sách Hội Đồng Được Phân Công ({{ $hoiDongs->total() }})
            </span>
            <span class="text-muted small">Hiển thị trang {{ $hoiDongs->currentPage() }} / {{ $hoiDongs->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($hoiDongs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6 class="fw-bold">Thầy/Cô chưa có hội đồng nào phù hợp với bộ lọc hiện tại.</h6>
                    <p class="small text-muted mb-0">Vui lòng thử chọn học kỳ hoặc trạng thái khác.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 280px;">Hội Đồng &amp; Lịch Bảo Vệ</th>
                                <th style="width: 140px;" class="text-center">Vai Trò Của Thầy/Cô</th>
                                <th style="min-width: 220px;">Thành Viên Hội Đồng</th>
                                <th style="width: 130px;" class="text-center">Số Nhóm Đề Tài</th>
                                <th style="width: 140px;" class="text-center">Trạng Thái</th>
                                <th style="width: 160px;" class="text-end pe-4">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoiDongs as $hd)
                                @php
                                    $myTv = $hd->thanhViens->firstWhere('MaGV', $gv->MaGV);
                                    $myRole = $myTv?->VaiTro ?? 'Thành viên';
                                    $isThuKyThis = ($myRole === 'Thư ký' || mb_stripos($myRole, 'thư ký') !== false);
                                    
                                    $today = now()->toDateString();
                                    $isToday = $hd->NgayBaoVe && $hd->NgayBaoVe->toDateString() === $today;
                                    $isPast = $hd->NgayBaoVe && $hd->NgayBaoVe->lt($today);
                                    $isFuture = $hd->NgayBaoVe && $hd->NgayBaoVe->gt($today);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold" style="color: #003366;">
                                            <a href="{{ route('giangvien.hoidong.show', $hd->MaHoiDong) }}" class="text-decoration-none text-primary">
                                                {{ $hd->TenHoiDong }}
                                            </a>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i>Phòng: <strong>{{ $hd->DiaDiem ?? 'Phòng B.304' }}</strong>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="fa-regular fa-clock me-1 text-primary"></i>
                                            @if($hd->NgayBaoVe)
                                                <strong>{{ $hd->NgayBaoVe->format('d/m/Y') }}</strong>
                                            @endif
                                            @if($hd->ThoiGianBatDau)
                                                ({{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i') }} - {{ $hd->ThoiGianKetThuc ? \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('H:i') : '17:00' }})
                                            @endif
                                        </div>
                                        @if($hd->SoQuyetDinh)
                                            <div class="small text-muted mt-1 font-monospace" style="font-size: 0.725rem;">
                                                QĐ: {{ $hd->SoQuyetDinh }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($myRole === 'Chủ tịch')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-bold">
                                                <i class="fa-solid fa-crown me-1"></i> Chủ Tịch
                                            </span>
                                        @elseif($myRole === 'Thư ký')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-bold">
                                                <i class="fa-solid fa-pen-nib me-1"></i> Thư Ký
                                            </span>
                                        @elseif($myRole === 'Phản biện')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold">
                                                <i class="fa-solid fa-user-check me-1"></i> Phản Biện
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-bold">
                                                <i class="fa-solid fa-user me-1"></i> Ủy Viên
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1" style="font-size: 0.78rem;">
                                            @foreach($hd->thanhViens->take(4) as $tv)
                                                <div class="text-truncate" style="max-width: 250px;">
                                                    <span class="badge bg-light text-secondary border px-1 py-0 me-1" style="font-size: 0.65rem;">{{ $tv->VaiTro }}</span>
                                                    <span class="fw-semibold text-dark">{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</span>
                                                </div>
                                            @endforeach
                                            @if($hd->thanhViens->count() > 4)
                                                <div class="text-muted fst-italic" style="font-size: 0.72rem;">
                                                    +{{ $hd->thanhViens->count() - 4 }} thành viên khác...
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace">
                                            {{ $hd->hoSoBaoVes->count() }} nhóm
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($hd->TrangThai === 'Đã kết thúc' || $hd->TrangThai === 'Đã hoàn thành' || ($isPast && !$hd->TrangThai))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-check me-1"></i> Đã Hoàn Thành
                                            </span>
                                        @elseif($hd->TrangThai === 'Đang diễn ra' || $isToday)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Đang Diễn Ra
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                                <i class="fa-regular fa-clock me-1"></i> Sắp Diễn Ra
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1 flex-wrap">
                                            <a href="{{ route('giangvien.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                                                <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                            </a>
                                            <a href="{{ route('giangvien.chamdiem.index', ['MaHoiDong' => $hd->MaHoiDong]) }}" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs">
                                                <i class="fa-solid fa-calculator me-1"></i> Chấm điểm
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    {{ $hoiDongs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
