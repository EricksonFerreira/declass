<?php

namespace App\Http\Requests\PeriodoLetivo;

class UpdatePeriodoLetivoRequest extends StorePeriodoLetivoRequest
{
    public function rules(): array
    {
        // O ano letivo e imutavel apos a criacao — escola_id e
        // derivado dele, entao os dois ficam fora do update.
        $rules = parent::rules();
        unset($rules['ano_letivo_id']);

        return $rules;
    }
}
