<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelas legadas sem nenhuma referencia no codigo da aplicacao
     * (models, controllers, services, seeders, views, configs ou pacotes).
     *
     * @var array<int, string>
     */
    private array $tabelas = [
        'avaria_cobrancas',
        'calcoes',
        'caracteristica_frotas',
        'caracteristica_grupo_frotas',
        'caracteristica_grupos',
        'caracteristicas_grupo_frota',
        'caracteristicas_tipo_frota',
        'categorias_financeiras',
        'chamado_anexos',
        'chamado_categorias',
        'chamado_logs',
        'chamado_statuses',
        'chamado_subcategorias',
        'chamados',
        'classificacoes_fiscais',
        'contrato_checkin',
        'contrato_checkout',
        'creditos_tributarios',
        'disponibilidade_inicial_veiculo',
        'disponibilidade_inicial_veiculos',
        'documentacao_veiculo_parcelas',
        'documentacoes_veiculo',
        'documentos_fiscais',
        'empresa_contratos',
        'estacoes_dias_uteis',
        'execucoes_manutencao_preventiva',
        'feriados',
        'frota_caracteristicas',
        'frotas',
        'frotas_caracteristicas',
        'grupo_frota_caracteristica_frota',
        'grupo_frota_opcional',
        'grupo_frotas',
        'grupos_caracteristicas',
        'horario_diarios',
        'itens_manutencao_preventiva',
        'lancamentos_financeiros',
        'motorista_app_solicitacoes',
        'movimentacaos',
        'movimentacoes_historico',
        'multa_historico',
        'opcionais_grupos_veiculos',
        'operacoes_fiscais',
        'operacoes_fiscais_tributos',
        'ordem_servico_anexos',
        'ordem_servico_campo_valores',
        'ordem_servico_itens',
        'ordens_servico',
        'os_subcategoria_campos',
        'os_subcategorias',
        'pendencias_manutencao_preventiva',
        'plano_manutencao_itens',
        'plano_motorista_app_grupo_frota',
        'plano_motorista_app_opcionais',
        'plano_motorista_app_opcional_grupo_veiculo',
        'planos_manutencao_preventiva',
        'planos_motorista_app',
        'recebimentos_tributos',
        'regras_tributarias',
        'reserva_codigos',
        'reserva_cupom',
        'reserva_logs',
        'reserva_motorista_app_aditivos',
        'reserva_motorista_app_pagamentos',
        'reservas_motorista_app',
        'reservas_opcionais',
        'saldo_movimentacoes',
        'sinistro_historico',
        'status_movimentacaos',
        'status_reservas',
        'tempo_entre_entacaos',
        'tempo_entre_estacoes',
        'termo_consentimentos',
        'tiop_valor_caracteristic_a_frotas',
        'tipo_frotas',
        'tipo_movimentacaos',
        'tipo_usuarios',
        'tipo_valor_caracteristica_frotas',
        'tour_realizacoes',
        'tour_telas',
        'tributos_municipais',
        'veiculo_danos_imagens',
    ];

    public function up(): void
    {
        // Remove FKs de tabelas MANTIDAS que apontam para tabelas removidas
        // (ex.: veiculos.classificacao_fiscal_id -> classificacoes_fiscais).
        $fksOrfas = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->selectRaw('TABLE_NAME as tabela, CONSTRAINT_NAME as constraint_name')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->whereIn('REFERENCED_TABLE_NAME', $this->tabelas)
            ->whereNotIn('TABLE_NAME', $this->tabelas)
            ->get();

        foreach ($fksOrfas as $fk) {
            Schema::table($fk->tabela, function (Blueprint $table) use ($fk): void {
                $table->dropForeign($fk->constraint_name);
            });
        }

        Schema::disableForeignKeyConstraints();

        foreach ($this->tabelas as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Irreversivel: as tabelas removidas nao sao recriadas.
    }
};
