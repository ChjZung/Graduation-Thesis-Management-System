@extends('layouts.admin')

@section('page_title', 'Xét Duyệt & Thống Kê Đề Tài Khóa Luận')

@section('content')

{{-- ── TIÊU ĐỀ TRANG & NÚT THAO TÁC NHANH ── --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="page-main-title mb-1">
            <i class="fa-solid fa-file-signature text-primary me-2"></i>
            Quản Lý, Xét Duyệt & Thống Kê Đề Tài Khóa Luận
        </h4>
        <p class="page-main-subtitle text-muted mb-0">
            Tiếp nhận đề xuất từ Giảng viên, kiểm tra hồ sơ đề cương, phê duyệt và công bố đề tài chính thức cho sinh viên đăng ký.
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#publishModal">
            <i class="fa-solid fa-bullhorn me-1"></i> Công Bố Đề Tài Đã Duyệt
        </button>
        <a href="{{ route('admin.duyet_detai.export', request()->all()) }}" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-semibold">
            <i class="fa-solid fa-file-excel me-1"></i> Xuất File Excel / CSV
        </a>
    </div>
</div>

{{-- ── TAB CHÍNH: XÉT DUYỆT vs THỐNG KÊ ── --}}
<ul class="nav nav-pills mb-3 border-bottom pb-2 gap-2" id="detaiTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ request('tab', 'danh_sach') === 'danh_sach' ? 'active' : '' }} fw-semibold rounded-pill px-4" 
                id="danh-sach-tab" data-bs-toggle="pill" data-bs-target="#danh-sach-pane" type="button" role="tab">
            <i class="fa-solid fa-list-check me-1"></i> Danh Sách Xét Duyệt
            @if(($counts['cho_duyet'] ?? 0) > 0)
                <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $counts['cho_duyet'] }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ request('tab') === 'thong_ke' ? 'active' : '' }} fw-semibold rounded-pill px-4" 
                id="thong-ke-tab" data-bs-toggle="pill" data-bs-target="#thong-ke-pane" type="button" role="tab">
            <i class="fa-solid fa-chart-pie me-1"></i> Thống Kê Đề Tài Toàn Khoa
            <span class="badge bg-secondary ms-1 rounded-pill">{{ $counts['total'] ?? 0 }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="detaiTabContent">

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: DANH SÁCH XÉT DUYỆT ĐỀ TÀI --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab', 'danh_sach') === 'danh_sach' ? 'show active' : '' }}" id="danh-sach-pane" role="tabpanel">

        {{-- PILL TABS TRẠNG THÁI --}}
        <div class="admin-pill-tabs mb-3">
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Chờ duyệt', 'tab' => 'danh_sach'])) }}" 
               class="admin-pill-tab {{ request('TrangThai', 'Chờ duyệt') === 'Chờ duyệt' ? 'active' : '' }}">
                <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#f59e0b;"></span>
                <span class="fw-bold">{{ $counts['cho_duyet'] ?? 0 }}</span> Chờ Duyệt
            </a>
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Đã duyệt', 'tab' => 'danh_sach'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Đã duyệt' ? 'active' : '' }}">
                <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#10b981;"></span>
                <span class="fw-bold">{{ $counts['da_duyet'] ?? 0 }}</span> Đã Duyệt (Chờ Công Bố)
            </a>
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Đã công bố', 'tab' => 'danh_sach'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Đã công bố' ? 'active' : '' }}">
                <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#0072ce;"></span>
                <span class="fw-bold">{{ $counts['da_cong_bo'] ?? 0 }}</span> Đã Công Bố (SV Được Đăng Ký)
            </a>
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Yêu cầu điều chỉnh', 'tab' => 'danh_sach'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Yêu cầu điều chỉnh' ? 'active' : '' }}">
                <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#ea580c;"></span>
                <span class="fw-bold">{{ $counts['yeu_cau_sua'] ?? 0 }}</span> Yêu Cầu Chỉnh Sửa
            </a>
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'Từ chối', 'tab' => 'danh_sach'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'Từ chối' ? 'active' : '' }}">
                <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#ef4444;"></span>
                <span class="fw-bold">{{ $counts['tu_choi'] ?? 0 }}</span> Từ Chối
            </a>
            <a href="{{ route('admin.duyet_detai.index', array_merge(request()->except('page'), ['TrangThai' => 'ALL', 'tab' => 'danh_sach'])) }}" 
               class="admin-pill-tab {{ request('TrangThai') === 'ALL' ? 'active' : '' }}">
                <span class="fw-bold">{{ $counts['total'] ?? 0 }}</span> Tất Cả Đề Tài
            </a>
        </div>

        {{-- BỘ LỌC TÌM KIẾM --}}
        <div class="admin-filter-bar mb-3">
            <form method="GET" action="{{ route('admin.duyet_detai.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="danh_sach">
                <input type="hidden" name="TrangThai" value="{{ request('TrangThai', 'Chờ duyệt') }}">
                
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Tên đề tài, mã, tên GV..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="HocPhan" class="form-select form-select-sm">
                        <option value="">-- Môn / Học phần --</option>
                        @foreach($hocPhans as $hp)
                            <option value="{{ $hp }}" {{ request('HocPhan') == $hp ? 'selected' : '' }}>{{ $hp }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="MaBoMon" class="form-select form-select-sm">
                        <option value="">-- Tất cả Bộ môn --</option>
                        @foreach($boMons as $bm)
                            <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                {{ $bm->TenBoMon }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="MaNganh" class="form-select form-select-sm">
                        <option value="">-- Tất cả Ngành --</option>
                        @foreach($nganhs as $ng)
                            <option value="{{ $ng->MaNganh }}" {{ request('MaNganh') == $ng->MaNganh ? 'selected' : '' }}>
                                {{ $ng->TenNganh }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="MaHocKy" class="form-select form-select-sm">
                        <option value="">-- Tất cả Học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 w-100 fw-semibold" title="Tìm kiếm">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.duyet_detai.index', ['tab' => 'danh_sach']) }}" class="btn btn-sm btn-light border rounded-3 text-secondary" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- BẢNG DỮ LIỆU ĐỀ TÀI --}}
        <div class="admin-table-card">
            <div class="admin-table-header">
                <h5 class="admin-table-header-title">
                    <i class="fa-regular fa-file-lines text-primary me-2"></i>
                    Danh Sách Đề Tài ({{ $detais->total() }} kết quả)
                </h5>
                <span class="small text-muted">
                    Học kỳ hiện tại: <strong>{{ $currentHocKy->TenHocKy ?? '' }} ({{ $currentHocKy->NamHoc ?? '' }})</strong>
                </span>
            </div>

            <div class="table-responsive">
                <table class="table admin-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Mã ĐT</th>
                            <th style="min-width: 320px;">Tên Đề Tài & Hướng Nghiên Cứu</th>
                            <th style="min-width: 200px;">Giảng Viên Đề Xuất</th>
                            <th class="text-center" style="width: 110px;">Chỉ Tiêu & Đề Cương</th>
                            <th class="text-center" style="width: 130px;">Trạng Thái</th>
                            <th class="text-center" style="width: 180px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detais as $dt)
                        <tr>
                            {{-- Mã ĐT --}}
                            <td>
                                <span class="badge-code">{{ $dt->MaDeTai }}</span>
                                <div class="small text-muted mt-1" style="font-size: 0.72rem;">{{ $dt->hocKy->TenHocKy ?? '' }}</div>
                            </td>

                            {{-- Tên ĐT --}}
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    {{ $dt->TenDeTai }}
                                </div>
                                <div class="text-muted mt-1 small" style="font-size: 0.78rem;">
                                    {{ Str::limit($dt->MoTa, 90, '...') }}
                                </div>
                                <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                    <span class="badge bg-primary-subtle text-primary border px-2 py-1" style="font-size: 0.72rem;">
                                        {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                                    </span>
                                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">
                                        {{ $dt->LinhVuc ?? 'CNTT' }}
                                    </span>
                                    @if($dt->nganh)
                                        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">
                                            {{ $dt->nganh->TenNganh }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                                            Toàn khoa
                                        </span>
                                    @endif
                                </div>

                                @if($dt->LyDoTuChoi && in_array($dt->TrangThai, ['Yêu cầu điều chỉnh', 'Từ chối']))
                                    <div class="small text-danger mt-1 p-2 bg-danger-subtle rounded border border-danger-subtle" style="font-size: 0.76rem;">
                                        <strong>Phản hồi:</strong> {{ $dt->LyDoTuChoi }}
                                    </div>
                                @endif
                            </td>

                            {{-- GV đề xuất --}}
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                                    {{ $dt->giangVien->HocHam ? $dt->giangVien->HocHam . '.' : '' }}{{ $dt->giangVien->HocVi ? $dt->giangVien->HocVi . ' ' : '' }}{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}
                                </div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    Bộ môn: {{ $dt->giangVien->boMon->TenBoMon ?? 'Chưa phân bổ' }}
                                </div>
                                <div class="text-secondary small mt-1" style="font-size: 0.75rem;">
                                    Mã GV: <strong>{{ $dt->MaGV }}</strong>
                                </div>
                            </td>

                            {{-- Chỉ tiêu & Đề cương --}}
                            <td class="text-center">
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                    {{ $dt->SoLuongSinhVienToiDa ?? 2 }} SV
                                </div>
                                @if($dt->FileDeCuong)
                                    <a href="{{ asset($dt->FileDeCuong) }}" target="_blank" class="badge bg-success-subtle text-success border text-decoration-none px-2 py-1" title="Tải về xem đề cương chi tiết">
                                        <i class="fa-solid fa-file-word me-1"></i> Có Đề Cương
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">Chưa nộp</span>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center">
                                @if($dt->TrangThai === 'Đã công bố')
                                    <span class="badge bg-primary text-white rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-bullhorn me-1"></i>Đã công bố
                                    </span>
                                @elseif($dt->TrangThai === 'Đã duyệt')
                                    <span class="badge bg-success text-white rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-check me-1"></i>Đã phê duyệt
                                    </span>
                                @elseif($dt->TrangThai === 'Yêu cầu điều chỉnh')
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-wrench me-1"></i>Yêu cầu sửa
                                    </span>
                                @elseif($dt->TrangThai === 'Từ chối')
                                    <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-xmark me-1"></i>Từ chối
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-clock me-1"></i>Chờ xét duyệt
                                    </span>
                                @endif
                            </td>

                            {{-- Thao tác --}}
                            <td class="text-center">
                                <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                    {{-- Nút xem chi tiết --}}
                                    <button type="button" class="btn btn-sm btn-light border rounded-3 px-2 py-1 text-primary fw-semibold" 
                                            data-bs-toggle="modal" data-bs-target="#detailModal{{ $dt->MaDeTai }}" title="Xem chi tiết đề tài">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    @if($dt->TrangThai === 'Chờ duyệt')
                                        {{-- Nút Phê Duyệt --}}
                                        <form action="{{ route('admin.duyet_detai.approve', $dt->MaDeTai) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-3 px-2 py-1 fw-semibold" 
                                                    onclick="return confirm('Xác nhận phê duyệt đề tài \'{{ addslashes($dt->TenDeTai) }}\'?');" title="Phê duyệt đề tài">
                                                <i class="fa-solid fa-check me-1"></i>Duyệt
                                            </button>
                                        </form>

                                        {{-- Nút Yêu Cầu Sửa --}}
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-3 px-2 py-1 fw-semibold" 
                                                data-bs-toggle="modal" data-bs-target="#requestEditModal{{ $dt->MaDeTai }}" title="Yêu cầu giảng viên chỉnh sửa">
                                            Sửa
                                        </button>

                                        {{-- Nút Từ Chối --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1 fw-semibold" 
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $dt->MaDeTai }}" title="Từ chối đề tài">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @elseif($dt->TrangThai === 'Đã duyệt')
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-3 px-2 py-1" 
                                                data-bs-toggle="modal" data-bs-target="#requestEditModal{{ $dt->MaDeTai }}" title="Yêu cầu điều chỉnh lại">
                                            Sửa lại
                                        </button>
                                    @endif
                                </div>

                                {{-- MODAL CHI TIẾT ĐỀ TÀI --}}
                                <div class="modal fade" id="detailModal{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content rounded-4 border-0 shadow text-start">
                                            <div class="modal-header border-bottom pb-3">
                                                <h5 class="modal-title fw-bold text-primary">
                                                    <i class="fa-solid fa-circle-info me-2"></i>Chi Tiết Đề Tài: {{ $dt->MaDeTai }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body py-3">
                                                <div class="mb-3">
                                                    <span class="badge bg-primary-subtle text-primary border me-2">Môn: {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}</span>
                                                    <span class="badge bg-light text-dark border me-2">Học kỳ: {{ $dt->hocKy->TenHocKy ?? '' }}</span>
                                                    <span class="badge bg-light text-primary border me-2">Ngành: {{ $dt->nganh->TenNganh ?? 'Toàn khoa' }}</span>
                                                    <span class="badge bg-light text-secondary border me-2">Lĩnh vực: {{ $dt->LinhVuc ?? 'CNTT' }}</span>
                                                    <span class="badge bg-light text-dark border">Tối đa: {{ $dt->SoLuongSinhVienToiDa ?? 2 }} SV</span>
                                                </div>

                                                <h5 class="fw-bold text-dark mb-2">{{ $dt->TenDeTai }}</h5>

                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                    <div class="small fw-bold text-secondary mb-1">GIẢNG VIÊN HƯỚNG DẪN</div>
                                                    <div class="fw-semibold text-dark">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }} ({{ $dt->giangVien->boMon->TenBoMon ?? '' }})</div>
                                                    <div class="small text-muted">Email: {{ $dt->giangVien->Email ?? 'N/A' }} | SĐT: {{ $dt->giangVien->SoDienThoai ?? 'N/A' }}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-align-left me-1 text-primary"></i> Mô Tả & Mục Tiêu Đề Tài:</h6>
                                                    <div class="p-3 bg-white border rounded-3 text-secondary" style="white-space: pre-line;">
                                                        {{ $dt->MoTa ?: 'Chưa có mô tả chi tiết.' }}
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-screwdriver-wrench me-1 text-primary"></i> Yêu Cầu Đối Với Sinh Viên:</h6>
                                                    <div class="p-3 bg-white border rounded-3 text-secondary" style="white-space: pre-line;">
                                                        {{ $dt->YeuCau ?: 'Không có yêu cầu đặc thù.' }}
                                                    </div>
                                                </div>

                                                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="fw-bold text-dark"><i class="fa-solid fa-paperclip text-primary me-1"></i> Hồ Sơ Đề Cương Chi Tiết:</span>
                                                        @if($dt->FileDeCuong)
                                                            <div class="small text-success mt-1">Đã đính kèm: {{ basename($dt->FileDeCuong) }}</div>
                                                        @else
                                                            <div class="small text-muted mt-1">Giảng viên chưa nộp tệp đề cương mẫu đính kèm.</div>
                                                        @endif
                                                    </div>
                                                    @if($dt->FileDeCuong)
                                                        <a href="{{ asset($dt->FileDeCuong) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                                                            <i class="fa-solid fa-download me-1"></i> Tải Đề Cương
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top pt-2">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- MODAL YÊU CẦU ĐIỀU CHỈNH --}}
                                <div class="modal fade" id="requestEditModal{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form action="{{ route('admin.duyet_detai.request_edit', $dt->MaDeTai) }}" method="POST">
                                            @csrf
                                            <div class="modal-content rounded-4 border-0 shadow text-start">
                                                <div class="modal-header border-bottom pb-3">
                                                    <h5 class="modal-title fw-bold text-warning-emphasis">
                                                        <i class="fa-solid fa-wrench me-2"></i>Yêu Cầu Chỉnh Sửa / Bổ Sung Đề Tài
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="small text-muted mb-2">Đề tài: <strong>{{ $dt->TenDeTai }}</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Nội Dung Cần Giảng Viên Điều Chỉnh <span class="text-danger">*</span></label>
                                                        <textarea name="YeuCauSua" class="form-control" rows="4" placeholder="Nhập chi tiết các nội dung, phạm vi hoặc đề cương cần giảng viên hoàn thiện..." required>{{ $dt->LyDoTuChoi }}</textarea>
                                                        <div class="form-text text-muted">Ý kiến này sẽ hiển thị trực tiếp trong trang quản lý đề tài của Giảng viên.</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top pt-2">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Gửi Yêu Cầu Cho GV</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- MODAL TỪ CHỐI ĐỀ TÀI --}}
                                <div class="modal fade" id="rejectModal{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form action="{{ route('admin.duyet_detai.reject', $dt->MaDeTai) }}" method="POST">
                                            @csrf
                                            <div class="modal-content rounded-4 border-0 shadow text-start">
                                                <div class="modal-header border-bottom pb-3">
                                                    <h5 class="modal-title fw-bold text-danger">
                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Từ Chối Đề Tài Khóa Luận
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="small text-muted mb-2">Đề tài: <strong>{{ $dt->TenDeTai }}</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Lý Do Từ Chối <span class="text-danger">*</span></label>
                                                        <textarea name="LyDoTuChoi" class="form-control" rows="4" placeholder="Nhập lý do không phê duyệt đề tài (trùng lặp, không khả thi, không đúng định hướng)..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top pt-2">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Từ Chối</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                Không có đề tài nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($detais->hasPages())
            <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff;">
                <div class="text-muted" style="font-size: 0.82rem;">
                    Hiển thị <strong>{{ $detais->firstItem() ?? 1 }}–{{ $detais->lastItem() ?? $detais->count() }}</strong> trên tổng số <strong>{{ $detais->total() }}</strong> đề tài
                </div>
                <div>
                    {{ $detais->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: THỐNG KÊ ĐỀ TÀI TOÀN KHOA --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade {{ request('tab') === 'thong_ke' ? 'show active' : '' }}" id="thong-ke-pane" role="tabpanel">

        {{-- SUMMARY TIÊU ĐIỂM --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-light">
                    <div class="small text-muted fw-semibold">Tổng Số Đề Tài Đề Xuất</div>
                    <div class="fs-3 fw-bold text-dark mt-1">{{ $counts['total'] }}</div>
                    <div class="small text-muted mt-1">Toàn thể cán bộ GV Khoa</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-light">
                    <div class="small text-muted fw-semibold">Đã Công Bố Cho Sinh Viên</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ $counts['da_cong_bo'] }}</div>
                    <div class="small text-muted mt-1">Sinh viên đủ điều kiện được đăng ký</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-light">
                    <div class="small text-muted fw-semibold">Đã Duyệt (Chờ Công Bố)</div>
                    <div class="fs-3 fw-bold text-success mt-1">{{ $counts['da_duyet'] }}</div>
                    <div class="small text-muted mt-1">Sẵn sàng mở đợt công bố</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-light">
                    <div class="small text-muted fw-semibold">Đang Chờ Xử Lý / Duyệt</div>
                    <div class="fs-3 fw-bold text-warning-emphasis mt-1">{{ $counts['cho_duyet'] }}</div>
                    <div class="small text-muted mt-1">Giáo vụ cần rà soát</div>
                </div>
            </div>
        </div>

        {{-- PHẦN 1: THỐNG KÊ THEO BỘ MÔN --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-building-columns text-primary me-2"></i>1. Thống Kê Đề Tài Theo Bộ Môn
                </h6>
                <span class="small text-muted">{{ $thongKeBoMon->count() }} Bộ môn trực thuộc</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Mã Bộ Môn</th>
                            <th style="width: 30%;">Tên Bộ Môn</th>
                            <th class="text-center" style="width: 12%;">Số Giảng Viên</th>
                            <th class="text-center" style="width: 10%;">Tổng ĐT</th>
                            <th class="text-center" style="width: 10%;">Chờ Duyệt</th>
                            <th class="text-center" style="width: 10%;">Đã Duyệt</th>
                            <th class="text-center" style="width: 13%;">Đã Công Bố</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thongKeBoMon as $bm)
                        <tr>
                            <td><span class="badge bg-light text-dark border fw-bold">{{ $bm->MaBoMon }}</span></td>
                            <td class="fw-semibold text-dark">{{ $bm->TenBoMon }}</td>
                            <td class="text-center">{{ $bm->giang_viens_count ?? 0 }} GV</td>
                            <td class="text-center fw-bold">{{ $bm->tong_detai }}</td>
                            <td class="text-center">
                                @if($bm->cho_duyet > 0)
                                    <span class="badge bg-warning text-dark">{{ $bm->cho_duyet }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center text-success fw-bold">{{ $bm->da_duyet }}</td>
                            <td class="text-center text-primary fw-bold">{{ $bm->da_cong_bo }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3 text-muted">Chưa có dữ liệu bộ môn.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PHẦN 2: THỐNG KÊ THEO NGÀNH & CHUYÊN NGÀNH --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-graduation-cap text-primary me-2"></i>2. Thống Kê Đề Tài Theo Ngành & Chuyên Ngành
                </h6>
                <span class="small text-muted">{{ $thongKeNganh->count() }} Ngành đào tạo</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Mã Ngành</th>
                            <th style="width: 25%;">Tên Ngành Đào Tạo</th>
                            <th style="width: 25%;">Chuyên Ngành Trực Thuộc</th>
                            <th class="text-center" style="width: 10%;">Tổng ĐT</th>
                            <th class="text-center" style="width: 12%;">Đã Công Bố</th>
                            <th class="text-center" style="width: 13%;">Chỉ Tiêu SV Tối Đa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thongKeNganh as $ng)
                        <tr>
                            <td><span class="badge bg-light text-primary border fw-bold">{{ $ng->MaNganh }}</span></td>
                            <td class="fw-semibold text-dark">{{ $ng->TenNganh }}</td>
                            <td>
                                @if($ng->chuyenNganhs && $ng->chuyenNganhs->count() > 0)
                                    @foreach($ng->chuyenNganhs as $cn)
                                        <span class="badge bg-light text-secondary border me-1 mb-1">{{ $cn->TenChuyenNganh }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Chưa phân chuyên ngành</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $ng->tong_detai }}</td>
                            <td class="text-center text-primary fw-bold">{{ $ng->da_cong_bo }}</td>
                            <td class="text-center fw-bold text-success">{{ $ng->tong_chi_tieu_sv ?? 0 }} SV</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">Chưa có dữ liệu ngành.</td></tr>
                        @endforelse
                        <tr class="table-light">
                            <td colspan="3" class="fw-bold text-secondary">
                                <i class="fa-solid fa-asterisk me-1 text-primary"></i> Đề tài định hướng chung (Áp dụng toàn khoa)
                            </td>
                            <td class="text-center fw-bold">{{ $deTaiToanKhoa['tong'] }}</td>
                            <td class="text-center text-primary fw-bold">{{ $deTaiToanKhoa['da_cong_bo'] }}</td>
                            <td class="text-center text-muted small">—</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PHẦN 3: THỐNG KÊ THEO GIẢNG VIÊN HƯỚNG DẪN --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>3. Thống Kê Đề Tài Theo Giảng Viên
                </h6>
                <span class="small text-muted">{{ $thongKeGiangVien->count() }} Giảng viên</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 12%;">Mã GV</th>
                            <th style="width: 28%;">Họ Và Tên Giảng Viên</th>
                            <th style="width: 20%;">Bộ Môn</th>
                            <th class="text-center" style="width: 10%;">Tổng Đề Xuất</th>
                            <th class="text-center" style="width: 10%;">Đã Duyệt / CB</th>
                            <th class="text-center" style="width: 10%;">Chờ Duyệt</th>
                            <th class="text-center" style="width: 10%;">Nhóm Đã Nhận</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($thongKeGiangVien as $gv)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $gv->MaGV }}</span></td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $gv->HocHam ? $gv->HocHam . '.' : '' }}{{ $gv->HocVi ? $gv->HocVi . ' ' : '' }}{{ $gv->HoTen }}
                                </div>
                                <div class="small text-muted">{{ $gv->Email }}</div>
                            </td>
                            <td>{{ $gv->boMon->TenBoMon ?? 'N/A' }}</td>
                            <td class="text-center fw-bold">{{ $gv->tong_detai }}</td>
                            <td class="text-center text-success fw-bold">{{ $gv->da_duyet }}</td>
                            <td class="text-center">
                                @if($gv->cho_duyet > 0)
                                    <span class="badge bg-warning text-dark">{{ $gv->cho_duyet }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center text-primary fw-bold">{{ $gv->so_nhom_nhan }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-3 text-muted">Chưa có dữ liệu giảng viên.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

{{-- MODAL CÔNG BỐ ĐỀ TÀI ĐÃ DUYỆT TOÀN KHOA --}}
<div class="modal fade" id="publishModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.duyet_detai.publish') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fa-solid fa-bullhorn me-2"></i>Công Bố Đề Tài Khóa Luận Chính Thức
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary fw-semibold">Số đề tài đã duyệt sẵn sàng công bố:</span>
                            <span class="fs-4 fw-bold text-success">{{ $counts['da_duyet'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Chọn Học Kỳ Áp Dụng:</label>
                        <select name="MaHocKy" class="form-select">
                            <option value="">-- Tất cả đề tài đã duyệt --</option>
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ ($currentHocKy && $currentHocKy->MaHocKy == $hk->MaHocKy) ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="small text-muted">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                        Sau khi công bố, trạng thái của các đề tài sẽ chuyển thành <strong>"Đã công bố"</strong>. 
                        Sinh viên đủ điều kiện sẽ có thể nhìn thấy và đại diện nhóm tiến hành đăng ký. Đồng thời hệ thống sẽ tự động phát thông báo chung tới sinh viên.
                    </div>
                </div>
                <div class="modal-footer border-top pt-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" {{ $counts['da_duyet'] == 0 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Công Bố
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
