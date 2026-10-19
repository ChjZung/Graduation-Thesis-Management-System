@extends('layouts.admin')

@section('page_title', 'Quản Lý Hội Đồng Bảo Vệ — Danh Sách Hội Đồng')

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section (Dành cho Giáo vụ: Theo dõi, Tra cứu & Xuất báo cáo) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold">
                    <i class="fa-solid fa-clipboard-check me-1"></i> Quản lý hội đồng bảo vệ
                </span>
                <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">Chế độ Giáo vụ: Tra cứu &amp; Báo cáo</span>
            </div>
            <h4 class="fw-bold mb-0" style="color: #00305a;">
                <i class="fa-solid fa-landmark text-primary me-2"></i>Danh Sách Hội Đồng Bảo Vệ Khóa Luận
            </h4>
            <div class="text-muted small mt-1">
                Theo dõi tiến độ, tra cứu thành viên, xem danh sách đề tài và tải văn bản quyết định do Trưởng khoa ban hành.
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.hoidong.exportReport', request()->query()) }}" class="btn btn-outline-success shadow-xs fw-semibold d-inline-flex align-items-center rounded-pill px-3">
                <i class="fa-solid fa-file-excel me-2"></i> Xuất Báo Cáo (Excel/CSV)
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Số Hội Đồng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-landmark fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDongs->total() }}</span>
                    <span class="small text-muted fw-medium">hội đồng</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đã Công Bố Chính Thức</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-bullhorn fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $hoiDongs->where('TrangThaiCongBo', 'Đã công bố')->count() }}</span>
                    <span class="small text-muted fw-medium">hội đồng ban hành</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đề Tài / Nhóm Đã Gắn</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-graduation-cap fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDongs->sum(fn($h) => $h->hoSoBaoVes->count()) }}</span>
                    <span class="small text-muted fw-medium">đề tài bảo vệ</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Chưa Công Bố (Nháp)</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-file-lines fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-primary lh-1">{{ $hoiDongs->where('TrangThaiCongBo', '!=', 'Đã công bố')->count() }}</span>
                    <span class="small text-muted fw-medium">đang chuẩn bị</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 bg-white">
        <form method="GET" action="{{ route('admin.hoidong.index') }}" class="d-flex flex-wrap align-items-center gap-2 w-100">
            <div class="input-group" style="min-width: 240px; flex: 2;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm tên Hội đồng, mã HĐ, địa điểm, số QĐ...">
            </div>

            <select name="MaHocKy" class="form-select" style="min-width: 170px; flex: 1;" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                @foreach($hocKys as $hk)
                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                </option>
                @endforeach
            </select>

            <select name="TrangThaiCongBo" class="form-select" style="min-width: 170px; flex: 1;" onchange="this.form.submit()">
                <option value="">-- Trạng thái công bố --</option>
                <option value="Đã công bố" {{ request('TrangThaiCongBo') === 'Đã công bố' ? 'selected' : '' }}>🟢 Đã công bố</option>
                <option value="Chưa công bố" {{ request('TrangThaiCongBo') === 'Chưa công bố' ? 'selected' : '' }}>🟡 Bản nháp (Chưa công bố)</option>
                <option value="Đã thu hồi" {{ request('TrangThaiCongBo') === 'Đã thu hồi' ? 'selected' : '' }}>🔴 Đã thu hồi</option>
            </select>

            <select name="TrangThai" class="form-select" style="min-width: 160px; flex: 1;" onchange="this.form.submit()">
                <option value="">-- Trạng thái diễn ra --</option>
                <option value="Chưa diễn ra" {{ request('TrangThai') === 'Chưa diễn ra' ? 'selected' : '' }}>Chưa diễn ra</option>
                <option value="Đang diễn ra" {{ request('TrangThai') === 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="Đã kết thúc" {{ request('TrangThai') === 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
            </select>

            @if(request('search') || request('TrangThai') || request('TrangThaiCongBo') || request('MaHocKy') || request('NgayBaoVe'))
                <a href="{{ route('admin.hoidong.index') }}" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center">
                    <i class="fa-solid fa-xmark me-2"></i>Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Hội Đồng Bảo Vệ Khóa Luận</span>
            <span class="small text-muted">Tổng số: <strong>{{ $hoiDongs->total() }}</strong> hội đồng</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 95px;">MÃ HĐ</th>
                            <th style="min-width: 230px;">TÊN HỘI ĐỒNG &amp; VĂN BẢN QUYẾT ĐỊNH</th>
                            <th style="min-width: 175px;">THỜI GIAN &amp; PHÒNG</th>
                            <th style="min-width: 290px;">THÀNH VIÊN HỘI ĐỒNG</th>
                            <th class="text-center" style="width: 110px;">ĐỀ TÀI / NHÓM</th>
                            <th class="text-center" style="width: 130px;">CÔNG BỐ</th>
                            <th class="text-center" style="width: 115px;">DIỄN RA</th>
                            <th class="pe-3 text-center" style="width: 160px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($hoiDongs as $hd)
                        <tr>
                            {{-- Mã HĐ --}}
                            <td class="ps-3 py-3">
                                <span class="badge bg-light text-primary border fw-bold px-2 py-1 font-monospace">{{ $hd->MaHoiDong }}</span>
                            </td>

                            {{-- Tên Hội đồng & Quyết định --}}
                            <td class="py-3">
                                <div class="fw-bold text-dark fs-6">{{ $hd->TenHoiDong }}</div>
                                <div class="small text-muted mt-1 d-flex flex-wrap gap-2 align-items-center">
                                    <span><i class="fa-solid fa-calendar me-1 text-secondary"></i>{{ $hd->hocKy?->TenHocKy ?? $hd->MaHocKy }}</span>
                                    <span>• Đợt: <strong>{{ $hd->DotBaoVe ?? '1' }}</strong></span>
                                    @if($hd->SoQuyetDinh)
                                    <span class="badge bg-light text-dark border">Số QĐ: {{ $hd->SoQuyetDinh }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Thời gian & Phòng --}}
                            <td class="py-3">
                                <div class="small fw-semibold text-dark">
                                    <i class="fa-regular fa-calendar-days me-1 text-primary"></i>
                                    {{ $hd->NgayBaoVe ? \Carbon\Carbon::parse($hd->NgayBaoVe)->format('d/m/Y') : (\Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('d/m/Y')) }}
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i') }} - {{ \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('H:i') }}
                                </div>
                                <div class="small text-danger mt-1">
                                    <i class="fa-solid fa-location-dot me-1"></i>
                                    {{ $hd->DiaDiem ?? 'Chưa xếp phòng' }}
                                </div>
                            </td>

                            {{-- Thành viên Hội đồng --}}
                            <td class="py-3">
                                <div class="d-flex flex-column gap-1.5" style="font-size: 0.82rem;">
                                    @forelse($hd->thanhViens->take(3) as $tv)
                                    @php
                                        $vt = $tv->VaiTro;
                                        $shortVt = match($vt) {
                                            'Ủy viên phản biện', 'Phản biện' => 'Phản biện',
                                            'Ủy viên thư ký', 'Thư ký' => 'Thư ký',
                                            'Chủ tịch' => 'Chủ tịch',
                                            default => 'Ủy viên',
                                        };
                                        $badgeBg = match($shortVt) {
                                            'Chủ tịch' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            'Phản biện' => 'bg-primary-subtle text-primary border-primary-subtle',
                                            'Thư ký' => 'bg-info-subtle text-info border-info-subtle',
                                            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 text-nowrap">
                                        <span class="badge {{ $badgeBg }} border px-2 py-1 fw-semibold flex-shrink-0" style="font-size: 0.72rem; min-width: 68px; text-align: center;">
                                            {{ $shortVt }}
                                        </span>
                                        <span class="fw-semibold text-dark text-truncate" title="{{ $tv->giangVien->HoTen ?? '' }} ({{ $tv->VaiTro }})">
                                            {{ $tv->giangVien->HoTen ?? '—' }}
                                        </span>
                                    </div>
                                    @empty
                                    <span class="text-muted fst-italic small">Chưa phân công thành viên</span>
                                    @endforelse
                                    @if($hd->thanhViens->count() > 3)
                                        <div class="text-muted small ps-1" style="font-size: 0.75rem;">
                                            +{{ $hd->thanhViens->count() - 3 }} thành viên khác
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Đề tài / Nhóm bảo vệ --}}
                            <td class="text-center py-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-users me-1"></i>{{ $hd->hoSoBaoVes->count() }} đề tài
                                </span>
                            </td>

                            {{-- Trạng thái công bố --}}
                            <td class="text-center py-3">
                                @if($hd->TrangThaiCongBo === 'Đã công bố')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đã công bố
                                    </span>
                                @elseif($hd->TrangThaiCongBo === 'Đã thu hồi')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Đã thu hồi
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-pen-ruler me-1"></i> Bản nháp
                                    </span>
                                @endif
                            </td>

                            {{-- Trạng thái diễn ra --}}
                            <td class="text-center py-3">
                                @if($hd->TrangThai === 'Đã kết thúc')
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">Đã kết thúc</span>
                                @elseif($hd->TrangThai === 'Đang diễn ra')
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1 small">Đang chấm</span>
                                @else
                                    <span class="badge bg-info-subtle text-info border rounded-pill px-2 py-1 small">Chưa diễn ra</span>
                                @endif
                            </td>

                            {{-- Thao tác dành cho Giáo vụ --}}
                            <td class="pe-3 text-center py-3">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <a href="{{ route('admin.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Xem chi tiết hội đồng">
                                        <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                    </a>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border rounded-circle" type="button" data-bs-toggle="dropdown" title="Tải / Xem văn bản">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.hoidong.previewPdf', $hd->MaHoiDong) }}" target="_blank">
                                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>Xem trước văn bản PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.hoidong.downloadPdf', $hd->MaHoiDong) }}">
                                                    <i class="fa-solid fa-download text-primary me-2"></i>Tải tệp PDF công bố
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-landmark text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Chưa tìm thấy Hội đồng bảo vệ nào phù hợp</h6>
                                    <p class="small text-muted mb-0">Việc lập hội đồng và ban hành quyết định do Trưởng khoa phụ trách.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $hoiDongs->firstItem() ?? 0 }}–{{ $hoiDongs->lastItem() ?? 0 }} trên tổng số {{ $hoiDongs->total() }} hội đồng
            </span>
            @if($hoiDongs->hasPages())
                <div>
                    {{ $hoiDongs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
