<?php

namespace App\Http\Controllers\GasTicket\Admin;

use App\Http\Controllers\Controller;
use App\Models\GasTicket;
use Illuminate\Http\Request;

class SalesAdminController extends Controller
{

    // Cambiar estado a 'verifying'
    public function verifyTicket($id)
    {
        // Buscar el ticket
        $ticket = GasTicket::findOrFail($id);

        if ($ticket->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'The ticket is not in pending state'
            ], 400);
        }

        // Cambiar el estado a 'verifying'
        $ticket->status = 'verifying';
        $ticket->save();

        // Cargar las relaciones del ticket después de actualizar el estado
        // $ticket = $ticket->load([
        //     'profile',                       // Carga el perfil
        //     'profile.user',                  // Carga el usuario a través del perfil
        //     'profile.phones',                // Carga los teléfonos a través del perfil
        //     'profile.emails',                // Carga los correos electrónicos a través del perfil
        //     'profile.documents',             // Carga los documentos a través del perfil
        //     'profile.addresses',             // Carga las direcciones a través del perfil
        //     'profile.gasCylinders',          // Carga los cilindros de gas a través del perfil
        //     'gasCylinder'                    // Carga el cilindro de gas directamente asociado al ticket
        // ]);

        $ticket = $ticket->load([
            'profile',
            'profile.user',
            'profile.phones.operatorCode',
            'profile.emails',
            'profile.documents',
            'profile.addresses',
            'profile.gasCylinders',
            'gasCylinder',
            'station',
        ]);

        // Responder con el ticket actualizado y sus relaciones
        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Ticket marked as verifying'
        ]);
    }

    // Cambiar estado a 'waiting'
    public function markAsWaiting($id)
    {
        $ticket = GasTicket::findOrFail($id);

        if ($ticket->status != 'verifying') {
            return response()->json([
                'success' => false,
                'message' => 'The ticket is not in verifying state'
            ], 400);
        }

        $ticket->status = 'waiting';
        $ticket->save();

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Ticket marked as waiting'
        ]);
    }





    // Obtener lista de tickets en estado pendiente, verificando o en espera
    public function qrCode($qrCodeId)
    {
        $tickets = GasTicket::with([
            'profile',                       // Carga el perfil
            'profile.user',                  // Carga el usuario a través del perfil
            // 'profile.phones',                // Carga los teléfonos a través del perfil
            'profile.phones.operatorCode',
            'profile.emails',                // Carga los correos electrónicos a través del perfil
            'profile.documents',             // Carga los documentos a través del perfil
            'profile.addresses',             // Carga las direcciones a través del perfil
            'profile.gasCylinders',          // Carga los cilindros de gas a través del perfil
            'gasCylinder',                    // Carga el cilindro de gas directamente asociado al ticket
            'station'
        ])->where('qr_code', $qrCodeId)
        ->whereIn('status', ['waiting'])->get();

        if ($tickets->isEmpty()) {
            return response()->json(['message' => 'No gas tickets found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'message' => 'Ticket'
        ]);
    }


    // public function qrCodeGasCylinderAdminSale($qrCodeId)
    // {
    //     $tickets = GasTicket::with([
    //        'profile.gasCylinders',          // Carga los cilindros de gas a través del perfil

    //     ])->where('qr_code', $qrCodeId)
    //     ->whereIn('status', ['verifying'])->get();

    //     if ($tickets->isEmpty()) {
    //         return response()->json(['message' => 'No gas tickets found'], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $tickets,
    //         'message' => 'Ticket'
    //     ]);
    // }

    public function qrCodeGasCylinderAdminSale($qrCodeId)
        {
            $tickets = GasTicket::with([
                'profile.gasCylinders', // Carga los cilindros de gas a través del perfil
            ])
            ->where('qr_code', $qrCodeId)
            ->whereIn('status', ['verifying'])
            ->get();

            if ($tickets->isEmpty()) {
                return response()->json(['message' => 'No gas tickets found'], 404);
            }

            // Extrae solo los códigos de los cilindros de gas
            $data = $tickets->pluck('profile.gasCylinders.*.gas_cylinder_code')->flatten();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Ticket'
            ]);
        }




    // Cambiar estado a 'dispatched'
    public function dispatchTicket($id)
    {
        $ticket = GasTicket::findOrFail($id);

        if ($ticket->status != 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'The ticket is not in waiting state'
            ], 400);
        }

        $ticket->status = 'dispatched';
        $ticket->save();

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Ticket marked as dispatched'
        ]);
    }

    // Cambiar estado a 'canceled'
    public function cancelTicket($id)
    {
        $ticket = GasTicket::findOrFail($id);

        if ($ticket->status != 'verifying') {
            return response()->json([
                'success' => false,
                'message' => 'The ticket is not in verifying state'
            ], 400);
        }

        $ticket->status = 'canceled';
        $ticket->save();

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Ticket marked as canceled'
        ]);
    }
}
