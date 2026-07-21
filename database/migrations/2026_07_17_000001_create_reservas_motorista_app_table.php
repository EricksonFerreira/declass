<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reservas_motorista_app')) {
            Schema::create('reservas_motorista_app', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('motorista_app_solicitacao_id')
                    ->nullable()
                    ->constrained('motorista_app_solicitacoes')
                    ->nullOnDelete();
                $table->foreignId('plano_motorista_app_id')
                    ->nullable()
                    ->constrained('planos_motorista_app')
                    ->nullOnDelete();
                $table->unsignedInteger('estacao_retirada_id')->nullable();
                $table->string('status', 40)->default('pendente_pagamento');
                $table->string('status_pagamento', 30)->default('pendente');
                $table->boolean('pagamento_efetuado')->default(false);
                $table->dateTime('data_inicio')->nullable();
                $table->dateTime('data_fim')->nullable();
                $table->unsignedInteger('quantidade_dias')->nullable();
                $table->decimal('valor_plano', 10, 2)->default(0);
                $table->decimal('valor_opcionais', 10, 2)->default(0);
                $table->decimal('valor_total_final', 10, 2)->default(0);
                $table->json('opcionais_json')->nullable();
                $table->json('dados_json')->nullable();
                $table->json('documentos_json')->nullable();
                $table->text('observacoes')->nullable();
                $table->timestamps();

                $table->index(['empresa_id', 'user_id', 'status'], 'reserva_motorista_app_empresa_user_status_idx');
                $table->index(['empresa_id', 'pagamento_efetuado'], 'reserva_motorista_app_empresa_pagamento_idx');
                $table->index(['empresa_id', 'created_at'], 'reserva_motorista_app_empresa_created_idx');
                $table->foreign('estacao_retirada_id')
                    ->references('id')
                    ->on('estacoes')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_motorista_app');
    }
};
