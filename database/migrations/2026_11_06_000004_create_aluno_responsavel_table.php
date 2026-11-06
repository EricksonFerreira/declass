<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vinculo N:N aluno <-> responsavel com papel (tipo), flags de
 * autorizacao (retirada, contato) e marcacao de responsavel
 * principal. Um mesmo responsavel pode ter papeis distintos
 * para o mesmo aluno (ex.: legal e financeiro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_responsavel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('responsavel_id')->constrained('responsaveis')->cascadeOnDelete();
            $table->string('tipo', 20)->default('legal');
            $table->boolean('principal')->default(false);
            $table->boolean('pode_retirar')->default(false);
            $table->boolean('autoriza_contato')->default(true);
            $table->text('restricoes')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['aluno_id', 'responsavel_id', 'tipo'], 'aluno_responsavel_unique');
            $table->index('responsavel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_responsavel');
    }
};
