<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Diario\SalvarChamadaRequest;
use App\Http\Requests\Diario\SalvarFrequenciaRequest;
use App\Http\Requests\Diario\StoreDiarioRequest;
use App\Http\Requests\Diario\UpdateDiarioRequest;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\DiarioService;
use App\Services\FrequenciaService;
use App\Services\TurmaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class DiarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected DiarioService $diarioService,
        protected FrequenciaService $frequenciaService,
        protected TurmaService $turmaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Diário de Classe';
        $this->nomeFiltro = 'diario';

        $this->middleware('can:diario.index')->only('index');
        $this->middleware('can:diario.create')->only(['create', 'disciplinasDaTurma']);
        $this->middleware('can:diario.store')->only('store');
        $this->middleware('can:diario.edit')->only('edit');
        $this->middleware('can:diario.update')->only('update');
        $this->middleware('can:diario.destroy')->only('destroy');
        $this->middleware('can:diario.frequencia')->only(['frequencia', 'salvarFrequencia']);
        $this->middleware('can:diario.frequencia')->only('chamada');
        $this->middleware('can:diario.frequencia.salvar')->only(['salvarFrequencia', 'salvarChamada']);
        $this->middleware('can:diario.filtro')->only('filtro');
        $this->middleware('can:diario.removerFiltro')->only('removerFiltro');
        $this->middleware('can:diario.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $diarios = $this->diarioService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Diário de Classe', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('diario.index', compact(
            'diarios', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Diário de Classe', 'diario.index', 'Registrar Aula', 'Vida Acadêmica');

        // Turma vinda do contexto (meus horarios / chamada da turma):
        // vem preenchida e travada; as disciplinas sao as dela.
        $turmaFixa = $this->turmaDoContexto();
        $opcoes = $this->opcoesFormulario();
        $opcoes['disciplinas'] = $turmaFixa
            ? $this->disciplinasDaTurmaOpcoes($turmaFixa)
            : [];

        return view('diario.add-edit', array_merge(compact('breadcrumb', 'turmaFixa'), $opcoes))
            ->with('titulo', $this->titulo);
    }

    /** JSON id=>nome das disciplinas visiveis ao usuario na turma. */
    public function disciplinasDaTurma(Request $request)
    {
        $turma = $this->turmaService->getById((int) $request->input('turma_id'), ['escola', 'anoLetivo']);
        abort_unless($turma, 404, 'Turma não encontrada.');
        abort_unless($this->turmaService->podeAcessar($turma), 403, 'Sem acesso a esta turma.');

        return response()->json($this->disciplinasDaTurmaOpcoes($turma));
    }

    /** Turma do parametro turma_id quando acessivel ao usuario (contexto de origem). */
    private function turmaDoContexto(): ?Turma
    {
        $turmaId = (int) request('turma_id');
        if (! $turmaId) {
            return null;
        }

        $turma = $this->turmaService->getById($turmaId, ['escola', 'anoLetivo']);

        return ($turma && $this->turmaService->podeAcessar($turma)) ? $turma : null;
    }

    private function disciplinasDaTurmaOpcoes(Turma $turma): array
    {
        return $this->turmaService->disciplinasVisiveisNaTurma($turma, auth()->user())
            ->mapWithKeys(fn ($d) => [$d->id => $d->nome])
            ->all();
    }

    public function store(StoreDiarioRequest $request)
    {
        $diario = $this->diarioService->create($request->validated());

        if ($request->input('ir_para_chamada') && $diario) {
            return redirect()->route('diario.frequencia', $diario->id);
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aula registrada com sucesso.');
    }

    public function edit($id)
    {
        $diario = $this->diarioService->getById($id, ['turma', 'disciplina', 'professor']);
        abort_if(! $diario, 404, 'Registro não encontrado.');
        abort_if(! $this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Diário de Classe', 'diario.index', 'Editar Aula', 'Vida Acadêmica');

        return view('diario.add-edit', array_merge(compact('diario', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateDiarioRequest $request, $id)
    {
        $this->diarioService->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aula atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $this->diarioService->delete($id);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /**
     * Chamada unificada: turma + disciplina + data definem o diario
     * (existente ou a ser criado); a tela mostra aula, presenca e
     * observacoes num unico formulario. Sem disciplina_id, resolve
     * automaticamente quando ha apenas uma; com varias, pede a escolha.
     */
    public function chamada(Request $request)
    {
        $dados = $request->validate([
            'turma_id' => ['required', 'integer'],
            'disciplina_id' => ['nullable', 'integer'],
            'data' => ['nullable', 'date'],
        ]);

        $turma = $this->turmaService->getById((int) $dados['turma_id'], ['escola', 'anoLetivo']);
        abort_unless($turma, 404, 'Turma não encontrada.');
        abort_unless($this->turmaService->podeAcessar($turma), 403, 'Sem acesso a esta turma.');

        $data = $dados['data'] ?? now()->toDateString();
        $disciplinas = $this->turmaService->disciplinasVisiveisNaTurma($turma, $request->user());

        $disciplinaId = (int) ($dados['disciplina_id'] ?? 0);
        if (! $disciplinaId && $disciplinas->count() === 1) {
            $disciplinaId = (int) $disciplinas->first()->id;
        }

        $disciplina = null;
        $diario = null;
        $matriculas = collect();
        $registradas = collect();

        if ($disciplinaId) {
            abort_unless($disciplinas->contains('id', $disciplinaId), 403,
                'Disciplina não atribuída a você nesta turma.');

            ['disciplina' => $disciplina, 'diario' => $diario] =
                $this->diarioService->contextoChamada($turma->id, $disciplinaId, $data);

            $matriculas = $this->diarioService->matriculasAtivasDaTurma($turma->id);
            $registradas = $diario ? $this->frequenciaService->porMatricula($diario) : collect();
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Diário de Classe', 'diario.index',
            "Chamada — {$turma->nome}".($disciplina ? " / {$disciplina->nome}" : '')
        );

        return view('diario.chamada', compact(
            'turma', 'disciplinas', 'disciplina', 'data', 'diario',
            'matriculas', 'registradas', 'breadcrumb'
        ))->with('titulo', $this->titulo);
    }

    /** Grava aula (se ainda nao existir) + presenca numa unica acao. */
    public function salvarChamada(SalvarChamadaRequest $request)
    {
        $dados = $request->validated();
        $turmaId = (int) $dados['turma_id'];
        $disciplinaId = (int) $dados['disciplina_id'];

        ['turma' => $turma, 'diario' => $diario] = $this->diarioService->contextoChamada(
            $turmaId, $disciplinaId, $dados['data']
        );

        if ($diario) {
            abort_unless($this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');
        } else {
            abort_unless($request->user()->can('diario.store'), 403);
        }

        $itens = $dados['frequencias'] ?? [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $dados, $turmaId, $disciplinaId, &$diario, $itens) {
            $diario ??= $this->diarioService->create([
                'turma_id' => $turmaId,
                'disciplina_id' => $disciplinaId,
                'data' => $dados['data'],
                'conteudo' => $dados['conteudo'],
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            if (! $diario->wasRecentlyCreated) {
                $this->diarioService->update($diario->id, [
                    'conteudo' => $dados['conteudo'],
                    'observacoes' => $dados['observacoes'] ?? null,
                ]);
            }

            $itens = $this->sanitizarItensFrequenciaProfessor($request, $diario, $itens);
            if ($itens !== []) {
                $this->frequenciaService->salvarChamada($diario, $itens);
            }
        });

        return redirect()
            ->to(route('turma.show', $turma->id).'#tab-registros')
            ->with('success', 'Chamada registrada com sucesso.');
    }

    /** Professor so marca presente/falta; justificativa e status especiais ficam com a secretaria. */
    private function sanitizarItensFrequenciaProfessor(Request $request, \App\Models\Diario $diario, array $itens): array
    {
        if (! $request->user()->isProfessor()) {
            return $itens;
        }

        $atuais = $this->frequenciaService->porMatricula($diario);

        return array_map(function ($item) use ($atuais) {
            unset($item['justificativa']);
            $statusAtual = $atuais->get($item['matricula_id'])?->status;
            if (! in_array($item['status'], ['presente', 'falta'], true) && $item['status'] !== $statusAtual) {
                $item['status'] = 'falta';
            }

            return $item;
        }, $itens);
    }

    /** Tela de chamada: lista alunos ativos da turma com status. */
    public function frequencia($id)
    {
        $diario = $this->diarioService->getById($id, [
            'turma.escola', 'turma.anoLetivo', 'disciplina', 'professor',
        ]);
        abort_if(! $diario, 404, 'Registro não encontrado.');
        abort_if(! $this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        $matriculas = $this->diarioService->matriculasDaTurma($diario);
        $registradas = $this->frequenciaService->porMatricula($diario);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Diário de Classe', 'diario.index',
            "Chamada — {$diario->turma->nome} / {$diario->disciplina->nome}"
        );

        return view('diario.frequencia', compact(
            'diario', 'matriculas', 'registradas', 'breadcrumb'
        ))->with('titulo', $this->titulo);
    }

    public function salvarFrequencia(SalvarFrequenciaRequest $request, $id)
    {
        $diario = $this->diarioService->getById($id);
        abort_if(! $diario, 404, 'Registro não encontrado.');
        abort_if(! $this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        $itens = $this->sanitizarItensFrequenciaProfessor(
            $request, $diario, $request->validated()['frequencias']
        );

        $this->frequenciaService->salvarChamada($diario, $itens);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Chamada registrada com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'diario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'diario.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->turmasOpcoes(),
            ],
            [
                'name' => 'disciplina_id', 'label' => 'Disciplina', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->disciplinasOpcoes(),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'user_id', 'label' => 'Professor', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->professoresOpcoes(),
            ],
            ['name' => 'data', 'label' => 'Data', 'type' => 'date', 'typeWhere' => 'where'],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'turmas' => $this->turmasOpcoes(),
            'disciplinas' => $this->disciplinasOpcoes(),
        ];
    }

    /**
     * Professores so veem a si mesmos no filtro; demais perfis veem
     * todos os usuarios com perfil vinculado.
     */
    private function professoresOpcoes(): array
    {
        $user = auth()->user();
        if ($user && \App\Services\DiarioService::ehProfessor($user)) {
            return [$user->id => $user->nome];
        }

        return User::query()->whereNotNull('role_id')->orderBy('nome')
            ->pluck('nome', 'id')->all();
    }

    /**
     * Disciplinas que o professor oferta: vinculadas diretamente na
     * atribuicao ou todas as ofertadas nas turmas onde ele e regente
     * (atribuicao sem disciplina).
     */
    private function disciplinasOpcoes(): array
    {
        $query = Disciplina::query()->where('ativo', 'S')->orderBy('nome');

        $user = auth()->user();
        if ($user && \App\Services\DiarioService::ehProfessor($user)) {
            $atribuicoes = \App\Models\Atribuicao::query()
                ->where('user_id', $user->id)
                ->where('ativo', 'S')
                ->get(['turma_id', 'disciplina_id']);

            $disciplinaIds = $atribuicoes->pluck('disciplina_id')->filter();
            $turmasRegencia = $atribuicoes->whereNull('disciplina_id')->pluck('turma_id');

            if ($turmasRegencia->isNotEmpty()) {
                $disciplinaIds = $disciplinaIds->merge(
                    \Illuminate\Support\Facades\DB::table('turma_disciplina')
                        ->whereIn('turma_id', $turmasRegencia)
                        ->pluck('disciplina_id')
                );
            }

            $query->whereIn('id', $disciplinaIds->unique()->values());
        }

        return $query->pluck('nome', 'id')->all();
    }

    /** Professores so veem turmas onde tem atribuicao ativa. */
    private function turmasOpcoes(): array
    {
        $query = Turma::query()->with(['escola', 'anoLetivo'])->orderBy('nome');

        $user = auth()->user();
        if ($user && \App\Services\DiarioService::ehProfessor($user)) {
            $query->whereHas('atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'));
        }

        return $query->get()
            ->mapWithKeys(fn (Turma $t) => [
                $t->id => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
            ])->all();
    }
}
