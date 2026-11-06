<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Liga a avaliacao ao periodo letivo estruturado. A coluna
 * 'periodo' (texto) continua existindo como rotulo desnormalizado
 * — o service a preenche com o nome do periodo vinculado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->foreignId('periodo_letivo_id')
                ->nullable()
                ->after('periodo')
                ->constrained('periodos_letivos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_letivo_id');
        });
    }
};
