<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media de aprovacao por oferta de disciplina na turma
 * (ex.: 6,0 ou 7,0). Nulo = padrao do sistema (7,0).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turma_disciplina', function (Blueprint $table) {
            $table->decimal('media_aprovacao', 4, 1)->nullable()->after('carga_horaria_semanal');
        });
    }

    public function down(): void
    {
        Schema::table('turma_disciplina', function (Blueprint $table) {
            $table->dropColumn('media_aprovacao');
        });
    }
};
