<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perfis sao por empresa: remove TODOS os perfis existentes e recria
     * apenas 'administrador' e 'usuario' para cada empresa. Usuarios sao
     * remapeados antes da exclusao (administrador -> administrador da
     * empresa, qualquer outro -> usuario da empresa; sem empresa -> null).
     * A unicidade por (empresa_id, name, guard_name) impede nomes
     * duplicados na mesma empresa.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('empresas')) {
            return;
        }

        $keepIds = []; // [empresa_id][nome] => role_id

        foreach (DB::table('empresas')->pluck('id') as $empresaId) {
            foreach (['administrador', 'usuario'] as $nome) {
                $id = DB::table('roles')
                    ->where('empresa_id', $empresaId)
                    ->where('name', $nome)
                    ->where('guard_name', 'web')
                    ->value('id');

                if (! $id) {
                    $id = DB::table('roles')->insertGetId([
                        'empresa_id' => $empresaId,
                        'name' => $nome,
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $keepIds[$empresaId][$nome] = $id;
            }
        }

        // Remapeia usuarios para o perfil da propria empresa.
        $nomesPorId = DB::table('roles')->pluck('name', 'id');
        $users = DB::table('users')->whereNotNull('role_id')->get(['id', 'role_id', 'empresa_id']);

        foreach ($users as $user) {
            $empresaId = (int) ($user->empresa_id ?? 0);
            $nomeAtual = mb_strtolower((string) ($nomesPorId[$user->role_id] ?? ''));

            if ($empresaId && isset($keepIds[$empresaId])) {
                $alvo = $nomeAtual === 'administrador'
                    ? $keepIds[$empresaId]['administrador']
                    : $keepIds[$empresaId]['usuario'];

                if ((int) $user->role_id !== (int) $alvo) {
                    DB::table('users')->where('id', $user->id)->update(['role_id' => $alvo]);
                }
            } else {
                DB::table('users')->where('id', $user->id)->update(['role_id' => null]);
            }
        }

        // Remove todos os perfis que nao sejam os padroes por empresa.
        $manter = collect($keepIds)->flatten()->map(fn ($id) => (int) $id)->all();
        $remover = DB::table('roles')->whereNotIn('id', $manter)->pluck('id');

        if ($remover->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('role_id', $remover)->delete();

            if (Schema::hasTable('model_has_roles')) {
                DB::table('model_has_roles')->whereIn('role_id', $remover)->delete();
            }

            DB::table('roles')->whereIn('id', $remover)->delete();
        }

        // Sincroniza a matriz de permissoes dos perfis mantidos.
        $permissoes = DB::table('permissions')->where('guard_name', 'web')->pluck('id', 'name');
        $adminIds = $permissoes->values()->all();
        $usuarioIds = collect(self::PERMISSOES_USUARIO)
            ->map(fn ($n) => $permissoes[$n] ?? null)
            ->filter()
            ->values()
            ->all();

        foreach ($keepIds as $roles) {
            DB::table('role_has_permissions')->where('role_id', $roles['administrador'])->delete();
            DB::table('role_has_permissions')->insert(
                array_map(fn ($p) => ['permission_id' => $p, 'role_id' => $roles['administrador']], $adminIds)
            );

            DB::table('role_has_permissions')->where('role_id', $roles['usuario'])->delete();
            if ($usuarioIds !== []) {
                DB::table('role_has_permissions')->insert(
                    array_map(fn ($p) => ['permission_id' => $p, 'role_id' => $roles['usuario']], $usuarioIds)
                );
            }
        }
    }

    public function down(): void
    {
        // Irreversivel: os perfis removidos nao sao recriados.
    }

    /**
     * Mesma matriz de PapeisPermissoesSeeder::PERMISSOES_USUARIO
     * (duplicada aqui porque a seeder vive em autoload-dev e pode nao
     * existir no vendor de producao).
     */
    private const PERMISSOES_USUARIO = [
        'operacao.tracker.index',
        'operacao.tracker.start',
        'operacao.tracker.stop',
        'operacao.tracker.manual',
        'operacao.tracker.update',
        'operacao.tracker.destroy',
        'operacao.tracker.active.update',
        'operacao.timesheet',
        'operacao.timesheet.save',
        'operacao.calendario.index',
        'operacao.calendario.store',
        'operacao.calendario.mover',
        'operacao.calendario.destroy',
        'operacao.timeoff.index',
        'operacao.timeoff.store',
        'operacao.timeoff.update',
        'operacao.timeoff.destroy',
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
};
