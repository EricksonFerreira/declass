<?php

namespace App\Http\Controllers;

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\FrequenciaService;
use App\Support\EscolaContext;
use Illuminate\Http\Request;

class RelatorioFrequenciaController extends BaseController
{
    public function __construct(
        protected FrequenciaService $frequenciaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:relatorio_frequencia.index')->only('index');
    }

    /**
     * Frequencia consolidada: ranking de faltas por aluno com
     * filtros de escola, ano letivo, turma e "somente em risco".
     */
    public function index(Request $request)
    {
        $filtros = $request->only(['escola_id', 'ano_letivo_id', 'turma_id', 'apenas_risco']);
        $filtros['apenas_risco'] = $request->boolean('apenas_risco');

        if (app(EscolaContext::class)->filtrar()) {
            unset($filtros['escola_id']);
        }

        $matriculas = $this->frequenciaService->consolidadoFaltas($filtros, 25);
        $resumo = $this->frequenciaService->resumoFaltas($filtros);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Relatório de Frequência', 'Relatórios');

        return view('relatorio_frequencia.index', [
            'titulo' => 'Relatório de Frequência',
            'breadcrumb' => $breadcrumb,
            'matriculas' => $matriculas,
            'resumo' => $resumo,
            'filtros' => $filtros,
            'escolas' => Escola::query()->orderBy('nome')->get(['id', 'nome']),
            'anosLetivos' => AnoLetivo::query()->orderByDesc('ano')->get(['id', 'ano', 'escola_id']),
            'turmas' => Turma::query()->with('escola:id,nome')->orderBy('nome')->get(['id', 'nome', 'escola_id', 'ano_letivo_id']),
        ]);
    }
}
