<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Permissoes do CRUD de tipos de solicitacao e do download de anexos.
 * Como a matriz de perfis so e aplicada na criacao do perfil, concede
 * as novas permissoes as roles que ja tratavam solicitacoes —
 * mesmo conjunto de usuarios, sem abrir acesso extra.
 */
return new class extends Migration
{
    private const NOVAS = [
        'solicitacao.anexo' => 'solicitacao',
        'tipo_solicitacao.index' => 'tipo_solicitacao',
        'tipo_solicitacao.create' => 'tipo_solicitacao',
        'tipo_solicitacao.store' => 'tipo_solicitacao',
        'tipo_solicitacao.edit' => 'tipo_solicitacao',
        'tipo_solicitacao.update' => 'tipo_solicitacao',
        'tipo_solicitacao.destroy' => 'tipo_solicitacao',
    ];

    public function up(): void
    {
        $agora = now();

        foreach (self::NOVAS as $nome => $grupo) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web'],
                ['grupo' => $grupo, 'created_at' => $agora, 'updated_at' => $agora]
            );
        }

        // Quem ve a fila baixa anexos e consulta os tipos
        $this->conceder('solicitacao.index', 'solicitacao.anexo');
        $this->conceder('solicitacao.index', 'tipo_solicitacao.index');

        // Quem trata solicitacoes gerencia os tipos
        foreach (['create', 'store', 'edit', 'update', 'destroy'] as $acao) {
            $this->conceder('solicitacao.responder', "tipo_solicitacao.$acao");
        }

        $cacheKey = config('permission.cache.key');
        if (is_string($cacheKey) && $cacheKey !== '') {
            cache()->forget($cacheKey);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', array_keys(self::NOVAS))
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
