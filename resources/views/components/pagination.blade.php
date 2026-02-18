{{-- Componente reutilizable de paginación --}}
@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="justify-content-center d-flex">
        <ul class="pagination">
            {{-- Botón Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Previous" tabindex="-1">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
            @endif

            {{-- Página anterior --}}
            @if (!$paginator->onFirstPage())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                        {{ $paginator->currentPage() - 1 }}
                    </a>
                </li>
            @endif

            {{-- Página actual --}}
            <li class="page-item active">
                <a class="page-link" href="#">
                    {{ $paginator->currentPage() }}
                </a>
            </li>

            {{-- Página siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                        {{ $paginator->currentPage() + 1 }}
                    </a>
                </li>
            @endif

            {{-- Botón Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Next" tabindex="-1">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
@endif

