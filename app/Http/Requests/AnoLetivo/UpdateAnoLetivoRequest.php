<?php

namespace App\Http\Requests\AnoLetivo;

use App\Models\AnoLetivo;
use Illuminate\Validation\Rule;

class UpdateAnoLetivoRequest extends StoreAnoLetivoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $anoLetivoId = $this->route('ano-letivo') ?? $this->route('ano_letivo');
        $registro = $anoLetivoId ? AnoLetivo::query()->find($anoLetivoId) : null;

        $empresaId = $this->user()?->empresa_id;

        $rules['ano'] = [
            'required', 'integer', 'between:2000,2100',
            Rule::unique('anos_letivos', 'ano')
                ->where('empresa_id', $empresaId)
                ->where('escola_id', $this->input('escola_id', $registro?->escola_id))
                ->ignore($anoLetivoId, 'id'),
        ];

        return $rules;
    }
}
