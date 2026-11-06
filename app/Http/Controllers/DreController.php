<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\DreService;
use Illuminate\Http\Request;

class DreController extends BaseController
{
    public function __construct(
        protected DreService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'DRE — Demonstrativo de Resultado';

        $this->middleware('can:dre.index')->only('index');
    }

    public function index(Request $request)
    {
        $ano = (int) $request->query('ano', now()->year);
        $escolaId = $request->query('escola_id') ? (int) $request->query('escola_id') : null;
        $empresaId = (int) $request->user()->empresa_id;

        $dados = $this->service->demonstrativo($ano, $escolaId, $empresaId);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('DRE', 'Financeiro');

        return view('dre.index', [
            'titulo' => $this->titulo,
            'ano' => $ano,
            'escolaId' => $escolaId,
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id'),
            'anos' => range(now()->year - 3, now()->year + 1),
            'categorias' => Despesa::CATEGORIAS,
            'dados' => $dados,
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
