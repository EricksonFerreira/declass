<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nova tela "Visão Geral do Financeiro". A matriz de permissoes so e
 * aplicada na criacao do perfil, entao concedemos a permissao as
 * roles que ja possuíam fluxo_caixa.index — mesmo publico (diretoria
 * e financeiro), sem abrir acesso extra.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'financeiro_visao_geral.index', 'guard_name' => 'web'],
            ['grupo' => 'financeiro', 'created_at' => $agora, 'updated_at' => $agora]
        );

        DB::statement("
            INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT nova.id, rhp.role_id
            FROM permissions nova
            JOIN permissions origem ON origem.name = 'fluxo_caixa.index' AND origem.guard_name = 'web'
            JOIN role_has_permissions rhp ON rhp.permission_id = origem.id
            WHERE nova.name = 'financeiro_visao_geral.index' AND nova.guard_name = 'web'
              AND NOT EXISTS (
                  SELECT 1 FROM role_has_permissions x
                  WHERE x.permission_id = nova.id AND x.role_id = rhp.role_id
              )
        ");

        $cacheKey = config('permission.cache.key');
        if (is_string($cacheKey) && $cacheKey !== '') {
            cache()->forget($cacheKey);
        }
    }

    public function down(): void
    {
        DB::table('role_has_permissions')
            ->whereIn('permission_id', DB::table('permissions')->where('name', 'financeiro_visao_geral.index')->pluck('id'))
            ->delete();

        DB::table('permissions')->where('name', 'financeiro_visao_geral.index')->delete();
    }
};
