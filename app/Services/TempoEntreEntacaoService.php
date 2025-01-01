<?php

namespace App\Services;

use App\Models\TempoEntreEntacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TempoEntreEntacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TempoEntreEntacao $tempo_entre_entacao)
    {
        $this->model = $tempo_entre_entacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TempoEntreEntacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TempoEntreEntacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
    public function hasDuplicateEstacao($estacaoOrigemId, $estacaoDestinoId, $ignoreId = null)
    {
        $query = $this->model->where('estacao_origem_id', $estacaoOrigemId)
            ->where('estacao_destino_id', $estacaoDestinoId);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function getEstacaoDestino($id)
    {
        try {

            $registro = $this->model->where('id', $id)->first()->estacaoDestino;
            return [$registro->id => $registro->nome];
        } catch (\Exception $e) {
            return [];
        }
    }
}
