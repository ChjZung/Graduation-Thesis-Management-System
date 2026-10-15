@extends('layouts.admin')

@section('page_title', 'Quản lý giảng viên & Phân bổ theo học kỳ')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: #00305a;"><i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Quản Lý Giảng Viên &amp; Phân Bổ Hướng Dẫn</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-calendar-days text-primary me-1"></i>Học kỳ đang chọn: <strong class="text-dark">{{ $hocKys->firstWhere('MaHocKy', $selectedHocKy)?->TenHocKy ?? ($selectedHocKy ? $selectedHocKy : 'Tất cả học kỳ') }}</strong>
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



    <!-- Tabs Navigation (Đồng bộ tuyệt đối cấu trúc với Quản Lý Sinh Viên) -->
    <ul class="nav nav-tabs nav-tabs-huit mb-4 border-bottom" id="giangVienUnifiedTabs" role="tablist" style="border-bottom: 2px solid #e2e8f0 !important;">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($activeTab !== 'theohocky' && $activeTab !== 'taikhoan') ? 'active fw-bold text-primary' : 'text-secondary' }}" 
               id="tab-btn-danhmuc" data-bs-toggle="tab" href="#pane-danhmuc" role="tab" aria-controls="pane-danhmuc" 
               aria-selected="{{ ($activeTab !== 'theohocky' && $activeTab !== 'taikhoan') ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px;">
                <i class="fa-solid fa-address-book me-2"></i>Danh Mục Giảng Viên (Toàn Trường)
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'theohocky' ? 'active fw-bold text-primary' : 'text-secondary' }}" 
               id="tab-btn-theohocky" data-bs-toggle="tab" href="#pane-theohocky" role="tab" aria-controls="pane-theohocky" 
               aria-selected="{{ $activeTab === 'theohocky' ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px;">
                <i class="fa-solid fa-chalkboard-user me-2"></i>Quản Lý Giảng Viên Theo Học Kỳ
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'taikhoan' ? 'active fw-bold text-primary' : 'text-secondary' }}" 
               id="tab-btn-taikhoan" data-bs-toggle="tab" href="#pane-taikhoan" role="tab" aria-controls="pane-taikhoan" 
               aria-selected="{{ $activeTab === 'taikhoan' ? 'true' : 'false' }}"
               style="font-size: 0.95rem; padding: 10px 22px;">
                <i class="fa-solid fa-user-shield me-2"></i>Tài Khoản Portal &amp; Phân Quyền Chức Vụ
            </a>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="giangVienUnifiedContent">

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 1: DANH MỤC GIẢNG VIÊN (TOÀN TRƯỜNG)                              -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ ($activeTab !== 'theohocky' && $activeTab !== 'taikhoan') ? 'show active' : '' }}" id="pane-danhmuc" role="tabpanel" aria-labelledby="tab-btn-danhmuc">
            
            <!-- 4 KPI Cards Tab 1 (Toàn trường) -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Giảng Viên Hồ Sơ</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                                <i class="fa-solid fa-chalkboard-user fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsGv['total'] ?? $giangviens->total() }}</span>
                            <span class="small text-muted fw-medium">giảng viên</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Học Vị Tiến Sĩ / PGS / GS</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-graduation-cap fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsGv['hoc_vi_cao'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">/ {{ $statsGv['total'] ?? $giangviens->total() }} GV ({{ ($statsGv['total'] ?? 0) > 0 ? round((($statsGv['hoc_vi_cao'] ?? 0) / $statsGv['total']) * 100, 1) : 0 }}%)</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Giữ Chức Vụ Quản Lý</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-award fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsGv['chuc_vu'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">Trưởng khoa / TBM</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tài Khoản Kích Hoạt</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ecfdf5; color: #059669;">
                                <i class="fa-solid fa-id-card-clip fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsGv['active'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">tài khoản</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar Tab 1 -->
            <div class="admin-filter-bar mb-4">
                <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                    <input type="hidden" name="tab" value="danhsach">
                    <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                        <div class="input-group" style="min-width: 260px; flex: 1;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 border-end-0 ps-0" placeholder="Tìm theo tên GV, học vị, email, mã GV..." value="{{ request('search') }}">
                            <button class="btn btn-primary px-3 fw-semibold" type="submit">Tìm</button>
                        </div>

                        <!-- Bộ lọc Học Kỳ -->
                        <select name="MaHocKy_filter" class="form-select" style="min-width: 220px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Học Kỳ --</option>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy_filter') == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Bộ lọc Bộ Môn -->
                        <select name="MaBoMon" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Bộ lọc Chức Vụ -->
                        <select name="ChucVu" class="form-select" style="min-width: 180px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Chức vụ --</option>
                            <option value="co_chuc_vu" {{ request('ChucVu') == 'co_chuc_vu' ? 'selected' : '' }}>Đang giữ chức vụ</option>
                            <option value="TK" {{ request('ChucVu') == 'TK' ? 'selected' : '' }}>Trưởng Khoa (TK)</option>
                            <option value="TBM" {{ request('ChucVu') == 'TBM' ? 'selected' : '' }}>Trưởng Bộ Môn (TBM)</option>
                            <option value="GV" {{ request('ChucVu') == 'GV' ? 'selected' : '' }}>Giảng viên thường</option>
                        </select>

                        <!-- Bộ lọc Định Mức Hướng Dẫn -->
                        <select name="tinh_trang_hd" class="form-select" style="min-width: 190px;" onchange="this.form.submit()">
                            <option value="">-- Tình trạng hướng dẫn --</option>
                            <option value="co_hd" {{ request('tinh_trang_hd') == 'co_hd' ? 'selected' : '' }}>Có nhóm hướng dẫn (&ge; 1)</option>
                            <option value="dat_dinh_muc" {{ request('tinh_trang_hd') == 'dat_dinh_muc' ? 'selected' : '' }}>Đạt định mức / Hết chỉ tiêu</option>
                            <option value="chua_hd" {{ request('tinh_trang_hd') == 'chua_hd' ? 'selected' : '' }}>Chưa nhận hướng dẫn</option>
                        </select>

                        @if(request()->anyFilled(['search', 'MaHocKy_filter', 'MaBoMon', 'ChucVu', 'tinh_trang_hd']))
                            <a href="{{ route('giangvien.index', ['tab' => 'danhsach']) }}" class="btn btn-outline-secondary px-3 flex-shrink-0"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                        <!-- Nút Chuyển Sang Tab Quản Lý Học Kỳ (Đồng bộ tuyệt đối với Sinh Viên) -->
                        <button type="button" class="btn btn-primary shadow-xs fw-semibold rounded-pill px-3" onclick="switchToTabTheoHocKy()" title="Chuyển sang tab quản lý giảng viên theo học kỳ để thêm hoặc import giảng viên">
                            <i class="fa-solid fa-calendar-check me-1"></i> Quản Lý Giảng Viên Theo Học Kỳ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bảng Danh Sách Giảng Viên Toàn Trường -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Hồ Sơ Giảng Viên (Toàn Trường)</span>
                    <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $giangviens->total() }} giảng viên</span>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3" width="13%">MÃ GV</th>
                                <th class="py-3" width="28%">HỌ TÊN, HỌC VỊ &amp; LIÊN HỆ</th>
                                <th class="py-3" width="23%">BỘ MÔN CHUYÊN MÔN</th>
                                <th class="py-3 text-center" width="16%">ĐỊNH MỨC HƯỚNG DẪN</th>
                                <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                                <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($giangviens as $gv)
                            @php
                                $ct = $gv->chi_tieu_stats ?? [
                                    'tong'       => 5,
                                    'da_dung'    => 0,
                                    'con_lai'    => 5,
                                    'tinh_trang' => 'con_chi_tieu',
                                    'is_full'    => false,
                                    'label'      => '0/5',
                                ];
                            @endphp
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="badge-code">{{ $gv->MaGV }}</span>
                                </td>
                                <td class="py-3">
                                    <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6">
                                        {{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}
                                    </a>
                                    <div class="text-muted small">
                                        {{ $gv->Email ?? 'chua_co@huit.edu.vn' }}
                                        @if($gv->SoDienThoai)
                                            &bull; {{ $gv->SoDienThoai }}
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $gv->boMon->TenBoMon ?? 'Chưa gán bộ môn' }}</div>
                                    <div class="small text-muted">{{ $gv->boMon->khoa->TenKhoa ?? 'Khoa CNTT' }}</div>
                                </td>
                                <td class="text-center py-3">
                                    <div class="fw-bold {{ $ct['tinh_trang'] === 'vuot_chi_tieu' ? 'text-danger' : ($ct['is_full'] ? 'text-warning-emphasis' : ($ct['da_dung'] > 0 ? 'text-success' : 'text-muted')) }}">
                                        {{ $ct['label'] }} nhóm
                                    </div>
                                    <div class="small text-muted mt-1">
                                        @if($ct['tinh_trang'] === 'vuot_chi_tieu')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5">Vượt chỉ tiêu</span>
                                        @elseif($ct['tinh_trang'] === 'het_chi_tieu')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">Đã đủ chỉ tiêu</span>
                                        @elseif($ct['da_dung'] > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">Còn {{ $ct['con_lai'] }} chỗ</span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5">Chưa có nhóm</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center py-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        {{ $gv->TrangThai ?? 'Đang công tác' }}
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem hồ sơ chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('giangvien.edit', $gv->MaGV) }}" class="btn btn-sm btn-light text-warning btn-action-circle border" title="Chỉnh sửa thông tin">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('giangvien.destroy', $gv->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên {{ $gv->HoTen }}? (Chỉ xóa được khi chưa có đề tài, hướng dẫn hoặc hội đồng liên quan)');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Xóa hồ sơ giảng viên">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state-box">
                                        <i class="fa-solid fa-chalkboard-user text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có dữ liệu giảng viên nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc hoặc thêm mới giảng viên.</p>
                                        @if(request()->anyFilled(['search', 'MaHocKy_filter', 'MaBoMon', 'ChucVu', 'tinh_trang_hd']))
                                            <a href="{{ route('giangvien.index', ['tab' => 'danhsach']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                                        @else
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="switchToTabTheoHocKy()">
                                                <i class="fa-solid fa-calendar-check me-1"></i> Quản Lý &amp; Thêm Theo Học Kỳ
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
                        Hiển thị {{ $giangviens->firstItem() ?? 0 }}–{{ $giangviens->lastItem() ?? 0 }} trên tổng số {{ $giangviens->total() }} giảng viên
                    </span>
                    @if($giangviens->hasPages())
                        <div>
                            {{ $giangviens->appends(['tab' => 'danhsach'])->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 2: QUẢN LÝ GIẢNG VIÊN THEO HỌC KỲ (GIAO DIỆN CHUẨN ĐỒNG BỘ 100%) -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ $activeTab === 'theohocky' ? 'show active' : '' }}" id="pane-theohocky" role="tabpanel" aria-labelledby="tab-btn-theohocky">
            
            <!-- Controls Header Card (Theo đúng ảnh người dùng cung cấp) -->
            <div class="card border-0 shadow-sm mb-4 rounded-3 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div>
                                <label class="form-label small fw-bold text-muted mb-1">Học Kỳ Xét Duyệt &amp; Quản Lý</label>
                                <form method="GET" action="{{ route('giangvien.index') }}" id="formChonHocKyGV">
                                    <input type="hidden" name="tab" value="theohocky">
                                    <select name="MaHocKy" class="form-select form-select-sm rounded-pill fw-bold" style="min-width: 320px;" onchange="this.form.submit()">
                                        @foreach($hocKys as $hk)
                                            <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                                {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '★ (Đang diễn ra)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                            <div class="vr d-none d-lg-block" style="height: 40px;"></div>
                            <div class="small text-muted">
                                <div>Tiêu chuẩn: Tối đa <strong>5 nhóm / GV</strong> &bull; Hướng dẫn tối đa <strong>15 sinh viên</strong> &bull; Yêu cầu có đề tài duyệt.</div>
                                <div class="text-secondary mt-0.5">Giảng viên tham gia học kỳ sẽ phụ trách hướng dẫn các nhóm sinh viên làm khóa luận và tham gia chấm bảo vệ.</div>
                            </div>
                        </div>

                        <!-- Các nút thao tác dạng pill theo ảnh mẫu người dùng -->
                        <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap">
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalThemGVVaoKy">
                                <i class="fa-solid fa-user-plus me-1"></i> Thêm giảng viên
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalPhanBoDinhMuc">
                                <i class="fa-solid fa-sliders me-1"></i> Phân Bổ Định Mức
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalImportGVVaoKy">
                                <i class="fa-solid fa-file-excel me-1"></i> Import Excel Vào Kỳ
                            </button>
                            <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs" title="Tải file mẫu Excel">
                                <i class="fa-solid fa-download me-1"></i> File Mẫu
                            </a>
                            <a href="{{ route('admin.giangvien.export', ['MaHocKy' => $selectedHocKy, 'tab' => 'theohocky']) }}" class="btn btn-success btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs">
                                <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 KPI Cards Tab 2: Tập trung trọng tâm Đề tài & Hướng dẫn theo kỳ -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng GV Tham Gia Kỳ ({{ $selectedHocKy }})</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9; color: #003366;">
                                <i class="fa-solid fa-chalkboard-user fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsHk['total_gv'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">giảng viên</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đề Tài Đã Công Bố ({{ $selectedHocKy }})</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-book-bookmark fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsHk['de_tai_cong_bo'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">đề tài khả dụng</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nhóm Đã Gán Đề Tài ({{ $selectedHocKy }})</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ecfdf5; color: #059669;">
                                <i class="fa-solid fa-users-viewfinder fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsHk['nhom_da_gan'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">nhóm sinh viên</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">GV Đang Hướng Dẫn</span>
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-person-chalkboard fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mt-auto">
                            <span class="fs-3 fw-bold text-dark lh-1">{{ $statsHk['huong_dan'] ?? 0 }}</span>
                            <span class="small text-muted fw-medium">/ {{ $statsHk['total_gv'] ?? 0 }} GV ({{ $statsHk['pct_huong_dan'] ?? 0 }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar Tab 2 -->
            <div class="admin-filter-bar mb-4">
                <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                    <input type="hidden" name="tab" value="theohocky">
                    <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">

                    <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                        <div class="input-group" style="min-width: 260px; flex: 1;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search_hk" class="form-control border-start-0 border-end-0 ps-0" placeholder="Nhập Mã GV, Họ tên, email..." value="{{ request('search_hk') }}">
                            <button class="btn btn-primary px-3 fw-semibold" type="submit">Tìm</button>
                        </div>

                        <select name="MaBoMon_hk" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon_hk') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                                </option>
                            @endforeach
                        </select>

                        <select name="tinh_trang_hd_hk" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Tình Trạng HD --</option>
                            <option value="co_hd" {{ request('tinh_trang_hd_hk') == 'co_hd' ? 'selected' : '' }}>Có nhóm hướng dẫn (&ge; 1)</option>
                            <option value="dat_dinh_muc" {{ request('tinh_trang_hd_hk') == 'dat_dinh_muc' ? 'selected' : '' }}>Đạt định mức / Hết chỉ tiêu</option>
                            <option value="chua_hd" {{ request('tinh_trang_hd_hk') == 'chua_hd' ? 'selected' : '' }}>Chưa nhận hướng dẫn</option>
                        </select>

                        <select name="pham_vi" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                            <option value="tham_gia" {{ request('pham_vi') != 'tat_ca' ? 'selected' : '' }}>GV tham gia kỳ này</option>
                            <option value="tat_ca" {{ request('pham_vi') == 'tat_ca' ? 'selected' : '' }}>Tất cả giảng viên trong trường</option>
                        </select>

                        @if(request()->anyFilled(['search_hk', 'MaBoMon_hk', 'tinh_trang_hd_hk', 'pham_vi']))
                            <a href="{{ route('giangvien.index', ['tab' => 'theohocky', 'MaHocKy' => $selectedHocKy]) }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table Tab 2: Danh sách phân bổ giảng viên theo học kỳ -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        <i class="fa-solid fa-list-check text-primary me-2"></i> Kết Quả Phân Bổ &amp; Quản Lý Giảng Viên Học Kỳ: <span class="text-primary">{{ $selectedHocKy }}</span>
                    </span>
                    <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $dsGiangVienHk->total() }} giảng viên</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center ps-3 py-3" style="width: 50px;">STT</th>
                                <th class="py-3" style="width: 120px;">MÃ GV</th>
                                <th class="py-3" style="width: 250px;">HỌ VÀ TÊN &amp; HỌC VỊ</th>
                                <th class="py-3" style="width: 200px;">BỘ MÔN CHUYÊN MÔN</th>
                                <th class="text-center py-3" style="width: 130px;">ĐỀ TÀI TRONG KỲ</th>
                                <th class="text-center py-3" style="width: 180px;">ĐỊNH MỨC HƯỚNG DẪN</th>
                                <th class="text-center py-3" style="width: 170px;">TÌNH TRẠNG HƯỚNG DẪN</th>
                                <th class="text-center pe-3 py-3" style="width: 120px;">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($dsGiangVienHk as $idx => $gv)
                            @php
                                $ct = $gv->chi_tieu_stats ?? [
                                    'tong'       => 5,
                                    'da_dung'    => 0,
                                    'con_lai'    => 5,
                                    'tinh_trang' => 'con_chi_tieu',
                                    'is_full'    => false,
                                    'label'      => '0/5',
                                ];
                                $pct = ($ct['tong'] > 0) ? min(100, round(($ct['da_dung'] / $ct['tong']) * 100)) : 0;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold ps-3">{{ $dsGiangVienHk->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge-code">{{ $gv->MaGV }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6">
                                        {{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}
                                    </a>
                                    <div class="text-muted small">
                                        {{ $gv->Email ?? 'chua_co@huit.edu.vn' }}
                                        @if($gv->SoDienThoai)
                                            &bull; {{ $gv->SoDienThoai }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $gv->boMon->TenBoMon ?? 'Chưa gán bộ môn' }}</div>
                                    <div class="small text-muted">{{ $gv->boMon->khoa->TenKhoa ?? 'Khoa CNTT' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-primary border px-2.5 py-1.5 fw-bold fs-6">
                                        {{ $gv->de_tais_hk_count ?? 0 }} đề tài
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold {{ $ct['tinh_trang'] === 'vuot_chi_tieu' ? 'text-danger' : ($ct['is_full'] ? 'text-warning-emphasis' : ($ct['da_dung'] > 0 ? 'text-success' : 'text-muted')) }}">
                                        {{ $ct['label'] }} nhóm tối đa
                                    </div>
                                    <div class="progress mt-1.5" style="height: 6px; background-color: #f1f5f9;">
                                        <div class="progress-bar {{ $ct['is_full'] ? 'bg-warning' : ($ct['da_dung'] > 0 ? 'bg-success' : 'bg-secondary') }}" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                        Đã nhận: <strong>{{ $ct['da_dung'] }}</strong> &bull; Còn lại: <strong>{{ $ct['con_lai'] }}</strong>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($ct['tinh_trang'] === 'vuot_chi_tieu')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                            Vượt chỉ tiêu
                                        </span>
                                    @elseif($ct['is_full'])
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                            Đã đủ chỉ tiêu
                                        </span>
                                    @elseif($ct['da_dung'] > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                            Đang HD (còn {{ $ct['con_lai'] }})
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-semibold">
                                            Chưa nhận nhóm
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" data-bs-toggle="modal" data-bs-target="#modalCapNhatChiTieu{{ $gv->MaGV }}" title="Thiết lập chỉ tiêu hướng dẫn">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Định mức
                                        </button>
                                        <a href="{{ route('giangvien.show', $gv->MaGV) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết hồ sơ">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </div>

                                    <!-- Modal Cập Nhật Chỉ Tiêu Hướng Dẫn Từng GV -->
                                    <div class="modal fade text-start" id="modalCapNhatChiTieu{{ $gv->MaGV }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header bg-light rounded-top-4">
                                                    <h5 class="modal-title fw-bold text-primary">
                                                        <i class="fa-solid fa-sliders me-1"></i> Định Mức Hướng Dẫn: {{ $gv->HoTen }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.chi_tieu_huong_dan.update') }}">
                                                    @csrf
                                                    <input type="hidden" name="MaGV" value="{{ $gv->MaGV }}">
                                                    <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                                                    <div class="modal-body p-4">
                                                        <div class="p-3 bg-light rounded-3 mb-3">
                                                            <div class="small text-muted">Giảng viên: <strong>{{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}</strong> ({{ $gv->MaGV }})</div>
                                                            <div class="small text-muted mt-1">Học kỳ áp dụng: <strong class="text-primary">{{ $selectedHocKy }}</strong></div>
                                                            <div class="small text-muted mt-1">Đã hướng dẫn: <strong class="text-success">{{ $ct['da_dung'] }} nhóm</strong></div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Chỉ Tiêu Hướng Dẫn Tối Đa (Số Nhóm) <span class="text-danger">*</span></label>
                                                            <input type="number" name="SoNhomToiDa" class="form-control form-control-lg rounded-3 fw-bold" value="{{ $ct['tong'] }}" min="0" max="20" required>
                                                            <div class="form-text small text-muted">Quy chế HUIT chuẩn: tối đa 5 nhóm sinh viên / học kỳ. Nhập 0 nếu giảng viên không nhận hướng dẫn kỳ này.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light rounded-bottom-4">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Định Mức</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state-box">
                                        @if(request()->anyFilled(['search_hk', 'MaBoMon_hk', 'tinh_trang_hd_hk']))
                                            <i class="fa-solid fa-magnifying-glass text-muted mb-3" style="font-size: 2.5rem;"></i>
                                            <h6 class="fw-bold text-secondary">Không tìm thấy giảng viên phù hợp trong học kỳ {{ $selectedHocKy }}</h6>
                                            <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc hoặc từ khóa tìm kiếm.</p>
                                            <a href="{{ route('giangvien.index', ['tab' => 'theohocky', 'MaHocKy' => $selectedHocKy]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                                <i class="fa-solid fa-rotate me-1"></i> Xóa Bộ Lọc
                                            </a>
                                        @else
                                            <i class="fa-solid fa-chalkboard-user text-muted mb-3" style="font-size: 2.5rem;"></i>
                                            <h6 class="fw-bold text-secondary">Chưa có dữ liệu phân bổ cho học kỳ {{ $selectedHocKy }}</h6>
                                            <p class="small text-muted mb-3">Bấm nút <strong>Thêm giảng viên</strong> hoặc <strong>Import Excel Vào Kỳ</strong> để phân bổ giảng viên vào học kỳ này.</p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalThemGVVaoKy">
                                                    <i class="fa-solid fa-user-plus me-1"></i> Thêm Giảng Viên Vào Kỳ
                                                </button>
                                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-4 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalImportGVVaoKy">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Import Excel
                                                </button>
                                            </div>
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
                        Hiển thị {{ $dsGiangVienHk->firstItem() ?? 0 }}–{{ $dsGiangVienHk->lastItem() ?? 0 }} trên tổng số {{ $dsGiangVienHk->total() }} giảng viên
                    </span>
                    @if($dsGiangVienHk->hasPages())
                        <div>
                            {{ $dsGiangVienHk->appends(['tab' => 'theohocky', 'MaHocKy' => $selectedHocKy])->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- TAB 3: TÀI KHOẢN PORTAL & PHÂN QUYỀN CHỨC VỤ                          -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ $activeTab === 'taikhoan' ? 'show active' : '' }}" id="pane-taikhoan" role="tabpanel" aria-labelledby="tab-btn-taikhoan">
            
            <!-- Filter Bar Tab 3 -->
            <div class="admin-filter-bar mb-4">
                <form method="GET" action="{{ route('giangvien.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
                    <input type="hidden" name="tab" value="taikhoan">
                    <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                        <div class="input-group" style="min-width: 250px; flex: 1;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 border-end-0 ps-0" placeholder="Tìm theo tên GV, tên đăng nhập, mã GV..." value="{{ request('search') }}">
                            <button class="btn btn-primary px-3 fw-semibold" type="submit">Tìm</button>
                        </div>
                        <select name="MaBoMon" class="form-select" style="min-width: 190px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        <select name="ChucVu" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả Chức vụ / Vai trò --</option>
                            <option value="co_chuc_vu" {{ request('ChucVu') == 'co_chuc_vu' ? 'selected' : '' }}>Đang giữ chức vụ (TK / TBM)</option>
                            <option value="TK" {{ request('ChucVu') == 'TK' ? 'selected' : '' }}>Chỉ Trưởng Khoa (TK)</option>
                            <option value="TBM" {{ request('ChucVu') == 'TBM' ? 'selected' : '' }}>Chỉ Trưởng Bộ Môn (TBM)</option>
                            <option value="GV" {{ request('ChucVu') == 'GV' ? 'selected' : '' }}>Giảng viên thường</option>
                        </select>
                        @if(request()->anyFilled(['search', 'MaBoMon', 'ChucVu']))
                            <a href="{{ route('giangvien.index', ['tab' => 'taikhoan']) }}" class="btn btn-outline-secondary px-3 flex-shrink-0"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                        <button type="button" class="btn btn-warning text-dark shadow-xs fw-semibold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importTBM_TK_Modal">
                            <i class="fa-solid fa-user-shield me-1"></i> Upload DS TK / TBM
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bảng Tài Khoản & Phân Quyền Chức Vụ -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-user-shield me-2 text-primary"></i> Quản Lý Tài Khoản Portal Giảng Viên &amp; Phân Quyền Chức Vụ</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border fw-normal">Tổng: {{ $giangviens->total() }} tài khoản</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3" width="22%">GIẢNG VIÊN / MÃ GV</th>
                                <th class="py-3" width="22%">TÀI KHOẢN GV GỐC (PORTAL GV)</th>
                                <th class="py-3" width="28%">CHỨC VỤ &amp; TÀI KHOẢN CHỨC NĂNG</th>
                                <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                                <th class="pe-4 py-3 text-center" width="16%">THAO TÁC TÀI KHOẢN</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($giangviens as $gv)
                            @php
                                $tk = $gv->taiKhoan;
                                $isActive = $tk ? (bool)$tk->TrangThai : true;

                                $isTK = in_array($gv->HoTen, $truongKhoaNames ?? []) 
                                    || ($tk && $tk->MaVaiTro === 'VT05') 
                                    || (isset($tkChucVus) && $tkChucVus->contains(fn($t) => str_starts_with($t->TenDangNhap, 'TK_') && str_ends_with($t->TenDangNhap, '_' . $gv->MaGV)));
                                
                                $isTBM = in_array($gv->HoTen, $truongBoMonNames ?? []) 
                                    || ($tk && $tk->MaVaiTro === 'VT04') 
                                    || (isset($tkChucVus) && $tkChucVus->contains(fn($t) => str_starts_with($t->TenDangNhap, 'TBM_') && str_ends_with($t->TenDangNhap, '_' . $gv->MaGV)));
                                
                                $hasChucVu = $isTK || $isTBM;
                                $tkChucVuObj = isset($tkChucVus) ? $tkChucVus->first(fn($t) => str_ends_with($t->TenDangNhap, '_' . $gv->MaGV)) : null;
                            @endphp
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark fs-6">
                                        {{ $gv->HocVi ? $gv->HocVi . '. ' : '' }}{{ $gv->HoTen }}
                                    </div>
                                    <div class="text-muted small mt-0.5">
                                        <span class="badge-code">{{ $gv->MaGV }}</span> &bull; {{ $gv->boMon->TenBoMon ?? 'Chưa gán bộ môn' }}
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div>
                                        <span class="badge bg-light text-primary border px-2.5 py-1 fs-6 font-monospace">
                                            <i class="fa-solid fa-id-badge me-1"></i>{{ $tk->TenDangNhap ?? $gv->MaGV }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên (VT02)
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if($isTK)
                                        <div>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold fs-7">
                                                <i class="fa-solid fa-crown me-1"></i>Trưởng Khoa (VT05)
                                            </span>
                                        </div>
                                        <div class="small text-dark font-monospace mt-1">
                                            <i class="fa-solid fa-shield-halved me-1 text-danger"></i>TK chức vụ: <strong>{{ $tkChucVuObj->TenDangNhap ?? ('TK_' . ($gv->boMon->khoa->MaKhoa ?? 'CNTT') . '_' . $gv->MaGV) }}</strong>
                                        </div>
                                    @elseif($isTBM)
                                        <div>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold fs-7">
                                                <i class="fa-solid fa-medal me-1"></i>Trưởng Bộ Môn (VT04)
                                            </span>
                                        </div>
                                        <div class="small text-dark font-monospace mt-1">
                                            <i class="fa-solid fa-shield me-1 text-warning"></i>TK chức vụ: <strong>{{ $tkChucVuObj->TenDangNhap ?? ('TBM_' . ($gv->MaBoMon ?? 'BM') . '_' . $gv->MaGV) }}</strong>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                                            <i class="fa-solid fa-user me-1 text-muted"></i>Giảng viên thường
                                        </span>
                                        <div class="small text-muted mt-0.5">Chưa bổ nhiệm chức vụ</div>
                                    @endif
                                </td>
                                <td class="text-center py-3">
                                    @if($isActive)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="fa-solid fa-circle me-1" style="font-size: 0.45rem; color: #16a34a;"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="fa-solid fa-lock me-1"></i> Đã khóa
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 py-3 text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm {{ $hasChucVu ? 'btn-outline-warning' : 'btn-outline-primary' }} rounded-pill px-2.5" 
                                                onclick="openBoNhiemModal('{{ $gv->MaGV }}', '{{ $gv->HoTen }}', '{{ $gv->MaBoMon }}', '{{ $gv->boMon->MaKhoa ?? '' }}', '{{ $isTK ? 'VT05' : ($isTBM ? 'VT04' : '') }}')"
                                                title="Bổ nhiệm Trưởng khoa / Trưởng bộ môn & cấp tài khoản Portal">
                                            <i class="fa-solid fa-award me-1"></i> {{ $hasChucVu ? 'Đổi chức vụ' : 'Bổ nhiệm' }}
                                        </button>
                                        
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-sm btn-light border btn-action-circle" data-bs-toggle="dropdown" aria-expanded="false" title="Thao tác khác">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 0.85rem;">
                                                @if($hasChucVu)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.giangvien.huy_bo_nhiem', $gv->MaGV) }}" onsubmit="return confirm('Xác nhận hủy chức vụ và thu hồi tài khoản chức vụ của giảng viên {{ $gv->HoTen }}?');">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-warning-emphasis py-1.5 d-flex align-items-center gap-2">
                                                            <i class="fa-solid fa-user-minus" style="width: 16px;"></i> Hủy chức vụ đã cấp
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                @endif
                                                @if($tk)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.giangvien.xoa_tai_khoan', $gv->MaGV) }}" onsubmit="return confirm('CẢNH BÁO: Thao tác này sẽ xóa toàn bộ tài khoản Portal của giảng viên {{ $gv->HoTen }} (hồ sơ giảng viên vẫn được giữ nguyên). Tiếp tục?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger py-1.5 d-flex align-items-center gap-2">
                                                            <i class="fa-solid fa-user-slash" style="width: 16px;"></i> Thu hồi tài khoản Portal
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state-box">
                                        <i class="fa-solid fa-user-shield text-muted mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-bold text-secondary">Chưa có dữ liệu tài khoản nào</h6>
                                        <p class="small text-muted mb-3">Vui lòng điều chỉnh bộ lọc tìm kiếm hoặc chức vụ.</p>
                                        <a href="{{ route('giangvien.index', ['tab' => 'taikhoan']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-rotate me-1"></i> Xóa bộ lọc</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <span class="small text-muted">
                        Hiển thị {{ $giangviens->firstItem() ?? 0 }}–{{ $giangviens->lastItem() ?? 0 }} trên tổng số {{ $giangviens->total() }} tài khoản
                    </span>
                    @if($giangviens->hasPages())
                        <div>
                            {{ $giangviens->appends(['tab' => 'taikhoan'])->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Info Card: HUIT Lecturer Rules -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-rocket text-primary"></i> QUY ĐỊNH HƯỚNG DẪN ĐỀ TÀI &amp; PHÂN BỔ TÀI KHOẢN GIẢNG VIÊN (HUIT):
        </h6>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1">1. Định mức hướng dẫn tối đa</div>
                    <div class="small text-muted">Mỗi giảng viên nhận tối đa <strong>&le; 5 nhóm sinh viên</strong> khóa luận trong một học kỳ để đảm bảo chất lượng. Hệ thống tự động khóa đăng ký khi đủ định mức.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1">2. Cấp tài khoản &amp; phân quyền Portal</div>
                    <div class="small text-muted">Tài khoản Giảng viên được tạo theo Mã GV (đúng 10 ký tự), mật khẩu mặc định: <code>123456</code>. Khi bổ nhiệm Trưởng khoa / Trưởng bộ môn, hệ thống tự động cấp tài khoản chức vụ riêng biệt (<code>TK_...</code> hoặc <code>TBM_...</code>) tách biệt hoàn toàn với tài khoản GV gốc.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-3 border h-100">
                    <div class="fw-bold text-dark mb-1">3. Tham gia Hội đồng chấm bảo vệ</div>
                    <div class="small text-muted">Giảng viên có học hàm/học vị từ Thạc sĩ trở lên đủ điều kiện tham gia <strong>5 vị trí HĐ</strong>. Tránh xung đột lợi ích (GVHD không chấm phản biện nhóm mình).</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- MODALS LIÊN QUAN                                                      -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<!-- MODAL 1: Thêm Giảng Viên Vào Học Kỳ (Đồng bộ tuyệt đối với Thêm Sinh Viên Vào Kỳ) -->
<div class="modal fade" id="modalThemGVVaoKy" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i> Thêm Giảng Viên Vào Học Kỳ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border rounded-3 d-flex align-items-center justify-content-between p-2.5 mb-3 bg-light">
                    <div class="small text-muted">
                        <i class="fa-solid fa-file-excel text-success me-1"></i> Có file danh sách nhiều giảng viên?
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalImportGVVaoKy">
                        Upload File Excel
                    </button>
                </div>

                <!-- Tabs chọn phương thức: GV có sẵn vs Nhập mới -->
                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-pill border" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2" id="btn-tab-gv-cosan" type="button" onclick="switchThemGVMode('co_san')">
                            <i class="fa-solid fa-user-check me-1"></i> 1. Chọn GV Đã Có Sẵn
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2" id="btn-tab-gv-moi" type="button" onclick="switchThemGVMode('moi')">
                            <i class="fa-solid fa-user-plus me-1"></i> 2. Nhập Mới Giảng Viên (Tạo Tài Khoản)
                        </button>
                    </li>
                </ul>

                <!-- PHẦN 1: CHỌN GIẢNG VIÊN CÓ SẴN (GÁN CHỈ TIÊU VÀO KỲ) -->
                <form method="POST" action="{{ route('admin.chi_tieu_huong_dan.update') }}" id="form_gv_co_san">
                    @csrf
                    <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn Giảng Viên Trong Trường <span class="text-danger">*</span></label>
                        <select name="MaGV" class="form-select form-select-lg rounded-3" required>
                            <option value="">-- Chọn giảng viên để phân bổ vào học kỳ --</option>
                            @foreach($allGiangViens as $agv)
                                <option value="{{ $agv->MaGV }}">{{ $agv->MaGV }} - {{ $agv->HocVi ? $agv->HocVi . '. ' : '' }}{{ $agv->HoTen }} ({{ $agv->boMon->TenBoMon ?? 'Bộ môn' }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-success mt-1">
                            <i class="fa-solid fa-shield-halved me-1"></i> Giảng viên đã có sẵn sẽ được <strong>bảo toàn tài khoản &amp; mật khẩu hiện tại</strong>, chỉ gán định mức vào học kỳ <strong>{{ $selectedHocKy }}</strong>.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Học Kỳ Quản Lý &amp; Gán Chỉ Tiêu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" value="{{ $selectedHocKy }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Chỉ Tiêu Hướng Dẫn Tối Đa (Số Nhóm) <span class="text-danger">*</span></label>
                            <input type="number" name="SoNhomToiDa" class="form-control" value="5" min="0" max="20" required>
                            <div class="form-text small">Mặc định: 5 nhóm theo quy chế HUIT.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-save me-1"></i> Phân Bổ Vào Học Kỳ</button>
                    </div>
                </form>

                <!-- PHẦN 2: NHẬP MỚI GIẢNG VIÊN (TỰ TẠO HỒ SƠ & TÀI KHOẢN PORTAL) -->
                <form method="POST" action="{{ route('giangvien.store') }}" id="form_gv_moi" style="display: none;">
                    @csrf
                    <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                    <div class="alert alert-primary bg-primary-subtle border-0 rounded-3 py-2 px-3 small mb-3">
                        <i class="fa-solid fa-info-circle me-1 text-primary"></i> <strong>Quy tắc tự động:</strong> Giảng viên mới sẽ được tạo hồ sơ, đồng thời được <strong>cấp tài khoản đăng nhập Portal</strong> với <code>Tên đăng nhập = Mã GV</code>, <code>Mật khẩu mặc định = 123456</code>.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Mã Giảng Viên (MaGV) <span class="text-danger">*</span></label>
                            <input type="text" name="TenDangNhap" class="form-control" placeholder="Ví dụ: GV00000001" minlength="10" maxlength="10" pattern="[A-Za-z0-9_]{10}">
                            <div class="form-text small text-muted">Đúng 10 ký tự (chữ & số, ví dụ: <code>GV00000001</code>).</div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold">Họ Và Tên Giảng Viên <span class="text-danger">*</span></label>
                            <input type="text" name="HoTen" class="form-control" placeholder="TS. Nguyễn Văn A">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Học Vị / Học Hàm <span class="text-danger">*</span></label>
                            <select name="HocVi" class="form-select">
                                <option value="Thạc sĩ">Thạc sĩ</option>
                                <option value="Tiến sĩ" selected>Tiến sĩ</option>
                                <option value="Phó Giáo sư">Phó Giáo sư</option>
                                <option value="Giáo sư">Giáo sư</option>
                                <option value="Kỹ sư">Kỹ sư</option>
                                <option value="Cử nhân">Cử nhân</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bộ Môn Trực Thuộc <span class="text-danger">*</span></label>
                            <select name="MaBoMon" class="form-select">
                                <option value="">-- Chọn Bộ môn --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $bm->TenBoMon }} ({{ $bm->khoa->TenKhoa ?? 'Khoa CNTT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Liên Lạc <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control" placeholder="email@huit.edu.vn">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số Điện Thoại (10 số)</label>
                            <input type="text" name="SoDienThoai" class="form-control" placeholder="0912345678" minlength="10" maxlength="10" pattern="0[0-9]{9}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-save me-1"></i> Lưu &amp; Cấp Tài Khoản</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: Phân Bổ Định Mức Hàng Loạt Cho Học Kỳ -->
<div class="modal fade" id="modalPhanBoDinhMuc" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-sliders me-2"></i> Thiết Lập Định Mức Hàng Loạt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.chi_tieu_huong_dan.bulk_update') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                        <select name="MaHocKy" class="form-select fw-bold" required>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Bộ Môn Áp Dụng</label>
                        <select name="MaBoMon" class="form-select">
                            <option value="">-- Tất cả Bộ môn trong Khoa --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}">{{ $bm->TenBoMon }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Định Mức Hướng Dẫn Tối Đa (Số Nhóm / GV) <span class="text-danger">*</span></label>
                        <input type="number" name="SoNhomToiDa" class="form-control form-control-lg fw-bold" value="5" min="0" max="20" required>
                        <div class="form-text small text-muted">Quy chế chuẩn HUIT: 5 nhóm sinh viên / giảng viên.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phạm Vi Áp Dụng <span class="text-danger">*</span></label>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="apply_scope" id="scope_all" value="all" checked>
                                <label class="form-check-label fw-semibold" for="scope_all">
                                    Áp dụng cho tất cả giảng viên (Ghi đè cấu hình cũ)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="apply_scope" id="scope_default" value="default_only">
                                <label class="form-check-label fw-semibold" for="scope_default">
                                    Chỉ áp dụng cho giảng viên chưa được thiết lập riêng
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Áp Dụng Định Mức</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: Import Excel Giảng Viên Vào Học Kỳ -->
<div class="modal fade" id="modalImportGVVaoKy" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i> Import Giảng Viên Vào Học Kỳ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Học Kỳ Quản Lý &amp; Gán Chỉ Tiêu <span class="text-danger">*</span></label>
                        <select name="MaHocKy" class="form-select fw-bold" required>
                            @foreach($hocKys as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '★ (Đang diễn ra)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> Giảng viên được import sẽ được cập nhật hồ sơ và sẵn sàng tham gia công tác hướng dẫn / phản biện trong học kỳ này.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small text-muted">Hỗ trợ các định dạng <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code>.</div>
                    </div>

                    <div class="alert alert-info rounded-3 mb-0 small">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-info me-1"></i> Hướng dẫn nhập liệu:</div>
                        <div class="mt-1 text-muted">Hệ thống sẽ tự động tạo tài khoản Portal với: <code>Tên đăng nhập = Mã GV</code>, <code>Mật khẩu tạm thời ngẫu nhiên</code> (Bắt buộc đổi mật khẩu lần đầu).</div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-upload me-1"></i> Import Dữ Liệu Ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CŨ GIỮ LẠI ĐỂ TƯƠNG THÍCH TEST (AdminGiangVienSemesterFilterTest) -->
<div class="modal fade" id="modalQuanLyGVTheoKy" tabindex="-1" aria-labelledby="modalQuanLyGVTheoKyLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalQuanLyGVTheoKyLabel">
                    <i class="fa-solid fa-calendar-check me-2"></i> Quản Lý &amp; Phân Bổ Giảng Viên Theo Học Kỳ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-pill border" id="pillsModalGvTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2" id="tab-import-gv" data-bs-toggle="pill" data-bs-target="#pane-import-gv" type="button" role="tab" aria-controls="pane-import-gv" aria-selected="true">
                            <i class="fa-solid fa-file-excel text-success me-1"></i> Import Danh Sách Giảng Viên
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2" id="tab-them-gv" data-bs-toggle="pill" data-bs-target="#pane-them-gv" type="button" role="tab" aria-controls="pane-them-gv" aria-selected="false">
                            <i class="fa-solid fa-user-plus text-primary me-1"></i> Thêm Mới Giảng Viên
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="pillsModalGvTabContent">
                    <div class="tab-pane fade show active" id="pane-import-gv" role="tabpanel" aria-labelledby="tab-import-gv">
                        <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chọn File Excel / CSV <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Upload</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pane-them-gv" role="tabpanel" aria-labelledby="tab-them-gv">
                        <p class="text-muted">Sử dụng nút "Thêm giảng viên" tại màn hình Quản Lý Theo Học Kỳ để thêm trực tiếp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BỔ NHIỆM TRƯỞNG KHOA / TRƯỞNG BỘ MÔN -->
<div class="modal fade" id="boNhiemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2">
                    <i class="fa-solid fa-award text-warning"></i> Bổ Nhiệm Chức Vụ &amp; Cấp Tài Khoản Portal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="boNhiemForm" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="small text-muted">Giảng viên được bổ nhiệm:</div>
                        <div class="fs-6 fw-bold text-dark" id="boNhiemGvName">TS. Nguyễn Văn A</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Chọn chức vụ bổ nhiệm hoặc thao tác <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvTruongKhoa" value="TK" onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-dark" for="cvTruongKhoa">
                                    <i class="fa-solid fa-crown text-danger me-1"></i> Trưởng Khoa (VT05)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvTruongBoMon" value="TBM" checked onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-dark" for="cvTruongBoMon">
                                    <i class="fa-solid fa-medal text-warning me-1"></i> Trưởng Bộ Môn (VT04)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="chuc_vu" id="cvHuy" value="HUY" onchange="toggleChucVuFields()">
                                <label class="form-check-label fw-bold text-danger" for="cvHuy">
                                    <i class="fa-solid fa-user-xmark me-1"></i> Hủy chức vụ đã cấp (Trả về vai trò Giảng viên thường VT02)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Trường chọn Khoa khi bổ nhiệm Trưởng khoa -->
                    <div class="mb-3" id="fieldKhoa" style="display: none;">
                        <label class="form-label fw-bold small text-muted">Khoa đảm nhiệm <span class="text-danger">*</span></label>
                        <select name="ma_khoa" id="selectBoNhiemKhoa" class="form-select rounded-3">
                            <option value="">-- Chọn Khoa --</option>
                            @php
                                $allKhoas = \App\Models\Khoa::orderBy('TenKhoa')->get();
                            @endphp
                            @foreach($allKhoas as $k)
                                <option value="{{ $k->MaKhoa }}">{{ $k->TenKhoa }} ({{ $k->MaKhoa }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-info mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i> Cập nhật quyền Trưởng khoa cho tài khoản của giảng viên
                        </div>
                    </div>

                    <!-- Trường chọn Bộ môn khi bổ nhiệm Trưởng bộ môn -->
                    <div class="mb-3" id="fieldBoMon">
                        <label class="form-label fw-bold small text-muted">Bộ môn đảm nhiệm <span class="text-danger">*</span></label>
                        <select name="ma_bomon" id="selectBoNhiemBoMon" class="form-select rounded-3">
                            <option value="">-- Chọn Bộ môn --</option>
                            @foreach($bomons as $bm)
                                <option value="{{ $bm->MaBoMon }}">{{ $bm->TenBoMon }} (Khoa {{ $bm->MaKhoa }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-info mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i> Cập nhật quyền Trưởng bộ môn cho tài khoản của giảng viên
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-0" id="alertHuyChucVu" style="display: none;">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> <strong>Lưu ý:</strong> Khi chọn <em>Hủy chức vụ</em>, quyền quản lý Trưởng khoa / Trưởng bộ môn sẽ được thu hồi và tài khoản chuyển về vai trò Giảng viên bình thường.
                    </div>

                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-0" id="alertMatKhauMacDinh">
                        <i class="fa-solid fa-shield-halved me-1"></i> Giảng viên sẽ sử dụng tài khoản liên kết để đăng nhập vào đúng Portal phân quyền.
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMPORT TÀI KHOẢN TRƯỞNG KHOA / TRƯỞNG BỘ MÔN (08_TaiKhoan_TBM_TK_Mau) -->
<div class="modal fade" id="importTBM_TK_Modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.giangvien.import_tbm_tk') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fa-solid fa-file-excel me-2 text-warning"></i>Upload DS Trưởng Khoa &amp; Trưởng Bộ Môn
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 px-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Chọn file (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
                    </div>

                    <div class="alert alert-secondary py-2 px-3 small rounded-3 mb-3">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-question me-1 text-primary"></i>Quy tắc đặt tên đăng nhập:</div>
                        <div>&bull; Trưởng khoa: <code>TK_{MaKhoa}_001</code> (Role: <code>VT05</code>)</div>
                        <div>&bull; Trưởng bộ môn: <code>TBM_{MaKhoa}_{MaBoMon}_001</code> (Role: <code>VT04</code>)</div>
                        <div class="mt-1">Dùng file mẫu: <strong>sample_imports/08_TaiKhoan_TBM_TK_Mau</strong></div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-upload me-1"></i> Upload &amp; Khởi Tạo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchToTabTheoHocKy() {
    const tabEl = document.getElementById('tab-btn-theohocky');
    if (tabEl) {
        const tab = new bootstrap.Tab(tabEl);
        tab.show();
        window.scrollTo({ top: tabEl.offsetTop - 80, behavior: 'smooth' });
    }
}

function switchThemGVMode(mode) {
    const secCoSan = document.getElementById('form_gv_co_san');
    const secMoi = document.getElementById('form_gv_moi');
    const btnCoSan = document.getElementById('btn-tab-gv-cosan');
    const btnMoi = document.getElementById('btn-tab-gv-moi');

    if (mode === 'moi') {
        if (secCoSan) secCoSan.style.display = 'none';
        if (secMoi) secMoi.style.display = 'block';
        if (btnCoSan) btnCoSan.classList.remove('active');
        if (btnMoi) btnMoi.classList.add('active');
    } else {
        if (secCoSan) secCoSan.style.display = 'block';
        if (secMoi) secMoi.style.display = 'none';
        if (btnCoSan) btnCoSan.classList.add('active');
        if (btnMoi) btnMoi.classList.remove('active');
    }
}

function openBoNhiemModal(gvId, gvName, maBoMon, maKhoa, currentRole) {
    document.getElementById('boNhiemGvName').textContent = gvName + ' (' + gvId + ')';
    document.getElementById('boNhiemForm').action = '/admin/giangvien/' + gvId + '/bo-nhiem';
    
    if (maBoMon) {
        document.getElementById('selectBoNhiemBoMon').value = maBoMon;
    }
    if (maKhoa) {
        document.getElementById('selectBoNhiemKhoa').value = maKhoa;
    }

    if (currentRole === 'VT05') {
        document.getElementById('cvTruongKhoa').checked = true;
    } else if (currentRole === 'VT04') {
        document.getElementById('cvTruongBoMon').checked = true;
    } else {
        document.getElementById('cvTruongBoMon').checked = true;
    }

    toggleChucVuFields();

    const modal = new bootstrap.Modal(document.getElementById('boNhiemModal'));
    modal.show();
}

function toggleChucVuFields() {
    const isTK = document.getElementById('cvTruongKhoa').checked;
    const isTBM = document.getElementById('cvTruongBoMon').checked;
    const isHuy = document.getElementById('cvHuy').checked;

    document.getElementById('fieldKhoa').style.display = isTK ? 'block' : 'none';
    document.getElementById('fieldBoMon').style.display = isTBM ? 'block' : 'none';
    
    const alertHuy = document.getElementById('alertHuyChucVu');
    const alertMk = document.getElementById('alertMatKhauMacDinh');
    if (alertHuy && alertMk) {
        alertHuy.style.display = isHuy ? 'block' : 'none';
        alertMk.style.display = isHuy ? 'none' : 'block';
    }
}

function openModalQuanLyGVTab(tab) {
    const modalEl = document.getElementById('modalQuanLyGVTheoKy');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    setTimeout(function() {
        if (tab === 'them') {
            const btnThem = document.getElementById('tab-them-gv');
            if (btnThem) bootstrap.Tab.getOrCreateInstance(btnThem).show();
        } else {
            const btnImport = document.getElementById('tab-import-gv');
            if (btnImport) bootstrap.Tab.getOrCreateInstance(btnImport).show();
        }
    }, 150);
}
</script>
@endpush
@endsection