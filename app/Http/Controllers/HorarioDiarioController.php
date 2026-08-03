<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\HorarioDiario\StoreHorarioDiarioRequest;
use App\Http\Requests\HorarioDiario\UpdateHorarioDiarioRequest;
use App\Models\HorarioDiario;
use App\Services\BreadcrumbService;
use App\Services\EstacaoService;
use App\Services\HorarioDiarioService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class HorarioDiarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $horarioDiarioService;
    protected $breadcrumbService;
    protected $estacaoService;

    public function __construct(
        HorarioDiarioService $horarioDiarioService,
        BreadcrumbService $breadcrumbService,
        EstacaoService $estacaoService
    ) {
        $this->titulo = "Horarios Diarios";
        $this->nomeFiltro = 'horario_diario';
        $this->horarioDiarioService = $horarioDiarioService;
        $this->breadcrumbService = $breadcrumbService;
        $this->estacaoService = $estacaoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $horarios_diarios = HorarioDiario::query()
            ->with('estacoes:id,nome')
            ->when($this->filtroExiste($itensFiltered, 'nome'), function ($query) use ($itensFiltered) {
                $query->where('nome', 'like', '%' . $itensFiltered['nome']['valor'] . '%');
            })
            ->when($this->filtroExiste($itensFiltered, 'ativo'), function ($query) use ($itensFiltered) {
                $query->where('ativo', $itensFiltered['ativo']['valor']);
            })
            ->when($this->filtroExiste($itensFiltered, 'estacao_id'), function ($query) use ($itensFiltered) {
                $query->whereHas('estacoes', function ($subQuery) use ($itensFiltered) {
                    $subQuery->where('estacoes.id', $itensFiltered['estacao_id']['valor']);
                });
            })
            ->orderBy('nome', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('HorarioDiarios');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $estacoes = $this->estacaoService->getAllFormatSelect('id', 'nome');

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('horario_diario.index', compact(
            'horarios_diarios',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'estacoes'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('HorarioDiarios', 'horario_diario.index', 'Criar');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('horario_diario.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreHorarioDiarioRequest $request)
    {
        $this->horarioDiarioService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $horario_diario = $this->horarioDiarioService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('HorarioDiarios', 'horario_diario.index', "Editar: $horario_diario->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($horario_diario);

        return view('horario_diario.add-edit', compact('horario_diario', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateHorarioDiarioRequest $request, $id)
    {
        $this->horarioDiarioService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $this->horarioDiarioService->delete($id);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $atributo = $request->input('atributo') ?? null;
        $this->removeSessao($this->nomeFiltro, $atributo);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'horario_diario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'horario_diario.index');
    }

    public function obterFiltrosPrincipais()
    {
        $estacaoOptions = self::formatOptions($this->estacaoService->getAllFormatSelect('id', 'nome'), 'Selecione a filial');

        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'estacao_id', 'label' => 'Filial', 'type' => 'select', 'typeWhere' => 'where', 'options' => $estacaoOptions],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false]
            ]]
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundarios aqui, se necessario
        ];
    }

    private function getFormMethodAndRoute($horario_diario = null)
    {
        if ($horario_diario) {
            return [
                'PUT',
                route('horario_diario.update', $horario_diario->id),
            ];
        }

        return [
            'POST',
            route('horario_diario.store'),
        ];
    }

    private function filtroExiste(array $itensFiltered, string $chave): bool
    {
        return isset($itensFiltered[$chave]['valor']) && $itensFiltered[$chave]['valor'] !== '' && $itensFiltered[$chave]['valor'] !== null;
    }
}
