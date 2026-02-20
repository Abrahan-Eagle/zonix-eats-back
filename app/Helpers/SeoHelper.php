<?php

namespace App\Helpers;

class SeoHelper
{
    protected static $data = [
        'title' => 'Zonix EATS - Delivery de Comida en Venezuela',
        'description' => 'Pide comida a domicilio de tus restaurantes favoritos en Caracas, Maracaibo, Valencia y más. Los mejores precios y delivery rápido con Zonix EATS.',
        'keywords' => 'delivery, comida, venezuela, zonix, hamburguesas, pizza, sushi, caracas, maracaibo',
        'image' => 'assets/img/hero/desktop-pizza.jpg', // Default image
        'url' => '',
        'type' => 'website',
        'robots' => 'index, follow',
    ];

    public static function setTitle($title)
    {
        self::$data['title'] = $title . ' | Zonix EATS';
    }

    public static function setDescription($description)
    {
        self::$data['description'] = $description;
    }

    public static function setKeywords($keywords)
    {
        self::$data['keywords'] = $keywords;
    }

    public static function setImage($image)
    {
        self::$data['image'] = $image;
    }

    public static function setUrl($url)
    {
        self::$data['url'] = $url;
    }

    public static function setType($type)
    {
        self::$data['type'] = $type;
    }

    public static function meta()
    {
        // Ensure URL is set to current if empty
        if (empty(self::$data['url'])) {
            self::$data['url'] = url()->current();
        }

        // Fix image URL if relative
        if (!filter_var(self::$data['image'], FILTER_VALIDATE_URL)) {
            self::$data['image'] = asset(self::$data['image']);
        }

        return self::$data;
    }

    public static function jsonLd()
    {
        $data = self::meta();
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Zonix EATS',
            'url' => $data['url'],
            'description' => $data['description'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/') . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function generateAppSchema()
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Zonix EATS',
            'applicationCategory' => 'FoodDeliveryApplication',
            'operatingSystem' => 'Android, iOS',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD'
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.8',
                'ratingCount' => '1250'
            ]
        ];
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function generateOrganizationSchema()
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Zonix EATS',
            'url' => url('/'),
            'logo' => asset('assets/img/logo.png'),
            'sameAs' => [
                'https://www.facebook.com/zonixeats',
                'https://www.instagram.com/zonixeats',
                'https://twitter.com/zonixeats'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+58-412-1234567',
                'contactType' => 'customer service',
                'areaServed' => 'VE',
                'availableLanguage' => 'es'
            ]
        ];
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function generateFaqSchema()
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => '¿Cuánto tarda en llegar mi pedido con Zonix Eats?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'El tiempo promedio de entrega en Zonix Eats es de 15 a 30 minutos, gracias a nuestra tecnología de despacho inteligente y flota de repartidores locales.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Qué métodos de pago acepta Zonix Eats?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Aceptamos pagos en Bolívares (Pago Móvil, Transferencia), Dólares (Efectivo, Zelle), PayPal y Tarjetas de Crédito/Débito internacionales.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿En qué ciudades de Venezuela opera Zonix Eats?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Actualmente operamos en Caracas, Maracaibo, Valencia, Barquisimeto y Lechería, expandiéndonos próximamente a más ciudades del país.'
                    ]
                ]
            ]
        ];
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
