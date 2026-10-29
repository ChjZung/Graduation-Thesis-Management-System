@extends('layouts.sinhvien')

@section('page_title', 'Chi Tiết Đợt Báo Cáo — ' . $dotNop->TenDotNop)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3 min-w-0 max-w-full overflow-x-hidden">

    @include('partials.report_milestone_progress')

    {{-- HEADER ĐIỀU HƯỚNG & THÔNG TIN ĐỢT NỘP --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-4 transition-all hover-shadow min-w-0 max-w-full overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 min-w-0 max-w-full">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 min-w-0 flex-grow-1 max-w-full">
                <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center transition-all hover-scale flex-shrink-0 text-nowrap">
                    <i class="fa-solid fa-arrow-left me-2"></i>Về danh sách tiến độ
                </a>
                <div class="min-w-0 flex-grow-1 max-w-full">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1 min-w-0 max-w-full">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold text-truncate d-inline-block max-w-full align-middle" style="max-width: 100%;" title="{{ $dotNop->giaiDoan->TenGiaiDoan ?? 'Giai đoạn khóa luận' }}">
                            {{ $dotNop->giaiDoan->TenGiaiDoan ?? 'Giai đoạn khóa luận' }}
                        </span>
                        <h5 class="fw-bold text-dark mb-0 min-w-0 max-w-full text-break break-words line-clamp-2" style="overflow-wrap: anywhere; word-break: break-word;" title="{{ $dotNop->TenDotNop }}">
                            {{ $dotNop->TenDotNop }}
                        </h5>
                        @php $tt = $dotNop->getTrangThaiHienThi(); @endphp
                        <span class="badge {{ $tt['badge'] }} rounded-pill px-3 py-1.5 fw-bold flex-shrink-0 text-nowrap">
                            {{ $tt['label'] }}
                        </span>
                    </div>

                    @php
                        $isOverdue = $dotNop->HanNop && now()->gt($dotNop->HanNop);
                        $isDueSoon = $dotNop->HanNop && now()->diffInDays($dotNop->HanNop, false) <= 2 && !$isOverdue;
                        if (!$dotNop->HanNop) {
                            $deadlineBadgeClass = 'text-secondary';
                            $deadlineIconClass = 'text-secondary';
                            $deadlineText = 'Không giới hạn';
                        } elseif ($isOverdue) {
                            $deadlineBadgeClass = 'text-danger fw-bold';
                            $deadlineIconClass = 'text-danger';
                            $deadlineText = \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i d/m/Y') . ' (Đã quá hạn)';
                        } elseif ($isDueSoon) {
                            $deadlineBadgeClass = 'text-warning-emphasis fw-bold';
                            $deadlineIconClass = 'text-warning';
                            $deadlineText = \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i d/m/Y') . ' (Sắp hết hạn)';
                        } else {
                            $deadlineBadgeClass = 'text-dark fw-semibold';
                            $deadlineIconClass = 'text-primary';
                            $deadlineText = \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i d/m/Y');
                        }
                    @endphp
                    <div class="text-muted small mt-1 py-1 d-flex flex-wrap align-items-center gap-x-3 gap-y-1 min-w-0 max-w-full">
                        <span class="d-inline-flex align-items-center min-w-0 text-truncate max-w-full">
                            <i class="fa-solid fa-chalkboard-user text-primary me-2 flex-shrink-0"></i>
                            <span class="text-truncate">GVHD: <strong class="text-dark">{{ $dotNop->deTai->giangVien?->HoTen ?? 'Chưa rõ' }}</strong></span>
                        </span>
                        <span class="d-none d-sm-inline text-muted">•</span>
                        <span class="d-inline-flex align-items-center text-nowrap">
                            <i class="fa-regular fa-clock {{ $deadlineIconClass }} me-1.5 flex-shrink-0"></i>
                            Hạn nộp: <strong class="{{ $deadlineBadgeClass }} ms-1">{{ $deadlineText }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 text-nowrap">
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small shadow-sm badge-check d-inline-flex align-items-center" style="gap: 6px;">
                    <i class="fa-solid fa-code-branch text-primary flex-shrink-0 icon-check"></i>
                    <span>Đã nộp {{ $phienBans->count() }} phiên bản</span>
                </span>
            </div>
        </div>
    </div>

    @if($dotNop->NoiDungYeuCau)
        <div class="info-callout info-callout-info mb-4">
            <div class="callout-title text-primary fs-6 mb-2">
                <i class="fa-solid fa-circle-info me-1 flex-shrink-0"></i>
                <span>Yêu Cầu Từ Giảng Viên Hướng Dẫn:</span>
            </div>
            <div class="callout-body ps-1" style="font-size: 13px; line-height: 1.65; white-space: pre-wrap;">{{ $dotNop->NoiDungYeuCau }}</div>
        </div>
    @endif

    {{-- BỐ CỤC 2 CỘT: XEM CÁC PHIÊN BẢN (TRÁI) & BIỂU MẪU NỘP BÁO CÁO (PHẢI) --}}
    <div class="row g-4 align-items-start min-w-0 max-w-full">
        
        {{-- CỘT TRÁI: DANH SÁCH & NỘI DUNG CÁC PHIÊN BẢN ĐÃ NỘP --}}
        <div class="col-12 col-lg-7 min-w-0 max-w-full">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden min-w-0 max-w-full" style="border: 1px solid #e2e8f0 !important;">
                
                <div class="card-header bg-white border-bottom p-3 p-md-4 min-w-0 max-w-full">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 min-w-0 max-w-full">
                        <span class="fw-bold text-dark text-uppercase small tracking-wide text-truncate">
                            <i class="fa-solid fa-timeline text-primary me-2"></i>Lịch Sử Các Phiên Bản Nộp
                        </span>
                        <span class="text-muted small text-nowrap" style="font-size: 11px;">Mỗi lần nộp lưu thành một phiên bản riêng biệt</span>
                    </div>

                    @if($phienBans->isNotEmpty())
                        <ul class="nav nav-pills gap-2 pt-1 flex-nowrap overflow-x-auto pb-1 max-w-full" id="pills-tab-sv" role="tablist">
                            @foreach($phienBans as $index => $pb)
                                <li class="nav-item flex-shrink-0" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }} rounded-pill py-2 px-3.5 small fw-bold transition-all text-nowrap pill-check-btn d-inline-flex align-items-center" 
                                            id="pills-sv-v{{ $pb->LanNop }}-tab" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#pills-sv-v{{ $pb->LanNop }}" 
                                            type="button" role="tab"
                                            style="gap: 6px;">
                                        <span>Phiên bản v{{ $pb->LanNop }}</span>
                                        @if($pb->TrangThai === 'Đạt')
                                            <i class="fa-solid fa-check icon-check text-success"></i>
                                        @elseif($pb->TrangThai === 'Yêu cầu nộp lại')
                                            <i class="fa-solid fa-rotate-left icon-check text-warning"></i>
                                        @else
                                            <i class="fa-solid fa-hourglass-half icon-check"></i>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="card-body p-3 p-md-4 min-w-0 max-w-full">
                    @if($phienBans->isEmpty())
                        <div class="text-center py-5 text-muted px-3">
                            <i class="fa-regular fa-folder-open fs-1 mb-3 d-block opacity-50"></i>
                            <span class="d-block mb-1 fw-medium text-dark">Chưa có bài nộp nào</span>
                            <span class="small">Nhóm của bạn chưa nộp báo cáo cho đợt này. Hãy hoàn thiện nội dung và nộp phiên bản đầu tiên ở form bên phải.</span>
                        </div>
                    @else
                        <div class="tab-content min-w-0 max-w-full" id="pills-tabContent-sv">
                            @foreach($phienBans as $index => $pb)
                                @php
                                    $aiJson = $pb->TomTatJson ?? [];
                                    $congViec = $aiJson['cong_viec_hoan_thanh'] ?? ($aiJson['data']['completed_work'] ?? []);
                                    $khoKhan = $aiJson['kho_khan'] ?? ($aiJson['data']['difficulties'] ?? []);
                                    $keHoach = $aiJson['ke_hoach_tiep_theo'] ?? ($aiJson['data']['next_steps'] ?? []);
                                    $canhBao = $aiJson['canh_bao'] ?? [];
                                @endphp

                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }} min-w-0 max-w-full" id="pills-sv-v{{ $pb->LanNop }}" role="tabpanel">
                                    
                                    {{-- Thông tin phiên bản --}}
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center pb-3 mb-4 border-bottom gap-2 min-w-0 max-w-full">
                                        <div class="min-w-0 flex-grow-1 max-w-full">
                                            <div class="fw-bold text-dark fs-5 mb-1 min-w-0 max-w-full text-break break-words" style="overflow-wrap: anywhere; word-break: break-word;" title="{{ $pb->TieuDe }}">{{ $pb->TieuDe }}</div>
                                            <div class="text-muted small">
                                                <i class="fa-regular fa-clock me-1 text-primary"></i>Nộp lúc: <strong>{{ \Carbon\Carbon::parse($pb->NgayNop)->format('H:i d/m/Y') }}</strong> •
                                                Tự đánh giá: <strong class="text-primary">{{ $pb->PhanTramHoanThanh }}%</strong>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-nowrap">
                                            @if($pb->TrangThai === 'Đạt')
                                                <span class="badge bg-success rounded-pill px-3 py-2 fw-bold shadow-sm badge-check d-inline-flex align-items-center" style="gap: 6px;">
                                                    <i class="fa-solid fa-check icon-check" style="font-size: 0.85em;"></i>
                                                    <span>GVHD đã duyệt Đạt</span>
                                                </span>
                                            @elseif($pb->TrangThai === 'Yêu cầu nộp lại')
                                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm badge-check d-inline-flex align-items-center" style="gap: 6px;">
                                                    <i class="fa-solid fa-rotate-left icon-check" style="font-size: 0.85em;"></i>
                                                    <span>GVHD yêu cầu nộp lại</span>
                                                </span>
                                            @else
                                                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold shadow-sm badge-check d-inline-flex align-items-center" style="gap: 6px;">
                                                    <i class="fa-solid fa-hourglass-half icon-check" style="font-size: 0.85em;"></i>
                                                    <span>Đang chờ GVHD nhận xét</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- File đính kèm & Git repository --}}
                                    <div class="row g-3 mb-4 min-w-0 max-w-full">
                                        <div class="col-12 {{ ($dotNop->YeuCauSourceCode || $pb->LinkCode) ? 'col-md-6' : '' }} min-w-0 max-w-full">
                                            @include('partials.report_attachment', ['report' => $pb])
                                        </div>

                                        @if($dotNop->YeuCauSourceCode || $pb->LinkCode)
                                        <div class="col-12 col-md-6 min-w-0 max-w-full">
                                            <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between transition-all hover-shadow-sm min-w-0 max-w-full">
                                                <div class="d-flex align-items-center gap-3 text-truncate me-2 min-w-0">
                                                    <i class="fa-brands fa-github text-dark fs-3 flex-shrink-0"></i>
                                                    <div class="text-truncate min-w-0">
                                                        <div class="fw-bold text-dark text-truncate small">Mã Nguồn Git</div>
                                                        <span class="text-muted small text-truncate d-block" style="font-size: 11px;">Repository liên kết</span>
                                                    </div>
                                                </div>
                                                @if($pb->LinkCode)
                                                    <a href="{{ $pb->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 transition-all flex-shrink-0 text-nowrap" title="{{ $pb->LinkCode }}">
                                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Mở
                                                    </a>
                                                @else
                                                    <span class="text-muted small fst-italic flex-shrink-0 text-nowrap">Không có link</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Nhận xét của Giảng viên hướng dẫn --}}
                                    @if($pb->NhanXetGV)
                                        <div class="p-3.5 rounded-3 border border-warning bg-warning bg-opacity-10 mb-4 shadow-sm min-w-0 max-w-full overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                                <span class="fw-bold text-dark small text-truncate">
                                                    <i class="fa-solid fa-comment-dots text-warning me-2 flex-shrink-0"></i>Nhận Xét Của GVHD ({{ $pb->NguoiNhanXet }}):
                                                </span>
                                                <span class="text-muted small flex-shrink-0" style="font-size: 11px;">
                                                    {{ \Carbon\Carbon::parse($pb->NgayNhanXet)->format('H:i d/m/Y') }}
                                                </span>
                                            </div>
                                            <div class="text-dark small whitespace-pre-line text-break break-words max-w-full" style="line-height: 1.6; overflow-wrap: anywhere; word-break: break-word;">{{ $pb->NhanXetGV }}</div>
                                        </div>
                                    @endif

                                    {{-- KẾT QUẢ THẨM ĐỊNH AI 3 NHÓM --}}
                                    <div class="mb-4 min-w-0 max-w-full overflow-hidden">
                                        @include('partials.ai_summary_11_sections', ['pb' => $pb])
                                    </div>

                                    {{-- Ghi chú bổ sung của sinh viên (nếu có nhập riêng) --}}
                                    @if(!empty($pb->NoiDungBaoCao) && $pb->NoiDungBaoCao !== $pb->TomTatAI)
                                        <div class="accordion mb-3 shadow-sm rounded-3 overflow-hidden min-w-0 max-w-full" id="accStudentNote-{{ $pb->LanNop }}">
                                            <div class="accordion-item border-0 min-w-0 max-w-full">
                                                <h2 class="accordion-header" id="headingNote-{{ $pb->LanNop }}">
                                                    <button class="accordion-button collapsed py-2.5 px-3 bg-light fw-bold text-dark small transition-all" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNote-{{ $pb->LanNop }}">
                                                        <i class="fa-solid fa-note-sticky text-primary me-2"></i>Ghi Chú Kèm Theo Của Sinh Viên
                                                    </button>
                                                </h2>
                                                <div id="collapseNote-{{ $pb->LanNop }}" class="accordion-collapse collapse min-w-0 max-w-full">
                                                    <div class="accordion-body p-3.5 small text-dark bg-white text-break break-words max-w-full overflow-hidden" style="font-size: 13px; line-height: 1.65; overflow-wrap: anywhere; word-break: break-word;">
                                                        {!! \App\Helpers\HtmlHelper::clean($pb->NoiDungBaoCao) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- CỘT PHẢI: BIỂU MẪU NỘP BÁO CÁO (TẠO PHIÊN BẢN MỚI) --}}
        <div class="col-12 col-lg-5 min-w-0 max-w-full">
            @php
                $canSubmit = $dotNop->isMoNop() || ($latestPhienBan && ($latestPhienBan->TrangThai === 'Yêu cầu nộp lại' || $latestPhienBan->TrangThai === 'Cần bổ sung'));
                $nextVersionNumber = ($latestPhienBan ? $latestPhienBan->LanNop : 0) + 1;
            @endphp

            @if(!($isLeader ?? true))
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center transition-all min-w-0 max-w-full overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                    <div class="concentric-circle-outer rounded-circle bg-warning bg-opacity-10 text-warning mb-3 shadow-sm">
                        <i class="fa-solid fa-user-lock fa-2x icon-check" style="font-size: 28px;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2 text-break break-words">Quyền Nộp Dành Riêng Cho Nhóm Trưởng</h5>
                    <p class="text-muted small mb-0 px-2 text-break break-words" style="line-height: 1.6;">
                        Theo quy định, chỉ <strong>Nhóm trưởng</strong> ({{ $nhom->truongNhom->HoTen ?? 'Trưởng nhóm' }}) mới có quyền đại diện nhóm nộp hoặc cập nhật báo cáo tiến độ. Bạn có thể theo dõi tiến độ và xem các phiên bản đã nộp ở cột bên trái.
                    </p>
                </div>
            @elseif($latestPhienBan && $latestPhienBan->TrangThai === 'Đạt')
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center transition-all min-w-0 max-w-full overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                    <div class="concentric-circle-outer rounded-circle bg-success bg-opacity-10 mb-3 shadow-sm">
                        <div class="concentric-circle-inner rounded-circle bg-success text-white shadow-xs">
                            <i class="fa-solid fa-check icon-check" style="font-size: 20px;"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-2 text-break break-words">Đợt Báo Cáo Đã Hoàn Thành</h5>
                    <p class="text-muted small mb-0 px-2 text-break break-words" style="line-height: 1.6;">Giảng viên hướng dẫn đã đánh giá Đạt cho phiên bản v{{ $latestPhienBan->LanNop }}. Bạn không cần nộp thêm cho đợt này.</p>
                </div>
            @elseif(!$canSubmit)
                @php
                    $isNotYetOpen = $dotNop->NgayMoNop && now()->lt($dotNop->NgayMoNop);
                    $isPastDeadline = $dotNop->HanNop && now()->gt($dotNop->HanNop);
                @endphp
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center transition-all min-w-0 max-w-full overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                    @if($isNotYetOpen)
                        <div class="concentric-circle-outer rounded-circle bg-warning bg-opacity-10 text-warning mb-3 shadow-sm">
                            <i class="fa-regular fa-clock fa-2x icon-check" style="font-size: 28px;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2 text-break break-words">Chưa Đến Thời Gian Mở Nộp</h5>
                        <p class="text-muted small mb-0 px-2 text-break break-words" style="line-height: 1.6;">
                            Đợt nộp này sẽ chính thức mở vào lúc: <strong>{{ \Carbon\Carbon::parse($dotNop->NgayMoNop)->format('H:i · d/m/Y') }}</strong>.<br>
                            Vui lòng quay lại sau thời gian trên để tải lên báo cáo tiến độ.
                        </p>
                    @elseif($isPastDeadline)
                        <div class="concentric-circle-outer rounded-circle bg-danger bg-opacity-10 text-danger mb-3 shadow-sm">
                            <i class="fa-solid fa-calendar-xmark fa-2x icon-check" style="font-size: 28px;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2 text-break break-words">Đã Quá Hạn Nộp Báo Cáo</h5>
                        <p class="text-muted small mb-0 px-2 text-break break-words" style="line-height: 1.6;">
                            Thời hạn nộp bài đã kết thúc vào lúc: <strong>{{ \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i · d/m/Y') }}</strong>.<br>
                            Nếu nhóm có lý do chính đáng cần bổ sung, vui lòng liên hệ trực tiếp Giảng viên hướng dẫn để được gia hạn.
                        </p>
                    @else
                        <div class="concentric-circle-outer rounded-circle bg-secondary bg-opacity-10 text-secondary mb-3 shadow-sm">
                            <i class="fa-solid fa-lock fa-2x icon-check" style="font-size: 28px;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2 text-break break-words">Đợt Nộp Đang Tạm Khóa</h5>
                        <p class="text-muted small mb-0 px-2 text-break break-words" style="line-height: 1.6;">Đợt nộp hiện đang được Giảng viên hướng dẫn tạm đóng hoặc đã hoàn thành.</p>
                    @endif
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-4 transition-all min-w-0 max-w-full" style="border: 1px solid #e2e8f0 !important; height: auto !important; max-height: none !important; overflow: visible !important;">
                    <div class="d-flex justify-content-between align-items-center mb-1 gap-2 min-w-0 max-w-full">
                        <h6 class="fw-bold text-dark mb-0 min-w-0 text-truncate" title="Nộp Báo Cáo Phiên Bản v{{ $nextVersionNumber }}">
                            <i class="fa-solid fa-file-arrow-up text-primary me-2 flex-shrink-0"></i><span class="text-truncate">Nộp Báo Cáo Phiên Bản v{{ $nextVersionNumber }}</span>
                        </h6>
                        <span class="badge bg-primary rounded-pill px-3 py-1.5 shadow-sm flex-shrink-0">v{{ $nextVersionNumber }}</span>
                    </div>
                    <span class="text-muted small mb-3.5 d-block text-break break-words">
                        {{ $latestPhienBan ? 'Nộp lại theo yêu cầu chỉnh sửa của Giảng viên hướng dẫn' : 'Nộp bài báo cáo lần đầu cho đợt này' }}
                    </span>

                    {{-- Thông báo nghiệp vụ: AI tự động phân tích không cần nhập thủ công --}}
                    <div class="info-callout info-callout-primary mb-3">
                        <div class="callout-title text-primary">
                            <i class="fa-solid fa-wand-magic-sparkles me-1 flex-shrink-0"></i>
                            <span>Tự Động Phân Tích Bằng AI:</span>
                        </div>
                        <div class="callout-body">
                            Hệ thống tự động sử dụng AI đọc hiểu báo cáo PDF hoặc DOCX, phân tích đầy đủ 3 nhóm thông tin: công việc đã thực hiện, khó khăn và kế hoạch tiếp theo.
                            <strong class="text-primary d-inline">Sinh viên không cần phải nhập thủ công lại các thông tin đã có trong tệp báo cáo.</strong>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('sinhvien.baocao.dot_nop.nop', $dotNop->MaDotNop) }}" enctype="multipart/form-data" class="min-w-0 max-w-full">
                        @csrf

                        {{-- Tệp PDF Báo Cáo --}}
                        <div class="mb-3.5 min-w-0 max-w-full">
                            <label class="form-label fw-bold small text-dark">
                                Tệp báo cáo (PDF hoặc DOCX) @if(!$latestPhienBan)<span class="text-danger">*</span>@endif
                            </label>
                            <input type="file" name="FilePdf" class="form-control form-control-sm py-2 px-3 rounded-3 transition-all w-100 max-w-full" accept=".pdf,.docx" {{ $latestPhienBan ? '' : 'required' }}>
                            <div class="form-text small mt-1 text-break break-words" style="font-size: 11px;">
                                @if($latestPhienBan)
                                    Đã có tệp báo cáo từ phiên bản trước. Chỉ cần tải lên nếu bạn có bản cập nhật mới.
                                @else
                                    Tệp PDF hoặc DOCX báo cáo tiến độ (tối đa 25MB). AI sẽ tự động đọc và lập tóm tắt.
                                @endif
                            </div>
                        </div>

                        {{-- Đường dẫn mã nguồn (Git) --}}
                        @if($dotNop->YeuCauSourceCode)
                        <div class="mb-3.5 min-w-0 max-w-full">
                            <label class="form-label fw-bold small text-dark">
                                Đường dẫn mã nguồn (GitHub / GitLab / Bitbucket) 
                                <span class="text-danger">*</span>
                            </label>
                            <input type="url" name="LinkCode" class="form-control form-control-sm py-2 px-3 rounded-3 transition-all w-100 max-w-full" 
                                   placeholder="https://github.com/username/repository" 
                                   value="{{ old('LinkCode', $latestPhienBan->LinkCode ?? '') }}"
                                   required>
                            <div class="form-text small mt-1 text-break break-words" style="font-size: 11px;">Đường dẫn repository mã nguồn của nhóm tại thời điểm nộp phiên bản này.</div>
                        </div>
                        @endif

                        {{-- Ghi chú chi tiết cho GVHD (Trình soạn thảo văn bản có định dạng) --}}
                        <div class="mb-3.5 min-w-0 max-w-full">
                            @include('partials.rich_text_editor', [
                                'name'         => 'NoiDungBaoCao',
                                'id'           => 'NoiDungBaoCao',
                                'value'        => old('NoiDungBaoCao', ''),
                                'label'        => 'Ghi chú & mô tả chi tiết gửi GVHD',
                                'placeholder'  => 'Nhập lời nhắn, mô tả chi tiết tiến độ hoặc các điểm lưu ý gửi GVHD (có thể định dạng in đậm, danh sách gạch đầu dòng)...',
                                'required'     => false,
                                'minHeight'    => '140px',
                                'compact'      => true,
                                'showTemplate' => false,
                                'helpText'     => 'Không bắt buộc. Nếu để trống, hệ thống sẽ tự động dùng bản tóm tắt trích xuất từ AI.',
                            ])
                        </div>

                        {{-- Thông báo: Tỷ lệ hoàn thành được AI tự động đánh giá khách quan --}}
                        <div class="info-callout info-callout-neutral mb-4">
                            <div class="callout-title text-primary">
                                <i class="fa-solid fa-calculator me-1 flex-shrink-0"></i>
                                <span>Tỷ Lệ Hoàn Thành Được AI Tự Động Đánh Giá:</span>
                            </div>
                            <div class="callout-body">
                                Hệ thống sẽ tự động phân tích mức độ hoàn thành công việc dựa trên nhiệm vụ được giao, kết quả đạt được và bằng chứng trong báo cáo. Tỷ lệ hoàn thành sẽ hiển thị sau khi AI thẩm định xong.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center transition-all hover-scale">
                            <i class="fa-solid fa-cloud-arrow-up me-2 fs-5"></i>Gửi Nộp &amp; Kích Hoạt AI Thẩm Định
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>

</div>

{{-- Bổ sung một vài hiệu ứng CSS nhẹ nhàng, hiện đại --}}
<style>
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
    }
    .hover-shadow-sm:hover {
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05) !important;
        background-color: #f8fafc !important;
    }
    .hover-scale:hover {
        transform: translateY(-1px);
    }
    .tracking-wide {
        letter-spacing: 0.5px;
    }
</style>
@endsection
