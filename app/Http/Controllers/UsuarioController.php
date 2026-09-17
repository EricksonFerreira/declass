<?php

namespace App\Http\Controllers;

// Exceptions
use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdatePasswordUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\RegraService;
use App\Services\UsuarioService;
use App\Support\SchemaCache;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class UsuarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    private const SENHA_PADRAO_ACESSO = 'Senh@123';

    use AtualizaFiltrosTrait;
    use FiltroTrait;

    protected $usuarioService;

    protected $breadcrumbService;

    protected $regraService;

    protected $paisService;

    public function __construct(
        UsuarioService $usuarioService,
        BreadcrumbService $breadcrumbService,
        RegraService $regraService,
    ) {
        $this->titulo = 'Usuários';
        $this->nomeFiltro = 'usuario';
        $this->usuarioService = $usuarioService;
        $this->breadcrumbService = $breadcrumbService;
        $this->regraService = $regraService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        // session()->forget("$this->nomeFiltro.filtro");
        $usuarios = $this->usuarioService->getAllWithFiltersAndPagination($itensFiltered, $perPage, ['tipoUsuario', 'funcao']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Usuários', 'Gerenciar');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        $nomeFiltro = $this->nomeFiltro;
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $funcoes = \App\Models\Funcao::where('ativo', true)->orderBy('nome')->pluck('nome', 'id');

        return view('usuario.index', compact(
            'usuarios',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'nomeFiltro',
            'tiposUsuario',
            'funcoes'
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
            'O' => 'Outro',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Usuários', 'usuario.index', 'Criar', 'Gerenciar');

        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $funcoes = \App\Models\Funcao::where('ativo', true)->orderBy('nome')->pluck('nome', 'id');
        [$metodo, $rota] = $this->getFormMethodAndRoute();

        return view('usuario.add-edit', compact('breadcrumb', 'sexo', 'metodo', 'tiposUsuario', 'funcoes', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreUsuarioRequest $request)
    {
        try {
            // Remove o campo de confirmacao de senha
            $data = $request->except('confirmacao_senha');
            $isCadastroReserva = $request->boolean('cadastro_reserva');

            if ($isCadastroReserva) {
                $roleClienteId = $this->regraService->getClienteRoleId();
                if (! $roleClienteId) {
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

            if (! empty(trim((string) $data['email']))) {
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

            // Cria o usuario no sistema
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

            $mensagemErro = $e->getMessage() == 'Nao foi possivel cadastrar o usuario no momento.' ? $e->getMessage() : 'Nao foi possivel cadastrar o usuario no momento. Erro: '.$e->getMessage();

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

        return 'reserva+'.implode('-', $partes).'@sem-email.local';
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
            ->where(function ($q) {
                $q->whereNull('tipo_usuario_id')->orWhere('tipo_usuario_id', '!=', 1);
            })
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
            'id' => $u->id,
            'nome' => $u->nome ?? ('Usuario #'.$u->id),
            'email' => $u->email ?? '',
            'documento' => $u->numero_identificacao ?? '',
            'telefone' => $u->telefone ?? '',
            'perfil' => $u->regra?->name ?? '',
            'nascimento' => $u->data_nascimento ? \Carbon\Carbon::parse($u->data_nascimento)->format('d/m/Y') : '',
            'cadastro' => $u->created_at ? $u->created_at->format('d/m/Y') : '',
        ]);

        return response()->json(['usuarios' => $usuarios]);
    }

    public function edit($id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (! $usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }
        $sexo = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro',
        ];

        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        $funcoes = \App\Models\Funcao::where('ativo', true)->orderBy('nome')->pluck('nome', 'id');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuários', 'usuario.index', "Editar: $usuario->nome", 'Gerenciar');
        [$metodo, $rota] = $this->getFormMethodAndRoute($usuario);

        return view('usuario.add-edit', compact('usuario', 'breadcrumb', 'tiposUsuario', 'funcoes', 'sexo', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (! $usuario) {
            abort(403, 'Sem acesso para editar este usuario.');
        }

        $this->usuarioService->updateUser($id, $request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function alterarSenha($id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (! $usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuários', 'usuario.index', "Alterar Senha: $usuario->nome", 'Gerenciar');
        $metodo = 'PUT';
        $rota = route('usuario.salvarNovaSenha', $usuario->id);

        return view('usuario.alterar-senha', compact('usuario', 'breadcrumb', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function salvarNovaSenha(UpdatePasswordUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioService->getById($id);
        if (! $usuario) {
            abort(403, 'Sem acesso a este usuario.');
        }

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
        if (! $success) {
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
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'celular', 'label' => 'Celular', 'type' => 'text', 'typeWhere' => 'like'],
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
}
