<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empresas') && !Schema::hasColumn('empresas', 'reserva_escolher_modelo')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->string('reserva_escolher_modelo', 1)->default('N');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('empresas') && Schema::hasColumn('empresas', 'reserva_escolher_modelo')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn('reserva_escolher_modelo');
            });
        }
    }
};
