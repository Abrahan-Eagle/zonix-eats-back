<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Soporta multi-restaurante: un perfil commerce puede tener N comercios.
     * is_primary marca el restaurante principal (por defecto en dashboard/config).
     */
    public function up(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->boolean('is_primary')->default(true)->after('profile_id');
        });

        // Garantizar que solo un commerce por profile tenga is_primary (el primero)
        $profiles = \DB::table('commerces')->select('profile_id')->distinct()->pluck('profile_id');
        foreach ($profiles as $profileId) {
            $first = \DB::table('commerces')->where('profile_id', $profileId)->orderBy('id')->first();
            if ($first) {
                \DB::table('commerces')->where('profile_id', $profileId)->update(['is_primary' => false]);
                \DB::table('commerces')->where('id', $first->id)->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
