<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Requests\Justificativa\StoreJustificativaRequest;
use App\Models\Escola;
use App\Models\Justificativa;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\JustificativaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Atestados e abonos de falta: registro pela secretaria com
 * efeito automatico nas ausencias do periodo.
 */
class JustificativaController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected JustificativaService $justificativaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Atestados e Abonos';
        $this->nomeFiltro = 'justificativa';

        $this->middleware('can:justificativa.index')->only('index');
        $this->middleware('can:justificativa.create')->only(['create', 'store']);
        $this->middleware('can:justificativa.destroy')->only('destroy');
        $this->middleware('can:justificativa.arquivo')->only('arquivo');
        $this->middleware('can:justificativa.filtro')->only('filtro');
        $this->middleware('can:justificativa.removerFiltro')->only('removerFiltro');
        $this->middleware('can:justificativa.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        // Rota aninhada da turma: filtra por ela e mostra no breadcrumb.
        $turmaRota = request()->route('turma');
        $turma = $turmaRota ? Turma::find($turmaRota) : null;
        if ($turma) {
            $itensFiltered['turma_id'] = [
                'valor' => $turma->id,
                'typeWhere' => 'where',
                'nomeExibir' => 'Turma: '.$turma->nome,
            ];
        }

        $justificativas = $this->justificativaService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $turma
            ? $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Turmas', route('turma.index')],
                [$turma->nome, route('turma.show', $turma->id)],
                ['Atestados e Abonos', null],
            ])
            : $this->breadcrumbService->prepararBreadcrumbIndex('Atestados e Abonos', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;
        $turmaIdFiltro = $itensFiltered['turma_id']['valor'] ?? null;

        return view('justificativa.index', compact(
            'justificativas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro', 'turmaIdFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create(Request $request)
    {
        $turmas = $this->justificativaService->turmasOpcoes()->load(['escola:id,nome', 'anoLetivo:id,ano']);

        $turmaRota = $request->route('turma');
        $turma = $turmaRota ? Turma::find($turmaRota) : null;
        if ($turma) {
            $request->merge(['turma_id' => $turma->id]);
        }

        $breadcrumb = $turma
            ? $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Turmas', route('turma.index')],
                [$turma->nome, route('turma.show', $turma->id)],
                ['Nova justificativa', null],
            ])
            : $this->breadcrumbService->prepararBreadcrumbCreate(
                'Atestados e Abonos', 'justificativa.index', 'Nova justificativa', 'Vida Acadêmica'
            );

        $escolas = $turmas->pluck('escola')->filter()->unique('id')->sortBy('nome')
            ->mapWithKeys(fn ($e) => [$e->id => $e->nome])->all();

        $turmasPicker = $turmas->values()->map(fn ($t) => [
            'id' => $t->id,
            'rotulo' => $t->nome,
            'detalhe' => trim(($t->anoLetivo?->ano ? $t->anoLetivo->ano.' · ' : '').($t->escola?->nome ?? ''), ' ·'),
            'escola_id' => $t->escola_id,
            'escola' => $t->escola?->nome ?? '',
        ])->all();

        return view('justificativa.add-edit', [
            'turmas' => $turmas,
            'escolas' => $escolas,
            'turmasPicker' => $turmasPicker,
            'matriculasPicker' => $this->justificativaService->matriculasPickerPorTurma($turmas),
            'matriculasPorTurma' => $this->justificativaService->matriculasPorTurma($turmas),
            'disciplinasPorTurma' => $this->justificativaService->disciplinasPorTurma($turmas),
            'tipos' => Justificativa::TIPOS,
            'efeitos' => Justificativa::EFEITOS,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function store(StoreJustificativaRequest $request)
    {
        $this->justificativaService->create($request->validated());

        return redirect()->route('justificativa.index')
            ->with('success', 'Justificativa registrada e faltas do período atualizadas.');
    }

    public function arquivo($id)
    {
        $justificativa = $this->justificativaService->getById($id);
        abort_if(! $justificativa, 404, 'Registro não encontrado.');
        abort_unless($justificativa->arquivo_path && Storage::disk('public')->exists($justificativa->arquivo_path), 404);

        return Storage::disk('public')->download(
            $justificativa->arquivo_path,
            'justificativa-'.$justificativa->id.'.'.pathinfo($justificativa->arquivo_path, PATHINFO_EXTENSION)
        );
    }

    public function destroy($id)
    {
        $success = $this->justificativaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este registro.');

        return redirect()->route('justificativa.index')
            ->with('success', 'Justificativa excluída e faltas revertidas.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'justificativas');
        session()->put("$this->nomeFiltro.rotaRedirect", 'justificativa.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Aluno', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'efeito', 'label' => 'Efeito', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todos', 'selected' => false]],
                    collect(Justificativa::EFEITOS)
                        ->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    $this->justificativaService->turmasOpcoes()
                        ->map(fn ($t) => ['value' => $t->id, 'label' => $t->nome, 'selected' => false])
                        ->values()->all()
                ),
            ],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Escola::query()->orderBy('nome')->get(['id', 'nome'])
                        ->map(fn ($e) => ['value' => $e->id, 'label' => $e->nome, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }
}
