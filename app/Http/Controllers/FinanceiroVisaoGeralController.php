<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\FinanceiroVisaoGeralService;
use App\Support\EscolaContext;
use Illuminate\Http\Request;

class FinanceiroVisaoGeralController extends BaseController
{
    public function __construct(
        protected FinanceiroVisaoGeralService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:financeiro_visao_geral.index')->only('index');
    }

    /**
     * Visao geral do financeiro: KPIs, inadimplencia por turma e
     * receita prevista x realizada por mes. Filtros: escola e ano.
     */
    public function index(Request $request)
    {
        $ano = (int) ($request->input('ano') ?: date('Y'));
        $escolaId = app(EscolaContext::class)->filtrar()
            ? app(EscolaContext::class)->escolaId()
            : ($request->filled('escola_id') ? (int) $request->input('escola_id') : null);

        $dados = $this->service->dados($ano, $escolaId);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Visão Geral do Financeiro', 'Financeiro');

        return view('financeiro_visao_geral.index', [
            'titulo' => 'Visão Geral do Financeiro',
            'breadcrumb' => $breadcrumb,
            'dados' => $dados,
            'ano' => $ano,
            'anos' => $this->service->anosDisponiveis(),
            'escolas' => Escola::query()->orderBy('nome')->get(['id', 'nome']),
            'escolaId' => $escolaId,
            'escolaContextoFixo' => app(EscolaContext::class)->filtrar(),
        ]);
    }
}
