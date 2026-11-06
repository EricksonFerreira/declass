<?php

namespace App\Services;

use App\Models\Diario;
use App\Models\Frequencia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FrequenciaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Frequencia $frequencia)
    {
        $this->model = $frequencia;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Frequencia) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Frequencia');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Grava a chamada em lote (upsert por matricula+diario).
     * Rejeita matriculas que nao pertencem a turma do diario.
     */
    public function salvarChamada(Diario $diario, array $itens): void
    {
        $matriculaIds = collect($itens)->pluck('matricula_id')->map('intval')->unique()->values();

        $validas = DB::table('matriculas')
            ->whereIn('id', $matriculaIds)
            ->where('turma_id', $diario->turma_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map('intval');

        $invalidas = $matriculaIds->diff($validas);
        if ($invalidas->isNotEmpty()) {
            throw ValidationException::withMessages([
                'frequencias' => 'Uma ou mais matrículas não pertencem a esta turma.',
            ]);
        }

        DB::transaction(function () use ($diario, $itens) {
            foreach ($itens as $item) {
                $this->model->updateOrCreate(
                    [
                        'diario_id' => $diario->id,
                        'matricula_id' => $item['matricula_id'],
                    ],
                    [
                        'empresa_id' => $diario->empresa_id,
                        'status' => $item['status'],
                        'justificativa' => $item['justificativa'] ?? null,
                    ]
                );
            }
        });
    }

    /** Frequencias ja registradas do diario, indexadas por matricula_id. */
    public function porMatricula(Diario $diario)
    {
        return $this->model->newQuery()
            ->where('diario_id', $diario->id)
            ->get()
            ->keyBy('matricula_id');
    }
}
