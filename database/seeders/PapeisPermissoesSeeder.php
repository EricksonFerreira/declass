<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Matriz de papeis e permissoes do DECLOCK.
 *
 * Papeis (roles.name):
 *  - administrador : acesso total ao tenant (via isAdministrador + plano)
 *  - usuario       : membro padrao — aponta tempo, solicita folga, ve dados proprios
 *
 * Idempotente: pode rodar quantas vezes precisar.
 * Limpeza: papeis globais fora de [administrador, usuario] tem seus usuarios
 * remapeados para 'usuario' e sao removidos. Papeis de empresa
 * (empresa_id preenchido) pertencem ao tenant e nao sao tocados.
 */
class PapeisPermissoesSeeder extends Seeder
{
    private const PAPEL_ADMIN = 'administrador';

    private const PAPEL_USUARIO = 'usuario';

    /**
     * Permissoes do usuario: registrar tempo e gerenciar os proprios dados.
     */
    public const PERMISSOES_USUARIO = [
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

    public function run(): void
    {
        $this->garantirPapeis();
        $this->sincronizarMatriz();
        $this->remapearUsuarios();
        $this->removerPapeisGlobaisObsoletos();
    }

    private function garantirPapeis(): void
    {
        foreach ([self::PAPEL_ADMIN, self::PAPEL_USUARIO] as $nome) {
            DB::table('roles')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web', 'empresa_id' => null],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function sincronizarMatriz(): void
    {
        $roleId = DB::table('roles')
            ->where('name', self::PAPEL_USUARIO)
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->value('id');

        if (! $roleId) {
            return;
        }

        $permissaoIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $ids = collect(self::PERMISSOES_USUARIO)
            ->map(fn ($nome) => $permissaoIds[$nome] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $faltantes = collect(self::PERMISSOES_USUARIO)->filter(fn ($n) => ! isset($permissaoIds[$n]))->values();
        if ($faltantes->isNotEmpty()) {
            $this->command?->warn('Permissoes inexistentes para '.self::PAPEL_USUARIO.': '.$faltantes->implode(', '));
        }

        DB::table('role_has_permissions')->where('role_id', $roleId)->delete();

        DB::table('role_has_permissions')->insert(
            $ids->map(fn ($pid) => ['permission_id' => $pid, 'role_id' => $roleId])->all()
        );

        $this->command?->info('Papel '.self::PAPEL_USUARIO.": {$ids->count()} permissoes sincronizadas.");
    }

    private function remapearUsuarios(): void
    {
        $usuarioId = DB::table('roles')
            ->where('name', self::PAPEL_USUARIO)
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->value('id');

        if (! $usuarioId) {
            return;
        }

        // Qualquer papel global fora de [administrador, usuario] vira usuario.
        $idsObsoletos = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->whereNotIn('name', [self::PAPEL_ADMIN, self::PAPEL_USUARIO])
            ->pluck('id');

        if ($idsObsoletos->isNotEmpty()) {
            $afetados = DB::table('users')->whereIn('role_id', $idsObsoletos)->update(['role_id' => $usuarioId]);
            if ($afetados > 0) {
                $this->command?->info("{$afetados} usuario(s) de papeis obsoletos -> '".self::PAPEL_USUARIO."'.");
            }
        }

        // Usuarios de tenant sem papel assumem usuario.
        $semPapel = DB::table('users')
            ->whereNotNull('empresa_id')
            ->whereNull('role_id')
            ->update(['role_id' => $usuarioId]);

        if ($semPapel > 0) {
            $this->command?->info("{$semPapel} usuario(s) sem papel -> '".self::PAPEL_USUARIO."'.");
        }
    }

    /**
     * Remove papeis globais que nao sejam administrador/usuario.
     * Papeis com empresa_id pertencem ao tenant e sao preservados.
     */
    private function removerPapeisGlobaisObsoletos(): void
    {
        $ids = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->whereNotIn('name', [self::PAPEL_ADMIN, self::PAPEL_USUARIO])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        // Seguranca: so remove papel sem usuarios vinculados; os que ainda
        // tiverem usuarios (ex.: role_id apontando para papel de empresa)
        // ficam para revisao manual.
        $emUso = DB::table('users')->whereIn('role_id', $ids)->pluck('role_id')->unique();
        $removiveis = $ids->diff($emUso);

        if ($emUso->isNotEmpty()) {
            $nomes = DB::table('roles')->whereIn('id', $emUso)->pluck('name')->implode(', ');
            $this->command?->warn("Papeis globais ainda em uso, nao removidos: {$nomes}");
        }

        if ($removiveis->isEmpty()) {
            return;
        }

        DB::table('role_has_permissions')->whereIn('role_id', $removiveis)->delete();
        DB::table('roles')->whereIn('id', $removiveis)->delete();

        $this->command?->info($removiveis->count().' papel(is) global(is) obsoleto(s) removido(s).');
    }
}
