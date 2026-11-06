<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_matricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('ano_letivo_id')->nullable()->constrained('anos_letivos')->nullOnDelete();
            $table->foreignId('etapa_id')->nullable()->constrained('etapas')->nullOnDelete();
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->nullOnDelete();

            // aluno pretendido
            $table->string('nome_aluno');
            $table->date('data_nascimento_aluno')->nullable();
            $table->string('sexo_aluno', 15)->nullable();

            // responsavel que solicitou
            $table->string('nome_responsavel');
            $table->string('parentesco', 60)->nullable();
            $table->string('telefone_responsavel', 30)->nullable();
            $table->string('email_responsavel')->nullable();
            $table->string('cpf_responsavel', 20)->nullable();

            $table->string('origem', 20)->default('interno');
            $table->string('status', 20)->default('pendente')->index();
            $table->text('observacoes')->nullable();

            // analise
            $table->unsignedBigInteger('analisado_por')->nullable();
            $table->timestamp('data_analise')->nullable();
            $table->text('motivo_recusa')->nullable();

            // resultado da aprovacao
            $table->foreignId('aluno_id')->nullable()->constrained('alunos')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('responsaveis')->nullOnDelete();
            $table->foreignId('matricula_id')->nullable()->constrained('matriculas')->nullOnDelete();

            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_matricula');
    }
};
