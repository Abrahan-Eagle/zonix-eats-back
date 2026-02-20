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
        return view('front.welcome');
    }
}
