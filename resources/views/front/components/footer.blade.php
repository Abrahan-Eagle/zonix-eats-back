<!-- Footer Multi-columna -->
<footer>
    <div class="container">
        <div class="row g-4">
            <!-- Brand + Descripción -->
            <div class="col-lg-4 col-md-6">
                <img src="{{ asset('assets/front/images/LOGO_CORRAL.png') }}" alt="Corral X - Marketplace ganadero de Venezuela" style="height: 28px; filter: brightness(0) invert(1);" class="mb-3">
                <p class="footer-brand-desc">
                    La primera plataforma digital de Venezuela dedicada al agro. Conectamos ganaderos de todo el país para comprar y vender ganado de forma directa y segura.
                </p>
                <!-- Redes Sociales -->
                <div class="d-flex gap-2 mt-3 justify-content-center justify-content-lg-start">
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="social-link" aria-label="Síguenos en Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/corral__x/?igsh=MTBvdWprdngwcW1ydA%3D%3D#" target="_blank" rel="noopener noreferrer"
                       class="social-link" aria-label="Síguenos en Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" target="_blank" rel="noopener noreferrer"
                       class="social-link" aria-label="Síguenos en X (Twitter)">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                </div>
            </div>

            <!-- Enlaces Rápidos -->
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Navegación</h6>
                <ul class="footer-links">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#caracteristicas">Características</a></li>
                    <li><a href="#beneficios">Beneficios</a></li>
                    <li><a href="#como-funciona">¿Cómo funciona?</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">Legal</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('pages.privacy') }}">Política de Privacidad</a></li>
                    <li><a href="{{ route('pages.terms') }}">Términos y Condiciones</a></li>
                    <li><a href="{{ route('pages.delete-account') }}">Eliminar cuenta</a></li>
                </ul>
            </div>

            <!-- Contacto -->
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">Contacto</h6>
                <div class="footer-contact-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:soporte@corralx.com">soporte@corralx.com</a>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-bullhorn"></i>
                    <a href="mailto:publicidad@corralx.com">publicidad@corralx.com</a>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Venezuela</span>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer-bottom text-center">
            <p class="text-muted small mb-0">&copy; {{ date('Y') }} CorralX. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
