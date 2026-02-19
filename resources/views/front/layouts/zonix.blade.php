<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zonix EATS - Delivery de Comida en Venezuela</title>
    <meta name="description" content="Pide comida a domicilio de tus restaurantes favoritos en Caracas, Maracaibo, Valencia y más. Los mejores precios y delivery rápido con Zonix EATS.">
    <meta name="keywords" content="delivery, comida, venezuela, zonix, hamburguesas, pizza, sushi, caracas, maracaibo">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://zonixeats.com/">
    <meta property="og:title" content="Zonix EATS - Tu comida favorita en minutos">
    <meta property="og:description" content="Pide comida a domicilio de los mejores restaurantes de tu zona. Entregas rápidas, ofertas exclusivas y la mejor variedad gastronómica.">
    <meta property="og:image" content="{{ asset('assets/img/hero/desktop-pizza.jpg') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom Semantic CSS -->
    <link href="{{ asset('css/zonix.css') }}" rel="stylesheet">
</head>
<body>
    @yield('content')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/zonix.js') }}"></script>
</body>
</html>
