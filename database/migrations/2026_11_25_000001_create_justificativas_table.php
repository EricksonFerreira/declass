<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Justificativas/abonos de ausencia registrados pela secretaria:
 * atestado medico, declaracao de compromisso, convocacao judicial
 * etc. Ao serem registrados, convertem as faltas do periodo em
 * 'justificada' ou 'abonada' (efeito), mantendo a referencia em
 * frequencias.justificativa_id para permitir reversao.
 * disciplina_id nulo = vale para todas as disciplinas do aluno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->nullOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('tipo', 40);
            $table->string('efeito', 15)->default('justificada'); // justificada | abonada
            $table->text('motivo')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['matricula_id', 'data_inicio', 'data_fim'], 'justif_matricula_periodo_idx');
            $table->index(['empresa_id', 'escola_id']);
        });

        Schema::table('frequencias', function (Blueprint $table) {
            $table->foreignId('justificativa_id')->nullable()->after('justificativa')
                ->constrained('justificativas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('justificativa_id');
        });
        Schema::dropIfExists('justificativas');
    }
};
