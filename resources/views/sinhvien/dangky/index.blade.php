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
        box-shadow: 0 4px 16px rgba(0, 51, 102, 0.04);
    }
    .topic-row {
        transition: background-color 0.15s ease;
    }
    .topic-row:hover {
        background-color: #f8fafc;
    }

    /* Bảng danh sách đề tài chuẩn hóa với khoảng đệm (padding) rộng rãi */
    .table-topic th {
        background-color: #003366 !important;
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
        border-color: #0072CE;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 114, 206, 0.08);
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
        color: #003366;
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
        color: #003366;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        line-height: 46px;
    }

    /* Các nút bấm chủ đạo theo tông màu hệ thống */
    .btn-cyan {
        background-color: #0072CE;
        border-color: #0072CE;
        color: #ffffff;
    }
    .btn-cyan:hover, .btn-cyan:focus {
        background-color: #006b73;
        border-color: #006b73;
        color: #ffffff;
    }
    .btn-theme-register {
        background-color: #003366 !important;
        color: #ffffff !important;
        border: 1px solid #003366 !important;
        transition: all 0.15s ease;
    }
    .btn-theme-register:hover {
        background-color: #002c44 !important;
        border-color: #002c44 !important;
        box-shadow: 0 4px 12px rgba(0, 51, 102, 0.25);
    }
    .badge-neutral {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .current-topic-card {
        border-radius: 14px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 51, 102, 0.05);
        overflow: hidden;
    }
    .topic-desc-content {
        line-height: 1.7;
        color: #334155;
        font-size: 0.85rem;
        word-break: break-word;
    }
    .topic-desc-content ul, .topic-desc-content ol {
        padding-left: 20px;
        margin-bottom: 8px;
    }
    .topic-desc-content li {
        margin-bottom: 4px;
    }
    .topic-desc-content p {
        margin-bottom: 8px;
    }
    .topic-scroll-container {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #0072CE #f1f5f9;
    }
    .topic-scroll-container::-webkit-scrollbar {
        width: 6px;
        height: 6px;
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
            <h5 class="fw-bold mb-0" style="color: #003366; font-size: 1.2rem;">
                <i class="fa-solid fa-book-bookmark me-2" style="color: #0072CE;"></i>Đăng Ký Đề Tài Khóa Luận
            </h5>
            <span class="badge badge-neutral px-3 py-2" style="font-size: 0.8rem;">
                {{ $hocKies->firstWhere('MaHocKy', $selectedHocKy)->TenHocKy ?? 'Học kỳ hiện tại' }}
            </span>
        </div>
        @if(isset($regState))
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $regState['badge'] }} rounded-pill px-4 py-2 fw-semibold shadow-xs" style="font-size: 0.825rem;">
                <i class="fa-solid fa-clock me-2"></i>{{ $regState['label'] }}
            </span>
        </div>
        @endif
    </div>

    <!-- DẢI THÔNG BÁO TỔNG HỢP: TIẾN ĐỘ THỜI GIAN & ĐIỀU KIỆN NHÓM -->
    <div class="card border border-slate-200 shadow-xs rounded-4 mb-4 p-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3" style="font-size: 0.875rem;">
            <!-- Trạng thái giai đoạn đăng ký -->
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                    <i class="fa-solid fa-circle-info fs-5" style="color: #0072CE;"></i>
                </div>
                <span class="text-dark">
                    {{ $regState['message'] ?? 'Theo dõi lịch công bố và đăng ký đề tài chính thức của khoa.' }}
                </span>
            </div>

            <!-- Tình trạng nhóm của sinh viên -->
            @if(isset($nhom) && $nhom)
                @php $isGroupReady = ($soThanhVien >= 3); @endphp
                <div class="d-flex align-items-center gap-2">
                    @if($isGroupReady)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-4 py-2 rounded-pill fw-semibold" style="font-size: 0.825rem;">
                            <i class="fa-solid fa-circle-check me-2"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 TV &bull; Đủ điều kiện ĐK)
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-4 py-2 rounded-pill fw-semibold" style="font-size: 0.825rem;">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 TV &bull; Thiếu {{ 3 - $soThanhVien }} TV)
                        </span>
                        <a href="{{ route('sinhvien.nhom.index', ['hoc_phan' => $nhom->MaHocPhan, 'hoc_ky' => $nhom->MaHocKy]) }}" 
                           class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-bold text-dark shadow-xs" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-user-plus me-2"></i>Mời thêm
                        </a>
                    @endif
                </div>
            @else
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-neutral px-4 py-2 rounded-pill" style="font-size: 0.825rem;">
                        <i class="fa-solid fa-users-slash me-2 text-secondary"></i>Chưa tham gia nhóm
                    </span>
                    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-cyan btn-sm rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user-group me-2"></i>Vào Quản Trị Nhóm
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- ĐỀ TÀI KHÓA LUẬN CỦA NHÓM (ĐÃ THÊM PADDING VÀ KHOẢNG ĐỆM THÔNG THOÁNG) -->
    @if(isset($dangKyCurrent) && $dangKyCurrent && $dangKyCurrent->deTai)
    @php
        $dtCurrent = $dangKyCurrent->deTai;
        $isApproved = ($dangKyCurrent->TrangThai === 'Đã duyệt');
        $isRejected = ($dangKyCurrent->TrangThai === 'Từ chối');
        $isPending = (!$isApproved && !$isRejected);

        $borderStatusClass = $isApproved ? 'border-primary' : ($isRejected ? 'border-danger' : 'border-warning');
        $statusBg = $isApproved ? 'rgba(0, 114, 206, 0.05)' : ($isRejected ? 'rgba(220, 38, 38, 0.05)' : 'rgba(245, 158, 11, 0.05)');
    @endphp
    <div class="card mb-4 current-topic-card shadow-xs border-start border-4 {{ $borderStatusClass }}">
        <!-- Header: Tiêu đề khối & Trạng thái & Nút thao tác -->
        <div class="card-header py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom" style="background-color: {{ $statusBg }};">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-xs" 
                     style="width: 42px; height: 42px; background-color: {{ $isApproved ? '#0072CE' : ($isRejected ? '#dc2626' : '#f59e0b') }}; flex-shrink: 0;">
                    <i class="fa-solid {{ $isApproved ? 'fa-award' : ($isRejected ? 'fa-circle-xmark' : 'fa-hourglass-half') }} fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold text-uppercase" style="color: #003366; font-size: 0.9rem; letter-spacing: 0.03em;">
                            Đề Tài Khóa Luận Của Nhóm Bạn
                        </span>
                        @if(isset($nhom) && $nhom)
                            <span class="badge badge-neutral px-3 py-1 fw-semibold text-primary" style="font-size: 0.775rem;">
                                <i class="fa-solid fa-users me-2"></i>{{ $nhom->TenNhom }}
                            </span>
                        @endif
                    </div>
                    <div class="text-secondary small mt-1">
                        Ngày đăng ký: {{ $dangKyCurrent->created_at ? $dangKyCurrent->created_at->format('d/m/Y H:i') : 'Đang cập nhật' }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($isApproved)
                    <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-semibold shadow-xs" style="font-size: 0.825rem;">
                        <i class="fa-solid fa-circle-check me-2"></i>Đã Gán Chính Thức Cho Nhóm
                    </span>
                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-sm btn-cyan rounded-pill px-3 py-2 fw-semibold shadow-xs d-inline-flex align-items-center" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-chart-line me-2"></i>Báo Cáo Tiến Độ
                    </a>
                @elseif($isRejected)
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold shadow-xs" style="font-size: 0.825rem;">
                        <i class="fa-solid fa-circle-xmark me-2"></i>Đã Từ Chối
                    </span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold shadow-xs" style="font-size: 0.825rem;">
                        <i class="fa-solid fa-clock me-2"></i>Đang Chờ Phê Duyệt
                    </span>
                @endif

                @if(!empty($dtCurrent->FileDeCuong))
                    @php
                        $fileDcUrl = Str::startsWith($dtCurrent->FileDeCuong, ['http', 'storage/']) ? asset($dtCurrent->FileDeCuong) : asset('storage/' . $dtCurrent->FileDeCuong);
                        $fileName = basename($dtCurrent->FileDeCuong);
                    @endphp
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 shadow-xs bg-white fw-semibold d-inline-flex align-items-center"
                            onclick="openTopicOutlineSplitModal(this)"
                            data-madetai="{{ $dtCurrent->MaDeTai }}"
                            data-tendetai="{{ $dtCurrent->TenDeTai }}"
                            data-gvhd="{{ $dtCurrent->giangVien->HoTen ?? 'Chưa cập nhật' }}"
                            data-hocvi="{{ $dtCurrent->giangVien->HocVi ?? 'Giảng viên' }}"
                            data-bomon="{{ $dtCurrent->giangVien->boMon->TenBoMon ?? ($dtCurrent->hocPhanRef->boMon->TenBoMon ?? 'Bộ môn CNTT') }}"
                            data-email="{{ $dtCurrent->giangVien->Email ?? '' }}"
                            data-sdt="{{ $dtCurrent->giangVien->SoDienThoai ?? '' }}"
                            data-hocphan="{{ $dtCurrent->hocPhanRef->TenHocPhan ?? $dtCurrent->HocPhan ?? 'Khóa luận' }}"
                            data-hocky="{{ $dtCurrent->hocKy->TenHocKy ?? '' }}"
                            data-linhvuc="{{ $dtCurrent->LinhVuc ?? '' }}"
                            data-chitieu="{{ $dtCurrent->SoLuongSinhVienToiDa ?? 3 }}"
                            data-mota="{{ base64_encode(\App\Services\HtmlSanitizerService::cleanDescription($dtCurrent->MoTa)) }}"
                            data-yeucau="{{ base64_encode(\App\Services\HtmlSanitizerService::cleanDescription($dtCurrent->YeuCau)) }}"
                            data-fileurl="{{ $fileDcUrl }}"
                            data-filename="{{ $fileName }}"
                            style="font-size: 0.8rem;">
                        <i class="fa-solid fa-file-pdf me-2 text-danger"></i>Xem Đề Cương PDF
                    </button>
                @endif

                @if(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV && !$isApproved)
                    <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký đề tài này để chọn đề tài khác?');" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-rotate-left me-2"></i>Hủy Đăng Ký
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Body: Chi tiết đầy đủ thông tin đề tài với khoảng đệm padding rộng rãi -->
        <div class="card-body p-4" style="padding: 2rem !important;">
            <!-- Tên đề tài lớn & Meta Tags -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3" style="color: #003366; font-size: 1.25rem; line-height: 1.55;">
                    {{ $dtCurrent->TenDeTai }}
                </h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge badge-neutral px-3 py-2">
                        Mã: <code class="fw-bold">{{ $dtCurrent->MaDeTai }}</code>
                    </span>
                    <span class="badge badge-neutral px-3 py-2">
                        <i class="fa-solid fa-book-open me-2" style="color: #0072CE;"></i>{{ $dtCurrent->hocPhanRef->TenHocPhan ?? $dtCurrent->HocPhan ?? 'Khóa luận' }}
                    </span>
                    <span class="badge badge-neutral px-3 py-2">
                        <i class="fa-solid fa-building-columns me-2" style="color: #0072CE;"></i>{{ $dtCurrent->giangVien->boMon->TenBoMon ?? ($dtCurrent->hocPhanRef->boMon->TenBoMon ?? 'Bộ môn CNTT') }}
                    </span>
                    @if($dtCurrent->hocKy)
                        <span class="badge badge-neutral px-3 py-2">
                            <i class="fa-regular fa-calendar me-2" style="color: #0072CE;"></i>{{ $dtCurrent->hocKy->TenHocKy }}
                        </span>
                    @endif
                    @if($dtCurrent->LinhVuc)
                        <span class="badge badge-neutral px-3 py-2">
                            <i class="fa-solid fa-microchip me-2" style="color: #0072CE;"></i>{{ $dtCurrent->LinhVuc }}
                        </span>
                    @endif
                    <span class="badge badge-neutral px-3 py-2">
                        <i class="fa-solid fa-users me-2" style="color: #0072CE;"></i>Chỉ tiêu: {{ $dtCurrent->SoLuongSinhVienToiDa ?? 3 }} SV
                    </span>
                </div>
            </div>

            @if($isRejected)
                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 py-3 px-3 mb-4" style="font-size: 0.875rem;">
                    <i class="fa-solid fa-triangle-exclamation fs-5 flex-shrink-0"></i>
                    <div>
                        <strong>Lý do từ chối:</strong> {{ $dangKyCurrent->LyDoTuChoi ?? 'Đề tài không phù hợp hoặc đã đủ chỉ tiêu. Vui lòng chọn đăng ký đề tài khác trong danh sách bên dưới.' }}
                    </div>
                </div>
            @endif

            <!-- 2 Cột thông tin: Bên trái là Mô tả & Yêu cầu, Bên phải là Giảng viên & Nhóm thực hiện -->
            <div class="row g-4 mt-1">
                <!-- Cột trái: Mô tả chi tiết & Yêu cầu -->
                <div class="col-12 col-lg-8">
                    <!-- Mô tả chi tiết đề tài -->
                    <div class="p-4 bg-slate-50 border rounded-3 mb-3 shadow-2xs" style="background-color: #f8fafc; border-color: #e2e8f0;">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-3" style="font-size: 0.9rem; color: #003366 !important;">
                            <i class="fa-solid fa-align-left me-2" style="color: #0072CE;"></i>Mô Tả Chi Tiết &amp; Mục Tiêu Đề Tài:
                        </div>
                        @if($dtCurrent->MoTa)
                            <div class="topic-desc-content">
                                {!! \App\Services\HtmlSanitizerService::cleanDescription($dtCurrent->MoTa) !!}
                            </div>
                        @else
                            <div class="text-muted fst-italic" style="font-size: 0.85rem;">(Chưa có nội dung mô tả chi tiết cho đề tài này)</div>
                        @endif
                    </div>

                    <!-- Yêu cầu cần đạt / Công nghệ -->
                    @if($dtCurrent->YeuCau)
                    <div class="p-4 bg-slate-50 border rounded-3 shadow-2xs" style="background-color: #f8fafc; border-color: #e2e8f0;">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-3" style="font-size: 0.9rem; color: #003366 !important;">
                            <i class="fa-solid fa-list-check me-2" style="color: #0072CE;"></i>Yêu Cầu Cần Đạt &amp; Kiến Thức Cần Chuẩn Bị:
                        </div>
                        <div class="topic-desc-content">
                            {!! \App\Services\HtmlSanitizerService::cleanDescription($dtCurrent->YeuCau) !!}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Cột phải: Thông tin GVHD & Thành viên nhóm -->
                <div class="col-12 col-lg-4 d-flex flex-column gap-3">
                    <!-- Khối GVHD -->
                    <div class="p-4 bg-white border rounded-3 shadow-xs" style="border-color: #e2e8f0;">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="font-size: 0.875rem; color: #003366 !important;">
                            <i class="fa-solid fa-chalkboard-user me-2" style="color: #0072CE;"></i>Giảng Viên Hướng Dẫn (GVHD)
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.925rem;">
                                    {{ $dtCurrent->giangVien->HoTen ?? 'Chưa cập nhật' }}
                                </div>
                                <div class="text-secondary small">
                                    {{ $dtCurrent->giangVien->HocVi ?? 'Giảng viên' }} &bull; {{ $dtCurrent->giangVien->boMon->TenBoMon ?? 'Bộ môn CNTT' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3 pt-2 border-top">
                            <div class="mb-2"><i class="fa-regular fa-envelope me-2 text-muted"></i>{{ $dtCurrent->giangVien->Email ?? 'Chưa cập nhật' }}</div>
                            @if(!empty($dtCurrent->giangVien->SoDienThoai))
                                <div><i class="fa-solid fa-phone me-2 text-muted"></i>{{ $dtCurrent->giangVien->SoDienThoai }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Khối Thành viên nhóm -->
                    <div class="p-4 bg-white border rounded-3 shadow-xs" style="border-color: #e2e8f0;">
                        <div class="fw-bold text-dark d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="font-size: 0.875rem; color: #003366 !important;">
                            <span><i class="fa-solid fa-users-viewfinder me-2" style="color: #0072CE;"></i>Nhóm Thực Hiện</span>
                            <span class="badge badge-neutral px-3 py-1" style="font-size: 0.75rem;">
                                {{ isset($nhom) && $nhom ? $nhom->TenNhom : 'Nhóm SV' }}
                            </span>
                        </div>
                        @if(isset($nhom) && $nhom && $nhom->thanhViens && $nhom->thanhViens->count() > 0)
                            <div class="d-flex flex-column gap-2">
                                @foreach($nhom->thanhViens->where('TrangThai', 'da_tham_gia') as $tv)
                                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light" style="font-size: 0.825rem;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-regular fa-circle-user {{ str_contains(strtolower($tv->VaiTro ?? ''), 'trưởng') ? 'text-primary' : 'text-secondary' }} fs-5"></i>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $tv->sinhVien->HoTen ?? $tv->MaSV }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">MSSV: {{ $tv->MaSV }}</div>
                                            </div>
                                        </div>
                                        @if(str_contains(strtolower($tv->VaiTro ?? ''), 'trưởng'))
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1" style="font-size: 0.725rem;">Trưởng nhóm</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1" style="font-size: 0.725rem;">Thành viên</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted fst-italic small">Chưa có thông tin danh sách thành viên.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- BỘ LỌC ĐỀ TÀI TOÀN CỤC: THIẾT KẾ CÂN ĐỐI 3 CỘT ĐỀU NHAU, THÔNG THOÁNG -->
    <div class="card mb-4 bg-white border border-slate-200 shadow-xs rounded-4 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 px-1">
            <span class="fw-bold text-dark d-flex align-items-center" style="color: #003366 !important; font-size: 0.95rem;">
                <i class="fa-solid fa-filter me-2" style="color: #0072CE;"></i>Bộ Lọc Đề Tài Khóa Luận:
            </span>
            @if(!empty($isGroupLocked) && isset($nhom))
                <span class="badge badge-neutral px-3 py-2 fw-semibold" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-lock me-2" style="color: #0072CE;"></i>Đã khóa bộ lọc theo nhóm: <strong>{{ $nhom->TenNhom }}</strong>
                </span>
            @endif
        </div>

        <!-- 3 CỘT CÂN ĐỐI HOÀN TOÀN -->
        <div class="row g-3 align-items-center">
            <!-- CỘT 1: CHỌN HỌC KỲ -->
            <div class="col-12 col-md-4">
                <div class="filter-item-box">
                    <span class="filter-item-label">
                        <i class="fa-regular fa-calendar-days text-secondary me-2"></i>Học kỳ:
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
                        <i class="fa-solid fa-sitemap text-secondary me-2"></i>Bộ môn:
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
                        <i class="fa-solid fa-graduation-cap text-secondary me-2"></i>Học phần:
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
        <div class="card-header bg-white py-4 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-clipboard-list" style="color: #0072CE;"></i>
                <h6 class="fw-bold mb-0" style="color: #003366; font-size: 1rem;">Danh Sách Đề Tài Đã Công Bố</h6>
                <span class="badge badge-neutral px-3 py-2" style="font-size: 0.775rem;">{{ $detais->total() }} đề tài (5 đề tài/trang)</span>
            </div>
            @if($selectedHocPhan)
                @php
                    $selHpName = $nhom?->hocPhan?->TenHocPhan ?? ($hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)->TenHocPhan ?? $selectedHocPhan);
                @endphp
                <span class="badge badge-neutral px-3 py-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-tag me-2" style="color: #0072CE;"></i>{{ $selHpName }}
                </span>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive topic-scroll-container">
                <table class="table table-topic align-middle mb-0">
                    <thead class="bg-light">
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
                                <span class="badge badge-neutral px-3 py-2 font-monospace"><code>{{ $dt->MaDeTai }}</code></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-2" style="line-height: 1.5; font-size: 0.95rem;">{{ $dt->TenDeTai }}</div>
                                @if($dt->MoTa)
                                    <div class="text-muted mt-1 text-truncate" style="font-size: 0.825rem; max-width: 540px;" title="{{ strip_tags($dt->MoTa) }}">
                                        {{ \App\Helpers\HtmlHelper::plainText($dt->MoTa, 90) }}
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 ms-1 bg-white fw-semibold shadow-xs d-inline-flex align-items-center"
                                                onclick="openTopicOutlineSplitModal(this)"
                                                data-madetai="{{ $dt->MaDeTai }}"
                                                data-tendetai="{{ $dt->TenDeTai }}"
                                                data-gvhd="{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}"
                                                data-hocvi="{{ $dt->giangVien->HocVi ?? 'Giảng viên' }}"
                                                data-bomon="{{ $dt->giangVien->boMon->TenBoMon ?? ($dt->hocPhanRef->boMon->TenBoMon ?? 'Bộ môn CNTT') }}"
                                                data-email="{{ $dt->giangVien->Email ?? '' }}"
                                                data-sdt="{{ $dt->giangVien->SoDienThoai ?? '' }}"
                                                data-hocphan="{{ $dt->hocPhanRef->TenHocPhan ?? $dt->HocPhan ?? 'Khóa luận' }}"
                                                data-hocky="{{ $dt->hocKy->TenHocKy ?? '' }}"
                                                data-linhvuc="{{ $dt->LinhVuc ?? '' }}"
                                                data-chitieu="{{ $dt->SoLuongSinhVienToiDa ?? 3 }}"
                                                data-mota="{{ base64_encode(\App\Services\HtmlSanitizerService::cleanDescription($dt->MoTa)) }}"
                                                data-yeucau="{{ base64_encode(\App\Services\HtmlSanitizerService::cleanDescription($dt->YeuCau)) }}"
                                                data-fileurl="{{ $dtDcUrl }}"
                                                data-filename="{{ $dtDcName }}"
                                                style="font-size: 0.8rem;"
                                                title="Xem đề cương và chi tiết đề tài">
                                            <i class="fa-solid fa-file-pdf me-2 text-danger"></i>Xem đề cương
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: #003366; font-size: 0.9rem;">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                                <div class="text-muted mt-1" style="font-size: 0.825rem;">{{ $dt->giangVien->HocVi ?? 'Giảng viên' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-neutral px-3 py-2" style="font-size: 0.825rem;">{{ $dt->SoLuongSinhVienToiDa }} SV</span>
                            </td>
                            <td class="text-center">
                                @if($myGroupRegThis)
                                    @if($myGroupRegThis->TrangThai === 'Từ chối')
                                        <span class="badge bg-danger rounded-pill px-3 py-2" title="{{ $myGroupRegThis->LyDoTuChoi }}" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-circle-xmark me-2"></i>Đã Từ Chối
                                        </span>
                                    @else
                                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-check-circle me-2"></i>Đã Gán Cho Nhóm
                                        </span>
                                    @endif
                                @elseif($isTakenByOther)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs d-inline-flex align-items-center" disabled title="Đề tài này đã có nhóm khác đăng ký" style="font-size: 0.825rem;">
                                        <i class="fa-solid fa-lock me-2"></i>Đã Có Nhóm
                                    </button>
                                @elseif(isset($nhom) && $nhom)
                                    @if(isset($regState) && !$regState['can_register'])
                                        <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs d-inline-flex align-items-center" disabled title="{{ $regState['message'] }}" style="font-size: 0.825rem;">
                                            <i class="fa-solid fa-lock me-2"></i>{{ $regState['label'] }}
                                        </button>
                                    @elseif($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                        <span class="text-muted fst-italic" style="font-size: 0.825rem;">Chỉ Trưởng nhóm có quyền</span>
                                    @elseif($hasActiveRegistration)
                                        <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs d-inline-flex align-items-center" disabled title="Nhóm của bạn đã có đề tài" style="font-size: 0.825rem;">
                                            <i class="fa-solid fa-lock me-2"></i>Đã Có Đề Tài
                                        </button>
                                    @elseif($soThanhVien < 3)
                                        <button class="btn btn-sm btn-secondary rounded-pill px-4 py-2 text-white shadow-xs d-inline-flex align-items-center" disabled title="Cần đủ 3 thành viên mới được phép đăng ký" style="font-size: 0.825rem;">
                                            <i class="fa-solid fa-lock me-2"></i>Cần 3 TV ({{ $soThanhVien }}/3)
                                        </button>
                                    @else
                                        <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                            <button type="submit" class="btn btn-sm {{ isset($regState) && $regState['is_supplementary'] ? 'btn-warning text-dark' : 'btn-theme-register' }} rounded-pill px-4 py-2 fw-bold shadow-xs" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn đăng ký đề tài {{ $dt->TenDeTai }} cho nhóm của mình?');" style="font-size: 0.825rem;">
                                                <i class="fa-solid fa-pen-to-square me-2"></i>{{ isset($regState) && $regState['is_supplementary'] ? 'ĐK Bổ Sung' : 'Đăng Ký' }}
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
                                <i class="fa-solid fa-clipboard-question fs-1 text-muted opacity-50 mb-3 d-block me-2"></i>
                                Hiện tại chưa có đề tài nào phù hợp với bộ lọc được công bố.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($detais->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="text-secondary" style="font-size: 0.85rem;">
                Hiển thị <strong>{{ $detais->firstItem() }}</strong> &ndash; <strong>{{ $detais->lastItem() }}</strong> trên tổng số <strong>{{ $detais->total() }}</strong> đề tài đã công bố
            </div>
            <div>
                {{ $detais->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- MODAL XEM CHI TIẾT ĐỀ TÀI & ĐỀ CƯƠNG (SPLIT VIEW 2 BÊN TRỰC TIẾP TRÊN MÀN HÌNH) -->
<div class="modal fade" id="modalTopicOutlineSplit" tabindex="-1" aria-labelledby="modalTopicOutlineSplitLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw; height: 92vh; margin: 4vh auto;">
        <div class="modal-content h-100 border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 text-white" style="background-color: #003366; flex-shrink: 0;">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <span class="badge bg-white text-primary fw-bold px-2 py-1" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-book-open-reader me-2"></i> ĐỀ CƯƠNG &amp; ĐỀ TÀI
                    </span>
                    <h6 class="modal-title text-truncate fw-bold mb-0 text-white" id="modalTopicOutlineSplitLabel" style="font-size: 1rem;">
                        Chi Tiết Đề Tài &amp; Đề Cương Khóa Luận
                    </h6>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                    <a href="#" id="modalSplitOpenTabBtn" target="_blank" class="btn btn-sm btn-outline-light py-1 px-3 rounded-pill d-inline-flex align-items-center" title="Mở tệp đề cương trong tab mới">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> <span class="d-none d-sm-inline">Mở tệp riêng</span>
                    </a>
                    <a href="#" id="modalSplitDownloadBtn" download class="btn btn-sm btn-warning text-dark fw-semibold py-1 px-3 rounded-pill d-inline-flex align-items-center" title="Tải file đề cương về máy">
                        <i class="fa-solid fa-download me-2"></i> <span class="d-none d-sm-inline">Tải về</span>
                    </a>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
            </div>

            <!-- Modal Body (Split 2 Cột: 1 bên đề tài - 1 bên đề cương) -->
            <div class="modal-body p-0 bg-light" style="flex: 1 1 auto; overflow: hidden; min-height: 0;">
                <div class="row g-0 h-100">
                    <!-- CỘT 1 (BÊN TRÁI): THÔNG TIN ĐỀ TÀI -->
                    <div class="col-12 col-lg-5 h-100 bg-white border-end d-flex flex-column" style="overflow-y: auto; scrollbar-width: thin; scrollbar-color: #0072CE #f1f5f9;">
                        <div class="p-4 flex-grow-1">
                            <!-- Tiêu đề đề tài -->
                            <div class="mb-3 pb-3 border-bottom">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.5px;">Tên Đề Tài Khóa Luận</span>
                                <h5 class="fw-bold text-dark mb-2" id="splitModalTenDeTai" style="color: #003366 !important; line-height: 1.5; font-size: 1.15rem;"></h5>
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2" id="splitModalBadges">
                                    <span class="badge badge-neutral px-3 py-1">Mã: <code id="splitModalMaDeTai" class="fw-bold"></code></span>
                                    <span class="badge badge-neutral px-3 py-1" id="splitModalHocPhan"></span>
                                    <span class="badge badge-neutral px-3 py-1" id="splitModalBoMon"></span>
                                    <span class="badge badge-neutral px-3 py-1" id="splitModalHocKy"></span>
                                    <span class="badge badge-neutral px-3 py-1" id="splitModalLinhVuc"></span>
                                    <span class="badge badge-neutral px-3 py-1" id="splitModalChiTieu"></span>
                                </div>
                            </div>

                            <!-- Giảng viên hướng dẫn -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
    <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.725rem; letter-spacing: 0.5px;">
        <i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Giảng Viên Hướng Dẫn (GVHD)
    </div>
    <div class="d-flex align-items-start gap-2">
        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold text-dark" id="splitModalGvHoTen" style="font-size: 0.95rem;"></div>
            <div class="text-secondary small" id="splitModalGvInfo"></div>
            <div class="text-secondary small mt-2 pt-2 border-top" id="splitModalGvContact"></div>
        </div>
    </div>
</div>

                            <!-- Mô tả chi tiết đề tài -->
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="font-size: 0.725rem; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-align-left text-primary me-2"></i>Mô Tả Chi Tiết &amp; Mục Tiêu Đề Tài
                                </label>
                                <div class="p-3 bg-light rounded-3 border">
                                    <div id="splitModalMoTa" class="topic-desc-content"></div>
                                </div>
                            </div>

                            <!-- Yêu cầu cần đạt -->
                            <div class="mb-3" id="splitModalYeuCauWrapper">
                                <label class="text-muted small fw-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="font-size: 0.725rem; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-list-check text-primary me-2"></i>Yêu Cầu &amp; Kiến Thức Cần Chuẩn Bị
                                </label>
                                <div class="p-3 bg-light rounded-3 border">
                                    <div id="splitModalYeuCau" class="topic-desc-content"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 2 (BÊN PHẢI): KHUNG XEM TRỰC TIẾP FILE ĐỀ CƯƠNG -->
                    <div class="col-12 col-lg-7 h-100 d-flex flex-column" style="background: #525659;">
                        <!-- Viewer sub-header -->
                        <div class="bg-dark text-white px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-shrink-0" style="font-size: 13px;">
                            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                <span class="badge bg-danger text-white" id="splitModalFileTypeBadge"><i class="fa-solid fa-file-pdf me-2"></i> PDF</span>
                                <span class="text-truncate text-light small" id="splitModalFileNameDisplay"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <a href="#" id="splitModalSubOpenTab" target="_blank" class="btn btn-sm btn-outline-light py-1 px-3 rounded-pill d-inline-flex align-items-center" style="font-size: 12px;">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Mở tab riêng
                                </a>
                                <a href="#" id="splitModalSubDownload" download class="btn btn-sm btn-warning text-dark fw-bold py-1 px-3 rounded-pill d-inline-flex align-items-center" style="font-size: 12px;">
                                    <i class="fa-solid fa-download me-2"></i> Tải PDF
                                </a>
                            </div>
                        </div>

                        <!-- Viewer content: Render canvas ảnh trang PDF với PDF.js & Iframe fallback -->
                        <div class="flex-grow-1 position-relative h-100" style="overflow: hidden;">
                            <!-- Loading Spinner -->
                            <div id="splitPdfLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white" style="z-index: 20;">
                                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                                <div class="fw-bold text-dark fs-6">Đang tải và hiển thị các trang tài liệu PDF...</div>
                                <div class="small text-muted mt-1">Hệ thống đang dựng trang ảnh tài liệu trực tiếp</div>
                            </div>

                            <!-- PDF Canvas Scroll Area (Dựng các trang PDF thành Canvas ảnh) -->
                            <div id="splitPdfViewerWrapper" class="w-100 h-100 overflow-auto text-center p-3" style="background-color: #525659; display: none;">
                                <div id="splitPdfPagesContainer" class="d-flex flex-column align-items-center gap-3">
                                    <!-- Các trang PDF Canvas sẽ được dựng tại đây -->
                                </div>
                            </div>

                            <!-- Fallback iframe khi cần -->
                            <div id="splitPdfIframeWrapper" class="w-100 h-100 d-none">
                                <iframe id="splitModalPdfIframe" src="" style="width: 100%; height: 100%; border: none; background: #525659;"></iframe>
                            </div>
                            
                            <!-- Container Word .docx fallback nếu có -->
                            <div id="splitModalDocxContainer" class="w-100 h-100 overflow-auto p-4 bg-white d-none position-absolute top-0 start-0" style="z-index: 15;">
                                <div id="splitModalDocxContent" class="topic-desc-content" style="max-width: 860px; margin: 0 auto;"></div>
                            </div>

                            <!-- Fallback khi chưa có file hoặc lỗi -->
                            <div id="splitModalEmptyContainer" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center bg-white d-none position-absolute top-0 start-0" style="z-index: 15;">
                                <i class="fa-regular fa-file-lines text-muted opacity-50 display-3 mb-3"></i>
                                <h6 class="fw-bold text-dark mb-1">Chưa Có File Đề Cương Đính Kèm</h6>
                                <p class="text-muted small mb-0">Giảng viên chưa tải lên tệp đề cương chi tiết cho đề tài này.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
                <div class="small text-muted">
                    <i class="fa-regular fa-circle-question me-2 text-primary"></i> Nhấn phím <kbd>ESC</kbd> hoặc nút Đóng để quay lại màn hình đăng ký đề tài.
                </div>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/pdf.min.js') }}"></script>
<script src="{{ asset('js/mammoth.browser.min.js') }}"></script>
<script>
if (window.pdfjsLib) {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
}

function decodeBase64Utf8(str) {
    if (!str) return '';
    try {
        return decodeURIComponent(atob(str).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    } catch(e) {
        try {
            return atob(str);
        } catch(err) {
            return str;
        }
    }
}

function renderPdfPagesToCanvas(pdfDoc) {
    const pagesContainer = document.getElementById('splitPdfPagesContainer');
    if (!pagesContainer) return;
    pagesContainer.innerHTML = '';

    const wrapper = document.getElementById('splitPdfViewerWrapper');
    const availableWidth = wrapper ? Math.max(wrapper.clientWidth - 50, 480) : 650;
    const dpr = window.devicePixelRatio || 1;

    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
        const pageDiv = document.createElement('div');
        pageDiv.className = 'pdf-page-box shadow-sm mb-3 rounded bg-white overflow-hidden';
        pageDiv.id = 'split-pdf-page-' + pageNum;

        const canvas = document.createElement('canvas');
        canvas.style.display = 'block';
        canvas.style.margin = '0 auto';
        pageDiv.appendChild(canvas);
        pagesContainer.appendChild(pageDiv);

        (function(num, c, div) {
            pdfDoc.getPage(num).then(function(page) {
                const unscaledViewport = page.getViewport({ scale: 1.0 });
                const actualScale = Math.min(Math.max(availableWidth / unscaledViewport.width, 0.6), 2.2);

                const viewport = page.getViewport({ scale: actualScale });
                c.style.width = viewport.width + 'px';
                c.style.height = viewport.height + 'px';

                c.width = Math.floor(viewport.width * dpr);
                c.height = Math.floor(viewport.height * dpr);

                const ctx = c.getContext('2d');
                ctx.scale(dpr, dpr);

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        })(pageNum, canvas, pageDiv);
    }
}

function openTopicOutlineSplitModal(btn) {
    const ds = btn.dataset;
    const maDeTai = ds.madetai || '';
    const tenDeTai = ds.tendetai || '';
    const gvhd = ds.gvhd || 'Chưa cập nhật';
    const hocVi = ds.hocvi || 'Giảng viên';
    const boMon = ds.bomon || '';
    const email = ds.email || '';
    const sdt = ds.sdt || '';
    const hocPhan = ds.hocphan || 'Khóa luận';
    const hocKy = ds.hocky || '';
    const linhVuc = ds.linhvuc || '';
    const chiTieu = ds.chitieu || '3';
    const moTa = decodeBase64Utf8(ds.mota || '');
    const yeuCau = decodeBase64Utf8(ds.yeucau || '');
    const fileUrl = ds.fileurl || '';
    const fileName = ds.filename || 'de-cuong.pdf';

    // Cột trái: Thông tin đề tài
    document.getElementById('splitModalTenDeTai').textContent = tenDeTai;
    document.getElementById('splitModalMaDeTai').textContent = maDeTai;
    document.getElementById('splitModalHocPhan').textContent = hocPhan;
    document.getElementById('splitModalBoMon').textContent = boMon;
    document.getElementById('splitModalHocKy').textContent = hocKy || 'Học kỳ';
    
    const elLinhVuc = document.getElementById('splitModalLinhVuc');
    if (linhVuc) {
        elLinhVuc.textContent = linhVuc;
        elLinhVuc.classList.remove('d-none');
    } else {
        elLinhVuc.classList.add('d-none');
    }
    document.getElementById('splitModalChiTieu').textContent = 'Chỉ tiêu: ' + chiTieu + ' SV';

    // GVHD
    document.getElementById('splitModalGvHoTen').textContent = gvhd;
    document.getElementById('splitModalGvInfo').textContent = hocVi + (boMon ? ' • ' + boMon : '');
    
    let contactHtml = '';
    if (email) contactHtml += '<div><i class="fa-regular fa-envelope me-2 text-muted"></i>' + email + '</div>';
    if (sdt) contactHtml += '<div class="mt-1"><i class="fa-solid fa-phone me-2 text-muted"></i>' + sdt + '</div>';
    if (!contactHtml) contactHtml = '<div class="text-muted fst-italic">Chưa có thông tin liên hệ</div>';
    document.getElementById('splitModalGvContact').innerHTML = contactHtml;

    // Mô tả
    document.getElementById('splitModalMoTa').innerHTML = moTa || '<span class="text-muted fst-italic">Chưa có nội dung mô tả chi tiết.</span>';

    // Yêu cầu
    const elYeuCauWrapper = document.getElementById('splitModalYeuCauWrapper');
    if (yeuCau && yeuCau.trim() !== '') {
        elYeuCauWrapper.classList.remove('d-none');
        document.getElementById('splitModalYeuCau').innerHTML = yeuCau;
    } else {
        elYeuCauWrapper.classList.add('d-none');
    }

    // Cột phải: Khung xem đề cương
    const loadingBox = document.getElementById('splitPdfLoading');
    const canvasWrapper = document.getElementById('splitPdfViewerWrapper');
    const iframeWrapper = document.getElementById('splitPdfIframeWrapper');
    const iframe = document.getElementById('splitModalPdfIframe');
    const docxBox = document.getElementById('splitModalDocxContainer');
    const emptyBox = document.getElementById('splitModalEmptyContainer');
    const openTabBtn = document.getElementById('modalSplitOpenTabBtn');
    const downloadBtn = document.getElementById('modalSplitDownloadBtn');
    const subOpenTab = document.getElementById('splitModalSubOpenTab');
    const subDownload = document.getElementById('splitModalSubDownload');
    const fileNameDisplay = document.getElementById('splitModalFileNameDisplay');
    const typeBadge = document.getElementById('splitModalFileTypeBadge');

    fileNameDisplay.textContent = fileName;

    // Reset giao diện trước khi tải
    loadingBox.classList.remove('d-none');
    canvasWrapper.style.display = 'none';
    iframeWrapper.classList.add('d-none');
    docxBox.classList.add('d-none');
    emptyBox.classList.add('d-none');
    document.getElementById('splitPdfPagesContainer').innerHTML = '';

    if (fileUrl) {
        openTabBtn.href = fileUrl;
        openTabBtn.classList.remove('d-none');
        downloadBtn.href = fileUrl;
        downloadBtn.setAttribute('download', fileName);
        downloadBtn.classList.remove('d-none');
        subOpenTab.href = fileUrl;
        subDownload.href = fileUrl;
        subDownload.setAttribute('download', fileName);

        const cleanUrl = fileUrl.split('?')[0].toLowerCase();
        if (cleanUrl.endsWith('.docx') || cleanUrl.endsWith('.doc')) {
            typeBadge.className = 'badge bg-primary text-white';
            typeBadge.innerHTML = '<i class="fa-solid fa-file-word me-2"></i> DOCX';
            loadingBox.classList.add('d-none');
            docxBox.classList.remove('d-none');
            
            if (typeof mammoth !== 'undefined') {
                fetch(fileUrl)
                    .then(res => res.arrayBuffer())
                    .then(ab => mammoth.convertToHtml({ arrayBuffer: ab }))
                    .then(res => {
                        document.getElementById('splitModalDocxContent').innerHTML = res.value || '<div class="alert alert-info">Tài liệu không có nội dung văn bản.</div>';
                    })
                    .catch(err => {
                        document.getElementById('splitModalDocxContent').innerHTML = '<div class="alert alert-warning text-center">Không thể hiển thị trực tiếp Word. Vui lòng bấm "Mở tab riêng" hoặc "Tải về" để xem file.</div>';
                    });
            } else {
                document.getElementById('splitModalDocxContent').innerHTML = '<div class="alert alert-warning text-center">Vui lòng bấm "Mở tab riêng" hoặc "Tải về" để xem tệp Word.</div>';
            }
        } else {
            typeBadge.className = 'badge bg-danger text-white';
            typeBadge.innerHTML = '<i class="fa-solid fa-file-pdf me-2"></i> PDF';

            // Dùng Mozilla PDF.js dựng các trang PDF thành ảnh Canvas trực tiếp
            if (window.pdfjsLib) {
                pdfjsLib.getDocument(fileUrl).promise.then(function(pdfDoc) {
                    loadingBox.classList.add('d-none');
                    canvasWrapper.style.display = 'block';
                    renderPdfPagesToCanvas(pdfDoc);
                }).catch(function(err) {
                    console.warn('PDF.js render failed, fallback to iframe:', err);
                    loadingBox.classList.add('d-none');
                    iframeWrapper.classList.remove('d-none');
                    iframe.src = fileUrl + '#toolbar=1&navpanes=0';
                });
            } else {
                loadingBox.classList.add('d-none');
                iframeWrapper.classList.remove('d-none');
                iframe.src = fileUrl + '#toolbar=1&navpanes=0';
            }
        }
    } else {
        loadingBox.classList.add('d-none');
        emptyBox.classList.remove('d-none');
        openTabBtn.classList.add('d-none');
        downloadBtn.classList.add('d-none');
    }

    // Hiển thị modal trên màn hình ngay lập tức
    const modalEl = document.getElementById('modalTopicOutlineSplit');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}
</script>

@include('partials.modal_preview_decuong')
@endsection