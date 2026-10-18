{{-- 
    Partial: Báo Cáo Phân Tích & Tóm Tắt Tự Động Bằng AI Gồm 11 Phần Chuyên Nghiệp
    Công nghệ: Gemini LLM & Map-Reduce Pipeline
    Sử dụng đồng bộ cho cả Sinh Viên và Giảng Viên Hướng Dẫn
--}}
@php
    $aiJson = $pb->TomTatJson ?? [];
    if (is_string($aiJson)) {
        $aiJson = json_decode($aiJson, true) ?? [];
    }
    // Unwrap if nested in 'data'
    if (isset($aiJson['data']) && is_array($aiJson['data'])) {
        $aiData = $aiJson['data'];
    } else {
        $aiData = $aiJson;
    }

    $overallSummary = $aiData['overall_summary'] ?? ($pb->TomTatAI ?? 'Báo cáo tiến độ đã được hệ thống AI trích xuất và tổng hợp đa tầng.');
    $completionPct = (int)($aiData['completion_percentage'] ?? ($pb->PhanTramHoanThanh ?? 70));
    $completedWork = $aiData['completed_work'] ?? [];
    $deliverables = $aiData['deliverables'] ?? [];
    $techLinks = $aiData['technical_links'] ?? [];
    $techStack = $aiData['tech_stack'] ?? [];
    $difficulties = $aiData['difficulties'] ?? [];
    $pendingItems = $aiData['pending_items'] ?? [];
    $nextSteps = $aiData['next_steps'] ?? [];
    $missingInfo = $aiData['missing_information'] ?? [];
    $citations = $aiData['citations'] ?? [];
    $aiRecs = $aiData['ai_recommendations'] ?? ($aiJson['canh_bao'] ?? []);
    $confidence = $pb->DoTinCayAI ?? ($aiJson['confidence_score'] ?? 95);
    
    // Đảm bảo LinkCode từ phiên bản được tích hợp vào mục kỹ thuật nếu có
    if (!empty($pb->LinkCode) && !collect($techLinks)->contains('url', $pb->LinkCode)) {
        array_unshift($techLinks, [
            'name' => 'Mã nguồn nộp đợt này (Repository)',
            'url' => $pb->LinkCode,
            'type' => 'repository',
            'source_pages' => [1]
        ]);
    }

    $uniqueId = 'ai_box_' . ($pb->MaKhoi ?? ($pb->MaBaoCao ?? rand(1000, 9999)));
@endphp

<div class="ai-summary-card rounded-4 border bg-white mb-4 shadow-sm overflow-hidden" id="{{ $uniqueId }}" style="border-color: #cbd5e1 !important; border-radius: 14px !important;">
    
    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- 1. HEADER CHUYÊN NGHIỆP: AI INTELLIGENCE SUITE                 --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="p-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #38bdf8 0%, #2563eb 100%);">
                    <i class="fa-solid fa-wand-magic-sparkles text-white fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h6 class="fw-bold mb-0 text-white fs-6">
                            Kết Quả Thẩm Định Tiến Độ Bằng Trợ Lý AI
                        </h6>
                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            <i class="fa-solid fa-microchip me-1"></i>Gemini NLP
                        </span>
                        <span class="badge text-white rounded-pill px-2.5 py-1" style="font-size: 11px; background-color: rgba(34, 197, 94, 0.3) !important; border: 1px solid rgba(34, 197, 94, 0.4);">
                            <i class="fa-solid fa-shield-check me-1"></i>11 Mục Chuẩn
                        </span>
                    </div>
                    <div class="text-light text-opacity-75 small mt-1" style="font-size: 12px;">
                        <i class="fa-regular fa-file-pdf text-danger me-1"></i>Tệp PDF: <strong class="text-white">{{ $pb->TenFile ?? 'baocao_tiendo.pdf' }}</strong>
                        <span class="mx-2 opacity-50">&bull;</span>
                        <i class="fa-solid fa-database text-warning me-1"></i>Đã lưu trữ hệ thống
                    </div>
                </div>
            </div>

            {{-- Thanh công cụ thao tác nhanh --}}
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3.5 py-2 fw-medium d-inline-flex align-items-center" 
                        onclick="copyAiSummaryText('{{ $uniqueId }}')" title="Sao chép toàn bộ tóm tắt vào bộ nhớ tạm">
                    <i class="fa-regular fa-copy me-1.5"></i><span class="copy-label">Sao chép</span>
                </button>
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3.5 py-2 fw-bold text-dark d-inline-flex align-items-center shadow-xs"
                        onclick="toggleAiDetails('{{ $uniqueId }}')">
                    <i class="fa-solid fa-layer-group me-1.5 text-primary"></i><span class="toggle-label">Thu gọn</span>
                </button>
            </div>
        </div>
{{-- METRICS BAR: CÁC CHỈ SỐ NHANH --}}
        <div class="row g-2 mt-4 pt-4 border-top border-white border-opacity-15 text-light small" style="font-size: 12px;">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2.5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100" style="padding-top: 16px !important; padding-bottom: 16px !important;">
                    <i class="fa-solid fa-circle-check text-success fs-5"></i>
                    <div>
                        <div class="text-white-50" style="font-size: 10px; letter-spacing: 0.5px;">ĐỘ TIN CẬY</div>
                        <div class="fw-bold text-white">{{ $confidence }}% Khớp cấu trúc</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2.5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100" style="padding-top: 16px !important; padding-bottom: 16px !important;">
                    <i class="fa-solid fa-bars-progress text-info fs-5"></i>
                    <div>
                        <div class="text-white-50" style="font-size: 10px; letter-spacing: 0.5px;">TIẾN ĐỘ</div>
                        <div class="fw-bold text-info">{{ $completionPct }}% Hoàn thành</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2.5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100" style="padding-top: 16px !important; padding-bottom: 16px !important;">
                    <i class="fa-solid fa-list-check text-warning fs-5"></i>
                    <div>
                        <div class="text-white-50" style="font-size: 10px; letter-spacing: 0.5px;">HẠNG MỤC</div>
                        <div class="fw-bold text-white">{{ count($completedWork) }} công việc chính</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2.5 p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100" style="padding-top: 16px !important; padding-bottom: 16px !important;">
                    <i class="fa-brands fa-github text-light fs-5"></i>
                    <div>
                        <div class="text-white-50" style="font-size: 10px; letter-spacing: 0.5px;">MÃ NGUỒN</div>
                        <div class="fw-bold text-white">{{ count($techLinks) > 0 ? 'Có Repo Git' : 'Chưa gắn' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- 2. NỘI DUNG CHI TIẾT 11 MỤC                                    --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="p-4 ai-details-body" id="{{ $uniqueId }}_body">
        <div class="row g-4">

            {{-- MỤC 1: TỔNG QUAN TIẾN ĐỘ (EXECUTIVE SUMMARY) --}}
            <div class="col-12">
                <div class="p-4 rounded-3 border bg-light position-relative" style="border-left: 5px solid #0284c7 !important; border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 1</span>
                            <i class="fa-solid fa-file-contract text-primary"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Tổng Quan Tiến Độ</span>
                        </span>
                        <span class="badge bg-white text-secondary border rounded-pill px-3 py-1 small shadow-2xs" style="font-size: 11px;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Tự động
                        </span>
                    </div>
                    <p class="text-dark mb-0 ai-text-summary" style="font-size: 13.5px; line-height: 1.7; text-align: justify;">
                        {{ $overallSummary }}
                    </p>
                </div>
            </div>

            {{-- MỤC 2: TIẾN ĐỘ ƯỚC TÍNH & KHỐI LƯỢNG --}}
            <div class="col-12">
                <div class="p-4 rounded-3 border bg-white" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                            <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 2</span>
                            <i class="fa-solid fa-chart-pie text-info"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Tiến Độ Ước Tính &amp; Khối Lượng</span>
                        </span>
                        <span class="fw-bold text-primary fs-5">{{ $completionPct }}%</span>
                    </div>
                    <div class="progress mb-2.5" style="height: 14px; border-radius: 8px; background-color: #f1f5f9; padding: 2px; border: 1px solid #e2e8f0;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ $completionPct }}%; background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%); border-radius: 6px;" 
                             aria-valuenow="{{ $completionPct }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 11.5px;">
                        <span>Khởi đầu (0%)</span>
                        <span class="fw-semibold text-dark">Giai đoạn hiện tại</span>
                        <span>Hoàn thành (100%)</span>
                    </div>
                </div>
            </div>

            {{-- MỤC 3: CÔNG VIỆC ĐÃ HOÀN THÀNH (Đồng bộ chiều cao h-100) --}}
            <div class="col-12 col-lg-6">
                <div class="p-4 rounded-3 border bg-white h-100 d-flex flex-column" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                        <span class="fw-bold text-success small d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 3</span>
                            <i class="fa-solid fa-circle-check"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Công Việc Đã Làm</span>
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ count($completedWork) }} mục
                        </span>
                    </div>
                    @if(!empty($completedWork))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 flex-grow-1" style="font-size: 13px;">
                            @foreach($completedWork as $cw)
                                @php
                                    $cContent = is_array($cw) ? ($cw['content'] ?? '') : (string)$cw;
                                    $cPages = is_array($cw) && !empty($cw['source_pages']) ? (array)$cw['source_pages'] : [];
                                    $cEvidence = is_array($cw) ? ($cw['evidence'] ?? null) : null;
                                @endphp
                                <li class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-start gap-2.5">
                                    <i class="fa-solid fa-check text-success mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="text-dark fw-medium" style="line-height: 1.5;">{{ $cContent }}</div>
                                        @if(!empty($cPages))
                                            <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 mt-1.5 shadow-2xs" style="font-size: 10.5px;">
                                                <i class="fa-regular fa-file-lines me-1"></i>Trang {{ implode(', ', $cPages) }}
                                            </span>
                                        @endif
                                        @if(!empty($cEvidence))
                                            <div class="text-muted small fst-italic mt-1.5" style="font-size: 11.5px; line-height: 1.4;">
                                                "{{ Str::limit($cEvidence, 110) }}"
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-4 bg-light rounded-3 text-center text-muted small fst-italic my-auto">
                            Chưa phát hiện danh mục công việc chi tiết.
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 4: KẾT QUẢ & SẢN PHẨM ĐẠT ĐƯỢC (DELIVERABLES) (Đồng bộ h-100) --}}
            <div class="col-12 col-lg-6">
                <div class="p-4 rounded-3 border bg-white h-100 d-flex flex-column" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                        <span class="fw-bold text-primary small d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 4</span>
                            <i class="fa-solid fa-box-archive"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Sản Phẩm Đầu Ra</span>
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ count($deliverables) }} đầu ra
                        </span>
                    </div>
                    @if(!empty($deliverables))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 flex-grow-1" style="font-size: 13px;">
                            @foreach($deliverables as $deliv)
                                @php
                                    $dContent = is_array($deliv) ? ($deliv['content'] ?? '') : (string)$deliv;
                                    $dPages = is_array($deliv) && !empty($deliv['source_pages']) ? (array)$deliv['source_pages'] : [];
                                @endphp
                                <li class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-start gap-2.5">
                                    <i class="fa-solid fa-cube text-primary mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="text-dark fw-medium" style="line-height: 1.5;">{{ $dContent }}</div>
                                        @if(!empty($dPages))
                                            <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 mt-1.5 shadow-2xs" style="font-size: 10.5px;">
                                                <i class="fa-regular fa-file-lines me-1"></i>Trang {{ implode(', ', $dPages) }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-4 bg-light rounded-3 text-center text-muted small fst-italic my-auto">
                            Chưa có sản phẩm nghiệm thu độc lập được liệt kê.
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 5: MÃ NGUỒN & TECH STACK --}}
            <div class="col-12">
                <div class="p-4 rounded-3 border bg-light" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                            <span class="badge bg-dark text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 5</span>
                            <i class="fa-solid fa-code-branch text-dark"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Mã Nguồn &amp; Kỹ Thuật (Tech Stack)</span>
                        </span>
                        @if(!empty($techStack))
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @foreach($techStack as $t)
                                    <span class="badge bg-white text-dark border shadow-2xs rounded-pill px-3 py-1.5" style="font-size: 11.5px;">
                                        <i class="fa-solid fa-code me-1 text-primary"></i>{{ $t }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if(!empty($techLinks))
                        <div class="row g-3">
                            @foreach($techLinks as $link)
                                <div class="col-12 col-md-6">
                                    <div class="p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between gap-3 shadow-2xs">
                                        <div class="d-flex align-items-center gap-2.5 text-truncate me-2">
                                            <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 36px; height: 36px;">
                                                <i class="fa-brands fa-github"></i>
                                            </div>
                                            <div class="text-truncate">
                                                <div class="fw-bold text-dark small text-truncate">{{ $link['name'] ?? 'Mã nguồn' }}</div>
                                                <div class="text-muted small text-truncate" style="font-size: 11.5px;">{{ $link['url'] }}</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1.5" 
                                                    onclick="navigator.clipboard.writeText('{{ $link['url'] }}'); alert('Đã sao chép liên kết repository!');" 
                                                    title="Sao chép link">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary text-white rounded-pill px-3.5 py-1.5 fw-medium" style="font-size: 12px;">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Truy cập
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-white border text-muted small fst-italic">
                            <i class="fa-regular fa-circle-question me-1.5 text-warning"></i>Báo cáo chưa trích dẫn liên kết repository công khai.
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 6: KHÓ KHĂN & THÁCH THỨC (Đồng bộ chiều cao h-100) --}}
            <div class="col-12 col-md-6">
                <div class="p-4 rounded-3 border bg-white h-100 d-flex flex-column" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                        <span class="fw-bold text-danger small d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 6</span>
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Khó Khăn Gặp Phải</span>
                        </span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ count($difficulties) }} phát hiện
                        </span>
                    </div>
                    @if(!empty($difficulties))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 flex-grow-1" style="font-size: 13px;">
                            @foreach($difficulties as $diff)
                                @php
                                    $dfContent = is_array($diff) ? ($diff['content'] ?? '') : (string)$diff;
                                    $dfPages = is_array($diff) && !empty($diff['source_pages']) ? (array)$diff['source_pages'] : [];
                                @endphp
                                <li class="p-3 rounded-3 bg-danger-subtle bg-opacity-25 border border-danger-subtle d-flex align-items-start gap-2.5">
                                    <i class="fa-solid fa-circle-xmark text-danger mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="text-dark fw-medium" style="line-height: 1.5;">{{ $dfContent }}</div>
                                        @if(!empty($dfPages))
                                            <span class="badge bg-white text-danger border border-danger-subtle rounded-pill px-2.5 py-1 mt-1.5 shadow-2xs" style="font-size: 10.5px;">
                                                Trang {{ implode(', ', $dfPages) }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-4 bg-success-subtle bg-opacity-25 rounded-3 text-success small d-flex align-items-center gap-2.5 my-auto">
                            <i class="fa-solid fa-circle-check fs-5 flex-shrink-0"></i>
                            <span style="line-height: 1.5;">Tiến độ ổn định, không ghi nhận rào cản kỹ thuật nghiêm trọng.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 7: CÔNG VIỆC TỒN ĐỌNG (PENDING) (Đồng bộ chiều cao h-100) --}}
            <div class="col-12 col-md-6">
                <div class="p-4 rounded-3 border bg-white h-100 d-flex flex-column" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                        <span class="fw-bold text-warning-emphasis small d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 7</span>
                            <i class="fa-solid fa-hourglass-half"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Hạng Mục Tồn Đọng</span>
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ count($pendingItems) }} mục
                        </span>
                    </div>
                    @if(!empty($pendingItems))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 flex-grow-1" style="font-size: 13px;">
                            @foreach($pendingItems as $pend)
                                @php
                                    $pdContent = is_array($pend) ? ($pend['content'] ?? '') : (string)$pend;
                                    $pdPages = is_array($pend) && !empty($pend['source_pages']) ? (array)$pend['source_pages'] : [];
                                @endphp
                                <li class="p-3 rounded-3 bg-warning-subtle bg-opacity-25 border border-warning-subtle d-flex align-items-start gap-2.5">
                                    <i class="fa-solid fa-clock text-warning-emphasis mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="text-dark fw-medium" style="line-height: 1.5;">{{ $pdContent }}</div>
                                        @if(!empty($pdPages))
                                            <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 mt-1.5 shadow-2xs" style="font-size: 10.5px;">
                                                Trang {{ implode(', ', $pdPages) }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-4 bg-light rounded-3 text-center text-muted small fst-italic my-auto">
                            Tất cả công việc đã đề ra cho giai đoạn này đều đã hoàn tất.
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 8: KẾ HOẠCH GIAI ĐOẠN TIẾP THEO (NEXT STEPS) (Đồng bộ h-100) --}}
            <div class="col-12 col-md-6">
                <div class="p-4 rounded-3 border bg-white h-100 d-flex flex-column" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                        <span class="fw-bold text-info-emphasis small d-flex align-items-center gap-2">
                            <span class="badge bg-info text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 8</span>
                            <i class="fa-solid fa-calendar-check"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Kế Hoạch Tiếp Theo</span>
                        </span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ count($nextSteps) }} định hướng
                        </span>
                    </div>
                    @if(!empty($nextSteps))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 flex-grow-1" style="font-size: 13px;">
                            @foreach($nextSteps as $ns)
                                @php
                                    $nsContent = is_array($ns) ? ($ns['content'] ?? '') : (string)$ns;
                                    $nsPages = is_array($ns) && !empty($ns['source_pages']) ? (array)$ns['source_pages'] : [];
                                @endphp
                                <li class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-start gap-2.5">
                                    <i class="fa-solid fa-arrow-right text-info mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="text-dark fw-medium" style="line-height: 1.5;">{{ $nsContent }}</div>
                                        @if(!empty($nsPages))
                                            <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 mt-1.5 shadow-2xs" style="font-size: 10.5px;">
                                                Trang {{ implode(', ', $nsPages) }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-4 bg-light rounded-3 text-center text-muted small fst-italic my-auto">
                            Tiếp tục thực hiện theo lộ trình giai đoạn kế tiếp do GVHD phân công.
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 9: NỘI DUNG CẦN LÀM RÕ (AMBIGUITIES) (Đồng bộ h-100) --}}
            <div class="col-12 col-md-6">
                <div class="p-4 rounded-3 border bg-white h-100 d-flex flex-column" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom">
                        <span class="fw-bold text-secondary small d-flex align-items-center gap-2">
                            <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 9</span>
                            <i class="fa-solid fa-clipboard-question"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Điểm Cần Làm Rõ</span>
                        </span>
                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ count($missingInfo) }} lưu ý
                        </span>
                    </div>
                    @if(!empty($missingInfo))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2.5 flex-grow-1" style="font-size: 13px;">
                            @foreach($missingInfo as $mi)
                                <li class="p-3 rounded-3 bg-light border border-light-subtle d-flex align-items-start gap-2.5">
                                    <i class="fa-solid fa-circle-exclamation text-warning mt-1 flex-shrink-0" style="font-size: 13px;"></i>
                                    <div class="text-dark" style="line-height: 1.5;">{{ $mi }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-4 bg-success-subtle bg-opacity-25 rounded-3 text-success small d-flex align-items-center gap-2.5 my-auto">
                            <i class="fa-solid fa-check-double fs-5 flex-shrink-0"></i>
                            <span style="line-height: 1.5;">Báo cáo trình bày mạch lạc, cấu trúc thông tin hoàn chỉnh.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 10: TRÍCH DẪN & CHỨNG CỨ SỐ TRANG (CITATIONS) --}}
            <div class="col-12">
                <div class="p-4 rounded-3 border bg-light" style="border-color: #cbd5e1; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3.5">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 10</span>
                            <i class="fa-solid fa-bookmark text-primary"></i>
                            <span class="text-uppercase" style="letter-spacing: 0.5px;">Trích Dẫn &amp; Số Trang</span>
                        </span>
                        <span class="badge bg-white text-secondary border rounded-pill px-3 py-1 small shadow-2xs" style="font-size: 11px;">
                            Đối chiếu minh bạch
                        </span>
                    </div>
                    @if(!empty($citations))
                        <div class="row g-3">
                            @foreach($citations as $cite)
                                <div class="col-12">
                                    <div class="p-3 rounded-3 border bg-white shadow-2xs" style="font-size: 13px; border-left: 4px solid #0284c7 !important; border-color: #e2e8f0;">
                                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold shadow-xs" style="font-size: 11px;">
                                                <i class="fa-regular fa-file-lines me-1"></i>Trang {{ $cite['page'] ?? 1 }}
                                            </span>
                                            <span class="text-primary-emphasis fw-bold small">
                                                <i class="fa-solid fa-tag me-1 text-primary"></i>{{ $cite['topic'] ?? 'Nội dung chứng thực' }}
                                            </span>
                                        </div>
                                        <div class="text-dark fst-italic p-2.5 rounded-2 bg-light border border-light-subtle" style="line-height: 1.65; word-break: break-word; white-space: normal;">
                                            <i class="fa-solid fa-quote-left text-muted me-1.5 opacity-50"></i>{{ $cite['snippet'] ?? '' }}<i class="fa-solid fa-quote-right text-muted ms-1.5 opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-white border text-muted small fst-italic">
                            Chứng cứ được tham chiếu tương ứng theo cấu trúc các chương mục trong tài liệu PDF nộp.
                        </div>
                    @endif
                </div>
            </div>

            {{-- MỤC 11: KHUYẾN NGHỊ & CẢNH BÁO TỪ TRỢ LÝ AI (AI ADVICE) --}}
            <div class="col-12">
                <div class="p-4 rounded-3 border" style="background-color: #f0fdf4 !important; border-color: #86efac !important; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold text-success-emphasis small d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 10.5px;">Mục 11</span>
                            <i class="fa-solid fa-lightbulb text-warning fs-5"></i>
                            <span class="text-uppercase fw-bold text-dark" style="letter-spacing: 0.5px;">Khuyến Nghị &amp; Cảnh Báo Từ Trợ Lý AI</span>
                        </span>
                        <span class="badge bg-success text-white rounded-pill px-3 py-1 shadow-xs" style="font-size: 11px;">Tư vấn chuyên sâu</span>
                    </div>
                    @if(!empty($aiRecs))
                        <ul class="mb-0 ps-3 text-dark d-flex flex-column gap-2" style="font-size: 13px; line-height: 1.65;">
                            @foreach($aiRecs as $rec)
                                <li><strong>Lưu ý:</strong> {{ $rec }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-dark small d-flex align-items-center gap-2.5" style="font-size: 13.5px; line-height: 1.5;">
                            <i class="fa-solid fa-circle-check text-success fs-5 flex-shrink-0"></i>
                            <span>Nhóm sinh viên đang bám rất sát kế hoạch tiến độ. GVHD có thể an tâm xác nhận hoàn thành đợt này.</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- CSS TÙY CHỈNH HIỆU ỨNG & CHỐNG LỆCH KHỐI --}}
<style>
    .transition-all {
        transition: all 0.25s ease-in-out;
    }
    .shadow-2xs {
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
    }
</style>

{{-- SCRIPT HỖ TRỢ THAO TÁC TRÊN CARD AI --}}
<script>
if (typeof copyAiSummaryText !== 'function') {
    window.copyAiSummaryText = function(cardId) {
        const card = document.getElementById(cardId);
        if (!card) return;
        const text = card.querySelector('.ai-text-summary')?.innerText || card.innerText;
        navigator.clipboard.writeText(text).then(() => {
            const btn = card.querySelector('.copy-label');
            if (btn) {
                const old = btn.innerText;
                btn.innerText = 'Đã chép!';
                setTimeout(() => btn.innerText = old, 2000);
            }
        });
    };
}

if (typeof toggleAiDetails !== 'function') {
    window.toggleAiDetails = function(cardId) {
        const body = document.getElementById(cardId + '_body');
        const card = document.getElementById(cardId);
        if (!body) return;
        const btn = card.querySelector('.toggle-label');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (btn) btn.innerText = 'Thu gọn';
        } else {
            body.style.display = 'none';
            if (btn) btn.innerText = 'Mở rộng';
        }
    };
}
</script>