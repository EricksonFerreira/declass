<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'reserva_selecionar_modelo')) {
                if (Schema::hasColumn('empresas', 'reserva_escolher_empresa')) {
                    $table->char('reserva_selecionar_modelo', 1)->default('N')->after('reserva_escolher_empresa');
                } else {
                    $table->char('reserva_selecionar_modelo', 1)->default('N');
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'reserva_selecionar_modelo')) {
                $table->dropColumn('reserva_selecionar_modelo');
            }
        });
    }
};
