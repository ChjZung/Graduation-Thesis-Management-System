@extends('layouts.admin')

@section('page_title', 'Quản lý sinh viên & Rà soát đủ điều kiện')

@section('content')
<div class="container-fluid px-0">
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

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-user-graduate text-primary me-2"></i>Quản Lý Sinh Viên &amp; Xét Điều Kiện Khóa Luận</h4>
            <p class="text-muted small mb-0">Quản lý hồ sơ sinh viên, kiểm tra tín chỉ tích lũy và rà soát điều kiện sinh viên tham gia khóa luận tốt nghiệp theo từng học kỳ.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-calendar-days text-primary me-1"></i>Học kỳ đang chọn: <strong class="text-dark">{{ $hocKys->firstWhere('MaHocKy', $selectedHocKy)?->TenHocKy ?? $selectedHocKy }}</strong>
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs nav-tabs-huit mb-4 border-bottom" id="sinhVienUnifiedTabs" role="tablist" style="border-bottom: 2px solid #e2e8f0 !important;">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab !== 'du_dieu_kien' ? 'active fw-bold text-primary' : 'text-secondary' }}" 
               id="tab-btn-danhmuc" data-bs-toggle="tab" href="#pane-danhmuc" role="tab" aria-controls="pane-danhmuc" 
               aria-selected="{{ $activeTab !== 'du_dieu_kien' ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px;">
                <i class="fa-solid fa-address-book me-2"></i>Danh Mục Sinh Viên (Toàn Trường)
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'du_dieu_kien' ? 'active fw-bold text-primary' : 'text-secondary' }}" 
               id="tab-btn-dudk" data-bs-toggle="tab" href="#pane-dudk" role="tab" aria-controls="pane-dudk" 
               aria-selected="{{ $activeTab === 'du_dieu_kien' ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px;">
                <i class="fa-solid fa-user-check me-2"></i>Rà Soát Điều Kiện &amp; Quản Lý SV Theo Học Kỳ
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
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Sinh Viên Hồ Sơ</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                                <i class="fa-solid fa-user-graduate fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['total'] ?? $sinhviens->total() }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đủ ĐK Khóa Luận (&ge; 115 TC)</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                                <i class="fa-solid fa-user-check fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['du_dk'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">/ {{ $statsSv['total'] ?? $sinhviens->total() }} SV ({{ $statsSv['pct_du_dk'] ?? 0 }}%)</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Chưa Đủ Điều Kiện Tín Chỉ</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                                <i class="fa-solid fa-user-clock fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['thieu_dk'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">sinh viên</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tài Khoản Kích Hoạt</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                                <i class="fa-solid fa-id-card-clip fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsSv['active'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">tài khoản</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar Tab 1 -->
            <div class="admin-filter-bar mb-4">
                <form method="GET" action="{{ route('sinhvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                    <input type="hidden" name="tab" value="danh_muc">
                    <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                        <div class="input-group" style="min-width: 260px; flex: 1;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 border-end-0 ps-0" placeholder="Tìm theo tên, MSSV, email, sđt..." value="{{ request('search') }}">
                            <button class="btn btn-primary px-3 fw-semibold" type="submit">Tìm</button>
                        </div>
                        <select name="MaHocKy_filter" class="form-select" style="min-width: 240px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Học Kỳ --</option>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy_filter') == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <select name="MaLop" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Lớp --</option>
                            @foreach($lops as $l)
                                <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                                    {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        <select name="trang_thai_hoc_tap" class="form-select" style="min-width: 190px;" onchange="this.form.submit()">
                            <option value="">-- Trạng thái học tập --</option>
                            <option value="Đang học" {{ request('trang_thai_hoc_tap') == 'Đang học' ? 'selected' : '' }}>Đang học</option>
                            <option value="Tạm dừng" {{ request('trang_thai_hoc_tap') == 'Tạm dừng' ? 'selected' : '' }}>Tạm dừng</option>
                            <option value="Đã tốt nghiệp" {{ request('trang_thai_hoc_tap') == 'Đã tốt nghiệp' ? 'selected' : '' }}>Đã tốt nghiệp</option>
                        </select>
                        <select name="dieu_kien" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                            <option value="">-- Điều kiện làm KL --</option>
                            <option value="du" {{ request('dieu_kien') == 'du' ? 'selected' : '' }}>Đủ điều kiện (&ge; 115 TC)</option>
                            <option value="chua" {{ request('dieu_kien') == 'chua' ? 'selected' : '' }}>Chưa đủ điều kiện</option>
                        </select>
                        @if(request()->anyFilled(['search', 'MaHocKy_filter', 'MaLop', 'trang_thai_hoc_tap', 'dieu_kien']))
                            <a href="{{ route('sinhvien.index', ['tab' => 'danh_muc']) }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                        <button type="button" class="btn btn-primary shadow-xs fw-semibold rounded-pill px-3" onclick="switchToTabDudk()" title="Chuyển sang tab quản lý sinh viên theo học kỳ để thêm hoặc import sinh viên">
                            <i class="fa-solid fa-calendar-check me-1"></i> Quản Lý &amp; Thêm SV Theo Học Kỳ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Card Tab 1 -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Sinh Viên</span>
                    <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $sinhviens->total() }} sinh viên</span>
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
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-user-graduate text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Chưa có dữ liệu sinh viên phù hợp</h6>
                                    <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc hoặc thêm mới sinh viên.</p>
                                    @if(request()->anyFilled(['search', 'MaLop', 'trang_thai_hoc_tap', 'dieu_kien']))
                                        <a href="{{ route('sinhvien.index', ['tab' => 'danh_muc']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                                    @else
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="switchToTabDudk()"><i class="fa-solid fa-calendar-check me-1"></i> Thêm Sinh Viên Theo Học Kỳ</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
                <span class="small text-muted">
                    Hiển thị {{ $sinhviens->firstItem() ?? 0 }}–{{ $sinhviens->lastItem() ?? 0 }} trên tổng số {{ $sinhviens->total() }} sinh viên
                </span>
                @if($sinhviens->hasPages())
                    <div>
                        {{ $sinhviens->appends(['tab' => 'danh_muc'])->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 2: DANH SÁCH SV ĐỦ ĐIỀU KIỆN KHÓA LUẬN (THEO HỌC KỲ)               -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="tab-pane fade {{ $activeTab === 'du_dieu_kien' ? 'show active' : '' }}" id="pane-dudk" role="tabpanel" aria-labelledby="tab-btn-dudk">
        
        <!-- Controls Header Tab 2 -->
        <div class="card border-0 shadow-sm mb-4 rounded-3 bg-white">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-calendar-days text-primary me-1"></i>Học Kỳ Xét Duyệt &amp; Quản Lý</label>
                            <form method="GET" action="{{ route('sinhvien.index') }}" id="formChonHocKy">
                                <input type="hidden" name="tab" value="du_dieu_kien">
                                <select name="MaHocKy" class="form-select form-select-sm rounded-pill fw-bold" style="min-width: 320px;" onchange="this.form.submit()">
                                    @foreach($hocKys as $hk)
                                        <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                            {{ $hk->TenHocKy }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="vr d-none d-lg-block" style="height: 40px;"></div>
                        <div class="small text-muted">
                            <div>Tiêu chuẩn: Tích lũy <strong>&ge; 115 TC</strong> &bull; ĐTB <strong>&ge; 2.00</strong> &bull; Trạng thái học tập bình thường.</div>
                            <div class="text-secondary mt-0.5">Sinh viên đủ điều kiện sẽ được phép tạo nhóm và đăng ký đề tài khóa luận trong học kỳ này.</div>
                        </div>
                    </div>

                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalThemSVVaoKy">
                            <i class="fa-solid fa-user-plus me-1"></i> Thêm sinh viên
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalRaSoat">
                            <i class="fa-solid fa-clipboard-check me-1"></i> Rà Soát Điều Kiện
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalImportSVVaoKy">
                            <i class="fa-solid fa-file-excel me-1"></i> Import Excel Vào Kỳ
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
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng SV Rà Soát ({{ $selectedHocKy }})</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-clipboard-check fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-auto">
                        <span class="fs-3 fw-bold text-dark lh-1">{{ $statsDk['total_evaluated'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">sinh viên</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đủ Điều Kiện Khóa Luận</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-user-check fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-auto">
                        <span class="fs-3 fw-bold text-dark lh-1">{{ $statsDk['total_eligible'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">sinh viên</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Chưa Đủ Điều Kiện</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-user-xmark fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-auto">
                        <span class="fs-3 fw-bold text-dark lh-1">{{ $statsDk['total_ineligible'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">sinh viên</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Trạng Thái Công Bố</span>
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                            <i class="fa-solid fa-bullhorn fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between gap-1 mt-auto">
                        @if($statsDk['is_published'])
                            <span class="fs-5 fw-bold text-success lh-1">Đã Công Bố</span>
                            <span class="badge bg-light text-success border border-success-subtle rounded-pill px-2 py-1 small fw-semibold">Phát hành</span>
                        @else
                            <span class="fs-5 fw-bold text-secondary lh-1">Chưa Công Bố</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small fw-semibold">Nội bộ</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar Tab 2 -->
        <div class="admin-filter-bar mb-4">
            <form method="GET" action="{{ route('sinhvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                <input type="hidden" name="tab" value="du_dieu_kien">
                <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">

                <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                    <div class="input-group" style="min-width: 260px; flex: 1;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search_dk" class="form-control border-start-0 border-end-0 ps-0" placeholder="Nhập MSSV, Họ tên..." value="{{ request('search_dk') }}">
                        <button class="btn btn-primary px-3 fw-semibold" type="submit">Tìm</button>
                    </div>

                    <select name="MaLop_dk" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                        <option value="">-- Tất cả Lớp --</option>
                        @foreach($lops as $l)
                            <option value="{{ $l->MaLop }}" {{ request('MaLop_dk') == $l->MaLop ? 'selected' : '' }}>
                                {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})
                            </option>
                        @endforeach
                    </select>

                    <select name="trang_thai_dk" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                        <option value="">-- Tất cả Trạng Thái --</option>
                        <option value="Đủ điều kiện" {{ request('trang_thai_dk') == 'Đủ điều kiện' ? 'selected' : '' }}>Đủ điều kiện</option>
                        <option value="Chưa đủ điều kiện" {{ request('trang_thai_dk') == 'Chưa đủ điều kiện' ? 'selected' : '' }}>Chưa đủ điều kiện</option>
                    </select>

                    @if(request()->anyFilled(['search_dk', 'MaLop_dk', 'trang_thai_dk']))
                        <a href="{{ route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $selectedHocKy]) }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Tab 2: Danh sách xét duyệt chi tiết kèm lý do -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-list-check text-primary me-2"></i> Kết Quả Rà Soát Điều Kiện Khóa Luận Học Kỳ: <span class="text-primary">{{ $selectedHocKy }}</span>
                </span>
                <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $dsDuDieuKien->total() }} hồ sơ</span>
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
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" data-bs-toggle="modal" data-bs-target="#modalCapNhat{{ $item->MaDSDK }}" title="Cập nhật điều kiện">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                        </button>
                                        <form method="POST" action="{{ route('admin.sinhvien.xoa_dk', $item->MaDSDK) }}" class="d-inline" onsubmit="return confirm('Xác nhận xóa sinh viên {{ $sv->HoTen ?? $item->MaSV }} khỏi danh sách học kỳ này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Xóa khỏi học kỳ này">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>

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
                                <td colspan="10" class="text-center py-5">
                                    <div class="empty-state-box">
                                        @if(request()->anyFilled(['search_dk', 'MaLop_dk', 'trang_thai_dk']))
                                            <i class="fa-solid fa-magnifying-glass text-muted mb-3" style="font-size: 2.5rem;"></i>
                                            <h6 class="fw-bold text-secondary">Không tìm thấy sinh viên phù hợp trong học kỳ {{ $selectedHocKy }}</h6>
                                            <p class="small text-muted mb-3">
                                                @if(request('search_dk'))
                                                    Từ khóa tìm kiếm: <strong>"{{ request('search_dk') }}"</strong>.
                                                @endif
                                                Vui lòng kiểm tra lại điều kiện lọc hoặc MSSV.
                                            </p>
                                            <a href="{{ route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $selectedHocKy]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                                <i class="fa-solid fa-rotate me-1"></i> Xóa Bộ Lọc
                                            </a>
                                        @else
                                            <i class="fa-solid fa-folder-open text-muted mb-3" style="font-size: 2.5rem;"></i>
                                            <h6 class="fw-bold text-secondary">Chưa có dữ liệu rà soát cho học kỳ {{ $selectedHocKy }}</h6>
                                            <p class="small text-muted mb-3">Bấm nút <strong>Rà soát điều kiện</strong> để hệ thống kiểm tra tiêu chí tích lũy tín chỉ &amp; GPA của sinh viên.</p>
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalRaSoat">
                                                <i class="fa-solid fa-clipboard-check me-1"></i> Bắt Đầu Rà Soát
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
                <span class="small text-muted">
                    Hiển thị {{ $dsDuDieuKien->firstItem() ?? 0 }}–{{ $dsDuDieuKien->lastItem() ?? 0 }} trên tổng số {{ $dsDuDieuKien->total() }} hồ sơ
                </span>
                @if($dsDuDieuKien->hasPages())
                    <div>
                        {{ $dsDuDieuKien->appends(['tab' => 'du_dieu_kien', 'MaHocKy' => $selectedHocKy])->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MODALS LIÊN QUAN                                                      -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<!-- MODAL 1: Tự Động Rà Soát Điều Kiện Khóa Luận -->
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
                                    {{ $hk->TenHocKy }}
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

                    <!-- Phạm vi rà soát -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phạm Vi / Đối Tượng Sinh Viên Rà Soát <span class="text-danger">*</span></label>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="PhamVi" id="pvHienTai" value="ky_hien_tai" checked onchange="document.getElementById('divLopPhamVi').style.display = 'none';">
                                <label class="form-check-label fw-semibold" for="pvHienTai">
                                    Chỉ rà soát sinh viên trong danh sách của học kỳ này
                                </label>
                                <div class="text-muted small ps-1">Rà soát lại và cập nhật điều kiện cho các sinh viên đã được nạp/thêm vào kỳ này.</div>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="PhamVi" id="pvLop" value="theo_lop" onchange="document.getElementById('divLopPhamVi').style.display = 'block';">
                                <label class="form-check-label fw-semibold" for="pvLop">
                                    Rà soát theo Lớp cụ thể
                                </label>
                                <div class="text-muted small ps-1">Chỉ rà soát sinh viên của một lớp dự kiến làm khóa luận trong kỳ.</div>
                            </div>
                            <div id="divLopPhamVi" class="ps-4 mb-2" style="display: none;">
                                <select name="MaLop_rasoat" class="form-select form-select-sm">
                                    <option value="">-- Chọn lớp cần rà soát --</option>
                                    @foreach($lops as $l)
                                        <option value="{{ $l->MaLop }}">{{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="PhamVi" id="pvToanBo" value="toan_bo" onchange="document.getElementById('divLopPhamVi').style.display = 'none';">
                                <label class="form-check-label fw-semibold" for="pvToanBo">
                                    Quét mới toàn bộ sinh viên chưa tốt nghiệp
                                </label>
                                <div class="text-muted small ps-1">Quét tất cả sinh viên toàn trường để tìm sinh viên đủ điều kiện cho kỳ này.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info rounded-3 mb-0 small">
                        <i class="fa-solid fa-circle-info me-1"></i> Hệ thống sẽ so khớp 3 tiêu chí:
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Tín chỉ tích lũy &ge; X</li>
                            <li>Điểm trung bình GPA &ge; Y</li>
                            <li>Không bị hạn chế học tập (Trạng thái đang học)</li>
                        </ul>
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

<!-- MODAL 2: Thêm Sinh Viên Vào Học Kỳ (Quản Lý / Thêm Mới / Cấp Tài Khoản) -->
<div class="modal fade" id="modalThemSVVaoKy" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i> Thêm Sinh Viên Vào Học Kỳ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.sinhvien.them_dk') }}" id="formThemSVVaoKy">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 d-flex align-items-center justify-content-between p-2.5 mb-3 bg-light">
                        <div class="small text-muted">
                            <i class="fa-solid fa-file-excel text-success me-1"></i> Có file danh sách nhiều sinh viên?
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalImportSVVaoKy">
                            Upload File Excel
                        </button>
                    </div>

                    <!-- Tabs chọn phương thức: SV có sẵn vs Nhập mới -->
                    <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-pill border" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill fw-semibold py-2" id="btn-tab-cosan" type="button" onclick="switchThemSVMode('co_san')">
                                <i class="fa-solid fa-user-check me-1"></i> 1. Chọn SV Đã Có Sẵn
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-semibold py-2" id="btn-tab-moi" type="button" onclick="switchThemSVMode('moi')">
                                <i class="fa-solid fa-user-plus me-1"></i> 2. Nhập Mới Sinh Viên (Tạo Tài Khoản)
                            </button>
                        </li>
                    </ul>
                    <input type="hidden" name="loai_them" id="loai_them_input" value="co_san">

                    <!-- PHẦN 1: CHỌN SINH VIÊN CÓ SẴN -->
                    <div id="sec_sv_co_san">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chọn Sinh Viên <span class="text-danger">*</span></label>
                            <select name="MaSV" id="select_sv_co_san" class="form-select" required>
                                <option value="">-- Chọn sinh viên trong toàn trường --</option>
                                @foreach($allSinhViens as $asv)
                                    <option value="{{ $asv->MaSV }}">{{ $asv->MaSV }} - {{ $asv->HoTen }} ({{ $asv->MaLop }})</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-success mt-1">
                                <i class="fa-solid fa-shield-halved me-1"></i> Sinh viên đã có trong hệ thống sẽ được <strong>bảo toàn tài khoản &amp; mật khẩu hiện tại</strong>, chỉ gán vào học kỳ này.
                            </div>
                        </div>
                    </div>

                    <!-- PHẦN 2: NHẬP MỚI SINH VIÊN (TỰ TẠO HỒ SƠ & TÀI KHOẢN ĐĂNG NHẬP) -->
                    <div id="sec_sv_moi" style="display: none;">
                        <div class="alert alert-primary bg-primary-subtle border-0 rounded-3 py-2 px-3 small mb-3">
                            <i class="fa-solid fa-info-circle me-1 text-primary"></i> <strong>Quy tắc tự động:</strong> Sinh viên mới sẽ được tạo hồ sơ, đồng thời được <strong>cấp tài khoản đăng nhập Portal</strong> với <code>Tên đăng nhập = MSSV</code>, <code>Mật khẩu mặc định = 123456</code>.
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Mã Số Sinh Viên (MSSV) <span class="text-danger">*</span></label>
                                <input type="text" name="TenDangNhap" id="input_mssv_moi" class="form-control" placeholder="Ví dụ: 2001210123">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold">Họ Và Tên Sinh Viên <span class="text-danger">*</span></label>
                                <input type="text" name="HoTen" id="input_hoten_moi" class="form-control" placeholder="Nguyễn Văn A">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Lớp Sinh Hoạt <span class="text-danger">*</span></label>
                                <select name="MaLop" id="select_lop_moi" class="form-select">
                                    <option value="">-- Chọn Lớp Học --</option>
                                    @foreach($lops as $l)
                                        <option value="{{ $l->MaLop }}">
                                            {{ $l->TenLop }} ({{ $l->nganh->TenNganh ?? 'Khoa CNTT' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Liên Lạc</label>
                                <input type="email" name="Email" class="form-control" placeholder="Để trống hệ thống tự tạo mssv@st.huit.edu.vn">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Số Điện Thoại</label>
                                <input type="text" name="SoDienThoai" class="form-control" placeholder="0912345678">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tín Chỉ Tích Lũy</label>
                                <input type="number" name="SoTinChiTichLuy" class="form-control" value="120" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">ĐTB GPA (Thang 4)</label>
                                <input type="number" step="0.01" name="DiemTichLuy" class="form-control" value="2.50" min="0" max="4.00">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 text-muted opacity-25">

                    <!-- PHẦN CHUNG: HỌC KỲ, TRẠNG THÁI & GHI CHÚ -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Học Kỳ Quản Lý <span class="text-danger">*</span></label>
                            <select name="MaHocKy" class="form-select" required>
                                @foreach($hocKys as $hk)
                                    <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng Thái Điều Kiện <span class="text-danger">*</span></label>
                            <select name="TrangThai" class="form-select" required>
                                <option value="Đủ điều kiện" selected>Đủ điều kiện (Được phép đăng ký khóa luận)</option>
                                <option value="Chưa đủ điều kiện">Chưa đủ điều kiện</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Ghi Chú / Lý Do Thêm</label>
                        <textarea name="GhiChu" class="form-control" rows="2" placeholder="Ví dụ: Đủ điều kiện đợt bổ sung, đặc cách làm khóa luận tốt nghiệp..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-save me-1"></i> Lưu &amp; Thêm Vào Học Kỳ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 5: Import Excel Sinh Viên Vào Học Kỳ -->
<div class="modal fade" id="modalImportSVVaoKy" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i> Import Danh Sách SV Vào Học Kỳ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.sinhvien.import_dk') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Học Kỳ Xét Duyệt &amp; Quản Lý <span class="text-danger">*</span></label>
                        <select name="MaHocKy" class="form-select" required>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small">Hỗ trợ các định dạng <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code>.</div>
                    </div>

                    <div class="alert alert-info rounded-3 mb-0 small">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-info me-1"></i> Hướng dẫn nhập liệu:</div>
                        <div>Tải <a href="{{ route('admin.import.template', 'sinhvien_hocky') }}" class="fw-bold text-decoration-underline text-primary">File mẫu Excel .xlsx</a> chuẩn định dạng.</div>
                        <ul class="mb-0 mt-1 ps-3 text-muted">
                            <li>Cột <code>MSSV</code> là bắt buộc để xác định sinh viên.</li>
                            <li>Cột <code>TrangThai</code>: Nhập <em>Đủ điều kiện</em> hoặc <em>Chưa đủ điều kiện</em> (nếu để trống, hệ thống tự động kiểm tra Tín chỉ &ge; 115 và ĐTB &ge; 2.0).</li>
                            <li>Hệ thống tự động liên kết và cập nhật sinh viên vào danh sách học kỳ đã chọn.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white">
                        <i class="fa-solid fa-upload me-1"></i> Tải Lên &amp; Nạp Danh Sách
                    </button>
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

    function switchThemSVMode(mode) {
        const inputLoai = document.getElementById('loai_them_input');
        const secCoSan = document.getElementById('sec_sv_co_san');
        const secMoi = document.getElementById('sec_sv_moi');
        const btnCoSan = document.getElementById('btn-tab-cosan');
        const btnMoi = document.getElementById('btn-tab-moi');

        const selectCoSan = document.getElementById('select_sv_co_san');
        const inputMssv = document.getElementById('input_mssv_moi');
        const inputHoTen = document.getElementById('input_hoten_moi');
        const selectLop = document.getElementById('select_lop_moi');

        if (mode === 'moi') {
            if (inputLoai) inputLoai.value = 'moi';
            if (secCoSan) secCoSan.style.display = 'none';
            if (secMoi) secMoi.style.display = 'block';
            if (btnCoSan) btnCoSan.classList.remove('active');
            if (btnMoi) btnMoi.classList.add('active');

            if (selectCoSan) {
                selectCoSan.required = false;
                selectCoSan.disabled = true;
            }
            if (inputMssv) {
                inputMssv.required = true;
                inputMssv.disabled = false;
            }
            if (inputHoTen) {
                inputHoTen.required = true;
                inputHoTen.disabled = false;
            }
            if (selectLop) {
                selectLop.required = true;
                selectLop.disabled = false;
            }
        } else {
            if (inputLoai) inputLoai.value = 'co_san';
            if (secCoSan) secCoSan.style.display = 'block';
            if (secMoi) secMoi.style.display = 'none';
            if (btnCoSan) btnCoSan.classList.add('active');
            if (btnMoi) btnMoi.classList.remove('active');

            if (selectCoSan) {
                selectCoSan.required = true;
                selectCoSan.disabled = false;
            }
            if (inputMssv) {
                inputMssv.required = false;
                inputMssv.disabled = true;
            }
            if (inputHoTen) {
                inputHoTen.required = false;
                inputHoTen.disabled = true;
            }
            if (selectLop) {
                selectLop.required = false;
                selectLop.disabled = true;
            }
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