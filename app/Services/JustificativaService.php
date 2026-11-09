<?php

namespace App\Services;

use App\Models\Frequencia;
use App\Models\Justificativa;
use App\Models\Matricula;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Atestados e declaracoes registrados pela secretaria para
 * justificar ou abonar ausencias. A criacao converte as faltas
 * do periodo (todas as disciplinas ou uma especifica) e guarda
 * o vinculo em frequencias.justificativa_id para reversao.
 */
class JustificativaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Justificativa $justificativa)
    {
        $this->model = $justificativa;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Justificativa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Justificativa');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Paginacao com filtros da sessao. 'nome' busca no aluno/pessoa
     * via matricula; 'turma_id'/'escola_id'/'efeito' sao diretos.
     */
    public function paginateComFiltros(array $filtros, int $perPage = 15)
    {
        $filtros = $this->sanitizeEscolaContextFilter($filtros);

        $nome = $this->filtroValor($filtros, 'nome');
        $efeito = $this->filtroValor($filtros, 'efeito');
        $turmaId = $this->filtroValor($filtros, 'turma_id');
        $escolaId = $this->filtroValor($filtros, 'escola_id');

        return Justificativa::query()
            ->with(['matricula.aluno.pessoa:id,nome', 'matricula.turma:id,nome', 'disciplina:id,nome', 'registradoPor:id,nome', 'escola:id,nome'])
            ->where('justificativas.empresa_id', auth()->user()->empresa_id)
            ->when($turmaId, fn ($q) => $q->whereHas('matricula', fn ($m) => $m->where('turma_id', (int) $turmaId)))
            ->when($escolaId, fn ($q) => $q->where('justificativas.escola_id', (int) $escolaId))
            ->when($efeito, fn ($q) => $q->where('efeito', $efeito))
            ->when($nome, fn ($q) => $q->whereHas('matricula.aluno.pessoa', fn ($p) => $p->where('nome', 'like', '%'.$nome.'%')))
            ->orderByDesc('data_inicio')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Matriculas ativas (com nome do aluno) por turma, para o
     * select do formulario agrupado por turma.
     *
     * @return array<int, array<int, string>> [turma_id => [matricula_id => aluno]]
     */
    public function matriculasPorTurma(Collection $turmas): array
    {
        if ($turmas->isEmpty()) {
            return [];
        }

        return Matricula::query()
            ->with('aluno.pessoa:id,nome')
            ->whereIn('turma_id', $turmas->pluck('id'))
            ->where('status', 'ativa')
            ->orderBy('numero_chamada')
            ->get()
            ->groupBy('turma_id')
            ->map(fn ($ms) => $ms->mapWithKeys(fn ($m) => [
                $m->id => $m->aluno?->pessoa?->nome ?? "Matrícula #{$m->id}",
            ])->all())
            ->all();
    }

    /**
     * Disciplinas ofertadas por turma, para o select opcional de
     * disciplina (vazio = vale para todas).
     *
     * @return array<int, array<int, string>> [turma_id => [disciplina_id => nome]]
     */
    /** Matriculas ativas por turma no formato do picker: [{id, rotulo, detalhe}]. */
    public function matriculasPickerPorTurma(Collection $turmas): array
    {
        if ($turmas->isEmpty()) {
            return [];
        }

        return Matricula::query()
            ->with('aluno.pessoa:id,nome')
            ->whereIn('turma_id', $turmas->pluck('id'))
            ->where('status', 'ativa')
            ->orderBy('numero_chamada')
            ->get()
            ->groupBy('turma_id')
            ->map(fn ($ms) => $ms->values()->map(fn ($m) => [
                'id' => $m->id,
                'rotulo' => $m->aluno?->pessoa?->nome ?? "Matrícula #{$m->id}",
                'detalhe' => ($m->numero_chamada ? 'Chamada '.$m->numero_chamada : '')
                    .($m->aluno?->registro_academico ? ' · RA '.$m->aluno->registro_academico : ''),
            ])->all())
            ->all();
    }

    public function disciplinasPorTurma(Collection $turmas): array
    {
        if ($turmas->isEmpty()) {
            return [];
        }

        return DB::table('turma_disciplina')
            ->join('disciplinas', 'turma_disciplina.disciplina_id', '=', 'disciplinas.id')
            ->whereIn('turma_disciplina.turma_id', $turmas->pluck('id'))
            ->orderBy('disciplinas.nome')
            ->get(['turma_disciplina.turma_id', 'disciplinas.id', 'disciplinas.nome'])
            ->groupBy('turma_id')
            ->map(fn ($rs) => $rs->mapWithKeys(fn ($r) => [$r->id => $r->nome])->all())
            ->all();
    }

    /**
     * Cria a justificativa e aplica o efeito as faltas do periodo.
     */
    public function create(array $dados): Justificativa
    {
        return DB::transaction(function () use ($dados) {
            $matricula = Matricula::with('turma')->findOrFail($dados['matricula_id']);

            $arquivo = $dados['arquivo'] ?? null;
            unset($dados['arquivo']);

            $justificativa = Justificativa::create(array_merge($dados, [
                'empresa_id' => $matricula->empresa_id,
                'escola_id' => $matricula->turma->escola_id,
            ]));

            if ($arquivo instanceof UploadedFile) {
                $justificativa->arquivo_path = UploadStorage::putFile($arquivo, "justificativas/{$justificativa->id}", null, 'public');
                $justificativa->save();
            }

            $this->aplicarNasFrequencias($justificativa);

            return $justificativa;
        });
    }

    /**
     * Converte as faltas do periodo no efeito da justificativa.
     * Abono tambem abrange faltas ja lancadas como justificada;
     * justificativa simples nao altera faltas abonadas.
     */
    public function aplicarNasFrequencias(Justificativa $justificativa): int
    {
        $statusBase = $justificativa->efeito === 'abonada'
            ? ['falta', 'justificada']
            : ['falta'];

        $afetadas = Frequencia::query()
            ->where('matricula_id', $justificativa->matricula_id)
            ->whereIn('status', $statusBase)
            ->whereHas('diario', fn ($d) => $d
                ->whereBetween('data', [$justificativa->data_inicio, $justificativa->data_fim])
                ->when($justificativa->disciplina_id, fn ($w) => $w->where('disciplina_id', $justificativa->disciplina_id)))
            ->get();

        $textoPadrao = trim($justificativa->tipoLabel().($justificativa->motivo ? " — {$justificativa->motivo}" : ''));

        foreach ($afetadas as $frequencia) {
            $frequencia->status = $justificativa->efeito;
            $frequencia->justificativa_id = $justificativa->id;
            if (! $frequencia->justificativa) {
                $frequencia->justificativa = $textoPadrao;
            }
            $frequencia->save();
        }

        return $afetadas->count();
    }

    /**
     * Exclusao reverte as frequencias ainda vinculadas: voltam a
     * 'falta' somente se seguirem com o efeito aplicado por este
     * registro (lancamentos posteriores nao sao tocados).
     */
    public function delete($id): bool
    {
        $justificativa = $this->getById($id);
        if (! $justificativa) {
            return false;
        }

        return DB::transaction(function () use ($justificativa) {
            Frequencia::query()
                ->where('justificativa_id', $justificativa->id)
                ->where('status', $justificativa->efeito)
                ->update(['status' => 'falta', 'justificativa_id' => null]);

            $arquivo = $justificativa->arquivo_path;
            $excluida = (bool) $justificativa->delete();

            if ($excluida && $arquivo && Storage::disk('public')->exists($arquivo)) {
                Storage::disk('public')->delete($arquivo);
            }

            return $excluida;
        });
    }

    /**
     * Turmas elegiveis para vinculo (gestao): ano letivo atual,
     * ordenadas por escola/nome, para o select do formulario.
     */
    public function turmasOpcoes(): Collection
    {
        return \App\Models\Turma::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->whereHas('anoLetivo', fn ($a) => $a->where('ativo', 'S'))
            ->orderBy('escola_id')
            ->orderBy('nome')
            ->get(['id', 'nome', 'escola_id', 'ano_letivo_id']);
    }
}
