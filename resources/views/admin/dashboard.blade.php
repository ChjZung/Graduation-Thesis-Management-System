@extends('layouts.admin')
@section('title', 'Bảng Tổng Quan Khóa Luận Tốt Nghiệp')
@section('page_title', 'Bảng Tổng Quan')

@section('content')

{{-- ── THANH TIÊU ĐỀ & BỘ LỌC HỌC KỲ / NÚT XUẤT BÁO CÁO ── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #003b73 100%); color: #ffffff;">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-white text-primary px-3 py-1 rounded-pill fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-circle-check text-success me-2"></i> DỮ LIỆU ĐỒNG BỘ THỜI GIAN THỰC
                    </span>
                    @if($keHoachHienHanh)
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill" style="font-size: 11px;">
                            {{ $keHoachHienHanh->TenKeHoach }}
                        </span>
                    @endif
                </div>
                <h4 class="fw-bold mb-1 text-white">
                    <i class="fa-solid fa-chart-line me-2 text-warning"></i> Bảng Điều Hành &amp; Thống Kê Khóa Luận Tốt Nghiệp
                </h4>
                <p class="mb-0 text-white-50 small">
                    Hệ thống quản lý, giám sát quy trình đề tài, nhóm sinh viên, tiến độ thực hiện và hồ sơ bảo vệ
                </p>
            </div>

            {{-- Bộ lọc Học kỳ & Dropdown Xuất Báo Cáo --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Form Lọc Học kỳ --}}
                <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex align-items-center bg-white bg-opacity-10 p-1 rounded-pill border border-white border-opacity-25">
                    <i class="fa-solid fa-calendar-days text-white-50 ms-3 me-2"></i>
                    <select name="hoc_ky" class="form-select form-select-sm border-0 bg-transparent text-white fw-bold pe-4" style="cursor: pointer; min-width: 170px;" onchange="this.form.submit()">
                        <option value="all" class="text-dark" {{ $selectedHocKy === 'all' ? 'selected' : '' }}>-- Tất cả học kỳ --</option>
                        @foreach($hocKys as $hk)
                            <option value="{{ $hk->MaHocKy }}" class="text-dark" {{ $selectedHocKy === $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- Nút Dropdown Xuất File Thống Kê & Báo Cáo --}}
                <div class="dropdown">
                    <button class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="dropdownExportReports" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-export me-2"></i> Xuất Báo Cáo &amp; Thống Kê
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 p-2" aria-labelledby="dropdownExportReports" style="min-width: 280px; z-index: 1050;">
                        <li>
                            <div class="px-3 py-2 small fw-bold text-muted text-uppercase" style="font-size: 11px;">
                                File Excel Tổng Hợp (.XLSX)
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2" href="{{ route('admin.dashboard.export_tong_hop', ['hoc_ky' => $selectedHocKy]) }}">
                                <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">Báo Cáo Tổng Hợp Khóa Luận</div>
                                    <div class="text-muted small" style="font-size: 11px;">Chỉ số KPI, số liệu theo Bộ môn</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2" href="{{ route('admin.dashboard.export_phan_cong', ['hoc_ky' => $selectedHocKy]) }}">
                                <i class="fa-solid fa-table-list text-primary fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">Danh Sách Phân Công Đề Tài</div>
                                    <div class="text-muted small" style="font-size: 11px;">Nhóm, thành viên, đề tài, GVHD</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2" href="{{ route('admin.dashboard.export_sinh_vien', ['hoc_ky' => $selectedHocKy]) }}">
                                <i class="fa-solid fa-user-check text-info fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">DS Sinh Viên Đủ Điều Kiện &amp; Nhóm</div>
                                    <div class="text-muted small" style="font-size: 11px;">Mã SV, điểm tích lũy, nhóm, trạng thái</div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-2"></li>
                        <li>
                            <div class="px-3 py-2 small fw-bold text-muted text-uppercase" style="font-size: 11px;">
                                Báo Cáo In Ấn &amp; PDF
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2" href="{{ route('admin.dashboard.print_report', ['hoc_ky' => $selectedHocKy]) }}" target="_blank">
                                <i class="fa-solid fa-print text-secondary fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">In Báo Cáo / Lưu PDF A4</div>
                                    <div class="text-muted small" style="font-size: 11px;">Bản in chuẩn mẫu văn bản công tác</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 1: 4 THẺ KPI TỔNG QUAN HỆ THỐNG ── --}}
<div class="row g-3 mb-4">
    <!-- 1. Sinh Viên Đủ Điều Kiện -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important; border-top: 4px solid #0072ce !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Sinh Viên Khóa Luận</span>
                    <div class="fw-bold text-dark fs-3 lh-1 mt-1">{{ $soSVDuDieuKien }}</div>
                </div>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #e0f2fe; color: #0072ce;">
                    <i class="fa-solid fa-graduation-cap fs-5"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 12px;">
                <span class="text-success fw-bold"><i class="fa-solid fa-check me-2"></i>{{ $soSVDaCoNhom }} đã có nhóm</span>
                <span class="text-muted">{{ $soSVChuaCoNhom }} chưa nhóm</span>
            </div>
        </div>
    </div>

    <!-- 2. Đề Tài Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important; border-top: 4px solid #10b981 !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Đề Tài Đã Công Bố</span>
                    <div class="fw-bold text-dark fs-3 lh-1 mt-1 text-success">{{ $soDeTaiDaCongBo }}</div>
                </div>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #dcfce7; color: #10b981;">
                    <i class="fa-solid fa-book-open fs-5"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 12px;">
                <span class="text-muted">Tổng đề xuất: <strong>{{ $soDeTaiTong }}</strong></span>
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill" title="Chờ duyệt BM hoặc Khoa">
                    {{ $soDeTaiChoDuyetBM + $soDeTaiChoDuyetKhoa }} chờ duyệt
                </span>
            </div>
        </div>
    </div>

    <!-- 3. Nhóm Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important; border-top: 4px solid #6366f1 !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Nhóm Sinh Viên</span>
                    <div class="fw-bold text-dark fs-3 lh-1 mt-1">{{ $soNhomTong }}</div>
                </div>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #e0e7ff; color: #6366f1;">
                    <i class="fa-solid fa-users fs-5"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 12px;">
                <span class="text-primary fw-bold"><i class="fa-solid fa-link me-2"></i>{{ $soNhomCoDeTai }} có đề tài</span>
                <span class="text-muted">{{ $soNhomChuaCoDeTai }} chờ ghép</span>
            </div>
        </div>
    </div>

    <!-- 4. Giảng Viên Hướng Dẫn -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important; border-top: 4px solid #f59e0b !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Giảng Viên Cán Bộ</span>
                    <div class="fw-bold text-dark fs-3 lh-1 mt-1">{{ $soGiangVien }}</div>
                </div>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #fef3c7; color: #f59e0b;">
                    <i class="fa-solid fa-chalkboard-user fs-5"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 12px;">
                <span class="text-dark fw-bold">{{ $soGVDangHuongDan }} GV đang HD</span>
                <span class="text-muted">{{ $soGVDeXuatDeTai }} GV ra đề</span>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 2: BIỂU ĐỒ SO SÁNH THEO BỘ MÔN & CƠ CẤU TRẠNG THÁI ĐỀ TÀI ── --}}
<div class="row g-4 mb-4">
    {{-- Cột trái: Biểu đồ Cột phân bổ đề tài & nhóm theo Bộ môn --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="border: 1px solid #e2e8f0 !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
                        <i class="fa-solid fa-chart-column me-2 text-primary"></i> Tình Hình Đề Tài &amp; Nhóm Khóa Luận Theo Bộ Môn
                    </h6>
                    <span class="text-muted small">So sánh số đề tài đề xuất, đề tài đã công bố và số nhóm thực hiện</span>
                </div>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                    {{ count($thongKeBoMon) }} Bộ môn
                </span>
            </div>

            <div style="height: 280px; position: relative;">
                <canvas id="chartBoMonComparison"></canvas>
            </div>
        </div>
    </div>

    {{-- Cột phải: Biểu đồ Donut cơ cấu trạng thái đề tài --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="border: 1px solid #e2e8f0 !important;">
            <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                <i class="fa-solid fa-pie-chart me-2 text-success"></i> Cơ Cấu Trạng Thái Đề Tài
            </h6>
            <span class="text-muted small mb-3 d-block">Phân loại theo quy trình phê duyệt 2 cấp</span>

            <div class="d-flex justify-content-center align-items-center my-auto" style="height: 190px; position: relative;">
                <canvas id="chartDeTaiStatus"></canvas>
                <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                    <div class="fw-bold text-dark lh-1" style="font-size: 1.85rem;">{{ $soDeTaiTong }}</div>
                    <div class="text-muted small fw-semibold" style="font-size: 11px;">Tổng đề tài</div>
                </div>
            </div>

            <div class="d-flex flex-column gap-2 mt-3 pt-2 border-top" style="font-size: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background: #10b981;"></span>
                        <span>Đã công bố (duyệt xong)</span>
                    </span>
                    <span class="fw-bold text-success">{{ $soDeTaiDaCongBo }} ({{ $soDeTaiTong > 0 ? round(($soDeTaiDaCongBo / $soDeTaiTong) * 100) : 0 }}%)</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background: #f59e0b;"></span>
                        <span>Chờ duyệt cấp Bộ môn</span>
                    </span>
                    <span class="fw-bold text-warning">{{ $soDeTaiChoDuyetBM }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background: #0ea5e9;"></span>
                        <span>Chờ duyệt cấp Khoa</span>
                    </span>
                    <span class="fw-bold text-info">{{ $soDeTaiChoDuyetKhoa }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background: #8b5cf6;"></span>
                        <span>Phản biện đề cương</span>
                    </span>
                    <span class="fw-bold" style="color: #8b5cf6;">{{ $soDeTaiPhanBien }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 3: BẢNG THỐNG KÊ TỔNG HỢP THEO BỘ MÔN ── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <div>
            <h6 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
                <i class="fa-solid fa-sitemap me-2 text-primary"></i> Bảng Thống Kê Số Liệu Khóa Luận Theo Từng Bộ Môn
            </h6>
            <span class="text-muted small">Số liệu cán bộ, đề tài đề xuất, tỷ lệ duyệt và số lượng nhóm thực hiện</span>
        </div>
        <a href="{{ route('admin.dashboard.export_tong_hop', ['hoc_ky' => $selectedHocKy]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-file-excel me-2"></i> Tải bảng thống kê Excel
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4" style="width: 50px;">STT</th>
                    <th style="width: 120px;">Mã Bộ Môn</th>
                    <th>Tên Bộ Môn Chuyên Ngành</th>
                    <th class="text-center" style="width: 120px;">Số Giảng Viên</th>
                    <th class="text-center" style="width: 130px;">Đề Tài Đề Xuất</th>
                    <th class="text-center" style="width: 140px;">Đề Tài Đã Công Bố</th>
                    <th class="text-center" style="width: 130px;">Nhóm Sinh Viên</th>
                    <th class="text-center pe-4" style="width: 130px;">Tỷ Lệ Duyệt</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tGV = 0; $tDT = 0; $tDTCongBo = 0; $tNhom = 0;
                @endphp
                @forelse($thongKeBoMon as $index => $bm)
                @php
                    $tGV += $bm['so_gv'];
                    $tDT += $bm['so_de_tai'];
                    $tDTCongBo += $bm['so_da_cong_bo'];
                    $tNhom += $bm['so_nhom'];
                @endphp
                <tr>
                    <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-3 fw-bold">
                            {{ $bm['ma_bm'] }}
                        </span>
                    </td>
                    <td class="fw-semibold text-dark">{{ $bm['ten_bm'] }}</td>
                    <td class="text-center">{{ $bm['so_gv'] }} cán bộ</td>
                    <td class="text-center fw-bold text-dark">{{ $bm['so_de_tai'] }}</td>
                    <td class="text-center fw-bold text-success">{{ $bm['so_da_cong_bo'] }}</td>
                    <td class="text-center fw-bold text-primary">{{ $bm['so_nhom'] }} nhóm</td>
                    <td class="text-center pe-4">
                        <span class="badge {{ $bm['ty_le_duyet'] >= 70 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-3 py-1 fw-bold">
                            {{ $bm['ty_le_duyet'] }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Chưa có dữ liệu bộ môn.</td>
                </tr>
                @endforelse
                <tr class="table-light fw-bold">
                    <td colspan="3" class="ps-4 text-uppercase">Tổng Cộng Toàn Khoa</td>
                    <td class="text-center">{{ $tGV }} cán bộ</td>
                    <td class="text-center">{{ $tDT }}</td>
                    <td class="text-center text-success">{{ $tDTCongBo }}</td>
                    <td class="text-center text-primary">{{ $tNhom }} nhóm</td>
                    <td class="text-center pe-4 text-success">
                        {{ $tDT > 0 ? round(($tDTCongBo / $tDT) * 100) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ── HÀNG 5: HAI BẢNG GIÁM SÁT THỜI GIAN THỰC (NHÓM MỚI NHẤT & ĐỀ TÀI CẦN DUYỆT) ── --}}
<div class="row g-4 mb-4">
    {{-- Cột trái: Danh sách nhóm mới nhất --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.98rem;">
                        <i class="fa-solid fa-users text-primary me-2"></i> Nhóm Khóa Luận Hoạt Động Mới Nhất
                    </h6>
                </div>
                <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center">
                    Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 100px;">Mã Nhóm</th>
                            <th>Trưởng Nhóm &amp; Thành Viên</th>
                            <th>Đề Tài &amp; GVHD</th>
                            <th class="text-center" style="width: 120px;">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nhomMoiNhat as $nhom)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-3 fw-bold">
                                    {{ $nhom->TenNhom ?: $nhom->MaNhom }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $nhom->truongNhom->HoTen ?? 'Chưa chọn trưởng nhóm' }}</div>
                                <div class="text-muted small">{{ $nhom->sinhViens->count() ?: 1 }} thành viên</div>
                            </td>
                            <td>
                                @if($nhom->deTai)
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 240px;" title="{{ $nhom->deTai->TenDeTai }}">
                                        {{ $nhom->deTai->TenDeTai }}
                                    </div>
                                    <div class="text-muted small">
                                        GVHD: {{ $nhom->deTai->giangVien?->HoTen ?? 'Chưa phân công' }}
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">(Chưa đăng ký đề tài)</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($nhom->TrangThai === 'Đã duyệt' || $nhom->TrangThai === 'Đang hoạt động')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        {{ $nhom->TrangThai }}
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                        {{ $nhom->TrangThai }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Chưa có nhóm đồ án nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Cột phải: Đề tài cần xét duyệt / xử lý khẩn cấp --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.98rem;">
                        <i class="fa-solid fa-hourglass-half text-warning me-2"></i> Đề Tài Chờ Duyệt / Cần Lưu Ý
                    </h6>
                </div>
                <a href="{{ route('admin.duyet_detai.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center">
                    Xử lý ngay <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="p-3">
                @forelse($deTaiCanXuLy as $dt)
                <div class="p-3 mb-2 rounded-3 border bg-light d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <span class="badge bg-secondary-subtle text-secondary border small">{{ $dt->MaDeTai }}</span>
                        @if($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill" style="font-size: 11px;">Chờ TBM duyệt</span>
                        @elseif($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                            <span class="badge bg-info-subtle text-info-emphasis rounded-pill" style="font-size: 11px;">Chờ Khoa duyệt</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 11px;">{{ $dt->TrangThai }}</span>
                        @endif
                    </div>
                    <div class="fw-bold text-dark text-truncate mt-1" style="font-size: 13px;" title="{{ $dt->TenDeTai }}">
                        {{ $dt->TenDeTai }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-1" style="font-size: 11.5px;">
                        <span>GV đề xuất: <strong>{{ $dt->giangVien?->HoTen ?? 'Chưa rõ' }}</strong></span>
                        <span>{{ $dt->boMon?->TenBoMon ?? '' }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-circle-check fs-2 text-success opacity-50 mb-2 d-block me-2"></i>
                    Không có đề tài nào đang bị nghẽn xét duyệt.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── 1. Biểu đồ Cột: So sánh Đề tài & Nhóm theo từng Bộ môn ──
    const ctxBm = document.getElementById('chartBoMonComparison');
    if (ctxBm) {
        const bmLabels = {!! json_encode(array_column($thongKeBoMon, 'ten_bm')) !!};
        const bmDeTaiTong = {!! json_encode(array_column($thongKeBoMon, 'so_de_tai')) !!};
        const bmDeTaiCongBo = {!! json_encode(array_column($thongKeBoMon, 'so_da_cong_bo')) !!};
        const bmNhom = {!! json_encode(array_column($thongKeBoMon, 'so_nhom')) !!};

        new Chart(ctxBm.getContext('2d'), {
            type: 'bar',
            data: {
                labels: bmLabels,
                datasets: [
                    {
                        label: 'Đề tài đề xuất',
                        data: bmDeTaiTong,
                        backgroundColor: '#cbd5e1',
                        borderRadius: 6,
                    },
                    {
                        label: 'Đề tài đã công bố',
                        data: bmDeTaiCongBo,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                    },
                    {
                        label: 'Nhóm sinh viên',
                        data: bmNhom,
                        backgroundColor: '#0072ce',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            callback: function(val, index) {
                                const fullLabel = bmLabels[index] || '';
                                return fullLabel.replace('Bộ môn ', '');
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 11 } },
                        grid: { color: '#f1f5f9' }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 12, weight: 'bold' }, usePointStyle: true, padding: 15 }
                    }
                }
            }
        });
    }

    // ── 2. Biểu đồ Donut: Cơ cấu trạng thái đề tài ──
    const ctxStatus = document.getElementById('chartDeTaiStatus');
    if (ctxStatus) {
        new Chart(ctxStatus.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Đã công bố', 'Chờ duyệt BM', 'Chờ duyệt Khoa', 'Phản biện đề cương', 'Cần chỉnh sửa'],
                datasets: [{
                    data: [
                        {{ $soDeTaiDaCongBo }},
                        {{ $soDeTaiChoDuyetBM }},
                        {{ $soDeTaiChoDuyetKhoa }},
                        {{ $soDeTaiPhanBien }},
                        {{ $soDeTaiChinhSua }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#8b5cf6', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + ' đề tài';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

@endsection
