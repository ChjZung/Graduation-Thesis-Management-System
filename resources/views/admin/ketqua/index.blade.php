@extends('layouts.admin')

@section('page_title', 'Quản Lý Bảng Điểm & Kết Quả Khóa Luận')

@section('content')
<div class="container-fluid px-0">


    <!-- Header Section (Đồng bộ chuẩn Học Kỳ) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">
                <i class="fa-solid fa-chart-column text-primary me-2"></i>Quản Lý Bảng Điểm &amp; Kết Quả Khóa Luận
            </h4>
            <p class="text-muted small mb-0">Tổng hợp điểm đánh giá của Hội đồng bảo vệ (100%), quy đổi thang điểm 4, xếp loại học lực và công bố điểm chính thức.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.ketqua.export', request()->all()) }}" class="btn btn-outline-success shadow-xs fw-semibold">
                <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary shadow-xs">
                <i class="fa-solid fa-print me-1"></i> In Bảng Điểm
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards: Cân đối, đồng đều, icon chuẩn, typography đồng nhất chuẩn Học Kỳ -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Điểm Trung Bình Hội Đồng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-bullseye fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['avg_10'] }}</span>
                    <span class="small text-muted fw-medium">/ 10 (Hệ 4: {{ $stats['avg_4'] }})</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đạt Xuất Sắc &amp; Giỏi</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-award fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $stats['count_xs_gioi'] }}</span>
                    <span class="small text-muted fw-medium">SV ({{ $stats['pct_xs_gioi'] }}%)</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Xếp Loại Khá</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-pen-to-square fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $stats['count_kha'] }}</span>
                    <span class="small text-muted fw-medium">SV ({{ $stats['pct_kha'] }}%)</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đủ ĐK Tốt Nghiệp</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-graduation-cap fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-primary lh-1">{{ $stats['pct_dat_tn'] }}%</span>
                    <span class="small text-muted fw-medium">({{ $stats['count_dat_tn'] }}/{{ $stats['total'] }} SV)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Đồng bộ chuẩn Học Kỳ) -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('admin.ketqua.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 200px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo MSSV, họ tên, mã nhóm...">
                </div>
                <select name="MaHocKy" class="form-select" style="min-width: 270px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>
                <select name="xep_loai" class="form-select" style="min-width: 190px;" onchange="this.form.submit()">
                    <option value="">-- Xếp loại --</option>
                    <option value="XuatSac" {{ request('xep_loai') == 'XuatSac' ? 'selected' : '' }}>Xuất sắc (A)</option>
                    <option value="Gioi" {{ request('xep_loai') == 'Gioi' ? 'selected' : '' }}>Giỏi (B+)</option>
                    <option value="Kha" {{ request('xep_loai') == 'Kha' ? 'selected' : '' }}>Khá (B)</option>
                    <option value="TrungBinh" {{ request('xep_loai') == 'TrungBinh' ? 'selected' : '' }}>Trung bình (C/D)</option>
                    <option value="KhongDat" {{ request('xep_loai') == 'KhongDat' ? 'selected' : '' }}>Không đạt (F)</option>
                </select>
                <select name="MaHoiDong" class="form-select" style="min-width: 220px;" onchange="this.form.submit()">
                    <option value="">-- Hội đồng bảo vệ --</option>
                    @foreach($hoiDongs as $hd)
                        <option value="{{ $hd->MaHoiDong }}" {{ request('MaHoiDong') == $hd->MaHoiDong ? 'selected' : '' }}>
                            {{ $hd->TenHoiDong }}
                        </option>
                    @endforeach
                </select>
                @if(request('search') || request('MaHocKy') || request('xep_loai') || request('MaHoiDong'))
                    <a href="{{ route('admin.ketqua.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.ketqua.export', request()->all()) }}" class="btn btn-success shadow-xs fw-semibold">
                    <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Main Table Card (Đồng bộ chuẩn Học Kỳ) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Bảng Tổng Hợp Điểm Khóa Luận — Điểm Đánh Giá Hội Đồng Bảo Vệ (100%)</span>
            <span class="small text-muted">Tổng số: <strong>{{ $ketQuas->total() }}</strong> sinh viên</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" style="width: 120px;">MSSV</th>
                            <th class="py-3" style="min-width: 200px;">HỌ VÀ TÊN SINH VIÊN</th>
                            <th class="py-3" style="min-width: 240px;">NHÓM &amp; ĐỀ TÀI</th>
                            <th class="py-3 text-center" style="width: 130px;">LOẠI KHÓA LUẬN</th>
                            <th class="py-3 text-center" style="width: 150px;">HỘI ĐỒNG (100%)</th>
                            <th class="py-3 text-center" style="width: 120px;">ĐIỂM CHỮ</th>
                            <th class="py-3 text-center" style="width: 140px;">XẾP LOẠI</th>
                            <th class="pe-4 py-3 text-center" style="width: 90px;">PHIẾU CHẤM</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($ketQuas as $kq)
                        @php
                            $sv = $kq->sinhVien;
                            $hoSo = $kq->hoSoBaoVe;
                            $nhom = $hoSo?->nhom;
                            $deTai = $hoSo?->deTai;
                            $isTruongNhom = $nhom && $nhom->MaTruongNhom === $kq->MaSV;

                            $diem10 = (float)($kq->DiemTongKet ?? $kq->DiemHoiDongTB ?? 0);
                            $diemBadgeClass = match(true) {
                                $diem10 >= 8.5 => 'bg-success-subtle text-success border border-success-subtle',
                                $diem10 >= 7.0 => 'bg-primary-subtle text-primary border border-primary-subtle',
                                $diem10 >= 5.0 => 'bg-warning-subtle text-warning border border-warning-subtle',
                                default        => 'bg-danger-subtle text-danger border border-danger-subtle',
                            };

                            $loai = $kq->LoaiKhoaLuan ?? (str_contains(mb_strtolower($deTai?->TenDeTai ?? ''), 'kỹ sư') ? 'KLKS' : 'KLCN');
                        @endphp
                        <tr>
                            {{-- MSSV --}}
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $sv->MaSoSinhVien ?? $kq->MaSV }}</span>
                            </td>

                            {{-- Họ tên SV --}}
                            <td class="py-3">
                                <div class="fw-bold text-dark">{{ $sv->HoTen ?? 'Chưa rõ họ tên' }}</div>
                                <div class="small text-muted mt-0.5">
                                    Lớp: {{ $sv->lop->TenLop ?? '14DHTH01' }} &bull; {{ $isTruongNhom ? 'Trưởng nhóm' : 'Thành viên' }}
                                </div>
                            </td>

                            {{-- Nhóm & Đề tài --}}
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 0.72rem;">{{ $nhom->TenNhom ?? 'Nhóm SV' }}</span>
                                </div>
                                <div class="small text-dark fw-semibold text-truncate" style="max-width: 250px;" title="{{ $deTai->TenDeTai ?? 'Chưa cập nhật' }}">
                                    {{ $deTai->TenDeTai ?? 'Chưa có tên đề tài' }}
                                </div>
                            </td>

                            {{-- Loại khóa luận --}}
                            <td class="text-center py-3">
                                @if($loai === 'KLKS')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-microchip me-1"></i> Kỹ sư (KLKS)
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-graduation-cap me-1"></i> Cử nhân (KLCN)
                                    </span>
                                @endif
                            </td>

                            {{-- Điểm Hội Đồng (100%) --}}
                            <td class="text-center py-3">
                                <span class="badge {{ $diemBadgeClass }} rounded-pill px-3 py-1.5 fs-6 fw-bold">
                                    {{ number_format($diem10, 2) }}
                                </span>
                            </td>

                            {{-- Điểm Chữ & Hệ 4 --}}
                            <td class="text-center py-3">
                                <div class="fw-bold text-dark">{{ $kq->diem_chu }}</div>
                                <div class="small text-muted">Hệ 4: {{ number_format($kq->diem_he4, 1) }}</div>
                            </td>

                            {{-- Xếp Loại --}}
                            <td class="text-center py-3">
                                @php
                                    $xlName = $kq->KetQua ?? \App\Models\KetQuaSinhVien::xepLoai($diem10);
                                    $xlBadge = match(true) {
                                        str_contains($xlName, 'Xuất sắc') || str_contains($xlName, 'Giỏi') => 'bg-success-subtle text-success border border-success-subtle',
                                        str_contains($xlName, 'Khá') => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        str_contains($xlName, 'Trung bình') => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        default => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    };
                                @endphp
                                <span class="badge {{ $xlBadge }} rounded-pill px-3 py-1.5 fw-semibold">
                                    {{ $xlName }}
                                </span>
                            </td>

                            <td class="pe-4 text-center py-3">
                                @php
                                    $loaiText = ($loai === 'KLKS') ? 'Khóa luận Kỹ sư' : 'Khóa luận Cử nhân';
                                    $alertMsg = "Sinh viên: " . ($sv->HoTen ?? $kq->MaSV) . "\\nMSSV: " . ($sv->MaSoSinhVien ?? $kq->MaSV) . "\\nĐiểm Hội đồng: " . number_format($diem10, 2) . "\\nĐiểm chữ: " . $kq->diem_chu . " (Hệ 4: " . $kq->diem_he4 . ")\\nXếp loại: " . $xlName . "\\nLoại: " . $loaiText;
                                @endphp
                                <button type="button" class="btn btn-sm btn-light text-primary btn-action-circle border" title="Xem phiếu điểm chi tiết" onclick="alert('{{ $alertMsg }}')">
                                    <i class="fa-regular fa-file-lines"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-chart-column text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Chưa có kết quả điểm bảo vệ nào</h6>
                                    <p class="small text-muted mb-0">Hệ thống sẽ tự động tổng hợp khi các Hội đồng hoàn tất nhập điểm.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $ketQuas->firstItem() ?? 0 }}–{{ $ketQuas->lastItem() ?? 0 }} trên tổng số {{ $ketQuas->total() }} sinh viên hệ thống
            </span>
            @if($ketQuas->hasPages())
                <div>
                    {{ $ketQuas->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Info Card: Quy chế quy đổi tín chỉ HUIT -->
    <div class="card border-0 shadow-sm rounded-3 p-4 bg-white mb-4">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-book-open-reader text-primary"></i> QUY CHẾ ĐÁNH GIÁ &amp; CÔNG NHẬN TỐT NGHIỆP (KHOA CÔNG NGHỆ THÔNG TIN — HUIT):
        </h6>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="fw-bold text-dark mb-1">Thang A (8.5 – 10.0 | Hệ 4: 3.7 – 4.0)</div>
                    <div class="small text-success fw-semibold">Xếp loại: Giỏi / Xuất Sắc</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="fw-bold text-dark mb-1">Thang B/B+ (7.0 – 8.4 | Hệ 4: 3.0 – 3.5)</div>
                    <div class="small text-primary fw-semibold">Xếp loại: Khá / Khá Giỏi</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="fw-bold text-dark mb-1">Thang C/C+ (5.5 – 6.9 | Hệ 4: 2.0 – 2.5)</div>
                    <div class="small text-warning fw-semibold">Xếp loại: Trung bình / TB Khá</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="fw-bold text-dark mb-1">Thang F (&lt; 4.0 | Hệ 4: 0.0)</div>
                    <div class="small text-danger fw-semibold">Xếp loại: Không đạt (Học lại)</div>
                </div>
            </div>
        </div>
        <div class="small text-muted">
            <div>&bull; <strong>Quy chế tính điểm:</strong> Điểm khóa luận tốt nghiệp được tính <strong>100% từ điểm đánh giá của Hội đồng bảo vệ</strong> theo phiếu chấm điểm chuẩn hóa KLCN hoặc KLKS.</div>
            <div class="text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i> Điểm tổng kết tự động đồng bộ sang Phân hệ Đào tạo sinh viên sau khi hoàn tất bảo vệ.</div>
        </div>
    </div>
</div>
@endsection
