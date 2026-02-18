@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Lista de Usuarios</h3>
                </div>
                <div class="card-body">
                    @include('dashboard.role&permission.custom.message')

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Email</th>
                                <th scope="col">Rol</th>
                                <th colspan="3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td scope="row">{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->description ?? 'Sin rol' }}</td>
                                    @can('haveaccess', 'users.show')
                                        <td>
                                            <a class="btn btn-primary" href="{{ route('users.show', $user->id) }}">Ver</a>
                                        </td>
                                    @endcan
                                    @can('haveaccess', 'users.edit')
                                        <td>
                                            <a class="btn btn-success" href="{{ route('users.edit', $user->id) }}">Editar</a>
                                        </td>
                                    @endcan
                                    @can('haveaccess', 'users.destroy')
                                        <td>
                                            <x-delete-button 
                                                route="users.destroy" 
                                                :params="['user' => $user->id]"
                                                confirmMessage="¿Estás seguro de eliminar este usuario?"
                                            />
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay usuarios registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <x-pagination :paginator="$users" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
