@if ($paginator->hasPages())
    <nav aria-label="Paginación de servicios">
        <ul class="pagination flex-wrap gap-1 mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->previousPageUrl() ?: '#' }}" aria-label="Página anterior">&laquo;</a>
            </li>
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
            @endforeach
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $paginator->nextPageUrl() ?: '#' }}" aria-label="Página siguiente">&raquo;</a>
            </li>
        </ul>
    </nav>
@endif
