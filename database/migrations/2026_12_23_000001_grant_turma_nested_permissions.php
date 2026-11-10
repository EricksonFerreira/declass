<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * As telas aninhadas a turma (turma/{turma}/chamada/create,
 * turma/{turma}/avaliacao/create etc.) sao URLs alternativas dos
 * mesmos controllers — mas o CheckPermissaoPorRota exige o nome
 * exato da rota como permissao. Perfis com a permissao base ja
 * exibiam os botoes (@can testa a permissao original) e caiam em
 * 403 ao clicar.
 *
 * Como a matriz de permissoes so e aplicada na criacao do perfil,
 * concedemos cada permissao aninhada as roles que ja possuíam a
 * permissao equivalente do controller — mesmo conjunto de usuarios,
 * sem abrir acesso extra.
 */
return new class extends Migration
{
    /** Rota aninhada => permissao base que libera a mesma tela. */
    private const MAPA = [
        'turma.chamada' => 'diario.frequencia',
        'turma.chamada.create' => 'diario.frequencia',
        'turma.avaliacao.create' => 'avaliacao.create',
        'turma.tarefa.create' => 'tarefa.create',
        'turma.matricula.create' => 'matricula.create',
        'turma.justificativas' => 'justificativa.index',
        'turma.justificativa.create' => 'justificativa.create',
        'turma.calendario' => 'calendario.index',
    ];

    public function up(): void
    {
        $agora = now();

        foreach (self::MAPA as $aninhada => $base) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $aninhada, 'guard_name' => 'web'],
                ['grupo' => 'turma', 'created_at' => $agora, 'updated_at' => $agora]
            );

            $this->conceder($base, $aninhada);
        }

        $cacheKey = config('permission.cache.key');
        if (is_string($cacheKey) && $cacheKey !== '') {
            cache()->forget($cacheKey);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', array_keys(self::MAPA))
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
    }

    /** Copia o grant da permissao base para a aninhada, sem duplicar. */
    private function conceder(string $permissaoBase, string $permissaoAninhada): void
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
        ", [$permissaoBase, $permissaoAninhada]);
    }
};
