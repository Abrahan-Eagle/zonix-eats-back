<?php

namespace App\Http\Controllers\GasTicket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasTicket;


class AdminController extends Controller
{
    // Función para cerrar el ciclo de actividades
    public function closeCycle()
    {
        // Obtener todos los tickets del día
        $tickets = GasTicket::where('date', now()->format('Y-m-d'))
                            ->where('status', 'pending')
                            ->get();

        // Actualizar el estado de todos los tickets a 'closed'
        foreach ($tickets as $ticket) {
            $ticket->update(['status' => 'closed']);
        }

        return response()->json([
            'message' => 'Ciclo de actividades cerrado, todos los tickets pendientes fueron actualizados a cerrados.'
        ], 200);
    }
}
