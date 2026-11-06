<?php

namespace App\Http\Requests\AnoLetivo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnoLetivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'escola_id' => [
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'ano' => [
                'required', 'integer', 'between:2000,2100',
                Rule::unique('anos_letivos', 'ano')
                    ->where('empresa_id', $empresaId)
                    ->where('escola_id', $this->input('escola_id')),
            ],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'status' => ['nullable', Rule::in(array_keys(\App\Models\AnoLetivo::STATUS))],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
