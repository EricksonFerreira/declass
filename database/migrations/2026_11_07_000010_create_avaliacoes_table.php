<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avaliacao: instrumento de medida (prova, trabalho, simulada,
 * recuperacao) aplicado a uma disciplina de uma turma.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 20)->default('prova');
            $table->string('nome', 150);
            $table->string('periodo', 30)->nullable();
            $table->date('data')->nullable();
            $table->decimal('nota_maxima', 6, 2)->default(10);
            $table->text('descricao')->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'turma_id']);
            $table->index(['turma_id', 'disciplina_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
