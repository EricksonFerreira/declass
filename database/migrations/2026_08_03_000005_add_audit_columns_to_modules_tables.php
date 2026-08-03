<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fullAuditTables = [
        'veiculos',
        'disponibilidade_inicial_veiculos',
        'movimentacoes',
        'movimentacoes_historico',
        'checklists',
        'contrato_checkin',
        'contrato_checkout',
        'danos',
        'veiculo_danos',
        'veiculo_danos_imagens',
        'avaria_cobrancas',
        'multas',
        'multa_historico',
        'sinistros',
        'sinistro_historico',
        'ordens_servico',
        'ordem_servico_itens',
        'ordem_servico_anexos',
        'execucoes_manutencao_preventiva',
        'pendencias_manutencao_preventiva',
        'documentacoes_veiculo',
        'documentacao_veiculo_parcelas',
        'reservas',
        'reservas_motorista_app',
        'reservas_opcionais',
        'reserva_cupom',
        'reserva_motorista_app_aditivos',
        'reserva_motorista_app_pagamentos',
        'reserva_satisfacoes',
        'calcoes',
        'termo_consentimento',
        'lancamentos_financeiros',
        'transacoes',
        'saldo_movimentacoes',
        'categorias_financeiras',
        'cupom_desconto',
        'empresa_plano_pagamentos',
        'payments',
        'subscriptions',
        'subscription_items',
        'nfse',
        'tributos_municipais',
        'empresas',
        'empresa_alertas',
        'empresa_planos',
        'users',
        'estacoes',
        'feriados',
        'dias_uteis',
        'horarios',
        'opcionais',
        'grupos_frota',
        'caracteristicas_frotas',
        'planos',
        'itens_plano',
        'planos_manutencao_preventiva',
        'itens_manutencao_preventiva',
        'chamados',
        'chamado_anexos',
    ];

    private array $insertOnlyTables = [
        'configuracao_checklist',
        'configuracoes_calcao',
        'item_checklist',
        'plano_manutencao_itens',
        'plano_permissao',
        'tour_telas',
        'reserva_logs',
        'chamado_logs',
        'log_jobs',
        'arquivos_rastreados',
        'tour_realizacoes',
        'notifications',
        'estacao_user',
        'estacoes_dias_uteis',
        'grupo_frota_modelo',
        'grupo_frota_opcional',
        'grupo_frota_caracteristica_frota',
        'frotas_caracteristicas',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
    ];

    public function up(): void
    {
        foreach ($this->fullAuditTables as $tableName) {
            $this->addAuditColumns($tableName, true);
        }

        foreach ($this->insertOnlyTables as $tableName) {
            $this->addAuditColumns($tableName, false);
        }
    }

    public function down(): void
    {
        foreach (array_merge($this->fullAuditTables, $this->insertOnlyTables) as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach (['usuatu', 'datatu', 'usuinc', 'datainc'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `{$column}`");
                }
            }
        }
    }

    private function addAuditColumns(string $tableName, bool $includeUpdateAudit): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $this->relaxSqlMode();

        if (!Schema::hasColumn($tableName, 'datainc')) {
            DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `datainc` DATETIME NULL");
        }

        if (!Schema::hasColumn($tableName, 'usuinc')) {
            DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `usuinc` BIGINT UNSIGNED NULL");
        }

        if ($includeUpdateAudit) {
            if (!Schema::hasColumn($tableName, 'datatu')) {
                DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `datatu` DATETIME NULL");
            }

            if (!Schema::hasColumn($tableName, 'usuatu')) {
                DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `usuatu` BIGINT UNSIGNED NULL");
            }
        }
    }

    private function relaxSqlMode(): void
    {
        try {
            DB::statement("SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '')");
        } catch (\Throwable $e) {
            // Se o banco não suportar o ajuste, seguimos com o sql_mode atual.
        }
    }
};
