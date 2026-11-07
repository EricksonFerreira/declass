<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\User;
use App\Services\SetupChecklistService;
use App\Services\TurmaGradeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected SetupChecklistService $setupChecklistService,
        protected TurmaGradeService $gradeService,
    ) {}

    public function home(Request $request): View
    {
        $user = $request->user();
        if ($user->isProfessor()) {
            return $this->homeProfessor($user);
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

        return view('home_professor', [
            'titulo' => 'Início',
            'breadcrumb' => [['name' => 'Home', 'active' => true]],
            'aulasHoje' => $aulasHoje,
            'totalTurmas' => $grade['turmas']->count(),
            'chamadasHoje' => $chamadasHoje,
            'avaliacoesSemana' => $avaliacoesSemana,
            'dataHoje' => $hojeData,
        ]);
    }

    public function dashboard(Request $request)
    {
        return redirect()->route('home');
    }
}
