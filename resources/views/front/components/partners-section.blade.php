<!-- Carrusel El Sector Ganadero Venezolano - Logos oficiales (INIA, INSAI) + estilizados (FEDENAGA, Asoc. Regionales) -->
@php
    $partners = [
        ['alt' => 'FEDENAGA - Federación Nacional de Ganaderos', 'type' => 'svg', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="100" viewBox="0 0 200 100"><g transform="translate(15,15)"><path d="M35 55c0-6-5-12-12-12s-12 6-12 12v10h24v-10z" fill="#487531"/><ellipse cx="23" cy="38" rx="16" ry="18" fill="#487531"/><path d="M8 38c0-5 4-10 11-10s11 5 11 10" stroke="#487531" stroke-width="2.5" fill="none"/><path d="M44 50c3 0 6-3 6-7 0-4-3-7-6-7" stroke="#8FB135" stroke-width="2" fill="none"/><circle cx="38" cy="28" r="5" fill="#8FB135"/></g><text x="100" y="48" font-family="Inter,Arial,sans-serif" font-size="20" font-weight="700" fill="#1D3215">FEDENAGA</text><text x="100" y="68" font-family="Inter,Arial,sans-serif" font-size="11" fill="#487531">Federación Nacional</text><text x="100" y="82" font-family="Inter,Arial,sans-serif" font-size="11" fill="#487531">de Ganaderos</text></svg>'],
        ['alt' => 'INIA - Instituto Nacional de Investigaciones Agrícolas', 'type' => 'img', 'file' => 'inia.png'],
        ['alt' => 'INSAI - Instituto Nacional de Salud Agrícola Integral', 'type' => 'img', 'file' => 'insai.png'],
        ['alt' => 'Asociaciones regionales del sector ganadero', 'type' => 'svg', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="100" viewBox="0 0 200 100"><g transform="translate(12,12)"><path d="M35 8L8 22v40l27 15 27-15V22L35 8z" fill="none" stroke="#487531" stroke-width="2.5"/><circle cx="18" cy="35" r="6" fill="#8FB135"/><circle cx="35" cy="50" r="6" fill="#8FB135"/><circle cx="52" cy="30" r="6" fill="#8FB135"/><path d="M18 35l17 15 17-20" stroke="#487531" stroke-width="1.5" fill="none"/></g><text x="100" y="48" font-family="Inter,Arial,sans-serif" font-size="18" font-weight="700" fill="#1D3215">Asociaciones</text><text x="100" y="66" font-family="Inter,Arial,sans-serif" font-size="18" font-weight="700" fill="#1D3215">Regionales</text><text x="100" y="84" font-family="Inter,Arial,sans-serif" font-size="11" fill="#487531">Sector ganadero venezolano</text></svg>'],
    ];
@endphp

<section id="sector-ganadero" class="partners-section">
    <div class="container">
        <div class="text-center mx-auto mb-5 max-w-700 fade-in-up">
            <h2 class="fw-bold mb-3">El Sector Ganadero Venezolano</h2>
            <p class="lead">Trabajamos para fortalecer la conexión entre todos los actores del agro</p>
        </div>
    </div>

    <div class="partners-carousel-wrapper">
        <div class="partners-track" aria-hidden="true">
            @foreach([1, 2] as $copy)
                @foreach($partners as $partner)
                    <div class="partner-item">
                        @if($partner['type'] === 'img')
                            <img src="{{ asset('assets/front/images/partners/' . $partner['file']) }}" alt="{{ $partner['alt'] }}" width="200" height="100" loading="lazy" class="partner-logo-img">
                        @else
                            <span class="partner-logo" aria-hidden="true">{!! $partner['svg'] !!}</span>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</section>
