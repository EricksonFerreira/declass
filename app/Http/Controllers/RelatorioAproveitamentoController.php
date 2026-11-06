<?php

namespace App\Http\Controllers;

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\RelatorioAproveitamentoService;
use App\Support\EscolaContext;
use Illuminate\Http\Request;

class RelatorioAproveitamentoController extends BaseController
{
    public function __construct(
        protected RelatorioAproveitamentoService $relatorioService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:relatorio_aproveitamento.index')->only('index');
    }

    /**
     * Aproveitamento academico consolidado por turma:
     * alunos, media geral, aprovados (>= 7) e cobertura.
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['escola_id', 'ano_letivo_id', 'etapa_id', 'turma_id']);

        // O service usa DB::table (sem escopo global): com escola ativa no
        // contexto, forca o filtro para a unidade selecionada.
        $escolaContexto = app(EscolaContext::class);
        if ($escolaContexto->filtrar()) {
            $filtros['escola_id'] = $escolaContexto->escolaId();
        }

        $dados = $this->relatorioService->porTurma($filtros);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Relatório de Aproveitamento', 'Relatórios');

        return view('relatorio_aproveitamento.index', [
            'titulo' => 'Relatório de Aproveitamento',
            'breadcrumb' => $breadcrumb,
            'linhas' => $dados['linhas'],
            'resumo' => $dados['resumo'],
            'filtros' => $filtros,
            'escolas' => Escola::query()->orderBy('nome')->get(['id', 'nome']),
            'anosLetivos' => AnoLetivo::query()->orderBy('ano', 'desc')->get(['id', 'ano']),
            'etapas' => Etapa::query()->orderBy('nome')->get(['id', 'nome']),
            'turmas' => Turma::query()->orderBy('nome')->get(['id', 'nome']),
            'notaAprovacao' => RelatorioAproveitamentoService::NOTA_APROVACAO,
        ]);
    }
}
