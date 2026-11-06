<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turma: agrupamento de alunos de uma escola em um ano letivo,
 * cursando uma etapa. Base para matriculas, diario e avaliacoes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('ano_letivo_id')->constrained('anos_letivos')->cascadeOnDelete();
            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('turno', 20)->nullable();
            $table->string('sala', 40)->nullable();
            $table->unsignedSmallInteger('capacidade_maxima')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'escola_id']);
            $table->index('ano_letivo_id');
            $table->index('etapa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};
