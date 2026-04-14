<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Movimentacao\StoreMovimentacaoRequest;
use App\Http\Requests\Movimentacao\UpdateMovimentacaoRequest;
use App\Services\BreadcrumbService;
use App\Services\MovimentacaoService;
use App\Services\VeiculoService;
use App\Services\EstacaoService;
use App\Services\StatusMovimentacaoService;
use App\Services\TipoMovimentacaoService;
use App\Services\UsuarioService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use App\Support\EmpresaContext;

class MovimentacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $movimentacaoService;
    protected $breadcrumbService;

    protected $tipoMovimentacaoService;
    protected $usuarioService;
    protected $statusMovimentacaoService;
    protected $veiculoService;
    protected $estacaoService;

    public function __construct(MovimentacaoService $movimentacaoService, BreadcrumbService $breadcrumbService, TipoMovimentacaoService $tipoMovimentacaoService, UsuarioService $usuarioService, StatusMovimentacaoService $statusMovimentacaoService, VeiculoService $veiculoService, EstacaoService $estacaoService)
    {
        $this->titulo = "Movimentações";
        $this->nomeFiltro = 'movimentacao';
        $this->movimentacaoService = $movimentacaoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->tipoMovimentacaoService = $tipoMovimentacaoService;
        $this->usuarioService = $usuarioService;
        $this->statusMovimentacaoService = $statusMovimentacaoService;
        $this->veiculoService = $veiculoService;
        $this->estacaoService = $estacaoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $movimentacaos = $this->movimentacaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage, [
            'tipoMovimentacao',
            'usuario',
            'statusMovimentacao',
            'veiculo.modelo',
            'veiculo.grupoFrota',
            'localOrigem',
            'localDestino',
            'reserva',
        ], 'id', 'desc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Movimentações');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $empresaId = $this->empresaIdParaConsulta();
        $statusList = \App\Models\StatusMovimentacao::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get(['id', 'nome', 'cor']);

        return view('movimentacao.index', compact(
            'movimentacaos',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'statusList'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Movimentações', 'movimentacao.index', 'Criar');

        $prefill = [
            'veiculo_id' => (int) request()->query('veiculo_id', 0) ?: null,
            'tipo_movimentacao_id' => (int) request()->query('tipo_movimentacao_id', 0) ?: null,
            'status_movimentacao_id' => (int) request()->query('status_movimentacao_id', 0) ?: null,
            'local_origem_id' => (int) request()->query('local_origem_id', 0) ?: null,
            'local_destino_id' => (int) request()->query('local_destino_id', 0) ?: null,
            'km_saida' => request()->query('km_saida') !== null ? (int) request()->query('km_saida') : null,
            'user_id' => (int) request()->query('user_id', auth()->id() ?? 0) ?: null,
        ];

        if ($prefill['veiculo_id']) {
            $veiculo = $this->veiculoService->getById($prefill['veiculo_id'], ['localDisponibilidade']);
            if ($veiculo) {
                $prefill['local_origem_id'] = $prefill['local_origem_id'] ?: ($veiculo->local_disponibilidade ? (int) $veiculo->local_disponibilidade : null);
                if ($prefill['km_saida'] === null && $veiculo->kms !== null) {
                    $prefill['km_saida'] = (int) $veiculo->kms;
                }
            }
        }

        $tiposMovimentacao = $this->tipoMovimentacaoService->getAllFormatSelect('id', 'nome', 'nome', 'asc');
        $statusMovimentacao = $this->statusMovimentacaoService->getAllFormatSelect('id', 'nome', 'nome', 'asc');
        $empresaId = $this->empresaIdParaConsulta();
        $tiposMovimentacaoObj = \App\Models\TipoMovimentacao::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get();
        $statusMovimentacaoObj = \App\Models\StatusMovimentacao::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get();
        $usuariosObj = \App\Models\User::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get(['id', 'nome', 'email', 'numero_identificacao']);
        $usuarios = $this->usuarioService->getAllForReservaSelect();
        $estacoes = $this->estacaoService->listarAtivasParaModal();

        $veiculosCol = $this->veiculoService->listarAtivosParaOrdemServico(800);
        $veiculos = $veiculosCol->mapWithKeys(function ($v) {
            $label = (string) ($v->nome ?? ('Veículo #' . (int) $v->id));
            if (!empty($v->placa_licenca)) {
                $label .= ' - ' . $v->placa_licenca;
            }
            return [(int) $v->id => $label];
        })->all();

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('movimentacao.add-edit', compact(
            'breadcrumb',
            'status',
            'metodo',
            'rota',
            'tiposMovimentacao',
            'statusMovimentacao',
            'tiposMovimentacaoObj',
            'statusMovimentacaoObj',
            'usuariosObj',
            'usuarios',
            'estacoes',
            'veiculos',
            'prefill'
        ))->with('titulo', 'Nova Movimentação');
    }

    public function store(StoreMovimentacaoRequest $request)
    {
        $this->movimentacaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $movimentacao = $this->movimentacaoService->getById($id, ['historico.usuario']);
        if (!$movimentacao) {
            abort(404);
        }

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Movimentações', 'movimentacao.index', "Editar: #{$movimentacao->id}");

        $tiposMovimentacao = $this->tipoMovimentacaoService->getAllFormatSelect('id', 'nome', 'nome', 'asc');
        $statusMovimentacao = $this->statusMovimentacaoService->getAllFormatSelect('id', 'nome', 'nome', 'asc');
        $empresaId = $this->empresaIdParaConsulta();
        $tiposMovimentacaoObj = \App\Models\TipoMovimentacao::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get();
        $statusMovimentacaoObj = \App\Models\StatusMovimentacao::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get();
        $usuariosObj = \App\Models\User::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', $empresaId))
            ->orderBy('nome')->get(['id', 'nome', 'email', 'numero_identificacao']);
        $usuarios = $this->usuarioService->getAllForReservaSelect();
        $estacoes = $this->estacaoService->listarAtivasParaModal();

        $veiculosCol = $this->veiculoService->listarAtivosParaOrdemServico(800);
        $veiculos = $veiculosCol->mapWithKeys(function ($v) {
            $label = (string) ($v->nome ?? ('Veículo #' . (int) $v->id));
            if (!empty($v->placa_licenca)) {
                $label .= ' - ' . $v->placa_licenca;
            }
            return [(int) $v->id => $label];
        })->all();

        [$metodo, $rota] = $this->getFormMethodAndRoute($movimentacao);

        return view('movimentacao.add-edit', compact(
            'movimentacao',
            'breadcrumb',
            'status',
            'metodo',
            'rota',
            'tiposMovimentacao',
            'statusMovimentacao',
            'tiposMovimentacaoObj',
            'statusMovimentacaoObj',
            'usuariosObj',
            'usuarios',
            'estacoes',
            'veiculos'
        ))->with('titulo', "Editar Movimentação #{$movimentacao->id}");
    }

    public function update(UpdateMovimentacaoRequest $request, $id)
    {
        $success = $this->movimentacaoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->movimentacaoService->delete($id);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function concluir(Request $request, $id)
    {
        $chegadaReal = $request->input('chegada_real');
        $this->movimentacaoService->concluir((int) $id, $chegadaReal ?: null);
        return response()->json(['success' => true]);
    }

    private function empresaIdParaConsulta(): ?int
    {
        $empresaId = app(EmpresaContext::class)->empresaId();

        if ($empresaId !== null) {
            return (int) $empresaId;
        }

        $userEmpresaId = auth()->user()?->empresa_id;
        return $userEmpresaId ? (int) $userEmpresaId : null;
    }

    public function buscarReservas(Request $request)
    {
        $q = $request->query('q', '');
        $query = \App\Models\Reserva::with(['usuario:id,nome', 'grupoFrota:id,nome', 'statusReserva:id,nome'])
            ->orderByDesc('id')
            ->limit(50);

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q)
                    ->orWhereHas('usuario', fn($u) => $u->where('nome', 'like', "%{$q}%"));
            });
        }

        return response()->json($query->get()->map(fn($r) => [
            'id'      => $r->id,
            'cliente' => $r->usuario?->nome ?? '—',
            'grupo'   => $r->grupoFrota?->nome ?? '—',
            'status'  => $r->statusReserva?->nome ?? '—',
            'inicio'  => $r->data_inicio ? \Carbon\Carbon::parse($r->data_inicio)->format('d/m/Y') : '—',
            'fim'     => $r->data_fim    ? \Carbon\Carbon::parse($r->data_fim)->format('d/m/Y')    : '—',
        ]));
    }

    public function buscarOrdens(Request $request)
    {
        $q = $request->query('q', '');
        $query = \App\Models\OrdemServico::orderByDesc('id')->limit(50);

        if ($q) {
            $query->where('id', $q)
                  ->orWhere('numero', 'like', "%{$q}%");
        }

        return response()->json($query->get()->map(fn($os) => [
            'id'     => $os->id,
            'numero' => $os->numero ?? ('#' . $os->id),
            'status' => $os->status ?? '—',
            'data'   => $os->created_at?->format('d/m/Y') ?? '—',
        ]));
    }

    public function kmVeiculo(Request $request)
    {
        $veiculoId = (int) $request->query('veiculo_id', 0);
        if (!$veiculoId) {
            return response()->json(['kms' => null]);
        }
        $v = \App\Models\Veiculo::find($veiculoId, ['id', 'kms', 'nome', 'placa_licenca']);
        return response()->json([
            'kms'   => $v?->kms,
            'nome'  => $v?->nome ?? '',
            'placa' => $v?->placa_licenca ?? '',
        ]);
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
        // Salva o valor de paginação na sessão
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a página de listagem
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'movimentacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'movimentacao.index');
    }

    public function obterFiltrosPrincipais()
    {
        $tipoMovimentacao = $this->tipoMovimentacaoService->getAllAtivos('id','nome','nome','asc');
        $dadosTipoMovimentacao = FiltroTrait::formatOptions($tipoMovimentacao, 'Selecione o tipo de Movimentação');

        $usuario = $this->usuarioService->getAllForSelect('id','nome');
        $dadosUsuario = FiltroTrait::formatOptions($usuario, 'Selecione o usuário');

        $statusMovimentacao = $this->statusMovimentacaoService->getAllAtivos('id','nome','nome','asc');
        $dadosStatusMovimentacao = FiltroTrait::formatOptions($statusMovimentacao, 'Selecione o status da Movimentação');

        return [
            ['name' => 'tipo_movimentacao_id', 'label'=> 'Tipos de Movimentações','type'=>'select','typeWhere' => 'where','options'=> $dadosTipoMovimentacao],
            ['name' => 'user_id', 'label'=> 'Usuários','type'=>'select','typeWhere' => 'where','options'=> $dadosUsuario],
            ['name' => 'status_movimentacao_id', 'label'=> 'Status das Movimentações','type'=>'select','typeWhere' => 'where','options'=> $dadosStatusMovimentacao],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundários aqui, se necessário
        ];
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Movimentacao|null  $movimentacao
     * @return array
     */
    private function getFormMethodAndRoute($movimentacao = null)
    {
        if ($movimentacao) {
            return [
                'PUT',
                route('movimentacao.update', $movimentacao->id),
            ];
        }

        return [
            'POST',
            route('movimentacao.store'),
        ];
    }
}
