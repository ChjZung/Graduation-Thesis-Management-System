@extends('layouts.admin')
@section('title', 'Dashboard Quản Trị')
@section('page_title', 'Dashboard')

@section('content')

{{-- ── HÀNG 1: 4 CARD KPI CHUẨN FIGMA ── --}}
<div class="row g-3 mb-4">
    <!-- Sinh Viên -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Sinh Viên</div>
                    <div class="kpi-value">{{ $soSinhVien }}</div>
                    <div class="kpi-subtext text-success fw-bold">
                        <i class="fa-solid fa-arrow-up me-1"></i>100% đủ đ/k
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Giảng Viên -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Giảng Viên</div>
                    <div class="kpi-value">{{ $soGiangVien }}</div>
                    <div class="kpi-subtext text-muted">Đã mở hướng dẫn</div>
                </div>
                <div class="kpi-icon-wrap" style="background: #dcfce7; color: #16a34a;">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Đề Tài Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-amber">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Đề Tài Khóa Luận</div>
                    <div class="kpi-value">{{ $soDeTai }}</div>
                    <div class="kpi-subtext" style="color: #d97706; font-weight: 600;">
                        {{ $deTaiDaDuyet }} đề tài đã duyệt
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Nhóm Khóa Luận -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="admin-kpi-card border-accent-purple">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-title">Nhóm Khóa Luận</div>
                    <div class="kpi-value">{{ $soNhom }}</div>
                    <div class="kpi-subtext" style="color: #6366f1; font-weight: 600;">
                        Đã chốt danh sách
                    </div>
                </div>
                <div class="kpi-icon-wrap" style="background: #ede9fe; color: #7c3aed;">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 2: XẾP LOẠI KẾT QUẢ & TIẾN ĐỘ ĐỀ TÀI / HỘI ĐỒNG ── --}}
<div class="row g-4 mb-4">

    {{-- Cột trái: Donut chart Xếp loại --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.96rem;">
                    🎯 Xếp Loại Kết Quả Khóa Luận
                </h6>
                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill" style="font-size: 0.78rem;">
                    {{ $tongXepLoai > 0 ? $tongXepLoai : $soSinhVien }} SV
                </span>
            </div>

            <div class="row align-items-center my-auto py-2">
                {{-- Donut Chart Container --}}
                <div class="col-12 col-sm-6 text-center position-relative d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div style="width: 190px; height: 190px; position: relative;">
                        <canvas id="chartXepLoaiFigma"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                            <div class="fw-bold text-dark lh-1" style="font-size: 1.85rem;">{{ $tyLeDatChuan }}%</div>
                            <div class="text-muted fw-semibold" style="font-size: 0.75rem;">Đạt chuẩn</div>
                        </div>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="col-12 col-sm-6 mt-3 mt-sm-0 ps-sm-3">
                    @php
                        $totalStudentsGraded = max($tongXepLoai, 1);
                        $pctXuatSac = round((($xepLoaiData['Xuất sắc'] ?? 0) / $totalStudentsGraded) * 100);
                        $pctGioi    = round((($xepLoaiData['Giỏi'] ?? 0) / $totalStudentsGraded) * 100);
                        $pctKha     = round((($xepLoaiData['Khá'] ?? 0) / $totalStudentsGraded) * 100);
                        $pctTB      = round((($xepLoaiData['Trung bình'] ?? 0) / $totalStudentsGraded) * 100);
                        if ($tongXepLoai == 0) {
                            $pctXuatSac = 25; $pctGioi = 45; $pctKha = 20; $pctTB = 10;
                        }
                    @endphp
                    <div class="d-flex flex-column gap-2" style="font-size: 0.83rem;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#10b981;"></span>
                                <span class="text-secondary">Xuất sắc</span>
                            </span>
                            <span class="fw-bold text-dark">{{ $xepLoaiData['Xuất sắc'] ?? 0 }} SV ({{ $pctXuatSac }}%)</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#0ea5e9;"></span>
                                <span class="text-secondary">Giỏi</span>
                            </span>
                            <span class="fw-bold text-dark">{{ $xepLoaiData['Giỏi'] ?? 0 }} SV ({{ $pctGioi }}%)</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#f59e0b;"></span>
                                <span class="text-secondary">Khá</span>
                            </span>
                            <span class="fw-bold text-dark">{{ $xepLoaiData['Khá'] ?? 0 }} SV ({{ $pctKha }}%)</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#8b5cf6;"></span>
                                <span class="text-secondary">Trung bình</span>
                            </span>
                            <span class="fw-bold text-dark">{{ $xepLoaiData['Trung bình'] ?? 0 }} SV ({{ $pctTB }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Banner --}}
            <div class="mt-3 p-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-size: 0.82rem; font-weight: 500;">
                <i class="fa-solid fa-circle-check text-success"></i>
                <span>100% sinh viên hoàn thành đúng niên độ</span>
            </div>
        </div>
    </div>

    {{-- Cột phải: Đăng Ký Đề Tài & Phân Công HĐ --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="border: 1px solid #e2e8f0 !important;">
            <h6 class="fw-bold mb-3 text-dark" style="font-size: 0.96rem;">
                📁 Đăng Ký Đề Tài & Phân Công HĐ
            </h6>

            {{-- Block 1: Đã duyệt chính thức --}}
            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-2" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                <span style="font-size: 0.86rem; font-weight: 600; color: #166534;">Đề tài đã xét duyệt chính thức</span>
                <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 0.88rem; font-weight: 700;">
                    {{ $dkDaDuyet }} đề tài
                </span>
            </div>

            {{-- Block 2: Chờ xét duyệt --}}
            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-2" style="background: #fefce8; border: 1px solid #fef08a;">
                <span style="font-size: 0.86rem; font-weight: 600; color: #854d0e;">Đề tài chờ Giáo vụ xét duyệt</span>
                <span class="badge" style="background: #fef9c3; color: #a16207; font-size: 0.88rem; font-weight: 700;">
                    {{ $dkChoDuyet }} đề tài
                </span>
            </div>

            {{-- Block 3: Yêu cầu sửa --}}
            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3" style="background: #fef2f2; border: 1px solid #fecaca;">
                <span style="font-size: 0.86rem; font-weight: 600; color: #991b1b;">Đề tài yêu cầu chỉnh sửa lại</span>
                <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 0.88rem; font-weight: 700;">
                    {{ $dkTuChoi }} đề tài
                </span>
            </div>

            <hr class="my-2" style="border-color: #f1f5f9;">

            {{-- Tóm tắt công tác chuẩn bị Hội đồng --}}
            <div class="mt-2">
                <div class="fw-bold mb-2" style="font-size: 0.82rem; color: #334155;">Công tác chuẩn bị Hội đồng:</div>
                <div class="mb-2" style="font-size: 0.82rem; color: #475569;">
                    <span class="text-muted">•</span> Hồ sơ bảo vệ hợp lệ (Turnitin &lt; 20%): 
                    <strong style="color: #0284c7;">{{ $hoSoHopLe }} hồ sơ</strong>
                </div>
                <div style="font-size: 0.82rem; color: #475569;">
                    <span class="text-muted">•</span> Đã phân công Hội đồng chấm: 
                    <strong style="color: #0f172a;">{{ $soHoiDong }} HĐ</strong>
                    @if($soHoiDong > 0)
                        <span class="badge bg-light text-primary border ms-1">HĐ01</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 3: DANH SÁCH NHÓM KHÓA LUẬN ĐĂNG KÝ MỚI NHẤT ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-solid fa-users text-primary"></i>
            Danh Sách Nhóm Khóa Luận Đăng Ký Mới Nhất
        </h5>
        <a href="{{ route('admin.theodoi.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
            Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 140px;">Mã Nhóm</th>
                    <th style="min-width: 200px;">Trưởng Nhóm &amp; Thành Viên</th>
                    <th style="min-width: 280px;">Đề Tài Khóa Luận</th>
                    <th class="text-center" style="width: 160px;">Trạng Thái</th>
                    <th class="text-end" style="width: 120px;">Ngày Tạo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nhomMoiNhat as $nhom)
                <tr>
                    {{-- Mã nhóm --}}
                    <td>
                        <span class="badge-code">{{ $nhom->TenNhom ?: $nhom->MaNhom }}</span>
                    </td>

                    {{-- Trưởng nhóm & thành viên --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            {{ $nhom->truongNhom->HoTen ?? 'Chưa có nhóm trưởng' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.76rem;">
                            @if($nhom->truongNhom)
                                MSSV: {{ $nhom->truongNhom->MaSV }} • Lớp {{ $nhom->truongNhom->lop->TenLop ?? '14DHTH' }}
                            @else
                                {{ $nhom->sinhViens->count() }} thành viên
                            @endif
                        </div>
                    </td>

                    {{-- Đề tài --}}
                    <td>
                        @if($nhom->deTai)
                            <div class="fw-semibold text-dark" style="font-size: 0.86rem;">
                                {{ Str::limit($nhom->deTai->TenDeTai, 65) }}
                            </div>
                            <div class="text-muted" style="font-size: 0.76rem;">
                                @if($nhom->deTai->giangVien)
                                    GVHD: {{ $nhom->deTai->giangVien->HocHamHocVi ? $nhom->deTai->giangVien->HocHamHocVi . ' ' : '' }}{{ $nhom->deTai->giangVien->HoTen }} • 
                                @endif
                                <span class="text-primary fw-medium">Đã đạt Mốc 1</span>
                            </div>
                        @else
                            <div class="text-muted fst-italic" style="font-size: 0.84rem;">(Chưa đăng ký đề tài)</div>
                            <div class="text-warning fw-semibold" style="font-size: 0.76rem;">
                                Đang mời thành viên ({{ $nhom->sinhViens->count() }}/3 SV)
                            </div>
                        @endif
                    </td>

                    {{-- Trạng thái --}}
                    <td class="text-center">
                        @if($nhom->TrangThai === 'Đã duyệt' || $nhom->TrangThai === 'Đủ điều kiện')
                            <span class="badge-status-green"><i class="fa-solid fa-check"></i> Đã duyệt đề tài</span>
                        @elseif($nhom->TrangThai === 'Đang thực hiện' || $nhom->TrangThai === 'Chờ duyệt')
                            <span class="badge-status-blue">{{ $nhom->TrangThai }}</span>
                        @elseif($nhom->TrangThai === 'Đang tạo nhóm')
                            <span class="badge-status-amber">Đang tạo nhóm</span>
                        @else
                            <span class="badge-status-amber">{{ $nhom->TrangThai }}</span>
                        @endif
                    </td>

                    {{-- Ngày tạo --}}
                    <td class="text-end text-muted" style="font-size: 0.82rem;">
                        {{ \Carbon\Carbon::parse($nhom->created_at ?? $nhom->NgayTao)->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Chưa có nhóm đồ án nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartXepLoaiFigma');
    if (!ctx) return;

    @php
        $rawValues = [
            $xepLoaiData['Xuất sắc'] ?? 0,
            $xepLoaiData['Giỏi'] ?? 0,
            $xepLoaiData['Khá'] ?? 0,
            $xepLoaiData['Trung bình'] ?? 0,
        ];
        if (array_sum($rawValues) === 0) {
            $rawValues = [12, 22, 10, 4];
        }
    @endphp

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Xuất sắc', 'Giỏi', 'Khá', 'Trung bình'],
            datasets: [{
                data: {!! json_encode($rawValues) !!},
                backgroundColor: ['#10b981', '#0ea5e9', '#f59e0b', '#8b5cf6'],
                borderWidth: 3,
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
                            return ' ' + context.label + ': ' + context.raw + ' SV';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

@endsection
