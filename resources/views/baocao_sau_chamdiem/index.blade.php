@extends($scope['layout'])

@section('page_title', 'Báo Cáo Sau Chấm Điểm & Bảo Vệ — ' . $scope['roleTitle'])

@section('content')
<style>
.filter-card,
.filter-item-box {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    padding: 16px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 114, 206, 0.04) !important;
    height: auto !important;
    min-height: unset !important;
    max-height: unset !important;
    display: block !important;
    overflow: visible !important;
    position: relative;
    z-index: 5;
}
.filter-card .form-control,
.filter-card .form-select,
.filter-card .input-group-text,
.filter-card .btn {
    height: 40px !important;
    font-size: 0.86rem;
    box-sizing: border-box;
}
.filter-card .form-select {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 2.25rem;
}
.filter-card .input-group {
    height: 40px;
}
.filter-card .input-group .form-control:focus {
    z-index: 3;
}
.kpi-stat-card {
    border-radius: 12px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.kpi-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}
.table-reports thead th {
    background-color: #0b3a6e !important;
    color: #ffffff !important;
    border-bottom: 2px solid #082a50 !important;
    vertical-align: middle;
    font-weight: 600;
    font-size: 0.84rem;
    white-space: nowrap;
}
.table-reports tbody td {
    vertical-align: middle;
    font-size: 0.85rem;
}
</style>

<div class="container-fluid px-0" style="padding-bottom: 60px;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark, #00529b);">
                <i class="fa-solid fa-file-circle-check me-2 text-primary"></i>
                Báo Cáo Hoàn Thiện Sau Chấm Điểm &amp; Bảo Vệ
            </h4>
            <div class="text-muted small">
                Theo dõi quá trình sinh viên tiếp thu ý kiến của Hội đồng, nộp lại báo cáo hoàn thiện và kết quả thẩm định của GVHD.
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold">
                <i class="fa-solid fa-user-shield me-1"></i> {{ $scope['roleTitle'] }} (Chỉ đọc)
            </span>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <a href="{{ route($scope['routePrefix'] . '.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 kpi-stat-card" style="border-left: 4px solid #0072ce !important;">
                    <div class="card-body p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.75rem;">Tổng Hồ Sơ</div>
                        <h3 class="fw-bold my-1 text-primary">{{ $counts['total'] }}</h3>
                        <div class="small text-muted" style="font-size: 0.75rem;">Hồ sơ sau bảo vệ</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route($scope['routePrefix'] . '.index', array_merge(request()->except('status', 'page'), ['status' => 'da_nop'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 kpi-stat-card" style="border-left: 4px solid #0dcaf0 !important;">
                    <div class="card-body p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.75rem;">Chờ Kiểm Tra</div>
                        <h3 class="fw-bold my-1 text-info">{{ $counts['cho_kiem_tra'] }}</h3>
                        <div class="small text-muted" style="font-size: 0.75rem;">Đã nộp bản mới</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route($scope['routePrefix'] . '.index', array_merge(request()->except('status', 'page'), ['status' => 'chua_dat'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 kpi-stat-card" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.75rem;">Chưa Đạt (Cần Sửa)</div>
                        <h3 class="fw-bold my-1 text-danger">{{ $counts['yeu_cau_sua'] }}</h3>
                        <div class="small text-muted" style="font-size: 0.75rem;">Yêu cầu sửa tiếp</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route($scope['routePrefix'] . '.index', array_merge(request()->except('status', 'page'), ['status' => 'dat'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 kpi-stat-card" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.75rem;">Đã Hoàn Thành (Đạt)</div>
                        <h3 class="fw-bold my-1 text-success">{{ $counts['da_hoan_thanh'] }}</h3>
                        <div class="small text-muted" style="font-size: 0.75rem;">Chuẩn lưu trữ Khoa</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route($scope['routePrefix'] . '.index', array_merge(request()->except('status', 'page'), ['status' => 'chua_nop'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 kpi-stat-card" style="border-left: 4px solid #6c757d !important;">
                    <div class="card-body p-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.75rem;">Chưa Nộp Lại</div>
                        <h3 class="fw-bold my-1 text-secondary">{{ $counts['chua_nop'] }}</h3>
                        <div class="small text-muted" style="font-size: 0.75rem;">Chờ sinh viên nộp</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter Box -->
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route($scope['routePrefix'] . '.index') }}" class="row g-2 align-items-end">
            <!-- Ô tìm kiếm MSSV, tên SV, đề tài, nhóm -->
            <div class="col-12 col-xl-4 min-w-0">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted ps-3 pe-2">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-1" 
                           placeholder="MSSV, họ tên, đề tài, nhóm..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Học kỳ -->
            <div class="col-6 col-md-4 col-xl-2 min-w-0">
                <select name="MaHocKy" class="form-select text-truncate w-100" title="Học kỳ">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" title="{{ $hk->TenHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Khoa (Chỉ Giáo vụ / Admin) -->
            @if($scope['role'] === 'admin')
            <div class="col-6 col-md-4 col-xl-2 min-w-0">
                <select name="MaKhoa" class="form-select text-truncate w-100" title="Khoa">
                    <option value="">-- Tất cả khoa --</option>
                    @foreach($khoas as $k)
                        <option value="{{ $k->MaKhoa }}" title="{{ $k->TenKhoa }}" {{ request('MaKhoa') == $k->MaKhoa ? 'selected' : '' }}>
                            {{ $k->TenKhoa }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Bộ môn (Giáo vụ & Trưởng khoa) -->
            @if(in_array($scope['role'], ['admin', 'truongkhoa']))
            <div class="col-6 col-md-4 col-xl-2 min-w-0">
                <select name="MaBoMon" class="form-select text-truncate w-100" title="Bộ môn">
                    <option value="">-- Tất cả bộ môn --</option>
                    @foreach($boMons as $bm)
                        <option value="{{ $bm->MaBoMon }}" title="{{ $bm->TenBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                            {{ $bm->TenBoMon }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Ngành -->
            <div class="col-6 col-md-4 col-xl-2 min-w-0">
                <select name="MaNganh" class="form-select text-truncate w-100" title="Ngành">
                    <option value="">-- Tất cả ngành --</option>
                    @foreach($nganhs as $ng)
                        <option value="{{ $ng->MaNganh }}" title="{{ $ng->TenNganh }}" {{ request('MaNganh') == $ng->MaNganh ? 'selected' : '' }}>
                            {{ $ng->TenNganh }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Hội đồng -->
            <div class="col-6 col-md-4 col-xl-2 min-w-0">
                <select name="MaHoiDong" class="form-select text-truncate w-100" title="Hội đồng">
                    <option value="">-- Tất cả hội đồng --</option>
                    @foreach($hoiDongs as $hd)
                        <option value="{{ $hd->MaHoiDong }}" title="{{ $hd->TenHoiDong }}" {{ request('MaHoiDong') == $hd->MaHoiDong ? 'selected' : '' }}>
                            {{ $hd->TenHoiDong }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Trạng thái hoàn thiện -->
            <div class="col-6 col-md-4 col-xl-3 min-w-0">
                <select name="status" class="form-select text-truncate w-100" title="Trạng thái hoàn thiện">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="da_nop" {{ in_array(request('status'), ['da_nop', 'cho_kiem_tra']) ? 'selected' : '' }}>Đã nộp, chờ kiểm tra</option>
                    <option value="dat" {{ in_array(request('status'), ['dat', 'da_hoan_thanh']) ? 'selected' : '' }}>Đạt (Đã hoàn thành)</option>
                    <option value="chua_dat" {{ in_array(request('status'), ['chua_dat', 'yeu_cau_sua']) ? 'selected' : '' }}>Chưa đạt (Yêu cầu sửa)</option>
                    <option value="chua_nop" {{ request('status') === 'chua_nop' ? 'selected' : '' }}>Chưa nộp</option>
                </select>
            </div>

            <!-- Nút hành động -->
            <div class="col-12 col-md-auto ms-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3 fw-semibold flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center shadow-sm">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
                <a href="{{ route($scope['routePrefix'] . '.index') }}" class="btn btn-outline-secondary px-3 flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center" title="Đặt lại bộ lọc">
                    <i class="fa-solid fa-rotate-right me-1"></i> Đặt lại
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>
                Danh Sách Báo Cáo Chỉnh Sửa Sau Chấm Điểm ({{ $hoSos->total() }} hồ sơ)
            </h6>
            <div class="small text-muted">Trang {{ $hoSos->currentPage() }} / {{ $hoSos->lastPage() }}</div>
        </div>
        <div class="card-body p-0">
            @if($hoSos->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6>Không tìm thấy hồ sơ báo cáo nào phù hợp với điều kiện lọc.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-reports">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">STT</th>
                                <th style="min-width: 170px;">Sinh viên thực hiện</th>
                                <th style="min-width: 240px;">Nhóm &amp; Đề tài</th>
                                <th style="min-width: 140px;">GVHD</th>
                                <th style="min-width: 150px;">Hội đồng &amp; Đợt</th>
                                <th style="width: 130px;">Hạn nộp lại</th>
                                <th style="width: 140px;" class="text-center">Trạng thái</th>
                                <th style="width: 130px;">Ngày nộp mới nhất</th>
                                <th style="width: 90px;" class="text-center">Phiên bản</th>
                                <th style="width: 130px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoSos as $idx => $hs)
                            @php
                                $nhom = $hs->nhom;
                                $deTai = $hs->deTai;
                                $latest = $hs->baoCaoSauBaoVeMoiNhat ?? $hs->baoCaoSauBaoVes->sortByDesc('LanChinhSua')->first();
                                $hanNop = $hs->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($hs->HanNopBaoCaoSauBaoVe) : null;
                                $isQuaHan = $hanNop && $hanNop->isPast() && $hs->TrangThaiChinhSuaSauBaoVe !== 'DaHoanThanh';
                                $soPhienBan = $hs->baoCaoSauBaoVes->count();
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $hoSos->firstItem() + $idx }}</td>
                                <td>
                                    @if($nhom && $nhom->thanhViens->isNotEmpty())
                                        @foreach($nhom->thanhViens as $tv)
                                            <div class="py-0">
                                                <span class="fw-bold font-monospace text-primary" style="font-size: 0.8rem;">
                                                    {{ $tv->sinhVien->MaSoSinhVien ?? $tv->sinhVien->MaSV ?? $tv->MaSV }}
                                                </span>
                                                <div class="text-dark fw-semibold" style="font-size: 0.82rem;">
                                                    {{ $tv->sinhVien->HoTen ?? 'Sinh viên' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted small fst-italic">Chưa có thành viên</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="line-height: 1.35;">
                                        {{ $deTai->TenDeTai ?? 'Chưa xác định' }}
                                    </div>
                                    <div class="small text-muted mt-1 d-flex flex-wrap gap-2 align-items-center">
                                        <span class="badge bg-light text-secondary border font-monospace">{{ $hs->MaHoSo }}</span>
                                        <span>Nhóm: <strong>{{ $nhom->TenNhom ?? $hs->MaNhom }}</strong></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $deTai->giangVien->HoTen ?? 'Chưa phân công' }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $deTai->giangVien->boMon->TenBoMon ?? '' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $hs->hoiDong->TenHoiDong ?? 'Hội đồng' }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $hs->hoiDong->hocKy->TenHocKy ?? $nhom->hocKy->TenHocKy ?? '' }}
                                    </div>
                                </td>
                                <td>
                                    @if($hanNop)
                                        <div class="small fw-semibold {{ $isQuaHan ? 'text-danger' : 'text-dark' }}">
                                            {{ $hanNop->format('d/m/Y H:i') }}
                                        </div>
                                        @if($isQuaHan)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.7rem;">
                                                Quá hạn nộp
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small fst-italic">--</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($hs->TrangThaiChinhSuaSauBaoVe === 'DaHoanThanh')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold">
                                            <i class="fa-solid fa-circle-check me-1"></i> Đạt
                                        </span>
                                    @elseif($hs->TrangThaiChinhSuaSauBaoVe === 'DaNop')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1 fw-bold">
                                            <i class="fa-solid fa-clock me-1"></i> Chờ kiểm tra
                                        </span>
                                    @elseif($hs->TrangThaiChinhSuaSauBaoVe === 'YeuCauSuaTiep')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fw-bold">
                                            <i class="fa-solid fa-rotate me-1"></i> Chưa đạt
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                            Chưa nộp
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($latest && $latest->NgayNop)
                                        <div class="small fw-semibold text-dark">{{ \Carbon\Carbon::parse($latest->NgayNop)->format('d/m/Y H:i') }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $latest->sinhVienNop->HoTen ?? $latest->NguoiNop }}</div>
                                    @else
                                        <span class="text-muted small fst-italic">Chưa có</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($soPhienBan > 0)
                                        <span class="badge bg-primary text-white rounded-pill px-2 py-1 fw-semibold">
                                            v{{ $latest->LanChinhSua ?? $soPhienBan }}
                                        </span>
                                        <div class="text-muted" style="font-size: 0.7rem;">({{ $soPhienBan }} lần)</div>
                                    @else
                                        <span class="text-muted small">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="{{ route($scope['routePrefix'] . '.show', $hs->MaHoSo) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" 
                                           title="Xem chi tiết hồ sơ & lịch sử">
                                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                        </a>

                                        @if($latest && $latest->FileBaoCao)
                                        <button type="button" 
                                                class="btn btn-sm btn-light border text-danger rounded-circle p-1" 
                                                style="width: 28px; height: 28px;"
                                                title="Xem nhanh bản PDF mới nhất"
                                                onclick="quickPreviewOutline('{{ route('baocao_sau_baove.pdf', $latest->id) }}', '{{ addslashes($latest->TenFile) }}', 'Báo cáo hoàn thiện - {{ addslashes($deTai->TenDeTai ?? $hs->MaHoSo) }}', { downloadUrl: '{{ route('baocao_sau_baove.download', $latest->id) }}', title: 'Báo Cáo Hoàn Thiện: {{ addslashes($deTai->TenDeTai ?? $hs->MaHoSo) }}' })">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($hoSos->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Hiển thị {{ $hoSos->firstItem() }} - {{ $hoSos->lastItem() }} trên tổng số {{ $hoSos->total() }} hồ sơ
                    </div>
                    <div>
                        {{ $hoSos->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

@include('partials.modal_preview_decuong')
@endsection