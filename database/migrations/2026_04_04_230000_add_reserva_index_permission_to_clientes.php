<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $roleHasTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        if (
            !Schema::hasTable($permissionsTable)
            || !Schema::hasTable($rolesTable)
            || !Schema::hasTable($roleHasTable)
            || !Schema::hasTable('empresas')
        ) {
            return;
        }

        $permissionName = 'reserva.index';
        $guardName = 'web';
        $permissionHasEmpresa = Schema::hasColumn($permissionsTable, 'empresa_id');
        $roleHasEmpresa = Schema::hasColumn($rolesTable, 'empresa_id');

        $empresaIds = DB::table('empresas')->pluck('id')->all();

        foreach ($empresaIds as $empresaId) {
            $permissionQuery = DB::table($permissionsTable)
                ->where('name', $permissionName)
                ->where('guard_name', $guardName);
            if ($permissionHasEmpresa) {
                $permissionQuery->where('empresa_id', $empresaId);
            }
            $permissionId = $permissionQuery->value('id');

            if (!$permissionId) {
                $data = [
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($permissionHasEmpresa) {
                    $data['empresa_id'] = $empresaId;
                }
                $permissionId = DB::table($permissionsTable)->insertGetId($data);
            }

            $roleQuery = DB::table($rolesTable)
                ->whereRaw('LOWER(name) = ?', ['cliente'])
                ->where('guard_name', $guardName);
            if ($roleHasEmpresa) {
                $roleQuery->where('empresa_id', $empresaId);
            }
            $roleId = $roleQuery->value('id');

            if ($roleId && $permissionId) {
                $exists = DB::table($roleHasTable)
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $roleId)
                    ->exists();
                if (!$exists) {
                    DB::table($roleHasTable)->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $roleHasTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        if (
            !Schema::hasTable($permissionsTable)
            || !Schema::hasTable($rolesTable)
            || !Schema::hasTable($roleHasTable)
            || !Schema::hasTable('empresas')
        ) {
            return;
        }

        $permissionName = 'reserva.index';
        $guardName = 'web';
        $permissionHasEmpresa = Schema::hasColumn($permissionsTable, 'empresa_id');
        $roleHasEmpresa = Schema::hasColumn($rolesTable, 'empresa_id');

        $empresaIds = DB::table('empresas')->pluck('id')->all();

        foreach ($empresaIds as $empresaId) {
            $permissionQuery = DB::table($permissionsTable)
                ->where('name', $permissionName)
                ->where('guard_name', $guardName);
            if ($permissionHasEmpresa) {
                $permissionQuery->where('empresa_id', $empresaId);
            }
            $permissionId = $permissionQuery->value('id');
            if (!$permissionId) {
                continue;
            }

            $roleQuery = DB::table($rolesTable)
                ->whereRaw('LOWER(name) = ?', ['cliente'])
                ->where('guard_name', $guardName);
            if ($roleHasEmpresa) {
                $roleQuery->where('empresa_id', $empresaId);
            }
            $roleId = $roleQuery->value('id');

            if ($roleId) {
                DB::table($roleHasTable)
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $roleId)
                    ->delete();
            }

            $stillUsed = DB::table($roleHasTable)
                ->where('permission_id', $permissionId)
                ->exists();
            if (!$stillUsed) {
                DB::table($permissionsTable)->where('id', $permissionId)->delete();
            }
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
