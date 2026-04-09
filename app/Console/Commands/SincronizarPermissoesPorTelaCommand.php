<?php

namespace App\Console\Commands;

use App\Services\PermissaoSyncService;
use Illuminate\Console\Command;

class SincronizarPermissoesPorTelaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissoes:sincronizar-telas {--empresa_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria permissoes por tela/acao e sincroniza perfis (globais)';

    public function __construct(private readonly PermissaoSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('empresa_id')) {
            $this->warn('Opcao --empresa_id ignorada: permissoes/regras sao globais.');
        }

        $this->info('Sincronizando permissoes e regras (globais)...');

        $result = $this->syncService->sync();

        $this->info('Permissoes e regras sincronizadas com sucesso.');
        $this->line(sprintf('Criadas: %d permissoes, %d regras.', (int) $result['permissions'], (int) $result['roles']));

        return self::SUCCESS;
    }
}
