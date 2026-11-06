<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Escola;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Support\EscolaContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Agenda de avaliacoes: calendario mensal somente-leitura
 * das provas/trabalhos lancados nas turmas. Professor ve
 * apenas avaliacoes das turmas que leciona.
 */
class AgendaAvaliacaoController extends BaseController
{
    public function __construct(
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:avaliacao.agenda')->only('index');
    }

    public function index(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));
        $mesCarbon = Carbon::parse($mes.'-01')->startOfMonth();

        $filtros = $request->only(['escola_id', 'turma_id', 'tipo']);

        // Escola ativa no contexto: o escopo global ja filtra — ignora
        // um escola_id divergente vindo da query string.
        if (app(EscolaContext::class)->filtrar()) {
            unset($filtros['escola_id']);
        }

        $query = Avaliacao::query()
            ->with(['turma.escola', 'turma.anoLetivo', 'turma.etapa', 'disciplina', 'professor'])
            ->whereBetween('data', [$mesCarbon->copy()->startOfMonth(), $mesCarbon->copy()->endOfMonth()])
            ->orderBy('data')
            ->orderBy('id');

        if ($filtros['escola_id'] ?? null) {
            $query->whereHas('turma', fn ($q) => $q->where('escola_id', $filtros['escola_id']));
        }
        if ($filtros['turma_id'] ?? null) {
            $query->where('turma_id', $filtros['turma_id']);
        }
        if ($filtros['tipo'] ?? null) {
            $query->where('tipo', $filtros['tipo']);
        }

        $user = $request->user();
        if ($user->isProfessor()) {
            $query->whereHas('turma.atribuicoes', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('ativo', 'S'));
        }

        $avaliacoes = $query->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Agenda de Avaliações', 'Vida Acadêmica');

        return view('agenda_avaliacao.index', [
            'titulo' => 'Agenda de Avaliações',
            'breadcrumb' => $breadcrumb,
            'avaliacoes' => $avaliacoes,
            'mesCarbon' => $mesCarbon,
            'mesAnterior' => $mesCarbon->copy()->subMonth()->format('Y-m'),
            'mesProximo' => $mesCarbon->copy()->addMonth()->format('Y-m'),
            'mesAtual' => now()->format('Y-m'),
            'filtros' => $filtros,
            'escolas' => Escola::query()->orderBy('nome')->get(['id', 'nome']),
            'turmas' => Turma::query()->orderBy('nome')->get(['id', 'nome']),
            'tipos' => Avaliacao::TIPOS,
        ]);
    }
}
