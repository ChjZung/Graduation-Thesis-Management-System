@if(($aiData['requirements_review']['mode']??'')==='verified_v2')
@include('partials.ai_verified_requirements')
@else
@php
    $briefDocument=$aiData['requirements_document']??[];$briefItems=$aiData['teacher_requirements']??[];
    $briefText=(string)($briefDocument['text']??'');
    $briefStale=$reviewRound && ($aiData['summary_scope']['context_hash']??'')!==$reviewRound->maNguCanhDoiChieuAi();
@endphp
<section class="ai-section" data-teacher-requirements-summary>
    <h3 class="ai-section-title mb-2">Yêu cầu của giảng viên theo đợt</h3>
    <p class="small text-muted">{{ $briefDocument['period']??'' }} · {{ $briefDocument['title']??'' }}</p>
    @if($briefStale)<p class="alert alert-warning">Yêu cầu của đợt đã thay đổi. Phần dưới thuộc bản đã lưu trước; chọn Phân tích lại để cập nhật.</p>@endif
    @if(!$briefText)<p class="ai-empty">Giảng viên chưa thiết lập yêu cầu phân tích cho đợt nộp này.</p>
    @elseif(!$briefItems)<p class="ai-empty">Yêu cầu này chưa được AI phân tích.</p>@endif
    @if($briefText)<details class="ai-review-disclosure"><summary>Xem toàn bộ mô tả yêu cầu gốc của đợt</summary><div class="mt-2" style="white-space:pre-wrap">{{ $briefText }}</div></details>@endif
    @php
        $requirementNumber=0; $requirementGroups=['completed'=>[], 'missing'=>[]]; $scopeNotes=[];
        foreach($briefItems as $briefItem){
            $points=$briefItem['report_points']??[];
            if(!$points){
                $references=[];
                foreach($briefItem['report_matches']??[] as $match) foreach($match['source_references']??[] as $ref) $references[]=$ref;
                $points=[['title'=>$briefItem['text']??'','relation'=>$briefItem['report_relation']??'pending',
                    'explanation'=>$briefItem['report_explanation']??'','source_references'=>$references]];
            }
            $original=trim((string)($briefItem['evidence_quote']??$briefItem['text']??''));
            if(collect($points)->every(fn($point)=>($point['relation']??'')==='scope_note')){
                $scopeNotes[]=$original; continue;
            }
            ++$requirementNumber;
            $heading=preg_match('/(?:^|\R)\s*(\d+[.)]\s*.*)$/us',$original,$numbered) ? trim($numbered[1]) : $requirementNumber.'. '.$original;
            foreach(['completed','missing'] as $category){
                $filtered=array_values(array_filter($points,fn($point)=>(($point['relation']??'')==='related_text' && count($point['source_references']??[])>0)===($category==='completed')));
                if($filtered) $requirementGroups[$category][]=['id'=>$briefItem['requirement_id']??'','heading'=>$heading,'points'=>$filtered];
            }
        }
    @endphp
    @foreach($scopeNotes as $scopeNote)<p class="small text-muted mt-3">{{ $scopeNote }}</p>@endforeach
    @if($briefItems)
    <nav class="ai-report-tabs nav nav-tabs mt-3" role="tablist" aria-label="Đối chiếu công việc theo yêu cầu">
        @foreach(['completed'=>'Có dẫn chứng','missing'=>'Chưa đủ dẫn chứng'] as $category=>$label)
            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $uniqueId }}-requirements-tab-{{ $category }}" data-bs-toggle="tab" data-bs-target="#{{ $uniqueId }}-requirements-pane-{{ $category }}" type="button" role="tab" aria-controls="{{ $uniqueId }}-requirements-pane-{{ $category }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-ai-requirement-tab="{{ $category }}">{{ $label }}</button>
        @endforeach
    </nav>
    <div class="tab-content" data-ai-requirement-content>
        @foreach($requirementGroups as $category=>$groups)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $uniqueId }}-requirements-pane-{{ $category }}" role="tabpanel" aria-labelledby="{{ $uniqueId }}-requirements-tab-{{ $category }}" tabindex="0">
                @if($category==='completed')
                    <p class="small text-muted mt-3">Nội dung báo cáo có dẫn chứng theo yêu cầu; giảng viên cần đối chiếu trước khi xác nhận hoàn thành.</p>
                @else
                    <p class="small text-muted mt-3">Các nội dung chưa có dẫn chứng được chấp nhận. Điều này không đồng nghĩa sinh viên chưa thực hiện công việc.</p>
                @endif
                @forelse($groups as $group)
                    <article class="ai-requirement-card" data-requirement-id="{{ $group['id'] }}">
                        <header class="ai-requirement-question"><h4>{{ $group['heading'] }}</h4></header>
                        <div data-requirement-report-links>
                        @foreach($group['points'] as $point)
                            <div class="ai-requirement-answer" data-requirement-point>
                                @if($category==='completed')
                                    <p>{{ $point['explanation']??'' }}</p>
                                    @include('partials.ai_lab_sources',['references'=>$point['source_references']??[],'referenceContext'=>$group['heading'].' — '.($point['explanation']??'')])
                                @else
                                    <p class="ai-empty"><strong>{{ $point['title']??'' }}:</strong> {{ ($point['relation']??'pending')==='pending' ? 'Chưa đối chiếu yêu cầu này.' : 'Không có dẫn chứng' }}</p>
                                @endif
                            </div>
                        @endforeach
                        </div>
                    </article>
                @empty
                    <p class="ai-empty">{{ $category==='completed' ? 'Chưa có công việc kèm dẫn chứng theo yêu cầu.' : 'Không có nội dung chưa có dẫn chứng trong kết quả đối chiếu hiện tại.' }}</p>
                @endforelse
            </div>
        @endforeach
    </div>
    @endif
</section>

@endif
