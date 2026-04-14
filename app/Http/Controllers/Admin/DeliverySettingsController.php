<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySetting;
use Illuminate\Http\Request;

class DeliverySettingsController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => DeliverySetting::getConfig(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'base_cost' => 'required|numeric|min:0',
            'cost_per_km' => 'required|numeric|min:0',
            'free_km' => 'required|numeric|min:0',
            'fee_min' => 'required|numeric|min:0',
            'fee_max' => 'required|numeric|min:0',
        ]);

        $config = DeliverySetting::getConfig();
        $config->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de delivery actualizada.',
            'data' => $config,
        ]);
    }
}
