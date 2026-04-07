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
            if (!Schema::hasColumn('empresas', 'reserva_atraso_max_minutos')) {
                $table->unsignedInteger('reserva_atraso_max_minutos')
                    ->nullable()
                    ->after('reserva_selecionar_modelo');
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
            if (Schema::hasColumn('empresas', 'reserva_atraso_max_minutos')) {
                $table->dropColumn('reserva_atraso_max_minutos');
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};
