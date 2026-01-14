<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega índices para mejorar el rendimiento de consultas frecuentes:
     * - orders: status, created_at, profile_id, commerce_id
     * - profiles: status
     * - notifications: profile_id, created_at
     * - chat_messages: order_id, created_at
     * - users: created_at
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Índice simple para status (usado frecuentemente en WHERE)
            try {
                $table->index('status', 'orders_status_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice simple para created_at (usado frecuentemente en ORDER BY y WHERE)
            try {
                $table->index('created_at', 'orders_created_at_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice simple para profile_id (usado en WHERE para obtener órdenes del usuario)
            try {
                $table->index('profile_id', 'orders_profile_id_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice simple para commerce_id (usado en WHERE para obtener órdenes del comercio)
            try {
                $table->index('commerce_id', 'orders_commerce_id_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice compuesto para consultas comunes: commerce_id + status + created_at
            // Usado en: OrderController::index() con filtros
            try {
                $table->index(['commerce_id', 'status', 'created_at'], 'orders_commerce_status_created_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice compuesto para consultas de usuario: profile_id + created_at
            // Usado en: OrderService::getUserOrders()
            try {
                $table->index(['profile_id', 'created_at'], 'orders_profile_created_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            // Índice para status (usado en WHERE para filtrar usuarios activos/suspendidos)
            try {
                $table->index('status', 'profiles_status_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Índice para profile_id (usado en WHERE para obtener notificaciones del usuario)
            try {
                $table->index('profile_id', 'notifications_profile_id_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice compuesto para consultas comunes: profile_id + created_at
            // Usado en: NotificationController::getNotifications()
            try {
                $table->index(['profile_id', 'created_at'], 'notifications_profile_created_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            // Índice para order_id (usado en WHERE para obtener mensajes de una orden)
            try {
                $table->index('order_id', 'chat_messages_order_id_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
            
            // Índice compuesto para consultas comunes: order_id + created_at
            // Usado en: ChatController::getMessages()
            try {
                $table->index(['order_id', 'created_at'], 'chat_messages_order_created_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Índice para created_at (usado en WHERE para analytics y reportes)
            try {
                $table->index('created_at', 'users_created_at_index');
            } catch (\Exception $e) {
                // Índice ya existe, continuar
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices con manejo de errores
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_status_index');
                $table->dropIndex('orders_created_at_index');
                $table->dropIndex('orders_profile_id_index');
                $table->dropIndex('orders_commerce_id_index');
                $table->dropIndex('orders_commerce_status_created_index');
                $table->dropIndex('orders_profile_created_index');
            });
        } catch (\Exception $e) {
            // Ignorar errores en rollback
        }

        try {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropIndex('profiles_status_index');
            });
        } catch (\Exception $e) {
            // Ignorar errores en rollback
        }

        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_profile_id_index');
                $table->dropIndex('notifications_profile_created_index');
            });
        } catch (\Exception $e) {
            // Ignorar errores en rollback
        }

        try {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropIndex('chat_messages_order_id_index');
                $table->dropIndex('chat_messages_order_created_index');
            });
        } catch (\Exception $e) {
            // Ignorar errores en rollback
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_created_at_index');
            });
        } catch (\Exception $e) {
            // Ignorar errores en rollback
        }
    }

};
