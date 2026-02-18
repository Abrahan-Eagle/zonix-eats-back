{{-- Componente reutilizable para botones de acción (show/edit/delete) --}}
@props([
    'showRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'showParams' => [],
    'editParams' => [],
    'deleteParams' => [],
    'showIcon' => 'cil-description',
    'editIcon' => 'cil-description',
    'deleteIcon' => 'cil-trash'
])

<div class="row">
    <div class="col-sm-3"></div>
    
    @if($showRoute)
        <div class="col-sm-2">
            <a class="btn btn-dark" href="{{ route($showRoute, $showParams) }}" title="Ver detalles">
                <svg class="c-icon">
                    <use xlink:href="{{ asset('icons/sprites/free.svg#' . $showIcon) }}"></use>
                </svg>
            </a>
        </div>
    @endif
    
    @if($editRoute)
        <div class="col-sm-2">
            <a class="btn btn-info" href="{{ route($editRoute, $editParams) }}" title="Editar">
                <svg class="c-icon">
                    <use xlink:href="{{ asset('icons/sprites/free.svg#' . $editIcon) }}"></use>
                </svg>
            </a>
        </div>
    @endif
    
    @if($deleteRoute)
        <div class="col-sm-2">
            <x-delete-button 
                :route="$deleteRoute" 
                :params="$deleteParams"
                :icon="$deleteIcon"
            />
        </div>
    @endif
    
    <div class="col-sm-3"></div>
</div>

