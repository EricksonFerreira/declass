<?php

namespace App\Console\Commands;

use App\Services\NfseService;
use Illuminate\Console\Command;

class ImportarNfsePendentesCommand extends Command
{
    protected $signature = 'nfse:importar-pendentes {--empresa_id=}';

    protected $description = 'Importa e sincroniza notas fiscais pendentes com limite minimo de 1 hora entre consultas.';

    public function __construct(private readonly NfseService $nfseService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $empresaId = $this->option('empresa_id');
        $totais = [
            'total' => 0,
            'atualizadas' => 0,
            'ignoradas' => 0,
            'erros' => 0,
        ];

        $empresas = $empresaId
            ? \App\Models\Empresa::query()->whereKey((int) $empresaId)->get()
            : \App\Models\Empresa::query()->get();

        foreach ($empresas as $empresa) {
            if (($empresa->nfse_ativo ?? 'N') !== 'S') {
                $this->warn('Empresa #' . $empresa->id . ' sem NFS-e ativo. Ignorando.');
                continue;
            }

            $ultima = $empresa->nfse_ultima_importacao_em;
            if ($ultima && $ultima->copy()->addHour()->isFuture()) {
                $this->warn('Empresa #' . $empresa->id . ' ainda na janela de 1h. Ignorando.');
                continue;
            }

            $resultado = $this->nfseService->sincronizarPendentes((int) $empresa->id);
            $empresa->nfse_ultima_importacao_em = now();
            $empresa->save();

            foreach ($totais as $key => $value) {
                $totais[$key] += $resultado[$key] ?? 0;
            }
        }

        $this->info('Importacao concluida.');
        $this->line('Total avaliadas: ' . $totais['total']);
        $this->line('Atualizadas: ' . $totais['atualizadas']);
        $this->line('Ignoradas (janela 1h): ' . $totais['ignoradas']);
        $this->line('Erros: ' . $totais['erros']);

        return self::SUCCESS;
    }
}
