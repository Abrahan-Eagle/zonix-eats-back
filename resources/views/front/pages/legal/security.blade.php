@extends('front.layouts.zonix')

@section('title', 'Seguridad - Zonix Eats')
@section('meta_description', 'Medidas de seguridad y protección en Zonix Eats.')

@section('content')
<!-- Hero Header -->
<header class="bg-navy position-relative overflow-hidden" style="padding-top: 8rem; padding-bottom: 4rem;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at center, rgba(34,197,94,0.15), transparent 50%); pointer-events: none;"></div>
    <div class="container-zonix position-relative z-1 text-center">
        <span class="d-inline-block px-3 py-1 bg-white bg-opacity-10 text-white rounded-pill border border-white border-opacity-10 font-bold text-uppercase tracking-wider mb-3 text-xs">
            Seguridad
        </span>
        <h1 class="text-4xl md:text-5xl font-black text-white mb-3 tracking-tight">Seguridad Integral</h1>
        <p class="text-slate-300 text-lg">Protegemos tu experiencia en cada pedido</p>
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
                             <span class="material-symbols-outlined text-green-600">verified_user</span>
                            Contenido
                        </h5>
                        <nav class="nav flex-column gap-2">
                            <a href="#measures" class="nav-link text-slate-600 px-0 py-1 hover:text-green-600 transition-colors">Medidas de seguridad</a>
                            <a href="#tips" class="nav-link text-slate-600 px-0 py-1 hover:text-green-600 transition-colors">Recomendaciones</a>
                            <a href="#report" class="nav-link text-slate-600 px-0 py-1 hover:text-green-600 transition-colors">Reportar incidentes</a>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                 <div class="bg-white rounded-xl shadow-lg p-5">
                    <div class="prose max-w-none text-slate-600 leading-relaxed">
                        
                        <div id="measures" class="scroll-mt-24 mb-5">
                             <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-green-100 p-2 rounded-circle text-green-600 d-flex">
                                    <span class="material-symbols-outlined">lock</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">Nuestras Medidas</h3>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-slate-50 p-4 rounded-xl">
                                        <span class="material-symbols-outlined text-4xl text-primary-zonix mb-3">vpn_key</span>
                                        <h4 class="font-bold text-navy text-lg">Encriptación Total</h4>
                                        <p class="text-sm mb-0">Usamos encriptación de grado bancario (TLS 1.3) para todas las comunicaciones.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-slate-50 p-4 rounded-xl">
                                        <span class="material-symbols-outlined text-4xl text-blue-zonix mb-3">security_update_warning</span>
                                        <h4 class="font-bold text-navy text-lg">Monitoreo 24/7</h4>
                                        <p class="text-sm mb-0">Sistemas automatizados detectan y bloquean amenazas en tiempo real.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tips" class="scroll-mt-24 mb-5">
                             <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-green-100 p-2 rounded-circle text-green-600 d-flex">
                                    <span class="material-symbols-outlined">tips_and_updates</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">Recomendaciones para usuarios</h3>
                            </div>
                             <ul class="list-none space-y-3 mb-0 ps-0">
                                <li class="d-flex gap-3 align-items-center bg-white border border-slate-100 p-3 rounded-lg">
                                    <span class="material-symbols-outlined text-yellow fs-5">password</span>
                                    <span>Usa una contraseña única y compleja para Zonix Eats.</span>
                                </li>
                                <li class="d-flex gap-3 align-items-center bg-white border border-slate-100 p-3 rounded-lg">
                                    <span class="material-symbols-outlined text-yellow fs-5">phishing</span>
                                    <span>Desconfía de correos que te pidan tu contraseña.</span>
                                </li>
                            </ul>
                        </div>

                         <div id="report" class="scroll-mt-24">
                             <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-green-100 p-2 rounded-circle text-green-600 d-flex">
                                    <span class="material-symbols-outlined">report_problem</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">Reportar Incidentes</h3>
                            </div>
                            <p>Si crees que has encontrado una vulnerabilidad de seguridad, por favor repórtala de inmediato a nuestro equipo de seguridad.</p>
                            <a href="mailto:seguridad@zonixeats.com" class="btn btn-navy rounded-pill px-4 mt-2">
                                Contactar Seguridad
                            </a>
                        </div>

                    </div>
                 </div>
            </div>
         </div>
    </div>
</main>
@endsection
