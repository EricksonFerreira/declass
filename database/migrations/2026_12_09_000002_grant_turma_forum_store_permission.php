<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Professor passa a poder abrir topicos (orientacoes) no forum da
 * disciplina, alem de responder. Como a matriz de perfis so e
 * aplicada na criacao do papel, concedemos a nova permissao as
 * roles que ja tinham turma.forum.comentar — mesmo conjunto.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'turma.forum.store', 'guard_name' => 'web'],
            ['grupo' => 'turma', 'created_at' => $agora, 'updated_at' => $agora]
        );

        DB::statement("
            INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT nova.id, rhp.role_id
            FROM permissions nova
            JOIN permissions origem ON origem.name = 'turma.forum.comentar' AND origem.guard_name = 'web'
            JOIN role_has_permissions rhp ON rhp.permission_id = origem.id
            WHERE nova.name = 'turma.forum.store' AND nova.guard_name = 'web'
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
            ->where('name', 'turma.forum.store')
            ->where('guard_name', 'web')
            ->value('id');

        if ($id) {
            DB::table('role_has_permissions')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
