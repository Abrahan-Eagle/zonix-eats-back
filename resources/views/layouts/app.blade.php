<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ Light() == '1' ? 'light' : 'dark' }}">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

     <!-- Favicons para múltiples dispositivos y navegadores -->
        <!-- ================================================== -->
        <!-- Versiones Apple Touch Icon (iOS/Safari) -->
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('img/favicon/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('img/favicon/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('img/favicon/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/favicon/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('img/favicon/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/favicon/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('img/favicon/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/favicon/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon/apple-icon-180x180.png') }}">

        <!-- Versiones estándar de favicon -->
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/favicon/android-icon-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('img/favicon/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon/favicon-16x16.png') }}">

        <!-- Favicon clásico .ico (compatible con navegadores antiguos) -->
        <link rel="shortcut icon" href="{{ asset('img/favicon/favicon.ico') }}" type="image/x-icon">

        <!-- Configuración de manifiesto para aplicaciones web progresivas -->
        <link rel="manifest" href="{{ asset('img/favicon/manifest.json') }}">

        <!-- Configuración para Microsoft -->
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('img/favicon/ms-icon-144x144.png') }}">
        <meta name="msapplication-config" content="{{ asset('img/favicon/browserconfig.xml') }}">


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css')}}">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.css">

    <!-- Icons-->
    {{-- <link href="{{ asset('css/free.min.css') }}" rel="stylesheet"> <!-- icons -->
    <link href="{{ asset('css/flag-icon.min.css') }}" rel="stylesheet"> <!-- icons --> --}}

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/back-app.css') }}" rel="stylesheet">

    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">

    @yield('css')

    <!--Google Analytics-->

    <!-- <link href="{{ asset('css/coreui-chartjs.css') }}" rel="stylesheet"> -->
    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>

</head>

<?php $lightdark = Light(); ?>

@isset($lightdark)
@switch($lightdark)
@case('1')

<body class="c-app">
    @break
    @case('0')

    <body class="c-app c-dark-theme">
        @break
        @default

        <body class="c-app c-dark-theme">
            @endswitch
            @endisset

            @empty($lightdark)

            <body class="c-app c-dark-theme">
                @endempty


    <div id="app">
        @include('dashboard.shared.side_left')
        @include('dashboard.shared.side_right')
        <div class="c-wrapper">
            @include('dashboard.shared.navbar')
            <div class="c-body" style="padding-top: 2%;">
                @include('errors.alert')
                @include('dashboard.shared.body')
                @include('dashboard.shared.footer')
            </div>
        </div>
    </div>


       <!-- jQuery primero -->
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

       <!-- Toastr JS -->
       <script src="{{ asset('js/toastr.min.js') }}"></script>


    <!-- CoreUI and necessary plugins-->
    <script src="{{ asset('js/back-app.js') }}"></script>



    @stack('script')
    @yield('script')
    {{-- <script>
        // Initialize CKEditor
        ClassicEditor
            .create(document.querySelector('textarea'))
            .then(editor => {
                console.log('Editor was initialized', editor);
            })
            .catch(error => {
                console.error('Error during initialization of the editor', error);
            });
    </script> --}}



    <script>
        // Inicializar CKEditor en todos los textarea
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('textarea').forEach(textarea => {
                ClassicEditor
                    .create(textarea)
                    .then(editor => {
                        console.log('Editor inicializado en:', textarea);
                    })
                    .catch(error => {
                        console.error('Error al inicializar el editor:', error);
                    });
            });
        });
    </script>
</body>


</html>
