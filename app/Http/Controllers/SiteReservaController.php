<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Models\CupomDesconto;
use App\Models\Empresa;
use App\Models\Estacao;
use App\Models\GrupoFrota;
use App\Models\Modelo;
use App\Models\Opcional;
use App\Models\Reserva;
use App\Models\User;
use App\Services\ClienteStripeService;
use App\Services\CupomDescontoService;
use App\Services\GrupoFrotaService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\PaisService;
use App\Services\RegraService;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Support\DomainNormalizer;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SiteReservaController extends Controller
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly GrupoFrotaService $grupoFrotaService,
        private readonly VeiculoService $veiculoService,
        private readonly CupomDescontoService $cupomDescontoService,
        private readonly UsuarioService $usuarioService,
        private readonly RegraService $regraService,
        private readonly ClienteStripeService $clienteStripeService,
        private readonly PaisService $paisService,
        private readonly GatewayEmpresaConfigService $gatewayEmpresaConfigService
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $estacoes = $this->estacoesForEmpresa();
        $gruposFrota = $this->gruposFrotaAtivos();
        $paises = $this->paisService->getAllForSelectSigla();
        $moedaEmpresa = strtoupper($this->gatewayEmpresaConfigService->moeda($empresa));

        return view('site-apresentacao.reserva', [
            'empresa' => $empresa,
            'estacoes' => $estacoes,
            'gruposFrota' => $gruposFrota,
            'paises' => $paises,
            'moedaEmpresa' => $moedaEmpresa,
            'simboloMoedaEmpresa' => $this->simboloMoeda($moedaEmpresa),
            'isAuth' => auth()->check(),
            'authUserName' => auth()->user()?->nome,
            'authUserEmail' => auth()->user()?->email,
            'loginRedirectUrl' => Route::has('site.reserva.login') ? route('site.reserva.login', ['step' => 4]) : null,
            'prefill' => [
                'estacao_retirada_id' => old('estacao_retirada_id', $this->normalizeInt($request->query('retirada'))),
                'estacao_devolucao_id' => old(
                    'estacao_devolucao_id',
                    $this->normalizeInt($request->query('devolucao')) ?? $this->normalizeInt($request->query('retirada'))
                ),
                'data_inicio' => old('data_inicio', $this->buildDateTimeLocal(
                    $request->query('data_retirada'),
                    $request->query('hora_retirada')
                )),
                'data_fim' => old('data_fim', $this->buildDateTimeLocal(
                    $request->query('data_devolucao'),
                    $request->query('hora_devolucao')
                )),
            ],
        ]);
    }

    public function disponibilidade(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $validator = Validator::make($request->all(), [
            'grupo_frota_id' => ['required', 'integer'],
            'estacao_retirada_id' => ['required', 'integer', 'exists:estacoes,id'],
            'estacao_devolucao_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after:data_inicio'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados invalidos para consultar disponibilidade.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataInicio = (string) $request->input('data_inicio');
        $dataFim = (string) $request->input('data_fim');
        if (!$this->dataInicioValida($dataInicio)) {
            return response()->json([
                'message' => 'A data de retirada nao pode ser anterior a agora.',
                'errors' => ['data_inicio' => ['A retirada nao pode ser antes de agora.']],
            ], 422);
        }

        if (!$this->dataFimMinimaValida($dataInicio, $dataFim)) {
            return response()->json([
                'message' => 'A data de devolucao deve ser ao menos 1 dia apos a retirada.',
                'errors' => ['data_fim' => ['A devolucao deve ser ao menos 1 dia apos a retirada.']],
            ], 422);
        }

        $grupoFrotaId = (int) $request->input('grupo_frota_id');
        $grupoFrota = GrupoFrota::query()->where('ativo', 'S')->find($grupoFrotaId);
        if (!$grupoFrota) {
            return response()->json(['message' => 'Grupo de frota nao encontrado.'], 404);
        }

        $estacaoRetiradaId = (int) $request->input('estacao_retirada_id');
        $qtdVeiculosEstacao = $this->veiculoService->getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
            $estacaoRetiradaId,
            $dataInicio,
            $dataFim
        );

        $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $grupoFrotaId,
            $estacaoRetiradaId,
            $dataInicio,
            $dataFim
        );

        $modelosVinculados = collect($this->grupoFrotaService->getModelosPorGrupoFrota($grupoFrotaId));
        $avisoModelos = null;
        if ($modelosDisponiveis->isEmpty()) {
            $avisoModelos = $modelosVinculados->isNotEmpty()
                ? 'Este grupo possui modelos vinculados, mas nao ha veiculos disponiveis para a estacao/data selecionadas.'
                : 'Este grupo de veiculos ainda nao possui modelos vinculados.';
        }

        $tipoOpcionais = $this->normalizeTipoOpcionais(
            $this->grupoFrotaService->getTiposOpcionaisComOpcionais($grupoFrotaId)
        );

        return response()->json([
            'grupo_frota' => [
                'id' => (int) $grupoFrota->id,
                'nome' => (string) $grupoFrota->nome,
                'valor_diario' => (float) $grupoFrota->valor_diario,
            ],
            'modelos' => $modelosDisponiveis->map(function ($modelo): array {
                return [
                    'id' => (int) $modelo->id,
                    'nome' => (string) $modelo->nome,
                ];
            })->values()->all(),
            'tipo_opcional' => $tipoOpcionais,
            'aviso_modelos' => $avisoModelos,
            'qtd_veiculos_estacao' => $qtdVeiculosEstacao,
        ]);
    }

    public function verificarCupom(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa nao encontrada para este dominio.'], 404);
        }

        $this->activateEmpresaContext($empresa);

        $validated = Validator::make($request->all(), [
            'cupom' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $codigo = trim((string) ($validated['cupom'] ?? ''));
        if ($codigo === '') {
            return response()->json(['cupom' => null]);
        }

        $cupom = $this->resolveCupomValido($codigo);
        if (!$cupom) {
            return response()->json(['cupom' => null]);
        }

        return response()->json([
            'cupom' => [
                'id' => (int) $cupom->id,
                'nome' => (string) ($cupom->nome ?? ''),
                'codigo' => (string) ($cupom->codigo ?? ''),
                'valor' => (float) ($cupom->valor ?? 0),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $validated = Validator::make($request->all(), $this->storeRules())->validate();

        $userCriado = false;
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            $user = $this->resolveReservaUser($validated, $empresa, $userCriado);
            if ($userCriado) {
                Auth::login($user);
            }
        }
        $grupo = GrupoFrota::query()->where('ativo', 'S')->find((int) $validated['grupo_frota_id']);
        $modelo = Modelo::query()->find((int) $validated['modelo_id']);
        $estacaoRetirada = Estacao::query()->where('ativo', 'S')->find((int) $validated['estacao_retirada_id']);
        $estacaoDevolucao = Estacao::query()->where('ativo', 'S')->find((int) $validated['estacao_devolucao_id']);

        if (!$grupo || !$modelo || !$estacaoRetirada || !$estacaoDevolucao) {
            throw ValidationException::withMessages([
                'reserva' => 'Nao foi possivel validar os dados basicos da reserva.',
            ]);
        }

        $dataInicio = (string) $validated['data_inicio'];
        $dataFim = (string) $validated['data_fim'];
        $grupoFrotaId = (int) $validated['grupo_frota_id'];
        $modeloId = (int) $validated['modelo_id'];
        $estacaoRetiradaId = (int) $validated['estacao_retirada_id'];

        if (!$grupo->modelos()->where('modelos.id', $modeloId)->exists()) {
            throw ValidationException::withMessages([
                'modelo_id' => 'O modelo selecionado nao pertence ao grupo de frota informado.',
            ]);
        }

        $modelosDisponiveis = $this->veiculoService->getModelosComVeiculosDisponiveis(
            $grupoFrotaId,
            $estacaoRetiradaId,
            $dataInicio,
            $dataFim
        )->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (!in_array($modeloId, $modelosDisponiveis, true)) {
            throw ValidationException::withMessages([
                'modelo_id' => 'Nao ha veiculo disponivel para o modelo selecionado neste periodo.',
            ]);
        }

        $opcionaisSelecionados = $this->parseOpcionaisPayload((string) ($validated['opcionais_payload'] ?? ''));
        $opcionaisReserva = $this->buildOpcionaisReserva($grupo, $opcionaisSelecionados);
        $cupom = $this->resolveCupomValido((string) ($validated['cupom_codigo'] ?? ''));

        $quantidadeDias = $this->calcularQuantidadeDias((string) $validated['data_inicio'], (string) $validated['data_fim']);
        $tarifaBase = (float) $grupo->valor_diario;

        $payload = [
            'estacao_retirada_id' => (int) $validated['estacao_retirada_id'],
            'estacao_devolucao_id' => (int) $validated['estacao_devolucao_id'],
            'data_inicio' => (string) $validated['data_inicio'],
            'data_fim' => (string) $validated['data_fim'],
            'grupo_frota_id' => $grupoFrotaId,
            'modelo_id' => $modeloId,
            'tarifa_base' => $tarifaBase,
            'quantidade_dias' => $quantidadeDias,
            'user_id' => (int) $user->id,
            'opcionais' => $opcionaisReserva,
            'cupom_desconto' => $cupom ? [
                'id' => (int) $cupom->id,
                'codigo' => (string) ($cupom->codigo ?? ''),
                'valor' => (float) ($cupom->valor ?? 0),
            ] : null,
            'valor_desconto' => $cupom ? (float) ($cupom->valor ?? 0) : 0.0,
            'observacoes' => null,
        ];

        $this->validateLikeAdminStoreRequest($payload, $opcionaisReserva);

        $dadosProcessados = $this->reservaService->processarDadosReserva((object) $payload);
        $reserva = $this->reservaService->create($dadosProcessados);

        if (!$reserva instanceof Reserva) {
            throw ValidationException::withMessages([
                'reserva' => 'Nao foi possivel concluir a reserva. Revise os dados e tente novamente.',
            ]);
        }

        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) $user->id,
            json_encode([
                'acao' => 'Reserva criada pelo site de apresentacao',
                'origem' => 'site-apresentacao',
                'reserva_id' => (int) $reserva->id,
            ])
        );

        $pagamentoUrl = Route::has('reserva.efetuarPagamento')
            ? route('reserva.efetuarPagamento', ['id' => $reserva->id])
            : null;

        if ($pagamentoUrl) {
            if (auth()->check()) {
                return redirect()
                    ->to($pagamentoUrl)
                    ->with('success', 'Reserva #' . $reserva->id . ' criada com sucesso.');
            }

            session()->put('url.intended', $pagamentoUrl);

            if (Route::has('login')) {
                return redirect()
                    ->route('login')
                    ->with('success', 'Reserva #' . $reserva->id . ' criada. Faca login para continuar o pagamento.');
            }
        }

        return redirect()
            ->route('site.reserva.success', ['reserva' => $reserva->id])
            ->with('success', 'Reserva #' . $reserva->id . ' criada com sucesso.');
    }

    public function success(Request $request, int $reserva): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $query = Reserva::query()->with(['estacaoRetirada', 'estacaoDevolucao', 'modelos', 'usuario']);
        if (Schema::hasColumn('reservas', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $reservaItem = $query->find($reserva);
        if (!$reservaItem) {
            return redirect()
                ->route('site.reserva.index')
                ->withErrors(['reserva' => 'Reserva nao encontrada para este dominio.']);
        }

        return view('site-apresentacao.reserva-success', [
            'empresa' => $empresa,
            'reserva' => $reservaItem,
            'urlNovaReserva' => route('site.reserva.index'),
            'urlAdmin' => route('site.admin.entry'),
            'urlPagamento' => Route::has('reserva.efetuarPagamento')
                ? route('reserva.efetuarPagamento', ['id' => $reservaItem->id])
                : null,
            'loginUrl' => Route::has('login') ? route('login') : $this->adminLoginFallbackUrl(),
            'isAuth' => auth()->check(),
        ]);
    }

    public function loginRedirect(Request $request): RedirectResponse
    {
        $step = (int) $request->query('step', 4);
        if ($step < 1 || $step > 4) {
            $step = 4;
        }

        session()->put('url.intended', route('site.reserva.index', ['step' => $step]));

        if (Route::has('login')) {
            return redirect()->route('login');
        }

        return redirect($this->adminLoginFallbackUrl());
    }

    public function adminEntry(Request $request): View|RedirectResponse
    {
        $empresa = $this->resolveEmpresaAtivaByHost($request);
        if (!$empresa) {
            return $this->defaultResponse();
        }

        $cores = is_array($empresa->site_apresentacao_cores) ? $empresa->site_apresentacao_cores : [];

        return view('site-apresentacao.admin-entry', [
            'empresa' => $empresa,
            'corFundo' => $this->colorValue($cores, 'admin_fundo', $empresa->cor_secundaria ?: '#193C68'),
            'corTexto' => $this->colorValue($cores, 'admin_texto', '#FFFFFF'),
            'corCard' => $this->colorValue($cores, 'form_fundo', '#FFFFFF'),
            'corTextoCard' => $this->colorValue($cores, 'form_texto', '#0F172A'),
            'corBotaoFundo' => $this->colorValue($cores, 'botao_primario_fundo', $empresa->cor_terciaria ?: '#E7B93A'),
            'corBotaoTexto' => $this->colorValue($cores, 'botao_primario_texto', '#111827'),
            'loginUrl' => Route::has('login') ? route('login') : $this->adminLoginFallbackUrl(),
        ]);
    }

    private function resolveEmpresaAtivaByHost(Request $request): ?Empresa
    {
        $host = DomainNormalizer::normalize($request->getHost());
        if (!$host) {
            return null;
        }

        return Empresa::query()
            ->withoutGlobalScopes()
            ->where('ativo', 'S')
            ->whereNotNull('dominio')
            ->get()
            ->first(function (Empresa $empresa) use ($host): bool {
                return DomainNormalizer::normalize($empresa->dominio) === $host
                    && (($empresa->site_apresentacao_ativo ?? 'S') === 'S');
            });
    }

    private function activateEmpresaContext(Empresa $empresa): void
    {
        app(EmpresaContext::class)->setEmpresaId((int) $empresa->id);
    }

    private function estacoesForEmpresa(): array
    {
        return Estacao::query()
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get(['id', 'nome', 'cidade'])
            ->map(function (Estacao $estacao): array {
                return [
                    'id' => (int) $estacao->id,
                    'nome' => (string) $estacao->nome,
                    'cidade' => (string) ($estacao->cidade ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    private function gruposFrotaAtivos(): array
    {
        return GrupoFrota::query()
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get(['id', 'nome', 'valor_diario'])
            ->map(function (GrupoFrota $grupo): array {
                return [
                    'id' => (int) $grupo->id,
                    'nome' => (string) $grupo->nome,
                    'valor_diario' => (float) $grupo->valor_diario,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeTipoOpcionais(array $tipoOpcionais): array
    {
        return collect($tipoOpcionais)
            ->map(function ($item): array {
                $tipo = $item['tipo'] ?? null;
                $opcionais = collect($item['opcionais'] ?? [])
                    ->map(function ($opcional): array {
                        return [
                            'id' => (int) $opcional->id,
                            'nome' => (string) $opcional->nome,
                            'tipo_cobranca' => (string) ($opcional->tipo_cobranca ?? ''),
                            'preco_por_dia' => $opcional->preco_por_dia !== null ? (float) $opcional->preco_por_dia : null,
                            'preco_integral' => $opcional->preco_integral !== null ? (float) $opcional->preco_integral : null,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'tipo' => [
                        'id' => (int) ($tipo->id ?? 0),
                        'nome' => (string) ($tipo->nome ?? ''),
                    ],
                    'opcionais' => $opcionais,
                ];
            })
            ->values()
            ->all();
    }

    private function resolveCupomValido(string $codigo): ?CupomDesconto
    {
        $codigoNormalizado = trim($codigo);
        if ($codigoNormalizado === '') {
            return null;
        }

        $cupom = $this->cupomDescontoService->verificarCupom($codigoNormalizado);
        if (!$cupom) {
            return null;
        }

        if (isset($cupom->ativo) && (string) $cupom->ativo !== 'S') {
            return null;
        }

        return $cupom;
    }

    private function parseOpcionaisPayload(string $rawPayload): array
    {
        $payload = trim($rawPayload);
        if ($payload === '') {
            return [];
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'opcionais_payload' => 'Formato invalido para opcionais.',
            ]);
        }

        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = (int) ($item['id'] ?? 0);
            $quantidade = (int) ($item['quantidade'] ?? 0);

            if ($id <= 0 || $quantidade <= 0) {
                continue;
            }

            $result[$id] = [
                'id' => $id,
                'quantidade' => $quantidade,
            ];
        }

        return array_values($result);
    }

    private function buildOpcionaisReserva(GrupoFrota $grupo, array $opcionaisSelecionados): array
    {
        if (empty($opcionaisSelecionados)) {
            return [];
        }

        $permitidos = $grupo
            ->opcionais()
            ->pluck('opcionais.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $idsSelecionados = array_map(fn ($item) => (int) $item['id'], $opcionaisSelecionados);
        $idsInvalidos = array_values(array_diff($idsSelecionados, $permitidos));

        if (!empty($idsInvalidos)) {
            throw ValidationException::withMessages([
                'opcionais_payload' => 'Um ou mais opcionais nao pertencem ao grupo de frota selecionado.',
            ]);
        }

        $opcionais = Opcional::query()
            ->whereIn('id', $idsSelecionados)
            ->get(['id', 'nome', 'tipo_cobranca', 'preco_por_dia', 'preco_integral'])
            ->keyBy('id');

        $result = [];
        foreach ($opcionaisSelecionados as $selecionado) {
            $opcional = $opcionais->get((int) $selecionado['id']);
            if (!$opcional) {
                continue;
            }

            $result[] = [
                'id' => (int) $opcional->id,
                'nome' => (string) $opcional->nome,
                'tipo_cobranca' => (string) ($opcional->tipo_cobranca ?? ''),
                'preco_por_dia' => (float) ($opcional->preco_por_dia ?? 0),
                'preco_integral' => (float) ($opcional->preco_integral ?? 0),
                'quantidade' => (int) $selecionado['quantidade'],
            ];
        }

        return $result;
    }

    private function resolveReservaUser(array $validated, Empresa $empresa, bool &$created = false): User
    {
        $email = mb_strtolower(trim((string) $validated['email']));

        $query = User::query()->whereRaw('LOWER(email) = ?', [$email]);
        if (Schema::hasColumn('users', 'empresa_id')) {
            $query->where('empresa_id', (int) $empresa->id);
        }

        $existingUser = $query->first();
        if ($existingUser instanceof User) {
            throw ValidationException::withMessages([
                'email' => 'Este e-mail ja possui cadastro. Faca login para continuar.',
            ]);
        }

        $roleClienteId = $this->regraService->getClienteRoleId();
        if (!$roleClienteId) {
            throw ValidationException::withMessages([
                'reserva' => 'Nao existe uma regra Cliente configurada para concluir a reserva.',
            ]);
        }

        $dadosUsuario = [
            'nome' => (string) $validated['nome'],
            'email' => (string) $validated['email'],
            'sexo' => (string) $validated['sexo'],
            'data_nascimento' => (string) $validated['data_nascimento'],
            'local_nascimento' => (string) $validated['local_nascimento'],
            'telefone' => (string) $validated['telefone'],
            'celular' => (string) $validated['celular'],
            'pais_sigla' => (string) $validated['pais_sigla'],
            'zip_code' => (string) $validated['zip_code'],
            'cidade' => (string) $validated['cidade'],
            'endereco' => (string) $validated['endereco'],
            'numero_identificacao' => (string) $validated['numero_identificacao'],
            'numero_carta_conducao' => (string) $validated['numero_carta_conducao'],
            'local_emissao_carta_conducao' => (string) $validated['local_emissao_carta_conducao'],
            'pais_emissao_carta_conducao' => (string) $validated['pais_emissao_carta_conducao'],
            'data_emissao_carta_conducao' => (string) $validated['data_emissao_carta_conducao'],
            'data_validade_carta_conducao' => $validated['data_validade_carta_conducao'] ?? null,
            'role_id' => (int) $roleClienteId,
            'acesso_todas_estacoes' => true,
            'password' => 'Tmp@' . random_int(100000, 999999),
        ];

        try {
            $clienteStripe = $this->clienteStripeService->criarCliente(
                (string) $dadosUsuario['email'],
                (string) $dadosUsuario['nome']
            );
            $dadosUsuario['stripe_id'] = (string) ($clienteStripe->id ?? '');
        } catch (\Throwable) {
            // Reserva publica nao deve falhar apenas por indisponibilidade do gateway.
        }

        try {
            $user = $this->usuarioService->createUser($dadosUsuario);
            $created = $user instanceof User;

            return $user;
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'email' => 'Nao foi possivel criar o usuario da reserva com o e-mail informado.',
            ]);
        }
    }

    private function validateLikeAdminStoreRequest(array $payload, array $opcionaisReserva): void
    {
        $storeRules = (new StoreReservaRequest())->rules();

        $dados = [
            'estacao_retirada_id' => $payload['estacao_retirada_id'],
            'data_inicio' => $payload['data_inicio'],
            'estacao_devolucao_id' => $payload['estacao_devolucao_id'],
            'data_fim' => $payload['data_fim'],
            'veiculo_id' => null,
            'modelo_id' => $payload['modelo_id'],
            'cupom_desconto' => $payload['cupom_desconto']['codigo'] ?? null,
            'opcionais' => array_map(fn ($item) => (int) $item['id'], $opcionaisReserva),
            'user_id' => $payload['user_id'],
        ];

        Validator::make($dados, $storeRules)->validate();
    }

    private function storeRules(): array
    {
        $isAuth = auth()->check();
        $required = fn (array $rules) => $isAuth ? array_merge(['nullable'], $rules) : array_merge(['required'], $rules);

        return [
            'estacao_retirada_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_inicio' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$this->dataInicioValida((string) $value)) {
                        $fail('A retirada nao pode ser antes de agora.');
                    }
                },
            ],
            'estacao_devolucao_id' => ['required', 'integer', 'exists:estacoes,id'],
            'data_fim' => [
                'required',
                'date',
                'after:data_inicio',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $inicio = (string) request()->input('data_inicio', '');
                    if ($inicio !== '' && !$this->dataFimMinimaValida($inicio, (string) $value)) {
                        $fail('A devolucao deve ser ao menos 1 dia apos a retirada.');
                    }
                },
            ],
            'grupo_frota_id' => ['required', 'integer', 'exists:grupos_frota,id'],
            'modelo_id' => ['required', 'integer', 'exists:modelos,id'],
            'cupom_codigo' => ['nullable', 'string', 'max:255'],
            'opcionais_payload' => ['nullable', 'string'],
            'nome' => $required(['string', 'max:255']),
            'email' => $required(['string', 'max:150', 'email']),
            'sexo' => $required(['string', Rule::in(['M', 'F', 'O'])]),
            'data_nascimento' => $required(['date', 'before:' . Carbon::now()->subYears(16)->format('Y-m-d')]),
            'local_nascimento' => $required(['string', 'max:100']),
            'telefone' => $required(['string', 'max:20']),
            'celular' => $required(['string', 'max:20', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/']),
            'pais_sigla' => $required(['string', 'max:2']),
            'zip_code' => $required(['string', 'max:20']),
            'cidade' => $required(['string', 'max:100']),
            'endereco' => $required(['string', 'max:255']),
            'numero_identificacao' => $required(['string', 'max:20']),
            'numero_carta_conducao' => $required(['string', 'max:20']),
            'local_emissao_carta_conducao' => $required(['string', 'max:100']),
            'pais_emissao_carta_conducao' => $required(['string', 'max:2']),
            'data_emissao_carta_conducao' => $required(['date', 'before:' . Carbon::now()->format('Y-m-d')]),
            'data_validade_carta_conducao' => ['nullable', 'date'],
        ];
    }

    private function calcularQuantidadeDias(string $dataInicio, string $dataFim): int
    {
        $inicio = strtotime($dataInicio);
        $fim = strtotime($dataFim);
        if ($inicio === false || $fim === false || $fim <= $inicio) {
            return 1;
        }

        return (int) max(1, ceil(($fim - $inicio) / 86400));
    }

    private function buildDateTimeLocal(mixed $date, mixed $time): ?string
    {
        $data = trim((string) ($date ?? ''));
        $hora = trim((string) ($time ?? ''));
        if ($data === '' || $hora === '') {
            return null;
        }

        try {
            return Carbon::parse($data . ' ' . $hora)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeInt(mixed $value): ?int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int !== false ? (int) $int : null;
    }

    private function dataInicioValida(string $dataInicio): bool
    {
        try {
            $inicio = Carbon::parse($dataInicio);
        } catch (\Throwable) {
            return false;
        }

        return $inicio->greaterThanOrEqualTo(Carbon::now());
    }

    private function dataFimMinimaValida(string $dataInicio, string $dataFim): bool
    {
        try {
            $inicio = Carbon::parse($dataInicio);
            $fim = Carbon::parse($dataFim);
        } catch (\Throwable) {
            return false;
        }

        return $fim->greaterThanOrEqualTo($inicio->copy()->addDay());
    }

    private function colorValue(array $cores, string $key, string $default): string
    {
        $value = $cores[$key] ?? null;
        if (!is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return $default;
        }

        if ($trimmed === 'transparent') {
            return 'transparent';
        }

        return preg_match('/^#[A-Fa-f0-9]{6}$/', $trimmed) ? strtoupper($trimmed) : $default;
    }

    private function simboloMoeda(string $moeda): string
    {
        return match (strtoupper($moeda)) {
            'USD' => '$',
            'EUR' => 'EUR',
            default => 'R$',
        };
    }

    private function defaultResponse(): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        if (Route::has('site.apresentacao')) {
            return redirect()->route('site.apresentacao');
        }

        if (Route::has('login')) {
            return redirect()->route('login');
        }

        return redirect($this->adminHomeFallbackUrl());
    }

    private function adminHomeFallbackUrl(): string
    {
        $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
        $path = $prefix === '' ? '/home' : '/' . $prefix . '/home';

        return url($path);
    }

    private function adminLoginFallbackUrl(): string
    {
        $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
        $path = $prefix === '' ? '/login' : '/' . $prefix . '/login';

        return url($path);
    }
}
