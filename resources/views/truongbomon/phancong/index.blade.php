@extends('layouts.truongbomon')

@section('page_title', 'Phân Công Phản Biện Đề Cương')

@push('styles')
<style>
    /* ── ACADEMIC PILL TABS ── */
    .admin-pill-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        padding: 8px 12px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow-x: auto;
        white-space: nowrap;
        flex-wrap: nowrap;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        -webkit-overflow-scrolling: touch;
    }
    .admin-pill-tabs::-webkit-scrollbar {
        height: 5px;
    }
    .admin-pill-tabs::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 999px;
    }
    .admin-pill-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 13px;
        font-size: 0.81rem;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.18s ease;
    }
    .admin-pill-tab:hover {
        background: #f1f5f9;
        color: #002855;
        border-color: #cbd5e1;
    }
    .admin-pill-tab.active {
        background: #002855;
        color: #ffffff;
        border-color: #002855;
        box-shadow: 0 2px 6px rgba(0, 40, 85, 0.18);
    }
    .admin-pill-tab .pill-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .admin-pill-tab .pill-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        font-size: 0.73rem;
        font-weight: 700;
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        border: 1px solid #e2e8f0;
        line-height: 1;
    }
    .admin-pill-tab.active .pill-count {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
    }

    /* ── TABLE STYLING ── */
    .badge-code {
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 0.78rem;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }
    .table-academic thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-academic tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.88rem;
    }
    .table-academic tbody tr:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="page-main-title mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-plus text-primary"></i> Phân Công Phản Biện Đề Cương – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
            </h4>
            <p class="text-muted mb-0 small">
                Chỉ định Giảng viên phản biện thẩm định nội dung, tính khả thi và mục tiêu nghiên cứu của đề cương khóa luận.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongbomon.duyet_decuong.index') }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-file-circle-check me-1"></i> Sang Trang Duyệt Đề Cương
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-list-check me-1"></i> Duyệt Đề Xuất Đề Tài
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="admin-pill-tabs mb-3" role="navigation">
        <a class="admin-pill-tab {{ $statusTab === 'chua_phan_cong' ? 'active' : '' }}" 
           href="{{ route('truongbomon.phancong.index', ['tab' => 'chua_phan_cong']) }}">
            <span class="pill-dot" style="background:#f59e0b;"></span>
            <span>Chưa Phân Công</span>
            <span class="pill-count">{{ $counts['chua_phan_cong'] }}</span>
        </a>
        <a class="admin-pill-tab {{ $statusTab === 'dang_phan_bien' ? 'active' : '' }}" 
           href="{{ route('truongbomon.phancong.index', ['tab' => 'dang_phan_bien']) }}">
            <span class="pill-dot" style="background:#06b6d4;"></span>
            <span>Đang Phản Biện</span>
            <span class="pill-count">{{ $counts['dang_phan_bien'] }}</span>
        </a>
        <a class="admin-pill-tab {{ $statusTab === 'da_phan_bien' ? 'active' : '' }}" 
           href="{{ route('truongbomon.phancong.index', ['tab' => 'da_phan_bien']) }}">
            <span class="pill-dot" style="background:#10b981;"></span>
            <span>Đã Có Kết Quả</span>
            <span class="pill-count">{{ $counts['da_phan_bien'] }}</span>
        </a>
        <a class="admin-pill-tab {{ $statusTab === 'ALL' ? 'active' : '' }}" 
           href="{{ route('truongbomon.phancong.index', ['tab' => 'ALL']) }}">
            <i class="fa-solid fa-layer-group text-primary {{ $statusTab === 'ALL' ? 'text-white' : '' }}"></i>
            <span>Tất Cả Đề Tài</span>
            <span class="pill-count">{{ $counts['total'] }}</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('truongbomon.phancong.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $statusTab }}">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm theo mã, tên đề tài..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="MaGV" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả GV đề xuất --</option>
                        @foreach($giangViens as $gvOption)
                            <option value="{{ $gvOption->MaGV }}" {{ request('MaGV') == $gvOption->MaGV ? 'selected' : '' }}>
                                {{ $gvOption->HoTen }} ({{ $gvOption->MaGV }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="MaHocKy" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'MaHocKy', 'MaGV']))
                        <a href="{{ route('truongbomon.phancong.index', ['tab' => $statusTab]) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Topics Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-table-list text-primary"></i> Danh Sách Phân Công Phản Biện Đề Cương
            </h5>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                Tổng: <strong>{{ $detais->total() }}</strong> đề tài
            </span>
        </div>

        @if($detais->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-user-check fa-3x mb-3 text-secondary opacity-50"></i>
                <h6 class="fw-semibold">Không có đề tài nào phù hợp với bộ lọc hiện tại.</h6>
                <p class="small text-muted mb-0">Thử thay đổi học kỳ, giảng viên hoặc từ khóa tìm kiếm.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-academic table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="9%">Mã ĐT</th>
                            <th width="28%">Tên Đề Tài &amp; Ngành</th>
                            <th width="16%">GV Đề Xuất</th>
                            <th width="10%" class="text-center">Đề Cương</th>
                            <th width="18%">GV Phản Biện Đề Cương</th>
                            <th width="9%" class="text-center">Trạng Thái ĐT</th>
                            <th width="10%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detais as $dt)
                            @php
                                $pb = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge-code">{{ $dt->MaDeTai }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                    <div class="small text-muted mt-1 d-flex align-items-center gap-2">
                                        <span><i class="fa-solid fa-graduation-cap me-1 text-primary"></i>{{ $dt->nganh->TenNganh ?? 'Chung' }}</span>
                                        <span>&bull;</span>
                                        <span><i class="fa-regular fa-calendar me-1"></i>{{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}</span>
                                        @if($dt->LinhVuc)
                                            <span class="badge bg-light text-secondary border rounded-pill">{{ $dt->LinhVuc }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $dt->giangVien->HoTen ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $dt->giangVien->MaGV ?? '' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($dt->FileDeCuong)
                                        @php
                                            $filePath = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                            $fileName = basename($dt->FileDeCuong);
                                        @endphp
                                        <div class="d-flex flex-column gap-1 align-items-center">
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-1 shadow-sm" 
                                                    onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($dt->TenDeTai) }}')"
                                                    title="Xem nhanh đề cương trực tiếp trên web">
                                                <i class="fa-solid fa-eye me-1"></i> Xem nhanh
                                            </button>
                                            <a href="{{ $filePath }}" target="_blank" class="text-muted small text-decoration-none" title="Mở tab mới hoặc tải về">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở tab
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small"><i class="fa-solid fa-circle-minus me-1"></i> Chưa có</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pb)
                                        <div class="fw-bold text-dark">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $pb->MaGV }} ({{ $pb->giangVien->boMon->TenBoMon ?? '' }})</div>
                                        <div class="mt-1">
                                            @if($pb->KetQua === 'Đạt')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                            @elseif($pb->KetQua === 'Đã nộp lại' || $pb->TrangThai === 'Đã nộp lại đề cương' || $dt->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-rotate me-1"></i> Đã nộp lại ĐC</span>
                                            @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="fa-solid fa-pen me-1"></i> Cần sửa</span>
                                            @elseif($pb->KetQua === 'Không đạt')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="fa-solid fa-hourglass-start me-1"></i> Chờ nhận xét</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Chưa phân công
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $pcStatusBadge = match($dt->TrangThai) {
                                            'Trưởng khoa đã duyệt - Chờ nộp đề cương' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                            'Đã nộp đề cương - Chờ phân công PB' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                            'Đang phản biện đề cương' => 'bg-info-subtle text-info border border-info-subtle',
                                            'Đã cập nhật đề cương - Chờ phản biện lại' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã phản biện - Chờ duyệt BM' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'Đã công bố' => 'bg-success-subtle text-success border border-success-subtle',
                                            'Yêu cầu chỉnh sửa đề cương', 'Yêu cầu chỉnh sửa' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            'Không đạt phản biện', 'Từ chối' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            default => 'bg-light text-muted border',
                                        };
                                    @endphp
                                    <span class="badge {{ $pcStatusBadge }} px-2 py-1" style="font-size: 0.76rem; border-radius: 6px;">{{ $dt->TrangThai }}</span>
                                </td>
                                <td class="text-center">
                                    @if($pb && $pb->KetQua === 'Đạt' && $dt->TrangThai !== 'Đã công bố')
                                        {{-- Nút duyệt đề cương lần 2 & công bố đề tài của TBM --}}
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap fw-bold shadow-sm mb-1 d-inline-flex align-items-center gap-1"
                                                data-bs-toggle="modal" data-bs-target="#modalDuyetDeCuong{{ $dt->MaDeTai }}"
                                                title="Phê duyệt kết quả phản biện và Công bố đề tài">
                                            <i class="fa-solid fa-bullhorn"></i> Duyệt ĐC
                                        </button>
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-0.5 text-nowrap" 
                                                    data-bs-toggle="modal" data-bs-target="#modalPhanCongPB{{ $dt->MaDeTai }}" title="Đổi giảng viên phản biện">
                                                <i class="fa-solid fa-user-pen"></i>
                                            </button>
                                            <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-0.5" title="Xem chi tiết đề tài">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    @elseif($pb && $pb->KetQua === 'Yêu cầu chỉnh sửa' && $dt->TrangThai !== 'Đã công bố')
                                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-2 py-1 text-nowrap fw-bold shadow-sm mb-1 d-inline-flex align-items-center gap-1"
                                                data-bs-toggle="modal" data-bs-target="#modalYeuCauSuaDC{{ $dt->MaDeTai }}"
                                                title="Gửi yêu cầu chỉnh sửa đề cương cho GVHD">
                                            <i class="fa-solid fa-pen-to-square"></i> Yêu cầu sửa
                                        </button>
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-0.5 text-nowrap" 
                                                    data-bs-toggle="modal" data-bs-target="#modalPhanCongPB{{ $dt->MaDeTai }}" title="Đổi giảng viên phản biện">
                                                <i class="fa-solid fa-user-pen"></i>
                                            </button>
                                            <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-0.5" title="Xem chi tiết đề tài">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    @elseif($dt->TrangThai === 'Đã công bố')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mb-1 d-inline-block">
                                            <i class="fa-solid fa-circle-check me-1"></i> Đã công bố
                                        </span>
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-0.5 text-nowrap" 
                                                    data-bs-toggle="modal" data-bs-target="#modalPhanCongPB{{ $dt->MaDeTai }}" title="Đổi giảng viên phản biện">
                                                <i class="fa-solid fa-user-pen"></i>
                                            </button>
                                            <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-0.5" title="Xem chi tiết đề tài">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    @else
                                        @if($dt->FileDeCuong)
                                            <button type="button" class="btn btn-sm {{ $pb ? 'btn-outline-secondary' : 'btn-primary' }} rounded-3 px-2 py-1 text-nowrap" 
                                                    data-bs-toggle="modal" data-bs-target="#modalPhanCongPB{{ $dt->MaDeTai }}">
                                                <i class="fa-solid {{ $pb ? 'fa-user-pen' : 'fa-user-plus' }} me-1"></i>
                                                {{ $pb ? 'Đổi PB' : 'Phân công' }}
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-light border text-muted rounded-3 px-2 py-1 text-nowrap" disabled title="Chưa có file đề cương chi tiết để phân công">
                                                <i class="fa-solid fa-clock me-1"></i> Chờ nộp ĐC
                                            </button>
                                        @endif
                                        <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1 ms-1" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal Phân Công Phản Biện -->
                            <div class="modal fade" id="modalPhanCongPB{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow border-0">
                                        <form action="{{ route('truongbomon.phancong.store', $dt->MaDeTai) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-user-plus text-primary"></i>
                                                    Phân Công Phản Biện Đề Cương
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Đề tài:</label>
                                                    <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                                    <div class="small text-muted mt-1">GV Đề xuất: <strong>{{ $dt->giangVien->HoTen ?? 'N/A' }}</strong> ({{ $dt->giangVien->MaGV ?? '' }})</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                        Chọn Giảng viên phản biện (Bộ môn {{ $dt->giangVien?->boMon?->TenBoMon ?? '' }}) <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="MaGVPhanBien" class="form-select rounded-3" required>
                                                        <option value="">-- Chọn Giảng viên phản biện (Bộ môn {{ $dt->giangVien?->boMon?->TenBoMon ?? '' }}) --</option>
                                                        @foreach($allGiangViens as $gvOption)
                                                            @if($gvOption->MaGV !== $dt->MaGV && (!$dt->giangVien || $gvOption->MaBoMon === $dt->giangVien->MaBoMon))
                                                                <option value="{{ $gvOption->MaGV }}" {{ ($pb && $pb->MaGV === $gvOption->MaGV) ? 'selected' : '' }}>
                                                                    {{ $gvOption->HoTen }} ({{ $gvOption->MaGV }} - {{ $gvOption->boMon->TenBoMon ?? 'N/A' }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text text-muted mt-1">
                                                        <i class="fa-solid fa-circle-info me-1 text-primary"></i> Chỉ hiển thị các Giảng viên thuộc Bộ môn {{ $dt->giangVien?->boMon?->TenBoMon ?? '' }}. Quy định BR07: GV đề xuất đề tài không được làm phản biện cho chính đề tài đó.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                                                    <i class="fa-solid fa-check me-1"></i> Xác nhận phân công
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if($pb && $pb->KetQua === 'Đạt')
                            <!-- Modal Duyệt Đề Cương & Công Bố Đề Tài (Lần 2) -->
                            <div class="modal fade" id="modalDuyetDeCuong{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow border-0">
                                        <form action="{{ route('truongbomon.duyet_detai.duyetVaCongBo', $dt->MaDeTai) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-bullhorn"></i>
                                                    Phê Duyệt Đề Cương &amp; Công Bố Đề Tài
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Tên đề tài:</label>
                                                    <div class="fw-bold text-dark fs-6">{{ $dt->TenDeTai }}</div>
                                                    <div class="small text-muted mt-1">
                                                        GV Đề xuất: <strong>{{ $dt->giangVien->HoTen ?? 'N/A' }}</strong> ({{ $dt->giangVien->MaGV ?? '' }})
                                                    </div>
                                                </div>

                                                <div class="p-3 bg-light rounded-3 border mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="small text-muted fw-bold">Giảng viên phản biện:</span>
                                                        <strong class="text-dark">{{ $pb->giangVien->HoTen ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="small text-muted fw-bold">Kết luận thẩm định:</span>
                                                        <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-check me-1"></i> ĐẠT YÊU CẦU</span>
                                                    </div>
                                                    @if($pb->NhanXet)
                                                        <div class="mt-2 pt-2 border-top">
                                                            <div class="small text-muted fw-bold mb-1">Ý kiến / Nhận xét của GV phản biện:</div>
                                                            <div class="small text-secondary bg-white p-2 rounded border" style="white-space: pre-line;">{{ $pb->NhanXet }}</div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="alert alert-success border-0 small mb-0 d-flex gap-2 align-items-start">
                                                    <i class="fa-solid fa-circle-check text-success fa-lg mt-1"></i>
                                                    <div>
                                                        <strong>Xác nhận phê duyệt lần 2:</strong><br>
                                                        Sau khi Trưởng bộ môn phê duyệt, đề tài sẽ chính thức chuyển sang trạng thái <strong>"Đã công bố"</strong> và mở quyền cho sinh viên bắt đầu đăng ký!
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm">
                                                    <i class="fa-solid fa-check-double me-1"></i> Xác Nhận Duyệt &amp; Công Bố
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($pb && $pb->KetQua === 'Yêu cầu chỉnh sửa')
                            <!-- Modal Yêu Cầu Chỉnh Sửa Đề Cương -->
                            <div class="modal fade" id="modalYeuCauSuaDC{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow border-0">
                                        <form action="{{ route('truongbomon.duyet_detai.yeuCauChinhSua', $dt->MaDeTai) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                    Yêu Cầu Chỉnh Sửa Đề Cương
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Tên đề tài:</label>
                                                    <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Ý kiến / Nội dung yêu cầu chỉnh sửa đề cương <span class="text-danger">*</span></label>
                                                    <textarea name="YeuCauSua" class="form-control" rows="4" required 
                                                              placeholder="Ghi rõ nội dung đề cương hoặc phương pháp cần giảng viên chỉnh sửa lại...">{{ $pb->NhanXet ? "Ý kiến GVPB: " . $pb->NhanXet : '' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm">
                                                    <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Cho GV
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($detais->hasPages())
                <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Hiển thị <strong>{{ $detais->firstItem() }}</strong> - <strong>{{ $detais->lastItem() }}</strong> trên tổng <strong>{{ $detais->total() }}</strong> đề tài
                    </div>
                    <div>
                        {{ $detais->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
