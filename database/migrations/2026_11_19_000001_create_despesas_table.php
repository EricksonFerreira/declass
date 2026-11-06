<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Despesas: contas a pagar da escola/rede (fornecedores, pessoal,
 * material, utilidades etc.). Base do fluxo de caixa e do DRE.
 * escola_id nulo = despesa da rede toda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->nullOnDelete();
            $table->string('fornecedor')->nullable();
            $table->string('descricao');
            $table->string('categoria', 40)->default('outros');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->string('status', 20)->default('prevista'); // prevista|paga|cancelada
            $table->string('forma_pagamento', 30)->nullable();
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
