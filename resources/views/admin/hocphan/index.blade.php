@extends('layouts.admin')

@section('page_title', 'Quản Lý Học Phần')

@push('styles')
<style>
    /* Styling chuẩn phong cách quản trị học vụ HUIT */
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
        background: #f8fafc;
        color: #334155;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.85rem 1rem;
    }
    .table-hp td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-hp tbody tr:hover {
        background-color: #f8fafc;
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

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert" style="background-color: #ecfdf5; border-left: 4px solid #10b981 !important; color: #065f46;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert" style="background-color: #fef2f2; border-left: 4px solid #ef4444 !important; color: #991b1b;">
            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Có lỗi xảy ra:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 1. Tiêu đề trang -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">Quản lý học phần</h4>
            <p class="text-muted small mb-0">Quản lý học phần theo bộ môn và các học phần được sử dụng chung trong Khoa.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-secondary border px-3 py-2 rounded-2 shadow-xs small">
                Khoa Công nghệ Thông tin
            </span>
        </div>
    </div>

    <!-- 2. Khu vực thống kê: 4 thẻ gọn gàng, không lạm dụng icon -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs">
                <div class="kpi-title">Tổng học phần</div>
                <div class="kpi-value">{{ $stats['total'] }}</div>
                <div class="kpi-sub">học phần trong chương trình</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs">
                <div class="kpi-title">Học phần theo bộ môn</div>
                <div class="kpi-value text-primary">{{ $stats['theo_bomon'] }}</div>
                <div class="kpi-sub">phân bổ các bộ môn chuyên môn</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs" style="border-left: 3px solid #00305a;">
                <div class="kpi-title">Học phần dùng chung</div>
                <div class="kpi-value" style="color: #00305a;">{{ $stats['dung_chung'] }}</div>
                <div class="kpi-sub">Khóa luận cử nhân, Khóa luận tốt nghiệp</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-clean h-100 shadow-xs">
                <div class="kpi-title">Số bộ môn</div>
                <div class="kpi-value text-secondary">{{ $stats['so_bomon'] }}</div>
                <div class="kpi-sub">bộ môn quản lý học thuật</div>
            </div>
        </div>
    </div>

    <!-- 3. Khu vực chuyển nhóm học phần: 2 Tab lớn -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav hp-nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'bomon' ? 'active' : '' }}" 
                       href="{{ route('hocphan.index', ['tab' => 'bomon']) }}">
                        <i class="fa-solid fa-layer-group me-1 text-muted"></i> Học phần theo bộ môn
                        <span class="hp-badge-count">{{ $stats['theo_bomon'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'dung_chung' ? 'active' : '' }}" 
                       href="{{ route('hocphan.index', ['tab' => 'dung_chung']) }}">
                        <i class="fa-solid fa-cubes me-1 text-muted"></i> Học phần dùng chung
                        <span class="hp-badge-count">{{ $stats['dung_chung'] }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">

            {{-- ═══════════════════════════════════════════════════════════════
                 TAB 1: HỌC PHẦN THEO BỘ MÔN
            ═══════════════════════════════════════════════════════════════ --}}
            @if($activeTab === 'bomon')
                <!-- Bộ lọc theo bộ môn (Pills) -->
                <div class="mb-3 d-flex flex-wrap align-items-center gap-1.5 pb-2 border-bottom">
                    <span class="text-secondary small fw-semibold me-2">Bộ môn:</span>
                    <a href="{{ route('hocphan.index', array_merge(request()->except(['ma_bomon', 'bm_page']), ['tab' => 'bomon'])) }}"
                       class="pill-filter {{ !request('ma_bomon') ? 'active' : '' }}">
                        Tất cả bộ môn
                    </a>
                    @foreach($bomons as $bm)
                        <a href="{{ route('hocphan.index', array_merge(request()->except(['ma_bomon', 'bm_page']), ['tab' => 'bomon', 'ma_bomon' => $bm->MaBoMon])) }}"
                           class="pill-filter {{ request('ma_bomon') == $bm->MaBoMon ? 'active' : '' }}">
                            {{ $bm->TenBoMon }}
                        </a>
                    @endforeach
                </div>

                <!-- Form tìm kiếm & Bộ lọc trạng thái & Nút thêm -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <form method="GET" action="{{ route('hocphan.index') }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 680px;">
                        <input type="hidden" name="tab" value="bomon">
                        @if(request('ma_bomon'))
                            <input type="hidden" name="ma_bomon" value="{{ request('ma_bomon') }}">
                        @endif

                        <div class="input-group input-group-sm" style="max-width: 320px;">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo mã hoặc tên học phần...">
                        </div>

                        <select name="trang_thai" class="form-select form-select-sm" style="max-width: 170px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="Đang sử dụng" {{ request('trang_thai') === 'Đang sử dụng' ? 'selected' : '' }}>Đang sử dụng</option>
                            <option value="Ngừng sử dụng" {{ request('trang_thai') === 'Ngừng sử dụng' ? 'selected' : '' }}>Ngừng sử dụng</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-outline-secondary">Lọc</button>

                        @if(request('search') || request('trang_thai') || request('ma_bomon'))
                            <a href="{{ route('hocphan.index', ['tab' => 'bomon']) }}" class="btn btn-sm btn-link text-secondary text-decoration-none">Xóa lọc</a>
                        @endif
                    </form>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-navy fw-semibold px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#createBoMonModal">
                            <i class="fa-solid fa-plus me-1"></i> Thêm học phần
                        </button>
                    </div>
                </div>

                <!-- Bảng danh sách học phần theo bộ môn -->
                <div class="table-responsive border rounded-2 mb-3">
                    <table class="table table-hp align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">STT</th>
                                <th width="15%">MÃ HỌC PHẦN</th>
                                <th width="28%">TÊN HỌC PHẦN</th>
                                <th width="22%">BỘ MÔN</th>
                                <th class="text-end" width="10%">SỐ TÍN CHỈ</th>
                                <th class="text-center" width="10%">TRẠNG THÁI</th>
                                <th class="text-center" width="10%">THAO TÁC</th>
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
                                        <div class="fw-semibold text-dark">{{ $hp->TenHocPhan }}</div>
                                        @if($hp->MoTa)
                                            <div class="text-muted small text-truncate" style="max-width: 320px;">{{ $hp->MoTa }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-secondary fw-medium">
                                            {{ $hp->boMon->TenBoMon ?? $hp->MaBoMon }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        {{ $hp->SoTinChi }}
                                    </td>
                                    <td class="text-center">
                                        @if($hp->TrangThai === 'Đang sử dụng' || $hp->TrangThai === 'Đang áp dụng')
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
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Không tìm thấy học phần nào thuộc bộ môn này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-muted small">
                        Hiển thị {{ $hocPhanBoMons->firstItem() ?? 0 }}–{{ $hocPhanBoMons->lastItem() ?? 0 }} trên tổng số {{ $hocPhanBoMons->total() }} học phần
                    </div>
                    <div>
                        {{ $hocPhanBoMons->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            {{-- ═══════════════════════════════════════════════════════════════
                 TAB 2: HỌC PHẦN DÙNG CHUNG
            ═══════════════════════════════════════════════════════════════ --}}
            @else
                <!-- Mô tả Tab Học phần dùng chung -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <div>
                        <div class="fw-semibold text-dark">Học phần dùng chung</div>
                        <div class="text-muted small">Các học phần được sử dụng chung trong Khoa và không thuộc riêng một bộ môn nào.</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-navy fw-semibold px-3 py-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#createDungChungModal">
                            <i class="fa-solid fa-plus me-1"></i> Thêm học phần dùng chung
                        </button>
                    </div>
                </div>

                <!-- Bộ lọc tab dùng chung (Không có bộ lọc bộ môn) -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <form method="GET" action="{{ route('hocphan.index') }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 580px;">
                        <input type="hidden" name="tab" value="dung_chung">

                        <div class="input-group input-group-sm" style="max-width: 320px;">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo mã hoặc tên học phần...">
                        </div>

                        <select name="trang_thai" class="form-select form-select-sm" style="max-width: 170px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="Đang sử dụng" {{ request('trang_thai') === 'Đang sử dụng' ? 'selected' : '' }}>Đang sử dụng</option>
                            <option value="Ngừng sử dụng" {{ request('trang_thai') === 'Ngừng sử dụng' ? 'selected' : '' }}>Ngừng sử dụng</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-outline-secondary">Lọc</button>

                        @if(request('search') || request('trang_thai'))
                            <a href="{{ route('hocphan.index', ['tab' => 'dung_chung']) }}" class="btn btn-sm btn-link text-secondary text-decoration-none">Xóa lọc</a>
                        @endif
                    </form>
                </div>

                <!-- Bảng danh sách học phần dùng chung (KHÔNG CÓ CỘT BỘ MÔN) -->
                <div class="table-responsive border rounded-2 mb-3">
                    <table class="table table-hp align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">STT</th>
                                <th width="15%">MÃ HỌC PHẦN</th>
                                <th width="35%">TÊN HỌC PHẦN</th>
                                <th class="text-end" width="10%">SỐ TÍN CHỈ</th>
                                <th class="text-end" width="10%">SỐ TIẾT LT</th>
                                <th class="text-end" width="10%">SỐ TIẾT TH</th>
                                <th class="text-center" width="10%">TRẠNG THÁI</th>
                                <th class="text-center" width="10%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hocPhanDungChungs as $index => $hp)
                                <tr>
                                    <td class="text-center text-muted small">
                                        {{ $hocPhanDungChungs->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <code class="fw-bold text-dark">{{ $hp->MaHocPhan }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $hp->TenHocPhan }}</div>
                                        <div class="text-muted small mt-0.5">
                                            @if($hp->MoTa)
                                                <span>{{ $hp->MoTa }}</span>
                                            @else
                                                <span class="fst-italic text-secondary">Học phần dùng chung toàn Khoa</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        {{ $hp->SoTinChi }}
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $hp->SoTietLT ?? 0 }}
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $hp->SoTietTH ?? 0 }}
                                    </td>
                                    <td class="text-center">
                                        @if($hp->TrangThai === 'Đang sử dụng' || $hp->TrangThai === 'Đang áp dụng')
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
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Chưa có học phần dùng chung nào được tạo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang Tab Dùng chung -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-muted small">
                        Hiển thị {{ $hocPhanDungChungs->firstItem() ?? 0 }}–{{ $hocPhanDungChungs->lastItem() ?? 0 }} trên tổng số {{ $hocPhanDungChungs->total() }} học phần
                    </div>
                    <div>
                        {{ $hocPhanDungChungs->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════════
     MODAL 1: THÊM HỌC PHẦN DÙNG CHUNG (MỤC 5)
     Không có trường Bộ môn, có ghi chú khẳng định là học phần dùng chung
═══════════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createDungChungModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title fw-bold" style="color: #00305a;">Thêm học phần dùng chung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hocphan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="is_dung_chung" value="1">
                <input type="hidden" name="MaBoMon" value="">
                <input type="hidden" name="LoaiHocPhan" value="Khóa luận">

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small border-0 mb-4" style="background-color: #f0f9ff; color: #0369a1;">
                        <i class="fa-solid fa-circle-info me-1"></i> Học phần này được xác định là học phần dùng chung và không thuộc riêng một bộ môn.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small text-dark">Mã học phần *</label>
                            <input type="text" name="MaHocPhan" class="form-control form-control-sm" value="{{ old('MaHocPhan', $suggestedMaHocPhan ?? '') }}" required placeholder="VD: HP_KLCN, HP_KLTN...">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small text-dark">Tên học phần *</label>
                            <input type="text" name="TenHocPhan" class="form-control form-control-sm" value="{{ old('TenHocPhan') }}" required placeholder="VD: Khóa luận cử nhân, Khóa luận tốt nghiệp...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-dark">Số tín chỉ *</label>
                            <input type="number" name="SoTinChi" class="form-control form-control-sm" value="{{ old('SoTinChi', 10) }}" min="1" max="30" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Số tiết lý thuyết</label>
                            <input type="number" name="SoTietLT" class="form-control form-control-sm" value="{{ old('SoTietLT', 0) }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Số tiết thực hành</label>
                            <input type="number" name="SoTietTH" class="form-control form-control-sm" value="{{ old('SoTietTH', 0) }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">Số tiết khác</label>
                            <input type="number" name="SoTietKhac" class="form-control form-control-sm" value="{{ old('SoTietKhac', 150) }}" min="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Trạng thái</label>
                            <select name="TrangThai" class="form-select form-select-sm">
                                <option value="Đang sử dụng" selected>Đang sử dụng</option>
                                <option value="Ngừng sử dụng">Ngừng sử dụng</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Khoa quản lý</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="Khoa Công nghệ Thông tin" readonly>
                            <input type="hidden" name="MaKhoa" value="CNTT">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">Mô tả</label>
                            <textarea name="MoTa" rows="3" class="form-control form-control-sm" placeholder="Mô tả học phần dùng chung, điều kiện đăng ký...">{{ old('MoTa') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-navy fw-semibold px-4">Lưu học phần</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════════
     MODAL 2: THÊM HỌC PHẦN THEO BỘ MÔN (MỤC 3)
     Gồm: Mã học phần, Tên học phần, Số tín chỉ, Bộ môn, Số tiết LT, Số tiết TH, Trạng thái
═══════════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createBoMonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title fw-bold" style="color: #00305a;">Thêm học phần theo bộ môn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hocphan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="is_dung_chung" value="0">
                <input type="hidden" name="LoaiHocPhan" value="Chuyên ngành">
                <input type="hidden" name="MaKhoa" value="CNTT">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small text-dark">Mã học phần *</label>
                            <input type="text" name="MaHocPhan" class="form-control form-control-sm" value="{{ old('MaHocPhan', $suggestedMaHocPhan ?? '') }}" required placeholder="VD: HP_CNPM03...">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small text-dark">Tên học phần *</label>
                            <input type="text" name="TenHocPhan" class="form-control form-control-sm" value="{{ old('TenHocPhan') }}" required placeholder="VD: Lập trình Web, An toàn mạng...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Bộ môn *</label>
                            <select name="MaBoMon" class="form-select form-select-sm" required>
                                <option value="">-- Chọn bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon', request('ma_bomon')) == $bm->MaBoMon ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Số tín chỉ *</label>
                            <input type="number" name="SoTinChi" class="form-control form-control-sm" value="{{ old('SoTinChi', 3) }}" min="1" max="30" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Số tiết lý thuyết</label>
                            <input type="number" name="SoTietLT" class="form-control form-control-sm" value="{{ old('SoTietLT', 30) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Số tiết thực hành</label>
                            <input type="number" name="SoTietTH" class="form-control form-control-sm" value="{{ old('SoTietTH', 30) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Trạng thái</label>
                            <select name="TrangThai" class="form-select form-select-sm">
                                <option value="Đang sử dụng" selected>Đang sử dụng</option>
                                <option value="Ngừng sử dụng">Ngừng sử dụng</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">Mô tả</label>
                            <textarea name="MoTa" rows="3" class="form-control form-control-sm" placeholder="Mô tả học phần chuyên ngành...">{{ old('MoTa') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-navy fw-semibold px-4">Lưu học phần</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════════
     MODAL EDIT & DELETE DÀNH CHO CẢ 2 DANH SÁCH (MỤC 6 & MỤC 7)
═══════════════════════════════════════════════════════════════════════════════ --}}
@php
    $allHocPhans = ($activeTab === 'bomon') ? $hocPhanBoMons : $hocPhanDungChungs;
@endphp

@foreach($allHocPhans as $hp)
    <!-- Modal Chỉnh Sửa Học Phần -->
    <div class="modal fade" id="editModal{{ $hp->MaHocPhan }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title fw-bold" style="color: #00305a;">
                        @if($hp->isDungChung())
                            Chỉnh sửa học phần dùng chung
                        @else
                            Chỉnh sửa học phần theo bộ môn
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('hocphan.update', $hp->MaHocPhan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if($hp->isDungChung())
                        <input type="hidden" name="is_dung_chung" value="1">
                        <input type="hidden" name="MaBoMon" value="">
                    @else
                        <input type="hidden" name="is_dung_chung" value="0">
                    @endif

                    <div class="modal-body p-4">
                        @if($hp->isDungChung())
                            <div class="alert alert-info py-2 px-3 small border-0 mb-4" style="background-color: #f0f9ff; color: #0369a1;">
                                <i class="fa-solid fa-circle-info me-1"></i> Học phần này là <strong>Học phần dùng chung</strong> trong Khoa và không thể gán vào bộ môn nào.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold small text-secondary">Mã học phần</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $hp->MaHocPhan }}" readonly>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold small text-dark">Tên học phần *</label>
                                <input type="text" name="TenHocPhan" class="form-control form-control-sm" value="{{ old('TenHocPhan', $hp->TenHocPhan) }}" required>
                            </div>

                            @if(!$hp->isDungChung())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Bộ môn *</label>
                                    <select name="MaBoMon" class="form-select form-select-sm" required>
                                        @foreach($bomons as $bm)
                                            <option value="{{ $bm->MaBoMon }}" {{ old('MaBoMon', $hp->MaBoMon) == $bm->MaBoMon ? 'selected' : '' }}>
                                                {{ $bm->TenBoMon }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="{{ $hp->isDungChung() ? 'col-md-3' : 'col-md-6' }}">
                                <label class="form-label fw-semibold small text-dark">Số tín chỉ *</label>
                                <input type="number" name="SoTinChi" class="form-control form-control-sm" value="{{ old('SoTinChi', $hp->SoTinChi) }}" min="1" max="30" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-secondary">Số tiết lý thuyết</label>
                                <input type="number" name="SoTietLT" class="form-control form-control-sm" value="{{ old('SoTietLT', $hp->SoTietLT ?? 0) }}" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-secondary">Số tiết thực hành</label>
                                <input type="number" name="SoTietTH" class="form-control form-control-sm" value="{{ old('SoTietTH', $hp->SoTietTH ?? 0) }}" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-secondary">Số tiết khác</label>
                                <input type="number" name="SoTietKhac" class="form-control form-control-sm" value="{{ old('SoTietKhac', $hp->SoTietKhac ?? 0) }}" min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">Trạng thái</label>
                                <select name="TrangThai" class="form-select form-select-sm">
                                    <option value="Đang sử dụng" {{ old('TrangThai', $hp->TrangThai) === 'Đang sử dụng' || old('TrangThai', $hp->TrangThai) === 'Đang áp dụng' ? 'selected' : '' }}>Đang sử dụng</option>
                                    <option value="Ngừng sử dụng" {{ old('TrangThai', $hp->TrangThai) === 'Ngừng sử dụng' || old('TrangThai', $hp->TrangThai) === 'Tạm ngưng' ? 'selected' : '' }}>Ngừng sử dụng</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Mô tả</label>
                                <textarea name="MoTa" rows="3" class="form-control form-control-sm">{{ old('MoTa', $hp->MoTa) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-2.5 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-sm btn-navy fw-semibold px-4">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xóa Học Phần (Mục 7) -->
    <div class="modal fade" id="deleteModal{{ $hp->MaHocPhan }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title fw-bold text-danger">Xóa học phần?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($hp->de_tais_count > 0 || $hp->nhoms_count > 0)
                        <div class="alert alert-warning border-0 small mb-0" style="background-color: #fffbeb; color: #92400e;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <strong>Không thể xóa học phần đang được sử dụng trong hệ thống.</strong><br>
                            Học phần “{{ $hp->TenHocPhan }}” hiện đang có <strong>{{ $hp->de_tais_count }} đề tài</strong> và <strong>{{ $hp->nhoms_count }} nhóm khóa luận</strong> liên quan. Vui lòng chuyển trạng thái sang “Ngừng sử dụng”.
                        </div>
                    @else
                        <p class="mb-2 text-dark">
                            Bạn có chắc chắn muốn xóa học phần <strong>“{{ $hp->TenHocPhan }}”</strong> (<code>{{ $hp->MaHocPhan }}</code>) không?
                        </p>
                        <p class="text-danger small mb-0">
                            Dữ liệu liên quan đến học phần có thể bị ảnh hưởng.
                        </p>
                    @endif
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Hủy</button>
                    @if($hp->de_tais_count == 0 && $hp->nhoms_count == 0)
                        <form action="{{ route('hocphan.destroy', $hp->MaHocPhan) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger fw-semibold px-3">Xác nhận xóa</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
