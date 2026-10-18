@extends('layouts.truongkhoa')

@section('page_title', 'Danh Sách Đề Tài - ' . $boMon->TenBoMon)

@push('styles')
<style>
    /* Chuẩn giao diện Admin: flat, không box-shadow, font tối thiểu 13px */
    body, .admin-table, .form-select, .form-control, .btn {
        font-size: 13.5px;
    }
    .admin-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: none !important;
    }
    .admin-table-header {
        padding: 12px 18px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .admin-table-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .admin-table {
        width: 100%;
        margin-bottom: 0;
    }
    .admin-table thead th {
        background-color: #003366 !important;
        color: #ffffff !important;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 12px !important;
        white-space: nowrap;
    }
    .admin-table tbody td {
        padding: 10px 12px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #1e293b;
    }
    .badge-code {
        background: #f1f5f9;
        color: #0f172a;
        font-family: monospace;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Breadcrumb & Back button (Chuẩn G) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('truongkhoa.duyet_detai.index', ['MaHocKy' => $selectedHocKy]) }}" class="btn btn-sm btn-outline-secondary px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách bộ môn
                </a>
                <span class="badge bg-light text-secondary border px-3 py-1">
                    Khoa: {{ $khoa->TenKhoa ?? '' }}
                </span>
            </div>
            <h4 class="fw-bold text-dark mb-0">
                Danh Sách Đề Tài &bull; Bộ Môn {{ $boMon->TenBoMon }}
            </h4>
        </div>
    </div>

    <!-- Khung Tìm Kiếm & Bộ Lọc Nâng Cao (P3.3) -->
    <div class="card border rounded-3 mb-4" style="box-shadow: none !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('truongkhoa.duyet_detai.bomon', $boMon->MaBoMon) }}" class="row g-2 align-items-center">
                <!-- 1. Học kỳ -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small fw-bold text-muted mb-1">Học kỳ:</label>
                    <select name="MaHocKy" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '(Hiện tại)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Tìm kiếm theo tên đề tài hoặc giảng viên -->
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label small fw-bold text-muted mb-1">Tìm kiếm:</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Tên đề tài, mã ĐT hoặc tên GV..." 
                           value="{{ request('search') }}">
                </div>

                <!-- 3. Lọc theo trạng thái phê duyệt của TK -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-muted mb-1">Trạng thái phê duyệt TK:</label>
                    <select name="trang_thai_tk" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="ALL">Tất cả trạng thái TK</option>
                        <option value="cho_duyet" {{ in_array(request('trang_thai_tk'), ['cho_duyet', 'chua_phe_duyet', 'Chưa phê duyệt']) ? 'selected' : '' }}>Chờ Trưởng khoa duyệt</option>
                        <option value="da_duyet" {{ in_array(request('trang_thai_tk'), ['da_duyet', 'da_phe_duyet', 'Đã phê duyệt']) ? 'selected' : '' }}>Trưởng khoa đã duyệt / Đã công bố</option>
                        <option value="yeu_cau_sua" {{ in_array(request('trang_thai_tk'), ['yeu_cau_sua', 'yeu_cau_chinh_sua']) ? 'selected' : '' }}>Yêu cầu chỉnh sửa</option>
                        <option value="tu_choi" {{ in_array(request('trang_thai_tk'), ['tu_choi', 'Từ chối']) ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>

                <!-- 4. Lọc theo tình trạng chỉ tiêu -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small fw-bold text-muted mb-1">Chỉ tiêu GV:</label>
                    <select name="tinh_trang_chi_tieu" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="ALL">Tất cả chỉ tiêu</option>
                        <option value="con_chi_tieu" {{ request('tinh_trang_chi_tieu') === 'con_chi_tieu' ? 'selected' : '' }}>Còn chỉ tiêu</option>
                        <option value="het_chi_tieu" {{ request('tinh_trang_chi_tieu') === 'het_chi_tieu' ? 'selected' : '' }}>Hết chỉ tiêu</option>
                        <option value="vuot_chi_tieu" {{ request('tinh_trang_chi_tieu') === 'vuot_chi_tieu' ? 'selected' : '' }}>Vượt chỉ tiêu</option>
                    </select>
                </div>

                <!-- Nút bấm Lọc và Đặt lại -->
                <div class="col-12 col-lg-1 d-flex align-items-end gap-1 mt-auto">
                    <button class="btn btn-sm btn-primary fw-semibold px-2 w-100" type="submit" title="Lọc">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'trang_thai_tk', 'tinh_trang_chi_tieu']))
                        <a href="{{ route('truongkhoa.duyet_detai.bomon', ['maBoMon' => $boMon->MaBoMon, 'MaHocKy' => $selectedHocKy]) }}" class="btn btn-sm btn-outline-secondary px-2" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách đề tài của bộ môn -->
    <div class="admin-table-card mb-4">
        <div class="admin-table-header">
            <h5 class="admin-table-title">
                Đề Tài Khóa Luận Bộ Môn {{ $boMon->TenBoMon }}
            </h5>
            <span class="text-muted small">
                Hiển thị <strong>{{ $detais->count() }}</strong> / Tổng cộng <strong>{{ $detais->total() }}</strong> đề tài
            </span>
        </div>

        <div class="table-responsive">
            <table class="table admin-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="width: 120px;">Mã Đề Tài</th>
                        <th style="width: 280px;">Tên Đề Tài &amp; Lĩnh Vực</th>
                        <th style="width: 220px;">Giảng Viên Hướng Dẫn</th>
                        <th class="text-center" style="width: 150px;">Phê Duyệt TBM</th>
                        <th class="text-center" style="width: 160px;">Phê Duyệt TK</th>
                        <th class="text-center" style="width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $idx => $dt)
                    @php
                        // Tính trạng thái phê duyệt TBM
                        $isBmApproved = !empty($dt->NgayDuyetBM) || in_array($dt->TrangThai, [
                            'Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương',
                            'Đã nộp đề cương - Chờ phân công PB', 'Đang phản biện đề cương',
                            'Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương',
                            'Đã công bố', 'Đã đăng ký', 'Hoàn thành'
                        ]);
                        $isBmPending = in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']) && empty($dt->NgayDuyetBM);
                        $isBmEdit = in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']) && empty($dt->NgayDuyetBM);
                        $isBmRejected = in_array($dt->TrangThai, ['Từ chối', 'Không đạt phản biện']) && empty($dt->NgayDuyetBM);

                        // Tính trạng thái phê duyệt TK
                        $isTkPending = $dt->TrangThai === 'Chờ duyệt cấp Khoa';
                        $isTkApproved = in_array($dt->TrangThai, ['Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành']) || !empty($dt->NgayDuyetKhoa);
                        $isTkEdit = in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương']) && !empty($dt->NgayDuyetBM);
                        $isTkRejected = in_array($dt->TrangThai, ['Từ chối', 'Không đạt phản biện']) && !empty($dt->NgayDuyetBM);

                        // Chỉ tiêu GV
                        $gvStat = $chiTieuStats[$dt->MaGV] ?? null;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $detais->firstItem() + $idx }}</td>
                        <td>
                            <span class="badge-code">{{ $dt->MaDeTai }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                            @if($dt->LinhVuc)
                                <div class="text-muted small mt-1">{{ $dt->LinhVuc }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $dt->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                            <div class="text-muted small mt-1">
                                Mã GV: {{ $dt->giangVien?->MaGV ?? 'N/A' }}
                            </div>
                            @if($gvStat)
                                <div class="mt-1">
                                    <span class="badge {{ $gvStat['tinh_trang'] === 'vuot_chi_tieu' ? 'bg-danger-subtle text-danger border border-danger-subtle' : ($gvStat['tinh_trang'] === 'het_chi_tieu' ? 'bg-secondary-subtle text-secondary border border-secondary-subtle' : 'bg-light text-secondary border') }}" style="font-size: 11.5px;">
                                        Chỉ tiêu: {{ $gvStat['label'] }} ({{ $gvStat['tinh_trang'] === 'vuot_chi_tieu' ? 'Vượt chỉ tiêu' : ($gvStat['tinh_trang'] === 'het_chi_tieu' ? 'Hết chỉ tiêu' : 'Còn chỉ tiêu') }})
                                    </span>
                                </div>
                            @endif
                        </td>
                        <!-- Cột Trạng Thái TBM (Tất cả đề tài Trưởng khoa xem đều đã được TBM duyệt) -->
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-semibold" style="font-size: 12.5px;">
                                <i class="fa-solid fa-check me-2"></i> TBM đã duyệt
                            </span>
                            @if($dt->NgayDuyetBM)
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    {{ \Carbon\Carbon::parse($dt->NgayDuyetBM)->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <!-- Cột Trạng Thái TK -->
                        <td class="text-center">
                            @if($isTkPending)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fw-bold" style="font-size: 12.5px;">
                                    Chờ duyệt cấp Khoa
                                </span>
                            @elseif($isTkApproved)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 12.5px;">
                                    TK đã duyệt
                                </span>
                            @elseif($isTkEdit)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 12.5px;">
                                    TK yêu cầu sửa
                                </span>
                            @elseif($isTkRejected)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 12.5px;">
                                    TK từ chối
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 12.5px;">
                                    Chờ bước BM
                                </span>
                            @endif
                        </td>
                        <!-- Cột Thao Tác -->
                        <td class="text-center">
                            @if($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                                <a href="{{ route('truongkhoa.duyet_detai.show', ['id' => $dt->MaDeTai, 'return_url' => url()->full()]) }}" 
                                   class="btn btn-sm btn-action btn-primary fw-semibold text-nowrap">
                                    <i class="fa-solid fa-stamp me-2"></i> Xem &amp; Duyệt
                                </a>
                            @else
                                <a href="{{ route('truongkhoa.duyet_detai.show', ['id' => $dt->MaDeTai, 'return_url' => url()->full()]) }}" 
                                   class="btn btn-sm btn-action btn-outline-primary text-nowrap">
                                    <i class="fa-solid fa-eye me-2"></i> Chi Tiết
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <h6 class="fw-semibold">Không tìm thấy đề tài nào phù hợp với bộ lọc hiện tại!</h6>
                            <p class="small text-muted mb-0">Thử thay đổi từ khóa tìm kiếm hoặc chọn lại trạng thái lọc.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($detais->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Hiển thị <strong>{{ $detais->firstItem() }}</strong> - <strong>{{ $detais->lastItem() }}</strong> trên tổng <strong>{{ $detais->total() }}</strong> đề tài
            </div>
            <div>
                {{ $detais->appends(request()->all())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
