<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\FluxoCaixaService;
use App\Support\EscolaContext;
use Illuminate\Http\Request;

class FluxoCaixaController extends BaseController
{
    public function __construct(
        protected FluxoCaixaService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Fluxo de Caixa';

        $this->middleware('can:fluxo_caixa.index')->only('index');
    }

    public function index(Request $request)
    {
        $ano = (int) $request->query('ano', now()->year);
        // Com escola no contexto, o escopo global ja restringe — nao
        // aplica um escola_id divergente da query string.
        $escolaId = app(EscolaContext::class)->filtrar()
            ? null
            : ($request->query('escola_id') ? (int) $request->query('escola_id') : null);
        $empresaId = (int) $request->user()->empresa_id;

        $dados = $this->service->anual($ano, $escolaId, $empresaId);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Fluxo de Caixa', 'Financeiro');

        return view('fluxo_caixa.index', [
            'titulo' => $this->titulo,
            'ano' => $ano,
            'escolaId' => $escolaId,
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id'),
            'anos' => range(now()->year - 2, now()->year + 1),
            'meses' => $dados['meses'],
            'totais' => $dados['totais'],
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
