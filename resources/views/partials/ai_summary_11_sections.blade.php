{{-- Tóm tắt AI dùng chung cho Sinh viên và Giảng viên, theo giao diện HUIT admin. --}}
@php
    $aiJson = $pb->TomTatJson ?? [];
    if (is_string($aiJson)) {
        $aiJson = json_decode($aiJson, true) ?? [];
    }
    $aiData = isset($aiJson['data']) && is_array($aiJson['data']) ? $aiJson['data'] : $aiJson;
    $overallSummary = $aiData['overall_summary'] ?? ($pb->TomTatAI ?? 'Chưa có nội dung tóm tắt.');
    $completionPct = ($aiData['completion_basis'] ?? null) === 'pdf_explicit' && is_numeric($aiData['completion_percentage'] ?? null)
        ? max(0, min(100, (int) $aiData['completion_percentage'])) : null;
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
    $analysis = $aiData['analysis'] ?? [];
    $outline = $aiData['document_outline'] ?? [];
    $checkedCitations = isset($analysis['matched_citations']) ? (int) $analysis['matched_citations'] : null;
    $reviewRound = $dotNop ?? ($pb->dotNop ?? null);
    $requirementsReview = $aiData['requirements_review'] ?? null;
    $citationAvailable = !empty($pb->MaKhoi) && !empty($pb->DuongDanFile);
    $citationUrl = $citationAvailable ? route('baocao.ai.trich_dan', $pb->MaKhoi) : '';
    $pdfUrl = $citationAvailable ? route('baocao.ai.pdf', $pb->MaKhoi) : '';
    $reviewUrl = $citationAvailable && $reviewRound ? route('baocao.ai.doi_chieu', $pb->MaKhoi) : '';
    $reanalyzeUrl = $citationAvailable ? route('baocao.ai.phan_tich_lai', $pb->MaKhoi) : '';
    if (!empty($pb->LinkCode) && !collect($techLinks)->contains('url', $pb->LinkCode)) {
        array_unshift($techLinks, ['name' => 'Mã nguồn nộp đợt này', 'url' => $pb->LinkCode, 'type' => 'repository', 'source_pages' => [1]]);
    }
    $uniqueId = 'ai-summary-' . uniqid();
    $sections = [
        ['number' => 3, 'title' => 'Công việc đã làm', 'icon' => 'fa-circle-check', 'tone' => 'success', 'items' => $completedWork, 'empty' => 'Chưa có công việc được trích xuất.'],
        ['number' => 4, 'title' => 'Sản phẩm đầu ra', 'icon' => 'fa-box-archive', 'tone' => 'primary', 'items' => $deliverables, 'empty' => 'Chưa có sản phẩm đầu ra được liệt kê.'],
        ['number' => 5, 'title' => 'Mã nguồn và công nghệ', 'icon' => 'fa-code-branch', 'tone' => 'primary', 'items' => $techLinks],
        ['number' => 6, 'title' => 'Khó khăn gặp phải', 'icon' => 'fa-triangle-exclamation', 'tone' => 'danger', 'items' => $difficulties, 'empty' => 'Chưa ghi nhận khó khăn trong kết quả trích xuất.'],
        ['number' => 7, 'title' => 'Hạng mục tồn đọng', 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'items' => $pendingItems, 'empty' => 'Chưa có hạng mục tồn đọng được liệt kê.'],
        ['number' => 8, 'title' => 'Kế hoạch tiếp theo', 'icon' => 'fa-calendar-check', 'tone' => 'primary', 'items' => $nextSteps, 'empty' => 'Chưa có kế hoạch tiếp theo được trích xuất.'],
        ['number' => 9, 'title' => 'Điểm cần làm rõ', 'icon' => 'fa-circle-question', 'tone' => 'warning', 'items' => $missingInfo, 'empty' => 'Chưa có nội dung cần làm rõ được liệt kê.'],
    ];
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
.ai-summary .ai-item-content { min-width: 0; flex: 1; overflow-wrap: anywhere; white-space: pre-line; }
.ai-summary .ai-source { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.ai-summary .ai-evidence { display: block; color: var(--ai-muted); border-left: 2px solid #c8dff7; padding-left: 8px; margin: 8px 0 0; font-size: 13px; }
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

<section class="ai-summary ai-summary-card" id="{{ $uniqueId }}" aria-labelledby="{{ $uniqueId }}-title" data-citation-url="{{ $citationUrl }}" data-pdf-url="{{ $pdfUrl }}" data-csrf-token="{{ $citationAvailable ? csrf_token() : '' }}">
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
                <form method="POST" action="{{ $reanalyzeUrl }}" style="margin:0">@csrf<button type="submit" class="ai-button" data-ai-review-submit data-busy-label="Đang phân tích PDF…"><i class="fa-solid fa-rotate" aria-hidden="true"></i>Phân tích lại PDF</button></form>
            @endif
            <button type="button" class="ai-button ai-button-primary" data-ai-action="open-large" aria-haspopup="dialog" title="Mở báo cáo trong cửa sổ lớn để dễ đọc"><i class="fa-solid fa-expand" aria-hidden="true"></i><span>Xem lớn</span></button>
            <button type="button" class="ai-button" data-ai-action="copy-summary" title="Sao chép báo cáo và phần đối chiếu yêu cầu"><i class="fa-regular fa-copy" aria-hidden="true"></i><span>Sao chép</span></button>
            <button type="button" class="ai-button" data-ai-action="toggle" aria-expanded="true" aria-controls="{{ $uniqueId }}-body"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i><span class="ai-toggle-label">Thu gọn</span></button>
        </div>
    </header>
    <div class="ai-stats" aria-label="Tổng quan kết quả phân tích">
        <div class="ai-stat"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><div><span class="ai-stat-label">Trích dẫn đã khớp</span><strong class="ai-stat-value">{{ $checkedCitations !== null ? $checkedCitations . ' đoạn' : 'Chưa kiểm chứng' }}</strong></div></div>
        <div class="ai-stat"><i class="fa-solid fa-chart-line" aria-hidden="true"></i><div><span class="ai-stat-label">Tiến độ trong PDF</span><strong class="ai-stat-value">{{ $completionPct !== null ? $completionPct . '%' : 'Chưa xác định' }}</strong></div></div>
        <div class="ai-stat"><i class="fa-solid fa-list-check" aria-hidden="true"></i><div><span class="ai-stat-label">Công việc đã làm</span><strong class="ai-stat-value">{{ count($completedWork) }} mục</strong></div></div>
        <div class="ai-stat"><i class="fa-solid fa-link" aria-hidden="true"></i><div><span class="ai-stat-label">Liên kết kỹ thuật</span><strong class="ai-stat-value">{{ count($techLinks) }} liên kết</strong></div></div>
    </div>
    <p class="ai-feedback" role="status" aria-live="polite" aria-atomic="true"></p>
    <div class="ai-details ai-details-body ai-copy-content" id="{{ $uniqueId }}-body" tabindex="0" role="region" aria-label="Nội dung tóm tắt AI, có thể cuộn để xem đầy đủ">
        <div class="ai-empty" style="margin-bottom:14px;line-height:1.65">
            @if(!empty($analysis['version']))
                <strong>{{ ($analysis['method'] ?? '') === 'gemini' ? 'Tổng hợp bằng dịch vụ AI' : 'Trích xuất cấu trúc và văn bản tại máy chủ' }}</strong>
                <div>Đã lấy văn bản từ {{ $analysis['text_pages'] ?? 0 }}/{{ $analysis['total_pages'] ?? 0 }} trang PDF.</div>
                @if(($analysis['method'] ?? '') === 'gemini')<div>AI xử lý {{ $analysis['analyzed_chunks'] ?? 0 }}/{{ $analysis['total_chunks'] ?? 0 }} phần văn bản.</div>@endif
                @if(!empty($analysis['note']))<div>{{ $analysis['note'] }}</div>@endif
                @if(!empty($analysis['visual_unread_pages']))<div>Trang có ảnh/sơ đồ cần kiểm tra thêm: {{ implode(', ', $analysis['visual_unread_pages']) }}.</div>@endif
            @else
                Kết quả cũ chưa có thông tin kiểm chứng mức độ đọc tài liệu. Chọn <strong>Phân tích lại PDF</strong> để cập nhật.
            @endif
        </div>
        <h3 class="ai-report-caption">Nội dung có trong báo cáo PDF</h3>
        <div class="ai-grid">
            <section class="ai-section ai-section-wide ai-section-overview">
                <div class="ai-section-head"><h3 class="ai-section-title"><span class="ai-number">01</span><span>Tổng Quan Tiến Độ</span></h3><span class="ai-count">Tổng quan</span></div>
                <p class="ai-text-summary">{{ $overallSummary }}</p>
                @if($outline)
                    <ul class="ai-list">@foreach($outline as $chapter)<li class="ai-list-item"><div>{{ $chapter['content'] }} <span class="ai-source">@foreach(($chapter['source_pages'] ?? []) as $sourcePage) @include('partials.ai_citation_button', ['citationPage' => $sourcePage, 'citationQuote' => $chapter['evidence'] ?? '']) @endforeach</span></div></li>@endforeach</ul>
                @endif
            </section>
            <section class="ai-section ai-section-wide">
                <div class="ai-section-head"><h3 class="ai-section-title"><span class="ai-number">02</span><span>Tiến độ được nêu trong PDF</span></h3><strong class="ai-progress-value">{{ $completionPct !== null ? $completionPct . '%' : 'Chưa xác định' }}</strong></div>
                @if($completionPct !== null)
                <div class="ai-progress" role="progressbar" aria-label="Tiến độ hoàn thành ước tính" aria-valuenow="{{ $completionPct }}" aria-valuemin="0" aria-valuemax="100"><div class="ai-progress-fill" style="width: {{ $completionPct }}%"></div></div>
                <div class="ai-progress-labels"><span>Khởi đầu · 0%</span><span>Hoàn thành · 100%</span></div>
                @else
                    <p class="ai-empty">Chưa có phần trăm tiến độ được xác định từ PDF. Hệ thống không tự gán phần trăm theo số trang hoặc số chức năng.</p>
                @endif
            </section>
            @foreach($sections as $section)
                <section class="ai-section ai-tone-{{ $section['tone'] }} {{ $section['number'] === 5 ? 'ai-section-wide' : '' }}">
                    <div class="ai-section-head">
                        <h3 class="ai-section-title"><span class="ai-number">{{ str_pad($section['number'], 2, '0', STR_PAD_LEFT) }}</span><span>{{ $section['title'] }}</span></h3>
                        <span class="ai-count">{{ count($section['items']) }} {{ $section['number'] === 5 ? 'liên kết' : 'mục' }}</span>
                    </div>
                    @if($section['number'] === 5)
                        @if(!empty($techStack))
                            <div class="ai-tags">@foreach($techStack as $technology)<span class="ai-tag"><i class="fa-solid fa-code" aria-hidden="true"></i>{{ $technology }}</span>@endforeach</div>
                        @endif
                        @if(!empty($techLinks))
                            <div class="ai-link-list">
                                @foreach($techLinks as $link)
                                    <div class="ai-link">
                                        <div class="ai-link-info"><span class="ai-link-name">{{ $link['name'] ?? 'Liên kết kỹ thuật' }}</span><span class="ai-link-url">{{ $link['url'] }}</span></div>
                                        <div class="ai-link-actions">
                                            <button type="button" class="ai-button" data-ai-action="copy-link" data-copy-value="{{ $link['url'] }}" aria-label="Sao chép liên kết {{ $link['name'] ?? 'kỹ thuật' }}" title="Sao chép liên kết"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="ai-button ai-button-primary" aria-label="Mở {{ $link['name'] ?? 'liên kết kỹ thuật' }} trong thẻ mới"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Truy cập</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="ai-empty">Chưa có liên kết mã nguồn hoặc tài liệu kỹ thuật.</div>
                        @endif
                    @elseif(!empty($section['items']))
                        <ul class="ai-list">
                            @foreach($section['items'] as $item)
                                @php
                                    $itemContent = is_array($item) ? ($item['content'] ?? '') : (string) $item;
                                    $itemPages = is_array($item) ? (array) ($item['source_pages'] ?? []) : [];
                                    $itemEvidence = is_array($item) ? ($item['evidence'] ?? null) : null;
                                @endphp
                                <li class="ai-list-item"><i class="fa-solid {{ $section['icon'] }}" aria-hidden="true"></i><div class="ai-item-content"><div>{{ $itemContent }}</div>@if(!empty($itemPages))<div class="ai-source">@foreach($itemPages as $sourcePage) @include('partials.ai_citation_button', ['citationPage' => $sourcePage, 'citationQuote' => $itemEvidence ?? '']) @endforeach</div>@endif @if(!empty($itemEvidence))<q class="ai-evidence">{{ \Illuminate\Support\Str::limit($itemEvidence, 160) }}</q>@endif</div></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="ai-empty">{{ $section['empty'] }}</div>
                    @endif
                </section>
            @endforeach
            <section class="ai-section ai-section-wide">
                <div class="ai-section-head"><h3 class="ai-section-title"><span class="ai-number">10</span><span>Trích dẫn và số trang</span></h3><span class="ai-count">{{ count($citations) }} trích dẫn</span></div>
                @if(!empty($citations))
                    <div class="ai-citations">
                        @foreach($citations as $cite)
                            <div class="ai-citation"><div class="ai-citation-head">@include('partials.ai_citation_button', ['citationPage' => $cite['page'] ?? 1, 'citationQuote' => $cite['snippet'] ?? ''])<span>{{ $cite['topic'] ?? 'Nội dung tham chiếu' }}</span></div><blockquote>{{ $cite['snippet'] ?? '' }}</blockquote></div>
                        @endforeach
                    </div>
                @else
                    <div class="ai-empty">Chưa có trích dẫn kèm số trang trong kết quả phân tích.</div>
                @endif
            </section>
            <section class="ai-section ai-section-wide ai-tone-warning">
                <div class="ai-section-head"><h3 class="ai-section-title"><span class="ai-number">11</span><span>Khuyến nghị và cảnh báo từ AI</span></h3></div>
                @if(!empty($aiRecs))
                    <ul class="ai-list">@foreach($aiRecs as $recommendation)<li class="ai-list-item"><i class="fa-regular fa-lightbulb" aria-hidden="true"></i><div class="ai-item-content">{{ $recommendation }}</div></li>@endforeach</ul>
                @else
                    <div class="ai-empty">Chưa có khuyến nghị bổ sung trong kết quả phân tích.</div>
                @endif
            </section>
        </div>
        @if($reviewRound)
            @include('partials.ai_requirement_review')
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
        const citationContext = opener.dataset.citationContext || '';
        const dialog = document.createElement('dialog');
        dialog.className = 'ai-citation-dialog';
        dialog.setAttribute('aria-label', 'Xem nhanh PDF và đối chiếu trích dẫn');
        const toolbar = document.createElement('div');
        toolbar.className = 'ai-citation-toolbar';
        const title = document.createElement('h3');
        title.textContent = 'Trang ' + originalPage + ' · Báo cáo PDF';
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
        const previous = control('Trang trước', () => loadPage(currentPage - 1));
        const next = control('Trang sau', () => loadPage(currentPage + 1));
        control('Về đoạn trích', () => loadPage(originalPage));
        const fullPdf = document.createElement('a');
        fullPdf.href = card.dataset.pdfUrl + '#page=' + originalPage;
        fullPdf.target = '_blank'; fullPdf.rel = 'noopener noreferrer';
        fullPdf.textContent = 'Mở PDF đầy đủ'; controls.appendChild(fullPdf);
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
            state.textContent = 'Đang mở trang PDF…'; viewer.replaceChildren();
            try {
                const response = await fetch(endpoint, {
                    method: 'POST', credentials: 'same-origin', signal: request.signal,
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': card.dataset.csrfToken },
                    body: JSON.stringify({ page, quote: page === originalPage ? originalQuote : '' }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Không mở được trang PDF.');
                if (request.signal.aborted || !dialog.open) return;
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
        panel.querySelectorAll('button').forEach((button) => { button.disabled = false; });
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
        const text = action === 'copy-link' ? button.dataset.copyValue : Array.from(card.querySelectorAll('.ai-section, .ai-review-block'))
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
