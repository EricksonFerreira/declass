<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Produção ainda tinha o índice único antigo (name, guard_name) em roles,
     * que impede o mesmo nome de perfil em empresas diferentes. Remove o
     * índice legado pelo nome e garante o único composto por empresa.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        if ($this->indexExists('roles', 'roles_name_guard_unique')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex('roles_name_guard_unique');
            });
        }

        if (
            Schema::hasColumn('roles', 'empresa_id')
            && ! $this->indexExists('roles', 'roles_empresa_name_guard_unique')
        ) {
            // Remove duplicatas exatas (empresa_id, name, guard_name) antes
            // de criar o índice, mantendo o registro mais antigo e
            // repassando usuários/permissões para ele.
            $duplicatas = DB::table('roles')
                ->select('empresa_id', 'name', 'guard_name', DB::raw('MIN(id) as manter_id'), DB::raw('COUNT(*) as total'))
                ->groupBy('empresa_id', 'name', 'guard_name')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicatas as $dup) {
                $extras = DB::table('roles')
                    ->where('name', $dup->name)
                    ->where('guard_name', $dup->guard_name)
                    ->where(function ($q) use ($dup) {
                        $dup->empresa_id === null
                            ? $q->whereNull('empresa_id')
                            : $q->where('empresa_id', $dup->empresa_id);
                    })
                    ->where('id', '!=', $dup->manter_id)
                    ->pluck('id');

                if ($extras->isNotEmpty()) {
                    DB::table('users')->whereIn('role_id', $extras)->update(['role_id' => $dup->manter_id]);
                    DB::table('role_has_permissions')->whereIn('role_id', $extras)->delete();
                    DB::table('roles')->whereIn('id', $extras)->delete();
                }
            }

            Schema::table('roles', function (Blueprint $table) {
                $table->unique(['empresa_id', 'name', 'guard_name'], 'roles_empresa_name_guard_unique');
            });
        }
    }

    public function down(): void
    {
        // Não reverte: restaurar o único antigo quebraria perfis por empresa.
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
