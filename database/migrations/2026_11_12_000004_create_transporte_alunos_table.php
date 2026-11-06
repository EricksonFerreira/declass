<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passageiro do transporte: aluno vinculado a uma rota e,
 * opcionalmente, a uma parada especifica. Um aluno so pode
 * ter uma atribuicao ativa por vez (regra no service).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transporte_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('rota_id')->constrained('rotas_transporte')->cascadeOnDelete();
            $table->foreignId('parada_id')->nullable()->constrained('paradas_transporte')->nullOnDelete();
            $table->string('ativo', 1)->default('S');
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['aluno_id', 'rota_id'], 'transporte_alunos_unique');
            $table->index(['empresa_id', 'rota_id']);
            $table->index('aluno_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_alunos');
    }
};
