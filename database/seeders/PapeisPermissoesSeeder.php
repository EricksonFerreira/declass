<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Matriz de papeis e permissoes do DECLOCK.
 *
 * Papeis (roles.name):
 *  - administrador : acesso total ao tenant (via isAdministrador + plano)
 *  - lider         : colaborador + gestao de equipe/projetos/aprovacoes/relatorios
 *  - colaborador   : membro padrao — aponta tempo, solicita folga, ve dados proprios
 *  - visualizador  : leitura de paineis e relatorios (sem escopo de restricao)
 *
 * Idempotente: pode rodar quantas vezes precisar.
 * Remapeamento: papeis legados (DeCars) viram o equivalente DECLOCK.
 */
class PapeisPermissoesSeeder extends Seeder
{
    private const PAPEL_COLABORADOR = 'colaborador';

    private const PAPEL_LIDER = 'lider';

    private const PAPEL_VISUALIZADOR = 'visualizador';

    /**
     * Permissoes base do colaborador: registrar tempo e gerenciar os proprios dados.
     */
    private const PERMISSOES_COLABORADOR = [
        'clockify.tracker.index',
        'clockify.tracker.start',
        'clockify.tracker.stop',
        'clockify.tracker.manual',
        'clockify.tracker.update',
        'clockify.tracker.destroy',
        'clockify.tracker.active.update',
        'clockify.timesheet',
        'clockify.timesheet.save',
        'clockify.calendario.index',
        'clockify.calendario.store',
        'clockify.calendario.mover',
        'clockify.calendario.destroy',
        'clockify.timeoff.index',
        'clockify.timeoff.store',
        'clockify.timeoff.update',
        'clockify.timeoff.destroy',
        'clockify.timeoff.filtro',
        'clockify.timeoff.removerFiltro',
        'clockify.timeoff.setPerPage',
        'clockify.projetos.index',
        'clockify.projetos.show',
        'clockify.projetos.filtro',
        'clockify.projetos.removerFiltro',
        'clockify.projetos.setPerPage',
        'clockify.clientes.index',
        'clockify.clientes.filtro',
        'clockify.clientes.removerFiltro',
        'clockify.clientes.setPerPage',
        'clockify.tags.index',
        'clockify.tags.filtro',
        'clockify.tags.removerFiltro',
        'clockify.tags.setPerPage',
        'clockify.times.index',
        'clockify.times.filtro',
        'clockify.times.removerFiltro',
        'clockify.times.setPerPage',
        'relatorios.index',
        'relatorios.horas',
        'relatorios.horas-trabalhadas',
        'relatorios.export',
        'relatorios.exportar',
        'home.equipe',
        'home.produtividade',
        'home.projetos',
    ];

    /**
     * Permissoes extras do lider: gestao de equipe, projetos, aprovacoes
     * e relatorios com dados de custo/margem.
     */
    private const PERMISSOES_LIDER_EXTRA = [
        'clockify.timeoff.approve',
        'clockify.timeoff.reject',
        'clockify.times.store',
        'clockify.times.update',
        'clockify.times.destroy',
        'clockify.projetos.store',
        'clockify.projetos.update',
        'clockify.projetos.planejamento',
        'clockify.projetos.planejamento.store',
        'clockify.projetos.planejar-equipe',
        'clockify.projetos.planejar-equipe.store',
        'clockify.projetos.planejar-equipe-geral',
        'clockify.projetos.planejar-equipe-geral.store',
        'clockify.projetos.cronogramas.novo',
        'clockify.projetos.cronogramas.store',
        'clockify.projetos.cronogramas.destroy',
        'clockify.clientes.store',
        'clockify.clientes.update',
        'clockify.tags.store',
        'clockify.tags.update',
        'clockify.tags.destroy',
        'relatorios.consumo',
        'relatorios.consumo.show',
        'relatorios.consumo.status',
        'relatorios.consumo.estimativas.store',
        'relatorios.consumo.estimativas.destroy',
        'relatorios.matriz',
    ];

    /**
     * Permissoes do visualizador: paineis e relatorios em modo leitura.
     */
    private const PERMISSOES_VISUALIZADOR = [
        'dashboard',
        'home.equipe',
        'home.produtividade',
        'home.projetos',
        'relatorios.index',
        'relatorios.horas',
        'relatorios.horas-trabalhadas',
        'relatorios.consumo',
        'relatorios.consumo.show',
        'relatorios.export',
        'relatorios.exportar',
        'clockify.projetos.index',
        'clockify.projetos.show',
        'clockify.clientes.index',
        'clockify.times.index',
        'clockify.timeoff.index',
    ];

    /**
     * Papeis legados (DeCars) -> equivalente DECLOCK.
     */
    private const REMAP_PAPEIS = [
        'Gerente' => self::PAPEL_LIDER,
        'gestor' => self::PAPEL_LIDER,
        'colaborador_chefe' => self::PAPEL_LIDER,
        'Atendente' => self::PAPEL_COLABORADOR,
        'Financeiro' => self::PAPEL_COLABORADOR,
        'Supervisor de Frota' => self::PAPEL_COLABORADOR,
        'operador' => self::PAPEL_COLABORADOR,
        'colaborador_administrativo' => self::PAPEL_COLABORADOR,
        'Cliente' => self::PAPEL_VISUALIZADOR,
    ];

    public function run(): void
    {
        $this->garantirPapeis();
        $this->sincronizarMatriz();
        $this->remapearUsuarios();
    }

    private function garantirPapeis(): void
    {
        foreach ([self::PAPEL_COLABORADOR, self::PAPEL_LIDER, self::PAPEL_VISUALIZADOR] as $nome) {
            DB::table('roles')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function sincronizarMatriz(): void
    {
        $matriz = [
            self::PAPEL_COLABORADOR => self::PERMISSOES_COLABORADOR,
            self::PAPEL_LIDER => array_merge(self::PERMISSOES_COLABORADOR, self::PERMISSOES_LIDER_EXTRA),
            self::PAPEL_VISUALIZADOR => self::PERMISSOES_VISUALIZADOR,
        ];

        $permissaoIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        foreach ($matriz as $papel => $perms) {
            $roleId = DB::table('roles')->where('name', $papel)->where('guard_name', 'web')->value('id');
            if (! $roleId) {
                continue;
            }

            $ids = collect($perms)
                ->map(fn ($nome) => $permissaoIds[$nome] ?? null)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $faltantes = collect($perms)->filter(fn ($n) => ! isset($permissaoIds[$n]))->values();
            if ($faltantes->isNotEmpty()) {
                $this->command?->warn("Permissoes inexistentes para {$papel}: ".$faltantes->implode(', '));
            }

            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();

            DB::table('role_has_permissions')->insert(
                $ids->map(fn ($pid) => ['permission_id' => $pid, 'role_id' => $roleId])->all()
            );

            $this->command?->info("Papel {$papel}: {$ids->count()} permissoes sincronizadas.");
        }
    }

    private function remapearUsuarios(): void
    {
        $roleIds = DB::table('roles')->where('guard_name', 'web')->pluck('id', 'name');

        foreach (self::REMAP_PAPEIS as $legado => $novo) {
            $legadoId = $roleIds[$legado] ?? null;
            $novoId = $roleIds[$novo] ?? null;
            if (! $legadoId || ! $novoId) {
                continue;
            }

            $afetados = DB::table('users')->where('role_id', $legadoId)->update(['role_id' => $novoId]);
            if ($afetados > 0) {
                $this->command?->info("{$afetados} usuario(s) de '{$legado}' -> '{$novo}'.");
            }
        }

        // Usuarios de tenant sem papel assumem colaborador.
        $colaboradorId = $roleIds[self::PAPEL_COLABORADOR] ?? null;
        if ($colaboradorId) {
            $semPapel = DB::table('users')
                ->whereNotNull('empresa_id')
                ->whereNull('role_id')
                ->update(['role_id' => $colaboradorId]);

            if ($semPapel > 0) {
                $this->command?->info("{$semPapel} usuario(s) sem papel -> 'colaborador'.");
            }
        }
    }
}
