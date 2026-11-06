<?php

namespace App\Http\Requests\Aluno;

use App\Models\Aluno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVinculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'responsavel_id' => [
                'required', 'integer',
                Rule::exists('responsaveis', 'id')->where('empresa_id', $empresaId),
            ],
            'tipo' => ['nullable', Rule::in(array_keys(Aluno::TIPOS_RESPONSAVEL))],
            'principal' => ['nullable', 'boolean'],
            'pode_retirar' => ['nullable', 'boolean'],
            'autoriza_contato' => ['nullable', 'boolean'],
            'restricoes' => ['nullable', 'string', 'max:2000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
