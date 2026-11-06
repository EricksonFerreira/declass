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
    }

    public function index(Request $request)
    {
        $anoLetivoId = $request->integer('ano_letivo_id') ?: null;

        $query = \App\Models\Turma::query()
            ->where('ativo', 'S')
            ->with(['escola', 'anoLetivo:id,ano', 'etapa:id,nome'])
            ->orderBy('nome');

        if ($anoLetivoId) {
            $query->where('ano_letivo_id', $anoLetivoId);
        }

        $turmas = $query->get()->map(function ($t) {
            $t->aulas_count = \App\Models\HorarioTurma::query()->where('turma_id', $t->id)->count();
            $t->tem_matriz = $t->escola_id && $t->turno
                && \App\Models\HorarioSlot::query()
                    ->where('escola_id', $t->escola_id)->where('turno', $t->turno)->exists();

            return $t;
        });

        $anosLetivos = AnoLetivo::query()->orderByDesc('ano')->get(['id', 'ano', 'escola_id']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Grades de Horário', 'Estrutura Acadêmica');

        return view('horarios.index', [
            'turmas' => $turmas,
            'anosLetivos' => $anosLetivos,
            'anoLetivoId' => $anoLetivoId,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
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
