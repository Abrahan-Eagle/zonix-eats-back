<?php

namespace App\Helpers;

class SeoHelper
{
    protected static string $appName = 'Zonix Glasses';

    protected static array $data = [
        'title' => 'Zonix Glasses — Smart glasses companion',
        'description' => 'Plataforma Zonix Glasses: autenticación, perfiles, notificaciones y administración.',
        'keywords' => 'zonix glasses, smart glasses, laravel, flutter, api',
        'image' => 'assets/img/logo.png',
        'url' => '',
        'type' => 'website',
        'robots' => 'index, follow',
    ];

    public static function setTitle($title): void
    {
        self::$data['title'] = $title.' | '.self::$appName;
    }

    public static function setDescription($description): void
    {
        self::$data['description'] = $description;
    }

    public static function setKeywords($keywords): void
    {
        self::$data['keywords'] = $keywords;
    }

    public static function setImage($image): void
    {
        self::$data['image'] = $image;
    }

    public static function setUrl($url): void
    {
        self::$data['url'] = $url;
    }

    public static function setType($type): void
    {
        self::$data['type'] = $type;
    }

    public static function meta(): array
    {
        if (empty(self::$data['url'])) {
            self::$data['url'] = url()->current();
        }

        if (! filter_var(self::$data['image'], FILTER_VALIDATE_URL)) {
            self::$data['image'] = asset(self::$data['image']);
        }

        return self::$data;
    }

    public static function jsonLd(): string
    {
        $data = self::meta();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => self::$appName,
            'url' => $data['url'],
            'description' => $data['description'],
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function generateAppSchema(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => self::$appName,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Android, iOS, Web',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function generateOrganizationSchema(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => self::$appName,
            'url' => url('/'),
            'logo' => asset('assets/img/logo.png'),
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function generateFaqSchema(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => '¿Qué es Zonix Glasses?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Plataforma companion para smart glasses: autenticación, perfiles, notificaciones y sync con dispositivo.',
                    ],
                ],
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
