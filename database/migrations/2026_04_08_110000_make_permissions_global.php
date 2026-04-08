<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adiciona coluna descricao
        if (!Schema::hasColumn('permissions', 'descricao')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('descricao', 500)->nullable()->after('guard_name');
            });
        }

        // 2. Remove duplicatas — mantém somente uma permissão por name+guard_name (a de menor id)
        // Primeiro: garante que role_has_permissions aponta para o id que vamos manter
        $permissoesDuplicadas = DB::table('permissions')
            ->select('name', 'guard_name', DB::raw('MIN(id) as manter_id'), DB::raw('GROUP_CONCAT(id ORDER BY id) as todos_ids'))
            ->groupBy('name', 'guard_name')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($permissoesDuplicadas as $dup) {
            $todos = array_map('intval', explode(',', $dup->todos_ids));
            $manter = (int) $dup->manter_id;
            $remover = array_filter($todos, fn ($id) => $id !== $manter);

            // Redireciona role_has_permissions para o id que será mantido
            foreach ($remover as $idRemover) {
                // Pega os roles que usam esta permissão duplicada
                $roles = DB::table('role_has_permissions')
                    ->where('permission_id', $idRemover)
                    ->pluck('role_id');

                foreach ($roles as $roleId) {
                    // Só insere se ainda não existir a combinação
                    $exists = DB::table('role_has_permissions')
                        ->where('permission_id', $manter)
                        ->where('role_id', $roleId)
                        ->exists();
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'permission_id' => $manter,
                            'role_id'       => $roleId,
                        ]);
                    }
                }

                // Remove entradas antigas
                DB::table('role_has_permissions')->where('permission_id', $idRemover)->delete();
            }

            // Remove permissões duplicadas
            DB::table('permissions')->whereIn('id', $remover)->delete();
        }

        // 3. Torna empresa_id nullable (já é) e seta todos para null (permissões são globais)
        DB::table('permissions')->update(['empresa_id' => null]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('permissions', 'descricao')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('descricao');
            });
        }
    }
};
