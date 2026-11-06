<?php

namespace App\Http\Requests\Mensalidade;

use App\Models\Mensalidade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMensalidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'matricula_id' => [
                'required', 'integer',
                Rule::exists('matriculas', 'id')->where('empresa_id', $empresaId),
            ],
            'competencia' => ['required', 'date_format:Y-m'],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'vencimento' => ['required', 'date'],
            'status' => ['nullable', Rule::in(array_keys(Mensalidade::STATUS))],
            'data_pagamento' => ['nullable', 'date'],
            'forma_pagamento' => ['nullable', Rule::in(array_keys(Mensalidade::FORMAS_PAGAMENTO))],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
