<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\RelatorioTransporteService;
use Illuminate\Http\Request;

class RelatorioTransporteController extends BaseController
{
    public function __construct(
        protected RelatorioTransporteService $relatorioService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:relatorio_transporte.index')->only('index');
    }

    /**
     * Ocupacao do transporte: rotas com veiculo, capacidade,
     * passageiros ativos e percentual de ocupacao.
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['escola_id', 'situacao', 'apenas_ativas']);
        $filtros['apenas_ativas'] = $request->boolean('apenas_ativas');

        $linhas = $this->relatorioService->relatorio($filtros);
        $resumo = $this->relatorioService->resumo($filtros);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Relatório de Transporte', 'Relatórios');

        return view('relatorio_transporte.index', [
            'titulo' => 'Relatório de Transporte',
            'breadcrumb' => $breadcrumb,
            'linhas' => $linhas,
            'resumo' => $resumo,
            'filtros' => $filtros,
            'escolas' => Escola::query()->orderBy('nome')->get(['id', 'nome']),
            'situacoes' => [
                'sem_veiculo' => 'Sem veículo',
                'sem_capacidade' => 'Sem capacidade',
                'superlotada' => 'Superlotada (>100%)',
                'lotada' => 'Lotada (≥90%)',
                'ok' => 'Com vagas',
            ],
        ]);
    }
}
