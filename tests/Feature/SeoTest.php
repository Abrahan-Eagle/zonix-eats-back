<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    /**
     * Test that the home page has the correct title and meta tags.
     *
     * @return void
     */
    public function test_home_page_has_seo_tags()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Assert Title
        $response->assertSee('<title>Tu comida favorita en minutos | Zonix EATS</title>', false);
        
        // Assert Meta Description
        $response->assertSee('name="description"', false);
        $response->assertSee('Pide comida a domicilio', false);
        
        // Assert Open Graph
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:type"', false);
        $response->assertSee('property="og:image"', false);
        
        // Assert JSON-LD
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "WebSite"', false);
        $response->assertSee('"name": "Zonix EATS"', false);
    }

    /**
     * Test that sitemap.xml returns valid XML.
     *
     * @return void
     */
    public function test_sitemap_returns_valid_xml()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        
        // Check for XML structure
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', false);
        
        // Check for home URL
        $response->assertSee(url('/'), false);
    }
}
