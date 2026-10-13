{{-- Expects: $paginator (LengthAwarePaginator) --}}
<div class="pager">
    <span>
        @if ($paginator->total())
            Showing {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        @else
            No tickets
        @endif
    </span>
    <div class="actions">
        @if ($paginator->onFirstPage())
            <span class="btn" style="opacity:0.5">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn">Previous</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn">Next</a>
        @else
            <span class="btn" style="opacity:0.5">Next</span>
        @endif
    </div>
</div>
