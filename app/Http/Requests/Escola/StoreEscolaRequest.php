<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEscolaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
            'capacidade_alunos' => ['nullable', 'integer', 'min:1'],
            'turno_padrao' => ['nullable', Rule::in(array_keys(\App\Models\Escola::TURNOS))],
            'cor_primaria' => ['nullable', 'string', 'max:7'],
            'cor_secundaria' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'ativa' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
