<?php
namespace App\Http\Controllers\GasTicket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasTicket;
use App\Models\Profile;
use App\Models\GasCylinder;
use App\Models\Station;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;

class GasTicketController extends Controller
{
    /**
     * Display a listing of gas tickets.
     */
    public function index()
    {
        // Obtener todos los tickets de gas con las relaciones profile y gas_cylinder
        $tickets = GasTicket::with(['profile', 'gasCylinder'])->get();
        return response()->json($tickets);
    }


    public function store(Request $request)
    {

        // Log::info('xxxxxxxxxxxxxxxxxxxxxxxxxx Datos recibidos:', $request->all());



            // Obtener el profile_id a partir del user_id recibido
        if ($request->has('user_id')) {
            $profile = Profile::where('user_id', $request->user_id)->first();
            if (!$profile) {
                return response()->json(['message' => 'Profile not found for the given user ID'], 404);
            }
            // Asignar el profile_id al request
            $request->merge(['profile_id' => $profile->id]);
        }

        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|exists:profiles,id',
            'gas_cylinders_id' => 'required|exists:gas_cylinders,id',
            'station_id' => 'nullable|exists:stations,id', // Si aplica una estación distinta
            'is_external' => 'boolean' // Checkbox para usuarios externos
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }





        // // Validar los datos de la solicitud
        // $validator = Validator::make($request->all(), [
        //     'profile_id' => 'required|exists:profiles,id',
        //     'gas_cylinders_id' => 'required|exists:gas_cylinders,id',
        //     'station_id' => 'nullable|exists:stations,id', // Si aplica una estación distinta
        //     'is_external' => 'boolean' // Checkbox para usuarios externos
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 400);
        // }




        $lastTicket = GasTicket::where('profile_id', $request->profile_id)
            ->where('gas_cylinders_id', $request->gas_cylinders_id)
            ->orderBy('appointment_date', 'desc')
            ->first();

        if ($lastTicket) {
            if (in_array($lastTicket->status, ['pending', 'verifying', 'waiting'])) {
                return response()->json(['message' => 'You cannot generate a new ticket while another one is pending, verifying, or waiting.'], 400);
            }

            if ($lastTicket->status === 'dispatched') {
                $daysSinceLastAppointment = Carbon::now()->diffInDays($lastTicket->appointment_date);
                if ($daysSinceLastAppointment < 21) {
                    return response()->json([
                        'message' => 'You must wait ' . (21 - $daysSinceLastAppointment) . ' more days before generating a new ticket.'
                    ], 400);
                }
            }
        }



        $profile = Profile::find($request->profile_id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        // Obtener la estación asignada al perfil del usuario
        $assignedStation = $profile->station_id;

        // Validar si es domingo y el checkbox para usuarios externos
        $isSunday = Carbon::now()->isSunday();
        $isExternal = $request->is_external ?? false;

        if ($isExternal) {
            // Validar que sea domingo para permitir el cambio de estación
            if (!$isSunday) {
                return response()->json(['message' => 'External appointments are only allowed on Sundays'], 400);
            }

            // Verificar que el usuario haya seleccionado una estación diferente
            if (!$request->station_id) {
                return response()->json(['message' => 'You must select a station for external appointments'], 400);
            }

            $stationId = $request->station_id;

            $appointmentDate = Carbon::now()->timezone('America/Caracas')->addDay()->startOfDay(); // Cita para el lunes. Cita para el siguiente día hábil permitido


            } else {
            // Usuario interno: validar que la estación asignada permite citas en el día actual
            if (!$assignedStation) {
                return response()->json(['message' => 'No station assigned to the user'], 400);
            }

            $station = Station::find($assignedStation);
            if (!$station) {
                return response()->json(['message' => 'Assigned station not found'], 404);
            }

            // Validar si el día actual está dentro de los días disponibles de la estación

            $currentDay = Carbon::now()->timezone('America/Caracas')->format('l'); // Ejemplo: "Tuesday"
            $daysAvailable = explode(',', $station->days_available); // Convertir a array

            if (!in_array($currentDay, $daysAvailable)) {
                return response()->json(['message' => 'Appointments are not allowed at the assigned station today'], 400);
            }

            $stationId = $assignedStation;
            $appointmentDate = Carbon::now()->addDay()->startOfDay(); // Cita para el siguiente día hábil permitido
        }

        // Verificar límite diario de tickets para la estación seleccionada
        $maxDailyAppointments = 200; // Límite diario
        $dailyAppointments = GasTicket::whereDate('appointment_date', $appointmentDate)
            ->where('station_id', $stationId)
            ->count();

        if ($dailyAppointments >= $maxDailyAppointments) {
            return response()->json(['message' => 'Daily appointment limit reached for this station'], 400);
        }

        // Continuar con la lógica de generación de tickets...
        // Código previo para posición en la cola, hora de llegada, etc.
        $queuePosition = GasTicket::whereDate('appointment_date', $appointmentDate)
            ->where('station_id', $stationId)
            ->count() + 1;

        $lastTimePosition = GasTicket::whereDate('appointment_date', $appointmentDate)
            ->where('station_id', $stationId)
            ->orderBy('time_position', 'desc')
            ->first();

        $timePosition = $lastTimePosition
            ? Carbon::parse($lastTimePosition->time_position)->addMinute()
            : Carbon::parse('09:00:00');

        // Crear el ticket de gas
        $ticket = new GasTicket();
        $ticket->profile_id = $request->profile_id;
        $ticket->gas_cylinders_id = $request->gas_cylinders_id;
        $ticket->station_id = $stationId; // Estación asignada o seleccionada
        $ticket->queue_position = $queuePosition;
        $ticket->time_position = $timePosition->format('H:i');

        $ticket->reserved_date = Carbon::now()->timezone('America/Caracas'); // Fecha de reserva es hoy
        $ticket->appointment_date = Carbon::now()->timezone('America/Caracas')->addDay(); // Cita es al día siguiente
        $ticket->expiry_date = Carbon::now()->timezone('America/Caracas')->addDay(2); // Expira 2 días después


        $ticket->status = 'pending';



        $gasCylinder = GasCylinder::find($request->gas_cylinders_id);
        if ($gasCylinder) {
            $ticket->qr_code = $gasCylinder->gas_cylinder_code;
        } else {
            return response()->json(['error' => 'Gas cylinder not found for the given profile ID'], 400);
        }




        $ticket->save();

        return response()->json([
            'message' => 'Gas ticket created successfully',
            'ticket' => $ticket
        ], 201);
    }



    /**
     * Display the specified gas ticket.
     */
    public function show($id)
    {
        $profile = Profile::where('user_id', $id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $tickets = GasTicket::with([
            'profile',
            'profile.user',
            'profile.phones.operatorCode',
            'profile.emails',
            'profile.documents',
            'profile.addresses',
            'profile.gasCylinders',
            'gasCylinder',
            'station',
        ])->where('profile_id', $profile->id)->get();



        if ($tickets->isEmpty()) {
            return response()->json(['message' => 'No gas tickets found'], 404);
        }

        return response()->json($tickets);
    }


    public function getGasCylinders($id)
    {

        $profile = Profile::where('user_id', $id)->first();


        $gasCylinders = GasCylinder::with(['profile', 'gasSupplier'])
            -> where('profile_id', $profile->id)
            -> where('approved', true) // Filtrar bombonas aprobadas
            -> get();

        return response()->json($gasCylinders);
    }


    public function getGasStations()
    {
        $stations = Station::where('active', true)->get();
        // Verifica si estás obteniendo los datos correctamente
        return response()->json($stations);
    }



    /**
     * Update the specified gas ticket.
     */
    public function update(Request $request, $id)
    {
        // Buscar el ticket de gas por ID
        $ticket = GasTicket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Gas ticket not found'], 404);
        }

        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'status' => 'in:pending,verifying,waiting,dispatched,canceled,expired',
            'appointment_date' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Actualizar el ticket con los nuevos datos
        $ticket->status = $request->status ?? $ticket->status;
        $ticket->appointment_date = $request->appointment_date ?? $ticket->appointment_date;
        $ticket->expiry_date = $request->has('appointment_date') ? Carbon::parse($request->appointment_date)->addDay() : $ticket->expiry_date;
        $ticket->save();

        return response()->json(['message' => 'Gas ticket updated successfully', 'ticket' => $ticket]);
    }

    /**
     * Remove the specified gas ticket from storage.
     */
    public function destroy($id)
    {
        // Buscar el ticket de gas por ID
        $ticket = GasTicket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Gas ticket not found'], 404);
        }

        // Eliminar el ticket de gas
        $ticket->delete();

        return response()->json(['message' => 'Gas ticket deleted successfully']);
    }

    /**
     * Generate a unique QR code for the gas ticket.
     */
    private function generateQRCode()
    {
        // Lógica para generar un código QR único
        return 'QR' . strtoupper(Str::random(10)); // Ejemplo simple, deberías usar un paquete QR para generar códigos QR reales
    }
}
