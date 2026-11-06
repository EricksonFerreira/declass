<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\Permissao;
use App\Models\Regra;
use Database\Seeders\PerfisEscolaresSeeder;
use Illuminate\Console\Command;

/**
 * Aplica ADITIVAMENTE a matriz padrao de PerfisEscolaresSeeder aos
 * papeis ja existentes de cada tenant. Nunca remove permissoes —
 * seguro para reexecucao e para ajustes manuais feitos na tela de
 * perfis. Uso: apos adicionar rotas/permissoes novas (modulos).
 */
class AplicarMatrizPermissoesCommand extends Command
{
    protected $signature = 'permissoes:aplicar-matriz {--empresa= : Limita a um empresa_id}';

    protected $description = 'Concede aos papeis existentes as permissoes que faltam da matriz padrao (sem remover nenhuma).';

    public function handle(): int
    {
        app(\App\Services\PermissaoSyncService::class)->sync();

        $todas = Permissao::query()->where('guard_name', 'web')->pluck('name');
        $empresaId = $this->option('empresa') ? (int) $this->option('empresa') : null;

        $empresas = Empresa::query()
            ->when($empresaId, fn ($q) => $q->where('id', $empresaId))
            ->pluck('id');

        $totalVinculos = 0;

        foreach ($empresas as $eid) {
            foreach (PerfisEscolaresSeeder::PERFIS as $nomePapel => $padroes) {
                $role = Regra::query()
                    ->where('name', $nomePapel)
                    ->where('guard_name', 'web')
                    ->where('empresa_id', $eid)
                    ->first();

                if (! $role) {
                    continue;
                }

                $permitidos = PerfisEscolaresSeeder::resolverPadroes($padroes, $todas);
                $ids = Permissao::query()
                    ->where('guard_name', 'web')
                    ->whereIn('name', $permitidos)
                    ->pluck('id')
                    ->all();

                $jaTem = $role->permissoes()->pluck('permissions.id')->all();
                $faltando = array_diff($ids, $jaTem);

                if ($faltando) {
                    $role->permissoes()->syncWithoutDetaching($faltando);
                    $totalVinculos += count($faltando);
                    $this->line("{$nomePapel} (empresa {$eid}): +".count($faltando).' permissoes');
                }
            }
        }

        $this->info("Concluido: {$totalVinculos} vinculos de permissao adicionados.");

        return self::SUCCESS;
    }
}
