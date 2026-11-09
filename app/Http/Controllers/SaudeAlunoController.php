<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Requests\SaveSaudeAlunoRequest;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\SaudeAluno;
use App\Services\BreadcrumbService;
use App\Services\SaudeAlunoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class SaudeAlunoController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected SaudeAlunoService $saudeService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Saúde dos Alunos';
        $this->nomeFiltro = 'saude';

        $this->middleware('can:saude.index')->only(['index']);
        $this->middleware('can:saude.show')->only(['show']);
        $this->middleware('can:saude.edit')->only(['edit']);
        $this->middleware('can:saude.update')->only(['update']);
        $this->middleware('can:saude.filtro')->only('filtro');
        $this->middleware('can:saude.removerFiltro')->only('removerFiltro');
        $this->middleware('can:saude.setPerPage')->only('setPerPage');
    }

    /** Lista as fichas de saude cadastradas. */
    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        // Converte os itens de sessao para mapa plano usado pelo service
        $filtros = collect($itensFiltered)
            ->mapWithKeys(fn ($f) => [$f['name'] => $f['valor']])
            ->all();

        $fichas = $this->saudeService->listarComFiltros($filtros, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Saúde dos Alunos', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [10, 15, 25, 50];
        $nomeFiltro = $this->nomeFiltro;

        return view('saude.index', compact(
            'fichas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    /** Visualizacao somente-leitura da ficha do aluno. */
    public function show(Aluno $aluno)
    {
        $ficha = $this->saudeService->paraAluno($aluno->id);
        abort_if(! $ficha, 404, 'Ficha de saúde não cadastrada para este aluno.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Saúde dos Alunos', 'saude.index',
            'Ficha: '.($aluno->pessoa->nome ?? 'Aluno'), 'Vida Acadêmica'
        );

        return view('saude.show', [
            'titulo' => $this->titulo,
            'aluno' => $aluno->load('pessoa', 'escola'),
            'ficha' => $ficha,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /** Formulario de criacao/edicao da ficha do aluno. */
    public function edit(Aluno $aluno)
    {
        $ficha = $this->saudeService->paraAluno($aluno->id);
        $aluno->load('pessoa', 'escola');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Saúde dos Alunos', 'saude.index',
            'Ficha: '.($aluno->pessoa->nome ?? 'Aluno'), 'Vida Acadêmica'
        );

        return view('saude.form', [
            'titulo' => $this->titulo,
            'aluno' => $aluno,
            'ficha' => $ficha,
            'breadcrumb' => $breadcrumb,
            'tiposSanguineos' => SaudeAluno::TIPOS_SANGUINEOS,
        ]);
    }

    /** Salva (upsert) a ficha do aluno. */
    public function update(SaveSaudeAlunoRequest $request, Aluno $aluno)
    {
        $this->saudeService->salvar($aluno->id, $request->validated());

        // Form vindo da aba Saude da ficha do aluno volta para la
        $destino = $request->input('voltar') === 'aluno'
            ? route('aluno.show', $aluno->id).'#as-saude'
            : route('saude.show', $aluno->id);

        return redirect()->to($destino)
            ->with('success', 'Ficha de saúde salva com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'saude');
        session()->put("$this->nomeFiltro.rotaRedirect", 'saude.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'nome', 'label' => 'Nome do aluno', 'type' => 'text',
                'typeWhere' => 'like',
            ],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }
}
