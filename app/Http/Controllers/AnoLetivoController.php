<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\AnoLetivo\StoreAnoLetivoRequest;
use App\Http\Requests\AnoLetivo\UpdateAnoLetivoRequest;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Services\AnoLetivoService;
use App\Services\BreadcrumbService;
use App\Services\PeriodoLetivoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class AnoLetivoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected AnoLetivoService $anoLetivoService,
        protected PeriodoLetivoService $periodoLetivoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Anos Letivos';
        $this->nomeFiltro = 'ano_letivo';

        $this->middleware('can:ano_letivo.index')->only('index');
        $this->middleware('can:ano_letivo.show')->only('show');
        $this->middleware('can:ano_letivo.create')->only('create');
        $this->middleware('can:ano_letivo.store')->only('store');
        $this->middleware('can:ano_letivo.edit')->only('edit');
        $this->middleware('can:ano_letivo.update')->only(['update', 'gerarPeriodos']);
        $this->middleware('can:ano_letivo.destroy')->only('destroy');
        $this->middleware('can:ano_letivo.filtro')->only('filtro');
        $this->middleware('can:ano_letivo.removerFiltro')->only('removerFiltro');
        $this->middleware('can:ano_letivo.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $anosLetivos = $this->anoLetivoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola', 'periodos', 'turmas']
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Anos Letivos', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('ano_letivo.index', compact(
            'anosLetivos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Anos Letivos', 'ano_letivo.index', 'Novo Ano Letivo', 'Vida Acadêmica');

        return view('ano_letivo.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreAnoLetivoRequest $request)
    {
        $anoLetivo = $this->anoLetivoService->create($request->validated());

        $periodos = $this->periodoLetivoService->gerarAutomaticos($anoLetivo);

        $mensagem = 'Ano letivo criado com sucesso.';
        if ($periodos > 0) {
            $mensagem .= " {$periodos} períodos letivos gerados automaticamente.";
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro, $mensagem);
    }

    /**
     * Gera os periodos letivos de um ano existente que ainda nao
     * possui nenhum (ex.: criado antes da periodicidade ou com
     * "personalizado"). Nao duplica nem sobrescreve periodos.
     */
    public function gerarPeriodos($id)
    {
        $anoLetivo = $this->anoLetivoService->getById($id);
        abort_if(! $anoLetivo, 404, 'Ano letivo não encontrado.');

        $periodos = $this->periodoLetivoService->gerarAutomaticos($anoLetivo);

        if ($periodos === 0) {
            return redirect()->route('ano_letivo.edit', $anoLetivo->id)
                ->with('error', 'Nenhum período gerado: o ano já possui períodos ou a periodicidade é personalizada.');
        }

        return redirect()->route('ano_letivo.edit', $anoLetivo->id)
            ->with('success', "{$periodos} períodos letivos gerados com sucesso.");
    }

    /**
     * Planejador do ano letivo: periodos, turmas e a matriz de
     * disciplinas com replicacao entre turmas irmas (A, B, C...).
     */
    public function show($id)
    {
        $anoLetivo = $this->anoLetivoService->getById($id, [
            'escola', 'periodos',
        ]);
        abort_if(! $anoLetivo, 404, 'Ano letivo não encontrado.');

        $turmas = \App\Models\Turma::query()
            ->where('ano_letivo_id', $anoLetivo->id)
            ->with([
                'etapa',
                'disciplinas:id,nome',
                'atribuicoes.user:id,nome',
                'atribuicoes.disciplina:id,nome',
            ])
            ->withCount('matriculas')
            ->orderBy('nome')
            ->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Anos Letivos', 'ano_letivo.index', (string) $anoLetivo->ano, 'Vida Acadêmica'
        );

        $user = auth()->user();

        return view('ano_letivo.show', [
            'anoLetivo' => $anoLetivo,
            'turmas' => $turmas,
            'breadcrumb' => $breadcrumb,
            'etapasOpcoes' => \App\Models\Etapa::query()->where('ativo', 'S')
                ->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'turnosOpcoes' => \App\Models\Turma::TURNOS,
            'nomesExistentes' => $turmas->pluck('nome')->map(fn ($n) => mb_strtolower($n))->all(),
            'podeGerar' => $user->hasPermissao('turma.gerar_lote'),
            'podeReplicar' => $user->hasPermissao('turma.replicar_organizacao'),
        ])->with('titulo', "Ano letivo {$anoLetivo->ano}");
    }

    public function edit($id)
    {
        $anoLetivo = $this->anoLetivoService->getById($id);
        abort_if(! $anoLetivo, 404, 'Ano letivo não encontrado.');

        $totalPeriodos = $anoLetivo->periodos()->count();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Anos Letivos', 'ano_letivo.index', "Editar: {$anoLetivo->ano}", 'Vida Acadêmica');

        return view('ano_letivo.add-edit', array_merge(compact('anoLetivo', 'breadcrumb', 'totalPeriodos'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateAnoLetivoRequest $request, $id)
    {
        $anoLetivo = $this->anoLetivoService->update($id, $request->validated());
        abort_if(! $anoLetivo, 403, 'Sem acesso para editar este ano letivo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Ano letivo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->anoLetivoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este ano letivo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'ano-letivo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'ano_letivo.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'ano', 'label' => 'Ano', 'type' => 'number', 'typeWhere' => 'where'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $e) => ['value' => $e->id, 'label' => $e->nome])->all(),
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => AnoLetivo::STATUS,
            ],
            [
                'name' => 'ativo', 'label' => 'Situação', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'status' => AnoLetivo::STATUS,
            'periodicidades' => AnoLetivo::PERIODICIDADES,
        ];
    }
}
