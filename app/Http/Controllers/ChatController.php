<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated No está registrado en rutas. Mantener solo como referencia histórica.
 *             Producción: {@see \App\Http\Controllers\Chat\ChatController} (`/api/chat/*`, `/api/buyer/orders/{orderId}/messages`).
 *             Buyer legado por pedido: {@see \App\Http\Controllers\Buyer\ChatController} (`/api/buyer/chat/*`).
 */
class ChatController extends Controller
{
    /**
     * Enviar un mensaje en el chat de una orden.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, $orderId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Stub legacy: mismo contrato que Chat\ChatController (canal orders.{id})
        $profileId = (int) ($user->profile?->id ?? $user->id);
        event(new NewMessage(
            (int) $orderId,
            [
                'content' => $validated['message'],
                'type' => 'text',
            ],
            $profileId,
            $user->name ?? 'Usuario',
            (string) $user->role,
        ));

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado',
        ]);
    }

    /**
     * Obtener historial de mensajes de una orden.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages($orderId)
    {
        // En producción, obtendrías los mensajes desde la base de datos
        // Por ahora, retornamos un array vacío
        $messages = [];

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }
}
