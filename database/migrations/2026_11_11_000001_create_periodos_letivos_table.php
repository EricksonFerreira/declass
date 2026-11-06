<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Periodo letivo: subdivisao do ano letivo (bimestre, trimestre,
 * semestre etc.). A quantidade e os nomes sao configuraveis por
 * escola/ano — ex.: escola regular usa 4 bimestres, faculdade
 * usa 2 semestres de ~6 meses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_letivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('ano_letivo_id')->constrained('anos_letivos')->cascadeOnDelete();
            $table->string('nome', 40);
            $table->unsignedTinyInteger('ordem');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ano_letivo_id', 'ordem'], 'periodos_letivos_unique');
            $table->index(['empresa_id', 'ano_letivo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_letivos');
    }
};
