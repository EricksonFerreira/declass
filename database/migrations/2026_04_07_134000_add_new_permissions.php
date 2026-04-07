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

        if (!Schema::hasTable($permissionsTable) || !Schema::hasTable('empresas')) {
            return;
        }

        $permissionHasEmpresa = Schema::hasColumn($permissionsTable, 'empresa_id');
        $empresaIds = DB::table('empresas')->pluck('id')->all();

        $permissoes = [
            // Feature 2: Editar reserva no show
            'reserva.editar_no_show',
            'reserva.gerenciar_opcionais',

            // Feature 3: Multas
            'multa.index',
            'multa.create',
            'multa.edit',
            'multa.delete',
            'multa.export',

            // Feature 4: Calcao
            'calcao.autorizar',
            'calcao.capturar',
            'calcao.cancelar',
            'calcao.visualizar',
        ];

        foreach ($empresaIds as $empresaId) {
            foreach ($permissoes as $name) {
                $query = DB::table($permissionsTable)
                    ->where('name', $name)
                    ->where('guard_name', 'web');

                if ($permissionHasEmpresa) {
                    $query->where('empresa_id', $empresaId);
                }

                if ($query->exists()) {
                    continue;
                }

                $data = [
                    'name'       => $name,
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

        DB::table($permissionsTable)->whereIn('name', [
            'reserva.editar_no_show',
            'reserva.gerenciar_opcionais',
            'multa.index',
            'multa.create',
            'multa.edit',
            'multa.delete',
            'multa.export',
            'calcao.autorizar',
            'calcao.capturar',
            'calcao.cancelar',
            'calcao.visualizar',
        ])->delete();

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
        Schema::enableForeignKeyConstraints();
    }
};
