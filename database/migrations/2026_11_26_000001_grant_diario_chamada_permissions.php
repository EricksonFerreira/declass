<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A chamada unificada (diario.chamada / diario.chamada.salvar) substitui
 * o fluxo antigo frequencia/create. Como a matriz de permissoes so e
 * aplicada na criacao do perfil, concedemos as novas permissoes as
 * roles que ja possuíam a permissao de frequencia equivalente —
 * mesmo conjunto de usuarios, sem abrir acesso extra.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        foreach (['diario.chamada', 'diario.chamada.salvar'] as $nome) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web'],
                ['grupo' => 'diario', 'created_at' => $agora, 'updated_at' => $agora]
            );
        }

        $this->conceder('diario.frequencia', 'diario.chamada');
        $this->conceder('diario.frequencia.salvar', 'diario.chamada.salvar');

        $cacheKey = config('permission.cache.key');
        if (is_string($cacheKey) && $cacheKey !== '') {
            cache()->forget($cacheKey);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', ['diario.chamada', 'diario.chamada.salvar'])
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }

    /** Copia o grant da permissao antiga para a nova, sem duplicar. */
    private function conceder(string $permissaoOrigem, string $permissaoNova): void
    {
        DB::statement("
            INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT nova.id, rhp.role_id
            FROM permissions nova
            JOIN permissions antiga ON antiga.name = ? AND antiga.guard_name = 'web'
            JOIN role_has_permissions rhp ON rhp.permission_id = antiga.id
            WHERE nova.name = ? AND nova.guard_name = 'web'
              AND NOT EXISTS (
                  SELECT 1 FROM role_has_permissions x
                  WHERE x.permission_id = nova.id AND x.role_id = rhp.role_id
              )
        ", [$permissaoOrigem, $permissaoNova]);
    }
};
