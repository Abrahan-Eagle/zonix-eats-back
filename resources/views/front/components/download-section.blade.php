<!-- CTA Section con captura de email -->
<section id="descargar" class="cta-section">
    <div class="container text-center cta-inner">
        <div class="mx-auto max-w-700">
            <h2 class="fw-bold mb-4 fade-in-up">Únete a la Comunidad de Corral X Hoy</h2>
            <p class="lead mb-4 fade-in-up">
                Sé parte de la revolución ganadera. Descarga la app y transforma la manera en que haces negocios.
            </p>

            <!-- Email Capture Form -->
            <div class="mb-4 fade-in-up">
                <p class="small mb-3 fw-semibold" style="opacity: 0.75;">Recibe novedades y acceso anticipado:</p>
                <form id="emailFormCta" class="email-capture mx-auto">
                    <input type="email" class="form-control" placeholder="tu@correo.com" required aria-label="Tu correo electrónico">
                    <button type="submit" class="btn-notify">Notificarme</button>
                </form>
                <div id="emailSuccessCta" class="email-success mt-3 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -3px; margin-right: 6px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    ¡Listo! Te avisaremos cuando haya novedades.
                </div>
            </div>

            <!-- Store Badges -->
            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3 fade-in-up">
                <a href="#!" class="opacity-75 download-link btn-shine" aria-label="Descargar Corral X en App Store">
                    <img class="store-badge"
                        src="{{ asset('assets/front/images/badges/app-store-badge.png') }}"
                        alt="Descargar app Corral X marketplace ganadero en App Store" loading="lazy">
                </a>
                <a href="https://play.google.com/store/apps/details?id=com.corralx.app" target="_blank" class="download-link btn-shine" aria-label="Descargar Corral X en Google Play">
                    <img class="store-badge"
                        src="{{ asset('assets/front/images/badges/google-play-badge.png') }}"
                        alt="Descargar app Corral X marketplace ganadero en Google Play Store" loading="lazy">
                </a>
                <a href="#!" class="opacity-75 download-link btn-shine" aria-label="Descargar Corral X en Microsoft Store">
                    <img class="store-badge"
                        src="{{ asset('assets/front/images/badges/microsoft-store-badge.png') }}"
                        alt="Descargar app Corral X marketplace ganadero en Microsoft Store" loading="lazy">
                </a>
            </div>
            <p class="opacity-75 small mt-2 fade-in-up">App Store y Microsoft Store: Próximamente</p>
        </div>
    </div>
</section>
