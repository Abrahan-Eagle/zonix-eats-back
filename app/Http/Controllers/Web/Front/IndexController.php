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
        \App\Helpers\SeoHelper::setTitle('Plataforma reutilizable');
        \App\Helpers\SeoHelper::setDescription(
            'Boilerplate Laravel + Flutter con autenticación, perfiles, notificaciones y panel admin.'
        );

        $schema = [
            'app' => \App\Helpers\SeoHelper::generateAppSchema(),
            'organization' => \App\Helpers\SeoHelper::generateOrganizationSchema(),
            'faq' => \App\Helpers\SeoHelper::generateFaqSchema(),
        ];

        return view('front.welcome', compact('schema'));
    }
}
