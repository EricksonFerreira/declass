<?php

namespace App\Http\Requests\Organizacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(array_keys(\App\Models\Organizacao::TIPOS))],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
