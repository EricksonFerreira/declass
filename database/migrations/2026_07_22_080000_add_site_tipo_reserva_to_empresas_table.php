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
            if (!Schema::hasColumn('empresas', 'site_tipo_reserva')) {
                $table->enum('site_tipo_reserva', ['motorista_app', 'reserva_comum', 'ambas'])
                      ->default('ambas')
                      ->after('aluguel_app_ativo');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (Schema::hasColumn('empresas', 'site_tipo_reserva')) {
                $table->dropColumn('site_tipo_reserva');
            }
        });
    }
};
