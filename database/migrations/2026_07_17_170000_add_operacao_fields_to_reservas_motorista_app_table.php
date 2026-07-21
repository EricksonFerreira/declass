<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reservas_motorista_app')) {
            return;
        }

        Schema::table('reservas_motorista_app', function (Blueprint $table): void {
            if (!Schema::hasColumn('reservas_motorista_app', 'veiculo_id')) {
                $table->unsignedBigInteger('veiculo_id')->nullable()->after('plano_motorista_app_id')->index();
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'data_retirada_real')) {
                $table->dateTime('data_retirada_real')->nullable()->after('data_fim');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'contrato_efetivado_em')) {
                $table->dateTime('contrato_efetivado_em')->nullable()->after('data_retirada_real');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'checkin_realizado_em')) {
                $table->dateTime('checkin_realizado_em')->nullable()->after('contrato_efetivado_em');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'checkout_realizado_em')) {
                $table->dateTime('checkout_realizado_em')->nullable()->after('checkin_realizado_em');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'quilometragem_inicial')) {
                $table->unsignedInteger('quilometragem_inicial')->nullable()->after('quantidade_dias');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'nivel_combustivel_inicial')) {
                $table->string('nivel_combustivel_inicial', 20)->nullable()->after('quilometragem_inicial');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'quilometragem_final')) {
                $table->unsignedInteger('quilometragem_final')->nullable()->after('nivel_combustivel_inicial');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'nivel_combustivel_final')) {
                $table->string('nivel_combustivel_final', 20)->nullable()->after('quilometragem_final');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'valor_periodo_contrato')) {
                $table->decimal('valor_periodo_contrato', 10, 2)->default(0)->after('valor_plano');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'periodo_contrato')) {
                $table->string('periodo_contrato', 30)->nullable()->after('valor_periodo_contrato');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'contrato_snapshot_json')) {
                $table->json('contrato_snapshot_json')->nullable()->after('periodo_contrato');
            }
            if (!Schema::hasColumn('reservas_motorista_app', 'contrato_pdf_path')) {
                $table->string('contrato_pdf_path', 500)->nullable()->after('contrato_snapshot_json');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('reservas_motorista_app')) {
            return;
        }

        Schema::table('reservas_motorista_app', function (Blueprint $table): void {
            if (Schema::hasColumn('reservas_motorista_app', 'veiculo_id')) {
                $table->dropIndex(['veiculo_id']);
                $table->dropColumn('veiculo_id');
            }

            foreach ([
                'data_retirada_real',
                'contrato_efetivado_em',
                'checkin_realizado_em',
                'checkout_realizado_em',
                'quilometragem_inicial',
                'nivel_combustivel_inicial',
                'quilometragem_final',
                'nivel_combustivel_final',
                'valor_periodo_contrato',
                'periodo_contrato',
                'contrato_snapshot_json',
                'contrato_pdf_path',
            ] as $column) {
                if (Schema::hasColumn('reservas_motorista_app', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
