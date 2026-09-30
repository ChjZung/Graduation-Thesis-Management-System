@extends('layouts.truongbomon')

@section('page_title', 'Theo Dõi Tiến Độ Bộ Môn')

@push('styles')
<style>
    /* ── MAIN TABS SWITCHER ── */
    #theoDoiMainTabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
    }
    #theoDoiMainTabs .nav-link {
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 8px 24px;
        border-radius: 999px;
        border: none;
        transition: all 0.2s ease;
    }
    #theoDoiMainTabs .nav-link:hover {
        color: #002855;
        background: rgba(255, 255, 255, 0.6);
    }
    #theoDoiMainTabs .nav-link.active {
        background: #002855;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 40, 85, 0.2);
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
                <i class="fa-solid fa-list-check text-primary"></i> Theo Dõi Tiến Độ Đề Tài &amp; GVHD – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
            </h4>
            <p class="text-muted mb-0 small">
                Giám sát việc thực hiện đề tài của các nhóm sinh viên, định mức hướng dẫn của Giảng viên và tiến độ hoàn thành khóa luận.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongbomon.duyet_detai.index', ['tab' => 'thongke']) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-chart-column me-1"></i> Xem Thống Kê Bộ Môn
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-clipboard-check me-1"></i> Duyệt Đề Tài
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Nhóm Sinh Viên</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total_nhom'] }}</h3>
                    <div class="small text-muted">{{ $stats['nhom_dang_thuc_hien'] }} nhóm đang thực hiện</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Đề Tài Đã Công Bố</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['detai_cong_bo'] }}</h3>
                    <div class="small text-muted">Trên tổng số {{ $stats['total_detai'] }} đề tài</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Giảng Viên Bộ Môn</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['total_gv'] }}</h3>
                    <div class="small text-muted">Cán bộ chuyên môn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #20c997 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Nhóm Đã Hoàn Thành</div>
                    <h3 class="fw-bold my-1 text-teal">{{ $stats['nhom_hoan_thanh'] }}</h3>
                    <div class="small text-muted">Đã bảo vệ thành công</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-4">
        <ul class="nav nav-pills d-inline-flex" id="theoDoiMainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nhom-tab" data-bs-toggle="pill" data-bs-target="#nhom-pane" type="button" role="tab">
                    <i class="fa-solid fa-users me-2"></i> Tiến Độ Nhóm &amp; Đề Tài ({{ $stats['total_nhom'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gv-tab" data-bs-toggle="pill" data-bs-target="#gv-pane" type="button" role="tab">
                    <i class="fa-solid fa-chalkboard-user me-2"></i> Giám Sát Giảng Viên Hướng Dẫn ({{ $stats['total_gv'] }})
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="theodoiTabContent">
        <!-- TAB 1: TIẾN ĐỘ NHÓM & ĐỀ TÀI -->
        <div class="tab-pane fade show active" id="nhom-pane" role="tabpanel">
            <!-- Filter and Search -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('truongbomon.theodoi.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" 
                                       placeholder="Tìm theo tên nhóm, đề tài, tên hoặc MSSV trưởng nhóm..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="MaGV" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả Giảng viên hướng dẫn --</option>
                                @foreach($giangViens as $gv)
                                    <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                        {{ $gv->HoTen }} ({{ $gv->nhoms_count }} nhóm)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold w-100">
                                <i class="fa-solid fa-filter me-1"></i> Lọc dữ liệu
                            </button>
                            @if(request()->anyFilled(['search', 'MaGV']))
                                <a href="{{ route('truongbomon.theodoi.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại bộ lọc">
                                    <i class="fa-solid fa-rotate"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Groups Tracking Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-table-list text-primary"></i> Danh Sách Nhóm Khóa Luận &amp; Tiến Độ
                    </h5>
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                        Tổng: <strong>{{ $nhoms->total() }}</strong> nhóm
                    </span>
                </div>

                <div class="card-body p-0">
                    @if($nhoms->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users fa-3x mb-3 text-secondary opacity-50"></i>
                            <h6 class="fw-semibold">Không tìm thấy nhóm sinh viên nào theo điều kiện tìm kiếm.</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-academic table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="10%">Mã Nhóm</th>
                                        <th width="28%">Đề Tài Đăng Ký</th>
                                        <th width="18%">Thành Viên Nhóm</th>
                                        <th width="16%">GV Hướng Dẫn</th>
                                        <th width="14%" class="text-center">Báo Cáo Tiến Độ</th>
                                        <th width="14%" class="text-center">Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nhoms as $nhom)
                                        @php
                                            $dt = $nhom->deTai;
                                            $gvhd = $dt->giangVien ?? null;
                                            $bcCount = $nhom->baoCaos ? $nhom->baoCaos->count() : 0;
                                            $bcApproved = $nhom->baoCaos ? $nhom->baoCaos->where('TrangThai', 'Đã duyệt')->count() : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge-code">{{ $nhom->MaNhom }}</span>
                                                <div class="small text-muted mt-1">{{ $nhom->TenNhom }}</div>
                                            </td>
                                            <td>
                                                @if($dt)
                                                    <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                                    <div class="small text-muted mt-1">
                                                        <span class="badge-code">{{ $dt->MaDeTai }}</span>
                                                        <span class="ms-1">{{ $dt->hocKy->TenHocKy ?? '' }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted fst-italic">Chưa có đề tài</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($nhom->thanhVienNhoms && $nhom->thanhVienNhoms->isNotEmpty())
                                                    <ul class="list-unstyled mb-0 small">
                                                        @foreach($nhom->thanhVienNhoms as $tv)
                                                            <li class="mb-1">
                                                                <span class="fw-semibold text-dark">{{ $tv->sinhVien->HoTen ?? 'N/A' }}</span>
                                                                <span class="text-muted">({{ $tv->MaSV }})</span>
                                                                @if($tv->VaiTro === 'Trưởng nhóm')
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">TN</span>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted small">0 sinh viên</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($gvhd)
                                                    <div class="fw-semibold text-dark">{{ $gvhd->HoTen }}</div>
                                                    <div class="small text-muted">{{ $gvhd->MaGV }} &bull; {{ $gvhd->boMon->TenBoMon ?? '' }}</div>
                                                @else
                                                    <span class="text-muted small">Chưa phân công</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="small fw-bold text-dark">{{ $bcApproved }}/{{ $bcCount }} Đạt</div>
                                                <div class="progress mt-1" style="height: 5px; width: 60px; margin: 0 auto;">
                                                    @php $pct = $bcCount > 0 ? round(($bcApproved / $bcCount) * 100) : 0; @endphp
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($nhom->TrangThai === 'Hoàn thành')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-flag-checkered me-1"></i> Hoàn thành</span>
                                                @elseif($nhom->TrangThai === 'Đang thực hiện')
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="fa-solid fa-spinner me-1"></i> Đang thực hiện</span>
                                                @else
                                                    <span class="badge bg-light text-secondary border">{{ $nhom->TrangThai }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($nhoms->hasPages())
                            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="text-muted small">
                                    Hiển thị <strong>{{ $nhoms->firstItem() }}</strong> - <strong>{{ $nhoms->lastItem() }}</strong> trên tổng <strong>{{ $nhoms->total() }}</strong> nhóm
                                </div>
                                <div>
                                    {{ $nhoms->appends(request()->all())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- TAB 2: GIÁM SÁT GIẢNG VIÊN HƯỚNG DẪN (GVHD) -->
        <div class="tab-pane fade" id="gv-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chalkboard-user text-primary"></i>
                        Danh Sách Giảng Viên Thuộc Bộ Môn &amp; Tình Trạng Hướng Dẫn
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-academic table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="10%">Mã GV</th>
                                    <th width="24%">Họ và Tên Giảng Viên</th>
                                    <th width="16%">Học Vị / Học Hàm</th>
                                    <th width="12%" class="text-center">Đề Tài Đề Xuất</th>
                                    <th width="12%" class="text-center">Đã Công Bố</th>
                                    <th width="14%" class="text-center">Nhóm Đang Hướng Dẫn</th>
                                    <th width="12%" class="text-center">Tình Trạng Tải</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($giangViens as $gv)
                                    @php
                                        $quota = 5; // Định mức tối đa khuyến nghị: 5 nhóm
                                        $percent = min(100, round(($gv->nhoms_count / $quota) * 100));
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge-code">{{ $gv->MaGV }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $gv->HoTen }}</div>
                                            <div class="small text-muted">{{ $gv->Email ?? 'Chưa cập nhật email' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">
                                                {{ $gv->HocVi ?? 'Giảng viên' }}
                                                @if($gv->HocHam) &bull; {{ $gv->HocHam }} @endif
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $gv->de_tais_count }}</td>
                                        <td class="text-center fw-bold text-success">{{ $gv->de_tais_cong_bo_count }}</td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ $gv->nhoms_count >= $quota ? 'text-danger' : 'text-primary' }}">
                                                {{ $gv->nhoms_count }} / {{ $quota }} nhóm
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($gv->nhoms_count >= $quota)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Đầy tải (100%)</span>
                                            @elseif($gv->nhoms_count > 0)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">{{ $percent }}% tải</span>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1">Còn chỗ (0%)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
