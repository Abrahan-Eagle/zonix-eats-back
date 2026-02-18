<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>@yield('page_title', 'Corral X - El Marketplace Ganadero de Venezuela')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Marketplace ganadero de Venezuela. Compra y vende ganado bovino, bufalino, equino y porcino. Equipos de hacienda y maquinaria agrícola. Conecta con ganaderos sin intermediarios.">
    <meta name="keywords" content="ganado venezuela, comprar ganado, vender ganado, marketplace ganadero, equipos de hacienda, maquinaria agrícola, ganadería venezuela, bovinos, bufalinos, equinos, porcinos, tractores ganaderos, ordeñadoras, cercas eléctricas, bebederos ganaderos, insumos agrícolas, transporte ganadero, fincas venezuela, hatos ganaderos">
    <meta name="author" content="Corral X">
    @if(str_contains(request()->getHost(), 'test.corralx.com'))
    <!-- NO INDEXAR: Entorno de testing -->
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">
    @else
    <!-- SEO: Producción - Indexar y seguir -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <link rel="canonical" href="https://corralx.com{{ request()->getPathInfo() }}">
    <link rel="alternate" hreflang="es-ve" href="https://corralx.com{{ request()->getPathInfo() }}">
    <link rel="alternate" hreflang="es" href="https://corralx.com{{ request()->getPathInfo() }}">
    <link rel="alternate" hreflang="x-default" href="https://corralx.com{{ request()->getPathInfo() }}">
    @endif
    <link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Corral X - El Marketplace Ganadero de Venezuela">
    <meta property="og:description" content="Compra y vende ganado, equipos de hacienda y maquinaria agrícola. Conecta con ganaderos de toda Venezuela sin intermediarios. Análisis de mercado con IA.">
    <meta property="og:image" content="{{ url('assets/front/images/LOGO_CORRAL.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="es_VE">
    <meta property="og:site_name" content="Corral X">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="Corral X - Marketplace Ganadero de Venezuela">
    <meta name="twitter:description" content="Compra y vende ganado, equipos de hacienda y maquinaria agrícola. Conecta con ganaderos de toda Venezuela.">
    <meta name="twitter:image" content="{{ url('assets/front/images/LOGO_CORRAL.png') }}">
    <meta name="twitter:creator" content="@corralx">

    <!-- Additional SEO -->
    <meta name="geo.region" content="VE">
    <meta name="geo.placename" content="Venezuela">
    <meta name="language" content="Spanish">
    <meta name="revisit-after" content="7 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">
    
    <!-- SGE (Search Generative Experience) - Rich Snippets -->
    <meta name="application-name" content="Corral X">
    <meta name="apple-mobile-web-app-title" content="Corral X">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- ASO (App Store Optimization) -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#386A20">
    <meta name="msapplication-TileColor" content="#386A20">
    <meta name="msapplication-config" content="{{ asset('assets/front/images/Favicon/browserconfig.xml') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (para iconos de redes sociales) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css?v=6.5.1" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Google Fonts: SF Pro alternative (Inter for web) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"></noscript>

    <!-- Favicons (local) -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/front/images/Favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/front/images/Favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/front/images/Favicon/favicon-96x96.png') }}">
    <link rel="icon" href="{{ asset('assets/front/images/Favicon/favicon.ico') }}" type="image/x-icon">
    <link rel="mask-icon" href="{{ asset('assets/front/images/Favicon/favicon.svg') }}" color="#386A20">
    <link rel="manifest" href="{{ asset('assets/front/images/Favicon/site.webmanifest') }}">
    <meta name="theme-color" content="#386A20">

    <!-- Preload hero image para LCP -->
    <link rel="preload" href="{{ asset('assets/front/images/images/images/phone-mockupx.jpeg') }}" as="image">

    <!-- Frontend CSS -->
    <link rel="stylesheet" href="{{ mix('css/front.css') }}">

    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" role="status" aria-live="polite" aria-label="Cargando Corral X">
        <div class="spinner" aria-hidden="true"></div>
    </div>

    <!-- Liquid Glass Background - iOS 18 Style -->
    <div class="liquid-background">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    @yield('content')

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/584227158087?text=Hola%2C%20quiero%20información%20sobre%20Corral%20X"
       target="_blank" rel="noopener noreferrer"
       class="whatsapp-float"
       aria-label="Contáctanos por WhatsApp"
       title="¿Necesitas ayuda? Escríbenos por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Schema.org Structured Data for SEO & SGE -->
    @include('front.components.schema-org')

    <!-- JavaScript - Liquid Glass Interactions -->
    @include('front.components.front-js')

    @stack('scripts')
</body>

</html>

