<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarefa/dever de casa atribuido por um professor a uma turma
 * (opcionalmente vinculada a uma disciplina), com data de
 * entrega e nota maxima opcional para correcao.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->text('instrucoes')->nullable();
            $table->date('data_entrega');
            $table->decimal('nota_maxima', 5, 2)->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'turma_id', 'data_entrega']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
