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
use App\Services\PdfService;
use App\Services\ReservaService;
use App\Services\TipoOpcionalService;
use App\Services\UsuarioService;
use App\Services\VeiculoDanoService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\LogTrait;
use DB;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
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
        PdfService $pdfService
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
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Criar');

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForSelect();
        $estacoes = $this->estacaoService->getAllAtivos();
        $gruposVeiculo = $this->grupoFrotaService->getAllAtivosForSelectSubText('id', 'nome', 'valor_diario', null, 'asc');

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
        $reserva = $this->reservaService->getById($id,'contratoCheckin');

        return view('reserva.show', compact('reserva', 'breadcrumb', 'titulo'));
    }

    public function edit($id)
    {
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
        if ($reserva->status_reserva_id != 1) { // Verifica se a reserva está com status Criada
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

        return view(
            'reserva.efetuar-pagamento',
            compact('reserva', 'breadcrumb', 'titulo', 'metodosPagamento')
        );
    }

    public function processarPagamento(Request $request, $id)
    {
        $paymentMethodId = $request->payment_method;
        $reserva = $this->reservaService->getById($id);
        $valorTotal = $reserva->valor_total_final;

        $user = $this->userService->getById($reserva->user_id);
        $charge = $this->clienteStripeService->createCharge($paymentMethodId, $valorTotal, $user->stripe_id);

        if ($charge->status == 'succeeded') {
            DB::table('reservas')->where('id', $id)->update(['status_reserva_id' => 2, 'pagamento_efetuado' => 'S']);
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
        } else {
            return redirect()->route('reserva.show', $id)->with('error', 'Pagamento não efetuado');
        }
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
    public function assinarContratoCheckin(Request $request, $reserva_id)
    {
        $data = [
            'documento' => [
                'codigo_interno' => 'ARCS-24',
                'atcud' => 'JJNXXNNC-24',
                'data_emissao' => '2025-01-27 04:19:18',
                'empresa' => [
                    'nome' => 'Xpand Cars Lda',
                    'endereco' => 'Rua Dr. Barata Salgueiro, 19 - Samouco - 2890-238',
                    'site' => 'www.xpandcars.com',
                    'instagram' => '@xpandcars',
                ],
            ],
            'contrato' => [
                'numero' => '#00036',
                'titulo' => 'CONTRATO DE ALUGUER',
                'subtitulo' => 'A SUA VIAGEM COMEÇA AQUI.',
            ],
            'cliente' => [
                'nif' => '2331313123',
                'nome' => 'erickson',
                'endereco' => 'saf 1213-123 sf',
                'pais' => 'Portugal',
                'apolice' => '207198624',
            ],
            'condutor' => [
                'nif' => '2331313123',
                'nome' => 'erickson',
                'email' => '123@123.com',
                'telefone' => '1231',
                'passaporte' => '1323213',
                'local_emissao' => '2313131',
                'data_nascimento' => '2025-01-24',
                'local_nascimento' => 'Portugal',
                'carta_conducao' => [
                    'numero' => '3123231',
                    'data_emissao' => '2025-01-24',
                    'validade' => '2051-02-17',
                ],
            ],
            'veiculo' => [
                'matricula' => 'BF-57-VS',
                'modelo' => 'Peugeot 208 Allure Pack 1.2 Puretech 100CV',
                'grupo' => '4-5 Door Manual Unspecified Drive Petrol Air',
                'quilometros' => 2367,
                'combustivel' => 'Cheio (Gasolina 95)',
                'taxas' => [
                    'combustivel_em_falta' => '€1.89 + IVA / litro',
                    'taxa_reabastecimento' => '€30.00 + IVA',
                ],
                'franquia' => '€3,000.00',
                'kms_permitidos' => 'ilimitado',
            ],
            'reserva' => [
                'alugado_em' => [
                    'local' => 'Delivery Lisboa-Centro',
                    'data' => '2025-01-26',
                    'hora' => '21:00',
                ],
                'devolver_em' => [
                    'local' => 'Delivery Lisboa-Centro',
                    'data' => '2025-01-28',
                    'hora' => '21:00',
                ],
            ],
            'faturacao' => [
                'dias_unidades' => 2,
                'itens' => [
                    ['descricao' => 'Tarifa de Aluguer', 'iva' => '23.0%', 'valor' => '€90.00'],
                    ['descricao' => 'Fora de Hora', 'iva' => '23.0%', 'valor' => '€100.00'],
                    ['descricao' => 'Básico', 'iva' => '0%', 'valor' => '€0.00'],
                ],
                'iva_total' => '€35.53',
                'valor_total' => '€190.00',
            ],
            'estado_viatura' => [
                'itens' => [
                    'Pára-choque dianteiro (Ausente)',
                    'Tampões de plástico (Ausente)',
                    'Troca de combustível (Ausente)',
                ],
            ],
            'funcionario' => [
                'nome' => 'Edson Soares',
            ],
        ];
        $this->pdfService->gerarCheckinPdf($data);
        dd(2);

        $reserva = $this->reservaService->getById($reserva_id,['contratoCheckin']);
        return view('reserva.assinar-contrato-checkin', compact('reserva'));
    }
    public function salvarAssinarContratoCheckIn(AssinarContratoRequest $request,$reserva_id){
        $dados = $request->validated();
        $dados['reserva_id'] = $reserva_id;

        $contrato = $this->contratoCheckinService->getById($dados['contrato_id']);
        if($contrato){
            $reserva = $this->reservaService->getById($reserva_id);

            $this->reservaService->adicionaLog($reserva_id,Auth::id(),json_encode(['acao' => 'Contrato de check-in assinado']));
            $this->contratoCheckinService->assinarDocumento($dados['contrato_id']);
            $this->reservaService->checkInRealizado($reserva_id,$contrato->veiculo_id);
            $this->movimentacaoService->realizarCheckIn($dados['veiculo_id'],$reserva->estacao_retirada_id,$reserva_id);
        }
        throw new \Exception('Contrato de check-in não encontrado');
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
}
