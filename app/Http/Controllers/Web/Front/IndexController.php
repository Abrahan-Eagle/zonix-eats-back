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
        \App\Helpers\SeoHelper::setTitle('Tu comida favorita en minutos');
        \App\Helpers\SeoHelper::setDescription(
            'Pide comida a domicilio de tus restaurantes favoritos en Caracas, Maracaibo, Valencia y más. ' .
            'Los mejores precios y delivery rápido con Zonix EATS.'
        );

        $schema = [
            'app' => \App\Helpers\SeoHelper::generateAppSchema(),
            'organization' => \App\Helpers\SeoHelper::generateOrganizationSchema(),
            'faq' => \App\Helpers\SeoHelper::generateFaqSchema()
        ];
        
        return view('front.welcome', compact('schema'));
    }
}
