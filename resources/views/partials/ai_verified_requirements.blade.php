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
    @if($briefStale)<p class="ai-empty">Yêu cầu của đợt đã thay đổi. Kết quả dưới đây thuộc bản trước; chọn Phân tích lại để cập nhật.</p>@endif
    @if($reviewPending)<p class="alert alert-info" data-requirements-pending>Đang chờ đối chiếu yêu cầu. Hệ thống xử lý bản tóm tắt trước; dẫn chứng sẽ tự xuất hiện tại đây khi được kiểm chứng. Chưa có kết quả không có nghĩa báo cáo thiếu nội dung.</p>@endif
    @if(($review['status']??'')==='partial')<p class="ai-empty">Đối chiếu đang xử lý hoặc đã dừng một phần. Các mục chưa kiểm chứng chưa có kết luận.</p>@endif
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
                        @if($category==='supported')
                            @php $findings=[];$references=[];$seen=[]; @endphp
                            @foreach($entry['points'] as $point)
                                @php
                                    if(!in_array($point['explanation']??'',$findings,true))$findings[]=$point['explanation']??'';
                                    foreach($point['source_references']??[] as $ref){$key=($ref['source_id']??'').'|'.($ref['page']??'').'|'.($ref['start']??'');if(!isset($seen[$key])){$seen[$key]=true;$references[]=$ref;}}
                                @endphp
                            @endforeach
                            <p>{{ $entry['item']['report_explanation'] ?? implode(' ',array_slice($findings,0,2)) }}</p>
                            @if(collect($entry['points'])->contains(fn($p)=>($p['check_status']??'')==='PARTIAL'))<p class="small text-muted">Có dẫn chứng cho một phần yêu cầu; chưa xác nhận đáp ứng toàn bộ.</p>@endif
                            @include('partials.ai_lab_sources',['references'=>$references,'referenceContext'=>$entry['item']['text']??''])
                            @foreach($entry['item']['report_points']??[] as $point)
                                @foreach($point['manual_references']??[] as $manualRef)
                                    <div class="mt-2 small">Sơ đồ cần xem trực tiếp — trang PDF {{ $manualRef['page'] }}.
                                    @include('partials.ai_citation_button',['citationPage'=>$manualRef['page'],'citationQuote'=>$manualRef['quote'],'citationProminent'=>false,'citationContext'=>'Vị trí hình/sơ đồ, chưa xác minh nội dung'])</div>
                                @endforeach
                            @endforeach
                        @else
                            <p class="ai-empty">Hệ thống chưa tìm được dẫn chứng phù hợp cho phần này. Giảng viên vui lòng đối chiếu báo cáo gốc; đây không phải kết luận sinh viên chưa thực hiện.</p>
                            @foreach($entry['points'] as $point)
                                @foreach($point['manual_references']??[] as $manualRef)
                                    <div class="ai-citation"><p>Vị trí sơ đồ cần xem trực tiếp — trang PDF {{ $manualRef['page'] }}.</p><q>{{ $manualRef['quote'] }}</q>
                                    @include('partials.ai_citation_button',['citationPage'=>$manualRef['page'],'citationQuote'=>$manualRef['quote'],'citationProminent'=>false,'citationContext'=>'Vị trí hình/sơ đồ, chưa xác minh nội dung'])</div>
                                @endforeach
                            @endforeach
                            @if($citationAvailable)<a href="{{ $pdfUrl }}" target="_blank" rel="noopener">Mở báo cáo gốc</a>@endif
                            @if(collect($entry['points'])->contains(fn($p)=>($p['check_status']??'')==='NEEDS_VISUAL_REVIEW'))<p class="small text-muted">Có mục hình/sơ đồ cần giảng viên xem trực tiếp trong PDF.</p>@endif
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
