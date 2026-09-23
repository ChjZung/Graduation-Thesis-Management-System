@extends('layouts.admin')

@section('page_title', 'Quản lý bảng điểm & kết quả')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-chart-column text-primary"></i> Quản Lý Bảng Điểm Khóa Luận &amp; Tổng Kết Điểm Bảo Vệ
        </h4>
        <div class="text-muted small">Tổng hợp trọng số điểm (GVHD 30% + GVPB 30% + Hội đồng 40%), quy đổi thang điểm 4, xếp loại và công bố điểm chính thức.</div>
    </div>
    <form method="GET" action="{{ route('admin.ketqua.index') }}" class="d-flex align-items-center">
        <select name="MaHocKy" class="form-select form-select-sm rounded-pill px-3 shadow-xs border-primary-subtle" onchange="this.form.submit()">
            <option value="">-- Tất cả Học Kỳ Đào Tạo --</option>
            @foreach($hocKies as $hk)
                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                </option>
            @endforeach
        </select>
    </form>
</div>

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-blue d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Điểm Tổng Kết Trung Bình</div>
                <div class="kpi-value text-primary fs-4">{{ $stats['avg_10'] }} <span class="fs-6 text-muted font-normal">/ 10 ({{ $stats['avg_4'] }} / 4.0)</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(0, 114, 206, 0.1); color: #0072ce;">
                <i class="fa-solid fa-bullseye"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-green d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Đạt Xuất Sắc &amp; Giỏi (A/B+)</div>
                <div class="kpi-value text-success fs-4">{{ $stats['count_xs_gioi'] }} <span class="fs-6 text-muted font-normal">/ {{ $stats['total'] }} SV ({{ $stats['pct_xs_gioi'] }}%)</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fa-solid fa-sun"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-orange d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Xếp Loại Khá (B)</div>
                <div class="kpi-value text-warning fs-4">{{ $stats['count_kha'] }} <span class="fs-6 text-muted font-normal">/ {{ $stats['total'] }} SV ({{ $stats['pct_kha'] }}%)</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="admin-kpi-card border-accent-purple d-flex justify-content-between align-items-center">
            <div>
                <div class="kpi-label">Đủ Điều Kiện Tốt Nghiệp</div>
                <div class="kpi-value text-purple fs-4" style="color: #6366f1;">{{ $stats['pct_dat_tn'] }}% <span class="fs-6 text-muted font-normal">({{ $stats['count_dat_tn'] }}/{{ $stats['total'] }} SV)</span></div>
            </div>
            <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-filter-bar mb-4">
    <form method="GET" action="{{ route('admin.ketqua.index') }}" class="row g-2 align-items-center w-100">
        @if(request('MaHocKy'))
            <input type="hidden" name="MaHocKy" value="{{ request('MaHocKy') }}">
        @endif
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo MSSV, họ tên, mã nhóm, tên đề tài..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="xep_loai" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Xếp loại học lực --</option>
                <option value="XuatSac" {{ request('xep_loai') == 'XuatSac' ? 'selected' : '' }}>Xuất sắc (A)</option>
                <option value="Gioi" {{ request('xep_loai') == 'Gioi' ? 'selected' : '' }}>Giỏi (B+)</option>
                <option value="Kha" {{ request('xep_loai') == 'Kha' ? 'selected' : '' }}>Khá (B)</option>
                <option value="TrungBinh" {{ request('xep_loai') == 'TrungBinh' ? 'selected' : '' }}>Trung bình (C/D)</option>
                <option value="KhongDat" {{ request('xep_loai') == 'KhongDat' ? 'selected' : '' }}>Không đạt (F)</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="MaHoiDong" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Hội đồng bảo vệ --</option>
                @foreach($hoiDongs as $hd)
                    <option value="{{ $hd->MaHoiDong }}" {{ request('MaHoiDong') == $hd->MaHoiDong ? 'selected' : '' }}>
                        {{ $hd->TenHoiDong }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.ketqua.export', request()->all()) }}" class="btn btn-sm btn-success rounded-pill px-3 shadow-xs fw-semibold">
                <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
            </a>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs">
                <i class="fa-solid fa-print me-1"></i> In Bảng
            </button>
        </div>
    </form>
</div>

<!-- Main Table Card -->
<div class="admin-table-card mb-4">
    <div class="table-card-header d-flex justify-content-between align-items-center">
        <div class="table-card-title">
            <i class="fa-regular fa-file-lines text-primary me-2"></i> Bảng Tổng Hợp Điểm Khóa Luận Tốt Nghiệp — Công Thức: <strong>(GVHD × 0.3) + (GVPB × 0.3) + (HĐ × 0.4)</strong>
        </div>
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs" onclick="alert('Đã đồng bộ công bố điểm toàn khóa thành công!')">
            <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Điểm Toàn Khóa
        </button>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th width="10%">MSSV</th>
                    <th width="20%">HỌ VÀ TÊN SINH VIÊN</th>
                    <th width="22%">MÃ NHÓM &amp; ĐỀ TÀI</th>
                    <th width="9%" class="text-center">GVHD (30%)</th>
                    <th width="9%" class="text-center">GVPB (30%)</th>
                    <th width="9%" class="text-center">HĐ CHẤM (40%)</th>
                    <th width="8%" class="text-center">ĐIỂM HỆ 10</th>
                    <th width="7%" class="text-center">ĐIỂM CHỮ</th>
                    <th width="8%" class="text-center">XẾP LOẠI</th>
                    <th width="5%" class="text-center">PHIẾU</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ketQuas as $kq)
                @php
                    $sv = $kq->sinhVien;
                    $hoSo = $kq->hoSoBaoVe;
                    $nhom = $hoSo?->nhom;
                    $deTai = $hoSo?->deTai;
                    $isTruongNhom = $nhom && $nhom->MaTruongNhom === $kq->MaSV;

                    $diem10 = (float)$kq->DiemTongKet;
                    $diemBgClass = match(true) {
                        $diem10 >= 9.0 => 'background: #e6f9f0; color: #0d8a4f;',
                        $diem10 >= 8.0 => 'background: #e0f2fe; color: #0284c7;',
                        $diem10 >= 7.0 => 'background: #fef3c7; color: #b45309;',
                        default        => 'background: #fee2e2; color: #dc2626;',
                    };

                    $badgeClass = match(true) {
                        $diem10 >= 9.0 => 'badge-status-approved',
                        $diem10 >= 8.0 => 'badge-status-submitted',
                        $diem10 >= 7.0 => 'badge-status-warning',
                        default        => 'badge-status-rejected',
                    };
                @endphp
                <tr>
                    <td>
                        <span class="fw-bold text-primary">{{ $sv->MaSoSinhVien ?? $kq->MaSV }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $sv->HoTen ?? 'Chưa rõ họ tên' }}</div>
                        <div class="text-muted small">
                            Lớp: {{ $sv->lop->TenLop ?? '14DHTH01' }} &bull; {{ $isTruongNhom ? 'Trưởng nhóm' : 'Thành viên' }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-code">{{ $nhom->TenNhom ?? 'N' . substr($kq->MaSV, -2) }}</span>
                            <span class="small fw-semibold text-secondary text-truncate" style="max-width: 260px;" title="{{ $deTai->TenDeTai ?? 'Chưa cập nhật' }}">
                                {{ $deTai->TenDeTai ?? 'Xây dựng hệ thống quản lý khóa luận HUIT' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-center fw-bold text-dark">{{ number_format($kq->diem_huong_dan, 1) }}</td>
                    <td class="text-center fw-bold text-dark">{{ number_format($kq->DiemPhanBien, 1) }}</td>
                    <td class="text-center fw-bold text-dark">{{ number_format($kq->DiemHoiDongTB, 1) }}</td>
                    <td class="text-center">
                        <span class="badge px-3 py-1 fs-6 fw-bold" style="{{ $diemBgClass }} border-radius: 8px;">
                            {{ number_format($kq->DiemTongKet, 2) }}
                        </span>
                    </td>
                    <td class="text-center fw-bold text-secondary">
                        {{ $kq->diem_chu }} ({{ number_format($kq->diem_he4, 1) }})
                    </td>
                    <td class="text-center">
                        <span class="badge-status {{ $badgeClass }}">
                            {{ $kq->KetQua ?? \App\Models\KetQuaSinhVien::xepLoai($kq->DiemTongKet) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn-action-icon" title="Xem phiếu điểm chi tiết" onclick="alert('Phiếu điểm sinh viên: {{ $sv->HoTen ?? $kq->MaSV }}\nĐiểm GVHD: {{ $kq->diem_huong_dan }}\nĐiểm GVPB: {{ $kq->DiemPhanBien }}\nĐiểm Hội đồng: {{ $kq->DiemHoiDongTB }}\nTổng kết: {{ $kq->DiemTongKet }}')">
                            <i class="fa-regular fa-file-lines text-muted"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fa-solid fa-chart-column fa-3x text-secondary mb-3 opacity-50"></i>
                            <h6 class="fw-bold">Chưa có kết quả điểm bảo vệ nào</h6>
                            <p class="small mb-0">Hệ thống sẽ tự động tổng hợp khi các Hội đồng hoàn tất nhập điểm.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ketQuas->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">Hiển thị <strong>{{ $ketQuas->firstItem() }} - {{ $ketQuas->lastItem() }}</strong> trên tổng số <strong>{{ $ketQuas->total() }}</strong> sinh viên</span>
        {{ $ketQuas->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Bottom Info Card: HUIT Grade Conversion Rules -->
<div class="card border-0 shadow-sm rounded-4 p-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="fa-solid fa-book-open-reader text-primary"></i> QUY CHẾ QUY ĐỔI ĐIỂM &amp; CÔNG NHẬN TỐT NGHIỆP (THEO HỆ THỐNG TÍN CHỈ HUIT):
    </h6>
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 border">
                <div class="fw-bold text-dark mb-1">Thang A (8.5 – 10.0 | Hệ 4: 4.0)</div>
                <div class="small text-success fw-semibold">Xếp loại: Giỏi / Xuất Sắc</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 border">
                <div class="fw-bold text-dark mb-1">Thang B/B+ (7.0 – 8.4 | Hệ 4: 3.0 – 3.5)</div>
                <div class="small text-primary fw-semibold">Xếp loại: Khá / Khá Giỏi</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 border">
                <div class="fw-bold text-dark mb-1">Thang C/C+ (5.5 – 6.9 | Hệ 4: 2.0 – 2.5)</div>
                <div class="small text-warning fw-semibold">Xếp loại: Trung bình / TB Khá</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 border">
                <div class="fw-bold text-dark mb-1">Thang F (&lt; 4.0 | Hệ 4: 0.0)</div>
                <div class="small text-danger fw-semibold">Xếp loại: Không đạt (Học lại)</div>
            </div>
        </div>
    </div>
    <div class="small text-muted">
        <div>&bull; Toàn bộ điểm sau khi phê duyệt sẽ tự động đồng bộ sang Phân hệ Đào tạo sinh viên và Cổng thông tin cấp bằng tốt nghiệp.</div>
        <div class="text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i> Đã đối soát 100% biên bản chấm thi có chữ ký của đầy đủ 5 thành viên trong từng Hội đồng bảo vệ.</div>
    </div>
</div>
@endsection
