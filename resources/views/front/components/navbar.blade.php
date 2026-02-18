<!-- Navbar mejorada -->
<nav id="header" class="navbar navbar-expand-md fixed-top slide-down">
    <div class="container">
        <a class="navbar-brand" href="#inicio" aria-label="Corral X - Inicio">
            <img src="{{ asset('assets/front/images/LOGO_CORRAL.png') }}" alt="Corral X - Logo del marketplace ganadero de Venezuela">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#caracteristicas" aria-label="Ir a sección de características del marketplace ganadero">Características</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#beneficios" aria-label="Ir a sección de beneficios para ganaderos">Beneficios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#como-funciona" aria-label="Ir a sección de cómo funciona Corral X">¿Cómo funciona?</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#faq" aria-label="Ir a preguntas frecuentes sobre el marketplace ganadero">Preguntas Frecuentes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#descargar" aria-label="Ir a sección de descarga de la app Corral X">Descargar</a>
                </li>
                @auth
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light ms-2 px-3" href="{{ route('dashboard') }}" aria-label="Ir al dashboard de Corral X">
                        Dashboard
                    </a>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

