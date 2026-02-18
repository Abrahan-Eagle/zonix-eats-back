<?php

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    /**
     * Registrar un nuevo suscriptor en la lista de espera.
     * Endpoint público sin autenticación.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:rfc,dns|max:255',
            'source' => 'nullable|string|in:hero,cta',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, ingresa un correo electrónico válido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Si ya existe, retornar éxito silencioso (no revelar que ya está registrado)
        $existing = Subscriber::where('email', $request->email)->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => '¡Listo! Te avisaremos cuando haya novedades.',
            ]);
        }

        Subscriber::create([
            'email' => $request->email,
            'source' => $request->source ?? 'unknown',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Listo! Te avisaremos cuando haya novedades.',
        ], 201);
    }
}
