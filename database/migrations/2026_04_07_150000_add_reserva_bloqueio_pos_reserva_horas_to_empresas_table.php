<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
                $table->unsignedInteger('reserva_bloqueio_pos_reserva_horas')
                    ->nullable()
                    ->after('reserva_atraso_max_minutos');
            }
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
                $table->dropColumn('reserva_bloqueio_pos_reserva_horas');
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};

