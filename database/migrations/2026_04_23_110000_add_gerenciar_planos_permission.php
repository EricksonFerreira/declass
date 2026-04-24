<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Garante que a permissão exista.
        $permissaoId = DB::table('permissions')
            ->where('name', 'gerenciar-planos')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissaoId) {
            $permissaoId = DB::table('permissions')->insertGetId([
                'name'       => 'gerenciar-planos',
                'guard_name' => 'web',
                'descricao'  => 'Gerenciar planos e assinaturas das empresas',
                'grupo'      => 'Planos',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Vincula ao role de id=1 (superadmin), se a tabela existir e o role existir.
        $superAdminRole = DB::table('roles')->where('id', 1)->first();
        if ($superAdminRole) {
            $jaVinculado = DB::table('role_has_permissions')
                ->where('permission_id', $permissaoId)
                ->where('role_id', 1)
                ->exists();

            if (!$jaVinculado) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissaoId,
                    'role_id'       => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissao = DB::table('permissions')
            ->where('name', 'gerenciar-planos')
            ->where('guard_name', 'web')
            ->first();

        if ($permissao) {
            DB::table('role_has_permissions')->where('permission_id', $permissao->id)->delete();
            DB::table('permissions')->where('id', $permissao->id)->delete();
        }
    }
};
