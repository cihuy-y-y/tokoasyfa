<nav class="pager">
    @if ($paginator->onFirstPage())
        <span class="pg disabled">‹</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="pg">‹</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pg disabled">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pg active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="pg">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="pg">›</a>
    @else
        <span class="pg disabled">›</span>
    @endif
</nav>