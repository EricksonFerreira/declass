<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'empresa_id')) {
            // Consolida roles duplicadas por (name + guard_name), mantendo o menor id.
            $duplicadas = DB::table('roles')
                ->select('name', 'guard_name', DB::raw('MIN(id) as manter_id'), DB::raw('GROUP_CONCAT(id ORDER BY id) as todos_ids'))
                ->groupBy('name', 'guard_name')
                ->having(DB::raw('COUNT(*)'), '>', 1)
                ->get();

            foreach ($duplicadas as $dup) {
                $todos = array_map('intval', explode(',', (string) $dup->todos_ids));
                $manter = (int) $dup->manter_id;
                $remover = array_filter($todos, fn ($id) => $id !== $manter);

                foreach ($remover as $idRemover) {
                    // Redireciona role_has_permissions (evitando duplicatas).
                    if (Schema::hasTable('role_has_permissions')) {
                        $permissionIds = DB::table('role_has_permissions')
                            ->where('role_id', $idRemover)
                            ->pluck('permission_id')
                            ->all();

                        foreach ($permissionIds as $permissionId) {
                            $exists = DB::table('role_has_permissions')
                                ->where('role_id', $manter)
                                ->where('permission_id', $permissionId)
                                ->exists();

                            if (! $exists) {
                                DB::table('role_has_permissions')->insert([
                                    'role_id' => $manter,
                                    'permission_id' => $permissionId,
                                ]);
                            }
                        }

                        DB::table('role_has_permissions')->where('role_id', $idRemover)->delete();
                    }

                    // Redireciona usuários que apontavam para a role antiga.
                    if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
                        DB::table('users')->where('role_id', $idRemover)->update(['role_id' => $manter]);
                    }
                }

                DB::table('roles')->whereIn('id', $remover)->delete();
            }

            // Roles são defaults globais do sistema.
            DB::table('roles')->update(['empresa_id' => null]);
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'empresa_id')) {
            // Permissões já são globais, mas garantimos empresa_id = null.
            DB::table('permissions')->update(['empresa_id' => null]);
        }

        // Ajusta índices/unique para global (name + guard_name).
        if (Schema::hasTable('roles')) {
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropUnique('roles_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropUnique(['empresa_id', 'name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['name', 'guard_name'], 'roles_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (Schema::hasTable('permissions')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique('permissions_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique(['empresa_id', 'name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->unique(['name', 'guard_name'], 'permissions_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Não há "volta" segura para re-vincular roles/permissões por empresa.
    }
};

