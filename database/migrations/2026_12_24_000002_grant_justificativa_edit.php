<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cria as permissoes de edicao de justificativa (edit/update —
 * o CheckPermissaoPorRota exige o nome exato da rota) e concede
 * as roles que ja tinham justificativa.create.
 */
return new class extends Migration
{
    private const NOVAS = ['justificativa.edit', 'justificativa.update'];

    public function up(): void
    {
        $agora = now();

        foreach (self::NOVAS as $nome) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web'],
                ['grupo' => 'justificativa', 'created_at' => $agora, 'updated_at' => $agora]
            );
        }

        $permissaoBase = DB::table('permissions')->where('name', 'justificativa.create')->value('id');
        if ($permissaoBase) {
            $roleIds = DB::table('role_has_permissions')->where('permission_id', $permissaoBase)->pluck('role_id');
            $novasIds = DB::table('permissions')->whereIn('name', self::NOVAS)->pluck('id');

            foreach ($roleIds as $roleId) {
                foreach ($novasIds as $permId) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['permission_id' => $permId, 'role_id' => $roleId],
                        []
                    );
                }
            }
        }

        $cacheKey = config('permission.cache.key');
        if (is_string($cacheKey) && $cacheKey !== '') {
            cache()->forget($cacheKey);
        }
    }

    public function down(): void
    {
        // Nao remove — permissao concedida pode ter sido ajustada a mao.
    }
};
