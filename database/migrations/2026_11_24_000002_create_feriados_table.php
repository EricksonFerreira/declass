<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feriados e dias sem aula da escola: nomeados por periodo,
 * com flag de suspensao de aulas (feriado com aula normal
 * existiria como ponto facultativo/decorativo).
 * escola_id nulo = vale para toda a rede.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->nullOnDelete();
            $table->string('nome', 150);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->string('suspende_aulas', 1)->default('S'); // S = sem aula; N = aula normal
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'data_inicio']);
            $table->index(['empresa_id', 'escola_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};
