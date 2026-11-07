<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * O endpoint diario.disciplinas alimenta o select dependente da tela
 * de registro de aula. Como a matriz de permissoes so e aplicada na
 * criacao do perfil, concedemos a nova permissao as roles que ja
 * possuíam diario.create — mesmo conjunto de usuarios que pode
 * registrar aula, sem abrir acesso extra.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'diario.disciplinas', 'guard_name' => 'web'],
            ['grupo' => 'diario', 'created_at' => $agora, 'updated_at' => $agora]
        );

        DB::statement("
            INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT nova.id, rhp.role_id
            FROM permissions nova
            JOIN permissions origem ON origem.name = 'diario.create' AND origem.guard_name = 'web'
            JOIN role_has_permissions rhp ON rhp.permission_id = origem.id
            WHERE nova.name = 'diario.disciplinas' AND nova.guard_name = 'web'
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
        $id = DB::table('permissions')
            ->where('name', 'diario.disciplinas')
            ->where('guard_name', 'web')
            ->value('id');

        if ($id) {
            DB::table('role_has_permissions')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
