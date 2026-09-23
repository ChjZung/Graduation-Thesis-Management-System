@extends('layouts.admin')

@section('page_title', 'Chi Tiết Giảng Viên - ' . $giangvien->HoTen)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giangvien.index') }}" class="text-decoration-none">Giảng Viên</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $giangvien->MaGV }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem; font-weight: bold;">
                    {{ mb_substr($giangvien->HoTen, 0, 1) }}
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <span>{{ $giangvien->HocVi ? $giangvien->HocVi . '. ' : '' }}{{ $giangvien->HoTen }}</span>
                        <span class="badge-code">{{ $giangvien->MaGV }}</span>
                    </h4>
                    <span class="small text-muted">
                        {{ $giangvien->boMon->TenBoMon ?? 'Bộ môn' }} &bull; {{ $giangvien->boMon->khoa->TenKhoa ?? 'Khoa CNTT' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('giangvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('giangvien.edit', $giangvien->MaGV) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa Hồ Sơ
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-blue">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tổng Đề Tài</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_detai'] }}</div>
                        <div class="small text-muted mt-1">Đã đề xuất</div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-book"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-green">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Nhóm Đang Hướng Dẫn</div>
                        <div class="fs-4 fw-bold text-dark mt-1">
                            {{ $stats['nhom_huong_dan'] }} <span class="fs-6 fw-normal text-muted">/ {{ $stats['chi_tieu_toida'] }} tối đa</span>
                        </div>
                        <div class="small text-success mt-1 fw-medium">
                            <i class="fa-solid fa-check-circle me-1"></i>Đang trong tải cho phép
                        </div>
                    </div>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Hội Đồng Bảo Vệ</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_hoidong'] }}</div>
                        <div class="small text-muted mt-1">Thành viên HĐ</div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Trạng Thái Tài Khoản</div>
                        <div class="fs-5 fw-bold mt-1">
                            @if($giangvien->taiKhoan && $giangvien->taiKhoan->TrangThai)
                                <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                            @else
                                <span class="text-danger"><i class="fa-solid fa-lock me-1"></i>Đang khóa</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">TK: {{ $giangvien->taiKhoan->TenDangNhap ?? $giangvien->MaGV }}</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left: Tabs for Topics & Committees -->
        <div class="col-lg-8">
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs border-bottom mb-3" id="gvTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-2" id="topics-tab" data-bs-toggle="tab" data-bs-target="#topics-pane" type="button" role="tab">
                        <i class="fa-solid fa-file-signature me-2 text-primary"></i>Đề Tài Hướng Dẫn ({{ $giangvien->deTais->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2" id="committees-tab" data-bs-toggle="tab" data-bs-target="#committees-pane" type="button" role="tab">
                        <i class="fa-solid fa-landmark me-2 text-primary"></i>Hội Đồng Tham Gia ({{ $giangvien->thanhVienHoiDongs->count() }})
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="gvTabsContent">
                <!-- Tab 1: Đề tài hướng dẫn -->
                <div class="tab-pane fade show active" id="topics-pane" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3" width="16%">MÃ ĐỀ TÀI</th>
                                        <th class="py-3">TÊN ĐỀ TÀI</th>
                                        <th class="py-3" width="18%">NHÓM SV</th>
                                        <th class="py-3 text-center" width="14%">HỌC KỲ</th>
                                        <th class="pe-4 py-3 text-center" width="16%">TRẠNG THÁI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($giangvien->deTais as $dt)
                                        @php
                                            $nhom = $dt->phieuDangKys->first()?->nhom;
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge-code">{{ $dt->MaDeTai }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                            </td>
                                            <td>
                                                @if($nhom)
                                                    <div class="fw-medium text-dark">{{ $nhom->TenNhom ?? $nhom->MaNhom }}</div>
                                                    <div class="small text-muted">{{ $nhom->sinhViens->count() }} thành viên</div>
                                                @else
                                                    <span class="text-muted small">Chưa có nhóm</span>
                                                @endif
                                            </td>
                                            <td class="text-center small">
                                                {{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}
                                            </td>
                                            <td class="pe-4 text-center">
                                                @if($dt->TrangThai === 'Đã duyệt' || $dt->TrangThai === 'DA_DUYET')
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                                        <i class="fa-solid fa-check me-1"></i> Đã duyệt
                                                    </span>
                                                @elseif($dt->TrangThai === 'Chờ duyệt')
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 small">
                                                        <i class="fa-solid fa-clock me-1"></i> Chờ duyệt
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                                                        {{ $dt->TrangThai ?? 'Bản nháp' }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                                <div>Chưa có đề tài nào được phân công cho giảng viên này.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Hội đồng tham gia -->
                <div class="tab-pane fade" id="committees-pane" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3" width="16%">MÃ HĐ</th>
                                        <th class="py-3">TÊN HỘI ĐỒNG</th>
                                        <th class="py-3" width="20%">VAI TRÒ TRONG HĐ</th>
                                        <th class="py-3 text-center" width="16%">HỌC KỲ</th>
                                        <th class="pe-4 py-3 text-center" width="14%">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($giangvien->thanhVienHoiDongs as $tv)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge-code">{{ $tv->MaHoiDong }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $tv->hoiDong->TenHoiDong ?? $tv->MaHoiDong }}</div>
                                                <div class="small text-muted">{{ $tv->hoiDong->PhongBaoVe ? 'Phòng ' . $tv->hoiDong->PhongBaoVe : '' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                                                    {{ $tv->VaiTro ?? 'Ủy viên' }}
                                                </span>
                                            </td>
                                            <td class="text-center small">
                                                {{ $tv->hoiDong->hocKy->TenHocKy ?? '' }}
                                            </td>
                                            <td class="pe-4 text-center">
                                                <a href="{{ route('admin.hoidong.show', $tv->MaHoiDong) }}" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem chi tiết hội đồng">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-folder-open mb-2" style="font-size: 1.5rem;"></i>
                                                <div>Chưa có hội đồng nào phân công giảng viên này.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Profile Details & Account Management -->
        <div class="col-lg-4">
            <!-- Hồ sơ chuyên môn Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-id-card text-primary me-2"></i>Hồ Sơ Chuyên Môn
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Giảng Viên</span>
                            <span class="badge-code">{{ $giangvien->MaGV }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Học Vị / Học Hàm</span>
                            <span class="fw-bold text-dark">{{ $giangvien->HocVi ?? 'Giảng viên' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Bộ Môn Trực Thuộc</span>
                            <a href="{{ route('bomon.show', $giangvien->MaBoMon) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $giangvien->boMon->TenBoMon ?? $giangvien->MaBoMon }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khoa Quản Lý</span>
                            <span class="text-dark">{{ $giangvien->boMon->khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Email Liên Hệ</span>
                            <span class="text-dark">{{ $giangvien->Email ?? 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Số Điện Thoại</span>
                            <span class="text-dark">{{ $giangvien->SoDienThoai ?? 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái Công Tác</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                                {{ $giangvien->TrangThai ?? 'Đang công tác' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tài khoản hệ thống Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-user-lock text-primary me-2"></i>Tài Khoản Hệ Thống
                    </h6>
                    @if($giangvien->taiKhoan)
                        <span class="badge {{ $giangvien->taiKhoan->TrangThai ? 'bg-success' : 'bg-danger' }}">
                            {{ $giangvien->taiKhoan->TrangThai ? 'Active' : 'Locked' }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if($giangvien->taiKhoan)
                        <ul class="list-group list-group-flush small mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Tên Đăng Nhập</span>
                                <span class="badge-code">{{ $giangvien->taiKhoan->TenDangNhap }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Vai Trò</span>
                                <span class="fw-medium text-dark">{{ $giangvien->taiKhoan->vaiTro->TenVaiTro ?? 'Giảng viên' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Trạng Thái Mật Khẩu</span>
                                <span class="badge bg-light text-dark border">
                                    {{ $giangvien->taiKhoan->TrangThaiMatKhau ?? 'ACTIVE' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Đăng Nhập Gần Nhất</span>
                                <span class="text-muted">
                                    {{ $giangvien->taiKhoan->LanDangNhapCuoi ? \Carbon\Carbon::parse($giangvien->taiKhoan->LanDangNhapCuoi)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}
                                </span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.taikhoan.toggleLock', $giangvien->taiKhoan->MaTK) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $giangvien->taiKhoan->TrangThai ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 rounded-pill">
                                    <i class="fa-solid {{ $giangvien->taiKhoan->TrangThai ? 'fa-lock' : 'fa-lock-open' }} me-1"></i>
                                    {{ $giangvien->taiKhoan->TrangThai ? 'Khóa Tài Khoản Này' : 'Mở Khóa Tài Khoản' }}
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">
                            <i class="fa-solid fa-user-xmark mb-2" style="font-size: 1.5rem;"></i>
                            <div>Chưa liên kết tài khoản hệ thống cho giảng viên này.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
