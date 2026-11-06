<?php

namespace App\Services;

use App\Models\SaudeAluno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaudeAlunoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(SaudeAluno $saudeAluno)
    {
        $this->model = $saudeAluno;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof SaudeAluno) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\SaudeAluno');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Lista fichas com filtros: nome do aluno (like), escola
     * (via aluno.escola_id) e "somente com alertas".
     */
    public function listarComFiltros(array $filtros, int $perPage)
    {
        $filtros = $this->sanitizeEscolaContextFilter($filtros);

        $query = SaudeAluno::query()
            ->with(['aluno.pessoa', 'aluno.escola'])
            ->orderBy('id', 'desc');

        if (! empty($filtros['nome'])) {
            $query->whereHas('aluno.pessoa', fn ($q) => $q->where('nome', 'like', '%'.$filtros['nome'].'%'));
        }

        if (! empty($filtros['escola_id'])) {
            $query->whereHas('aluno', fn ($q) => $q->where('escola_id', $filtros['escola_id']));
        }

        if (! empty($filtros['com_alertas'])) {
            $query->where(fn ($q) => $q
                ->whereNotNull('alergias')->where('alergias', '!=', '')
                ->orWhere(fn ($q2) => $q2->whereNotNull('condicoes_cronicas')->where('condicoes_cronicas', '!=', ''))
                ->orWhere(fn ($q3) => $q3->whereNotNull('medicamentos')->where('medicamentos', '!=', '')));
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** Ficha de saúde do aluno (ou null se ainda não cadastrada). */
    public function paraAluno(int $alunoId): ?SaudeAluno
    {
        return SaudeAluno::query()
            ->where('aluno_id', $alunoId)
            ->with('aluno.pessoa', 'aluno.escola')
            ->first();
    }

    /**
     * Cria ou atualiza a ficha do aluno (upsert por aluno_id,
     * que é unique). Retorna a ficha persistida.
     */
    public function salvar(int $alunoId, array $data): SaudeAluno
    {
        return DB::transaction(function () use ($alunoId, $data) {
            $ficha = SaudeAluno::query()->where('aluno_id', $alunoId)->first();

            if ($ficha) {
                $ficha->update($data);

                return $ficha->fresh();
            }

            return SaudeAluno::create(array_merge($data, ['aluno_id' => $alunoId]));
        });
    }
}
