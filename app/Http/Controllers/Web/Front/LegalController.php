<?php

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function terms()
    {
        return view('front.pages.legal.terms');
    }

    public function privacy()
    {
        return view('front.pages.legal.privacy');
    }

    public function cookies()
    {
        return view('front.pages.legal.cookies');
    }

    public function security()
    {
        return view('front.pages.legal.security');
    }
}
