<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Permissao;
use App\Models\Regra;
use Database\Seeders\PapeisPermissoesSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PermissaoSyncService
{
    /**
     * Sincroniza permissões (por rotas nomeadas) e perfis padrão (roles) no modo global.
     *
     * @return array{permissions:int, roles:int, pivots:int}
     */
    public function sync(): array
    {
        $permissionNames = $this->collectPermissionNamesFromRoutes();
        if (empty($permissionNames)) {
            return ['permissions' => 0, 'roles' => 0, 'pivots' => 0];
        }

        $createdPermissions = $this->syncPermissionsGlobal($permissionNames);
        [$createdRoles, $pivotCount] = $this->syncRolesGlobal($permissionNames);

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        return ['permissions' => $createdPermissions, 'roles' => $createdRoles, 'pivots' => $pivotCount];
    }

    /**
     * Roda sync somente se estiver vazio (tela não mostra nada / regras sem permissões).
     */
    public function syncIfNeeded(): void
    {
        $hasPermissions = Permissao::query()->where('guard_name', 'web')->exists();
        $hasAnyPivot = DB::table('role_has_permissions')->exists();
        $adminRoleId = (int) (Regra::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(name) = ?', ['administrador'])
            ->value('id') ?? 0);

        $adminHasPivot = $adminRoleId > 0
            ? DB::table('role_has_permissions')->where('role_id', $adminRoleId)->exists()
            : false;

        if (! $hasPermissions || ! $hasAnyPivot || ! $adminHasPivot) {
            $this->sync();
        }
    }

    /**
     * @return array<int, string>
     */
    private function collectPermissionNamesFromRoutes(): array
    {
        $names = [];
        $ignoredPrefixes = [
            'login',
            'logout',
            'register',
            'password.',
            'verification.',
            'sanctum.',
            'ignition.',
            'debugbar.',
            'generated::',
        ];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name) {
                continue;
            }

            $ignore = false;
            foreach ($ignoredPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $ignore = true;
                    break;
                }
            }

            if ($ignore) {
                continue;
            }

            $names[] = $name;
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    /**
     * @param array<int, string> $permissionNames
     */
    private function syncPermissionsGlobal(array $permissionNames): int
    {
        $created = 0;

        foreach ($permissionNames as $permissionName) {
            $existing = Permissao::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if ($existing) {
                continue;
            }

            Permissao::query()->create([
                'name'       => $permissionName,
                'guard_name' => 'web',
                'descricao'  => null,
                'grupo'      => $this->grupoFromName($permissionName),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * @param array<int, string> $permissionNames
     * @return array{0:int, 1:int}
     */
    private function syncRolesGlobal(array $permissionNames): array
    {
        $allPermissions = Permissao::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionNames)
            ->get();

        $createdRoles = 0;
        $pivotCount = 0;

        // Perfis sao vinculados a empresa: cada tenant tem seus proprios
        // 'administrador' e 'usuario' (empresa_id preenchido).
        foreach (Empresa::query()->pluck('id') as $empresaId) {
            [$c, $p] = $this->garantirPapeisParaEmpresa((int) $empresaId, $allPermissions);
            $createdRoles += $c;
            $pivotCount += $p;
        }

        $this->limparPapeisGlobaisObsoletos();

        return [$createdRoles, $pivotCount];
    }

    /**
     * Garante os papeis padrao (administrador, usuario) de uma empresa e
     * sincroniza a matriz de permissoes de cada um.
     *
     * @param \Illuminate\Support\Collection<int, Permissao>|null $allPermissions
     * @return array{0:int, 1:int} [papeis criados, permissoes vinculadas]
     */
    public function garantirPapeisParaEmpresa(int $empresaId, $allPermissions = null): array
    {
        $allPermissions ??= Permissao::query()->where('guard_name', 'web')->get();

        $createdRoles = 0;
        $pivotCount = 0;

        foreach ($this->buildRolePermissionFilters() as $roleName => $filter) {
            $role = Regra::query()->firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'empresa_id' => $empresaId,
                ],
                []
            );

            if (! $role->wasRecentlyCreated) {
                continue;
            }

            $createdRoles++;

            // A matriz padrao so e aplicada na criacao do perfil — re-syncs
            // nao sobrescrevem ajustes feitos manualmente na edicao.
            $permissionsForRole = $allPermissions
                ->filter(fn (Permissao $permission) => $filter($permission->name))
                ->pluck('id')
                ->all();

            $role->permissoes()->sync($permissionsForRole);
            $pivotCount += count($permissionsForRole);
        }

        return [$createdRoles, $pivotCount];
    }

    /**
     * Perfis globais padrao: administrador (acesso total via isAdministrador)
     * e usuario (membro padrao, permissoes curadas no seeder).
     *
     * @return array<string, \Closure(string): bool>
     */
    private function buildRolePermissionFilters(): array
    {
        return [
            'administrador' => fn (string $permissionName) => true,
            'usuario' => fn (string $permissionName) =>
                in_array($permissionName, PapeisPermissoesSeeder::PERMISSOES_USUARIO, true),
        ];
    }

    /**
     * Perfis sao vinculados a empresa: remapeia usuarios de QUALQUER papel
     * global (empresa_id nulo) para o papel equivalente da propria empresa
     * — mesmo nome quando for um papel padrao, senao 'usuario'. Papeis
     * globais que ficam sem usuarios sao removidos; os que ainda tiverem
     * usuarios sem empresa ficam para revisao manual.
     */
    private function limparPapeisGlobaisObsoletos(): void
    {
        $globais = Regra::query()
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->get(['id', 'name']);

        if ($globais->isEmpty()) {
            return;
        }

        // [empresa_id][name em minusculo] => Regra
        $porEmpresa = Regra::query()
            ->where('guard_name', 'web')
            ->whereNotNull('empresa_id')
            ->get(['id', 'name', 'empresa_id'])
            ->groupBy('empresa_id')
            ->map(fn ($roles) => $roles->keyBy(fn ($r) => mb_strtolower($r->name)));

        $padroes = ['administrador', 'usuario'];
        $globaisPorId = $globais->keyBy('id');

        $users = DB::table('users')
            ->whereIn('role_id', $globais->pluck('id'))
            ->get(['id', 'role_id', 'empresa_id']);

        foreach ($users as $user) {
            $empresaId = (int) ($user->empresa_id ?? 0);
            if ($empresaId === 0 || ! isset($porEmpresa[$empresaId])) {
                continue;
            }

            $nomeGlobal = mb_strtolower((string) ($globaisPorId[$user->role_id]->name ?? ''));
            $alvo = in_array($nomeGlobal, $padroes, true) ? $nomeGlobal : 'usuario';
            $roleId = $porEmpresa[$empresaId][$alvo]->id ?? null;

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }

        $aindaUsados = DB::table('users')
            ->whereIn('role_id', $globais->pluck('id'))
            ->pluck('role_id')
            ->unique();

        $remover = $globais->pluck('id')->diff($aindaUsados);
        if ($remover->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('role_id', $remover)->delete();
            Regra::query()->whereIn('id', $remover)->delete();
        }
    }

    private function grupoFromName(string $name): string
    {
        if (!str_contains($name, '.')) {
            return $name;
        }
        return (string) str($name)->before('.');
    }
}
