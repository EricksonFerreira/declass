<?php

namespace App\Http\Requests\Veiculo;

class UpdateVeiculoRequest extends StoreVeiculoRequest
{
    public function rules(): array
    {
        // Veiculo nao muda de escola apos o cadastro.
        $rules = parent::rules();
        unset($rules['escola_id']);

        return $rules;
    }
}
