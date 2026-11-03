@php
    $cId = $cr['id'] ?? '';
    $maxScore = (float)($cr['max'] ?? 0);
    $targetScore = ($cId === 'tc7') ? ($maxScore > 0.5 ? $maxScore - 0.5 : $maxScore) : null;
    $diemLines = $cr['diem_toi_da_lines'] ?? null;
    $parsed = \App\Models\PhieuChamDiem::parseTieuChi($cr['noi_dung'] ?? '', $targetScore, $diemLines);
@endphp

<div class="tieu-chi-renderer text-start" style="white-space: normal !important; overflow-wrap: anywhere !important; word-break: normal; font-size: 0.8rem; line-height: 1.45;">
    @if(!empty($parsed['title']))
        <div class="{{ !empty($parsed['items']) ? 'fw-bold text-dark mb-1' : 'text-dark fw-medium' }}">
            {{ $parsed['title'] }}
        </div>
    @endif

    @if(!empty($parsed['items']))
        <div class="tieu-chi-items">
            @foreach($parsed['items'] as $item)
                @if($item['type'] === 'group')
                    <div class="tieu-chi-group mt-1 pt-1 ps-2 border-start border-2 border-primary-subtle ms-1">
                        <div class="fw-semibold text-dark mb-1 d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-folder-tree text-primary" style="font-size: 0.72rem;"></i>
                            <span>{{ $item['title'] }}</span>
                        </div>
                        @if(!empty($item['children']))
                            <ul class="list-unstyled ps-3 mb-1">
                                @foreach($item['children'] as $child)
                                    <li class="py-0.5 d-flex align-items-baseline gap-1" style="line-height: 1.35; font-size: 0.76rem;">
                                        <span class="text-secondary me-1" style="font-size: 0.65rem;">&bull;</span>
                                        <span class="text-dark">{{ $child['text'] }}</span>
                                        @if(!empty($child['score']))
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace ms-auto text-nowrap px-1.5 py-0.5" style="font-size: 0.65rem;">
                                                {{ $child['score'] }}
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @elseif($item['type'] === 'bullet')
                    <div class="tieu-chi-bullet ps-2 py-0.5 d-flex align-items-baseline gap-1" style="line-height: 1.35; font-size: 0.78rem;">
                        <span class="text-secondary me-1" style="font-size: 0.65rem;">&bull;</span>
                        <span class="text-dark">{{ $item['text'] }}</span>
                        @if(!empty($item['score']))
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace ms-auto text-nowrap px-1.5 py-0.5" style="font-size: 0.65rem;">
                                {{ $item['score'] }}
                            </span>
                        @endif
                    </div>
                @else
                    <div class="ps-2 py-0.5 text-secondary" style="font-size: 0.78rem;">
                        {{ $item['text'] }}
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
