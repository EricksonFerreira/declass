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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Matrículas', 'matricula.index', 'Nova Matrícula', 'Vida Acadêmica');

        return view('matricula.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
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
     * Rematricula em lote: passo 1 escolhe ano origem/destino,
     * passo 2 lista alunos elegiveis com a turma de destino.
     */
    public function rematricula(Request $request)
    {
        $empresaId = (int) $request->user()->empresa_id;
        $anoOrigemId = (int) $request->query('ano_origem', 0);
        $anoDestinoId = (int) $request->query('ano_destino', 0);

        $anosLetivos = \App\Models\AnoLetivo::query()
            ->where('empresa_id', $empresaId)
            ->with('escola:id,nome')
            ->orderByDesc('ano')
            ->get(['id', 'escola_id', 'ano']);

        $dados = null;
        $erroAnos = null;

        if ($anoOrigemId && $anoDestinoId) {
            try {
                [$anoOrigem, $anoDestino] = $this->rematriculaService->validarAnos($anoOrigemId, $anoDestinoId, $empresaId);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $erroAnos = collect($e->errors())->flatten()->first();
                $anoOrigem = $anoDestino = null;
            }

            if ($anoOrigem && $anoDestino) {
                $elegiveis = $this->rematriculaService->matriculasElegiveis($anoOrigemId, $anoDestinoId, $empresaId);
                $turmasDestino = $this->rematriculaService->turmasDestino(
                    $anoDestinoId, (int) $anoOrigem->escola_id, $empresaId
                );

                $dados = [
                    'anoOrigem' => $anoOrigem,
                    'anoDestino' => $anoDestino,
                    'elegiveis' => $elegiveis,
                    'turmasDestino' => $turmasDestino,
                ];
            }
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Rematrícula em lote', 'Vida Acadêmica');

        return view('matricula.rematricula', compact(
            'anosLetivos', 'anoOrigemId', 'anoDestinoId', 'dados', 'erroAnos', 'breadcrumb'
        ))->with('titulo', 'Rematrícula em lote');
    }

    public function rematricular(Request $request)
    {
        $request->validate([
            'ano_origem' => ['required', 'integer'],
            'ano_destino' => ['required', 'integer'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*' => ['integer'],
        ]);

        $empresaId = (int) $request->user()->empresa_id;
        $anoDestinoId = (int) $request->input('ano_destino');

        // garante que os anos sao validos antes de gravar
        $this->rematriculaService->validarAnos(
            (int) $request->input('ano_origem'), $anoDestinoId, $empresaId
        );

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
                'ano_origem' => $request->input('ano_origem'),
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
        return [
            'alunos' => $this->alunosOpcoes(),
            'turmas' => $this->turmasOpcoes(),
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
