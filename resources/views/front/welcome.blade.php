@extends('front.layouts.zonix')

@section('content')
    <!-- Preloader (Restored) -->
    <div id="preloader" class="position-fixed top-0 start-0 w-100 h-100 bg-white d-flex align-items-center justify-content-center z-fixed-max">
        <div class="text-center animate-pulse">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix" style="height: 12rem;">
        </div>
    </div>
    
    <!-- Mobile Sticky Download Bar (Conversion) -->
    <div class="fixed-bottom bg-white border-top shadow-lg p-3 d-lg-none z-fixed-max d-flex align-items-center justify-content-between animate-slide-up d-none" id="mobileStickyCTA">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Close" id="closeStickyBtn" style="font-size: 0.7rem;"></button>
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix" style="height: 2.5rem;">
            <div>
                <p class="mb-0 font-black text-navy leading-none">Zonix EATS</p>
                <p class="mb-0 text-xs text-slate-500">Pide lo que quieras 🍔</p>
            </div>
        </div>
        <button class="btn btn-zonix-primary rounded-pill px-4 font-bold small shadow-md" data-bs-toggle="modal" data-bs-target="#registerModal">
            Descargar
        </button>
    </div>

    <!-- Navbar -->
    <nav class="navbar-zonix d-flex align-items-center">
        <div class="container-zonix w-100 d-flex align-items-center justify-content-between">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center gap-1" href="{{ url('/') }}">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix EATS" class="navbar-brand-logo">
                <span class="navbar-brand-text fs-3 font-black tracking-tighter leading-none">Zonix<span class="text-primary-zonix">EATS</span></span>
            </a>

            <!-- Search Removed for Landing Page -->
            <div class="d-none d-md-block">
                <!-- Spacer or tagline could go here -->
            </div>

            <!-- Desktop Menu -->
            <div class="d-none d-lg-flex align-items-center gap-4">
                <a href="#categories" class="nav-link font-bold text-navy">Restaurantes</a>
                <a href="#offers" class="nav-link font-bold text-navy">Ofertas</a>
                <a href="#become-partner" class="nav-link font-bold text-navy">Ser Aliado</a>
            </div>

            <!-- Actions -->
            <div class="d-flex align-items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost font-bold text-navy d-none d-lg-block">Dashboard</a>
                @else
                    <button class="btn btn-ghost font-bold text-navy d-none d-lg-block" data-bs-toggle="modal" data-bs-target="#loginModal">Iniciar Sesión</button>
                @endauth
                <button class="btn btn-zonix-primary rounded-pill px-4 hover-scale d-none d-lg-block" data-bs-toggle="modal" data-bs-target="#registerModal">
                    Descarga la App
                </button>
                <!-- Mobile Toggle -->
                <button class="btn btn-icon d-lg-none" id="mobileMenuBtn">
                    <span class="material-symbols-outlined fs-2 text-navy">menu</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-wrapper">
        <div class="hero-left">
            <div style="max-width: 36rem;">
                <span class="d-inline-block px-3 py-1 bg-yellow-10 text-yellow rounded-pill border border-warning border-opacity-25 font-bold text-uppercase tracking-wider mb-4 text-xs">
                    🚀 Entregas en 15 minutos
                </span>
                <h1 class="text-hero-zonix font-black leading-none tracking-tight text-white mb-4 reveal">
                    El Marketplace de comida <br>
                    <span class="text-primary-zonix">más grande</span> <br>
                    en tu bolsillo.
                </h1>
                <p class="text-slate-300 text-lg text-sm-xl mb-5 leading-relaxed font-medium reveal reveal-delay-100" style="margin-bottom: 2.5rem !important;">
                    Descarga la App y accede a miles de restaurantes, ofertas exclusivas y entregas rápidas. Todo en un solo lugar.
                </p>

                <div class="d-flex flex-wrap gap-3">
                    <a href="javascript:void(0)" class="app-badge" onclick="alert('Disponible pronto en App Store')">
                        <img src="{{ asset('assets/img/badges/app-store.png') }}" alt="Download on App Store" class="h-100">
                    </a>
                    <a href="javascript:void(0)" class="app-badge" onclick="alert('Disponible pronto en Google Play')">
                        <img src="{{ asset('assets/img/badges/google-play.png') }}" alt="Get it on Google Play" class="h-100">
                    </a>
                </div>
            </div>
            
            <!-- Blob (Decorative) -->
            <div class="blob-bg"></div>
        </div>

        <div class="hero-right">
            <!-- Desktop Image -->
            <div class="position-absolute top-0 start-0 w-100 h-100 d-none d-lg-block rounded-4rem-bl overflow-hidden">
                <picture>
                    <source srcset="{{ asset('assets/img/hero/desktop-pizza.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/img/hero/desktop-pizza.jpg') }}" 
                         alt="Pizza" class="w-100 h-100 object-fit-cover" width="1920" height="1080">
                </picture>
                <div class="hero-overlay-desktop"></div>
                
                <!-- Stats Card -->
                <div class="position-absolute bottom-0 end-0 m-5 p-3 glass-panel shadow-xl d-block" style="width: 20rem;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle bg-green-100 text-green-600 d-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem;">
                            <span class="material-symbols-outlined">verified</span>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold text-uppercase mb-0">Calidad Verificada</p>
                            <p class="text-slate-900 font-bold mb-0">100% Fresco</p>
                        </div>
                    </div>
                    <div class="progress bg-slate-100" style="height: 6px;">
                        <div class="progress-bar bg-green-500 rounded-pill" style="width: 92%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Image -->
            <div class="position-absolute top-0 start-0 w-100 h-100 d-lg-none">
                 <picture>
                    <source srcset="{{ asset('assets/img/hero/mobile-pizza.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/img/hero/mobile-pizza.jpg') }}" 
                         alt="Pizza" class="w-100 h-100 object-fit-cover" width="800" height="1200">
                 </picture>
                 <div class="hero-overlay-mobile"></div>
            </div>
        </div>
    </header>

    <!-- Social Proof Strip -->
    <section class="social-proof-strip">
        <div class="container-zonix">
            <div class="row g-4 justify-content-center justify-content-md-between align-items-center">
                <div class="col-6 col-md-auto d-flex justify-content-center">
                    <div class="social-stat reveal">
                        <span class="material-symbols-outlined fs-1 text-slate-300">download</span>
                        <div>
                            <div class="social-stat-number">+1M</div>
                            <div class="social-stat-label">Descargas<br>Globales</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-auto d-flex justify-content-center">
                    <div class="social-stat reveal reveal-delay-100">
                        <span class="material-symbols-outlined fs-1 text-slate-300">star</span>
                        <div>
                            <div class="social-stat-number">4.9</div>
                            <div class="social-stat-label">Calificación<br>App Store</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-auto d-flex justify-content-center">
                    <div class="social-stat reveal reveal-delay-200">
                        <span class="material-symbols-outlined fs-1 text-slate-300">store</span>
                        <div>
                            <div class="social-stat-number">+5k</div>
                            <div class="social-stat-label">Restaurantes<br>Aliados</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-auto d-flex justify-content-center">
                    <div class="social-stat reveal reveal-delay-300">
                        <span class="material-symbols-outlined fs-1 text-slate-300">timer</span>
                        <div>
                            <div class="social-stat-number">15m</div>
                            <div class="social-stat-label">Tiempo<br>Promedio</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Audience Cards -->
    <section id="become-partner" class="position-relative py-5 z-2" style="margin-top: -2.5rem; padding-left: 1rem; padding-right: 1rem;">
        <div class="container-zonix">
            <div class="row g-4 g-lg-zonix">
                <div class="col-md-4">
                    <div class="card-audience hover-lift reveal">   
                        <div class="bg-gradient-glass"></div>
                        <div class="card-content">
                            <div class="card-audience-icon hover-lift-icon bg-red-50 text-primary-zonix">
                                <span class="material-symbols-outlined fs-2">restaurant</span>
                            </div>
                            <h3 class="text-2xl font-bold text-navy mb-2">Pide ahora en la App</h3>
                            <p class="text-slate-600 mb-4 flex-grow-1">Accede a miles de restaurantes y recibe tu comida en minutos.</p>
                            <button class="btn w-100 py-3 rounded-xl bg-slate-100 text-navy font-bold hover:bg-primary-zonix hover:text-white transition-colors border-0" data-bs-toggle="modal" data-bs-target="#registerModal">
                                Pedir Ahora
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-audience hover-lift reveal reveal-delay-100">
                        <div class="bg-gradient-glass"></div>
                        <div class="card-content">
                            <div class="card-audience-icon hover-lift-icon bg-blue-50 text-info">
                                <span class="material-symbols-outlined fs-2 text-blue-zonix">directions_bike</span>
                            </div>
                            <h3 class="text-2xl font-bold text-navy mb-2">Gana Dinero</h3>
                            <p class="text-slate-600 mb-4 flex-grow-1">Conduce, entrega y genera ingresos extra con tu propio horario.</p>
                            <button class="btn w-100 py-3 rounded-xl bg-slate-100 text-navy font-bold hover:bg-blue-zonix hover:text-white transition-colors border-0" onclick="alert('Próximamente: Registro de Repartidores')">
                                Ser Repartidor
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-audience hover-lift reveal reveal-delay-200">
                        <div class="bg-gradient-glass"></div>
                        <div class="card-content">
                            <div class="card-audience-icon hover-lift-icon bg-yellow-50 text-warning">
                                <span class="material-symbols-outlined fs-2 text-yellow">storefront</span>
                            </div>
                            <h3 class="text-2xl font-bold text-navy mb-2">Vende más con Zonix</h3>
                            <p class="text-slate-600 mb-4 flex-grow-1">Digitaliza tu restaurante y llega a nuevos clientes hoy mismo.</p>
                            <button class="btn w-100 py-3 rounded-xl bg-slate-100 text-navy font-bold hover:bg-yellow hover:text-navy transition-colors border-0" onclick="alert('Próximamente: Registro de Aliados')">
                                Registrar Restaurante
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section id="categories" class="py-5 bg-white">
        <div class="container-zonix">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="text-2xl md:text-3xl font-black text-navy tracking-tight">Categorías Populares</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 2.5rem; height: 2.5rem;" id="catBtnPrev"><span class="material-symbols-outlined">arrow_back</span></button>
                    <button class="btn bg-navy text-white rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 2.5rem; height: 2.5rem;" id="catBtnNext"><span class="material-symbols-outlined">arrow_forward</span></button>
                </div>
            </div>
            
            <div class="scroll-snap-x hide-scrollbar" id="categoriesContainer">
                <a href="#" class="category-item snap-start">
                    <div class="category-ring"><img src="{{ asset('assets/img/categories/burger.jpg') }}" alt="Burger" loading="lazy" width="80" height="80"></div>
                    <span class="font-bold text-navy text-sm">Hamburguesas</span>
                </a>
                <a href="#" class="category-item snap-start">
                    <div class="category-ring"><img src="{{ asset('assets/img/categories/sushi.jpg') }}" alt="Sushi" loading="lazy" width="80" height="80"></div>
                    <span class="font-bold text-navy text-sm">Sushi</span>
                </a>
                <a href="#" class="category-item snap-start">
                    <div class="category-ring"><img src="{{ asset('assets/img/categories/mexicana.jpg') }}" alt="Mexicana" loading="lazy" width="80" height="80"></div>
                    <span class="font-bold text-navy text-sm">Mexicana</span>
                </a>
                <a href="#" class="category-item snap-start">
                    <div class="category-ring"><img src="{{ asset('assets/img/categories/pizza.jpg') }}" alt="Pizza" loading="lazy" width="80" height="80"></div>
                    <span class="font-bold text-navy text-sm">Pizza</span>
                </a>
                <!-- Adding more items for scrolling effect -->
                 <a href="#" class="category-item snap-start">
                        <div class="category-ring"><img src="{{ asset('assets/img/categories/bebidas.jpg') }}" alt="Bebidas" loading="lazy" width="80" height="80"></div>
                        <span class="font-bold text-navy text-sm">Bebidas</span>
                </a>
                <a href="#" class="category-item snap-start">
                        <div class="category-ring"><img src="{{ asset('assets/img/categories/saludable.jpg') }}" alt="Saludable" loading="lazy" width="80" height="80"></div>
                        <span class="font-bold text-navy text-sm">Saludable</span>
                </a>
                <a href="#" class="category-item snap-start">
                        <div class="category-ring"><img src="{{ asset('assets/img/categories/postres.jpg') }}" alt="Postres" loading="lazy" width="80" height="80"></div>
                        <span class="font-bold text-navy text-sm">Postres</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Promotions -->
    <section id="offers" class="py-5 bg-light">
        <div class="container-zonix">
            <div class="d-flex align-items-end justify-content-between mb-4">
                <div>
                    <h2 class="text-3xl md:text-4xl font-black text-navy tracking-tight mb-2">Descubre en la App</h2>
                    <p class="text-slate-600 font-medium mb-0">Miles de ofertas exclusivas esperándote en tu móvil.</p>
                </div>
                <div class="text-primary-zonix font-bold d-flex align-items-center gap-1">Solo en la App <span class="material-symbols-outlined fs-6">smartphone</span></div>
            </div>
            
            <div class="row g-4">
                <div class="col-sm-6 col-lg-zonix-3">
                    <div class="card-promo">
                        <div class="card-promo-img-wrapper">
                            <img src="{{ asset('assets/img/promos/burger-king.jpg') }}" alt="Burger" loading="lazy" width="400" height="250">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary-zonix rounded-pill">50% OFF</span>
                            <div class="position-absolute bottom-0 end-0 m-3 px-2 py-1 bg-white rounded shadow-sm d-flex align-items-center gap-1 small font-bold"><span class="material-symbols-outlined text-warning" style="font-size: 14px;">star</span> 4.8</div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-navy fs-5 mb-1">Burger King</h3>
                            <p class="text-slate-500 text-sm mb-3">Hamburguesas • Americana • $$</p>
                            <div class="d-flex justify-content-between pt-3 border-top border-slate-100"><span class="text-xs font-bold text-slate-400">20-30 min</span><span class="text-xs font-bold text-success bg-success bg-opacity-10 px-2 rounded">Envío Gratis</span></div>
                        </div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col-sm-6 col-lg-zonix-3">
                    <div class="card-promo">
                        <div class="card-promo-img-wrapper">
                            <img src="{{ asset('assets/img/promos/tacos.jpg') }}" alt="Tacos" loading="lazy" width="400" height="250">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-yellow text-navy rounded-pill">2x1 HOY</span>
                            <div class="position-absolute bottom-0 end-0 m-3 px-2 py-1 bg-white rounded shadow-sm d-flex align-items-center gap-1 small font-bold"><span class="material-symbols-outlined text-warning" style="font-size: 14px;">star</span> 4.5</div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-navy fs-5 mb-1">El Tizoncito</h3>
                            <p class="text-slate-500 text-sm mb-3">Tacos • Mexicana • $</p>
                            <div class="d-flex justify-content-between pt-3 border-top border-slate-100"><span class="text-xs font-bold text-slate-400">15-25 min</span><span class="text-xs font-bold text-slate-500">$25 envío</span></div>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-sm-6 col-lg-zonix-3">
                    <div class="card-promo">
                        <div class="card-promo-img-wrapper">
                            <img src="{{ asset('assets/img/promos/dunkin.jpg') }}" alt="Dunkin" loading="lazy" width="400" height="250">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary-zonix text-white rounded-pill">NUEVO</span>
                            <div class="position-absolute bottom-0 end-0 m-3 px-2 py-1 bg-white rounded shadow-sm d-flex align-items-center gap-1 small font-bold"><span class="material-symbols-outlined text-warning" style="font-size: 14px;">star</span> 4.9</div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-navy fs-5 mb-1">Dunkin'</h3>
                            <p class="text-slate-500 text-sm mb-3">Donas • Café • $</p>
                            <div class="d-flex justify-content-between pt-3 border-top border-slate-100"><span class="text-xs font-bold text-slate-400">10-20 min</span><span class="text-xs font-bold text-success bg-success bg-opacity-10 px-2 rounded">Envío Gratis</span></div>
                        </div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col-sm-6 col-lg-zonix-3">
                    <div class="card-promo">
                        <div class="card-promo-img-wrapper">
                            <img src="{{ asset('assets/img/promos/dominos.jpg') }}" alt="Dominos" loading="lazy" width="400" height="250">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary-zonix text-white rounded-pill">30% OFF</span>
                            <div class="position-absolute bottom-0 end-0 m-3 px-2 py-1 bg-white rounded shadow-sm d-flex align-items-center gap-1 small font-bold"><span class="material-symbols-outlined text-warning" style="font-size: 14px;">star</span> 4.7</div>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-navy fs-5 mb-1">Domino's</h3>
                            <p class="text-slate-500 text-sm mb-3">Pizza • Italiana • $$</p>
                            <div class="d-flex justify-content-between pt-3 border-top border-slate-100"><span class="text-xs font-bold text-slate-400">30-45 min</span><span class="text-xs font-bold text-slate-500">$15 envío</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Client / Phone Section -->
    <section class="bg-white overflow-hidden" style="padding-top: 6rem; padding-bottom: 6rem;">
        <div class="container-zonix">
            <div class="row align-items-center gy-5 fix-overflow">
                <div class="col-lg-zonix-6 position-relative d-flex justify-content-center">
                    <div class="blob-bg" style="top:50%; left:50%; transform:translate(-50%, -50%); width: 120%; height: 120%;"></div>
                    <div class="position-relative z-1 bg-navy rounded-3rem border-8 border-slate-900 shadow-2xl overflow-hidden phone-mockup">
                         <!-- Phone Content Simulated -->
                         <div class="bg-primary-zonix p-3 pt-5 text-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="material-symbols-outlined">menu</span>
                                <span class="font-bold">Zonix EATS</span>
                                <span class="material-symbols-outlined">shopping_cart</span>
                            </div>
                            <div class="bg-white bg-opacity-25 p-2 rounded d-flex align-items-center text-sm text-white">
                                <span class="material-symbols-outlined me-2 text-white">search</span> Buscar...
                            </div>
                         </div>
                         <div class="h-100 bg-slate-50 p-3 overflow-hidden d-flex flex-column gap-3">
                             <!-- Skeleton Banner -->
                             <div class="bg-slate-200 rounded-xl animate-pulse" style="height: 8rem;"></div>
                             <!-- Skeleton Circles -->
                             <div class="d-flex gap-2">
                                 <div class="bg-slate-200 rounded-circle animate-pulse" style="width: 4rem; height: 4rem;"></div>
                                 <div class="bg-slate-200 rounded-circle animate-pulse" style="width: 4rem; height: 4rem;"></div>
                                 <div class="bg-slate-200 rounded-circle animate-pulse" style="width: 4rem; height: 4rem;"></div>
                             </div>
                             <!-- Skeleton List Items -->
                             <div class="bg-slate-200 rounded-xl animate-pulse w-100" style="height: 5rem;"></div>
                             <div class="bg-slate-200 rounded-xl animate-pulse w-100" style="height: 5rem;"></div>
                         </div>
                         
                         <div class="position-absolute bottom-0 start-0 end-0 m-3 p-3 bg-white rounded-xl shadow-lg d-flex align-items-center gap-3" style="animation: bounce 1s infinite;">
                             <div class="rounded-circle bg-green-100 text-green-600 p-2 d-flex"><span class="material-symbols-outlined">check_circle</span></div>
                             <div><p class="text-xs text-slate-500 mb-0 font-bold">Estado del pedido</p><p class="text-sm font-bold text-navy mb-0">¡Tu orden está en camino!</p></div>
                         </div>
                    </div>
                </div>
                <div class="col-lg-zonix-6 ps-lg-5">
                    <span class="text-primary-zonix font-bold tracking-wider text-uppercase text-sm d-block mb-2 reveal">¿Cómo funciona?</span>
                    <h2 class="text-3xl text-md-5xl font-black text-navy mb-5 reveal">Tu comida favorita en 3 simples pasos</h2>
                    <div class="d-flex flex-column" style="gap: 2.5rem;">
                        <div class="d-flex gap-4 reveal reveal-delay-100">
                             <div class="flex-shrink-0"><div class="rounded-circle bg-primary-10 text-primary-zonix d-flex align-items-center justify-content-center" style="width: 3.5rem; height: 3.5rem;"><span class="material-symbols-outlined text-3xl">touch_app</span></div></div>
                             <div><h3 class="text-xl font-bold text-navy mb-2">1. Descarga la App</h3><p class="text-slate-600">Disponible gratis para iOS y Android. Crea tu cuenta en segundos.</p></div>
                        </div>
                        <div class="d-flex gap-4">
                             <div class="flex-shrink-0"><div class="rounded-circle bg-yellow-10 text-yellow d-flex align-items-center justify-content-center" style="width: 3.5rem; height: 3.5rem;"><span class="material-symbols-outlined text-3xl">restaurant_menu</span></div></div>
                             <div><h3 class="text-xl font-bold text-navy mb-2">2. Elige tus favoritos</h3><p class="text-slate-600">Explora menús, personaliza tu orden y paga de forma segura en la app.</p></div>
                        </div>
                        <div class="d-flex gap-4">
                             <div class="flex-shrink-0"><div class="rounded-circle bg-blue-10 text-blue-zonix d-flex align-items-center justify-content-center" style="width: 3.5rem; height: 3.5rem;"><span class="material-symbols-outlined text-3xl">location_on</span></div></div>
                             <div><h3 class="text-xl font-bold text-navy mb-2">3. Rastrea en vivo</h3><p class="text-slate-600">Mira en el mapa cómo tu repartidor se acerca a tu ubicación en tiempo real.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- GEO / Definition Section -->
    <section id="about" class="py-5 bg-light border-top border-bottom border-slate-100">
        <div class="container-zonix text-center" style="max-width: 800px;">
            <span class="text-primary-zonix font-bold tracking-wider text-uppercase text-sm d-block mb-3 reveal">Sobre Nosotros</span>
            <h2 class="text-3xl font-black text-navy mb-4 reveal">¿Qué es Zonix Eats?</h2>
            <p class="text-slate-600 text-lg leading-relaxed reveal reveal-delay-100">
                <strong>Zonix Eats</strong> es la plataforma tecnológica de delivery líder en Venezuela que conecta a usuarios con los mejores restaurantes, farmacias y comercios locales. 
                A diferencia de otras apps, ofrecemos <strong>entregas en 15 minutos</strong>, soporte al cliente 100% humano y métodos de pago adaptados a la realidad local (Bolívares, Dólares en efectivo, Zelle, PayPal).
            </p>
        </div>
    </section>

    <!-- Drivers -->
    <section class="position-relative py-5 bg-navy text-white overflow-hidden">
        <div class="position-absolute top-0 start-0 w-100 h-100">
             <img src="{{ asset('assets/img/driver/driver-bg.jpg') }}" class="w-100 h-100 object-fit-cover" alt="Driver">
             <div class="driver-overlay-gradient position-absolute top-0 start-0 w-100 h-100"></div>
        </div>
        <div class="container-zonix position-relative z-1 py-5">
            <div class="" style="max-width: 42rem;">
                <!-- Fixed Heading size: text-4xl (mobile) -> text-md-5xl (tablet) -> text-lg-6xl (desktop) -->
                <h2 class="text-4xl text-md-5xl text-lg-6xl font-black mb-4 leading-tight text-white reveal">Sé tu propio jefe. <br><span class="text-blue-zonix">Gana dinero extra.</span></h2>
                <p class="text-slate-300 text-lg mb-5 reveal reveal-delay-100">Únete a la flota de repartidores más grande de Latinoamérica. Tú decides cuándo y cuánto trabajar. Sin horarios fijos, sin jefes.</p>
                <div class="row g-4 mb-5">
                    <div class="col-sm-6">
                        <div class="bg-white bg-opacity-10 backdrop-blur p-4 rounded-xl border border-white border-opacity-10">
                            <span class="d-block text-3xl font-black text-yellow mb-1">$350+</span><p class="text-sm font-medium text-white mb-0">Ganancia promedio diaria</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-white bg-opacity-10 backdrop-blur p-4 rounded-xl border border-white border-opacity-10">
                             <span class="d-block text-3xl font-black text-primary-zonix mb-1">100%</span><p class="text-sm font-medium text-white mb-0">De las propinas son tuyas</p>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <button class="btn-zonix-primary btn-ripple py-3 px-4 rounded-pill">Registrarme para conducir <span class="material-symbols-outlined">directions_bike</span></button>
                    <button class="btn border border-white border-opacity-25 text-white font-bold py-3 px-4 rounded-pill hover:bg-white hover:bg-opacity-10 transition">Más información</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5 bg-white border-bottom border-slate-100">
        <div class="container-zonix">
            <div class="text-center mb-5">
                <span class="text-primary-zonix font-bold tracking-wider text-uppercase text-sm d-block mb-2 reveal">Comunidad</span>
                <h2 class="text-3xl font-black text-navy reveal">Ellos ya usan Zonix</h2>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="testimonial-card reveal">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="{{ asset('assets/img/avatars/user1.jpg') }}" alt="User" class="rounded-circle avatar-ring" width="56" height="56">
                            <div>
                                <h4 class="font-bold text-navy text-base mb-0">Sofía M.</h4>
                                <div class="text-warning text-sm"><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span></div>
                            </div>
                        </div>
                        <p class="text-slate-600 mb-0">"La mejor app de delivery que he probado. Llegan súper rápido y el soporte es excelente. ¡Totalmente recomendada!"</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card reveal reveal-delay-100">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="{{ asset('assets/img/avatars/user2.jpg') }}" alt="User" class="rounded-circle avatar-ring" width="56" height="56">
                            <div>
                                <h4 class="font-bold text-navy text-base mb-0">Carlos R.</h4>
                                <div class="text-warning text-sm"><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span></div>
                            </div>
                        </div>
                        <p class="text-slate-600 mb-0">"Como repartidor, Zonix me da la libertad que necesito. Pagos puntuales y siempre hay pedidos."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card reveal reveal-delay-200">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="{{ asset('assets/img/avatars/user3.jpg') }}" alt="User" class="rounded-circle avatar-ring" width="56" height="56">
                            <div>
                                <h4 class="font-bold text-navy text-base mb-0">Ana P.</h4>
                                <div class="text-warning text-sm"><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span><span class="material-symbols-outlined fs-6">star</span></div>
                            </div>
                        </div>
                        <p class="text-slate-600 mb-0">"Desde que registré mi pastelería, las ventas se duplicaron. La plataforma para negocios es muy intuitiva."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-white">
        <div class="container-zonix" style="max-width: 800px;">
            <div class="text-center mb-5">
                <span class="text-primary-zonix font-bold tracking-wider text-uppercase text-sm d-block mb-2 reveal">Preguntas Frecuentes</span>
                <h2 class="text-3xl font-black text-navy reveal">Resolvemos tus dudas</h2>
            </div>
            
            <div class="accordion accordion-flush" id="faqAccordion">
                <!-- FAQ 1 -->
                <div class="accordion-item border-0 mb-3 bg-slate-50 rounded-xl overflow-hidden reveal reveal-delay-100">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-transparent shadow-none font-bold text-navy py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            ¿Cuánto tarda en llegar mi pedido con Zonix Eats?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-slate-600 pb-4">
                            El tiempo promedio de entrega en Zonix Eats es de <strong>15 a 30 minutos</strong>, gracias a nuestra tecnología de despacho inteligente y flota de repartidores locales. Priorizamos la rapidez para que tu comida llegue caliente y fresca.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="accordion-item border-0 mb-3 bg-slate-50 rounded-xl overflow-hidden reveal reveal-delay-200">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-transparent shadow-none font-bold text-navy py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            ¿Qué métodos de pago acepta Zonix Eats?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-slate-600 pb-4">
                            Entendemos las necesidades de Venezuela. Aceptamos pagos en <strong>Bolívares</strong> (Pago Móvil, Transferencia), <strong>Dólares</strong> (Efectivo, Zelle), PayPal y todas las Tarjetas de Crédito/Débito internacionales.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="accordion-item border-0 mb-3 bg-slate-50 rounded-xl overflow-hidden reveal reveal-delay-300">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-transparent shadow-none font-bold text-navy py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            ¿En qué ciudades de Venezuela opera Zonix Eats?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-slate-600 pb-4">
                            Actualmente operamos en <strong>Caracas, Maracaibo, Valencia, Barquisimeto y Lechería</strong>. Estamos expandiéndonos rápidamente para llevar el mejor delivery a más ciudades del país.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Banner -->
    <section id="download" class="py-5" style="margin-bottom: -4rem; position: relative; z-index: 10;">
        <div class="container-zonix">
            <div class="cta-banner shadow-primary reveal">
                <div>
                    <h2 class="text-3xl text-md-4xl font-black text-white mb-3">¿Qué esperas?</h2>
                    <p class="text-white text-opacity-75 text-lg mb-4 mb-md-0">Únete a la comunidad de comida más grande. Descarga la App hoy.</p>
                </div>
                <div class="d-flex flex-wrap gap-3">
                    <a href="javascript:void(0)" class="app-badge" onclick="alert('Disponible pronto en App Store')">
                        <img src="{{ asset('assets/img/badges/app-store.png') }}" alt="Download on App Store" class="h-100">
                    </a>
                    <a href="javascript:void(0)" class="app-badge" onclick="alert('Disponible pronto en Google Play')">
                        <img src="{{ asset('assets/img/badges/google-play.png') }}" alt="Get it on Google Play" class="h-100">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Cookie Consent Banner -->
    <div id="cookieBanner" class="cookie-banner">
        <div class="d-flex align-items-center gap-3">
            <span class="material-symbols-outlined text-primary-zonix fs-1">cookie</span>
            <div>
                <p class="font-bold text-navy mb-0">Usamos cookies 🍪</p>
                <p class="text-slate-500 text-sm mb-0">Para mejorar tu experiencia y ofrecerte las mejores ofertas.</p>
            </div>
        </div>
        <button id="acceptCookies" class="btn btn-zonix-primary py-2 px-4 shadow-none">
            Aceptar
        </button>
    </div>

    <!-- Smart App Banner (Mobile Only) -->
    <div id="smartBanner" class="smart-banner d-lg-none">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm text-slate-400 border-0 p-1" onclick="document.getElementById('smartBanner').remove()">
                <span class="material-symbols-outlined fs-6">close</span>
            </button>
            <div class="bg-primary-zonix rounded p-1 d-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem;">
                <span class="material-symbols-outlined text-white">lunch_dining</span>
            </div>
            <div>
                <p class="font-bold text-navy text-sm mb-0">Zonix EATS</p>
                <p class="text-xs text-slate-500 mb-0">Gratis en App Store</p>
            </div>
        </div>
        <button class="btn btn-sm btn-zonix-primary rounded-pill px-3 font-bold">
            Ver
        </button>
    </div>



    <!-- Back to Top Button -->
    <button id="backToTop" class="hover-lift" title="Volver arriba">
        <span class="material-symbols-outlined">arrow_upward</span>
    </button>

    <!-- Footer -->
    <footer class="bg-navy border-top border-white border-opacity-10" style="padding-top: 5rem; padding-bottom: 3rem;">
        <div class="container-zonix">
            <div class="row g-5 mb-5 text-white">
                <!-- Brand Col -->
                <div class="col-lg-4">
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-1">
                            <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix EATS" style="height: 4rem; filter: none !important;">
                            <span class="text-white fs-3 font-black tracking-tighter leading-none">Zonix<span class="text-primary-zonix">EATS</span></span>
                        </div>
                    </div>
                    <p class="text-slate-400 mb-4 bg-transparent border-0 p-0">
                        La plataforma tecnológica que conecta a usuarios, restaurantes y repartidores para transformar la experiencia gastronómica.
                    </p>
                    <div class="d-flex gap-3">
                        <!-- Facebook -->
                        <a href="https://facebook.com/zonixeats" target="_blank" class="social-icon-btn" aria-label="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z"/></svg>
                        </a>
                        <!-- Instagram -->
                        <a href="https://instagram.com/zonixeats" target="_blank" class="social-icon-btn" aria-label="Instagram">
                             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <!-- TikTok -->
                        <a href="https://tiktok.com/@zonixeats" target="_blank" class="social-icon-btn" aria-label="TikTok">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Links Cols -->
                <div class="col-6 col-lg-2">
                    <h5 class="font-bold mb-3 text-white">Zonix</h5>
                    <a href="#about" class="footer-link">Sobre Nosotros</a>
                    <a href="javascript:void(0)" onclick="alert('Sección de Carreras próximamente')" class="footer-link">Carreras</a>
                    <a href="javascript:void(0)" onclick="alert('Blog próximamente')" class="footer-link">Blog</a>
                    <a href="javascript:void(0)" onclick="alert('Kit de Prensa próximamente')" class="footer-link">Prensa</a>
                </div>

                <div class="col-6 col-lg-2">
                    <h5 class="font-bold mb-3 text-white">Legal</h5>
                    <a href="{{ route('pages.terms') }}" class="footer-link">Términos y Condiciones</a>
                    <a href="{{ route('pages.privacy') }}" class="footer-link">Privacidad</a>
                    <a href="{{ route('pages.cookies') }}" class="footer-link">Cookies</a>
                    <a href="{{ route('pages.security') }}" class="footer-link">Seguridad</a>
                </div>

                <div class="col-lg-4">
                    <h5 class="font-bold mb-3 text-white">Ciudades en Venezuela</h5>
                    <div class="row g-2">
                        <!-- Linking to FAQ about cities -->
                        <div class="col-6"><a href="#faq3" class="footer-link" onclick="document.getElementById('faq3').classList.add('show')">Caracas</a></div>
                        <div class="col-6"><a href="#faq3" class="footer-link" onclick="document.getElementById('faq3').classList.add('show')">Maracaibo</a></div>
                        <div class="col-6"><a href="#faq3" class="footer-link" onclick="document.getElementById('faq3').classList.add('show')">Valencia</a></div>
                        <div class="col-6"><a href="#faq3" class="footer-link" onclick="document.getElementById('faq3').classList.add('show')">Barquisimeto</a></div>
                        <div class="col-6"><a href="#faq3" class="footer-link" onclick="document.getElementById('faq3').classList.add('show')">Maracay</a></div>
                        <div class="col-6"><a href="#faq3" class="footer-link" onclick="document.getElementById('faq3').classList.add('show')">Lechería</a></div>
                    </div>
                </div>
            </div>

            <div class="border-top border-white border-opacity-10 pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <p class="text-slate-500 text-sm mb-0">© <span id="footerYear">2026</span> Zonix Technologies Inc.</p>
                <div class="d-flex gap-3">
                     <img src="{{ asset('assets/img/badges/app-store.png') }}" style="height: 2rem; opacity: 0.7;" alt="App Store">
                     <img src="{{ asset('assets/img/badges/google-play.png') }}" style="height: 2rem; opacity: 0.7;" alt="Google Play">
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Offcanvas Menu -->
    <div class="offcanvas-backdrop" id="offcanvasBackdrop"></div>
    <div class="offcanvas-menu" id="offcanvasMenu">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div class="d-flex align-items-center gap-1">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix EATS" style="height: 3rem;">
                <span class="text-navy fs-3 font-black tracking-tighter leading-none">Zonix<span class="text-primary-zonix">EATS</span></span>
            </div>
            <button class="btn btn-icon" id="closeMenuBtn">
                <span class="material-symbols-outlined fs-2 text-slate-400">close</span>
            </button>
        </div>
        
        <div class="d-flex flex-col gap-4">
            <a href="#" class="nav-link-mobile">
                <span class="material-symbols-outlined">home</span> Inicio
            </a>
            <a href="#categories" class="nav-link-mobile">
                <span class="material-symbols-outlined">restaurant</span> Restaurantes
            </a>
            <a href="#offers" class="nav-link-mobile">
                <span class="material-symbols-outlined">percent</span> Ofertas
            </a>
             <hr class="border-slate-100 my-2">
            @auth
                 <a href="{{ route('dashboard') }}" class="nav-link-mobile text-primary-zonix font-bold">
                    <span class="material-symbols-outlined">dashboard</span> Dashboard
                </a>
            @else
                 <a href="#" class="nav-link-mobile" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <span class="material-symbols-outlined">person</span> Iniciar Sesión
                </a>
                 <a href="#" class="nav-link-mobile text-primary-zonix font-bold" data-bs-toggle="modal" data-bs-target="#registerModal">
                    <span class="material-symbols-outlined">how_to_reg</span> Registrarse
                </a>
            @endauth
        </div>
        
        <div class="mt-auto bg-slate-50 p-4 rounded-xl">
            <div class="d-flex gap-3 mb-4">
                <a href="#" class="btn btn-icon bg-white text-navy hover-scale"><i class="bi bi-instagram"></i></a>
                <a href="#" class="btn btn-icon bg-white text-navy hover-scale"><i class="bi bi-tiktok"></i></a>
                <a href="#" class="btn btn-icon bg-white text-navy hover-scale"><i class="bi bi-twitter-x"></i></a>
            </div>
             <p class="text-xs text-slate-500 font-bold text-uppercase mb-3">Descarga la App</p>
             <div class="d-flex gap-2">
                 <button class="btn bg-navy text-white flex-grow-1 py-2 rounded-lg"><span class="material-symbols-outlined">android</span></button>
                 <button class="btn bg-navy text-white flex-grow-1 py-2 rounded-lg"><span class="material-symbols-outlined">ios</span></button>
             </div>
        </div>
    </div>
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-xl border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix" style="height: 3rem;" class="mb-4">
                    <h3 class="font-black text-navy mb-2">Bienvenido de nuevo</h3>
                    <p class="text-slate-500 mb-4">Ingresa a tu cuenta para pedir</p>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="email" name="email" class="form-control form-control-lg bg-slate-50 border-0 mb-3" placeholder="Correo electrónico" required>
                        <input type="password" name="password" class="form-control form-control-lg bg-slate-50 border-0 mb-4" placeholder="Contraseña" required>
                        <button type="submit" class="btn btn-zonix-primary w-100 py-3 font-bold rounded-pill">Iniciar Sesión</button>
                    </form>
                    <p class="text-xs text-slate-400 mt-4">¿No tienes cuenta? <a href="#" class="text-primary-zonix font-bold" data-bs-toggle="modal" data-bs-target="#registerModal">Regístrate</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-xl border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Zonix" style="height: 3rem;" class="mb-4">
                    <h3 class="font-black text-navy mb-2">Crea tu cuenta</h3>
                    <p class="text-slate-500 mb-4">Empieza a disfrutar Zonix EATS</p>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-lg bg-slate-50 border-0 mb-3" placeholder="Nombre completo" required>
                        <input type="email" name="email" class="form-control form-control-lg bg-slate-50 border-0 mb-3" placeholder="Correo electrónico" required>
                        <input type="password" name="password" class="form-control form-control-lg bg-slate-50 border-0 mb-3" placeholder="Crear contraseña" required>
                        <input type="password" name="password_confirmation" class="form-control form-control-lg bg-slate-50 border-0 mb-4" placeholder="Confirmar contraseña" required>
                        <button type="submit" class="btn btn-zonix-primary w-100 py-3 font-bold rounded-pill">Crear Cuenta</button>
                    </form>
                    <p class="text-xs text-slate-400 mt-4">¿Ya tienes cuenta? <a href="#" class="text-primary-zonix font-bold" data-bs-toggle="modal" data-bs-target="#loginModal">Inicia Sesión</a></p>
                </div>
            </div>
        </div>
    </div>
    <!-- JSON-LD Schema -->
    @if(isset($schema))
        <script type="application/ld+json">
            {!! $schema['app'] !!}
        </script>
        <script type="application/ld+json">
            {!! $schema['organization'] !!}
        </script>
        <script type="application/ld+json">
            {!! $schema['faq'] !!}
        </script>
    @endif
@endsection
