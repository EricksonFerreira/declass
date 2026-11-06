<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grade horaria: horario_slots define a matriz de horarios da escola
 * por turno (dias letivos x aulas com hora inicio/fim); horarios_turma
 * posiciona as aulas de cada turma nos slots (disciplina, professor
 * opcional e sala).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->string('turno', 20);
            $table->unsignedTinyInteger('dia_semana'); // 1=seg ... 7=dom
            $table->unsignedTinyInteger('ordem');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->unique(['escola_id', 'turno', 'dia_semana', 'ordem'], 'horario_slots_unique');
            $table->index(['empresa_id', 'escola_id', 'turno']);
        });

        Schema::create('horarios_turma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->unsignedTinyInteger('ordem');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sala', 40)->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['turma_id', 'dia_semana', 'ordem'], 'horarios_turma_celula');
            $table->index(['empresa_id', 'escola_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_turma');
        Schema::dropIfExists('horario_slots');
    }
};
