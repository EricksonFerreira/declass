<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plano de atendimento especializado do aluno (PEI/PDI/PAEE):
 * diagnostico, necessidades, objetivos, estrategias e controle
 * de status/revisao pelo profissional de apoio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos_atendimento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo', 20);
            $table->string('status', 20)->default('ativo');
            $table->date('data_elaboracao')->nullable();
            $table->date('proxima_revisao')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('necessidades')->nullable();
            $table->text('objetivos');
            $table->text('estrategias')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'aluno_id']);
            $table->index(['empresa_id', 'escola_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos_atendimento');
    }
};
