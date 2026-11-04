@php
    $briefDocument=$aiData['requirements_document']??[];
    $briefItems=$aiData['teacher_requirements']??[];
    $briefText=(string)($briefDocument['text']??'');
    $review=$aiData['requirements_review']??[];
    $reviewActive=in_array($aiData['_processing']['status']??'', ['queued','running','waiting']);
    $reviewPending=($review['status']??'')==='pending' && $reviewActive;
    $briefStale=$reviewRound && ($aiData['summary_scope']['context_hash']??'')!==$reviewRound->maNguCanhDoiChieuAi();
    $groups=['supported'=>[],'uncertain'=>[]];
    foreach($briefItems as $item){
        $supported=array_values(array_filter($item['report_points']??[],fn($p)=>($p['relation']??'')==='related_text' && count($p['source_references']??[])>0));
        $uncertain=array_values(array_filter($item['report_points']??[],fn($p)=>($p['relation']??'')!=='related_text' || count($p['source_references']??[])===0));
        if($supported)$groups['supported'][]=['item'=>$item,'points'=>$supported];
        if($uncertain)$groups['uncertain'][]=['item'=>$item,'points'=>$uncertain];
    }
@endphp
<section class="ai-section ai-section-wide" data-teacher-requirements-summary data-verified-requirement-review>
    <h3 class="ai-section-title">Yêu cầu của giảng viên theo đợt</h3>
    <p class="small text-muted">{{ $briefDocument['period']??'' }} · {{ $briefDocument['title']??'' }}</p>
    <p class="small text-muted">Dẫn chứng hỗ trợ giảng viên đối chiếu báo cáo; không tự xác nhận công việc đã hoàn thành.</p>
    @if($briefText)
        <details class="ai-review-disclosure" @if($reviewPending) open @endif><summary>Xem mô tả yêu cầu gốc</summary><div class="mt-2" style="white-space:pre-wrap">{{ $briefText }}</div></details>
    @endif
    @if($briefStale)<p class="ai-empty">Yêu cầu của đợt đã thay đổi. Chọn đối chiếu lại để cập nhật; không sử dụng kết quả cũ cho yêu cầu mới.</p>@endif
    @if($reviewPending)<p class="alert alert-info" data-requirements-pending>Đang chờ đối chiếu yêu cầu. Hệ thống xử lý bản tóm tắt trước; dẫn chứng sẽ tự xuất hiện tại đây khi được kiểm chứng. Chưa có kết quả không có nghĩa báo cáo thiếu nội dung.</p>@endif
    @if(($review['status']??'')==='partial')
        @if($reviewActive)
            <p class="small text-muted">Đang tiếp tục đối chiếu yêu cầu. Các mục chưa kiểm chứng chưa có kết luận.</p>
        @else
            @php $checked=(int)($review['processing']['completed_checks']??0); $checkTotal=(int)($review['processing']['total_checks']??0); @endphp
            @if($checkTotal > 0)
                <p class="small text-muted" data-review-incomplete-note>Đã lưu kết quả đối chiếu cho {{ $checked }}/{{ $checkTotal }} tiêu chí; {{ max(0, $checkTotal - $checked) }} tiêu chí chưa xác minh được nguồn. Giảng viên vui lòng kiểm tra các mục chưa đủ dẫn chứng trong PDF gốc.</p>
            @else
                <p class="small text-muted" data-review-incomplete-note>Đã lưu kết quả đối chiếu. Giảng viên vui lòng kiểm tra các mục chưa đủ dẫn chứng trong PDF gốc.</p>
            @endif
            <button type="button" class="ai-button" data-open-ai-progress>Xem chi tiết tiến độ và thông báo</button>
        @endif
    @endif
    @if(!$briefText)<p class="ai-empty">Chưa có mô tả yêu cầu để đối chiếu.</p>
    @elseif(!$briefItems && !$reviewPending)<p class="ai-empty">Chưa có kết quả đối chiếu cho mô tả này.</p>
    @elseif($briefItems)
        <nav class="ai-report-tabs nav nav-tabs mt-3" role="tablist" aria-label="Dẫn chứng theo yêu cầu">
        @foreach(['supported'=>'Có dẫn chứng','uncertain'=>'Chưa đủ dẫn chứng'] as $category=>$label)
            <button class="nav-link {{ $loop->first?'active':'' }}" id="{{ $uniqueId }}-verified-tab-{{ $category }}" data-bs-toggle="tab" data-bs-target="#{{ $uniqueId }}-verified-pane-{{ $category }}" type="button" role="tab" aria-controls="{{ $uniqueId }}-verified-pane-{{ $category }}" aria-selected="{{ $loop->first?'true':'false' }}" data-ai-requirement-tab="{{ $category }}">{{ $label }}</button>
        @endforeach
        </nav>
        <div class="tab-content" data-ai-requirement-content>
        @foreach($groups as $category=>$entries)
            <div class="tab-pane fade {{ $loop->first?'show active':'' }}" id="{{ $uniqueId }}-verified-pane-{{ $category }}" role="tabpanel" aria-labelledby="{{ $uniqueId }}-verified-tab-{{ $category }}">
                @forelse($entries as $entry)
                    <article class="ai-requirement-card" data-requirement-id="{{ $entry['item']['requirement_id']??'' }}">
                        <header class="ai-requirement-question"><h4>{{ $entry['item']['text']??'' }}</h4></header>
                        <div data-requirement-report-links>
                        @php
                            $captionReferences=[]; $textReferences=[];
                            $sourceKey=fn($ref)=>(int)($ref['page']??0).'|'.preg_replace('/\s+/u',' ',trim((string)($ref['quote']??'')));
                            foreach($entry['points'] as $point) foreach($point['manual_references']??[] as $ref) {
                                if(!empty($ref['page']) && trim($ref['quote']??'')!=='')$captionReferences[$sourceKey($ref)]=$ref;
                            }
                            foreach($entry['points'] as $point) foreach($point['source_references']??[] as $ref) {
                                $key=$sourceKey($ref);
                                if(isset($captionReferences[$key]) || ($ref['source_type']??'')==='CAPTION_REFERENCE')$captionReferences[$key]=$ref;
                                else $textReferences[$key]=$ref;
                            }
                        @endphp
                        @if($category==='supported')
                            @php $findings=[]; @endphp
                            @foreach($entry['points'] as $point)
                                @php
                                    if(!in_array($point['explanation']??'',$findings,true))$findings[]=$point['explanation']??'';
                                @endphp
                            @endforeach
                            <p>{{ $entry['item']['report_explanation'] ?? implode(' ',array_slice($findings,0,2)) }}</p>
                            @if(collect($entry['points'])->contains(fn($p)=>($p['check_status']??'')==='PARTIAL'))<p class="small text-muted">Có dẫn chứng cho một phần yêu cầu; chưa xác nhận đáp ứng toàn bộ.</p>@endif
                        @else
                            <p class="ai-empty">Hệ thống chưa tìm được dẫn chứng phù hợp cho phần này. Giảng viên vui lòng đối chiếu báo cáo gốc; đây không phải kết luận sinh viên chưa thực hiện.</p>
                            @if($citationAvailable)<a href="{{ $pdfUrl }}" target="_blank" rel="noopener">Mở báo cáo gốc</a>@endif
                            @if(collect($entry['points'])->contains(fn($p)=>($p['check_status']??'')==='NEEDS_VISUAL_REVIEW'))<p class="small text-muted">Có mục hình/sơ đồ cần giảng viên xem trực tiếp trong PDF.</p>@endif
                        @endif
                        @if($textReferences || $captionReferences)
                            @php
                                $unifiedReferences=array_values($textReferences);
                                foreach($captionReferences as $captionRef)$unifiedReferences[]=array_merge($captionRef,['source_type'=>'CAPTION_REFERENCE']);
                            @endphp
                            @include('partials.ai_lab_sources',['references'=>$unifiedReferences,'referenceContext'=>$entry['item']['text']??''])
                        @endif
                        </div>
                    </article>
                @empty
                    <p class="ai-empty">{{ $category==='supported'?'Chưa có dẫn chứng đủ hỗ trợ được hiển thị.':'Không có mục cần đối chiếu thêm trong kết quả hiện có.' }}</p>
                @endforelse
            </div>
        @endforeach
        </div>
    @endif
</section>
