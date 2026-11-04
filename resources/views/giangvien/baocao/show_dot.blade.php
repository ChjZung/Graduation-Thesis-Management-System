@extends('layouts.giangvien')

@section('page_title', 'Chi Tiết Đợt Nộp & Đánh Giá Phiên Bản — ' . $dotNop->TenDotNop)

@section('content')
<div class="container-fluid report-review-page px-2 px-md-4 py-3 min-w-0 max-w-full">

    @once('ai-report-mobile-styles')
        <link rel="stylesheet" href="{{ asset('css/ai_report_mobile.css') }}?v={{ filemtime(public_path('css/ai_report_mobile.css')) }}">
    @endonce
    @php
        $roundLabels=['DANG_MO'=>'Đang mở nộp','HOAN_THANH'=>'Đã hoàn thành','TAM_DONG'=>'Tạm đóng','DA_DONG'=>'Đã đóng'];
        $roundStateClass=match($dotNop->TrangThai){'DANG_MO'=>'bg-info-subtle text-info-emphasis','HOAN_THANH'=>'bg-success-subtle text-success-emphasis',default=>'bg-secondary-subtle text-secondary'};
        $late=$dotNop->HanNop && now()->gt($dotNop->HanNop);
    @endphp
    <header class="report-round-header mb-4">
        <div class="report-round-header-grid">
            <a href="{{ route('giangvien.baocao.index', array_filter(['nhom'=>$nhom?->MaNhom,'gd'=>$dotNop->giaiDoan?->ThuTu])) }}" class="report-round-back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Danh sách tiến độ</a>
            <div class="report-round-heading">
                <div class="report-round-badges">
                    <span class="report-round-stage">{{ $dotNop->giaiDoan->TenGiaiDoan ?? 'Giai đoạn khóa luận' }}</span>
                    <span class="badge report-round-state {{ $roundStateClass }}">{{ $roundLabels[$dotNop->TrangThai]??$dotNop->TrangThai }}</span>
                    <span class="report-round-stage">{{ $phienBans->count() }} phiên bản đã nộp</span>
                    @php
                        $milestonePct = isset($reportMilestoneProgress['percentage']) ? $reportMilestoneProgress['percentage'] : ($nhom->progress_percent ?? 0);
                    @endphp
                    <span class="badge bg-white text-primary border rounded-pill px-3 py-1.5 shadow-xs d-inline-flex align-items-center" style="gap: 5px;">
                        <i class="fa-solid fa-chart-line text-primary"></i>
                        <span>Tiến độ hoàn thành: <strong>{{ number_format($milestonePct, 0) }}%</strong></span>
                    </span>
                </div>
                <h1>{{ $dotNop->TenDotNop }}</h1>
                <div class="report-round-meta">
                    <div><i class="fa-solid fa-folder-open text-primary" aria-hidden="true"></i><span>Đề tài: <strong>{{ $deTai->TenDeTai ?? $dotNop->deTai->TenDeTai ?? 'Khóa luận tốt nghiệp' }}</strong></span></div>
                    <div><i class="fa-solid fa-users text-primary" aria-hidden="true"></i><span>Nhóm: <strong>{{ $nhom ? ($nhom->TenNhom ?: $nhom->MaNhom) : 'Chưa rõ nhóm' }}</strong></span></div>
                    <div><i class="fa-regular fa-clock text-primary" aria-hidden="true"></i><span>Hạn nộp: <strong class="{{ $late?'text-danger':'' }}">{{ $dotNop->HanNop ? \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i d/m/Y') : 'Không giới hạn' }}</strong></span></div>
                </div>
            </div>
            <form class="teacher-round-toggle" method="POST" action="{{ route('giangvien.tiendo.dot_nop.toggle',$dotNop->MaDotNop) }}">
                @csrf
                <input type="hidden" name="nhom" value="{{ $nhom->MaNhom ?? '' }}">
                <input type="hidden" name="gd" value="{{ $dotNop->giaiDoan?->ThuTu ?? '' }}">
                <button type="submit" class="btn btn-sm {{ $dotNop->TrangThai==='DANG_MO'?'btn-outline-danger':'btn-outline-success' }}"><i class="fa-solid {{ $dotNop->TrangThai==='DANG_MO'?'fa-lock':'fa-lock-open' }} me-1" aria-hidden="true"></i>{{ $dotNop->TrangThai==='DANG_MO'?'Đóng đợt nộp':'Mở lại đợt nộp' }}</button>
            </form>
        </div>
        <details class="report-round-requirements">
            <summary><span class="report-round-requirements-title"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>Yêu cầu của giảng viên</span><span class="report-round-requirements-action"><span class="when-closed">Xem nội dung</span><span class="when-open">Thu gọn</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span></summary>
            <div class="report-round-requirements-body">{{ $dotNop->NoiDungYeuCau ?: 'Chưa có mô tả yêu cầu cho đợt nộp này.' }}</div>
        </details>
    </header>

    @if($phienBans->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white min-w-0 max-w-full overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
            <i class="fa-regular fa-folder-open fs-1 text-muted opacity-50 mb-3 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">Chưa có bài nộp nào từ sinh viên</h5>
            <p class="text-muted small mb-0 text-break break-words">Khi nhóm sinh viên nộp báo cáo cho đợt này, các phiên bản nộp sẽ hiển thị chi tiết tại đây kèm kết quả phân tích AI.</p>
        </div>
    @else

    {{-- BỐ CỤC 2 CỘT: XEM PHIÊN BẢN (TRÁI) & FORM NHẬN XÉT ĐÁNH GIÁ (PHẢI) --}}
    <div class="report-workspace-layout">
        
        {{-- CỘT TRÁI: DANH SÁCH & NỘI DUNG CÁC PHIÊN BẢN (VERSIONS) --}}
        <div class="col-12 col-lg-7 min-w-0 max-w-full">
            <div class="card border-0 shadow-sm rounded-4 bg-white min-w-0 max-w-full overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                
                {{-- Thanh Tab chọn phiên bản --}}
                <div class="card-header bg-white border-bottom p-3 min-w-0 max-w-full overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap min-w-0">
                        <span class="fw-bold text-dark small text-uppercase min-w-0 text-truncate">
                            <i class="fa-solid fa-code-branch text-primary me-2 flex-shrink-0"></i>Lịch Sử Các Phiên Bản Nộp ({{ $phienBans->count() }} phiên bản)
                        </span>
                        <span class="text-muted small flex-shrink-0 text-nowrap" style="font-size: 11px;">Mỗi lần nộp lại được bảo toàn nguyên vẹn</span>
                    </div>

                    <ul class="nav report-version-tabs flex-nowrap overflow-x-auto max-w-full" id="pills-tab" role="tablist">
                        @foreach($phienBans as $index => $pb)
                            <li class="nav-item flex-shrink-0" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }} small fw-bold text-nowrap d-inline-flex align-items-center"
                                        id="pills-v{{ $pb->LanNop }}-tab" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#pills-v{{ $pb->LanNop }}" 
                                        type="button" role="tab"
                                        style="gap: 6px;">
                                    <span>Phiên bản v{{ $pb->LanNop }}</span>
                                    @if($pb->TrangThai === 'Đạt')
                                        <i class="fa-solid fa-check icon-check text-success"></i>
                                    @elseif($pb->TrangThai === 'Yêu cầu nộp lại')
                                        <i class="fa-solid fa-rotate-left icon-check text-warning"></i>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Nội dung từng phiên bản --}}
                <div class="card-body p-3 p-md-4 tab-content min-w-0 max-w-full" id="pills-tabContent">
                    @foreach($phienBans as $index => $pb)
                        @php
                            $aiJson = $pb->TomTatJson ?? [];
                            $congViec = $aiJson['cong_viec_hoan_thanh'] ?? ($aiJson['data']['completed_work'] ?? []);
                            $ketQua = $aiJson['ket_qua_dat_duoc'] ?? [];
                            $khoKhan = $aiJson['kho_khan'] ?? ($aiJson['data']['difficulties'] ?? []);
                            $keHoach = $aiJson['ke_hoach_tiep_theo'] ?? ($aiJson['data']['next_steps'] ?? []);
                            $noiDungChuaRo = $aiJson['noi_dung_chua_ro'] ?? ($aiJson['data']['missing_information'] ?? []);
                            $canhBao = $aiJson['canh_bao'] ?? [];
                        @endphp

                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-v{{ $pb->LanNop }}" role="tabpanel">
                            
                            {{-- Thông tin tóm tắt phiên bản --}}
                            <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom gap-2 flex-wrap min-w-0">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-dark fs-6 text-break break-words" style="overflow-wrap: anywhere; word-break: break-word;">{{ $pb->TieuDe }}</div>
                                    <div class="text-muted small mt-1 min-w-0 text-break break-words">
                                        <i class="fa-regular fa-clock me-2"></i>Thời gian nộp: <strong>{{ \Carbon\Carbon::parse($pb->NgayNop)->format('H:i d/m/Y') }}</strong>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-nowrap d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        $pbPct = $pb->PhanTramHoanThanh ?? ($pb->TrangThai === 'Đạt' ? 100 : ($pb->TomTatJson['completion_percentage'] ?? ($pb->TomTatJson['data']['completion_percentage'] ?? null)));
                                    @endphp
                                    <span class="badge bg-white text-primary border rounded-pill px-3 py-2 fw-bold shadow-xs d-inline-flex align-items-center" style="gap: 6px;">
                                        <i class="fa-solid fa-chart-pie text-primary"></i>
                                        <span>Tiến độ: <strong class="text-primary">{{ $pbPct !== null ? $pbPct . '%' : 'Chưa đánh giá' }}</strong></span>
                                    </span>

                                    @if($pb->TrangThai === 'Đạt')
                                        <span class="badge bg-success rounded-pill px-3 py-2 fw-bold badge-check d-inline-flex align-items-center" style="gap: 6px;">
                                            <i class="fa-solid fa-check icon-check" style="font-size: 0.85em;"></i>
                                            <span>Đã duyệt Đạt</span>
                                        </span>
                                    @elseif($pb->TrangThai === 'Yêu cầu nộp lại')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold badge-check d-inline-flex align-items-center" style="gap: 6px;">
                                            <i class="fa-solid fa-rotate-left icon-check" style="font-size: 0.85em;"></i>
                                            <span>Yêu cầu nộp lại</span>
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold badge-check d-inline-flex align-items-center" style="gap: 6px;">
                                            <span>Chờ nhận xét</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="teacher-report-attachments mb-3">
                                @include('partials.report_attachment', ['report' => $pb])
                                @if($dotNop->YeuCauSourceCode || $pb->LinkCode)
                                    <div class="teacher-source-attachment">
                                        <div class="teacher-source-info">
                                            <i class="fa-solid fa-code-branch" aria-hidden="true"></i>
                                            <div>
                                                <strong>Mã nguồn dự án</strong>
                                                <span>{{ $pb->LinkCode ? 'Link mã nguồn kèm theo báo cáo' : 'Phiên bản này chưa đính kèm link mã nguồn' }}</span>
                                            </div>
                                        </div>
                                        @if($pb->LinkCode)
                                            <a href="{{ $pb->LinkCode }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>Mở mã nguồn</a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- KẾT QUẢ THẨM ĐỊNH AI 3 NHÓM --}}
                            @include('partials.ai_summary_11_sections', ['pb' => $pb])

                            {{-- Ghi chú bổ sung của sinh viên (nếu có nhập riêng) --}}
                            @if(!empty($pb->NoiDungBaoCao) && $pb->NoiDungBaoCao !== $pb->TomTatAI)
                                <div class="accordion mb-3" id="accStudentNote-{{ $pb->LanNop }}">
                                    <div class="accordion-item border rounded-3 overflow-hidden">
                                        <h2 class="accordion-header" id="headingStudent-{{ $pb->LanNop }}">
                                            <button class="accordion-button collapsed py-2 px-3 bg-light fw-bold text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudent-{{ $pb->LanNop }}">
                                                <i class="fa-solid fa-note-sticky text-primary me-2"></i>Ghi Chú Kèm Theo Của Sinh Viên
                                            </button>
                                        </h2>
                                        <div id="collapseStudent-{{ $pb->LanNop }}" class="accordion-collapse collapse">
                                            <div class="accordion-body p-3 small text-dark" style="font-size: 13px; line-height: 1.65;">
                                                {!! \App\Helpers\HtmlHelper::clean($pb->NoiDungBaoCao) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Nhận xét đã có của Giảng viên cho phiên bản này --}}
                            @if($pb->NhanXetGV)
                                <div class="mt-3 p-3 rounded-3 border bg-warning bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small"><i class="fa-solid fa-comment-dots text-warning me-2"></i>Nhận xét của GVHD ({{ $pb->NguoiNhanXet }}):</span>
                                        <span class="text-muted small" style="font-size: 11px;">{{ \Carbon\Carbon::parse($pb->NgayNhanXet)->format('H:i d/m/Y') }}</span>
                                    </div>
                                    <div class="text-dark small">{!! \App\Helpers\HtmlHelper::clean($pb->NhanXetGV) !!}</div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- CỘT PHẢI: KHUNG ĐÁNH GIÁ CHUYÊN MÔN CỦA GIẢNG VIÊN --}}
        <div class="col-12 col-lg-5 min-w-0 max-w-full">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 transition-all min-w-0 max-w-full" style="border: 1px solid #e2e8f0 !important; height: auto !important; max-height: none !important; overflow: visible !important;">
                @php
                    $latestPct = $latestPhienBan->PhanTramHoanThanh ?? ($latestPhienBan->TrangThai === 'Đạt' ? 100 : ($latestPhienBan->TomTatJson['completion_percentage'] ?? ($latestPhienBan->TomTatJson['data']['completion_percentage'] ?? null)));
                    $overallPct = isset($reportMilestoneProgress['percentage']) ? $reportMilestoneProgress['percentage'] : ($nhom->progress_percent ?? 0);
                @endphp
                <div class="d-flex justify-content-between align-items-center gap-2 mb-1 flex-wrap">
                    <h6 class="fw-bold text-dark mb-0 min-w-0 text-truncate" title="Đánh Giá & Nhận Xét Chuyên Môn">
                        <i class="fa-solid fa-pen-clip text-primary me-2 flex-shrink-0"></i>Đánh giá &amp; nhận xét
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                        Tiến độ: {{ $latestPct !== null ? $latestPct . '%' : 'Chưa đánh giá' }}
                    </span>
                </div>
                <span class="teacher-review-target small mb-3 d-block text-break break-words">
                    Đánh giá cho phiên bản nộp mới nhất: <strong>v{{ $latestPhienBan->LanNop ?? 1 }}</strong>
                </span>

                {{-- Thẻ hiển thị tổng quan % hoàn thành --}}
                <div class="p-3 mb-3 rounded-3 bg-light border d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="text-muted small fw-medium d-block">Tiến độ phiên bản v{{ $latestPhienBan->LanNop ?? 1 }}</span>
                        <span class="fs-5 fw-bold text-primary">{{ $latestPct !== null ? $latestPct . '%' : '100% (sau khi duyệt)' }}</span>
                    </div>
                    <div class="text-end border-start ps-3">
                        <span class="text-muted small fw-medium d-block">Tiến độ toàn đề tài</span>
                        <span class="fs-5 fw-bold text-success">{{ number_format($overallPct, 0) }}%</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('giangvien.tiendo.dot_nop.danh_gia', $dotNop->MaDotNop) }}">
                    @csrf
                    <input type="hidden" name="MaKhoi" value="{{ $latestPhienBan->MaKhoi ?? '' }}">

                    {{-- Chọn Hành Động --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark" id="label-eval-group">
                            Kết quả đánh giá đợt nộp <span class="text-danger">*</span>
                        </label>
                        <div class="eval-btn-group d-flex flex-wrap flex-sm-nowrap gap-2" role="radiogroup" aria-labelledby="label-eval-group">
                            <input type="radio" class="btn-check eval-radio-input" name="HanhDong" id="actionDat" value="DAT" {{ ($latestPhienBan->TrangThai ?? '') === 'Đạt' ? 'checked' : '' }} required>
                            <label class="btn eval-btn eval-btn-pass w-50 rounded-3 py-2 fw-bold small" for="actionDat" id="label-actionDat" role="radio" aria-checked="{{ ($latestPhienBan->TrangThai ?? '') === 'Đạt' ? 'true' : 'false' }}" tabindex="0">
                                <i class="fa-solid fa-check-circle me-2"></i>Xác nhận Đạt
                            </label>

                            <input type="radio" class="btn-check eval-radio-input" name="HanhDong" id="actionNopLai" value="YEU_CAU_NOP_LAI" {{ ($latestPhienBan->TrangThai ?? '') === 'Yêu cầu nộp lại' ? 'checked' : '' }}>
                            <label class="btn eval-btn eval-btn-redo w-50 rounded-3 py-2 fw-bold small" for="actionNopLai" id="label-actionNopLai" role="radio" aria-checked="{{ ($latestPhienBan->TrangThai ?? '') === 'Yêu cầu nộp lại' ? 'true' : 'false' }}" tabindex="0">
                                <i class="fa-solid fa-rotate-left me-2"></i>Yêu cầu nộp lại
                            </label>
                        </div>
                        <div class="form-text small mt-2" style="font-size: 11px;">
                            • <strong>Xác nhận Đạt:</strong> Đợt nộp được hoàn thành.<br>
                            • <strong>Yêu cầu nộp lại:</strong> Sinh viên sẽ tạo phiên bản mới (v{{ ($latestPhienBan->LanNop ?? 1) + 1 }}) theo nhận xét của bạn.
                        </div>
                    </div>

                    {{-- % Hoàn thành đợt nộp --}}
                    <div class="mb-3" id="groupPhanTramHoanThanh" style="{{ ($latestPhienBan->TrangThai ?? '') === 'Đạt' ? '' : 'display: none;' }}">
                        <label class="form-label fw-bold small text-dark" for="inputPhanTram">
                            Tiến độ hoàn thành phiên bản (%):
                        </label>
                        <div class="input-group input-group-sm" style="max-width: 160px;">
                            <input type="number" name="PhanTramHoanThanh" id="inputPhanTram" class="form-control fw-bold text-primary" 
                                   value="{{ old('PhanTramHoanThanh', $latestPhienBan->PhanTramHoanThanh ?? 100) }}" min="1" max="100">
                            <span class="input-group-text bg-light fw-bold">%</span>
                        </div>
                        <div class="form-text small mt-1" style="font-size: 11px;">Mặc định 100% khi xác nhận Đạt đợt nộp này.</div>
                    </div>

                    {{-- Nhận xét chuyên môn --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Nội dung nhận xét chuyên môn <span class="text-danger">*</span></label>
                        <textarea name="NhanXet" class="form-control" rows="5" placeholder="Nhập nhận xét chi tiết, góp ý các phần cần cải thiện hoặc xác nhận hoàn thành..." required>{{ $latestPhienBan->NhanXetGV ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-paper-plane me-2"></i>Lưu đánh giá phiên bản
                    </button>
                </form>
            </div>
        </div>

    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       GIAI ĐOẠN 1: TÙY BIẾN CẶP NÚT ĐÁNH GIÁ CHUYÊN MÔN
       (GỌN GÀNG, ĐÚNG MÀU, HOÀN TOÀN KHÔNG TỎA LIGHTING)
       ═══════════════════════════════════════════════════════════ */
    .eval-btn-group {
        position: relative;
    }

    .eval-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.18s ease-in-out !important;
        cursor: pointer !important;
        position: relative;
        user-select: none;
        min-height: 44px;
        font-weight: 600 !important;
        border-radius: 8px !important;
        box-shadow: none !important; /* Không tỏa lighting */
    }

    /* ══════════════════════════════════════════════════════════════
       A. NÚT "XÁC NHẬN ĐẠT" (TÔNG XANH LÁ)
       ══════════════════════════════════════════════════════════════ */
    /* 1. Mặc định / Khi chưa được chọn: Nền xanh nhạt, viền nét liền, chữ xanh, KHÔNG tỏa lighting */
    .eval-btn-pass,
    .eval-btn-group:has(#actionNopLai:checked) .eval-btn-pass:not(:hover),
    .eval-btn-pass.is-unselected:not(:hover) {
        background-color: #ecfdf5 !important;
        border: 1.5px solid #10b981 !important;
        color: #059669 !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        opacity: 1 !important;
        transform: none !important;
    }
    .eval-btn-pass i {
        color: #059669;
        transition: color 0.18s ease;
    }

    /* 2. Khi ĐƯỢC CHỌN (Active / Selected): Nền xanh đậm, viền đậm, chữ trắng, bóng phẳng nhẹ */
    #actionDat:checked + .eval-btn-pass,
    .eval-btn-pass.is-selected {
        background-color: #10b981 !important;
        border: 1.8px solid #059669 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
        transform: none !important;
        opacity: 1 !important;
    }
    #actionDat:checked + .eval-btn-pass i,
    .eval-btn-pass.is-selected i {
        color: #ffffff !important;
    }

    /* 3. Khi TRỎ CHUỘT VÀO (HOVER): Đậm màu hơn, nâng nhẹ, KHÔNG tỏa lighting */
    .eval-btn-pass:hover,
    #actionDat:checked + .eval-btn-pass:hover,
    .eval-btn-pass.is-selected:hover {
        background-color: #059669 !important;
        border-color: #047857 !important;
        color: #ffffff !important;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12) !important;
        transform: translateY(-1px) !important;
    }
    .eval-btn-pass:hover i,
    #actionDat:checked + .eval-btn-pass:hover i,
    .eval-btn-pass.is-selected:hover i {
        color: #ffffff !important;
    }

    /* ══════════════════════════════════════════════════════════════
       B. NÚT "YÊU CẦU NỘP LẠI" (TÔNG VÀNG CAM)
       ══════════════════════════════════════════════════════════════ */
    /* 1. Mặc định / Khi chưa được chọn: Nền cam nhạt (KHÔNG BỊ TRẮNG), viền nét liền, chữ cam, KHÔNG tỏa lighting */
    .eval-btn-redo,
    .eval-btn-group:has(#actionDat:checked) .eval-btn-redo:not(:hover),
    .eval-btn-redo.is-unselected:not(:hover) {
        background-color: #fffbeb !important;
        border: 1.5px solid #f59e0b !important;
        color: #d97706 !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        opacity: 1 !important;
        transform: none !important;
    }
    .eval-btn-redo i {
        color: #d97706;
        transition: color 0.18s ease;
    }

    /* 2. Khi ĐƯỢC CHỌN (Active / Selected): Nền cam đậm, viền đậm, chữ trắng, bóng phẳng nhẹ */
    #actionNopLai:checked + .eval-btn-redo,
    .eval-btn-redo.is-selected {
        background-color: #f59e0b !important;
        border: 1.8px solid #d97706 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
        transform: none !important;
        opacity: 1 !important;
    }
    #actionNopLai:checked + .eval-btn-redo i,
    .eval-btn-redo.is-selected i {
        color: #ffffff !important;
    }

    /* 3. Khi TRỎ CHUỘT VÀO (HOVER): Đậm màu hơn, nâng nhẹ, KHÔNG tỏa lighting */
    .eval-btn-redo:hover,
    #actionNopLai:checked + .eval-btn-redo:hover,
    .eval-btn-redo.is-selected:hover {
        background-color: #d97706 !important;
        border-color: #b45309 !important;
        color: #ffffff !important;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12) !important;
        transform: translateY(-1px) !important;
    }
    .eval-btn-redo:hover i,
    #actionNopLai:checked + .eval-btn-redo:hover i,
    .eval-btn-redo.is-selected:hover i {
        color: #ffffff !important;
    }

    /* ══════════════════════════════════════════════════════════════
       C. ACCESSIBILITY & DISABLED
       ══════════════════════════════════════════════════════════════ */
    .eval-radio-input:focus-visible + .eval-btn,
    .eval-btn:focus-visible {
        outline: 2px solid #0284c7 !important;
        outline-offset: 2px !important;
    }

    .eval-radio-input:disabled + .eval-btn,
    .eval-btn.disabled,
    fieldset:disabled .eval-btn {
        opacity: 0.4 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        box-shadow: none !important;
        transform: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radioDat = document.getElementById('actionDat');
    const radioNopLai = document.getElementById('actionNopLai');
    const labelDat = document.getElementById('label-actionDat');
    const labelNopLai = document.getElementById('label-actionNopLai');

    if (!radioDat || !radioNopLai || !labelDat || !labelNopLai) return;

    function syncRadioVisuals() {
        const phanTramGroup = document.getElementById('groupPhanTramHoanThanh');
        if (radioDat.checked) {
            labelDat.classList.add('is-selected');
            labelDat.classList.remove('is-unselected');
            labelDat.setAttribute('aria-checked', 'true');

            labelNopLai.classList.remove('is-selected');
            labelNopLai.classList.add('is-unselected');
            labelNopLai.setAttribute('aria-checked', 'false');

            if (phanTramGroup) phanTramGroup.style.display = 'block';
        } else if (radioNopLai.checked) {
            labelNopLai.classList.add('is-selected');
            labelNopLai.classList.remove('is-unselected');
            labelNopLai.setAttribute('aria-checked', 'true');

            labelDat.classList.remove('is-selected');
            labelDat.classList.add('is-unselected');
            labelDat.setAttribute('aria-checked', 'false');

            if (phanTramGroup) phanTramGroup.style.display = 'none';
        } else {
            labelDat.classList.remove('is-selected');
            labelDat.classList.remove('is-unselected');
            labelDat.setAttribute('aria-checked', 'false');

            labelNopLai.classList.remove('is-selected');
            labelNopLai.classList.remove('is-unselected');
            labelNopLai.setAttribute('aria-checked', 'false');
        }
    }

    // Lắng nghe sự kiện change từ bàn phím hoặc click
    radioDat.addEventListener('change', syncRadioVisuals);
    radioNopLai.addEventListener('change', syncRadioVisuals);

    // Hỗ trợ accessibility khi focus trực tiếp vào label bằng phím Space / Enter
    [labelDat, labelNopLai].forEach(label => {
        label.addEventListener('keydown', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                const targetInput = document.getElementById(this.getAttribute('for'));
                if (targetInput && !targetInput.disabled) {
                    targetInput.checked = true;
                    targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                    targetInput.focus();
                }
            }
        });
    });

    // Đồng bộ ban đầu khi load trang
    syncRadioVisuals();
});
</script>
@endpush
