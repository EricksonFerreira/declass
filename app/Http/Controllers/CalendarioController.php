<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\CalendarioLetivoService;
use App\Services\TurmaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Calendario letivo por turma: mostra dia a dia do ano/semestre/
 * periodo letivo com aulas, feriados e eventos — responde se
 * ha aula normal, so evento ou suspensao.
 */
class CalendarioController extends BaseController
{
    public function __construct(
        protected CalendarioLetivoService $calendarioService,
        protected TurmaService $turmaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Calendário Letivo';
        $this->middleware('can:calendario.index')->only('index');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $turmas = $this->turmasOpcoes($user);

        $turmaId = (int) $request->input('turma_id', $turmas->keys()->first() ?: 0);
        $turma = $turmaId ? Turma::query()->with(['escola', 'anoLetivo.periodos', 'horario'])->find($turmaId) : null;

        // Professor so enxerga turmas com atribuicao ativa.
        if ($turma) {
            abort_if(! $this->turmaService->podeAcessar($turma, $user), 403,
                'Você não tem acesso ao calendário desta turma.');
        }

        [$inicio, $fim, $rotuloPeriodo] = $turma
            ? $this->resolverPeriodo($turma, $request)
            : [now()->startOfMonth(), now()->endOfMonth(), ''];

        $dias = $turma ? $this->calendarioService->calendarioDaTurma($turma, $inicio, $fim) : collect();
        $meses = $this->calendarioService->mesesDoCalendario($dias);
        $alteracoes = $turma ? $this->calendarioService->proximasAlteracoes($dias) : collect();

        // Um mes por vez: meses do periodo resolvido (ano/semestre/periodo).
        $mesesDisponiveis = [];
        $ultimoMes = $fim->copy()->startOfMonth();
        for ($cursor = $inicio->copy()->startOfMonth(); $cursor->lte($ultimoMes); $cursor->addMonth()) {
            $mesesDisponiveis[$cursor->format('Y-m')] = ucfirst($cursor->locale('pt_BR')->translatedFormat('F/Y'));
        }

        $mesHoje = now()->format('Y-m');
        $mesSolicitado = (string) $request->input('mes', '');
        $mesSelecionado = $turma && isset($mesesDisponiveis[$mesSolicitado]) ? $mesSolicitado
            : (isset($mesesDisponiveis[$mesHoje]) ? $mesHoje : (string) array_key_first($mesesDisponiveis));

        $chavesMeses = array_keys($mesesDisponiveis);
        $idxMes = array_search($mesSelecionado, $chavesMeses, true);
        $mesAnterior = $idxMes !== false && $idxMes > 0 ? $chavesMeses[$idxMes - 1] : null;
        $proximoMes = $idxMes !== false && $idxMes < count($chavesMeses) - 1 ? $chavesMeses[$idxMes + 1] : null;
        $mesExibido = collect($meses)->firstWhere('chave', $mesSelecionado);

        // Professor: aulas que leciona na turma para destacar seus dias.
        $aulasProfessor = collect();
        $somenteMinhas = false;
        if ($turma && $user->isProfessor()) {
            $aulasProfessor = $this->calendarioService->aulasDoProfessor($turma, $user->id);
            $somenteMinhas = $request->boolean('minhas_aulas');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Calendário Letivo', 'Vida Acadêmica');

        return view('calendario.index', [
            'turma' => $turma,
            'turmas' => $turmas,
            'dias' => $dias,
            'meses' => $meses,
            'mesExibido' => $mesExibido,
            'mesesDisponiveis' => $mesesDisponiveis,
            'mesSelecionado' => $mesSelecionado,
            'mesAnterior' => $mesAnterior,
            'proximoMes' => $proximoMes,
            'aulasProfessor' => $aulasProfessor,
            'diasProfessor' => $aulasProfessor->keys()->all(),
            'somenteMinhas' => $somenteMinhas,
            'alteracoes' => $alteracoes,
            'inicio' => $inicio,
            'fim' => $fim,
            'rotuloPeriodo' => $rotuloPeriodo,
            'escopo' => $request->input('escopo', 'ano'),
            'periodoLetivoId' => (int) $request->input('periodo_letivo_id'),
            'rotulosStatus' => CalendarioLetivoService::ROTULOS,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    /**
     * Resolve o intervalo exibido: periodo letivo especifico,
     * semestre ou o ano letivo inteiro.
     */
    private function resolverPeriodo(Turma $turma, Request $request): array
    {
        $ano = $turma->anoLetivo;
        $inicio = $ano?->data_inicio ?? Carbon::create($ano?->ano ?? now()->year, 1, 1);
        $fim = $ano?->data_fim ?? Carbon::create($ano?->ano ?? now()->year, 12, 31);

        $escopo = $request->input('escopo', 'ano');
        $rotulo = 'Ano letivo '.($ano->ano ?? '');

        if ($escopo === 'periodo' && ($pid = (int) $request->input('periodo_letivo_id'))) {
            $periodo = $ano?->periodos?->firstWhere('id', $pid);
            if ($periodo) {
                return [$periodo->data_inicio, $periodo->data_fim, $periodo->nome.' ('.$ano->ano.')'];
            }
        }

        if (in_array($escopo, ['sem1', 'sem2'], true)) {
            $meio = $inicio->copy()->addDays($inicio->diffInDays($fim) / 2);
            if ($escopo === 'sem1') {
                return [$inicio->copy(), $meio->copy()->subDay(), '1º semestre de '.$ano->ano];
            }

            return [$meio, $fim->copy(), '2º semestre de '.$ano->ano];
        }

        return [$inicio->copy(), $fim->copy(), $rotulo];
    }

    /** Turmas elegiveis no select: todas para gestao, atribuidas para professor. */
    private function turmasOpcoes($user): \Illuminate\Support\Collection
    {
        $q = Turma::query()->where('ativo', 'S')->with('escola:id,nome')->orderBy('nome');

        if ($user->isProfessor()) {
            $q->whereHas('atribuicoes', function ($a) use ($user) {
                $a->where('user_id', $user->id)->where('ativo', 'S');
            });
        }

        return $q->get()->mapWithKeys(fn (Turma $t) => [
            $t->id => $t->nome.' — '.($t->escola->nome ?? ''),
        ]);
    }
}
