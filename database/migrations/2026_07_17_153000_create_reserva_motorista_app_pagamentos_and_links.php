<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reserva_motorista_app_pagamentos')) {
            Schema::create('reserva_motorista_app_pagamentos', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('reserva_motorista_app_id');
                $table->foreign('reserva_motorista_app_id', 'rmap_pags_rmap_id_fk')
                    ->references('id')
                    ->on('reservas_motorista_app')
                    ->cascadeOnDelete();
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('periodos')->default(1);
                $table->string('periodo_unidade', 30)->nullable();
                $table->decimal('valor_unitario', 10, 2)->default(0);
                $table->decimal('valor_total', 10, 2)->default(0);
                $table->dateTime('data_inicio_prevista')->nullable();
                $table->dateTime('data_fim_prevista')->nullable();
                $table->string('status', 30)->default('pendente');
                $table->string('metodo_pagamento', 30)->nullable();
                $table->dateTime('pago_em')->nullable();
                $table->string('referencia_pagamento', 100)->nullable();
                $table->text('observacoes')->nullable();
                $table->json('metadados')->nullable();
                $table->timestamps();

                $table->index(['reserva_motorista_app_id', 'status'], 'rmap_pags_reserva_status_idx');
                $table->index(['empresa_id', 'status'], 'rmap_pags_empresa_status_idx');
            });
        }

        if (Schema::hasTable('multas') && !Schema::hasColumn('multas', 'reserva_motorista_app_id')) {
            Schema::table('multas', function (Blueprint $table): void {
                $table->unsignedBigInteger('reserva_motorista_app_id')->nullable()->index()->after('reserva_id');
            });
        }

        if (Schema::hasTable('sinistros') && !Schema::hasColumn('sinistros', 'reserva_motorista_app_id')) {
            Schema::table('sinistros', function (Blueprint $table): void {
                $table->unsignedBigInteger('reserva_motorista_app_id')->nullable()->index()->after('reserva_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sinistros') && Schema::hasColumn('sinistros', 'reserva_motorista_app_id')) {
            Schema::table('sinistros', function (Blueprint $table): void {
                $table->dropIndex(['reserva_motorista_app_id']);
                $table->dropColumn('reserva_motorista_app_id');
            });
        }

        if (Schema::hasTable('multas') && Schema::hasColumn('multas', 'reserva_motorista_app_id')) {
            Schema::table('multas', function (Blueprint $table): void {
                $table->dropIndex(['reserva_motorista_app_id']);
                $table->dropColumn('reserva_motorista_app_id');
            });
        }

        Schema::dropIfExists('reserva_motorista_app_pagamentos');
    }
};
