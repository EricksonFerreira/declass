<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Forum do portal: aluno passa a poder editar o titulo e encerrar/
 * reabrir a propria conversa. Como a matriz de perfis so e aplicada
 * na criacao do papel, concedemos as novas permissoes as roles que
 * ja tinham portal.turma.disciplina.forum.comentar — mesmo conjunto.
 */
return new class extends Migration
{
    private const NOVAS = [
        'portal.turma.disciplina.forum.update',
        'portal.turma.disciplina.forum.encerrar',
    ];

    public function up(): void
    {
        $agora = now();

        foreach (self::NOVAS as $nome) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web'],
                ['grupo' => 'portal', 'created_at' => $agora, 'updated_at' => $agora]
            );
        }

        DB::statement("
            INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT nova.id, rhp.role_id
            FROM permissions nova
            JOIN permissions origem ON origem.name = 'portal.turma.disciplina.forum.comentar' AND origem.guard_name = 'web'
            JOIN role_has_permissions rhp ON rhp.permission_id = origem.id
            WHERE nova.name IN ('portal.turma.disciplina.forum.update', 'portal.turma.disciplina.forum.encerrar')
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
