{{-- All labels and facts are escaped. Source buttons reuse the controlled PDF preview. --}}
@if(($node['kind'] ?? '') !== 'front_matter' || !empty($node['items']) || !empty($node['sections']))
<details class="ai-review-disclosure" data-document-node="{{ $node['id'] }}">
    <summary>
        {{ ($node['kind'] ?? '') === 'chapter' ? 'Chương ' : '' }}{{ $node['number'] ?? '' }}{{ !empty($node['number']) ? '. ' : '' }}{{ $node['title'] }}
        <span class="ai-count">{{ $documentType === 'docx' ? 'Vị trí' : 'Trang' }} {{ $node['start_page'] }}–{{ $node['end_page'] }}</span>
    </summary>
    <div style="padding:12px 0" data-node-content>
        @php
            $isChapter = ($node['level'] ?? 1) === 1;
            $hasSubsections = !empty($node['sections']);
            $items = $node['items'] ?? [];

            // Extract all unique source references and pages
            $allReferences = [];
            $allPages = [];
            foreach ($items as $it) {
                if (is_array($it)) {
                    foreach ($it['source_references'] ?? [] as $ref) {
                        $refKey = ($ref['page'] ?? '') . ':' . ($ref['quote'] ?? '');
                        if (!isset($allReferences[$refKey])) {
                            $allReferences[$refKey] = $ref;
                        }
                        if (!empty($ref['page'])) {
                            $allPages[(int)$ref['page']] = (int)$ref['page'];
                        }
                    }
                    foreach ($it['source_pages'] ?? [] as $p) {
                        $allPages[(int)$p] = (int)$p;
                    }
                }
            }
            sort($allPages);
            $allReferences = array_values($allReferences);

            // Summary text for this node
            $summaryText = trim($node['summary'] ?? '');
            if (empty($summaryText) && !empty($items)) {
                $summaryText = implode(' ', array_filter(array_map(function($it) {
                    return is_array($it) ? ($it['content'] ?? '') : (string)$it;
                }, $items)));
            }
        @endphp

        @if(!empty($items))
            <div class="ai-section ai-section-wide ai-tone-primary mb-3 p-3 bg-white border rounded" data-summary-group="{{ $node['id'] }}" style="border-left: 3px solid #3189d3 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom flex-wrap gap-2">
                    <h5 class="mb-0 text-primary fs-6 fw-bold">
                        <i class="fa-solid fa-file-lines me-2"></i>{{ $isChapter ? 'Tóm tắt chương' : ($hasSubsections ? 'Tổng quan mục' : 'Tóm tắt mục') }}
                    </h5>
                    @if(!empty($allPages))
                        <div class="ai-source-pages d-flex align-items-center gap-1 flex-wrap">
                            <span class="small text-muted me-1">Nguồn:</span>
                            @foreach($allPages as $sourcePage)
                                @include('partials.ai_citation_button', [
                                    'citationPage' => $sourcePage,
                                    'citationQuote' => $allReferences[0]['quote'] ?? $summaryText,
                                    'citationProminent' => false,
                                    'citationContext' => $node['title'] ?? 'Nội dung tóm tắt'
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Summary content --}}
                <div class="ai-node-summary-body" style="line-height: 1.65; color: #212529;">
                    @if($isChapter)
                        {{-- Chapter level: render each chapter paragraph cleanly --}}
                        @foreach($items as $item)
                            @php
                                $itemContent = is_array($item) ? ($item['content'] ?? '') : (string)$item;
                            @endphp
                            @if(!empty($itemContent))
                                <p class="mb-2">{{ $itemContent }}</p>
                            @endif
                        @endforeach
                    @else
                        {{-- Section / Subsection level: unified summary text --}}
                        <p class="mb-2">{{ $summaryText }}</p>
                    @endif
                </div>

                {{-- Collapsed source evidence: accessible on demand without dominating the summary --}}
                @if(!empty($allReferences) && (!$hasSubsections || !empty($directItems)))
                    <details class="ai-review-disclosure mt-2 pt-2 border-top" data-lab-source-references>
                        <summary class="small text-muted" style="cursor: pointer; user-select: none;">
                            <i class="fa-solid fa-quote-left me-1"></i>Xem dẫn chứng từ PDF ({{ count($allReferences) }} trích dẫn)
                        </summary>
                        <div class="mt-2 ps-2 border-start">
                            @foreach($allReferences as $reference)
                                <div class="ai-citation mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <small class="text-muted fw-semibold">
                                            @if(!($teacherReportUi ?? false) && !empty($reference['source_id']))
                                                {{ $reference['source_id'] }} · 
                                            @endif
                                            Trang {{ $reference['page'] ?? 1 }}@if(($reference['end_page'] ?? $reference['page']) > $reference['page'])–{{ $reference['end_page'] }}@endif
                                        </small>
                                        @include('partials.ai_citation_button', [
                                            'citationPage' => $reference['page'] ?? 1,
                                            'citationQuote' => mb_substr($reference['quote'] ?? '', 0, 3000),
                                            'citationProminent' => false,
                                            'citationContext' => $node['title'] ?? 'Nội dung báo cáo'
                                        ])
                                    </div>
                                    <q class="ai-evidence d-block text-secondary fst-italic" style="font-size: 13px;">“{{ \Illuminate\Support\Str::limit($reference['quote'] ?? '', 260) }}”</q>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
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
