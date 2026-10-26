{{-- Tóm tắt AI dùng chung cho Sinh viên và Giảng viên, theo giao diện HUIT admin. --}}
@php
    $aiJson = $pb->TomTatJson ?? ($pb->tomTat->TomTatJson ?? []);
    if (is_string($aiJson)) {
        $aiJson = json_decode($aiJson, true) ?? [];
    }
    $aiData = isset($aiJson['data']) && is_array($aiJson['data']) ? $aiJson['data'] : $aiJson;
    $overallSummary = $aiData['overall_summary'] ?? $aiData['tom_tat'] ?? ($pb->TomTatAI ?? 'Chưa có nội dung tóm tắt.');
    $documentContent = $aiData['document_content'] ?? [];
    $completedWork = $aiData['completed_work'] ?? $aiData['cong_viec_hoan_thanh'] ?? [];
    $difficulties = $aiData['difficulties'] ?? $aiData['kho_khan'] ?? [];
    $nextSteps = $aiData['next_steps'] ?? $aiData['ke_hoach_tiep_theo'] ?? [];
    $analysis = $aiData['analysis'] ?? [];
    $reviewRound = $dotNop ?? ($pb->dotNop ?? null);
    $requirementsReview = $aiData['requirements_review'] ?? null;
    $flexibleSummary = (int) ($aiData['schema_version'] ?? 0) >= 5 && is_array($aiData['summary_sections'] ?? null);
    $summaryScope = $aiData['summary_scope'] ?? [];
    $additionalSections = $flexibleSummary ? ($aiData['additional_sections'] ?? []) : [];
    $summaryStale = $flexibleSummary && $reviewRound && ($summaryScope['context_hash'] ?? '') !== $reviewRound->maNguCanhDoiChieuAi();
    $citationAvailable = !empty($pb->MaKhoi) && !empty($pb->DuongDanFile);
    $citationUrl = $citationAvailable ? route('baocao.ai.trich_dan', $pb->MaKhoi) : '';
    $pdfUrl = $citationAvailable ? route('baocao.ai.pdf', $pb->MaKhoi) : '';
    $reviewUrl = $citationAvailable && $reviewRound ? route('baocao.ai.doi_chieu', $pb->MaKhoi) : '';
    $reanalyzeUrl = $citationAvailable ? route('baocao.ai.phan_tich_lai', $pb->MaKhoi) : '';
    $uniqueId = 'ai-summary-' . uniqid();
    $documentType = strtolower(pathinfo($pb->DuongDanFile ?? '', PATHINFO_EXTENSION)) === 'docx' ? 'docx' : ($aiData['document_type'] ?? 'pdf');
    $sourceLabels = $analysis['source_labels'] ?? [];
    $analysisComplete = ($analysis['status'] ?? '') === 'completed';
    if (!$analysisComplete && !$documentContent && isset($analysis['analyzed_chunks'], $analysis['total_chunks']) && $analysis['analyzed_chunks'] < $analysis['total_chunks']) {
        $overallSummary = 'Bản tóm tắt chưa hoàn tất. Các thông tin đã trích xuất được giữ bên dưới; chưa thể kết luận những nội dung còn lại không có trong báo cáo.';
    }
    $sections = [
        ['key' => 'completed_work', 'number' => 1, 'title' => 'Công việc đã thực hiện', 'icon' => 'fa-circle-check', 'tone' => 'success', 'items' => $completedWork],
        ['key' => 'difficulties', 'number' => 2, 'title' => 'Khó khăn gặp phải', 'icon' => 'fa-triangle-exclamation', 'tone' => 'danger', 'items' => $difficulties],
        ['key' => 'next_steps', 'number' => 3, 'title' => 'Kế hoạch tiếp theo', 'icon' => 'fa-calendar-check', 'tone' => 'primary', 'items' => $nextSteps],
    ];
    $aiEvaluation = $aiData['completion_percentage'] ?? ($pb->PhanTramHoanThanh ?? null);
    $aiBasis = $aiData['completion_basis'] ?? [];
    $versionComparison = $aiData['version_comparison'] ?? null;
    $hasVersionComparison = !empty($versionComparison['has_previous']);
    $lanNop = $pb->LanNop ?? 1;
    $isOverdue = false;
    if ($reviewRound && $reviewRound->HanNop && $pb->NgayNop) {
        $isOverdue = \Carbon\Carbon::parse($pb->NgayNop)->gt($reviewRound->HanNop);
    }
    if ($flexibleSummary) {
        $sections = array_map(fn ($section) => array_merge($section, [
            'key' => $section['id'] ?? 'topic', 'icon' => 'fa-file-lines', 'tone' => 'primary',
        ]), $aiData['summary_sections']);
        $additionalSections = array_map(fn ($section) => array_merge($section, [
            'key' => $section['id'] ?? 'additional', 'icon' => 'fa-file-lines', 'tone' => 'primary',
        ]), $additionalSections);
    }
    $missingGroups = [];
    foreach ($sections as &$summarySection) {
        $summarySection['items'] = array_values(array_filter((array) $summarySection['items'], fn ($item) => trim(is_array($item) ? (string) ($item['content'] ?? '') : (string) $item) !== ''));
        if (!$flexibleSummary && !$summarySection['items']) $missingGroups[] = mb_strtolower($summarySection['title']);
    }
    unset($summarySection);

@endphp

@once
<style>
.ai-summary {
    --ai-blue: var(--huit-blue, #0072ce); --ai-navy: var(--huit-navy, #004a8f);
    --ai-ink: var(--text-dark, #1a2b4a); --ai-muted: #64748b; --ai-border: #e2e8f0; --ai-tint: #f0f7ff;
    color: var(--ai-ink); background: #fff; border: 1px solid var(--ai-border); border-radius: 8px;
    margin-bottom: 20px; font-size: 13.5px; line-height: 1.65; overflow: hidden; min-width: 0; container-type: inline-size;
}
.ai-summary *, .ai-summary *::before, .ai-summary *::after { box-sizing: border-box; }
.ai-summary .ai-header { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 20px; border-bottom: 1px solid var(--ai-border); background: #fff; }
.ai-summary .ai-heading { display: flex; align-items: flex-start; gap: 12px; min-width: 0; flex: 1 1 280px; }
.ai-summary .ai-brand-icon { display: grid; place-items: center; width: 40px; height: 40px; flex: 0 0 40px; color: var(--ai-blue); background: #e5f0fa; border-radius: 8px; font-size: 17px; }
.ai-summary .ai-title { margin: 0 0 4px; color: var(--ai-navy); font-size: 16px; line-height: 1.45; font-weight: 700; }
.ai-summary .ai-file { display: flex; align-items: baseline; gap: 6px; color: var(--ai-muted); font-size: 13px; overflow-wrap: anywhere; }
.ai-summary .ai-file i { color: var(--ai-blue); flex-shrink: 0; }
.ai-summary .ai-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.ai-summary .ai-button {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 34px; padding: 6px 12px;
    border: 1px solid #c8dff7; border-radius: 6px; background: #fff; color: var(--ai-navy); font-family: inherit;
    font-size: 13px; font-weight: 600; line-height: 1.4; text-decoration: none; cursor: pointer;
    transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.ai-summary .ai-button:hover { background: var(--ai-tint); border-color: var(--ai-blue); }
.ai-summary .ai-button-primary { background: var(--ai-blue); border-color: var(--ai-blue); color: #fff; }
.ai-summary .ai-button-primary:hover { background: var(--ai-navy); border-color: var(--ai-navy); color: #fff; }
.ai-summary .ai-button:focus-visible, .ai-summary .ai-details:focus-visible { outline: 3px solid #a2d2ff; outline-offset: -3px; }
.ai-summary .ai-button:disabled { opacity: .65; cursor: wait; }
.ai-summary .ai-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--ai-border); }
.ai-summary .ai-stat { display: flex; gap: 10px; align-items: center; min-width: 0; padding: 12px; border: 1px solid var(--ai-border); border-radius: 6px; background: #fff; }
.ai-summary .ai-stat > i { color: var(--ai-blue); font-size: 16px; }
.ai-summary .ai-stat-label { display: block; font-size: 12px; color: var(--ai-muted); margin-bottom: 2px; }
.ai-summary .ai-stat-value { display: block; font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--ai-navy); overflow-wrap: anywhere; }
.ai-summary .ai-details {
    padding: 20px; max-height: 72vh; max-height: 72dvh; overflow: auto; overscroll-behavior: contain;
    scrollbar-gutter: stable; scrollbar-width: thin; scrollbar-color: #94b4cc #f1f5f9;
}
.ai-summary .ai-details::-webkit-scrollbar { width: 8px; height: 8px; }
.ai-summary .ai-details::-webkit-scrollbar-track { background: #f1f5f9; }
.ai-summary .ai-details::-webkit-scrollbar-thumb { background: #94b4cc; border-radius: 8px; border: 2px solid #f1f5f9; }
.ai-summary .ai-details::-webkit-scrollbar-thumb:hover { background: var(--ai-blue); }
.ai-summary .ai-details[hidden] { display: none !important; }
.ai-summary .ai-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; min-width: 280px; }
.ai-summary .ai-section { border: 1px solid var(--ai-border); border-radius: 8px; padding: 16px; min-width: 0; background: #fff; }
.ai-summary .ai-section-wide { grid-column: 1 / -1; }
.ai-summary .ai-section-overview { background: var(--ai-tint); border-color: #c8dff7; }
.ai-summary .ai-section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
.ai-summary .ai-section-title { display: flex; align-items: baseline; gap: 8px; min-width: 0; margin: 0; color: var(--ai-ink); font-size: 14px; font-weight: 700; line-height: 1.55; }
.ai-summary .ai-number { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 24px; height: 24px; border-radius: 5px; background: #e5f0fa; color: var(--ai-navy); font-size: 12px; font-weight: 700; }
.ai-summary .ai-count, .ai-summary .ai-tag { display: inline-flex; align-items: center; gap: 5px; border: 1px solid var(--ai-border); background: #f8fafc; color: #475569; border-radius: 4px; padding: 2px 7px; font-size: 12px; font-weight: 500; line-height: 1.6; }
.ai-summary .ai-count { flex-shrink: 0; }
.ai-summary .ai-copy-content p { margin: 0; overflow-wrap: anywhere; white-space: pre-line; }
.ai-summary .ai-progress-value { font-size: 20px; font-weight: 700; line-height: 1.3; color: var(--ai-blue); }
.ai-summary .ai-progress { height: 8px; border-radius: 4px; overflow: hidden; background: #e5f0fa; }
.ai-summary .ai-progress-fill { height: 100%; background: var(--ai-blue); border-radius: inherit; }
.ai-summary .ai-progress-labels { display: flex; justify-content: space-between; color: var(--ai-muted); font-size: 12px; margin-top: 6px; }
.ai-summary .ai-list { display: flex; flex-direction: column; gap: 10px; list-style: none; margin: 0; padding: 0; }
.ai-summary .ai-list-item { display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; background: #f8fafc; border: 1px solid #edf1f5; border-radius: 6px; }
.ai-summary .ai-list-item > i { color: var(--ai-blue); margin-top: 4px; font-size: 13px; flex-shrink: 0; }
.ai-summary .ai-item-content { min-width: 0; flex: 1; overflow-wrap: anywhere; white-space: normal; }
.ai-summary .ai-item-content > div:not(.ai-source), .ai-summary .ai-item-content > q { white-space: pre-line; }
.ai-summary [data-document-content] .ai-item-content { white-space: normal; }
.ai-summary [data-document-content] .ai-evidence { white-space: pre-line; }
.ai-summary .ai-source { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.ai-summary .ai-evidence { display: block; color: var(--ai-muted); border-left: 2px solid #c8dff7; padding-left: 8px; margin: 8px 0 0; font-size: 13px; }
.ai-summary [data-fact-kind] { display: block; color: var(--ai-muted); margin-bottom: 4px; font-size: 12px; }
.ai-summary .ai-review-note[data-summary-scope="focused"] { background: var(--ai-tint); border-color: #c8dff7; color: var(--ai-navy); }
.ai-summary .ai-empty { padding: 12px; border-radius: 6px; background: #f8fafc; color: var(--ai-muted); font-size: 13px; }
.ai-summary .ai-tone-success .ai-list-item > i { color: #16815c; }
.ai-summary .ai-tone-danger .ai-list-item > i { color: #c2414b; }
.ai-summary .ai-tone-warning .ai-list-item > i { color: #a16207; }
.ai-summary .ai-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.ai-summary .ai-link-list { display: grid; gap: 10px; }
.ai-summary .ai-link { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px; border: 1px solid var(--ai-border); border-radius: 6px; }
.ai-summary .ai-link-info { min-width: 0; flex: 1; }
.ai-summary .ai-link-name { display: block; font-weight: 600; overflow-wrap: anywhere; }
.ai-summary .ai-link-url { display: block; color: var(--ai-muted); font-size: 13px; overflow-wrap: anywhere; }
.ai-summary .ai-link-actions { display: flex; flex-shrink: 0; gap: 6px; }
.ai-summary .ai-citations { display: grid; gap: 12px; }
.ai-summary .ai-citation { padding: 12px 14px; border-left: 3px solid var(--ai-blue); border-radius: 0 6px 6px 0; background: #f8fafc; }
.ai-summary .ai-citation-head { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; font-weight: 600; }
.ai-summary .ai-citation blockquote { margin: 0; color: #475569; font-size: 13.5px; overflow-wrap: anywhere; white-space: pre-line; }
.ai-summary .ai-feedback { margin: 0; padding: 8px 20px; color: var(--ai-navy); border-bottom: 1px solid var(--ai-border); background: var(--ai-tint); font-size: 13px; }
.ai-summary .ai-feedback:empty { display: none; }
.ai-summary-dialog {
    width: calc(100vw - 40px); max-width: 1320px;
    height: calc(100vh - 40px); height: calc(100dvh - 40px);
    max-height: calc(100vh - 40px); max-height: calc(100dvh - 40px);
    margin: auto; padding: 0; border: 0; border-radius: 10px;
    background: #fff; color: #1a2b4a; overflow: hidden;
    box-shadow: 0 20px 70px rgba(0, 40, 85, .24);
}
.ai-summary-dialog::backdrop { background: rgba(15, 23, 42, .6); }
.ai-summary-dialog .ai-summary-expanded {
    display: flex; flex-direction: column; width: 100%; height: 100%;
    margin: 0; border: 0; border-radius: 0; font-size: 16px; line-height: 1.8;
}
.ai-summary-expanded .ai-header, .ai-summary-expanded .ai-stats, .ai-summary-expanded .ai-feedback { flex-shrink: 0; }
.ai-summary-expanded .ai-header { padding: 20px 24px; }
.ai-summary-expanded .ai-title { font-size: 20px; }
.ai-summary-expanded .ai-file, .ai-summary-expanded .ai-button { font-size: 14px; }
.ai-summary-expanded .ai-stats { padding: 16px 24px; }
.ai-summary-expanded .ai-stat-label { font-size: 14px; }
.ai-summary-expanded .ai-stat-value { font-size: 20px; }
.ai-summary-expanded .ai-details { flex: 1; min-height: 0; max-height: none; padding: 24px; }
.ai-summary-expanded .ai-grid { gap: 20px; }
.ai-summary-expanded .ai-section { padding: 20px; }
.ai-summary-expanded .ai-section-title { font-size: 17px; }
.ai-summary-expanded .ai-number { height: 28px; flex-basis: 28px; font-size: 13px; }
.ai-summary-expanded .ai-count, .ai-summary-expanded .ai-tag { font-size: 13px; }
.ai-summary-expanded .ai-list-item { padding: 12px 14px; }
.ai-summary-expanded .ai-evidence, .ai-summary-expanded .ai-empty, .ai-summary-expanded .ai-link-url { font-size: 14px; }
.ai-summary-expanded .ai-citation blockquote { font-size: 16px; }
.ai-summary-expanded .ai-progress-labels { font-size: 13px; }
@media (max-width: 600px) {
    .ai-summary-dialog { width: 100vw; max-width: 100vw; height: 100vh; height: 100dvh; max-height: 100vh; max-height: 100dvh; border-radius: 0; }
    .ai-summary-expanded .ai-header { padding: 16px; gap: 12px; }
    .ai-summary-expanded .ai-title { font-size: 18px; }
    .ai-summary-expanded .ai-stats { padding: 12px 16px; }
    .ai-summary-expanded .ai-stat { padding: 8px; }
    .ai-summary-expanded .ai-stat-label { font-size: 13px; }
    .ai-summary-expanded .ai-stat-value { font-size: 17px; }
    .ai-summary-expanded .ai-details { padding: 16px; }
    .ai-summary-expanded .ai-section { padding: 14px; }
}
@container (max-width: 680px) {
    .ai-summary .ai-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ai-summary .ai-grid { grid-template-columns: minmax(0, 1fr); }
}
@container (max-width: 440px) {
    .ai-summary .ai-header, .ai-summary .ai-details { padding: 16px; }
    .ai-summary .ai-stats { padding: 12px 16px; gap: 8px; }
    .ai-summary .ai-stat { padding: 10px; gap: 8px; }
    .ai-summary .ai-stat-value { font-size: 14px; }
    .ai-summary .ai-actions { width: 100%; }
    .ai-summary .ai-actions > .ai-button { flex: 1; }
    .ai-summary .ai-section { padding: 12px; }
    .ai-summary .ai-link { flex-wrap: wrap; }
    .ai-summary .ai-link-info { flex-basis: 100%; }
}
@media (prefers-reduced-motion: reduce) { .ai-summary .ai-button { transition: none; } }
</style>
@include('partials.ai_review_styles')
@endonce

<section class="ai-summary ai-summary-card" id="{{ $uniqueId }}" aria-labelledby="{{ $uniqueId }}-title" data-document-type="{{ $documentType }}" data-citation-url="{{ $citationUrl }}" data-pdf-url="{{ $pdfUrl }}" data-csrf-token="{{ $citationAvailable ? csrf_token() : '' }}">
    <header class="ai-header">
        <div class="ai-heading">
            <div class="ai-brand-icon" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <div>
                <h2 class="ai-title" id="{{ $uniqueId }}-title">Tóm tắt báo cáo AI</h2>
                <div class="ai-file"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i><span>{{ $pb->TenFile ?? 'Báo cáo tiến độ' }}</span></div>
            </div>
        </div>
        <div class="ai-actions">
            @if($reanalyzeUrl)
                <form method="POST" action="{{ $reanalyzeUrl }}" style="margin:0">@csrf<button type="submit" class="ai-button" data-ai-review-submit data-busy-label="Đang phân tích báo cáo…"><i class="fa-solid fa-rotate" aria-hidden="true"></i>Phân tích lại</button></form>
            @endif
            <button type="button" class="ai-button ai-button-primary" data-ai-action="open-large" aria-haspopup="dialog" title="Mở báo cáo trong cửa sổ lớn để dễ đọc"><i class="fa-solid fa-expand" aria-hidden="true"></i><span>Xem lớn</span></button>
            <button type="button" class="ai-button" data-ai-action="copy-summary" title="Sao chép báo cáo và phần đối chiếu yêu cầu"><i class="fa-regular fa-copy" aria-hidden="true"></i><span>Sao chép</span></button>
            <button type="button" class="ai-button" data-ai-action="toggle" aria-expanded="true" aria-controls="{{ $uniqueId }}-body"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i><span class="ai-toggle-label">Thu gọn</span></button>
        </div>
    </header>

    <!-- 4 Khối Thống Kê & Đánh Giá Tỷ Lệ Hoàn Thành AI -->
    <div class="ai-stats">
        <div class="ai-stat">
            <i class="fa-solid fa-robot" aria-hidden="true"></i>
            <div>
                <span class="ai-stat-label">AI Ước Lượng Tiến Độ</span>
                <span class="ai-stat-value">
                    @if($aiEvaluation !== null)
                        <span style="color: var(--ai-blue);">{{ $aiEvaluation }}%</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">Chưa đánh giá</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="ai-stat">
            <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
            <div>
                <span class="ai-stat-label">Tiến Độ Tổng Thể Đề Tài</span>
                <span class="ai-stat-value">
                    {{ $pb->baoCaoTienDo?->PhanTramHoanThanh ?? ($aiEvaluation ?? 'Đang cập nhật') }}{{ is_numeric($pb->baoCaoTienDo?->PhanTramHoanThanh ?? $aiEvaluation) ? '%' : '' }}
                </span>
            </div>
        </div>
        <div class="ai-stat">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            <div>
                <span class="ai-stat-label">Trạng Thái Nộp Báo Cáo</span>
                <span class="ai-stat-value" style="font-size: 13.5px;">
                    @if($isOverdue)
                        <span style="color: #dc3545;"><i class="fa-solid fa-triangle-exclamation me-1"></i>Nộp quá hạn</span>
                    @else
                        <span style="color: #198754;"><i class="fa-solid fa-circle-check me-1"></i>Đúng thời hạn</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="ai-stat">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            <div>
                <span class="ai-stat-label">Phiên Bản Báo Cáo</span>
                <span class="ai-stat-value" style="font-size: 13.5px;">
                    Lần {{ $lanNop }}
                    @if($hasVersionComparison)
                        <span class="badge bg-info-subtle text-info border font-monospace ms-1" style="font-size: 0.65rem;">+ So sánh Lần {{ $versionComparison['previous_version'] }}</span>
                    @else
                        <span class="badge bg-light text-secondary border font-monospace ms-1" style="font-size: 0.65rem;">Bản độc lập</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    @include('partials.ai_processing_status')
    <p class="ai-feedback" role="status" aria-live="polite" aria-atomic="true"></p>
    <div class="ai-details ai-details-body ai-copy-content" id="{{ $uniqueId }}-body" tabindex="0" role="region" aria-label="Nội dung tóm tắt AI, có thể cuộn để xem đầy đủ">
        
        <!-- Đối chiếu phiên bản (Lần 1 vs Lần 2) nếu có phiên bản trước -->
        @if($hasVersionComparison)
        <section class="ai-section ai-section-wide mb-3" style="border-left: 4px solid var(--ai-blue); background: var(--ai-tint); padding: 14px 16px;" data-summary-version-comparison>
            <div class="ai-section-head">
                <h3 class="ai-section-title" style="color: var(--ai-navy);">
                    <i class="fa-solid fa-code-compare me-1 text-primary"></i>Đối Chiếu Thay Đổi So Với Phiên Bản Lần {{ $versionComparison['previous_version'] }}
                </h3>
                @if(isset($versionComparison['completion_delta']))
                    <span class="badge {{ $versionComparison['completion_delta'] >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} rounded-pill px-3 py-1 font-monospace">
                        {{ $versionComparison['completion_delta'] >= 0 ? '+' : '' }}{{ $versionComparison['completion_delta'] }}% tiến độ
                    </span>
                @endif
            </div>
            <p class="text-dark mb-2" style="font-size: 13.5px; line-height: 1.6;">
                {{ $versionComparison['comparative_summary'] ?? 'Đã so sánh đối chiếu với phiên bản trước liền kề.' }}
            </p>

            <div class="row g-2 mt-1">
                @if(!empty($versionComparison['new_items']))
                <div class="col-md-6">
                    <div class="p-2 bg-white rounded border">
                        <div class="fw-bold text-success small mb-1"><i class="fa-solid fa-plus-circle me-1"></i>Nội dung mới bổ sung ({{ count($versionComparison['new_items']) }} mục):</div>
                        <ul class="mb-0 ps-3 small text-secondary" style="font-size: 12.5px;">
                            @foreach($versionComparison['new_items'] as $newItem)
                                <li>{{ $newItem }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                @if(!empty($versionComparison['modified_items']))
                <div class="col-md-6">
                    <div class="p-2 bg-white rounded border">
                        <div class="fw-bold text-primary small mb-1"><i class="fa-solid fa-pen-nib me-1"></i>Nội dung hoàn thiện/sửa đổi ({{ count($versionComparison['modified_items']) }} mục):</div>
                        <ul class="mb-0 ps-3 small text-secondary" style="font-size: 12.5px;">
                            @foreach($versionComparison['modified_items'] as $modItem)
                                <li>{{ is_array($modItem) ? ($modItem['hien_tai'] ?? '') : $modItem }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            @if(!empty($versionComparison['feedback_integration']))
                <div class="mt-2 p-2 bg-white rounded border">
                    <div class="fw-bold text-dark small mb-1"><i class="fa-solid fa-comment-dots me-1 text-info"></i>Tiếp thu góp ý của Giảng viên:</div>
                    <ul class="mb-0 ps-3 small text-secondary" style="font-size: 12.5px;">
                        @foreach($versionComparison['feedback_integration'] as $fbNote)
                            <li>{{ $fbNote }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
        @endif

        <!-- Chi tiết 4 Căn cứ đánh giá tỷ lệ AI nếu có -->
        @if(!empty($aiBasis['tieu_chi']))
        <details class="ai-review-disclosure mb-3" style="background: #f8fafc; border: 1px solid var(--ai-border); border-radius: 6px; padding: 10px 14px;">
            <summary class="fw-semibold text-primary" style="cursor: pointer; font-size: 13px;">
                <i class="fa-solid fa-scale-balanced me-1"></i> Căn cứ &amp; trọng số đánh giá tỷ lệ hoàn thành AI (4 tiêu chí khoa học)
            </summary>
            <div class="pt-2 mt-2 border-top">
                <div class="small text-muted mb-2">Tỷ lệ hoàn thành do AI ước lượng dựa trên 4 nhóm căn cứ khoa học với trọng số cụ thể:</div>
                <div class="row g-2">
                    @foreach($aiBasis['tieu_chi'] as $tc)
                    <div class="col-sm-6">
                        <div class="p-2 bg-white rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark" style="font-size: 12px;">{{ $tc['ten'] ?? '' }}</span>
                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 11px;">{{ $tc['diem'] ?? 0 }}/{{ $tc['trong_so'] ?? 0 }}đ</span>
                            </div>
                            <div class="small text-muted" style="font-size: 11.5px;">{{ $tc['dien_giai'] ?? '' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </details>
        @endif
        @if(!$analysisComplete && empty($aiData['_processing']))
            <p class="ai-review-note">{{ $analysis['note'] ?? 'Kết quả chưa xác nhận đã đọc đầy đủ tài liệu. Bạn có thể phân tích lại để cập nhật.' }} Các nhóm chưa có thông tin chưa đồng nghĩa tài liệu không đề cập.</p>
        @endif
        @if(isset($analysis['total_chunks']) && $analysis['total_chunks'] > 0)
            <p class="ai-empty" data-analysis-coverage>Văn bản: {{ $analysis['analyzed_chunks'] ?? 0 }}/{{ $analysis['total_chunks'] }} phần đã xử lý. {{ !empty($analysis['text_complete']) ? 'Đã đọc đủ các phần văn bản.' : 'Chưa hoàn tất phân tích văn bản; chưa dùng để kết luận báo cáo thiếu nội dung.' }}</p>
        @endif
        @foreach(collect(empty($aiData['_processing']) ? ($analysis['errors'] ?? []) : [])->unique('code') as $processingError)
            <p class="ai-review-note">{{ $processingError['message'] ?? 'Một phần xử lý AI chưa hoàn tất.' }}</p>
        @endforeach
        @if(!empty($aiData['_processing']) && !empty($analysis['visual_unread_pages']))
            <p class="ai-review-note">Một số trang có sơ đồ hoặc ảnh chưa được phân tích nội dung; chưa dùng các trang đó để kết luận thiếu yêu cầu.</p>
        @endif
        @if(!empty($analysis['checkpoint_warning']))<p class="ai-review-note">{{ $analysis['checkpoint_warning'] }}</p>@endif
        @if($flexibleSummary)
            <div class="ai-review-note" data-summary-scope="{{ $summaryScope['mode'] ?? 'document_fallback' }}">
                <strong>{{ ($summaryScope['mode'] ?? '') === 'focused' ? 'Tóm tắt trọng tâm theo yêu cầu đợt nộp.' : 'Tóm tắt theo nội dung tài liệu.' }}</strong>
                {{ $summaryScope['note'] ?? '' }}
            </div>
            @if($summaryStale)
                <p class="ai-review-note" data-summary-stale>Yêu cầu hoặc tên giai đoạn/đợt đã thay đổi. Bản tóm tắt này theo ngữ cảnh cũ; chọn Phân tích lại hoặc Đối chiếu lại để cập nhật trọng tâm.</p>
            @endif
        @endif
        <div class="ai-grid">
            @if(trim((string) $overallSummary) !== '')
                <section class="ai-section ai-section-wide ai-section-overview" data-summary-overview>
                    <div class="ai-section-head"><h3 class="ai-section-title">{{ $flexibleSummary ? 'Tổng quan tài liệu' : 'Tóm tắt chung' }}</h3></div>
                    <p class="ai-text-summary">{{ $overallSummary }}</p>
                    @if($documentContent)
                        <ul class="ai-list" style="margin-top:12px" data-document-content>
                            @foreach($documentContent as $contentItem)
                                <li class="ai-list-item"><div class="ai-item-content">
                                    <div>{{ $contentItem['content'] ?? '' }}</div>
                                    @if(!empty($contentItem['evidence']))
                                        <q class="ai-evidence">{{ \Illuminate\Support\Str::limit($contentItem['evidence'], 260) }}</q>
                                        <div class="ai-source">@foreach(($contentItem['source_pages'] ?? []) as $sourcePage) @include('partials.ai_citation_button', ['citationPage' => $sourcePage, 'citationQuote' => $contentItem['evidence'], 'citationProminent' => false, 'citationContext' => $contentItem['content'] ?? 'Nội dung báo cáo']) @endforeach</div>
                                    @endif
                                </div></li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
            @foreach($sections as $section)
                @if(!empty($section['items']))
                    @include('partials.ai_summary_section')
                @endif
            @endforeach
        </div>
        @if($flexibleSummary && !$sections)
            <p class="ai-empty">Chưa có mục trọng tâm được trích xuất. Đây chưa phải kết luận tài liệu thiếu yêu cầu; hãy xem độ bao phủ và phần đối chiếu bên dưới.</p>
        @endif
        @if($additionalSections)
            <details class="ai-review-disclosure" data-summary-additional>
                <summary>Nội dung khác trong tài liệu · Xem thêm ({{ count($additionalSections) }} mục)</summary>
                <p class="ai-empty">Các nội dung này giúp hiểu toàn bộ tài liệu, không phải tiêu chí bắt buộc của đợt nộp hiện tại.</p>
                <div class="ai-grid">
                    @foreach($additionalSections as $section)
                        @if(!empty($section['items']))
                            @include('partials.ai_summary_section')
                        @endif
                    @endforeach
                </div>
            </details>
        @endif
        @if($missingGroups)
            <p class="ai-empty" data-summary-missing>{{ $analysisComplete ? 'Báo cáo chưa đề cập: ' : 'Chưa trích xuất được từ phần văn bản đã đọc: ' }}{{ implode(', ', $missingGroups) }}.</p>
        @endif
        @if($reviewRound)
            <details class="ai-review-disclosure"><summary>Đối chiếu yêu cầu của giảng viên · Xem thêm</summary>
                @include('partials.ai_requirement_review')
            </details>
        @endif
    </div>
</section>

@once
<script>
(() => {
    if (window.huitAiSummaryActionsBound) return;
    window.huitAiSummaryActionsBound = true;
    async function openPdfCitation(card, opener) {
        const endpoint = card.dataset.citationUrl;
        if (!endpoint) return;
        const originalPage = Number(opener.dataset.citationPage);
        const originalQuote = opener.dataset.citationQuote || '';
        const isDocx = card.dataset.documentType === 'docx';
        const citationContext = opener.dataset.citationContext || '';
        const dialog = document.createElement('dialog');
        dialog.className = 'ai-citation-dialog';
        dialog.setAttribute('aria-label', 'Xem nhanh tài liệu và đối chiếu trích dẫn');
        const toolbar = document.createElement('div');
        toolbar.className = 'ai-citation-toolbar';
        const title = document.createElement('h3');
        title.textContent = isDocx ? 'Bằng chứng trong DOCX' : 'Trang ' + originalPage + ' · Báo cáo PDF';
        toolbar.appendChild(title);
        const controls = document.createElement('div');
        function control(label, handler) {
            const button = document.createElement('button');
            button.type = 'button'; button.textContent = label;
            button.addEventListener('click', handler);
            controls.appendChild(button);
            return button;
        }
        let currentPage = originalPage, totalPages = originalPage, pendingRequest = null;
        const previous = control(isDocx ? 'Vị trí trước' : 'Trang trước', () => loadPage(currentPage - 1));
        const next = control(isDocx ? 'Vị trí sau' : 'Trang sau', () => loadPage(currentPage + 1));
        control('Về đoạn trích', () => loadPage(originalPage));
        const fullPdf = document.createElement('a');
        fullPdf.href = card.dataset.pdfUrl + '#page=' + originalPage;
        fullPdf.target = '_blank'; fullPdf.rel = 'noopener noreferrer';
        fullPdf.textContent = isDocx ? 'Tải DOCX gốc' : 'Mở PDF đầy đủ'; controls.appendChild(fullPdf);
        const close = control('Đóng', () => dialog.close());
        toolbar.appendChild(controls);
        const quote = document.createElement('blockquote'); quote.className = 'ai-citation-quote';
        const transcript = document.createElement('details'); transcript.className = 'ai-citation-transcript';
        const transcriptLabel = document.createElement('summary'); transcriptLabel.textContent = 'Xem trích dẫn nguyên văn';
        transcript.append(transcriptLabel, quote);
        const context = document.createElement('p'); context.className = 'ai-citation-context';
        context.textContent = citationContext; context.hidden = !citationContext;
        const state = document.createElement('div'); state.className = 'ai-citation-state'; state.setAttribute('role', 'status');
        const viewer = document.createElement('div'); viewer.className = 'ai-citation-viewer'; viewer.tabIndex = 0;
        viewer.setAttribute('role', 'region'); viewer.setAttribute('aria-label', 'Trang PDF, cuộn để xem toàn bộ');
        dialog.append(toolbar, context, transcript, state, viewer);
        document.body.appendChild(dialog);
        const previousOverflow = document.body.style.overflow;
        dialog.addEventListener('close', () => {
            pendingRequest?.abort(); dialog.remove();
            document.body.style.overflow = previousOverflow;
            if (opener.isConnected) opener.focus({ preventScroll: true });
        }, { once: true });
        dialog.showModal(); document.body.style.overflow = 'hidden'; close.focus();

        async function loadPage(page) {
            pendingRequest?.abort(); pendingRequest = new AbortController();
            const request = pendingRequest;
            previous.disabled = next.disabled = true;
            quote.textContent = page === originalPage && originalQuote ? originalQuote : 'Xem nội dung trang PDF để đối chiếu trực tiếp.';
            transcript.open = false;
            transcript.hidden = page !== originalPage || !originalQuote;
            state.textContent = 'Đang mở vị trí dẫn chứng…'; viewer.replaceChildren();
            try {
                const response = await fetch(endpoint, {
                    method: 'POST', credentials: 'same-origin', signal: request.signal,
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': card.dataset.csrfToken },
                    body: JSON.stringify({ page, quote: page === originalPage ? originalQuote : '' }),
                });
                if (response.redirected || [401, 419].includes(response.status)) throw new Error('Phiên đăng nhập đã hết. Vui lòng đăng nhập lại để xem bằng chứng.');
                if (response.status === 403) throw new Error('Bạn không có quyền xem tài liệu này.');
                if (response.status === 404) throw new Error('Không tìm thấy tệp báo cáo. Vui lòng kiểm tra lại bài nộp.');
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) throw new Error('Máy chủ chưa trả dữ liệu xem tài liệu hợp lệ. Hãy tải lại trang và thử lại.');
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Không mở được trang PDF.');
                if (request.signal.aborted || !dialog.open) return;
                if (data.kind === 'docx') {
                    currentPage = Number(data.page); totalPages = Number(data.total_pages);
                    title.textContent = 'Bằng chứng · ' + data.label + ' · DOCX';
                    fullPdf.href = card.dataset.pdfUrl;
                    const source = document.createElement('article'); source.className = 'ai-docx-source';
                    const text = String(data.text || '');
                    const characters = Array.from(text);
                    const start = Number(data.highlight_start), end = Number(data.highlight_end);
                    const matched = data.matched && Number.isInteger(start) && Number.isInteger(end) && start >= 0 && end > start && end <= characters.length;
                    let mark = null;
                    if (matched) {
                        source.appendChild(document.createTextNode(characters.slice(0, start).join('')));
                        mark = document.createElement('mark'); mark.textContent = characters.slice(start, end).join(''); source.appendChild(mark);
                        source.appendChild(document.createTextNode(characters.slice(end).join('')));
                    } else source.textContent = text;
                    viewer.appendChild(source);
                    state.textContent = matched ? 'Đã tô vàng đoạn trích trong nội dung DOCX. Đây là vị trí đoạn/bảng, không phải số trang Word.' : 'Chưa khớp chính xác đoạn trích. Hãy đối chiếu trực tiếp nội dung nguồn.';
                    if (mark) mark.scrollIntoView({ block: 'center', inline: 'nearest' });
                    else viewer.scrollTop = 0;
                    previous.disabled = currentPage <= 1; next.disabled = currentPage >= totalPages;
                    return;
                }
                if (typeof data.image !== 'string' || !data.image.startsWith('data:image/png;base64,')) throw new Error('Dữ liệu xem PDF chưa hợp lệ.');
                currentPage = Number(data.page); totalPages = Number(data.total_pages);
                title.textContent = 'Bằng chứng · Trang PDF ' + currentPage + ' / ' + totalPages;
                fullPdf.href = card.dataset.pdfUrl + '#page=' + currentPage;
                state.textContent = data.matched ? 'Đoạn trích đã được tô vàng trên trang PDF. Có thể cuộn để xem thêm ngữ cảnh.'
                    : (page === originalPage && originalQuote ? 'Không khớp chính xác đoạn trích trên trang này. Vui lòng đối chiếu trực tiếp; chưa có vùng được đánh dấu.' : 'Đang hiển thị trang PDF gốc.');
                const image = document.createElement('img');
                image.alt = 'Nội dung trang ' + currentPage + ' của báo cáo PDF';
                image.addEventListener('load', () => {
                    if (!dialog.open || request.signal.aborted || pendingRequest !== request) return;
                    const offset = image.getBoundingClientRect().top - viewer.getBoundingClientRect().top + viewer.scrollTop;
                    viewer.scrollTop = Math.max(0, offset + image.clientHeight * (data.matched ? Number(data.focus_y) : 0) - viewer.clientHeight * .25);
                }, { once: true });
                image.src = data.image; viewer.appendChild(image);
                previous.disabled = currentPage <= 1; next.disabled = currentPage >= totalPages;
            } catch (error) {
                if (!request.signal.aborted) state.textContent = error.message || 'Chưa mở được PDF. Hãy chọn Mở PDF đầy đủ.';
            }
        }
        await loadPage(originalPage);
    }
    function openLargeSummary(card, opener) {
        const dialog = document.createElement('dialog');
        dialog.className = 'ai-summary-dialog';
        const panel = card.cloneNode(true);
        panel.id = card.id + '-expanded';
        panel.classList.add('ai-summary-expanded');
        const title = panel.querySelector('.ai-title');
        title.id = panel.id + '-title';
        title.textContent = 'Tóm tắt báo cáo AI — Xem lớn';
        panel.setAttribute('aria-labelledby', title.id);
        dialog.setAttribute('aria-labelledby', title.id);
        panel.querySelector('[data-ai-action="open-large"]').remove();
        panel.querySelector('[data-ai-action="toggle"]').remove();
        panel.querySelector('.ai-feedback').textContent = '';
        panel.querySelectorAll('button').forEach((button) => { button.disabled = button.matches('[data-ai-review-submit]') && panel.querySelector('[data-processing-active="1"]') !== null; });
        const body = panel.querySelector('.ai-details');
        body.id = panel.id + '-body';
        body.hidden = false;
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'ai-button ai-button-primary';
        closeButton.dataset.aiAction = 'close-large';
        closeButton.autofocus = true;
        closeButton.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i><span>Đóng</span>';
        panel.querySelector('.ai-actions').appendChild(closeButton);
        dialog.appendChild(panel);
        document.body.appendChild(dialog);
        const previousOverflow = document.body.style.overflow;
        dialog.addEventListener('close', () => {
            document.body.style.overflow = previousOverflow;
            dialog.remove();
            opener.focus({ preventScroll: true });
        }, { once: true });
        dialog.addEventListener('click', (event) => {
            if (event.target !== dialog) return;
            const bounds = dialog.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
        });
        dialog.showModal();
        document.body.style.overflow = 'hidden';
        body.scrollTop = 0;
        body.scrollLeft = 0;
    }
    async function copyText(text) {
        if (window.isSecureContext && navigator.clipboard?.writeText) {
            try { await navigator.clipboard.writeText(text); return; } catch (error) { /* Dùng cách sao chép tương thích bên dưới. */ }
        }
        const activeElement = document.activeElement;
        const field = document.createElement('textarea');
        field.value = text;
        field.setAttribute('readonly', '');
        field.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
        (activeElement?.closest('dialog[open]') || document.body).appendChild(field);
        let copied = false;
        try { field.select(); copied = document.execCommand('copy'); }
        finally { field.remove(); activeElement?.focus({ preventScroll: true }); }
        if (!copied) throw new Error('clipboard-unavailable');
    }
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-ai-action]');
        const card = button?.closest('.ai-summary');
        if (!card) return;
        const action = button.dataset.aiAction;
        if (action === 'citation') {
            await openPdfCitation(card, button);
            return;
        }
        if (action === 'open-large') {
            openLargeSummary(card, button);
            return;
        }
        if (action === 'close-large') {
            card.closest('dialog').close();
            return;
        }
        if (action === 'toggle') {
            const body = document.getElementById(button.getAttribute('aria-controls'));
            if (!body) return;
            body.hidden = !body.hidden;
            button.setAttribute('aria-expanded', String(!body.hidden));
            button.querySelector('.ai-toggle-label').textContent = body.hidden ? 'Mở rộng' : 'Thu gọn';
            button.querySelector('i').className = 'fa-solid ' + (body.hidden ? 'fa-chevron-down' : 'fa-chevron-up');
            return;
        }
        const feedback = card.querySelector('.ai-feedback');
        const text = action === 'copy-link' ? button.dataset.copyValue : Array.from(card.querySelectorAll('.ai-section, [data-summary-missing], .ai-review-block'))
            .map((section) => section.textContent.replace(/[ \t]+/g, ' ').replace(/\n\s*\n/g, '\n').trim()).join('\n\n');
        button.disabled = true;
        try {
            await copyText(text);
            feedback.textContent = action === 'copy-link' ? 'Đã sao chép liên kết.' : 'Đã sao chép đầy đủ nội dung tóm tắt.';
        } catch (error) {
            feedback.textContent = 'Chưa thể sao chép. Bạn có thể chọn nội dung và nhấn Ctrl+C.';
        } finally { button.disabled = false; }
    });
    document.addEventListener('submit', (event) => {
        if (!event.target.matches('.ai-review-submit')) return;
        const button = event.target.querySelector('[data-ai-review-submit]');
        if (button) { button.disabled = true; button.textContent = button.dataset.busyLabel || 'Đang đối chiếu yêu cầu…'; }
    });
})();
</script>
@endonce
