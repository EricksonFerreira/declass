<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Moderacao do forum: professor/coordenacao passam a poder editar
 * o titulo, encerrar/reabrir e remover qualquer topico da
 * disciplina que lecionam. Como a matriz de perfis so e aplicada
 * na criacao do papel, concedemos as novas permissoes as roles
 * que ja tinham turma.forum.comentar — mesmo conjunto.
 */
return new class extends Migration
{
    private const NOVAS = [
        'turma.forum.update',
        'turma.forum.encerrar',
        'turma.forum.destroy',
    ];

    public function up(): void
    {
        $agora = now();

        foreach (self::NOVAS as $nome) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web'],
                ['grupo' => 'turma', 'created_at' => $agora, 'updated_at' => $agora]
            );
        }

        DB::statement("
            INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT nova.id, rhp.role_id
            FROM permissions nova
            JOIN permissions origem ON origem.name = 'turma.forum.comentar' AND origem.guard_name = 'web'
            JOIN role_has_permissions rhp ON rhp.permission_id = origem.id
            WHERE nova.name IN ('turma.forum.update', 'turma.forum.encerrar', 'turma.forum.destroy')
              AND nova.guard_name = 'web'
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
        $ids = DB::table('permissions')
            ->whereIn('name', self::NOVAS)
            ->where('guard_name', 'web')
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
