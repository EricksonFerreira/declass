<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Responsavel;
use App\Services\AvisoService;
use App\Services\BoletimService;
use App\Services\BreadcrumbService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    /** Telas do portal que usam o filtro padrao (sessao), como no admin. */
    private const TELAS_FILTRO = [
        'disciplinas' => 'portal.disciplinas',
        'horarios' => 'portal.horarios',
        'turmas' => 'portal.turmas',
        'frequencia' => 'portal.frequencia',
        'alertas' => 'portal.alertas',
        'avisos' => 'portal.avisos',
        'agenda' => 'portal.agenda',
        'financeiro' => 'portal.financeiro',
        'solicitacoes' => 'portal.solicitacoes',
        'notas' => 'portal.notas',
        'tarefas' => 'portal.tarefas',
    ];

    public function __construct(
        private BoletimService $boletimService,
        private AvisoService $avisoService,
        private BreadcrumbService $breadcrumbService,
    ) {}

    /**
     * Home do portal: lista os alunos vinculados ao usuario logado
     * (como responsavel ou como aluno).
     */
    public function index()
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with('turma.anoLetivo', 'turma.etapa')
            ->get()
            ->keyBy('aluno_id');

        $tarefasPendentes = $this->tarefasPendentesQuery($matriculas)->count();

        $mensalidadesAbertas = \App\Models\Mensalidade::query()
            ->whereHas('matricula', fn ($q) => $q->whereIn('aluno_id', $alunos->pluck('id')))
            ->where('status', 'pendente')
            ->count();

        return view('portal.index', [
            'titulo' => 'Portal Familiar',
            'breadcrumb' => [['name' => 'Home', 'active' => true]],
            'alunos' => $alunos,
            'matriculas' => $matriculas,
            'tarefasPendentes' => $tarefasPendentes,
            'mensalidadesAbertas' => $mensalidadesAbertas,
            'avisosCount' => $this->avisoService->avisosParaAlunos($alunos)->count(),
            'eventosCount' => app(\App\Services\EventoService::class)->eventosParaAlunos($alunos)->count(),
        ]);
    }

    /**
     * Disciplinas das turmas dos alunos vinculados: nome,
     * turma e professor atribuido — a "grade" do aluno.
     * Filtravel por aluno, turma e situacao (em andamento/encerradas).
     */
    public function disciplinas()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with(['aluno.pessoa', 'turma.disciplinas', 'turma.escola',
                'turma.anoLetivo', 'turma.atribuicoes.user'])
            ->get()
            ->sortBy(fn ($m) => $m->aluno->nome.'|'.($m->turma->nome ?? ''));

        $turmas = $matriculas->pluck('turma')->filter()->unique('id')
            ->sortBy('nome')->sortByDesc(fn ($t) => $t->anoLetivo->ano ?? 0)
            ->values();

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'disciplinas',
            [
                $this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos)),
                $this->filtroSelect('turma_id', 'Turma', $turmas->mapWithKeys(fn ($t) => [
                    $t->id => $t->nome.($t->anoLetivo ? ' — '.$t->anoLetivo->ano : ''),
                ])),
            ],
            [
                $this->filtroSelect('situacao', 'Situação', [
                    'andamento' => 'Em andamento',
                    'encerradas' => 'Encerradas',
                    'todas' => 'Todas',
                ], 'Em andamento'),
            ]
        );

        $alunoFiltro = (int) ($itensFiltered['aluno_id']['valor'] ?? 0);
        $turmaFiltro = (int) ($itensFiltered['turma_id']['valor'] ?? 0);
        $situacao = ($itensFiltered['situacao']['valor'] ?? '') ?: 'andamento';
        abort_unless(in_array($situacao, ['andamento', 'encerradas', 'todas'], true), 404);

        $emAndamento = fn (Matricula $m) => $m->status === 'ativa'
            && ($m->turma?->anoLetivo?->status ?? 'letivo') !== 'encerrado';

        $matriculas = $matriculas
            ->when($alunoFiltro, fn ($c) => $c->where('aluno_id', $alunoFiltro))
            ->when($turmaFiltro, fn ($c) => $c->where('turma_id', $turmaFiltro))
            ->when($situacao === 'andamento', fn ($c) => $c->filter($emAndamento))
            ->when($situacao === 'encerradas', fn ($c) => $c->reject($emAndamento));

        $itens = $matriculas->flatMap(function ($matricula) use ($emAndamento) {
            $turma = $matricula->turma;
            if (! $turma) {
                return [];
            }

            return $turma->disciplinas->map(function ($disciplina) use ($turma, $matricula, $emAndamento) {
                $professor = $turma->atribuicoes
                    ->where('disciplina_id', $disciplina->id)
                    ->whereNotNull('user_id')
                    ->sortByDesc(fn ($a) => $a->estaAtiva())
                    ->first()?->user?->nome;

                return [
                    'disciplina' => $disciplina,
                    'professor' => $professor,
                    'turma' => $turma,
                    'matricula' => $matricula,
                    'emAndamento' => $emAndamento($matricula),
                ];
            });
        })
            ->sortBy(fn ($i) => $i['turma']->nome.'|'.$i['disciplina']->nome)
            ->values();

        return view('portal.disciplinas', [
            'titulo' => 'Minhas Disciplinas',
            'breadcrumb' => $this->breadcrumbPortal([['Minhas Disciplinas', null]]),
            'alunos' => $alunos,
            'itens' => $itens,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /** Turmas dos alunos vinculados — entrada do "classroom" (inclui anos concluidos). */
    public function turmas()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->whereIn('status', ['ativa', 'concluida'])
            ->with(['aluno.pessoa', 'turma.escola', 'turma.etapa', 'turma.anoLetivo', 'turma.disciplinas'])
            ->get()
            ->sortBy(fn ($m) => $m->aluno->nome.'|'.($m->turma->nome ?? ''));

        $anosLetivos = $matriculas
            ->pluck('turma.anoLetivo')
            ->filter()
            ->unique('id')
            ->sortByDesc('ano')
            ->values();

        // Conta de aluno so ve a si mesmo — sem filtro "Aluno".
        $isAluno = auth()->user()->tipoConta() === \App\Models\User::TIPO_CONTA_ALUNO;

        $filtrosPrincipal = $isAluno
            ? []
            : [$this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos))];
        $filtrosPrincipal[] = $this->filtroSelect('ano_letivo_id', 'Ano letivo', $anosLetivos->mapWithKeys(fn ($a) => [$a->id => $a->ano]));
        $filtrosPrincipal[] = $this->filtroSelect('situacao', 'Situação', [
            'andamento' => 'Em andamento',
            'encerrado' => 'Encerrado',
        ], 'Todos');

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'turmas',
            $filtrosPrincipal
        );

        $alunoFiltro = (int) ($itensFiltered['aluno_id']['valor'] ?? 0);
        $anoFiltro = (int) ($itensFiltered['ano_letivo_id']['valor'] ?? 0);
        $situacaoFiltro = (string) ($itensFiltered['situacao']['valor'] ?? '');

        $matriculas = $matriculas
            ->when($alunoFiltro, fn ($c) => $c->where('aluno_id', $alunoFiltro))
            ->when($anoFiltro, fn ($c) => $c->filter(
                fn ($m) => (int) $m->turma->ano_letivo_id === $anoFiltro
            ))
            ->when($situacaoFiltro === 'andamento', fn ($c) => $c->filter(
                fn ($m) => ($m->turma->anoLetivo?->status ?? 'letivo') !== 'encerrado'
            ))
            ->when($situacaoFiltro === 'encerrado', fn ($c) => $c->filter(
                fn ($m) => ($m->turma->anoLetivo?->status ?? 'letivo') === 'encerrado'
            ));

        $resultados = $this->boletimService->resultadoAprovacaoPorMatricula($matriculas);

        return view('portal.turmas', [
            'titulo' => 'Minhas Turmas',
            'breadcrumb' => $this->breadcrumbPortal([['Minhas Turmas', null]]),
            'alunos' => $alunos,
            'matriculas' => $matriculas,
            'resultados' => $resultados,
            'isAluno' => $isAluno,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Grade semanal do aluno: horarios de aula da(s) turma(s) em
     * que ele tem matricula ativa — disciplina, professor e sala
     * em cada dia/periodo da semana. Somente leitura.
     */
    public function horarios()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $variosAlunos = $alunos->count() > 1;

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with('aluno.pessoa', 'turma.escola', 'turma.anoLetivo')
            ->get()
            ->sortByDesc(fn ($m) => $m->turma?->anoLetivo?->ano ?? 0)
            ->values();

        $turmasOpcoes = $matriculas
            ->filter(fn ($m) => $m->turma)
            ->mapWithKeys(fn ($m) => [
                $m->turma_id => $m->turma->nome
                    .($m->turma->anoLetivo ? ' — '.$m->turma->anoLetivo->ano : '')
                    .($variosAlunos ? ' · '.($m->aluno->pessoa->nome ?? $m->aluno->nome) : ''),
            ]);

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'horarios',
            [
                $this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos)),
                $this->filtroSelect('turma_id', 'Turma', $turmasOpcoes, 'Todas'),
            ]
        );

        // Primeira visita (sem filtro gravado): a turma do ano letivo
        // mais recente vem pre-selecionada apenas no select — sem
        // injetar em $itensFiltered, para nao exibir badge de filtro
        // aplicado que o usuario nao escolheu.
        $turmaPadrao = array_key_exists('turma_id', $itensFiltered)
            ? null
            : $matriculas->first()?->turma_id;

        if ($turmaPadrao) {
            foreach ($filters as &$filtro) {
                if (($filtro['name'] ?? '') !== 'turma_id') {
                    continue;
                }
                foreach ($filtro['options'] as &$opcao) {
                    $opcao['selected'] = $opcao['value'] == $turmaPadrao;
                }
                unset($opcao);
            }
            unset($filtro);
        }

        $alunoFiltro = (int) ($itensFiltered['aluno_id']['valor'] ?? 0);
        $turmaFiltro = (int) ($itensFiltered['turma_id']['valor'] ?? 0) ?: $turmaPadrao;

        $matriculas = $matriculas
            ->when($alunoFiltro, fn ($c) => $c->where('aluno_id', $alunoFiltro))
            ->when($turmaFiltro, fn ($c) => $c->where('turma_id', $turmaFiltro))
            ->values();

        $gradeService = app(\App\Services\TurmaGradeService::class);

        $grades = $matriculas
            ->filter(fn ($m) => $m->turma)
            ->map(fn ($m) => ['matricula' => $m] + $gradeService->gradeDaTurma($m->turma))
            ->values();

        return view('portal.horarios', [
            'titulo' => 'Meus Horários',
            'breadcrumb' => $this->breadcrumbPortal([['Meus Horários', null]]),
            'alunos' => $alunos,
            'aluno' => $alunoFiltro ? $alunos->firstWhere('id', $alunoFiltro) : null,
            'grades' => $grades,
            'diasLabels' => \App\Models\HorarioSlot::DIAS,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Frequencia consolidada por disciplina: percentual de
     * presenca e total de faltas/justificadas por matricula.
     * Filtravel por turma, disciplina, periodo letivo e
     * situacao (em andamento/encerradas).
     */
    public function frequencia()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with('aluno.pessoa', 'turma.anoLetivo', 'turma.disciplinas')
            ->get()
            ->sortBy(fn ($m) => $m->aluno->nome.'|'.($m->turma->nome ?? ''))
            ->values();

        $turmas = $matriculas->pluck('turma')->filter()->unique('id')
            ->sortBy('nome')->sortByDesc(fn ($t) => $t->anoLetivo->ano ?? 0)
            ->values();

        $disciplinasOpcoes = $turmas->flatMap->disciplinas
            ->unique('id')->sortBy('nome')->pluck('nome', 'id');

        $periodosLista = \App\Models\PeriodoLetivo::query()
            ->whereIn('ano_letivo_id', $turmas->pluck('ano_letivo_id')->filter())
            ->with('anoLetivo:id,ano')
            ->orderBy('ordem')
            ->get()
            ->sortByDesc(fn ($p) => $p->anoLetivo->ano ?? 0)
            ->values();

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'frequencia',
            [
                $this->filtroSelect('turma_id', 'Turma', $turmas->mapWithKeys(fn ($t) => [
                    $t->id => $t->nome.($t->anoLetivo ? ' — '.$t->anoLetivo->ano : ''),
                ])),
                $this->filtroSelect('disciplina_id', 'Disciplina', $disciplinasOpcoes),
                $this->filtroSelect('periodo_letivo_id', 'Período letivo', $periodosLista
                    ->mapWithKeys(fn ($p) => [$p->id => $p->nome.($p->anoLetivo ? ' — '.$p->anoLetivo->ano : '')])),
            ],
            [
                $this->filtroSelect('situacao', 'Situação', [
                    'andamento' => 'Em andamento',
                    'encerradas' => 'Encerradas',
                    'todas' => 'Todas',
                ], 'Em andamento'),
            ]
        );

        $turmaFiltro = (int) ($itensFiltered['turma_id']['valor'] ?? 0);
        $disciplinaFiltro = (int) ($itensFiltered['disciplina_id']['valor'] ?? 0);
        $periodoSel = $periodosLista
            ->firstWhere('id', (int) ($itensFiltered['periodo_letivo_id']['valor'] ?? 0));
        $situacao = ($itensFiltered['situacao']['valor'] ?? '') ?: 'andamento';
        abort_unless(in_array($situacao, ['andamento', 'encerradas', 'todas'], true), 404);

        $emAndamento = fn (Matricula $m) => $m->status === 'ativa'
            && ($m->turma?->anoLetivo?->status ?? 'letivo') !== 'encerrado';

        $selecionadas = $matriculas
            ->when($turmaFiltro, fn ($c) => $c->where('turma_id', $turmaFiltro))
            ->when($situacao === 'andamento', fn ($c) => $c->filter($emAndamento))
            ->when($situacao === 'encerradas', fn ($c) => $c->reject($emAndamento))
            ->values();

        $resumoDe = function ($itens) {
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

        $blocos = collect();

        if ($selecionadas->isNotEmpty()) {
            $registros = \App\Models\Frequencia::query()
                ->whereIn('frequencias.matricula_id', $selecionadas->pluck('id'))
                ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
                ->leftJoin('disciplinas', 'diarios.disciplina_id', '=', 'disciplinas.id')
                ->when($disciplinaFiltro, fn ($q) => $q->where('diarios.disciplina_id', $disciplinaFiltro))
                ->when($periodoSel, fn ($q) => $q->whereBetween('diarios.data', [
                    $periodoSel->data_inicio, $periodoSel->data_fim,
                ]))
                ->orderByDesc('diarios.data')
                ->get([
                    'frequencias.matricula_id',
                    'frequencias.status',
                    'frequencias.justificativa',
                    'diarios.data',
                    DB::raw("coalesce(disciplinas.nome, 'Sem disciplina') as disciplina"),
                ]);

            $blocos = $selecionadas->map(function ($matricula) use ($registros, $resumoDe) {
                $doAluno = $registros->where('matricula_id', $matricula->id);

                return [
                    'matricula' => $matricula,
                    'disciplinas' => $doAluno->groupBy('disciplina')->sortKeys()
                        ->map(fn ($itens, $nome) => $resumoDe($itens) + [
                            'disciplina' => $nome,
                            'registros' => $itens,
                        ])->values(),
                    'percentualGeral' => $resumoDe($doAluno)['percentual'],
                ];
            });
        }

        return view('portal.frequencia', [
            'titulo' => 'Frequência',
            'breadcrumb' => $this->breadcrumbPortal([['Frequência', null]]),
            'alunos' => $alunos,
            'matriculas' => $matriculas,
            'blocos' => $blocos,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Notas lancadas: por matricula e disciplina, com
     * avaliacao, periodo e nota maxima. Filtravel por
     * turma, disciplina, periodo e situacao (em
     * andamento/encerradas).
     */
    public function notas()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with('aluno.pessoa', 'turma.anoLetivo', 'turma.disciplinas')
            ->get()
            ->sortBy(fn ($m) => $m->aluno->nome.'|'.($m->turma->nome ?? ''))
            ->values();

        $turmas = $matriculas->pluck('turma')->filter()->unique('id')
            ->sortBy('nome')->sortByDesc(fn ($t) => $t->anoLetivo->ano ?? 0)
            ->values();

        $disciplinasOpcoes = $turmas->flatMap->disciplinas
            ->unique('id')->sortBy('nome')->pluck('nome', 'id');

        $periodos = \App\Models\PeriodoLetivo::query()
            ->whereIn('ano_letivo_id', $turmas->pluck('ano_letivo_id')->filter())
            ->with('anoLetivo:id,ano')
            ->orderBy('ordem')
            ->get()
            ->sortByDesc(fn ($p) => $p->anoLetivo->ano ?? 0)
            ->mapWithKeys(fn ($p) => [$p->id => $p->nome.($p->anoLetivo ? ' — '.$p->anoLetivo->ano : '')]);

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'notas',
            [
                $this->filtroSelect('turma_id', 'Turma', $turmas->mapWithKeys(fn ($t) => [
                    $t->id => $t->nome.($t->anoLetivo ? ' — '.$t->anoLetivo->ano : ''),
                ])),
                $this->filtroSelect('disciplina_id', 'Disciplina', $disciplinasOpcoes),
                $this->filtroSelect('periodo_letivo_id', 'Período letivo', $periodos),
            ],
            [
                $this->filtroSelect('situacao', 'Situação', [
                    'andamento' => 'Em andamento',
                    'encerradas' => 'Encerradas',
                    'todas' => 'Todas',
                ], 'Em andamento'),
            ]
        );

        $turmaFiltro = (int) ($itensFiltered['turma_id']['valor'] ?? 0);
        $disciplinaFiltro = (int) ($itensFiltered['disciplina_id']['valor'] ?? 0);
        $periodoFiltro = (int) ($itensFiltered['periodo_letivo_id']['valor'] ?? 0);
        $situacao = ($itensFiltered['situacao']['valor'] ?? '') ?: 'andamento';
        abort_unless(in_array($situacao, ['andamento', 'encerradas', 'todas'], true), 404);

        $emAndamento = fn (Matricula $m) => $m->status === 'ativa'
            && ($m->turma?->anoLetivo?->status ?? 'letivo') !== 'encerrado';

        $selecionadas = $matriculas
            ->when($turmaFiltro, fn ($c) => $c->where('turma_id', $turmaFiltro))
            ->when($situacao === 'andamento', fn ($c) => $c->filter($emAndamento))
            ->when($situacao === 'encerradas', fn ($c) => $c->reject($emAndamento))
            ->values();

        $notas = \App\Models\Nota::query()
            ->whereIn('matricula_id', $selecionadas->pluck('id'))
            ->whereHas('avaliacao', fn ($q) => $q
                ->when($disciplinaFiltro, fn ($q) => $q->where('disciplina_id', $disciplinaFiltro))
                ->when($periodoFiltro, fn ($q) => $q->where('periodo_letivo_id', $periodoFiltro)))
            ->with(['avaliacao:id,nome,tipo,periodo,data,nota_maxima,disciplina_id,periodo_letivo_id',
                'avaliacao.disciplina:id,nome,sigla', 'avaliacao.periodoLetivo:id,nome,ordem'])
            ->get()
            ->groupBy('matricula_id')
            ->map(fn ($lista) => $lista
                ->filter(fn ($n) => $n->avaliacao)
                ->sortBy(fn ($n) => $n->avaliacao->data)
                ->groupBy(fn ($n) => $n->avaliacao->disciplina->nome ?? 'Sem disciplina')
                ->sortKeys());

        // media em % (nota * 100 / nota_maxima), mesma regra do historico escolar
        $mediaDe = function ($lista) {
            $validas = $lista->filter(fn ($n) => $n->nota !== null && $n->avaliacao->nota_maxima > 0);

            return $validas->isEmpty() ? null
                : round($validas->avg(fn ($n) => $n->nota * 100 / $n->avaliacao->nota_maxima), 1);
        };

        $medias = $notas->map(fn ($disciplinas) => $disciplinas->map($mediaDe));

        return view('portal.notas', [
            'titulo' => 'Notas',
            'breadcrumb' => $this->breadcrumbPortal([['Notas', null]]),
            'alunos' => $alunos,
            'matriculas' => $matriculas,
            'selecionadas' => $selecionadas,
            'notas' => $notas,
            'medias' => $medias,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Alertas da familia: ocorrencias dos alunos marcadas
     * para notificacao do responsavel + avisos nao lidos.
     */
    public function alertas()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'alertas',
            [
                $this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos)),
                $this->filtroSelect('tipo', 'Tipo', \App\Models\Ocorrencia::TIPOS),
            ],
            [
                ['name' => 'data', 'label' => 'Data', 'type' => 'date', 'typeWhere' => 'where'],
            ]
        );

        $ocorrencias = \App\Models\Ocorrencia::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('ativo', 'S')
            ->where('notifica_responsavel', true)
            ->with('aluno.pessoa:id,nome')
            ->when($v = $itensFiltered['aluno_id']['valor'] ?? null, fn ($q) => $q->where('aluno_id', $v))
            ->when($v = $itensFiltered['tipo']['valor'] ?? null, fn ($q) => $q->where('tipo', $v))
            ->when($v = $itensFiltered['data']['valor'] ?? null, fn ($q) => $q->whereDate('data', $v))
            ->orderByDesc('data')
            ->limit(50)
            ->get();

        return view('portal.alertas', [
            'titulo' => 'Alertas',
            'breadcrumb' => $this->breadcrumbPortal([['Alertas', null]]),
            'alunos' => $alunos,
            'ocorrencias' => $ocorrencias,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Solicitacoes da familia a secretaria: lista de pedidos enviados.
     */
    public function solicitacoes()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'solicitacoes',
            [
                $this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos)),
                $this->filtroSelect('status', 'Status', \App\Models\Solicitacao::STATUS),
                $this->filtroSelect('tipo', 'Tipo', \App\Models\Solicitacao::TIPOS),
            ]
        );

        $solicitacoes = \App\Models\Solicitacao::query()
            ->where('user_id', auth()->id())
            ->with('aluno.pessoa:id,nome', 'anexos', 'mensagens.autor:id,nome', 'mensagens.anexos')
            ->when($v = $itensFiltered['aluno_id']['valor'] ?? null, fn ($q) => $q->where('aluno_id', $v))
            ->when($v = $itensFiltered['status']['valor'] ?? null, fn ($q) => $q->where('status', $v))
            ->when($v = $itensFiltered['tipo']['valor'] ?? null, fn ($q) => $q->where('tipo', $v))
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('portal.solicitacoes', [
            'titulo' => 'Solicitações',
            'breadcrumb' => $this->breadcrumbPortal([['Solicitações', null]]),
            'solicitacoes' => $solicitacoes,
            'alunos' => $alunos,
            'tipos' => $this->tiposSolicitacaoPortal($alunos),
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /** Formulario de nova solicitacao a secretaria. */
    public function solicitacaoNova()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        return view('portal.solicitacao-nova', [
            'titulo' => 'Nova Solicitação',
            'breadcrumb' => $this->breadcrumbPortal([
                ['Solicitações', route('portal.solicitacoes')],
                ['Nova solicitação', null],
            ]),
            'alunos' => $alunos,
            'tipos' => $this->tiposSolicitacaoPortal($alunos),
        ]);
    }

    /** Tipos de solicitacao ativos da rede ou das escolas dos alunos vinculados. */
    private function tiposSolicitacaoPortal(Collection $alunos): Collection
    {
        return \App\Models\SolicitacaoTipo::query()
            ->where('ativo', true)
            ->where(fn ($q) => $q->whereNull('escola_id')
                ->orWhereIn('escola_id', $alunos->pluck('escola_id')->filter()))
            ->orderBy('nome')
            ->get();
    }

    /** Cria uma solicitacao a partir do tipo de requerimento cadastrado pela secretaria. */
    public function solicitacaoStore(\Illuminate\Http\Request $request)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $dados = $request->validate([
            'aluno_id' => [$alunos->count() > 1 ? 'required' : 'nullable', 'integer'],
            'tipo_id' => ['nullable', 'integer'],
            'tipo' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Models\Solicitacao::TIPOS))],
            'titulo' => ['nullable', 'string', 'max:200'],
            'descricao' => ['nullable', 'string', 'max:3000'],
        ]);

        $aluno = $alunos->count() === 1
            ? $alunos->first()
            : $alunos->firstWhere('id', (int) ($dados['aluno_id'] ?? 0));

        abort_if(! $aluno, 403, 'Aluno não vinculado a esta conta.');

        // O tipo precisa estar ativo e visivel para a escola do aluno
        $tipo = null;
        if (! empty($dados['tipo_id'])) {
            $tipo = \App\Models\SolicitacaoTipo::query()
                ->where('ativo', true)
                ->where(fn ($q) => $q->whereNull('escola_id')->orWhere('escola_id', $aluno->escola_id))
                ->findOrFail($dados['tipo_id']);
        }

        abort_if(! $tipo && empty($dados['tipo']), 422, 'Selecione o tipo de solicitação.');

        $request->validate($this->regrasSolicitacaoDinamicas($tipo));

        $solicitacao = \App\Models\Solicitacao::create([
            'empresa_id' => $aluno->empresa_id,
            'escola_id' => $aluno->escola_id,
            'aluno_id' => $aluno->id,
            'user_id' => auth()->id(),
            'tipo' => $tipo ? 'outro' : $dados['tipo'],
            'tipo_solicitacao_id' => $tipo?->id,
            'titulo' => $tipo?->nome
                ?? (($dados['titulo'] ?? null) ?: (\App\Models\Solicitacao::TIPOS[$dados['tipo'] ?? 'outro'] ?? 'Solicitação')),
            'descricao' => $dados['descricao'] ?? null,
            'dados' => null,
            'status' => 'aberta',
        ]);

        $respostas = collect($tipo?->campos ?? [])
            ->map(function ($campo, $i) use ($request, $solicitacao) {
                if (($campo['tipo'] ?? '') === 'anexo') {
                    $arquivo = $request->file("campos.$i");
                    if (! $arquivo) {
                        return null;
                    }
                    $path = $arquivo->store(
                        "empresas/{$solicitacao->empresa_id}/solicitacoes/{$solicitacao->id}", 'public');
                    $solicitacao->anexos()->create([
                        'empresa_id' => $solicitacao->empresa_id,
                        'arquivo_path' => $path,
                        'nome_original' => $arquivo->getClientOriginalName(),
                    ]);

                    return ['campo' => $campo['rotulo'], 'valor' => 'Anexo: '.$arquivo->getClientOriginalName()];
                }

                return ['campo' => $campo['rotulo'], 'valor' => $request->input("campos.$i")];
            })
            ->filter(fn ($item) => $item !== null && filled($item['valor']))
            ->values()
            ->all();

        if ($respostas) {
            $solicitacao->update(['dados' => $respostas]);
        }

        foreach ((array) $request->file('anexos', []) as $arquivo) {
            $path = $arquivo->store(
                "empresas/{$solicitacao->empresa_id}/solicitacoes/{$solicitacao->id}",
                'public'
            );

            $solicitacao->anexos()->create([
                'empresa_id' => $solicitacao->empresa_id,
                'arquivo_path' => $path,
                'nome_original' => $arquivo->getClientOriginalName(),
            ]);
        }

        return redirect()->route('portal.solicitacoes')->with('success', 'Solicitação enviada à secretaria.');
    }

    /** Familia responde na conversa da solicitacao (esclarecimento ou documento pedido). */
    public function solicitacaoMensagem(\Illuminate\Http\Request $request, $id)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $solicitacao = \App\Models\Solicitacao::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        abort_if(! $solicitacao->conversaAberta(), 422, 'Esta solicitação já foi finalizada.');

        $dados = $request->validate([
            'mensagem' => ['required', 'string', 'max:3000'],
            'anexos' => ['nullable', 'array', 'max:5'],
            'anexos.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        $mensagem = $solicitacao->mensagens()->create([
            'empresa_id' => $solicitacao->empresa_id,
            'user_id' => auth()->id(),
            'mensagem' => $dados['mensagem'],
        ]);

        foreach ((array) $request->file('anexos', []) as $arquivo) {
            $path = $arquivo->store(
                "empresas/{$solicitacao->empresa_id}/solicitacoes/{$solicitacao->id}",
                'public'
            );

            $solicitacao->anexos()->create([
                'empresa_id' => $solicitacao->empresa_id,
                'mensagem_id' => $mensagem->id,
                'arquivo_path' => $path,
                'nome_original' => $arquivo->getClientOriginalName(),
            ]);
        }

        // Retorno da familia recoloca o pedido no topo da fila da secretaria
        if ($solicitacao->status === 'em_analise') {
            $solicitacao->update(['status' => 'aberta']);
        }

        return redirect()
            ->route('portal.solicitacoes')
            ->with('success', 'Resposta enviada à secretaria.')
            ->withFragment('sol'.$solicitacao->id);
    }

    /** Regras que dependem do tipo escolhido: campos extras e anexo obrigatorio. */
    private function regrasSolicitacaoDinamicas(?\App\Models\SolicitacaoTipo $tipo): array
    {
        $regras = [
            'anexos' => [$tipo?->exige_anexo ? 'required' : 'nullable', 'array', 'max:5'],
            'anexos.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'campos' => ['nullable', 'array'],
        ];

        foreach ($tipo->campos ?? [] as $i => $campo) {
            if (($campo['tipo'] ?? '') === 'anexo') {
                $regras["campos.$i"] = [($campo['obrigatoria'] ?? false) ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:8192'];
                continue;
            }

            $regra = [($campo['obrigatoria'] ?? false) ? 'required' : 'nullable', 'string', 'max:2000'];

            if (($campo['tipo'] ?? '') === 'numero') {
                $regra[] = 'numeric';
            }
            if (($campo['tipo'] ?? '') === 'data') {
                $regra[] = 'date';
            }
            if (($campo['tipo'] ?? '') === 'selecao') {
                $regra[] = \Illuminate\Validation\Rule::in($campo['opcoes'] ?? []);
            }

            $regras["campos.$i"] = $regra;
        }

        return $regras;
    }

    /** Familia baixa um anexo que ela mesma enviou na solicitacao. */
    public function solicitacaoAnexo($id, $anexoId)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        $solicitacao = \App\Models\Solicitacao::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->findOrFail($id);

        $anexo = $solicitacao->anexos()->findOrFail($anexoId);

        return \Illuminate\Support\Facades\Storage::disk('public')
            ->response($anexo->arquivo_path, $anexo->nomeExibicao());
    }

    /**
     * Documentos solicitados pela escola: lista pendente
     * e historico — a familia envia o arquivo pelo portal.
     */
    public function documentos()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $solicitacoes = \App\Models\DocumentoSolicitacao::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with('aluno.pessoa:id,nome')
            ->orderByRaw("CASE status WHEN 'devolvido' THEN 0 WHEN 'pendente' THEN 1 WHEN 'enviado' THEN 2 ELSE 3 END")
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        return view('portal.documentos', [
            'titulo' => 'Documentos',
            'breadcrumb' => $this->breadcrumbPortal([['Documentos', null]]),
            'alunos' => $alunos,
            'solicitacoes' => $solicitacoes,
        ]);
    }

    /** Familia envia o arquivo de uma solicitacao de documento. */
    public function documentoEnviar(\Illuminate\Http\Request $request, $id)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $solicitacao = \App\Models\DocumentoSolicitacao::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->findOrFail($id);

        abort_if($solicitacao->status === 'aceito', 403, 'Este documento já foi aceito.');

        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        $path = $request->file('arquivo')->store(
            "empresas/{$solicitacao->empresa_id}/documentos/{$solicitacao->aluno_id}",
            'public'
        );

        $solicitacao->update([
            'arquivo_path' => $path,
            'enviado_em' => now(),
            'status' => 'enviado',
        ]);

        return redirect()->route('portal.documentos')->with('success', 'Documento enviado para conferência.');
    }

    /**
     * Boletim do aluno em PDF, mesma regra de acesso do boletim em tela.
     */
    public function alunoPdf($alunoId, \Illuminate\Http\Request $request)
    {
        $aluno = $this->alunosDoUsuario(auth()->user())->firstWhere('id', (int) $alunoId);

        abort_if(! $aluno, 403, 'Aluno nao vinculado a esta conta.');

        $anoLetivoId = $request->integer('ano_letivo_id') ?: null;
        $dados = $this->boletimService->dadosAluno($aluno, $anoLetivoId);

        abort_if(! $dados || ! $dados['matricula'], 404, 'Aluno sem matricula ativa.');

        return app(\App\Services\PdfService::class)->download(
            'boletim.pdf-aluno',
            array_merge($dados, ['aluno' => $aluno, 'anoLetivoId' => $anoLetivoId]),
            'boletim-'.\Illuminate\Support\Str::slug($aluno->pessoa->nome ?? 'aluno').'.pdf'
        );
    }

    /**
     * Financeiro do portal: mensalidades dos alunos vinculados,
     * agrupadas por aluno com situacao de cada cobranca.
     */
    public function financeiro()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'financeiro',
            [
                $this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos)),
                $this->filtroSelect('status', 'Situação', \App\Models\Mensalidade::STATUS + ['vencida' => 'Vencida'], 'Todas'),
            ],
            [
                ['name' => 'vencimento', 'label' => 'Vencimento', 'type' => 'date', 'typeWhere' => 'where'],
            ]
        );

        $status = $itensFiltered['status']['valor'] ?? null;

        $mensalidades = \App\Models\Mensalidade::query()
            ->whereHas('matricula', fn ($q) => $q->whereIn('aluno_id', $alunos->pluck('id')))
            ->with(['matricula.aluno.pessoa', 'matricula.turma', 'planoMensalidade'])
            ->when($v = $itensFiltered['aluno_id']['valor'] ?? null, fn ($q) => $q
                ->whereHas('matricula', fn ($m) => $m->where('aluno_id', $v)))
            ->when($status === 'vencida', fn ($q) => $q
                ->where('status', 'pendente')->whereDate('vencimento', '<', now()->toDateString()))
            ->when($status && $status !== 'vencida', fn ($q) => $q->where('status', $status))
            ->when($v = $itensFiltered['vencimento']['valor'] ?? null, fn ($q) => $q->whereDate('vencimento', $v))
            ->orderBy('competencia', 'desc')
            ->get()
            ->groupBy(fn ($m) => $m->matricula->aluno_id);

        return view('portal.financeiro', [
            'titulo' => 'Financeiro',
            'breadcrumb' => $this->breadcrumbPortal([['Financeiro', null]]),
            'alunos' => $alunos,
            'mensalidades' => $mensalidades,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Recibo de pagamento da mensalidade: apenas se a cobranca
     * pertence a um aluno vinculado a conta logada.
     */
    public function reciboMensalidade($id)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $mensalidade = \App\Models\Mensalidade::query()
            ->whereHas('matricula', fn ($q) => $q->whereIn('aluno_id', $alunos->pluck('id')))
            ->with([
                'matricula.aluno.pessoa', 'matricula.turma.escola',
                'matricula.turma.anoLetivo', 'planoMensalidade',
            ])
            ->findOrFail($id);

        abort_if($mensalidade->status !== 'paga', 404, 'Recibo disponível apenas para mensalidades pagas.');

        return app(\App\Services\PdfService::class)->download(
            'mensalidade.recibo-pdf',
            ['mensalidade' => $mensalidade],
            'recibo-mensalidade-'.$mensalidade->id.'.pdf'
        );
    }

    /**
     * Boleto anexado pela secretaria: download para a familia
     * pagar, apenas se a cobranca pertence a um aluno vinculado.
     */
    public function boletoMensalidade($id)
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $mensalidade = \App\Models\Mensalidade::query()
            ->whereHas('matricula', fn ($q) => $q->whereIn('aluno_id', $alunos->pluck('id')))
            ->findOrFail($id);

        abort_if(! $mensalidade->boleto_path, 404, 'Boleto não disponível para esta mensalidade.');

        return \Illuminate\Support\Facades\Storage::disk('public')
            ->download($mensalidade->boleto_path, $mensalidade->boletoNomeDownload());
    }

    /**
     * Avisos/comunicados das escolas dos alunos vinculados:
     * gerais da escola + direcionados as turmas em que estao.
     */
    public function avisos()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'avisos',
            [
                ['name' => 'titulo', 'label' => 'Título', 'type' => 'text', 'typeWhere' => 'like'],
                $this->filtroSelect('tipo', 'Tipo', \App\Models\Aviso::TIPOS),
                $this->filtroSelect('escola_id', 'Escola', $this->escolasOpcoes($alunos)),
            ],
            [
                ['name' => 'data_publicacao', 'label' => 'Publicado em', 'type' => 'date', 'typeWhere' => 'where'],
            ]
        );

        $avisos = $this->avisoService->avisosParaAlunos($alunos)
            ->when($v = $itensFiltered['titulo']['valor'] ?? null, fn ($c) => $c
                ->filter(fn ($a) => mb_stripos((string) $a->titulo, (string) $v) !== false))
            ->when($v = $itensFiltered['tipo']['valor'] ?? null, fn ($c) => $c->where('tipo', $v))
            ->when($v = $itensFiltered['escola_id']['valor'] ?? null, fn ($c) => $c->where('escola_id', (int) $v))
            ->when($v = $itensFiltered['data_publicacao']['valor'] ?? null, fn ($c) => $c
                ->filter(fn ($a) => $a->data_publicacao?->toDateString() === $v))
            ->values();

        return view('portal.avisos', [
            'titulo' => 'Avisos',
            'breadcrumb' => $this->breadcrumbPortal([['Avisos', null]]),
            'alunos' => $alunos,
            'avisos' => $avisos,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Transporte dos alunos vinculados: rota, parada e veiculo
     * de cada um, quando houver vinculo ativo.
     */
    public function transporte()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $vinculos = \App\Models\TransporteAluno::query()
            ->where('ativo', 'S')
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with(['aluno.pessoa', 'rota.escola', 'rota.veiculo', 'parada'])
            ->get()
            ->keyBy('aluno_id');

        return view('portal.transporte', [
            'titulo' => 'Transporte Escolar',
            'breadcrumb' => $this->breadcrumbPortal([['Transporte Escolar', null]]),
            'alunos' => $alunos,
            'vinculos' => $vinculos,
        ]);
    }

    /**
     * Cardapio das escolas onde os alunos vinculados estao
     * matriculados: proximas refeicoes, da data atual em diante.
     */
    public function cardapio()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $escolaIds = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->whereHas('turma', fn ($q) => $q->whereNotNull('escola_id'))
            ->with('turma:id,escola_id')
            ->get()
            ->pluck('turma.escola_id')
            ->filter()
            ->unique();

        $cardapios = \App\Models\Cardapio::query()
            ->whereIn('escola_id', $escolaIds)
            ->where('ativo', 'S')
            ->whereDate('data', '>=', now()->toDateString())
            ->with('escola')
            ->orderBy('data')
            ->orderBy('tipo')
            ->limit(60)
            ->get()
            ->groupBy(fn ($c) => $c->data->toDateString());

        return view('portal.cardapio', [
            'titulo' => 'Cardápio',
            'breadcrumb' => $this->breadcrumbPortal([['Cardápio', null]]),
            'alunos' => $alunos,
            'cardapios' => $cardapios,
        ]);
    }

    /**
     * Tarefas das turmas onde os alunos vinculados estao
     * matriculados, com o status de entrega de cada um.
     * Filtravel por aluno, turma e tipo — o nome do aluno so
     * aparece para conta de responsavel.
     */
    public function tarefas()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with(['turma:id,nome,escola_id,ano_letivo_id', 'turma.anoLetivo:id,ano',
                'aluno:id,pessoa_id', 'aluno.pessoa:id,nome'])
            ->get();

        $turmas = $matriculas->pluck('turma')->filter()->unique('id')
            ->sortBy('nome')->sortByDesc(fn ($t) => $t->anoLetivo->ano ?? 0)->values();

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'tarefas',
            [
                $this->filtroSelect('aluno_id', 'Aluno', $this->alunosOpcoes($alunos)),
                $this->filtroSelect('turma_id', 'Turma', $turmas->mapWithKeys(fn ($t) => [
                    $t->id => $t->nome.($t->anoLetivo ? ' — '.$t->anoLetivo->ano : ''),
                ])),
                $this->filtroSelect('tipo', 'Tipo', \App\Models\Tarefa::TIPOS),
            ]
        );

        $matriculas = $matriculas
            ->when($v = $itensFiltered['aluno_id']['valor'] ?? null, fn ($c) => $c->where('aluno_id', (int) $v))
            ->when($v = $itensFiltered['turma_id']['valor'] ?? null, fn ($c) => $c->where('turma_id', (int) $v))
            ->values();

        $tarefas = $this->tarefasPendentesQuery($matriculas)
            ->with(['turma:id,nome,escola_id', 'disciplina:id,nome', 'entregas' => fn ($q) => $q
                ->whereIn('matricula_id', $matriculas->pluck('id')),
                'entregas.tarefa:id,data_entrega'])
            ->when($v = $itensFiltered['tipo']['valor'] ?? null, fn ($q) => $q->where('tipo', $v))
            ->orderBy('data_entrega')
            ->limit(60)
            ->get();

        // Entrega de cada aluno por tarefa
        $entregasPorAluno = $tarefas->flatMap->entregas->keyBy(fn ($e) => $e->tarefa_id.'|'.$e->matricula_id);
        $matriculaPorAlunoTurma = $matriculas->keyBy(fn ($m) => $m->aluno_id.'|'.$m->turma_id);

        return view('portal.tarefas', [
            'titulo' => 'Tarefas',
            'breadcrumb' => $this->breadcrumbPortal([['Tarefas', null]]),
            'alunos' => $alunos,
            'tarefas' => $tarefas,
            'matriculas' => $matriculas,
            'entregasPorAluno' => $entregasPorAluno,
            'matriculaPorAlunoTurma' => $matriculaPorAlunoTurma,
            'isResponsavel' => auth()->user()->tipoConta() === \App\Models\User::TIPO_CONTA_RESPONSAVEL,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
        ]);
    }

    /**
     * Tarefa pendente = ativa e ainda sem entrega 'entregue'/'avaliada'
     * em ao menos uma matricula da conta naquela turma (irmaos na mesma
     * turma contam em separado — so sai da lista quando todos entregam).
     */
    private function tarefasPendentesQuery(Collection $matriculas)
    {
        return \App\Models\Tarefa::query()
            ->whereIn('turma_id', $matriculas->pluck('turma_id')->unique()->filter())
            ->where('ativo', 'S')
            ->whereExists(function ($q) use ($matriculas) {
                $q->select(DB::raw(1))
                    ->from('matriculas as m2')
                    ->whereColumn('m2.turma_id', 'tarefas.turma_id')
                    ->whereIn('m2.id', $matriculas->pluck('id'))
                    ->whereNotExists(function ($q2) {
                        $q2->select(DB::raw(1))
                            ->from('entregas_tarefa as et')
                            ->whereColumn('et.tarefa_id', 'tarefas.id')
                            ->whereColumn('et.matricula_id', 'm2.id')
                            ->whereIn('et.status', ['entregue', 'avaliada']);
                    });
            });
    }

    /**
     * Emprestimos de livros dos alunos vinculados a conta:
     * ativos primeiro (com atraso derivado), depois historico.
     */
    public function emprestimos()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $emprestimos = \App\Models\EmprestimoLivro::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with(['livro:id,titulo,autor', 'aluno.pessoa:id,nome'])
            ->orderByRaw("CASE WHEN status = 'ativo' THEN 0 ELSE 1 END")
            ->orderBy('data_devolucao_prevista')
            ->limit(100)
            ->get()
            ->groupBy('aluno_id');

        return view('portal.emprestimos', [
            'titulo' => 'Meus Empréstimos',
            'breadcrumb' => $this->breadcrumbPortal([['Meus Empréstimos', null]]),
            'alunos' => $alunos,
            'emprestimos' => $emprestimos,
        ]);
    }

    /**
     * Agenda escolar: eventos com publico "todos" das escolas
     * onde os alunos vinculados estudam (ou da rede toda).
     */
    public function agenda()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $mesParam = (string) request('mes', '');
        $mes = preg_match('/^\d{4}-\d{2}$/', $mesParam)
            ? \Carbon\Carbon::createFromFormat('Y-m-d', $mesParam.'-01')->startOfMonth()
            : now()->startOfMonth();

        $inicio = $mes->copy();
        $fim = $mes->copy()->endOfMonth();

        [$itensFiltered, $filters, $additionalFilters, $nomeFiltro] = $this->prepararFiltroPortal(
            'agenda',
            [
                $this->filtroSelect('tipo', 'Tipo', \App\Models\Evento::TIPOS),
                $this->filtroSelect('escola_id', 'Escola', $this->escolasOpcoes($alunos)),
            ]
        );

        $eventos = app(\App\Services\EventoService::class)->eventosParaAlunos($alunos, $inicio, $fim)
            ->when($v = $itensFiltered['tipo']['valor'] ?? null, fn ($c) => $c->where('tipo', $v))
            ->when($v = $itensFiltered['escola_id']['valor'] ?? null, fn ($c) => $c->where('escola_id', (int) $v))
            ->values();

        // Grade do mes: eventos de varios dias aparecem em cada dia coberto.
        $eventosPorDia = [];
        foreach ($eventos as $evento) {
            $de = $evento->data_inicio->copy()->max($inicio);
            $ate = $evento->periodoFim()->copy()->min($fim);

            for ($dia = $de->copy(); $dia->lte($ate); $dia->addDay()) {
                $eventosPorDia[$dia->toDateString()][] = $evento;
            }
        }

        $semanas = [];
        $cursor = $inicio->copy()->startOfWeek();
        $ultimo = $fim->copy()->endOfWeek();

        while ($cursor->lte($ultimo)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++, $cursor->addDay()) {
                $semana[] = [
                    'data' => $cursor->copy(),
                    'do_mes' => $cursor->month === $mes->month,
                    'eventos' => collect($eventosPorDia[$cursor->toDateString()] ?? []),
                ];
            }
            $semanas[] = $semana;
        }

        // Fundo verde/vermelho do calendario: o dia conta como "com
        // aula" quando pelo menos uma turma (matricula ativa) dos
        // alunos vinculados tem aula nele.
        $turmas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with('turma')
            ->get()
            ->pluck('turma')
            ->filter()
            ->unique('id');

        $temAulaPorDia = [];
        $feriadosPorDia = [];
        if ($turmas->isNotEmpty()) {
            $calService = app(\App\Services\CalendarioLetivoService::class);
            $statusComAula = [
                \App\Services\CalendarioLetivoService::STATUS_AULA,
                \App\Services\CalendarioLetivoService::STATUS_AULA_EVENTO,
            ];

            foreach ($turmas as $turma) {
                $diasTurma = $calService->calendarioDaTurma($turma, $inicio, $fim, somenteEventosPublicos: true);
                foreach ($diasTurma as $chave => $dia) {
                    $temAulaPorDia[$chave] = ($temAulaPorDia[$chave] ?? false)
                        || in_array($dia['status'], $statusComAula, true);

                    if ($dia['status'] === \App\Services\CalendarioLetivoService::STATUS_SEM_AULA) {
                        foreach ($dia['feriados']->where('suspende_aulas', 'S') as $feriado) {
                            $feriadosPorDia[$chave][$feriado->id] = $feriado->nome;
                        }
                    }
                }
            }
        }

        return view('portal.agenda', [
            'titulo' => 'Agenda Escolar',
            'breadcrumb' => $this->breadcrumbPortal([['Agenda Escolar', null]]),
            'alunos' => $alunos,
            'eventos' => $eventos,
            'aba' => request()->query('aba', 'calendario'),
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $nomeFiltro,
            'mes' => $mes,
            'rotuloMes' => ucfirst($mes->locale('pt_BR')->translatedFormat('F \d\e Y')),
            'mesAnterior' => $inicio->copy()->subMonth()->format('Y-m'),
            'mesProximo' => $inicio->copy()->addMonth()->format('Y-m'),
            'ehMesAtual' => $mes->isSameMonth(now()),
            'semanas' => $semanas,
            'temAulaPorDia' => $temAulaPorDia,
            'feriadosPorDia' => $feriadosPorDia,
        ]);
    }

    /**
     * Calendario letivo do aluno: cruza dias letivos da grade da
     * turma com feriados e eventos publicos — responde "tem aula?"
     * sem a familia precisar perguntar.
     */
    public function calendario()
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $alunoId = (int) request('aluno_id', $alunos->first()->id);
        $aluno = $alunos->firstWhere('id', $alunoId) ?? $alunos->first();

        $matricula = Matricula::query()
            ->where('aluno_id', $aluno->id)
            ->where('status', 'ativa')
            ->with('turma.anoLetivo.periodos', 'turma.escola', 'turma.horario')
            ->first();

        abort_if(! $matricula?->turma, 404, 'Este aluno não tem matrícula ativa em uma turma.');

        $turma = $matricula->turma;
        $service = app(\App\Services\CalendarioLetivoService::class);

        // Intervalo exibido: mes atual por padrao; "ano" mostra o ano letivo.
        $escopo = request('escopo', 'mes');
        if ($escopo === 'ano') {
            $ano = $turma->anoLetivo;
            $inicio = $ano?->data_inicio ?? now()->startOfYear();
            $fim = $ano?->data_fim ?? now()->endOfYear();
            $rotuloPeriodo = 'Ano letivo '.($ano->ano ?? '');
        } else {
            $mes = \Carbon\Carbon::parse(request('mes', now()->format('Y-m')).'-01')->startOfMonth();
            $inicio = $mes->copy();
            $fim = $mes->copy()->endOfMonth();
            $rotuloPeriodo = ucfirst($mes->locale('pt_BR')->translatedFormat('F/Y'));
        }

        $dias = $service->calendarioDaTurma($turma, $inicio, $fim, somenteEventosPublicos: true);
        $meses = $service->mesesDoCalendario($dias);

        return view('portal.calendario', [
            'titulo' => 'Calendário do Aluno',
            'breadcrumb' => $this->breadcrumbPortal([['Calendário do Aluno', null]]),
            'alunos' => $alunos,
            'aluno' => $aluno,
            'turma' => $turma,
            'meses' => $meses,
            'escopo' => $escopo,
            'rotuloPeriodo' => $rotuloPeriodo,
            'mesAtual' => $inicio->format('Y-m'),
            'mesAnterior' => $inicio->copy()->subMonth()->format('Y-m'),
            'mesProximo' => $inicio->copy()->addMonth()->format('Y-m'),
            'alteracoes' => $service->proximasAlteracoes($dias),
            'rotulosStatus' => \App\Services\CalendarioLetivoService::ROTULOS,
        ]);
    }

    /**
     * Pesquisas abertas para as familias: pesquisa da escola
     * dos alunos vinculados (ou da rede toda) dentro do periodo
     * e ainda nao respondida pelo usuario.
     */
    public function pesquisas()
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $pesquisas = app(\App\Services\PesquisaService::class)
            ->pesquisasParaUsuario($user, $alunos);

        return view('portal.pesquisas', [
            'titulo' => 'Pesquisas',
            'breadcrumb' => $this->breadcrumbPortal([['Pesquisas', null]]),
            'alunos' => $alunos,
            'pesquisas' => $pesquisas,
        ]);
    }

    /** Formulario de resposta de uma pesquisa aberta. */
    public function pesquisaResponder($id)
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $pesquisa = \App\Models\Pesquisa::query()
            ->with('perguntas')
            ->where('status', 'aberta')
            ->findOrFail($id);

        // So responde pesquisa da escola dos alunos (ou rede toda)
        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();
        abort_if($pesquisa->escola_id && ! $escolaIds->contains($pesquisa->escola_id), 403);
        abort_unless($pesquisa->estaAberta(), 403, 'Esta pesquisa não está aberta.');
        abort_if(
            \App\Models\PesquisaResposta::where('pesquisa_id', $pesquisa->id)->where('user_id', $user->id)->exists(),
            403, 'Você já respondeu esta pesquisa.'
        );

        return view('portal.pesquisa-responder', [
            'titulo' => 'Responder Pesquisa',
            'breadcrumb' => $this->breadcrumbPortal([
                ['Pesquisas', route('portal.pesquisas')],
                ['Responder', null],
            ]),
            'alunos' => $alunos,
            'pesquisa' => $pesquisa,
        ]);
    }

    /** Salva a resposta da pesquisa. */
    public function pesquisaSalvar(\Illuminate\Http\Request $request, $id)
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $pesquisa = \App\Models\Pesquisa::query()->findOrFail($id);

        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();
        abort_if($pesquisa->escola_id && ! $escolaIds->contains($pesquisa->escola_id), 403);

        // aluno_id opcional: o primeiro aluno vinculado como contexto
        $alunoId = $request->filled('aluno_id') && $alunos->contains('id', (int) $request->input('aluno_id'))
            ? (int) $request->input('aluno_id')
            : $alunos->first()?->id;

        app(\App\Services\PesquisaService::class)->responder(
            $pesquisa, $user, $request->input('respostas', []), $alunoId
        );

        return redirect()->route('portal.pesquisas')->with('success', 'Resposta enviada com sucesso!');
    }

    /**
     * Fichas de saude dos alunos vinculados: alergias,
     * medicamentos, condicoes e contato de emergencia.
     */
    public function saude()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $fichas = \App\Models\SaudeAluno::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with('aluno.pessoa:id,nome')
            ->get()
            ->keyBy('aluno_id');

        return view('portal.saude', [
            'titulo' => 'Saúde dos Alunos',
            'breadcrumb' => $this->breadcrumbPortal([['Saúde dos Alunos', null]]),
            'alunos' => $alunos,
            'fichas' => $fichas,
        ]);
    }

    /** Grava na sessao os filtros enviados pelo formulario da tela. */
    public function filtro(Request $request, string $tela)
    {
        abort_unless(isset(self::TELAS_FILTRO[$tela]), 404);

        $nomeFiltro = $this->configurarSessaoPortal($tela);
        $this->criarFiltros($request, $nomeFiltro);

        return $this->redirecionaRotaRedirect($nomeFiltro);
    }

    /** Remove um filtro da sessao — ou todos, sem parametro "atributo". */
    public function removerFiltro(Request $request, string $tela)
    {
        abort_unless(isset(self::TELAS_FILTRO[$tela]), 404);

        $nomeFiltro = $this->configurarSessaoPortal($tela);
        $this->removeSessao($nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($nomeFiltro);
    }

    /**
     * Configura a sessao do filtro padrao da tela: le os filtros
     * gravados e marca os valores aplicados nos selects.
     *
     * @return array{0: array, 1: array, 2: array, 3: string}
     */
    private function prepararFiltroPortal(string $tela, array $filters, array $additionalFilters = []): array
    {
        $nomeFiltro = $this->configurarSessaoPortal($tela);

        $itensFiltered = session("$nomeFiltro.filtro", []);

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        // Ignora chaves gravadas em sessao que nao existem mais nos
        // filtros da tela (ex.: filtro removido em nova versao).
        $validos = collect($filters)->merge($additionalFilters)->pluck('name')->all();
        $itensFiltered = array_intersect_key($itensFiltered, array_flip($validos));

        return [$itensFiltered, $filters, $additionalFilters, $nomeFiltro];
    }

    private function configurarSessaoPortal(string $tela): string
    {
        $nomeFiltro = "portal.$tela";
        session()->put("$nomeFiltro.urlPadrao", "portal/$tela");
        session()->put("$nomeFiltro.rotaRedirect", self::TELAS_FILTRO[$tela]);

        return $nomeFiltro;
    }

    /** Definicao de filtro select no formato do componente <x-filter>. */
    private function filtroSelect(string $name, string $label, iterable $options, string $vazio = 'Todos'): array
    {
        return [
            'name' => $name, 'label' => $label, 'type' => 'select',
            'typeWhere' => 'where', 'options' => $this->opcoesFiltro($options, $vazio),
        ];
    }

    /** Opcoes no formato value/label/selected, com a primeira vazia. */
    private function opcoesFiltro(iterable $itens, string $vazio = 'Todos'): array
    {
        $opcoes = [['value' => '', 'label' => $vazio, 'selected' => false]];

        foreach ($itens as $value => $label) {
            $opcoes[] = ['value' => $value, 'label' => (string) $label, 'selected' => false];
        }

        return $opcoes;
    }

    private function alunosOpcoes(Collection $alunos): array
    {
        return $alunos->mapWithKeys(fn ($a) => [
            $a->id => $a->pessoa->nome ?? $a->nome,
        ])->all();
    }

    private function escolasOpcoes(Collection $alunos): array
    {
        return \App\Models\Escola::query()
            ->whereIn('id', $alunos->pluck('escola_id')->filter())
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->all();
    }

    /**
     * Breadcrumb padrao do portal: Home aponta para a home do
     * portal (para onde a conta de aluno/responsavel cai no login).
     * Cada item extra e um par [nome, url|null].
     */
    private function breadcrumbPortal(array $itens): array
    {
        return $this->breadcrumbService->prepararBreadcrumbCustom(
            array_merge([['Home', route('portal.index')]], $itens)
        );
    }

    /**
     * Alunos acessiveis pela conta: filhos de responsaveis vinculados
     * + o proprio aluno quando a conta e de aluno.
     */
    private function alunosDoUsuario($user): Collection
    {
        $ids = collect();

        $responsavel = Responsavel::query()->where('user_id', $user->id)->first();
        if ($responsavel) {
            $ids = $ids->merge($responsavel->alunos()->pluck('alunos.id'));
        }

        $proprio = Aluno::query()->where('user_id', $user->id)->first();
        if ($proprio) {
            $ids->push($proprio->id);
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return Aluno::query()->with(['pessoa', 'user:id,foto_perfil'])->whereIn('id', $ids->unique())->get();
    }
}
