@extends('layouts.sinhvien')

@section('page_title', 'Đăng Ký Đề Tài Khóa Luận')

@section('content')
<style>
    .dangky-page-root {
        font-size: 14px;
    }
    .dangky-page-root .small, 
    .dangky-page-root small, 
    .dangky-page-root .badge, 
    .dangky-page-root .text-muted,
    .dangky-page-root .btn-sm,
    .dangky-page-root .form-select-sm,
    .dangky-page-root .form-control-sm {
        font-size: 13px !important;
    }
    /* Khung bộ lọc chuẩn mực, cân đối 3 cột 100% thẳng hàng không bị lệch */
    .filter-item-box {
        display: flex;
        align-items: center;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        height: 38px;
        padding: 0 10px;
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        width: 100%;
    }
    .filter-item-box:hover, .filter-item-box:focus-within {
        border-color: #00838C;
        background-color: #ffffff;
        box-shadow: 0 1px 4px rgba(0, 131, 140, 0.12);
    }
    .filter-item-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        margin-right: 8px;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }
    .filter-item-select {
        border: none !important;
        background-color: transparent !important;
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        padding: 0 4px;
        box-shadow: none !important;
        cursor: pointer;
        width: 100%;
    }
    .filter-item-select:focus {
        outline: none;
    }
    .filter-item-static {
        font-size: 13px;
        font-weight: 600;
        color: #004E64;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        line-height: 36px;
    }
    .table-topic th {
        background-color: #003366 !important;
        font-weight: 700;
        font-size: 13px !important;
        padding: 10px 12px !important;
        color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        white-space: nowrap;
    }
    .table-topic td {
        font-size: 13px !important;
        padding: 10px 12px !important;
        vertical-align: middle;
        line-height: 1.45;
        border-bottom: 1px solid #f1f5f9;
    }
    .topic-row:hover {
        background-color: #f8fafc;
    }
    .btn-theme-register {
        background-color: #004E64 !important;
        color: #ffffff !important;
        border: 1px solid #004E64 !important;
        transition: all 0.15s ease;
    }
    .btn-theme-register:hover {
        background-color: #003F5F !important;
        border-color: #003F5F !important;
        box-shadow: 0 2px 6px rgba(0, 63, 95, 0.25);
    }
    .btn-outline-outline-doc {
        border-color: #25A18E !important;
        color: #004E64 !important;
        background-color: #e8f8f2 !important;
        font-weight: 600;
    }
    .btn-outline-outline-doc:hover {
        background-color: #25A18E !important;
        color: #ffffff !important;
    }
    .topic-scroll-container {
        max-height: calc(100vh - 350px);
        min-height: 380px;
        overflow-y: auto;
    }
</style>

<div class="dangky-page-root">
    <!-- TIÊU ĐỀ TRANG & DẢI TRẠNG THÁI GỌN GÀNG -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem;">
                <i class="fa-solid fa-book-bookmark me-2 text-primary"></i>Đăng Ký Đề Tài Khóa Luận
            </h5>
            <span class="badge bg-light text-muted border px-2.5 py-1">
                {{ $hocKies->firstWhere('MaHocKy', $selectedHocKy)->TenHocKy ?? 'Học kỳ hiện tại' }}
            </span>
        </div>
        @if(isset($regState))
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $regState['badge'] }} rounded-pill px-3 py-1.5 fw-semibold shadow-xs">
                <i class="fa-solid fa-clock me-1"></i>{{ $regState['label'] }}
            </span>
        </div>
        @endif
    </div>

    <!-- DẢI THÔNG BÁO TỔNG HỢP: TIẾN ĐỘ THỜI GIAN & ĐIỀU KIỆN NHÓM (GỌN GÀNG 1 KHỐI DUY NHẤT) -->
    <div class="card border-0 shadow-xs rounded-3 mb-3 p-2.5 bg-white border border-slate-200">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 13px;">
            <!-- Trạng thái giai đoạn đăng ký -->
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <i class="fa-solid fa-circle-info text-primary fs-6"></i>
                <span class="text-dark">
                    {{ $regState['message'] ?? 'Theo dõi lịch công bố và đăng ký đề tài chính thức của khoa.' }}
                </span>
            </div>

            <!-- Tình trạng nhóm của sinh viên -->
            @if(isset($nhom) && $nhom)
                @php $isGroupReady = ($soThanhVien >= 3); @endphp
                <div class="d-flex align-items-center gap-2">
                    @if($isGroupReady)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="fa-solid fa-circle-check me-1"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 TV &bull; Đủ điều kiện ĐK)
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 TV &bull; Thiếu {{ 3 - $soThanhVien }} TV)
                        </span>
                        <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $nhom->MaHocPhan, 'hoc_ky' => $nhom->MaHocKy]) }}" 
                           class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold text-dark shadow-xs" style="font-size: 12.5px !important;">
                            <i class="fa-solid fa-user-plus me-1"></i>Mời thêm
                        </a>
                    @endif
                </div>
            @else
                <div>
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-1.5 rounded-pill">
                        <i class="fa-solid fa-users-slash me-1"></i>Chưa tham gia nhóm
                    </span>
                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 ms-1 fw-semibold" style="font-size: 12.5px !important;">
                        <i class="fa-solid fa-user-group me-1"></i>Vào Quản Trị Nhóm
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- ĐƠN ĐĂNG KÝ HIỆN TẠI CỦA NHÓM (NẾU CÓ ĐỀ TÀI ĐÃ ĐƯỢC GÁN/ĐĂNG KÝ) -->
    @if(isset($dangKyCurrent) && $dangKyCurrent)
    <div class="card mb-3 border-start border-4 {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? 'border-success' : ($dangKyCurrent->TrangThai === 'Từ chối' ? 'border-danger' : 'border-warning') }} shadow-xs">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" 
                         style="width: 38px; height: 38px; background-color: {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? '#25A18E' : ($dangKyCurrent->TrangThai === 'Từ chối' ? '#dc2626' : '#f59e0b') }}; flex-shrink: 0;">
                        <i class="fa-solid {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? 'fa-check' : ($dangKyCurrent->TrangThai === 'Từ chối' ? 'fa-xmark' : 'fa-hourglass-half') }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold text-dark fs-6">{{ $dangKyCurrent->deTai->TenDeTai ?? 'Chưa rõ tên đề tài' }}</span>
                            <span class="badge bg-light text-muted border"><code>{{ $dangKyCurrent->deTai->MaDeTai ?? '' }}</code></span>
                        </div>
                        <div class="text-secondary d-flex align-items-center gap-3 mt-0.5" style="font-size: 12.5px;">
                            <span><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i>GVHD: <strong>{{ $dangKyCurrent->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong></span>
                            <span>&bull;</span>
                            <span>Trạng thái: 
                                @if($dangKyCurrent->TrangThai === 'Từ chối')
                                    <strong class="text-danger">Đã từ chối</strong> ({{ $dangKyCurrent->LyDoTuChoi ?? 'Đề tài không phù hợp' }})
                                @elseif($dangKyCurrent->TrangThai === 'Đã duyệt')
                                    <strong class="text-success">Đã gán chính thức cho nhóm</strong>
                                @else
                                    <strong class="text-warning">Chờ phê duyệt</strong>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @php
                        $hasDeCuong = !empty($dangKyCurrent->deTai?->FileDeCuong);
                    @endphp
                    @if($hasDeCuong)
                        @php
                            $fileDcUrl = Str::startsWith($dangKyCurrent->deTai->FileDeCuong, ['http', 'storage/']) ? asset($dangKyCurrent->deTai->FileDeCuong) : asset('storage/' . $dangKyCurrent->deTai->FileDeCuong);
                            $fileName = basename($dangKyCurrent->deTai->FileDeCuong);
                        @endphp
                        <button type="button" class="btn btn-sm btn-outline-outline-doc rounded-pill px-3 py-1 shadow-xs"
                                onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($dangKyCurrent->deTai->TenDeTai ?? '') }}')">
                            <i class="fa-solid fa-eye me-1"></i>Xem Đề Cương
                        </button>
                    @endif

                    @if(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV && $dangKyCurrent->TrangThai !== 'Đã duyệt')
                        <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký đề tài này để chọn đề tài khác?');" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">
                                <i class="fa-solid fa-rotate-left me-1"></i>Hủy Đăng Ký
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- BỘ LỌC ĐỀ TÀI TOÀN CỤC: THIẾT KẾ CÂN ĐỐI 100%, 3 CỘT ĐỀU NHAU, KHÔNG LỆCH HÀNG -->
    <div class="card mb-3 bg-white border shadow-xs rounded-3">
        <div class="card-body p-2.5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <span class="fw-bold text-dark" style="font-size: 13px;">
                    <i class="fa-solid fa-filter text-primary me-1.5"></i>Bộ Lọc Đề Tài Khóa Luận:
                </span>
                @if(!empty($isGroupLocked) && isset($nhom))
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 fw-semibold" style="font-size: 12px;">
                        <i class="fa-solid fa-lock me-1 text-primary"></i>Đã khóa bộ lọc theo nhóm: <strong>{{ $nhom->TenNhom }}</strong>
                    </span>
                @endif
            </div>

            <!-- 3 CỘT CÂN ĐỐI HOÀN TOÀN: col-12 col-md-4 MỖI CỘT -->
            <div class="row g-2 align-items-center">
                <!-- CỘT 1: CHỌN HỌC KỲ -->
                <div class="col-12 col-md-4">
                    <div class="filter-item-box">
                        <span class="filter-item-label">
                            <i class="fa-solid fa-calendar-days text-primary me-1.5"></i>Học kỳ:
                        </span>
                        @if(!empty($isGroupLocked))
                            <select class="filter-item-select text-muted" disabled>
                                <option selected>
                                    {{ $hocKies->firstWhere('MaHocKy', $selectedHocKy)->TenHocKy ?? $selectedHocKy }} (Khóa theo nhóm)
                                </option>
                            </select>
                        @else
                            <select class="filter-item-select" onchange="window.location.href=this.value;">
                                @foreach($hocKies as $hk)
                                    <option value="{{ route('sinhvien.dangky.index', ['HocKy' => $hk->MaHocKy, 'HocPhan' => $selectedHocPhan]) }}" {{ ($selectedHocKy == $hk->MaHocKy || $maHocKy == $hk->MaHocKy) ? 'selected' : '' }}>
                                        {{ $hk->TenHocKy }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <!-- CỘT 2: BỘ MÔN CHUYÊN NGÀNH -->
                <div class="col-12 col-md-4">
                    <div class="filter-item-box">
                        <span class="filter-item-label">
                            <i class="fa-solid fa-sitemap text-primary me-1.5"></i>Bộ môn:
                        </span>
                        <div class="filter-item-static" title="{{ $boMons->firstWhere('MaBoMon', $selectedBoMon)->TenBoMon ?? ($sinhVien->lop->nganh->TenNganh ?? 'Bộ môn chuyên ngành') }}">
                            {{ $boMons->firstWhere('MaBoMon', $selectedBoMon)->TenBoMon ?? ($sinhVien->lop->nganh->TenNganh ?? 'Bộ môn chuyên ngành') }}
                        </div>
                    </div>
                </div>

                <!-- CỘT 3: HỌC PHẦN CHUYÊN NGÀNH -->
                <div class="col-12 col-md-4">
                    <div class="filter-item-box">
                        <span class="filter-item-label">
                            <i class="fa-solid fa-graduation-cap text-primary me-1.5"></i>Học phần:
                        </span>
                        @if(!empty($isGroupLocked))
                            <select class="filter-item-select text-muted" disabled>
                                <option selected>
                                    {{ $nhom->hocPhan->TenHocPhan ?? ($hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? $selectedHocPhan) }} (Khóa theo nhóm)
                                </option>
                            </select>
                        @else
                            <select class="filter-item-select" onchange="window.location.href=this.value;">
                                <option value="{{ route('sinhvien.dangky.index', ['HocKy' => $selectedHocKy]) }}">-- Tất cả học phần --</option>
                                @foreach($hocPhans as $hp)
                                    @php
                                        $hpCode = is_object($hp) ? $hp->MaHocPhan : $hp;
                                        $hpName = is_object($hp) ? $hp->TenHocPhan : $hp;
                                        $isSelected = ($selectedHocPhan === $hpCode || $selectedHocPhan === $hpName);
                                    @endphp
                                    <option value="{{ route('sinhvien.dangky.index', ['HocKy' => $selectedHocKy, 'HocPhan' => $hpCode]) }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $hpName }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH ĐỀ TÀI ĐÃ CÔNG BỐ (MẬT ĐỘ CAO, CUỘN MƯỢT, VỪA VẶN MÀN HÌNH) -->
    <div class="card border shadow-xs rounded-3 overflow-hidden">
        <div class="card-header bg-white py-2.5 px-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-primary fs-6"></i>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;">Danh Sách Đề Tài Đã Công Bố</h6>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-0.5 rounded-pill">{{ $detais->total() }} đề tài</span>
            </div>
            @if($selectedHocPhan)
                @php
                    $selHpName = $nhom?->hocPhan?->TenHocPhan ?? ($hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? $selectedHocPhan);
                @endphp
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                    <i class="fa-solid fa-tag me-1"></i>{{ $selHpName }}
                </span>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive topic-scroll-container sv-smooth-scroll">
                <table class="table table-topic align-middle mb-0">
                    <thead class="sticky-top bg-light">
                        <tr>
                            <th width="12%">Mã Đề Tài</th>
                            <th width="42%">Tên Đề Tài &amp; Thông Tin</th>
                            <th width="20%">GVHD Đề Xuất</th>
                            <th width="10%" class="text-center">Chỉ Tiêu</th>
                            <th width="16%" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detais as $dt)
                        @php
                            $myGroupRegThis = (isset($dangKyCurrent) && $dangKyCurrent && $dangKyCurrent->MaDeTai === $dt->MaDeTai) ? $dangKyCurrent : null;
                            $isTakenByOther = isset($deTaiDaDangKys[$dt->MaDeTai]) && (!isset($nhom) || $deTaiDaDangKys[$dt->MaDeTai]->MaNhom !== $nhom->MaNhom);
                            $hasActiveRegistration = isset($dangKyCurrent) && $dangKyCurrent && in_array($dangKyCurrent->TrangThai, ['Chờ duyệt', 'Đã duyệt']);
                        @endphp
                        <tr class="topic-row">
                            <td>
                                <span class="badge bg-light text-dark fw-bold border"><code>{{ $dt->MaDeTai }}</code></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="line-height: 1.4;">{{ $dt->TenDeTai }}</div>
                                @if($dt->MoTa)
                                    <div class="text-muted mt-0.5 text-truncate" style="font-size: 12.5px; max-width: 520px;" title="{{ strip_tags($dt->MoTa) }}">
                                        {{ \App\Helpers\HtmlHelper::plainText($dt->MoTa, 90) }}
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap gap-1.5 align-items-center mt-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11.5px !important;">
                                        {{ $dt->hocPhanRef->TenHocPhan ?? $dt->HocPhan ?? 'Khóa luận' }}
                                    </span>
                                    @if($dt->LinhVuc)
                                        <span class="badge bg-light text-secondary border" style="font-size: 11.5px !important;">{{ $dt->LinhVuc }}</span>
                                    @endif
                                    @if($dt->FileDeCuong)
                                        @php
                                            $dtDcUrl = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                            $dtDcName = basename($dt->FileDeCuong);
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-outline-outline-doc rounded-pill px-2.5 py-0.5 ms-1"
                                                onclick="quickPreviewOutline('{{ $dtDcUrl }}', '{{ $dtDcName }}', '{{ addslashes($dt->TenDeTai) }}')">
                                            <i class="fa-solid fa-file-lines me-1"></i>Xem đề cương
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                                <div class="text-muted" style="font-size: 12.5px;">{{ $dt->giangVien->HocVi ?? 'Giảng viên' }}</div>
                            </td>
                            <td class="text-center fw-bold text-secondary">
                                <span class="badge bg-light text-dark border px-2.5 py-1">{{ $dt->SoLuongSinhVienToiDa }} SV</span>
                            </td>
                            <td class="text-center">
                                @if($myGroupRegThis)
                                    @if($myGroupRegThis->TrangThai === 'Từ chối')
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5" title="{{ $myGroupRegThis->LyDoTuChoi }}">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>Đã Từ Chối
                                        </span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3 py-1.5">
                                            <i class="fa-solid fa-check-circle me-1"></i>Đã Gán Cho Nhóm
                                        </span>
                                    @endif
                                @elseif($isTakenByOther)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3 py-1 text-muted" disabled title="Đề tài này đã có nhóm khác đăng ký">
                                        <i class="fa-solid fa-lock me-1"></i>Đã Có Nhóm
                                    </button>
                                @elseif(isset($nhom) && $nhom)
                                    @if(isset($regState) && !$regState['can_register'])
                                        <button class="btn btn-sm btn-secondary rounded-pill px-3 py-1 text-muted" disabled title="{{ $regState['message'] }}">
                                            <i class="fa-solid fa-lock me-1"></i>{{ $regState['label'] }}
                                        </button>
                                    @elseif($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                        <span class="text-muted fst-italic" style="font-size: 12.5px;">Chỉ Trưởng nhóm có quyền</span>
                                    @elseif($hasActiveRegistration)
                                        <button class="btn btn-sm btn-secondary rounded-pill px-3 py-1 text-muted" disabled title="Nhóm của bạn đã có đề tài">
                                            <i class="fa-solid fa-lock me-1"></i>Đã Có Đề Tài
                                        </button>
                                    @elseif($soThanhVien < 3)
                                        <button class="btn btn-sm btn-secondary rounded-pill px-3 py-1 text-muted" disabled title="Cần đủ 3 thành viên mới được phép đăng ký">
                                            <i class="fa-solid fa-lock me-1"></i>Cần 3 TV ({{ $soThanhVien }}/3)
                                        </button>
                                    @else
                                        <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                            <button type="submit" class="btn btn-sm {{ isset($regState) && $regState['is_supplementary'] ? 'btn-warning text-dark' : 'btn-theme-register' }} rounded-pill px-3 py-1 fw-bold shadow-xs" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn đăng ký đề tài {{ $dt->TenDeTai }} cho nhóm của mình?');">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>{{ isset($regState) && $regState['is_supplementary'] ? 'ĐK Bổ Sung' : 'Đăng Ký' }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1" style="font-size: 12.5px !important;">
                                        Cần tạo nhóm
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clipboard-question fs-2 text-muted opacity-50 mb-2 d-block"></i>
                                Hiện tại chưa có đề tài nào phù hợp với bộ lọc được công bố.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($detais->hasPages())
        <div class="card-footer bg-white border-top py-2 px-3">
            {{ $detais->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@include('partials.modal_preview_decuong')
@endsection