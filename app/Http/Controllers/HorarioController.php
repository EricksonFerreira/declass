<?php

namespace App\Http\Controllers;

use App\Models\AnoLetivo;
use App\Services\BreadcrumbService;
use App\Services\HorarioExportService;
use App\Services\PdfService;
use App\Services\TurmaGradeService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Visao geral das grades horarias: lista as turmas da escola
 * atual e exporta todas as grades em XLSX (uma aba por turma)
 * ou PDF consolidado.
 */
class HorarioController extends BaseController
{
    public function __construct(
        protected TurmaGradeService $gradeService,
        protected HorarioExportService $exportService,
        protected PdfService $pdfService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Grades de Horário';

        $this->middleware('can:horarios.index')->only(['index']);
        $this->middleware('can:horarios.exportar')->only(['exportar']);
        $this->middleware('can:horarios.vincular')->only(['vincular']);
    }

    public function index(Request $request)
    {
        $anoLetivoId = $request->integer('ano_letivo_id') ?: null;
        $escolaId = $request->integer('escola_id') ?: null;
        $turno = $request->input('turno') ?: null;
        $horarioId = $request->input('horario_id');

        $query = \App\Models\Turma::query()
            ->where('ativo', 'S')
            ->with(['escola', 'anoLetivo:id,ano', 'etapa:id,nome', 'horario:id,nome'])
            ->orderBy('nome');

        if ($anoLetivoId) {
            $query->where('ano_letivo_id', $anoLetivoId);
        }
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }
        if ($turno) {
            $query->where('turno', $turno);
        }
        if ($horarioId === 'sem') {
            $query->whereNull('horario_id');
        } elseif ($horarioId) {
            $query->where('horario_id', (int) $horarioId);
        }

        $turmas = $query->get()->map(function ($t) {
            $t->aulas_count = \App\Models\HorarioTurma::query()->where('turma_id', $t->id)->count();
            $t->tem_matriz = \App\Models\HorarioSlot::query()
                ->when($t->horario_id, fn ($q) => $q->where('horario_id', $t->horario_id))
                ->when(! $t->horario_id && $t->escola_id && $t->turno,
                    fn ($q) => $q->where('escola_id', $t->escola_id)->where('turno', $t->turno))
                ->when(! $t->horario_id && ! ($t->escola_id && $t->turno), fn ($q) => $q->whereRaw('1 = 0'))
                ->exists();

            return $t;
        });

        $anosLetivos = AnoLetivo::query()->orderByDesc('ano')->get(['id', 'ano', 'escola_id']);

        // Arvore de collapses: escola > ano letivo > turno > turmas.
        $grupos = $turmas->groupBy('escola_id')
            ->map(function ($porEscola, $escolaId) {
                return [
                    'id' => $escolaId,
                    'escola' => $porEscola->first()->escola,
                    'total' => $porEscola->count(),
                    'anos' => $porEscola->groupBy('ano_letivo_id')
                        ->map(function ($porAno, $anoId) {
                            return [
                                'id' => $anoId,
                                'ano' => $porAno->first()->anoLetivo->ano ?? '—',
                                'total' => $porAno->count(),
                                'turnos' => $porAno->groupBy('turno')
                                    ->map(fn ($porTurno, $turno) => [
                                        'turno' => $turno,
                                        'label' => \App\Models\Turma::TURNOS[$turno] ?? $turno,
                                        'total' => $porTurno->count(),
                                        'turmas' => $porTurno->sortBy('nome')->values(),
                                    ])
                                    ->sortBy(fn ($g) => array_search($g['turno'], array_keys(\App\Models\Turma::TURNOS)))
                                    ->values(),
                            ];
                        })
                        ->sortByDesc('ano')
                        ->values(),
                ];
            })
            ->sortBy(fn ($g) => $g['escola']->nome ?? '')
            ->values();

        $matrizes = \App\Models\Horario::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->withCount('slots')
            ->orderBy('nome')
            ->get(['id', 'nome', 'escola_id', 'turno', 'ativo']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Grades de Horário', 'Estrutura Acadêmica');

        return view('horarios.index', [
            'turmas' => $turmas,
            'grupos' => $grupos,
            'anosLetivos' => $anosLetivos,
            'anoLetivoId' => $anoLetivoId,
            'matrizes' => $matrizes,
            'matrizesJs' => $matrizes->map(fn ($m) => [
                'id' => $m->id, 'nome' => $m->nome, 'escola_id' => $m->escola_id,
                'turno' => $m->turno, 'slots' => $m->slots_count, 'ativo' => $m->ativo,
            ])->values(),
            'escolas' => \App\Models\Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'turnos' => \App\Models\Turma::TURNOS,
            'filtros' => [
                'escola_id' => $escolaId,
                'turno' => $turno,
                'horario_id' => $horarioId,
            ],
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    /**
     * Vincula uma matriz de horario (escola+turno compativeis) a
     * uma turma que esta sem grade. A partir dai a turma usa os
     * slots dessa matriz especifica.
     */
    public function vincular(Request $request, $turmaId)
    {
        $turma = \App\Models\Turma::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($turmaId);

        $data = $request->validate([
            'horario_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('horarios', 'id')
                    ->where('empresa_id', $turma->empresa_id)
                    ->where('escola_id', $turma->escola_id)
                    ->where('turno', $turma->turno),
            ],
        ], [], ['horario_id' => 'matriz de horários']);

        $turma->update(['horario_id' => $data['horario_id']]);

        $matriz = \App\Models\Horario::find($data['horario_id']);

        return redirect()->route('horarios.index', $request->only(['ano_letivo_id', 'escola_id', 'turno', 'horario_id']))
            ->with('success', "Matriz \"{$matriz->nome}\" vinculada à turma {$turma->nome}.");
    }

    public function exportar(Request $request)
    {
        $data = $request->validate([
            'formato' => ['required', 'in:xlsx,pdf'],
            'exibir' => ['nullable', 'in:disciplina,professor'],
            'ano_letivo_id' => ['nullable', 'integer', 'exists:anos_letivos,id'],
        ]);

        $comProfessor = ($data['exibir'] ?? 'disciplina') === 'professor';

        $query = \App\Models\Turma::query()
            ->where('ativo', 'S')
            ->with(['escola', 'anoLetivo:id,ano', 'etapa:id,nome'])
            ->orderBy('nome');

        if (! empty($data['ano_letivo_id'])) {
            $query->where('ano_letivo_id', $data['ano_letivo_id']);
        }

        $turmas = $query->get();
        abort_if($turmas->isEmpty(), 404, 'Nenhuma turma encontrada para exportar.');

        $sufixo = $comProfessor ? '-com-professores' : '';

        if ($data['formato'] === 'xlsx') {
            $spreadsheet = $this->exportService->planilha($turmas, $comProfessor);

            return new StreamedResponse(function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="grades-horarias'.$sufixo.'.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        return $this->pdfService->download(
            'pdf.horarios-todas',
            [
                'turmas' => $this->exportService->dadosPdf($turmas, $comProfessor),
                'dias' => \App\Models\HorarioSlot::DIAS,
                'comProfessor' => $comProfessor,
            ],
            'grades-horarias'.$sufixo.'.pdf',
            'a4',
            'landscape'
        );
    }
}
