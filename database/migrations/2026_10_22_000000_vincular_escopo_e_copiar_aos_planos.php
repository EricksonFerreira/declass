<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * As permissoes escopo.* e operacao.timesheet.copiar nao sao features
     * pagas — todo plano deve poder concede-las. Sem vinculo em
     * plano_permissao, elas nem aparecem na edicao de perfil para
     * empresas com plano ativo.
     */
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('plano_permissao')) {
            return;
        }

        $permIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->where(function ($q): void {
                $q->where('name', 'operacao.timesheet.copiar')
                    ->orWhere('name', 'like', 'escopo.%');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($permIds === []) {
            return;
        }

        // So planos que ja tem matriz em plano_permissao; plano sem
        // restricao ja enxerga todas as permissoes.
        $planoIds = DB::table('plano_permissao')
            ->distinct()
            ->pluck('plano_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $pivots = [];
        foreach ($planoIds as $planoId) {
            foreach ($permIds as $pid) {
                $pivots[] = ['plano_id' => $planoId, 'permission_id' => $pid];
            }
        }

        foreach (array_chunk($pivots, 500) as $chunk) {
            DB::table('plano_permissao')->insertOrIgnore($chunk);
        }
    }

    public function down(): void
    {
        // Vinculos sao idempotentes; remover exigiria distinguir o que
        // ja existia — sem rollback.
    }
};
