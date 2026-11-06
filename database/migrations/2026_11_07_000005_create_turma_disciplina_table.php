<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oferta de disciplina por turma: quais componentes curriculares
 * a turma cursa e com qual carga horaria semanal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turma_disciplina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->unsignedSmallInteger('carga_horaria_semanal')->nullable();
            $table->timestamps();

            $table->unique(['turma_id', 'disciplina_id'], 'turma_disciplina_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turma_disciplina');
    }
};
