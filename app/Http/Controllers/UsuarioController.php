<?php

namespace App\Http\Controllers;

// Exceptions
use App\Exceptions\ClienteNaoEncontradoException;
use App\Exceptions\JaExisteClienteComEmailException;
use App\Exceptions\MultiplosClientesComEmailException;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdatePasswordUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Pais;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\EstacaoService;
use App\Services\PaisService;
use App\Services\RegraService;
use App\Services\UsuarioService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;
use App\Services\ClienteStripeService;
use App\Support\SchemaCache;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use Illuminate\Support\Facades\Log;

class UsuarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    private const SENHA_PADRAO_ACESSO = 'Senh@123';

    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $usuarioService;
    protected $breadcrumbService;
    protected $regraService;
    protected $paisService;
    protected $estacaoService;
    protected $clienteStripeService;
    protected $gatewayEmpresaConfigService;

    public function __construct(
        UsuarioService $usuarioService,
        BreadcrumbService $breadcrumbService,
        RegraService $regraService,
        PaisService $paisService,
        EstacaoService $estacaoService,
        ClienteStripeService $clienteStripeService,
        GatewayEmpresaConfigService $gatewayEmpresaConfigService
    ) {
        $this->titulo = "Usuários";
        $this->nomeFiltro = 'usuario';
        $this->usuarioService = $usuarioService;
        $this->breadcrumbService = $breadcrumbService;
        $this->regraService = $regraService;
        $this->paisService = $paisService;
        $this->estacaoService = $estacaoService;
        $this->clienteStripeService = $clienteStripeService;
        $this->gatewayEmpresaConfigService = $gatewayEmpresaConfigService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        // session()->forget("$this->nomeFiltro.filtro");
        $usuarios = $this->usuarioService->getAllWithFiltersAndPagination($itensFiltered, $perPage, ['tipoUsuario']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Usuarios');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        $nomeFiltro = $this->nomeFiltro;

        return view('usuario.index', compact(
            'usuarios',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        // Bloqueia criacao de usuario se o plano atingiu o limite.
        $empresa = auth()->user()?->empresa;
        // if ($empresa) {
        //     $plano = $empresa->planoAtivo()?->with('plano')->first()?->plano;
        //     if ($plano && $plano->limite_usuarios !== null) {
        //         $totalUsuarios = \App\Models\User::where('empresa_id', $empresa->id)->count();
        //         if ($totalUsuarios >= $plano->limite_usuarios) {
        //             return redirect()->route('usuario.index')
        //                 ->with('error', "Seu plano permite até {$plano->limite_usuarios} usuario(s). Faça upgrade para adicionar mais colaboradores.");
        //         }
        //     }
        // }

        $sexo = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Usuarios', 'usuario.index', 'Criar');

        $paises = $this->paisService->getAllForSelectSigla();
        $estacoes = $this->estacaoService->getAllAtivos();

        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        [$metodo, $rota] = $this->getFormMethodAndRoute();
        $acessoTodasEstacoes = true;
        $estacoesSelecionadas = [];

        return view('usuario.add-edit', compact('breadcrumb', 'sexo', 'metodo', 'tiposUsuario', 'paises', 'estacoes', 'acessoTodasEstacoes', 'estacoesSelecionadas', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreUsuarioRequest $request)
    {
        try {
            // Remove o campo de confirmacao de senha
            $data = $request->except('confirmacao_senha');
            $isCadastroReserva = $request->boolean('cadastro_reserva');

            if ($isCadastroReserva) {
                $roleClienteId = $this->regraService->getClienteRoleId();
                if (!$roleClienteId) {
                    $errors = [
                        'role_id' => ['Cadastre uma regra "Cliente" para permitir cadastro rapido na reserva.'],
                    ];

                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Nao foi encontrada uma regra Cliente para sua empresa.',
                            'errors' => $errors,
                        ], 422);
                    }

                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors($errors);
                }

                $data['role_id'] = $roleClienteId;
                $data['acesso_todas_estacoes'] = true;
                if (empty($data['email'])) {
                    $data['email'] = $this->gerarEmailTemporarioReserva($data);
                }
            }

            $data['password'] = self::SENHA_PADRAO_ACESSO;
            $data['forcar_troca_senha'] = true;

            $stripeSecret = $this->resolveStripeSecret(null, isset($data['empresa_id']) ? (int) $data['empresa_id'] : null);

            if (!empty(trim((string) $data['email']))) {
                $query = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower(trim((string) $data['email']))]);
                if (SchemaCache::hasColumn('users', 'empresa_id')) {
                    $query->where('empresa_id', (int) ($data['empresa_id'] ?? auth()->user()?->empresa_id ?? 0));
                }

                if ($query->exists()) {
                    return response()->json([
                        'message' => 'Nao foi possivel cadastrar o usuario no momento.',
                        'error' => 'Este e-mail ja possui cadastro para esta empresa.',
                    ], 422);
                }
            }

            // Cria o cliente no Stripe
            $cliente = $this->clienteStripeService->criarCliente($data['email'], $data['nome'], $stripeSecret);

            if (!$cliente || empty($cliente->id)) {
                throw new \RuntimeException('Nao foi possivel identificar o cliente no Stripe.');
            }

            // Cria o usuario no sistema
            $data['stripe_id'] = $cliente->id;
            $usuario = $this->usuarioService->createUser($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Usuario cadastrado com sucesso.',
                    'usuario' => [
                        'id' => (int) $usuario->id,
                        'nome' => (string) $usuario->nome,
                        'numero_identificacao' => (string) ($usuario->numero_identificacao ?? ''),
                        'email' => (string) $usuario->email,
                    ],
                ], 201);
            }

            return $this->redirecionaRotaRedirect($this->nomeFiltro);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Nao foi possivel cadastrar o usuario no momento.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 422);
            }

            $mensagemErro = 'Nao foi possivel cadastrar o usuario no momento.' == $e->getMessage() ? $e->getMessage() : 'Nao foi possivel cadastrar o usuario no momento. Erro: ' . $e->getMessage();
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['usuario' => $mensagemErro]);
        }
    }

    private function gerarEmailTemporarioReserva(array $data): string
    {
        $documentoBase = preg_replace('/\D+/', '', (string) ($data['numero_identificacao'] ?? ''));
        $celularBase = preg_replace('/\D+/', '', (string) ($data['celular'] ?? ''));
        $empresaBase = (int) (auth()->user()?->empresa_id ?? 0);
        $partes = array_filter([
            'reserva',
            $empresaBase > 0 ? (string) $empresaBase : null,
            $documentoBase !== '' ? $documentoBase : null,
            $celularBase !== '' ? $celularBase : null,
            uniqid('', true),
        ]);

        return 'reserva+' . implode('-', $partes) . '@sem-email.local';
    }

    public function selectOptions(Request $request)
    {
        $usuarios = collect($this->usuarioService->getAllForReservaSelect())
            ->map(fn ($nome, $id) => ['id' => (int) $id, 'nome' => (string) $nome])
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json([
            'usuarios' => $usuarios,
        ]);
    }

    public function buscar(Request $request)
    {
        $query = User::with('regra')
            ->select(['id', 'nome', 'email', 'numero_identificacao', 'telefone', 'empresa_id', 'role_id', 'data_nascimento', 'created_at'])
            ->orderBy('nome');

        if (SchemaCache::hasColumn('users', 'empresa_id')) {
            $empresaId = app(\App\Support\EmpresaContext::class)->empresaId()
                ?? \Illuminate\Support\Facades\Auth::user()?->empresa_id;
            if ($empresaId) {
                $query->where(function ($sub) use ($empresaId) {
                    $sub->whereNull('empresa_id')
                        ->orWhere('empresa_id', $empresaId);
                });
            }
        }

        // Busca global
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('numero_identificacao', 'like', "%{$q}%")
                    ->orWhere('telefone', 'like', "%{$q}%");
            });
        }

        // Filtros individuais
        if ($v = trim((string) $request->input('nome', ''))) {
            $query->where('nome', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->input('email', ''))) {
            $query->where('email', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->input('documento', ''))) {
            $query->where('numero_identificacao', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->input('telefone', ''))) {
            $query->where('telefone', 'like', "%{$v}%");
        }
        if ($v = (int) $request->input('role_id', 0)) {
            $query->where('role_id', $v);
        }
        if ($v = trim((string) $request->input('nascimento_de', ''))) {
            $query->whereDate('data_nascimento', '>=', $v);
        }
        if ($v = trim((string) $request->input('nascimento_ate', ''))) {
            $query->whereDate('data_nascimento', '<=', $v);
        }
        if ($v = trim((string) $request->input('cadastro_de', ''))) {
            $query->whereDate('created_at', '>=', $v);
        }
        if ($v = trim((string) $request->input('cadastro_ate', ''))) {
            $query->whereDate('created_at', '<=', $v);
        }

        $usuarios = $query->limit(80)->get()->map(fn ($u) => [
            'id'          => $u->id,
            'nome'        => $u->nome ?? ('Usuario #' . $u->id),
            'email'       => $u->email ?? '',
            'documento'   => $u->numero_identificacao ?? '',
            'telefone'    => $u->telefone ?? '',
            'perfil'      => $u->regra?->name ?? '',
            'nascimento'  => $u->data_nascimento ? \Carbon\Carbon::parse($u->data_nascimento)->format('d/m/Y') : '',
            'cadastro'    => $u->created_at ? $u->created_at->format('d/m/Y') : '',
        ]);

        return response()->json(['usuarios' => $usuarios]);
    }

    public function edit($id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (!$usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }

        $stripeSecret = $this->resolveStripeSecret($usuario);

        try {
            if ($usuario->stripe_id) {
                try {
                    $metodosPagamento = $this->clienteStripeService->listarMetodosPagamento($usuario->stripe_id, $stripeSecret);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                        Log::warning('Stripe customer invalid when loading user payment methods - attempting reconcile', ['user_id' => $usuario->id, 'stripe_id' => $usuario->stripe_id, 'exception' => $e->getMessage()]);
                    try {
                        $novoStripeId = $this->clienteStripeService->garantirClienteValido($usuario->stripe_id, $usuario->email ?? '', $usuario->nome ?? 'Cliente', $stripeSecret);
                        if (!empty($novoStripeId) && $novoStripeId !== ($usuario->stripe_id ?? '')) {
                            $this->usuarioService->updateUser($usuario->id, ['stripe_id' => $novoStripeId]);
                            $usuario = $this->usuarioService->getById($usuario->id);
                        }
                        $metodosPagamento = $this->clienteStripeService->listarMetodosPagamento($usuario->stripe_id, $stripeSecret);
                    } catch (\Exception $e2) {
                            Log::warning('Erro ao reconciliar cliente Stripe ou ao listar métodos após reconcile (usuario.edit)', ['exception' => $e2->getMessage()]);
                        $metodosPagamento = [];
                    }
                }
            } else {
                $metodosPagamento = [];
            }
        } catch (\Exception $e) {
            $metodosPagamento = [];
        }

        $sexo = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ];

        $tiposUsuario = $this->regraService->getAllTiposUsuarios();

        $paises = $this->paisService->getAllForSelectSigla();
        $estacoes = $this->estacaoService->getAllAtivos();
        $acessoTodasEstacoes = $usuario->hasAcessoTodasEstacoes();
        $estacoesSelecionadas = $usuario->estacoesPermitidas()->pluck('estacoes.id')->map(fn ($id) => (int) $id)->all();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuarios', 'usuario.index', "Editar: $usuario->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($usuario);

        return view('usuario.add-edit', compact('metodosPagamento', 'usuario', 'breadcrumb', 'tiposUsuario', 'sexo', 'paises', 'estacoes', 'acessoTodasEstacoes', 'estacoesSelecionadas', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (!$usuario) {
            abort(403, 'Sem acesso para editar este usuario.');
        }

        // if (isset($request->stripeToken)) {
        //     $cliente = $this->clienteStripeService->getClienteByEmail($usuario->email);

        //     if ($cliente->data == []) {
        //         throw new ClienteNaoEncontradoException();
        //     }

        //     if (count($cliente->data) > 1) {
        //         throw new MultiplosClientesComEmailException();
        //     }

        //     $cliente = $cliente->data[0];
        //     $this->clienteStripeService->createPaymentMethod($cliente->id, $request->stripeToken);
        // }

        $this->usuarioService->updateUser($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function metodoPagamento($usuarioId)
    {
        $usuario = $this->usuarioService->getById($usuarioId);
        if (!$usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }

        $stripeSecret = $this->resolveStripeSecret($usuario);

        if($usuario->stripe_id == null){
            return $this->redirecionaRotaRedirect('usuario.index', 'Usuário não possui código Stripe','error');
        }

        try {
            $metodosPagamento = $this->clienteStripeService->listarMetodosPagamento($usuario->stripe_id, $stripeSecret);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::warning('Stripe customer invalid when loading user payment methods (metodoPagamento) - attempting reconcile', ['user_id' => $usuario->id, 'stripe_id' => $usuario->stripe_id, 'exception' => $e->getMessage()]);
            try {
                $novoStripeId = $this->clienteStripeService->garantirClienteValido($usuario->stripe_id, $usuario->email ?? '', $usuario->nome ?? 'Cliente', $stripeSecret);
                if (!empty($novoStripeId) && $novoStripeId !== ($usuario->stripe_id ?? '')) {
                    $this->usuarioService->updateUser($usuario->id, ['stripe_id' => $novoStripeId]);
                    $usuario = $this->usuarioService->getById($usuario->id);
                }
                $metodosPagamento = $this->clienteStripeService->listarMetodosPagamento($usuario->stripe_id, $stripeSecret);
            } catch (\Exception $e2) {
                Log::warning('Erro ao reconciliar cliente Stripe ou ao listar métodos após reconcile (usuario.metodoPagamento)', ['exception' => $e2->getMessage()]);
                $metodosPagamento = [];
            }
        } catch (\Exception $e) {
            $metodosPagamento = [];
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuarios', 'usuario.index', "Editar: $usuario->nome");
        $metodo = 'POST';
        $rota = route('usuario.adicionarMetodoPagamento', $usuarioId);
        // Prefer company-specific publishable key when available
        $empresa = null;
        if (!empty($usuario->empresa_id)) {
            $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $usuario->empresa_id);
        }
        if (!$empresa) {
            $empresa = $this->gatewayEmpresaConfigService->empresaAtual();
        }
        $stripePublicKey = $empresa?->stripe_public_key ?? config('services.stripe.key') ?? env('STRIPE_KEY');

        return view(
            'usuario.metodoPagamento',
            compact(
                'metodosPagamento',
                'breadcrumb',
                'usuarioId',
                'metodo',
                'rota',
                'stripePublicKey'
            )
        )
            ->with('titulo', 'Métodos de Pagamento');
    }

    public function adicionarMetodoPagamento(Request $request, $usuarioId)
    {
        $stripeToken = $request->input('stripeToken');
        if ($stripeToken) {

            $usuario = $this->usuarioService->getById($usuarioId);
            if (!$usuario) {
                abort(403, 'Sem acesso a este usuario.');
            }
            $stripeSecret = $this->resolveStripeSecret($usuario);
            try {
                // Garante que exista um cliente válido no Stripe (procura por email ou cria)
                $stripeIdValido = $this->clienteStripeService->garantirClienteValido($usuario->stripe_id, $usuario->email ?? '', $usuario->nome ?? 'Cliente', $stripeSecret);
                if (!empty($stripeIdValido) && $stripeIdValido !== ($usuario->stripe_id ?? '')) {
                    $this->usuarioService->updateUser($usuario->id, ['stripe_id' => $stripeIdValido]);
                    $usuario = $this->usuarioService->getById($usuario->id);
                }

                // Agora cria o método de pagamento vinculado ao cliente garantido
                $this->clienteStripeService->criarMetodoPagamento($usuario->stripe_id, $stripeToken, $stripeSecret);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                Log::error('Erro Stripe ao adicionar metodo de pagamento', ['user_id' => $usuario->id, 'exception' => $e->getMessage()]);
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Nao foi possivel adicionar o metodo de pagamento no provedor.'], 422);
                }
                return $this->redirecionaRotaRedirect('usuario.metodoPagamento', 'Metodo de pagamento nao adicionado no provedor.', 'error');
            } catch (\Exception $e) {
                Log::error('Erro ao adicionar metodo de pagamento', ['user_id' => $usuario->id, 'exception' => $e->getMessage()]);
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Nao foi possivel adicionar o metodo de pagamento.'], 422);
                }
                return $this->redirecionaRotaRedirect('usuario.metodoPagamento', 'Metodo de pagamento nao adicionado!', 'error');
            }

            if ($request->expectsJson()) {
                try {
                    $metodosPagamento = $this->clienteStripeService->listarMetodosPagamento($usuario->stripe_id, $stripeSecret);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    Log::warning('Stripe customer invalid when listing methods after add - attempting reconcile', ['user_id' => $usuario->id, 'stripe_id' => $usuario->stripe_id, 'exception' => $e->getMessage()]);
                    try {
                        $novoStripeId = $this->clienteStripeService->garantirClienteValido($usuario->stripe_id, $usuario->email ?? '', $usuario->nome ?? 'Cliente', $stripeSecret);
                        if (!empty($novoStripeId) && $novoStripeId !== ($usuario->stripe_id ?? '')) {
                            $this->usuarioService->updateUser($usuario->id, ['stripe_id' => $novoStripeId]);
                            $usuario = $this->usuarioService->getById($usuario->id);
                        }
                        $metodosPagamento = $this->clienteStripeService->listarMetodosPagamento($usuario->stripe_id, $stripeSecret);
                    } catch (\Exception $e2) {
                        Log::warning('Erro ao reconciliar cliente Stripe ou ao listar métodos após reconcile (usuario.adicionarMetodoPagamento)', ['exception' => $e2->getMessage()]);
                        $metodosPagamento = [];
                    }
                } catch (\Exception $e) {
                    $metodosPagamento = [];
                }
                $metodos = collect($metodosPagamento)->map(function ($metodo) {
                    return [
                        'id' => (string) $metodo['id'],
                        'brand' => (string) ($metodo['card']->brand ?? ''),
                        'last4' => (string) ($metodo['card']->last4 ?? ''),
                        'exp_month' => (string) ($metodo['card']->exp_month ?? ''),
                        'exp_year' => (string) ($metodo['card']->exp_year ?? ''),
                    ];
                })->values()->all();

                return response()->json([
                    'message' => 'Metodo de pagamento adicionado com sucesso.',
                    'metodos' => $metodos,
                ]);
            }

            return $this->redirecionaRotaRedirect('usuario.metodoPagamento', 'MÃ©todo de pagamento adicionado com sucesso!');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Metodo de pagamento não adicionado.',
            ], 422);
        }

        return $this->redirecionaRotaRedirect('usuario.metodoPagamento', 'MÃ©todo de pagamento não adicionado!', 'error');
    }

    public function excluirMetodoPagamento($usuarioId, $metodoId)
    {
        $usuario = $this->usuarioService->getById($usuarioId);
        if (!$usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }

        $this->clienteStripeService->deletarMetodoPagamento($metodoId, $this->resolveStripeSecret($usuario));
        return $this->redirecionaRotaRedirect('usuario.metodoPagamento', 'Metodo de pagamento excluido com sucesso!');
    }

    public function alterarSenha($id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (!$usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuarios', 'usuario.index', "Alterar Senha: $usuario->nome");
        $metodo = 'PUT';
        $rota = route('usuario.salvarNovaSenha', $usuario->id);

        return view('usuario.alterar-senha', compact('usuario', 'breadcrumb', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function salvarNovaSenha(UpdatePasswordUsuarioRequest $request, $id)
    {
        $resetarParaAcesso = $request->boolean('resetar_para_acesso');

        $this->usuarioService->updatePassword($id, [
            'password' => $request->input('password'),
            'forcar_troca_senha' => $resetarParaAcesso,
        ]);

        $mensagem = $resetarParaAcesso
            ? 'Senha resetada para Senh@123. No primeiro login o usuario sera obrigado a definir uma nova senha.'
            : 'Senha alterada com sucesso.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $mensagem]);
        }

        if ($resetarParaAcesso) {
            return redirect()
                ->route('usuario.index')
                ->with('usuarioResetSenhaMensagem', $mensagem);
        }

        return redirect()
            ->route('usuario.index')
            ->with('success', $mensagem);
    }

    public function destroy($id)
    {
        $success = $this->usuarioService->delete($id);
        if (!$success) {
            abort(403, 'Sem acesso para excluir este usuario.');
        }
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
        session()->put("$this->nomeFiltro.urlPadrao", 'usuario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'usuario.index');
    }

    public function obterFiltrosPrincipais()
    {
        $tiposUsuarios = $this->regraService->getAllTiposUsuarios();
        $dataTiposUsuarios = [];
        $dataTiposUsuarios[] = ['value' => '', 'label' => 'Selecione o status', 'selected' => false];

        foreach ($tiposUsuarios as $key => $value) {
            $dataTiposUsuarios[] = ['value' => $key, 'label' => $value, 'selected' => false];
        }
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'celular', 'label' => 'Celular', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cidade', 'label' => 'Cidade', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'role_id', 'label' => 'Regra de Usuário', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataTiposUsuarios],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundÃ¡rios aqui, se necessÃ¡rio
        ];
    }

    /**
     * Função para obter o mÃ©todo e a rota para o formulÃ¡rio.
     *
     * @param  User|null  $usuario
     * @return array
     */
    private function getFormMethodAndRoute($usuario = null)
    {
        if ($usuario) {
            return [
                'PUT',
                route('usuario.update', $usuario->id),
            ];
        }

        return [
            'POST',
            route('usuario.store'),
        ];
    }

    private function resolveStripeSecret(?User $usuario = null, ?int $empresaId = null): ?string
    {
        $empresaId = $empresaId
            ?? ($usuario?->empresa_id ? (int) $usuario->empresa_id : null)
            ?? $this->gatewayEmpresaConfigService->empresaIdAtual();

        $empresa = $empresaId
            ? $this->gatewayEmpresaConfigService->empresaPorId($empresaId)
            : $this->gatewayEmpresaConfigService->empresaAtual();

        return $empresa?->stripe_secret ?: config('services.stripe.secret');
    }
}
