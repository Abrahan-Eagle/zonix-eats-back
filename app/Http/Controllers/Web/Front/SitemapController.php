<?php

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;

class SitemapController extends Controller
{
    public function index()
    {
        $content = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>/</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>
XML;

        return response($content, 200)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
