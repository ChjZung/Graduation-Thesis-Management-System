@extends('layouts.truongbomon')

@section('page_title', 'Phê duyệt đề tài')

@push('styles')
<style>
    /* Chuẩn giao diện Admin: flat, không box-shadow, font tối thiểu 13px */
    body, .admin-table, .form-select, .form-control, .btn {
        font-size: 13.5px;
    }
    .kpi-card-admin {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem 1.15rem;
    }
    .kpi-card-admin .kpi-label {
        font-size: 12.5px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .kpi-card-admin .kpi-number {
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .kpi-card-admin .kpi-subtext {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 0.25rem;
    }
    .admin-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
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
        background-color: #f8fafc;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        padding: 11px 14px;
        white-space: nowrap;
    }
    .admin-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
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
        font-size: 12.5px;
    }
    .row-highlight-pending {
        background-color: #fffdf5 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Header Page (Chuẩn tham chiếu Admin) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                    Bộ Môn: {{ $boMon->TenBoMon ?? 'N/A' }} ({{ $boMon->MaBoMon ?? '' }})
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                    Cổng Trưởng Bộ Môn
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-0">Phê duyệt đề tài</h3>
            <p class="text-muted small mb-0">Quản lý, xem xét và phê duyệt đề tài theo từng giảng viên thuộc bộ môn phụ trách</p>
        </div>

        @if($hocKies->count() > 1)
        <!-- Bộ Lọc Học Kỳ (Chỉ hiện nếu hệ thống quản lý nhiều học kỳ) -->
        <div class="d-flex align-items-center gap-2 bg-white p-2 rounded-3 border">
            <label class="small fw-bold text-secondary mb-0 text-nowrap">
                Học kỳ:
            </label>
            <form method="GET" action="{{ route('truongbomon.phe_duyet_detai.index') }}" class="d-inline-block">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('trang_thai'))
                    <input type="hidden" name="trang_thai" value="{{ request('trang_thai') }}">
                @endif
                @if(request('tinh_trang_chi_tieu'))
                    <input type="hidden" name="tinh_trang_chi_tieu" value="{{ request('tinh_trang_chi_tieu') }}">
                @endif
                <select name="hocKy" class="form-select form-select-sm fw-semibold border-0 bg-light" style="min-width: 220px;" onchange="this.form.submit()">
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} {{ $hk->TrangThai === 'Đang diễn ra' ? '(Hiện tại)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif
    </div>

    <!-- Hàng Thẻ Thống Kê Phía Trên (5 Thẻ Chuẩn P2) -->
    <div class="row g-3 mb-4">
        <!-- 1. Tổng số đề tài -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <div class="kpi-card-admin h-100">
                <div class="kpi-label">Tổng số đề tài</div>
                <div class="kpi-number text-dark">{{ $summaryKPIs['tong_detai'] }}</div>
                <div class="kpi-subtext">Đề xuất trong học kỳ</div>
            </div>
        </div>

        <!-- 2. Số đề tài chờ duyệt -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <div class="kpi-card-admin h-100 border-warning border-opacity-50" style="background: #fffdf5;">
                <div class="kpi-label text-warning-emphasis">Số đề tài chờ duyệt</div>
                <div class="kpi-number text-warning-emphasis">{{ $summaryKPIs['cho_duyet'] }}</div>
                <div class="kpi-subtext">
                    @if($summaryKPIs['cho_duyet'] > 0)
                        <span class="text-danger fw-semibold">Cần bộ môn phê duyệt</span>
                    @else
                        <span>Đã duyệt hết</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Số đề tài đã duyệt -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <div class="kpi-card-admin h-100">
                <div class="kpi-label text-success">Số đề tài đã duyệt</div>
                <div class="kpi-number text-success">{{ $summaryKPIs['da_duyet'] }}</div>
                <div class="kpi-subtext">Chuyển tiếp Khoa / Đã công bố</div>
            </div>
        </div>

        <!-- 4. Số đề tài yêu cầu chỉnh sửa -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <div class="kpi-card-admin h-100">
                <div class="kpi-label text-info-emphasis">Yêu cầu chỉnh sửa</div>
                <div class="kpi-number text-info-emphasis">{{ $summaryKPIs['yeu_cau_sua'] }}</div>
                <div class="kpi-subtext">Đang chờ GV hoàn thiện</div>
            </div>
        </div>

        <!-- 5. Số đề tài bị từ chối -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl">
            <div class="kpi-card-admin h-100">
                <div class="kpi-label text-danger">Số đề tài bị từ chối</div>
                <div class="kpi-number text-danger">{{ $summaryKPIs['tu_choi'] }}</div>
                <div class="kpi-subtext">Không đạt tiêu chuẩn</div>
            </div>
        </div>
    </div>

    <!-- Khu Vực Tìm Kiếm Và Lọc Bên Dưới (Chuẩn P2: Tìm tên/mã GV/tên đề tài, Lọc trạng thái, Lọc chỉ tiêu, Lọc học kỳ) -->
    <div class="card border rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('truongbomon.phe_duyet_detai.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="hocKy" value="{{ $selectedHocKy }}">

                <!-- Ô tìm kiếm -->
                <div class="col-12 col-md-5 col-lg-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên giảng viên, mã GV hoặc tên đề tài..." value="{{ request('search') }}">
                </div>

                <!-- Lọc theo trạng thái đề tài -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <select name="trang_thai" class="form-select" onchange="this.form.submit()">
                        <option value="tat_ca">Tất cả trạng thái đề tài</option>
                        <option value="cho_duyet" {{ request('trang_thai') == 'cho_duyet' ? 'selected' : '' }}>Có đề tài Chờ duyệt</option>
                        <option value="da_duyet" {{ request('trang_thai') == 'da_duyet' ? 'selected' : '' }}>Có đề tài Đã duyệt</option>
                        <option value="yeu_cau_sua" {{ request('trang_thai') == 'yeu_cau_sua' ? 'selected' : '' }}>Có đề tài Cần chỉnh sửa</option>
                        <option value="tu_choi" {{ request('trang_thai') == 'tu_choi' ? 'selected' : '' }}>Có đề tài Bị từ chối</option>
                    </select>
                </div>

                <!-- Lọc theo tình trạng chỉ tiêu hướng dẫn -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <select name="tinh_trang_chi_tieu" class="form-select" onchange="this.form.submit()">
                        <option value="tat_ca">Tất cả tình trạng chỉ tiêu</option>
                        <option value="con_chi_tieu" {{ request('tinh_trang_chi_tieu') == 'con_chi_tieu' ? 'selected' : '' }}>Còn chỉ tiêu</option>
                        <option value="het_chi_tieu" {{ request('tinh_trang_chi_tieu') == 'het_chi_tieu' ? 'selected' : '' }}>Hết chỉ tiêu</option>
                        <option value="vuot_chi_tieu" {{ request('tinh_trang_chi_tieu') == 'vuot_chi_tieu' ? 'selected' : '' }}>Vượt chỉ tiêu</option>
                    </select>
                </div>

                <!-- Nút Thao Tác Tìm Kiếm / Xóa Lọc -->
                <div class="col-12 col-md-auto d-flex align-items-center gap-2 ms-auto">
                    <button class="btn btn-primary fw-semibold px-3" type="submit">Tìm kiếm</button>
                    @if(request()->anyFilled(['search', 'trang_thai', 'tinh_trang_chi_tieu']))
                        <a href="{{ route('truongbomon.phe_duyet_detai.index', ['hocKy' => $selectedHocKy]) }}" class="btn btn-outline-secondary px-3">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng Danh Sách Giảng Viên Thuộc Bộ Môn (Chuẩn P2: Mã GV, Họ tên, Số lượng đề tài, Chỉ tiêu, Tình trạng phê duyệt, Thao tác) -->
    <div class="admin-table-card">
        <div class="admin-table-header">
            <h5 class="admin-table-title">
                Danh Sách Giảng Viên Bộ Môn
            </h5>
            <span class="text-muted small">
                Hiển thị <strong>{{ $giangViens->count() }}</strong> giảng viên
            </span>
        </div>

        <div class="table-responsive">
            <table class="table admin-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th style="width: 120px;">Mã GV</th>
                        <th style="width: 250px;">Họ và tên giảng viên</th>
                        <th class="text-center" style="width: 140px;">Số lượng đề tài</th>
                        <th class="text-center" style="width: 160px;">Chỉ tiêu (Đã dùng/Tổng)</th>
                        <th class="text-center" style="width: 160px;">Tình trạng phê duyệt</th>
                        <th class="text-center" style="width: 220px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giangViens as $index => $gv)
                    @php
                        $stat = $topicStats[$gv->MaGV] ?? [
                            'total'       => 0,
                            'cho_duyet'   => 0,
                            'yeu_cau_sua' => 0,
                            'da_duyet'    => 0,
                            'tu_choi'     => 0,
                        ];
                        $ct = $chiTieuStats[$gv->MaGV] ?? [
                            'tong'       => 5,
                            'da_dung'    => 0,
                            'con_lai'    => 5,
                            'tinh_trang' => 'con_chi_tieu',
                            'label'      => '0/5',
                        ];
                        $hasPending = $stat['cho_duyet'] > 0;
                    @endphp
                    <tr class="{{ $hasPending ? 'row-highlight-pending' : '' }}">
                        <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                        <td>
                            <span class="badge-code">{{ $gv->MaGV }}</span>
                        </td>
                        <td>
                            <div>
                                <div class="fw-bold text-dark">
                                    {{ $gv->HoTen }}
                                </div>
                                <div class="small text-muted mt-0.5">
                                    <span>{{ $gv->HocVi ?? 'Giảng viên' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $gv->Email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($stat['total'] > 0)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold rounded-pill px-2.5 py-1">
                                    {{ $stat['total'] }} đề tài
                                </span>
                            @else
                                <span class="text-muted small">0 đề tài</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($ct['tinh_trang'] === 'con_chi_tieu')
                                <span class="badge bg-success-subtle text-success-emphasis fw-bold rounded-pill px-2.5 py-1" title="Đã dùng {{ $ct['da_dung'] }} trên {{ $ct['tong'] }} nhóm chỉ tiêu">
                                    {{ $ct['label'] }} (Còn {{ $ct['con_lai'] }})
                                </span>
                            @elseif($ct['tinh_trang'] === 'het_chi_tieu')
                                <span class="badge bg-warning-subtle text-warning-emphasis fw-bold rounded-pill px-2.5 py-1" title="Đã nhận đủ chỉ tiêu hướng dẫn">
                                    {{ $ct['label'] }} (Đã đủ)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger-emphasis fw-bold rounded-pill px-2.5 py-1" title="Đã nhận vượt chỉ tiêu">
                                    {{ $ct['label'] }} (Vượt {{ $ct['da_dung'] - $ct['tong'] }})
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($stat['total'] == 0)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis fw-semibold rounded-pill px-2.5 py-1">
                                    Chưa đề xuất
                                </span>
                            @elseif($stat['cho_duyet'] > 0)
                                <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1">
                                    Chờ duyệt ({{ $stat['cho_duyet'] }})
                                </span>
                            @elseif($stat['yeu_cau_sua'] > 0)
                                <span class="badge bg-info-subtle text-info-emphasis fw-semibold rounded-pill px-2.5 py-1">
                                    Cần sửa ({{ $stat['yeu_cau_sua'] }})
                                </span>
                            @elseif($stat['da_duyet'] > 0 && $stat['total'] == $stat['da_duyet'])
                                <span class="badge bg-success-subtle text-success-emphasis fw-semibold rounded-pill px-2.5 py-1">
                                    Đã duyệt hết
                                </span>
                            @elseif($stat['tu_choi'] > 0 && $stat['total'] == $stat['tu_choi'])
                                <span class="badge bg-danger-subtle text-danger-emphasis fw-semibold rounded-pill px-2.5 py-1">
                                    Bị từ chối
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary-emphasis fw-semibold rounded-pill px-2.5 py-1">
                                    Đã xử lý
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1.5">
                                <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $gv->MaGV, 'hocKy' => $selectedHocKy]) }}" 
                                   class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold text-nowrap"
                                   title="Xem chi tiết danh sách đề tài của giảng viên này">
                                    <i class="fa-solid fa-list-check me-1"></i>Xem chi tiết
                                </a>
                                @if($hasPending)
                                <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $gv->MaGV, 'hocKy' => $selectedHocKy, 'trang_thai' => 'cho_duyet']) }}" 
                                   class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold text-dark text-nowrap"
                                   title="Phê duyệt các đề tài đang chờ">
                                    <i class="fa-solid fa-check me-1"></i>Duyệt
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-3 text-muted">
                                <p class="fw-semibold mb-1">Không tìm thấy giảng viên nào phù hợp với bộ lọc hiện tại</p>
                                <p class="small mb-0">Vui lòng thử lại với từ khóa tìm kiếm hoặc tùy chọn lọc khác.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top py-2.5 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small text-muted">
                Tổng cộng <strong>{{ $giangViens->count() }}</strong> giảng viên trong bộ môn {{ $boMon->TenBoMon ?? '' }}. Học kỳ: <strong>{{ $selectedHocKy }}</strong>.
            </span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2.5 py-1 small">
                    Dòng nền vàng: Giảng viên có đề tài chờ phê duyệt
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
