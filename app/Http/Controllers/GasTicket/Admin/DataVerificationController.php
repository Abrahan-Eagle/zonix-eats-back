<?php

namespace App\Http\Controllers\GasTicket\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasCylinder;
use App\Models\Document;
use App\Models\Address;
use App\Models\Phone;
use App\Models\Email;
use App\Models\Profile;

class DataVerificationController extends Controller
{
    public function getdataVerifications($profile_id)
    {
        // Validar el profile_id
        if (!is_numeric($profile_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid profile ID'
            ], 400);
        }

        // Respuesta inicial por defecto
        $response = [
            'status' => 'error',
            'data' => null,
            'message' => 'No data found for the provided profile ID'
        ];

        // Lógica de verificación
        switch (true) {
            case $profile = Profile::where('id', $profile_id)
                ->whereIn('status', ['notverified', 'incompleteData'])
                ->first():
                $response['status'] = 'success';
                $response['data'] = 'profile';
                $response['message'] = 'Profile found for the provided profile ID';
                break;

            case Address::where('profile_id', $profile_id)
                ->whereIn('status', ['notverified', 'incompleteData'])
                ->exists():
                $response['status'] = 'success';
                $response['data'] = 'addresses';
                $response['message'] = 'Addresses found for the provided profile ID';
                break;

            case GasCylinder::where('profile_id', $profile_id)
                ->where('approved', false)
                ->exists():
                $response['status'] = 'success';
                $response['data'] = 'gasCylinders';
                $response['message'] = 'Gas cylinders found for the provided profile ID';
                break;

            case Document::where('profile_id', $profile_id)
                ->where('approved', false)
                ->where('status', true)
                ->exists():
                $response['status'] = 'success';
                $response['data'] = 'documents';
                $response['message'] = 'Documents found for the provided profile ID';
                break;

            case Phone::where('profile_id', $profile_id)
                ->where('status', false)
                ->exists():
                $response['status'] = 'success';
                $response['data'] = 'phones';
                $response['message'] = 'Phones found for the provided profile ID';
                break;

            case Email::where('profile_id', $profile_id)
                ->where('status', false)
                ->exists():
                $response['status'] = 'success';
                $response['data'] = 'emails';
                $response['message'] = 'Emails found for the provided profile ID';
                break;
        }

        return response()->json($response, $response['status'] === 'success' ? 200 : 404);
    }



    public function updateVerificationsProfiles(Request $request, $profile_id)
    {
        try {
            // Validar que se envíe el selectedOptionId en la solicitud
            $validatedData = $request->validate([
                'selectedOptionId' => 'required|integer'
            ]);

            $selectedOptionId = $validatedData['selectedOptionId'];

            // Recuperar el perfil asociado al profile_id
            $profile = Profile::find($profile_id);

            // Verificar si el perfil existe
            if (!$profile) {
                return response()->json([
                    'message' => 'No se encontró el perfil con el ID proporcionado.'
                ], 404);
            }

            // Guardar el selectedOptionId en la base de datos
            $profile->station_id = $selectedOptionId; // Asegúrate de que exista la columna station_id en tu tabla profiles
            $profile->status = 'completeData'; // Actualizar el estado del perfil
            $profile->save();

            // Responder con un mensaje de éxito
            return response()->json([
                'message' => 'El perfil fue actualizado exitosamente.',
                'profile' => $profile // Opcional: incluir los datos actualizados del perfil
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Manejo de excepciones generales si algo falla
            return response()->json([
                'message' => 'Error al actualizar el perfil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function updateVerificationsDocuments($profile_id)
        {
            try {
                // Recuperar todos los documentos asociados al perfil
                $documents = Document::where('profile_id', $profile_id)->get();

                // Verificar si existen documentos asociados
                if ($documents->isEmpty()) {
                    return response()->json([
                        'message' => 'No se encontraron documentos asociados a este perfil.'
                    ], 404);
                }

                // Actualizar el estado de cada documento a 'completeData'
                foreach ($documents as $document) {
                    $document->approved = true;
                    $document->save();
                }

                // Responder con un mensaje de éxito
                return response()->json([
                    'message' => 'Los documentos fueron actualizados exitosamente a completeData.'
                ], 200);

            } catch (\Exception $e) {
                // Manejo de excepciones si algo falla
                return response()->json([
                    'message' => 'Error al actualizar los documentos.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }


    public function updateVerificationsAddresses($profile_id)
    {
        try {
            // Recuperar todas las direcciones asociadas al perfil
            $addresses = Address::where('profile_id', $profile_id)->get();

            // Verificar si existen direcciones asociadas
            if ($addresses->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron direcciones asociadas a este perfil.'
                ], 404);
            }

            // Actualizar el estado de cada dirección a 'completeData'
            foreach ($addresses as $address) {
                $address->status = 'completeData';
                $address->save();
            }

            // Responder con un mensaje de éxito
            return response()->json([
                'message' => 'Las direcciones fueron actualizadas exitosamente a completeData.'
            ], 200);

        } catch (\Exception $e) {
            // Manejo de excepciones si algo falla
            return response()->json([
                'message' => 'Error al actualizar las direcciones.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateVerificationsGasCylinders($profile_id)
        {
            try {
                // Recuperar todos los cilindros de gas asociados al perfil
                $gasCylinders = GasCylinder::where('profile_id', $profile_id)->get();

                // Verificar si existen cilindros de gas asociados
                if ($gasCylinders->isEmpty()) {
                    return response()->json([
                        'message' => 'No se encontraron cilindros de gas asociados a este perfil.'
                    ], 404);
                }

                // Actualizar el estado de cada cilindro de gas a 'completeData'
                foreach ($gasCylinders as $gasCylinder) {
                    $gasCylinder->approved = true;
                    $gasCylinder->save();
                }

                // Responder con un mensaje de éxito
                return response()->json([
                    'message' => 'Los cilindros de gas fueron actualizados exitosamente a completeData.'
                ], 200);

            } catch (\Exception $e) {
                // Manejo de excepciones si algo falla
                return response()->json([
                    'message' => 'Error al actualizar los cilindros de gas.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        public function updateVerificationsPhones($profile_id)
            {
                try {
                    // Recuperar todos los teléfonos asociados al perfil
                    $phones = Phone::where('profile_id', $profile_id)->get();

                    // Verificar si existen teléfonos asociados
                    if ($phones->isEmpty()) {
                        return response()->json([
                            'message' => 'No se encontraron teléfonos asociados a este perfil.'
                        ], 404);
                    }

                    // Actualizar el estado de cada teléfono a 'completeData'
                    foreach ($phones as $phone) {
                        $phone->status = true;
                        $phone->save();
                    }

                    // Responder con un mensaje de éxito
                    return response()->json([
                        'message' => 'Los teléfonos fueron actualizados exitosamente a completeData.'
                    ], 200);

                } catch (\Exception $e) {
                    // Manejo de excepciones si algo falla
                    return response()->json([
                        'message' => 'Error al actualizar los teléfonos.',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }


            public function updateVerificationsEmails($profile_id)
                {
                    try {
                        // Recuperar todos los correos electrónicos asociados al perfil
                        $emails = Email::where('profile_id', $profile_id)->get();

                        // Verificar si existen correos electrónicos asociados
                        if ($emails->isEmpty()) {
                            return response()->json([
                                'message' => 'No se encontraron correos electrónicos asociados a este perfil.'
                            ], 404);
                        }

                        // Actualizar el estado de cada correo electrónico a 'completeData'
                        foreach ($emails as $email) {
                            $email->status = true;
                            $email->save();
                        }

                        // Responder con un mensaje de éxito
                        return response()->json([
                            'message' => 'Los correos electrónicos fueron actualizados exitosamente a completeData.'
                        ], 200);

                    } catch (\Exception $e) {
                        // Manejo de excepciones si algo falla
                        return response()->json([
                            'message' => 'Error al actualizar los correos electrónicos.',
                            'error' => $e->getMessage()
                        ], 500);
                    }
                }


}
