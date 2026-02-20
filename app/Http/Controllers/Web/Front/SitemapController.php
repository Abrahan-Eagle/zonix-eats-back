<?php

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Commerce;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            // Add other static pages here
            ;

        // Add dynamic pages for Commerces
        // Assuming there is a route for viewing a commerce, e.g., /commerce/{id} or /restaurant/{slug}
        // Since we don't have slugs yet (based on previous analysis), we might use IDs.
        // However, looking at the code, we should check routes first. 
        // For now, I'll add the basic structure and comment out the dynamic parts 
        // until I verify the routes for commerces and products.
        
        return $sitemap->toResponse(request());
    }
}
