<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diario de classe: registro de uma aula (turma x disciplina x data)
 * ministrada por um professor, com conteudo e observacoes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('data');
            $table->text('conteudo');
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['turma_id', 'disciplina_id', 'data'], 'diarios_unique_aula');
            $table->index(['empresa_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diarios');
    }
};
