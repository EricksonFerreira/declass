<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80);
            $table->string('slug', 80)->unique();
            $table->text('descricao')->nullable();
            $table->decimal('valor_mensal', 10, 2);
            $table->decimal('valor_anual', 10, 2)->comment('Valor mensal cobrado no plano anual (com desconto)');
            $table->integer('limite_veiculos')->nullable()->comment('null = ilimitado');
            $table->boolean('site_vendas')->default(false);
            $table->boolean('modulo_multas')->default(false);
            $table->boolean('modulo_nfse')->default(false);
            $table->boolean('multi_estacao')->default(false);
            $table->boolean('api_integracao')->default(false);
            $table->boolean('ativo')->default(true);
            $table->boolean('destaque')->default(false)->comment('Plano mais escolhido / recomendado');
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('itens_plano', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->string('descricao', 200);
            $table->boolean('incluso')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('empresa_planos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos');
            $table->enum('periodicidade', ['mensal', 'anual'])->default('mensal');
            $table->enum('status', ['ativo', 'inadimplente', 'cancelado', 'trial', 'aguardando_pagamento'])->default('trial');
            $table->enum('metodo_cobranca', ['boleto', 'automatico', 'manual'])->default('manual');
            $table->date('data_inicio');
            $table->date('data_vencimento');
            $table->date('data_renovacao')->nullable()->comment('Próxima data de cobrança automática');
            $table->integer('dia_vencimento')->nullable()->comment('Dia do mês para cobranças automáticas');
            $table->decimal('valor_cobrado', 10, 2)->comment('Valor real no momento da contratação');
            $table->string('gateway_cobranca', 40)->nullable()->comment('stripe, asaas, manual, etc.');
            $table->string('gateway_subscription_id', 200)->nullable()->comment('ID da assinatura no gateway');
            $table->string('gateway_customer_id', 200)->nullable();
            $table->json('metadados')->nullable();
            $table->foreignId('criado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'status']);
        });

        Schema::create('empresa_plano_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_plano_id')->constrained('empresa_planos')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->decimal('valor', 10, 2);
            $table->enum('periodicidade', ['mensal', 'anual'])->default('mensal');
            $table->enum('metodo', ['boleto', 'cartao', 'pix', 'manual', 'transferencia'])->default('boleto');
            $table->enum('status', ['pendente', 'pago', 'vencido', 'cancelado'])->default('pendente');
            $table->date('data_vencimento');
            $table->timestamp('pago_em')->nullable();
            $table->string('gateway_payment_id', 200)->nullable();
            $table->string('boleto_url', 500)->nullable();
            $table->string('boleto_codigo', 500)->nullable();
            $table->string('nosso_numero', 100)->nullable();
            $table->json('metadados')->nullable();
            $table->foreignId('registrado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'status', 'data_vencimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_plano_pagamentos');
        Schema::dropIfExists('empresa_planos');
        Schema::dropIfExists('itens_plano');
        Schema::dropIfExists('planos');
    }
};
