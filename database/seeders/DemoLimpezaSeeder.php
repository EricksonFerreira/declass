<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        'Portal de Orçamentos Online',
        'App de Rastreamento de Frota',
        'Redesign do Site Institucional',
        'Integração ERP × E-commerce',
        'Sistema de Agendamento de Consultas',
    ];

    private const CLIENTES_DEMO = [
        'Construtora Horizonte',
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
            DB::table('clockify_projetos')->whereIn('id', $projetoIds)->delete();
        }

        $timeId = DB::table('clockify_times')
            ->where('empresa_id', $empresaId)
            ->where('nome', 'Squad Produtos Digitais')
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

        $this->command->info("Limpeza demo concluida na empresa {$empresaId}.");
    }
}
