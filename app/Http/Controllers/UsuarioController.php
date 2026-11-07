<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdatePasswordUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Responsavel;
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

    public function __construct(
        protected UsuarioService $usuarioService,
        protected BreadcrumbService $breadcrumbService,
        protected RegraService $regraService,
    ) {
        $this->titulo = 'Usuários';
        $this->nomeFiltro = 'usuario';

        $this->middleware('can:usuario.index')->only('index');
        $this->middleware('can:usuario.show')->only('show');
        $this->middleware('can:usuario.create')->only('create');
        $this->middleware('can:usuario.store')->only('store');
        $this->middleware('can:usuario.edit')->only('edit');
        $this->middleware('can:usuario.update')->only('update');
        $this->middleware('can:usuario.destroy')->only('destroy');
        $this->middleware('can:usuario.alterarSenha')->only('alterarSenha');
        $this->middleware('can:usuario.salvarNovaSenha')->only('salvarNovaSenha');
        $this->middleware('can:usuario.buscar')->only('buscar');
        $this->middleware('can:usuario.selectOptions')->only('selectOptions');
        $this->middleware('can:usuario.filtro')->only('filtro');
        $this->middleware('can:usuario.removerFiltro')->only('removerFiltro');
        $this->middleware('can:usuario.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tipoFiltro = $itensFiltered['tipo_conta']['valor'] ?? null;
        $usuarios = $this->usuarioService->getAllWithFiltersAndPagination(
            $itensFiltered,
            $perPage,
            ['tipoUsuario', 'regra', 'escolas', 'aluno.pessoa', 'responsavel.pessoa'],
            'id',
            'asc',
            $this->usuarioService->scopeTipoConta(is_string($tipoFiltro) ? $tipoFiltro : null)
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Usuários', 'Gerenciar');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();

        return view('usuario.index', compact(
            'usuarios', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro', 'tiposUsuario'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Usuários', 'usuario.index', 'Criar', 'Gerenciar');
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        [$opcoes, $rota] = [$this->opcoesVinculos(), $this->getFormMethodAndRoute()];

        return view('usuario.add-edit', array_merge(
            compact('breadcrumb', 'tiposUsuario'),
            $opcoes,
            ['metodo' => $rota[0], 'formRota' => $rota[1]]
        ))->with('titulo', $this->titulo);
    }

    public function store(StoreUsuarioRequest $request)
    {
        try {
            $data = $request->except(['confirmacao_senha', 'escolas']);
            $data['password'] = self::SENHA_PADRAO_ACESSO;
            $data['forcar_troca_senha'] = true;
            $data['email_verified_at'] = now();

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

            $usuario = $this->usuarioService->createUser($data);
            $this->sincronizarVinculos($usuario, $request);
            $this->usuarioService->sincronizarVinculoConta(
                $usuario,
                (string) $request->input('tipo_conta', User::TIPO_CONTA_COLABORADOR),
                $request->integer('vinculo_id') ?: null
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Usuario cadastrado com sucesso.',
                    'usuario' => [
                        'id' => (int) $usuario->id,
                        'nome' => (string) $usuario->nome,
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

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['usuario' => 'Nao foi possivel cadastrar o usuario no momento.']);
        }
    }

    public function selectOptions(Request $request)
    {
        $usuarios = collect($this->usuarioService->getAllForSelect())
            ->map(fn ($nome, $id) => ['id' => (int) $id, 'nome' => (string) $nome])
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json(['usuarios' => $usuarios]);
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
            $empresaId = auth()->user()?->empresa_id;
            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }
        }

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('numero_identificacao', 'like', "%{$q}%")
                    ->orWhere('telefone', 'like', "%{$q}%");
            });
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

    public function show($id)
    {
        $usuario = $this->usuarioService->getById($id, ['regra', 'escolas', 'aluno.pessoa', 'responsavel.pessoa']);
        abort_if(! $usuario, 403, 'Sem acesso a este usuario.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Usuários', 'usuario.index', $usuario->nome ?? 'Usuário');

        return view('usuario.show', compact('usuario', 'breadcrumb'))
            ->with('titulo', $usuario->nome ?? 'Usuário');
    }

    public function edit($id)
    {
        $usuario = $this->usuarioService->getById($id, ['escolas', 'aluno', 'responsavel']);
        abort_if(! $usuario, 403, 'Sem acesso a este usuario.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuários', 'usuario.index', "Editar: {$usuario->nome}", 'Gerenciar');
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        [$opcoes, $rota] = [$this->opcoesVinculos($usuario), $this->getFormMethodAndRoute($usuario)];

        return view('usuario.add-edit', array_merge(
            compact('usuario', 'breadcrumb', 'tiposUsuario'),
            $opcoes,
            ['metodo' => $rota[0], 'formRota' => $rota[1]]
        ))->with('titulo', $this->titulo);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioService->getById($id);
        abort_if(! $usuario, 403, 'Sem acesso para editar este usuario.');

        $data = $request->except(['escolas']);
        $atualizado = $this->usuarioService->updateUser($id, $data);
        $this->sincronizarVinculos($usuario, $request);

        // Vinculo so e regravado quando o tipo de conta veio no
        // form — chamadas antigas sem tipo_conta preservam o atual.
        if ($request->has('tipo_conta') && $atualizado) {
            $this->usuarioService->sincronizarVinculoConta(
                $atualizado,
                (string) $request->input('tipo_conta'),
                $request->integer('vinculo_id') ?: null
            );
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function alterarSenha($id)
    {
        $usuario = $this->usuarioService->getById($id);
        abort_if(! $usuario, 403, 'Sem acesso a este usuario.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuários', 'usuario.index', "Alterar Senha: {$usuario->nome}", 'Gerenciar');

        return view('usuario.alterar-senha', [
            'usuario' => $usuario,
            'breadcrumb' => $breadcrumb,
            'metodo' => 'PUT',
            'rota' => route('usuario.salvarNovaSenha', $usuario->id),
        ])->with('titulo', $this->titulo);
    }

    public function salvarNovaSenha(UpdatePasswordUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioService->getById($id);
        abort_if(! $usuario, 403, 'Sem acesso a este usuario.');

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

        return redirect()
            ->route('usuario.index')
            ->with('success', $mensagem);
    }

    public function destroy($id)
    {
        $success = $this->usuarioService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este usuario.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'usuario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'usuario.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'telefone', 'label' => 'Telefone', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'tipo_conta', 'label' => 'Tipo de conta', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => collect(User::TIPOS_CONTA)
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * Opcoes de vinculo: escolas do colaborador + alunos/
     * responsaveis disponiveis para contas de portal.
     */
    private function opcoesVinculos(?User $usuario = null): array
    {
        $usuario?->loadMissing(['aluno', 'responsavel', 'escolas']);

        $alunos = $this->vinculoRegistros(Aluno::class, $usuario);
        $responsaveis = $this->vinculoRegistros(Responsavel::class, $usuario);

        return [
            'escolasOpcoes' => Escola::query()->orderBy('nome')->pluck('nome', 'id'),
            'escolasSelecionadas' => $usuario ? $usuario->escolas->pluck('id')->all() : [],
            'tiposConta' => User::TIPOS_CONTA,
            'tipoConta' => $usuario?->tipoConta() ?? User::TIPO_CONTA_COLABORADOR,
            'vinculoSelecionado' => $usuario ? ($usuario->aluno?->id ?? $usuario->responsavel?->id) : null,
            'alunosOpcoes' => $alunos->mapWithKeys(fn (Aluno $a) => [$a->id => $a->nome ?? ('#'.$a->id)])->all(),
            'responsaveisOpcoes' => $responsaveis->mapWithKeys(fn (Responsavel $r) => [$r->id => $r->nome ?? ('#'.$r->id)])->all(),
            'vinculoDadosAluno' => $this->vinculoDados($alunos),
            'vinculoDadosResponsavel' => $this->vinculoDados($responsaveis),
        ];
    }

    /**
     * Alunos/responsaveis ativos do tenant sem conta de acesso
     * (ou ja vinculados a esta conta, para edicao).
     */
    private function vinculoRegistros(string $modelClass, ?User $usuario): \Illuminate\Support\Collection
    {
        $empresaId = auth()->user()?->empresa_id;

        return $modelClass::query()
            ->with('pessoa:id,nome,nome_social,email,celular,telefone,cpf,data_nascimento')
            ->where('ativo', 'S')
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where(fn ($q) => $q->whereNull('user_id')
                ->when($usuario, fn ($qq) => $qq->orWhere('user_id', $usuario->id)))
            ->orderBy('id')
            ->get();
    }

    /** Dados de pessoa usados para preencher o form ao escolher o vinculo. */
    private function vinculoDados(\Illuminate\Support\Collection $registros): array
    {
        return $registros->mapWithKeys(fn ($r) => [$r->id => [
            'nome' => $r->pessoa?->nomeExibicao() ?? '',
            'email' => $r->pessoa?->email ?? '',
            'nascimento' => optional($r->pessoa?->data_nascimento)->format('Y-m-d') ?? '',
            'telefone' => $r->pessoa?->telefone ?? '',
            'celular' => $r->pessoa?->celular ?? '',
            'cpf' => $r->pessoa?->cpf ?? '',
        ]])->all();
    }

    /**
     * Sincroniza os vinculos N:N usuario <-> escolas.
     */
    private function sincronizarVinculos(User $usuario, Request $request): void
    {
        $usuario->escolas()->sync($request->input('escolas', []));
    }

    private function getFormMethodAndRoute($usuario = null)
    {
        if ($usuario) {
            return ['PUT', route('usuario.update', $usuario->id)];
        }

        return ['POST', route('usuario.store')];
    }
}
