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
        @elseif(($node['status'] ?? '') === 'insufficient_text')
            <p class="ai-empty">Chưa có đủ nội dung văn bản đã xử lý để tóm tắt mục này.</p>
        @else
            <p class="ai-empty">{{ ($node['status'] ?? '') === 'completed' ? 'Chưa trích được ý tóm tắt có bằng chứng rõ trong mục này.' : 'Mục này chưa hoàn tất phân tích; nội dung chưa có tóm tắt không đồng nghĩa tài liệu không đề cập.' }}</p>
        @endif
        @if(!empty($node['related_assets']))
            <div class="card border-info my-3"><div class="card-body">
                <h6 class="text-primary">Hình/bảng liên quan</h6>
                <small class="text-muted">Hệ thống chỉ nhận diện tên hình/bảng từ văn bản PDF. Nội dung bên trong chưa được AI phân tích; vui lòng xem PDF gốc để kiểm chứng.</small>
                @foreach($node['related_assets'] as $asset)
                    <div class="border-top mt-2 pt-2">
                        <strong>{{ $asset['display_text'] }}</strong>
                        <div class="small text-muted">Mục {{ $asset['section_number'] ?? 'chưa xác định' }} — {{ $asset['section_title'] ?? '' }} · Trang PDF {{ $asset['page'] }}</div>
                        @if($citationAvailable)
                            <button type="button" class="btn btn-sm btn-outline-info mt-1" data-ai-action="citation" data-citation-page="{{ (int)$asset['page'] }}" data-citation-quote="{{ $asset['raw_text'] }}" data-citation-context="Tên hình/bảng — cần kiểm chứng nội dung trong PDF gốc">Xem vị trí tên hình/bảng</button>
                        @endif
                        <a class="btn btn-sm btn-outline-primary mt-1" href="{{ $pdfUrl }}#page={{ (int)$asset['page'] }}" target="_blank" rel="noopener">Mở PDF</a>
                    </div>
                @endforeach
            </div></div>
        @endif
        @foreach(($node['sections'] ?? []) as $childNode)
            @include('partials.ai_document_node', ['node'=>$childNode])
        @endforeach
    </div>
</details>
@endif
