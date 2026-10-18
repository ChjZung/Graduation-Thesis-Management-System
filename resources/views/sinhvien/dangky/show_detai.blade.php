@extends('layouts.sinhvien')

@section('page_title', 'Chi Tiết Đề Tài & Đề Cương - ' . $deTai->TenDeTai)

@push('styles')
<style>
    .split-layout-root {
        font-size: 0.875rem;
        color: #334155;
        padding: 4px 0 24px 0;
    }
    .topic-info-card {
        border-radius: 14px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 51, 102, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 195px);
        min-height: 650px;
        overflow: hidden;
    }
    .topic-info-scroll {
        overflow-y: auto;
        padding: 24px;
        flex-grow: 1;
        scrollbar-width: thin;
        scrollbar-color: #0072CE #f1f5f9;
    }
    .topic-info-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .topic-info-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .topic-info-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .doc-viewer-card {
        border-radius: 14px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 51, 102, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 195px);
        min-height: 650px;
        overflow: hidden;
    }
    .doc-viewer-header {
        background: #003366;
        color: #f8fafc;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .doc-viewer-body {
        flex-grow: 1;
        position: relative;
        background: #525659;
        overflow: hidden;
    }
    .doc-viewer-body iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
    .doc-fullscreen-active {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
    .badge-neutral {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .topic-desc-content {
        line-height: 1.7;
        color: #334155;
        font-size: 0.875rem;
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
</style>
@endpush

@section('content')
@php
    $hasFile = !empty($deTai->FileDeCuong);
    $fileUrl = '';
    $fileName = '';
    $isPdf = false;
    $isDocx = false;

    if ($hasFile) {
        $fileUrl = Str::startsWith($deTai->FileDeCuong, ['http', 'storage/']) ? asset($deTai->FileDeCuong) : asset('storage/' . $deTai->FileDeCuong);
        $fileName = basename($deTai->FileDeCuong);
        $cleanLower = strtolower($fileName);
        $isPdf = str_ends_with($cleanLower, '.pdf');
        $isDocx = str_ends_with($cleanLower, '.docx') || str_ends_with($cleanLower, '.doc');
    }

    $myGroupRegThis = (isset($dangKyCurrent) && $dangKyCurrent && $dangKyCurrent->MaDeTai === $deTai->MaDeTai) ? $dangKyCurrent : null;
    $isTakenByOther = isset($daDangKy) && (!isset($nhom) || $daDangKy->MaNhom !== $nhom->MaNhom);
    $hasActiveRegistration = isset($dangKyCurrent) && $dangKyCurrent && in_array($dangKyCurrent->TrangThai, ['Chờ duyệt', 'Đã duyệt']);
@endphp

<div class="split-layout-root">
    <!-- ACTION BAR & BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('sinhvien.dangky.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs bg-white d-inline-flex align-items-center">
                <i class="fa-solid fa-arrow-left me-2 text-secondary"></i>Quay Lại Danh Sách
            </a>
            <span class="badge badge-neutral px-3 py-2 font-monospace">
                <code>{{ $deTai->MaDeTai }}</code>
            </span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                {{ $deTai->hocPhanRef->TenHocPhan ?? $deTai->HocPhan ?? 'Khóa Luận Tốt Nghiệp' }}
            </span>
            <span class="badge badge-neutral px-3 py-2 rounded-pill">
                {{ $deTai->hocKy->TenHocKy ?? 'Học kỳ' }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($hasFile)
                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-xs bg-white d-inline-flex align-items-center">
                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Mở File Riêng
                </a>
                <a href="{{ $fileUrl }}" download="{{ $fileName }}" class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-semibold text-dark shadow-xs d-inline-flex align-items-center">
                    <i class="fa-solid fa-download me-2"></i>Tải Đề Cương
                </a>
            @endif
        </div>
    </div>

    <!-- GIAO DIỆN SONG SONG 2 BÊN (SPLIT VIEW): 1 BÊN ĐỀ TÀI - 1 BÊN ĐỀ CƯƠNG -->
    <div class="row g-3">
        <!-- CỘT TRÁI: THÔNG TIN CHI TIẾT ĐỀ TÀI (5 CỘT) -->
        <div class="col-12 col-xl-5">
            <div class="topic-info-card">
                <!-- Header cột trái -->
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info me-2" style="color: #0072CE;"></i>
                        <span class="fw-bold" style="color: #003366; font-size: 0.95rem;">Thông Tin Chi Tiết Đề Tài</span>
                    </div>
                    <span class="badge badge-neutral px-3 py-1" style="font-size: 0.775rem;">
                        Chỉ tiêu: <strong>{{ $deTai->SoLuongSinhVienToiDa ?? 3 }} SV</strong>
                    </span>
                </div>

                <!-- Thân cuộn thông tin đề tài -->
                <div class="topic-info-scroll">
                    <!-- Tên đề tài -->
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.725rem;">Tên Đề Tài Khóa Luận</label>
                        <h5 class="fw-bold mb-2 text-dark" style="color: #003366 !important; line-height: 1.5; font-size: 1.15rem;">
                            {{ $deTai->TenDeTai }}
                        </h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                            <span class="badge badge-neutral px-3 py-1">
                                <i class="fa-solid fa-building-columns me-2" style="color: #0072CE;"></i>
                                {{ $deTai->giangVien->boMon->TenBoMon ?? ($deTai->hocPhanRef->boMon->TenBoMon ?? 'Bộ môn CNTT') }}
                            </span>
                            @if($deTai->LinhVuc)
                                <span class="badge badge-neutral px-3 py-1">
                                    <i class="fa-solid fa-microchip me-2" style="color: #0072CE;"></i>{{ $deTai->LinhVuc }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Giảng viên hướng dẫn đề xuất -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.725rem;">
                            <i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Giảng Viên Hướng Dẫn (GVHD)
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                    {{ $deTai->giangVien->HoTen ?? 'Chưa rõ' }}
                                </div>
                                <div class="text-secondary small">
                                    {{ $deTai->giangVien->HocVi ?? 'Giảng viên' }} &bull; {{ $deTai->giangVien->boMon->TenBoMon ?? 'Bộ môn CNTT' }}
                                </div>
                                <div class="text-secondary small mt-2 pt-2 border-top">
                                    <div><i class="fa-regular fa-envelope me-2 text-muted"></i>{{ $deTai->giangVien->Email ?? 'Chưa cập nhật email' }}</div>
                                    @if(!empty($deTai->giangVien->SoDienThoai))
                                        <div class="mt-1"><i class="fa-solid fa-phone me-2 text-muted"></i>{{ $deTai->giangVien->SoDienThoai }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mô tả chi tiết đề tài -->
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="letter-spacing: 0.5px; font-size: 0.725rem;">
                            <i class="fa-solid fa-align-left text-primary me-2"></i>Mô Tả Chi Tiết &amp; Mục Tiêu Đề Tài
                        </label>
                        <div class="p-3 bg-light rounded-3 border">
                            @if($deTai->MoTa)
                                <div class="topic-desc-content">
                                    {!! \App\Services\HtmlSanitizerService::cleanDescription($deTai->MoTa) !!}
                                </div>
                            @else
                                <div class="text-muted fst-italic small">Chưa có nội dung mô tả chi tiết cho đề tài này.</div>
                            @endif
                        </div>
                    </div>

                    <!-- Yêu cầu cần đạt đối với sinh viên -->
                    @if($deTai->YeuCau)
                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase mb-2 d-flex align-items-center gap-2" style="letter-spacing: 0.5px; font-size: 0.725rem;">
                            <i class="fa-solid fa-list-check text-primary me-2"></i>Yêu Cầu &amp; Kiến Thức Cần Chuẩn Bị
                        </label>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="topic-desc-content">
                                {!! \App\Services\HtmlSanitizerService::cleanDescription($deTai->YeuCau) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Khu vực thao tác đăng ký -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.725rem;">
                            Tình Trạng &amp; Đăng Ký Đề Tài
                        </div>
                        @if($myGroupRegThis)
                            @if($myGroupRegThis->TrangThai === 'Từ chối')
                                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 rounded-3 small mb-0">
                                    <i class="fa-solid fa-circle-xmark fs-5"></i>
                                    <div>Đơn đăng ký của nhóm đã bị từ chối: {{ $myGroupRegThis->LyDoTuChoi ?? 'Đề tài không phù hợp.' }}</div>
                                </div>
                            @elseif($myGroupRegThis->TrangThai === 'Đã duyệt')
                                <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 rounded-3 small mb-0">
                                    <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                                    <div><strong>Đề tài đã được gán chính thức cho nhóm của bạn!</strong></div>
                                </div>
                            @else
                                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 px-3 rounded-3 small mb-0">
                                    <i class="fa-solid fa-clock fs-5 text-warning"></i>
                                    <div>Đơn đăng ký đề tài của nhóm đang chờ phê duyệt.</div>
                                </div>
                            @endif
                        @elseif($isTakenByOther)
                            <div class="alert alert-secondary d-flex align-items-center gap-2 py-2 px-3 rounded-3 small mb-0">
                                <i class="fa-solid fa-lock text-muted"></i>
                                <div>Đề tài này hiện đã có nhóm khác đăng ký thực hiện.</div>
                            </div>
                        @elseif(isset($nhom) && $nhom)
                            @if(isset($regState) && !$regState['can_register'])
                                <button class="btn btn-secondary w-100 rounded-pill py-2 text-white shadow-xs fw-semibold d-flex align-items-center justify-content-center" disabled>
                                    <i class="fa-solid fa-lock me-2"></i>{{ $regState['label'] }} ({{ $regState['message'] }})
                                </button>
                            @elseif($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                <div class="alert alert-info py-2 px-3 rounded-3 small mb-0">
                                    <i class="fa-solid fa-circle-info me-2"></i>Bạn là thành viên nhóm. Chỉ Trưởng nhóm (<strong>{{ $nhom->truongNhom->HoTen ?? 'Nhóm trưởng' }}</strong>) mới có quyền đăng ký đề tài.
                                </div>
                            @elseif($hasActiveRegistration)
                                <button class="btn btn-secondary w-100 rounded-pill py-2 text-white shadow-xs fw-semibold d-flex align-items-center justify-content-center" disabled>
                                    <i class="fa-solid fa-lock me-2"></i>Nhóm của bạn đã đăng ký đề tài khác
                                </button>
                            @elseif($soThanhVien < 3)
                                <div class="alert alert-warning py-2 px-3 rounded-3 small mb-2">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Nhóm của bạn hiện có <strong>{{ $soThanhVien }}/3</strong> thành viên. Cần đủ 3 thành viên mới được phép đăng ký.
                                </div>
                                <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-warning w-100 rounded-pill py-2 fw-semibold text-dark d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-user-plus me-2"></i>Vào Mời Thêm Thành Viên
                                </a>
                            @else
                                <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="MaDeTai" value="{{ $deTai->MaDeTai }}">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-xs d-flex align-items-center justify-content-center" 
                                            style="background-color: #003366 !important; border-color: #003366 !important;"
                                            onclick="return confirm('Bạn có chắc chắn muốn đăng ký đề tài {{ $deTai->TenDeTai }} cho nhóm của mình?');">
                                        <i class="fa-solid fa-pen-to-square me-2"></i>Xác Nhận Đăng Ký Đề Tài Này
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold shadow-xs d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-user-group me-2"></i>Bạn Cần Tạo Nhóm Trước Khi Đăng Ký
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: KHUNG XEM TRỰC TIẾP FILE ĐỀ CƯƠNG PDF / WORD (7 CỘT) -->
        <div class="col-12 col-xl-7">
            <div class="doc-viewer-card" id="deCuongDocViewerCard">
                <!-- Viewer Header -->
                <div class="doc-viewer-header">
                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                        <i class="fa-solid fa-file-pdf text-danger fs-5 flex-shrink-0 me-2"></i>
                        <span class="fw-semibold text-truncate small text-white" title="{{ $fileName ?: 'Đề cương chi tiết' }}">
                            {{ $fileName ?: 'Đề Cương Chi Tiết Đề Tài' }}
                        </span>
                        @if($isPdf)
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">PDF</span>
                        @elseif($isDocx)
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">DOCX</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        @if($hasFile)
                            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-light py-1 px-3 rounded-pill d-inline-flex align-items-center" title="Mở trong tab mới của trình duyệt" style="font-size: 0.775rem;">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Mở Tab Mới
                            </a>
                            <a href="{{ $fileUrl }}" download="{{ $fileName }}" class="btn btn-sm btn-warning text-dark fw-semibold py-1 px-3 rounded-pill d-inline-flex align-items-center" title="Tải file về máy tính" style="font-size: 0.775rem;">
                                <i class="fa-solid fa-download me-2"></i>Tải Về
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-light py-1 px-3 rounded-pill" id="btnToggleFullscreen" title="Xem toàn màn hình" style="font-size: 0.775rem;">
                            <i class="fa-solid fa-expand" id="fullscreenIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Viewer Body: Nhúng trực tiếp PDF hoặc Word -->
                <div class="doc-viewer-body">
                    @if($hasFile)
                        @if($isPdf)
                            {{-- Nhúng trực tiếp PDF Viewer của trình duyệt --}}
                            <iframe src="{{ $fileUrl }}#toolbar=1&navpanes=0" id="deCuongPdfIframe" title="Đề cương chi tiết"></iframe>
                        @elseif($isDocx)
                            {{-- Trình đọc file Word .docx --}}
                            <div class="w-100 h-100 overflow-auto p-4 bg-white" id="docxViewWrapper">
                                <div id="docxLoading" class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3" role="status"></div>
                                    <div class="fw-semibold text-secondary">Đang nạp và hiển thị nội dung tài liệu Word...</div>
                                </div>
                                <div id="docxContent" class="topic-desc-content" style="max-width: 860px; margin: 0 auto;"></div>
                            </div>
                            <script src="{{ asset('js/mammoth.browser.min.js') }}"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    fetch("{{ $fileUrl }}")
                                        .then(res => res.arrayBuffer())
                                        .then(ab => {
                                            if (typeof mammoth !== 'undefined') {
                                                return mammoth.convertToHtml({ arrayBuffer: ab });
                                            }
                                            throw new Error('Mammoth not loaded');
                                        })
                                        .then(result => {
                                            document.getElementById('docxLoading').classList.add('d-none');
                                            document.getElementById('docxContent').innerHTML = result.value || '<div class="alert alert-info">Tài liệu không có nội dung văn bản.</div>';
                                        })
                                        .catch(err => {
                                            console.warn('Word render error:', err);
                                            document.getElementById('docxLoading').innerHTML = `
                                                <i class="fa-solid fa-file-word text-primary display-4 mb-3"></i>
                                                <h6 class="fw-bold">Tài Liệu Đề Cương Microsoft Word</h6>
                                                <p class="text-muted small">Tệp Word đã sẵn sàng. Bạn có thể mở trong tab riêng hoặc tải về máy để xem trọn vẹn.</p>
                                                <a href="{{ $fileUrl }}" download class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center">
                                                    <i class="fa-solid fa-download me-2"></i>Tải Đề Cương Word (.docx)
                                                </a>
                                            `;
                                        });
                                });
                            </script>
                        @else
                            {{-- Định dạng khác --}}
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center bg-white">
                                <i class="fa-solid fa-file-lines text-primary display-4 mb-3"></i>
                                <h6 class="fw-bold text-dark mb-2">Tài Liệu Đề Cương Đính Kèm</h6>
                                <p class="text-muted small mb-3">Tệp đề cương đã sẵn sàng để xem hoặc tải về máy.</p>
                                <div class="d-flex gap-2">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2 d-inline-flex align-items-center">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Mở Trong Tab Mới
                                    </a>
                                    <a href="{{ $fileUrl }}" download class="btn btn-warning rounded-pill px-4 py-2 text-dark fw-semibold d-inline-flex align-items-center">
                                        <i class="fa-solid fa-download me-2"></i>Tải Về Máy
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Khi giảng viên chưa nộp đề cương --}}
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-5 bg-white">
                            <div class="rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fa-regular fa-file-lines fs-1 text-muted opacity-50"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Chưa Có File Đề Cương Chi Tiết</h6>
                            <p class="text-muted small mx-auto mb-0" style="max-width: 480px;">
                                Giảng viên hướng dẫn chưa tải lên tệp đề cương chi tiết cho đề tài này. Bạn có thể tham khảo mục tiêu và yêu cầu chuyên môn ở cột bên trái.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFullscreen = document.getElementById('btnToggleFullscreen');
    const docCard = document.getElementById('deCuongDocViewerCard');
    const icon = document.getElementById('fullscreenIcon');

    if (btnFullscreen && docCard) {
        btnFullscreen.addEventListener('click', function() {
            docCard.classList.toggle('doc-fullscreen-active');
            if (docCard.classList.contains('doc-fullscreen-active')) {
                icon.className = 'fa-solid fa-compress';
                btnFullscreen.title = 'Thu nhỏ lại';
            } else {
                icon.className = 'fa-solid fa-expand';
                btnFullscreen.title = 'Xem toàn màn hình';
            }
        });

        // Bấm phím ESC để thoát chế độ toàn màn hình
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && docCard.classList.contains('doc-fullscreen-active')) {
                docCard.classList.remove('doc-fullscreen-active');
                icon.className = 'fa-solid fa-expand';
                btnFullscreen.title = 'Xem toàn màn hình';
            }
        });
    }
});
</script>
@endsection
