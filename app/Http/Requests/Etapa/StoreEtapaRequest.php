<?php

namespace App\Http\Requests\Etapa;

use App\Models\Etapa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEtapaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'segmento' => ['required', Rule::in(array_keys(Etapa::SEGMENTOS))],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:999'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
