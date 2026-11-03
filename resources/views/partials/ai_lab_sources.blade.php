{{-- Source identities stay separate: each button opens that quote's own page. --}}
<details class="ai-review-disclosure" data-lab-source-references>
    <summary>Xem dẫn chứng ({{ count($references) }} nguồn)</summary>
    @foreach($references as $reference)
        @php $isCaptionReference=($reference['source_type']??'')==='CAPTION_REFERENCE'; @endphp
        <div class="ai-citation" @if($isCaptionReference) data-requirement-caption @endif>
            <small>@if(!($teacherReportUi ?? false)){{ $reference['source_id'] ?? '' }} · @endif {{ $isCaptionReference ? 'Tên hình/bảng · ' : 'Văn bản · ' }}Trang {{ $reference['page'] ?? 1 }}@if(($reference['end_page'] ?? $reference['page']) > $reference['page'])–{{ $reference['end_page'] }}@endif</small>
            <q class="ai-evidence">{{ \Illuminate\Support\Str::limit($reference['quote'] ?? '', 300) }}</q>
            @include('partials.ai_citation_button', ['citationPage'=>$reference['page'] ?? 1, 'citationQuote'=>mb_substr($reference['quote'] ?? '',0,3000), 'citationProminent'=>false, 'citationContext'=>$referenceContext ?? 'Nội dung báo cáo'])
            @if($isCaptionReference)<p class="small text-muted mb-0">(Chỉ nhận diện tên hình/bảng; giảng viên vui lòng kiểm chứng nội dung trong PDF gốc.)</p>@endif
        </div>
    @endforeach
</details>
