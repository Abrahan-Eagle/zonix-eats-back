<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seo = \App\Helpers\SeoHelper::meta();
    @endphp
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <link rel="canonical" href="{{ $seo['url'] }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:url" content="{{ $seo['url'] }}">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seo['url'] }}">
    <meta property="twitter:title" content="{{ $seo['title'] }}">
    <meta property="twitter:description" content="{{ $seo['description'] }}">
    <meta property="twitter:image" content="{{ $seo['image'] }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
    
    <!-- Bootstrap 5 -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Fonts -->
    <link href="{{ asset('vendor/google-fonts/plus-jakarta-sans.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/material-symbols/material-symbols-outlined.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    
    <!-- Custom Semantic CSS -->
    <link href="{{ asset('css/zonix.css') }}" rel="stylesheet">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
        {!! \App\Helpers\SeoHelper::jsonLd() !!}
    </script>
</head>
<body>
    <div id="content-wrapper">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/zonix.js') }}"></script>
</body>
</html>
