<?php

namespace App\Http\Requests\Feriado;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeriadoRequest extends FormRequest
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
                'nullable', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'suspende_aulas' => ['required', Rule::in(['S', 'N'])],
        ];
    }

    public function messages(): array
    {
        return [
            'data_fim.after_or_equal' => 'O fim do período não pode ser anterior ao início.',
        ];
    }
}
