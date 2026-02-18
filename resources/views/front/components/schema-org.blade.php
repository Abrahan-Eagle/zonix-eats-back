<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "{{ url('/') }}#organization",
      "name": "Corral X",
      "alternateName": "CorralX",
      "url": "{{ url('/') }}",
      "logo": {
        "@type": "ImageObject",
        "url": "{{ asset('assets/front/images/LOGO_CORRAL.png') }}",
        "width": 512,
        "height": 512
      },
      "description": "Marketplace ganadero de Venezuela. Compra y vende ganado, equipos de hacienda y maquinaria agrícola.",
      "foundingDate": "2024",
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "Soporte Técnico",
        "email": "soporte@corralx.com"
      },
      "sameAs": [
        "https://www.facebook.com/corralx",
        "https://www.instagram.com/corral__x/?igsh=MTBvdWprdngwcW1ydA%3D%3D#",
        "https://twitter.com/corralx"
      ],
      "areaServed": {
        "@type": "Country",
        "name": "Venezuela",
        "identifier": "VE"
      },
      "knowsAbout": [
        "Ganadería",
        "Marketplace",
        "Ganado Bovino",
        "Ganado Bufalino",
        "Ganado Equino",
        "Ganado Porcino",
        "Equipos de Hacienda",
        "Maquinaria Agrícola"
      ],
      "foundingLocation": {
        "@type": "Country",
        "name": "Venezuela"
      }
    },
    {
      "@type": "WebApplication",
      "@id": "{{ url('/') }}#webapp",
      "name": "Corral X",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Android, iOS, Web",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD",
        "availability": "https://schema.org/Available"
      },
      "applicationSubCategory": "Marketplace Ganadero",
      "browserRequirements": "Requires JavaScript. Requires HTML5.",
      "softwareVersion": "1.0.0",
      "releaseNotes": "Marketplace ganadero de Venezuela con IA para análisis de mercado",
      "description": "Marketplace ganadero para comprar y vender ganado bovino, bufalino, equino y porcino. También incluye equipos de hacienda, maquinaria agrícola, insumos y servicios de transporte.",
      "featureList": [
        "Mercado Inteligente de Ganado",
        "Perfiles Verificados",
        "Comunicación Directa",
        "Pulso de Mercado con IA",
        "Publicación Rápida",
        "Favoritos y Notificaciones"
      ],
      "screenshot": "{{ asset('assets/front/images/images/images/phone-mockup.jpg') }}"
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "¿Qué es exactamente Corral X?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Corral X es la primera plataforma digital integral de Venezuela dedicada al agro. Es una aplicación y web donde puedes comprar y vender ganado, fincas, maquinaria, insumos y contratar servicios de transporte. Es como una feria ganadera, pero en tu bolsillo y disponible las 24 horas."
          }
        },
        {
          "@type": "Question",
          "name": "¿La aplicación es gratuita?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "¡Sí! Descargar la app, registrarse y ver las publicaciones es totalmente gratis. Queremos que todos los productores, grandes y pequeños, tengan acceso al mercado nacional."
          }
        },
        {
          "@type": "Question",
          "name": "¿Solo sirve para vender vacas?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "En esta primera etapa, nos enfocamos exclusivamente en el ganado. Aunque nuestro proyecto es integral y abarcará todo el agro (maquinaria, insumos, fincas), por los momentos solo está habilitada la comercialización de ganado (bovino, bufalino, equino, porcino). Iremos adicionando poco a poco los demás productos y categorías."
          }
        },
        {
          "@type": "Question",
          "name": "¿Corral X cobra comisión por venta?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "No. Corral X es un facilitador de contacto. El negocio, el precio y el apretón de manos (o la transferencia) se hacen directamente entre el comprador y el vendedor, sin intermediarios ni comisiones por nuestra parte en la transacción final."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cómo se realizan los pagos?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Los pagos se coordinan directamente entre las partes. La aplicación no procesa pagos bancarios ni retiene dinero. Ustedes acuerdan si es transferencia, divisa en efectivo o pago móvil. Recomendamos siempre verificar el pago antes de entregar cualquier animal o producto."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cómo publico mi ganado o finca?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Es muy fácil. Te registras, vas al botón de 'Publicar', subes las fotos (puedes poner hasta 5), llenas los datos (raza, peso, ubicación, precio referencial) y listo. En segundos tu anuncio lo ven miles de ganaderos."
          }
        },
        {
          "@type": "Question",
          "name": "¿Es seguro comprar en Corral X?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Trabajamos duro para que así sea. Contamos con perfiles verificados, sistema de calificación y reportes. Si ves algo raro, puedes reportar la publicación y nuestro equipo la revisará de inmediato."
          }
        },
        {
          "@type": "Question",
          "name": "¿Qué es el 'Pulso de Mercado IA'?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Es nuestra herramienta estrella. Usamos Inteligencia Artificial para analizar todas las publicaciones y decirte qué está pasando en el mercado: qué razas se buscan más, cómo se están moviendo los precios en tu estado y qué tendencias hay. Es información de poder para que tomes mejores decisiones."
          }
        }
      ]
    },
    {
      "@type": "WebSite",
      "@id": "{{ url('/') }}#website",
      "url": "{{ url('/') }}",
      "name": "Corral X - Marketplace Ganadero",
      "description": "Marketplace ganadero de Venezuela. Compra y vende ganado, equipos de hacienda y maquinaria agrícola.",
      "publisher": {
        "@id": "{{ url('/') }}#organization"
      },
      "inLanguage": "es-VE",
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "{{ url('/') }}/buscar?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ url('/') }}#breadcrumb",
      "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}#inicio"},
        {"@type": "ListItem", "position": 2, "name": "Características", "item": "{{ url('/') }}#caracteristicas"},
        {"@type": "ListItem", "position": 3, "name": "Beneficios", "item": "{{ url('/') }}#beneficios"},
        {"@type": "ListItem", "position": 4, "name": "Cómo Funciona", "item": "{{ url('/') }}#como-funciona"},
        {"@type": "ListItem", "position": 5, "name": "Preguntas Frecuentes", "item": "{{ url('/') }}#faq"},
        {"@type": "ListItem", "position": 6, "name": "Descargar", "item": "{{ url('/') }}#descargar"}
      ]
    },
    {
      "@type": "ItemList",
      "@id": "{{ url('/') }}#features",
      "name": "Características de Corral X",
      "description": "Funcionalidades principales del marketplace ganadero Corral X",
      "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Mercado Inteligente", "description": "Explora un catálogo inmenso de ganado. Filtra por raza, tipo y ubicación para encontrar exactamente lo que buscas."},
        {"@type": "ListItem", "position": 2, "name": "Perfiles Verificados", "description": "Construye confianza con perfiles de vendedor completos, calificaciones y nuestra insignia de verificación para transacciones seguras."},
        {"@type": "ListItem", "position": 3, "name": "Comunicación Directa", "description": "Nuestro chat integrado te conecta directamente con compradores y vendedores de forma privada y segura."},
        {"@type": "ListItem", "position": 4, "name": "Pulso del Mercado (IA)", "description": "Toma decisiones informadas con nuestro análisis de mercado. Entiende las tendencias de precios, oferta y demanda."},
        {"@type": "ListItem", "position": 5, "name": "Publica en Minutos", "description": "Sube fotos de tu ganado, añade los detalles y llega a miles de compradores potenciales desde tu teléfono."},
        {"@type": "ListItem", "position": 6, "name": "Favoritos y Notificaciones", "description": "Guarda los anuncios que te interesan y recibe notificaciones para no perder ninguna oportunidad de negocio."}
      ]
    },
    {
      "@type": "Product",
      "@id": "{{ url('/') }}#ganado-bovino",
      "name": "Ganado Bovino",
      "description": "Compra y venta de ganado bovino en Venezuela. Razas lecheras, de engorde y criollas.",
      "category": "Ganado",
      "brand": {
        "@type": "Brand",
        "name": "Corral X"
      },
      "offers": {
        "@type": "AggregateOffer",
        "lowPrice": "500",
        "highPrice": "15000",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "{{ url('/') }}"
      }
    },
    {
      "@type": "Product",
      "@id": "{{ url('/') }}#ganado-bufalino",
      "name": "Ganado Bufalino",
      "description": "Compra y venta de ganado bufalino en Venezuela. Búfalos de río y pantano.",
      "category": "Ganado",
      "brand": {
        "@type": "Brand",
        "name": "Corral X"
      },
      "offers": {
        "@type": "AggregateOffer",
        "lowPrice": "600",
        "highPrice": "18000",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "{{ url('/') }}"
      }
    },
    {
      "@type": "Product",
      "@id": "{{ url('/') }}#ganado-equino",
      "name": "Ganado Equino",
      "description": "Compra y venta de ganado equino en Venezuela. Caballos de trabajo, criollos y pura sangre.",
      "category": "Ganado",
      "brand": {
        "@type": "Brand",
        "name": "Corral X"
      },
      "offers": {
        "@type": "AggregateOffer",
        "lowPrice": "300",
        "highPrice": "25000",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "{{ url('/') }}"
      }
    },
    {
      "@type": "Product",
      "@id": "{{ url('/') }}#ganado-porcino",
      "name": "Ganado Porcino",
      "description": "Compra y venta de ganado porcino en Venezuela. Cerdos de engorde y reproductores.",
      "category": "Ganado",
      "brand": {
        "@type": "Brand",
        "name": "Corral X"
      },
      "offers": {
        "@type": "AggregateOffer",
        "lowPrice": "50",
        "highPrice": "800",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "{{ url('/') }}"
      }
    },
    {
      "@type": "Product",
      "@id": "{{ url('/') }}#equipos-hacienda",
      "name": "Equipos de Hacienda",
      "description": "Compra y venta de equipos de hacienda: bebederos, comederos, cercas eléctricas, ordeñadoras.",
      "category": "Equipos",
      "brand": {
        "@type": "Brand",
        "name": "Corral X"
      },
      "offers": {
        "@type": "AggregateOffer",
        "lowPrice": "20",
        "highPrice": "5000",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "{{ url('/') }}"
      }
    },
    {
      "@type": "Product",
      "@id": "{{ url('/') }}#maquinaria-agricola",
      "name": "Maquinaria Agrícola",
      "description": "Compra y venta de maquinaria agrícola: tractores, cosechadoras, implementos agrícolas.",
      "category": "Maquinaria",
      "brand": {
        "@type": "Brand",
        "name": "Corral X"
      },
      "offers": {
        "@type": "AggregateOffer",
        "lowPrice": "1000",
        "highPrice": "150000",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "{{ url('/') }}"
      }
    }
  ]
}
</script>
