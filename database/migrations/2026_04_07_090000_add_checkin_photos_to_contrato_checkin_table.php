<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('contrato_checkin')) {
            return;
        }

        Schema::table('contrato_checkin', function (Blueprint $table) {
            // Defensive: only add columns if they don't exist (legacy DBs).
            $cols = [
                'foto_direita_path',
                'foto_esquerda_path',
                'foto_frente_path',
                'foto_tras_path',
                'foto_velocimetro_path',
                'foto_combustivel_path',
            ];

            foreach ($cols as $col) {
                if (!Schema::hasColumn('contrato_checkin', $col)) {
                    $table->string($col, 255)->nullable();
                }
            }
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('contrato_checkin')) {
            return;
        }

        Schema::table('contrato_checkin', function (Blueprint $table) {
            $cols = [
                'foto_direita_path',
                'foto_esquerda_path',
                'foto_frente_path',
                'foto_tras_path',
                'foto_velocimetro_path',
                'foto_combustivel_path',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('contrato_checkin', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};

