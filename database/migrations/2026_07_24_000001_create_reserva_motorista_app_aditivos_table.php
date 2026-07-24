<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reserva_motorista_app_aditivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('reserva_motorista_app_id')->constrained('reservas_motorista_app')->cascadeOnDelete();
            $table->foreignId('pagamento_id')->nullable()->constrained('reserva_motorista_app_pagamentos')->nullOnDelete();
            $table->foreignId('contrato_checkin_id')->nullable()->constrained('contrato_checkin')->nullOnDelete();
            $table->unsignedInteger('numero_aditivo');
            $table->dateTime('data_inicio_anterior');
            $table->dateTime('data_fim_anterior');
            $table->dateTime('data_inicio_nova');
            $table->dateTime('data_fim_nova');
            $table->foreignId('plano_anterior_id')->nullable()->constrained('planos_motorista_app')->nullOnDelete();
            $table->foreignId('plano_novo_id')->nullable()->constrained('planos_motorista_app')->nullOnDelete();
            $table->decimal('valor_renovacao', 12, 2);
            $table->decimal('valor_opcionais', 12, 2)->default(0);
            $table->decimal('valor_desconto', 12, 2)->default(0);
            $table->decimal('valor_total', 12, 2);
            $table->string('status')->default('aguardando_pagamento');
            $table->longText('texto_contrato')->nullable();
            $table->json('snapshot_json')->nullable();
            $table->longText('assinatura')->nullable();
            $table->dateTime('assinado_em')->nullable();
            $table->string('ip_assinatura', 45)->nullable();
            $table->text('user_agent_assinatura')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->unique(['reserva_motorista_app_id', 'numero_aditivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_motorista_app_aditivos');
    }
};
