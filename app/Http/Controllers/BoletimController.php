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
        protected \App\Services\PdfService $pdfService,
    ) {
        $this->middleware('can:boletim.index')->only(['index', 'turma', 'aluno', 'turmaPdf', 'alunoPdf']);
    }

    /** Seletor: escolher turma (grade) ou aluno (individual). */
    public function index()
    {
        $turmas = $this->boletimService->turmasVisiveis(auth()->user());
        $alunos = $this->boletimService->alunosComMatricula(auth()->user());

        // Dados estruturados para os modais de busca (EntityPicker):
        // turma filtra por escola + ano; aluno filtra por escola + turma.
        $turmasPicker = $turmas->map(fn (Turma $t) => [
            'id' => $t->id,
            'rotulo' => $t->nome,
            'detalhe' => trim(($t->escola->nome ?? '').' · '.($t->anoLetivo->ano ?? ''), ' ·'),
            'escola_id' => $t->escola_id,
            'escola' => $t->escola->nome ?? '',
            'ano_letivo_id' => $t->ano_letivo_id,
            'ano' => $t->anoLetivo->ano ?? '',
        ])->values()->all();

        $alunosPicker = $alunos->map(function (Aluno $a) {
            $mat = $a->matriculas->first();
            $escolaNome = $mat?->turma?->escola?->nome ?? ($a->escola?->nome ?? '');

            return [
                'id' => $a->id,
                'rotulo' => $a->pessoa->nome ?? 'Aluno #'.$a->id,
                'detalhe' => trim(($a->registro_academico ? 'RA '.$a->registro_academico.' · ' : '').($mat?->turma?->nome ?? ''), ' ·'),
                'escola_id' => $mat?->turma?->escola_id ?? $a->escola_id,
                'escola' => $escolaNome,
                'turma_id' => $mat?->turma_id,
                'turma' => $mat?->turma?->nome ?? '',
            ];
        })->values()->all();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Boletim', 'Vida Acadêmica');

        return view('boletim.index', compact('turmasPicker', 'alunosPicker', 'breadcrumb'))
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

        return $this->pdfService->download(
            'boletim.pdf-turma',
            array_merge($dados, ['turma' => $turma]),
            'boletim-turma-'.$turma->nome.'.pdf',
            'a4',
            'landscape'
        );
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

        return $this->pdfService->download(
            'boletim.pdf-aluno',
            array_merge($dados, ['aluno' => $aluno, 'anoLetivoId' => $anoLetivoId]),
            'boletim-'.\Illuminate\Support\Str::slug($aluno->pessoa->nome ?? 'aluno').'.pdf'
        );
    }
}
