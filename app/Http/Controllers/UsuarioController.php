<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdatePasswordUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Aluno;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\EntregaTarefa;
use App\Models\Escola;
use App\Models\HorarioTurma;
use App\Models\Matricula;
use App\Models\PeriodoLetivo;
use App\Models\Regra;
use App\Models\Responsavel;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\RegraService;
use App\Services\UsuarioService;
use App\Support\SchemaCache;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Aba da listagem: funcionarios (equipe — diretor, coordenador,
        // professor...), alunos, responsaveis ou todos. Substitui o
        // antigo filtro de tipo_conta, removido dos filtros extras.
        $grupo = (string) request()->query('grupo', session('usuario.grupo', 'funcionarios'));
        if (! in_array($grupo, ['todos', 'funcionarios', 'alunos', 'responsaveis'], true)) {
            $grupo = 'funcionarios';
        }
        session(['usuario.grupo' => $grupo]);
        unset($itensFiltered['tipo_conta']);
        session()->forget("$this->nomeFiltro.filtro.tipo_conta");

        $tipoGrupo = match ($grupo) {
            'funcionarios' => User::TIPO_CONTA_COLABORADOR,
            'alunos' => User::TIPO_CONTA_ALUNO,
            'responsaveis' => User::TIPO_CONTA_RESPONSAVEL,
            default => null,
        };

        $usuarios = $this->usuarioService->getAllWithFiltersAndPagination(
            $itensFiltered,
            $perPage,
            ['tipoUsuario', 'regra', 'escolas', 'aluno.pessoa', 'responsavel.pessoa'],
            'id',
            'asc',
            $this->usuarioService->scopeTipoConta($tipoGrupo)
        );

        $contagemGrupos = $this->usuarioService->contagemPorTipoConta();

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
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro', 'tiposUsuario',
            'grupo', 'contagemGrupos'
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

        // Matriz de permissoes diretas — visivel apenas para o superadmin.
        $permissoesGrupos = null;
        $permEstado = ['permitir' => [], 'negar' => []];
        $permPerfil = collect();
        if (auth()->user()->isSuperAdmin()) {
            $permissoesGrupos = DB::table('permissions')->orderBy('name')->pluck('name')
                ->mapToGroups(fn ($n) => [explode('.', $n)[0] => $n]);
            $permEstado['permitir'] = DB::table('model_has_permissions')
                ->where('model_id', $usuario->id)->where('model_type', \App\Models\User::class)
                ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
                ->pluck('permissions.name')->all();
            $permEstado['negar'] = DB::table('user_permission_denies')->where('user_id', $usuario->id)
                ->join('permissions', 'user_permission_denies.permission_id', '=', 'permissions.id')
                ->pluck('permissions.name')->all();
            $permPerfil = $usuario->role_id ? DB::table('permissions')
                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_has_permissions.role_id', $usuario->role_id)->pluck('permissions.name') : collect();
        }

        $prof = $usuario->isProfessor() ? $this->dadosProfessor($usuario) : null;

        return view('usuario.show', compact('usuario', 'breadcrumb', 'permissoesGrupos', 'permEstado', 'permPerfil', 'prof'))
            ->with('titulo', $usuario->nome ?? 'Usuário');
    }

    /**
     * Painel do professor (perfil de usuario.show): turmas atribuidas,
     * habilitacoes, quadro semanal e pendencias de lancamento/correcao.
     * Tudo ja chega pronto para a view (labels, contadores, urls).
     */
    private function dadosProfessor(User $usuario): array
    {
        // Turmas atribuidas (papel + disciplinas por turma)
        $atribuicoes = Atribuicao::query()
            ->where('user_id', $usuario->id)
            ->where('ativo', 'S')
            ->with(['turma:id,nome,escola_id,ano_letivo_id', 'turma.escola:id,nome', 'turma.anoLetivo:id,ano', 'disciplina:id,nome'])
            ->get();

        $turmas = $atribuicoes->whereNotNull('turma')->groupBy('turma_id')
            ->map(function ($grupo) {
                $t = $grupo->first()->turma;

                return [
                    'id' => $t->id,
                    'nome' => $t->nome,
                    'escola' => $t->escola->nome ?? '—',
                    'esc' => $t->escola_id,
                    'ano' => $t->anoLetivo->ano ?? null,
                    'ano_letivo' => $t->ano_letivo_id,
                    'disciplinas' => $grupo->map(fn ($a) => [
                        'nome' => $a->disciplina->nome ?? 'Todas as disciplinas',
                        'papel' => Atribuicao::PAPEIS[$a->papel] ?? $a->papel,
                        'papel_cor' => ['titular' => 'success', 'auxiliar' => 'info', 'substituto' => 'warning'][$a->papel] ?? 'secondary',
                    ])->values()->all(),
                ];
            })->sortBy('nome')->values()->all();

        // Habilitacoes (principal/optativa)
        $habilitacoes = $usuario->disciplinasHabilitadas
            ->map(fn ($d) => ['nome' => $d->nome, 'tipo' => $d->pivot->tipo ?? 'principal'])
            ->sortBy('nome')->values()->all();

        // Quadro de horario semanal (estilo i-Educar)
        $horarios = HorarioTurma::query()
            ->where('user_id', $usuario->id)
            ->with(['turma:id,nome,escola_id,ano_letivo_id', 'disciplina:id,nome'])
            ->orderBy('dia_semana')->orderBy('ordem')->get();
        $grade = [];
        foreach ($horarios as $h) {
            $grade[$h->dia_semana][$h->ordem] = [
                'disciplina' => $h->disciplina->nome ?? '—',
                'turma' => $h->turma->nome ?? '—',
                'sala' => $h->sala,
                'esc' => $h->turma->escola_id ?? null,
                'ano_letivo' => $h->turma->ano_letivo_id ?? null,
            ];
        }
        $maxOrdem = (int) $horarios->max('ordem');
        $diasSemana = [1 => 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        // Avaliacoes criadas por ele + progresso de lancamento de notas
        $avaliacoes = Avaliacao::query()
            ->where('user_id', $usuario->id)
            ->with(['turma:id,nome', 'disciplina:id,nome'])
            ->withCount('notas')
            ->orderByDesc('data')->limit(40)->get();
        $alunosPorTurma = Matricula::query()
            ->whereIn('turma_id', $avaliacoes->pluck('turma_id')->unique()->filter())
            ->where('status', 'ativa')
            ->selectRaw('turma_id, count(*) as n')->groupBy('turma_id')
            ->pluck('n', 'turma_id');
        $avItems = $avaliacoes->map(function ($a) use ($alunosPorTurma) {
            $total = (int) ($alunosPorTurma[$a->turma_id] ?? 0);

            return [
                'nome' => $a->nome,
                'tipo' => Avaliacao::TIPOS[$a->tipo] ?? $a->tipo,
                'turma' => $a->turma->nome ?? '—',
                'disciplina' => $a->disciplina->nome ?? '—',
                'data' => $a->data?->format('d/m/Y') ?? '—',
                'lancadas' => (int) $a->notas_count,
                'total' => $total,
                'pendente' => $total > 0 && $a->notas_count < $total,
                'url' => route('avaliacao.notas', $a->id),
                'esc' => $a->escola_id,
                'per' => $a->periodo_letivo_id,
            ];
        })->values();

        // Entregas de tarefa aguardando correcao (tarefas criadas por ele)
        $entregasPorTarefa = EntregaTarefa::query()
            ->where('status', 'entregue')
            ->whereHas('tarefa', fn ($q) => $q->where('user_id', $usuario->id))
            ->with('tarefa:id,titulo,turma_id,user_id,escola_id', 'tarefa.turma:id,nome')
            ->get()->groupBy('tarefa_id');
        $tarefasPendentes = $entregasPorTarefa->map(function ($grupo, $tarefaId) {
            $tarefa = $grupo->first()->tarefa;

            return [
                'titulo' => $tarefa->titulo ?? 'Tarefa',
                'turma' => $tarefa->turma->nome ?? '—',
                'pendentes' => $grupo->count(),
                'url' => route('tarefa.entregas', $tarefaId),
                'esc' => $tarefa->escola_id,
            ];
        })->values();

        $avPendentes = $avItems->where('pendente', true)->values();

        // Filtros do painel: escolas onde atua + periodos letivos das avaliacoes
        $escolaIds = collect($turmas)->pluck('esc')
            ->merge($avItems->pluck('esc'))
            ->merge($tarefasPendentes->pluck('esc'))
            ->merge(collect($grade)->flatten(2)->pluck('esc'))
            ->filter()->unique()->values();
        $escolasFiltro = Escola::query()->whereIn('id', $escolaIds)
            ->orderBy('nome')->pluck('nome', 'id')->all();

        $periodos = PeriodoLetivo::query()
            ->whereIn('id', $avaliacoes->pluck('periodo_letivo_id')->filter()->unique())
            ->orderBy('data_inicio')
            ->get(['id', 'nome', 'ano_letivo_id', 'escola_id', 'data_inicio', 'data_fim']);
        $periodosFiltro = $periodos->map(fn ($p) => [
            'id' => $p->id,
            'rotulo' => $p->nome.' ('.$p->data_inicio->format('d/m').'–'.$p->data_fim->format('d/m').')',
            'ano_letivo' => $p->ano_letivo_id,
            'esc' => $p->escola_id,
        ])->values()->all();

        return [
            'turmas' => $turmas,
            'habilitacoes' => $habilitacoes,
            'grade' => $grade,
            'dias_semana' => $diasSemana,
            'max_ordem' => $maxOrdem,
            'avaliacoes' => $avItems->all(),
            'avaliacoes_pendentes' => $avPendentes->all(),
            'tarefas_pendentes' => $tarefasPendentes->all(),
            'escolas_filtro' => $escolasFiltro,
            'periodos_filtro' => $periodosFiltro,
            'stats' => [
                'turmas' => count($turmas),
                'disciplinas' => $atribuicoes->whereNotNull('disciplina_id')->pluck('disciplina_id')->unique()->count(),
                'aulas_semana' => $horarios->count(),
                'pendencias' => $avPendentes->count() + $entregasPorTarefa->sum(fn ($g) => $g->count()),
            ],
        ];
    }

    /**
     * Permissoes individuais: superadmin concede ('permitir' em
     * model_has_permissions) ou remove ('negar' em user_permission_denies)
     * telas/acoes especificas para um usuario, independente do perfil.
     */
    public function permissoes(Request $request, $id)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Somente o superadmin gerencia permissões diretas.');

        $usuario = $this->usuarioService->getById($id);
        abort_if(! $usuario, 404, 'Usuário não encontrado.');

        $escolhas = $request->input('perm', []);
        $permitir = collect($escolhas)->filter(fn ($v) => $v === 'permitir')->keys();
        $negar = collect($escolhas)->filter(fn ($v) => $v === 'negar')->keys();
        $permIds = DB::table('permissions')->whereIn('name', $permitir->merge($negar))->pluck('id', 'name');

        DB::table('model_has_permissions')
            ->where('model_id', $usuario->id)->where('model_type', \App\Models\User::class)->delete();
        foreach ($permitir as $nome) {
            if (isset($permIds[$nome])) {
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $permIds[$nome],
                    'model_type' => \App\Models\User::class,
                    'model_id' => $usuario->id,
                ]);
            }
        }

        DB::table('user_permission_denies')->where('user_id', $usuario->id)->delete();
        foreach ($negar as $nome) {
            if (isset($permIds[$nome])) {
                DB::table('user_permission_denies')->insert([
                    'user_id' => $usuario->id,
                    'permission_id' => $permIds[$nome],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('usuario.show', $usuario->id)
            ->with('success', 'Permissões diretas atualizadas.');
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
        $this->sincronizarVinculos($atualizado ?: $usuario, $request);

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
        // O agrupamento por tipo (funcionario/aluno/responsavel) virou
        // abas no topo da listagem — nao ha filtro secundario.
        return [];
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

        $habilitadas = $usuario
            ? $usuario->disciplinasHabilitadas->groupBy('pivot.tipo')
            : collect();

        return [
            'escolasOpcoes' => Escola::query()->orderBy('nome')->pluck('nome', 'id'),
            'escolasSelecionadas' => $usuario ? $usuario->escolas->pluck('id')->all() : [],
            'disciplinasOpcoes' => Disciplina::query()->orderBy('nome')->pluck('nome', 'id'),
            'rolesProfessor' => Regra::query()
                ->where('empresa_id', auth()->user()?->empresa_id)
                ->whereIn('name', ['professor', 'professor_substituto'])
                ->pluck('id')->all(),
            'disciplinasPrincipaisIds' => $habilitadas->get('principal', collect())->pluck('id')->all(),
            'disciplinasOptativasIds' => $habilitadas->get('optativa', collect())->pluck('id')->all(),
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

        if (! SchemaCache::hasTable('user_disciplina')) {
            return;
        }

        // Habilitacoes so se aplicam a professor — quando o perfil
        // sai de professor as habilitacoes sao limpas.
        $sync = [];
        if ($usuario->isProfessor()) {
            foreach ((array) $request->input('disciplinas_principais', []) as $did) {
                $sync[(int) $did] = ['tipo' => 'principal', 'empresa_id' => $usuario->empresa_id];
            }
            foreach ((array) $request->input('disciplinas_optativas', []) as $did) {
                $sync += [(int) $did => ['tipo' => 'optativa', 'empresa_id' => $usuario->empresa_id]];
            }
        }
        $usuario->disciplinasHabilitadas()->sync($sync);
    }

    private function getFormMethodAndRoute($usuario = null)
    {
        if ($usuario) {
            return ['PUT', route('usuario.update', $usuario->id)];
        }

        return ['POST', route('usuario.store')];
    }
}
