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
}
