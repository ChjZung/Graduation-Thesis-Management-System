@forelse($findings as $finding)
    @if($loop->first)<ul>@endif
    <li>
        @if($finding['summary'])
            {{ $finding['summary'] }}
        @endif
        <details>
            <summary>{{ $finding['summary'] ? 'Xem câu nguồn' : 'Xem câu nguồn đã lưu · Chưa có bản diễn đạt' }}</summary>
            <p>{{ $finding['quote'] }}</p>
        </details>
    </li>
    @if($loop->last)</ul>@endif
@empty
    <p class="ai-muted">{{ $emptyFindingsText ?? 'Chưa xác định từ phần văn bản đã đọc.' }}</p>
@endforelse
