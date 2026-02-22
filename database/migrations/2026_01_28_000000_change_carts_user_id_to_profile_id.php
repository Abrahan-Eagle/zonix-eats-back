<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Solo profiles (y user_roles) deben estar conectados a users.
     * El carrito pertenece al perfil del comprador, no al user.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('carts', 'profile_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('profile_id')->nullable()->after('id');
                $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            });
        }

        // Migrar datos: un carrito por user → asignar profile_id del primer profile de ese user
        if (Schema::hasColumn('carts', 'user_id')) {
            DB::table('carts')->orderBy('id')->chunk(100, function ($carts) {
                foreach ($carts as $cart) {
                    if (isset($cart->user_id)) {
                        $profileId = DB::table('profiles')->where('user_id', $cart->user_id)->value('id');
                        if ($profileId !== null) {
                            DB::table('carts')->where('id', $cart->id)->update(['profile_id' => $profileId]);
                        }
                    }
                }
            });
        }

        if (Schema::hasColumn('carts', 'user_id')) {
            /* 
            // MEDIDA DE EMERGENCIA: Comentamos esto porque el servidor está fallando al borrar la FK
            // y parece que no actualiza el archivo correctamente.
            
            try {
                Schema::table('carts', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('carts', function (Blueprint $table) {
                    $table->dropUnique(['user_id']);
                });
            } catch (\Throwable $e) {}

            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
            */
        }

        // Eliminar carritos sin perfil (usuarios sin profile) y marcar profile_id como NOT NULL
        DB::table('carts')->whereNull('profile_id')->delete();

        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable(false)->change();
            $table->unique('profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero quitar FK e índice único de profile_id (orden requerido por MySQL)
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropUnique(['profile_id']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('carts')->orderBy('id')->chunk(100, function ($carts) {
            foreach ($carts as $cart) {
                $userId = DB::table('profiles')->where('id', $cart->profile_id)->value('user_id');
                if ($userId !== null) {
                    DB::table('carts')->where('id', $cart->id)->update(['user_id' => $userId]);
                }
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('profile_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unique('user_id');
        });
    }
};
