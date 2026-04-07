<?php

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use Illuminate\View\View;

/**
 * Enlace público compartido desde la app: GET /r/{id} → abre Zonix con el comercio (deep link).
 */
class StorefrontLinkController extends Controller
{
    public function show(int $commerce): View
    {
        $model = Commerce::query()->find($commerce);

        if ($model === null) {
            abort(404);
        }

        $deepLink = 'zonix://restaurant/'.$model->id;

        return view('front.storefront.commerce_link', [
            'commerce' => $model,
            'deepLink' => $deepLink,
            'pageUrl' => url()->current(),
        ]);
    }
}
