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
 * Perfis sao vinculados a empresa: cada tenant tem seus proprios
 * 'administrador' e 'usuario' (empresa_id preenchido).
 * Limpeza: usuarios em papeis globais sao remapeados para o papel
 * equivalente da propria empresa e os globais sem uso sao removidos.
 */
class PapeisPermissoesSeeder extends Seeder
{
    private const PAPEL_ADMIN = 'administrador';

    private const PAPEL_EQUIPE = 'equipe';

    private const PAPEL_USUARIO = 'usuario';

    /**
     * Permissoes do usuario: registrar tempo e gerenciar os proprios dados.
     */
    public const PERMISSOES_USUARIO = [
        'operacao.tracker.index',
        'operacao.tracker.start',
        'operacao.tracker.stop',
        'operacao.tracker.manual',
        'operacao.tracker.update',
        'operacao.tracker.destroy',
        'operacao.tracker.active.update',
        'operacao.timesheet',
        'operacao.timesheet.save',
        'operacao.timesheet.copiar',
        'escopo.proprio',
        'operacao.calendario.index',
        'operacao.calendario.store',
        'operacao.timeoff.index',
        'operacao.timeoff.store',
        'operacao.timeoff.update',
        'operacao.timeoff.filtro',
        'operacao.timeoff.removerFiltro',
        'operacao.timeoff.setPerPage',
        'operacao.projetos.index',
        'operacao.projetos.show',
        'operacao.projetos.filtro',
        'operacao.projetos.removerFiltro',
        'operacao.projetos.setPerPage',
        'operacao.clientes.index',
        'operacao.clientes.filtro',
        'operacao.clientes.removerFiltro',
        'operacao.clientes.setPerPage',
        'operacao.tags.index',
        'operacao.tags.filtro',
        'operacao.tags.removerFiltro',
        'operacao.tags.setPerPage',
        'operacao.times.index',
        'operacao.times.filtro',
        'operacao.times.removerFiltro',
        'operacao.times.setPerPage',
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
     * Perfil 'equipe' (lideres/membros com visao de time): mesma matriz do
     * usuario + escopo.equipe, que libera ver dados das equipes lideradas.
     */
    public static function permissoesEquipe(): array
    {
        return array_merge(self::PERMISSOES_USUARIO, ['escopo.equipe']);
    }

    public function run(): void
    {
        $this->garantirPapeis();
        $this->sincronizarMatriz();
        $this->remapearUsuarios();
        $this->removerPapeisGlobaisObsoletos();
    }

    private function garantirPapeis(): void
    {
        $empresaIds = DB::table('empresas')->pluck('id');

        foreach ($empresaIds as $empresaId) {
            foreach ([self::PAPEL_ADMIN, self::PAPEL_USUARIO, self::PAPEL_EQUIPE] as $nome) {
                DB::table('roles')->updateOrInsert(
                    ['name' => $nome, 'guard_name' => 'web', 'empresa_id' => $empresaId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function sincronizarMatriz(): void
    {
        $permissaoIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $this->sincronizarMatrizPapel(self::PAPEL_USUARIO, self::PERMISSOES_USUARIO, $permissaoIds);
        $this->sincronizarMatrizPapel(self::PAPEL_EQUIPE, self::permissoesEquipe(), $permissaoIds);
    }

    private function sincronizarMatrizPapel(string $papel, array $permissoes, $permissaoIds): void
    {
        $ids = collect($permissoes)
            ->map(fn ($nome) => $permissaoIds[$nome] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $faltantes = collect($permissoes)->filter(fn ($n) => ! isset($permissaoIds[$n]))->values();
        if ($faltantes->isNotEmpty()) {
            $this->command?->warn('Permissoes inexistentes para '.$papel.': '.$faltantes->implode(', '));
        }

        $roleIds = DB::table('roles')
            ->where('name', $papel)
            ->where('guard_name', 'web')
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('role_id', $roleIds)->delete();

        $pivots = [];
        foreach ($roleIds as $roleId) {
            foreach ($ids as $pid) {
                $pivots[] = ['permission_id' => $pid, 'role_id' => $roleId];
            }
        }

        if ($pivots !== []) {
            DB::table('role_has_permissions')->insert($pivots);
        }

        $this->command?->info('Papel '.$papel.": {$ids->count()} permissoes sincronizadas em {$roleIds->count()} perfil(is).");
    }

    private function remapearUsuarios(): void
    {
        // [empresa_id][name em minusculo] => role_id
        $porEmpresa = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereNotNull('empresa_id')
            ->get(['id', 'name', 'empresa_id'])
            ->groupBy('empresa_id')
            ->map(fn ($roles) => $roles->keyBy(fn ($r) => mb_strtolower($r->name)));

        $padroes = [self::PAPEL_ADMIN, self::PAPEL_USUARIO, self::PAPEL_EQUIPE];

        // Usuarios em papeis globais migram para o papel equivalente da empresa.
        $globais = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->get(['id', 'name'])
            ->keyBy('id');

        $users = DB::table('users')
            ->whereIn('role_id', $globais->keys())
            ->get(['id', 'role_id', 'empresa_id']);

        $migrados = 0;
        foreach ($users as $user) {
            $empresaId = (int) ($user->empresa_id ?? 0);
            if ($empresaId === 0 || ! isset($porEmpresa[$empresaId])) {
                continue;
            }

            $nomeGlobal = mb_strtolower((string) ($globais[$user->role_id]->name ?? ''));
            $alvo = in_array($nomeGlobal, $padroes, true) ? $nomeGlobal : self::PAPEL_USUARIO;
            $roleId = $porEmpresa[$empresaId][$alvo]->id ?? null;

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
                $migrados++;
            }
        }

        if ($migrados > 0) {
            $this->command?->info("{$migrados} usuario(s) remapeado(s) de papeis globais para a empresa.");
        }

        // Usuarios de tenant sem papel assumem o 'usuario' da empresa.
        $semPapel = DB::table('users')
            ->whereNotNull('empresa_id')
            ->whereNull('role_id')
            ->get(['id', 'empresa_id']);

        $atribuidos = 0;
        foreach ($semPapel as $user) {
            $roleId = $porEmpresa[(int) $user->empresa_id][self::PAPEL_USUARIO]->id ?? null;
            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
                $atribuidos++;
            }
        }

        if ($atribuidos > 0) {
            $this->command?->info("{$atribuidos} usuario(s) sem papel -> '".self::PAPEL_USUARIO."'.");
        }
    }

    /**
     * Remove papeis globais que ficaram sem usuarios vinculados.
     * Papeis com empresa_id pertencem ao tenant e sao preservados.
     */
    private function removerPapeisGlobaisObsoletos(): void
    {
        $ids = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        // Seguranca: so remove papel sem usuarios vinculados; os que ainda
        // tiverem usuarios (ex.: usuarios sem empresa) ficam para revisao.
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
