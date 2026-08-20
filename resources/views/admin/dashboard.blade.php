@extends('layouts.admin')
@section('title', 'Tổng Quan Hệ Thống')
@section('content')

{{-- ── WIDGET THỐNG KÊ TỔNG QUAN ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg,#0072CE,#005ba1);">
            <div class="card-body p-3 text-center">
                <i class="fa-solid fa-user-graduate fa-2x opacity-75 mb-2"></i>
                <div class="fw-bold fs-3">{{ $soSinhVien }}</div>
                <div style="font-size:.78rem;opacity:.85;">Sinh Viên</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg,#198754,#146c43);">
            <div class="card-body p-3 text-center">
                <i class="fa-solid fa-chalkboard-user fa-2x opacity-75 mb-2"></i>
                <div class="fw-bold fs-3">{{ $soGiangVien }}</div>
                <div style="font-size:.78rem;opacity:.85;">Giảng Viên</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-dark h-100" style="background: linear-gradient(135deg,#ffc107,#e0a800);">
            <div class="card-body p-3 text-center">
                <i class="fa-solid fa-book-open fa-2x opacity-75 mb-2"></i>
                <div class="fw-bold fs-3">{{ $soDeTai }}</div>
                <div style="font-size:.78rem;opacity:.75;">Đề Tài</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg,#dc3545,#b02a37);">
            <div class="card-body p-3 text-center">
                <i class="fa-solid fa-users fa-2x opacity-75 mb-2"></i>
                <div class="fw-bold fs-3">{{ $soNhom }}</div>
                <div style="font-size:.78rem;opacity:.85;">Nhóm Đồ Án</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg,#6f42c1,#59359a);">
            <div class="card-body p-3 text-center">
                <i class="fa-solid fa-landmark fa-2x opacity-75 mb-2"></i>
                <div class="fw-bold fs-3">{{ $soHoiDong }}</div>
                <div style="font-size:.78rem;opacity:.85;">Hội Đồng</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg,#0dcaf0,#0aa2c0);">
            <div class="card-body p-3 text-center">
                <i class="fa-solid fa-folder-check fa-2x opacity-75 mb-2"></i>
                <div class="fw-bold fs-3">{{ $hoSoTong }}</div>
                <div style="font-size:.78rem;opacity:.85;">Hồ Sơ Bảo Vệ</div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 2: BIỂU ĐỒ ── --}}
<div class="row g-4 mb-4">

    {{-- Biểu đồ xếp loại kết quả SV --}}
    <div class="col-md-5">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <i class="fa-solid fa-award me-2 text-primary"></i>Xếp Loại Kết Quả Sinh Viên
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:250px;">
                <canvas id="chartXepLoai" style="max-height:230px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Tiến độ 5 Mốc --}}
    <div class="col-md-4">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <i class="fa-solid fa-chart-bar me-2 text-primary"></i>Tiến Độ 5 Mốc Báo Cáo
            </div>
            <div class="card-body" style="min-height:250px;">
                <canvas id="chartMoc" style="max-height:220px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Đăng ký đề tài --}}
    <div class="col-md-3">
        <div class="card card-premium h-100">
            <div class="card-header-premium">
                <i class="fa-solid fa-clipboard-list me-2 text-primary"></i>Đăng Ký Đề Tài
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold text-success">Đã duyệt</small>
                        <small class="fw-bold">{{ $dkDaDuyet }}</small>
                    </div>
                    <div class="progress rounded-pill" style="height:8px;">
                        @php $total = max($dkDaDuyet + $dkChoDuyet + $dkTuChoi, 1); @endphp
                        <div class="progress-bar bg-success" style="width:{{ ($dkDaDuyet/$total)*100 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold text-warning">Chờ duyệt</small>
                        <small class="fw-bold">{{ $dkChoDuyet }}</small>
                    </div>
                    <div class="progress rounded-pill" style="height:8px;">
                        <div class="progress-bar bg-warning" style="width:{{ ($dkChoDuyet/$total)*100 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold text-danger">Từ chối</small>
                        <small class="fw-bold">{{ $dkTuChoi }}</small>
                    </div>
                    <div class="progress rounded-pill" style="height:8px;">
                        <div class="progress-bar bg-danger" style="width:{{ ($dkTuChoi/$total)*100 }}%"></div>
                    </div>
                </div>

                <hr>
                <div class="small text-muted">
                    <i class="fa-solid fa-folder-check me-1 text-info"></i>Hồ sơ chờ xác nhận: <strong>{{ $hoSoCho }}</strong><br>
                    <i class="fa-solid fa-check-circle me-1 text-success"></i>Đã phân công HĐ: <strong>{{ $hoSoPhanCong }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── HÀNG 3: DANH SÁCH NHÓM MỚI NHẤT ── --}}
<div class="row g-4">
    <div class="col-md-12">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Nhóm Đăng Ký Mới Nhất</span>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary rounded-pill">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nhóm</th>
                            <th>Trưởng Nhóm</th>
                            <th>Đề Tài</th>
                            <th class="text-center">Trạng Thái</th>
                            <th class="text-end">Ngày Tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nhomMoiNhat as $nhom)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $nhom->MaNhom }}</span> {{ $nhom->TenNhom }}</td>
                            <td>{{ $nhom->truongNhom->HoTen ?? '—' }}</td>
                            <td><span class="text-muted small">{{ Str::limit($nhom->deTai->TenDeTai ?? '(Chưa có đề tài)', 55) }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $nhom->TrangThai === 'Đã duyệt' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill">{{ $nhom->TrangThai }}</span>
                            </td>
                            <td class="text-end small text-muted">{{ \Carbon\Carbon::parse($nhom->created_at)->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có nhóm nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Biểu đồ Xếp Loại (Doughnut)
const ctxXepLoai = document.getElementById('chartXepLoai').getContext('2d');
new Chart(ctxXepLoai, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($xepLoaiData)) !!},
        datasets: [{
            data: {!! json_encode(array_values($xepLoaiData)) !!},
            backgroundColor: ['#f093fb','#4facfe','#43e97b','#ffc107','#fd7e14','#dc3545'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 } } }
        },
        cutout: '65%',
    }
});

// Biểu đồ Tiến Độ Mốc (Bar)
const ctxMoc = document.getElementById('chartMoc').getContext('2d');
new Chart(ctxMoc, {
    type: 'bar',
    data: {
        labels: ['Mốc 1','Mốc 2','Mốc 3','Mốc 4','Mốc 5'],
        datasets: [{
            label: 'Nhóm đạt',
            data: {!! json_encode(array_values($mocProgress)) !!},
            backgroundColor: ['#4facfe','#43e97b','#ffc107','#fd7e14','#f093fb'],
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush

@endsection
