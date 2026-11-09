<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estrutura do "classroom" do portal do aluno:
 * materiais da turma, forum, solicitacoes/documentos solicitados
 * e questoes das atividades (objetivas e discursivas).
 */
return new class extends Migration
{
    private function audit(Blueprint $table): void
    {
        $table->unsignedBigInteger('usuinc')->nullable();
        $table->timestamp('datainc')->nullable();
        $table->unsignedBigInteger('usuatu')->nullable();
        $table->timestamp('datatu')->nullable();
        $table->timestamps();
        $table->softDeletes();
    }

    public function up(): void
    {
        // Material de apoio postado pelo professor na turma (link ou arquivo)
        Schema::create('turma_materiais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('arquivo_path')->nullable();
            $this->audit($table);

            $table->index(['empresa_id', 'turma_id']);
        });

        // Forum da turma: posts (parent_id nulo) e comentarios (parent_id = post)
        Schema::create('turma_forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('titulo', 200)->nullable();
            $table->text('mensagem');
            $this->audit($table);

            $table->index(['empresa_id', 'turma_id', 'parent_id']);
        });

        // Pedidos da familia/aluno para a secretaria (declaracao, atendimento, outros)
        Schema::create('solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->nullable()->constrained('alunos')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo', 30)->default('outro');
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->string('status', 20)->default('aberta');
            $table->text('resposta')->nullable();
            $table->unsignedBigInteger('respondida_por')->nullable();
            $table->timestamp('respondida_em')->nullable();
            $this->audit($table);

            $table->index(['empresa_id', 'escola_id', 'status']);
        });

        // Documento que a escola solicita da familia (RG, comprovante...)
        Schema::create('documento_solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo', 50)->default('outro');
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->string('status', 20)->default('pendente');
            $table->string('arquivo_path')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $this->audit($table);

            $table->index(['empresa_id', 'aluno_id', 'status']);
        });

        // Questao de uma atividade/prova: objetiva (com alternativas) ou discursiva
        Schema::create('tarefa_questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('tarefa_id')->constrained('tarefas')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem')->default(1);
            $table->string('tipo', 15)->default('discursiva');
            $table->text('enunciado');
            $table->json('alternativas')->nullable();
            $table->string('gabarito', 2)->nullable();
            $table->decimal('valor', 5, 2)->nullable();
            $this->audit($table);

            $table->index(['tarefa_id', 'ordem']);
        });

        // Resposta do aluno a cada questao na sua entrega
        Schema::create('entrega_tarefa_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('entrega_id')->constrained('entregas_tarefa')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('tarefa_questoes')->cascadeOnDelete();
            $table->text('resposta')->nullable();
            $table->boolean('correta')->nullable();
            $this->audit($table);

            $table->unique(['entrega_id', 'questao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_tarefa_respostas');
        Schema::dropIfExists('tarefa_questoes');
        Schema::dropIfExists('documento_solicitacoes');
        Schema::dropIfExists('solicitacoes');
        Schema::dropIfExists('turma_forum_posts');
        Schema::dropIfExists('turma_materiais');
    }
};
