@php
    $cancelUrl = $cancelUrl ?? ($citationAvailable ? route('baocao.ai.dung_phan_tich', $pb->MaKhoi) : '');
    $reportTopic = $deTai ?? ($reviewRound?->deTai ?? ($nhom->deTai ?? null));
    $reportAdvisor = $giangVien ?? $reportTopic?->giangVien;
    $uiMetrics = \App\Support\AiReportPresentation::metrics($aiData);
    $overviewChapters = $aiData['chapters'] ?? [];
    $summarizedChapters = array_values(array_filter($overviewChapters, static fn (array $chapter) =>
        !empty($chapter['chapter_reading']['paragraphs'])
    ));
    $overviewHasMissingChapters = ($analysis['engine'] ?? '') === 'pdf_lab'
        && count($summarizedChapters) < count($overviewChapters);
    $reportPhase = $reviewRound?->giaiDoan?->TenGiaiDoan ?? ($aiData['requirements_document']['period'] ?? 'Chưa có thông tin');
    $reportRound = $reviewRound?->TenDotNop ?? ($aiData['requirements_document']['title'] ?? 'Chưa có thông tin');
    $generatedAt = null;
    try { if(!empty($analysis['generated_at'])) $generatedAt=\Carbon\Carbon::parse($analysis['generated_at'])->timezone(config('app.timezone'))->format('H:i d/m/Y').' ('.config('app.timezone').')'; } catch(\Throwable $e) {}
@endphp
<section class="ai-summary ai-summary-card ai-teacher-report" id="{{ $uniqueId }}" aria-labelledby="{{ $uniqueId }}-title" data-teacher-ui="1" data-showing-saved-summary="{{ $showingSavedSummary ? '1' : '0' }}" data-report-key="{{ $pb->MaKhoi ?? '' }}" data-document-type="{{ $documentType }}" data-citation-url="{{ $citationUrl }}" data-cancel-url="{{ $cancelUrl }}" data-pdf-url="{{ $pdfUrl }}" data-csrf-token="{{ $citationAvailable ? csrf_token() : '' }}">
    <header class="ai-header">
        <div class="ai-heading"><div class="ai-brand-icon" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></div><div>
            <h2 class="ai-title" id="{{ $uniqueId }}-title">Kết quả phân tích báo cáo AI</h2>
            <div class="ai-file">{{ $pb->TenFile ?? 'Báo cáo tiến độ' }} · Phiên bản {{ $pb->LanNop ?? 1 }}</div>
            <div class="ai-file" data-summary-metadata>{{ $showingSavedSummary ? 'Đang hiển thị bản đã lưu' : ($analysisComplete ? 'Đã có kết quả phân tích' : 'Chưa hoàn tất phân tích') }}</div>
        </div></div>
        <div class="ai-actions">
            @if($cancelUrl)
                <form method="POST" action="{{ $cancelUrl }}" data-teacher-cancel-form style="margin:0; display:{{ in_array($aiData['_processing']['status'] ?? '', ['queued','running','waiting']) ? 'inline-block' : 'none' }};">
                    @csrf
                    <button type="submit" class="ai-button ai-button-cancel" data-ai-cancel-submit style="color:#dc3545; border-color:#dc3545; background:#fff;">
                        <i class="fa-solid fa-stop" aria-hidden="true"></i>Dừng phân tích
                    </button>
                </form>
            @endif
            @if($reanalyzeUrl)
                <form method="POST" action="{{ $reanalyzeUrl }}" data-teacher-analyze-form style="margin:0; display:inline-block;">
                    @csrf
                    <button type="submit" class="ai-button" data-ai-review-submit title="Phân tích lại báo cáo này">
                        <i class="fa-solid fa-rotate" aria-hidden="true"></i>Phân tích lại
                    </button>
                </form>
            @endif
            <button type="button" class="ai-button ai-button-primary" data-ai-action="open-large" aria-haspopup="dialog"><i class="fa-solid fa-expand" aria-hidden="true"></i><span>Xem lớn</span></button>
            <button type="button" class="ai-button" data-ai-action="copy-summary"><i class="fa-regular fa-copy" aria-hidden="true"></i><span>Sao chép</span></button>
            <button type="button" class="ai-button" data-ai-action="toggle" aria-expanded="true" aria-controls="{{ $uniqueId }}-body"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i><span class="ai-toggle-label">Thu gọn</span></button>
        </div>
    </header>
    @include('partials.ai_processing_status')
    <p class="ai-feedback" role="status" aria-live="polite"></p>
    <div class="ai-details ai-details-body ai-copy-content" id="{{ $uniqueId }}-body" tabindex="0" role="region" aria-label="Nội dung tóm tắt AI, có thể cuộn để xem đầy đủ">
        @if(in_array($aiData['_processing']['status']??'', ['queued','running','waiting']))
            <p class="ai-empty" data-incremental-results>Đang cập nhật kết quả từng phần. Nội dung đã có dẫn chứng được hiển thị trước; các mục còn lại vẫn đang xử lý.</p>
        @endif
        <dl class="ai-report-context">
            <div><dt>Đề tài</dt><dd>{{ $reportTopic->TenDeTai ?? 'Chưa có thông tin đề tài' }}</dd></div>
            <div><dt>Sinh viên / Nhóm</dt><dd>{{ $nhom->TenNhom ?? $nhom->MaNhom ?? 'Chưa có thông tin nhóm' }}</dd></div>
            <div><dt>Giảng viên hướng dẫn</dt><dd>{{ $reportAdvisor->HoTen ?? 'Chưa có thông tin giảng viên' }}</dd></div>
            <div><dt>Giai đoạn / Đợt nộp</dt><dd>{{ $reportPhase }} · {{ $reportRound }}</dd></div>
            <div><dt>Thời điểm phân tích</dt><dd>{{ $generatedAt ?? 'Chưa có kết quả đã lưu' }}</dd></div>
        </dl>
        <h3 class="ai-subheading">Thông tin kiểm chứng kết quả AI</h3>
        <div class="ai-result-notice ai-result-notice-inline">
            <span>Lưu ý về kết quả AI</span>
            <button type="button" class="ai-info-icon-button" data-ai-open-notice aria-haspopup="dialog" aria-label="Xem lưu ý về kết quả AI" title="Lưu ý về kết quả AI">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            </button>
            <template data-ai-notice-content>
                <p>Nội dung được AI tổng hợp có thể chưa đầy đủ hoặc chưa hoàn toàn chính xác. Giảng viên nên đối chiếu với báo cáo gốc trước khi đưa ra đánh giá.</p>
                @if($pdfUrl)<a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">Xem báo cáo gốc <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>@endif
            </template>
        </div>
        <div class="row g-2 ai-verification-cards">
            <div class="col-12"><div class="card h-100 ai-metric-source"><div class="card-body"><i class="fa-solid fa-check-double" aria-hidden="true"></i><h4>Dẫn chứng khớp nguồn PDF</h4><strong data-verified-count>{{ $uiMetrics['measured'] ? $uiMetrics['matched'].' / '.$uiMetrics['references'] : 'Chưa có dữ liệu kiểm chứng' }}</strong><p>Kiểm tra câu trích với nguồn.</p><details class="ai-metric-help"><summary>Xem giải thích</summary><p>Số câu trích khớp chữ trong các nguồn đang hiển thị; không phải độ chính xác ngữ nghĩa.</p></details></div></div></div>
            <div class="col-12"><div class="card h-100 ai-metric-coverage"><div class="card-body"><i class="fa-regular fa-file-lines" aria-hidden="true"></i><h4>Văn bản đã xử lý</h4><strong data-coverage-count>{{ $uiMetrics['total'] ? $uiMetrics['done'].' / '.$uiMetrics['total'].' phần' : 'Chưa có dữ liệu bao phủ' }}</strong><p>Phạm vi văn bản đã phân tích.</p><details class="ai-metric-help"><summary>Xem giải thích</summary><p>Chỉ tính phần văn bản được chọn phân tích. Chưa bao gồm nội dung ảnh và sơ đồ.</p></details></div></div></div>
            <div class="col-12"><div class="card h-100 ai-metric-review"><div class="card-body"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><h4>Nội dung cần đối chiếu</h4><strong>{{ $uiMetrics['empty_chapters']+$uiMetrics['unlinked_requirements'] }} phần</strong><p>Các mục cần xem thêm nguồn.</p><details class="ai-metric-help"><summary>Xem giải thích</summary><p>{{ $uiMetrics['empty_chapters'] }} chương/phần và {{ $uiMetrics['unlinked_requirements'] }} yêu cầu chưa có dẫn chứng liên kết. Các nhận định còn lại vẫn cần kiểm tra.</p></details></div></div></div>
            <div class="col-12">
                @include('partials.ai_requirement_coverage',['coverageMetric'=>true,'coverageSourceOnly'=>false,'coverageSidebar'=>false,'coverageVersion'=>$pb->LanNop??null,'coverageReportKey'=>$pb->MaKhoi??''])
            </div>
        </div>
        @if(request()->routeIs('sinhvien.baocao.dot_nop.show','sinhvien.baocao.show_dot'))
            @include('partials.ai_requirement_coverage',['coverageMetric'=>false,'coverageSourceOnly'=>true,'coverageSidebar'=>false,'coverageVersion'=>$pb->LanNop??null,'coverageReportKey'=>$pb->MaKhoi??''])
        @endif
        @if($sourceChanged)<p class="alert alert-warning">Tệp báo cáo đã thay đổi. Chọn Phân tích lại để đọc đúng tệp hiện tại.</p>@endif
        <nav class="ai-report-tabs nav nav-tabs" role="tablist" aria-label="Kết quả AI dành cho giảng viên">
            @foreach(['chapters'=>'Tóm tắt', 'evidence'=>'Dẫn chứng', 'diagrams'=>'Sơ đồ và bảng', 'requirements'=>'Yêu cầu của giảng viên', 'assessment'=>'Tiến độ và đánh giá'] as $tabKey=>$tabLabel)
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $uniqueId }}-tab-{{ $tabKey }}" data-bs-toggle="tab" data-bs-target="#{{ $uniqueId }}-pane-{{ $tabKey }}" type="button" role="tab" aria-controls="{{ $uniqueId }}-pane-{{ $tabKey }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-ai-result-tab="{{ $tabKey }}">{{ $tabLabel }}</button>
            @endforeach
        </nav>
        <div class="tab-content ai-report-tab-content">
            <div class="tab-pane fade show active" id="{{ $uniqueId }}-pane-chapters" role="tabpanel" aria-labelledby="{{ $uniqueId }}-tab-chapters" tabindex="0" data-ai-pane="chapters">
                @if(($analysis['engine'] ?? '') === 'pdf_lab' && ($documentContent || $overviewHasMissingChapters))
                    <section class="ai-section ai-section-overview" data-summary-overview>
                        <h3 class="ai-section-title mb-3">Tổng quan báo cáo</h3>
                        @if($documentContent)
                            @foreach($documentContent as $contentItem)
                                <p>{{ $contentItem['content'] ?? '' }}</p>
                            @endforeach
                        @else
                            <p class="ai-muted">Chưa có tổng quan toàn báo cáo. Đã có tóm tắt cho {{ count($summarizedChapters) }}/{{ count($overviewChapters) }} phần. Xem các phần đã có nội dung bên dưới.</p>
                        @endif
                    </section>
                @endif
                <section class="ai-section" data-structural-summary><h3 class="ai-section-title mb-3">Bản đọc theo chương dành cho giảng viên</h3>
                    @forelse($displayChapters ?? ($aiData['chapters'] ?? []) as $chapter)@include('partials.ai_document_node',['node'=>$chapter])@empty<p class="ai-empty">Chưa có bản tóm tắt theo chương. Kết quả đã lưu, nếu có, được giữ ở phần tổng quan.</p>@endforelse
                </section>
            </div>
            <div class="tab-pane fade" id="{{ $uniqueId }}-pane-evidence" role="tabpanel" aria-labelledby="{{ $uniqueId }}-tab-evidence" tabindex="0" data-ai-pane="evidence">
                @include('partials.ai_source_navigation', ['navigationMode'=>'search'])
                @if(empty($aiData['source_navigation']['rows']))
                    <p class="ai-empty">Chưa có chỉ mục dẫn chứng để tra cứu.</p>
                @endif
                @if($documentContent)
                    <details>
                        <summary>Dẫn chứng và các ý có nguồn trong phần tổng quan</summary>
                        @include('partials.ai_summary_section', ['section'=>['key'=>'overview-evidence', 'title'=>'Nguồn đối chiếu', 'icon'=>'fa-file-lines', 'tone'=>'primary', 'items'=>$documentContent]])
                    </details>
                @endif
                <h3 class="ai-section-title mt-3">Dẫn chứng theo chương và mục</h3>
                @forelse($displayChapters ?? ($aiData['chapters'] ?? []) as $chapter)
                    @include('partials.ai_document_evidence', ['node'=>$chapter])
                @empty
                    <p class="ai-muted">Chưa có dẫn chứng được lưu theo chương hoặc mục.</p>
                @endforelse
            </div>
            <div class="tab-pane fade" id="{{ $uniqueId }}-pane-diagrams" role="tabpanel" aria-labelledby="{{ $uniqueId }}-tab-diagrams" tabindex="0" data-ai-pane="diagrams">
                @if(array_filter($aiData['source_navigation']['assets'] ?? [], \App\Support\AiReportPresentation::visibleReportAsset(...)))
                    @include('partials.ai_source_navigation', ['navigationMode'=>'catalog'])
                @else
                    <p class="ai-empty">Chưa nhận diện được sơ đồ hoặc bảng trong phần đã đọc.</p>
                @endif
                @include('partials.ai_unassigned_captions')
            </div>
            <div class="tab-pane fade" id="{{ $uniqueId }}-pane-assessment" role="tabpanel" aria-labelledby="{{ $uniqueId }}-tab-assessment" tabindex="0" data-ai-pane="assessment">
                @include('partials.ai_report_progress')
            </div>
            <div class="tab-pane fade" id="{{ $uniqueId }}-pane-requirements" role="tabpanel" aria-labelledby="{{ $uniqueId }}-tab-requirements" tabindex="0" data-ai-pane="requirements">
                @if(($analysis['engine'] ?? '')==='pdf_lab')@include('partials.ai_lab_requirements')@elseif($reviewRound)@include('partials.ai_requirement_review')@else<p class="ai-empty">Giảng viên chưa thiết lập yêu cầu phân tích cho đợt nộp này.</p>@endif
            </div>
        </div>
    </div>
</section>
@once
<link rel="stylesheet" href="{{ asset('css/ai_teacher_report.css') }}?v={{ filemtime(public_path('css/ai_teacher_report.css')) }}">
<script src="{{ asset('js/ai_teacher_report.js') }}?v={{ filemtime(public_path('js/ai_teacher_report.js')) }}" defer></script>
@include('partials.ai_teacher_progress_modal')
@endonce



