@extends('layouts.admin')

@section('page_title', 'Quản lý kế hoạch')

@section('content')
<div class="container-fluid px-0">

    {{-- ── 1. TIÊU ĐỀ TRANG & NÚT THAO TÁC CHÍNH ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-primary me-2"></i>
                Quản Lý Kế Hoạch Khóa Luận
            </h4>
            <p class="text-muted small mb-0">Theo dõi, điều chỉnh và công bố kế hoạch khóa luận tốt nghiệp cho các học kỳ.</p>
        </div>
        <div>
            <a href="{{ route('admin.kehoach.importDocument') }}" class="btn btn-primary shadow-sm rounded-3 px-3 fw-semibold d-inline-flex align-items-center">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Tải Lên Công Văn Kế Hoạch
            </a>
        </div>
    </div>

    {{-- ── 2. THỐNG KÊ NHANH (SUMMARY CARDS) ── --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-muted fw-medium small d-block">Tổng số kế hoạch</span>
                        <h4 class="fw-bold mb-0 mt-1 text-dark">{{ $keHoachs->total() }}</h4>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fa-solid fa-folder-open fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-muted fw-medium small d-block">Đang diễn ra</span>
                        <h4 class="fw-bold mb-0 mt-1 text-success">
                            {{ $keHoachs->where('TrangThai', 'ĐANG THỰC HIỆN')->count() }}
                        </h4>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fa-solid fa-circle-play fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-muted fw-medium small d-block">Chờ duyệt / Nháp</span>
                        <h4 class="fw-bold mb-0 mt-1 text-warning">
                            {{ $keHoachs->whereIn('TrangThai', ['NHÁP', 'CHỜ DUYỆT'])->count() }}
                        </h4>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="fa-solid fa-clock-rotate-left fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <span class="text-muted fw-medium small d-block">Đã công bố</span>
                        <h4 class="fw-bold mb-0 mt-1 text-info">
                            {{ $keHoachs->where('TrangThai', 'ĐÃ CÔNG BỐ')->count() }}
                        </h4>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                        <i class="fa-solid fa-bullhorn fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. THANH TÌM KIẾM & BỘ LỌC ── --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.kehoach.index') }}" class="row g-2 align-items-center">
                {{-- Ô tìm kiếm --}}
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Tìm theo tên hoặc mã kế hoạch..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Lọc Trạng thái --}}
                <div class="col-6 col-md-3 col-lg-3">
                    <select name="TrangThai" class="form-select bg-light">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="ĐÃ CÔNG BỐ" {{ request('TrangThai') == 'ĐÃ CÔNG BỐ' ? 'selected' : '' }}>Đã công bố</option>
                        <option value="ĐANG THỰC HIỆN" {{ request('TrangThai') == 'ĐANG THỰC HIỆN' ? 'selected' : '' }}>Đang diễn ra</option>
                        <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="CHỜ DUYỆT" {{ request('TrangThai') == 'CHỜ DUYỆT' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="HOÀN THÀNH" {{ request('TrangThai') == 'HOÀN THÀNH' ? 'selected' : '' }}>Hoàn thành</option>
                    </select>
                </div>

                {{-- Lọc Học kỳ --}}
                <div class="col-6 col-md-3 col-lg-3">
                    <select name="MaHocKy" class="form-select bg-light">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nút bấm --}}
                <div class="col-12 col-md-auto ms-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3 fw-semibold flex-grow-1 flex-md-grow-0 align-items-center">
                        <i class="fa-solid fa-filter me-2"></i> Lọc
                    </button>
                    @if(request()->hasAny(['search', 'TrangThai', 'MaHocKy']))
                    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-right me-2"></i> Xóa lọc
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── 4. BẢNG DANH SÁCH KẾ HOẠCH ── --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-regular fa-file-lines text-primary me-2"></i>
                Danh Sách Kế Hoạch Khóa Luận Tốt Nghiệp
            </h6>
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill">
                Hiển thị {{ $keHoachs->count() }} kế hoạch
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase fs-7 fw-bold">
                    <tr>
                        <th class="ps-4" style="width: 110px;">Mã KH</th>
                        <th style="min-width: 300px;">Tên Kế Hoạch & Khoa/Ngành</th>
                        <th style="width: 200px;">Thời Gian Thực Hiện</th>
                        <th class="text-center" style="width: 140px;">Mốc Quy Trình</th>
                        <th class="text-center" style="width: 140px;">Trạng Thái</th>
                        <th class="text-end pe-4" style="width: 160px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0 fs-6">
                    @forelse($keHoachs as $kh)
                    @php
                        $durationWeeks = 16;
                        if ($kh->NgayBatDau && $kh->NgayKetThuc) {
                            $start = \Carbon\Carbon::parse($kh->NgayBatDau);
                            $end = \Carbon\Carbon::parse($kh->NgayKetThuc);
                            $durationWeeks = max(1, $start->diffInWeeks($end));
                        }
                    @endphp
                    <tr>
                        {{-- Mã KH --}}
                        <td class="ps-4">
                            <span class="badge bg-light text-dark border font-monospace fw-semibold px-2 py-1">
                                {{ $kh->MaKeHoach }}
                            </span>
                        </td>

                        {{-- Tên Kế hoạch & Học kỳ --}}
                        <td>
                            <div class="fw-bold text-dark mb-1">
                                <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="text-dark text-decoration-none hover-primary">
                                    {{ $kh->TenKeHoach }}
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap small text-muted">
                                <span class="text-primary fw-semibold">
                                    <i class="fa-solid fa-graduation-cap me-2"></i>
                                    {{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }}
                                </span>
                                <span>•</span>
                                <span>Khoa Công nghệ Thông tin</span>
                            </div>

                            @if($kh->FileDinhKem)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $kh->FileDinhKem) }}" target="_blank" class="inline-flex align-items-center gap-1 badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill text-decoration-none px-2 py-1">
                                    <i class="fa-solid fa-file-pdf me-2"></i> File công văn đính kèm
                                </a>
                            </div>
                            @endif

                            <div class="mt-1 small">
                                @if(in_array($kh->TrangThai, ['NHÁP', 'CHỜ DUYỆT']))
                                    <span class="text-warning-emphasis fw-medium">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Đang chờ BCN Khoa phê duyệt
                                    </span>
                                @else
                                    <span class="text-muted">Đối tượng: Sinh viên năm cuối đủ điều kiện</span>
                                @endif
                            </div>
                        </td>

                        {{-- Thời gian --}}
                        <td>
                            <div class="fw-medium text-dark small">
                                {{ $kh->NgayBatDau ? \Carbon\Carbon::parse($kh->NgayBatDau)->format('d/m/Y') : '01/09/2026' }}
                                <i class="fa-solid fa-arrow-right-long mx-1 text-muted fs-7"></i>
                                {{ $kh->NgayKetThuc ? \Carbon\Carbon::parse($kh->NgayKetThuc)->format('d/m/Y') : '15/01/2027' }}
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="fa-regular fa-clock me-2 text-primary"></i> Thời lượng: <strong>{{ $durationWeeks }}</strong> tuần
                            </div>
                        </td>

                        {{-- Mốc quy trình --}}
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 mb-1">
                                {{ $kh->mocThoiGians->count() ?: 5 }} mốc
                            </span>
                            <div>
                                <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="text-decoration-none text-primary fw-semibold small">
                                    Chi tiết <i class="fa-solid fa-angle-right ms-1 fs-7"></i>
                                </a>
                            </div>
                        </td>

                        {{-- Trạng thái --}}
                        <td class="text-center">
                            @if($kh->TrangThai === 'ĐÃ CÔNG BỐ')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-check me-2"></i>Đã công bố
                                </span>
                            @elseif($kh->TrangThai === 'ĐANG THỰC HIỆN')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Đang diễn ra
                                </span>
                            @elseif($kh->TrangThai === 'NHÁP')
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                    Bản nháp
                                </span>
                            @elseif($kh->TrangThai === 'CHỜ DUYỆT')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-clock me-2"></i>Chờ duyệt
                                </span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">{{ $kh->TrangThai }}</span>
                            @endif
                        </td>

                        {{-- Thao tác --}}
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group">
                                {{-- Xem chi tiết --}}
                                <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="btn btn-outline-secondary" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                {{-- Upload file --}}
                                <a href="{{ route('admin.kehoach.importDocument') }}?ma_hoc_ky={{ $kh->MaHocKy }}" class="btn btn-outline-primary" title="Cập nhật bằng file công văn">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </a>

                                {{-- Công bố --}}
                                <form action="{{ route('admin.kehoach.publish', $kh->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Công bố kế hoạch này? Hệ thống sẽ phát hành thông báo kèm file công văn tới tất cả người dùng.');">
                                    @csrf
                                    <button type="submit" class="btn {{ $kh->TrangThai === 'ĐÃ CÔNG BỐ' ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="Công bố kế hoạch">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </button>
                                </form>

                                {{-- Xóa --}}
                                <form action="{{ route('admin.kehoach.destroy', $kh->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kế hoạch này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Xóa kế hoạch">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-regular fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                <h6 class="fw-bold">Không tìm thấy kế hoạch phù hợp</h6>
                                <p class="small mb-0">Thử thay đổi bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang & Đếm dòng --}}
        <div class="card-footer bg-white border-top py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted small">
                Hiển thị <strong>{{ $keHoachs->firstItem() ?? 0 }}–{{ $keHoachs->lastItem() ?? 0 }}</strong> trên tổng số <strong>{{ $keHoachs->total() }}</strong> kế hoạch
            </div>
            <div>
                {{ $keHoachs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS Tùy chỉnh làm mịn giao diện */
    .fs-7 { font-size: 0.75rem; }
    .hover-primary:hover { color: #0d6efd !important; }
    .table > :not(caption) > * > * { padding: 0.85rem 0.75rem; }
</style>
@endsection