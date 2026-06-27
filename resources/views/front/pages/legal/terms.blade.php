@extends('front.layouts.app')

@section('content')
<main class="container py-5">
    <h1>Términos y condiciones</h1>
    <p class="text-muted">Contenido legal de Zonix Glasses (revisar antes de producción). </p>
    <p>Al usar Zonix Glasses aceptas las políticas de tu organización y la legislación aplicable.</p>
    <p><a href="{{ route('front.home') }}">Volver al inicio</a></p>
</main>
@endsection
