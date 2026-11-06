<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ano letivo: periodo letivo de uma escola/unidade.
 * Turmas e matriculas futuras referenciam o ano letivo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anos_letivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->smallInteger('ano');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('status', 20)->default('letivo');
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'escola_id', 'ano'], 'anos_letivos_unique');
            $table->index('escola_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anos_letivos');
    }
};
