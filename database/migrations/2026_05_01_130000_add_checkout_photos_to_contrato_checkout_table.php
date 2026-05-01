<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contrato_checkout')) {
            return;
        }

        Schema::table('contrato_checkout', function (Blueprint $table) {
            $columns = [
                'foto_direita_path',
                'foto_esquerda_path',
                'foto_frente_path',
                'foto_tras_path',
                'foto_velocimetro_path',
                'foto_combustivel_path',
            ];

            foreach ($columns as $column) {
                if (!Schema::hasColumn('contrato_checkout', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('contrato_checkout')) {
            return;
        }

        Schema::table('contrato_checkout', function (Blueprint $table) {
            $columns = [
                'foto_direita_path',
                'foto_esquerda_path',
                'foto_frente_path',
                'foto_tras_path',
                'foto_velocimetro_path',
                'foto_combustivel_path',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('contrato_checkout', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
