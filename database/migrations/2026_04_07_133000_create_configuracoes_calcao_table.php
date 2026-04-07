<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('configuracoes_calcao')) {
            Schema::create('configuracoes_calcao', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();

                // Escopo: empresa (nullable grupo/modelo), grupo ou modelo
                $table->unsignedBigInteger('grupo_frota_id')->nullable()->index()
                    ->comment('NULL = regra de empresa; preenchido = regra de grupo');
                $table->unsignedBigInteger('modelo_id')->nullable()->index()
                    ->comment('NULL = regra de empresa/grupo; preenchido = regra de modelo');

                $table->string('tipo_regra_valor', 20)->default('fixo')
                    ->comment('fixo|percentual');
                $table->decimal('valor', 10, 2)->default(0)
                    ->comment('Valor fixo em reais ou percentual (ex: 30 = 30%)');
                $table->decimal('valor_minimo', 10, 2)->nullable();
                $table->decimal('valor_maximo', 10, 2)->nullable();
                $table->boolean('ativo')->default(true);

                $table->timestamps();
    });
        }

        if (!Schema::hasTable('calcoes')) {
            Schema::create('calcoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('reserva_id')->nullable()->index();
                $table->unsignedBigInteger('contrato_checkin_id')->nullable()->index();
                $table->unsignedBigInteger('cliente_id')->nullable()->index();

                // Stripe
                $table->string('payment_intent_id', 255)->nullable()->index();
                $table->string('payment_method_id', 255)->nullable();
                $table->string('stripe_customer_id', 255)->nullable();

                // Valores
                $table->decimal('valor_calculado', 10, 2)->default(0);
                $table->decimal('valor_autorizado', 10, 2)->nullable();
                $table->decimal('valor_capturado', 10, 2)->nullable();

                // Regra aplicada
                $table->string('tipo_regra_aplicada', 20)->nullable()
                    ->comment('fixo|percentual');
                $table->string('origem_regra_aplicada', 30)->nullable()
                    ->comment('empresa|grupo|modelo');
                $table->unsignedBigInteger('configuracao_calcao_id')->nullable();

                // Status
                $table->string('status', 50)->default('pendente')
                    ->comment('pendente|autorizado|parcialmente_capturado|capturado|cancelado|expirado|falhou');

                // Timestamps de eventos
                $table->datetime('data_hora_autorizacao')->nullable();
                $table->datetime('data_hora_captura')->nullable();
                $table->datetime('data_hora_cancelamento')->nullable();

                // Auditoria
                $table->unsignedBigInteger('usuario_responsavel_id')->nullable();
                $table->text('observacao')->nullable();
                $table->text('justificativa_captura')->nullable();
                $table->json('stripe_response')->nullable()
                    ->comment('Resposta completa do Stripe para auditoria');

                $table->timestamps();
            });
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('calcoes');
        Schema::dropIfExists('configuracoes_calcao');
        Schema::enableForeignKeyConstraints();
    }
};
