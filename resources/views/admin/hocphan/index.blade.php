@extends('layouts.admin')

@section('page_title', 'Quản Lý Học Phần')

@push('styles')
<style>
    /* Hiệu ứng Fade-In mượt mà cho nội dung Tab khi reload */
.tab-pane-fade {
    animation: fadeInTab 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeInTab {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
    /* Styling chuẩn phong cách quản trị học vụ HUIT - Đồng bộ 2 Tab */
    .hp-nav-tabs .nav-link {
        color: #475569;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.75rem 1.25rem;
        border: none;
        border-bottom: 3px solid transparent;
        background: transparent;
        border-radius: 0;
        transition: all 0.2s ease;
    }
    .hp-nav-tabs .nav-link:hover {
        color: #00305a;
        background: rgba(0, 48, 90, 0.03);
    }
    .hp-nav-tabs .nav-link.active {
        color: #00305a;
        border-bottom-color: #00305a;
        background: #fff;
    }
    .hp-badge-count {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        margin-left: 0.4rem;
    }
    .hp-nav-tabs .nav-link.active .hp-badge-count {
        background: #e0f2fe;
        color: #0284c7;
    }
    .kpi-card-clean {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem 1.25rem;
    }
    .kpi-title {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.35rem;
    }
    .kpi-value {
        color: #0f172a;
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .kpi-sub {
        color: #94a3b8;
        font-size: 0.8rem;
        margin-top: 0.2rem;
    }
    .btn-navy {
        background-color: #00305a;
        color: #ffffff;
        border: 1px solid #00305a;
    }
    .btn-navy:hover {
        background-color: #002240;
        color: #ffffff;
    }
    .table-hp th {
        background: #003366 !important;
        color: #ffffff !important;
        font-size: 13px !important;
        letter-spacing: 0.3px;
        font-weight: 600 !important;
        border: none !important;
        padding: 11px 12px !important;
        vertical-align: middle !important;
    }
    .table-hp td {
        padding: 10px 12px !important;
        vertical-align: middle !important;
        font-size: 13px !important;
        border-bottom: 1px solid #edf2f7;
    }
    .table-hp tbody tr:hover {
        background-color: #f0f7ff !important;
    }
    .pill-filter {
        font-size: 0.825rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        color: #475569;
        background: #f1f5f9;
        text-decoration: none;
        font-weight: 500;
        border: 1px solid transparent;
        display: inline-block;
        transition: all 0.15s ease;
    }
    .pill-filter:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .pill-filter.active {
        background: #00305a;
        color: #ffffff;
    }
    .status-badge-active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
    }
    .status-badge-inactive {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- 1. Tiêu đề trang -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: #00305a;">Quản lý học phần</h4>
            <p class="text-muted small mb-0">Quản lý danh mục học phần và phân bổ giảng dạy theo học kỳ</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-secondary border px-3 py-2 rounded-2 shadow-xs small">
                <i class="fa-solid fa-building-columns text-primary me-1"></i> Khoa Công nghệ Thông tin
            </span>
        </div>
    </div>

    <!-- 2. Khu vực thống kê KPI -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs">
                <div class="kpi-title">Tổng học phần</div>
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-sub">học phần trong toàn Khoa</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs" style="border-left: 3px solid #00305a;">
                <div class="kpi-title">Bộ môn CNPM</div>
                <div class="kpi-value" style="color: #00305a;">{{ $stats['cnpm'] }}</div>
                <div class="kpi-sub">học phần Công nghệ phần mềm</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs" style="border-left: 3px solid #0284c7;">
                <div class="kpi-title">Bộ môn HTTT</div>
                <div class="kpi-value text-primary">{{ $stats['httt'] }}</div>
                <div class="kpi-sub">học phần Hệ thống thông tin</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs" style="border-left: 3px solid #10b981;">
                <div class="kpi-title">Bộ môn ATTT & KHMT</div>
                <div class="kpi-value text-success">{{ $stats['attt_khmt'] }}</div>
                <div class="kpi-sub">học phần chuyên ngành & khóa luận</div>
            </div>
        </div>
    </div>

    <!-- 3. Khu vực chuyển nhóm: 2 Tab lớn -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav hp-nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab !== 'phan_bo_hocky' ? 'active' : '' }}" 
                       href="{{ route('hocphan.index', ['tab' => 'danh_muc']) }}">
                        <i class="fa-solid fa-layer-group me-1 text-muted"></i> Danh mục học phần (Theo bộ môn)
                        <span class="hp-badge-count">{{ $stats['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'phan_bo_hocky' ? 'active' : '' }}" 
                       href="{{ route('hocphan.index', ['tab' => 'phan_bo_hocky', 'ma_hocky' => $selectedHocKy]) }}">
                        <i class="fa-solid fa-calendar-check me-1 text-muted"></i> Gán &amp; Phân bổ học phần theo học kỳ
                        <span class="hp-badge-count">{{ $statsHk['opened'] }}/{{ $statsHk['total'] }} đang mở</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
<div class="tab-pane-fade">
            {{-- ═══════════════════════════════════════════════════════════════
                 TAB 1: DANH MỤC HỌC PHẦN (THEO BỘ MÔN)
            ═══════════════════════════════════════════════════════════════ --}}
            @if($activeTab !== 'phan_bo_hocky')
                <!-- Bộ lọc theo bộ môn (Pills) -->
                <div class="mb-3 d-flex flex-wrap align-items-center gap-1.5 pb-2 border-bottom">
                    <span class="text-secondary small fw-semibold me-2">Bộ môn:</span>
                    <a href="{{ route('hocphan.index', array_merge(request()->except(['ma_bomon', 'hp_page']), ['tab' => 'danh_muc'])) }}"
                       class="pill-filter {{ !request('ma_bomon') ? 'active' : '' }}">
                        Tất cả bộ môn
                    </a>
                    @foreach($bomons as $bm)
                        <a href="{{ route('hocphan.index', array_merge(request()->except(['ma_bomon', 'hp_page']), ['tab' => 'danh_muc', 'ma_bomon' => $bm->MaBoMon])) }}"
                           class="pill-filter {{ request('ma_bomon') == $bm->MaBoMon ? 'active' : '' }}">
                            {{ $bm->TenBoMon }}
                        </a>
                    @endforeach
                </div>

                <!-- Form tìm kiếm & Bộ lọc trạng thái & Nút thêm -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <form method="GET" action="{{ route('hocphan.index') }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 720px;">
                        <input type="hidden" name="tab" value="danh_muc">
                        @if(request('ma_bomon'))
                            <input type="hidden" name="ma_bomon" value="{{ request('ma_bomon') }}">
                        @endif

                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo mã hoặc tên học phần...">
                        </div>

                        <select name="loai_hocphan" class="form-select form-select-sm" style="max-width: 160px;" onchange="this.form.submit()">
                            <option value="">-- Loại học phần --</option>
                            <option value="Khóa luận" {{ request('loai_hocphan') == 'Khóa luận' ? 'selected' : '' }}>Khóa luận</option>
                            <option value="Chuyên ngành" {{ request('loai_hocphan') == 'Chuyên ngành' ? 'selected' : '' }}>Chuyên ngành</option>
                            <option value="Đồ án" {{ request('loai_hocphan') == 'Đồ án' ? 'selected' : '' }}>Đồ án</option>
                        </select>

                        <select name="trang_thai" class="form-select form-select-sm" style="max-width: 160px;" onchange="this.form.submit()">
                            <option value="">-- Trạng thái --</option>
                            <option value="Đang sử dụng" {{ request('trang_thai') == 'Đang sử dụng' ? 'selected' : '' }}>Đang sử dụng</option>
                            <option value="Ngừng sử dụng" {{ request('trang_thai') == 'Ngừng sử dụng' ? 'selected' : '' }}>Ngừng sử dụng</option>
                        </select>

                        @if(request()->anyFilled(['search', 'ma_bomon', 'loai_hocphan', 'trang_thai']))
                            <a href="{{ route('hocphan.index', ['tab' => 'danh_muc']) }}" class="btn btn-sm btn-outline-secondary">Xóa lọc</a>
                        @endif
                    </form>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary shadow-xs" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fa-solid fa-file-excel text-success me-1"></i> Import Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-navy fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fa-solid fa-plus me-1"></i> Thêm học phần mới
                        </button>
                    </div>
                </div>

                <!-- Bảng danh sách học phần theo bộ môn -->
                <div class="table-responsive border rounded-2 mb-3">
                    <table class="table table-hp align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">STT</th>
                                <th width="14%">MÃ HỌC PHẦN</th>
                                <th width="27%">TÊN HỌC PHẦN</th>
                                <th width="18%">BỘ MÔN PHỤ TRÁCH</th>
                                <th class="text-center" width="11%">LOẠI MÔN</th>
                                <th class="text-end" width="7%">SỐ TC</th>
                                <th class="text-center" width="10%">TRẠNG THÁI</th>
                                <th class="text-center" width="8%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hocPhanBoMons as $index => $hp)
                                <tr>
                                    <td class="text-center text-muted small">
                                        {{ $hocPhanBoMons->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <code class="fw-bold text-dark">{{ $hp->MaHocPhan }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hp->TenHocPhan }}</div>
                                        <div class="text-muted small mt-0.5">
                                            @if($hp->MoTa)
                                                <span>{{ Str::limit($hp->MoTa, 60) }}</span>
                                            @else
                                                <span class="text-secondary fst-italic">LT: {{ $hp->SoTietLT ?? 0 }} tiết · TH: {{ $hp->SoTietTH ?? 0 }} tiết</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $bmBadges = [
                                                'CNPM' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                'HTTT' => 'bg-info-subtle text-dark border border-info-subtle',
                                                'ATTT' => 'bg-warning-subtle text-dark border border-warning-subtle',
                                                'KHMT' => 'bg-success-subtle text-success border border-success-subtle',
                                            ];
                                            $badgeClass = $bmBadges[$hp->MaBoMon] ?? 'bg-secondary-subtle text-secondary';
                                        @endphp
                                        @if(!empty($hp->boMon))
                                            <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1">
                                                {{ $hp->boMon->TenBoMon }}
                                            </span>
                                        @elseif(!empty($hp->MaBoMon))
                                            <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1">
                                                {{ $hp->MaBoMon }}
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                                Dùng chung
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($hp->LoaiHocPhan === 'Khóa luận')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">🎓 Khóa luận</span>
                                        @elseif($hp->LoaiHocPhan === 'Đồ án')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">📁 Đồ án</span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">Chuyên ngành</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        {{ $hp->SoTinChi }}
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($hp->TrangThai, ['Đang sử dụng', 'Đang áp dụng', '1', true]))
                                            <span class="status-badge-active">Đang sử dụng</span>
                                        @else
                                            <span class="status-badge-inactive">Ngừng sử dụng</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-link p-0 text-primary text-decoration-none fw-semibold" 
                                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $hp->MaHocPhan }}">
                                                Sửa
                                            </button>
                                            <span class="text-muted">·</span>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-danger text-decoration-none fw-semibold" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal{{ $hp->MaHocPhan }}">
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa-regular fa-folder-open mb-2 d-block fs-3 text-secondary"></i>
                                        Không tìm thấy học phần nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang Tab 1 -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
                    <div class="text-muted small">
                        Hiển thị {{ $hocPhanBoMons->firstItem() ?? 0 }}–{{ $hocPhanBoMons->lastItem() ?? 0 }} trên tổng số {{ $hocPhanBoMons->total() }} học phần
                    </div>
                    <div>
                        {{ $hocPhanBoMons->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            {{-- ═══════════════════════════════════════════════════════════════
                 TAB 2: GÁN & PHÂN BỔ HỌC PHẦN THEO HỌC KỲ
            ═══════════════════════════════════════════════════════════════ --}}
            @else
                <!-- Thanh công cụ Tab 2: Chọn Học kỳ & Thao tác hàng loạt -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 me-md-3" style="min-width: 380px;">
                            <label class="fw-bold text-dark small mb-0 text-nowrap"><i class="fa-solid fa-calendar-days text-primary me-1"></i>Chọn Học Kỳ:</label>
                            <select class="form-select form-select-sm fw-semibold flex-grow-1" style="min-width: 320px;" onchange="window.location.href='{{ route('hocphan.index') }}?tab=phan_bo_hocky&ma_hocky=' + this.value">
                                @foreach($hocKies as $hk)
                                    @php
                                        $namHocSuffix = (!empty($hk->NamHoc) && !str_contains($hk->TenHocKy, $hk->NamHoc)) ? ' (' . $hk->NamHoc . ')' : '';
                                    @endphp
                                    <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy === $hk->MaHocKy ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }}{{ $namHocSuffix }} {{ $hk->TrangThai === 'Đang diễn ra' ? '— [Đang diễn ra]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="{{ route('admin.hocphan.bulk_hocky') }}" class="d-inline" onsubmit="return confirm('Xác nhận MỞ TẤT CẢ các học phần cho học kỳ này?')">
                                @csrf
                                <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                                <input type="hidden" name="action" value="open_all">
                                <button type="submit" class="btn btn-sm btn-success rounded-pill fw-semibold shadow-xs px-3">
                                    <i class="fa-solid fa-check-double me-1"></i> Mở tất cả
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.hocphan.bulk_hocky') }}" class="d-inline" onsubmit="return confirm('Xác nhận ĐÓNG TẤT CẢ học phần trong học kỳ này?')">
                                @csrf
                                <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                                <input type="hidden" name="action" value="close_all">
                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold shadow-xs px-3">
                                    <i class="fa-solid fa-ban me-1"></i> Đóng tất cả
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Thẻ tóm tắt thông tin học kỳ đang chọn -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                    <div>
                        <span class="text-secondary small">Học kỳ áp dụng:</span>
                        <strong class="text-dark ms-1">{{ $currentHocKyObj?->TenHocKy }} ({{ $currentHocKyObj?->NamHoc }})</strong>
                        <span class="badge {{ $currentHocKyObj?->TrangThai === 'Đang diễn ra' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' }} ms-2 rounded-pill px-2.5">
                            {{ $currentHocKyObj?->TrangThai }}
                        </span>
                    </div>
                    <div class="small">
                        Đang mở: <strong class="text-success fs-6">{{ $statsHk['opened'] }}</strong> / {{ $statsHk['total'] }} học phần
                    </div>
                </div>

                <!-- Form lọc nhanh trong Tab 2 -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <form method="GET" action="{{ route('hocphan.index') }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                        <input type="hidden" name="tab" value="phan_bo_hocky">
                        <input type="hidden" name="ma_hocky" value="{{ $selectedHocKy }}">

                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search_hk" value="{{ request('search_hk') }}" class="form-control border-start-0 ps-0" placeholder="Lọc theo tên / mã môn...">
                        </div>

                        <select name="ma_bomon_hk" class="form-select form-select-sm" style="max-width: 220px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('ma_bomon_hk') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }}
                                </option>
                            @endforeach
                        </select>

                        @if(request()->anyFilled(['search_hk', 'ma_bomon_hk']))
                            <a href="{{ route('hocphan.index', ['tab' => 'phan_bo_hocky', 'ma_hocky' => $selectedHocKy]) }}" class="btn btn-sm btn-outline-secondary">Xóa lọc</a>
                        @endif
                    </form>
                </div>

                <!-- Bảng phân bổ học phần theo học kỳ -->
                <div class="table-responsive border rounded-2 mb-3">
                    <table class="table table-hp align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">STT</th>
                                <th width="14%">MÃ HỌC PHẦN</th>
                                <th width="28%">TÊN HỌC PHẦN</th>
                                <th width="18%">BỘ MÔN PHỤ TRÁCH</th>
                                <th class="text-center" width="11%">LOẠI MÔN</th>
                                <th class="text-end" width="7%">SỐ TC</th>
                                <th class="text-center" width="10%">TRẠNG THÁI KỲ NÀY</th>
                                <th class="text-center" width="7%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hocPhanTheoHocKy as $index => $hp)
                                @php
                                    $isOpenedThisHk = $hp->hocPhanHocKies->where('TrangThai', 'Đang mở')->isNotEmpty();
                                    $bmBadges = [
                                        'CNPM' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'HTTT' => 'bg-info-subtle text-dark border border-info-subtle',
                                        'ATTT' => 'bg-warning-subtle text-dark border border-warning-subtle',
                                        'KHMT' => 'bg-success-subtle text-success border border-success-subtle',
                                    ];
                                    $badgeClass = $bmBadges[$hp->MaBoMon] ?? 'bg-secondary-subtle text-secondary';
                                @endphp
                                <tr>
                                    <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                    <td><code class="fw-bold text-dark">{{ $hp->MaHocPhan }}</code></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hp->TenHocPhan }}</div>
                                        <small class="text-muted">{{ $hp->MoTa ?? 'Môn học thuộc bộ môn ' . ($hp->boMon->TenBoMon ?? '') }}</small>
                                    </td>
                                    <td>
                                        @if(!empty($hp->boMon))
                                            <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1">
                                                {{ $hp->boMon->TenBoMon }}
                                            </span>
                                        @elseif(!empty($hp->MaBoMon))
                                            <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1">
                                                {{ $hp->MaBoMon }}
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                                Dùng chung
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($hp->LoaiHocPhan === 'Khóa luận')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">🎓 Khóa luận</span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">{{ $hp->LoaiHocPhan }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">{{ $hp->SoTinChi }}</td>
                                    <td class="text-center">
                                        @if($isOpenedThisHk)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i>Đang mở
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-circle-xmark me-1"></i>Chưa mở
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.hocphan.toggle_hocky') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="MaHocPhan" value="{{ $hp->MaHocPhan }}">
                                            <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                                            @if($isOpenedThisHk)
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1 rounded-pill" title="Đóng học phần trong học kỳ này">
                                                    Đóng
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-success px-2.5 py-1 rounded-pill" title="Mở học phần cho học kỳ này">
                                                    Mở
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa-regular fa-folder-open mb-2 d-block fs-3 text-secondary"></i>
                                        Không tìm thấy học phần nào trong học kỳ này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
</div>
        </div>
    </div>
</div>
@endsection