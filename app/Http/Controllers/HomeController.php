<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\User;
use App\Services\SetupChecklistService;
use App\Services\TurmaGradeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct(
        protected SetupChecklistService $setupChecklistService,
        protected TurmaGradeService $gradeService,
    ) {}

    public function home(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Contas de portal (aluno/responsavel): a home operacional nao
        // se aplica — a tela de inicio delas e o proprio perfil.
        if ($user->isPortalAccount()) {
            return redirect()->route('profile.edit');
        }

        if ($user->isProfessor()) {
            return $this->homeProfessor($user);
        }
        if ($user->isSecretaria() || $user->isDiretor()) {
            return $this->homeSecretaria($user);
        }

        return view('home', [
            'titulo' => 'Início',
            'breadcrumb' => [
                ['name' => 'Home', 'active' => true],
            ],
            'setupChecklist' => $this->setupChecklistService->itens($user),
        ]);
    }

    /**
     * Home do professor em formato de app: resumo do dia (aulas,
     * chamadas feitas, avaliacoes proximas) e aulas de hoje com
     * atalho direto para a chamada unificada.
     */
    private function homeProfessor(User $user): View
    {
        $grade = $this->gradeService->gradeSemanalDoProfessor($user);

        $dow = (int) now()->dayOfWeek; // 0=dom..6=sab
        $hoje = $dow === 0 ? 7 : $dow; // sistema usa 1=seg..7=dom

        $aulasHoje = collect($grade['celulas'])
            ->filter(fn ($celula, $chave) => str_starts_with((string) $chave, $hoje.'-'))
            ->flatten(1)
            ->sortBy('inicio')
            ->values();

        $hojeData = today()->toDateString();
        $chamadasHoje = Diario::where('empresa_id', $user->empresa_id)
            ->where('user_id', $user->id)
            ->whereDate('data', $hojeData)
            ->count();

        $avaliacoesSemana = Avaliacao::where('empresa_id', $user->empresa_id)
            ->where('user_id', $user->id)
            ->whereBetween('data', [$hojeData, today()->addDays(7)->toDateString()])
            ->count();

        // Entregas enviadas pelos alunos aguardando correcao, por turma
        $correcoesPorTurma = \App\Models\EntregaTarefa::query()
            ->join('tarefas', 'entregas_tarefa.tarefa_id', '=', 'tarefas.id')
            ->where('entregas_tarefa.status', 'entregue')
            ->where('tarefas.ativo', 'S')
            ->whereNull('tarefas.deleted_at')
            ->whereIn('tarefas.turma_id', $grade['turmas']->pluck('id'))
            ->groupBy('tarefas.turma_id')
            ->pluck(DB::raw('count(*)'), 'tarefas.turma_id');

        $turmasComCorrecao = $grade['turmas']
            ->filter(fn ($t) => ($correcoesPorTurma[$t->id] ?? 0) > 0)
            ->map(fn ($t) => ['turma' => $t, 'pendentes' => (int) $correcoesPorTurma[$t->id]])
            ->sortByDesc('pendentes')
            ->values();

        return view('home_professor', [
            'titulo' => 'Início',
            'breadcrumb' => [['name' => 'Home', 'active' => true]],
            'aulasHoje' => $aulasHoje,
            'totalTurmas' => $grade['turmas']->count(),
            'chamadasHoje' => $chamadasHoje,
            'avaliacoesSemana' => $avaliacoesSemana,
            'turmasComCorrecao' => $turmasComCorrecao,
            'totalCorrecoes' => $turmasComCorrecao->sum('pendentes'),
            'dataHoje' => $hojeData,
        ]);
    }

    /**
     * Home da secretaria e da direcao em formato de app: fila de
     * atendimento (solicitacoes, documentos, atestados), resumo da
     * escola e atalhos das acoes do dia a dia.
     */
    private function homeSecretaria(User $user): View
    {
        $eid = $user->empresa_id;
        $hoje = today()->toDateString();
        $anoAtual = \App\Models\AnoLetivo::query()
            ->where('empresa_id', $eid)->where('status', 'letivo')->latest('ano')->value('id');

        $documentos = \App\Models\DocumentoSolicitacao::query()
            ->where('empresa_id', $eid)->selectRaw('status, count(*) as qtd')
            ->groupBy('status')->pluck('qtd', 'status');

        return view('home_secretaria', [
            'titulo' => 'Início',
            'breadcrumb' => [['name' => 'Home', 'active' => true]],
            'solicitacoesAbertas' => \App\Models\Solicitacao::where('empresa_id', $eid)
                ->whereIn('status', ['aberta', 'em_analise'])->count(),
            'docEnviados' => (int) ($documentos['enviado'] ?? 0),
            'docPendentes' => (int) ($documentos['pendente'] ?? 0) + (int) ($documentos['devolvido'] ?? 0),
            'atestadosEmVigor' => \App\Models\Justificativa::where('empresa_id', $eid)
                ->whereDate('data_inicio', '<=', $hoje)->whereDate('data_fim', '>=', $hoje)->count(),
            'ocorrenciasSemana' => \App\Models\Ocorrencia::where('empresa_id', $eid)
                ->whereDate('created_at', '>=', today()->subDays(7))->count(),
            'pesquisasAbertas' => \App\Models\Pesquisa::where('empresa_id', $eid)
                ->where('status', 'aberta')->whereDate('data_inicio', '<=', $hoje)
                ->whereDate('data_fim', '>=', $hoje)->count(),
            'alunosAtivos' => \App\Models\Aluno::where('empresa_id', $eid)->where('ativo', 'S')->count(),
            'matriculasAtivas' => \App\Models\Matricula::where('empresa_id', $eid)
                ->where('status', 'ativa')
                ->when($anoAtual, fn ($q) => $q->where('ano_letivo_id', $anoAtual))->count(),
            'turmasAno' => \App\Models\Turma::where('empresa_id', $eid)
                ->when($anoAtual, fn ($q) => $q->where('ano_letivo_id', $anoAtual))->count(),
            'avisosAtivos' => \App\Models\Aviso::where('empresa_id', $eid)
                ->where('ativo', 'S')->where('destino', 'familias')
                ->whereDate('data_publicacao', '<=', $hoje)->count(),
            'eventosHoje' => \App\Models\Evento::where('empresa_id', $eid)
                ->whereDate('data_inicio', '<=', $hoje)
                ->where(fn ($q) => $q->whereNull('data_fim')->orWhereDate('data_fim', '>=', $hoje))
                ->count(),
        ]);
    }

    public function dashboard(Request $request)
    {
        return redirect()->route('home');
    }
}
