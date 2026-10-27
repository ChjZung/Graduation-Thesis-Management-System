@if(($aiData['requirements_review']['mode']??'')==='verified_v2' ||
    (($aiData['requirements_review']['status']??'')==='pending' && config('ai_processing.lab.review_engine')==='verified_v2'))
@include('partials.ai_verified_requirements')
@else
@if($teacherReportUi ?? false)
@include('partials.ai_teacher_requirements')
@else
<section class="ai-section ai-section-wide" data-teacher-requirements-summary>
    <div class="ai-section-head"><h3 class="ai-section-title">Yêu cầu giảng viên theo đợt</h3></div>
    <p class="ai-muted">Từng yêu cầu kèm mô tả gốc và đoạn liên quan trong báo cáo. Dẫn chứng hỗ trợ giảng viên đối chiếu, không tự chấm đạt/không đạt.</p>
    @php
        $briefDocument = $aiData['requirements_document'] ?? [];
        $briefText = (string) ($briefDocument['text'] ?? '');
        $briefItems = $aiData['teacher_requirements'] ?? [];
        $briefStale = $reviewRound && ($aiData['summary_scope']['context_hash'] ?? '') !== $reviewRound->maNguCanhDoiChieuAi();
    @endphp
    @if($briefStale)<p class="ai-empty">Yêu cầu của đợt đã thay đổi. Phần dưới thuộc bản đã xử lý trước; chọn Phân tích lại để cập nhật.</p>@endif
    @if(!$briefText)
        <p class="ai-empty">Chưa cung cấp yêu cầu theo đợt để tóm tắt.</p>
    @elseif(!$briefItems)
        <p class="ai-empty">Chưa có ý yêu cầu được tóm tắt và xác nhận câu nguồn. Xem mô tả gốc bên dưới.</p>
    @endif
    @foreach($briefItems as $briefItem)
        <div class="ai-list-item" data-requirement-id="{{ $briefItem['requirement_id'] ?? '' }}"><div class="ai-item-content">
            <div>{{ $briefItem['text'] ?? '' }}</div>
            <details class="ai-review-disclosure">
                <summary>Xem yêu cầu gốc</summary>
                <small>{{ $briefDocument['title'] ?? '' }} · {{ $briefDocument['period'] ?? '' }}</small>
                <q class="ai-evidence">{{ $briefItem['evidence_quote'] ?? '' }}</q>
            </details>
            <div data-requirement-report-links>
                @if(($briefItem['report_relation'] ?? 'pending') === 'related_text')
                    <strong>Đoạn liên quan trong báo cáo</strong>
                    <p>{{ $briefItem['report_explanation'] ?? '' }}</p>
                    <small>Nội dung liên quan chưa chứng minh đã triển khai hoặc hoàn thành yêu cầu.</small>
                    @foreach($briefItem['report_matches'] ?? [] as $reportMatch)
                        <div class="ai-citation">
                            <div>{{ $reportMatch['content'] ?? '' }}</div>
                            @if(in_array($reportMatch['kind'] ?? '', ['design','theory']))<small>Loại nội dung: {{ ($reportMatch['kind'] ?? '') === 'design' ? 'Thiết kế / đặc tả / dự kiến' : 'Cơ sở lý thuyết' }}.</small>@endif
                            @include('partials.ai_lab_sources', ['references'=>$reportMatch['source_references'] ?? [], 'referenceContext'=>($briefItem['text'] ?? '').' — '.($reportMatch['content'] ?? '')])
                        </div>
                    @endforeach
                @elseif(($briefItem['report_relation'] ?? '') === 'scope_note')
                    <p class="ai-empty">Phạm vi / điều kiện của đợt: {{ $briefItem['report_explanation'] ?? '' }}</p>
                @elseif(($briefItem['report_relation'] ?? '') === 'not_found')
                    <p class="ai-empty">Chưa tìm được đoạn phù hợp trong các nguồn text đã đối chiếu. {{ $briefItem['report_explanation'] ?? '' }} Giảng viên có thể kiểm tra PDF; đây không phải kết luận báo cáo thiếu nội dung.</p>
                @else
                    <p class="ai-empty">Chưa hoàn tất tìm dẫn chứng báo cáo cho yêu cầu này. Chọn Phân tích lại để cập nhật.</p>
                @endif
            </div>
        </div></div>
    @endforeach
    @if($briefText)
        <details class="ai-review-disclosure"><summary>Xem toàn bộ mô tả đã gửi AI</summary><div style="white-space:pre-wrap">{{ $briefText }}</div></details>
    @endif
</section>


@endif

@endif
