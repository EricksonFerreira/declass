<?php

namespace App\Http\Controllers;

use App\DTO\BreadcrumbItem;
use App\Exceptions\CartaoNaoCadastradoException;
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Http\Requests\Reserva\UpdateReservaRequest;
use App\Services\BreadcrumbService;
use App\Services\ClienteStripeService;
use App\Services\EstacaoService;
use App\Services\GrupoFrotaService;
use App\Services\ModeloService;
use App\Services\OpcionalService;
use App\Services\ReservaService;
use App\Services\TipoOpcionalService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\LogTrait;
use DB;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

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

    protected $modeloService;
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
        ClienteStripeService $clienteStripeService
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
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $reservas = $this->reservaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

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
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Criar');

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForSelect();
        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id','nome','valor_diario',null,'asc');

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
            'tiposOpcionais'
        ))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreReservaRequest $request)
    {
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
        $reserva = $this->reservaService->getById($id);
        $reserva->total_opcional = 0;
        foreach($reserva->opcionais as $opcional){
            // dd($opcional->pivot);
            // $opcional->pivot->preco_total = $opcional->pivot->preco_total ?? 0;
            $reserva->total_opcional += $opcional->pivot->preco_total;
        }
        return view('reserva.show', compact('reserva','breadcrumb','titulo'));
    }

    public function edit($id)
    {
        $reserva = $this->reservaService->getById($id,['opcionais','cupomDesconto']);
        $reserva->opcionais = $reserva->opcionais->map(function($opcional) {
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
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id','nome','valor_diario',null,'asc');

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
            'tiposOpcionais',
            'modelos',
            'marcas',
            'segmentos',
            'reserva',
            'breadcrumb',
            'status',
            'metodo',
            'rota'
        ))->with('titulo', $this->titulo);
    }

    public function update(UpdateReservaRequest $request, $id)
    {
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


    public function atualizaStatusReservas()
    {
        // Rotina de atraso das reservas confirmadas e nao retiradas
        $this->reservaService->atualizarStatusReservasConfirmadasNaoRetiradasParaAtrasadas();

        // Rotina de expiracao das reservas atrasadas
        $this->reservaService->atualizarStatusReservasAtrasadasParaExpiradas();

        // Rotina de expiracao de reservascriadas
        $this->reservaService->atualizarStatusReservasCriadasParaExpiradas();

        // $this->reservaService->atualizarStatusReservasPagasNaoRetiradasParaAtrasadas();
        // $this->reservaService->cancelarReservasAguardandoPagamentoExpiradas();
    }

    public function obterOpcionais(Request $request){

        $opcionaisSelecionados = isset($request->opcionais) ? $request->opcionais : [];
        // dd($opcionaisSelecionados);

        $quantidade_dias = $this->reservaService->calculaQuantidadeDias(
            $request->data_inicio,
            $request->data_fim
        );

        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($request->grupo_frota_id);
        // dd($opcionais);

        return view('reserva.components.opcionais', ['tiposOpcionais'=>$opcionais,'quantidade_dias'=>$quantidade_dias, 'opcionaisSelecionados' => $opcionaisSelecionados]);

    }

    public function veiculosDisponiveis(Request $request)
    {
        $grupoFrota = $this->grupoFrotaService->getById($request->grupo_frota_id);


        $modelos = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $request->grupo_frota_id,
            $request->estacao_retirada_id,
            $request->data_inicio,
            $request->data_fim
        );
        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($request->grupo_frota_id);


        return response()->json([
            'grupo_frota' => $grupoFrota,
            'modelo' => $modelos,
            'tipo_opcional' => $opcionais,
        ]);
    }
    public function efetuarPagamento($id)
    {

        $reserva = $this->reservaService->getById($id);
        if($reserva->status_reserva_id != 1){ // Verifica se a reserva está com status Criada
            return redirect()->route('reserva.show', $id)->with('error', 'Reserva não pode ser paga');
        }

        $titulo = "Efetuar Pagamento da Reserva";

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadcrumbItem('Home', route('home')),
            new BreadcrumbItem('Reservas', route('reserva.index')),
            new BreadcrumbItem('Efetuar Pagamento da Reserva Nº' . $id)
        ]);

        $user = $this->userService->getById($reserva->user_id);
        $metodosPagamento = $this->clienteStripeService->getPaymentMethods($user->stripe_id);

        return view('reserva.efetuar-pagamento',
            compact('reserva','breadcrumb','titulo','metodosPagamento'));
    }

    public function processarPagamento(Request $request, $id)
    {
        $paymentMethodId = $request->payment_method;
        $reserva = $this->reservaService->getById($id);
        $valorTotal = $reserva->valor_total;

        $user = $this->userService->getById($reserva->user_id);
        $charge = $this->clienteStripeService->createCharge($paymentMethodId, $valorTotal, $user->stripe_id);

        if($charge->status == 'succeeded'){
            DB::table('reservas')->where('id', $id)->update(['status_reserva_id' => 2,'pagamento_efetuado' => 'S']);
            $json = json_encode([
                'acao' => 'Pagamento efetuado',
                'valor_total' => $valorTotal,
                'customer_id' => $charge->customer,
                'currency' => $charge->currency,
                'id_pagamento' => $charge->id,
                'status' => $charge->status,
                'data_pagamento' => date('Y-m-d H:i:s'),
            ]);
            $this->reservaService->adicionaLog($id, $user->id, $json);
            return redirect()->route('reserva.show', $id)->with('success', 'Pagamento efetuado com sucesso');
        }else{
            return redirect()->route('reserva.show', $id)->with('error', 'Pagamento não efetuado');
        }
    }


}
