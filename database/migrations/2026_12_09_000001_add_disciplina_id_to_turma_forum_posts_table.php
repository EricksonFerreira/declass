<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O forum deixa de ser geral da turma e passa a ser por disciplina:
 * topicos e respostas ganham disciplina_id (nulo = post antigo do
 * forum geral, mantido so para historico — nao aparece nas telas).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turma_forum_posts', function (Blueprint $table) {
            $table->foreignId('disciplina_id')->nullable()->after('turma_id')
                ->constrained('disciplinas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('turma_forum_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disciplina_id');
        });
    }
};
