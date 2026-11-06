<?php

namespace App\Services;

use App\Models\Cardapio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CardapioService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Cardapio $cardapio)
    {
        $this->model = $cardapio;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Cardapio) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Cardapio');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $this->validarUnicidade($data);

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $registro = $this->getById($id);
        abort_if(! $registro, 403, 'Sem acesso a este registro.');

        $this->validarUnicidade($data, $registro);

        return parent::update($id, $data);
    }

    /**
     * Uma refeicao por tipo por dia por escola (unique no banco).
     */
    private function validarUnicidade(array $data, ?Cardapio $atual = null): void
    {
        $escolaId = $atual?->escola_id ?? $data['escola_id'] ?? null;

        $duplicado = Cardapio::query()
            ->where('escola_id', $escolaId)
            ->whereDate('data', $data['data'])
            ->where('tipo', $data['tipo'])
            ->when($atual, fn ($q) => $q->where('id', '!=', $atual->id))
            ->exists();

        if ($duplicado) {
            throw ValidationException::withMessages([
                'data' => 'Já existe um cardápio deste tipo para esta escola nesta data.',
            ]);
        }
    }
}
