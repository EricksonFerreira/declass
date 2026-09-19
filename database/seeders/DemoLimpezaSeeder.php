<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove todos os dados criados pelo DemoOperacaoSeeder:
 * usuarios demo (@demo.declock.app), seus apontamentos, folgas,
 * alocacoes, vinculos — alem dos projetos, clientes, time e
 * estimativas demo.
 *
 * Uso: php artisan db:seed --class=DemoLimpezaSeeder
 */
class DemoLimpezaSeeder extends Seeder
{
    private const EMAIL_DOMAIN = '%@demo.declock.app';

    private const PROJETOS_DEMO = [
        // Lote engenharia/serviços (atual)
        'Loteamento Residencial Vista Alegre — Lote 2',
        'Edifício Comercial Parque Central',
        'Ampliação da Ala Sul — Hospital Regional',
        'Reforma da Unidade Fabril — Planta Norte',
        'Projeto Executivo de Instalações — Ala Norte',
        'Due Diligence — Terreno Distrito Sul',
        'Terraplenagem — Loteamento Alto Serra',
        // Lote anterior (dados já semeados em empresas antigas)
        'Portal de Orçamentos Online',
        'App de Rastreamento de Frota',
        'Redesign do Site Institucional',
        'Integração ERP × E-commerce',
        'Sistema de Agendamento de Consultas',
        'Auditoria Interna — Compliance',
        'Portal Legado — Descontinuado',
    ];

    private const CLIENTES_DEMO = [
        'Construtora Horizonte',
        'Incorporadora Vila Nova',
        'Hospital Regional Santa Cruz',
        'Indústria MetalFort',
        // Lote anterior
        'ViaLog Transportes',
        'Grupo SaúdeVida',
        'VarejoMax Supermercados',
    ];

    public function run(): void
    {
        $empresaId = (int) env('DEMO_EMPRESA_ID', 1);

        $userIds = DB::table('users')
            ->where('empresa_id', $empresaId)
            ->where('email', 'like', self::EMAIL_DOMAIN)
            ->pluck('id');

        $projetoIds = DB::table('clockify_projetos')
            ->where('empresa_id', $empresaId)
            ->whereIn('nome', self::PROJETOS_DEMO)
            ->pluck('id');

        if ($userIds->isNotEmpty()) {
            $trackerIds = DB::table('clockify_time_trackers')->whereIn('user_id', $userIds)->pluck('id');
            DB::table('clockify_tracker_tag')->whereIn('clockify_time_tracker_id', $trackerIds)->delete();
            DB::table('clockify_time_trackers')->whereIn('user_id', $userIds)->delete();
            DB::table('clockify_time_offs')->whereIn('user_id', $userIds)->delete();
            DB::table('clockify_calendarios')->whereIn('user_id', $userIds)->delete();
            DB::table('clockify_projeto_user')->whereIn('user_id', $userIds)->delete();
            DB::table('clockify_time_user')->whereIn('user_id', $userIds)->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
        }

        if ($projetoIds->isNotEmpty()) {
            $trackerIds = DB::table('clockify_time_trackers')->whereIn('clockify_projeto_id', $projetoIds)->pluck('id');
            DB::table('clockify_tracker_tag')->whereIn('clockify_time_tracker_id', $trackerIds)->delete();
            DB::table('clockify_time_trackers')->whereIn('clockify_projeto_id', $projetoIds)->delete();
            DB::table('clockify_projeto_estimativas')->whereIn('clockify_projeto_id', $projetoIds)->delete();
            DB::table('clockify_projeto_user')->whereIn('clockify_projeto_id', $projetoIds)->delete();
            DB::table('clockify_calendarios')->whereIn('clockify_projeto_id', $projetoIds)->delete();
            $cronogramaIds = DB::table('clockify_projeto_cronogramas')->whereIn('clockify_projeto_id', $projetoIds)->pluck('id');
            DB::table('clockify_projeto_planejamentos')->whereIn('clockify_projeto_cronograma_id', $cronogramaIds)->delete();
            DB::table('clockify_projeto_cronogramas')->whereIn('id', $cronogramaIds)->delete();
            DB::table('clockify_projetos')->whereIn('id', $projetoIds)->delete();
        }

        $timeId = DB::table('clockify_times')
            ->where('empresa_id', $empresaId)
            ->whereIn('nome', ['Equipe de Fiscalização — Obras Sul', 'Squad Produtos Digitais'])
            ->value('id');

        if ($timeId) {
            DB::table('clockify_time_user')->where('clockify_time_id', $timeId)->delete();
            DB::table('clockify_times')->where('id', $timeId)->delete();
        }

        $clienteIds = DB::table('clockify_clientes')
            ->where('empresa_id', $empresaId)
            ->whereIn('nome', self::CLIENTES_DEMO)
            ->pluck('id');

        foreach ($clienteIds as $cid) {
            $temProjetos = DB::table('clockify_projetos')->where('clockify_cliente_id', $cid)->exists();
            if (! $temProjetos) {
                DB::table('clockify_clientes')->where('id', $cid)->delete();
            }
        }

        if (Schema::hasTable('licitacoes')) {
            $licitacaoIds = DB::table('licitacoes')->where('empresa_id', $empresaId)->pluck('id');
            DB::table('licitacao_analises')->whereIn('licitacao_id', $licitacaoIds)->delete();
            DB::table('licitacoes')->whereIn('id', $licitacaoIds)->delete();
        }

        $this->command->info("Limpeza demo concluida na empresa {$empresaId}.");
    }
}
