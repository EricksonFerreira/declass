<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Services\ReplicacaoBaseService;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ReplicacaoDiaria extends Command
{
    protected $signature = 'replicacao:diaria {--origem=} {--destinos=} {--modulos=}';

    protected $description = 'Executa replicacao de base usando configuracao de horarios/parametros (usado no agendamento)';

    private ReplicacaoBaseService $service;

    public function __construct(ReplicacaoBaseService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $origemOption = $this->option('origem');
        $destinosOption = $this->option('destinos');
        $modulosOption = $this->option('modulos');

        // Origem: opcao -> config -> template slug
        $origemId = null;
        if (!empty((string) $origemOption)) {
            $origemId = (int) $origemOption;
        } elseif (config('replicacao.empresa_origem_id')) {
            $origemId = (int) config('replicacao.empresa_origem_id');
        } else {
            $template = Empresa::query()->where('slug', Empresa::DEFAULT_TEMPLATE_SLUG)->first();
            if ($template) {
                $origemId = (int) $template->id;
            }
        }

        if ($origemId === null) {
            $this->error('Empresa origem não definida. Use --origem ou defina REPLICACAO_EMPRESA_ORIGEM_ID.');
            return 1;
        }

        $destinos = [];
        if (!empty((string) $destinosOption)) {
            $destinos = array_values(array_filter(array_map('intval', array_map('trim', explode(',', (string) $destinosOption)))));
        } elseif (!empty(config('replicacao.empresas_destino_ids'))) {
            $destinos = (array) config('replicacao.empresas_destino_ids');
        }

        if (empty($destinos)) {
            $this->error('Nenhuma empresa destino configurada. Passe --destinos ou defina REPLICACAO_EMPRESAS_DESTINO.');
            return 1;
        }

        $modulos = [];
        if (!empty((string) $modulosOption)) {
            $modulos = array_values(array_filter(array_map('trim', explode(',', (string) $modulosOption))));
        } elseif (!empty(config('replicacao.modulos'))) {
            $modulos = (array) config('replicacao.modulos');
        } else {
            $modulos = array_keys($this->service->modulosDisponiveis());
        }

        if (in_array($origemId, $destinos, true)) {
            $this->error('Empresa origem não pode estar na lista de destinos.');
            return 1;
        }

        try {
            $this->info(sprintf('Iniciando replicacao: origem=%d destinos=%s modulos=%s', $origemId, implode(',', $destinos), implode(',', $modulos)));
            $resultado = $this->service->replicar($origemId, $destinos, $modulos);
            $this->info('Replicacao finalizada. Resultado:');
            foreach ($resultado as $mod) {
                $this->line(sprintf('- %s (%s): inseridos=%d ignorados=%d', $mod['modulo'], $mod['tabela'] ?? '', $mod['inseridos'], $mod['ignorados']));
            }
        } catch (InvalidArgumentException $e) {
            $this->error('Erro na replicacao: ' . $e->getMessage());
            return 1;
        } catch (\Throwable $e) {
            $this->error('Erro inesperado: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
