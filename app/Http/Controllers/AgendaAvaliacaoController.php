<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Models\Avaliacao;
use App\Models\Escola;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Support\EscolaContext;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Agenda de avaliacoes: calendario mensal somente-leitura
 * das provas/trabalhos lancados nas turmas. Professor ve
 * apenas avaliacoes das turmas que leciona.
 */
class AgendaAvaliacaoController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Agenda de Avaliações';
        $this->nomeFiltro = 'avaliacao_agenda';

        $this->middleware('can:avaliacao.agenda')->only(['index', 'filtro', 'removerFiltro']);
    }

    public function index(Request $request)
    {
        $this->configurarSessao();

        $mes = $request->input('mes', now()->format('Y-m'));
        $mesCarbon = Carbon::parse($mes.'-01')->startOfMonth();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $filtros = collect($itensFiltered)
            ->mapWithKeys(fn ($f) => [$f['name'] => $f['valor']])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        // Escola ativa no contexto: o escopo global ja filtra — ignora
        // um escola_id divergente salvo na sessao.
        if (app(EscolaContext::class)->filtrar()) {
            unset($filtros['escola_id']);
        }

        $query = Avaliacao::query()
            ->with(['turma.escola', 'turma.anoLetivo', 'turma.etapa', 'disciplina', 'professor'])
            ->whereBetween('data', [$mesCarbon->copy()->startOfMonth(), $mesCarbon->copy()->endOfMonth()])
            ->orderBy('data')
            ->orderBy('id');

        if ($filtros['escola_id'] ?? null) {
            $query->whereHas('turma', fn ($q) => $q->where('escola_id', $filtros['escola_id']));
        }
        if ($filtros['turma_id'] ?? null) {
            $query->where('turma_id', $filtros['turma_id']);
        }
        if ($filtros['tipo'] ?? null) {
            $query->where('tipo', $filtros['tipo']);
        }

        $user = $request->user();
        if ($user->isProfessor()) {
            $query->whereHas('turma.atribuicoes', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('ativo', 'S'));
        }

        $avaliacoes = $query->get();

        // Grade do mes: celulas de domingo a sabado cobrindo o mes.
        $porDia = $avaliacoes->groupBy(fn ($a) => $a->data->format('Y-m-d'));
        $semanas = [];
        $cursor = $mesCarbon->copy()->startOfWeek();
        $ultimo = $mesCarbon->copy()->endOfMonth()->endOfWeek();

        while ($cursor->lte($ultimo)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++, $cursor->addDay()) {
                $semana[] = [
                    'data' => $cursor->copy(),
                    'do_mes' => $cursor->month === $mesCarbon->month,
                    'avaliacoes' => collect($porDia[$cursor->toDateString()] ?? []),
                ];
            }
            $semanas[] = $semana;
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Agenda de Avaliações', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $nomeFiltro = $this->nomeFiltro;

        return view('agenda_avaliacao.index', [
            'titulo' => 'Agenda de Avaliações',
            'breadcrumb' => $breadcrumb,
            'avaliacoes' => $avaliacoes,
            'semanas' => $semanas,
            'mesCarbon' => $mesCarbon,
            'mesAnterior' => $mesCarbon->copy()->subMonth()->format('Y-m'),
            'mesProximo' => $mesCarbon->copy()->addMonth()->format('Y-m'),
            'mesAtual' => now()->format('Y-m'),
            'filters' => $filters,
            'itensFiltered' => $itensFiltered,
            'additionalFilters' => $additionalFilters,
            'nomeFiltro' => $nomeFiltro,
        ]);
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

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'agenda-avaliacoes');
        session()->put("$this->nomeFiltro.rotaRedirect", 'avaliacao.agenda');
    }

    public function obterFiltrosPrincipais()
    {
        $filtros = [];

        if (! app(EscolaContext::class)->filtrar()) {
            $filtros[] = [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ];
        }

        $user = auth()->user();
        $filtros[] = [
            'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
            'typeWhere' => 'where',
            'options' => Turma::query()
                ->when($user && $user->isProfessor(), fn ($q) => $q->whereHas('atribuicoes', fn ($a) => $a
                    ->where('user_id', $user->id)
                    ->where('ativo', 'S')))
                ->orderBy('nome')->get(['id', 'nome'])
                ->map(fn (Turma $turma) => ['value' => $turma->id, 'label' => $turma->nome])
                ->all(),
        ];

        $filtros[] = [
            'name' => 'tipo', 'label' => 'Tipo', 'type' => 'select',
            'typeWhere' => 'where',
            'options' => Avaliacao::TIPOS,
        ];

        return $filtros;
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }
}
