<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Emprestimo de livro por aluno. status 'ativo'/'devolvido';
 * atraso e derivado de data_devolucao_prevista < hoje.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emprestimos_livros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('livro_id')->constrained('livros')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->date('data_emprestimo');
            $table->date('data_devolucao_prevista');
            $table->date('data_devolucao')->nullable();
            $table->string('status', 15)->default('ativo');
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'livro_id', 'status']);
            $table->index(['empresa_id', 'aluno_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprestimos_livros');
    }
};
