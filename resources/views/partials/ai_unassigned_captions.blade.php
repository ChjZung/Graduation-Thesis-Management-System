@php
    $unassignedCaptions = array_filter($aiData['caption_index']['assets'] ?? [], fn($asset) => empty($asset['section_id']));
@endphp
@if($unassignedCaptions)
    <details class="ai-review-disclosure">
        <summary>Tên hình/bảng chưa xác định mục</summary>
        <p class="small text-muted">Chỉ nhận diện tên; giảng viên vui lòng kiểm chứng trong PDF gốc.</p>
        @foreach($unassignedCaptions as $asset)
            <p>{{ $asset['display_text'] }} · Trang PDF {{ $asset['page'] }}
                <a href="{{ $pdfUrl }}#page={{ (int)$asset['page'] }}" target="_blank" rel="noopener">Mở PDF</a>
            </p>
        @endforeach
    </details>
@endif
