@extends('layouts.giangvien')

@section('page_title', 'Hội Đồng Bảo Vệ Của Tôi')

@push('styles')
<style>
/* ════ COUNCIL CARD GRID STYLES (HỘI ĐỒNG CỦA TÔI) ════ */
.council-card {
    transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e2e8f0 !important;
}

.council-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 51, 102, 0.09) !important;
    border-color: #cbd5e1 !important;
}

.council-card .card-header {
    background-color: #ffffff;
    border-bottom: 1px solid #f1f5f9;
}

.council-card .card-footer {
    background-color: #fafbfc;
    border-top: 1px solid #f1f5f9;
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.hover-primary-title {
    color: #003366;
    transition: color 0.15s ease-in-out;
}

.hover-primary-title:hover {
    color: var(--huit-blue, #0072ce) !important;
}

.thesis-item-box {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: background-color 0.15s ease;
}

.thesis-item-box:hover {
    background-color: #f8fafc;
}
</style>
@endpush

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
            @if(!empty($gradingHoiDongs) && $gradingHoiDongs->isNotEmpty())
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-xs" onclick="switchHoiDongTab('chamdiem')">
                    <i class="fa-solid fa-calculator me-1"></i> Vào Phòng Chấm Điểm
                </button>
            @else
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold text-muted" onclick="switchHoiDongTab('chamdiem')">
                    <i class="fa-solid fa-clipboard-check me-1"></i> Phòng Chấm Điểm
                </button>
            @endif
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

    <!-- Tabs Navigation (Đồng bộ tuyệt đối cấu trúc với Quản Lý Giảng Viên /admin/giangvien) -->
    <ul class="nav nav-tabs nav-tabs-huit mb-4 border-bottom" id="hoiDongUnifiedTabs" role="tablist" style="border-bottom: 2px solid #cbd5e1 !important;">
        <!-- Tab 1 -->
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab !== 'chamdiem' ? 'active fw-bold' : 'text-secondary' }}" 
               id="tab-btn-danhsach" data-bs-toggle="tab" href="#pane-danhsach" role="tab" aria-controls="pane-danhsach" 
               aria-selected="{{ $activeTab !== 'chamdiem' ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px; {{ $activeTab !== 'chamdiem' ? 'color: #1e3a8a !important; border-bottom: 2px solid #1e3a8a; margin-bottom: -2px;' : '' }}">
                <i class="fa-solid fa-list-check me-2"></i>Danh Sách Hội Đồng Được Phân Công ({{ $counts['tong_cong'] }})
            </a>
        </li>

        <!-- Tab 2 -->
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'chamdiem' ? 'active fw-bold' : 'text-secondary' }}" 
               id="tab-btn-chamdiem" data-bs-toggle="tab" href="#pane-chamdiem" role="tab" aria-controls="pane-chamdiem" 
               aria-selected="{{ $activeTab === 'chamdiem' ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px; {{ $activeTab === 'chamdiem' ? 'color: #1e3a8a !important; border-bottom: 2px solid #1e3a8a; margin-bottom: -2px;' : '' }}">
                <i class="fa-solid fa-clipboard-check me-2"></i>Chấm Điểm Hội Đồng
                @if(($choChamDiemCount ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill ms-2" id="badgeChoChamDiem">{{ $choChamDiemCount }}</span>
                @endif
            </a>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="hoiDongUnifiedContent">
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 1: DANH SÁCH HỘI ĐỒNG ĐƯỢC PHÂN CÔNG                              -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ $activeTab !== 'chamdiem' ? 'show active' : '' }}" id="pane-danhsach" role="tabpanel" aria-labelledby="tab-btn-danhsach">

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

    <!-- Danh Sách Hội Đồng (Giao diện Card Grid) -->
    <div class="mb-4">
        <!-- Tiêu đề & Thông tin phân trang -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-layer-group text-primary me-2"></i>Danh Sách Hội Đồng Được Phân Công
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1 font-monospace">{{ $hoiDongs->total() }}</span>
            </div>
            <span class="text-muted small">
                Hiển thị trang <strong>{{ $hoiDongs->currentPage() }}</strong> / <strong>{{ $hoiDongs->lastPage() }}</strong>
            </span>
        </div>

        @if($hoiDongs->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center text-muted">
                <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                <h6 class="fw-bold">Thầy/Cô chưa có hội đồng nào phù hợp với bộ lọc hiện tại.</h6>
                <p class="small text-muted mb-0">Vui lòng thử chọn học kỳ hoặc trạng thái khác.</p>
            </div>
        @else
            <!-- Lưới Card Hội Đồng (2 Card/Hàng trên Desktop, 1 Card trên Mobile/Tablet) -->
            <div class="row g-4 mb-4">
                @foreach($hoiDongs as $hd)
                    @php
                        $myTv = $hd->thanhViens->firstWhere('MaGV', $gv->MaGV);
                        $myRole = \App\Enums\VaiTroHoiDong::normalize($myTv?->VaiTro ?? 'Ủy viên');
                        $isThuKyThis = \App\Enums\VaiTroHoiDong::isThuKy($myRole);
                        $isChuTichThis = \App\Enums\VaiTroHoiDong::isChuTich($myRole);
                        
                        $ttThoiGian = $hd->trang_thai_thoi_gian;
                        $demNguoc = $hd->dem_nguoc_bat_dau;
                        $reasonCheck = '';
                        $canGradeThis = $hd->canGrade($gv, null, $reasonCheck);

                        // Phân loại thành viên qua accessors chuẩn hóa
                        $chuTich = $hd->chu_tich;
                        $thuKy = $hd->thu_ky;
                        $thanhVienKhac = $hd->thanh_vien_khac;
                    @endphp
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-column council-card">
                            <!-- 1. Header Card -->
                            <div class="card-header bg-white border-bottom py-3 px-4">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-hashtag me-0.5"></i>{{ $hd->MaHoiDong }}
                                            </span>
                                            @if($hd->hocKy)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">
                                                    {{ $hd->hocKy->TenHocKy }}
                                                </span>
                                            @endif
                                            @if($hd->nganh)
                                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                                    {{ $hd->nganh->TenNganh }}
                                                </span>
                                            @endif
                                        </div>
                                        <h5 class="fw-bold mb-0 text-truncate-2" style="font-size: 1.05rem; line-height: 1.4;">
                                            <a href="{{ route('giangvien.hoidong.show', $hd->MaHoiDong) }}" class="text-decoration-none hover-primary-title" title="{{ $hd->TenHoiDong }}">
                                                {{ $hd->TenHoiDong }}
                                            </a>
                                        </h5>
                                    </div>

                                    <!-- Badge Trạng thái hội đồng -->
                                    <div class="text-end flex-shrink-0">
                                        @if($ttThoiGian === 'DaKetThuc')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="fa-solid fa-check me-1"></i> Đã Kết Thúc
                                            </span>
                                        @elseif($ttThoiGian === 'DangDienRa')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Đang Diễn Ra
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="fa-regular fa-clock me-1"></i> Sắp Diễn Ra
                                            </span>
                                            @if($demNguoc)
                                                <div class="text-muted small mt-1 font-monospace" style="font-size: 0.7rem;">{{ $demNguoc }}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Dòng Vai Trò Của Giảng Viên & Số Lượng Đề Tài -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top border-light-subtle">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="text-muted small" style="font-size: 0.78rem;">Vai trò của Thầy/Cô:</span>
                                        @if($myRole === 'Chủ tịch')
                                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-crown me-1"></i> Chủ Tịch
                                            </span>
                                        @elseif($myRole === 'Thư ký')
                                            <span class="badge bg-info text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-pen-nib me-1"></i> Thư Ký
                                            </span>
                                        @elseif($myRole === 'Phản biện')
                                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-user-check me-1"></i> Phản Biện
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-user me-1"></i> Ủy Viên
                                            </span>
                                        @endif
                                    </div>

                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-layer-group me-1"></i>{{ $hd->hoSoBaoVes->count() }} nhóm đề tài
                                    </span>
                                </div>
                            </div>

                            <!-- 2. Body Card -->
                            <div class="card-body p-4 d-flex flex-column gap-3 flex-grow-1">
                                <!-- Khối Thời Gian & Địa Điểm Bảo Vệ -->
                                <div class="p-3 rounded-3 bg-light border border-slate-200">
                                    <div class="row g-2">
                                        <div class="col-12 col-sm-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-regular fa-calendar-days text-primary fa-fw fs-6"></i>
                                                <div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">Thời gian bảo vệ</div>
                                                    <div class="fw-semibold text-dark small">
                                                        {{ $hd->NgayBaoVe ? $hd->NgayBaoVe->format('d/m/Y') : 'Chưa xếp ngày' }}
                                                        @if($hd->ThoiGianBatDau)
                                                            <span class="text-muted fw-normal">({{ \Carbon\Carbon::parse($hd->ThoiGianBatDau)->format('H:i') }} - {{ $hd->ThoiGianKetThuc ? \Carbon\Carbon::parse($hd->ThoiGianKetThuc)->format('H:i') : '17:00' }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-location-dot text-danger fa-fw fs-6"></i>
                                                <div class="min-w-0">
                                                    <div class="text-muted" style="font-size: 0.72rem;">Địa điểm bảo vệ</div>
                                                    <div class="fw-semibold text-dark small text-truncate" title="{{ $hd->DiaDiem ?? 'Phòng B.304' }}">
                                                        {{ $hd->DiaDiem ?? 'Phòng B.304 - Nhà B' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($hd->SoQuyetDinh)
                                        <div class="col-12 pt-1 border-top border-light-subtle">
                                            <div class="d-flex align-items-center gap-1.5 text-muted" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-file-contract text-info fa-fw"></i>
                                                <span>QĐ thành lập: <strong class="text-dark font-monospace">{{ $hd->SoQuyetDinh }}</strong></span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Khối Thành Phần Hội Đồng -->
                                <div class="council-members-box p-3 rounded-3 bg-light border border-slate-200">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-uppercase fw-bold text-secondary" style="font-size: 0.72rem; letter-spacing: 0.3px;">
                                            <i class="fa-solid fa-users me-1 text-primary"></i>Thành Viên Hội Đồng ({{ $hd->thanhViens->count() }} Thầy/Cô)
                                        </span>
                                    </div>

                                    <div class="row g-2">
                                        <!-- Chủ tịch -->
                                        <div class="col-12 col-sm-6">
                                            <div class="p-2 bg-white rounded-2 border d-flex align-items-center gap-2 h-100 {{ ($chuTich?->MaGV === $gv->MaGV) ? 'border-warning shadow-xs' : 'border-slate-200' }}">
                                                <div class="p-1.5 bg-warning-subtle text-warning-emphasis rounded-circle flex-shrink-0">
                                                    <i class="fa-solid fa-crown fa-fw" style="font-size: 0.85rem;"></i>
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="text-muted" style="font-size: 0.68rem; line-height: 1.1;">CHỦ TỊCH</div>
                                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;" title="{{ $chuTich?->giangVien?->HoTen ?? 'Chưa phân công' }}">
                                                        {{ $chuTich?->giangVien?->HoTen ?? 'Chưa phân công' }}
                                                        @if($chuTich?->MaGV === $gv->MaGV)
                                                            <span class="badge bg-warning text-dark ms-0.5" style="font-size: 0.6rem;">Bạn</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Thư ký -->
                                        <div class="col-12 col-sm-6">
                                            <div class="p-2 bg-white rounded-2 border d-flex align-items-center gap-2 h-100 {{ ($thuKy?->MaGV === $gv->MaGV) ? 'border-info shadow-xs' : 'border-slate-200' }}">
                                                <div class="p-1.5 bg-info-subtle text-info-emphasis rounded-circle flex-shrink-0">
                                                    <i class="fa-solid fa-pen-nib fa-fw" style="font-size: 0.85rem;"></i>
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="text-muted" style="font-size: 0.68rem; line-height: 1.1;">THƯ KÝ</div>
                                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;" title="{{ $thuKy?->giangVien?->HoTen ?? 'Chưa phân công' }}">
                                                        {{ $thuKy?->giangVien?->HoTen ?? 'Chưa phân công' }}
                                                        @if($thuKy?->MaGV === $gv->MaGV)
                                                            <span class="badge bg-info text-white ms-0.5" style="font-size: 0.6rem;">Bạn</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Các thành viên khác (Phản biện, Ủy viên) -->
                                        @if($thanhVienKhac->isNotEmpty())
                                        <div class="col-12">
                                            <div class="p-2 bg-white rounded-2 border border-slate-200">
                                                <div class="text-muted mb-1" style="font-size: 0.68rem; line-height: 1.1;">ỦY VIÊN &amp; PHẢN BIỆN:</div>
                                                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                                                    @foreach($thanhVienKhac as $tvk)
                                                        @php
                                                            $isMeTv = ($tvk->MaGV === $gv->MaGV);
                                                        @endphp
                                                        <span class="badge {{ $isMeTv ? 'bg-primary-subtle text-primary border-primary' : 'bg-light text-dark border-slate-200' }} border px-2 py-1" style="font-size: 0.74rem;">
                                                            <span class="text-secondary fw-normal me-0.5">[{{ $tvk->short_vai_tro }}]</span>
                                                            <span class="fw-semibold">{{ $tvk->giangVien?->HoTen ?? $tvk->MaGV }}</span>
                                                            @if($isMeTv)
                                                                <span class="badge bg-primary text-white ms-1" style="font-size: 0.6rem;">Bạn</span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Khối Danh Sách Nhóm Đề Tài & Sinh Viên -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-uppercase fw-bold text-secondary" style="font-size: 0.72rem; letter-spacing: 0.3px;">
                                            <i class="fa-solid fa-graduation-cap me-1 text-primary"></i>Đề Tài &amp; Sinh Viên Báo Cáo
                                        </span>
                                        <span class="text-muted small" style="font-size: 0.72rem;">{{ $hd->hoSoBaoVes->count() }} nhóm</span>
                                    </div>

                                    @if($hd->hoSoBaoVes->isEmpty())
                                        <div class="text-muted fst-italic small p-3 bg-light rounded-3 text-center border">
                                            Chưa có nhóm đề tài nào được phân công vào hội đồng này.
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($hd->hoSoBaoVes->take(2) as $hIdx => $hoSo)
                                                @php
                                                    $nhom = $hoSo->nhom;
                                                    $deTai = $nhom?->deTai;
                                                    $gvhd = $deTai?->giangVien;
                                                    $sinhViens = $nhom?->thanhViens ?? [];
                                                @endphp
                                                <div class="thesis-item-box p-3 rounded-3">
                                                    <!-- Header đề tài: Số thứ tự & Tên đề tài -->
                                                    <div class="d-flex align-items-start gap-2 mb-2">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-bold flex-shrink-0" style="font-size: 0.7rem;">
                                                            Đề tài {{ $hIdx + 1 }}
                                                        </span>
                                                        <div class="fw-bold text-dark flex-grow-1 text-break-anywhere" style="font-size: 0.85rem; line-height: 1.4;">
                                                            {{ $deTai?->TenDeTai ?? ($nhom?->TenNhom ?? 'Hồ sơ: ' . $hoSo->MaHoSo) }}
                                                        </div>
                                                    </div>

                                                    <!-- Thông tin GVHD & Nhóm -->
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pt-1 border-top border-light-subtle" style="font-size: 0.78rem;">
                                                        <div class="text-muted">
                                                            <i class="fa-solid fa-chalkboard-user text-primary me-1"></i>
                                                            <span>GVHD: <strong class="text-dark">{{ $gvhd?->HoTen ?? 'Chưa phân công' }}</strong></span>
                                                        </div>
                                                        @if(!empty($nhom?->TenNhom))
                                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.68rem;">
                                                                {{ $nhom->TenNhom }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Sinh viên thực hiện -->
                                                    <div class="d-flex align-items-start gap-1.5" style="font-size: 0.78rem;">
                                                        <i class="fa-solid fa-user-graduate text-secondary fa-fw mt-1 flex-shrink-0"></i>
                                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                                            <span class="text-muted me-1">Sinh viên:</span>
                                                            @forelse($sinhViens as $sTv)
                                                                <span class="badge bg-white text-dark border border-slate-200 px-2 py-1" title="MSSV: {{ $sTv->sinhVien?->MaSV }}">
                                                                    <span class="fw-semibold">{{ $sTv->sinhVien?->HoTen ?? $sTv->MaSV }}</span>
                                                                    @if(!empty($sTv->sinhVien?->MaSV))
                                                                        <span class="text-muted font-monospace ms-0.5">({{ $sTv->sinhVien?->MaSV }})</span>
                                                                    @endif
                                                                </span>
                                                            @empty
                                                                <span class="fst-italic text-muted">Chưa có thông tin sinh viên</span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if($hd->hoSoBaoVes->count() > 2)
                                                <div class="collapse" id="moreTheses_{{ $hd->MaHoiDong }}">
                                                    <div class="d-flex flex-column gap-2 pt-1">
                                                        @foreach($hd->hoSoBaoVes->skip(2) as $hIdx => $hoSo)
                                                            @php
                                                                $nhom = $hoSo->nhom;
                                                                $deTai = $nhom?->deTai;
                                                                $gvhd = $deTai?->giangVien;
                                                                $sinhViens = $nhom?->thanhViens ?? [];
                                                            @endphp
                                                            <div class="thesis-item-box p-3 rounded-3">
                                                                <div class="d-flex align-items-start gap-2 mb-2">
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-bold flex-shrink-0" style="font-size: 0.7rem;">
                                                                        Đề tài {{ $hIdx + 3 }}
                                                                    </span>
                                                                    <div class="fw-bold text-dark flex-grow-1 text-break-anywhere" style="font-size: 0.85rem; line-height: 1.4;">
                                                                        {{ $deTai?->TenDeTai ?? ($nhom?->TenNhom ?? 'Hồ sơ: ' . $hoSo->MaHoSo) }}
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pt-1 border-top border-light-subtle" style="font-size: 0.78rem;">
                                                                    <div class="text-muted">
                                                                        <i class="fa-solid fa-chalkboard-user text-primary me-1"></i>
                                                                        <span>GVHD: <strong class="text-dark">{{ $gvhd?->HoTen ?? 'Chưa phân công' }}</strong></span>
                                                                    </div>
                                                                    @if(!empty($nhom?->TenNhom))
                                                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.68rem;">
                                                                            {{ $nhom->TenNhom }}
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                <div class="d-flex align-items-start gap-1.5" style="font-size: 0.78rem;">
                                                                    <i class="fa-solid fa-user-graduate text-secondary fa-fw mt-1 flex-shrink-0"></i>
                                                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                                                        <span class="text-muted me-1">Sinh viên:</span>
                                                                        @forelse($sinhViens as $sTv)
                                                                            <span class="badge bg-white text-dark border border-slate-200 px-2 py-1" title="MSSV: {{ $sTv->sinhVien?->MaSV }}">
                                                                                <span class="fw-semibold">{{ $sTv->sinhVien?->HoTen ?? $sTv->MaSV }}</span>
                                                                                @if(!empty($sTv->sinhVien?->MaSV))
                                                                                    <span class="text-muted font-monospace ms-0.5">({{ $sTv->sinhVien?->MaSV }})</span>
                                                                                @endif
                                                                            </span>
                                                                        @empty
                                                                            <span class="fst-italic text-muted">Chưa có thông tin sinh viên</span>
                                                                        @endforelse
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="text-center pt-1">
                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-toggle-theses" 
                                                            data-bs-toggle="collapse" data-bs-target="#moreTheses_{{ $hd->MaHoiDong }}" 
                                                            aria-expanded="false" aria-controls="moreTheses_{{ $hd->MaHoiDong }}">
                                                        <span class="show-text"><i class="fa-solid fa-angle-down me-1"></i> Xem thêm {{ $hd->hoSoBaoVes->count() - 2 }} đề tài khác</span>
                                                        <span class="hide-text d-none"><i class="fa-solid fa-angle-up me-1"></i> Thu gọn đề tài</span>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- 3. Footer Thao Tác Card -->
                            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <a href="{{ route('giangvien.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-circle-info me-1"></i> Xem Chi Tiết
                                </a>

                                <div class="d-flex gap-1.5 align-items-center">
                                    @if($canGradeThis)
                                        <a href="{{ route('giangvien.hoidong.index', ['tab' => 'chamdiem', 'hd' => $hd->MaHoiDong]) }}" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-xs">
                                            <i class="fa-solid fa-calculator me-1"></i> Chấm Điểm
                                        </a>
                                    @elseif($ttThoiGian === 'SapDienRa')
                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3 py-1.5 fw-semibold text-white-50 opacity-75" disabled 
                                                title="{{ $reasonCheck ?: 'Chưa đến giờ chấm điểm. Mở chấm điểm từ ' . ($hd->thoi_diem_bat_dau ? $hd->thoi_diem_bat_dau->format('H:i \n\g\à\y d/m/Y') : 'thời gian bảo vệ') }}">
                                            <i class="fa-solid fa-lock me-1"></i> Chấm Điểm
                                        </button>
                                    @else
                                        <a href="{{ route('giangvien.hoidong.show', $hd->MaHoiDong) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" title="Xem bảng điểm và biên bản hội đồng (Chế độ chỉ đọc)">
                                            <i class="fa-solid fa-file-lines me-1"></i> Xem Điểm
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Phân trang -->
            @if($hoiDongs->hasPages())
                <div class="p-3 bg-white border border-slate-200 rounded-4 shadow-sm d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div class="small text-muted">
                        Hiển thị <strong>{{ $hoiDongs->firstItem() }}</strong> - <strong>{{ $hoiDongs->lastItem() }}</strong> trên tổng số <strong>{{ $hoiDongs->total() }}</strong> hội đồng
                    </div>
                    <div>
                        {{ $hoiDongs->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
        </div><!-- /#pane-danhsach -->

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 2: CHẤM ĐIỂM HỘI ĐỒNG                                             -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ $activeTab === 'chamdiem' ? 'show active' : '' }}" id="pane-chamdiem" role="tabpanel" aria-labelledby="tab-btn-chamdiem">
            @include('giangvien.hoidong._cham-diem')
        </div><!-- /#pane-chamdiem -->
    </div><!-- /#hoiDongUnifiedContent -->
</div>

@push('scripts')
<script>
function switchHoiDongTab(tabName) {
    const tabBtnId = tabName === 'chamdiem' ? 'tab-btn-chamdiem' : 'tab-btn-danhsach';
    const tabEl = document.getElementById(tabBtnId);
    if (tabEl) {
        const tab = bootstrap.Tab.getOrCreateInstance(tabEl);
        tab.show();
        window.scrollTo({ top: tabEl.offsetTop - 80, behavior: 'smooth' });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('#hoiDongUnifiedTabs a[data-bs-toggle="tab"]');
    tabLinks.forEach(link => {
        link.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('href');
            const tabKey = (targetId === '#pane-chamdiem') ? 'chamdiem' : 'danhsach';

            tabLinks.forEach(l => {
                l.classList.remove('active', 'fw-bold');
                l.classList.add('text-secondary');
                l.style.color = '';
                l.style.borderBottom = '';
                l.style.marginBottom = '';
            });
            e.target.classList.add('active', 'fw-bold');
            e.target.classList.remove('text-secondary');
            e.target.style.color = '#1e3a8a';
            e.target.style.borderBottom = '2px solid #1e3a8a';
            e.target.style.marginBottom = '-2px';

            const url = new URL(window.location.href);
            if (url.searchParams.get('tab') !== tabKey) {
                url.searchParams.set('tab', tabKey);
                window.history.replaceState({}, '', url.toString());
            }
        });
    });

    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabKey = urlParams.get('tab');
        if (tabKey === 'chamdiem') {
            switchHoiDongTab('chamdiem');
        } else {
            switchHoiDongTab('danhsach');
        }
    });

    // Toggle Text Xem thêm / Thu gọn danh sách đề tài trên Card
    document.querySelectorAll('.btn-toggle-theses').forEach(btn => {
        const targetSelector = btn.getAttribute('data-bs-target');
        const targetEl = document.querySelector(targetSelector);
        if (targetEl) {
            targetEl.addEventListener('show.bs.collapse', function() {
                const showText = btn.querySelector('.show-text');
                const hideText = btn.querySelector('.hide-text');
                if (showText) showText.classList.add('d-none');
                if (hideText) hideText.classList.remove('d-none');
            });
            targetEl.addEventListener('hide.bs.collapse', function() {
                const showText = btn.querySelector('.show-text');
                const hideText = btn.querySelector('.hide-text');
                if (showText) showText.classList.remove('d-none');
                if (hideText) hideText.classList.add('d-none');
            });
        }
    });
});
</script>
@endpush
@endsection
