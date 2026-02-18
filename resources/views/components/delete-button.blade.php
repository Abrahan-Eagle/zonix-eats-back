{{-- Componente para botón de eliminación con confirmación --}}
@props([
    'route',
    'params' => [],
    'icon' => 'cil-trash',
    'confirmMessage' => '¿Estás seguro de que deseas eliminar este elemento?'
])

<form method="POST" action="{{ route($route, $params) }}" class="d-inline">
    @csrf
    @method('DELETE')
    <button 
        type="submit" 
        class="btn btn-danger" 
        title="Eliminar"
        onclick="return confirm('{{ $confirmMessage }}');"
    >
        <svg class="c-icon">
            <use xlink:href="{{ asset('icons/sprites/free.svg#' . $icon) }}"></use>
        </svg>
    </button>
</form>

