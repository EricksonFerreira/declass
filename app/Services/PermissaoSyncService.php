<?php

namespace App\Services;

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

        foreach ($this->buildRolePermissionFilters() as $roleName => $filter) {
            $role = Regra::query()->firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                []
            );

            if ($role->wasRecentlyCreated) {
                $createdRoles++;
            }

            $permissionsForRole = $allPermissions
                ->filter(fn (Permissao $permission) => $filter($permission->name))
                ->pluck('id')
                ->all();

            $role->permissoes()->sync($permissionsForRole);
            $pivotCount += count($permissionsForRole);
        }

        $this->limparPapeisGlobaisObsoletos();

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
     * Remapeia usuarios de papeis globais fora de [administrador, usuario]
     * para 'usuario' e remove esses papeis. Papeis de empresa
     * (empresa_id preenchido) pertencem ao tenant e sao preservados.
     */
    private function limparPapeisGlobaisObsoletos(): void
    {
        $usuarioRole = Regra::query()
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->whereRaw('LOWER(name) = ?', ['usuario'])
            ->first();

        if (! $usuarioRole) {
            return;
        }

        $idsObsoletos = Regra::query()
            ->where('guard_name', 'web')
            ->whereNull('empresa_id')
            ->whereRaw("LOWER(name) NOT IN ('administrador', 'usuario')")
            ->pluck('id');

        if ($idsObsoletos->isEmpty()) {
            return;
        }

        DB::table('users')->whereIn('role_id', $idsObsoletos)->update(['role_id' => $usuarioRole->id]);

        DB::table('role_has_permissions')->whereIn('role_id', $idsObsoletos)->delete();
        Regra::query()->whereIn('id', $idsObsoletos)->delete();
    }

    private function grupoFromName(string $name): string
    {
        if (!str_contains($name, '.')) {
            return $name;
        }
        return (string) str($name)->before('.');
    }
}
