@extends('layouts.app')
@auth
@section('title','Dashboard - Zonix Eats')

@section('content')
<main class="c-main">
    <div class="container-fluid">
        <div id="ui-view">
            <div class="fade-in">
                <!-- Header -->
                <div class="mb-4">
                    <h1 class="h3 mb-0">Dashboard</h1>
                    <p class="text-muted">Resumen general de Zonix Eats</p>
                </div>

                <!-- Estadísticas principales -->
                <div class="row">
                    <!-- Usuarios -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="text-value-lg">{{ number_format($stats['users']) }}</div>
                                <div>Usuarios Totales</div>
                                <div class="progress progress-white progress-xs my-2">
                                    <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="text-muted">Registrados en la plataforma</small>
                            </div>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="text-value-lg">{{ number_format($stats['roles']) }}</div>
                                <div>Roles Configurados</div>
                                <div class="progress progress-white progress-xs my-2">
                                    <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="text-muted">Sistema de permisos</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="row mt-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Acciones Rápidas</h6>
                                <div class="d-flex flex-column gap-2">
                                    @can('haveaccess', 'users.index')
                                    <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">
                                        <svg class="c-icon">
                                            <use xlink:href="{{asset('icons/sprites/free.svg#cil-people')}}"></use>
                                        </svg> Gestionar Usuarios
                                    </a>
                                    @endcan
                                    @can('haveaccess', 'roles.index')
                                    <a href="{{ route('roles.index') }}" class="btn btn-success btn-sm text-white">
                                        <svg class="c-icon">
                                            <use xlink:href="{{asset('icons/sprites/free.svg#cil-user')}}"></use>
                                        </svg> Gestionar Roles
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablas de información reciente -->
                <div class="row">
                    <!-- Usuarios recientes -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>Usuarios Recientes</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-responsive-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Registrado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recent_users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay usuarios recientes</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@endauth
