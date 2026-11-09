<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\EmprestimoLivro\StoreEmprestimoLivroRequest;
use App\Models\Aluno;
use App\Models\EmprestimoLivro;
use App\Models\Livro;
use App\Services\BreadcrumbService;
use App\Services\EmprestimoLivroService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmprestimoLivroController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected EmprestimoLivroService $emprestimoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Empréstimos de Livros';
        $this->nomeFiltro = 'emprestimo';

        $this->middleware('can:emprestimo.index')->only('index');
        $this->middleware('can:emprestimo.create')->only('create');
        $this->middleware('can:emprestimo.store')->only('store');
        $this->middleware('can:emprestimo.destroy')->only('destroy');
        $this->middleware('can:emprestimo.devolver')->only('devolver');
        $this->middleware('can:emprestimo.filtro')->only('filtro');
        $this->middleware('can:emprestimo.removerFiltro')->only('removerFiltro');
        $this->middleware('can:emprestimo.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $emprestimos = $this->emprestimoService->getAllWithFiltersAndPagination(
            $this->filtrosParaConsulta($itensFiltered), $perPage, ['livro', 'aluno.pessoa', 'anexos'], 'data_emprestimo', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Empréstimos', 'Biblioteca');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('emprestimo.index', compact(
            'emprestimos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Empréstimos', 'emprestimo.index', 'Novo Empréstimo', 'Biblioteca');

        return view('emprestimo.create', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEmprestimoLivroRequest $request)
    {
        $this->emprestimoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Empréstimo registrado com sucesso.');
    }

    public function devolver(Request $request, $id)
    {
        $dados = $request->validate([
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'fotos' => ['nullable', 'array', 'max:5'],
            'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $emprestimo = $this->emprestimoService->devolver($id, $dados, $request->file('fotos', []));
        abort_if($emprestimo === null, 404, 'Empréstimo não encontrado.');
        abort_if($emprestimo === false, 422, 'Este empréstimo já foi devolvido.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Devolução registrada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->emprestimoService->delete($id);
        abort_if(! $success, 403, 'Só é possível excluir empréstimos ainda não devolvidos.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'emprestimo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'emprestimo.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'aluno_id', 'label' => 'Aluno', 'type' => 'select', 'typeWhere' => 'where', 'options' => $this->alunosOpcoesFiltro()],
            ['name' => 'livro_id', 'label' => 'Livro', 'type' => 'select', 'typeWhere' => 'where', 'options' => $this->livrosOpcoesFiltro()],
            [
                'name' => 'situacao', 'label' => 'Situação', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => EmprestimoLivro::SITUACOES,
            ],
            [
                'name' => 'data_emprestimo', 'label' => 'Empréstimo', 'type' => 'date',
                'typeWhere' => 'where',
            ],
            [
                'name' => 'data_devolucao_prevista', 'label' => 'Devolução prevista', 'type' => 'date',
                'typeWhere' => 'where',
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

    /**
     * O filtro "situacao" e derivado (atraso depende da data prevista),
     * entao e traduzido para colunas reais apenas na consulta — o chip
     * visivel continua sendo o "situacao" gravado na sessao.
     * ativo = status ativo + prevista hoje/futura; atrasado = ativo +
     * prevista passada; devolvido = status devolvido.
     */
    private function filtrosParaConsulta(array $filtros): array
    {
        $situacao = $filtros['situacao']['valor'] ?? null;
        if (! $situacao) {
            return $filtros;
        }

        unset($filtros['situacao'], $filtros['status']);
        $hoje = now()->toDateString();

        if ($situacao === 'devolvido') {
            $filtros['status'] = ['valor' => 'devolvido', 'typeWhere' => 'where'];

            return $filtros;
        }

        $filtros['status'] = ['valor' => 'ativo', 'typeWhere' => 'where'];
        $filtros['data_devolucao_prevista'] = $situacao === 'atrasado'
            ? ['valor' => ['to' => now()->subDay()->toDateString()], 'typeWhere' => 'between']
            : ['valor' => ['from' => $hoje], 'typeWhere' => 'between'];

        return $filtros;
    }

    private function opcoesFormulario(): array
    {
        return [
            'alunos' => $this->alunosOpcoes(),
            'livros' => $this->livrosOpcoes(),
            'situacoes' => EmprestimoLivro::SITUACOES,
        ];
    }

    private function alunosOpcoes(): array
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->get()
            ->mapWithKeys(fn (Aluno $a) => [
                $a->id => $a->pessoa?->nome ?? ('Aluno #'.$a->id),
            ])->all();
    }

    /** id => "Titulo — Autor (N disp.)", so livros ativos. */
    private function livrosOpcoes(): array
    {
        $livros = Livro::query()->where('ativo', 'S')->orderBy('titulo')->get();

        $ocupados = EmprestimoLivro::query()
            ->whereIn('livro_id', $livros->pluck('id'))
            ->where('status', 'ativo')
            ->select('livro_id', DB::raw('count(*) as total'))
            ->groupBy('livro_id')
            ->pluck('total', 'livro_id');

        return $livros
            ->mapWithKeys(fn (Livro $l) => [
                $l->id => $l->titulo.($l->autor ? ' — '.$l->autor : '')
                    .' ('.max(0, $l->exemplares - (int) ($ocupados[$l->id] ?? 0)).' disp.)',
            ])->all();
    }

    private function alunosOpcoesFiltro()
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->get()
            ->map(fn (Aluno $a) => [
                'value' => $a->id,
                'label' => $a->pessoa?->nome ?? ('Aluno #'.$a->id),
            ])->all();
    }

    private function livrosOpcoesFiltro()
    {
        return Livro::query()->orderBy('titulo')->get(['id', 'titulo'])
            ->map(fn (Livro $l) => ['value' => $l->id, 'label' => $l->titulo])
            ->all();
    }
}
