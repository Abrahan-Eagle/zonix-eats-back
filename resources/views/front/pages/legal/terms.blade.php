@extends('front.layouts.zonix')

@section('title', 'Términos y Condiciones - Zonix Eats')
@section('meta_description', 'Términos y condiciones de uso de la plataforma Zonix Eats.')

@section('content')
<!-- Hero Header -->
<header class="bg-navy position-relative overflow-hidden" style="padding-top: 8rem; padding-bottom: 4rem;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(255,61,64,0.15), transparent 40%); pointer-events: none;"></div>
    <div class="container-zonix position-relative z-1 text-center">
        <span class="d-inline-block px-3 py-1 bg-white bg-opacity-10 text-white rounded-pill border border-white border-opacity-10 font-bold text-uppercase tracking-wider mb-3 text-xs">
            Legal
        </span>
        <h1 class="text-4xl md:text-5xl font-black text-white mb-3 tracking-tight">Términos y Condiciones</h1>
        <p class="text-slate-300 text-lg">Última actualización: {{ date('d/m/Y') }}</p>
    </div>
</header>

<!-- Main Content -->
<main class="bg-light py-5">
    <div class="container-zonix">
        <div class="row g-lg-5">
            <!-- Sidebar (Sticky TOC) -->
            <div class="col-lg-4 d-none d-lg-block">
                <div class="sticky-top" style="top: 100px; z-index: 1020;">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                        <h5 class="font-bold text-navy mb-3 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary-zonix">toc</span>
                            En esta página
                        </h5>
                        <nav class="nav flex-column gap-2" id="legal-toc">
                            <a href="#intro" class="nav-link text-slate-600 px-0 py-1 hover:text-primary-zonix transition-colors">Introducción</a>
                            <a href="#usage" class="nav-link text-slate-600 px-0 py-1 hover:text-primary-zonix transition-colors">1. Uso de la Plataforma</a>
                            <a href="#orders" class="nav-link text-slate-600 px-0 py-1 hover:text-primary-zonix transition-colors">2. Pedidos y Pagos</a>
                            <a href="#cancellations" class="nav-link text-slate-600 px-0 py-1 hover:text-primary-zonix transition-colors">3. Cancelaciones</a>
                            <a href="#liability" class="nav-link text-slate-600 px-0 py-1 hover:text-primary-zonix transition-colors">4. Limitación de Responsabilidad</a>
                        </nav>
                    </div>
                    
                    <!-- Help Box -->
                    <div class="bg-navy bg-opacity-5 p-4 rounded-xl mt-4 border border-navy border-opacity-5">
                        <h6 class="font-bold text-navy mb-2">¿Necesitas ayuda?</h6>
                        <p class="text-sm text-slate-500 mb-3">Si tienes dudas sobre nuestros términos, contáctanos.</p>
                        <a href="mailto:legal@zonixeats.com" class="text-primary-zonix font-bold text-sm text-decoration-none">legal@zonixeats.com &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-8">
                <div class="bg-white rounded-xl shadow-lg p-5">
                    <div class="prose max-w-none text-slate-600 leading-relaxed">
                        <div id="intro" class="scroll-mt-24">
                            <p class="lead font-medium text-navy mb-4">
                                Bienvenido a <strong>Zonix Eats</strong>. Al acceder y utilizar nuestra aplicación móvil y sitio web, aceptas cumplir con los siguientes términos y condiciones. Te recomendamos leerlos detenidamente antes de utilizar nuestros servicios.
                            </p>
                        </div>
                        
                        <hr class="my-5 border-slate-100">

                        <div id="usage" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-primary-zonix bg-opacity-10 p-2 rounded-circle text-primary-zonix d-flex">
                                    <span class="material-symbols-outlined">person</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">1. Uso de la Plataforma</h3>
                            </div>
                            <p class="mb-4">Zonix Eats es una plataforma tecnológica que actúa como intermediario entre usuarios, comercios aliados (restaurantes, tiendas) y repartidores independientes.</p>
                            <div class="bg-slate-50 p-4 rounded-lg border-start border-4 border-primary-zonix">
                                <ul class="list-none space-y-2 mb-0 ps-0">
                                    <li class="d-flex gap-2">
                                        <span class="material-symbols-outlined text-green-600 fs-6 mt-1">check_circle</span>
                                        <span>Debes ser mayor de 18 años para registrar una cuenta.</span>
                                    </li>
                                    <li class="d-flex gap-2">
                                        <span class="material-symbols-outlined text-green-600 fs-6 mt-1">check_circle</span>
                                        <span>Eres responsable de la veracidad de los datos suministrados.</span>
                                    </li>
                                    <li class="d-flex gap-2">
                                        <span class="material-symbols-outlined text-green-600 fs-6 mt-1">check_circle</span>
                                        <span>No debes compartir tus credenciales de acceso con terceros.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div id="orders" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-primary-zonix bg-opacity-10 p-2 rounded-circle text-primary-zonix d-flex">
                                    <span class="material-symbols-outlined">shopping_cart</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">2. Pedidos y Pagos</h3>
                            </div>
                            <p>Los precios de los productos son establecidos directamente por los comercios aliados. Zonix Eats cobra una tarifa de servicio y/o envío por el uso de la plataforma.</p>
                            <p>Aceptamos los siguientes métodos de pago:</p>
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-light text-navy border">Pago Móvil</span>
                                <span class="badge bg-light text-navy border">Zelle</span>
                                <span class="badge bg-light text-navy border">Efectivo (Divisas)</span>
                            </div>
                        </div>

                        <div id="cancellations" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-primary-zonix bg-opacity-10 p-2 rounded-circle text-primary-zonix d-flex">
                                    <span class="material-symbols-outlined">cancel</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">3. Cancelaciones y Reembolsos</h3>
                            </div>
                            <p>Puedes cancelar tu pedido sin costo siempre que el comercio no haya comenzado a prepararlo. Una vez en preparación, podrían aplicar cargos por cancelación.</p>
                        </div>

                        <div id="liability" class="scroll-mt-24">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-primary-zonix bg-opacity-10 p-2 rounded-circle text-primary-zonix d-flex">
                                    <span class="material-symbols-outlined">gavel</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">4. Limitación de Responsabilidad</h3>
                            </div>
                            <p>Zonix Eats no prepara ni manipula alimentos. La responsabilidad sobre la calidad e higiene de los productos recae exclusivamente en el comercio aliado. Sin embargo, gestionaremos cualquier reclamo para asegurar tu satisfacción.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
