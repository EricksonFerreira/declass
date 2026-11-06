<?php

namespace App\Http\Requests\Despesa;

use App\Models\Despesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDespesaRequest extends FormRequest
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
            'fornecedor' => ['nullable', 'string', 'max:190'],
            'descricao' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', Rule::in(array_keys(Despesa::CATEGORIAS))],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'data_vencimento' => ['required', 'date'],
            'forma_pagamento' => ['nullable', 'string', Rule::in(array_keys(Despesa::FORMAS_PAGAMENTO))],
            'observacoes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
