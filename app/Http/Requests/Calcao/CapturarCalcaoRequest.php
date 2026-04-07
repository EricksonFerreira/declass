<?php

namespace App\Http\Requests\Calcao;

use Illuminate\Foundation\Http\FormRequest;

class CapturarCalcaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valor_captura'  => 'required|numeric|min:0.01',
            'justificativa'  => 'required|string|min:5|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'valor_captura.required'  => 'Informe o valor a capturar.',
            'valor_captura.min'       => 'O valor de captura deve ser maior que zero.',
            'justificativa.required'  => 'A justificativa para captura do calcao e obrigatoria.',
            'justificativa.min'       => 'A justificativa deve ter ao menos 5 caracteres.',
        ];
    }
}
