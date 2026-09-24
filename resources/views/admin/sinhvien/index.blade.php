@extends('layouts.admin')

@section('page_title', 'Quản lý sinh viên & Rà soát đủ điều kiện')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            Quản Lý Sinh Viên &amp; Xét Điều Kiện Khóa Luận
        </h4>
        <div class="text-muted small">Quản lý hồ sơ sinh viên, kiểm tra tín chỉ tích lũy và xét duyệt điều kiện làm khóa luận tốt nghiệp.</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
            Học kỳ hiện tại: <strong>{{ $selectedHocKy }}</strong>
        </span>
    </div>
</div>

@if(session('import_result'))
    @php
        $alertType = session('import_alert_type') ?? (session('import_danger') ? 'import_danger' : (session('import_warning') ? 'import_warning' : 'import_result'));
        $alertClass = ($alertType === 'import_danger') ? 'alert-danger' : (($alertType === 'import_warning') ? 'alert-warning' : 'alert-success');
        $borderColor = ($alertType === 'import_danger') ? '#dc3545' : (($alertType === 'import_warning') ? '#f59e0b' : '#198754');
        $bgColor = ($alertType === 'import_danger') ? '#fef2f2' : (($alertType === 'import_warning') ? '#fffbeb' : '#f0fdf4');
    @endphp
    <div class="alert {{ $alertClass }} alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid {{ $borderColor }} !important; background-color: {{ $bgColor }};">
        {!! session('import_result') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tabs Navigation -->
<ul class="nav nav-tabs nav-tabs-huit mb-4 border-bottom" id="sinhVienUnifiedTabs" role="tablist" style="border-bottom: 2px solid #e2e8f0 !important;">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab !== 'du_dieu_kien' ? 'active fw-bold text-primary' : 'text-secondary' }}" 
           id="tab-btn-danhmuc" data-bs-toggle="tab" href="#pane-danhmuc" role="tab" aria-controls="pane-danhmuc" 
           aria-selected="{{ $activeTab !== 'du_dieu_kien' ? 'true' : 'false' }}"
           style="font-size: 0.95rem; padding: 10px 22px;">
            <i class="fa-solid fa-address-book me-2"></i>Danh Mục Sinh Viên
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'du_dieu_kien' ? 'active fw-bold text-primary' : 'text-secondary' }}" 
           id="tab-btn-dudk" data-bs-toggle="tab" href="#pane-dudk" role="tab" aria-controls="pane-dudk" 
           aria-selected="{{ $activeTab === 'du_dieu_kien' ? 'true' : 'false' }}"
           style="font-size: 0.95rem; padding: 10px 22px;">
            <i class="fa-solid fa-user-check me-2"></i>Xét Duyệt Đủ Điều Kiện Khóa Luận
        </a>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="sinhVienUnifiedContent">

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 1: QUẢN LÝ DANH MỤC SINH VIÊN                                    -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="tab-pane fade {{ $activeTab !== 'du_dieu_kien' ? 'show active' : '' }}" id="pane-danhmuc" role="tabpanel" aria-labelledby="tab-btn-danhmuc">
        
        <!-- 4 KPI Cards Tab 1 -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Tổng Sinh Viên Hồ Sơ</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-dark">{{ $statsSv['total'] ?? $sinhviens->total() }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-user-graduate fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Đủ ĐK Khóa Luận (&ge; 115 TC)</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-success">{{ $statsSv['du_dk'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">/ {{ $statsSv['total'] ?? $sinhviens->total() }} SV ({{ $statsSv['pct_du_dk'] ?? 0 }}%)</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-user-check fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-danger d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Chưa Đủ Điều Kiện Tín Chỉ</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-danger">{{ $statsSv['thieu_dk'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fee2e2; color: #dc2626;">
                        <i class="fa-solid fa-user-clock fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Tài Khoản Kích Hoạt</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-info">{{ $statsSv['active'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">tài khoản</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f7fa; color: #00838f;">
                        <i class="fa-solid fa-id-card-clip fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar Tab 1 -->
        <div class="admin-filter-bar mb-4">
            <form method="GET" action="{{ route('sinhvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                <input type="hidden" name="tab" value="danh_muc">
                <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                    <div class="input-group" style="min-width: 240px; flex: 1;">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên, MSSV, email, sđt..." value="{{ request('search') }}">
                    </div>
                    <select name="MaLop" class="form-select" style="min-width: 170px;" onchange="this.form.submit()">
                        <option value="">-- Tất cả Lớp --</option>
                        @foreach($lops as $l)
                            <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                                {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    <select name="trang_thai_hoc_tap" class="form-select" style="min-width: 160px;" onchange="this.form.submit()">
                        <option value="">-- Trạng thái học tập --</option>
                        <option value="Đang học" {{ request('trang_thai_hoc_tap') == 'Đang học' ? 'selected' : '' }}>Đang học</option>
                        <option value="Đủ điều kiện" {{ request('trang_thai_hoc_tap') == 'Đủ điều kiện' ? 'selected' : '' }}>Đủ điều kiện</option>
                        <option value="Tạm dừng" {{ request('trang_thai_hoc_tap') == 'Tạm dừng' ? 'selected' : '' }}>Tạm dừng</option>
                        <option value="Đã tốt nghiệp" {{ request('trang_thai_hoc_tap') == 'Đã tốt nghiệp' ? 'selected' : '' }}>Đã tốt nghiệp</option>
                    </select>
                    <select name="dieu_kien" class="form-select" style="min-width: 170px;" onchange="this.form.submit()">
                        <option value="">-- Điều kiện làm KL --</option>
                        <option value="du" {{ request('dieu_kien') == 'du' ? 'selected' : '' }}>Đủ điều kiện (&ge; 115 TC)</option>
                        <option value="chua" {{ request('dieu_kien') == 'chua' ? 'selected' : '' }}>Chưa đủ điều kiện</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                    <button type="button" class="btn btn-primary shadow-xs fw-semibold" onclick="switchToTabDudk()" title="Chuyển sang tab xét duyệt theo học kỳ">
                        <i class="fa-solid fa-arrow-right me-1"></i> Xét Điều Kiện
                    </button>
                    <a href="{{ route('admin.import.template', 'sinhvien') }}" class="btn btn-outline-secondary shadow-xs" title="Tải file mẫu Excel">
                        <i class="fa-solid fa-download me-1"></i> File Mẫu
                    </a>
                    <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fa-solid fa-file-import me-1"></i> Import Excel
                    </button>
                    <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Mới
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Card Tab 1 -->
        <div class="admin-table-card mb-4 bg-white rounded-3 shadow-sm border overflow-hidden">
            <div class="table-card-header d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                <div class="table-card-title fw-bold text-dark">
                    Danh Sách Sinh Viên
                </div>
                <div class="small text-muted">
                    Hiển thị <strong>{{ $sinhviens->count() }}</strong> / <strong>{{ $sinhviens->total() }}</strong> sinh viên
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" width="13%">MSSV</th>
                            <th class="py-3" width="24%">HỌ VÀ TÊN &amp; LIÊN LẠC</th>
                            <th class="py-3" width="20%">LỚP / NGÀNH / KHÓA</th>
                            <th class="py-3 text-center" width="15%">TÍN CHỈ / GPA</th>
                            <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                            <th class="py-3 text-center" width="10%">ĐIỀU KIỆN KL</th>
                            <th class="pe-4 py-3 text-center" width="6%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($sinhviens as $sv)
                        @php
                            $tk = $sv->taiKhoan;
                            $isActive = $tk ? (bool)$tk->TrangThai : true;
                            $tc = (int)($sv->SoTinChiTichLuy ?? 0);
                            $gpa = (float)($sv->DiemTichLuy ?? 0.0);
                            $isDuDk = $sv->isDuDieuKien();
                            $nhom = $sv->nhoms->first();
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $sv->MaSV }}</span>
                            </td>
                            <td class="py-3">
                                <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6">{{ $sv->HoTen }}</a>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-envelope me-1"></i>{{ $sv->Email ?? ($sv->MaSV . '@st.huit.edu.vn') }}
                                    @if($sv->SoDienThoai)
                                        &bull; <i class="fa-solid fa-phone ms-1 me-1"></i>{{ $sv->SoDienThoai }}
                                    @endif
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $sv->lop->TenLop ?? 'Chưa phân lớp' }}</div>
                                <div class="small text-muted">{{ $sv->lop->nganh->TenNganh ?? ($sv->nganh->TenNganh ?? 'Khoa CNTT') }} &bull; Khóa: {{ $sv->KhoaHoc ?? ($sv->lop->KhoaHoc ?? '2022') }}</div>
                            </td>
                            <td class="text-center py-3">
                                <div class="fw-bold {{ $tc >= 115 ? 'text-success' : 'text-danger' }}">
                                    {{ $tc }} TC
                                </div>
                                <div class="small text-muted">
                                    GPA: <span class="fw-semibold text-dark">{{ number_format($gpa, 2) }}</span> / 4.0
                                </div>
                            </td>
                            <td class="text-center py-3">
                                @if($sv->TrangThai === 'Đang học')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        Đang học
                                    </span>
                                @elseif($sv->TrangThai === 'Đủ điều kiện')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                        Đủ điều kiện
                                    </span>
                                @elseif($sv->TrangThai === 'Tạm dừng')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">
                                        Tạm dừng
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">
                                        {{ $sv->TrangThai ?? 'Chưa rõ' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center py-3">
                                @if($isDuDk)
                                    <span class="badge px-2.5 py-1 fw-semibold rounded-pill" style="background: #e6f9f0; color: #0d8a4f; border: 1px solid #bbf7d0;">
                                        Đủ ĐK
                                    </span>
                                @else
                                    <span class="badge px-2.5 py-1 fw-semibold rounded-pill" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;" title="Thiếu {{ max(0, 115 - $tc) }} TC hoặc GPA < 2.0">
                                        Chưa ĐK
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="{{ route('sinhvien.show', $sv->MaSV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết 360°">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sinhvien.edit', $sv->MaSV) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa hồ sơ">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('sinhvien.destroy', $sv->MaSV) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận xóa sinh viên {{ $sv->HoTen }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa sinh viên">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-graduate fa-3x text-secondary mb-3 opacity-50"></i>
                                <h6 class="fw-bold">Chưa có dữ liệu sinh viên phù hợp</h6>
                                <p class="small mb-3">Vui lòng điều chỉnh bộ lọc hoặc thêm mới sinh viên.</p>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createModal">Thêm Sinh Viên Mới</button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sinhviens->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted">Hiển thị <strong>{{ $sinhviens->firstItem() }} - {{ $sinhviens->lastItem() }}</strong> trên tổng số <strong>{{ $sinhviens->total() }}</strong> sinh viên</span>
                {{ $sinhviens->appends(['tab' => 'danh_muc'])->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 2: DANH SÁCH SV ĐỦ ĐIỀU KIỆN KHÓA LUẬN (THEO HỌC KỲ)               -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="tab-pane fade {{ $activeTab === 'du_dieu_kien' ? 'show active' : '' }}" id="pane-dudk" role="tabpanel" aria-labelledby="tab-btn-dudk">
        
        <!-- Controls Header Tab 2 -->
        <div class="card card-premium shadow-sm mb-4 border rounded-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <label class="form-label small fw-bold text-muted mb-1">Học Kỳ Xét Duyệt</label>
                            <form method="GET" action="{{ route('sinhvien.index') }}" id="formChonHocKy">
                                <input type="hidden" name="tab" value="du_dieu_kien">
                                <select name="MaHocKy" class="form-select form-select-sm rounded-pill fw-bold" style="min-width: 220px;" onchange="this.form.submit()">
                                    @foreach($hocKys as $hk)
                                        <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="vr d-none d-lg-block" style="height: 40px;"></div>
                        <div class="small text-muted">
                            <div>Tiêu chuẩn: Tích lũy <strong>&ge; 115 TC</strong> &bull; ĐTB <strong>&ge; 2.00</strong> &bull; Trạng thái học tập bình thường.</div>
                            <div class="text-secondary mt-0.5">Sinh viên đủ điều kiện sẽ được phép tạo nhóm và đăng ký đề tài khóa luận.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalRaSoat">
                            <i class="fa-solid fa-clipboard-check me-1"></i> Rà Soát Điều Kiện
                        </button>
                        <form method="POST" action="{{ route('admin.sinhvien.cong_bo') }}" class="d-inline" onsubmit="return confirm('Xác nhận công bố chính thức danh sách đủ điều kiện và phát hành thông báo tới toàn thể sinh viên?')">
                            @csrf
                            <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                            <button type="submit" class="btn btn-warning btn-sm text-dark rounded-pill px-3 py-2 fw-semibold shadow-xs">
                                <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Danh Sách
                            </button>
                        </form>
                        <a href="{{ route('admin.sinhvien.export_du_dk', ['MaHocKy' => $selectedHocKy]) }}" class="btn btn-success btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs">
                            <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 KPI Cards Tab 2 -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Tổng SV Rà Soát (HK: {{ $selectedHocKy }})</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-dark">{{ $statsDk['total_evaluated'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-clipboard-check fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Đủ Điều Kiện Làm Khóa Luận</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-success">{{ $statsDk['total_eligible'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-user-check fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-danger d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Chưa Đủ Điều Kiện</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-danger">{{ $statsDk['total_ineligible'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fee2e2; color: #dc2626;">
                        <i class="fa-solid fa-user-xmark fs-5"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-medium mb-1">Trạng Thái Công Bố</div>
                        <div class="mt-1">
                            @if($statsDk['is_published'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                    Đã Công Bố
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                    Chưa Công Bố
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                        <i class="fa-solid fa-bullhorn fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar Tab 2 -->
        <div class="card card-premium shadow-sm mb-4 border rounded-4 bg-white">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('sinhvien.index') }}" class="row g-3">
                    <input type="hidden" name="tab" value="du_dieu_kien">
                    <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Lớp Hành Chính</label>
                        <select name="MaLop_dk" class="form-select rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Tất cả Lớp --</option>
                            @foreach($lops as $l)
                                <option value="{{ $l->MaLop }}" {{ request('MaLop_dk') == $l->MaLop ? 'selected' : '' }}>
                                    {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Kết Quả Rà Soát</label>
                        <select name="trang_thai_dk" class="form-select rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Tất cả Trạng Thái --</option>
                            <option value="Đủ điều kiện" {{ request('trang_thai_dk') == 'Đủ điều kiện' ? 'selected' : '' }}>Đủ điều kiện</option>
                            <option value="Chưa đủ điều kiện" {{ request('trang_thai_dk') == 'Chưa đủ điều kiện' ? 'selected' : '' }}>Chưa đủ điều kiện</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Tìm Kiếm MSSV / Họ Tên</label>
                        <div class="input-group">
                            <input type="text" name="search_dk" class="form-control rounded-start-pill" placeholder="Nhập MSSV, Họ tên..." value="{{ request('search_dk') }}">
                            <button type="submit" class="btn btn-primary rounded-end-pill px-3"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Tab 2: Danh sách xét duyệt chi tiết kèm lý do -->
        <div class="admin-table-card mb-4 bg-white rounded-3 shadow-sm border overflow-hidden">
            <div class="table-card-header d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                <div class="table-card-title fw-bold text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i> Kết Quả Rà Soát Điều Kiện Khóa Luận Học Kỳ: <span class="text-primary">{{ $selectedHocKy }}</span>
                </div>
                <div class="small text-muted">
                    Hiển thị <strong>{{ $dsDuDieuKien->count() }}</strong> / <strong>{{ $dsDuDieuKien->total() }}</strong> hồ sơ xét duyệt
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="text-center ps-3 py-3" style="width: 50px;">STT</th>
                            <th class="py-3" style="width: 120px;">MSSV</th>
                            <th class="py-3" style="width: 220px;">HỌ VÀ TÊN</th>
                            <th class="py-3" style="width: 180px;">LỚP / NGÀNH</th>
                            <th class="text-center py-3" style="width: 110px;">TÍN CHỈ</th>
                            <th class="text-center py-3" style="width: 110px;">ĐTB (GPA)</th>
                            <th class="text-center py-3" style="width: 140px;">KẾT QUẢ</th>
                            <th class="py-3">LÝ DO &amp; GHI CHÚ XÉT DUYỆT</th>
                            <th class="text-center py-3" style="width: 130px;">TRẠNG THÁI SV</th>
                            <th class="text-center pe-3 py-3" style="width: 100px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($dsDuDieuKien as $idx => $item)
                            @php
                                $sv = $item->sinhVien;
                                $tc = (int)($sv->SoTinChiTichLuy ?? 0);
                                $gpa = (float)($sv->DiemTichLuy ?? 0.0);
                                $isEligible = ($item->TrangThai === 'Đủ điều kiện');
                                $nhom = $sv ? $sv->nhoms->first() : null;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold ps-3">{{ $dsDuDieuKien->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge-code">{{ $item->MaSV }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $sv->HoTen ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $sv->Email ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $sv->lop->TenLop ?? 'Chưa phân lớp' }}</span>
                                    <div class="small text-muted mt-0.5">{{ $sv->lop->nganh->TenNganh ?? '' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $tc >= 115 ? 'text-success' : 'text-danger' }}">
                                        {{ $tc }} TC
                                    </span>
                                    <div class="small text-muted">&ge; 115 TC</div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $gpa >= 2.0 ? 'text-info' : 'text-danger' }}">
                                        {{ number_format($gpa, 2) }}
                                    </span>
                                    <div class="small text-muted">&ge; 2.00</div>
                                </td>
                                <td class="text-center">
                                    @if($isEligible)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold">
                                            Đủ điều kiện
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-semibold">
                                            Chưa đủ ĐK
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($isEligible)
                                        <div class="small text-success fw-semibold">Đạt chuẩn tín chỉ (&ge; 115) &amp; ĐTB (&ge; 2.0)</div>
                                    @else
                                        <div class="small text-danger fw-semibold">{{ $item->GhiChu }}</div>
                                    @endif
                                    <div class="small text-muted mt-0.5 fst-italic">{{ $item->DieuKien }}</div>
                                </td>
                                <td class="text-center">
                                    @if($nhom)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" title="Đã có nhóm khóa luận">
                                            {{ $nhom->TenNhom }}
                                        </span>
                                    @elseif($isEligible)
                                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">
                                            Chưa ghép nhóm
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">
                                            Chưa đủ ĐK
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalCapNhat{{ $item->MaDSDK }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                    </button>

                                    <!-- Modal Cập Nhật Trạng Thái & Đặc Cách -->
                                    <div class="modal fade text-start" id="modalCapNhat{{ $item->MaDSDK }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header bg-light rounded-top-4">
                                                    <h5 class="modal-title fw-bold text-primary">Cập Nhật Điều Kiện Khóa Luận: {{ $sv->HoTen ?? $item->MaSV }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.sinhvien.cap_nhat') }}">
                                                    @csrf
                                                    <input type="hidden" name="MaDSDK" value="{{ $item->MaDSDK }}">
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">MSSV &amp; Họ Tên</label>
                                                            <input type="text" class="form-control bg-light" value="{{ $item->MaSV }} - {{ $sv->HoTen ?? '' }}" readonly>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Trạng Thái Xét Duyệt <span class="text-danger">*</span></label>
                                                            <select name="TrangThai" class="form-select" required>
                                                                <option value="Đủ điều kiện" {{ $item->TrangThai === 'Đủ điều kiện' ? 'selected' : '' }}>Đủ điều kiện</option>
                                                                <option value="Chưa đủ điều kiện" {{ $item->TrangThai === 'Chưa đủ điều kiện' ? 'selected' : '' }}>Chưa đủ điều kiện</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Lý Do Phê Duyệt / Quyết Định Đặc Cách</label>
                                                            <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập lý do hoặc quyết định đặc cách của Hội đồng Khoa...">{{ $item->GhiChu }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light rounded-bottom-4">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập Nhật Trạng Thái</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <div class="fw-bold">Chưa có dữ liệu rà soát cho học kỳ {{ $selectedHocKy }}</div>
                                    <div class="small mt-1">Bấm nút <strong>Rà soát điều kiện</strong> để hệ thống kiểm tra tiêu chí tích lũy tín chỉ &amp; GPA của sinh viên.</div>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 mt-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalRaSoat">
                                        <i class="fa-solid fa-clipboard-check me-1"></i> Bắt Đầu Rà Soát
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($dsDuDieuKien->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted">Hiển thị <strong>{{ $dsDuDieuKien->firstItem() }} - {{ $dsDuDieuKien->lastItem() }}</strong> trên tổng số <strong>{{ $dsDuDieuKien->total() }}</strong> hồ sơ</span>
                {{ $dsDuDieuKien->appends(['tab' => 'du_dieu_kien', 'MaHocKy' => $selectedHocKy])->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MODALS LIÊN QUAN                                                      -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<!-- MODAL 1: Thêm Mới Sinh Viên Thủ Công (Standardized HUIT Modal) -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-huit">
        <div class="modal-content modal-content-huit">
            <div class="modal-header-huit">
                <div class="modal-title-huit">
                    + Thêm Mới Sinh Viên Hồ Sơ Khóa Luận
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('sinhvien.store') }}">
                @csrf
                <div class="modal-body modal-create-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Số Sinh Viên (MSSV) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control form-control-huit" placeholder="22110001" required>
                            <div class="form-text small text-muted">MSSV dùng làm tài khoản đăng nhập. Mật khẩu mặc định: <code>123456</code></div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Họ Và Tên Sinh Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control form-control-huit" placeholder="Nguyễn Văn B" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Lớp Sinh Hoạt <span class="text-danger">*</span></label>
                            <select name="MaLop" class="form-select form-select-huit" required>
                                <option value="">-- Chọn Lớp Học --</option>
                                @foreach($lops as $l)
                                    <option value="{{ $l->MaLop }}">
                                        {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? 'Khoa CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control form-control-huit" placeholder="sinhvien@huit.edu.vn" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Số Điện Thoại</label>
                            <input type="text" name="SoDienThoai" class="form-control form-control-huit" placeholder="0912345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Trạng Thái Học Tập</label>
                            <select name="TrangThai" class="form-select form-select-huit">
                                <option value="Đang học" selected>Đang học</option>
                                <option value="Đủ điều kiện">Đủ điều kiện làm khóa luận</option>
                                <option value="Tạm dừng">Tạm dừng học</option>
                                <option value="Đã tốt nghiệp">Đã tốt nghiệp</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-huit">Số Tín Chỉ Tích Lũy</label>
                            <input type="number" name="SoTinChiTichLuy" class="form-control form-control-huit" value="0" min="0">
                            <div class="form-text small text-muted">Điều kiện làm khóa luận: &ge; 115 tín chỉ</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-huit">Điểm Trung Bình Tích Lũy (GPA)</label>
                            <input type="number" step="0.01" name="DiemTichLuy" class="form-control form-control-huit" value="0.00" min="0" max="4.00">
                            <div class="form-text small text-muted">Thang điểm 4.0: &ge; 2.00</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-huit">
                    <button type="submit" class="btn btn-save-huit">
                        <i class="fa-solid fa-check me-1"></i> Lưu &amp; Thêm Sinh Viên
                    </button>
                    <button type="button" class="btn btn-cancel-huit" data-bs-dismiss="modal">Hủy Bỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: Import Danh Sách Sinh Viên Từ Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.sinhvien.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2 text-success"></i>Import Danh Sách Sinh Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0 rounded-3">
                        <i class="fa-solid fa-info-circle me-1"></i> Tải <a href="{{ route('admin.import.template', 'sinhvien') }}" class="fw-bold text-decoration-underline">File mẫu .xlsx</a> để nhập đúng định dạng.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-upload me-1"></i>Import Ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Tự Động Rà Soát Điều Kiện Khóa Luận -->
<div class="modal fade" id="modalRaSoat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-sliders me-2"></i> Thiết Lập Tiêu Chí Rà Soát</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.sinhvien.ra_soat') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Học Kỳ Xét Duyệt <span class="text-danger">*</span></label>
                        <select name="MaHocKy" class="form-select" required>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} - {{ $hk->NamHoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số Tín Chỉ Tối Thiểu <span class="text-danger">*</span></label>
                            <input type="number" name="MinTinChi" class="form-control" value="115" min="0" required>
                            <div class="form-text small">Quy định chuẩn: &ge; 115 Tín chỉ.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ĐTB Tích Lũy Tối Thiểu <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="MinGPA" class="form-control" value="2.0" min="0" max="4.0" required>
                            <div class="form-text small">Thang điểm 4: &ge; 2.00.</div>
                        </div>
                    </div>

                    <div class="alert alert-info rounded-3 mb-0 small">
                        <i class="fa-solid fa-circle-info me-1"></i> Hệ thống sẽ tự động quét toàn bộ sinh viên, so khớp 3 tiêu chí:
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Tín chỉ tích lũy &ge; X</li>
                            <li>Điểm trung bình GPA &ge; Y</li>
                            <li>Không bị hạn chế học tập (Trạng thái đang học)</li>
                        </ul>
                        Sinh viên không đạt sẽ được lưu chi tiết lý do cụ thể vào danh sách.
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-play me-1"></i> Bắt Đầu Rà Soát</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchToTabDudk() {
        const triggerEl = document.querySelector('#tab-btn-dudk');
        if (triggerEl) {
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Sync URL tab on click
        const tabLinks = document.querySelectorAll('#sinhVienUnifiedTabs a[data-bs-toggle="tab"]');
        tabLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function (e) {
                const targetId = e.target.getAttribute('href');
                const tabParam = targetId === '#pane-dudk' ? 'du_dieu_kien' : 'danh_muc';
                const url = new URL(window.location);
                url.searchParams.set('tab', tabParam);
                window.history.replaceState({}, '', url);
            });
        });
    });
</script>
@endpush
@endsection