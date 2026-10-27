{{-- All labels and facts are escaped. Source buttons reuse the controlled PDF preview. --}}
@if(($node['kind'] ?? '') !== 'front_matter' || !empty($node['items']) || !empty($node['sections']))
<details class="ai-review-disclosure" data-document-node="{{ $node['id'] }}">
    <summary>
        {{ ($node['kind'] ?? '') === 'chapter' ? 'Chương ' : '' }}{{ $node['number'] ?? '' }}{{ !empty($node['number']) ? '. ' : '' }}{{ $node['title'] }}
        <span class="ai-count">{{ $documentType === 'docx' ? 'Vị trí' : 'Trang' }} {{ $node['start_page'] }}–{{ $node['end_page'] }}</span>
    </summary>
    <div style="padding:12px 0" data-node-content>
        @if(!empty($node['items']))
            @include('partials.ai_summary_section', ['section'=>[
                'key'=>$node['id'], 'title'=>($node['level'] ?? 1) === 1 ? 'Tóm tắt chương' : 'Tóm tắt mục',
                'icon'=>'fa-file-lines', 'tone'=>'primary', 'items'=>$node['items'],
            ]])
        @else
            <p class="ai-empty">{{ ($node['status'] ?? '') === 'completed' ? 'Chưa trích được ý tóm tắt có bằng chứng rõ trong mục này.' : 'Mục này chưa hoàn tất phân tích; nội dung chưa có tóm tắt không đồng nghĩa tài liệu không đề cập.' }}</p>
        @endif
        @foreach(($node['sections'] ?? []) as $childNode)
            @include('partials.ai_document_node', ['node'=>$childNode])
        @endforeach
    </div>
</details>
@endif
