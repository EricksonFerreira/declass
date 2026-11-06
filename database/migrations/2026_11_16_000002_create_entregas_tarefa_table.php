<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entrega de uma tarefa por matricula: status
 * (pendente/entregue/avaliada/atrasada derivavel), nota e
 * data de entrega. Uma linha por aluno por tarefa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas_tarefa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('tarefa_id')->constrained('tarefas')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->string('status', 20)->default('pendente');
            $table->decimal('nota', 5, 2)->nullable();
            $table->date('data_entrega')->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->unique(['tarefa_id', 'matricula_id']);
            $table->index(['empresa_id', 'matricula_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas_tarefa');
    }
};
