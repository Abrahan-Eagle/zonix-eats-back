<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountDeletionController extends Controller
{
    /**
     * Solicitar eliminación de cuenta
     */
    public function requestAccountDeletion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:500',
                'feedback' => 'nullable|string|max:1000',
                'immediate' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $reason = $request->input('reason');
            $feedback = $request->input('feedback');
            $immediate = $request->input('immediate', false);

            // Verificar si ya existe una solicitud pendiente
            $existingRequest = $this->getMockDeletionRequest($user->id);
            if ($existingRequest && $existingRequest['status'] === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una solicitud de eliminación pendiente'
                ], 400);
            }

            // Generar código de confirmación
            $confirmationCode = Str::random(6);

            // En producción, esto se guardaría en la base de datos
            $deletionRequest = [
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'reason' => $reason,
                'feedback' => $feedback,
                'immediate' => $immediate,
                'confirmation_code' => $confirmationCode,
                'status' => 'pending',
                'scheduled_for' => $immediate ? now() : now()->addDays(30),
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            // Simular envío de email con código de confirmación
            $this->sendConfirmationEmail($user->email, $confirmationCode);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de eliminación enviada. Revisa tu email para el código de confirmación.',
                'data' => [
                    'deletion_id' => $deletionRequest['id'],
                    'scheduled_for' => $deletionRequest['scheduled_for'],
                    'immediate' => $immediate,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al solicitar eliminación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar eliminación de cuenta
     */
    public function confirmAccountDeletion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'confirmation_code' => 'required|string|size:6',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $confirmationCode = $request->input('confirmation_code');
            $password = $request->input('password');

            // Verificar contraseña
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ], 401);
            }

            // Verificar código de confirmación
            $deletionRequest = $this->getMockDeletionRequest($user->id);
            if (!$deletionRequest || $deletionRequest['confirmation_code'] !== $confirmationCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de confirmación inválido'
                ], 400);
            }

            if ($deletionRequest['status'] !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud de eliminación no está pendiente'
                ], 400);
            }

            // Procesar eliminación de cuenta
            $this->processAccountDeletion($user);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta eliminada correctamente',
                'data' => [
                    'deleted_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar eliminación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar solicitud de eliminación
     */
    public function cancelDeletionRequest()
    {
        try {
            $user = Auth::user();

            $deletionRequest = $this->getMockDeletionRequest($user->id);
            if (!$deletionRequest || $deletionRequest['status'] !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay una solicitud de eliminación pendiente'
                ], 400);
            }

            // En producción, esto se actualizaría en la base de datos
            $deletionRequest['status'] = 'cancelled';
            $deletionRequest['cancelled_at'] = now()->toISOString();
            $deletionRequest['updated_at'] = now()->toISOString();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de eliminación cancelada correctamente',
                'data' => $deletionRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar eliminación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estado de la solicitud de eliminación
     */
    public function getDeletionStatus()
    {
        try {
            $user = Auth::user();

            $deletionRequest = $this->getMockDeletionRequest($user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'has_pending_request' => $deletionRequest && $deletionRequest['status'] === 'pending',
                    'status' => $deletionRequest ? $deletionRequest['status'] : null,
                    'requested_at' => $deletionRequest ? $deletionRequest['created_at'] : null,
                    'scheduled_for' => $deletionRequest ? $deletionRequest['scheduled_for'] : null,
                    'reason' => $deletionRequest ? $deletionRequest['reason'] : null,
                    'immediate' => $deletionRequest ? $deletionRequest['immediate'] : false,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener solicitud mock de eliminación
     */
    private function getMockDeletionRequest($userId)
    {
        // En producción, esto se consultaría de la base de datos
        $requests = [
            $userId => [
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'user_id' => $userId,
                'reason' => 'Ya no uso la aplicación',
                'feedback' => 'La aplicación funciona bien, pero ya no la necesito',
                'immediate' => false,
                'confirmation_code' => 'ABC123',
                'status' => 'pending',
                'scheduled_for' => now()->addDays(30)->toISOString(),
                'created_at' => now()->subDays(1)->toISOString(),
                'updated_at' => now()->subDays(1)->toISOString(),
            ],
        ];

        return $requests[$userId] ?? null;
    }

    /**
     * Enviar email de confirmación (simulado)
     */
    private function sendConfirmationEmail($email, $confirmationCode)
    {
        // En producción, usar Laravel Mail
        \Log::info("Email de confirmación enviado a {$email} con código: {$confirmationCode}");
    }

    /**
     * Procesar eliminación de cuenta
     */
    private function processAccountDeletion($user)
    {
        // En producción, esto eliminaría todos los datos del usuario
        // de manera segura y cumpliendo con GDPR
        
        \Log::info("Cuenta eliminada para usuario: {$user->id}");
        
        // Simular eliminación de datos relacionados
        $this->deleteUserData($user->id);
        
        // Cerrar sesión
        Auth::logout();
    }

    /**
     * Eliminar datos del usuario
     */
    private function deleteUserData($userId)
    {
        // En producción, esto eliminaría:
        // - Perfil del usuario
        // - Historial de pedidos
        // - Reseñas
        // - Direcciones
        // - Notificaciones
        // - Actividad
        // - Configuraciones
        
        \Log::info("Datos eliminados para usuario: {$userId}");
    }
}
