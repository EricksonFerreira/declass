<?php

namespace App\Http\Requests\PlanoMotoristaApp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanoMotoristaAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['nullable', 'integer', 'exists:empresas,id'],
            'nome' => ['required', 'string', 'max:120'],
            'valor_periodo' => ['required', 'numeric', 'min:0'],
            'periodo' => ['required', Rule::in(['semana', 'quinze', 'mes', 'trimestre', 'semestre', 'ano'])],
            'km_tipo' => ['required', Rule::in(['livre', 'limitado'])],
            'km_por_periodo' => ['nullable', 'integer', 'min:1', 'required_if:km_tipo,limitado'],
            'calcao' => ['nullable', 'numeric', 'min:0'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'opcionais' => ['nullable', 'array'],
            'opcionais.*' => ['integer', 'exists:opcionais,id'],
        ];
    }
}
