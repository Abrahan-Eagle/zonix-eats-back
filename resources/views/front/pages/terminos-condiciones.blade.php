@extends('front.layouts.app-front')

@section('page_title', 'Términos y Condiciones — Corral X Marketplace Ganadero')

@section('content')
    <!-- Navbar simplificada -->
    <nav id="header" class="navbar navbar-expand-md fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}" aria-label="Corral X - Inicio">
                <img src="{{ asset('assets/front/images/LOGO_CORRAL.png') }}" alt="Corral X - Marketplace ganadero de Venezuela">
            </a>
            <a href="{{ url('/') }}" class="legal-nav-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Volver al inicio
            </a>
        </div>
    </nav>

    <div class="legal-page">
        <div class="container">
            <nav class="legal-breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">Inicio</a>
                <span>/</span>
                <span>Términos y Condiciones</span>
            </nav>

            <div class="legal-hero">
                <h1>Términos y Condiciones</h1>
                <p class="legal-date">Última actualización: 19 de noviembre de 2025</p>
            </div>

            <div class="legal-content">
                <h2>1. Aceptación de los Términos</h2>
                <p>Al acceder y utilizar Corral X, aceptas cumplir con estos Términos y Condiciones. Si no estás de acuerdo con alguna parte de estos términos, no debes utilizar nuestros servicios.</p>

                <h2>2. Descripción del Servicio</h2>
                <p>Corral X es una plataforma digital que conecta a productores ganaderos, compradores y vendedores de ganado, facilitando transacciones comerciales en el sector agropecuario.</p>

                <h2>3. Registro y Cuenta de Usuario</h2>
                <p>Para utilizar nuestros servicios, debes crear una cuenta proporcionando información veraz y actualizada. Eres responsable de mantener la confidencialidad de tus credenciales y de todas las actividades que ocurran bajo tu cuenta.</p>

                <h2>4. Uso de la Plataforma</h2>
                <p>Te comprometes a utilizar Corral X de manera legal y ética. Está prohibido:</p>
                <ul>
                    <li>Publicar información falsa o engañosa</li>
                    <li>Realizar actividades fraudulentas</li>
                    <li>Infringir derechos de propiedad intelectual</li>
                    <li>Violar cualquier ley o regulación aplicable</li>
                </ul>

                <h2>5. Publicaciones y Contenido</h2>
                <p>Eres responsable del contenido que publiques en la plataforma. Corral X se reserva el derecho de moderar, editar o eliminar cualquier contenido que viole estos términos o las políticas de la plataforma.</p>

                <h2>6. Transacciones Comerciales</h2>
                <p>Corral X actúa como intermediario facilitando la conexión entre compradores y vendedores. No somos parte de las transacciones comerciales ni garantizamos la calidad de los productos. Las transacciones son responsabilidad exclusiva de las partes involucradas.</p>
                <div class="legal-highlight">
                    <strong>Importante:</strong> Corral X no procesa pagos ni retiene dinero. Todos los acuerdos económicos se realizan directamente entre las partes.
                </div>

                <h2>7. Privacidad y Protección de Datos</h2>
                <p>El manejo de tus datos personales se rige por nuestra <a href="{{ route('pages.privacy') }}">Política de Privacidad</a>. Al utilizar nuestros servicios, aceptas la recopilación y uso de información según se describe en dicha política.</p>

                <h2>8. Propiedad Intelectual</h2>
                <p>Todos los derechos de propiedad intelectual sobre la plataforma, incluyendo diseño, logos, y contenido, son propiedad de Corral X. No puedes reproducir, distribuir o crear obras derivadas sin autorización previa.</p>

                <h2>9. Limitación de Responsabilidad</h2>
                <p>Corral X no se hace responsable por daños directos, indirectos, incidentales o consecuentes derivados del uso o la imposibilidad de usar nuestros servicios.</p>

                <h2>10. Modificaciones de los Términos</h2>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios entrarán en vigor al ser publicados en la plataforma. Es tu responsabilidad revisar periódicamente estos términos.</p>

                <h2>11. Terminación</h2>
                <p>Podemos suspender o terminar tu cuenta en cualquier momento si violas estos términos o cualquier política de la plataforma.</p>

                <h2>12. Ley Aplicable</h2>
                <p>Estos términos se rigen por las leyes de Venezuela. Cualquier disputa será resuelta en los tribunales competentes del país.</p>

                <h2>13. Contacto</h2>
                <p>Para consultas sobre estos términos, puedes contactarnos a través de los canales de soporte disponibles en la plataforma o escribiendo a <a href="mailto:soporte@corralx.com"><strong>soporte@corralx.com</strong></a>.</p>
            </div>
        </div>
    </div>

    @include('front.components.footer')
@endsection
