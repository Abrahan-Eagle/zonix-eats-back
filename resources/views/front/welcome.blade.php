@extends('front.layouts.app')

@section('content')
<main class="container py-5">
    <div class="text-center py-5">
        <h1 class="display-5 fw-bold mb-3">Zonix Glasses</h1>
        <p class="lead text-muted mb-4">
            Boilerplate Laravel + Flutter: autenticación, perfiles, notificaciones y administración de usuarios.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Iniciar sesión</a>
            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">Registrarse</a>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Auth + RBAC</h5>
                    <p class="card-text text-muted">Sanctum, Google login y roles <code>user</code> / <code>admin</code>.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Perfiles</h5>
                    <p class="card-text text-muted">Direcciones, teléfonos, documentos y geo (Country/State/City).</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Notificaciones</h5>
                    <p class="card-text text-muted">Push FCM y tiempo real con Pusher.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted mt-5 pt-4 border-top">
        <p class="mb-1">&copy; {{ date('Y') }} Zonix Glasses</p>
        <p class="small mb-0">
            <a href="{{ route('pages.terms') }}">Términos</a> ·
            <a href="{{ route('pages.privacy') }}">Privacidad</a> ·
            <a href="{{ route('pages.cookies') }}">Cookies</a>
        </p>
    </footer>
</main>
@endsection
