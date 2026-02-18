{{-- Componente reutilizable para tablas de datos --}}
@props([
    'items',
    'columns' => [],
    'showRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'routeParams' => [],
    'emptyMessage' => 'No hay registros disponibles'
])

<div class="row">
    <div class="col-sm-12">
        <table class="table table-striped table-bordered datatable dataTable no-footer"
            id="DataTables_Table_0" role="grid"
            aria-describedby="DataTables_Table_0_info"
            style="border-collapse: collapse !important">
            <thead>
                <tr role="row">
                    @foreach ($columns as $column)
                        <th width="{{ $column['width'] ?? 'auto' }}">{{ $column['label'] }}</th>
                    @endforeach
                    @if($showRoute || $editRoute || $deleteRoute)
                        <th width="40%">Acción</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr role="row" style="vertical-align:middle; text-align: center;">
                        @foreach ($columns as $column)
                            <td>{{ $item->{$column['field']} ?? '' }}</td>
                        @endforeach
                        @if($showRoute || $editRoute || $deleteRoute)
                            <td>
                                <x-action-buttons
                                    :showRoute="$showRoute"
                                    :editRoute="$editRoute"
                                    :deleteRoute="$deleteRoute"
                                    :showParams="array_merge($routeParams, ['id' => $item->id])"
                                    :editParams="array_merge($routeParams, ['id' => $item->id])"
                                    :deleteParams="array_merge($routeParams, ['id' => $item->id])"
                                />
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($showRoute || $editRoute || $deleteRoute ? 1 : 0) }}" class="text-center">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

