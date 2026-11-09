<?php

namespace App\Http\Controllers;

use App\Services\BreadcrumbService;
use App\Services\PainelRedeService;
use Illuminate\Http\Request;

class RedeController extends BaseController
{
    public function __construct(
        protected PainelRedeService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:acessar-superadmin')->only('index');
    }

    /**
     * Painel da rede (superadmin): KPIs consolidados e uma linha por
     * empresa com escolas, alunos, matriculas, turmas e equipe.
     */
    public function index(Request $request)
    {
        $ano = $request->filled('ano') ? (int) $request->input('ano') : null;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Painel da Rede', 'Configurações');

        return view('rede.index', [
            'titulo' => 'Painel da Rede',
            'breadcrumb' => $breadcrumb,
            'dados' => $this->service->dados($ano),
            'ano' => $ano,
            'anos' => $this->service->anosDisponiveis(),
        ]);
    }
}
