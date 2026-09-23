@extends('layouts.admin')

@section('page_title', 'Danh sách SV Đủ Điều Kiện Khóa Luận')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-check text-primary"></i> Rà Soát &amp; Lập Danh Sách Sinh Viên Đủ Điều Kiện Khóa Luận
        </h4>
        <div class="text-muted small">Quy trình Giáo vụ rà soát điều kiện tích lũy tín chỉ (&ge; 115 TC), ĐTB (&ge; 2.0), cập nhật bổ sung và công bố danh sách chính thức theo học kỳ.</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm">
            <i class="fa-solid fa-list me-1"></i> Danh Sách Tất Cả SV
        </a>
        <a href="{{ route('admin.sinhvien.export_du_dk', ['MaHocKy' => $selectedHocKy]) }}" class="btn btn-success rounded-pill px-3 py-2 btn-sm fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel Danh Sách
        </a>
    </div>
</div>



<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4 border-bottom">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('sinhvien.index') }}">
            <i class="fa-solid fa-users me-1"></i> Quản Lý Danh Mục Sinh Viên
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active fw-bold text-primary" href="{{ route('admin.sinhvien.dieu_kien') }}">
            <i class="fa-solid fa-award me-1"></i> Danh Sách SV Đủ Điều Kiện Khóa Luận (Học Kỳ)
        </a>
    </li>
</ul>

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card h-100 p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-medium mb-1">Tổng SV Rà Soát (HK: {{ $selectedHocKy }})</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-dark">{{ $stats['total_evaluated'] ?? 0 }}</span>
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
                    <span class="fs-3 fw-bold text-success">{{ $stats['total_eligible'] ?? 0 }}</span>
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
                    <span class="fs-3 fw-bold text-danger">{{ $stats['total_ineligible'] ?? 0 }}</span>
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
                    @if($stats['is_published'])
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="fa-solid fa-bullhorn me-1"></i> Đã Công Bố
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="fa-solid fa-lock me-1"></i> Chưa Công Bố
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

<!-- Main Content Card -->
<div class="card card-premium shadow-sm">
    <div class="card-header-premium d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-filter text-primary"></i>
            <span class="fw-bold text-dark">Rà Soát &amp; Lọc Danh Sách Theo Học Kỳ</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalRaSoat">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> ⚡ Tự Động Rà Soát
            </button>
            <form method="POST" action="{{ route('admin.sinhvien.cong_bo') }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn công bố chính thức danh sách đủ điều kiện cho học kỳ này?')">
                @csrf
                <input type="hidden" name="MaHocKy" value="{{ $selectedHocKy }}">
                <button type="submit" class="btn btn-warning btn-sm text-dark rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Danh Sách
                </button>
            </form>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.sinhvien.dieu_kien') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Chọn Học Kỳ</label>
                <select name="MaHocKy" class="form-select rounded-pill" onchange="this.form.submit()">
                    @foreach($hocKys as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} - {{ $hk->NamHoc }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Lớp Hành Chính</label>
                <select name="MaLop" class="form-select rounded-pill" onchange="this.form.submit()">
                    <option value="">-- Tất cả Lớp --</option>
                    @foreach($lops as $l)
                        <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                            {{ $l->TenLop }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Kết Quả Rà Soát</label>
                <select name="trang_thai" class="form-select rounded-pill" onchange="this.form.submit()">
                    <option value="">-- Tất cả Trạng Thái --</option>
                    <option value="Đủ điều kiện" {{ request('trang_thai') == 'Đủ điều kiện' ? 'selected' : '' }}>Đủ Điều Kiện</option>
                    <option value="Chưa đủ điều kiện" {{ request('trang_thai') == 'Chưa đủ điều kiện' ? 'selected' : '' }}>Chưa Đủ Điều Kiện</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Tìm Kiếm MSSV / Họ Tên</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Nhập MSSV, Họ tên..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary rounded-end-pill px-3"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th>MSSV</th>
                        <th>Họ Và Tên</th>
                        <th>Lớp / Ngành</th>
                        <th class="text-center">Số Tín Chỉ</th>
                        <th class="text-center">ĐTB Tích Lũy</th>
                        <th class="text-center">Kết Quả Xét Duyệt</th>
                        <th>Tiêu Chí &amp; Ghi Chú</th>
                        <th class="text-center" style="width: 120px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dsDuDieuKien as $idx => $item)
                        @php
                            $sv = $item->sinhVien;
                        @endphp
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $dsDuDieuKien->firstItem() + $idx }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $item->MaSV }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $sv->HoTen ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $sv->Email ?? '' }}</div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-dark border">{{ $sv->lop->TenLop ?? 'Chưa phân lớp' }}</span></div>
                                <div class="small text-muted">{{ $sv->lop->nganh->TenNganh ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold {{ ($sv->SoTinChiTichLuy ?? 0) >= 115 ? 'text-success' : 'text-danger' }}">
                                    {{ $sv->SoTinChiTichLuy ?? 0 }} TC
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ ($sv->DiemTichLuy ?? 0) >= 2.0 ? 'bg-info text-dark' : 'bg-warning text-dark' }}">
                                    {{ number_format($sv->DiemTichLuy ?? 0, 2) }} / 4.0
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->TrangThai === 'Đủ điều kiện')
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đủ Điều Kiện
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Chưa Đủ Điều Kiện
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $item->DieuKien }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $item->GhiChu }}</div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalCapNhat{{ $item->MaDSDK }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                </button>

                                <!-- Modal Cập Nhật Trạng Thái -->
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
                                                            <option value="Đủ điều kiện" {{ $item->TrangThai === 'Đủ điều kiện' ? 'selected' : '' }}>Đủ Điều Kiện</option>
                                                            <option value="Chưa đủ điều kiện" {{ $item->TrangThai === 'Chưa đủ điều kiện' ? 'selected' : '' }}>Chưa Đủ Điều Kiện</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Ghi Chú Phê Duyệt / Lý Do Khác</label>
                                                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập lý do hoặc quyết định đặc cách của Khoa...">{{ $item->GhiChu }}</textarea>
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
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                                <div class="fw-bold">Chưa có dữ liệu rà soát cho học kỳ {{ $selectedHocKy }}</div>
                                <div class="small">Nhấn nút <strong>⚡ Tự Động Rà Soát</strong> để hệ thống tự động quét tiêu chí tích lũy tín chỉ của tất cả sinh viên.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $dsDuDieuKien->links() }}
        </div>
    </div>
</div>

<!-- Modal Tự Động Rà Soát -->
<div class="modal fade" id="modalRaSoat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Tự Động Rà Soát Điều Kiện Làm Khóa Luận</h5>
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
                            <div class="form-text small">Mặc định quy định Khoa: 115 Tín chỉ.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ĐTB Tích Lũy Tối Thiểu <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="MinGPA" class="form-control" value="2.0" min="0" max="4.0" required>
                            <div class="form-text small">Mặc định thang điểm 4: &ge; 2.0.</div>
                        </div>
                    </div>

                    <div class="alert alert-info rounded-3 mb-0 small">
                        <i class="fa-solid fa-circle-info me-1"></i> Hệ thống sẽ rà soát tất cả sinh viên đang học, tự động so sánh số tín chỉ và điểm tích lũy với tiêu chí để lập danh sách chính thức.
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Thực Hiện Rà Soát</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
