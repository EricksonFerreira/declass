<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Services\FipeCatalogService;
use App\Support\EmpresaContext;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Schema;

class SincronizarCatalogoFipeCommand extends Command
{
    protected $signature = 'fipe:sincronizar-catalogo
        {--vehicle-type=cars : Tipo de veiculo na FIPE}
        {--brand= : Filtro opcional por codigo ou nome da marca, separado por virgula}
        {--refresh-existing : Atualiza nomes locais com o nome FIPE}
        {--dry-run : Simula a sincronizacao sem persistir}
    ';

    protected $description = 'Sincroniza marcas e modelos da FIPE nas tabelas locais';

    public function __construct(
        private readonly FipeCatalogService $fipeCatalogService,
        private readonly EmpresaContext $empresaContext
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $vehicleType = (string) $this->option('vehicle-type');
        $brandOption = trim((string) $this->option('brand'));
        $brandFilters = $brandOption !== '' ? array_filter(array_map('trim', explode(',', $brandOption))) : [];
        $refreshExisting = (bool) $this->option('refresh-existing');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Sincronizando catalogo FIPE...');
        $this->line('Tipo: ' . $vehicleType);

        if (!empty($brandFilters)) {
            $this->line('Filtro de marcas: ' . implode(', ', $brandFilters));
        }

        if ($refreshExisting) {
            $this->line('Modo refresh: habilitado');
        }

        if ($dryRun) {
            $this->warn('Dry-run habilitado: nenhuma escrita sera feita.');
        }

        if (!SchemaCache::hasTable('empresas')) {
            $this->error('Tabela de empresas nao encontrada.');
            return self::FAILURE;
        }

        $empresaAnterior = $this->empresaContext->empresaId();
        $empresasDestino = $this->obterEmpresasDestino();

        if (SchemaCache::hasColumn('marcas', 'empresa_id') && !Empresa::query()->where('id', 1)->exists()) {
            $this->error('A empresa padrao com id 1 nao existe. Nao foi possivel sincronizar o catalogo FIPE.');
            return self::FAILURE;
        }

        $totais = [
            'companies' => 0,
            'brands_api' => 0,
            'brands_created' => 0,
            'brands_updated' => 0,
            'models_api' => 0,
            'models_created' => 0,
            'models_updated' => 0,
            'models_skipped' => 0,
        ];

        try {
            foreach ($empresasDestino as $empresaId) {
                $this->empresaContext->setEmpresaId($empresaId);

                $stats = $this->fipeCatalogService->syncCatalog($vehicleType, $brandFilters, $refreshExisting, $dryRun);
                $totais['companies']++;
                $totais['brands_api'] += (int) $stats['brands_api'];
                $totais['brands_created'] += (int) $stats['brands_created'];
                $totais['brands_updated'] += (int) $stats['brands_updated'];
                $totais['models_api'] += (int) $stats['models_api'];
                $totais['models_created'] += (int) $stats['models_created'];
                $totais['models_updated'] += (int) $stats['models_updated'];
                $totais['models_skipped'] += (int) $stats['models_skipped'];
            }
        } catch (ConnectionException $exception) {
            $this->error('Nao foi possivel conectar na API FIPE: ' . $exception->getMessage());

            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error('Falha ao sincronizar catalogo FIPE: ' . $exception->getMessage());

            return self::FAILURE;
        } finally {
            $this->empresaContext->setEmpresaId($empresaAnterior);
        }

        $this->info('Sincronizacao concluida.');
        $this->line('Empresas processadas: ' . $totais['companies']);
        $this->line(sprintf(
            'Marcas API: %d | Criadas: %d | Atualizadas: %d',
            $totais['brands_api'],
            $totais['brands_created'],
            $totais['brands_updated']
        ));
        $this->line(sprintf(
            'Modelos API: %d | Criados: %d | Atualizados: %d | Ignorados: %d',
            $totais['models_api'],
            $totais['models_created'],
            $totais['models_updated'],
            $totais['models_skipped']
        ));

        if ($dryRun) {
            $this->warn('Dry-run concluido: revise os totais acima antes de executar sem --dry-run.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function obterEmpresasDestino(): array
    {
        $targetIds = [1];

        if (
            SchemaCache::hasColumn('empresas', 'adicionar_novas_marcas_automaticamente')
            && SchemaCache::hasColumn('empresas', 'adicionar_novos_modelos_automaticamente')
        ) {
            $targetIds = array_merge(
                $targetIds,
                Empresa::query()
                    ->where('ativo', 'S')
                    ->where('adicionar_novas_marcas_automaticamente', 'S')
                    ->where('adicionar_novos_modelos_automaticamente', 'S')
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            );
        }

        return array_values(array_unique($targetIds));
    }
}
