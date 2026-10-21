<section class="ai-section ai-section-wide ai-tone-{{ $section['tone'] }}" data-summary-group="{{ $section['key'] }}">
                        <div class="ai-section-head"><h3 class="ai-section-title">{{ $section['title'] }}</h3><span class="ai-count">{{ count($section['items']) }} mục</span></div>
                        <ul class="ai-list">
                            @foreach($section['items'] as $item)
                                @php
                                    $itemContent = is_array($item) ? ($item['content'] ?? '') : (string) $item;
                                    $itemPages = is_array($item) ? (array) ($item['source_pages'] ?? []) : [];
                                    $itemEvidence = is_array($item) ? ($item['evidence'] ?? '') : '';
                                    $kindLabel = is_array($item) ? ([
                                        'background' => 'Bối cảnh / kiến thức nền', 'requirement' => 'Yêu cầu trong tài liệu',
                                        'proposal' => 'Đề xuất', 'method' => 'Phương pháp',
                                        'reported_work' => 'Tác giả báo cáo đã thực hiện', 'reported_result' => 'Kết quả được báo cáo',
                                        'limitation' => 'Hạn chế được nêu', 'future_work' => 'Kế hoạch / hướng phát triển',
                                        'other' => 'Nội dung trích xuất',
                                    ][$item['kind'] ?? ''] ?? '') : '';
                                @endphp
                                <li class="ai-list-item"><i class="fa-solid {{ $section['icon'] }}" aria-hidden="true"></i><div class="ai-item-content">
                                    @if($kindLabel)<small class="ai-muted" data-fact-kind="{{ $item['kind'] }}">{{ $kindLabel }}</small>@endif
                                    <div>{{ $itemContent }}</div>
                                    @if($itemEvidence)<q class="ai-evidence">{{ \Illuminate\Support\Str::limit($itemEvidence, 260) }}</q>@endif
                                    @if($itemPages && $itemEvidence)<div class="ai-source">@foreach($itemPages as $sourcePage) @include('partials.ai_citation_button', ['citationPage' => $sourcePage, 'citationQuote' => $itemEvidence, 'citationProminent' => false, 'citationContext' => $itemContent]) @endforeach</div>@endif
                                </div></li>
                            @endforeach
                        </ul>
                    </section>
