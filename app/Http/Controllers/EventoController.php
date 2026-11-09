<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Evento\StoreEventoRequest;
use App\Http\Requests\Evento\UpdateEventoRequest;
use App\Models\Escola;
use App\Models\Evento;
use App\Services\BreadcrumbService;
use App\Services\EventoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class EventoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected EventoService $eventoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Agenda';
        $this->nomeFiltro = 'evento';

        $this->middleware('can:evento.index')->only('index');
        $this->middleware('can:evento.create')->only('create');
        $this->middleware('can:evento.store')->only('store');
        $this->middleware('can:evento.edit')->only('edit');
        $this->middleware('can:evento.update')->only('update');
        $this->middleware('can:evento.destroy')->only('destroy');
        $this->middleware('can:evento.filtro')->only('filtro');
        $this->middleware('can:evento.removerFiltro')->only('removerFiltro');
        $this->middleware('can:evento.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $mesParam = (string) request('mes', '');
        $mesCarbon = preg_match('/^\d{4}-\d{2}$/', $mesParam)
            ? \Carbon\Carbon::createFromFormat('Y-m-d', $mesParam.'-01')->startOfMonth()
            : now()->startOfMonth();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $filtros = collect($itensFiltered)
            ->map(fn ($f) => is_array($f) ? ($f['valor'] ?? null) : null)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        $eventos = $this->eventoService->agendaDoMes($mesCarbon, $filtros);

        // Grade do mes para a visao de calendario: eventos de varios
        // dias aparecem em cada dia coberto pelo periodo.
        $inicio = $mesCarbon->copy();
        $fim = $mesCarbon->copy()->endOfMonth();
        $eventosPorDia = [];
        foreach ($eventos as $evento) {
            $de = $evento->data_inicio->copy()->max($inicio);
            $ate = $evento->periodoFim()->copy()->min($fim);

            for ($dia = $de->copy(); $dia->lte($ate); $dia->addDay()) {
                $eventosPorDia[$dia->toDateString()][] = $evento;
            }
        }

        $semanas = [];
        $cursor = $inicio->copy()->startOfWeek();
        $ultimo = $fim->copy()->endOfWeek();

        while ($cursor->lte($ultimo)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++, $cursor->addDay()) {
                $semana[] = [
                    'data' => $cursor->copy(),
                    'do_mes' => $cursor->month === $mesCarbon->month,
                    'eventos' => collect($eventosPorDia[$cursor->toDateString()] ?? []),
                ];
            }
            $semanas[] = $semana;
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Agenda', 'Comunicação');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;
        $mesAnterior = $mesCarbon->copy()->subMonth()->format('Y-m');
        $mesProximo = $mesCarbon->copy()->addMonth()->format('Y-m');
        $mesAtual = now()->format('Y-m');
        $ehMesAtual = $mesCarbon->isSameMonth(now());

        return view('evento.index', compact(
            'eventos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'nomeFiltro',
            'mesCarbon', 'mesAnterior', 'mesProximo', 'mesAtual',
            'semanas', 'ehMesAtual'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Agenda', 'evento.index', 'Novo Evento', 'Comunicação');

        return view('evento.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEventoRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['suspende_aulas'] ??= 'N';
        $turmas = $data['turmas'] ?? [];
        unset($data['turmas']);

        $evento = $this->eventoService->create($data);
        $evento->turmas()->sync($turmas);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Evento cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $evento = $this->eventoService->getById($id, ['escola', 'turmas']);
        abort_if(! $evento, 404, 'Evento não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Agenda', 'evento.index', "Editar: {$evento->titulo}", 'Comunicação');

        return view('evento.add-edit', array_merge(compact('evento', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEventoRequest $request, $id)
    {
        $data = $request->validated();
        $data['suspende_aulas'] ??= 'N';
        $turmas = $data['turmas'] ?? [];
        unset($data['turmas']);

        $evento = $this->eventoService->update($id, $data);
        abort_if(! $evento, 403, 'Sem acesso para editar este evento.');
        $evento->turmas()->sync($turmas);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Evento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->eventoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este evento.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'agenda');
        session()->put("$this->nomeFiltro.rotaRedirect", 'evento.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'titulo', 'label' => 'Título', 'type' => 'text',
                'typeWhere' => 'like', 'placeholder' => 'Buscar evento...',
            ],
            [
                'name' => 'tipo', 'label' => 'Tipo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Evento::TIPOS,
            ],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Evento::STATUS,
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'suspende_aulas', 'label' => 'Aulas', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Suspende aulas', 'N' => 'Aula normal'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'tipos' => Evento::TIPOS,
            'publicos' => Evento::PUBLICOS,
            'statusList' => Evento::STATUS,
            'turmasJson' => \App\Models\Turma::query()->where('ativo', 'S')
                ->with('escola:id,nome')->orderBy('nome')
                ->get(['id', 'nome', 'escola_id'])
                ->map(fn ($t) => ['id' => $t->id, 'escola_id' => $t->escola_id, 'label' => $t->nome.' — '.($t->escola->nome ?? '')])
                ->values(),
        ];
    }
}
