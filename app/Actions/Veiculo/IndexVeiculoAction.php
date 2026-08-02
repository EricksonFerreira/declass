<?php

namespace App\Actions\Veiculo;

use App\Models\Movimentacao;
use App\Services\BreadcrumbService;
use App\Services\DisponibilidadeVeiculoService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;

class IndexVeiculoAction
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        private readonly VeiculoService $veiculoService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly DisponibilidadeVeiculoService $disponibilidadeVeiculoService,
    ) {}

    public function handle(object $controller): array
    {
        $controller->configurarSessao();

        $nomeFiltro = $controller->getNomeFiltro();
        $itensFiltered = session("$nomeFiltro.filtro", []);
        $perPage = session()->get("$nomeFiltro.perPage", 10);
        $orderBy = session()->get("$nomeFiltro.orderBy", 'id');
        $orderDir = session()->get("$nomeFiltro.orderDir", 'asc');

        $allowedOrderBy = ['id', 'nome', 'placa_licenca', 'renavam', 'ano_fabricacao', 'kms', 'created_at'];

        if (! in_array($orderBy, $allowedOrderBy, true)) {
            $orderBy = 'id';
        }

        $veiculos = $this->veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage, [], $orderBy, $orderDir);

        $dispAnoIndex = (int) request()->query('disp_ano', date('Y'));
        $dispMesIndex = (int) request()->query('disp_mes', date('n'));

        if ($dispMesIndex < 1 || $dispMesIndex > 12) {
            $dispMesIndex = (int) date('n');
        }

        if ($dispAnoIndex < 2024 || $dispAnoIndex > ((int) date('Y') + 1)) {
            $dispAnoIndex = (int) date('Y');
        }

        $veiculosCollection = method_exists($veiculos, 'getCollection') ? $veiculos->getCollection() : collect($veiculos);

        $resultadoDisponibilidadeIndex = $this->disponibilidadeVeiculoService->montarGradeMensal(
            $veiculosCollection,
            $dispAnoIndex,
            $dispMesIndex
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Veiculos');
        $filters = $controller->obterFiltrosPrincipais();
        $additionalFilters = $controller->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return compact(
            'veiculos',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPage',
            'orderBy',
            'orderDir'
        ) + [
            'perPages' => [5, 10, 15, 25, 50, 100],
            'resumoVeiculosIndex' => $resultadoDisponibilidadeIndex['resumoVeiculos'] ?? [],
            'gradeVeiculosIndex' => $resultadoDisponibilidadeIndex['grade'] ?? [],
            'dispAnoIndex' => $dispAnoIndex,
            'dispMesIndex' => $dispMesIndex,
        ];
    }
}
