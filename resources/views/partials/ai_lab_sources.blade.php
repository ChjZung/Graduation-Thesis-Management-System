{{-- Source identities stay separate: each button opens that quote's own page. --}}
<details class="ai-review-disclosure" data-lab-source-references>
    <summary>Xem dẫn chứng ({{ count($references) }} nguồn)</summary>
    @foreach($references as $reference)
        <div class="ai-citation">
            <small>@if(!($teacherReportUi ?? false)){{ $reference['source_id'] ?? '' }} · @endif Trang {{ $reference['page'] ?? 1 }}@if(($reference['end_page'] ?? $reference['page']) > $reference['page'])–{{ $reference['end_page'] }}@endif</small>
            <q class="ai-evidence">{{ \Illuminate\Support\Str::limit($reference['quote'] ?? '', 300) }}</q>
            @include('partials.ai_citation_button', ['citationPage'=>$reference['page'] ?? 1, 'citationQuote'=>mb_substr($reference['quote'] ?? '',0,3000), 'citationProminent'=>false, 'citationContext'=>$referenceContext ?? 'Nội dung báo cáo'])
        </div>
    @endforeach
</details>
