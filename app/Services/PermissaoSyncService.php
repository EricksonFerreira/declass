<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Permissao;
use App\Models\Regra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PermissaoSyncService
{
    /**
     * Sincroniza permissões (por rotas nomeadas) e perfis padrão (roles) no modo global.
     *
     * @return array{permissions:int, roles:int, pivots:int}
     */
    /**
     * Permissoes que nao derivam de rotas mas fazem parte do produto —
     * sempre garantidas pelo sync (independente de migration).
     *
     * @var array<int, string>
     */
    private const PERMISSOES_EXTRAS = [];

    public function sync(): array
    {
        $permissionNames = $this->collectPermissionNamesFromRoutes();
        if (empty($permissionNames)) {
            return ['permissions' => 0, 'roles' => 0, 'pivots' => 0];
        }

        $permissionNames = array_values(array_unique(
            array_merge($permissionNames, self::PERMISSOES_EXTRAS)
        ));

        $createdPermissions = $this->syncPermissionsGlobal($permissionNames);
        [$createdRoles, $pivotCount] = $this->syncRolesGlobal($permissionNames);

        $cacheKey = config('permission.cache.key');
        if (is_string($cacheKey) && $cacheKey !== '') {
            app('cache')
                ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
                ->forget($cacheKey);
        }

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

        // Garante tambem as permissoes extras mesmo quando o restante
        // ja esta populado — cobre ambientes onde a migration
        // correspondente ainda nao rodou.
        $faltamExtras = Permissao::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::PERMISSOES_EXTRAS)
            ->count() < count(self::PERMISSOES_EXTRAS);

        if (! $hasPermissions || ! $hasAnyPivot || ! $adminHasPivot || $faltamExtras) {
            $this->sync();
        }
    }

    /**
     * Padroes de permissao por perfil — definidos em
     * PerfisEscolaresSeeder::PERFIS (matriz da especificacao).
     *
     * @return array<string, array<int, string>>
     */
    private function buildRolePermissionFilters(): array
    {
        return \Database\Seeders\PerfisEscolaresSeeder::PERFIS;
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
     * @param  array<int, string>  $permissionNames
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
                'name' => $permissionName,
                'guard_name' => 'web',
                'descricao' => null,
                'grupo' => $this->grupoFromName($permissionName),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * @param  array<int, string>  $permissionNames
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
        // roles (empresa_id preenchido).
        foreach (Empresa::query()->pluck('id') as $empresaId) {
            [$c, $p] = $this->garantirPapeisParaEmpresa((int) $empresaId, $allPermissions);
            $createdRoles += $c;
            $pivotCount += $p;
        }

        $this->limparPapeisGlobaisObsoletos();

        return [$createdRoles, $pivotCount];
    }

    /**
     * Garante os papeis padrao de uma empresa e
     * sincroniza a matriz de permissoes de cada um.
     *
     * @param  \Illuminate\Support\Collection<int, Permissao>|null  $allPermissions
     * @return array{0:int, 1:int} [papeis criados, permissoes vinculadas]
     */
    public function garantirPapeisParaEmpresa(int $empresaId, $allPermissions = null): array
    {
        $allPermissions ??= Permissao::query()->where('guard_name', 'web')->get();

        $createdRoles = 0;
        $pivotCount = 0;

        foreach ($this->buildRolePermissionFilters() as $roleName => $padroes) {
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
            $nomesRoles = $allPermissions->pluck('name');
            $nomesPermitidos = \Database\Seeders\PerfisEscolaresSeeder::resolverPadroes($padroes, $nomesRoles);
            $permissionsForRole = $allPermissions
                ->filter(fn (Permissao $permission) => in_array($permission->name, $nomesPermitidos, true))
                ->pluck('id')
                ->all();

            $role->permissoes()->sync($permissionsForRole);
            $pivotCount += count($permissionsForRole);
        }

        return [$createdRoles, $pivotCount];
    }

    /**
     * Perfis sao vinculados a empresa: remapeia usuarios de QUALQUER papel
     * global (empresa_id nulo) para o papel equivalente da propria empresa
     * — mesmo nome quando for um perfil padrao, senao 'membro'. Papeis
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

        $padroes = array_keys(\Database\Seeders\PerfisEscolaresSeeder::PERFIS);
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
            $alvo = in_array($nomeGlobal, $padroes, true) ? $nomeGlobal : 'membro';
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
        if (! str_contains($name, '.')) {
            return $name;
        }

        return (string) str($name)->before('.');
    }
}
