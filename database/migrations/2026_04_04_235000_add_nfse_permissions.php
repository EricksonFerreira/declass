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
        if (!Schema::hasTable($permissionsTable) || !Schema::hasTable('empresas')) {
            return;
        }

        $permissionHasEmpresa = Schema::hasColumn($permissionsTable, 'empresa_id');
        $empresaIds = DB::table('empresas')->pluck('id')->all();
        $permissoes = [
            'nfse.index',
            'nfse.download',
        ];

        foreach ($empresaIds as $empresaId) {
            foreach ($permissoes as $name) {
                $query = DB::table($permissionsTable)
                    ->where('name', $name)
                    ->where('guard_name', 'web');
                if ($permissionHasEmpresa) {
                    $query->where('empresa_id', $empresaId);
                }
                $exists = $query->exists();
                if ($exists) {
                    continue;
                }

                $data = [
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($permissionHasEmpresa) {
                    $data['empresa_id'] = $empresaId;
                }
                DB::table($permissionsTable)->insert($data);
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
        if (!Schema::hasTable($permissionsTable)) {
            return;
        }

        DB::table($permissionsTable)
            ->whereIn('name', ['nfse.index', 'nfse.download'])
            ->delete();

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
