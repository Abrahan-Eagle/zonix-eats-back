<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\Profile;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Get conversations (orders with chat messages)
     * Adaptado de CorralX: aquí las "conversaciones" son órdenes con chat
     */
    public function getConversations()
    {
        try {
            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            // Obtener órdenes que tienen mensajes de chat o que pertenecen al usuario
            $orders = Order::where(function($query) use ($profileId) {
                    $query->where('profile_id', $profileId)
                          ->orWhereHas('chatMessages', function($q) use ($profileId) {
                              $q->where('sender_id', $profileId);
                          })
                          ->orWhereHas('commerce', function($q) use ($profileId) {
                              $q->whereHas('profile', function($q2) use ($profileId) {
                                  $q2->where('id', $profileId);
                              });
                          })
                          ->orWhereHas('orderDelivery', function($q) use ($profileId) {
                              $q->whereHas('agent', function($q2) use ($profileId) {
                                  $q2->where('profile_id', $profileId);
                              });
                          });
                })
                ->with(['commerce', 'profile', 'orderDelivery.agent.profile', 'chatMessages' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(1);
                }])
                ->orderByDesc('updated_at')
                ->get()
                ->map(function($order) use ($profileId) {
                    $lastMessage = $order->chatMessages->first();
                    $unreadCount = ChatMessage::where('order_id', $order->id)
                        ->where('sender_id', '!=', $profileId)
                        ->whereNull('read_at')
                        ->count();

                    return [
                        'id' => $order->id, // Usamos order_id como conversation_id
                        'type' => 'order',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? "ORD-{$order->id}",
                        'status' => $order->status,
                        'last_message' => $lastMessage ? [
                            'id' => $lastMessage->id,
                            'content' => $lastMessage->content,
                            'sender_id' => $lastMessage->sender_id,
                            'type' => $lastMessage->type,
                            'timestamp' => $lastMessage->created_at->toIso8601String(),
                            'read' => $lastMessage->read_at !== null,
                        ] : null,
                        'unread_count' => $unreadCount,
                        'participants' => $this->getParticipants($order),
                        'created_at' => $order->created_at->toIso8601String(),
                        'updated_at' => $order->updated_at->toIso8601String(),
                    ];
                });

            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('Error getting conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conversaciones'
            ], 500);
        }
    }

    /**
     * Get messages for a conversation (order)
     */
    public function getMessages($conversationId)
    {
        try {
            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $order = Order::findOrFail($conversationId);

            // Verificar acceso: usuario debe ser cliente, comercio o delivery de la orden
            if (!$this->hasAccessToOrder($order, $profileId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta conversación'
                ], 403);
            }

            $messages = ChatMessage::where('order_id', $conversationId)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($message) use ($profileId) {
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'content' => $message->content,
                        'type' => $message->type,
                        'sender_type' => $message->sender_type,
                        'sender_name' => trim(($message->sender->firstName ?? '') . ' ' . ($message->sender->lastName ?? '')) ?: 'Usuario',
                        'sender_avatar' => $message->sender->photo_users ?? null,
                        'is_own_message' => $message->sender_id == $profileId,
                        'read' => $message->read_at !== null,
                        'timestamp' => $message->created_at->toIso8601String(),
                        'created_at' => $message->created_at->format('H:i'),
                    ];
                });

            return response()->json($messages);
        } catch (\Exception $e) {
            Log::error('Error getting messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mensajes'
            ], 500);
        }
    }

    /**
     * Send message in a conversation (order)
     */
    public function sendMessage(Request $request, $conversationId)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:2000',
                'type' => 'nullable|in:text,image,location',
            ]);

            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $order = Order::findOrFail($conversationId);

            // Verificar acceso
            if (!$this->hasAccessToOrder($order, $profileId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta conversación'
                ], 403);
            }

            // Determinar sender_type basado en el rol del usuario
            $senderType = $this->getSenderType($user, $order, $profileId);

            // Crear mensaje
            $message = ChatMessage::create([
                'order_id' => $order->id,
                'sender_id' => $profileId,
                'sender_type' => $senderType,
                'recipient_type' => 'all', // Por defecto para todos los participantes
                'content' => $request->content,
                'type' => $request->type ?? 'text',
            ]);

            $message->load('sender');

            // Actualizar timestamp de la orden
            $order->touch();

            // Broadcast del mensaje (si tienes eventos configurados)
            // broadcast(new MessageSent($message, $order->id))->toOthers();

            // 🔔 Enviar notificación push al receptor SOLO si la app del receptor está en background/cerrada
            $this->sendPushNotification($order, $message, $profileId);

            return response()->json($message, 201);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar mensaje'
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead($conversationId)
    {
        try {
            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $order = Order::findOrFail($conversationId);

            // Verificar acceso
            if (!$this->hasAccessToOrder($order, $profileId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta conversación'
                ], 403);
            }

            // Marcar mensajes como leídos
            ChatMessage::where('order_id', $conversationId)
                ->where('sender_id', '!=', $profileId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json(['marked' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking messages as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar mensajes como leídos'
            ], 500);
        }
    }

    /**
     * Create conversation (crear chat para una orden)
     */
    public function createConversation(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $order = Order::findOrFail($request->order_id);

            // Verificar acceso
            if (!$this->hasAccessToOrder($order, $profileId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a esta orden'
                ], 403);
            }

            // El chat se crea automáticamente cuando se envía el primer mensaje
            // Por ahora solo retornamos la información de la orden como "conversación"
            return response()->json([
                'id' => $order->id,
                'type' => 'order',
                'order_id' => $order->id,
                'order_number' => $order->order_number ?? "ORD-{$order->id}",
                'status' => $order->status,
                'participants' => $this->getParticipants($order),
                'created_at' => $order->created_at->toIso8601String(),
                'updated_at' => $order->updated_at->toIso8601String(),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear conversación'
            ], 500);
        }
    }

    /**
     * Delete conversation (eliminar mensajes de una orden)
     */
    public function deleteConversation($conversationId)
    {
        try {
            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $order = Order::findOrFail($conversationId);

            // Solo el cliente puede eliminar sus mensajes
            if ($order->profile_id != $profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes eliminar mensajes de tus propias órdenes'
                ], 403);
            }

            // Eliminar solo los mensajes del usuario actual
            ChatMessage::where('order_id', $conversationId)
                ->where('sender_id', $profileId)
                ->delete();

            return response()->json(['deleted' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar conversación'
            ], 500);
        }
    }

    /**
     * Search messages
     */
    public function searchMessages(Request $request)
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1',
            ]);

            $user = Auth::user();
            $profileId = optional($user->profile)->id;

            if (!$profileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil asociado'
                ], 403);
            }

            $query = $request->input('q');

            // Buscar mensajes en órdenes a las que el usuario tiene acceso
            $messages = ChatMessage::where('content', 'like', "%{$query}%")
                ->whereHas('order', function($q) use ($profileId) {
                    $q->where(function($query) use ($profileId) {
                        $query->where('profile_id', $profileId)
                              ->orWhereHas('commerce', function($q) use ($profileId) {
                                  $q->whereHas('profile', function($q2) use ($profileId) {
                                      $q2->where('user_id', Auth::id());
                                  });
                              })
                              ->orWhereHas('orderDelivery', function($q) use ($profileId) {
                                  $q->whereHas('agent', function($q2) use ($profileId) {
                                      $q2->where('profile_id', $profileId);
                                  });
                              });
                    });
                })
                ->with(['order', 'sender'])
                ->limit(50)
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'type' => $message->type,
                        'sender_name' => trim(($message->sender->firstName ?? '') . ' ' . ($message->sender->lastName ?? '')) ?: 'Usuario',
                        'order_id' => $message->order_id,
                        'order_number' => $message->order->order_number ?? "ORD-{$message->order_id}",
                        'timestamp' => $message->created_at->toIso8601String(),
                    ];
                });

            return response()->json($messages);
        } catch (\Exception $e) {
            Log::error('Error searching messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar mensajes'
            ], 500);
        }
    }

    /**
     * Block user
     */
    public function blockUser(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            // TODO: Implementar sistema de bloqueo si es necesario
            // Por ahora solo retornamos éxito
            Log::info('User blocked', [
                'blocker_id' => Auth::id(),
                'blocked_user_id' => $request->user_id
            ]);

            return response()->json(['blocked' => true]);
        } catch (\Exception $e) {
            Log::error('Error blocking user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al bloquear usuario'
            ], 500);
        }
    }

    /**
     * Unblock user
     */
    public function unblockUser($userId)
    {
        try {
            // TODO: Implementar sistema de bloqueo si es necesario
            Log::info('User unblocked', [
                'unblocker_id' => Auth::id(),
                'unblocked_user_id' => $userId
            ]);

            return response()->json(['unblocked' => true]);
        } catch (\Exception $e) {
            Log::error('Error unblocking user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desbloquear usuario'
            ], 500);
        }
    }

    /**
     * Get blocked users
     */
    public function getBlockedUsers()
    {
        try {
            // TODO: Implementar sistema de bloqueo si es necesario
            // Por ahora retornamos lista vacía
            return response()->json([]);
        } catch (\Exception $e) {
            Log::error('Error getting blocked users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios bloqueados'
            ], 500);
        }
    }

    /**
     * Helper: Verificar si el usuario tiene acceso a una orden
     */
    private function hasAccessToOrder(Order $order, $profileId): bool
    {
        // Cliente de la orden
        if ($order->profile_id == $profileId) {
            return true;
        }

        // Comercio de la orden
        if ($order->commerce && $order->commerce->profile && $order->commerce->profile->user_id == Auth::id()) {
            return true;
        }

        // Delivery agent asignado
        if ($order->orderDelivery && $order->orderDelivery->agent && $order->orderDelivery->agent->profile_id == $profileId) {
            return true;
        }

        return false;
    }

    /**
     * Helper: Obtener tipo de remitente basado en rol
     */
    private function getSenderType($user, Order $order, $profileId): string
    {
        if ($order->profile_id == $profileId) {
            return 'customer';
        }

        if ($order->commerce && $order->commerce->profile && $order->commerce->profile->user_id == $user->id) {
            return 'restaurant';
        }

        if ($order->orderDelivery && $order->orderDelivery->agent && $order->orderDelivery->agent->profile_id == $profileId) {
            return 'delivery_agent';
        }

        return 'customer'; // Default
    }

    /**
     * Helper: Obtener participantes de una orden
     */
    private function getParticipants(Order $order): array
    {
        $participants = [];

        // Cliente
        if ($order->profile) {
            $participants[] = [
                'id' => $order->profile->id,
                'name' => trim(($order->profile->firstName ?? '') . ' ' . ($order->profile->lastName ?? '')) ?: 'Cliente',
                'role' => 'customer',
                'avatar' => $order->profile->photo_users ?? null,
            ];
        }

        // Comercio
        if ($order->commerce && $order->commerce->profile) {
            $participants[] = [
                'id' => $order->commerce->profile->id,
                'name' => $order->commerce->business_name ?? 'Restaurante',
                'role' => 'restaurant',
                'avatar' => $order->commerce->image ?? null,
            ];
        }

        // Delivery agent
        if ($order->orderDelivery && $order->orderDelivery->agent && $order->orderDelivery->agent->profile) {
            $participants[] = [
                'id' => $order->orderDelivery->agent->profile->id,
                'name' => trim(($order->orderDelivery->agent->profile->firstName ?? '') . ' ' . ($order->orderDelivery->agent->profile->lastName ?? '')) ?: 'Repartidor',
                'role' => 'delivery_agent',
                'avatar' => $order->orderDelivery->agent->profile->photo_users ?? null,
            ];
        }

        return $participants;
    }

    /**
     * Registrar device token de Firebase FCM para notificaciones push
     */
    public function registerFcmToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        // Manejar caso donde el usuario aún no tiene perfil creado
        if (!$profile) {
            Log::warning('⚠️ Intento de registrar FCM token sin perfil asociado', [
                'user_id' => $user?->id,
                'device_token_preview' => substr($request->device_token, 0, 20) . '...',
            ]);

            // No lanzar 500: simplemente informar al frontend para que pueda reintentar
            return response()->json([
                'status' => 'profile_missing',
                'message' => 'Token recibido pero el usuario aún no tiene perfil asociado.',
            ], 200);
        }

        // Guardar token en el perfil (evitar problemas de fillable)
        $profile->fcm_device_token = $request->device_token;
        $profile->save();

        Log::info('✅ FCM token registrado', [
            'profile_id' => $profile->id,
            'token' => substr($request->device_token, 0, 20) . '...'
        ]);

        return response()->json(['status' => 'token_registered']);
    }

    /**
     * Eliminar device token de Firebase FCM
     */
    public function unregisterFcmToken(Request $request)
    {
        $profile = Auth::user()->profile;

        if (!$profile) {
            Log::warning('⚠️ Intento de eliminar FCM token sin perfil asociado', [
                'user_id' => Auth::id(),
            ]);

            return response()->json(['status' => 'token_unregistered']);
        }

        $profile->update([
            'fcm_device_token' => null
        ]);

        Log::info('✅ FCM token eliminado', ['profile_id' => $profile->id]);

        return response()->json(['status' => 'token_unregistered']);
    }

    /**
     * Enviar notificación push al receptor del mensaje
     */
    private function sendPushNotification(Order $order, ChatMessage $message, $senderId)
    {
        Log::info('🚀 INICIO sendPushNotification', [
            'order_id' => $order->id,
            'message_id' => $message->id,
            'sender_id' => $senderId
        ]);
        
        try {
            // Obtener el receptor (el otro participante)
            $receiver = $this->getReceiverForOrder($order, $senderId);
            
            Log::info('🔍 Debug sendPushNotification', [
                'sender_id' => $senderId,
                'receiver' => $receiver ? $receiver->id : 'null',
                'has_fcm_token' => $receiver && $receiver->fcm_device_token ? 'YES' : 'NO',
                'token_preview' => $receiver && $receiver->fcm_device_token ? substr($receiver->fcm_device_token, 0, 20) . '...' : 'null'
            ]);
            
            if (!$receiver || !$receiver->fcm_device_token) {
                Log::info('⚠️ Receptor sin device token, no se envía push', [
                    'receiver_id' => $receiver ? $receiver->id : 'null',
                    'has_token' => $receiver && $receiver->fcm_device_token ? 'YES' : 'NO'
                ]);
                return;
            }

            // Obtener nombre del remitente (sender ya es un Profile)
            $sender = $message->sender;
            $senderName = trim(($sender->firstName ?? '') . ' ' . ($sender->lastName ?? '')) ?: 'Usuario';
            
            // Preparar snippet del mensaje (máximo 100 caracteres para notificación)
            $content = (string)$message->content; // Convertir a string si es Stringable
            $snippet = strlen($content) > 100 
                ? substr($content, 0, 97) . '...' 
                : $content;
            
            // Enviar notificación con datos estilo WhatsApp
            Log::info('🔥 LLAMANDO FirebaseService->sendToDevice', [
                'device_token' => substr($receiver->fcm_device_token, 0, 20) . '...',
                'title' => $senderName,
                'body' => $snippet
            ]);
            
            $firebaseService = new FirebaseService();
            $result = $firebaseService->sendToDevice(
                $receiver->fcm_device_token,
                $senderName, // Título: nombre del remitente
                $snippet,    // Cuerpo: snippet del mensaje
                [
                    'order_id' => (string)$order->id,
                    'message_id' => (string)$message->id,
                    'sender_id' => (string)$senderId,
                    'sender_name' => $senderName,
                    'snippet' => $snippet,
                    'full_message' => (string)$message->content,
                    'type' => 'chat_message',
                    'timestamp' => $message->created_at->timestamp,
                ]
            );
            
            Log::info('🔥 RESULTADO FirebaseService->sendToDevice', [
                'result' => $result ? 'SUCCESS' : 'FAILED'
            ]);

            Log::info('📬 Notificación push enviada', [
                'receiver_id' => $receiver->id,
                'sender_name' => $senderName,
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error enviando notificación push', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
        }
    }

    /**
     * Helper: Obtener el receptor del mensaje (el otro participante de la orden)
     */
    private function getReceiverForOrder(Order $order, $senderId)
    {
        // Si el remitente es el cliente, el receptor puede ser el comercio o el delivery
        if ($order->profile_id == $senderId) {
            // Priorizar delivery si está asignado, sino comercio
            if ($order->orderDelivery && $order->orderDelivery->agent && $order->orderDelivery->agent->profile) {
                return $order->orderDelivery->agent->profile;
            }
            if ($order->commerce && $order->commerce->profile) {
                return $order->commerce->profile;
            }
        }

        // Si el remitente es el comercio, el receptor es el cliente
        if ($order->commerce && $order->commerce->profile && $order->commerce->profile->id == $senderId) {
            return $order->profile;
        }

        // Si el remitente es el delivery, el receptor es el cliente
        if ($order->orderDelivery && $order->orderDelivery->agent && $order->orderDelivery->agent->profile_id == $senderId) {
            return $order->profile;
        }

        return null;
    }
}
