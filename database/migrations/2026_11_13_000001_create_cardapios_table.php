<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cardapio de alimentacao escolar: uma refeicao por dia por escola
 * (cafe da manha, almoco, lanche), com descricao dos itens e
 * restricoes alimentares (alergias, vegetariano etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cardapios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->date('data');
            $table->string('tipo', 30);
            $table->text('descricao');
            $table->string('restricoes_alimentares', 500)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['escola_id', 'data', 'tipo']);
            $table->index(['empresa_id', 'escola_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cardapios');
    }
};
