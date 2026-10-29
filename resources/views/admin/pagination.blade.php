@if ($paginator->hasPages())
    @php
        $first = max(1, $paginator->currentPage() - 2);
        $last = min($paginator->lastPage(), $paginator->currentPage() + 2);
    @endphp
    <nav class="admin-pagination" aria-label="Pagination">
        <span>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div>
            @if ($paginator->onFirstPage())
                <span class="disabled">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}">Previous</a>
            @endif
            @for ($page = $first; $page <= $last; $page++)
                @if ($page === $paginator->currentPage())
                    <span class="current">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
                @endif
            @endfor
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next</a>
            @else
                <span class="disabled">Next</span>
            @endif
        </div>
    </nav>
@endif
