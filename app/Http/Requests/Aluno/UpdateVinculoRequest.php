<?php

namespace App\Http\Requests\Aluno;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVinculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'principal' => ['nullable', 'boolean'],
            'pode_retirar' => ['nullable', 'boolean'],
            'autoriza_contato' => ['nullable', 'boolean'],
            'restricoes' => ['nullable', 'string', 'max:2000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
