<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'ativo' => ['nullable', 'in:S,N'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
