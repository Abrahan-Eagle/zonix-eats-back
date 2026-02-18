@extends('front.layouts.app-front')

@section('page_title', 'Eliminar Cuenta y Datos — Corral X')

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
                <span>Eliminar cuenta</span>
            </nav>

            <div class="legal-hero">
                <h1>Eliminar Cuenta y Datos</h1>
                <p class="legal-date">En esta página se explica cómo puedes eliminar tu cuenta de Corral X y qué ocurre con tus datos.</p>
            </div>

            <div class="legal-columns">
                <!-- Español -->
                <div class="legal-column">
                    <div class="legal-lang-label">ES · Español</div>

                    <div class="legal-card">
                        <div class="card-title">Cómo eliminar tu cuenta desde la app</div>
                        <p>Puedes eliminar tu cuenta de Corral X directamente desde la app móvil. Una vez eliminada, perderás acceso a tu perfil y a todos tus datos asociados.</p>
                        <ol>
                            <li>Abre la app <strong>Corral X</strong> en tu dispositivo.</li>
                            <li>Ve a <strong>Perfil → Configuración → Eliminar cuenta</strong>.</li>
                            <li>Revisa el aviso de eliminación y confirma tu decisión.</li>
                        </ol>
                        <div class="legal-highlight warning">
                            <strong>Importante:</strong> la eliminación de cuenta es permanente y no puede deshacerse. Asegúrate de haber guardado cualquier información importante antes de continuar.
                        </div>
                    </div>

                    <div class="legal-card">
                        <div class="card-title">Qué datos se eliminan</div>
                        <p>Cuando solicitas la eliminación de tu cuenta, se elimina de forma permanente lo siguiente:</p>
                        <ul>
                            <li>Información de perfil (nombre, correo electrónico, teléfono).</li>
                            <li>Perfiles de fincas / hatos registrados en Corral X.</li>
                            <li>Anuncios, publicaciones y listados de ganado y servicios.</li>
                            <li>Fotos e imágenes que hayas subido a la plataforma.</li>
                            <li>Chats y mensajes enviados dentro de la app.</li>
                            <li>Favoritos, historial de búsquedas y elementos guardados.</li>
                            <li>Cualquier otra información asociada directamente a tu cuenta.</li>
                        </ul>
                    </div>

                    <div class="legal-card">
                        <div class="card-title">Datos que pueden conservarse temporalmente</div>
                        <p>Por motivos legales, de seguridad y prevención de fraude, algunos datos técnicos pueden conservarse durante un tiempo limitado:</p>
                        <ul>
                            <li>Registros técnicos y de seguridad relacionados con actividad sospechosa (hasta 180 días).</li>
                            <li>Registros de transacciones necesarios para auditorías internas (hasta 90 días).</li>
                        </ul>
                        <p style="color: var(--text-main); opacity: 0.6; font-size: 0.9rem;">Estos datos se conservan únicamente con fines legítimos, no se usan para publicidad y se protegen mediante medidas de seguridad apropiadas.</p>
                    </div>

                    <div class="legal-card">
                        <div class="card-title">Plazos y contacto de soporte</div>
                        <p>Una vez confirmada la eliminación desde la app, el proceso puede tardar entre <strong>7 y 30 días</strong> en completarse en todos nuestros sistemas.</p>
                        <p>Si necesitas ayuda adicional: <a href="mailto:soporte@corralx.com">soporte@corralx.com</a></p>
                    </div>
                </div>

                <!-- English -->
                <div class="legal-column">
                    <div class="legal-lang-label">EN · English</div>

                    <div class="legal-card">
                        <div class="card-title">How to delete your account</div>
                        <p>You can delete your Corral X account directly from within the mobile app. Once deleted, you will lose access to your profile and all associated data.</p>
                        <ol>
                            <li>Open the <strong>Corral X</strong> app on your device.</li>
                            <li>Go to <strong>Profile → Settings → Delete Account</strong>.</li>
                            <li>Review the deletion notice and confirm your decision.</li>
                        </ol>
                        <div class="legal-highlight danger">
                            <strong>Note:</strong> Account deletion is permanent and cannot be undone. Make sure you have saved any important information before proceeding.
                        </div>
                    </div>

                    <div class="legal-card">
                        <div class="card-title">Data that will be deleted</div>
                        <p>When you request account deletion, the following data will be permanently removed:</p>
                        <ul>
                            <li>Profile information (name, email address, phone number).</li>
                            <li>Registered farm/ranch profiles within Corral X.</li>
                            <li>Listings and posts for cattle and related services.</li>
                            <li>Photos and media you uploaded to the platform.</li>
                            <li>Chats and messages sent within the app.</li>
                            <li>Favorites, saved items and browsing preferences.</li>
                            <li>Any other information directly linked to your user account.</li>
                        </ul>
                    </div>

                    <div class="legal-card">
                        <div class="card-title">Data that may be retained temporarily</div>
                        <p>For legal, security and fraud-prevention purposes, some technical records may be retained for a limited period:</p>
                        <ul>
                            <li>Security and anti-fraud logs related to suspicious activity (up to 180 days).</li>
                            <li>Transaction and bookkeeping logs required for internal audits (up to 90 days).</li>
                        </ul>
                        <p style="color: var(--text-main); opacity: 0.6; font-size: 0.9rem;">These records are kept only for legitimate purposes, are not used for advertising, and are protected with appropriate security measures.</p>
                    </div>

                    <div class="legal-card">
                        <div class="card-title">Timing & support</div>
                        <p>After you confirm deletion from within the app, your account and its associated data will normally be deleted within <strong>7–30 days</strong>.</p>
                        <p>For any questions: <a href="mailto:soporte@corralx.com">soporte@corralx.com</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('front.components.footer')
@endsection
