<?php

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * Display the home page (one-page template).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        \App\Helpers\SeoHelper::setTitle('Delivery de Comida en Venezuela | Restaurantes, Farmacias y Más');
        \App\Helpers\SeoHelper::setDescription('Pide comida de tus restaurantes favoritos en Venezuela. Entregas en 15 min, ofertas exclusivas y rastreo en vivo. ¡Descarga la App Zonix Eats y disfruta!');

        $schema = [
            'app' => \App\Helpers\SeoHelper::generateAppSchema(),
            'organization' => \App\Helpers\SeoHelper::generateOrganizationSchema(),
            'faq' => \App\Helpers\SeoHelper::generateFaqSchema()
        ];
        
        return view('front.welcome', compact('schema'));
    }
}
