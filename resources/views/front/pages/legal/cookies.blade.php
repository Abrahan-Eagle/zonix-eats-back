@extends('front.layouts.zonix')

@section('title', 'Política de Cookies - Zonix Eats')
@section('meta_description', 'Información sobre el uso de cookies en Zonix Eats.')

@section('content')
<!-- Hero Header -->
<header class="bg-navy position-relative overflow-hidden" style="padding-top: 8rem; padding-bottom: 4rem;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at bottom right, rgba(253,184,19,0.15), transparent 40%); pointer-events: none;"></div>
    <div class="container-zonix position-relative z-1 text-center">
        <span class="d-inline-block px-3 py-1 bg-white bg-opacity-10 text-white rounded-pill border border-white border-opacity-10 font-bold text-uppercase tracking-wider mb-3 text-xs">
            Cookies
        </span>
        <h1 class="text-4xl md:text-5xl font-black text-white mb-3 tracking-tight">Política de Cookies</h1>
        <p class="text-slate-300 text-lg">Cómo utilizamos las tecnologías de rastreo</p>
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
                             <span class="material-symbols-outlined text-yellow">cookie</span>
                            Contenido
                        </h5>
                        <nav class="nav flex-column gap-2">
                            <a href="#what-are" class="nav-link text-slate-600 px-0 py-1 hover:text-yellow transition-colors">¿Qué son las cookies?</a>
                            <a href="#types" class="nav-link text-slate-600 px-0 py-1 hover:text-yellow transition-colors">Tipos de cookies</a>
                            <a href="#manage" class="nav-link text-slate-600 px-0 py-1 hover:text-yellow transition-colors">Gestionar cookies</a>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="bg-white rounded-xl shadow-lg p-5">
                    <div class="prose max-w-none text-slate-600 leading-relaxed">
                        
                        <div id="what-are" class="scroll-mt-24 mb-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-yellow-10 p-2 rounded-circle text-yellow d-flex">
                                    <span class="material-symbols-outlined">lightbulb</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">¿Qué son las cookies?</h3>
                            </div>
                            <p>Las cookies son pequeños archivos de texto que los sitios web que visitas guardan en tu ordenador o teléfono. Permiten que el sitio recuerde tus acciones y preferencias (como inicio de sesión, idioma, tamaño de letra) durante un período de tiempo.</p>
                        </div>

                        <div id="types" class="scroll-mt-24 mb-5">
                             <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-yellow-10 p-2 rounded-circle text-yellow d-flex">
                                    <span class="material-symbols-outlined">category</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">Tipos de cookies que usamos</h3>
                            </div>
                            
                            <div class="d-flex flex-column gap-3">
                                <div class="p-3 border border-slate-100 rounded-xl d-flex gap-3 align-items-start">
                                    <span class="material-symbols-outlined text-success mt-1">check_circle</span>
                                    <div>
                                        <h4 class="font-bold text-navy text-lg m-0">Cookies Esenciales</h4>
                                        <p class="text-sm m-0">Necesarias para que la web funcione. Incluyen login y seguridad.</p>
                                    </div>
                                </div>
                                <div class="p-3 border border-slate-100 rounded-xl d-flex gap-3 align-items-start">
                                    <span class="material-symbols-outlined text-blue-zonix mt-1">analytics</span>
                                    <div>
                                        <h4 class="font-bold text-navy text-lg m-0">Cookies de Rendimiento</h4>
                                        <p class="text-sm m-0">Nos ayudan a entender cómo los visitantes interactúan con la web (Google Analytics).</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="manage" class="scroll-mt-24">
                             <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-yellow-10 p-2 rounded-circle text-yellow d-flex">
                                    <span class="material-symbols-outlined">tune</span>
                                </div>
                                <h3 class="text-2xl font-bold text-navy m-0">Gestionar cookies</h3>
                            </div>
                            <p>Puedes controlar y/o eliminar las cookies como desees. Puedes eliminar todas las cookies que ya están en tu ordenador y puedes configurar la mayoría de los navegadores para que impidan su instalación.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
