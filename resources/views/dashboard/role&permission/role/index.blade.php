@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>
                        Lista de Roles
                        <a class="btn btn-primary float-right" href="{{ route('roles.create') }}">Crear</a>
                    </h3>
                </div>
                <div class="card-body">
                    @include('dashboard.role&permission.custom.message')

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Slug</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Acceso Completo</th>
                                <th colspan="3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $rol)
                                <tr>
                                    <td scope="row">{{ $rol->id }}</td>
                                    <td>{{ $rol->name }}</td>
                                    <td>{{ $rol->slug }}</td>
                                    <td>{{ $rol->description }}</td>
                                    <td>{{ $rol['full-access'] }}</td>
                                    @can('haveaccess', 'roles.show')
                                        <td>
                                            <a class="btn btn-primary" href="{{ route('roles.show', $rol->id) }}">Ver</a>
                                        </td>
                                    @endcan
                                    @can('haveaccess', 'roles.edit')
                                        <td>
                                            <a class="btn btn-success" href="{{ route('roles.edit', $rol->id) }}">Editar</a>
                                        </td>
                                    @endcan
                                    @can('haveaccess', 'roles.destroy')
                                        <td>
                                            <x-delete-button 
                                                route="roles.destroy" 
                                                :params="['role' => $rol->id]"
                                                confirmMessage="¿Estás seguro de eliminar este rol?"
                                            />
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay roles registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <x-pagination :paginator="$roles" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
