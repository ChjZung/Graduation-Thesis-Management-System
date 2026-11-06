<details class="ai-review-disclosure">
    <summary>{{ $node['number'] ?? '' }} {{ $node['title'] ?? 'Mục báo cáo' }} · {{ $documentType === 'docx' ? 'Vị trí' : 'Trang PDF' }} {{ $node['start_page'] ?? '—' }}</summary>
    @if(!empty($node['items']))
        @include('partials.ai_summary_section', ['section'=>[
            'key'=>'evidence-'.($node['id'] ?? ''), 'title'=>'Ý có nguồn và trích dẫn', 'icon'=>'fa-file-lines',
            'tone'=>'primary', 'items'=>$node['items'],
        ]])
    @elseif(!empty($node['raw_source_references']))
        @include('partials.ai_lab_sources', ['references'=>$node['raw_source_references'], 'referenceContext'=>$node['title'] ?? 'Mục báo cáo'])
    @elseif(empty($node['sections']))
        <p class="ai-muted">Chưa tìm được dẫn chứng phù hợp cho mục này.</p>
    @endif
    @foreach($node['sections'] ?? [] as $child)
        @include('partials.ai_document_evidence', ['node'=>$child])
    @endforeach
</details>
