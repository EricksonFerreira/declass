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
        $filtros = $request->only(['escola_id', 'ano_letivo_id', 'etapa_id', 'turma_id', 'turno', 'situacao']);

        // Padrao: ano letivo em curso quando o usuario nao filtrou nada.
        if (empty($filtros['ano_letivo_id'])) {
            $filtros['ano_letivo_id'] = AnoLetivo::query()->where('status', 'letivo')->orderByDesc('ano')->value('id');
        }

        // O service usa DB::table (sem escopo global): com escola ativa no
        // contexto, forca o filtro para a unidade selecionada.
        $escolaContexto = app(EscolaContext::class);
        if ($escolaContexto->filtrar()) {
            $filtros['escola_id'] = $escolaContexto->escolaId();
        }

        $dados = $this->relatorioService->porTurma($filtros);

        // Situacao para decisao: em risco (<60% aprovacao) vs boas (>=80%)
        $situacao = $filtros['situacao'] ?? null;
        if ($situacao) {
            $dados['linhas'] = $dados['linhas']->filter(function ($l) use ($situacao) {
                $pct = $l['pct_aprovacao'];
                if ($situacao === 'risco') {
                    return $pct !== null && $pct < 60;
                }

                return $pct !== null && $pct >= 80;
            })->values();
        }

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
            'turnos' => \App\Models\Turma::query()->whereNotNull('turno')->distinct()->orderBy('turno')->pluck('turno')->filter()->values(),
            'turmas' => Turma::query()->orderBy('nome')->get(['id', 'nome']),
            'notaAprovacao' => RelatorioAproveitamentoService::NOTA_APROVACAO,
        ]);
    }
}
