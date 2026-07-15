<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (!Schema::hasColumn('empresas', 'aluguel_app_ativo')) {
                $table->enum('aluguel_app_ativo', ['S', 'N'])->default('N')->after('reserva_bloqueio_pos_reserva_horas');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (Schema::hasColumn('empresas', 'aluguel_app_ativo')) {
                $table->dropColumn('aluguel_app_ativo');
            }
        });
    }
};
