<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Events\NotificationCreated;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    // Listar notificaciones del usuario autenticado
    public function getNotifications()
    {
        $profile = Auth::user()->load('profile')->profile;
        $notifications = Notification::where('profile_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $notifications]);
    }

    // Marcar una notificación como leída
    public function markAsRead($notificationId)
    {
        $profile = Auth::user()->load('profile')->profile;
        $notification = Notification::where('id', $notificationId)
            ->where('profile_id', $profile->id)
            ->firstOrFail();
        $notification->read_at = now();
        $notification->save();
        return response()->json(['success' => true]);
    }

    // Crear notificación (para pruebas/admin)
    public function store(Request $request)
    {
        $profile = Auth::user()->load('profile')->profile;
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|max:50',
            'data' => 'nullable|array',
        ]);
        $notification = Notification::create([
            'profile_id' => $profile->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'] ?? null,
            'data' => $data['data'] ?? null,
        ]);

        // Emitir evento para WebSocket
        event(new NotificationCreated($notification));

        return response()->json(['success' => true, 'data' => $notification]);
    }

    // Eliminar notificación
    public function delete($notificationId)
    {
        $profile = Auth::user()->load('profile')->profile;
        $notification = Notification::where('id', $notificationId)
            ->where('profile_id', $profile->id)
            ->firstOrFail();
        $notification->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Enviar notificación push usando Firebase
     */
    public function sendPushNotification(Request $request)
    {
        try {
            $profile = Auth::user()->load('profile')->profile;
            
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'nullable|string|max:50',
                'data' => 'nullable|array',
            ]);

            // Verificar que el usuario tenga device token
            if (!$profile->fcm_device_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no tiene device token registrado'
                ], 400);
            }

            // Verificar preferencias de notificaciones
            $preferences = $profile->notification_preferences ?? [];
            $notificationType = $data['type'] ?? 'system';
            
            // Verificar si el tipo de notificación está habilitado
            $typeKey = $notificationType . '_notifications';
            if (isset($preferences[$typeKey]) && !$preferences[$typeKey]) {
                Log::info('Notificación bloqueada por preferencias del usuario', [
                    'profile_id' => $profile->id,
                    'type' => $notificationType
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Este tipo de notificación está deshabilitado'
                ], 403);
            }

            // Enviar push notification usando FirebaseService
            $firebaseService = new FirebaseService();
            $result = $firebaseService->sendToDevice(
                $profile->fcm_device_token,
                $data['title'],
                $data['message'],
                array_merge($data['data'] ?? [], [
                    'type' => $notificationType,
                    'notification_id' => null, // Se creará después si es necesario
                ])
            );

            if ($result) {
                // Opcionalmente crear registro en BD
                $notification = Notification::create([
                    'profile_id' => $profile->id,
                    'title' => $data['title'],
                    'body' => $data['message'],
                    'type' => $notificationType,
                    'data' => $data['data'] ?? null,
                ]);

                // Emitir evento para WebSocket
                event(new NotificationCreated($notification));

                return response()->json([
                    'success' => true,
                    'message' => 'Notificación push enviada',
                    'data' => $notification
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar notificación push'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error enviando push notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración de notificaciones del usuario
     */
    public function getNotificationSettings()
    {
        try {
            $profile = Auth::user()->load('profile')->profile;
            
            // Valores por defecto
            $defaultSettings = [
                'push_notifications' => true,
                'email_notifications' => true,
                'sms_notifications' => false,
                'order_notifications' => true,
                'commission_notifications' => true,
                'maintenance_notifications' => true,
                'system_notifications' => true,
                'chat_notifications' => true,
                'quiet_hours' => [
                    'enabled' => false,
                    'start' => '22:00',
                    'end' => '08:00',
                ],
            ];

            // Obtener preferencias del usuario (si existen)
            $preferences = $profile->notification_preferences ?? [];
            
            // Combinar con valores por defecto
            $settings = array_merge($defaultSettings, $preferences);

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo notification settings', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración'
            ], 500);
        }
    }

    /**
     * Actualizar configuración de notificaciones del usuario
     */
    public function updateNotificationSettings(Request $request)
    {
        try {
            $profile = Auth::user()->load('profile')->profile;
            
            $data = $request->validate([
                'push_notifications' => 'nullable|boolean',
                'email_notifications' => 'nullable|boolean',
                'sms_notifications' => 'nullable|boolean',
                'order_notifications' => 'nullable|boolean',
                'commission_notifications' => 'nullable|boolean',
                'maintenance_notifications' => 'nullable|boolean',
                'system_notifications' => 'nullable|boolean',
                'chat_notifications' => 'nullable|boolean',
                'quiet_hours' => 'nullable|array',
                'quiet_hours.enabled' => 'nullable|boolean',
                'quiet_hours.start' => 'nullable|string|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
                'quiet_hours.end' => 'nullable|string|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
            ]);

            // Obtener preferencias actuales
            $currentPreferences = $profile->notification_preferences ?? [];
            
            // Combinar con nuevas preferencias
            $updatedPreferences = array_merge($currentPreferences, $data);
            
            // Actualizar en el perfil
            $profile->notification_preferences = $updatedPreferences;
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada',
                'data' => $updatedPreferences
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando notification settings', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }
} 