<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contas a receber: receitas nao-tuition da escola/rede (taxas,
 * material, uniforme, transporte, eventos, servicos etc.).
 * Ciclo: prevista -> recebida (com data_recebimento) ou cancelada.
 * aluno_id opcional vincula a cobranca a um aluno especifico.
 * Base do fluxo de caixa e do DRE (outras receitas).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_receber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->nullOnDelete();
            $table->foreignId('aluno_id')->nullable()->constrained('alunos')->nullOnDelete();
            $table->string('descricao');
            $table->string('categoria', 40)->default('outros');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->string('status', 20)->default('prevista'); // prevista|recebida|cancelada
            $table->string('forma_recebimento', 30)->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
            $table->index(['empresa_id', 'data_vencimento']);
            $table->index(['empresa_id', 'categoria']);
            $table->index(['empresa_id', 'aluno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_receber');
    }
};
