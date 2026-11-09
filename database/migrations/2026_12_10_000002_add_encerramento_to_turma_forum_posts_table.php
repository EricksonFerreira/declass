<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moderacao do forum: o topico pode ser "encerrado" (desabilitado
 * para novas respostas) pelo autor ou pelo professor. O conteudo
 * continua visivel — diferente da remocao (soft delete), que so
 * o professor/coordenacao pode fazer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turma_forum_posts', function (Blueprint $table) {
            $table->timestamp('encerrada_em')->nullable()->after('mensagem');
            $table->unsignedBigInteger('encerrada_por')->nullable()->after('encerrada_em');
        });
    }

    public function down(): void
    {
        Schema::table('turma_forum_posts', function (Blueprint $table) {
            $table->dropColumn(['encerrada_em', 'encerrada_por']);
        });
    }
};
