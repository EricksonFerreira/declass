<?php

namespace App\Actions\Veiculo;

use App\Services\BreadcrumbService;
use App\Services\EstacaoService;
use App\Services\GrupoFrotaService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\SegmentoService;
use App\Services\StatusVeiculoService;
use App\Services\TipoFrotaService;
use App\Services\VeiculoService;

class EditVeiculoAction
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService,
        private readonly TipoFrotaService $tipoFrotaService,
        private readonly GrupoFrotaService $grupoFrotaService,
        private readonly MarcaService $marcaService,
        private readonly ModeloService $modeloService,
        private readonly SegmentoService $segmentoService,
        private readonly StatusVeiculoService $statusVeiculoService,
        private readonly EstacaoService $estacaoService,
        private readonly VeiculoService $veiculoService,
    ) {}

    public function handle(object $controller, $id): array
    {
        $veiculo = $this->veiculoService->getById($id);
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Veiculos', 'veiculo.index', "Editar: $veiculo->nome");
        [$metodo, $rota] = $controller->getFormMethodAndRoute($veiculo);

        $tipoFrotas = $this->tipoFrotaService->getAllAtivos();
        $grupoFrotas = $this->grupoFrotaService->getAllAtivos();
        $marcas = $this->marcaService->getAllAtivos();
        $modelosDetalhados = $this->modeloService->getModel()->newQuery()->where('ativo', 'S')->orderBy('nome')->get(['id', 'nome', 'ano_modelo']);
        $modelos = $modelosDetalhados->pluck('nome', 'id')->all();
        $modelosAno = $modelosDetalhados->pluck('ano_modelo', 'id')->all();
        $segmentos = $this->segmentoService->getAllAtivos();
        $statusVeiculos = $this->statusVeiculoService->getAllAtivos();
        $estacoes = $this->estacaoService->getAllAtivos();
        $defaultStatusId = $veiculo->status_veiculo_id;

        return compact('veiculo','tipoFrotas','grupoFrotas','marcas','estacoes','modelos','modelosAno','segmentos','statusVeiculos','defaultStatusId','breadcrumb','status','metodo','rota') + ['titulo' => $controller->getTitulo()];
    }
}
