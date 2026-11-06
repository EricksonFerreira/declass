<?php

namespace App\Services;

use App\Models\Avaliacao;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Nota $nota)
    {
        $this->model = $nota;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Nota) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Nota');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Lancamento em lote (upsert por avaliacao+matricula).
     * nota vazia remove o registro; valor acima da nota maxima rejeita.
     */
    public function salvarNotas(Avaliacao $avaliacao, array $itens): void
    {
        $matriculaIds = collect($itens)->pluck('matricula_id')->map('intval')->unique()->values();

        $validas = DB::table('matriculas')
            ->whereIn('id', $matriculaIds)
            ->where('turma_id', $avaliacao->turma_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map('intval');

        if ($matriculaIds->diff($validas)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'notas' => 'Uma ou mais matrículas não pertencem a esta turma.',
            ]);
        }

        foreach ($itens as $item) {
            $nota = $item['nota'] ?? null;
            $nota = ($nota === '' || $nota === null) ? null : (float) $nota;

            if ($nota !== null && ($nota < 0 || $nota > (float) $avaliacao->nota_maxima)) {
                throw ValidationException::withMessages([
                    'notas' => 'Nota fora do intervalo permitido (máx. '.$avaliacao->nota_maxima.').',
                ]);
            }

            $existente = $this->model->newQuery()
                ->where('avaliacao_id', $avaliacao->id)
                ->where('matricula_id', $item['matricula_id'])
                ->first();

            if ($nota === null && ! $existente) {
                continue; // nada a gravar
            }

            if ($nota === null) {
                $existente?->delete();

                continue;
            }

            $this->model->updateOrCreate(
                ['avaliacao_id' => $avaliacao->id, 'matricula_id' => $item['matricula_id']],
                [
                    'empresa_id' => $avaliacao->empresa_id,
                    'nota' => $nota,
                    'observacao' => $item['observacao'] ?? null,
                ]
            );
        }
    }

    /** Notas da avaliacao indexadas por matricula_id. */
    public function porMatricula(Avaliacao $avaliacao)
    {
        return $this->model->newQuery()
            ->where('avaliacao_id', $avaliacao->id)
            ->get()
            ->keyBy('matricula_id');
    }
}
