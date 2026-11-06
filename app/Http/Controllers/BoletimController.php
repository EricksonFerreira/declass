<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Turma;
use App\Services\BoletimService;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;

class BoletimController extends BaseController
{
    public function __construct(
        protected BoletimService $boletimService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:boletim.index')->only(['index', 'turma', 'aluno', 'turmaPdf', 'alunoPdf']);
    }

    /** Seletor: escolher turma (grade) ou aluno (individual). */
    public function index()
    {
        $turmas = $this->boletimService->turmasVisiveis(auth()->user());
        $alunos = $this->boletimService->alunosComMatricula(auth()->user());

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Boletim', 'Vida Acadêmica');

        return view('boletim.index', compact('turmas', 'alunos', 'breadcrumb'))
            ->with('titulo', 'Boletim');
    }

    /** Grade da turma: alunos x disciplinas com medias e frequencia. */
    public function turma($turmaId)
    {
        $turma = Turma::query()->with(['escola', 'anoLetivo', 'etapa'])->findOrFail($turmaId);
        abort_unless($this->boletimService->podeVerTurma($turma, auth()->user()), 403);

        $dados = $this->boletimService->dadosTurma($turma);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Boletim', 'boletim.index', "Turma {$turma->nome}"
        );

        return view('boletim.turma', array_merge($dados, compact('turma', 'breadcrumb')))
            ->with('titulo', 'Boletim — '.$turma->nome);
    }

    /** Boletim da turma em PDF (paisagem A4). */
    public function turmaPdf($turmaId)
    {
        $turma = Turma::query()->with(['escola', 'anoLetivo', 'etapa'])->findOrFail($turmaId);
        abort_unless($this->boletimService->podeVerTurma($turma, auth()->user()), 403);

        $dados = $this->boletimService->dadosTurma($turma);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('boletim.pdf-turma', array_merge($dados, ['turma' => $turma]))
            ->setPaper('a4', 'landscape');

        return $pdf->download('boletim-turma-'.$turma->nome.'.pdf');
    }

    /** Boletim individual do aluno (matricula ativa mais recente). */
    public function aluno($alunoId, Request $request)
    {
        $aluno = Aluno::query()->with('pessoa')->findOrFail($alunoId);
        $anoLetivoId = $request->integer('ano_letivo_id') ?: null;

        $dados = $this->boletimService->dadosAluno($aluno, $anoLetivoId);
        abort_if(! $dados, 404, 'Aluno sem matrícula ativa.');

        // professor so ve boletim de aluno em turma que leciona
        $user = auth()->user();
        if ($user->isProfessor()) {
            abort_unless(
                $this->boletimService->podeVerTurma($dados['turma'], $user),
                403
            );
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Boletim', 'boletim.index', $aluno->pessoa->nome ?? 'Aluno'
        );

        return view('boletim.aluno', array_merge($dados, compact('aluno', 'anoLetivoId', 'breadcrumb')))
            ->with('titulo', 'Boletim — '.($aluno->pessoa->nome ?? 'Aluno'));
    }

    /** Boletim individual do aluno em PDF (retrato A4). */
    public function alunoPdf($alunoId, Request $request)
    {
        $aluno = Aluno::query()->with('pessoa')->findOrFail($alunoId);
        $anoLetivoId = $request->integer('ano_letivo_id') ?: null;

        $dados = $this->boletimService->dadosAluno($aluno, $anoLetivoId);
        abort_if(! $dados, 404, 'Aluno sem matrícula ativa.');

        $user = auth()->user();
        if ($user->isProfessor()) {
            abort_unless(
                $this->boletimService->podeVerTurma($dados['turma'], $user),
                403
            );
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('boletim.pdf-aluno', array_merge($dados, [
            'aluno' => $aluno,
            'anoLetivoId' => $anoLetivoId,
        ]))->setPaper('a4');

        return $pdf->download('boletim-'.\Illuminate\Support\Str::slug($aluno->pessoa->nome ?? 'aluno').'.pdf');
    }
}
