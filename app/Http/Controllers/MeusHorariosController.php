<?php

namespace App\Http\Controllers;

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\HorarioSlot;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\TurmaGradeService;
use Illuminate\Http\Request;

/**
 * Grade semanal do professor logado: quais aulas ele da em cada
 * dia/horario, filtrada por ano letivo, escola e turno.
 */
class MeusHorariosController extends BaseController
{
    public function __construct(
        protected TurmaGradeService $gradeService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Meus Horários';
        $this->middleware('can:meus_horarios.index')->only('index');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Escolas onde o professor tem atribuicao ativa.
        $escolaIds = Turma::query()
            ->whereHas('atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'))
            ->distinct()->pluck('escola_id');

        $escolas = Escola::query()->whereIn('id', $escolaIds)->orderBy('id')->pluck('nome', 'id');
        $anos = AnoLetivo::query()->whereIn('escola_id', $escolaIds)
            ->orderByDesc('ano')->get(['id', 'ano', 'escola_id', 'data_inicio', 'data_fim']);

        // Defaults: escola mais antiga, ano letivo cobrindo hoje
        // (senao o mais recente) e turno matutino.
        $escolaId = (int) $request->input('escola_id', $escolas->keys()->first() ?: 0);
        $anoAtual = $anos->first(fn ($a) => $a->data_inicio && $a->data_fim
            && $a->data_inicio->lte(now()) && $a->data_fim->gte(now()));
        $anoLetivoId = (int) $request->input('ano_letivo_id', ($anoAtual ?? $anos->first())?->id ?? 0);
        $turno = $request->input('turno', 'matutino');
        abort_unless($turno === '' || array_key_exists($turno, Turma::TURNOS), 422);

        $grade = $this->gradeService->gradeSemanalDoProfessor($user, $anoLetivoId ?: null, $escolaId ?: null, $turno ?: null);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Meus Horários', 'Vida Acadêmica');

        return view('meus_horarios.index', [
            'grade' => $grade,
            'escolas' => $escolas,
            'anos' => $anos,
            'turnos' => Turma::TURNOS,
            'escolaId' => $escolaId,
            'anoLetivoId' => $anoLetivoId,
            'turno' => $turno,
            'diasLabels' => HorarioSlot::DIAS,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }
}
