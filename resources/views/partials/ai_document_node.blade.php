{{-- All labels and facts are escaped. Source buttons reuse the controlled PDF preview. --}}
@if(($node['kind'] ?? '') !== 'front_matter' || !empty($node['items']) || !empty($node['sections']))
@php
    $nodeTitle = $node['title'] ?? '';
    $visibleAssets = array_filter($node['related_assets'] ?? [], \App\Support\AiReportPresentation::visibleReportAsset(...));
    if ($documentType === 'pdf') {
        $nodeTitle = preg_replace(
            ['/(sở)(dữ)/iu', '/(dữ)(liệu)/iu', '/(xử)(lý)/iu', '/(trị)(hệ)/iu', '/(hệ)(thống)/iu'],
            '$1 $2',
            $nodeTitle
        );
    }
    $chapterPrefix = ($node['kind'] ?? '') === 'chapter' && !preg_match('/^Chương\b/iu', $nodeTitle) ? 'Chương ' : '';
@endphp
<details class="ai-review-disclosure" data-document-node="{{ $node['id'] }}">
    <summary>
        <span class="me-2">{{ $chapterPrefix }}{{ $node['number'] ?? '' }}{{ !empty($node['number']) ? '. ' : '' }}{{ $nodeTitle }}</span>
        <span class="ai-count">{{ $documentType === 'docx' ? 'Vị trí' : 'Trang' }} {{ $node['start_page'] }}–{{ $node['end_page'] }}</span>
    </summary>
    <div style="padding:12px 0" data-node-content>
        @if(isset($node['chapter_reading']))
            <h4 class="ai-section-title mb-2">Tóm tắt bằng lời văn mới</h4>
            @foreach(($node['chapter_reading']['paragraphs'] ?? []) as $paragraph)
                <p class="mb-2">{{ $paragraph }}</p>
            @endforeach
            @if(($node['chapter_reading']['status'] ?? '') !== 'complete')
                <p class="ai-muted">{{ !empty($node['chapter_reading']['error']) ? 'Bản tóm tắt chương chưa qua kiểm tra. Bạn vẫn có thể xem các ý có nguồn và mở dẫn chứng bên dưới.' : 'Bản tóm tắt chưa đầy đủ. Một số ý có thể cần đối chiếu với tài liệu gốc.' }}</p>
            @endif
            @if(!empty($node['items']))
                <details class="mt-2">
                    <summary>Xem dẫn chứng đối chiếu</summary>
                    @foreach($node['items'] as $sourceIndex => $sourceItem)
                        @continue(isset($node['chapter_reading']['evidence_indexes']) && !in_array($sourceIndex, $node['chapter_reading']['evidence_indexes'], true))
                        @if(!empty($sourceItem['source_references']))
                            @include('partials.ai_lab_sources', ['references'=>$sourceItem['source_references'], 'referenceContext'=>$nodeTitle])
                        @elseif(!empty($sourceItem['evidence']))
                            @foreach(($sourceItem['source_pages'] ?? []) as $sourcePage)
                                @include('partials.ai_citation_button', ['citationPage'=>$sourcePage, 'citationQuote'=>$sourceItem['evidence'], 'citationProminent'=>false, 'citationContext'=>$nodeTitle])
                            @endforeach
                        @endif
                    @endforeach
                </details>
            @endif
        @elseif(!empty($node['items']))
            <p class="ai-muted">Chưa có bản tóm tắt diễn đạt lại cho phần này.</p>
            <details>
                <summary>Xem các ý có nguồn</summary>
            @include('partials.ai_summary_section', ['section'=>[
                'key'=>$node['id'], 'title'=>'Nguồn đối chiếu',
                'icon'=>'fa-file-lines', 'tone'=>'primary', 'items'=>$node['items'],
            ]])
            </details>
        @elseif(($node['status'] ?? '') === 'insufficient_text')
            @if(!empty($node['raw_source_references']))
                <p class="ai-muted">Đã tìm thấy văn bản trong PDF. Bản tóm tắt của mục này chưa được lưu.</p>
                @include('partials.ai_lab_sources', ['references'=>$node['raw_source_references'], 'referenceContext'=>$nodeTitle])
            @elseif(!empty($node['visual_only']))
                <p class="ai-muted">Mục này có hình hoặc sơ đồ. Hãy mở PDF để xem nội dung trực quan.</p>
            @else
                <p class="ai-empty">Chưa tìm được đoạn văn phù hợp để tóm tắt mục này.</p>
            @endif
        @else
            <p class="ai-empty">{{ ($node['status'] ?? '') === 'completed' ? 'Chưa trích được ý tóm tắt có bằng chứng rõ trong mục này.' : 'Mục này chưa hoàn tất phân tích; nội dung chưa có tóm tắt không đồng nghĩa tài liệu không đề cập.' }}</p>
        @endif
        @if($visibleAssets)
            <div class="card border-info my-3"><div class="card-body">
                <h6 class="text-primary">Sơ đồ và bảng liên quan</h6>
                <small class="text-muted">Hệ thống chỉ nhận diện tên hình/bảng từ văn bản PDF. Nội dung bên trong chưa được AI phân tích; vui lòng xem PDF gốc để kiểm chứng.</small>
                @foreach($visibleAssets as $asset)
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
