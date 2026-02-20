@extends('front.layouts.zonix')

@section('title', 'Política de Privacidad - Zonix Eats')
@section('meta_description', 'Política de privacidad y protección de datos de Zonix Eats.')

@section('content')
<!-- Hero Header -->
<header class="bg-navy position-relative overflow-hidden" style="padding-top: 8rem; padding-bottom: 4rem;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top left, rgba(0,153,221,0.15), transparent 40%); pointer-events: none;"></div>
    <div class="container-zonix position-relative z-1 text-center">
        <span class="d-inline-block px-3 py-1 bg-white bg-opacity-10 text-white rounded-pill border border-white border-opacity-10 font-bold text-uppercase tracking-wider mb-3 text-xs">
            Privacidad
        </span>
        <h1 class="text-4xl md:text-5xl font-black text-white mb-3 tracking-tight">Política de Privacidad</h1>
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
                             <span class="material-symbols-outlined text-blue-zonix">toc</span>
                            En esta página
                        </h5>
                        <nav class="nav flex-column gap-2">
                            <a href="#collection" class="nav-link text-slate-600 px-0 py-1 hover:text-blue-zonix transition-colors">1. Recopilación de Datos</a>
                            <a href="#usage" class="nav-link text-slate-600 px-0 py-1 hover:text-blue-zonix transition-colors">2. Uso de la Información</a>
                            <a href="#sharing" class="nav-link text-slate-600 px-0 py-1 hover:text-blue-zonix transition-colors">3. Compartir Información</a>
                            <a href="#rights" class="nav-link text-slate-600 px-0 py-1 hover:text-blue-zonix transition-colors">4. Tus Derechos</a>
                        </nav>
                    </div>

                    <!-- Help Box -->
                    <div class="bg-navy bg-opacity-5 p-4 rounded-xl mt-4 border border-navy border-opacity-5">
                        <h6 class="font-bold text-navy mb-2">Oficial de Privacidad</h6>
                        <p class="text-sm text-slate-500 mb-3">Para consultas sobre tus datos personales.</p>
                        <a href="mailto:privacidad@zonixeats.com" class="text-blue-zonix font-bold text-sm text-decoration-none">privacidad@zonixeats.com &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-8">
                <div class="bg-white rounded-xl shadow-lg p-5">
                    <div class="prose max-w-none text-slate-600 leading-relaxed">
                        
                        <div id="collection" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-blue-zonix bg-opacity-10 p-2 rounded-circle text-blue-zonix d-flex">
                                    <span class="material-symbols-outlined">data_usage</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">1. Información que Recopilamos</h3>
                            </div>
                            <p class="mb-4">Para brindar nuestro servicio, necesitamos recopilar cierta información personal. Esto incluye:</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-slate-50 rounded-lg h-100 border border-slate-100">
                                        <strong class="text-navy d-block mb-1">Datos de Cuenta</strong>
                                        <span class="text-sm">Nombre, email, teléfono y contraseña encriptada.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-slate-50 rounded-lg h-100 border border-slate-100">
                                        <strong class="text-navy d-block mb-1">Datos de Ubicación</strong>
                                        <span class="text-sm">Dirección de entrega y coordenadas GPS para el delivery.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="usage" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-blue-zonix bg-opacity-10 p-2 rounded-circle text-blue-zonix d-flex">
                                    <span class="material-symbols-outlined">settings_suggest</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">2. Uso de la Información</h3>
                            </div>
                            <ul class="list-none space-y-2 mb-0 ps-0">
                                <li class="d-flex gap-2 align-items-start">
                                    <span class="material-symbols-outlined text-blue-zonix fs-6 mt-1">arrow_forward</span>
                                    <span>Procesar y entregar tus pedidos de comida y productos.</span>
                                </li>
                                <li class="d-flex gap-2 align-items-start">
                                    <span class="material-symbols-outlined text-blue-zonix fs-6 mt-1">arrow_forward</span>
                                    <span>Comunicarnos contigo sobre el estado de tu orden.</span>
                                </li>
                                <li class="d-flex gap-2 align-items-start">
                                    <span class="material-symbols-outlined text-blue-zonix fs-6 mt-1">arrow_forward</span>
                                    <span>Mejorar y personalizar tu experiencia en la App.</span>
                                </li>
                            </ul>
                        </div>

                        <div id="sharing" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-blue-zonix bg-opacity-10 p-2 rounded-circle text-blue-zonix d-flex">
                                    <span class="material-symbols-outlined">share</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">3. Compartir Información</h3>
                            </div>
                            <p>Compartimos tus datos estrictamente con las partes involucradas en el cumplimiento de tu pedido:</p>
                            <ul class="list-disc ps-4 space-y-2">
                                <li><strong>Comercios Aliados:</strong> Reciben tu nombre y detalles del pedido para su preparación.</li>
                                <li><strong>Repartidores:</strong> Reciben tu nombre, dirección y teléfono para realizar la entrega.</li>
                            </ul>
                            <p class="mt-3 text-sm text-slate-500">No vendemos ni alquilamos tus datos personales a terceros con fines de marketing sin tu consentimiento explícito.</p>
                        </div>

                        <div id="rights" class="scroll-mt-24">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-blue-zonix bg-opacity-10 p-2 rounded-circle text-blue-zonix d-flex">
                                    <span class="material-symbols-outlined">shield</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">4. Tus Derechos</h3>
                            </div>
                            <p>Tienes derecho a acceder, corregir, eliminar y restringir el uso de tus datos personales. Puedes gestionar la mayoría de estos ajustes directamente desde tu perfil en la App o contactándonos.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
