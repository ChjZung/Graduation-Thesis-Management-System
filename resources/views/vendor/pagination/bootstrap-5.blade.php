@if ($paginator->total() > 0)
<nav class="d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 w-100">
    <div class="d-flex align-items-center">
        <span class="small text-muted">
            Hiển thị <span class="fw-bold text-dark">{{ $paginator->firstItem() ?? 0 }}</span> 
            đến <span class="fw-bold text-dark">{{ $paginator->lastItem() ?? 0 }}</span> 
            trên tổng số <span class="fw-bold text-primary">{{ $paginator->total() }}</span> bản ghi
            <span class="text-secondary">(Tối đa 5 bản ghi/trang &mdash; Trang {{ $paginator->currentPage() }} / {{ max(1, $paginator->lastPage()) }})</span>
        </span>
    </div>

    @if ($paginator->hasPages())
    <div>
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Trang trước">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Trang sau">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>
            @endif
        </ul>
    </div>
    @endif
</nav>
@endif
