@extends('front.layouts.app')

@section('content')
<main class="container py-5">
    <h1>Seguridad</h1>
    <p class="text-muted">Contenido legal de Zonix Glasses (revisar antes de producción).</p>
    <p>Autenticación vía Sanctum, tokens seguros y buenas prácticas OWASP recomendadas para tu despliegue.</p>
    <p><a href="{{ route('front.home') }}">Volver al inicio</a></p>
</main>
@endsection
