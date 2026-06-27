@extends('front.layouts.app')

@section('content')
<main class="container py-5">
    <h1>Política de privacidad</h1>
    <p class="text-muted">Contenido legal de Zonix Glasses (revisar antes de producción). </p>
    <p>Recopilamos datos de cuenta y perfil necesarios para autenticación, notificaciones y administración.</p>
    <p><a href="{{ route('front.home') }}">Volver al inicio</a></p>
</main>
@endsection
