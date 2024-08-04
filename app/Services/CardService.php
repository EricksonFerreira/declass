<?php

namespace App\Services;

use App\Models\Card;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CardService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof Card) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Card");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
