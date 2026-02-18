@extends('front.layouts.app-front')

@section('page_title', 'Política de Privacidad — Corral X Marketplace Ganadero')

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
                <span>Política de Privacidad</span>
            </nav>

            <div class="legal-hero">
                <h1>Política de Privacidad</h1>
                <p class="legal-date">Fecha de entrada en vigor: 18 de noviembre de 2025</p>
            </div>

            <div class="legal-content">
                <p>Esta Política de Privacidad describe cómo la aplicación y el sitio web <strong>Corral X</strong> (en adelante, "la Aplicación" o "nosotros") recopilan, usan y protegen la información personal de sus usuarios.</p>

                <h2>1. Recopilación y Uso de la Información</h2>
                <p>Recopilamos información para proporcionar y mejorar nuestro servicio, incluyendo las funciones del marketplace, mensajería y perfiles.</p>
                <ul>
                    <li><strong>Datos de perfil:</strong> nombre, correo electrónico, foto de perfil, biografía y datos de finca/publicación que usted proporciona.</li>
                    <li><strong>Fotos y videos:</strong> imágenes de productos (ganado) que publica, selfies y documentos de identidad para verificación de identidad (KYC), y fotos de perfil.</li>
                    <li><strong>Ubicación:</strong> recopilamos su ubicación precisa (GPS) y aproximada (basada en red) para mostrar productos cercanos, filtrar búsquedas por ubicación y mejorar la experiencia del marketplace. Puede desactivar el acceso a la ubicación en la configuración de su dispositivo.</li>
                    <li><strong>Contenido de mensajes:</strong> los mensajes enviados a través del chat se almacenan para permitir la comunicación entre usuarios.</li>
                    <li><strong>Datos de actividad:</strong> acciones en la Aplicación (favoritos, reportes, interacciones).</li>
                </ul>

                <h2>2. Datos de uso y registro (automáticos)</h2>
                <p>Recopilamos datos sobre cómo accede y utiliza la Aplicación: tipo de dispositivo, sistema operativo, identificadores de dispositivo, dirección IP, registros de errores y diagnóstico.</p>
                <p><strong>Token de dispositivo para notificaciones:</strong> utilizamos Firebase Cloud Messaging (FCM) para enviar notificaciones push. Para ello, recopilamos un token único de dispositivo que nos permite enviarle notificaciones sobre mensajes, actualizaciones de productos y otras comunicaciones relevantes.</p>

                <h2>3. Bases legales</h2>
                <p>Procesamos sus datos con base en su consentimiento, para ejecutar el servicio (contrato) y por interés legítimo (seguridad, prevención de fraude y mejora del servicio).</p>

                <h2>4. Uso de servicios de terceros</h2>
                <p>Utilizamos los siguientes servicios de terceros que recopilan datos bajo sus propias políticas de privacidad:</p>
                <ul>
                    <li><strong>Google (Autenticación):</strong> para permitir el inicio de sesión con cuenta de Google. Google recopila información según su <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Política de Privacidad</a>.</li>
                    <li><strong>Firebase Cloud Messaging (FCM):</strong> servicio de Google para enviar notificaciones push. Compartimos el token de dispositivo y datos básicos de uso. Firebase procesa estos datos según la <a href="https://firebase.google.com/support/privacy" target="_blank" rel="noopener noreferrer">Política de Privacidad de Firebase</a>.</li>
                    <li><strong>Google Analytics (si aplica):</strong> para analizar el uso de la aplicación y mejorar nuestros servicios según la <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Política de Privacidad de Google</a>.</li>
                    <li><strong>Proveedores de hosting y servicios:</strong> utilizamos servicios de terceros para alojar nuestros servidores y procesar datos técnicos necesarios para el funcionamiento de la aplicación.</li>
                </ul>

                <h2>5. Compartición de datos</h2>
                <p>Podemos compartir datos con:</p>
                <ul>
                    <li><strong>Otros usuarios:</strong> información de publicación visible públicamente (fotos de productos, descripción, ubicación general, datos de contacto si usted lo permite).</li>
                    <li><strong>Proveedores de servicios:</strong> hosting, procesadores de pago (si aplica), y servicios técnicos necesarios para el funcionamiento de la aplicación.</li>
                    <li><strong>Google/Firebase:</strong> identificadores de dispositivo, datos de uso básicos y tokens para notificaciones push.</li>
                    <li><strong>Autoridades:</strong> cuando la ley lo requiera o para proteger nuestros derechos legales.</li>
                </ul>

                <h2>6. Permisos de la aplicación</h2>
                <p>La aplicación solicita los siguientes permisos:</p>
                <ul>
                    <li><strong>Cámara:</strong> para tomar fotos de productos (ganado) y para la verificación de identidad (KYC) mediante selfies y captura de documentos.</li>
                    <li><strong>Ubicación:</strong> para mostrar productos cercanos y filtrar búsquedas por ubicación. Puede desactivar este permiso en cualquier momento.</li>
                    <li><strong>Notificaciones:</strong> para enviarle alertas sobre mensajes recibidos, actualizaciones de productos y otras comunicaciones relevantes.</li>
                    <li><strong>Almacenamiento:</strong> para guardar temporalmente imágenes antes de subirlas y para mejorar el rendimiento mediante caché.</li>
                </ul>
                <div class="legal-highlight">
                    <strong>Nota:</strong> Puede revocar estos permisos en cualquier momento desde la configuración de su dispositivo, aunque esto puede afectar algunas funcionalidades de la aplicación.
                </div>

                <h2>7. Seguridad</h2>
                <p>Implementamos medidas razonables para proteger la información. Sin embargo, ningún sistema es 100% seguro; actúe con precaución al compartir información sensible.</p>

                <h2>8. Privacidad de menores</h2>
                <p>La Aplicación no está dirigida a menores de 18 años. No recopilamos conscientemente información de menores; si tiene conocimiento de ello, contáctenos para eliminar los datos.</p>

                <h2>9. Cambios a esta Política</h2>
                <p>Podemos actualizar esta Política periódicamente; los cambios serán efectivos cuando se publiquen en esta página. Revise la política con regularidad.</p>

                <h2>10. Contáctenos</h2>
                <p>Si tiene preguntas sobre esta Política de Privacidad, contáctenos en: <a href="mailto:soporte@corralx.com"><strong>soporte@corralx.com</strong></a></p>
            </div>
        </div>
    </div>

    @include('front.components.footer')
@endsection
