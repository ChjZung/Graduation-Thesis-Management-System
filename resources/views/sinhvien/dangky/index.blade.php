@extends('layouts.sinhvien')

@section('page_title', 'Đăng Ký Đề Tài Khóa Luận')

@section('content')
<style>
    /* Thiết lập padding tổng thể và không gian thoáng đãng cho trang */
    .dangky-page-root {
        font-size: 0.875rem;
        color: #334155;
        padding: 8px 4px 32px 4px;
    }
    .dangky-page-root .small, 
    .dangky-page-root small, 
    .dangky-page-root code {
        font-size: 0.8rem !important;
    }

    /* Khung thẻ bao quát với góc bo tròn mềm mại và hiệu ứng bóng đổ tinh tế */
    .dangky-panel-card {
        border-radius: 14px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 63, 95, 0.04);
    }
    .topic-row {
        transition: background-color 0.15s ease;
    }
    .topic-row:hover {
        background-color: #f8fafc;
    }

    /* Bảng danh sách đề tài chuẩn hóa với khoảng đệm (padding) rộng rãi */
    .table-topic th {
        background-color: #003F5F !important;
        font-weight: 700;
        font-size: 0.775rem !important;
        padding: 14px 18px !important;
        color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }
    .table-topic td {
        font-size: 0.85rem !important;
        padding: 16px 18px !important;
        vertical-align: middle;
        line-height: 1.6;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Bộ lọc toàn cục 3 cột đồng đều, cân đối không bị lệch */
    .filter-item-box {
        display: flex;
        align-items: center;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        height: 48px;
        padding: 0 16px;
        transition: all 0.18s ease;
        width: 100%;
    }
    .filter-item-box:hover, .filter-item-box:focus-within {
        border-color: #00838C;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 131, 140, 0.08);
    }
    .filter-item-label {
        font-size: 0.825rem;
        font-weight: 600;
        color: #475569;
        white-space: nowrap;
        margin-right: 12px;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }
    .filter-item-select {
        border: none !important;
        background-color: transparent !important;
        font-size: 0.875rem;
        font-weight: 600;
        color: #003F5F;
        padding: 0 4px;
        box-shadow: none !important;
        cursor: pointer;
        width: 100%;
    }
    .filter-item-select:focus {
        outline: none;
    }
    .filter-item-static {
        font-size: 0.875rem;
        font-weight: 600;
        color: #003F5F;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        line-height: 46px;
    }

    /* Các nút bấm chủ đạo theo tông màu hệ thống */
    .btn-cyan {
        background-color: #00838C;
        border-color: #00838C;
        color: #ffffff;
    }
    .btn-cyan:hover, .btn-cyan:focus {
        background-color: #006b73;
        border-color: #006b73;
        color: #ffffff;
    }
    .btn-theme-register {
        background-color: #003F5F !important;
        color: #ffffff !important;
        border: 1px solid #003F5F !important;
        transition: all 0.15s ease;
    }
    .btn-theme-register:hover {
        background-color: #002c44 !important;
        border-color: #002c44 !important;
        box-shadow: 0 4px 12px rgba(0, 63, 95, 0.25);
    }
    .badge-neutral {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .topic-scroll-container {
        max-height: calc(100vh - 350px);
        min-height: 380px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #00838C #f1f5f9;
    }
    .topic-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    .topic-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .topic-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
</style>

<div class="dangky-page-root">
    <!-- TIÊU ĐỀ TRANG & DẢI TRẠNG THÁI -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 px-1">
        <div class="d-flex align-items-center gap-3">
            <h5 class="fw-bold mb-0" style="color: #003F5F; font-size: 1.2rem;">
                <i class="fa-solid fa-book-bookmark me-2" style="color: #00838C;"></i>Đăng Ký Đề Tài Khóa Luận
            </h5>
            <span class="badge badge-neutral px-3.5 py-2" style="font-size: 0.8rem;">
                {{ $hocKies->firstWhere('MaHocKy', $selectedHocKy)->TenHocKy ?? 'Học kỳ hiện tại' }}
            </span>
        </div>
        @if(isset($regState))
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $regState['badge'] }} rounded-pill px-4 py-2 fw-semibold shadow-xs" style="font-size: 0.825rem;">
                <i class="fa-solid fa-clock me-1.5"></i>{{ $regState['label'] }}
            </span>
        </div>
        @endif
    </div>

    <!-- DẢI THÔNG BÁO TỔNG HỢP: TIẾN ĐỘ THỜI GIAN & ĐIỀU KIỆN NHÓM -->
    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 p-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3" style="font-size: 0.875rem;">
            <!-- Trạng thái giai đoạn đăng ký -->
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <div class="rounded-circle bg-info bg-opacity-10 p-2.5 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                    <i class="fa-solid fa-circle-info fs-5" style="color: #00838C;"></i>
                </div>
                <span class="text-dark">
                    {{ $regState['message'] ?? 'Theo dõi lịch công bố và đăng ký đề tài chính thức của khoa.' }}
                </span>
            </div>

            <!-- Tình trạng nhóm của sinh viên -->
            @if(isset($nhom) && $nhom)
                @php $isGroupReady = ($soThanhVien >= 3); @endphp
                <div class="d-flex align-items-center gap-2.5">
                    @if($isGroupReady)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-4 py-2.5 rounded-pill fw-semibold" style="font-size: 0.825rem;">
                            <i class="fa-solid fa-circle-check me-1.5"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 TV &bull; Đủ điều kiện ĐK)
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-4 py-2.5 rounded-pill fw-semibold" style="font-size: 0.825rem;">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-1.5"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 TV &bull; Thiếu {{ 3 - $soThanhVien }} TV)
                        </span>
                        <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $nhom->MaHocPhan, 'hoc_ky' => $nhom->MaHocKy]) }}" 
                           class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-user-plus me-1"></i>Mời thêm
                        </a>
                    @endif
                </div>
            @else
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-neutral px-4 py-2.5 rounded-pill" style="font-size: 0.825rem;">
                        <i class="fa-solid fa-users-slash me-1.5 text-secondary"></i>Chưa tham gia nhóm
                    </span>
                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-cyan btn-sm rounded-pill px-4 py-2 fw-semibold" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user-group me-1"></i>Vào Quản Trị Nhóm
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- ĐƠN ĐĂNG KÝ HIỆN TẠI CỦA NHÓM -->
    @if(isset($dangKyCurrent) && $dangKyCurrent)
    <div class="card mb-4 border border-slate-200 border-start border-4 {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? 'border-success' : ($dangKyCurrent->TrangThai === 'Từ chối' ? 'border-danger' : 'border-warning') }} shadow-xs rounded-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-xs" 
                         style="width: 48px; height: 48px; background-color: {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? '#25A18E' : ($dangKyCurrent->TrangThai === 'Từ chối' ? '#dc2626' : '#f59e0b') }}; flex-shrink: 0;">
                        <i class="fa-solid {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? 'fa-check' : ($dangKyCurrent->TrangThai === 'Từ chối' ? 'fa-xmark' : 'fa-hourglass-half') }} fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                            <span class="fw-bold text-dark" style="color: #003F5F !important; font-size: 1.05rem;">{{ $dangKyCurrent->deTai->TenDeTai ?? 'Chưa rõ tên đề tài' }}</span>
                            <span class="badge badge-neutral px-2.5 py-1"><code>{{ $dangKyCurrent->deTai->MaDeTai ?? '' }}</code></span>
                        </div>
                        <div class="text-secondary d-flex align-items-center gap-3 flex-wrap" style="font-size: 0.85rem;">
                            <span><i class="fa-solid fa-chalkboard-user me-1" style="color: #00838C;"></i>GVHD: <strong>{{ $dangKyCurrent->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong></span>
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

                <div class="d-flex align-items-center gap-2.5">
                    @php
                        $hasDeCuong = !empty($dangKyCurrent->deTai?->FileDeCuong);
                    @endphp
                    @if($hasDeCuong)
                        @php
                            $fileDcUrl = Str::startsWith($dangKyCurrent->deTai->FileDeCuong, ['http', 'storage/']) ? asset($dangKyCurrent->deTai->FileDeCuong) : asset('storage/' . $dangKyCurrent->deTai->FileDeCuong);
                            $fileName = basename($dangKyCurrent->deTai->FileDeCuong);
                        @endphp
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 shadow-xs bg-white fw-semibold"
                                onclick="quickPreviewOutline('{{ $fileDcUrl }}', '{{ $fileName }}', '{{ addslashes($dangKyCurrent->deTai->TenDeTai ?? '') }}')" style="font-size: 0.825rem;">
                            <i class="fa-solid fa-eye me-1.5" style="color: #00838C;"></i>Xem Đề Cương
                        </button>
                    @endif

                    @if(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV && $dangKyCurrent->TrangThai !== 'Đã duyệt')
                        <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký đề tài này để chọn đề tài khác?');" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-4 py-2 fw-semibold" style="font-size: 0.825rem;">
                                <i class="fa-solid fa-rotate-left me-1.5"></i>Hủy Đăng Ký
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- BỘ LỌC ĐỀ TÀI TOÀN CỤC: THIẾT KẾ CÂN ĐỐI 3 CỘT ĐỀU NHAU, THÔNG THOÁNG -->
    <div class="card mb-4 bg-white border border-slate-200 shadow-xs rounded-4 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3.5 px-1">
            <span class="fw-bold text-dark d-flex align-items-center" style="color: #003F5F !important; font-size: 0.95rem;">
                <i class="fa-solid fa-filter me-2" style="color: #00838C;"></i>Bộ Lọc Đề Tài Khóa Luận:
            </span>
            @if(!empty($isGroupLocked) && isset($nhom))
                <span class="badge badge-neutral px-3.5 py-2 fw-semibold" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-lock me-1" style="color: #00838C;"></i>Đã khóa bộ lọc theo nhóm: <strong>{{ $nhom->TenNhom }}</strong>
                </span>
            @endif
        </div>

        <!-- 3 CỘT CÂN ĐỐI HOÀN TOÀN -->
        <div class="row g-3.5 align-items-center">
            <!-- CỘT 1: CHỌN HỌC KỲ -->
            <div class="col-12 col-md-4">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-regular fa-calendar-days text-secondary me-1"></i>Học kỳ:
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
                        <i class="fa-solid fa-sitemap text-secondary me-1"></i>Bộ môn:
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
                        <i class="fa-solid fa-graduation-cap text-secondary me-1"></i>Học phần:
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

    <!-- DANH SÁCH ĐỀ TÀI ĐÃ CÔNG BỐ -->
    <div class="card border border-slate-200 shadow-xs dangky-panel-card overflow-hidden mb-4">
        <div class="card-header bg-white py-4 px-4.5 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2.5">
                <i class="fa-solid fa-clipboard-list" style="color: #00838C;"></i>
                <h6 class="fw-bold mb-0" style="color: #003F5F; font-size: 1rem;">Danh Sách Đề Tài Đã Công Bố</h6>
                <span class="badge badge-neutral px-3 py-1.5" style="font-size: 0.775rem;">{{ $detais->total() }} đề tài</span>
            </div>
            @if($selectedHocPhan)
                @php
                    $selHpName = $nhom?->hocPhan?->TenHocPhan ?? ($hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? $selectedHocPhan);
                @endphp
                <span class="badge badge-neutral px-3.5 py-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-tag me-1.5" style="color: #00838C;"></i>{{ $selHpName }}
                </span>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive topic-scroll-container">
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
                                <span class="badge badge-neutral px-3 py-1.5 font-monospace"><code>{{ $dt->MaDeTai }}</code></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1.5" style="line-height: 1.5; font-size: 0.95rem;">{{ $dt->TenDeTai }}</div>
                                @if($dt->MoTa)
                                    <div class="text-muted mt-1 text-truncate" style="font-size: 0.825rem; max-width: 540px;" title="{{ strip_tags($dt->MoTa) }}">
                                        {{ \App\Helpers\HtmlHelper::plainText($dt->MoTa, 90) }}
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2.5">
                                    <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem !important;">
                                        {{ $dt->hocPhanRef->TenHocPhan ?? $dt->HocPhan ?? 'Khóa luận' }}
                                    </span>
                                    @if($dt->LinhVuc)
                                        <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem !important;">{{ $dt->LinhVuc }}</span>
                                    @endif
                                    @if($dt->FileDeCuong)
                                        @php
                                            $dtDcUrl = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                            $dtDcName = basename($dt->FileDeCuong);
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 ms-1 bg-white fw-semibold shadow-xs"
                                                onclick="quickPreviewOutline('{{ $dtDcUrl }}', '{{ $dtDcName }}', '{{ addslashes($dt->TenDeTai) }}')" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-file-lines me-1" style="color: #00838C;"></i>Xem đề cương
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: #003F5F; font-size: 0.9rem;">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                                <div class="text-muted mt-1" style="font-size: 0.825rem;">{{ $dt->giangVien->HocVi ?? 'Giảng viên' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-neutral px-3.5 py-2" style="font-size: 0.825rem;">{{ $dt->SoLuongSinhVienToiDa }} SV</span>
                            </td>
                            <td class="text-center">
                                @if($myGroupRegThis)
                                    @if($myGroupRegThis->TrangThai === 'Từ chối')
                                        <span class="badge bg-danger rounded-pill px-3.5 py-2" title="{{ $myGroupRegThis->LyDoTuChoi }}" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>Đã Từ Chối
                                        </span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3.5 py-2" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-check-circle me-1"></i>Đã Gán Cho Nhóm
                                        </span>
                                    @endif
                                @elseif($isTakenByOther)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs" disabled title="Đề tài này đã có nhóm khác đăng ký" style="font-size: 0.825rem;">
                                        <i class="fa-solid fa-lock me-1"></i>Đã Có Nhóm
                                    </button>
                                @elseif(isset($nhom) && $nhom)
                                    @if(isset($regState) && !$regState['can_register'])
                                        <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs" disabled title="{{ $regState['message'] }}" style="font-size: 0.825rem;">
                                            <i class="fa-solid fa-lock me-1"></i>{{ $regState['label'] }}
                                        </button>
                                    @elseif($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                        <span class="text-muted fst-italic" style="font-size: 0.825rem;">Chỉ Trưởng nhóm có quyền</span>
                                    @elseif($hasActiveRegistration)
                                        <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs" disabled title="Nhóm của bạn đã có đề tài" style="font-size: 0.825rem;">
                                            <i class="fa-solid fa-lock me-1"></i>Đã Có Đề Tài
                                        </button>
                                    @elseif($soThanhVien < 3)
                                        <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs" disabled title="Cần đủ 3 thành viên mới được phép đăng ký" style="font-size: 0.825rem;">
                                            <i class="fa-solid fa-lock me-1"></i>Cần 3 TV ({{ $soThanhVien }}/3)
                                        </button>
                                    @else
                                        <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                            <button type="submit" class="btn btn-sm {{ isset($regState) && $regState['is_supplementary'] ? 'btn-warning text-dark' : 'btn-theme-register' }} rounded-pill px-4 py-2 fw-bold shadow-xs" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn đăng ký đề tài {{ $dt->TenDeTai }} cho nhóm của mình?');" style="font-size: 0.825rem;">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>{{ isset($regState) && $regState['is_supplementary'] ? 'ĐK Bổ Sung' : 'Đăng Ký' }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-sm btn-outline-secondary text-dark bg-white rounded-pill px-4 py-2 fw-semibold shadow-xs" style="font-size: 0.825rem;">
                                        Cần tạo nhóm
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted" style="font-size: 0.9rem;">
                                <i class="fa-solid fa-clipboard-question fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                Hiện tại chưa có đề tài nào phù hợp với bộ lọc được công bố.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($detais->hasPages())
        <div class="card-footer bg-white border-top py-3.5 px-4.5">
            {{ $detais->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@include('partials.modal_preview_decuong')
@endsection