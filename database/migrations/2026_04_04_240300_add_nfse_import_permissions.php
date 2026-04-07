<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $roleHasPermissions = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        if (!Schema::hasTable($permissionsTable) || !Schema::hasTable('empresas')) {
            return;
        }

        $permissionHasEmpresa = Schema::hasColumn($permissionsTable, 'empresa_id');
        $empresaIds = DB::table('empresas')->pluck('id')->all();
        $permissoes = [
            'nfse.importarPendentes',
        ];

        foreach ($empresaIds as $empresaId) {
            foreach ($permissoes as $name) {
                $query = DB::table($permissionsTable)
                    ->where('name', $name)
                    ->where('guard_name', 'web');
                if ($permissionHasEmpresa) {
                    $query->where('empresa_id', $empresaId);
                }

                $permissionId = $query->value('id');
                if (!$permissionId) {
                    $data = [
                        'name' => $name,
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if ($permissionHasEmpresa) {
                        $data['empresa_id'] = $empresaId;
                    }
                    $permissionId = DB::table($permissionsTable)->insertGetId($data);
                }

                if (Schema::hasTable($rolesTable) && Schema::hasTable($roleHasPermissions)) {
                    $role = DB::table($rolesTable)
                        ->where('name', 'administrador')
                        ->when($permissionHasEmpresa, fn ($q) => $q->where('empresa_id', $empresaId))
                        ->first();

                    if ($role) {
                        $exists = DB::table($roleHasPermissions)
                            ->where('permission_id', $permissionId)
                            ->where('role_id', $role->id)
                            ->exists();

                        if (!$exists) {
                            DB::table($roleHasPermissions)->insert([
                                'permission_id' => $permissionId,
                                'role_id' => $role->id,
                            ]);
                        }
                    }
                }
            }
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';

        if (!Schema::hasTable($permissionsTable)) {
            return;
        }

        DB::table($permissionsTable)
            ->where('name', 'nfse.importarPendentes')
            ->delete();

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
        Schema::enableForeignKeyConstraints();
    }
};
