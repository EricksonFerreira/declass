<?php

namespace App\Http\Requests\RotaTransporte;

class UpdateRotaTransporteRequest extends StoreRotaTransporteRequest
{
    public function rules(): array
    {
        // Rota nao muda de escola apos o cadastro.
        $rules = parent::rules();
        unset($rules['escola_id']);

        return $rules;
    }
}
