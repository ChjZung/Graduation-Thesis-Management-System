@extends('layouts.admin')

@section('page_title', 'Quản Lý Học Phần & Môn Học')

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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid #198754 !important;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid #dc3545 !important;">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Quản Lý Danh Mục Môn Học & Học Phần</h4>
            <p class="text-muted small mb-0">Quản lý cây danh mục học phần: Bộ môn chuyên môn, Học phần dùng chung (Khóa luận, Đồ án) phục vụ đăng ký đề tài và phân chia nhóm.</p>
        </div>
        <div>
            <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small">
                <i class="fa-solid fa-sitemap text-primary me-1"></i> Khoa CNTT — Quản Lý Dữ Liệu Nền Học Vụ
            </div>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Tổng Số Học Phần</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $stats['total'] ?? $hocphans->total() }}</span>
                        <span class="small text-muted fw-medium">học phần</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-book-bookmark fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Học Phần Dùng Chung</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-success">{{ $stats['dung_chung'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">Khóa luận / Đồ án</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-share-nodes fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Môn Chuyên Ngành Bộ Môn</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-warning">{{ $stats['chuyen_nganh'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">theo bộ môn</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-layer-group fs-5"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium mb-1">Học Phần Khoa CNTT</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-info">{{ $stats['cntt'] ?? 0 }}</span>
                        <span class="small text-muted fw-medium">học phần</span>
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fa-solid fa-laptop-code fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="admin-filter-bar mb-4 bg-white p-3 rounded-3 shadow-sm border">
        <form method="GET" action="{{ route('hocphan.index') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
            <div class="d-flex flex-grow-1 flex-wrap align-items-center gap-2" style="min-width: 320px;">
                <div class="input-group" style="min-width: 220px; flex: 1;">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên học phần, mã học phần...">
                </div>
                <select name="ma_khoa" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Khoa --</option>
                    @foreach($khoas as $k)
                        <option value="{{ $k->MaKhoa }}" {{ request('ma_khoa') == $k->MaKhoa ? 'selected' : '' }}>
                            {{ $k->TenKhoa }}
                        </option>
                    @endforeach
                </select>
                <select name="ma_bomon" class="form-select" style="max-width: 220px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả Bộ môn / Dùng chung --</option>
                    <option value="DUNG_CHUNG" {{ request('ma_bomon') === 'DUNG_CHUNG' ? 'selected' : '' }}>⭐ Học phần dùng chung</option>
                    @foreach($bomons as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('ma_bomon') == $bm->MaBoMon ? 'selected' : '' }}>
                            {{ $bm->TenBoMon }} ({{ $bm->MaBoMon }})
                        </option>
                    @endforeach
                </select>
                @if(request('search') || request('ma_khoa') || request('ma_bomon') || request('loai_hoc_phan'))
                    <a href="{{ route('hocphan.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                <button type="button" class="btn btn-info text-white shadow-xs fw-semibold" style="background: #0284c7;" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fa-solid fa-file-import me-1"></i> Import Excel
                </button>
                <button type="button" class="btn btn-success shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Học Phần
                </button>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Học Phần / Môn Học Theo Phân Cấp Bộ Môn</span>
            <span class="badge bg-light text-muted border">Tổng: {{ $hocphans->total() }} học phần</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" width="13%">MÃ HỌC PHẦN</th>
                            <th class="py-3" width="25%">TÊN HỌC PHẦN</th>
                            <th class="py-3 text-center" width="8%">TÍN CHỈ</th>
                            <th class="py-3" width="24%">PHÂN CẤP TRỰC THUỘC</th>
                            <th class="py-3" width="12%">LOẠI HỌC PHẦN</th>
                            <th class="py-3 text-center" width="10%">ĐỀ TÀI / NHÓM</th>
                            <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($hocphans as $hp)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace px-2.5 py-1 fw-bold">{{ $hp->MaHocPhan }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold text-dark fs-6">{{ $hp->TenHocPhan }}</div>
                                    @if($hp->MoTa)
                                        <div class="small text-muted text-truncate" style="max-width: 320px;">{{ $hp->MoTa }}</div>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1">{{ $hp->SoTinChi }} TC</span>
                                </td>
                                <td class="py-3">
                                    @if($hp->isDungChung())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="fa-solid fa-share-nodes me-1"></i> Học phần dùng chung
                                        </span>
                                        <div class="small text-muted mt-1 ps-1">
                                            <i class="fa-solid fa-university me-1"></i>Khoa: {{ $hp->khoa->TenKhoa ?? 'Khoa CNTT' }}
                                        </div>
                                    @else
                                        <span class="badge bg-info-subtle text-dark border border-info-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="fa-solid fa-layer-group me-1 text-primary"></i> {{ $hp->boMon->TenBoMon ?? $hp->MaBoMon }}
                                        </span>
                                        <div class="small text-muted mt-1 ps-1">
                                            <i class="fa-solid fa-university me-1"></i>{{ $hp->khoa->TenKhoa ?? 'Khoa CNTT' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if(str_contains(mb_strtolower($hp->TenHocPhan), 'khóa luận'))
                                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1">Khóa luận</span>
                                    @elseif(str_contains(mb_strtolower($hp->TenHocPhan), 'đồ án'))
                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">Đồ án</span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">{{ $hp->LoaiHocPhan }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    <div class="small">
                                        <span class="text-primary fw-bold">{{ $hp->de_tais_count }}</span> đề tài
                                    </div>
                                    <div class="small text-muted">
                                        <span class="text-success fw-bold">{{ $hp->nhoms_count }}</span> nhóm
                                    </div>
                                </td>
                                <td class="pe-4 text-center py-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('hocphan.show', $hp->MaHocPhan) }}" class="btn btn-sm btn-light text-primary border" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('hocphan.edit', $hp->MaHocPhan) }}" class="btn btn-sm btn-light text-warning border" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('hocphan.destroy', $hp->MaHocPhan) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa học phần này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger border" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                    Không tìm thấy học phần nào phù hợp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($hocphans->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $hocphans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL THÊM HỌC PHẦN -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Thêm Mới Học Phần / Môn Học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hocphan.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mã Học Phần <small class="text-muted">(để trống tự sinh)</small></label>
                            <input type="text" name="MaHocPhan" class="form-control" placeholder="VD: HP_CNPM, HP01...">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-danger">Tên Học Phần *</label>
                            <input type="text" name="TenHocPhan" class="form-control" required placeholder="VD: Công nghệ phần mềm, Khóa luận tốt nghiệp...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-danger">Số Tín Chỉ *</label>
                            <input type="number" name="SoTinChi" class="form-control" value="3" min="1" max="30" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Khoa Trực Thuộc</label>
                            <select name="MaKhoa" class="form-select">
                                @foreach($khoas as $k)
                                    <option value="{{ $k->MaKhoa }}" {{ $k->MaKhoa === 'CNTT' ? 'selected' : '' }}>{{ $k->TenKhoa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Bộ Môn Phụ Trách</label>
                            <select name="MaBoMon" class="form-select">
                                <option value="">-- Học phần dùng chung (Không thuộc riêng BM) --</option>
                                @foreach($bomons as $bm)
                                    <option value="{{ $bm->MaBoMon }}">{{ $bm->TenBoMon }} ({{ $bm->MaBoMon }})</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Nếu để trống, học phần sẽ được phân loại là <strong>Học phần dùng chung</strong> (như Khóa luận tốt nghiệp, Khóa luận kỹ sư).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Loại Học Phần</label>
                            <select name="LoaiHocPhan" class="form-select">
                                <option value="Chuyên ngành">Chuyên ngành</option>
                                <option value="Khóa luận">Khóa luận tốt nghiệp</option>
                                <option value="Đồ án">Đồ án tốt nghiệp / Đồ án chuyên ngành</option>
                                <option value="Dùng chung">Dùng chung cấp Khoa/Trường</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng Thái</label>
                            <select name="TrangThai" class="form-select">
                                <option value="Đang áp dụng">Đang áp dụng</option>
                                <option value="Tạm ngưng">Tạm ngưng</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô Tả / Ghi Chú</label>
                            <textarea name="MoTa" rows="3" class="form-control" placeholder="Mô tả mục tiêu, yêu cầu học phần..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-1"></i> Lưu Học Phần</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-file-excel me-2"></i>Import Học Phần Bằng File Excel/CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hocphan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info small border-0 mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i> Các cột chấp nhận trong file: <code>TenHocPhan</code> (bắt buộc), <code>MaHocPhan</code>, <code>SoTinChi</code>, <code>MaKhoa</code>, <code>MaBoMon</code>, <code>LoaiHocPhan</code>, <code>MoTa</code>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn file Excel (.xlsx, .xls) hoặc CSV:</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-upload me-1"></i> Tiến Hành Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
