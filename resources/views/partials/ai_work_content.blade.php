@php
    $contentReview=$aiData['content_review']??null;
    $reviewAvailable=is_array($contentReview) && ($contentReview['status']??'')==='completed'
        && ($contentReview['source_hash']??null)===($aiData['_source_hash']??$aiData['source_identity']['source_hash']??null) && !$sourceChanged;
@endphp
<section data-ai-work-summary>
    <nav class="ai-report-tabs nav nav-tabs mt-0" role="tablist" aria-label="Công việc và nội dung báo cáo">
        @foreach(['completed'=>'Công việc đã hoàn thành','unfinished'=>'Nội dung chưa hoàn thiện'] as $workKey=>$workLabel)
            <button class="nav-link {{ $loop->first?'active':'' }}" id="{{ $uniqueId }}-work-tab-{{ $workKey }}" data-bs-toggle="tab" data-bs-target="#{{ $uniqueId }}-work-pane-{{ $workKey }}" type="button" role="tab" aria-controls="{{ $uniqueId }}-work-pane-{{ $workKey }}" aria-selected="{{ $loop->first?'true':'false' }}" data-ai-work-tab="{{ $workKey }}">{{ $workLabel }}</button>
        @endforeach
    </nav>
    <div class="tab-content pt-3" data-ai-work-content>
        <div class="tab-pane fade show active" id="{{ $uniqueId }}-work-pane-completed" role="tabpanel" aria-labelledby="{{ $uniqueId }}-work-tab-completed">
            <p class="ai-muted">Các công việc tác giả báo cáo đã thực hiện, có câu trích khớp nguồn; chưa xác minh việc triển khai độc lập.</p>
            @if($completedWork)@include('partials.ai_summary_section',['section'=>['key'=>'completed_work','title'=>'Công việc đã hoàn thành','icon'=>'fa-circle-check','tone'=>'success','items'=>$completedWork]])@else<div class="card"><div class="card-body">Không tìm thấy công việc đã hoàn thành có đủ bằng chứng trong báo cáo.</div></div>@endif
        </div>
        <div class="tab-pane fade" id="{{ $uniqueId }}-work-pane-unfinished" role="tabpanel" aria-labelledby="{{ $uniqueId }}-work-tab-unfinished" data-content-review>
            <p class="ai-muted">Các dấu hiệu chưa hoàn thiện trong tài liệu; không tự kết luận sinh viên chưa làm công việc tương ứng.</p>
            @if(!$reviewAvailable)
                <p class="ai-empty">Chưa có kết quả kiểm tra nội dung cho đúng phiên bản tệp hiện tại; chưa thể kết luận mục nào còn thiếu.</p>
            @else
                @foreach(['missing_content'=>'Mục chưa có nội dung hoặc còn TODO/TBD','reported_unfinished'=>'Nội dung tác giả nêu chưa hoàn thiện'] as $groupKey=>$groupLabel)
                    @if(!empty($contentReview[$groupKey]))
                        <section class="ai-section ai-tone-warning" data-content-review-group="{{ $groupKey }}">
                            <div class="ai-section-head"><h3 class="ai-section-title">{{ $groupLabel }}</h3><span class="ai-count">{{ count($contentReview[$groupKey]) }} mục</span></div>
                            @foreach($contentReview[$groupKey] as $observation)
                                <article class="ai-requirement-card" data-content-observation="{{ $observation['status'] }}">
                                    <h4 class="h6">{{ $observation['section_title'] }}</h4>
                                    <p>{{ $observation['content'] }}</p>
                                    @include('partials.ai_lab_sources',['references'=>$observation['source_references']??[],'referenceContext'=>$observation['section_title']])
                                </article>
                            @endforeach
                        </section>
                    @endif
                @endforeach
                @if(empty($contentReview['missing_content']) && empty($contentReview['reported_unfinished']))
                    <p class="ai-empty">Chưa phát hiện mục trống hoặc dấu hiệu chưa hoàn thiện trong phạm vi đã kiểm tra. Điều này không xác nhận toàn bộ công việc đã hoàn thành.</p>
                @endif
                @if(!empty($contentReview['manual_review']))
                    <details class="ai-review-disclosure" data-content-manual-review>
                        <summary>Cần xem trực tiếp / chưa xác định ({{ count($contentReview['manual_review']) }} mục)</summary>
                        <p class="small text-muted mt-2">Các mục này không bị tính là thiếu nội dung hoặc công việc chưa hoàn thành.</p>
                        @foreach($contentReview['manual_review'] as $observation)
                            <article class="ai-requirement-card"><h4 class="h6">{{ $observation['section_title'] }}</h4><p>{{ $observation['content'] }}</p>
                            @include('partials.ai_lab_sources',['references'=>$observation['source_references']??[],'referenceContext'=>$observation['section_title']])</article>
                        @endforeach
                    </details>
                @endif
                <p class="small text-muted">Đã kiểm tra cấu trúc {{ $contentReview['coverage']['checked_leaf_sections']??0 }} mục không có mục con trong tài liệu. @if(empty($contentReview['coverage']['map_available']))Chưa có dữ liệu Map để kiểm tra các câu tác giả nêu chưa hoàn thiện.@elseif(empty($contentReview['coverage']['map_scope_complete']))Phần kiểm tra câu trích Map còn giới hạn ở các gói đã xử lý.@endif</p>
            @endif
        </div>
    </div>
</section>
