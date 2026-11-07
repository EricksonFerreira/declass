<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eventos no calendario letivo: suspende_aulas diz se o evento
 * substitui o dia de aula (excursao, feriado local) ou apenas
 * convive (festa com aula normal). evento_turma limita o evento
 * a turmas especificas; pivot vazio = todas as turmas do alcance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('suspende_aulas', 1)->default('N')->after('status');
        });

        Schema::create('evento_turma', function (Blueprint $table) {
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();

            $table->primary(['evento_id', 'turma_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_turma');
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('suspende_aulas');
        });
    }
};
