<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Matricula\StoreMatriculaRequest;
use App\Http\Requests\Matricula\UpdateMatriculaRequest;
use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\MatriculaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class MatriculaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected MatriculaService $matriculaService,
        protected BreadcrumbService $breadcrumbService,
        protected \App\Services\RematriculaService $rematriculaService,
    ) {
        $this->titulo = 'Matrículas';
        $this->nomeFiltro = 'matricula';

        $this->middleware('can:matricula.index')->only('index');
        $this->middleware('can:matricula.create')->only('create');
        $this->middleware('can:matricula.store')->only('store');
        $this->middleware('can:matricula.edit')->only('edit');
        $this->middleware('can:matricula.update')->only('update');
        $this->middleware('can:matricula.destroy')->only('destroy');
        $this->middleware('can:matricula.rematricula')->only('rematricula');
        $this->middleware('can:matricula.rematricular')->only('rematricular');
        $this->middleware('can:matricula.filtro')->only('filtro');
        $this->middleware('can:matricula.removerFiltro')->only('removerFiltro');
        $this->middleware('can:matricula.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $matriculas = $this->matriculaService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Matrículas', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('matricula.index', compact(
            'matriculas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $turmaRota = request()->route('turma');
        $turma = $turmaRota ? Turma::find($turmaRota) : null;

        $breadcrumb = $turma
            ? $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Turmas', route('turma.index')],
                [$turma->nome, route('turma.show', $turma->id)],
                ['Nova Matrícula', null],
            ])
            : $this->breadcrumbService->prepararBreadcrumbCreate('Matrículas', 'matricula.index', 'Nova Matrícula', 'Vida Acadêmica');

        return view('matricula.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario(), [
            'turmaPreId' => (int) request()->query('turma_id', 0) ?: $turma?->id,
        ]))->with('titulo', $this->titulo);
    }

    public function store(StoreMatriculaRequest $request)
    {
        $this->matriculaService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Matrícula criada com sucesso.');
    }

    public function edit($id)
    {
        $matricula = $this->matriculaService->getById($id, ['aluno.pessoa', 'turma.anoLetivo']);
        abort_if(! $matricula, 404, 'Matrícula não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Matrículas', 'matricula.index', 'Editar Matrícula', 'Vida Acadêmica');

        return view('matricula.add-edit', array_merge(compact('matricula', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateMatriculaRequest $request, $id)
    {
        $matricula = $this->matriculaService->update($id, $request->validated());
        abort_if(! $matricula, 403, 'Sem acesso para editar esta matrícula.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Matrícula atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->matriculaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta matrícula.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /**
     * Rematricula em lote: passo 1 escolhe escola/ano/turma de origem
     * e escola/ano de destino em cascata; passo 2 lista os alunos
     * elegiveis da turma com a turma de destino por aluno.
     */
    public function rematricula(Request $request)
    {
        $empresaId = (int) $request->user()->empresa_id;
        $turmaOrigemId = (int) $request->query('turma_origem', 0);
        $anoDestinoId = (int) $request->query('ano_destino', 0);

        $selecao = [
            'escola_origem' => (int) $request->query('escola_origem', 0),
            'ano_origem' => (int) $request->query('ano_origem', 0),
            'turma_origem' => $turmaOrigemId,
            'escola_destino' => (int) $request->query('escola_destino', 0),
            'ano_destino' => $anoDestinoId,
        ];

        $escolas = \App\Models\Escola::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $anosLetivos = \App\Models\AnoLetivo::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('ano')
            ->get(['id', 'escola_id', 'ano', 'status'])
            ->map(fn ($a) => [
                'id' => $a->id,
                'escola_id' => (int) $a->escola_id,
                'rotulo' => $a->ano,
                'status' => $a->status ?: 'letivo',
            ])->values();

        $turmas = $this->rematriculaService->turmasComOcupacao($empresaId)
            ->map(fn ($t) => [
                'id' => $t->id,
                'ano_letivo_id' => (int) $t->ano_letivo_id,
                'rotulo' => $t->nome
                    .($t->etapa?->nome ? ' ('.$t->etapa->nome.')' : '')
                    .' — '.$t->alunos_ativos_count.($t->capacidade_maxima ? '/'.$t->capacidade_maxima : '').' aluno(s)',
            ])->values();

        $dados = null;
        $erroSelecao = null;

        if ($turmaOrigemId && $anoDestinoId) {
            try {
                [$turmaOrigem, $anoDestino] = $this->rematriculaService->validarSelecao($turmaOrigemId, $anoDestinoId, $empresaId);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $erroSelecao = collect($e->errors())->flatten()->first();
                $turmaOrigem = $anoDestino = null;
            }

            if ($turmaOrigem && $anoDestino) {
                $dados = [
                    'turmaOrigem' => $turmaOrigem,
                    'anoDestino' => $anoDestino,
                    'elegiveis' => $this->rematriculaService->matriculasElegiveis($turmaOrigemId, $anoDestinoId, $empresaId),
                    'turmasDestino' => $this->rematriculaService->turmasDestino($anoDestinoId, $empresaId),
                ];
            }
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Rematrícula em lote', 'Vida Acadêmica');

        return view('matricula.rematricula', compact(
            'escolas', 'anosLetivos', 'turmas', 'selecao',
            'dados', 'erroSelecao', 'breadcrumb'
        ))->with('titulo', 'Rematrícula em lote');
    }

    public function rematricular(Request $request)
    {
        $request->validate([
            'turma_origem' => ['required', 'integer'],
            'ano_destino' => ['required', 'integer'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*' => ['integer'],
            'escola_origem' => ['nullable', 'integer'],
            'ano_origem' => ['nullable', 'integer'],
            'escola_destino' => ['nullable', 'integer'],
        ]);

        $empresaId = (int) $request->user()->empresa_id;
        $turmaOrigemId = (int) $request->input('turma_origem');
        $anoDestinoId = (int) $request->input('ano_destino');

        // garante que turma de origem e ano de destino sao validos antes de gravar
        $this->rematriculaService->validarSelecao($turmaOrigemId, $anoDestinoId, $empresaId);

        $resultado = $this->rematriculaService->rematricular(
            $request->input('itens'), $anoDestinoId, $empresaId
        );

        $mensagem = "Rematrícula concluída: {$resultado['criadas']} matrícula(s) criada(s)";
        if ($resultado['ignoradas'] !== []) {
            $mensagem .= ', '.count($resultado['ignoradas']).' ignorada(s)';
        }
        $mensagem .= '.';

        return redirect()
            ->route('matricula.rematricula', [
                'escola_origem' => $request->input('escola_origem'),
                'ano_origem' => $request->input('ano_origem'),
                'turma_origem' => $turmaOrigemId,
                'escola_destino' => $request->input('escola_destino'),
                'ano_destino' => $anoDestinoId,
            ])
            ->with('success', $mensagem)
            ->with('ignoradas', $resultado['ignoradas']);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'matricula');
        session()->put("$this->nomeFiltro.rotaRedirect", 'matricula.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Aluno', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Matricula::STATUS,
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->turmasOpcoes(),
            ],
            [
                'name' => 'ano_letivo_id', 'label' => 'Ano letivo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->anosLetivosOpcoes(),
            ],
            [
                'name' => 'numero_chamada', 'label' => 'Nº chamada', 'type' => 'text',
                'typeWhere' => 'where',
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        $alunos = Aluno::query()->with(['pessoa', 'escola'])->orderBy('id')->get();
        $turmas = Turma::query()->with(['escola', 'anoLetivo', 'etapa'])->orderBy('nome')->get();

        return [
            'alunos' => $alunos->mapWithKeys(fn (Aluno $a) => [
                $a->id => $a->pessoa?->nome ?? ('Aluno #'.$a->id),
            ])->all(),
            'turmas' => $turmas->mapWithKeys(fn (Turma $t) => [
                $t->id => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
            ])->all(),
            // Dados estruturados para o modal de busca (picker) do formulario.
            'alunosPicker' => $alunos->map(fn (Aluno $a) => [
                'id' => $a->id,
                'rotulo' => $a->pessoa?->nome ?? ('Aluno #'.$a->id),
                'detalhe' => trim(($a->registro_academico ? 'RA '.$a->registro_academico.' · ' : '').($a->escola->nome ?? ''), ' ·'),
                'escola_id' => $a->escola_id,
                'escola' => $a->escola->nome ?? '',
            ])->values()->all(),
            'turmasPicker' => $turmas->map(fn (Turma $t) => [
                'id' => $t->id,
                'rotulo' => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
                'nome' => $t->nome,
                'detalhe' => trim(($t->etapa->nome ?? '').' · '.($t->anoLetivo->ano ?? '').' · '.($t->escola->nome ?? ''), ' ·'),
                'escola_id' => $t->escola_id,
                'escola' => $t->escola->nome ?? '',
                'ano_letivo_id' => $t->ano_letivo_id,
                'ano' => $t->anoLetivo?->ano,
            ])->values()->all(),
            'status' => Matricula::STATUS,
            'planos' => \App\Models\PlanoMensalidade::query()
                ->where('ativo', 'S')
                ->orderBy('nome')
                ->pluck('nome', 'id')
                ->all(),
        ];
    }

    private function alunosOpcoes(): array
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->get()
            ->mapWithKeys(fn (Aluno $a) => [
                $a->id => $a->pessoa?->nome ?? ('Aluno #'.$a->id),
            ])->all();
    }

    private function turmasOpcoes(): array
    {
        return Turma::query()->with(['escola', 'anoLetivo', 'etapa'])->orderBy('nome')->get()
            ->mapWithKeys(fn (Turma $t) => [
                $t->id => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
            ])->all();
    }

    private function anosLetivosOpcoes(): array
    {
        return \App\Models\AnoLetivo::query()->with('escola')->orderBy('ano', 'desc')->get()
            ->mapWithKeys(fn (\App\Models\AnoLetivo $a) => [
                $a->id => $a->ano.' — '.($a->escola->nome ?? ''),
            ])->all();
    }
}
