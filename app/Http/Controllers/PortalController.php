<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Responsavel;
use App\Services\BoletimService;
use Illuminate\Support\Collection;

class PortalController extends Controller
{
    public function __construct(private BoletimService $boletimService) {}

    /**
     * Home do portal: lista os alunos vinculados ao usuario logado
     * (como responsavel ou como aluno).
     */
    public function index()
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with('turma.anoLetivo', 'turma.etapa')
            ->get()
            ->keyBy('aluno_id');

        return view('portal.index', [
            'titulo' => 'Portal Familiar',
            'alunos' => $alunos,
            'matriculas' => $matriculas,
        ]);
    }

    /**
     * Boletim do aluno, acessivel apenas se o aluno estiver vinculado
     * a conta logada (responsavel vinculado ou o proprio aluno).
     */
    public function aluno($alunoId)
    {
        $user = auth()->user();
        $aluno = $this->alunosDoUsuario($user)->firstWhere('id', (int) $alunoId);

        abort_if(! $aluno, 403, 'Aluno nao vinculado a esta conta.');

        $dados = $this->boletimService->dadosAluno($aluno);

        abort_if(! $dados || ! $dados['matricula'], 404, 'Aluno sem matricula ativa.');

        return view('portal.aluno', array_merge($dados, [
            'titulo' => 'Boletim - '.$aluno->nome,
            'aluno' => $aluno,
        ]));
    }

    /**
     * Alunos acessiveis pela conta: filhos de responsaveis vinculados
     * + o proprio aluno quando a conta e de aluno.
     */
    private function alunosDoUsuario($user): Collection
    {
        $ids = collect();

        $responsavel = Responsavel::query()->where('user_id', $user->id)->first();
        if ($responsavel) {
            $ids = $ids->merge($responsavel->alunos()->pluck('alunos.id'));
        }

        $proprio = Aluno::query()->where('user_id', $user->id)->first();
        if ($proprio) {
            $ids->push($proprio->id);
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return Aluno::query()->with('pessoa')->whereIn('id', $ids->unique())->get();
    }
}
