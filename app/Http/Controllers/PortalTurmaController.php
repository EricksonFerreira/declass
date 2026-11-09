<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Disciplina;
use App\Models\EntregaTarefa;
use App\Models\EntregaTarefaResposta;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Responsavel;
use App\Models\Tarefa;
use App\Models\Turma;
use App\Models\TurmaForumLeitura;
use App\Models\TurmaForumPost;
use App\Models\TurmaMaterial;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hub da turma no portal do aluno (estilo "classroom"):
 * abas Avisos, Atividades, Notas, Frequencia, Disciplinas,
 * Materiais e Pessoas — so para turmas em que um aluno
 * vinculado a conta tenha matricula ativa.
 * O forum e por disciplina e fica dentro da pagina da disciplina.
 */
class PortalTurmaController extends Controller
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
    ) {}

    /**
     * Turma liberada so se um aluno vinculado tem matricula ativa nela.
     * Com $incluirConcluidas, aceita tambem matricula concluida — usado
     * nas telas de leitura (turma, disciplina, tarefa) para historico;
     * os posts (forum, comentario, resposta) seguem exigindo ativa.
     */
    private function turmaAutorizada(Turma $turma, Collection $alunos, bool $incluirConcluidas = false): Collection
    {
        $matriculas = Matricula::query()
            ->where('turma_id', $turma->id)
            ->whereIn('status', $incluirConcluidas ? ['ativa', 'concluida'] : ['ativa'])
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with('aluno.pessoa')
            ->get();

        abort_if($matriculas->isEmpty(), 403, 'Nenhum aluno desta conta está matriculado nesta turma.');

        return $matriculas;
    }

    public function show($turmaId, Request $request)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());
        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $turma = Turma::with(['escola', 'anoLetivo', 'etapa', 'atribuicoes.user', 'disciplinas'])
            ->findOrFail($turmaId);
        $matriculas = $this->turmaAutorizada($turma, $alunos, incluirConcluidas: true);
        $matriculaIds = $matriculas->pluck('id');

        $avisos = Aviso::query()
            ->where('ativo', 'S')
            ->where('destino', 'familias')
            ->whereDate('data_publicacao', '<=', now()->toDateString())
            ->where(fn ($q) => $q->where('turma_id', $turma->id)->orWhereNull('turma_id'))
            ->orderByDesc('data_publicacao')
            ->orderByDesc('id')
            ->limit(30)
            ->get(['id', 'titulo', 'mensagem', 'tipo', 'data_publicacao']);

        $materiais = TurmaMaterial::query()
            ->where('turma_id', $turma->id)
            ->with('disciplina:id,nome', 'autor:id,nome')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        // Colegas com o mesmo status das matriculas da conta —
        // turma concluida mostra a turma da epoca, nao lista vazia.
        $colegas = Matricula::query()
            ->where('turma_id', $turma->id)
            ->whereIn('status', $matriculas->pluck('status')->unique()->all())
            ->with(['aluno.pessoa:id,nome,foto_path', 'aluno.user:id,foto_perfil'])
            ->orderBy('numero_chamada')
            ->get();

        $tarefas = Tarefa::query()
            ->where('turma_id', $turma->id)
            ->where('ativo', 'S')
            ->with(['disciplina:id,nome', 'questoes:id,tarefa_id'])
            ->orderBy('data_entrega')
            ->limit(60)
            ->get();

        // Calendario da aba Avisos (?mes=Y-m): combina avisos (data de
        // publicacao) e atividades (data de entrega) do mes — mesmo
        // grid da agenda do portal; o clique abre modal com os dados.
        $mesParam = (string) $request->query('mes', '');
        // Sem ?mes=: mes atual; se a turma e de outro ano letivo,
        // abre no primeiro mes letivo (data_inicio do ano letivo).
        $anoLetivo = $turma->anoLetivo;
        $mesPadrao = now()->startOfMonth();
        if ($anoLetivo && $anoLetivo->ano !== (int) now()->year) {
            $mesPadrao = ($anoLetivo->data_inicio ?? \Carbon\Carbon::create($anoLetivo->ano, 1, 1))
                ->copy()->startOfMonth();
        }
        $mesCal = preg_match('/^\d{4}-\d{2}$/', $mesParam)
            ? \Carbon\Carbon::createFromFormat('Y-m-d', $mesParam.'-01')->startOfMonth()
            : $mesPadrao;
        $inicioMes = $mesCal->copy();
        $fimMes = $mesCal->copy()->endOfMonth();

        $avisosCal = Aviso::query()
            ->where('ativo', 'S')
            ->where('destino', 'familias')
            ->whereDate('data_publicacao', '<=', now()->toDateString())
            ->where(fn ($q) => $q->where('turma_id', $turma->id)->orWhereNull('turma_id'))
            ->whereBetween('data_publicacao', [$inicioMes->toDateString(), $fimMes->toDateString()])
            ->orderBy('data_publicacao')
            ->get(['id', 'turma_id', 'titulo', 'mensagem', 'tipo', 'data_publicacao']);

        $tarefasCal = Tarefa::query()
            ->where('turma_id', $turma->id)
            ->where('ativo', 'S')
            ->whereNotNull('data_entrega')
            ->whereBetween('data_entrega', [$inicioMes->toDateString(), $fimMes->toDateString()])
            ->with(['disciplina:id,nome', 'questoes:id,tarefa_id'])
            ->orderBy('data_entrega')
            ->get();

        $entregas = EntregaTarefa::query()
            ->whereIn('tarefa_id', $tarefas->pluck('id')->merge($tarefasCal->pluck('id')))
            ->whereIn('matricula_id', $matriculaIds)
            ->get()
            ->keyBy(fn ($e) => $e->tarefa_id.'|'.$e->matricula_id);

        $itensCal = [];
        foreach ($avisosCal as $aviso) {
            $itensCal[$aviso->data_publicacao->toDateString()][] = [
                'id' => 'aviso-'.$aviso->id,
                'icone' => 'fa-bullhorn',
                'rotulo' => 'Aviso',
                'titulo' => $aviso->titulo,
                'badge' => Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo,
                'cor' => ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$aviso->tipo] ?? 'secondary',
                'data' => $aviso->data_publicacao->format('d/m/Y'),
                'info' => $aviso->turma_id ? 'Aviso da turma' : 'Aviso geral da escola',
                'info_icone' => $aviso->turma_id ? 'fa-users' : 'fa-school',
                'mensagem' => $aviso->mensagem,
                'url' => null,
            ];
        }
        foreach ($tarefasCal as $tarefa) {
            $entrega = $entregas->get($tarefa->id.'|'.$matriculas->first()->id);
            $itensCal[$tarefa->data_entrega->toDateString()][] = [
                'id' => 'tarefa-'.$tarefa->id,
                'icone' => 'fa-tasks',
                'rotulo' => $tarefa->tipoLabel(),
                'titulo' => $tarefa->titulo,
                'badge' => $entrega ? (EntregaTarefa::STATUS[$entrega->status] ?? $entrega->status) : 'Pendente',
                'cor' => $entrega?->status === 'avaliada' ? 'success' : ($entrega?->status === 'entregue' ? 'info' : ($tarefa->data_entrega->isPast() ? 'danger' : 'warning')),
                'data' => 'entrega até '.$tarefa->data_entrega->format('d/m/Y'),
                'info' => ($tarefa->disciplina->nome ?? 'Geral').($tarefa->questoes->isNotEmpty() ? ' · '.$tarefa->questoes->count().' questão(ões)' : ''),
                'info_icone' => 'fa-book',
                'mensagem' => $tarefa->descricao,
                'url' => route('portal.turma.tarefa', [$turma->id, $tarefa->id]),
            ];
        }

        // Abas Notas e Frequencia: mesmo consolidado das telas
        // Notas/Frequencia do portal, restrito as matriculas desta
        // turma. setRelation evita N+1 no cabecalho dos blocos.
        $matriculas->each->setRelation('turma', $turma);

        $notas = Nota::query()
            ->whereIn('matricula_id', $matriculaIds)
            ->whereHas('avaliacao', fn ($q) => $q->where('turma_id', $turma->id))
            ->with(['avaliacao:id,nome,tipo,periodo,data,nota_maxima,disciplina_id,periodo_letivo_id',
                'avaliacao.disciplina:id,nome,sigla', 'avaliacao.periodoLetivo:id,nome,ordem'])
            ->get()
            ->groupBy('matricula_id')
            ->map(fn ($lista) => $lista
                ->filter(fn ($n) => $n->avaliacao)
                ->sortBy(fn ($n) => $n->avaliacao->data)
                ->groupBy(fn ($n) => $n->avaliacao->disciplina->nome ?? 'Sem disciplina')
                ->sortKeys());

        // media em % (nota * 100 / nota_maxima), mesma regra do boletim do portal
        $mediaDe = function ($lista) {
            $validas = $lista->filter(fn ($n) => $n->nota !== null && $n->avaliacao->nota_maxima > 0);

            return $validas->isEmpty() ? null
                : round($validas->avg(fn ($n) => $n->nota * 100 / $n->avaliacao->nota_maxima), 1);
        };
        $medias = $notas->map(fn ($disciplinas) => $disciplinas->map($mediaDe));
        $mediasGerais = $notas->map(fn ($disciplinas) => $mediaDe($disciplinas->flatten(1)));

        $resumoFreqDe = function ($itens) {
            $porStatus = $itens->countBy('status');
            $total = $itens->count();
            $presencas = (int) $porStatus->get('presente', 0);

            return [
                'total' => $total,
                'presencas' => $presencas,
                'faltas' => (int) $porStatus->get('falta', 0),
                'justificadas' => (int) $porStatus->get('justificada', 0),
                'percentual' => $total > 0 ? round($presencas / $total * 100) : null,
            ];
        };

        $registrosFreq = Frequencia::query()
            ->whereIn('frequencias.matricula_id', $matriculaIds)
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->where('diarios.turma_id', $turma->id)
            ->leftJoin('disciplinas', 'diarios.disciplina_id', '=', 'disciplinas.id')
            ->orderByDesc('diarios.data')
            ->get([
                'frequencias.matricula_id',
                'frequencias.status',
                'frequencias.justificativa',
                'diarios.data',
                DB::raw("coalesce(disciplinas.nome, 'Sem disciplina') as disciplina"),
            ]);

        $frequenciaBlocos = $matriculas->map(function ($matricula) use ($registrosFreq, $resumoFreqDe) {
            $doAluno = $registrosFreq->where('matricula_id', $matricula->id);

            return [
                'matricula' => $matricula,
                'disciplinas' => $doAluno->groupBy('disciplina')->sortKeys()
                    ->map(fn ($itens, $nome) => $resumoFreqDe($itens) + [
                        'disciplina' => $nome,
                        'registros' => $itens,
                    ])->values(),
                'percentualGeral' => $resumoFreqDe($doAluno)['percentual'],
            ];
        });

        $semanasCal = [];
        $cursor = $inicioMes->copy()->startOfWeek();
        $ultimo = $fimMes->copy()->endOfWeek();
        while ($cursor->lte($ultimo)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++, $cursor->addDay()) {
                $semana[] = [
                    'data' => $cursor->copy(),
                    'do_mes' => $cursor->month === $mesCal->month,
                    'itens' => collect($itensCal[$cursor->toDateString()] ?? []),
                ];
            }
            $semanasCal[] = $semana;
        }

        return view('portal.turma', [
            'titulo' => 'Turma — '.$turma->nome,
            'breadcrumb' => $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Home', route('portal.index')],
                ['Minhas Turmas', route('portal.turmas')],
                [$turma->nome, null],
            ]),
            'alunos' => $alunos,
            'turma' => $turma,
            'matriculas' => $matriculas,
            'avisos' => $avisos,
            'materiais' => $materiais,
            'colegas' => $colegas,
            'tarefas' => $tarefas,
            'entregas' => $entregas,
            'notas' => $notas,
            'medias' => $medias,
            'mediasGerais' => $mediasGerais,
            'frequenciaBlocos' => $frequenciaBlocos,
            'itensCalJson' => collect($itensCal)->flatten(1)->keyBy('id'),
            'semanasCal' => $semanasCal,
            'rotuloMesCal' => ucfirst($mesCal->locale('pt_BR')->translatedFormat('F \d\e Y')),
            'mesAnteriorCal' => $inicioMes->copy()->subMonth()->format('Y-m'),
            'mesProximoCal' => $inicioMes->copy()->addMonth()->format('Y-m'),
            'ehMesAtualCal' => $mesCal->isSameMonth(now()),
            'aba' => $request->query('aba', 'avisos'),
        ]);
    }

    /**
     * Pagina da disciplina dentro da turma: rendimento do(s) aluno(s)
     * vinculados (notas, media e frequencia), conteudos publicados
     * e o forum da disciplina (topicos e respostas).
     */
    public function disciplinaShow($turmaId, $disciplinaId)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());
        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $turma = Turma::with(['escola', 'anoLetivo', 'etapa', 'atribuicoes.user', 'disciplinas'])
            ->findOrFail($turmaId);
        $matriculas = $this->turmaAutorizada($turma, $alunos, incluirConcluidas: true);
        $matriculaIds = $matriculas->pluck('id');

        $disciplina = $turma->disciplinas->firstWhere('id', (int) $disciplinaId);
        abort_unless($disciplina, 404, 'Disciplina não vinculada a esta turma.');

        $professor = $turma->atribuicoes
            ->where('disciplina_id', $disciplina->id)
            ->whereNotNull('user_id')
            ->sortByDesc(fn ($a) => $a->estaAtiva())
            ->first()?->user?->nome;

        $notas = Nota::query()
            ->whereIn('matricula_id', $matriculaIds)
            ->whereHas('avaliacao', fn ($q) => $q->where('disciplina_id', $disciplina->id))
            ->with(['avaliacao:id,nome,tipo,periodo,data,nota_maxima,disciplina_id,periodo_letivo_id',
                'avaliacao.periodoLetivo:id,nome,ordem'])
            ->get()
            ->groupBy('matricula_id')
            ->map(fn ($lista) => $lista->filter(fn ($n) => $n->avaliacao)
                ->sortBy(fn ($n) => $n->avaliacao->data)
                ->values());

        // media em % (nota * 100 / nota_maxima), mesma regra do boletim do portal
        $mediaDe = function ($lista) {
            $validas = $lista->filter(fn ($n) => $n->nota !== null && $n->avaliacao->nota_maxima > 0);

            return $validas->isEmpty() ? null
                : round($validas->avg(fn ($n) => $n->nota * 100 / $n->avaliacao->nota_maxima), 1);
        };
        $medias = $notas->map($mediaDe);

        // notas agrupadas por periodo letivo (bimestre, semestre...),
        // ordenadas pela ordem do periodo, com a media de cada um
        $notasPorPeriodo = $notas->map(fn ($lista) => $lista
            ->groupBy(fn ($n) => $n->avaliacao->periodo_letivo_id
                ? 'pl'.$n->avaliacao->periodo_letivo_id
                : 'tx'.$n->avaliacao->periodo)
            ->map(function ($grupo) use ($mediaDe) {
                $av = $grupo->first()->avaliacao;

                return [
                    'nome' => $av->periodoLetivo->nome ?? $av->periodo ?? 'Sem período',
                    'ordem' => $av->periodoLetivo->ordem ?? 999,
                    'media' => $mediaDe($grupo),
                    'notas' => $grupo->values(),
                ];
            })
            ->sortBy('ordem')
            ->values());

        $frequencias = Frequencia::query()
            ->whereIn('frequencias.matricula_id', $matriculaIds)
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->where('diarios.disciplina_id', $disciplina->id)
            ->orderByDesc('diarios.data')
            ->get(['frequencias.matricula_id', 'frequencias.status', 'frequencias.justificativa', 'diarios.data'])
            ->groupBy('matricula_id')
            ->map(function ($itens) {
                $porStatus = $itens->countBy('status');
                $total = $itens->count();
                $presencas = (int) $porStatus->get('presente', 0);

                return [
                    'total' => $total,
                    'presencas' => $presencas,
                    'faltas' => (int) $porStatus->get('falta', 0),
                    'justificadas' => (int) $porStatus->get('justificada', 0),
                    'percentual' => $total > 0 ? round($presencas / $total * 100) : null,
                    'registros' => $itens,
                ];
            });

        $materiais = TurmaMaterial::query()
            ->where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->with('autor:id,nome')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $tarefas = Tarefa::query()
            ->where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->where('ativo', 'S')
            ->with('questoes:id,tarefa_id')
            ->orderBy('data_entrega')
            ->limit(40)
            ->get();

        $entregas = EntregaTarefa::query()
            ->whereIn('tarefa_id', $tarefas->pluck('id'))
            ->whereIn('matricula_id', $matriculaIds)
            ->get()
            ->keyBy(fn ($e) => $e->tarefa_id.'|'.$e->matricula_id);

        // Responsavel acompanha a disciplina sem participar do forum
        $isResponsavel = $this->ehResponsavel();
        $posts = $isResponsavel ? collect() : $this->postsDoForum($turma, $disciplina);

        return view('portal.turma-disciplina', [
            'titulo' => $disciplina->nome.' — '.$turma->nome,
            'breadcrumb' => $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Home', route('portal.index')],
                ['Minhas Turmas', route('portal.turmas')],
                [$turma->nome, route('portal.turma', $turma->id)],
                [$disciplina->nome, null],
            ]),
            'turma' => $turma,
            'disciplina' => $disciplina,
            'professor' => $professor,
            'matriculas' => $matriculas,
            'notas' => $notas,
            'medias' => $medias,
            'notasPorPeriodo' => $notasPorPeriodo,
            'frequencias' => $frequencias,
            'materiais' => $materiais,
            'tarefas' => $tarefas,
            'entregas' => $entregas,
            'posts' => $posts,
            'isResponsavel' => $isResponsavel,
        ]);
    }

    /** Aluno abre um topico no forum da disciplina (responsavel nao participa). */
    public function forumStore(Request $request, $turmaId, $disciplinaId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis não participam do fórum da turma.');

        $turma = Turma::with('disciplinas:id')->findOrFail($turmaId);
        $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()));
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);
        abort_if($turma->finalizada(), 403, 'Esta turma já foi finalizada — o fórum está somente para consulta.');

        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:200'],
            'mensagem' => ['required', 'string', 'max:3000'],
        ]);

        TurmaForumPost::create($dados + [
            'empresa_id' => $turma->empresa_id,
            'escola_id' => $turma->escola_id,
            'turma_id' => $turma->id,
            'disciplina_id' => $disciplina->id,
            'user_id' => auth()->id(),
        ]);

        return redirect(route('portal.turma.disciplina', [$turma->id, $disciplina->id]).'#tab-forum')
            ->with('success', 'Tópico publicado no fórum.');
    }

    /** Autor edita o titulo do proprio topico (o professor edita pelo admin). */
    public function forumUpdate(Request $request, $turmaId, $disciplinaId, $postId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis não participam do fórum da turma.');

        $turma = Turma::with('disciplinas:id')->findOrFail($turmaId);
        $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()));
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);

        $post = $this->topicoDoForum($turma, $disciplina, $postId);
        abort_if($post->user_id !== auth()->id(), 403, 'Só o autor pode editar este tópico.');

        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:200'],
        ]);

        $post->update(['titulo' => $dados['titulo'] ?? null]);

        return redirect(route('portal.turma.disciplina', [$turma->id, $disciplina->id]).'#tab-forum')
            ->with('success', 'Título da conversa atualizado.');
    }

    /** Autor encerra/reabre a propria conversa (sem novas respostas, historico mantido). */
    public function forumToggleEncerrada($turmaId, $disciplinaId, $postId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis não participam do fórum da turma.');

        $turma = Turma::with('disciplinas:id')->findOrFail($turmaId);
        $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()));
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);

        $post = $this->topicoDoForum($turma, $disciplina, $postId);
        abort_if($post->user_id !== auth()->id(), 403, 'Só o autor pode encerrar esta conversa.');

        $encerrada = $post->estaEncerrada();
        $post->update([
            'encerrada_em' => $encerrada ? null : now(),
            'encerrada_por' => $encerrada ? null : auth()->id(),
        ]);

        return redirect(route('portal.turma.disciplina', [$turma->id, $disciplina->id]).'#tab-forum')
            ->with('success', $encerrada ? 'Conversa reaberta.' : 'Conversa encerrada — ninguém mais pode responder.');
    }

    /** Comentario em um topico do forum da disciplina (responsavel nao participa). */
    public function forumComentar(Request $request, $turmaId, $disciplinaId, $postId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis não participam do fórum da turma.');

        $turma = Turma::with('disciplinas:id')->findOrFail($turmaId);
        $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()));
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);
        abort_if($turma->finalizada(), 403, 'Esta turma já foi finalizada — o fórum está somente para consulta.');

        $post = $this->topicoDoForum($turma, $disciplina, $postId);
        abort_if($post->estaEncerrada(), 403, 'Esta conversa foi encerrada e não aceita novas respostas.');

        $request->validate(['mensagem' => ['required', 'string', 'max:2000']]);

        TurmaForumPost::create([
            'empresa_id' => $turma->empresa_id,
            'escola_id' => $turma->escola_id,
            'turma_id' => $turma->id,
            'disciplina_id' => $disciplina->id,
            'parent_id' => $post->id,
            'user_id' => auth()->id(),
            'mensagem' => $request->input('mensagem'),
        ]);

        return redirect(route('portal.turma.disciplina', [$turma->id, $disciplina->id]).'#tab-forum')
            ->with('toast_success', 'Comentário publicado.');
    }

    /**
     * Marca um topico como lido ate agora — chamado pela tela quando
     * a conversa e aberta (estilo WhatsApp: abrir ja conta como lida).
     */
    public function forumMarcarLida($turmaId, $disciplinaId, $postId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis não participam do fórum da turma.');

        $turma = Turma::with('disciplinas:id')->findOrFail($turmaId);
        $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()), incluirConcluidas: true);
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);
        $post = $this->topicoDoForum($turma, $disciplina, $postId);

        $this->marcarTopicoLido($turma, $post->id);

        return response()->json(['ok' => true]);
    }

    /**
     * HTML do chat do forum para o polling da tela (a cada 5s).
     * Retorna uma assinatura (md5) junto: a pagina so re-renderiza
     * quando a assinatura muda, preservando o que o usuario digita.
     * Com ?aberta=<post>, marca a conversa aberta como lida antes de
     * renderizar — mensagens novas vistas na tela nao viram "nao lidas".
     */
    public function forumPosts(Request $request, $turmaId, $disciplinaId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis não participam do fórum da turma.');

        $turma = Turma::with('disciplinas:id')->findOrFail($turmaId);
        $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()), incluirConcluidas: true);
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);

        if ($aberta = (int) $request->query('aberta')) {
            $valida = TurmaForumPost::where('turma_id', $turma->id)
                ->where('disciplina_id', $disciplina->id)
                ->whereNull('parent_id')
                ->where('id', $aberta)
                ->exists();
            if ($valida) {
                $this->marcarTopicoLido($turma, $aberta);
            }
        }

        $posts = $this->postsDoForum($turma, $disciplina);
        $html = view('portal.partials.forum-chat', [
            'turma' => $turma,
            'disciplina' => $disciplina,
            'posts' => $posts,
        ])->render();

        return response()->json([
            'sig' => md5($html),
            'html' => $html,
            'total' => $posts->count(),
        ]);
    }

    private function topicoDoForum(Turma $turma, Disciplina $disciplina, $postId): TurmaForumPost
    {
        return TurmaForumPost::where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->whereNull('parent_id')
            ->findOrFail($postId);
    }

    private function postsDoForum(Turma $turma, Disciplina $disciplina): Collection
    {
        $posts = TurmaForumPost::query()
            ->where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->whereNull('parent_id')
            ->with(['autor:id,nome,foto_perfil', 'respostas.autor:id,nome,foto_perfil'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        // Nao lidas por conversa (estilo WhatsApp): mensagens de outros
        // postadas depois do lido_em do usuario; sem registro, tudo conta.
        $lidasEm = TurmaForumLeitura::where('user_id', auth()->id())
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('lido_em', 'post_id');
        $euId = auth()->id();

        foreach ($posts as $post) {
            $lidoEm = $lidasEm->get($post->id);
            $naoLidas = collect([$post])
                ->concat($post->respostas)
                ->filter(fn ($msg) => $msg->user_id !== $euId
                    && ($lidoEm === null || $msg->created_at?->gt($lidoEm)));

            $post->nao_lidas = $naoLidas->count();
            $post->primeira_nao_lida_id = $naoLidas->first()?->id;
        }

        return $posts;
    }

    /** Registra que o usuario leu o topico ate este instante. */
    private function marcarTopicoLido(Turma $turma, int $postId): void
    {
        TurmaForumLeitura::updateOrCreate(
            ['post_id' => $postId, 'user_id' => auth()->id()],
            ['empresa_id' => $turma->empresa_id, 'escola_id' => $turma->escola_id, 'lido_em' => now()]
        );
    }

    /** Tela da atividade/prova com questoes — pensada para celular. */
    public function tarefaShow($turmaId, $tarefaId)
    {
        $turma = Turma::with('disciplinas')->findOrFail($turmaId);
        $matriculas = $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()), incluirConcluidas: true);

        return $this->renderTarefa($turma, $this->tarefaDaTurma($turma, $tarefaId), $matriculas);
    }

    /**
     * Mesma tela da atividade, mas dentro do contexto da disciplina:
     * a URL ganha um segmento e a trilha mostra turma > disciplina > tarefa.
     */
    public function disciplinaTarefaShow($turmaId, $disciplinaId, $tarefaId)
    {
        $turma = Turma::with('disciplinas')->findOrFail($turmaId);
        $matriculas = $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()), incluirConcluidas: true);
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);

        return $this->renderTarefa(
            $turma, $this->tarefaDaTurma($turma, $tarefaId, $disciplina), $matriculas, $disciplina
        );
    }

    private function renderTarefa(Turma $turma, Tarefa $tarefa, Collection $matriculas, ?Disciplina $disciplina = null)
    {
        $entregas = EntregaTarefa::where('tarefa_id', $tarefa->id)
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->with(['respostas', 'anexos'])
            ->get()
            ->keyBy('matricula_id');

        // Uma conta com varios alunos escolhe qual entrega ver/fazer
        $matriculaId = (int) request('matricula_id', $matriculas->first()->id);
        abort_unless($matriculas->contains('id', $matriculaId), 403);

        $trilha = [
            ['Home', route('portal.index')],
            ['Minhas Turmas', route('portal.turmas')],
            [$turma->nome, route('portal.turma', $turma->id)],
        ];
        if ($disciplina) {
            $trilha[] = [$disciplina->nome, route('portal.turma.disciplina', [$turma->id, $disciplina->id])];
        }
        $trilha[] = [$tarefa->titulo, null];

        return view('portal.tarefa-responder', [
            'titulo' => $tarefa->titulo,
            'breadcrumb' => $this->breadcrumbService->prepararBreadcrumbCustom($trilha),
            'turma' => $turma,
            'disciplina' => $disciplina,
            'tarefa' => $tarefa,
            'matriculas' => $matriculas,
            'matricula' => $matriculas->firstWhere('id', $matriculaId),
            'entrega' => $entregas->get($matriculaId),
            'respostas' => $entregas->get($matriculaId)?->respostas->keyBy('questao_id') ?? collect(),
            'isResponsavel' => $this->ehResponsavel(),
            'rotaPost' => $disciplina
                ? route('portal.turma.disciplina.tarefa.responder', [$turma->id, $disciplina->id, $tarefa->id])
                : route('portal.turma.tarefa.responder', [$turma->id, $tarefa->id]),
        ]);
    }

    /** Salva as respostas das questoes e marca a entrega — so o aluno envia. */
    public function tarefaResponder(Request $request, $turmaId, $tarefaId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis apenas acompanham atividades — quem responde é o aluno.');

        $turma = Turma::findOrFail($turmaId);
        $matriculas = $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()));

        $tarefa = $this->tarefaDaTurma($turma, $tarefaId);
        $matricula = $this->matriculaDaEntrega($request, $matriculas);
        $mensagem = $this->processarEnvioTarefa($request, $tarefa, $matricula);

        return redirect()->route('portal.turma.tarefa', [$turma->id, $tarefa->id, 'matricula_id' => $matricula->id])
            ->with('success', $mensagem);
    }

    /** Envio feito a partir da tela da disciplina — volta para ela (so o aluno envia). */
    public function disciplinaTarefaResponder(Request $request, $turmaId, $disciplinaId, $tarefaId)
    {
        abort_if($this->ehResponsavel(), 403, 'Responsáveis apenas acompanham atividades — quem responde é o aluno.');

        $turma = Turma::with('disciplinas')->findOrFail($turmaId);
        $matriculas = $this->turmaAutorizada($turma, $this->alunosDoUsuario(auth()->user()));
        $disciplina = $this->disciplinaDaTurma($turma, $disciplinaId);

        $tarefa = $this->tarefaDaTurma($turma, $tarefaId, $disciplina);
        $matricula = $this->matriculaDaEntrega($request, $matriculas);
        $mensagem = $this->processarEnvioTarefa($request, $tarefa, $matricula);

        return redirect()->route('portal.turma.disciplina.tarefa', [$turma->id, $disciplina->id, $tarefa->id, 'matricula_id' => $matricula->id])
            ->with('success', $mensagem);
    }

    private function disciplinaDaTurma(Turma $turma, $disciplinaId): Disciplina
    {
        $disciplina = $turma->disciplinas->firstWhere('id', (int) $disciplinaId);
        abort_unless($disciplina, 404, 'Disciplina não vinculada a esta turma.');

        return $disciplina;
    }

    private function tarefaDaTurma(Turma $turma, $tarefaId, ?Disciplina $disciplina = null): Tarefa
    {
        return Tarefa::where('turma_id', $turma->id)->where('ativo', 'S')
            ->when($disciplina, fn ($q) => $q->where('disciplina_id', $disciplina->id))
            ->with(['disciplina:id,nome', 'questoes'])
            ->findOrFail($tarefaId);
    }

    private function matriculaDaEntrega(Request $request, Collection $matriculas): Matricula
    {
        $matricula = $matriculas->firstWhere('id', (int) $request->input('matricula_id'));
        abort_unless($matricula, 403, 'Matrícula inválida para esta conta.');

        return $matricula;
    }

    /**
     * Dois modos de envio pelo aluno: 'entrega' (confirmacao de leitura,
     * texto digitado ou fotos, conforme tipo_entrega da tarefa) e o
     * padrao, respostas das questoes online.
     */
    private function processarEnvioTarefa(Request $request, Tarefa $tarefa, Matricula $matricula): string
    {
        if ($request->input('modo') === 'entrega' && $tarefa->exigeEntregaOnline()) {
            return $this->processarEntregaPortal($request, $tarefa, $matricula);
        }

        return $this->processarRespostasQuestoes($request, $tarefa, $matricula);
    }

    /**
     * Entrega simples: leitura so confirma; texto grava a resposta;
     * foto anexa os arquivos enviados (comentario opcional).
     */
    private function processarEntregaPortal(Request $request, Tarefa $tarefa, Matricula $matricula): string
    {
        $entregaAtual = EntregaTarefa::where('tarefa_id', $tarefa->id)
            ->where('matricula_id', $matricula->id)
            ->withCount('anexos')
            ->first();

        $regras = match ($tarefa->tipo_entrega) {
            'texto' => ['texto' => ['required', 'string', 'max:4000']],
            'foto' => [
                'texto' => ['nullable', 'string', 'max:4000'],
                'anexos' => ['nullable', 'array', 'max:5'],
                'anexos.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            ],
            default => [],
        };
        $request->validate($regras);

        if ($tarefa->tipo_entrega === 'foto'
            && ! $request->hasFile('anexos')
            && ! ($entregaAtual?->anexos_count > 0)) {
            throw ValidationException::withMessages(['anexos' => 'Envie ao menos uma foto ou arquivo.']);
        }

        DB::transaction(function () use ($request, $tarefa, $matricula) {
            $entrega = EntregaTarefa::firstOrCreate(
                ['tarefa_id' => $tarefa->id, 'matricula_id' => $matricula->id],
                ['empresa_id' => $tarefa->empresa_id, 'escola_id' => $tarefa->escola_id, 'status' => 'pendente']
            );

            abort_if($entrega->status === 'avaliada', 403, 'Esta entrega já foi avaliada.');

            if (in_array($tarefa->tipo_entrega, ['texto', 'foto'], true)) {
                $entrega->resposta = trim((string) $request->input('texto', '')) ?: $entrega->resposta;
            }

            foreach ((array) $request->file('anexos', []) as $arquivo) {
                if (! $arquivo) {
                    continue;
                }
                $entrega->anexos()->create([
                    'empresa_id' => $tarefa->empresa_id,
                    'arquivo_path' => $arquivo->store(
                        "empresas/{$tarefa->empresa_id}/entregas/{$entrega->id}", 'public'
                    ),
                    'nome_original' => $arquivo->getClientOriginalName(),
                ]);
            }

            $entrega->update([
                'status' => 'entregue',
                'data_entrega' => now()->toDateString(),
            ]);
        });

        return $tarefa->tipo_entrega === 'leitura' ? 'Leitura confirmada.' : 'Entrega enviada para correção.';
    }

    /** Respostas das questoes online com nota automatica nas objetivas. */
    private function processarRespostasQuestoes(Request $request, Tarefa $tarefa, Matricula $matricula): string
    {
        $request->validate([
            'respostas' => ['required', 'array'],
            'respostas.*' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $tarefa, $matricula) {
            $entrega = EntregaTarefa::firstOrCreate(
                ['tarefa_id' => $tarefa->id, 'matricula_id' => $matricula->id],
                ['empresa_id' => $tarefa->empresa_id, 'escola_id' => $tarefa->escola_id, 'status' => 'pendente']
            );

            abort_if($entrega->status === 'avaliada', 403, 'Esta entrega já foi avaliada.');

            $temDiscursiva = $tarefa->questoes->contains(fn ($q) => ! $q->ehObjetiva());

            foreach ($tarefa->questoes as $questao) {
                $resposta = trim((string) ($request->input('respostas')[$questao->id] ?? ''));
                $correta = null;
                if ($questao->ehObjetiva() && $resposta !== '') {
                    $correta = $questao->gabarito !== null && strtoupper($resposta) === strtoupper((string) $questao->gabarito);
                }

                EntregaTarefaResposta::updateOrCreate(
                    ['entrega_id' => $entrega->id, 'questao_id' => $questao->id],
                    ['empresa_id' => $tarefa->empresa_id, 'resposta' => $resposta, 'correta' => $correta]
                );
            }

            // Nota automatica quando todas as questoes sao objetivas
            $nota = null;
            if (! $temDiscursiva && $tarefa->questoes->isNotEmpty()) {
                $corretas = EntregaTarefaResposta::where('entrega_id', $entrega->id)->where('correta', true)->count();
                $nota = $tarefa->nota_maxima !== null
                    ? round($corretas / $tarefa->questoes->count() * $tarefa->nota_maxima, 2)
                    : $corretas;
            }

            $entrega->update([
                'status' => $temDiscursiva ? 'entregue' : 'avaliada',
                'nota' => $nota,
                'data_entrega' => now()->toDateString(),
            ]);
        });

        return 'Respostas enviadas.';
    }

    /** Conta de responsavel: acompanha os alunos, mas nao responde tarefa nem usa forum. */
    private function ehResponsavel(): bool
    {
        return auth()->user()->tipoConta() === \App\Models\User::TIPO_CONTA_RESPONSAVEL;
    }

    private function alunosDoUsuario($user): Collection
    {
        $ids = collect();

        $responsavel = Responsavel::query()->where('user_id', $user->id)->first();
        if ($responsavel) {
            $ids = $ids->merge($responsavel->alunos()->pluck('alunos.id'));
        }

        $proprio = \App\Models\Aluno::query()->where('user_id', $user->id)->first();
        if ($proprio) {
            $ids->push($proprio->id);
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return \App\Models\Aluno::query()->with('pessoa')->whereIn('id', $ids->unique())->get();
    }
}
