<?php

namespace App\Http\Controllers;

use App\DTO\BreadcrumbItem;
use App\Exceptions\CartaoNaoCadastradoException;
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Reserva\AdicionarDanoVeiculoRequest;
use App\Http\Requests\Reserva\AssinarContratoRequest;
use App\Http\Requests\Reserva\CriarContratoCheckInRequest;
use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Http\Requests\Reserva\UpdateReservaRequest;
use App\Models\Transacao;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ClienteStripeService;
use App\Services\ContratoCheckinService;
use App\Services\EstacaoService;
use App\Services\GrupoFrotaService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\MovimentacaoService;
use App\Services\OpcionalService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\PdfService;
use App\Services\PaisService;
use App\Services\ReservaService;
use App\Services\RegraService;
use App\Services\TipoOpcionalService;
use App\Services\UsuarioService;
use App\Services\VeiculoDanoService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\LogTrait;
use DB;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $reservaService;
    protected $breadcrumbService;
    protected $userService;
    protected $veiculoService;
    protected $estacaoService;
    protected $grupoFrotaService;
    protected $tipoOpcionalService;
    protected $opcionalService;
    protected $clienteStripeService;
    protected $categoriaDanoService;
    protected $marcaService;
    protected $veiculoDanoService;
    protected $modeloService;
    protected $contratoCheckinService;
    protected $movimentacaoService;
    protected $pdfService;
    protected $gerenciadorDePagamentos;
    protected $gatewayEmpresaConfigService;
    protected $regraService;
    protected $paisService;

    public function __construct(
        ReservaService $reservaService,
        BreadcrumbService $breadcrumbService,
        UsuarioService $userService,
        VeiculoService $veiculoService,
        EstacaoService $estacaoService,
        GrupoFrotaService $grupoFrotaService,
        TipoOpcionalService $tipoOpcionalService,
        OpcionalService $opcionalService,
        ModeloService $modeloService,
        ClienteStripeService $clienteStripeService,
        CategoriaDanoService $categoriaDanoService,
        MarcaService $marcaService,
        VeiculoDanoService $veiculoDanoService,
        ContratoCheckinService $contratoCheckinService,
        MovimentacaoService $movimentacaoService,
        PdfService $pdfService,
        GerenciadorDePagamentos $gerenciadorDePagamentos,
        GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        RegraService $regraService,
        PaisService $paisService
    ) {
        $this->titulo = "Reservas";
        $this->nomeFiltro = 'reserva';
        $this->reservaService = $reservaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->userService = $userService;
        $this->veiculoService = $veiculoService;
        $this->estacaoService = $estacaoService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->tipoOpcionalService = $tipoOpcionalService;
        $this->modeloService = $modeloService;
        $this->opcionalService = $opcionalService;
        $this->clienteStripeService = $clienteStripeService;
        $this->categoriaDanoService = $categoriaDanoService;
        $this->marcaService = $marcaService;
        $this->veiculoDanoService = $veiculoDanoService;
        $this->contratoCheckinService = $contratoCheckinService;
        $this->movimentacaoService = $movimentacaoService;
        $this->pdfService = $pdfService;
        $this->gerenciadorDePagamentos = $gerenciadorDePagamentos;
        $this->gatewayEmpresaConfigService = $gatewayEmpresaConfigService;
        $this->regraService = $regraService;
        $this->paisService = $paisService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $reservas = $this->reservaService->getAllWithFiltersAndPagination(
            $itensFiltered,
            $perPage,
            ['opcionais', 'cupomDesconto'],
            'id',
            'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Reservas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('reserva.index', compact(
            'reservas',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $tituloFormulario = 'Cadastro de Reservas';
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Criar');

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForSelect();
        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $modelosAtivos = $this->modeloService->getAllAtivos();
        $resumoVeiculosEstacoes = $this->montarResumoVeiculosPorEstacao($estacoes);
        $preRequisitosReserva = $this->montarPreRequisitosReserva(
            $estacoes,
            $gruposVeiculo,
            $modelosAtivos,
            $resumoVeiculosEstacoes
        );
        $moedaEmpresa = 'BRL';
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $paises = $this->paisService->getAllForSelectSigla();
        $empresaAtual = $this->gatewayEmpresaConfigService->empresaAtual();
        if ($empresaAtual) {
            $moedaEmpresa = $this->gatewayEmpresaConfigService->moeda($empresaAtual);
        }
        $simboloMoedaEmpresa = $this->simboloMoeda($moedaEmpresa);

        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome', 'asc');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('reserva.add-edit', compact(

            'breadcrumb',
            'status',
            'metodo',
            'rota',
            'users',
            'gruposVeiculo',
            'estacoes',
            'preRequisitosReserva',
            'moedaEmpresa',
            'simboloMoedaEmpresa',
            'tiposOpcionais',
            'tiposUsuario',
            'paises'
        ))
            ->with('titulo', $tituloFormulario);
    }

    public function store(StoreReservaRequest $request)
    {
        if ($redirect = $this->validarPreRequisitosReservaOuRedirecionar('create')) {
            return $redirect;
        }

        // Recebe os dados da reserva em formato JSON
        $reservaData = json_decode($request->reservaHidden);

        // Processa os dados da reserva
        $data = $this->reservaService->processarDadosReserva($reservaData);

        // Cria a reserva com os dados processados
        $reserva = $this->reservaService->create($data);

        // Adiciona um log da ação realizada
        $json = json_encode([
            'acao' => 'Reserva criada',
            'data' => $data,
        ]);
        $this->reservaService->adicionaLog($reserva->id, auth()->user()->id, $json);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }



    public function show($id)
    {
        $titulo = "Detalhes da Reserva";
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadcrumbItem('Home', route('home')),
            new BreadcrumbItem('Reservas', route('reserva.index')),
            new BreadcrumbItem('Detalhes da Reserva Nº' . $id)
        ]);
        $reserva = $this->reservaService->getById($id,'contratoCheckin');

        return view('reserva.show', compact('reserva', 'breadcrumb', 'titulo'));
    }

    public function edit($id)
    {
        $tituloFormulario = 'Cadastro de Reservas';
        $reserva = $this->reservaService->getById($id, ['opcionais', 'cupomDesconto']);
        $reserva->opcionais = $reserva->opcionais->map(function ($opcional) {
            return [
                'id' => $opcional->id,
                'nome' => $opcional->nome,
                'preco_total' => $opcional->pivot->preco_total,
                'quantidade' => $opcional->pivot->quantidade
            ];
        });

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Reservas', 'reserva.index', "Editar: $reserva->id");
        [$metodo, $rota] = $this->getFormMethodAndRoute($reserva);

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForSelect();
        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $modelosAtivos = $this->modeloService->getAllAtivos();
        $resumoVeiculosEstacoes = $this->montarResumoVeiculosPorEstacao($estacoes);
        $preRequisitosReserva = $this->montarPreRequisitosReserva(
            $estacoes,
            $gruposVeiculo,
            $modelosAtivos,
            $resumoVeiculosEstacoes
        );
        $moedaEmpresa = 'BRL';
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $paises = $this->paisService->getAllForSelectSigla();
        $empresaAtual = $this->gatewayEmpresaConfigService->empresaAtual();
        if ($empresaAtual) {
            $moedaEmpresa = $this->gatewayEmpresaConfigService->moeda($empresaAtual);
        }
        $simboloMoedaEmpresa = $this->simboloMoeda($moedaEmpresa);

        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome', 'asc');

        $modelos = $reserva->modelos?->pluck('nome', 'id') ?? [];
        $marcas = $reserva->modelos->marcas ?? [];
        $segmentos = $reserva->modelos->segmentos ?? [];


        $opcionaisSelecionados = $reserva->opcionais?->pluck('id')->toArray() ?? [];
        // dd($reserva);

        return view('reserva.add-edit', compact(
            'opcionaisSelecionados',
            'users',
            'estacoes',
            'gruposVeiculo',
            'preRequisitosReserva',
            'moedaEmpresa',
            'simboloMoedaEmpresa',
            'tiposOpcionais',
            'tiposUsuario',
            'paises',
            'modelos',
            'marcas',
            'segmentos',
            'reserva',
            'breadcrumb',
            'status',
            'metodo',
            'rota'
        ))->with('titulo', $tituloFormulario);
    }

    private function montarPreRequisitosReserva(
        $estacoes,
        $gruposVeiculo,
        $modelosAtivos,
        array $resumoVeiculosEstacoes = []
    ): array
    {
        $qtdEstacoes = $this->contarItens($estacoes);
        $qtdGrupos = $this->contarItens($gruposVeiculo);
        $qtdModelos = $this->contarItens($modelosAtivos);
        $qtdVeiculosTotal = (int) ($resumoVeiculosEstacoes['total'] ?? 0);
        $qtdEstacoesComVeiculo = (int) ($resumoVeiculosEstacoes['estacoes_com_veiculo'] ?? 0);
        $detalhesVeiculosPorEstacao = $resumoVeiculosEstacoes['detalhes'] ?? [];

        $faltas = [];
        $user = Auth::user();
        $semPermissaoDeEstacao = $user && !$user->hasAcessoTodasEstacoes() && empty($user->estacoesPermitidasIds());

        if ($semPermissaoDeEstacao) {
            $faltas[] = 'Seu usuario nao possui estacoes permitidas. Solicite ao administrador acesso a pelo menos uma estacao.';
        } elseif ($qtdEstacoes <= 0) {
            $faltas[] = 'Cadastre pelo menos uma estacao ativa para iniciar reservas.';
        }

        if ($qtdEstacoes > 0 && $qtdGrupos <= 0) {
            $faltas[] = 'Cadastre pelo menos um grupo de veiculos ativo.';
        }

        if ($qtdEstacoes > 0 && $qtdGrupos > 0 && $qtdModelos <= 0) {
            $faltas[] = 'Cadastre pelo menos um modelo ativo para os grupos de veiculos.';
        }

        if ($qtdEstacoes > 0 && $qtdGrupos > 0 && $qtdModelos > 0 && $qtdVeiculosTotal <= 0) {
            $faltas[] = 'Nao ha veiculos ativos nas estacoes permitidas. Cadastre ou libere pelo menos um veiculo para reservar.';
        }

        return [
            'qtd_estacoes' => $qtdEstacoes,
            'qtd_grupos' => $qtdGrupos,
            'qtd_modelos' => $qtdModelos,
            'qtd_veiculos_total' => $qtdVeiculosTotal,
            'qtd_estacoes_com_veiculo' => $qtdEstacoesComVeiculo,
            'detalhes_veiculos_estacao' => $detalhesVeiculosPorEstacao,
            'faltas' => $faltas,
            'pode_reservar' => empty($faltas),
        ];
    }

    private function simboloMoeda(string $moeda): string
    {
        return match (strtoupper($moeda)) {
            'USD' => '$',
            'EUR' => '€',
            default => 'R$',
        };
    }

    private function contarItens($itens): int
    {
        if (is_array($itens)) {
            return count($itens);
        }

        if (is_object($itens) && method_exists($itens, 'count')) {
            return (int) $itens->count();
        }

        if ($itens instanceof \Countable) {
            return count($itens);
        }

        return empty($itens) ? 0 : 1;
    }

    private function montarResumoVeiculosPorEstacao($estacoes): array
    {
        $estacoesArray = [];
        if (is_array($estacoes)) {
            $estacoesArray = $estacoes;
        } elseif (is_object($estacoes) && method_exists($estacoes, 'toArray')) {
            $estacoesArray = (array) $estacoes->toArray();
        } elseif ($estacoes instanceof \Traversable) {
            $estacoesArray = iterator_to_array($estacoes);
        }

        $estacaoIds = array_map('intval', array_keys($estacoesArray));
        $quantidadesPorEstacao = $this->veiculoService->getQuantidadeVeiculosAtivosPorEstacao($estacaoIds);

        $detalhes = [];
        foreach ($estacoesArray as $estacaoId => $nomeEstacao) {
            $id = (int) $estacaoId;
            $detalhes[] = [
                'label' => (string) $nomeEstacao,
                'value' => (int) ($quantidadesPorEstacao[$id] ?? 0),
            ];
        }

        $total = array_sum(array_map('intval', $quantidadesPorEstacao));
        $estacoesComVeiculo = count(array_filter(
            $quantidadesPorEstacao,
            static fn ($quantidade): bool => (int) $quantidade > 0
        ));

        return [
            'quantidades' => $quantidadesPorEstacao,
            'detalhes' => $detalhes,
            'total' => (int) $total,
            'estacoes_com_veiculo' => (int) $estacoesComVeiculo,
        ];
    }

    public function update(UpdateReservaRequest $request, $id)
    {
        if ($redirect = $this->validarPreRequisitosReservaOuRedirecionar('edit', (int) $id)) {
            return $redirect;
        }

        // Recebe os dados da reserva em formato JSON
        $reservaData = json_decode($request->reservaHidden);

        // Processa os dados da reserva
        $data = $this->reservaService->processarDadosReserva($reservaData);

        // Atualiza a reserva com os dados processados
        $this->reservaService->update($id, $data);

        // Adiciona um log da ação realizada
        $json = json_encode([
            'acao' => 'Reserva atualizada',
            'data' => $data,
        ]);
        $this->reservaService->adicionaLog($id, auth()->user()->id, json: $json);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        // $this->reservaService->delete($id);
        // return $this->redirecionaRotaRedirect($this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Reserva não pode ser excluida', 'error');
    }


    public function cancelar($id)
    {
        $this->reservaService->cancelar($id);
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
        // Salva o valor de paginação na sessão
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a página de listagem
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'reserva');
        session()->put("$this->nomeFiltro.rotaRedirect", 'reserva.index');
    }

    public function obterFiltrosPrincipais()
    {
        
        // $locais = $this->estacaoService->getAllAtivos();
        // $locaisPossiveis = [];
        // $locaisPossiveis[] = [
        //     'value' =>'',
        //     'label' => 'Todos'
        // ];
        // foreach ($locais as $id => $local) {
        //     $locaisPossiveis[] = [
        //         'value' => $id,
        //         'label' => $local
        //     ];
        // }
        // //option 
        // $statusReserva = $this->statusReservaService->getAllForSelect();
        // $statusPossiveis = [];
        // $statusPossiveis[] = [
        //     'value' =>'',
        //     'label' => 'Todos'
        // ];
        // foreach ($statusReserva as $id => $status) {
        //     $statusPossiveis[] = [
        //         'value' => $id,
        //         'label' => $status
        //     ];
        // }

        // $usuarios = $this->userService->getAllForSelect();
        // $usuariosPossiveis = [];
        // $usuariosPossiveis[] = [
        //     'value' =>'',
        //     'label' => 'Todos'
        // ];
        // foreach ($usuarios as $id => $usuario) {
        //     $usuariosPossiveis[] = [
        //         'value' => $id,
        //         'label' => $usuario
        //     ];
        // }
        // return [
        //     ['name' => 'retirada', 'label' => 'Retirada', 'type' => 'select', 'typeWhere' => 'where', 'options' =>$locaisPossiveis],
        //     ['name' => 'devolucao', 'label' => 'Devolução', 'type' => 'select', 'typeWhere' => 'where', 'options' =>$locaisPossiveis],
        //     ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => $statusPossiveis],
        //     ['name' => 'usuario', 'label' => 'Usuário', 'type' => 'select', 'typeWhere' => 'where', 'options' => $usuariosPossiveis],
        //     ['name' => 'data_inicio', 'label' => 'Data Início', 'type' => 'date', 'typeWhere' => 'where'],
        //     ['name' => 'data_fim', 'label' => 'Data Fim', 'type' => 'date', 'typeWhere' => 'where'],
        // ];
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
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
            // Adicione filtros secundários aqui, se necessário
        ];
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Reserva|null  $reserva
     * @return array
     */
    private function getFormMethodAndRoute($reserva = null)
    {
        if ($reserva) {
            return [
                'PUT',
                route('reserva.update', $reserva->id),
            ];
        }

        return [
            'POST',
            route('reserva.store'),
        ];
    }

    private function validarPreRequisitosReservaOuRedirecionar(string $contexto, ?int $reservaId = null)
    {
        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');
        $modelosAtivos = $this->modeloService->getAllAtivos();
        $resumoVeiculosEstacoes = $this->montarResumoVeiculosPorEstacao($estacoes);
        $preRequisitosReserva = $this->montarPreRequisitosReserva(
            $estacoes,
            $gruposVeiculo,
            $modelosAtivos,
            $resumoVeiculosEstacoes
        );
        if ($preRequisitosReserva['pode_reservar']) {
            return null;
        }

        $faltas = implode(' ', $preRequisitosReserva['faltas']);
        if ($contexto === 'edit' && $reservaId) {
            return redirect()
                ->route('reserva.edit', $reservaId)
                ->withInput()
                ->withErrors(['reserva' => trim('Nao foi possivel salvar a reserva. ' . $faltas)]);
        }

        return redirect()
            ->route('reserva.create')
            ->withInput()
            ->withErrors(['reserva' => trim('Nao foi possivel criar a reserva. ' . $faltas)]);
    }


    public function atualizaStatusReservas()
    {
        // Rotina de atraso das reservas confirmadas e nao retiradas
        $this->reservaService->atualizarStatusReservasConfirmadasNaoRetiradasParaAtrasadas();

        // Rotina de expiracao das reservas atrasadas
        $this->reservaService->atualizarStatusReservasAtrasadasParaExpiradas();

        // Rotina de expiracao de reservascriadas
        $this->reservaService->atualizarStatusReservasCriadasParaExpiradas();

        return response()->json(['message' => 'Status das reservas atualizados com sucesso']);
        // $this->reservaService->atualizarStatusReservasPagasNaoRetiradasParaAtrasadas();
        // $this->reservaService->cancelarReservasAguardandoPagamentoExpiradas();
    }

    public function obterOpcionais(Request $request)
    {

        $opcionaisSelecionados = isset($request->opcionais) ? $request->opcionais : [];
        // dd($opcionaisSelecionados);

        $quantidade_dias = $this->reservaService->calculaQuantidadeDias(
            $request->data_inicio,
            $request->data_fim
        );

        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($request->grupo_frota_id);
        // dd($opcionais);

        return view('reserva.components.opcionais', ['tiposOpcionais' => $opcionais, 'quantidade_dias' => $quantidade_dias, 'opcionaisSelecionados' => $opcionaisSelecionados]);
    }

    public function veiculosDisponiveis(Request $request)
    {
        $grupoFrota = $this->grupoFrotaService->getById($request->grupo_frota_id);
        $qtdVeiculosEstacao = $this->veiculoService->getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
            (int) $request->estacao_retirada_id,
            (string) $request->data_inicio,
            (string) $request->data_fim
        );

        $modelos = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $request->grupo_frota_id,
            $request->estacao_retirada_id,
            $request->data_inicio,
            $request->data_fim
        );
        $modelosVinculados = collect($this->grupoFrotaService->getModelosPorGrupoFrota($request->grupo_frota_id));
        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($request->grupo_frota_id);
        $avisoModelos = null;

        if ($modelos->isEmpty()) {
            $avisoModelos = $modelosVinculados->isNotEmpty()
                ? 'Este grupo possui modelos vinculados, mas nao ha veiculos disponiveis para a estacao/data selecionadas.'
                : 'Este grupo de veiculos ainda nao possui modelos vinculados.';
        }


        return response()->json([
            'grupo_frota' => $grupoFrota,
            'modelo' => $modelos,
            'tipo_opcional' => $opcionais,
            'aviso_modelos' => $avisoModelos,
            'qtd_veiculos_estacao' => $qtdVeiculosEstacao,
        ]);
    }
    public function efetuarPagamento($id)
    {

        $reserva = $this->reservaService->getById($id);
        if ($reserva->status_reserva_id != 1) { // Verifica se a reserva está com status Criada
            return redirect()->route('reserva.show', $id)->with('error', 'Reserva não pode ser paga');
        }

        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (!$empresa) {
            return redirect()->route('reserva.show', $id)->with('error', 'Empresa da reserva não encontrada para configuração de pagamento.');
        }

        $gatewayPadrao = $this->gatewayEmpresaConfigService->gateway($empresa);
        $moedaPadrao = $this->gatewayEmpresaConfigService->moeda($empresa);
        $gatewayNome = [
            'stripe' => 'Stripe',
            'mercado_livre' => 'Mercado Livre',
            'paypal' => 'PayPal',
        ][$gatewayPadrao] ?? strtoupper($gatewayPadrao);

        $titulo = "Efetuar Pagamento da Reserva";

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadcrumbItem('Home', route('home')),
            new BreadcrumbItem('Reservas', route('reserva.index')),
            new BreadcrumbItem('Efetuar Pagamento da Reserva Nº' . $id)
        ]);

        $user = $this->userService->getById($reserva->user_id);
        $metodosPagamento = [];
        if ($gatewayPadrao === 'stripe' && !empty($user->stripe_id)) {
            $stripeSecret = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
            if ($stripeSecret !== '') {
                $metodosPagamento = $this->clienteStripeService->getPaymentMethods($user->stripe_id, $stripeSecret);
            }
        }

        return view(
            'reserva.efetuar-pagamento',
            compact('reserva', 'breadcrumb', 'titulo', 'metodosPagamento', 'gatewayPadrao', 'gatewayNome', 'moedaPadrao')
        );
    }

    public function processarPagamento(Request $request, $id)
    {
        $reserva = $this->reservaService->getById($id);
        $valorTotal = (float) $reserva->valor_total_final;

        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (!$empresa) {
            return redirect()->route('reserva.show', $id)->with('error', 'Empresa da reserva não encontrada para configuração de pagamento.');
        }

        $userReserva = $this->userService->getById($reserva->user_id);
        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

        if ($gateway === 'stripe' && empty($request->payment_method)) {
            return redirect()->back()->with('error', 'Selecione um método de pagamento para Stripe.');
        }

        $payload = array_merge([
            'reserva_id' => $reserva->id,
            'valor' => $valorTotal,
            'currency' => $currency,
            'payment_method' => (string) $request->input('payment_method', ''),
            'customer_id' => (string) ($userReserva->stripe_id ?? ''),
            'descricao' => 'Pagamento da reserva #' . $reserva->id,
            'payer_email' => (string) ($userReserva->email ?? ''),
        ], $credenciais);

        $resultado = $this->gerenciadorDePagamentos
            ->resolver($gateway)
            ->cobrarPagamento($payload);

        if (($resultado['status'] ?? 'error') !== 'success') {
            return redirect()->route('reserva.show', $id)->with('error', $resultado['message'] ?? 'Pagamento não efetuado');
        }

        DB::table('reservas')->where('id', $id)->update(['status_reserva_id' => 2, 'pagamento_efetuado' => 'S']);

        Transacao::create([
            'user_id' => $reserva->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reserva->id,
            'valor' => $valorTotal,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'metodo_pagamento' => $gateway,
            'gateway_transaction_id' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'info_pagamento' => $resultado,
        ]);

        $json = json_encode([
            'acao' => 'Pagamento efetuado',
            'valor_total' => $valorTotal,
            'gateway' => $gateway,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'usuario_pagador_id' => Auth::id(),
            'customer_id' => $resultado['customer_id'] ?? null,
            'id_pagamento' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'data_pagamento' => date('Y-m-d H:i:s'),
        ]);
        $this->reservaService->adicionaLog($id, (int) Auth::id(), $json);

        return redirect()->route('reserva.show', $id)->with('success', 'Pagamento efetuado com sucesso');
    }
    public function checkin(Request $request, $reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id,['modelos']);

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();
        $modelos = $this->modeloService->obtemModelosPorGrupoFrota($reserva->grupo_frota_id);

        $marcas_id = array_unique($modelos->pluck('marca_id')->toArray() );

        $marcas = $this->marcaService->getAll(['id' => $marcas_id]);

        $veiculos = $this->veiculoService->getVeiculosDisponiveisParaCheckin($reserva->estacao_retirada_id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadcrumbItem('Home', route('home')),
            new BreadcrumbItem('Reservas', route('reserva.index')),
            new BreadcrumbItem('Detalhes da Reserva Nº' . $reserva_id, route('reserva.show', $reserva_id)),
            new BreadcrumbItem('Check-in')
        ]);

        return view(
            'reserva.checkin',
            compact(
                'reserva', 'marcas', 'modelos','veiculos','breadcrumb',
                    'categoriaDanos'
                )
        )
            ->with('titulo', 'Check-in da reserva Nº ' . $reserva->id);
    }

    public function verContratoCheckin(Request $request, $reserva_id){

        // Carrega a reserva com todos os relacionamentos necessários
    $reserva = $this->reservaService->getById($reserva_id, [
        'usuario', // para dados do cliente
        // 'condutor', // para dados do condutor
        'veiculo', // para dados do veículo
        'grupoFrota', // para dados do grupo
        'estacaoRetirada', // para local de retirada
        'estacaoDevolucao', // para local de devolução
        'opcionais', // para itens de faturação
        'contratoCheckin', // para dados do contrato
        'veiculoDano'
        // 'contratoCheckin.veiculoDanos' // para estado da viatura
    ]);
    // dd( $reserva->contratoCheckin->veiculo->modelo->gruposFrota[0]->nome );

    $data = [
        'documento' => [
            'codigo_interno' => 'ARCS-' . date('y'), // Você pode gerar isso
            'atcud' => 'JJNXXNNC-' . date('y'), // Você pode gerar isso
            'data_emissao' => now()->format('Y-m-d H:i:s'),
            'empresa' => [
                'nome' => config('app.empresa.nome'),
                'endereco' => config('app.empresa.endereco'),
                'site' => config('app.empresa.site'),
                'instagram' => config('app.empresa.instagram'),
            ],
        ],
        'contrato' => [
            'numero' => '#' . str_pad($reserva->id, 5, '0', STR_PAD_LEFT),
            'titulo' => 'CONTRATO DE ALUGUEL',
            'subtitulo' => 'CONDIÇÕES PARTICULARES DO CONTRATO DE ALUGUEL',
        ],
        'cliente' => [
            // 'nif' => $reserva->user->nif,
            'nome' => $reserva->usuario->nome,
            'endereco' => $reserva->usuario->endereco,
            'pais' => $reserva->usuario->pais,
            'apolice' => $reserva->usuario->apolice,
        
            'nif' => $reserva->usuario->nif,
            'nome' => $reserva->usuario->nome,
            'email' => $reserva->usuario->email,
            'telefone' => $reserva->usuario->telefone,
            'passaporte' => $reserva->usuario->passaporte ?? '<br>',
            'local_emissao' => $reserva->usuario->local_emissao,
            'data_nascimento' => $reserva->usuario->data_nascimento,
            'local_nascimento' => $reserva->usuario->local_nascimento,
            'carta_conducao' => [
                'pais' => $reserva->usuario->pais_emissao,
                'numero' => $reserva->usuario->numero_carta_conducao,
                'data_emissao' => $reserva->usuario->data_emissao_carta_conducao,
                'validade' => $reserva->usuario->data_validade_carta_conducao,
            ],
        ],
        'veiculo' => [
            'matricula' => $reserva->contratoCheckin->veiculo->id,
            'marca' => $reserva->contratoCheckin->marca->nome,
            'modelo' => $reserva->contratoCheckin->modelo->nome,
            'grupo' => $reserva->contratoCheckin->veiculo->modelo->gruposFrota[0]->nome,
            'quilometros' => $reserva->contratoCheckin->quilometragem_atual,
            'combustivel' => $reserva->contratoCheckin->nivel_combustivel,
            'taxas' => [
                // 'combustivel_em_falta' => '€' . number_format($reserva->taxa_combustivel, 2) . ' + IVA / litro',
                // 'taxa_reabastecimento' => '€' . number_format($reserva->taxa_reabastecimento, 2) . ' + IVA',
                'combustivel_em_falta' => '€ 0',
                'taxa_reabastecimento' => '€ 0',
            ],
            // 'franquia' => '€' . number_format($reserva->franquia, 2),
            // 'kms_permitidos' => $reserva->kms_permitidos ?: 'ilimitado',
            'franquia' => '€ 0',
            'kms_permitidos' => $reserva->kms_permitidos ?: 'ilimitado',
        ],
        'reserva' => [
            'alugado_em' => [
                'local' => $reserva->estacaoRetirada->nome,
                'data' => Carbon::parse($reserva->data_inicio)->format('Y-m-d'),
                'hora' => Carbon::parse($reserva->data_inicio)->format('H:i'),
            ],
            'devolver_em' => [
                'local' => $reserva->estacaoDevolucao->nome,
                'data' => Carbon::parse($reserva->data_fim)->format('Y-m-d'),
                'hora' => Carbon::parse($reserva->data_fim)->format('H:i'),
            ],
        ],
        'faturacao' => [
            'dias_unidades' => $reserva->quantidade_dias,
            // 'itens' => $this->obterItensFaturacao($reserva),
            'iva_total' => '€ 0',
            'valor_total' => '€ 0',
            // 'iva_total' => '€' . number_format($reserva->valor_iva, 2),
            // 'valor_total' => '€' . number_format($reserva->valor_total_final, 2),
        ],
        'estado_viatura' => [
            'itens' => $reserva->veiculoDano->map(function ($dano) {
                return [
                    'local'=> ucfirst($dano->local_dano).':',
                    'localizacao'=>$dano->localizacao_dano,
                    'tipo_dano'=>$dano->observacoes == null ? '' : '('.$dano->tipo_dano.')',
                    'observacoes'=>$dano->observacoes
                ];
            })->toArray(),
        ],
        'funcionario' => [
            'nome' => Auth::user()->nome,
        ],
        'opcionais' => $reserva->opcionais->map(function ($opcional) {
                return [
                    'nome' => $opcional->nome,
                    'valor_total' => "€ " . number_format($opcional->pivot->preco_total, 2),
                    'preco_unitario' => $opcional->preco_por_dia ? "€ " . number_format($opcional->preco_por_dia , 2) :"€ " . number_format($opcional->preco_integral , 2) ,
                    'quantidade' => $opcional->pivot->quantidade,
                ];
            })->toArray(),
            'assinatura'=> $reserva->contratoCheckin->assinatura,
    ];

    // dd($reserva);

    // dd($data);
    // Percorrer todos os atributos de data e caso não tenha valor, substituir por <br>
    $this->tratarCamposData($data);

       $this->pdfService->gerarCheckinPdf($data);
    }
    
    public function assinarContratoCheckin(Request $request, $reserva_id)
    {
       
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadcrumbItem('Home', route('home')),
            new BreadcrumbItem('Reservas', route('reserva.index')),
            new BreadcrumbItem('Detalhes da Reserva Nº' . $reserva_id, route('reserva.show', $reserva_id)),
            new BreadcrumbItem('Assinar Contrato de Check-in')
        ]);

        $reserva = $this->reservaService->getById($reserva_id,['contratoCheckin']);
        return view('reserva.assinar-contrato-checkin', compact('reserva','breadcrumb'))->with('titulo', 'Assinar contrato de check-in da reserva Nº ' . $reserva->id);
    }

    public function salvarAssinarContratoCheckIn(AssinarContratoRequest $request, $reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;
        
        $contrato = $this->contratoCheckinService->getById($dados['contrato_id']);
        
        if ($contrato) {
            try {
                $reserva = $this->reservaService->getById($reserva_id);
                $this->contratoCheckinService->assinarDocumento($dados['contrato_id'], $dados['assinatura']);
                $this->reservaService->checkInRealizado($reserva_id, $contrato->veiculo_id);
                $this->movimentacaoService->realizarCheckIn($dados['veiculo_id'], $reserva->estacao_retirada_id, $reserva_id);
                $this->reservaService->adicionaLog($reserva_id, Auth::id(), json_encode(['acao' => 'Contrato de check-in assinado']));
                
                return response()->json(['success' => true, 'message' => 'Contrato assinado com sucesso']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Erro ao assinar contrato: ' . $e->getMessage()], 500);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Contrato de check-in não encontrado'], 404);
    }

    public function criarContratoCheckIn(CriarContratoCheckInRequest $request,$reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;

        try{
            $this->reservaService->adicionaLog($reserva_id,Auth::id(),json_encode(['acao' => 'Contrato de check-in criado']));
            $contrato = $this->contratoCheckinService->create($dados);
            return response()->json($contrato);

        }catch(\Exception $e){
            throw new \Exception('Erro ao criar contrato de check-in');
        }
    }

    public function adicionarDanoVeiculo(AdicionarDanoVeiculoRequest $request,$reserva_id)
    {
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;

        if(isset($dados['fotos'])){
            $fotos = $dados['fotos'];
            unset($dados['fotos']);
        }

        $dano = $this->veiculoDanoService->create($dados);
        return response()->json($dano);
    }
 
      /**
     * Percorre recursivamente o array e substitui valores nulos em campos de data por <br>
     * 
     * @param array &$data Array de dados a ser processado
     */
    private function tratarCamposData(&$data) 
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->tratarCamposData($value);
            } else {
                $value = $this->convertBrCasoValorSejaNulo($value);
            }
        }
    }
    private function convertBrCasoValorSejaNulo($valor){
        return !isset($valor) || is_null($valor) ? '<br>' : $valor;
    }
}

