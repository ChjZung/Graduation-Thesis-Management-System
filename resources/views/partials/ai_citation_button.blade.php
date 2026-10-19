@if($citationAvailable && (int) $citationPage > 0)
    <button type="button" class="ai-tag ai-citation-button {{ !empty($citationProminent) ? 'ai-evidence-link' : '' }}" data-ai-action="citation" data-citation-page="{{ (int) $citationPage }}" data-citation-quote="{{ (string) $citationQuote }}" data-citation-context="{{ $citationContext ?? '' }}" aria-haspopup="dialog" title="Mở đúng trang PDF, tự cuộn tới và tô vàng đoạn làm bằng chứng">
        <i class="fa-regular fa-file-lines" aria-hidden="true"></i>{{ !empty($citationProminent) ? 'Xem bằng chứng trong PDF · Trang ' : 'Trang ' }}{{ (int) $citationPage }} <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
    </button>
@else
    <span class="ai-tag">Trang {{ (int) $citationPage }}</span>
@endif
