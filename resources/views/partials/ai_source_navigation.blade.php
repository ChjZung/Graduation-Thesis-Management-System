@php
    $navigationMode = $navigationMode ?? 'all';
    $visibleNavigation = \App\Support\AiReportPresentation::visibleReportNavigation($aiData['source_navigation'] ?? []);
@endphp
@if(!$sourceChanged && ($navigationMode === 'catalog' ? !empty($visibleNavigation['assets']) : (!empty($visibleNavigation['rows']) || !empty($visibleNavigation['assets']))))
    <section class="ai-section ai-section-wide mb-3" data-source-navigation data-source-can-open="{{ $citationAvailable ? 1 : 0 }}">
        <h3 class="ai-section-title">{{ $navigationMode === 'catalog' ? 'Danh mục sơ đồ và bảng' : 'Tra cứu chức năng và dẫn chứng' }}</h3>
        <p class="ai-review-note">Đã trích xuất văn bản · Gợi ý minh chứng · Chưa được AI kiểm chứng</p>
        @if($navigationMode !== 'catalog')
        <label for="{{ $uniqueId }}-search">Tên chức năng hoặc từ khóa</label>
        <input id="{{ $uniqueId }}-search" type="search" class="form-control mb-2" data-source-search placeholder="Ví dụ: Quản lý ngân hàng câu hỏi">
        <label for="{{ $uniqueId }}-source-kind">Phạm vi mô tả</label>
        <select id="{{ $uniqueId }}-source-kind" class="form-select mb-2" data-source-kind>
            <option value="">Tất cả</option>
            @foreach(array_filter(array_unique(array_column($visibleNavigation['rows'] ?? [], 'type'))) as $sourceKind)
                <option>{{ $sourceKind }}</option>
            @endforeach
        </select>
        <div data-source-results aria-live="polite"></div>
        @endif
        @if($navigationMode !== 'search')
        <details @if($navigationMode === 'catalog') open @endif>
            <summary>Danh mục sơ đồ và bảng ({{ count($visibleNavigation['assets'] ?? []) }})</summary>
            <p class="ai-review-note">Mục có thể dựa trên chú thích hoặc văn bản trích xuất từ bảng. Mở nguồn để kiểm tra.</p>
            <label for="{{ $uniqueId }}-asset-type">Loại sơ đồ hoặc bảng</label>
            <select id="{{ $uniqueId }}-asset-type" class="form-select mb-2" data-source-filter>
                <option value="">Tất cả</option>
                @foreach(array_unique(array_column($visibleNavigation['assets'] ?? [], 'type')) as $assetType)
                    <option>{{ $assetType }}</option>
                @endforeach
            </select>
            <ul class="ps-3" data-source-assets>
                @foreach($visibleNavigation['assets'] ?? [] as $asset)
                    <li class="mb-2" data-source-asset="{{ $asset['type'] }}">
                        <strong>{{ $asset['type'] }}</strong> · {{ $asset['quote'] }}
                        @if(!empty($asset['section'])) <small>· {{ $asset['section'] }}</small> @endif
                        @if($citationAvailable)
                            @include('partials.ai_citation_button', ['citationPage' => $asset['page'], 'citationQuote' => $asset['quote'], 'sourceLabels' => [(string) $asset['page'] => $asset['label']]])
                        @endif
                    </li>
                @endforeach
            </ul>
        </details>
        @endif
        @if(!empty($visibleNavigation['truncated']))
            <small>Chỉ mục xem nhanh có giới hạn. Kết quả tìm kiếm chỉ phản ánh phần đã lập chỉ mục.</small>
        @endif
        @if($navigationMode !== 'catalog')
            <template data-source-index>@json($visibleNavigation)</template>
        @endif
    </section>
@endif
@once
<script src="{{ asset('js/ai_source_navigation.js') }}?v={{ filemtime(public_path('js/ai_source_navigation.js')) }}" defer></script>
@endonce
