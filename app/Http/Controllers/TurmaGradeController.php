<?php

namespace App\Http\Controllers;

use App\Http\Requests\TurmaGrade\StoreCelulaRequest;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\PdfService;
use App\Services\TurmaGradeService;
use Illuminate\Http\Request;

/**
 * Grade horaria da turma: grade semanal de aulas, edicao por
 * celula e exportacao em PDF.
 */
class TurmaGradeController extends BaseController
{
    public function __construct(
        protected TurmaGradeService $gradeService,
        protected BreadcrumbService $breadcrumbService,
        protected PdfService $pdfService,
    ) {
        $this->titulo = 'Grade Horária';

        $this->middleware('can:turma.grade')->only(['grade']);
        $this->middleware('can:turma.grade.celula.store')->only(['celulaStore']);
        $this->middleware('can:turma.grade.celula.destroy')->only(['celulaDestroy']);
        $this->middleware('can:turma.grade.pdf')->only(['pdf']);
    }

    public function grade(Turma $turma)
    {
        $dados = $this->gradeService->gradeDaTurma($turma);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Turmas', 'turma.index', "Grade: {$turma->nome}", 'Vida Acadêmica'
        );

        return view('turma.grade', $dados + [
            'breadcrumb' => $breadcrumb,
            'diasLabels' => \App\Models\HorarioSlot::DIAS,
        ])->with('titulo', "Grade horária — {$turma->nome}");
    }

    public function celulaStore(StoreCelulaRequest $request, Turma $turma)
    {
        $data = $request->validated();

        $aula = $this->gradeService->salvarCelula(
            $turma,
            (int) $data['dia_semana'],
            (int) $data['ordem'],
            $data
        );
        $aula->load(['disciplina:id,nome,sigla', 'user:id,nome']);

        return response()->json([
            'ok' => true,
            'rotulo' => $this->gradeService->rotuloCelula($aula, true),
            'disciplina' => $aula->disciplina?->sigla ?: $aula->disciplina?->nome,
            'professor' => $aula->user?->nome,
            'sala' => $aula->sala,
        ]);
    }

    public function celulaDestroy(Request $request, Turma $turma)
    {
        $data = $request->validate([
            'dia_semana' => ['required', 'integer'],
            'ordem' => ['required', 'integer'],
        ]);

        $this->gradeService->limparCelula($turma, (int) $data['dia_semana'], (int) $data['ordem']);

        return response()->json(['ok' => true]);
    }

    public function pdf(Request $request, Turma $turma)
    {
        $comProfessor = $request->query('exibir') === 'professor';
        $grade = $this->gradeService->gradeParaExportacao($turma, $comProfessor);

        return $this->pdfService->download(
            'pdf.horario-turma',
            [
                'turma' => $turma->loadMissing(['escola', 'anoLetivo', 'etapa']),
                'export' => $grade,
                'dias' => \App\Models\HorarioSlot::DIAS,
                'comProfessor' => $comProfessor,
            ],
            'grade-'.$turma->id.'.pdf',
            'a4',
            'landscape'
        );
    }
}
