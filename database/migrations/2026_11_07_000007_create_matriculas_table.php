<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Matricula: vinculo de um aluno a uma turma em um ano letivo.
 * Mantem status (ativa/trancada/cancelada/concluida), numero de
 * chamada e datas. ano_letivo_id e denormalizado da turma para
 * simplificar filtros e relatorios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('ano_letivo_id')->constrained('anos_letivos')->cascadeOnDelete();
            $table->string('status', 20)->default('ativa');
            $table->unsignedSmallInteger('numero_chamada')->nullable();
            $table->date('data_matricula')->nullable();
            $table->date('data_saida')->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['aluno_id', 'turma_id'], 'matriculas_unique');
            $table->unique(['turma_id', 'numero_chamada'], 'matriculas_chamada_unique');
            $table->index(['empresa_id', 'status']);
            $table->index('ano_letivo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
