@props(['items'])
@if ($items->hasPages())
    <nav aria-label="Stranice" class="pagination">
        @if ($items->previousPageUrl())
            <a class="button secondary compact" href="{{ $items->previousPageUrl() }}">Prethodna</a>
        @endif
        <span>Strana {{ $items->currentPage() }} od {{ $items->lastPage() }}</span>
        @if ($items->nextPageUrl())
            <a class="button secondary compact" href="{{ $items->nextPageUrl() }}">Sledeća</a>
        @endif
    </nav>
@endif
