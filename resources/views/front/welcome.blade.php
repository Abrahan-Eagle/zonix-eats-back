@extends('front.layouts.app-front')

@section('content')
    @include('front.components.navbar')

    <div class="position-relative overflow-hidden no-pt">
        <!-- Hero Section mejorada -->
        <section id="inicio" class="section-lg">
            <div class="container text-center hero-content">
                <div class="row justify-content-center align-items-center">
                    <div class="col-lg-7 col-md-12">
                        <div class="text-center text-lg-start">
                            <span class="badge badge-custom rounded-pill fw-semibold mb-4 fade-in-up delay-100">
                                La nueva era de la ganadería
                            </span>
                            <h1 class="display-4 fw-bolder mb-4 fade-in-up delay-200">
                                Conecta, Compra y Vende Ganado con Facilidad
                            </h1>
                            <p class="lead mb-5 fade-in-up delay-300">
                                Corral X es la plataforma digital que une a ganaderos de toda Venezuela. Encuentra los
                                mejores ejemplares o publica tu rebaño para miles de compradores.
                            </p>
                            <div
                                class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start align-items-center gap-3 fade-in-up delay-400">
                                <a href="#descargar" class="download-link btn-shine" aria-label="Descargar Corral X en App Store para iOS">
                                    <img class="store-badge"
                                        src="{{ asset('assets/front/images/badges/app-store-badge.png') }}"
                                        alt="Descargar app Corral X marketplace ganadero en App Store" loading="lazy"
                                        width="135" height="45">
                                </a>
                                <a href="https://play.google.com/store/apps/details?id=com.corralx.app" target="_blank" class="download-link btn-shine" aria-label="Descargar Corral X en Google Play para Android">
                                    <img class="store-badge"
                                        src="{{ asset('assets/front/images/badges/google-play-badge.png') }}"
                                        alt="Descargar app Corral X marketplace ganadero en Google Play Store" loading="lazy"
                                        width="135" height="45">
                                </a>
                                <a href="#descargar" class="download-link btn-shine" aria-label="Descargar Corral X en Microsoft Store">
                                    <img class="store-badge"
                                        src="{{ asset('assets/front/images/badges/microsoft-store-badge.png') }}"
                                        alt="Descargar app Corral X marketplace ganadero en Microsoft Store" loading="lazy"
                                        width="135" height="45">
                                </a>
                            </div>
                            <div class="mt-4 fade-in-up delay-400">
                                <p class="small mb-2 fw-medium" style="opacity: 0.7;">Recibe novedades y acceso anticipado:</p>
                                <form id="emailFormHero" class="email-capture mx-auto mx-lg-0">
                                    <input type="email" class="form-control" placeholder="tu@correo.com" required aria-label="Tu correo electrónico">
                                    <button type="submit" class="btn-notify">Notificarme</button>
                                </form>
                                <div id="emailSuccessHero" class="email-success mt-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    ¡Listo! Te notificaremos.
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Phone Mockup Section -->
                    <div class="col-lg-5 mt-5 mt-lg-0">
                        <div class="zoom-in phone-mockup-wrapper mx-auto">
                            <div class="phone-mockup">
                                <div class="phone-screen">
                                    <img src="{{ asset('assets/front/images/images/images/phone-mockupx.jpeg') }}"
                                        alt="Captura de pantalla de la app Corral X mostrando el marketplace ganadero con listado de ganado bovino, bufalino, equino y porcino disponible en Venezuela"
                                        width="250" height="500" loading="eager">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Scroll Indicator -->
            <a href="#caracteristicas" class="scroll-indicator" aria-label="Scroll para ver más">
                <span>Explorar</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 13 12 18 17 13"></polyline><polyline points="7 6 12 11 17 6"></polyline></svg>
            </a>
        </section>
    </div>

    <main>
        @include('front.components.features-section')
        @include('front.components.stats-section')
        @include('front.components.benefits-section')
        @include('front.components.how-it-works')
        @include('front.components.partners-section')
        @include('front.components.faq-section')
        @include('front.components.download-section')
    </main>

    @include('front.components.footer')
@endsection

