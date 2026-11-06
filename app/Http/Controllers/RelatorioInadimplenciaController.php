<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Models\Mensalidade;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\MensalidadeService;
use App\Support\EscolaContext;
use Illuminate\Http\Request;

class RelatorioInadimplenciaController extends BaseController
{
    public function __construct(
        protected MensalidadeService $mensalidadeService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:relatorio_inadimplencia.index')->only('index');
    }

    /**
     * Inadimplencia: mensalidades por situacao (vencida/pendente/
     * paga/cancelada) com filtros de escola, turma, competencia e
     * situacao. Dividas mais antigas primeiro.
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['escola_id', 'turma_id', 'competencia', 'situacao']);

        if (app(EscolaContext::class)->filtrar()) {
            unset($filtros['escola_id']);
        }

        $mensalidades = $this->mensalidadeService->relatorioInadimplencia($filtros, 25);
        $resumo = $this->mensalidadeService->resumoInadimplencia($filtros);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Relatório de Inadimplência', 'Relatórios');

        return view('relatorio_inadimplencia.index', [
            'titulo' => 'Relatório de Inadimplência',
            'breadcrumb' => $breadcrumb,
            'mensalidades' => $mensalidades,
            'resumo' => $resumo,
            'filtros' => $filtros,
            'escolas' => Escola::query()->orderBy('nome')->get(['id', 'nome']),
            'turmas' => Turma::query()->with('escola:id,nome')->orderBy('nome')->get(['id', 'nome', 'escola_id']),
            'competencias' => $this->competenciasDisponiveis(),
        ]);
    }

    /** Ultimas competencias (YYYY-MM) distintas, para o select. */
    private function competenciasDisponiveis()
    {
        return Mensalidade::query()
            ->select('competencia')
            ->distinct()
            ->orderByDesc('competencia')
            ->limit(24)
            ->pluck('competencia');
    }
}
