@extends('layouts.admin')

@section('page_title', 'Chi Tiết Sinh Viên - ' . $sinhvien->HoTen)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Actions Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.index') }}" class="text-decoration-none">Sinh Viên</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $sinhvien->MaSV }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem; font-weight: bold;">
                    {{ mb_substr($sinhvien->HoTen, 0, 1) }}
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <span>{{ $sinhvien->HoTen }}</span>
                        <span class="badge-code">{{ $sinhvien->MaSV }}</span>
                    </h4>
                    <span class="small text-muted">
                        Lớp {{ $sinhvien->lop->TenLop ?? $sinhvien->MaLop }} &bull; Ngành {{ $sinhvien->nganh->TenNganh ?? '' }} &bull; {{ $sinhvien->khoa->TenKhoa ?? 'Khoa CNTT' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
            </a>
            <a href="{{ route('sinhvien.edit', $sinhvien->MaSV) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
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
                        <div class="text-muted small fw-medium">Tín Chỉ Tích Lũy</div>
                        <div class="fs-4 fw-bold text-dark mt-1">
                            {{ $stats['tin_chi'] }} <span class="fs-6 fw-normal text-muted">/ 115 chuẩn</span>
                        </div>
                        <div class="small {{ $stats['is_du_dk'] ? 'text-success' : 'text-danger' }} mt-1 fw-medium">
                            <i class="fa-solid {{ $stats['is_du_dk'] ? 'fa-check' : 'fa-xmark' }} me-1"></i>
                            {{ $stats['is_du_dk'] ? 'Đạt chuẩn tín chỉ' : 'Thiếu ' . (115 - $stats['tin_chi']) . ' tín chỉ' }}
                        </div>
                    </div>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-cyan">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Điểm Tích Lũy (GPA)</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($stats['gpa'], 2) }}</div>
                        <div class="small text-muted mt-1">Thang điểm 4.0</div>
                    </div>
                    <div class="kpi-icon-circle bg-info-subtle text-info">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card {{ $stats['is_du_dk'] ? 'kpi-green' : 'kpi-orange' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Xét Điều Kiện KLTN</div>
                        <div class="fs-5 fw-bold mt-1">
                            @if($stats['is_du_dk'])
                                <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Đủ Điều Kiện</span>
                            @else
                                <span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa Đủ ĐK</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">Quy định KLTN HUIT</div>
                    </div>
                    <div class="kpi-icon-circle {{ $stats['is_du_dk'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                        <i class="fa-solid {{ $stats['is_du_dk'] ? 'fa-certificate' : 'fa-clock' }}"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 admin-kpi-card kpi-orange">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Nhóm Khóa Luận</div>
                        <div class="fs-5 fw-bold text-dark mt-1">
                            @if($nhomHienTai)
                                <span class="text-primary">{{ $nhomHienTai->TenNhom ?? $nhomHienTai->MaNhom }}</span>
                            @else
                                <span class="text-muted">Chưa tham gia</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            {{ $nhomHienTai ? $nhomHienTai->sinhViens->count() . ' thành viên' : 'Chưa có nhóm' }}
                        </div>
                    </div>
                    <div class="kpi-icon-circle bg-warning-subtle text-warning">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left: Khóa Luận Tốt Nghiệp Info & Tiến Độ & Điểm -->
        <div class="col-lg-8">
            @if($nhomHienTai)
                @php
                    $deTai = $nhomHienTai->dangKyDeTai?->deTai;
                    $gvhd = $deTai?->giangVien;
                    $baoCaos = $nhomHienTai->baoCaos ?? collect();
                @endphp

                <!-- Đề tài & Giảng viên hướng dẫn -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-contract text-primary"></i>
                            <span>Thông Tin Đề Tài & Nhóm Đồ Án</span>
                        </h6>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                            {{ $nhomHienTai->TenNhom ?? $nhomHienTai->MaNhom }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <div class="text-muted small">Tên Đề Tài Khóa Luận:</div>
                            <div class="fs-6 fw-bold text-dark mt-1">
                                {{ $deTai->TenDeTai ?? 'Chưa được gán đề tài chính thức' }}
                            </div>
                            @if($deTai)
                                <div class="small text-muted mt-1"><span class="badge-code">{{ $deTai->MaDeTai }}</span></div>
                            @endif
                        </div>

                        <div class="row g-3 pt-2 border-top">
                            <div class="col-sm-6">
                                <div class="text-muted small">Giảng Viên Hướng Dẫn:</div>
                                <div class="fw-bold text-dark mt-1">
                                    {{ $gvhd ? ($gvhd->HocVi ? $gvhd->HocVi . '. ' : '') . $gvhd->HoTen : 'Chưa phân công' }}
                                </div>
                                <div class="small text-muted">{{ $gvhd->Email ?? '' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small">Các Thành Viên Trong Nhóm:</div>
                                <div class="mt-1">
                                    @foreach($nhomHienTai->sinhViens as $member)
                                        <div class="d-inline-block me-2 mb-1">
                                            <span class="badge {{ $member->MaSV === $sinhvien->MaSV ? 'bg-primary' : 'bg-light text-dark border' }} rounded-pill px-3 py-1">
                                                {{ $member->HoTen }} ({{ $member->MaSV }})
                                                @if($member->pivot->VaiTro)
                                                    - <small>{{ $member->pivot->VaiTro }}</small>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tiến độ 5 mốc liên hoàn -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-list-check text-primary"></i>
                            <span>Tiến Độ Báo Cáo 5 Mốc Liên Hoàn</span>
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3 py-2" width="15%">MỐC</th>
                                        <th class="py-2">TÊN MỐC TIẾN ĐỘ</th>
                                        <th class="py-2 text-center" width="18%">NGÀY NỘP</th>
                                        <th class="py-2 text-center" width="20%">TRẠNG THÁI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($m = 1; $m <= 5; $m++)
                                        @php
                                            $bc = $baoCaos->where('LanBaoCao', $m)->first();
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge bg-light text-primary border rounded-pill px-2 py-1">Mốc {{ $m }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-medium text-dark">
                                                    @switch($m)
                                                        @case(1) Đề cương chi tiết &amp; Khảo sát hệ thống @break
                                                        @case(2) Thiết kế kiến trúc &amp; Cơ sở dữ liệu @break
                                                        @case(3) Hiện thực hóa các chức năng cốt lõi @break
                                                        @case(4) Kiểm thử &amp; Hoàn thiện sản phẩm @break
                                                        @case(5) Báo cáo tổng kết &amp; Hồ sơ bảo vệ @break
                                                    @endswitch
                                                </div>
                                            </td>
                                            <td class="text-center small text-muted">
                                                {{ $bc && $bc->NgayNop ? \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') : 'Chưa nộp' }}
                                            </td>
                                            <td class="text-center">
                                                @if($bc)
                                                    @if($bc->TrangThai === 'Đạt' || $bc->TrangThai === 'DAT')
                                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                                            <i class="fa-solid fa-check me-1"></i> Đạt yêu cầu
                                                        </span>
                                                    @elseif($bc->TrangThai === 'Nộp lại' || $bc->TrangThai === 'NOP_LAI')
                                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">
                                                            <i class="fa-solid fa-rotate me-1"></i> Nộp lại
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 small">
                                                            <i class="fa-solid fa-clock me-1"></i> Đang chấm
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">
                                                        Chưa nộp
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kết Quả Chấm Điểm & Đánh Giá -->
                @if($ketQua)
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-award text-primary"></i>
                                <span>Kết Quả Bảo Vệ Khóa Luận Tốt Nghiệp</span>
                            </h6>
                            <span class="badge bg-success rounded-pill px-3 py-1">
                                Xếp loại: {{ $ketQua->xepLoai($ketQua->DiemTongKet ?? 0) }}
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3 text-center">
                                <div class="col-sm-3">
                                    <div class="p-3 rounded bg-light">
                                        <div class="text-muted small">Điểm Hướng Dẫn (30%)</div>
                                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($ketQua->DiemHuongDan ?? 0, 1) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="p-3 rounded bg-light">
                                        <div class="text-muted small">Điểm Phản Biện (30%)</div>
                                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($ketQua->DiemPhanBien ?? 0, 1) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="p-3 rounded bg-light">
                                        <div class="text-muted small">Điểm Hội Đồng (40%)</div>
                                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($ketQua->DiemHoiDongTB ?? 0, 1) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="p-3 rounded bg-primary text-white shadow-sm">
                                        <div class="small opacity-75">Điểm Tổng Kết (Hệ 10)</div>
                                        <div class="fs-3 fw-bold mt-1">{{ number_format($ketQua->DiemTongKet ?? 0, 2) }}</div>
                                        <div class="small">Thang 4: {{ $ketQua->DiemHe4 }} &bull; Điểm chữ: {{ $ketQua->DiemChu }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                <!-- Chưa có nhóm KLTN banner -->
                <div class="card border-0 shadow-sm rounded-3 p-5 text-center mb-4">
                    <i class="fa-solid fa-users-slash text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold text-dark">Sinh viên chưa tham gia nhóm đồ án</h5>
                    <p class="text-muted small mb-3">
                        @if($stats['is_du_dk'])
                            Sinh viên này đã đủ điều kiện (&ge; 115 tín chỉ) nhưng chưa tạo hoặc gia nhập nhóm khóa luận nào trong học kỳ hiện tại.
                        @else
                            Sinh viên chưa đủ chuẩn tín chỉ tích lũy (&ge; 115 TC) để đăng ký đề tài khóa luận.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Right: Profile Info & Account Management -->
        <div class="col-lg-4">
            <!-- Hồ sơ học tập Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-id-card text-primary me-2"></i>Hồ Sơ Cá Nhân &amp; Học Tập
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Mã Sinh Viên (MSSV)</span>
                            <span class="badge-code">{{ $sinhvien->MaSV }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Họ Và Tên</span>
                            <span class="fw-bold text-dark">{{ $sinhvien->HoTen }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Giới Tính</span>
                            <span class="text-dark">{{ $sinhvien->GioiTinh ?? 'Nam' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngày Sinh</span>
                            <span class="text-dark">{{ $sinhvien->NgaySinh ? \Carbon\Carbon::parse($sinhvien->NgaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Lớp Hành Chính</span>
                            <a href="{{ route('lop.show', $sinhvien->MaLop) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $sinhvien->lop->TenLop ?? $sinhvien->MaLop }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Ngành Đào Tạo</span>
                            <a href="{{ route('nganh.show', $sinhvien->MaNganh) }}" class="text-dark text-decoration-none text-end">
                                {{ $sinhvien->nganh->TenNganh ?? $sinhvien->MaNganh }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khoa Quản Lý</span>
                            <span class="text-dark text-end">{{ $sinhvien->khoa->TenKhoa ?? 'Khoa CNTT' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Khóa Tuyển Sinh</span>
                            <span class="text-dark">{{ $sinhvien->KhoaHoc ?? 'K11' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Email Sinh Viên</span>
                            <span class="text-dark">{{ $sinhvien->Email ?? 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Số Điện Thoại</span>
                            <span class="text-dark">{{ $sinhvien->SoDienThoai ?? 'Chưa cập nhật' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Trạng Thái Đào Tạo</span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                                {{ $sinhvien->TrangThai ?? 'Đang học' }}
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
                    @if($sinhvien->taiKhoan)
                        <span class="badge {{ $sinhvien->taiKhoan->TrangThai ? 'bg-success' : 'bg-danger' }}">
                            {{ $sinhvien->taiKhoan->TrangThai ? 'Active' : 'Locked' }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if($sinhvien->taiKhoan)
                        <ul class="list-group list-group-flush small mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Tên Đăng Nhập</span>
                                <span class="badge-code">{{ $sinhvien->taiKhoan->TenDangNhap }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Vai Trò</span>
                                <span class="fw-medium text-dark">{{ $sinhvien->taiKhoan->vaiTro->TenVaiTro ?? 'Sinh viên' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Trạng Thái Mật Khẩu</span>
                                <span class="badge bg-light text-dark border">
                                    {{ $sinhvien->taiKhoan->TrangThaiMatKhau ?? 'ACTIVE' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted">Đăng Nhập Gần Nhất</span>
                                <span class="text-muted">
                                    {{ $sinhvien->taiKhoan->LanDangNhapCuoi ? \Carbon\Carbon::parse($sinhvien->taiKhoan->LanDangNhapCuoi)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}
                                </span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.taikhoan.toggleLock', $sinhvien->taiKhoan->MaTK) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $sinhvien->taiKhoan->TrangThai ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 rounded-pill">
                                    <i class="fa-solid {{ $sinhvien->taiKhoan->TrangThai ? 'fa-lock' : 'fa-lock-open' }} me-1"></i>
                                    {{ $sinhvien->taiKhoan->TrangThai ? 'Khóa Tài Khoản Sinh Viên' : 'Mở Khóa Tài Khoản' }}
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">
                            <i class="fa-solid fa-user-xmark mb-2" style="font-size: 1.5rem;"></i>
                            <div>Chưa liên kết tài khoản hệ thống cho sinh viên này.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
